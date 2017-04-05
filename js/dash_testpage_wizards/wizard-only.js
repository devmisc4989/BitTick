$(function () {
    var BTeditorForms = {
        /* property refers to wizard view*/
        'wizard': [
            {id: '#frmVisualStep1',
             purpose: '',
             options: {
                onValidationComplete: function (form, status) {
                    if (status) {
                        CreateVisualAB(2);
                    }
                }
            }
            },
            {
                id: '#frmABStep1',
                purpose: '',
                options: {
                    onValidationComplete: function (form, status) {
                        if (status) {
                            CreateAB(2);
                        }
                    }
                }
            },
            {
                id: '#frmABStep2',
                purpose: '',
                options: {
                    onValidationComplete: function (form, status) {
                        if (status) {
                            CreateAB(3);
                        }
                    }
                }
            },
            {
                id: '#frmABStep3',
                purpose: '',
                options: {
                    onValidationComplete: function (form, status) {
                        if (status) {
                            CreateAB(4);
                        }
                    }
                }
            },
            {
                id: '#frmABStep4',
                purpose: '',
                options: {
                    onValidationComplete: function (form, status) {
                        if (status) {
                            CreateAB(5);
                        }
                    }
                }
            }



        ]
    }

    document.domain = BTeditorVars.DocDomain;
    $("#frmVisualStep1").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                CreateVisualAB(2);
        }
    });
    
    addUrlValidation($("#frmABStep1"), $('#controlpagename'), 'open_step2');
    
    $("#frmABStep2").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                CreateAB(3);
        }
    });
    $("#frmABStep3").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                CreateAB(4);
        }
    });
    $("#frmABStep4").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                CreateAB(5);
        }
    });

    /**** visual AB ******/
    
    addUrlValidation($("#frmVisualABStep1"), $('#vablpname'), 'open_editor');

    $("#frmVisualABStep3").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                CreateVisualAB(4);
        }
    });

//set auto text selection
    $('textarea.trackingcode').click(function () {
        this.select()
    });

//set autofocus text
    EnableTextOnFocus();
});

// conditionally adds "http://" to the begining of the URL entered by the user, then validates the form
function addUrlValidation($form, $field, action) {
    $form.on('submit', function () {
        var url = $field.val().replace(/ /g, '');
        if (url.lastIndexOf('http', 0) !== 0) {
            var prefix = url.indexOf('//') === 0 ? 'http:' : 'http://';
            $field.val(prefix + url);
        }
        
        if ($(this).validationEngine('validate')) {
            if (action === 'open_editor') {
                window.location = BTeditorVars.BaseSslUrl + 'editor?url=' + encodeURIComponent($field.val());
                return false;
            } else if (action === 'open_step2') {
                CreateAB(2);
            }
        } else {
            $field.off('focus');
            if ($field.val() === 'http://') {
                $field.on('focus', function () {
                    $(this).val('');
                });
            }
        }
        
        return false;
    });
}

