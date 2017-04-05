/**
 * Functions consolidated from multiple editor files
 */
var frameWindow;
/* define variables specific to views*/
if (BTeditorVars.view == 'test') {
    $(function() {
        frameWindow = $('#frame_editor')[0].contentWindow;
    });
} else {
    var ClientCodeExists = false;
}
/* start to consoldate form validations from editor view specific js files*/
/* todo finish form validation consolidation inot a FormsInit function in this file*/
var BTeditorForms = {
    /* property refers to wizard view*/
    'edit': [
        {id: '#frmVisualStep3', purpose: '', validCallback: function() {
                CreateVisualAB(4)
            }},
        {id: '#frmABStep1', purpose: 'A/B Test step1', validCallback: function() {
                SaveABTest('variants')
            }},
        {id: '#frmABStep2', purpose: 'A/B Test  step2', validCallback: function() {
                SaveABTest('goals')
            }},
        {id: '#frmVisualABStep3', purpose: 'Visual A/B Test  approach', validCallback: function() {
                SaveVisualABTest('approach')
            }}
    ]
};

/* not used in test view*/
function DeleteVariant(numval) {
    if (BTeditorVars.view == 'edit') {
        if (isNaN(numval)) {
            matchval = numval.match(/old/i);
            if (matchval != null) {
                len = numval.length;
                id = numval.substring(3, len);
                hidoldvalue = $("#variantpagehidold").val();
                hidoldvalue = hidoldvalue.replace(id + "_", "");
                $('input#variantpagehidold').attr('value', hidoldvalue);
                var deleteids = $('input#deleteid').attr('value');
                $('input#deleteid').attr('value', deleteids + id + "_");
            }
        }
        else {
            variantid = $("#variantpagehid").val();
            variantid = variantid.replace(numval + "_", "");
            $('input#variantpagehid').attr('value', variantid);

        }
        $('div#ABVariant' + numval).remove();

    }
    /* todo find out if this is used in wizard, or why so different from edit view version*/
    if (BTeditorVars.view == 'wizard') {
        variantid = $('input#variantpagehid').attr('value');
        $('input#variantpagehid').attr('value', variantid.replace(numval + '_', ''));

        variantid = $("#variantpagehid").val();
        var arry = new Array();
        arry = variantid.split("_");
        largest = arry.sort().reverse();
        num = new Number(largest[0]);

        $('div#ABVariant' + numval).html('');
    }

}


/* only used in edit view */
function SaveVisualABTest(step) {
    var localStep = step || '';
    if (localStep == '')
        return;
    var context = $("#vab2_3");
    Log('Save test step: ' + localStep);

    $('#savestep', context).val(localStep);
    if (step == 'variants') {
        var variantdata = GetVariantData();
        $('#variantdata', context).val($.toJSON(variantdata));
    }
    
    $('.click_goals_name, .click_goals_selector').trigger('keyup');

    $.ajax({
        type: "POST",
        //url: "<?=$basesslurl?>ue/ls/<?=$clientid?>/",
        url: BTeditorVars.BaseSslUrl + "ue/ls/" + BTeditorVars.ClientId + "/",
        data: $("#frmVisualABStep3, #frmVisualABStep4").serialize(),
        cache: false,
        success: function(data) {
            $.fancybox.close();
            //reload
            document.location = document.location;
        },
        error: function(data) {
        }
    });
}

function EditorCleanup()
{
    Log('Cleaning editor tabs and frames');

    var tabContext = $('.tabs_main');
    //make clean
    tabContext.find('.zone_tabs > div:not(.default,.new)').remove();
    $("#frame_editor").remove();

    if (BTeditorVars.view != 'edit') {
        var context = $("#vab2_2");
        //clean  keep state
        $("#user_url", context).html('&nbsp;')
    }
}



/* todo thoroughly test EditorLoaded() cases*/
function EditorLoaded() {
    var View = BTeditorVars.view;
    Log('Editor loaded event called');
    var tabContext = $('.tabs_main');
    var context = $("#vab2_2");
    if (View != 'test') {
        //check for messages
        ShowEditorPreloadMessage();
    }

    //init tabing functions
    InitTabs();
    //attach tab events
    AttachTabEvents();

    //create new test and load existing if exists


    //hide frame preloading
    $("#frame_editor").css('background-color', '#FFF');

    if (View == 'wizard') {
        //create new test and load existing if exists
        var clientUrl = $("#testurl", context).val();
        //check to see if this is new or edit test
        BlackTri.CreateNewTest(clientUrl);
    }


    //if not editable trigger new variant
    if (View == 'edit' && BTIsEditable) {
        var clientUrl = $("#vablpname", context).val();
        BlackTri.LoadExistingTest(clientUrl);
        BuildEditorTabs();

        //select first tab
        setTimeout(function() {
            var tabContext = $('.tabs_main');
            tabContext.find('.zone_tabs .tab:eq(1)').trigger('click');
        }, 150);

    } else {
        $('.zone_tabs .new', tabContext).trigger('click');
    }
}

