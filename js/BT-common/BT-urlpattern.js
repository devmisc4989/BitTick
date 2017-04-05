/**
 * This script applies for:
 *   - lpc/cs  (creation of VAB and SMS tests -- wizard mode)
 *   - lpc/lcd  (LPC details - when clicking on "Edit project" -> "Project name and URL")
 * Handles the possibility to add multiple URL patterns to a project and select whether the changes 
 * should apply for the given URL or be ignored.*
 */
var bt_urlpattern_config = {
    patterns: {},
    // When a URL input is added/deleted we resize the fancybox container, hide the validation engine popups and set only one "required" URL
    commonTasks: function () {
        $.fancybox.resize();
        $('#frmVisualABStep4').validationEngine('hideAll');
        $('.url_pattern_element').find('.textbox').removeClass('validationError validate[required]').removeAttr('id');
        $('.url_pattern_element').first().find('.textbox').addClass('validate[required]').attr('id', 'url_pattern_textbox');
    },
    // In "edit" mode we need to store the original URL patterns to be able to restore them when clickin on "cancel" or "close"
    saveOriginalState: function () {
        var self = this;
        if (BTeditorVars.view === 'edit') {
            $('.url_pattern_element').each(function (index) {
                self.patterns[index] = $(this).clone();
            });
            this.bindAddUrl();
            this.bindDeleteUrl();
        }
    },
    // This function is called from landingpagecollectiondetails.php (line 583) to restore the original state of the URL patterns
    restoreOriginalState: function () {
        $('#url_control_patterns').empty();
        $.each(this.patterns, function (ind, pattern) {
            $('#url_control_patterns').append(pattern);
        });
        this.saveOriginalState();
    },
    // When clicking on the "delete" icon, removes the corresponding URL container from the form
    bindDeleteUrl: function () {
        var self = this;
        $('.url_pattern_remove .lp-delete').off('click');
        $('.url_pattern_remove .lp-delete').on('click', function () {
            $(this).closest('.url_pattern_element').remove();
            self.commonTasks();
            var remaining = $('#url_control_patterns').find('.url_pattern_element').length;
            if (remaining <= 1) {
                $('#url_control_patterns').find('.url_pattern_remove').fadeOut(0);
            }
        });
    },
    // When clicking on "Add Url" clones the first URL pattern container and append it to the form
    bindAddUrl: function () {
        var self = this;
        $('#wizard_add_url').off('click');
        $('#wizard_add_url').on('click', function (e) {
            e.stopPropagation();
            var $clone = $('.url_pattern_element').first().clone();
            $('#url_control_patterns').append($clone);
            self.commonTasks();
            $('#url_control_patterns').find('.url_pattern_remove').fadeIn(0, function () {
                self.bindDeleteUrl();
            });
        });
    },
    // when creating a new SPLIT test, we check the URL entered in the first step to update the URL pattern
    bindSplitUrlChange: function () {
        $('#controlpagename').off('blur');
        $('#controlpagename').on('blur', function () {
            $('.url_pattern_element').first().find('.textbox').val($(this).val());
        });
    },
    // In edit mode, the patterns are added in the VIEW file, so we need to hide/show the "delete" icon and perform other actions accordingly
    init: function () {
        if ($('.url_pattern_element').length > 1) {
            $('#url_control_patterns').find('.url_pattern_remove').fadeIn(0);
        } else {
            $('#url_control_patterns').find('.url_pattern_remove').fadeOut(0);
        }
        this.commonTasks();
        this.bindAddUrl();
        this.bindDeleteUrl();
        this.saveOriginalState();
        this.bindSplitUrlChange();
    }
};

$(document).on('ready', function () {
    bt_urlpattern_config.init();
});