function CreateVisualAB(step, keepState) {
    Log('CreateVisualAB step: ' + step);
    Log('Keep editor state: ' + (keepState ? 'Yes' : 'No'));
    BTTestType = 'visual';

    //remove pop-ups
    RemoveEditorPreloader();
    //$('html, body').removeClass('noscroll').addClass('noscroll');

    if (step == 0) {
Log('c1');
        EditorCleanup();
Log('c2');

        //clean disable_scripts
        $('#disable_scripts').attr('checked', false);

        OpenPopup("#ws1");
    }
    else if (step == 1) {
        //reset data
Log('c3');
        OpenPopup("#vab2_1");
Log('c4');
    }
    else if (step == 2) {
        var context = $("#vab2_2");
        var tabContext = $('.tabs_main');

        if (!keepState) {
            EditorCleanup();
            //be sure you use #fancybox-content context on every editor html operations
            var vablpname = $("#vablpname").val();
            if (vablpname.indexOf("http") < 0)
                vablpname = "http://" + vablpname;
            Log('Found url: ', vablpname);
            //set visual user url
            $("#user_url", context).html(trimUrl(vablpname));

            //store test url for step 4
            $("#testurl", $("#vab2_3")).val(vablpname);
            $("#control_pattern", $("#vab2_3")).val(vablpname);


            //create iframe if doesnt exists
            if ($("#frame_editor").size() == 0) {
                $('<iframe frameborder="0" marginwidth="0" marginheight="0" scrolling="auto" id="frame_editor" name="frame_editor" class="frames"></iframe>').appendTo('#fancybox-wrap');
                //move tabs also
                $('.tabs_main').appendTo('#fancybox-wrap');
                //pop-ups
                $('.editor_popup_loading').appendTo('#fancybox-wrap');
                //overlayer
                $('.editor_overlayer').appendTo('#fancybox-wrap');
                //overlayer
                $('.editor_proxy, .editor_action').appendTo('#fancybox-wrap');
            }

            ResizeEditor("#vab2_2", 0);
            //resize fix
            setTimeout(function () {
                ResizeEditor("#vab2_2", 0);
            }, 100);
        }
        else {
            BlackTri.EnableEditor();
        }

        OpenPopup("#vab2_2", {
            margintop: 0,
            fullScreen: true,
            showCloseButton: Is_etracker,
            onCleanup: function () {
                Log('Hide editor layers');
                $("#frame_editor").fadeOut();
                $('.tabs_main').fadeOut();
                HideEditorPreloader();
                //hide
                BlackTri.DisableEditor();
            },
            onComplete: function () {
                Log('Show editor layers');
                PositionEditor();
                if (!keepState) {
                    ShowEditorPreloader(context);
                    var cacheBuster = Math.random();
                    var FrameUrl = BTeditorVars.FrameEditorBaseUrl + encodeURIComponent(vablpname) + "&client=" + BTeditorVars.ClientId;
                    FrameUrl += '&noscripts=' + ( $('#disable_scripts').is(':checked') ? "yes" : "no" );
                    FrameUrl += "&" + cacheBuster;
                    $("#frame_editor").attr("src", FrameUrl);
                }
                $("#frame_editor").fadeIn();
                $('.tabs_main').fadeIn();
            }
        });
    }
    else if (step == 3) {
        Log("Default Tracking Approach to: OCPC");
        ResetLastStep();

        TrackingApproach(GetTrackingApproach())
        OpenPopup("#vab2_3");
        //GetVariantsData();
		
		$('#ip_filter_action').unbind('change').bind('change', function(){
			$('#ip_filter_list').attr('disabled', $(this).val() == 'not_used');
		});
    }
    else if (step == 4) {
        $(window).scrollTop(0);
        OpenPopup("#vab2_4");
    }
    else if (step == 5) {
        var variantdata = GetVariantData();
        $('#variantdata', $("#vab2_3")).val($.toJSON(variantdata));
        //return;
        $.ajax({
            type: "POST",
            //url: "<?=$basesslurl?>ue/ls/<?=$clientid?>/?isnew=yes",
            url: BTeditorVars.BaseSslUrl + "ue/ls/" + BTeditorVars.ClientId + "/?isnew=yes",
            data: $("#frmVisualABStep3, #frmVisualABStep4").serialize(),
            cache: false,
            success: function (data) {
                $.fancybox.close();
                window.location = BTeditorVars.BaseSslUrl + 'lpc/lcd/' + data;
            }
        });
    }
}


/*
***************************************************************************************
*********************************** FOR SPLIT TESTS ************************************
**************************************************************************************/

/* not in test view, but not easy to combine with edit version */
function AddVariant() {
    variantid = $("input#variantpagehid").val();
    var len = $('.clonedInput').length;
    if (len == 1) {
        var vid = variantid.split("_");
        num = new Number(vid[0]);
    }


    newNum = new Number(num + 1);
    var persoInput = 'variant_persorule_' + newNum;

    $('div#ABVariant' + num).after(
        '<div id="ABVariant' + newNum + '">' +
            '<div style="float:left; width: 670px;" class="clonedInput">' +
            '<br>' +
            '<input type="text" class="validate[required] textbox textfocus" style="width:660px" name="' + newNum + newNum + '" id="variantname' + newNum + '" value="' + BTeditorVars.enterNameText + '" originaltext="' + BTeditorVars.enterNameText + '"/>' +
            '<input type="text" class="validate[required] textbox" style="width:660px" name="' + newNum + '" id="variantpagename' + newNum + '" value="http://"/>' +
            '<input type="hidden" class="split_test_rule_input" id="' + persoInput + '" name="' + persoInput + '"  value="0" />' +

            (Is_etracker ? '<div class="star_url_1_3_2">*</div>' : '') +
            '</div>' +
            '<div class="lp-delete-cont">' +
            '<br>' +
            '<input type="button" class="lp-button lp-delete lp_delete_url_1_3" onclick="DeleteVariant(' + newNum + ');" id="btnDel1"/>' +
            '</div>' +
            '</div>'
    );

    variantid = variantid + newNum + "_";
    $('input#variantpagehid').attr('value', variantid);
    num = newNum;
}