/* only used in edit view*/
function BuildEditorTabs() {
    if (typeof (BTVariantsData) == 'undefined')
        return;
    var labels = BTVariantsData['labels'];
    var tabContext = $('.tabs_main');
    var context = $("#vab2_2");
    var This = tabContext.find('.zone_tabs .new');
    for (newTabID in labels) {
        Log('Adding new tab ' + newTabID);
        BTRenamingIndex++;
        var variantLabel = labels[newTabID];
        var newTab = This.clone(false, false).removeClass('new').attr('id', newTabID);
        //clone menu
        var menu = $('.menu_template .menu_container').clone();
        //select current tab
        newTab.find('a').html(variantLabel);
        newTab.append(menu);
        newTab.click(SelectVariantTab);
        //add menu
        This.before(newTab);

        //attach mouse over events
        newTab.find('.menu_container').hover(
                function() {
                    var parentW = $(this).parent().outerWidth(false);
                    $(this).removeClass('menu_hover').addClass('menu_hover').find('.menu').show().css({left: -parentW + 25, width: parentW > BTMenuWidth ? parentW + 20 : BTMenuWidth});
                    tabContext.find('.tabs_container').css('height', '150px');
                },
                function() {
                    $(this).removeClass('menu_hover').find('.menu').hide();
                    tabContext.find('.tabs_container').css('height', 'auto');
                })
                .click(function(evt) {
                    tabContext.find('.tabs_container').css('height', 'auto');
                    evt.preventDefault();
                    evt.stopPropagation();
                });
    }
    ResizeTabContainer();
    tabContext.find('.default').addClass('selected')
}





function PositionEditor()
{
    Log('PositionEditor');
    var context = $("#vab2_2");
    //if etracker
    /* this wasn't included in editor_wizard_test view*/
    if (BTeditorVars.view != 'test' && $('body').hasClass('etracker'))
    {
        context.css('padding', 5);
        var p = $("#frame_editor_ph", context).offset();
        var ue = context.find("#user_editor");
        var fb = $('#fancybox-wrap');
        $("#frame_editor").css('left', 35/*p.left*/).css('top', p.top - 8);

        $('.editor_overlayer, .editor_proxy, .editor_action').css('left', 35/*p.left*/).css('top', p.top - 8);
        $(".editor_popup_loading", fb).each(function(idx, o) {
            $(o).css("left", (ue.width() - $(o).width()) / 2).css('top', 250);
        });
    }
    else
    {
        var p = $("#frame_editor_ph", context).offset();
        var ue = context.find("#user_editor");
        var fb = $('#fancybox-wrap');
        $("#frame_editor").css('left', 52/*p.left*/).css('top', p.top);
        if (BTeditorVars.view != 'test') {
            $('.editor_overlayer, .editor_proxy, .editor_action').css('left', 52/*p.left*/).css('top', p.top);
        }
        $(".editor_popup_loading", fb).css("left", (ue.width() - $(".editor_popup_loading", fb).width()) / 2).css('top', 250);
    }
}

function InitTabs() {

    var context = $("#vab2_2");
    var tabContext = $('.tabs_main');
    var vablpname = $("#testurl", $("#vab2_3")).val();

    //attach resize event
    $(window).unbind('resize').bind('resize', function() {
        ResizeEditor("#vab2_2", 0);
    });

    Log('Init Tabs for', vablpname);

    //remove tabs
    tabContext.find('.zone_tabs > div:not(.default,.new)').remove();

    //check for arrows
    CheckTabsScrollArrows();
    /* view dependent code*/
    if (BTeditorVars.view == 'wizard') {

        Log("Getting test tracking codes");
        //get return for tracking code and code
        $.getJSON(BTeditorVars.BaseSslUrl + "ue/nc/?url=" + vablpname, function(data) {
            Log("Tracking code data received: ", data);
            //save return data to html
            if (data)
            {
                var lastStepContext = $("#vab2_3");
                $("#trackingcode_success", lastStepContext).val(data.lpctrackingcode_success);
                $("#trackingcode_control", lastStepContext).val(data.lpctrackingcode_control);
                $("#trackingcode_ocpc", lastStepContext).val(data.lpctrackingcode_ocpc);


                $("#lpccode", lastStepContext).val(data.lpccode);

                ClientCodeExists = data.client_code_exists == 'true';
            }
        })
    }

    if (BTeditorVars.view == 'edit') {
        //get tracking codes
        InitTrackingCodes();
    }


    //reset current tab
    BTCurrentTab = 0;
    //reset renaming index
    BTRenamingIndex = 0;
}


function InitTrackingCodes() {
    Log("Getting test tracking codes");
    if (BTIsEditable) {
        if (BTTrackingCodeData) {
            var lastStepContext = $("#vab2_3");
            $("#trackingcode_success", lastStepContext).val(BTTrackingCodeData.lpctrackingcode_success);
            $("#trackingcode_control", lastStepContext).val(BTTrackingCodeData.lpctrackingcode_control);
            $("#testname", lastStepContext).val(BTTrackingCodeData.testname);
            $("#trackingcode_ocpc", lastStepContext).val(BTTrackingCodeData.lpctrackingcode_ocpc);

            $("#lpccode", lastStepContext).val(BTTrackingCodeData.lpccode);

            ClientCodeExists = BTTrackingCodeData.client_code_exists == 'true';

            //$("input[name='testgoal'][value='"+BTTrackingCodeData.testgoal+"']", lastStepContext).trigger("click");
            Log('Tracking code loaded');
        }
    }
}


function AttachTabEvents() {
    Log('Attach tab events');
    var context = $("#vab2_2");
    var tabContext = $('.tabs_main');
    var tc = tabContext.find(".tabs_container");
    var zt = tc.find('.zone_tabs');

    $('.zone_tabs .new', tabContext).unbind('click', AddNewVariantTab).click(AddNewVariantTab);
    $('.zone_tabs .default', tabContext).unbind('click', AddNewVariantTab).click(SelectVariantTab);

    //set scrollers clicks
    var clickInitiated = false;
    $('.tabs_main .left').unbind('click').click(function() {
        if ($(this).hasClass('disabled'))
            return;
        if (clickInitiated)
            return;
        clickInitiated = true;
        tc.animate({scrollLeft: "-=" + scrollAmount}, 250, function() {
            CheckTabsScrollArrows();
            clickInitiated = false;
        });
    });
    $('.tabs_main .right').unbind('click').click(function() {
        if ($(this).hasClass('disabled'))
            return;
        if (clickInitiated)
            return;
        clickInitiated = true;
        tc.animate({scrollLeft: "+=" + scrollAmount}, 250, function() {
            CheckTabsScrollArrows();
            clickInitiated = false;
        });
    });
}


