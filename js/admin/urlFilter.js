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
    pageSize = Math.floor((15 / 900) * sh);

    $('#url-list').jtable({
        title: 'URL FILTER',
        paging: true, //Enable paging
        pageSize: pageSize,
        sorting: true, //Enable sorting
        defaultSorting: 'url ASC pattern ASC',
        deleteConfirmation: true,
        listmethod: smsV.baseurl + 'admin/listUrlPatterns',
        updatemethod: smsV.baseurl + 'admin/updateUrlPatterns',
        insertmethod: smsV.baseurl + 'admin/insertUrlPatterns',
        deletemethod: smsV.baseurl + 'admin/deleteUrlPatterns',
        actions: {
            listAction: 'listmethod',
            deleteAction: 'deletemethod',
            updateAction: 'updatemethod',
            createAction: 'insertmethod'
        },
        fields: {
            id: {
                key: true,
                create: false,
                edit: false,
                list: false
            },
            url: {
                title: 'URL',
                width: '45%'
            },
            pattern: {
                title: 'Pattern',
                width: '40%'
            }
        }
    });

    // loads the jtable with the created tests, changes the text of the add button and the css for the same item
    $('#url-list').jtable('load');

    $('.jtable-toolbar-item-text')[0].innerHTML = 'New Pattern';

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

var var_dump = function(arr, level) {
    var dumped_text = "";
    if (!level)
        level = 0;
    var level_padding = "";
    for (var j = 0; j < level + 1; j++)
        level_padding += " ";
    if (typeof (arr) === 'object') {
        for (var item in arr) {
            var value = arr[item];
            if (typeof (value) === 'object') {
                dumped_text += level_padding + "'" + item + "' ...\n";
                dumped_text += this.var_dump(value, level + 1);
            } else {
                dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
            }
        }
    } else {
        dumped_text = "===>" + arr + "<===(" + typeof (arr) + ")";
    }
    return dumped_text;
};