var mvtV = {
    baseurl: null,
    factor: 0,
    level: 0,
    isEdition: false,
    edit_lporiginal: false,
    edit_lpcid: null,
    edit_factors: null
};

$(document).ready(function() {
    mvtV.baseurl = $('#base-url').val();
    getTestList();
    addTestDialog();
});

// Gets the list of test and users id to show in the jtable
var getTestList = function() {
    var sh = screen.height;
    pageSize = Math.floor((15 / 1024) * sh);

    $('#test-list').jtable({
        title: 'Multivariate Testing',
        paging: true, //Enable paging
        pageSize: pageSize,
        sorting: true, //Enable sorting
        defaultSorting: 'creation_date DESC',
        deleteConfirmation: true,
        listmethod: mvtV.baseurl + 'admin/listMvtTests',
        updatemethod: mvtV.baseurl + 'admin/listMvtTests',
        insertmethod: mvtV.baseurl + 'admin/listMvtTests',
        deletemethod: mvtV.baseurl + 'admin/deleteEntireMvt',
        actions: {
            listAction: 'listmethod',
            deleteAction: 'updatemethod',
            updateAction: 'insertmethod',
            createAction: 'deletemethod'
        },
        fields: {
            landingpage_collectionid: {
                key: true,
                create: false,
                edit: false,
                list: false
            },
            name: {
                title: 'Test Name',
                width: '50%'
            },
            clientid_hash: {
                title: 'Account ID',
                width: '30%'
            },
            status: {
                title: 'Status',
                width: '15%'
            },
            code: {
                title: 'Code',
                list: false
            },
            lp_url: {
                title: 'Main URL',
                list: false
            },
            canonical_url: {
                title: 'URL Pattern',
                list: false
            }
        }
    });

    // loads the jtable with the created tests, changes the text of the add button and the css for the same item
    $('#test-list').jtable('load');

    $('.jtable-toolbar-item-text')[0].innerHTML = 'New Test';

    $('.jtable-toolbar-item-text').css({
        'font-weight': 'bold',
        'font-size': '1.2em'
    });

};

// Opens the test dialog to create or edit tests
var openTestDialog = function() {
    $('.ui-dialog').removeClass('alert');
    if (!mvtV.isEdition)
        $("#new-test-dialog text").removeAttr('disabled');

    $("#new-test-dialog").dialog("open");
    return false;
};