function AttachTabEvents() {
    Log('Attach tab events');
    var context = $("#vab2_2");
    var tabContext = $('.tabs_main');
    var tc = tabContext.find(".tabs_container");
    var zt = tc.find('.zone_tabs');

    $('.zone_tabs .new', tabContext).unbind('click', AddNewVariantTab).click(AddNewVariantTab);
    $('.zone_tabs .default', tabContext).unbind('click', AddNewVariantTab).click(SelectVariantTab);

    //set scrollers clicks
    var clickInitiated = false;
    $('.tabs_main .left').unbind('click').click(function() {
        if ($(this).hasClass('disabled'))
            return;
        if (clickInitiated)
            return;
        clickInitiated = true;
        tc.animate({scrollLeft: "-=" + scrollAmount}, 250, function() {
            CheckTabsScrollArrows();
            clickInitiated = false;
        });
    });
    $('.tabs_main .right').unbind('click').click(function() {
        if ($(this).hasClass('disabled'))
            return;
        if (clickInitiated)
            return;
        clickInitiated = true;
        tc.animate({scrollLeft: "+=" + scrollAmount}, 250, function() {
            CheckTabsScrollArrows();
            clickInitiated = false;
        });
    });
}

function AddNewVariantTab() {
    Log('Add new tab');
    var context = $("#vab2_2");
    var tabContext = $('.tabs_main');
    BTRenamingIndex++;
    var currentVariants = tabContext.find('.zone_tabs > div').size() - 1;

    //this is the temporary unique name assigned to identify tab
    var tabId = 'variant_' + BTRenamingIndex;
    var newTab = $(this).clone(false, false).removeClass('new').attr('id', tabId);
    if (BTeditorVars.view != 'test') {
        var variantLabel = BlackTri.GetNewLabel(NewVariant, BTRenamingIndex);
    } else {
        var variantLabel = NewVariant + ' ' + BTRenamingIndex;
    }


    var menu = $('.menu_template .menu_container').clone();
    BTCurrentTab = currentVariants;
    Log("Add new variant tab " + tabId);
    if (BTeditorVars.view != 'test') {
        BlackTri.AddNewTab(tabId, variantLabel);
    } else {
        frameWindow.BlackTri.AddNewTab(tabId, variantLabel);
    }


    //deselect last tab
    tabContext.find('.zone_tabs > div').removeClass('selected');
    //select current tab
    newTab.find('a').html(variantLabel);
    newTab.addClass('selected').append(menu);
    newTab.click(SelectVariantTab);
    //add menu
    $(this).before(newTab);

    ResizeTabContainer();

    newTab.trigger('click');

    //attach mouse over events
    newTab.find('.menu_container').hover(
            function(evt) {
                var parentW = $(this).parent().outerWidth(false);
                $(this).removeClass('menu_hover').addClass('menu_hover').find('.menu').show().css({left: -parentW + 20, width: parentW > BTMenuWidth ? parentW + 15 : BTMenuWidth});
                tabContext.find('.tabs_container').css('height', '150px');
                if (BTeditorVars.view != 'test') {
                    $(this).parent().removeClass('hover_fix').addClass('hover_fix');

                    evt.prevendDefault();
                    evt.stopPropagation();
                }

            },
            function(evt) {
                $(this).removeClass('menu_hover').find('.menu').hide();

                tabContext.find('.tabs_container').css('height', 'auto');
                $(this).parent().removeClass('hover_fix');
                if (BTeditorVars.view != 'test') {
                    evt.prevendDefault();
                    evt.stopPropagation();
                }
            })
            .click(function(evt) {
                tabContext.find('.tabs_container').css('height', 'auto');
                if (BTeditorVars.view != 'test') {
                    evt.preventDefault();
                    evt.stopPropagation();
                }
            });
    DisablePopupNotice = true;
}



function SelectVariantTab() {
    var tabContext = $('.tabs_main');
    var idx = $('.zone_tabs > div').index(this);
    var tabId = $(this).attr('id');
    BTCurrentTab = idx;
    Log('Current tab id: ' + tabId);
    //deselect last tab
    tabContext.find('.zone_tabs div').removeClass('selected');
    //select clicked tab
    $(this).addClass('selected');

    Log('Select tab ' + tabId);

    if (BTeditorVars.view == 'test') {
        frameWindow.BlackTri.SelectTab(tabId);
    } else {
        BlackTri.SelectTab(tabId);
    }

}

function RenameVisualABVariant(o) {
    var tab = $(o).parent().parent().parent();
    var idx = $('.zone_tabs > div').index(tab);
    Log('Rename tab ' + idx);
    if (idx >= 1)
    {
        var name = $('.zone_tabs > div').eq(idx).find('>a').html();
        $(".editor_rename_variant #variant_name").val(name).attr('tabindex', idx);
        $(".editor_overlayer").show();
        $(".editor_rename_variant").show();
    }
}
function SaveVisualABVariantName() {
    var name = $(".editor_rename_variant #variant_name").val();
    var idx = parseInt($(".editor_rename_variant #variant_name").val(name).attr('tabindex'));
    var tabId = $('.zone_tabs > div').eq(idx).attr('id');
    if (idx >= 1)
    {
        //save name to array
        // var frameWindow = $('#frame_editor')[0].contentWindow;

        if (BTeditorVars.view == 'test') {
            frameWindow.BlackTri.RenameTab(tabId, name);
        } else {
            BlackTri.RenameTab(tabId, name);
        }


        Log('Renaming tab id ', tabId, 'to', name);
        $('.zone_tabs > div').eq(idx).find('>a').html(name);
        ResizeTabContainer();
        CloseRenameVisualABPopup();
    }
}

