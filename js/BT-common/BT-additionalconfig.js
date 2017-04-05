var bt_additional_config = {
    // on page load and when the user selects a "visibility" option, we perform the corresponding operations
    checkCurrentVisibility: function () {
        var val = $('#bt_additional_config').find('.dom_action_select').val();
        var view = BTeditorVars.view;

        $("#frmVisualABStep3").validationEngine('hideAll');
        $('#frmVisualABStep3').validationEngine('detach');

        if (val !== 'not_used') {
            $('#bt_additional_config').find('.textbox').addClass('validate[required]');
        } else {
            $('#bt_additional_config').find('.textbox').removeClass('validate[required]');
        }

        $("#frmVisualABStep3").validationEngine({
            onValidationComplete: function (form, status) {
                if (status) {
                    if (view === 'edit') {
                        SaveVisualABTest('approach');
                    } else {
                        CreateVisualAB(4);
                    }
                }
            }
        });
    },
    // catches when the user selects another "visibility" option for the dom element.
    bindVisibilityChange: function () {
        var self = this;
        $('#bt_additional_config').find('.dom_action_select').on('change', function () {
            self.checkCurrentVisibility();
        });
    },
    // When clicking on the "additional settings" label, we display the corresponding container
    bindLabelClick: function () {
        $('#bt_additional_config').find('label.bt_additional_label').on('click', function () {
            if ($(this).hasClass('bt_additional_show')) {
                $(this).removeClass('bt_additional_show').addClass('bt_additional_hide');
                $(this).find('strong').html('▼ ');
                $('#bt_additional_config').find('.bt_additional_settings').fadeIn(99);
            } else {
                $(this).removeClass('bt_additional_hide').addClass('bt_additional_show');
                $(this).find('strong').html('► ');
                $('#bt_additional_config').find('.bt_additional_settings').fadeOut(0);
            }
        });

        var action = $('#bt_additional_config').find('.dom_action_select').val();
        if (BTeditorVars.view === 'edit' && action !== 'not_used') {
            $('#bt_additional_config').find('label.bt_additional_label').trigger('click');
        }
    },
    // depending on IP filter pulldown the input field is disabled
    bindIpFilterDisabling: function () {
        $('#ip_filter_action').unbind('change').bind('change', function(){
            $('#ip_filter_list').attr('disabled', $(this).val() == 'not_used');
        });
    },
    init: function () {
        this.bindLabelClick();
        this.checkCurrentVisibility();
        this.bindVisibilityChange();
        this.bindIpFilterDisabling();
    }
};

$(document).on('ready', function () {
    bt_additional_config.init();
});