// Adds the dialog plugin and validates required fields if any
var addTestDialog = function() {

    var ccode = $("#c-code"),
            lcname = $("#lc-name"),
            lpurl = $("#lp-url"),
            lpcanonical = $("#lp-canonical"),
            allFields = $([]).add(ccode).add(lcname).add(lpurl).add(lpcanonical),
            tips = $(".validateTips");

    // Updates the text of the tips text (to show messages or errors)
    var updateTips = function(t) {
        tips.text(t).addClass("ui-state-highlight");
        setTimeout(function() {
            tips.removeClass("ui-state-highlight", 1500);
        }, 500);
    };

    // Validates the min and max lenght for a string in a required field
    var checkLength = function(o, n, min, max) {
        if (o.val().length > max || o.val().length < min) {
            o.addClass("ui-state-error");
            updateTips(n + " is required");
            document.body.scrollTop = document.documentElement.scrollTop = 0;
            return false;
        } else {
            return true;
        }
    };

    // check for REGEX match in required fields
    var checkRegexp = function(o, regexp, n) {
        if (!(regexp.test(o.val()))) {
            o.addClass("ui-state-error");
            updateTips(n);
            document.body.scrollTop = document.documentElement.scrollTop = 0;
            return false;
        } else {
            return true;
        }
    };

    // Add the dialog plugin to the container div
    $("#new-test-dialog").dialog({
        autoOpen: false,
        modal: true,
        buttons: {
            "Save": function() {
                var bValid = true;
                allFields.removeClass("ui-state-error");

                bValid = bValid && checkRegexp(ccode, /^[0-9a-z]{0,32}$/i, "The client code doesn`t seem to be valid");
                bValid = bValid && checkLength(lcname, 'The test name ', 1, 128);
                bValid = bValid && checkLength(lpurl, 'The main page URL ', 1, 1024);
                bValid = bValid && checkLength(lpcanonical, 'the URL Pattern ', 1, 1024);

                if (bValid) {
                    $('.validateTips')[0].innerHTML = '';
                    allFields.removeClass("ui-state-error");
                    var factors = getFactorString();

                    // If we are editing a test, we need the array of edited elements by separate
                    if (mvtV.isEdition)
                        mvtV.edit_factors = getFactorString('.edition');

                    $('.ui-dialog-buttonset').css('visibility', 'hidden');

                    $.ajax({
                        type: "POST",
                        url: mvtV.baseurl + 'admin/createMvtTests',
                        dataType: 'text',
                        data: {
                            isEdition: mvtV.isEdition ? 1 : 0,
                            lporiginal: mvtV.edit_lporiginal,
                            lpcid: mvtV.edit_lpcid,
                            edited: mvtV.edit_factors,
                            ccode: $('#c-code').val(),
                            lcstatus: $('#lc-status option:selected').val(),
                            lcname: $('#lc-name').val(),
                            lpurl: $('#lp-url').val(),
                            lpcanonical: $('#lp-canonical').val(),
                            factors: factors
                        }
                    }).done(function(res) {
                        if (res.indexOf('ERROR') >= 0) {
                            $('.ui-dialog-buttonset').css('visibility', 'visible');
                            document.body.scrollTop = document.documentElement.scrollTop = 0;
                            updateTips(res);
                        } else {
                            $("#new-test-dialog").dialog("close");
                        }
                    });
                }
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        },
        focus: function() {
            centerAnimate($('.ui-dialog:has(div#new-test-dialog)'));
        },
        close: function() {
            $('.ui-dialog-buttonset').css('visibility', 'visible');
            window.location.reload();
        }
    });

    // when clicking on +Add Factor, adds new fields to the form to allow the user to include new factors and levels
    $('#add-factor').click(function() {
        addFactor();
    });
};

// Returns a json string with the factors and level per factor; if class c ('.edition') is passed, it returns only the edited factor/levels
var getFactorString = function(c) {
    var cls = (c) ? c : '';
    var factors = new Array($('.factors' + cls).length);  // array of factors
    var idf; // each factor id

    var levels; // will contain the array of levels per factor
    var indf = 0; // index for the factors
    var indl = 0; // index for the levels per factor

    $('.factors' + cls).each(function() {
        indl = 0;
        idf = $(this).attr('id').replace(/factor-/, '');

        // array of levels which will contain the id and the code of each level (the value)
        levels = new Array($(this).find('.levels' + cls).length);
        $(this).find('.levels' + cls).each(function() {
            var idl = $(this).attr('id').split('-')[2];
            levels[indl] = {
                idl: idl,
                code: $(this).val()
            };
            indl++;
        });

        // Array of factors which will contain the id, name, css selector and the array of levels
        factors[indf] = new Array(3);
        factors[indf] = {
            idf: idf,
            name: $('#f-name-' + idf).val(),
            selector: $('#css-selector-' + idf).val(),
            levels: levels
        };
        indf++;
    });

    return $.toJSON(factors);
};

// Gets all the related info to the lpc to edit it including lp, mvt factors, levels, etc.
var editTestDialog = function(lpcid) {
    mvtV.isEdition = true;
    mvtV.edit_lpcid = lpcid;

    $.ajax({
        type: "POST",
        url: mvtV.baseurl + 'admin/listTestByLPC',
        dataType: 'json',
        data: {
            lpcid: mvtV.edit_lpcid
        }
    }).done(function(res) {
        mvtV.edit_lporiginal = res.Lpc[0].landing_pageid;
        $('#c-code').val(res.Lpc[0].clientid_hash);
        $('#lc-status option[value="' + res.Lpc[0].status + '"]').prop('selected', true);
        $('#lc-name').val(res.Lpc[0].name);
        $('#lp-url').val(res.Lpc[0].lp_url);
        $('#lp-canonical').val(res.Lpc[0].canonical_url);

        createFactorForEdition(res.Factors);
        createLevelsForEdition(res.Levels);

        openTestDialog();

    }).fail(function(jqXHR, textStatus) {

        alert('there was an error trying to get the info');

    });
};

// Creates the corresponding factors por edition
var createFactorForEdition = function(factors) {
    for (var i = 0; i < factors.length; i++) {
        addFactor(factors[i]);
    }
};

// creates the html for the corresponding levels per factor
var createLevelsForEdition = function(levels) {
    var lev, fid, name, content;

    for (var i = 0; i < levels.length; i++) {
        fid = levels[i].mvt_factor_id;
        lev = levels[i].mvt_level_id;
        name = levels[i].name;
        content = levels[i].level_content;

        $('.level-delete').unbind('click');
        var htm = '<label for="level-' + fid + '-' + lev + '"></label>';
        htm += ' <textarea title="' + name + '" id="level-' + fid + '-' + lev + '" class="edition levels text ui-widget-content ui-corner-all">' + content + '</textarea>';
        htm += ' <button class="delbtn level-delete" id="delete-' + fid + '-' + lev + '" title="Delete this Level">X</button>';
        htm += ' <div class="clear"></div>';
        $('#add-level-' + fid).before(htm);

        // checks if the user clicks on the delete level button
        $('.level-delete').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            deleteLevel($(this).attr('id'));
        });
    }
};