/* todo switch out onclick for more generic CloseEditorPopups() and deprecate this function*/
function CloseRenameVisualABPopup() {
    /* $(".editor_overlayer").hide();
     $(".editor_rename_variant").hide();*/

    /* simplified to generic popup close*/
    CloseEditorPopups();
}
function OpenDeleteVariantPopup(o) {
    $(".editor_overlayer").show();
    /* in case loading popup still showing*/
    $('.editor_popup_loading').hide();

    $('#confirm_delete_variant_popup').show();
    var $tab = $(o).closest('.tab').addClass('confirm_delete');



}
/*function RemoveVisualABVariant(o){*/
function RemoveVisualABVariant() {
    /*var context = $("#vab2_2");
     var tab = $(o).parent().parent().parent();*/

    var tab = $('.zone_tabs').find('.confirm_delete');

    var tabId = tab.attr('id');
    var idx = $('.zone_tabs > div').index(tab);
    Log('Remove tab ' + tabId);

    //var frameWindow = $('#frame_editor')[0].contentWindow;

    if (BTeditorVars.view == 'test') {
        frameWindow.BlackTri.RemoveTab(tabId);
    } else {
        BlackTri.RemoveTab(tabId);
    }
    CloseEditorPopups();
    if (idx >= 0)
    {
        //remove from array
        // $('.zone_tabs > div').eq(idx).fadeOut(250, function(){
        tab.fadeOut(250, function() {
            $(this).remove();
            /* size() is deprecated use length instead*/
            //var totalTabs = $('.zone_tabs > div').size();
            var totalTabs = $('.zone_tabs > div').length;
            //select next tab or last
            if (idx >= totalTabs - 1)
                idx = totalTabs - 2;
            if (idx < 0)
                idx = 0;
            $('.zone_tabs > div').eq(idx).trigger('click');
        });
        ResizeTabContainer();
    }
}

function CloseEditorPopups() {
    $('.editor_popup_loading:visible').hide();
    $(".editor_overlayer").hide();
    /* remove any action classes if closing as a cancel*/
    $('.zone_tabs .confirm_delete').removeClass('confirm_delete')

}
function EditVisualABVariantCustomCSS(o)
{
    var tab = $(o).parent().parent().parent();
    //select tab to avoid editing bugs
    //tab.trigger('click');

    var idx = $('.zone_tabs > div').index(tab);
    var tabId = $(tab).attr('id');
    Log('Editing custom css for tab ' + tabId);
    //var frameWindow = $('#frame_editor')[0].contentWindow;

    if (BTeditorVars.view == 'test') {
        frameWindow.BlackTri.EditCustomCSS(tabId);
    } else {
        BlackTri.EditCustomCSS(tabId);
    }
}
function EditVisualABVariantCustomJS(o)
{
    var tab = $(o).parent().parent().parent();
    //select tab to avoid editing bugs
    //tab.trigger('click');

    var idx = $('.zone_tabs > div').index(tab);
    var tabId = $(tab).attr('id');
    Log('Editing custom javascript for tab ' + tabId);
    //var frameWindow = $('#frame_editor')[0].contentWindow;

    if (BTeditorVars.view == 'test') {
        frameWindow.BlackTri.EditCustomJS(tabId);
    } else {
        BlackTri.EditCustomJS(tabId);
    }
}

function ResizeTabContainer() {
    var context = $("#vab2_2");
    var tabContext = $('.tabs_main');
    var zt = tabContext.find(".zone_tabs");
    var totalW = 0;
    zt.find('> div').each(function(i, o) {
        totalW += $(o).outerWidth(true);
    });

    zt.width(totalW);

    //fix function for ie9/safari mac/ff mac
    function FixWidth() {
        //ie9. safari fix
        var last = zt.find('> div:last');
        var pos = last.position();
        if (pos.top > 0)
        {
            totalW += 2;
            zt.width(totalW);
            setTimeout(FixWidth, 1);
        }
    }
    FixWidth();

    CheckTabsScrollArrows();
    //Log('Total Width: ', totalW);
}
var scrollAmount = 120;
function CheckTabsScrollArrows() {
    var context = $("#vab2_2");
    var tabContext = $('.tabs_main');
    var tc = tabContext.find(".tabs_container");
    var zt = tc.find('.zone_tabs');

    if (tc.scrollLeft() == 0)
        tabContext.find('.left').removeClass('disabled').addClass('disabled');
    else
        tabContext.find('.left').show().removeClass('disabled');

    if (tc.width() + tc.scrollLeft() >= zt.width())
        tabContext.find('.right').removeClass('disabled').addClass('disabled');
    else
        tabContext.find('.right').removeClass('disabled');
}

function ResetLastStep() {
    $('.ocpc_headline, .spc, .trackingcode_ocpc, .ocpt, .ocpt_headline, .h2, .ocpt_control, .ocpt_variant, .ocpt_success').hide();
    /* only in test view*/
    if (BTeditorVars.view == 'test') {
        /* todo change to prop() method if upgrade jQuery */
        $('.conversion_goals .goal').attr('checked', false);
    }

    $('.tracking_approach').val('1');
}

