/**
 * handles the teaser test project creation/edition in the dashboard as well as in the 
 * collection details page.
 */
var bt_teasertest_config = {
    // Stores the original text of the "create project" layer (Which is actually the 3rd step of the SPLIT test wizard)
    original_text: {},
    // given an array with "title", "btn_save" and "btn_cancel" element, replace the corresponding texts in the HTML elements
    replaceLayerText: function (newText) {
        $('#ab2_3').find('h1').first().html(newText['title']);
        $('#ab2_3 .ctrl-buttons').find('.editor_back').html(newText.btn_cancel);
        $('#ab2_3 .ctrl-buttons').find('.button.ok').val(newText.btn_save);
    },
    // Restores the original text and the original button actions when selecting a different project type or when closing the popup
    restoreOriginalState: function () {
        this.replaceLayerText(this.original_text);

        $('#tt_interface_container, #tt_mainurl_container').fadeOut(0);

        $('#ab2_3 .ctrl-buttons').find('.editor_back').off('click').on('click', function () {
            CreateAB(2);
        });

        $("#frmABStep3").validationEngine('hideAll');
        $('#frmABStep3').validationEngine('detach');
        $("#frmABStep3").validationEngine({
            onValidationComplete: function (form, status) {
                if (status) {
                    CreateAB(4);
                }
            }
        });
    },
    // When the form is completed and it is validates, we save the new TT sending the data via AJAX to the server.
    saveNewTeaserTest: function () {
        $.ajax({
            type: "POST",
            url: BTeditorVars.BaseSslUrl + "tt/tt_create",
            data: $("#frmABStep3").serialize(),
            cache: false
        }).done(function (res) {
            $.fancybox.close();
            window.location.href = res;
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // Opens the corresponding popup to create a new TT after replacing some texts and buttons actions
    openCreateProjectLayer: function () {
        var self = this;
        self.replaceLayerText(bt_teasertest_translations['wizard']);

        $('#tt_interface_container, #tt_mainurl_container').fadeIn(0);
        $('.ocpc').find('.ocpc_headline.hide').fadeIn(0);

        $('#ab2_3 .ctrl-buttons').find('.editor_back').removeAttr('onclick').off('click').on('click', function () {
            CreateAB(0);
            return false;
        });

        $("#frmABStep3").validationEngine('hideAll');
        $('#frmABStep3').validationEngine('detach');
        this.validateMainUrl($('#frmABStep3'), 'create');

        OpenPopup("#ab2_3");
    },
    // adds "http://" to the mainurl, then validates the given form and calls the corresponding function based on the "action" value
    validateMainUrl: function ($form, action) {
        var self = this;
        $form.on('submit', function () {
            var url = $('#tt_mainurl').val().replace(/ /g, '');
            if (url.lastIndexOf('http', 0) !== 0) {
                var prefix = url.indexOf('//') === 0 ? 'http:' : 'http://';
                $('#tt_mainurl').val(prefix + url);
            }

            if ($(this).validationEngine('validate')) {
                if (action === 'edit') {
                    self.updateTeaserTest();
                } else if (action === 'create') {
                    self.saveNewTeaserTest();
                }
            } else {
                $('#tt_mainurl').off('focus');
                if ($('#tt_mainurl').val() === 'http://') {
                    $('#tt_mainurl').on('focus', function () {
                        $(this).val('');
                    });
                }
            }

            return false;
        });
    },
    // sends the serialized form to the server to update the current teasertest
    updateTeaserTest: function () {
        $.ajax({
            type: "POST",
            url: BTeditorVars.BaseSslUrl + "tt/tt_update",
            data: $("#frmVisualABStep3").serialize(),
            cache: false
        }).done(function (res) {
            $.fancybox.close();
            window.location.reload();
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // sends a request via AJAX to start/stop the entire project, the given group OR delete the given group
    genericAjaxRequest: function (control, groupid, status, doReturn) {
        var self = this;
        var controllers = {
            'toggle_collection': 'lpc/playpausecollection',
            'toggle_group': 'tt/tt_group_toggle',
            'restart_group': 'tt/tt_group_restart',
            'delete_group': 'tt/tt_group_delete',
            'group_details': 'tt/tt_overview_getGroupDetails'
        };

        var url = BTeditorVars.BaseSslUrl;
        url += controllers[control];

        $('div.action_trigger').trigger('mouseleave');

        $.ajax({
            type: "POST",
            url: url,
            data: {
                'collectionid': BTeditorVars.CollectionId,
                'groupid': groupid,
                action: status
            },
            cache: false
        }).done(function (res) {
            if (doReturn) {
                self.openEditVariantsLayer(res);
            } else {
                window.location.reload();
            }
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // sends the serialized tt_headlines form to create a new group with its variants
    createOrEditGroup: function () {
        $.ajax({
            type: "POST",
            url: BTeditorVars.BaseSslUrl + "tt/tt_group_create_or_update",
            data: $("#tt_headlines_form").serialize(),
            cache: false
        }).done(function (res) {
            $.fancybox.close();
            window.location.reload();
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // pre fills the form with the current values and changes the behavior of the validation engine for the form
    openEditProjectLayer: function () {
        var self = this;
        var startDate = bt_teasertest_details.startdate.split(' ');
        var endDate = bt_teasertest_details.enddate.split(' ');
        var config = $.parseJSON(bt_teasertest_details.config);

        $('.tt_interface_radio').prop('disabled', 'true');
        $('.tt_interface_label').addClass('disabled');
        $('#tt_interface_' + config.TT_INTERFACE_TYPE).prop('checked', 'true');

        $('#testname').val(bt_teasertest_details.name);
        $('#control_pattern').val(bt_teasertest_details.runpattern);
        $('#lpc_start_date').val(startDate[0]);
        $('#lpc_end_date').val(endDate[0]);
        $('#lpc_start_time').val(startDate[1]);
        $('#lpc_end_time').val(endDate[1]);
        $('#allocation').val(bt_teasertest_details.allocation / 100);

        $('.hide_for_tt').fadeOut(0);
        $('#tt_interface_container, #tt_mainurl_container').fadeIn(0);
        self.replaceLayerText(bt_teasertest_translations['edit']);

        $('.ocpc').find('.ocpc_headline.hide').fadeIn(0);
        $("#frmVisualABStep3").validationEngine('hideAll');
        $('#frmVisualABStep3').validationEngine('detach');
        this.validateMainUrl($('#frmVisualABStep3'), 'edit');

        setTimeout(function () {
            $('html').animate({scrollTop: 0}, 50, function () {
                OpenPopup("#vab2_3");
            });
        }, 50);
    },
    // After getting the group details, we pre-fill the original and variants text and display the corresponding popup
    openEditVariantsLayer: function (res) {
        var self = this;
        var group = $.parseJSON(res);
        var vCount = 0;

        this.resetHeadlinesForm();

        $.each(group, function (type, details) {
            if (type === 'original') {
                $('#tt_ov_groupid').val(details.groupid);
                $('#tt_control_headline').val(details.name);
            } else {
                $.each(details, function (ind, variant) {
                    if (vCount > 0) {
                        self.addNewVariant();
                    }
                    var $currentH = $('#tt_headline_alternatives').find('.tt_headline_variant').last();

                    if ($currentH.find('.tt_variant_id').length <= 0) {
                        $currentH.prepend('<input class="tt_variant_id" type="hidden" name="tt_variant_id[]">');
                    }

                    $currentH.find('.tt_variant_id').val(variant.id);
                    $currentH.find('.tt_variant_headlines').val(variant.name);
                    vCount++;
                });
            }
        });
        OpenPopup('#tt_manage_headlines');
    },
    // When opening "original and variants" or "create Headline A/B test" we need to reset and empty the fields of the form
    resetHeadlinesForm: function () {
        var formHeadlines = $('#tt_headline_alternatives').find('.tt_headline_variant').length;

        if (formHeadlines > 1) {
            for (var i = 1; i < formHeadlines; i++) {
                $('#tt_headline_alternatives').find('.tt_headline_variant').last().remove();
            }
        }

        $('#tt_ov_groupid, #tt_control_headline, .tt_variant_id, .tt_variant_headlines').val('');
        $('.tt_delete_icon').fadeOut(0);
    },
    // function to restart the corresponding group (if is TT) or the project itself if any other test type.
    lcdRestart: function () {
        var lpcid = BTeditorVars.CollectionId;
        var clientid = BTeditorVars.ClientId;
        if (BTeditorVars.isTT !== '1') {
            restartCollections(0, lpcid, clientid, 1);
            return false;
        }

        this.genericAjaxRequest('restart_group', BTeditorVars.GroupId, 1, false);
    },
    // We perform the corresponding action ("start", "stop" or "continue") for groups (TT) or for the the test itself
    lcdToggle: function ($e) {
        var lpcid = BTeditorVars.CollectionId;
        var action = $e.attr('id').replace(/toggle_/, '');
        var clientid = BTeditorVars.ClientId;

        if (BTeditorVars.isTT === '1') {
            this.genericAjaxRequest('toggle_group', BTeditorVars.GroupId, action, false);
            return false;
        }

        if (parseInt(action) === 1) {
            bt_conflict_layer.client_id = clientid;
            bt_conflict_layer.project_id = lpcid;
            bt_conflict_layer.reload_target = 'lpc/lcd/' + lpcid;
            return bt_conflict_layer.getProjectConflicts(true);
        }
        
        doToggleCollection(lpcid, action, clientid, 'lpc/lcd/' + lpcid);

    },
    // Clones the last variant and append it to the alternatives container, also displays the "delete" icon
    addNewVariant: function () {
        var clone = $('#tt_headline_alternatives').find('.tt_headline_variant').last().clone().appendTo('#tt_headline_alternatives');
        $('#tt_headline_alternatives').find('.tt_headline_variant').last().find('.tt_variant_id').remove();
        $('.tt_delete_icon').fadeIn(0);
        this.bindDeleteVariant();
    },
    // bind buttons click
    bindButtonsClick: function () {
        var self = this;
        // when closing the popup without saving, we resotre the original state
        $('#fancybox-close, a.editor_back').on('click', function () {
            self.restoreOriginalState();
        });
        // When the user goes back and click on another test type we restore the original state as well
        $('#ws1').find('.testBtn').not('#dash-create-teasertest').on('click', function () {
            self.restoreOriginalState();
        });
        // When the user wants to create a TT we replace the popup title and the buttons texts and actions.
        $('#dash-create-teasertest').on('click', function () {
            self.openCreateProjectLayer();
        });
    },
    // when clicking on a "delete variant" icon we get the parent element and remove it, if there is only one left, the icon is hidden
    bindDeleteVariant: function () {
        var self = this;
        $('.tt_delete_icon').off('click');
        $('.tt_delete_icon').on('click', function (e) {
            e.stopPropagation();
            $(this).closest('.tt_headline_variant').remove();
            if ($('.tt_headline_variant').length <= 1) {
                $('.tt_delete_icon').fadeOut(0);
            }
        });
    },
    // In lpc/lcd we catch clicks on menu items to differentiate normals tests from TT actions
    bindLCDMenu: function () {
        var self = this;

        if ($('.tt_headline_variant').length > 1) {
            $('.tt_delete_icon').fadeIn(0);
            this.bindDeleteVariant();
        }

        $('#lcd_restart').on('click', function () {
            OpenPopup("#restartCollection");
        });

        $('#confirm_restart').on('click', function () {
            self.lcdRestart();
        });

        $('a.lcd_toggle').on('click', function () {
            self.lcdToggle($(this));
        });

        $('a#lcd_original_variants').on('click', function () {
            if (BTeditorVars.isTT && BTeditorVars.view === 'edit') {
                OpenPopup('#tt_manage_headlines');
                $('#tt_ov_groupid').val(BTeditorVars.GroupId);
            }
        });

        $('a#tto_original_variants').on('click', function () {
            var groupid = $(this).closest('tr').attr('id');
            self.genericAjaxRequest('group_details', groupid, false, true);
        });
    },
    // catches when hovering over the menu or clicking on a menu element/button/link
    bindTtoMenu: function () {
        var self = this;
        $("#tt_headlines_form").validationEngine({
            onValidationComplete: function (form, status) {
                if (status) {
                    self.createOrEditGroup();
                }
            }
        });

        $('div.action_trigger').on('mouseenter', function () {
            $(this).addClass('action_over');
            var menu = $(this).parent().find('div.action_menu');
            menu.show();
        });
        $('div.action_trigger').on('mouseleave', function () {
            $(this).removeClass('action_over');
            var menu = $(this).parent().find('div.action_menu');
            menu.hide();
        });

        $('.tt_start_stop_test').on('click', function () {
            var ss = $(this).attr('id') === 'tt_stop' ? 0 : 1;
            self.genericAjaxRequest('toggle_collection', false, ss, false);
        });

        $('.tt_start_stop_story').on('click', function () {
            var groupid = $(this).closest('tr.table-list').attr('id');
            var ss = $(this).attr('id').replace(/start_stop_/, '');
            self.genericAjaxRequest('toggle_group', groupid, ss, false);
        });

        $('.tt_delete_story').on('click', function () {
            var groupid = $(this).closest('tr.table-list').attr('id');
            OpenPopup('#deleteConfirm');

            $('#tt_confirm_delete_test').off('click');
            $('#tt_confirm_delete_test').on('click', function () {
                self.genericAjaxRequest('delete_group', groupid, false, false);
            });
        });

        $('#tt_open_testpageurl').on('click', function () {
            self.openEditProjectLayer();
        });

        $('#tt_new_variant').on('click', function () {
            self.addNewVariant();
        });

        $('#tt_create_headline_link').on('click', function () {
            self.resetHeadlinesForm();
            OpenPopup('#tt_manage_headlines');
        });

        $('#tt_headlines_cancel, #tt_delete_cancel').on('click', function () {
            $.fancybox.close();
        });
    },
    init: function () {
        this.bindLCDMenu();
        this.bindTtoMenu();
        this.bindButtonsClick();
        this.original_text = {
            'title': $('#ab2_3').find('h1').first().html(),
            'btn_cancel': $('#ab2_3 .ctrl-buttons').find('.editor_back').html(),
            'btn_save': $('#ab2_3 .ctrl-buttons').find('.button.ok').val()
        };
    }
};

$(document).on('ready', function () {
    bt_teasertest_config.init();
});
