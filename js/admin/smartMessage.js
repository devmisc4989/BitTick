var smsV = {
    baseurl: null,
    factor: 0,
    level: 0,
    isEdition: false,
    edit_lporiginal: false,
    edit_lpcid: null,
    edit_factors: null
};

$(document).ready(function() {
    smsV.baseurl = $('#base-url').val();
    getSmsList();
});

// Gets the list of test and users id to show in the jtable
var getSmsList = function() {
    var sh = screen.height;
    pageSize = Math.floor((5 / 900) * sh);

    $('#sms-list').jtable({
        title: 'SMS Templates',
        paging: true, //Enable paging
        pageSize: pageSize,
        sorting: true, //Enable sorting
        defaultSorting: 'sms_template_group_id, sort_order, name',
        deleteConfirmation: true,
        listmethod: smsV.baseurl + 'admin/listSmsTemplates',
        updatemethod: smsV.baseurl + 'admin/editSmsTemplate',
        insertmethod: smsV.baseurl + 'admin/createSmsTemplate',
        deletemethod: smsV.baseurl + 'admin/deleteSmsTemplate',
        actions: {
            listAction: 'listmethod',
            deleteAction: 'deletemethod',
            updateAction: 'updatemethod',
            createAction: 'insertmethod'
        },
        formCreated: function(event, data) {
            data.form.find('input[name=content_type]').parent('.jtable-input').css('display', 'none');
            data.form.find('input[name=content_type]').parent('.jtable-input').parent('.jtable-input-field-container').css({
                'border-bottom': '0',
                'margin-top': '10px'
            });
            data.form.find('input[name=content_type]').css('display', 'none');
            data.form.find('input[name=sort_order]').attr('type', 'number').attr('min', '0').css('width', '100px');
        },
        fields: {
            sms_template_id: {
                key: true,
                create: false,
                edit: false,
                list: false
            },
            //Nested table with the Smart Message per template
            SMS: {
                title: 'SMS',
                width: '5%',
                sorting: false,
                edit: false,
                create: false,
                display: function(templateData) {
                    var $img = $('<img class="img-display-sms" src="http://blacktri-dev.de/images/sms.png" title="Show SMS List" />');
                    $img.click(function() {
                        $('#sms-list').jtable('openChildTable',
                                $img.closest('tr'),
                                {
                                    paging: true,
                                    pageSize: 10,
                                    sorting: true,
                                    defaultSorting: 'clientid_hash ASC',
                                    title: 'List of SMS which template is "' + templateData.record.name + '"',
                                    listmethod: smsV.baseurl + 'admin/listSmsByTemplate?template=' + templateData.record.sms_template_id,
                                    actions: {
                                        listAction: 'listmethod'
                                    },
                                    fields: {
                                        clientid_hash: {
                                            title: 'Client Hash',
                                            width: '30%'
                                        },
                                        subid: {
                                            title: 'Sub Id',
                                            width: '10%'
                                        },
                                        lpc: {
                                            title: 'LPC Name',
                                            width: '25%'
                                        },
                                        lp: {
                                            title: 'Variant Name',
                                            width: '25%'
                                        }
                                    }
                                },
                        function(data) {
                            data.childTable.jtable('load');
                        });
                    });
                    return $img;
                }
            },
            thumbnail_url: {
                title: 'Thumbnail',
                width: '15%'
            },
            previewimage_url: {
                title: 'Preview Image',
                list: false
            },
            name: {
                title: 'Name',
                width: '24%'
            },
            xml_content: {
                title: 'Xml',
                type: 'textarea',
                list: false
            },
            description: {
                title: 'Description',
                type: 'textarea',
                list: false
            },
            sms_template_group_id: {
                title: 'Group',
                width: '5%',
                options: smsV.baseurl + 'admin/listSmsGroups'
            },
            message_type: {
                title: 'Message type',
                width: '20%',
                options: smsV.baseurl + 'admin/listSmsTypes'
            },
            content_type: {
                title: 'Content type',
                width: '20%',
                list: true
            },
            coupon: {
                title: '',
                type: "checkbox",
                values: {'false': 'Coupon', 'true': 'coupon'},
                list: false,
                create: true,
                edit: true
            },
            tip: {
                title: '',
                type: "checkbox",
                values: {'false': 'tip', 'true': 'tip'},
                list: false,
                create: true,
                edit: true
            },
            opt_in: {
                title: '',
                type: "checkbox",
                values: {'false': 'opt_in', 'true': 'opt_in'},
                list: false,
                create: true,
                edit: true
            },
            lead_gen: {
                title: '',
                type: "checkbox",
                values: {'false': 'lead_gen', 'true': 'lead_gen'},
                list: false,
                create: true,
                edit: true
            },
            sort_order: {
                title: 'sort_order',
                list: false
            },
            sms_count: {
                title: 'Count',
                width: '5%',
                create: false,
                edit: false
            }
        }
    });

    // loads the jtable with the created tests, changes the text of the add button and the css for the same item
    $('#sms-list').jtable('load');

    $('.jtable-toolbar-item-text')[0].innerHTML = 'New Template';

    $('.jtable-toolbar-item-text').css({
        'font-weight': 'bold',
        'font-size': '1.2em'
    });

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