function ConversionGoal(check)
{
    var localContext = $('#vab2_3');
    if (BTTestType == 'split')
        localContext = $('#ab2_2');

    var trackingapproach = GetTrackingApproach();

    Log("Test type: ", BTTestType);


    $('.trackingcode_ocpc, .ocpt_control, .ocpt_variant, .ocpt_success', localContext).hide();
    //alert(ClientCodeExists);
    if (trackingapproach == "OCPC" /*&& !ClientCodeExists */)
    {
        $('.trackingcode_ocpc, .ocpc_headline').show();
        $('.ocpc > .spc').show();

        /* only in wizard view */
        if (BTeditorVars.view == 'wizard') {
            Log("Client code found: ", ClientCodeExists);
            if (ClientCodeExists)
            {
                $('.trackingcode_ocpc, .select-3-4').hide();
            }
        }

    }
    else if (trackingapproach == "OCPT")
    {
        $('.trackingcode_ocpt, .ocpt_headline, .ocpt_control').show();//variant show only for split test
        if (BTTestType == 'split')
        {

        }

        if (BTTestType == 'visual')
        {

            //headline
            $('.ocpt .headline', localContext).hide();
            $('.ocpt .h1', localContext).show();
            $('.ocpt_control', localContext).show();
        }
        if (BTTestType == 'split')
        {
            //headline
            $('.ocpt .headline', localContext).hide();
            $('.ocpt .h3', localContext).show();

            $('.ocpt_control', localContext).show();
        }
    }

}

function TrackingApproach(what) {
    Log("Set Tracking Approach to: " + what);
    if (what == 'OCPC')
    {
        $('.tracking_approach').val("1");

        $('.ocpc').show();
        $('.ocpt').hide();
        ConversionGoal(null);
    }
    else if (what == 'OCPT')
    {
        $('.tracking_approach').val("2");

        $('.ocpc').hide();
        $('.ocpt').show();
        ConversionGoal(null);
    }
}

function GetTrackingApproach()
{
    if ($('.tracking_approach').val() == "1")
        return "OCPC";
    else if ($('.tracking_approach').val() == "2")
        return "OCPT";
    return "";
}


function GetVariantData()
{
    if (BTeditorVars.view == 'test') {
        /* slight difference in editor_wizard_test.php than other 2 views */
        var frameWindow = $('#frame_editor')[0].contentWindow;
        return frameWindow.BlackTri.GetVariantsData();
    } else {
        return BlackTri.GetVariantsData();
    }

}

function GetVisualABUrlChangedState()
{
    var context = $("#vab2_2");
    var newvablpname = $("#vablpname").val();
    //set visual user url
    var oldvablpname = $("#user_url", context).html();
    var changed = newvablpname != oldvablpname;
    Log("Url Changed State: " + changed);
    return changed;
}

function ResizeEditor(ctx) {
    Log('ResizeEditor');
    var context = "#vab2_2";
    if (typeof (context) != 'undefined')
        context = ctx;
    margin = 10;
    var el = $(context);
    /* todo define "view" in params from php */
    var isTestView = BTeditorVars.view == 'test'



    if ($('body').hasClass('etracker') && !isTestView) {
        /* this condition was not defined in editor_wizard_test but was in other 2 views */
        el.height($(window).height() - 25);
        var w = $(window).width(), h = el.height();

        w = w - 2 * margin + (5) - ($('body').css('overflow') == 'hidden' ? 15 : 0);
        h = h - 2 * margin;
        Log('width: ', w, 'height:', h);
        el.width(w);//.height(h);

        //resize separator line
        $(".sep_line", el).width("100%").css("margin", "15px auto");
        //resize frame container
        $("#user_editor", el).width(w).height(h - 88 + 6);
        //resize frame
        $("#frame_editor_ph", el).width(w).height(h - 88 + 6);
        $("#frame_editor").width(w).height(h - 88);
        //resize overlayer
        $('.editor_overlayer, .editor_proxy, .editor_action').width(w).height(h - 88 + 6);
        //resize tab container
        $(".tabs_container").width(w - 2 * 22).attr("minwidth", w - 2 * 22);
        $(".tabs_main").width(w + 2 * margin + ($('body').css('overflow') == 'hidden' ? 15 : 0) + 5);
        $.fancybox.resize();
        //$("#user_editor").removeClass("preload").addClass("preload");
    }
    else {
        el.height($(window).height() - 50);
        var w = $(window).width(), h = el.height();

        w = w - 2 * margin - (25) - ($('body').css('overflow') == 'hidden' ? 15 : 0);
        h = h - 2 * margin;
        Log('width: ', w, 'height:', h);
        el.width(w);//.height(h);

        //resize separator line
        $(".sep_line", el).width("100%").css("margin", "15px auto");
        //resize frame container
        $("#user_editor", el).width(w).height(h - 60);
        //resize frame
        $("#frame_editor_ph", el).width(w).height(h - 60);
        $("#frame_editor").width(w).height(h - 60);
        //resize overlayer
        if (!isTestView) {
            $('.editor_overlayer, .editor_proxy, .editor_action').width(w).height(h - 60);
        } else {
            $('.editor_overlayer').width(w).height(h - 60);
        }

        //resize tab container
        $(".tabs_container").width(w - 2 * 22).attr("minwidth", w - 2 * 22);
        $(".tabs_main").width(w);
        $.fancybox.resize();
        //$("#user_editor").removeClass("preload").addClass("preload");
    }
}