/********************************** Split test perso methods ******************************/
var ab_personalization = {
    baseurl: $('#baseurl').val(),
    // When clicking on a ".open_step_perso" opens the select perso tye layer
    openStepPerso: function() {
        this.contentByType($('#split_test_persomode').val());
    },
    // shows the content depending on the perso type radio selected
    contentByType: function(type) {
        $('#split_test_persomode').val(type);
        switch (parseInt(type)) {
            case 1:
                $('#perso-complete-rule').fadeIn(0);
                $('#perso-table-container').fadeOut(0);
                if (!$('#perso-copy-text').hasClass('smaller')) {
                    $('#perso-copy-text').addClass('smaller');
                }
                $('#perso-copy-text').text($('#perso-complete-intro').val());
                this.showCompleteRule($('#variant_persorule_0').val(), $('#variant_persorule_0').attr('title'));
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
        htm += '<a href="javascript:void(0)" id="variant_rule_' + ruleid + '" class="' + BTTenant + '  icon-edit-rule rule-list-link perso_wizard_link">';
        htm += (BTTenant === 'etracker') ? '<i class="fa fa-pencil"></i></a>' : '<span class="bt_icon"></span></a>';
        $('#perso-complete-rule').append(htm);
        $('#perso-complete-ruleid').val(ruleid);
        this.bindRuleClick();
    },
    // creates and shows the variant/perso rule table in the wizard.
    showPersoTable: function () {
        if ($('.split_test_rule_input').length > 0) {
            var htm = '<table id="rule-per-variant" class="wizard-table"><tr>' +
                    '<th>' + $('#perso-variant-label').val() + '</th>' +
                    '<th>' + $('#perso-table-title').val() + '</th></tr>';

            var vId;
            var vUrl;
            var ruleid;
            var rulename;
            $('.split_test_rule_input').each(function () {
                vId = $(this).attr('id').replace(/variant_persorule_/, '');
                vUrl = $('#variantpagename' + vId).val();
                ruleid = $(this).val();

                rulename = $('#perso-unpersonalized').val();
                if (parseInt(ruleid) !== 0) {
                    rulename = $(this).attr('title');
                }
                htm += '<tr><td>' + vUrl + '</td><td class="' + vId + '">' +
                        '<span class="span-table-rule">' + rulename + '</span>' +
                        '<a href="javascript:void(0)" id="variant_rule_' + ruleid + '"' + 
                        '     class="' + BTTenant + '  icon-edit-rule step-table-link rule-table-link perso_wizard_link">';
                htm += (BTTenant === 'etracker') ?  '<i class="fa fa-pencil"></i></a>' : '<span class="bt_icon"></span></a>';
                htm += '</td></tr>';
                $('#perso-table-container').fadeIn(0);
            });
            htm += '</table>';
            $('#perso-table-container').empty().append(htm);
            this.bindRuleClick();
        }else{
            $('#perso-table-container').empty();
        }
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
    // Updates the perso data to be saved when saving the entire split test
    updateSplitTests: function(variantId, ruleid, rulename) {
        var self = this;
        if (parseInt(variantId) === 0) {
            $('#split_test_persomode').val(1);
            $('#variant_persorule_0').val(ruleid);
            $('#variant_persorule_0').attr('title', rulename);
            $('span.span-list-rule').text(rulename);
            $('#perso-complete-rule').find('a').attr('id', 'variant_rule_' + ruleid);
        } else {
            $('#variant_persorule_' + variantId).val(ruleid);
            $('#rule-per-variant td.' + variantId).find('span.span-table-rule').text(rulename);
            $('#rule-per-variant td.' + variantId).find('a').attr('id', 'variant_rule_' + ruleid);
            $('#split_test_persomode').val(2);
        }
    },
    // the headline depends on whether the client is editing a test or creating a new one
    init: function() {
        var typeradio = $('input[name="perso-type-selection"]');
        this.contentByType(typeradio.filter(':checked').val());
        this.bindPersoTypes(typeradio);
    }
};

$(document).ready(function(){
    ab_personalization.init();
});


function CreateAB(step) {
    Log('CreateAB step: ' + step);
    BTTestType = "split";

    if (step == 0) {
        OpenPopup("#ws1");
    }
    else if (step == 1) {
        //remove iframe
        $("#frame_editor").remove();
        OpenPopup("#ab2_1");
    }
    else if (step == 2) {
        ab_personalization.openStepPerso();
        OpenPopup("#ab2_2");
    }
    else if (step == 3) {

        ResetLastStep();
        TrackingApproach(GetTrackingApproach())

        var controlpagename = $("#controlpagename").val();
        Log("Found control page name: " + controlpagename);
        if (controlpagename.indexOf("http") < 0)
            controlpagename = "http://" + controlpagename;

        //set visual user url
        $("#user_url").html(controlpagename);

        //store test url for step 4
        $("#testurl", $("#ab2_3")).val(controlpagename);
        $("#control_pattern", $("#ab2_3")).val(controlpagename);

        //get return for tracking code and code
        //$.getJSON("<?=$basesslurl?>ue/ncab/?url=" + controlpagename + "&lpcid=" + 0, function (data) {
        $.getJSON(BTeditorVars.BaseSslUrl+"ue/ncab/?url=" + controlpagename + "&lpcid=" + 0, function (data) {
            Log("Tracking code data received: ", data);
            //save return data to html
            if (data) {
                var lastStepContext = $("#ab2_3");
                $("#trackingcode_success", lastStepContext).val(data.lpctrackingcode_success);
                $("#trackingcode_control", lastStepContext).val(data.lpctrackingcode_control);
                $("#trackingcode_variant", lastStepContext).val(data.lpctrackingcode_variant);

                $("#trackingcode_ocpc", lastStepContext).val(data.lpctrackingcode_ocpc);

                $("#lpccode", lastStepContext).val(data.lpccode);
            }
        });

        OpenPopup("#ab2_3");
    }
    else if (step == 4) {
        OpenPopup("#ab2_4");
    }
    else if (step == 5) {
        //return;
        //fix placeholders
        $('.clonedInput').each(function (i, o) {
            var t = $(this).find('input:first');
            if (t.val() == EmptyNamePH)
                t.val('');
        });
        /* todo this same ajax post is made in edit SaveABTest() */
        $.ajax({
            type: "POST",
            //url: "<?=$basesslurl?>lpc/save/<?=$clientid?>/",
            url: BTeditorVars.BaseSslUrl + "lpc/save/" + BTeditorVars.ClientId + "/",
            data: $(".frmAB").serialize(),
            cache: false,
            success: function (data) {
                $.fancybox.close();
                document.location = BTeditorVars.BaseSslUrl + 'lpc/lcd/' + data;
            }
        });
    }
}

function createSmartMessage(){

    $.fancybox.close();

    $('#full_editor_overlay, #sms_ui').show();
    setTimeout(centerSmsUI, 30);

    var currHeight=$(window).height();
    var ui= $('#sms_ui_frame')[0].contentWindow;
    var heightData= {action: 'setHeight', height: currHeight};
    ui.postMessage(JSON.stringify(heightData),'*');
}

function centerSmsUI( uiHt){
    var windowH=$(window).height();
    var $ui=$('#sms_ui');
    var top =((windowH -uiHt) /2) - 40;
   if(top < 0){
       top = 0
   }
    $ui.css({top: top}).data('top', top);
}

// Some browsers will work with addEventListener, some others with attachEvent
if (window.addEventListener) {
    addEventListener("message", receiveSmsMessage);
} else if (window.attachEvent) {
    window.attachEvent("message", receiveSmsMessage);
}

function receiveSmsMessage(e) {
    var rData = $.parseJSON(e.data);
    var action = rData.action;
    var $ui = $('#sms_ui');
    if (action === 'close') {
        closeSMS();
        CreateVisualAB(0);
    }

    if (action === 'heightChange') {
        var uiData = rData.uiData;
        if (uiData.view === 'rules') {
            uiData.height = 600;
        }
        centerSmsUI(uiData.height);
    }

    if (action === 'smsPreview') {
        $ui.css('top', 0);
    }

    if (action === 'closePreview') {
        $ui.css('top', $ui.data('top'));
    }
}

function closeSMS(){
    var ui= $('#sms_ui_frame')[0].contentWindow;
    var cancelData= {action: 'cancel'};
    ui.postMessage(JSON.stringify(cancelData),'*');

    $('#full_editor_overlay, #sms_ui').fadeOut();
}

