var bt_clickgoals_config = {
    isEditor: false,
    isVisualTest: false,
    isSplitTest: false,
    goalOptions: '',
    goalSelectorError: false,
    showHighlight: true,
    preselectTimeout: false,
    currentFormContainer: false,
    nameLabel: false,
    primaryLabel: false,
    secondaryLabel: false,
    archivedGoals: [],
    goalWithParams: ['TARGETPAGE', 'LINKURL', 'CUSTOMJS'],
    uniqueGoals: ['ENGAGEMENT', 'AFFILIATE'],
    uniqueGoalInProject: {
        'ENGAGEMENT': false,
        'AFFILIATE': false
    },
    // Depending on the testtype and if it has pages (MPT) it returns the groupID, the page index or the LPCID
    getGoalPageId: function () {
        if (!BTeditorVars.isMpt) {
            return '-1';
        }

        var pageid = typeof (BTVariantsData.pages) !== 'undefined' ? BTVariantsData.pages[BTVariantsData.activePage].id : false;
        if (!pageid || pageid === null) {
            pageid = BTVariantsData.activePage || '-1';
        }
        return pageid;
    },
    // When the page is loaded, it gets the available goals from the server to pre-fill the corresponding <select>
    getAvailableGoals: function () {
        var self = this;

        $.ajax({
            type: "GET",
            url: BTeditorVars.BaseSslUrl + "editor/getAvailableGoals",
            dataType: 'json',
            data: {
                'lpcid': BTeditorVars.CollectionId || false
            }
        }).done(function (res) {
            if (self.isEditor) {
                BTeditorVars.goalsData = [];
            }

            self.setSavedGoals(res, false, false);
            self.showHighlight = res.highlighning === 'enabled';
            self.nameLabel = res.nameLabel;
            self.primaryLabel = res.primaryLabel;
            self.secondaryLabel = res.secondaryLabel;
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // iterates over all "saved" goals to set the corresponding values in the labels and input fields into the goals form
    setSavedGoals: function (goals, newgoal, edited) {
        var self = this;
        var editMode = this.isEditor && BTeditorVars.view === 'edit';
        this.uniqueGoalInProject = {
            'ENGAGEMENT': false,
            'AFFILIATE': false
        };

        $.each(goals.saved, function (ind, goal) {
            var rg = /g+\_+[0-9]{4}/;
            var prefix = new RegExp(bt_clickgoals_vars.goalPrefix, "g");
            var gname = goal.name.replace(rg, '');
            gname = gname.replace(prefix, '');

            if (newgoal) {
                goal.goalid = 'G' + (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
            }

            if ($('.cgoal_main_container').find('.first_cgoal_row').length > 0) {
                $('.cgoal_main_container').find('.first_cgoal_row').clone().appendTo('.cgoal_main_container');
                var $last = $('.cgoal_row').not('.archived_row').last();

                $last.removeClass('first_cgoal_row');
                $last.find('.goals_name_label').fadeOut(0);
                $last.data('idgoal', goal.goalid);
                $last.find('.goals_type_label').html(goal.label);
                $last.find('input.goals_id').val(goal.goalid);
                $last.find('input.goals_type').val(goal.type);
                $last.find('input.goals_level').val(goal.level);
                $last.find('input.goals_param').val(goal.param);
                $last.find('input.goals_pageid').val(goal.pageid);
                $last.find('input.goals_deleteddate').val(goal.deleteddate);

                if (goal.level === 'PRIMARY') {
                    $last.find('.secondary.goals_level_label').fadeOut(0);
                    $last.find('.primary.goals_level_label').fadeIn(0);
                    $last.find('.goal_menu_primary').fadeOut(0);
                }

                if (goal.type === 'CLICK' || $.inArray(goal.type, self.goalWithParams) >= 0) {
                    $last.find('input.goals_name').val(gname);
                    $last.find('.goals_name_label').html(gname).fadeIn(0);
                }
            }

            if ($.inArray(goal.type, self.uniqueGoals) !== -1) {
                $last.find('.goal_menu_edit').fadeOut(0);
                if (goal.status !== 'ACTIVE') {
                    self.uniqueGoalInProject[goal.type] = false;
                    $('#goal_details_type').find('option[value=' + goal.type + ']').removeProp('disabled');
                } else {
                    self.uniqueGoalInProject[goal.type] = true;
                    $('#goal_details_type').find('option[value=' + goal.type + ']').prop('disabled', true);
                }
            }

            if (goal.status !== 'ACTIVE') {
                $last.addClass('archived_cgoal_row');
                $last.find('.goals_value').each(function () {
                    var name = $(this).attr('name');
                    $(this).attr('name', name.replace(/conversion/, 'archived'));
                });
                self.setArchivedGoals(goal, gname);
            }

            if ((editMode || edited) && goal.type === 'CLICK') {
                goal.name = bt_clickgoals_vars.goalPrefix + goal.name;
                self.goalOptions += self.addGoalsOption(goal, goal.param);
                self.addGoalData(goal, goal.param);
                $('#frmGoalDetails').find('select#goal_details_type').html(self.goalOptions);
            }
        });

        this.bindGoalsActionTrigger();

        if (BTeditorVars.view === 'edit') {
            this.verifyArchivedGoalsCount();
        }

        if (!newgoal) {
            this.setAvailableGoals(goals);
        }
    },
    // (see: setSavedGoals) is a goals is set to "archived", this function fills the values in the corresponding form
    setArchivedGoals: function (goal, gname) {
        if (goal.status !== 'ACTIVE') {
            if ($('.archived_goals_main_container').find('.first_cgoal_row').length > 0) {
                $('.archived_goals_main_container').find('.first_cgoal_row').clone().appendTo('.archived_goals_main_container');
                var $arch = $('.archived_row').last();

                $arch.removeClass('first_cgoal_row');
                $arch.data('idgoal', goal.goalid);
                $arch.find('.goals_type_label').html(goal.label);
                $arch.find('.goals_name_label').html(gname);
                $arch.find('.goals_deleteddate_label').html(goal.deleteddate);
                $arch.find('input.goals_id').val(goal.goalid);
                $arch.find('input.goals_type').val(goal.type);
                $arch.find('input.goals_level').val(goal.level);
                $arch.find('input.goals_name').val(gname);
                $arch.find('input.goals_param').val(goal.param);
            }

            if ($.inArray(goal.type, this.uniqueGoals) !== -1) {
                this.uniqueGoalInProject[goal.type] = false;
                $('#goal_details_type').find('option[value=' + goal.type + ']').removeProp('disabled');
            }
        }

        this.bindGoalsActionTrigger();
    },
    // Fills the select with all available goals (default goals and click goals saved in session)
    setAvailableGoals: function (goals) {
        var self = this;
        var clickgoals = 0;
        var defaultgoal = false;
        var wizardGoals = {
            'saved': []
        };
        this.goalOptions = '<option value="0" data-description="false">' + bt_clickgoals_vars.pleaseSelect + '</option>';

        $.each(goals.available, function (ind, goal) {
            var inputVal = goal.type;

            if (goal.type === 'CLICK') {
                inputVal = goal.param;
            } else if (goal.type === 'ENGAGEMENT') {
                defaultgoal = goal;
            }

            self.goalOptions += self.addGoalsOption(goal, inputVal);

            if (goal.type === 'CLICK' && !goal.saved && self.isEditor) {
                self.addGoalData(goal, inputVal);

                if (clickgoals < 1) {
                    goal.level = 'PRIMARY';
                }

                wizardGoals.saved.push(goal);
                clickgoals++;
            }

        });

        $('#frmGoalDetails').find('select#goal_details_type').html(this.goalOptions);

        if (BTeditorVars.view === 'wizard') {
            if (clickgoals < 1) {
                defaultgoal.level = 'PRIMARY';
                wizardGoals.saved.push(defaultgoal);
            }
            $('.cgoal_main_container').find('.cgoal_row').not('.first_cgoal_row').remove();
            self.setSavedGoals(wizardGoals, true, false);
        }

        if (self.isEditor && self.showHighlight && self.isVisualTest) {
            self.setElementHighlightning();
        }

        this.bindGoalDropdown();
    },
    // returns a new option fo fill the goals "select" in the goal_details popup
    addGoalsOption: function (goal, inputVal) {
        if (goal.type === 'CLICK') {
            inputVal = inputVal.replace(/\"/g, '&quot;');
        }

        var disabled = '';
        if ($.inArray(goal.type, this.uniqueGoals) !== -1 && this.uniqueGoalInProject[goal.type] === true) {
            disabled = ' disabled="true" ';
        }

        return '<option data-pageid="' + goal.pageid + '" ' +
                'data-description="' + goal.description + '" ' +
                'data-field_desc="' + goal.fieldDescription + '" ' +
                'class="' + goal.type + '" ' +
                'value="' + inputVal + '" ' +
                disabled + '>' +
                goal.name +
                '</option>';
    },
    // Adds a new entry to the goalsData object in BTeditorVars.
    addGoalData: function (goal, inputVal) {
        var prefix = new RegExp(bt_clickgoals_vars.goalPrefix, "g");
        var newGoalData = {
            arg1: inputVal,
            collection_goal_id: typeof (goal.goalid) !== 'undefined' ? goal.goalid : false,
            pageid: goal.pageid,
            name: goal.name.replace(prefix, ''),
            type: 'CLICK'
        };
        BTeditorVars.goalsData.push(newGoalData);
    },
    // When changing the value of the available goal select (goal details popup) we pre fill the corresponding options
    prefillGoalDetails: function ($item) {
        $('*').validationEngine('hideAll');
        var self = this;
        var $opt = $item.find('option').filter(':selected');
        var optDesc = $opt.data('description');
        var fieldDesc = $opt.data('field_desc');
        var trimRegex = /^\s+|\s+$/g;

        $('.goal_params_container').attr('class', 'goal_params_container').fadeOut(0);
        $('.bt_clickgoals_config').fadeOut(0);

        $('.conversion_goal_desc').find('label').empty().html(optDesc);
        $('#goal_type_label').html($opt.html());

        if ($opt.hasClass('CLICK')) {
            var name = $opt.text().split(':');
            var $conf = $('.bt_clickgoals_config');

            $('.pageid_clickgoal').val($opt.data('pageid'));
            $conf.find('.conversion_goal_name').val(name[1].replace(trimRegex, ""));
            $conf.find('.conversion_goal_param').val($opt.val());
            $conf.fadeIn(0);
        } else {
            if (fieldDesc && fieldDesc !== 'false') {
                $('.goal_params_container').fadeIn(0, function () {
                    $(this).find('.goal_name_label').html(self.nameLabel);
                    $(this).find('.goal_desc_label').html(fieldDesc);
                    $(this).addClass($opt.val());
                });
            }
            $('.cgoal_input').val('');
        }
    },
    // When creating or editing a goal, we reset the goal details popup options and prefill the corresponding data
    editGoalDetails: function ($item) {
        var self = this;
        $('.goal_params_container, .bt_clickgoals_config').fadeOut(0);
        $('#goal_details_type').val('0').trigger('change');
        $('#frmGoalDetails').find('.goal_param_item').not(':first').remove();
        $('#frmGoalDetails').find('input[type=text], input[type=hidden]').val('');
        $('#frmGoalDetails').find('.targetpage_remove').fadeOut(0);

        if ($item) {
            $('#goal_details_popup').find('.goal_create_only').fadeOut(0);
            $('#goal_details_popup').find('.goal_edit_only').fadeIn(0);

            var $parent = $item.closest('.cgoal_row');
            var param = $parent.find('.goals_param').val();
            var name = $parent.find('.goals_name').val().replace(/ /g, '');
            var type = $parent.find('.goals_type').val();

            $('#goal_details_id').val($parent.find('.goals_id').val());

            if (type === 'CLICK') {
                $('#goal_details_type').find('option').each(function () {
                    var prefix = bt_clickgoals_vars.goalPrefix.replace(/ /g, '');
                    var rgx = new RegExp(prefix, "i");
                    var optName = $(this).html().replace(/ /g, '').replace(rgx, '');
                    if ($(this).val() === param && optName === name) {
                        $(this).prop('selected', true);
                        $('#goal_details_type').trigger('change');
                        return false;
                    }
                });

                var $container = $('.bt_clickgoals_config');
                $container.find('.conversion_goal_param').val(param);
                $container.find('#goal_details_name').val($parent.find('.goals_name').val());
                $container.fadeIn(0);
            } else {
                $('#goal_details_type').val(type).trigger('change');

                if ($.inArray(type, this.goalWithParams) >= 0) {
                    var $container = $('.goal_params_container');
                    $container.addClass(type).fadeIn(0);
                    try {
                        param = $.parseJSON($parent.find('.goals_param').val());
                        $.each(param, function (ind, p) {
                            if (ind > 0) {
                                self.addNewTargetPageUrl();
                                setTimeout(function () {
                                    $container.find('.conversion_goal_param').last().val(p);
                                }, 99);
                            } else {
                                $container.find('.conversion_goal_param').last().val(p);
                            }
                        });
                    } catch (e) {
                        $container.find('.conversion_goal_param').val($parent.find('.goals_param').val());
                    }

                    $container.find('#goal_details_name').val($parent.find('.goals_name').val());
                    $container.fadeIn(0);
                } else {
                    $('.goal_params_container').fadeOut(0);
                }
            }
        } else {
            $('#goal_details_popup').find('.goal_edit_only').fadeOut(0);
            $('#goal_details_popup').find('.goal_create_only').fadeIn(0);
        }

        OpenPopup("#goal_details_popup");

        setTimeout(function () {
            $('.action_trigger').removeClass('action_over').find('div.action_menu').hide();
        }, 499);
    },
    // enables/disables the highglightning of element when they have a click goal assigned (see the editor element's menu)
    enableOrDisableHighlightning: function (status) {
        $('#BTMouseOverEditorMenu').hide();
        $('#menu_tabs .tab.selected').click();

        if (status === 'enabled') {
            this.setElementHighlightning();
            this.showHighlight = true;
        } else {
            $('.editor_element_clickgoal, .tag_clickgoal').fadeOut(0);
            this.showHighlight = false;
        }

        $.ajax({
            type: "GET",
            url: BTeditorVars.BaseSslUrl + "editor/enableOrDisableHighlightning",
            dataType: 'text',
            data: {
                'status': status
            }
        }).done(function (res) {
            console.log('Disabled Highlightning in sessions: ' + res);
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // (main-new-editor.js/ProxyMouseEvents) hides/displays some menu elements depending on the selector (click goal, highlight disabled, etc)
    checkClickedElementMenu: function (selector) {
        var self = this;
        $('#BTMouseOverEditorMenu').find('.clickgoal_action').addClass('hiddenMenu');
        $('.ctrl-buttons').find('.remove.BtEditorButton').fadeOut(0);

        var foundGoal = false;
        var pageid = this.getGoalPageId();
        pageid = parseInt(pageid.replace(/page_/, ''));

        $.each(BTeditorVars.goalsData, function (index, data) {
            var pid = parseInt(data.pageid.replace(/page_/, ''));
            var arg = self.getGoalParameters(data);

            if (data.type === bt_clickgoals_vars.goalType && pid === pageid && selector.replace(/ /g, '') === arg.replace(/ /g, '')) {
                foundGoal = true;
                var highLightMenu = self.showHighlight ? '#clickgoal_action_hide' : '#clickgoal_action_highlight';

                $('#BTMouseOverEditorMenu').find('#clickgoal_action_edit').removeClass('hiddenMenu');
                $('#BTMouseOverEditorMenu').find(highLightMenu).removeClass('hiddenMenu');
                $('.ctrl-buttons').find('.remove.BtEditorButton').fadeIn(0);
            }
        });

        if (!foundGoal) {
            $('#BTMouseOverEditorMenu').find('#clickgoal_action_create').removeClass('hiddenMenu');
        }
    },
    // Goes throught every click goal and validates whether to highlight or not the corresponding element
    setElementHighlightning: function () {
        if (!this.isEditor) {
            return false;
        }

        var self = this;
        $('.editor_proxy').find('.editor_element_clickgoal').remove();
        $('.editor_proxy').find('.tag_clickgoal').remove();

        if ($('.editor_proxy_scroll').height() <= 1 || $('.editor_element_outline').length <= 0) {
            setTimeout(function () {
                self.setElementHighlightning();
            }, 99);
        } else {
            var pageid = this.getGoalPageId();
            pageid = parseInt(pageid.replace(/page_/, ''));

            $.each(BTeditorVars.goalsData, function (index, data) {
                var pid = parseInt(data.pageid.replace(/page_/, ''));
                if (data.type === bt_clickgoals_vars.goalType && pid === pageid) {
                    self.doHighlight(data);
                }
            });
        }
    },
    // after getting all available goals, it styles the elements that has a click goal assigned
    doHighlight: function (data) {
        var tagname = '';
        var arg = this.getGoalParameters(data);
        arg = arg.replace(/\>/g, ' ');

        $.each(arg.split(' '), function (ind, selector) {
            tagname = selector;
        });

        BlackTri.com.sendCallBackMessage('btGetElementInfo', function (res) {
            var off = res.offset;
            var w = res.outerWidth;
            var h = res.outerHeight;

            var tw = $('.editor_proxy_scroll').width();
            var th = $('.editor_proxy_scroll').height();

            w = w > tw - 4 ? tw - 4 : w;
            h = h > th - 4 ? th - 4 : h;

            if (off.left < 3) {
                w -= 2 - off.left;
                off.left = 3;
            }

            if (off.top < 3) {
                h -= 2 - off.top;
                off.top = 3;
            }

            var tagIdentTop = off.top - 12 >= 10 ? off.top - 12 : off.top + 6;

            var $clickBorder = $('.editor_element_outline').clone().appendTo('.editor_proxy');
            var $clickTag = $('#tag_ident').clone().appendTo('.editor_proxy');

            $clickBorder.removeClass('editor_element_outline').addClass('editor_element_clickgoal');
            $clickBorder.css({
                left: off.left - 1,
                top: off.top - 1,
                width: w + 2,
                height: h + 2
            }).show();

            $clickTag.removeAttr('id').addClass('tag_clickgoal');
            $clickTag.data('tagname', tagname.toLowerCase());
            $clickTag.find('#tag_ident_tagname').removeAttr('id').addClass('tag_clickgoal_span').html(bt_clickgoals_vars.goalTagLabel);
            $clickTag.css({
                left: off.left - 4,
                top: tagIdentTop
            }).show();
        }, arg);
    },
    // when the form is submited we validate the name of the goal to avoid conflicts
    validateNewClickGoalInEditor: function () {
        var self = this;
        var samePage = false;
        var pageid = this.getGoalPageId();
        pageid = parseInt(pageid.replace(/page_/, ''));

        var goalid = $('.edit_clickgoal_content').find('.click_goals_id').val();
        var $nameField = $('.edit_clickgoal_content').find('.click_goals_name');
        var $selectorField = $('.edit_clickgoal_content').find('.click_goals_selector');

        $nameField.removeClass('bt_goal_name_error');
        $selectorField.removeClass('bt_goal_param_error');

        $.each(BTeditorVars.goalsData, function (index, data) {
            var arg = self.getGoalParameters(data);

            if (data.collection_goal_id !== goalid && data.name.replace(/ /g, '') === $nameField.val().replace(/ /g, '')) {
                $nameField.addClass('bt_goal_name_error');
            }

            if (data.type === bt_clickgoals_vars.goalType) {
                samePage = data.pageid === '-1' || BTVariantsData.activePage === data.pageid;
                if (data.collection_goal_id !== goalid && samePage && arg.replace(/ /g, '') === $selectorField.val().replace(/ /g, '')) {
                    $selectorField.addClass('bt_goal_param_error');
                }
            }
        });

        if ($("#create_clickgoal_form").validationEngine('validate')) {
            this.saveGoalToSession();
        }
    },
    // opens the editor's click goal popup
    openCreateOrEditGoalPopup: function (edit) {
        $('#BTMouseOverEditorMenu').hide();
        var self = this;
        var $popup = $('#create_clickgoal_popup');
        var selector = BlackTri.CurrentSelector;
        var pageid = this.getGoalPageId();

        pageid = parseInt(pageid.replace(/page_/, ''));

        if (edit) {
            $.each(BTeditorVars.goalsData, function (index, data) {
                var pid = parseInt(data.pageid.replace(/page_/, ''));
                var arg = self.getGoalParameters(data);

                if (data.type === bt_clickgoals_vars.goalType && pageid === pid && arg.replace(/ /g, '') === selector.replace(/ /g, '')) {
                    $('.edit_clickgoal_content').find('.click_goals_id').val(data.collection_goal_id);
                    $('.edit_clickgoal_content').find('.click_goals_name').val(data.name);
                    $('.edit_clickgoal_content').find('.click_goals_selector').val(arg);
                }

            });
        } else {
            $('.edit_clickgoal_content').find('.click_goals_name').val('');
        }

        $('.edit_clickgoal_content').find('.click_goals_selector').val(selector);
        $popup.show().draggable();
    },
    // saves a new click goal to the session (sending it to the server via AJAX)
    saveGoalToSession: function () {
        var self = this;
        this.hideAdditionalSettings();

        $.ajax({
            type: "POST",
            url: BTeditorVars.BaseSslUrl + "editor/postNewClickgoal",
            data: {
                'lpcid': BTeditorVars.CollectionId || false,
                'goalid': $('.edit_clickgoal_content').find('.click_goals_id').val(),
                'pageid': this.getGoalPageId(),
                'gName': $('#clickgoal_input').val(),
                'selector': BlackTri.CurrentSelector
            }
        }).done(function (res) {
            $('#clickgoal_input').val('');
            $('#menu_tabs .tab.selected').click();
            CloseEditorPopups();
            self.getAvailableGoals();
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // Hides the "advanced" div with the selector for the new/edited goal
    hideAdditionalSettings: function () {
        $('.edit_clickgoal_content').find('label.bt_additional_label').removeClass('bt_additional_hide').addClass('bt_additional_show');
        $('.edit_clickgoal_content').find('label.bt_additional_label').find('strong').html('► ');
        $('.edit_clickgoal_content').find('.bt_additional_settings').fadeOut(0);
    },
    // To be able to validate non visible fields we first make a "custom" validation and then attach the plugin
    validateGoalsForm: function () {
        var names = {};
        var selectors = {};
        var nameExists = false;
        var selectorExists = false;

        $('.conversion_goal_name').removeClass('bt_goal_name_error');
        $('.conversion_goal_param').removeClass('bt_goal_param_error');

        // Adds every goal "name" and selector (if CLICK) from the saved goals list to the name object to verify if they already exists
        $(this.currentFormContainer).find('.cgoal_row').not('.first_cgoal_row').each(function () {
            var i = $(this).find('input.goals_id').val();
            var t = $(this).find('input.goals_type').val();
            var n = $(this).find('input.goals_name').val().replace(/ /g, '');

            if (n !== '') {
                names[i] = n;
            }
            if (t === 'CLICK') {
                selectors[i] = {
                    pageid: $(this).find('input.goals_pageid').val(),
                    param: $(this).find('input.goals_param').val().replace(/ /g, '')
                };
            }
        });

        var $opt = $('#goal_details_type').find('option').filter(':selected');
        var $cont = $opt.hasClass('CLICK') ? $('.bt_clickgoals_config') : $('.goal_params_container');
        var curId = $('#goal_details_id').val();
        var curName = $cont.find('.conversion_goal_name').val();

        // If the form has a name field we verify if it already exists
        if (typeof (curName) !== 'undefined') {
            $.each(names, function (idg, name) {
                var prefix = bt_clickgoals_vars.goalPrefix.replace(/ /g, '');
                var rgx = new RegExp(prefix, "i");
                name = name.replace(/ /g, '').replace(rgx, '');

                if (curId !== idg && curName.replace(/ /g, '') === name) {
                    nameExists = true;
                    return false;
                }
            });
        }

        // If the goal type is "CLICK" we also verify if the goal selector exists already
        if ($opt.hasClass('CLICK')) {
            var curSelector = $('.bt_clickgoals_config').find('.conversion_goal_param').val().replace(/ /g, '');
            var curPageid = $('.bt_clickgoals_config').find('.pageid_clickgoal').val();

            $.each(selectors, function (idg, selector) {
                if (curId !== idg && curSelector === selector.param.replace(/ /g, '') && curPageid === selector.pageid) {
                    selectorExists = true;
                    return false;
                }
            });
        }

        // If the new goal name or selector exists, we add a class to the corresponding field so the "validation engine" fails
        if (nameExists || selectorExists) {
            if (nameExists) {
                $('.textbox.conversion_goal_name').filter(':visible').addClass('bt_goal_name_error');
            }

            if (selectorExists) {
                if ($('.bt_clickgoals_config').find('label.bt_additional_label').hasClass('bt_additional_show')) {
                    $('.bt_clickgoals_config').find('label.bt_additional_label').trigger('click');
                }
                $('.bt_clickgoals_config').find('.conversion_goal_param').addClass('bt_goal_param_error');
            }

        }

        // Applies the goal details form and if passed, adds the data to the list of goals DIV
        if ($('#frmGoalDetails').validationEngine('validate')) {
            var goalid = $('#goal_details_id').val().replace(/ /g, '');
            var $opt = $('#goal_details_type').find('option').filter(':selected');
            var $container = $opt.hasClass('CLICK') ? $('.bt_clickgoals_config') : $('.goal_params_container');
            var name = '';
            var param = 'NA';

            if ($container.filter(':visible').length > 0) {
                var $params = $container.filter(':visible').find('.conversion_goal_param');
                name = $container.filter(':visible').find('.conversion_goal_name').val();
                param = $params.val();
            }

            if ($opt.hasClass('TARGETPAGE') && $params.length > 1) {
                var p = [];
                $params.each(function () {
                    p.push($(this).val());
                });
                param = JSON.stringify(p);
            }

            // If we are editing a goal, we edit the corresponding entry in the "saved goals" list
            if (goalid && goalid !== '' && goalid !== 'false') {
                $('.cgoal_row').not('.first_cgoal_row').each(function () {
                    var idg = $(this).find('input.goals_id').val();

                    if (goalid === idg) {
                        $(this).find('input.goals_type').val($opt.attr('class'));
                        $(this).find('input.goals_name').val(name);
                        $(this).find('input.goals_param').val(param);
                        $(this).find('.goals_name_label').html(name);

                        if ($opt.hasClass('CLICK')) {
                            $opt.val(param).html(bt_clickgoals_vars.goalPrefix + name);
                        } else {
                            $(this).find('.goals_type_label').html($opt.html());
                        }
                    }
                });
            } else {
                var addOrRestore = 'ADD';

                // If the goal is of type "ENGAGEMENT" or "AFFILIATE", we TRY to re activate the goal ( if it already exists ar "archived" )
                if ($.inArray($opt.val(), this.uniqueGoals) !== -1) {
                    $('.archived_row').not('.first_cgoal_row').each(function () {
                        var type = $(this).find('input.goals_type').val();

                        if (type === $opt.val()) {
                            addOrRestore = 'RESTORE';
                            $(this).find('.goals_reactivate_link').trigger('click');
                            return false;
                        }
                    });
                }

                // in any other case we create another goal
                if (addOrRestore === 'ADD') {
                    var level = this.getNewGoalLevel();
                    var label = $opt.hasClass('CLICK') ? bt_clickgoals_vars.goalLabel : $opt.html();
                    var newGoal = {
                        'saved': {
                            0: {
                                'type': $opt.attr('class'),
                                'label': label,
                                'level': level,
                                'levelLabel': level === 'PRIMARY' ? this.primaryLabel : this.secondaryLabel,
                                'goalid': false,
                                'name': name,
                                'pageid': this.isSplitTest ? -1 : BTVariantsData.activePage,
                                'param': param,
                                'status': 'ACTIVE'
                            }
                        }
                    };
                    this.setSavedGoals(newGoal, true, true);
                }
            }

            OpenPopup(this.currentFormContainer);
        }
    },
    // adds validation to the goal details form
    setGoalsFormValidation: function () {
        var self = this;
        $("#frmGoalDetails").off('submit').on('submit', function () {
            self.validateGoalsForm();
        });
    },
    // After clicking on "remove goal" we send the request to the server to unset the goal from the session array
    removeGoalFromElement: function () {
        var self = this;
        var pageid = this.getGoalPageId();
        var selector = $('.edit_clickgoal_content').find('.click_goals_selector').val();

        $.each(BTeditorVars.goalsData, function (index, data) {
            try {
                if (data.pageid === pageid && data.arg1 === selector) {
                    BTeditorVars.goalsData[index] = {
                        'arg1': '',
                        'collection_goal_id': '',
                        'name': '',
                        'pageid': '',
                        'type': ''
                    };
                }
            } catch (e) {
                console.log('Not a Click Goal');
            }
        });

        $.ajax({
            type: "POST",
            url: BTeditorVars.BaseSslUrl + "editor/unsetClickGoal",
            data: {
                'lpcid': BTeditorVars.CollectionId || false,
                'pageid': pageid,
                'selector': selector
            }
        }).done(function (res) {
            $('#clickgoal_input').val('');
            $('#menu_tabs .tab.selected').click();
            self.getAvailableGoals();
            CloseEditorPopups();
        }).fail(function () {
            console.log('Error connecting with the server');
        });
    },
    // We verify the number of active goals to define the "level" (primary/secondary) for a new goal or a "re-activated" one
    getNewGoalLevel: function () {
        var level = 'SECONDARY';
        if ($('.cgoal_main_container').find('.cgoal_row').not('.first_cgoal_row, .archived_cgoal_row').length < 1) {
            level = 'PRIMARY';
        }
        return level;
    },
    // Function used by several others to get the goal parameters depending on the type 
    getGoalParameters: function (data) {
        var arg = '';
        try {
            var params = $.parseJSON(data.arg1);
            arg = params.selector;
        } catch (e) {
            arg = data.arg1;
        }
        return arg;
    },
    // When re-activating a goal, we verify that the goal list have the "PRIMARY" goal at the top
    sortGoalsByLevel: function () {
        $.fn.sortGoalList = (function () {
            return function (comparator) {
                return Array.prototype.sort.call(this, comparator).each(function (i) {
                    this.parentNode.appendChild(this);
                });
            };
        })();

        $('.cgoal_main_container').find('.cgoal_row').not('.first_cgoal_row, .archived_cgoal_row').sortGoalList(function (a, b) {
            var akey = $(a).find('input.goals_level').val();
            var bkey = $(b).find('input.goals_level').val();
            var ret = 0;
            if (akey !== bkey) {
                ret = akey === 'PRIMARY' ? -1 : 1;
            }
            return ret;
        });
    },
    // After archiving or re activating a goal, we verify the number of archived goals to display some options/DIVS
    verifyArchivedGoalsCount: function () {
        var count = $('.archived_goals_main_container').find('.archived_row').not('.first_cgoal_row').length;
        if (count < 1) {
            $('.links').find('.links_separator, .button_reactivategoal').fadeOut(0);
            if ($('#frmGoalReactivate').filter(':visible').length > 0) {
                OpenPopup(this.currentFormContainer);
            }
        } else {
            $('.links').find('.links_separator, .button_reactivategoal').fadeIn(0);
        }
    },
    // depending on the number of  target pages, displays or hides the "delete" icon
    verifyTargetPageCount: function () {
        $('.param_item_container').find('.targetpage_remove').fadeOut(0);
        var count = $('.param_item_container').find('.goal_param_item').length;
        if (count > 1) {
            $('.param_item_container').find('.targetpage_remove').fadeIn(0);
        }
        this.bindGoalsActionTrigger();
    },
    // When clicking on "add URL" if the goal is TARGETPAGE we add a new input field
    addNewTargetPageUrl: function () {
        var $newp = $('.goal_param_item').last().clone();
        var idps = $newp.find('.textbox').attr('id').split('_');
        var newid = (parseInt(idps[0]) + 1) + '_goal_param_value';
        $newp.find('.textbox').attr('id', newid).val('');
        $newp.appendTo('.param_item_container');
        this.verifyTargetPageCount();
    },
    // Everytime we add another goal row, we detect the hover action to display the menu
    bindGoalsActionTrigger: function () {
        var self = this;

        // On mouse over, displayes the menu for "make primary", "archive", "edit"
        $('.cgoal_row').find('.action_trigger').off('mouseover');
        $('.cgoal_row').find('.action_trigger').on('mouseover', function () {
            $(this).addClass('action_over');
            $(this).find('div.action_menu').show();
        }).on('mouseout', function () {
            $(this).removeClass('action_over');
            $(this).find('div.action_menu').hide();
        });

        // When clicking on "make primary" set the rest as secondary
        $('.goal_menu_primary').off('click').on('click', function () {
            var $row = $(this).closest('div.cgoal_row');
            var $allRows = $('div.cgoal_row').filter(':visible');

            $allRows.find('input.goals_level').val('SECONDARY');
            $allRows.find('.primary.goals_level_label').fadeOut(0, function () {
                $allRows.find('.secondary.goals_level_label,  .goal_menu_primary').fadeIn(0);
                $row.find('.action_trigger').removeClass('action_over').find('div.action_menu').hide();
                $row.find('.secondary.goals_level_label,  .goal_menu_primary').fadeOut(0);
                $row.find('.primary.goals_level_label').fadeIn(0);
                $row.find('input.goals_level').val('PRIMARY');
            });
        });

        // "archives" a goal by changing the container class and the input names, if it was primary, sets the 1st remainig goal as primary
        $('.goal_menu_archive').off('click').on('click', function () {
            var $parent = $(this).closest('.cgoal_row');
            var level = $parent.find('input.goals_level').val();

            $parent.addClass('archived_cgoal_row');
            $parent.find('.goals_value').each(function () {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/conversion/, 'archived'));
            });

            if (level === 'PRIMARY') {
                $parent.find('input.goals_level').val('SECONDARY');
                var $default = $('.cgoal_row').not('.first_cgoal_row, .archived_cgoal_row');
                $default.find('.goal_menu_primary').trigger('click');
            }

            var idg = $parent.data('idgoal');
            var gname = $parent.find('input.goals_name').val();
            var deleteddate = idg * 1 > 0 ? $parent.find('input.goals_deleteddate').val() : '';
            if (deleteddate.replace(/ /g, '') === '') {
                var today = new Date();
                var month = today.getMonth() + 1;
                month = month < 10 ? '0' + month : month;
                deleteddate = today.getFullYear() + '.' + month + '.' + today.getDate() +
                        ' ' + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
            }

            var goal = {
                'status': 'ARCHIVED',
                'label': $parent.find('.goals_type_label').html(),
                'deleteddate': deleteddate,
                'goalid': $parent.find('input.goals_id').val(),
                'type': $parent.find('input.goals_type').val(),
                'level': $parent.find('input.goals_level').val(),
                'selector': $parent.find('input.goals_param').val()
            };

            self.setArchivedGoals(goal, gname, idg);
            self.verifyArchivedGoalsCount();
        });

        // "reactivates" a goal by changing back the container class and the input field names
        $('.goals_reactivate_link').off('click').on('click', function () {
            var $parent = $(this).closest('.cgoal_row');
            var idg = $parent.data('idgoal');
            var level = self.getNewGoalLevel();
            var type = $parent.find('.goals_type').val();

            $('.cgoal_main_container').find('.cgoal_row').each(function () {
                var $cgoal = $(this);
                if ($cgoal.data('idgoal') === idg) {
                    $cgoal.removeClass('archived_cgoal_row');
                    $cgoal.find('.goals_value').each(function () {
                        var name = $(this).attr('name');
                        $(this).attr('name', name.replace(/archived/, 'conversion'));
                    });

                    $cgoal.find('input.goals_level').val(level);
                    $cgoal.find('.goals_level_label').fadeOut(0, function () {
                        $cgoal.find('.goals_level_label').filter('.' + level.toLowerCase()).fadeIn(0);
                    });

                    if ($.inArray(type, self.uniqueGoals) !== -1) {
                        self.uniqueGoalInProject[type] = true;
                        $('#goal_details_type').find('option[value=' + type + ']').prop('disabled', true);
                    }
                }
            });

            $parent.remove();
            self.bindGoalsActionTrigger();
            self.verifyArchivedGoalsCount();
            self.sortGoalsByLevel();
        });

        $('.goals_action_link.button-addurl').off('click').on('click', function () {
            self.addNewTargetPageUrl();
        });

        // Removes the corresponding "target page" input when clicking on the "delete" icon
        $('.targetpage_remove').find('.lp-delete').off('click').on('click', function () {
            $(this).closest('.goal_param_item').remove();
            self.verifyTargetPageCount();
        });

        $('.button_addgoal').off('click').on('click', function () {
            self.editGoalDetails(false);
        });

        $('.goal_menu_edit').off('click').on('click', function () {
            self.editGoalDetails($(this));
        });

        $('.button_reactivategoal').off('click').on('click', function () {
            OpenPopup('#goal_reactivate_popup');
        });

        $('#frmGoalDetails').find('.editor_back').off('click').on('click', function () {
            OpenPopup(self.currentFormContainer);
        });

        $('#frmGoalReactivate').find('.editor_back').off('click').on('click', function () {
            OpenPopup(self.currentFormContainer);
        });

        $(this.currentFormContainer).find('.editor_back').off('click').on('click', function () {
            $.fancybox.close();
        });

        $(this.currentFormContainer).find('form').off('submit').on('submit', function () {
            $('.first_cgoal_row').remove();
            if (self.isSplitTest) {
                if (self.currentFormContainer === '#ab2_4') {
                    CreateAB(5);
                } else {
                    SaveABTest('goals');
                }
            } else {
                if (BTeditorVars.view === 'wizard') {
                    CreateVisualAB(6);
                } else {
                    SaveVisualABTest('goals');
                }
            }
        });
    },
    //This is specifically for conversion goals form, it binds goals <select> change and span ".advanced" click
    bindGoalDropdown: function () {
        var self = this;
        $('.goal_dropdownlist').off('change.bt_clickgoals');
        $('.bt_clickgoals_config').find('label.bt_additional_label').off('click');

        $('.goal_dropdownlist').on('change.bt_clickgoals', function () {
            self.prefillGoalDetails($(this));
        });

        $('.bt_clickgoals_config').find('label.bt_additional_label').on('click', function () {
            if ($(this).hasClass('bt_additional_show')) {
                $(this).removeClass('bt_additional_show').addClass('bt_additional_hide');
                $(this).find('strong').html('▼ ');
                $(this).closest('.bt_clickgoals_config').find('.bt_additional_settings').fadeIn(99);
            } else {
                $(this).removeClass('bt_additional_hide').addClass('bt_additional_show');
                $(this).find('strong').html('► ');
                $(this).closest('.bt_clickgoals_config').find('.bt_additional_settings').fadeOut(0);
            }
        });
    },
    // binds click event on the click goal related buttons (close popup, save goal, etc...)
    bindButtonsClick: function () {
        var self = this;
        $('#clickgoal_action_create').on('click', function () {
            self.openCreateOrEditGoalPopup();
        });

        $('#clickgoal_action_edit').on('click', function () {
            self.openCreateOrEditGoalPopup(true);
        });

        $('#create_clickgoal_popup').find('.cancel.BtEditorButton').on('click', function () {
            self.hideAdditionalSettings();
            $('#menu_tabs .tab.selected').click();
            $('*').validationEngine('hideAll');
            CloseEditorPopups();
        });

        $('#create_clickgoal_popup').find('.remove.BtEditorButton').on('click', function () {
            $('*').validationEngine('hideAll');
            self.removeGoalFromElement();
        });

        $('#create_clickgoal_form').on('submit', function () {
            self.validateNewClickGoalInEditor(true);
        });

        $('#clickgoal_action_hide').on('click', function () {
            self.enableOrDisableHighlightning('disabled');
        });

        $('#clickgoal_action_highlight').on('click', function () {
            self.enableOrDisableHighlightning('enabled');
        });

        $('.edit_clickgoal_content').find('label.bt_additional_label').on('click', function () {
            if ($(this).hasClass('bt_additional_show')) {
                $(this).removeClass('bt_additional_show').addClass('bt_additional_hide');
                $(this).find('strong').html('▼ ');
                $('.edit_clickgoal_content').find('.bt_additional_settings').fadeIn(99);
            } else {
                $(this).removeClass('bt_additional_hide').addClass('bt_additional_show');
                $(this).find('strong').html('► ');
                $('.edit_clickgoal_content').find('.bt_additional_settings').fadeOut(0);
            }
        });

        this.bindGoalDropdown();
    },
    // There is no safe way to get the testtype in the front end
    setTestType: function () {
        var self = this;
        var path = window.location.pathname.split('/');
        this.currentFormContainer = $('#vab2_5').length > 0 ? '#vab2_5' : '#vab2_4';
        $.each(path, function (i, p) {
            if (p === 'editor') {
                self.isVisualTest = true;
                self.isEditor = true;
                return false;
            }
        });

        if (!self.isEditor && typeof (bt_clickgoals_vars) !== 'undefined') {
            if (typeof (bt_clickgoals_vars.testtypeSplit) && bt_clickgoals_vars.testtypeSplit) {
                this.isSplitTest = true;
                this.currentFormContainer = $('#ab2_4').length > 0 ? '#ab2_4' : '#vab2_4';
            } else {
                var ttype = typeof (window.testtype) !== 'undefined' ? window.testtype : 0;
                this.isVisualTest = ttype === bt_clickgoals_vars.testtypeVisual || ttype === bt_clickgoals_vars.testtypeMultipage;
            }
        }
    },
    init: function () {
        if (parseInt(BTeditorVars.isTT) === 1) {
            return false;
        }

        this.setTestType();
        this.getAvailableGoals();

        if (this.isVisualTest) {
            this.bindButtonsClick();
            this.setGoalsFormValidation();
        } else if (this.isSplitTest) {
            this.setGoalsFormValidation();
        }
    }
};

var validateUniqueNameAndSelector = function (field, rules, i, options) {
    if (field.hasClass('bt_goal_name_error')) {
        return options.allrules.uniqueClickGoalName.alertText;
    }
    if (field.hasClass('bt_goal_param_error')) {
        return options.allrules.uniqueClickGoalSelector.alertText;
    }
};

$(document).on('ready', function () {
    bt_clickgoals_config.init();
});