function ShowEditorPreloader() {
    Log('ShowEditorPreloader');
    var fb = $("#fancybox-wrap");
    /* todo check if needs different fade in editor_wizard.php view */
    $(".editor_overlayer", fb).fadeIn();
    /* when moved from 3 files, 2 were same, editor_wizard.php had fadeTo below*/
    //$(".editor_overlayer", fb).fadeTo("normal", 0.7);

    $(".editor_popup_message1").fadeIn();
}

function HideEditorPreloader() {
    Log('HideEditorPreloader');
    var fb = $("#fancybox-wrap");

    $(".editor_overlayer", fb).fadeOut();
    //ie fix
    setTimeout(function() {
        $(".editor_overlayer").hide()
    }, 200);

    $(".editor_popup_message1").fadeOut();
    $("#frame_editor").css('background-color', '#FFF');
}
function ShowEditorPreloadMessage() {
    if (DisablePopupNotice) {
        RemoveEditorPreloader();
        return;
    }

    if ($.Storage.get("BT_echk") != "Yes") {
        $(".editor_popup_message1").fadeOut();
        $(".editor_popup_message2").fadeIn();
    }
    else {
        setTimeout(function() {
            RemoveEditorPreloader();
        }, 800);
    }
}

/* when consolidated this from 3 files, editor_wizard.php had extra "show" argument but wasn't used in function */
function RemoveEditorPreloader() {
    Log('Remove editor preloader');
    $(".editor_popup_loading").hide();
    $(".editor_overlayer").fadeOut();
    //ie fix
    setTimeout(function() {
        $(".editor_overlayer").hide()
    }, 200);
    $("#frame_editor").css('background-color', '#FFF');
}
function SaveEditorSettings(o) {
    if ($(o).is(":checked"))
        $.Storage.set("BT_echk", "Yes");
    else
        $.Storage.set("BT_echk", "No");
}

/* only used in edit view */
function SetInitialValues() {
    Log('Set initial test values...');
    $('.tracking_approach').val(BTCurrentApproach);
    TrackingApproach(GetTrackingApproach());
}
/* only used in edit view */
function SaveABTest(step) {
    var localStep = step || '';
    if (localStep === '') {
        return;
    }
    
    var context = $("#ab2_2");
    Log('Save ab test step: ' + localStep);

    $('#savestep', context).val(localStep);

    //fix placeholders
    $('.clonedInput').each(function (i, o) {
        var t = $(this).find('input:first');
        if (t.val() === EmptyNamePH) {
            t.val('');
        }
    });

    var data = $(".frmAB").serialize();
    if (step === 'goals') {
        data = $('#frmVisualABStep4').serialize();
        data += data.replace(/ /g, '') === '' ? '' : '&';
        data += 'savestep=goals&collectionid=' + bt_clickgoals_vars.lpcid;
    }

    $.ajax({
        type: "POST",
        url: BTeditorVars.BaseSslUrl + "lpc/save/" + BTeditorVars.ClientId + "/",
        data: data,
        cache: false,
        success: function (data) {
            $.fancybox.close();
            document.location.reload();
        }
    });
}
/* only used in edit view */
function ABShowTrackingCode(radio) {
    var type = $(radio).val();
    $('.ab_testgoal_0, .ab_testgoal_1, .ab_variant_page').hide();
    if (type == "0") {
        //change btGA = true to false;
        var tk = $('#trackingcode_control2').val();
        tk = tk.replace('_btGA = true', '_btGA = false');
        $('#trackingcode_control2').val(tk);

        $('.ab_testgoal_0').show();
        $('.ab_control_page, .ab_success_page').show();

        $('#ab_testgoal_req').val(type).trigger("change");
    }
    else if (type == "1") {
        //change btGA = false to true;
        var tk = $('#trackingcode_control2').val();
        tk = tk.replace('_btGA = false', '_btGA = true');
        $('#trackingcode_control2').val(tk);

        $('.ab_testgoal_1').show();
        $('.ab_control_page').show();
        $('.ab_variant_page').show();
        $('.ab_success_page').hide();

        $('#ab_testgoal_req').val(type).trigger("change");
    }
    else {
        $('.ab_control_page, .ab_success_page').hide();
    }
    $.fancybox.resize();
}

/* only used in editor_wizard_test.php*/
function TestUserCanceled()
{
    CloseHelp();
}
//close and go to save
/* only used in editor_wizard_test.php*/
function TestUserRegistered()
{
    CloseHelp();
    $('#btnregisteredok').attr('onclick', 'CreateVisualAB(3)');
    CreateVisualAB(3);
}
/* UTILS */
function EnableTextOnFocus()
{
    $('.textfocus').each(function(i, o) {
        $(this).attr("originaltext", $(this).val());
    }).live('focus', function() {
        if ($(this).attr("originaltext") == $(this).val())
            $(this).val('');
    }).live('blur', function() {
        if ($(this).val() == '')
            $(this).val($(this).attr("originaltext"));
    });
}
function getScrollTop()
{
    return Math.max($("body").scrollTop(), $("html").scrollTop());
}
function Log()
{
    if (window.console && EnableLog)
    {
        var args = arguments;
        var out = [];
        for (var i = 0; i < args.length; i++)
            out.push('args[' + i + ']');
        try {
            eval('window.console.log(' + out.join(',') + ')');
        }
        catch (e) {
            //
        }
    }
}
function trimUrl(url)
{
    if (url.length > 65)
        url = url.substring(0, 65) + "...";
    return url;
}