// After creating, updating or deleting rows, this method reloads the jtable
var updateTestList = function() {
    $('#test-list').jtable('load');
};

// Adds the necessary fileds to create a new factor
var addFactor = function(factor) {
    var idf;
    var fname = '';
    var fdom = '';
    var disb = '';
    var cls = '';
    $('.factor-delete').unbind('click');
    $('.level-delete').unbind('click');
    $('.add-level').unbind('click');

    // I we are editing a test, sets the default values for  name and css selector
    if (factor) {
        idf = factor.mvt_factor_id;
        fname = factor.name;
        fdom = factor.dom_path;
        disb = '';
        cls = 'edition';
    } else {
        mvtV.factor++;
        mvtV.level++;
        idf = mvtV.factor;
    }

    // creates the html for the factors and levels
    var htm = '<fieldset class="factors ' + cls + '" id="factor-' + idf + '">';
    htm += '    <button class="delbtn factor-delete" id="delete-' + idf + '" title="Delete this Factor">X</button>';
    htm += '    <label for="f-name-' + idf + '">Factor Name:</label>';
    htm += '    <input ' + disb + ' type="text" id="f-name-' + idf + '" value="' + fname + '" class="text ui-widget-content ui-corner-all" />';
    htm += '    <div class="clear"></div>';
    htm += '    <label for="css-selector-' + idf + '">CSS Selector:</label>';
    htm += '    <input ' + disb + ' type="text" id="css-selector-' + idf + '" value="' + fdom + '" class="css-sel text ui-widget-content ui-corner-all" />';
    htm += '    <div class="clear"></div>';

    // If it is not an edition, adds a level text area by default
    if (!factor) {
        htm += '    <label for="level-' + idf + '-' + mvtV.level + '">Levels:</label>';
        htm += '    <textarea id="level-' + idf + '-' + mvtV.level + '" class="levels text ui-widget-content ui-corner-all"></textarea>';
        htm += '    <button class="delbtn level-delete" id="delete-' + idf + '-' + mvtV.level + '" title="Delete this Level">X</button>';
        htm += '    <div class="clear"></div>';
    }

    htm += '    <a class="add-test-feature add-level" id="add-level-' + idf + '"> + Add Level</a>';
    htm += '    <div class="separator"></div>';
    htm += '</fieldset>';

    $('#new-factors').append(htm);

    // check for a click on "add level" to add a new textarea
    $('.add-level').click(function() {
        addLevel($(this).attr('id'));
    });

    // checks if the user clicks on the delete factor button
    $('.factor-delete').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        deleteFactor($(this).attr('id'));
    });

    // checks if the user clicks on the delete factor button
    $('.level-delete').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        deleteLevel($(this).attr('id'));
    });

    defaultLevelText();
};

