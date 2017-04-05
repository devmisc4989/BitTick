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
    getGroupsList();
});

// Gets the list of test and users id to show in the jtable
var getGroupsList = function() {
    var sh = screen.height;
    pageSize = Math.floor((5 / 900) * sh);

    $('#group-list').jtable({
        title: 'SMS Template Groups',
        paging: true, //Enable paging
        pageSize: pageSize,
        sorting: true, //Enable sorting
        defaultSorting: 'sort_order',
        deleteConfirmation: true,
        listmethod: smsV.baseurl + 'admin/listTemplateGroups',
        updatemethod: smsV.baseurl + 'admin/updateTemplateGroups',
        insertmethod: smsV.baseurl + 'admin/createTemplateGroups',
        deletemethod: smsV.baseurl + 'admin/deleteTemplateGroup',
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
            sms_template_group_id: {
                title: 'Group Id',
                key: true,
                create: false,
                edit: false,
                list: true,
                width: '10%'
            },
            thumbnail_url: {
                title: 'Thumbnail',
                width: '40%'
            },
            sort_order: {
                title: 'sort_order',
                width: '25%'
            },
            cnt: {
                title: 'templates',
                width: '25%',
                create: false,
                edit: false
            }
        }
    });

    // loads the jtable with the created tests, changes the text of the add button and the css for the same item
    $('#group-list').jtable('load');

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