// Adds the name of the test to the fields in the form and Opens the duplicate test popup
function openDuplicatePupup(clientid, tid) {

    $('#frmDuplicateTest').submit(function(e) {
        return false;
    });

    var testid = tid.replace(/tid-/, '');
    var testname = $('#test-' + testid)[0].innerHTML.trim();
    $('#test-name-title')[0].innerHTML = '&quot;' + testname + '&quot;';
    //$('#test-name-info')[0].innerHTML = '&quot;' + testname + '&quot;';
    $('#duplicate-name').val($('#duplicate-copyof').val() + testname);

    OpenPopup('#popDuplicateTest', true, null, {
        onComplete: function() {
            validateTestName(clientid, testid);
        }
    });
}

// Validates the name for the test copy and submits the test ID to be duplicated
function validateTestName(clientid, testid) {
    $("#frmDuplicateTest").validationEngine({
        onValidationComplete: function(form, status) {
            if (status) {
                var path = document.getElementById('path').value;
                // Shows the loading... popup while we wait for the ajax response
                OpenPopup('#popDuplicateWait', true, null, null);
                $.ajax({
                    type: "POST",
                    url: path + "lpc/duplicatecollection",
                    dataType: 'text',
                    data: {
                        clientid: clientid,
                        testid: testid,
                        testname: $('#duplicate-name').val()
                    }
                }).done(function(res) {
                    if (parseInt(res) > 0) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        setTimeout(function() {
                            showDuplicateError();
                        }, 1000);
                    }
                }).fail(function(jqXHR, textStatus) {
                    showDuplicateError();
                });
            }
        }
    });
}

// Shouws a popup with an error message
function showDuplicateError() {
    OpenPopup('#popDuplicateError', true, null, {
        onClosed: function() {
            window.location.reload();
        }
    });
}

//// AUGMENTING TYPES
Function.prototype.method = function(name, func) {
    if (!this.prototype[name]) {
        this.prototype[name] = func;
        return this;
    }
};
//Method trim to remove white spaces from the start and the end of a string
String.method('trim', function() {
    return this.replace(/^\s+|\s+$/g, '');
});

/******************************************************************************************************
 * ***************************************** PERSO EDIT MODE ****************************************
 ******************************************************************************************************/