// Deletes all fields related to a factor
var deleteFactor = function(del) {

    var fid = del.replace(/delete-/, '');
    var fact = $('#factor-' + fid);
    var isEdit = fact.hasClass('edition');
    var mess = (isEdit) ? 'This action will delete this factor and related levels from the DB, are you sure?' : 'Are you sure?';

    if (confirm(mess)) {
        removeFactorFromUI(del, fact);

        // if the user is editing the test sends the level id to the server to delete the related rows
        if (isEdit) {
            $.ajax({
                type: "POST",
                url: mvtV.baseurl + 'admin/deleteMvtFactor',
                dataType: 'text',
                data: {
                    factor: fid
                }
            }).done(function() {
            }).fail(function(jqXHR, textStatus) {
                alert('there was an error trying to delete this factor, reload the page and try again later');
            });
        }
    }
};

// Removes the entire factor and related level from the UI
var removeFactorFromUI = function(del, fact) {
    $('#' + del).remove();
    $(fact).remove();
    $('label[for="level-' + del + '"]').remove();
};

// Adds a new level textarea
var addLevel = function(fid) {

    $('.level-delete').unbind('click');

    fid = fid.replace(/add-level-/, '');
    mvtV.level++;
    var htm = '<label for="level-' + fid + '-' + mvtV.level + '"></label>';
    htm += ' <textarea id="level-' + fid + '-' + mvtV.level + '" class="levels text ui-widget-content ui-corner-all"></textarea>';
    htm += ' <button class="delbtn level-delete" id="delete-' + fid + '-' + mvtV.level + '" title="Delete this Level">X</button>';
    htm += ' <div class="clear"></div>';
    $('#add-level-' + fid).before(htm);

    // checks if the user clicks on the delete level button
    $('.level-delete').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        deleteLevel($(this).attr('id'));
    });

    defaultLevelText();
};

// Deletes the selected level textarea
var deleteLevel = function(del) {
    var lid = del.replace(/delete-/, '');
    var lev = $('#level-' + lid);
    var isEdit = lev.hasClass('edition');
    var mess = (isEdit) ? 'This action will delete this level from the DB, are you sure?' : 'Are you sure?';

    if (confirm(mess)) {
        var idlevel = lid.split('-');
        idlevel = idlevel[1];
        removeLevelFromUI(del, lev);

        // if the user is editing the test sends the level id to the server to delete the related rows
        if (isEdit) {
            $.ajax({
                type: "POST",
                url: mvtV.baseurl + 'admin/deleteMvtLevel',
                dataType: 'text',
                data: {
                    level: idlevel
                }
            }).done(function() {
            }).fail(function(jqXHR, textStatus) {
                alert('there was an error trying to delete this level, reload the page and try again');
            });
        }
    }
};

// Removes the textarea, label and buttons related to the level to be deleted.
var removeLevelFromUI = function(lid, lev) {
    $('#' + lid).remove();
    $(lev).remove();
    $('label[for="level-' + lid + '"]').remove();
};

// if level is empty, adds the selector to the textarea
var defaultLevelText = function() {
    $('.levels').each(function() {
        $(this).focus(function() {
            if ($(this).val().replace(/ /, '').length < 1) {
                var sel = $(this).parent('fieldset').find('.css-sel').val();
                $(this).val("$('" + sel + "')");
            }
        });
    });
};

// Resets all filds in a form (from http://www.codigomanso.com/es/2008/12/resetear-un-formulario-con-jquery/)
jQuery.fn.reset = function() {
    $(this).each(function() {
        this.reset();
    });
};

// Center the dialog
var centerAnimate = function(div) {
    div.css('visibility', 'hidden');
    setTimeout(function() {
        div.css('visibility', 'visible');
        if (window.innerWidth && window.innerHeight && div.length > 0) {
            var sh = window.innerHeight;
            var h = div.css('height').replace(/px/, '');
            var my = ((sh - h) / 2) - 10;
            if (my > 0) {
                div.animate({
                    top: my
                }, 200);
            }
        }
    }, 10);
};

// Get vars from a URL
var getUrlVars = function(url) {
    var vars = [], hash;
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
};

var var_dump = function(arr, level) {
    var dumped_text = "";
    if (!level)
        level = 0;
    var level_padding = "";
    for (var j = 0; j < level + 1; j++)
        level_padding += " ";
    if (typeof(arr) === 'object') {
        for (var item in arr) {
            var value = arr[item];
            if (typeof(value) === 'object') {
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += this.var_dump(value, level + 1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else {
        dumped_text = "===>" + arr + "<===(" + typeof(arr) + ")";
    }
    return dumped_text;
};