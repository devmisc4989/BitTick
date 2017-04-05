/**
 * handles the multipage test project creation/edition in the dashboard and in the editor
 */
var bt_mptest_config = {
    mainPageId: null,
    // sends a request via AJAX to the controller/function depending on the given parameters
    genericAjaxRequest: function (control, data, callback) {
        var controllers = {
            'setMpt': 'editor/setOrUnsetMpTest'
        };

        var url = BTeditorVars.BaseSslUrl + controllers[control];

        $.ajax({
            type: "POST",
            url: url,
            data: data,
            cache: false
        }).done(function () {
            if (callback) {
                callback();
            }
        }).fail(function () {
            console.log('Error connecting with the server');
            $.fancybox.close();
            window.location.reload();
        });
    },
    // When updating or deleting a page, we need to propagate the changes to the page select
    updatePageSelect: function () {
        var self = this;
        var mainPage = BTVariantsData.pages[this.mainPageId];
        $('#mpt_select').find('#mpt_page_main').val(mainPage.url).text(mainPage.name);
        $('#mpt_select').find('option').not('#mpt_page_main').addClass('NotFound');

        $.each(BTVariantsData.pages, function (idp, page) {
            if (idp !== self.mainPageId) {
                $('#mpt_select').find('option[value="' + page.url + '"]').text(page.name).removeClass('NotFound');
            }
        });

        $('#mpt_select').find('.NotFound').remove();
    },
    // after saving only the page names, we update the variants data accordingly
    updateWithoutReload: function (close) {
        var self = this;
        $('.mpt_page_container').each(function () {
            var url = $(this).find('.mpt_page_url').val();
            var name = $(this).find('.mpt_pages').val();

            $.each(BTVariantsData.pages, function (idp, page) {
                if (page.url === url) {
                    BTVariantsData.pages[idp].name = name;
                }
            });
        });

        self.updatePageSelect();

        if (close) {
            $.fancybox.close();
        }
    },
    // After clicking "save" in the list of pages we verify if we have to add a new URL or just save the current pages
    addUrlToNewPage: function () {
        var self = this;
        this.updateWithoutReload(false);

        $('#mpt_pageurl_form').find('#mpt_page_name').val($('.mpt_page_container').last().find('.mpt_pages').val());
        OpenPopup("#mpt_visual_name_url");

        $("#mpt_pageurl_form").validationEngine('detach');
        $("#mpt_pageurl_form").validationEngine('attach', {
            validationEventTrigger: 'submit',
            onValidationComplete: function (form, status) {
                if (status) {
                    self.addNewPage(form);
                }
            }
        });
    },
    // We create a new page and the corresponding entries in BTVariantsData
    addNewPage: function (form) {
        var pageCount = parseInt(BTVariantsData.pageCount) + 1;
        var name = form.find('#mpt_page_name').val();
        var url = form.find('#mpt_vab_page_url').val();

        BTVariantsData.pages['page_' + pageCount] = {
            id: null,
            name: name,
            url: url,
            variants: {}
        };

        $.each(BTVariantsData.pages[this.mainPageId].variants, function (idv, variant) {
            BTVariantsData.pages['page_' + pageCount].variants[idv] = {
                version: variant.version,
                name: variant.name,
                id: null,
                persorule: null,
                selectors: {},
                sms: {
                    id: null,
                    template: null
                },
                dom_modification_code: {
                    "[JS]": null,
                    "[CSS]": null
                }
            };
        });

        BTVariantsData.activePage = 'page_' + pageCount;
        BTVariantsData.pageCount = pageCount;
        BlackTri.inlineURLEditor.postSavedData(url);
    },
    // this is just to be able to be called from the "genericAjaxRequesr" function as a callback
    createMptCallback: function () {
        CreateVisualAB(1);
    },
    // Clones the original page container and append it to the corresponding container
    addMptPageFields: function () {
        var pageCount = parseInt(BTVariantsData.pageCount) + 1;
        $('.mpt_page_container').first().clone().appendTo('#mpt_pages_list');
        $('.mpt_wizard_pattern').first().clone().appendTo('#urls_for_mpt');
        var $newpage = $('.mpt_page_container').last();
        $newpage.attr('id', 'mpt_page_' + pageCount);
        $newpage.removeClass('mpt_page_first');
        $newpage.find('.mpt_page_id').val('');
        $newpage.find('.mpt_page_url').val('');
        $newpage.find('.mpt_delete_icon').fadeIn(0);
        $newpage.find('.mpt_pages').val(BTeditorVars.pagePrefix + ' ' + pageCount);
        this.bindDeletePage();
    },
    // After "confirm delete" we remove all references to the given page
    doDeletePage: function ($container, url) {
        $container.remove();

        var current = url === BTVariantsData.pages[BTVariantsData.activePage].url;

        $.each(BTVariantsData.pages, function (idp, page) {
            if (page.url === url) {
                delete BTVariantsData.pages[idp];
                return false;
            }
        });

        if (current) {
            BTVariantsData.activePage = this.mainPageId;
            BlackTri.inlineURLEditor.postSavedData(BTVariantsData.pages[this.mainPageId].url);
        } else {
            this.updateWithoutReload(false);
            OpenPopup('#mpt_manage_pages');
        }
    },
    // We verify if we have to display the "confirm delete" popup or just delete the page right away
    bindDeletePage: function () {
        var self = this;
        $('.mpt_delete_icon').off('click');
        $('.mpt_delete_icon').on('click', function (e) {
            e.stopPropagation();
            var $container = $(this).closest('.mpt_page_container');
            var url = $container.find('.mpt_page_url').val();
            var idp = $container.find('.mpt_page_id').val();

            if (idp === '' && url.replace(/ /g, '') === '') {
                $container.remove();
            } else {
                $('#mpt_confirm_page_delete').off('click');
                $('#mpt_confirm_page_delete').on('click', function () {
                    self.doDeletePage($container, url);
                });
                OpenPopup('#deleteConfirm');
            }
        });
    },
    // bind buttons click
    bindButtonsClick: function () {
        var self = this;

        // When the user selects any project type we verify if it is a MPT to save/remove the MPT session variable
        $('#ws1').find('.testBtn').on('click', function () {
            if ($(this).attr('id') === 'dash-create-mptest') {
                self.genericAjaxRequest('setMpt', {'isMpt': true}, self.createMptCallback);
            } else {
                self.genericAjaxRequest('setMpt', {'isMpt': false}, false);
            }
        });

        $('#mpt_select').on('change', function () {
            var url = $(this).val();
            $.each(BTVariantsData.pages, function (idp, page) {
                if (url === page.url) {
                    BTVariantsData.activePage = idp;
                    return false;
                }
            });
            BlackTri.inlineURLEditor.postSavedData(url);
        });

        $('.top-panel-selector').find('.mpt-edit').on('click', function () {
            self.setMptNamesAndUrls();
            OpenPopup('#mpt_manage_pages');
        });

        $('#mtp_new_page').on('click', function () {
            self.addMptPageFields();
            self.addUrlToNewPage();
        });

        $('#mpt_pages_form').on('submit', function () {
            self.updateWithoutReload(true);
        });

        $('#mpt_pageurl_form').find('.editor_back').on('click', function () {
            self.setMptNamesAndUrls();
            OpenPopup('#mpt_manage_pages');
        });

        $('#mpt_delete_cancel').on('click', function () {
            OpenPopup('#mpt_manage_pages');
        });

        $('#frmVisualABStep4').on('submit', function () {
            if ($('#urls_for_mpt').is(':visible')) {
                $('.mpt_wizard_pattern').each(function () {
                    $(this).find('.bt_mpt_group_pattern').remove();
                    var pageName = $(this).find('.label_input').val().replace(/\s/g, '');
                    var $field = $(this).find('.textbox');
                    var url = $field.val().replace(/ /g, '');

                    if (url.lastIndexOf('http', 0) !== 0) {
                        var prefix = url.indexOf('//') === 0 ? 'http:' : 'http://';
                        $field.val(prefix + url);
                    }

                    var htm = '<input type="hidden" class="bt_mpt_group_pattern" value="' + $field.val() + '" name="mpt_' + pageName + '" />'
                    $(this).append($(htm));
                });
            }
        });

        $('#mpt_cancel_pages').on('click', function () {
            $.fancybox.close();
        });
    },
    // When the editor is loaded we set the corresponding main test URL and the rest of the pages
    setMptNamesAndUrls: function () {
        var self = this;

        if (!BTeditorVars.isEditor) {
            this.bindButtonsClick();
            return false;
        } else if (typeof (BTVariantsData) === 'undefined') {
            setTimeout(function () {
                self.setMptNamesAndUrls();
            }, 99);
        } else {
            var currentUrl = $('#user_url_input').val().replace(/\s/g, '');
            $('#mpt_select').find('option').not('#mpt_page_main').remove();
            $('.mpt_page_container').not(':first').remove();
            $('.mpt_wizard_pattern').not(':first').remove();

            $.each(BTVariantsData.pages, function (idp, page) {
                if (page.url !== null && page.url.replace(/\s/g, '') === currentUrl) {
                    self.mainPageId = BTVariantsData.activePage = idp;
                    $('#mpt_select').find('#mpt_page_main').val(page.url).text(page.name);
                    $('.mpt_page_container.mpt_page_first').find('.mpt_page_url').val(page.url);
                    $('.mpt_page_container.mpt_page_first').find('.mpt_pages').val(page.name);
                    $('.mpt_wizard_pattern').first().find('.label_input').val(page.name);
                    $('.mpt_wizard_pattern').first().find('.textbox').val(page.url);
                } else {
                    self.addMptPageFields();
                    var selected = page.url === BTeditorVars.test_url ? ' selected="true" ' : '';
                    var htm = '<option value="' + page.url + '" ' + selected + '>' + page.name + '</option>';
                    $('#mpt_select').append(htm);
                    $('.mpt_page_container').last().find('.mpt_page_url').val(page.url);
                    $('.mpt_page_container').last().find('.mpt_pages').val(page.name);
                    $('.mpt_wizard_pattern').last().find('.label_input').val(page.name);
                    $('.mpt_wizard_pattern').last().find('.textbox').val(page.url);
                }
            });
            this.bindButtonsClick();
        }
    },
    init: function () {
        var isMpt = BTeditorVars.isMpt ? 'true' : 'false';
        if (typeof (savedVdata) === 'undefined' || !savedVdata) {
            this.setMptNamesAndUrls();
        }

        $('#frmVisualABStep4').append('<input type="hidden" value="' + isMpt + '" name="lpc_isMpt" />');

        if (BTeditorVars.isMpt) {
            $('.non_mpt').remove();
        } else {
            $('.only_mpt').remove();
        }
    }
};

// Validates the value of $element compared with the page's <field>, -->it cannot be duplicated
var mpt_validateFields = function ($element, field) {
    var valid = true;
    $.each(BTVariantsData.pages, function (idp, page) {
        if ($element.val().replace(/\s/g, '') === page[field].replace(/\s/g, '')) {
            valid = false;
            return false;
        }
    });
    return valid;
};

// Validates that the field "name" is not duplicated
var mpt_validatePageName = function () {
    if (!mpt_validateFields($('#mpt_page_name'), 'name')) {
        return $('#mpt_name_error').val();
    }
};

// Validates that the field "url" is not duplicated
var mpt_validatePageUrl = function () {
    if (!mpt_validateFields($('#mpt_vab_page_url'), 'url')) {
        return $('#mpt_url_error').val();
    }
};

$(document).on('ready', function () {
    bt_mptest_config.init();
});