var bt_edit_personalization = {
    persomode: parseInt($('#current-persomode').val()),
    newVdata: null,
    deletedRule: false,
    baseurl: $('#baseurl').val(),
    lpcid: $('#lpc-collection-id').val(),
    tenant: $('#current-tenant').val(),
    // When clicking on a ".open_step_perso" opens the select perso tye layer
    openStepPerso: function() {
        var self = this;
        $('.open_step_perso').on('click', function() {
            self.createNewVdataObject();
            self.contentByType(self.persomode);
            OpenPopup("#details-page-personalization");
            $('#perso-type-' + self.persomode).prop('checked', true);
        });
    },
    // creates a new object with the variants data to be edited
    createNewVdataObject: function() {
        var self = this;
        this.newVdata = {};
        $.each(VariantsPerso, function(key, variant) {
            self.newVdata[key] = variant;
        });
    },
    // shows the content depending on the perso type radio selected
    contentByType: function(type) {
        switch (parseInt(type)) {
            case 1:
                $('#perso-complete-rule').fadeIn(0);
                $('#perso-table-container').fadeOut(0);
                if (!$('#perso-copy-text').hasClass('smaller')) {
                    $('#perso-copy-text').addClass('smaller');
                }
                $('#perso-copy-text').text($('#perso-complete-intro').val());
                if ($('#perso-complete-rule').find('a.icon-edit-rule').length === 0) {
                    this.showCompleteRule($('#complete-persoid').val(), $('#complete-personame').val());
                }
                break;
            case 2:
                $('#perso-complete-rule').fadeOut(0);
                if (!$('#perso-copy-text').hasClass('smaller')) {
                    $('#perso-copy-text').addClass('smaller');
                }
                $('#perso-copy-text').text($('#perso-single-intro').val());
                this.showPersoTable();
                break;
            default:
                $('#perso-complete-rule').fadeOut(0);
                $('#perso-table-container').fadeOut(0);
                $('#perso-copy-text').removeClass('smaller');
                $('#perso-copy-text').text($('#perso-noperso-intro').val());
                break;
        }
    },
    // Shows the link with the rule selected for the entire test, the default is the first found variant rule or "unpersonalized",
    showCompleteRule: function(ruleid, rulename) {
        $('#perso-complete-rule').find('a.icon-edit-rule, span.span-list-rule').remove();
        $('#edit-rule-link-container a.rule-list-link').attr('id', 'variant_rule_' + ruleid);
        if (parseInt(ruleid) === 0) {
            rulename = $('#perso-unpersonalized').val();
        }

        var htm = '<span class="span-list-rule">' + rulename + '</span>';
        htm += '<a href="javascript:void(0)" id="variant_rule_' + ruleid + '" class="' + this.tenant + '  icon-edit-rule rule-list-link perso_wizard_link">';
        htm += (this.tenant === 'etracker') ? '<i class="fa fa-pencil"></i></a>' : '<span class="bt_icon"></span></a>';
        $('#perso-complete-rule').append(htm);
        $('#perso-complete-ruleid').val(ruleid);
        this.bindRuleClick();
    },
    // creates and shows the variant/perso rule table in the wizard.
    showPersoTable: function() {
        var self = this;
        var htm = '<table id="rule-per-variant" class="wizard-table"><tr>' +
                '<th>' + $('#perso-variant-label').val() + '</th>' +
                '<th>' + $('#perso-table-title').val() + '</th></tr>';

        $.each(VariantsPerso, function(key, variant) {
            if (variant.name.indexOf('_control') < 0 && variant.name.length > 0) {
                var ruleid = 0;
                var rulename = $('#perso-unpersonalized').val();
                if (variant.rule_id !== null) {
                    ruleid = variant.rule_id;
                    rulename = variant.rulename;
                }
                htm += '<tr><td>' + variant.name + '</td><td class="' + key + '">' +
                        '<span class="span-table-rule">' + rulename + '</span>' +
                        '<a href="javascript:void(0)" id="variant_rule_' + ruleid + '"' +
                        '      class="' + self.tenant + '  icon-edit-rule step-table-link rule-table-link perso_wizard_link">';
                htm += (self.tenant === 'etracker') ?  '<i class="fa fa-pencil"></i></a>' : '<span class="bt_icon"></span></a>';
                htm += '</td></tr>';
            }
            $('#perso-table-container').fadeIn(0);
        });
        htm += '</table>';
        $('#perso-table-container').empty().append(htm);
        this.bindRuleClick();
    },
    // when clicking on a rule name or "add a rule" in the variant navigation menu, the rule editor popups is shown
    bindRuleClick: function() {
        var self = this;
        $('.perso_wizard_link').off('click');
        $('.perso_wizard_link').on('click', function() {
            var singleRule = $(this).attr('id').replace(/variant_rule_/, '');
            BT_perso_rules.variantRule = singleRule;
            BT_perso_rules.changeMade = false;
            BT_perso_rules.variantId = false;
            if ($(this).hasClass('step-table-link')) {
                BT_perso_rules.variantId = $(this).closest('td').attr('class');
            } else if ($(this).hasClass('rule-table-link')) {
                var parentField = $(this).closest('td');
                var clss = parentField.attr('class');
                var idArr = (typeof (parentField.attr('id')) !== 'undefined') ? parentField.attr('id').split('_') : [0, 0];
                BT_perso_rules.variantId = (typeof (clss) !== 'undefined' && parseInt(clss) > 0) ? clss : idArr[1];
                BT_perso_rules.variantIndex = (typeof (idArr[2]) !== 'undefined') ? idArr[2] : false;
                BT_perso_rules.variantRule = (typeof (idArr[0]) !== 'undefined') ? idArr[0] : singleRule;
            } else if ($(this).hasClass('rule-list-link')) {
                BT_perso_rules.variantId = 0;
            }
            BT_perso_rules.selectDefaultRule(true);
        });
    },
    // binds the perso type radio value change to show the appropriate content based on its value
    bindPersoTypes: function(typeradio) {
        var self = this;
        typeradio.off('change');
        typeradio.on('change', function() {
            self.contentByType(($(this).filter(':checked').val()));
        });
    },
    // When clicking on "save changes",
    bindPersoSubmit: function() {
        var self = this;
        $('#frm_edit_lpc_personalization').on('submit', function() {
            var persoRadio = $('#frm_edit_lpc_personalization input[name=perso-type-selection]').filter(':checked').val();
            switch (parseInt(persoRadio)) {
                case 0:
                    self.persomode = 0;
                    self.saveTestDetailsVariants();
                    break;
                case 1:
                    var rid = $('#perso-complete-rule').find('a').attr('id').replace(/variant_rule_/, '');
                    var rname = $('#perso-complete-rule').find('a').text();
                    if (parseInt(rid) > 0) {
                        self.updateTestdetailsVariants(0, rid, rname, false);
                        self.persomode = 1;
                    }
                    self.saveTestDetailsVariants();
                    break;
                case 2:
                    self.persomode = 2;
                    self.saveTestDetailsVariants();
                    break;
            }
        });

        $('#perso_cancel_edition').on('click', function() {
            $.fancybox.close();
            if (self.deletedRule)
                location.reload();
        });
    },
    // Updates the perso data to be saved later if the user clicks "save changes"
    updateTestdetailsVariants: function(variantId, ruleid, rulename, variantIndex) {
        var self = this;
        this.persomode = 0;
        if (parseInt(variantId) === 0) {
            this.persomode = 1;
            var control = $('.variant_row_0').attr('id');
            self.newVdata[control]['rule_id'] = ruleid;
            $('span.span-list-rule').text(rulename);
            $('#perso-complete-rule').find('a').attr('id', 'variant_rule_' + ruleid);
        } else {
            this.newVdata[variantId]['rule_id'] = ruleid;
            this.newVdata[variantId]['rulename'] = rulename;
            $('#rule-per-variant td.' + variantId).find('span.span-table-rule').text(rulename);
            $('#rule-per-variant td.' + variantId).find('a').attr('id', 'variant_rule_' + ruleid);
            this.persomode = (variantIndex && parseInt(variantIndex) === 0) ? 1 : 2;
        }
    },
    // sends the required data to the server via AJAX to be saved in the LP and LPC tables
    saveTestDetailsVariants: function() {
        $.fancybox.close();
        $.ajax({
            type: "POST",
            url: this.baseurl + 'lpc/updateLpRules',
            datatype: 'text',
            data: {
                'lpcid': this.lpcid,
                'persomode': this.persomode,
                'lpdata': JSON.stringify(this.newVdata)
            }
        }).done(function(res) {
            location.reload();
        }).fail(function() {
            console.log('error connecting with the server');
        });
    },
    // the headline depends on whether the client is editing a test or creating a new one
    init: function() {
        var typeradio = $('input[name="perso-type-selection"]');
        this.contentByType(typeradio.filter(':checked').val());
        this.openStepPerso();
        this.bindPersoTypes(typeradio);
        this.bindPersoSubmit();
    }
};