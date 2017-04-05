$(document).ready(function () {
    BT_perso_rules = {
        msgConfCancel: $('#msg_conf_cancel').val(),
        msgDelCond: $('#msg_del_condition').val(),
        selectTooltip: $('#option_select_tt').val(),
        optionSelect: $('#option_select').val(),
        countrySelect: $('#option_select_country').val(),
        msgDelRule: $('#msg_del_rule').val(),
        ruleLabel: $('#rule_label').val(),
        minCond: $('#min_conds').val(),
        tenant: $('#cur_tenant').val(),
        baseurl: $('#baseurl').val(),
        variantIndex: false,
        lang: $('#site_lang').val(),
        loadingTout: false, // Timeout to hide the "loading" overlay when filling "location is" selects
        resizeTout: false, // Timeout to resize the parameter container
        geoCountries: null, // Array with all the geo countries to be displayed when selecting "location is"
        variantRule: 0, // this is the selected rule for the given variant (to select the default rule when opening the popup)
        variantId: false, // if the popup is openend from the 3rd step, this will have the variant Id to update its rules.
        changeMade: false, //when a <select> is changed, a new cond is added, an input is filled, etc.
        currentRules: null, //array of rules that the user previously created
        ruleSelected: false, // the ID if the rule selected from the list
        currentConds: null, //array of condition PER rule previously created
        conditions: null, //array of ALL available conditions (config)
        nestedGet: 0, // tracks the total number of calls to be compared 
        nestedSet: 0, // the total number of ajax responses, when both are equal, the loading animation is hidden
        placeSelect: {
            2: $('#option_select_state').val(), //select state...
            3: $('#option_select_region').val(), // select region/county...
            4: $('#option_select_city').val() // select city...
        },
        nestedSelect: {
            '.geocountry.select': {
                'label': $('#option_select_state').val(),
                'child': '.geostate.select'
            },
            '.geostate.select': {
                'label': $('#option_select_region').val(),
                'child': '.georegion.select'
            },
            '.georegion.select': {
                'label': $('#option_select_city').val(),
                'child': '.geocity.select'
            }
        },
        // Gets the rules that the user has already created
        getCurrentRules: function () {
            $('#perso_rule_list').empty();
            this.changeMade = false;
            this.ruleSelected = false;
            var self = this;
            $.ajax({
                type: 'GET',
                url: this.baseurl + 'personalization/getRuleOptions',
                dataType: 'json'
            }).done(function (res) {
                self.currentRules = res.rules;
                self.conditions = res.conditions;
                self.fillCurrentRules();
            }).fail(function () {
                console.log('Error connecting with the server');
            });
        },
        // Add each rule to the list in the left
        fillCurrentRules: function () {
            var self = this;
            var htm = '';
            if (this.currentRules.rules.length > 0) {
                $.each(this.currentRules.rules, function (ind, rule) {
                    htm += '<span id="rule_' + rule.rule_id + '" class="' + rule.operation + '">' + rule.name + '</span>';
                });
                $('#perso_rule_list').html(htm);
            } else {
                $('.perso_rule_hidden_title').fadeIn(0);
            }
            this.bindRuleActions();
        },
        // gets the list of countries (if they are not already set)
        getGeoCountries: function () {
            var self = this;
            if (this.geoCountries === null) {
                $.ajax({
                    type: "GET",
                    url: this.baseurl + 'personalization/getGeoCountries',
                    datatype: 'json'
                }).done(function (res) {
                    self.geoCountries = $.parseJSON(res);
                }).fail(function () {
                    console.log('error connecting with geonames server');
                }).always(function () {
                    self.getCurrentRules();
                });
            }
        },
        // after filling the list of available rules, selects the default rule from the list
        selectDefaultRule: function (show) {
            if (this.variantIndex) {
                bt_edit_personalization.createNewVdataObject();
            }
            $('#perso_rule_form').fadeOut(0);

            if (parseInt(this.variantRule) > 0) {
                var self = this;
                $('#perso_rule_list span').removeClass('selected');
                setTimeout(function () {
                    self.fillRuleParameters(self.variantRule);
                }, 499);
                self.showMainContainer(show, 499);
            } else if ($('#perso_rule_list span').length) {
                var idf = $('#perso_rule_list span').first().attr('id').replace(/rule_/, '');
                this.fillRuleParameters(idf);
                this.showMainContainer(show, 99);
            } else if (show) {
                this.showMainContainer(true, 99);
            }
        },
        // If the parameter show is true, displays the main container and then the inner form
        showMainContainer: function (show, tout) {
            var self = this;
            if (show) {
                $('#perso_rule_editor_overlay, #perso_rule_editor_wrapper').fadeIn(tout, function () {
                    $('#perso_rule_form').fadeIn(99, function () {
                        self.resizeMainContainer();
                    });
                });
            }
        },
        // When the user clicks on a rule name, the fields are filled with the corresponding data, also catches the ADD and DELETE events
        bindRuleActions: function () {
            var self = this;
            this.bindNameChange();
            $('#perso_rule_list span').off('click');
            $('#perso_rule_add_action').off('click');
            $('#perso_rule_del_action').off('click');

            $('#perso_rule_list span').on('click', function () {
                $(this).off('click');
                self.currentConds = null;
                var r_id = $(this).attr('id').replace(/rule_/i, '');
                if (r_id !== self.ruleSelected) {
                    $('#perso_rule_list span').removeClass('selected');
                    $('#perso_rule_form').fadeOut(150, function () {
                        self.saveSelectedRule(false, r_id);
                    });
                } else if (!$('#rule_' + self.ruleSelected).hasClass('selected')) {
                    $('#rule_' + self.ruleSelected).addClass('selected');
                }

                setTimeout(function () {
                    self.bindRuleActions();
                }, 500);
            });

            $('#perso_rule_add_action').on('click', function () {
                $(this).off('click');
                setTimeout(function () {
                    self.bindRuleActions();
                }, 500);

                if (!self.validateInputData('add')) {
                    return false;
                }
                var rcount = $('#perso_rule_list span').length + 1;
                $('<span id="rule_new' + rcount + '" class="AND">' + self.ruleLabel + ' ' + rcount + '</span>').appendTo('#perso_rule_list');

                self.currentConds = null;
                $('#perso_rule_list span').removeClass('selected');
                $('#perso_rule_form').fadeOut(150, function () {
                    self.saveSelectedRule(false, 'new' + rcount);
                });
            });

            $('#perso_rule_del_action').on('click', function () {
                $(this).off('click');
                var ruleid = $('#perso_rule_list span.selected').attr('id');
                ruleid = ruleid.replace(/rule_/i, '');
                if (typeof (ruleid) !== 'undefined') {
                    self.deleteSelectedRule(ruleid);
                }

                setTimeout(function () {
                    self.bindRuleActions();
                }, 500);
            });
        },
        // after clicking "delete rule" in the confirmation popup, deletes the rule from the UI and calls the controller to be deleted from the DB
        deleteSelectedRule: function (ruleid) {
            var self = this;
            $('#perso_rule_delete, #perso_rule_nodelete').off('click');
            $('#perso_del_rule_name').empty().append(this.msgDelRule + '(' + $('#rule_' + ruleid).text() + ') ?');

            $('#perso_rule_confirm_overlay, #perso_rule_confirm_wrapper').fadeIn(0);
            $('#perso_rule_form').validationEngine('hideAll');

            $('#perso_rule_delete').on('click', function () {
                self.ruleSelected = false;
                $('#rule_' + ruleid).remove();
                $('#perso_rule_list span').removeClass('selected');
                $('.perso_rule_condition_container').remove();
                $('#perso_rule_add_condition').fadeOut(0);
                $('#perso_rule_parameters').fadeOut(0);
                $.ajax({
                    type: "POST",
                    url: self.baseurl + 'personalization/deleteSelectedRule',
                    datatype: 'text',
                    data: {
                        ruleid: ruleid
                    }
                }).done(function () {
                    self.updateDeletedRuleVariants(ruleid);
                    self.getCurrentRules();
                    $('.perso_confirm_popup').fadeOut(0);
                }).fail(function () {
                    console.log('error connecting with the server');
                });
            });

            $('#perso_rule_nodelete').on('click', function () {
                $('.perso_confirm_popup').fadeOut(0);
            });
        },
        // When a rule is deleted, every variant rule link and variantsData object has to be updated -- or the page reloaded if we are in testdetails page
        updateDeletedRuleVariants: function (ruleid) {
            var self = this;
            if (location.href.indexOf('/lpc/lcd') >= 0) {
                bt_edit_personalization.deletedRule = true;
                $.each(bt_edit_personalization.newVdata, function (ind, vdata) {
                    if (parseInt(vdata.rule_id) === parseInt(ruleid)) {
                        bt_edit_personalization.newVdata[ind]['rule_id'] = null;
                        bt_edit_personalization.newVdata[ind]['rulename'] = null;
                    }
                });
            }

            $('.perso_wizard_link').each(function () {
                if ($(this).attr('id') === 'variant_rule_' + ruleid) {
                    $(this).attr('id', 'variant_rule_0');

                    if ($(this).hasClass('perso_add_rule')) {
                        var tabId = $(this).closest('div.tab').attr('id');
                        BlackTri.personalization.updateVariantRule(tabId, 0, $('#perso-unpersonalized').val(), true);
                        BTVariantsData.pages[BTVariantsData.activePage].variants[tabId].persorule = null;
                    } else if ($(this).hasClass('rule-table-link')) {
                        $(this).closest('td').find('span.span-table-rule').text($('#perso-unpersonalized').val());
                    } else if ($(this).hasClass('rule-list-link')) {
                        $(this).closest('div#perso-complete-rule').find('span.span-list-rule').text($('#perso-unpersonalized').val());
                    }
                }
            });
            if (ruleid.toString() === this.variantRule.toString()) {
                this.variantRule = 0;
            }
        },
        // fills the rule name and the "rule is valid if" <select> for the selected rule and shows the conditions select/input container
        fillRuleParameters: function (r_id) {
            var self = this;
            $('#perso_rule_list span').removeClass('selected');
            var r_op = $('#rule_' + r_id).hasClass('OR') ? 'OR' : 'AND';
            var r_name = $('#rule_' + r_id).text();
            this.ruleSelected = r_id;
            $.each(this.currentRules.conds, function (ind, cond) {
                if (typeof (cond[r_id]) !== 'undefined') {
                    self.currentConds = cond[r_id];
                    return false;
                }
            });

            $('#rule_' + r_id).addClass('selected');
            $('#perso_rule_validif option[value="' + r_op + '"]').prop('selected', true);
            $('.perso_rule_condition_container').remove();
            $('#perso_rule_add_condition').css('display', 'inline-block');
            $('.perso_rule_hidden_title').fadeOut(0);
            $('#perso_rule_parameters').fadeIn(0);
            $('#perso_rule_name').val(r_name);
            this.fillCondsPerRule();
        },
        // For each current rule, creates and shows the corresponding conditions in the right container
        fillCondsPerRule: function () {
            var self = this;
            var sub = {};
            var idr = 0;
            var args;
            $('#perso_rule_form').fadeIn(250);
            if (typeof (this.currentConds) !== 'undefined' && this.currentConds !== null && this.currentConds.length > 0) {
                $.each(this.currentConds, function (ind, cond) {
                    try {
                        args = $.parseJSON(cond.arg);
                    } catch (e) {
                        args = cond.arg;
                    }
                    idr = 'ID' + cond.rule_condition_id;
                    self.fillGenericConditions(idr, cond);
                    sub[idr] = args;
                });

                setTimeout(function () {
                    self.fillSubconditionsPerRule(sub);
                }, 99);
            } else {
                this.fillGenericConditions('NEW' + 0, null);
                this.changeMade = true;
                self.resizeMainContainer();
            }
        },
        // Selects the saved sub-condition for every condition that is assigned to a rule
        fillSubconditionsPerRule: function (sub) {
            var self = this;
            var ord = 0;
            var locations = 0;

            $.each(sub, function (index, arguments) {
                var ind = index.replace(/ID/, '');
                var arg = typeof (arguments) === 'object' && typeof (arguments.value) !== 'undefined' ? arguments.value : arguments;
                if ($('#perso_cond_' + ord).val() === 'location_is') {
                    locations++;
                    self.fillGeoSubconditions(ind, arg);
                } else {
                    $('#sub_condition_' + ind).val(arg);
                }
                ord++;
            });

            if (locations === 0) {
                self.resizeMainContainer();
            }
            self.bindContentEdits();
        },
        // When the condition is "location is" we need to display the appropriate sub-conditions in the corresponding selects
        fillGeoSubconditions: function (ind, arg, sub) {
            var self = this;

            if (typeof (arg) !== 'object') {
                try {
                    arg = $.parseJSON(arg);
                } catch (e) {
                    arg = {};
                }
            }
            sub = !sub || typeof (sub) === 'undefined' ? '.geocountry.select' : sub;

            $.each(arg, function (placeid, value) {
                placeid = placeid.replace(/R/, '');
                placeid = placeid.replace(/A/, '');
                var place = $('#condition_' + ind + ' > ' + sub).find('option#' + placeid);

                if (place.length > 0) {
                    place.prop('selected', 'selected');
                    if (sub !== '.geocity.select') {
                        self.nestedGet++;
                        var geoplace = new BT_geoplace(arg, placeid, ind, self.nestedSelect[sub]);
                        geoplace.getChildren();
                    }
                    return false;
                }
            });
        },
        // Creates and appends every <select> of conditions
        fillGenericConditions: function (index, cond) {
            var ind = index.replace(/ID/, '');
            var htm = '<div class="perso_rule_condition_container" id="condition_' + ind + '">';
            htm += '<div class="perso_sharp_icon perso_rule_condition_del ' + this.tenant + '" title="' + this.msgDelCond + '" id="del_condition_' + ind + '">';
            htm += (this.tenant === 'etracker') ? '<i class="fa fa-minus minus-plus-icons"></i></div>' : '<span class="bt_icon"></span></div>';
            htm += this.getConditionSelect();
            htm += '<span class="perso_rule_tooltip"><i class="fa fa-info-circle"></i></span>';
            htm += '</div><div class="clear"></div>';
            $('#perso_rule_contition_items').append(htm);
            this.getOptionsByCondition();
            this.bindDeleteCondition();

            if (cond !== null) {
                var type = cond.type;
                var indication = parseInt(cond.indication) === 0 ? 'NOT_EQUALS' : 'EQUALS';

                $('#condition_' + ind + ' .conditions_select').val(type);
                $('.conditions_select').trigger('change');

                setTimeout(function () {
                    $('#condition_' + ind + ' .verbs_select').val(indication);
                }, 249);
            }
        },
        // returns the html with the options for the conditions select (device equals, OS equals...)
        getConditionSelect: function () {
            var self = this;
            var opts, clss;
            var ind = $('.conditions_select').length;
            var required = (ind > 0 && $('#perso_cond_0').hasClass('validate[required]')) ? '' : ' validate[required] ';
            var conditions = this.conditions.groups;

            var htm = '<select class="' + this.tenant + ' select perso_rule_input conditions_select ' + required + '" id="perso_cond_' + ind + '">';
            htm += '<option value="">' + this.optionSelect + '</option>';

            $.each(conditions, function (key, group) {
                htm += '<optgroup id="' + key + '" label="' + group.label + '">';
                if (typeof (group.elements) === 'object' && group.elements !== null) {
                    $.each(group.elements, function (k, opt) {
                        opts = self.conditions.groups[key].elements[k];
                        clss = (opts !== null && typeof (opts.isConfigured) !== 'undefined' && !opts.isConfigured) ? 'disabled' : '';
                        htm += '<option class="' + clss + '" value="' + k + '">' + opt.label + '</option>';
                    });
                }

                htm += '</optgroup>';
            });

            htm += '</select>';
            return htm;
        },
        // After selecting a condition, gets the corresponding options for it (could be a <select> or an <input>)
        getOptionsByCondition: function () {
            var self = this;

            $(document).off('change', 'select.conditions_select');
            $(document).on('change', 'select.conditions_select', function () {
                $('#perso_rule_form').validationEngine('hideAll');
                var htm = '';
                var cond = $(this).val();
                var $parent = $(this).parent('div');
                var optgroup = $('option[value="' + cond + '"]').closest('optgroup').attr('id');

                $parent.find('.conditions_select').removeClass('disabled');
                $parent.find('.perso_rule_tooltip').hide();
                $parent.find('select.verbs_select, ' +
                        ' .geocountry, .geoip, ' +
                        ' .condition_option_args, .clear').remove();

                var opts = null;
                if (cond !== '') {
                    opts = self.conditions.groups[optgroup].elements[cond];
                }
                $parent.find('.perso_rule_tooltip').show();

                if (typeof (opts) !== 'undefined' && opts !== null) {
                    $parent.find('.perso_rule_tooltip').attr('name', opts.tooltip);
                    var validate = opts.validate;
                    var inp = $parent.attr('id');
                    var clss = self.tenant + ' ';
                    clss += (validate !== null && validate !== undefined) ? 'validate[required,custom[' + validate + ']]' : 'validate[required]';

                    var wtype = opts.widgetType;
                    htm = self.getVerbSelect(opts);

                    switch (wtype) {
                        case null:
                            htm = '';
                            break;
                        case 'API':
                            htm += '<select class="geocountry select perso_rule_input opt_arguments validate[required]" id="sub-1-' + inp + '">' +
                                    '<option value="">' + self.countrySelect + '</option>' +
                                    self.setGeoCountries() + '</select>' + '<div class="clear"></div>' +
                                    '<select class="disabled geoip geostate select perso_rule_input opt_arguments" id="sub-2-' + inp + '" disabled="disabled">' +
                                    '<option value="">' + self.placeSelect[2] + '</option></select>' +
                                    '<select class="disabled geoip georegion select perso_rule_input opt_arguments" id="sub-3-' + inp + '" disabled="disabled">' +
                                    '<option value="">' + self.placeSelect[3] + '</option></select>' +
                                    '<select class="disabled geoip geocity select perso_rule_input opt_arguments" id="sub-4-' + inp + '" disabled="disabled">' +
                                    '<option value="">' + self.placeSelect[4] + '</option></select> ' +
                                    '<div class="clear"></div>';
                            $parent.append(htm);
                            self.resizeMainContainer();
                            break;
                        case 'TEXT':
                            htm += '<input type="text" class="subcond text perso_rule_input condition_option_args text_cond ' + clss + '" id="sub_' + inp + '">';
                            $parent.append(htm);
                            break;
                        case 'SELECT':
                        default:
                            htm += '<select class="' + self.tenant + ' select perso_rule_input condition_option_args pull_cond validate[required]" id="sub_' + inp + '">';
                            htm += '<option value="">' + self.optionSelect + '</option>';
                            $.each(opts.options, function (k, o) {
                                htm += '<option value="' + o.value + '">' + o.label + '</option>';
                            });
                            htm += '</select>';
                            $parent.append(htm);
                            break;
                    }

                    if (typeof (opts.defaultOperator) !== 'undefined' && opts.defaultOperator !== null) {
                        $parent.find('.verbs_select').val(opts.defaultOperator);
                    }
                }
                self.getOptionsByCondition();
                self.bindGeoChange();
            });

            this.addToolTips();
        },
        // creates the HTML snippet with the country options to fill the corresponding select and returns it
        setGeoCountries: function () {
            var htm = '';
            $.each(this.geoCountries, function (ind, country) {
                htm += '<option value="' + country.code + '" id="' + country.geonameId + '">' + ind + '</option>';
            });
            return htm;
        },
        // returns the html with the options for the verb select (EQUALS, NOT EQUALS)
        getVerbSelect: function (opts) {
            var self = this;
            var htm = '<select class="' + this.tenant + ' select perso_rule_input small verbs_select">';

            $.each(opts.operator, function (key, val) {
                if (typeof (self.conditions.operators) === 'object' && self.conditions.operators !== null) {
                    $.each(self.conditions.operators[val], function (k, v) {
                        htm += '<option value="' + v.value + '">' + v.label + '</option>';
                    });
                }
            });

            htm += '</select>';
            return htm;
        },
        // destroy the tooltip plugin and apply it again
        addToolTips: function () {
            if ($('.perso_rule_condition_container').length <= 0) {
                return false;
            }

            var $toolt;
            $('.perso_rule_condition_container').each(function () {
                $toolt = $(this).find('span.perso_rule_tooltip');
                $toolt.data('powertip', $toolt.attr('name'));
                $toolt.powerTip({placement: 'sw-alt'});
            });
        },
        // When adding or deleting conditions, the main container gets resized.
        resizeMainContainer: function () {
            var self = this;
            clearTimeout(this.resizeTout);

            this.resizeTout = setTimeout(function () {
                var sh = $('#perso_rule_conditions').outerHeight();
                var tenanth = (self.tenant === 'etracker') ? 335 : 371;

                overf = 'visible';
                if (sh > 70) {
                    tenanth += sh - 60;
                    var overf = sh >= 200 ? 'auto' : 'visible';
                }
                $('#perso_rule_conditions').css('overflow-x', overf);

                var my = ((tenanth + 100) / 2) * -1;

                $('#perso_rule_main_container').animate({
                    'height': tenanth + 'px'
                }, 300);
                $('#perso_rule_editor_wrapper').animate({
                    'margin-top': my + 'px'
                }, 300);
            }, 49);
        },
        // Shows the loading animation while <select> elements are being loaded, also, disables action buttons
        showLoadingWheel: function () {
            clearTimeout(this.loadingTout);
            $('#perso_rule_loading').fadeIn(0);
            $('#perso_rule_cancel').off('click');
            $('.perso_confirm_close').off('click');
            $('#perso_rule_editor_close').off('click').addClass('disabled');
            $('.perso_rule_btn_container input').attr('disabled', 'disabled').addClass('disabled');
        },
        // Hides the loading animation when everything is in place
        hideLoadingWheel: function () {
            var self = this;
            this.loadingTout = setTimeout(function () {
                self.bindCancelClick();
                $('#perso_rule_loading').fadeOut(0);
                $('#perso_rule_editor_close').removeClass('disabled');
                $('.perso_rule_btn_container input').removeAttr('disabled').removeClass('disabled');
            }, 99);
        },
        // When the name input has been changed, the corresponding list item is updated
        bindNameChange: function () {
            var self = this;
            $('#perso_rule_name').off('change');
            setTimeout(function () {
                $('#perso_rule_name').on('change', function () {
                    self.bindNameChange();
                    if ($(this).val().replace(/ /g, '').length > 0) {
                        $('#rule_' + self.ruleSelected).text($(this).val());
                    }
                });
            }, 99);
        },
        // whenever a select is changed or a field is filled, sets the changeMade parameter to true.
        bindContentEdits: function () {
            var self = this;
            $('.perso_rule_input').on('change', function (e) {
                self.changeMade = true;
                $('.perso_rule_input').off('change');
            });
        },
        //Add a new row with empty conditions when the user clicks on "add a condition"
        bindAddCondition: function () {
            var self = this;
            $('#perso_rule_add_condition').on('click', function () {
                self.validateInputData('add');
                self.changeMade = true;
                var count = $('.perso_rule_condition_container').length;
                self.fillGenericConditions('NEW' + count, null);
                self.resizeMainContainer();
                self.bindContentEdits();
            });
        },
        // Verifies whether a geo <select> changes
        bindGeoChange: function () {
            var self = this;

            var nestedIndex = {
                1: '.geocountry.select',
                2: '.geostate.select',
                3: '.georegion.select'
            };

            $('.geocountry.select, .geoip.select').off('change');

            $('.geocountry.select, .geoip.select').on('change', function () {

                self.nestedGet = self.nestedSet = 0;

                if ($('#perso_rule_editor_wrapper').is(':visible')) {
                    var geonameId = $(this).find('option:selected').attr('id');
                    var container = $(this).attr('id').split('-');
                    var current = parseInt(container[1]);
                    container = container[2];

                    var ind = container.split('_');

                    for (var i = current; i < 4; i++) {
                        var $elem = '#sub-' + (i + 1) + '-' + container;
                        var htm = '<option value="">' + self.placeSelect[i + 1] + '</option>';
                        $($elem).html(htm);
                        $($elem).addClass('disabled').prop('disabled', 'disabled');
                    }

                    if (current < 4) {
                        self.nestedGet++;
                        var geoplace = new BT_geoplace(false, geonameId, ind[1], self.nestedSelect[nestedIndex[current]]);
                        geoplace.getChildren();
                    }
                }
            });
        },
        // When the user clicks on the X placed at the left of every conditions, it gets deleted
        bindDeleteCondition: function () {
            var self = this;
            $('.perso_rule_condition_del').on('click', function () {
                var idcond = $(this).attr('id').replace(/del_condition_/, '');
                var container = $(this).attr('id').replace(/del_/, '');
                $('#' + container).find('select.conditions_select').validationEngine('hide');
                $('#' + container).remove();
                self.resizeMainContainer();

                if (!$('.conditions_select').first().hasClass('validate[required]')) {
                    $('.conditions_select').first().addClass('validate[required]');
                }

                $.ajax({
                    type: "POST",
                    url: self.baseurl + 'personalization/deleteSelectedCondition',
                    datatype: 'text',
                    data: {
                        idc: idcond
                    }
                }).done(function () {
                }).fail(function () {
                    console.log('error connecting with the server');
                });
                $('.perso_rule_condition_del').off('click');
                self.bindDeleteCondition();
            });
        },
        // When clicking on "cancel", or the close buttons (X), checks if there are changes made to confirm before closing the dialog
        bindCancelClick: function () {
            var self = this;
            $('#perso_rule_cancel').off('click');
            $('.perso_confirm_close').off('click');
            $('#perso_rule_editor_close').off('click');
            $('#perso_rule_cancel').on('click', function () {
                if (self.changeMade) {
                    self.cancelAndExit();
                } else {
                    self.hideLoadingWheel();
                    self.closeConditionsPopup();
                }
            });
            $('#perso_rule_editor_close').on('click', function () {
                if (self.changeMade) {
                    self.cancelAndExit();
                } else {
                    self.hideLoadingWheel();
                    self.closeConditionsPopup();
                }
            });
            $('.perso_confirm_close').on('click', function () {
                $('.perso_confirm_popup').fadeOut(0);
            });

        },
        // After clicking "Cancel", if there are unsaved changes, the user is prompted to either discard or stay
        cancelAndExit: function (ruleid) {
            var self = this;
            $('#perso_rule_noexit, #perso_rule_exit').off('click');
            $('#perso_rule_cancel_overlay, #perso_rule_cancel_wrapper').fadeIn(0);
            $('#perso_rule_form').validationEngine('hideAll');

            $('#perso_rule_exit').on('click', function () {
                $('.perso_confirm_popup').fadeOut(0);
                self.hideLoadingWheel();
                self.closeConditionsPopup();
            });
            $('#perso_rule_noexit').on('click', function () {
                $('.perso_confirm_popup').fadeOut(0);
            });
        },
        // when clicking "select rule and close", the current rule is saved in the DB.
        bindSelectRule: function () {
            var self = this;
            $('#perso_rule_save').on('click', function () {
                if (!self.validateInputData('close')) {
                    return false;
                }
                self.saveSelectedRule(true, false);
                if (typeof (Blacktri) !== 'undefined') {
                    BlackTri.history.store('select_rule', 'code');
                }
            });
        },
        // validates the conditions and parameter selected/entered to display an error message if no value has been enterd or else, return true
        validateInputData: function (action) {
            if (action === 'add' || action === 'close') {
                if ($("#perso_rule_form").validationEngine('validate')) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        },
        // Rules are saved when clicking on "select rule and close", on "add a rule" and on --rule name-- in the list
        saveSelectedRule: function (close, r_id) {
            if (this.ruleSelected) {
                if ($("#perso_rule_form").validationEngine('validate')) {
                    this.setArrayOfConditions(close, r_id);
                } else {
                    this.saveDontClose(r_id, 500);
                }
            } else {
                this.saveDontClose(r_id, 0);
            }
        },
        // r_id is the ID of the selected rule to be displayed after saving the current one or 'undefined' if the user clicks on "add rule"
        saveDontClose: function (r_id, tout) {
            var self = this;

            if (r_id && typeof (r_id) !== 'undefined') {
                setTimeout(function () {
                    self.fillRuleParameters(r_id);
                }, tout);
            }
        },
        //generates the array of conditions to be sent to the server via AJAX in the next step
        setArrayOfConditions: function (close, r_id) {
            var self = this;
            var rule = {};
            var conds = 0;
            rule.id = this.ruleSelected;
            rule.name = $('#perso_rule_name').val();
            rule.operation = $('#perso_rule_validif').val();
            rule.conditions = {};
            $('.perso_rule_condition_container').each(function () {
                if ($(this).find('select.conditions_select').val() !== '') {
                    var idc = $(this).attr('id').replace(/condition_/, '');
                    rule.conditions[idc] = {};
                    rule.conditions[idc]['indication'] = $(this).find('select.verbs_select').val();
                    rule.conditions[idc]['type'] = $(this).find('select.conditions_select').val();
                    rule.conditions[idc]['arg'] = self.setConditionArguments(rule.conditions[idc]['type'], this);
                    if ($(this).find('.condition_option_args').length > 0) {
                        if (rule.conditions[idc]['arg'].replace(/ /g, '').length > 0) {
                            conds++;
                        } else {
                            rule.conditions[idc]['type'] = null;
                        }
                    } else {
                        conds++;
                    }
                }
            });
            if (conds > 0) {
                this.ajaxRuleSave(close, r_id, rule);
            } else if (r_id) {
                this.fillRuleParameters(r_id);
            }
        },
        // If the type of condition is "location_is", we need to add all available parameters (country, state, city)
        setConditionArguments: function (type, $parent) {
            var $input_arg = $($parent).find('.condition_option_args');
            var args = $input_arg.val();

            if (type === 'location_is') {
                var arg = {};
                var dropdowns = {
                    0: 'geocountry',
                    1: 'geostate',
                    2: 'georegion',
                    3: 'geocity'
                };

                for (var i = 0; i < 4; i++) {
                    var ind = dropdowns[i];
                    var clss = 'select.opt_arguments.' + ind;

                    $($parent).find(clss).each(function () {
                        var code = $(this).hasClass('georegion') ? 'R' : '';
                        arg[ind] = {
                            'code': code + $(this).find('option:selected').attr('id'),
                            'name': $(this).val()
                        };
                    });

                    arg['lang'] = this.lang === 'german' ? 'de' : 'en';
                    args = JSON.stringify(arg);
                }
            }
            return args;
        },
        // sends the rule data to be saved in the server via AJAX
        ajaxRuleSave: function (close, r_id, rule) {
            var self = this;
            $.ajax({
                type: "POST",
                url: this.baseurl + 'personalization/saveCurrentRule',
                datatype: 'json',
                data: {
                    rule: JSON.stringify(rule)
                }
            }).done(function (res) {
                res = $.parseJSON(res);
                if (typeof (res) !== 'undefined' && res !== null) {
                    if (res.status !== true) {
                        console.log(res.message);
                        return false;
                    } else {
                        self.currentRules = res.rules;
                        self.updateSavedRule(res.ruleid, rule.name, rule.operation);
                        if (close && (location.href.indexOf('/editor') >= 0)) {
                            self.updateEditorVariants(rule, res);
                        } else if (close && location.href.indexOf('/lpc/lcd') >= 0) {
                            bt_edit_personalization.updateTestdetailsVariants(self.variantId, res.ruleid, rule.name, self.variantIndex);
                            self.closeConditionsPopup();
                            if (self.variantIndex) {
                                bt_edit_personalization.saveTestDetailsVariants();
                            }
                        } else if (close && (location.href.indexOf('/lpc/cs') >= 0)) {
                            ab_personalization.updateSplitTests(self.variantId, res.ruleid, rule.name);
                            self.closeConditionsPopup();
                        }
                    }
                }

                if (r_id) {
                    self.fillRuleParameters(r_id);
                }
            }).fail(function () {
                console.log('error connecting with the server');
            });
        },
        // updates the perso data in the variants data object in the editor mode
        updateEditorVariants: function (rule, res) {
            var tabid = (this.variantId !== false) ? this.variantId : $('#variant_tabs .selected').attr('id');
            BlackTri.modifyPersoRule(tabid, res.ruleid, rule.name);
            BlackTri.personalization.updateVariantRule(tabid, res.ruleid, rule.name, true);
            this.closeConditionsPopup();
        },
        // Updates the recently saved rule with the corresponding data
        updateSavedRule: function (ruleid, name, operation) {
            $('#rule_' + this.ruleSelected).removeClass('AND').removeClass('OR');
            $('#rule_' + this.ruleSelected).text($('#perso_rule_name').val()).attr('id', 'rule_' + ruleid).addClass(operation);
        },
        // if this method is called from the editor, it closes the container
        closeConditionsPopup: function () {
            $('#perso_rule_form').validationEngine('hideAll');
            $('#perso_rule_editor_overlay, #perso_rule_editor_wrapper').fadeOut(0);
            this.getCurrentRules();

            if (location.href.indexOf('/lpc/lcd') >= 0 && this.variantIndex && bt_edit_personalization.deletedRule) {
                location.reload();
            }
        },
        // Calls the main methods to start
        init: function () {
            this.getGeoCountries();
            this.bindAddCondition();
            this.bindCancelClick();
            this.bindSelectRule();
        }
    };

    BT_perso_rules.init();

    /**
     * @param {Object} arg - containing the current conditions for the given rule
     * @param {Int} idp - the current place ID (i.e. "DE" or 2863324)
     * @param {Int} ind - the rule container index (1 to N in order)
     * @param {Object} nested - the <select> childs and labels
     */
    var BT_geoplace = function (arg, idp, ind, nested) {
        this.baseurl = $('#baseurl').val();
        this.arg = arg;
        this.place = idp;
        this.nestedChild = nested.child;
        this.label = nested.label;
        this.ind = ind;
    };
    // Gets the corresponding PLACE children to fill the nested <select>s
    BT_geoplace.prototype.getChildren = function () {
        var self = this;
        BT_perso_rules.showLoadingWheel();

        $.ajax({
            type: "GET",
            url: this.baseurl + 'personalization/getGeoChildren',
            datatype: 'json',
            data: {
                'geonameId': self.place
            }
        }).done(function (res) {
            return self.setChildren(res);
        }).fail(function () {
            console.log('error connecting with the geonames server');
        });
    };
    // Fills the nested <selects> with the corresponding children, when all dropdowns has been filled, hides the "loading" animation
    BT_geoplace.prototype.setChildren = function (res) {
        var children = $.parseJSON(res);

        if (typeof (children) === 'object' && children !== null && typeof (children.geoChildren) !== 'undefined' && children.geoChildren !== null) {

            if (children.hasChildren === null) {
                this.nestedChild = '.geocity.select';
                this.label = BT_perso_rules.placeSelect[4];
            }

            var htm = '<option value="">' + this.label + '</option>';

            $.each(children.geoChildren, function (name, id) {
                htm += '<option value="' + name + '" id="' + id + '">' + name + '</option>';
            });

            var $dropdown = $('#condition_' + this.ind + ' > ' + this.nestedChild);
            $dropdown.empty().append(htm).removeClass('disabled').removeProp('disabled');
        }

        BT_perso_rules.nestedSet++;
        if (BT_perso_rules.nestedSet === BT_perso_rules.nestedGet) {
            BT_perso_rules.hideLoadingWheel();
            BT_perso_rules.bindGeoChange();
        }

        if (typeof (this.arg) === 'object') {
            BT_perso_rules.fillGeoSubconditions(this.ind, this.arg, this.nestedChild);
        }
    };
});