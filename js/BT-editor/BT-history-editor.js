/**
 * Extends BlackTri object to provide editor "Undo" capability
 * @requires BTStyleProps object
 */


;
(function(bt) {

    var history_actions = [
        'rename_variant',
        'delete_variant'
    ];

    /* just here for reference */
    var action_types = ['var', 'elem', 'code']; /* var=variant, elem=element*/
    var $tabs;


    var history = {
        //last:null,
        enabled: false,
        debug: true,
        stack: {},
        init: function() {
            /************************************/
            /* expose as global for initial dev */
            if (history.debug) {
                h = history;
            }
            /**************************************/


        },
        store: function(action, type, data) {

            /* quick check we have method to restore*/
            if (type == 'var' && !history.dom_restore.hasOwnProperty(action)) {
                alert('[Error] No restore method for action: ' + action);
            }

            /*history.last={*/
            var last = {
                action: action,
                data: data,
                type: type,
                variantSnapshot: $.extend(true, {}, bt.variant),
                BTVariants: $.extend(true, {}, BTVariantsData)
            }

            if (!history.stack[bt.variantKey]) {
                history.stack[bt.variantKey] = []
            }



            history.stack[bt.variantKey].push(last);
            if (!history.enabled) {
                history.setUI();
            } else {
                history.setUndoText(action);
            }

        },
        /**
         * Changes the text of "undo" link in variant tab menu to reflect last action
         * @param string editAction
         */
        setUndoText: function(editAction) {
            var undoText;
            if (editAction === 'default') {
                undoText = BTeditorVars.undo_text.default;


            } else {
                undoText = BTeditorVars.undo_text.actions[editAction];
                if (!undoText) {
                    alert('[DEV ERROR] Undo history text\n\nAn action passed to "Blactri.history.setUndoText()" has no corresponding property in editUndoHistory_lang.php\n\nThe action passed as argument is: \n\n' + editAction);
                    return;
                }

                if (undoText.charAt(0) === '[') {
                    console.error('Text for undo link needs translations in editorUndoHistory_lang.php')
                }
            }
            $('#variant_tabs .selected .history_undo').text(undoText);


        },
        restore: function() {

            var stack = history.stack[bt.variantKey];

            if (stack.length) {
                var last = stack[stack.length - 1];
                function doRestore() {
                    //var last=stack.pop();
                    stack.pop();
                    var restoredVariant = $.extend(true, {}, last.variantSnapshot)

                    bt.variant = restoredVariant;
                    BTVariantsData.pages[BTVariantsData.activePage].variants[bt.variantKey] = restoredVariant;
                    var action_type = last.type
                    if (action_type != 'var') {
                        history.tabReload();
                    } else {
                        history.dom_restore[history.last.action]();
                    }

                    history.setUI();

                    setTimeout(function() {
                        if (last.action === 'select_rule') {
                            BlackTri.personalization.updateVariantRuleText();
                        }
                    }, 99);
                }

                if (last.action === 'delete_sms') {
                    var smsid = last.variantSnapshot.sms.id;
                    bt.sms.undelete(smsid, function(response) {
                        if (response.status) {
                            doRestore();
                            /* todo remove this once controller method finalized */
                            if (response.test_mode) {
                                alert('Server controller is in test mode for "undeleteSms');
                            }
                        } else {
                            alert("Error with server")
                        }
                    });
                } else {
                    doRestore();
                }

            }

        },
        setUI: function() {
            var stack = history.stack[bt.variantKey];
            history.enabled = stack && stack.length > 0;

            if (history.enabled) {
                var stackLength = history.stack[bt.variantKey].length;
                var lastAction = history.stack[bt.variantKey][ stackLength - 1 ].action;
                history.setUndoText(lastAction);
            } else {
                history.setUndoText('default');
            }

            $('#variant_tabs .selected .history_undo').toggleClass('disabled', !history.enabled);
        },
        tabReload: function() {
            $('#variant_tabs .selected').click();
        },
        dom_restore: {
            rename_variant: function() {
                var name = history.last.variantSnapshot.name;

                $('#variant_tabs .selected >a').text(name);
            },
            delete_variant: function() {
                var $tab = history.last.data.tab;
                /*var $prev=history.last.data.prev;
                 $prev.after($tab);*/
                $tab.show().click();
            }
        }


    }

    bt.history = history;




})(BlackTri);