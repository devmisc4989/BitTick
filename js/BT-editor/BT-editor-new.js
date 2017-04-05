/* fixes bug [DC-175]https://blacktri-jira.atlassian.net/browse/DC-175*/
var BTShowUnload = true;

/* isIE is set in IE conditional comment in head of page*/
if (!isIE) {
    window.onbeforeunload = function() {
        if (!BTShowUnload) {
            /* don't return anything or do anything....IE displays the message "null"*/
            //return null;
        } else {
            return BTeditorVars.unload_message;
        }
    };
}

$(function () {
    document.domain = BTeditorVars.DocDomain;
    BlackTri.editorForms.init();
    BlackTri.inlineURLEditor.init();
});

// handle the personalization events and methods
BlackTri.personalization = {
    tenant: null,
    singleRule: 0, // Rule selected for single variants (each of them)
    // If we are editing an LPC, this updates the text and the ID for every variant tab link (to open the Rule editor popup)
    updateVariantRuleText: function() {
        this.tenant = $('#current-tenant').val();
        
        if(typeof(BTVariantsData) === 'undefined' || typeof(BTVariantsData.pages[BTVariantsData.activePage] === 'undefined')){
            return false;
        }
        
        var self = this;
        var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
        $.each(activePageData.variants, function(key, variant) {
            if (typeof (variant.persorule) !== 'undefined' && variant.persorule !== null) {
                self.updateVariantRule(key, variant.persorule['id'], variant.persorule['name'], false);
            } else {
                self.updateVariantRule(key, 0, $('#perso_rule_actions').attr('class'), false);
            }
        });
    },
    // if the user is creating or editing a test, the header text may change. Also checks by default the corresponding rule in step 3
    showIntroText: function() {
        var radio = this.preselectedRadio();
        $('input#perso-type-' + radio).prop('checked', true);
        this.contentByType(radio);
    },
    // depending on the variant count and selected rules, a "perso type" radio button is selected
    preselectedRadio: function() {
        var hasrules = this.variantHasRules();
        var vcount = parseInt(hasrules.vcount);
        var rcount = parseInt(hasrules.rcount);
        var vrules = hasrules.rules;
        var radio = (vrules.length !== 1) ? vrules.length : ((vcount === rcount) ? 1 : 2);
        if(radio > 2)
            radio = 2;

        var ruleid = (radio === 1) ? vrules[0].id : 0;
        var rulename = (radio === 1) ? vrules[0].name : 0;
        this.showCompleteRule(ruleid, rulename);
        return radio;
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
                    this.showCompleteRule(0, '');
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
                $('#perso-complete-ruleid').val(0);
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

        var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
        $.each(activePageData.variants, function(key, variant) {
            var ruleid = 0;
            var rulename = $('#perso-unpersonalized').val();
            if (typeof (variant.persorule) !== 'undefined' && variant.persorule !== null) {
                ruleid = variant.persorule['id'];
                rulename = variant.persorule['name'];
            }
            htm += '<tr><td>' + variant.name + '</td><td class="' + key + '">' +
                    '<span class="span-table-rule">' + rulename + '</span>' +
                    '<a href="javascript:void(0)" id="variant_rule_' + ruleid + '"' +
                        '      class="' + self.tenant + '  icon-edit-rule step-table-link rule-table-link perso_wizard_link">';
                htm += (self.tenant === 'etracker') ?  '<i class="fa fa-pencil"></i></a>' : '<span class="bt_icon"></span></a>';
                htm += '</td></tr>';
            $('#perso-table-container').fadeIn(0);
        });
        htm += '</table>';
        $('#perso-table-container').empty().append(htm);
        this.bindRuleClick();
    },
    // returns the array of diferent rules present in all variants
    variantHasRules: function() {
        var self = this;
        var rules = [];
        var vcount = 0;
        var rcount = 0;
        var found = false;
        var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
        
        $.each(activePageData.variants, function(key, variant) {
            vcount++;
            if (typeof (variant.persorule) !== 'undefined' && variant.persorule !== null) {
                rcount++;
                found = false;
                var current = variant.persorule['id'];
                $.each(rules, function(ind, rule) {
                    if (parseInt(rule.id) === parseInt(current)) {
                        found = true;
                        return false;
                    }
                });
                if (!found || rules.length === 0) {
                    rules.push({
                        id: current,
                        name: variant.persorule['name']
                    });
                }
            }
        });
        return {
            vcount: vcount,
            rcount: rcount,
            rules: rules
        };
    },
    // Updates the rule id in the selected variant, if tabid = 0, updates the rule for every variant
    updateVariantRule: function(tabid, ruleid, rulename, overwrite) {
        var self = this;
        if (parseInt(tabid) === 0) {
            $('#perso-complete-ruleid').val(ruleid);
            $('span.span-list-rule').text(rulename);
            $('.rule-list-link').attr('id', 'variant_rule_' + ruleid);
        } else {
            $('#variant_tabs .tab').filter(':visible').each(function() {
                if ($(this).attr('id') === tabid) {
                    var $aRule = $(this).find('a.perso_add_rule');
                    $aRule.attr('id', 'variant_rule_' + ruleid).text(rulename);
                    return self.variantRuleClickEvent($aRule, overwrite);
                }
            });
            $('#rule-per-variant td.' + tabid).find('span.span-table-rule').text(rulename);
            $('#rule-per-variant td.' + tabid).find('a.perso_wizard_link').attr('id', 'variant_rule_' + ruleid);
        }
    },
    // verifies the current perso mode to add or remove the click event on every variant perso rule
    variantRuleClickEvent: function($aRule, overwrite) {
        if (BTeditorVars.view === 'edit' && typeof (BTCurrentPersomode) !== 'undefined') {
            if (parseInt(BTCurrentPersomode) === 1) {
                $('.menu_personalization_container').fadeOut(0);
                $aRule.off('click');
                $aRule.removeClass().addClass('disabled');
            } else if (parseInt(BTCurrentPersomode) === 0 && !overwrite) {
                $aRule.attr('id', 'variant_rule_0').text($('#perso-unpersonalized').val());
                var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
                $.each(activePageData.variants, function (ind, variant) {
                    variant.persorule = null;
                });
            }
        }
        return false;
    },
    // Returns the rule name given a rule Id, -- searchs into the currentRule array located in perso-rules.js --
    ruleNameById: function(ruleid) {
        var rname = '';
        $.each(BT_perso_rules.currentRules, function(ind, rule) {
            if (rule.rule_id === ruleid) {
                rname = rule.name;
                return false;
            }
        });
        return rname;
    },
    // when clicking on a rule name or "add a rule" in the variant navigation menu, the rule editor popups is shown
    bindRuleClick: function() {
        var self = this;
        $('.perso_wizard_link').off('click');
        $('.perso_wizard_link').on('click', function() {
            self.singleRule = $(this).attr('id').replace(/variant_rule_/, '');
            BT_perso_rules.variantRule = self.singleRule;
            BT_perso_rules.changeMade = false;
            BT_perso_rules.variantId = false;
            if ($(this).hasClass('rule-table-link')) {
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
    // the headline depends on whether the client is editing a test or creating a new one
    init: function() {
        var typeradio = $('input[name="perso-type-selection"]');
        this.contentByType(typeradio.filter(':checked').val());
        this.bindPersoTypes(typeradio);
        this.updateVariantRuleText();
    }
};

function CreateVisualAB(step, keepState) {
    var view = BTeditorVars.view;
    Log('CreateVisualAB step: ' + step);
    Log('Keep editor state: ' + (keepState ? 'Yes' : 'No'));
    BTTestType = 'visual';

    switch (step) {
        /* only in wizard view*/
        case 1:
            //reset data
            $('#vablpname').val(BTeditorVars.test_url);
console.log("clickme");            
            OpenPopup("#vab2_1");
            break;
        case 3:
            BlackTri.personalization.showIntroText();
            OpenPopup("#vab2_3");
            break;
        case 4:
            /* edit version*/
            if (view === 'edit') {
                Log("Tracking Approach is: " + GetTrackingApproach());

                TrackingApproach(GetTrackingApproach())
                OpenPopup("#vab2_4");
            }
            /* todo figure out if this step is used in wizard*/
            if (view == 'wizard') {
                Log("Default Tracking Approach to: OCPC");
                ResetLastStep();
                TrackingApproach(GetTrackingApproach());
                $('#control_pattern').val(BTeditorVars.test_url);
                OpenPopup("#vab2_4");
            }

            break;

        case 5:
            if (view == 'edit') {
                OpenPopup("#vab2_5");
            } else if (view == 'wizard') {
                $(window).scrollTop(0);
                OpenPopup("#vab2_5");
            }

            break;
            /* only used in wizard view to save a new test*/
        case 6:
            
            $('.click_goals_name, .click_goals_selector').trigger('keyup');
            
            var rtype = $('input[name="perso-type-selection"]').filter(':checked').val();
            if (parseInt(rtype) === 0) {
                BlackTri.modifyPersoRule(null, null, null);
            } else if (parseInt(rtype) === 1) {
                var $cRule = $('#perso-complete-rule').find('a.rule-list-link');
                var cid = $cRule.attr('id').replace(/variant_rule_/, '');
                cid = (parseInt(cid) > 0) ? cid : 0;
                if (cid === 0) {
                    $('#perso-type-0').prop('checked', 'true');
                }
                $('#perso-complete-ruleid').val(cid);
            }

            var variantdata =  BTVariantsData;
            $('#variantdata', $("#vab2_4")).val($.toJSON(variantdata));
            //return;
			var saveUrl = BTeditorVars.BaseSslUrl;
			
            $.ajax({
                type: "POST",
                url: saveUrl + "ue/ls/" + BTeditorVars.ClientId + "/?isnew=yes",
                data: $("#frmVisualABStep3, #frmVisualABStep4, #frmVisualABStep5").serialize(),
                cache: false,
                success: function(redirectData) {
                    BTShowUnload = false;
                    BlackTri.redirect('test_page', redirectData);

                }
            });

            break;

        default:
            alert('[ERROR]  CreateVisualAB() Step ' + step + ' not accounted for in new code');
    }
}
/* combines form validation from old views*/
BlackTri.editorForms = {
    init: function() {
        var view = BTeditorVars.view;
        $('#frmVisualABStep3').on('submit', function() {
            if (view === 'edit') {
                SaveVisualABTest('perso');
            } else if (view === 'wizard') {
                CreateVisualAB(4);
            }
            return false;
        });

        if (view === 'wizard') {
            $("#frmVisualABStep4").validationEngine({
                onValidationComplete: function (form, status) {
                    if (status) {
                        CreateVisualAB(5);
                    }
                }
            });
        }

        /* Wizard view only*/
        
        $("#frmVisualABStep1").on('submit', function () {
            var $field = $('#vablpname');
            var url = $field.val().replace(/ /g, '');
            if (url.lastIndexOf('http', 0) !== 0) {
                var prefix = url.indexOf('//') === 0 ? 'http:' : 'http://';
                $field.val(prefix + url);
            }

            if ($(this).validationEngine('validate')) {
                window.location = BTEditorUrl + '?url=' + encodeURIComponent($field.val());
                return false;
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

        //set auto text selection
        $('textarea.trackingcode').click(function() {
            this.select()
        });

        //set autofocus text
        EnableTextOnFocus();

        if (view == ' edit') {
            SetInitialValues();
        }
    }
}

BlackTri.redirect = function(type, redirect_data) {
    var url, redirectParent = false;
	
	if(redirect_data && redirect_data.indexOf('//') != -1){
		document.location = redirect_data;
		return;
	}
    switch (type) {
        case 'visitor_proceed':
            redirectParent = true;
            url = BTeditorVars.visitor_redirects.proceed;
            break;

        case 'visitor_cancel':
            redirectParent = true;
            url = BTeditorVars.visitor_redirects.cancel;
            break;
        case 'dash':
            url = BTeditorVars.DashUrl;
            break;

        case 'test_page':
            var urlParams = redirect_data ? redirect_data : BTeditorVars.CollectionId + '/' + BTeditorVars.ClientId;
            url = BTeditorVars.testPageUrl + urlParams;
            break;

    }
    var win = redirectParent ? window.top : window;
	url = url.replace('http://', 'https://');
    win.location = url;
}

function visitorProceed() {
    BlackTri.redirect('visitor_proceed');
}

function SwitchMode(mode){
	BlackTri.browseMode.switchMode(mode);
}
function CancelEditing(redirUrl) {
    /* trigger for etracker*/
	if(redirUrl && redirUrl != ''){
		
		var saveUrl = BTeditorVars.BaseSslUrl;
		$.ajax({
			type: "POST",
			url: saveUrl + "ue/cancel/",
			data: null,
			cache: false
		})
		.always( function(){
	        BlackTri.redirect('dash', redirUrl);		
		});
		return;
	}
	
    var isEditView = BTeditorVars.view == 'edit';
    if (isEditView) {
        BlackTri.redirect('test_page');
    } else {
        BlackTri.redirect('dash');
    }
}

var ClientCodeExists = false;

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
    var context = $("#vab2_4");
    Log('Save test step: ' + localStep);

    $('#savestep', context).val(localStep);

    var hasrules = BlackTri.personalization.variantHasRules();
    var vcount = parseInt(hasrules.vcount);
    var rcount = parseInt(hasrules.rcount);
    var vrules = hasrules.rules;
    var radio = (vrules.length !== 1) ? vrules.length : ((vcount === rcount) ? 1 : 2);
    if (radio > 2)
        radio = 2;

    if (parseInt(BTCurrentPersomode) === 0) {
        $('input#perso-type-' + radio).prop('checked', true);
        if (parseInt(radio) === 0) {
            BlackTri.modifyPersoRule(null, null, null);
        } else if (parseInt(radio) === 1) {
            var ruleid = (vrules.length === 1) ? vrules[0].id : 0;
            var rulename = (vrules.length === 1) ? vrules[0].name : '';
            $('#perso-complete-ruleid').val(ruleid);
        }
    }else{
        $('#perso-complete-ruleid').val(BTControlRuleId);
        $('input#perso-type-' + BTCurrentPersomode).prop('checked', true);
    }

    if (step == 'variants') {
        var variantdata = BTVariantsData;

        $('#variantdata', context).val($.toJSON(variantdata));
    }

	var saveUrl = BTeditorVars.BaseSslUrl;
	
    $.ajax({
        type: "POST",
        url: saveUrl + "ue/ls/" + BTeditorVars.ClientId + "/",
        data: $("#frmVisualABStep3, #frmVisualABStep4, #frmVisualABStep5").serialize(),
        cache: false,
        success: function (redirectData) {
            BTShowUnload = false;
            BlackTri.redirect('test_page', redirectData);
        },
        error: function (data) {
        }
    });
}

function EditorCleanup()
{
    Log('Cleaning editor tabs and frames');

    var tabContext = $('#menu_tabs');
    //make clean
    tabContext.find('.zone_tabs > div:not(.default,.new)').remove();
    $("#frame_editor").remove();

    if (BTeditorVars.view != 'edit') {
        //var context = $("#vab2_2");
        //clean  keep state
        $("#user_url", context).html('&nbsp;')
    }
}

function ShowEditorOverlayers(){
	DisablePopupNotice = false;
	//show loading message
	$('#editor_loading_message').show();
	//show overlayer
	$('.editor_overlayer').show();
}

function EditorSwitchDevice(device){
    console.log('Switching device', device);
	
	BlackTri.IsDeviceSwitching = true;
	
    ShowEditorOverlayers();
	
    //hide mouse over outline
    $('.editor_element_outline').hide();
}
function EditorBrowse(clientUrl){
	console.log('Editor Browsing to url ', clientUrl);
	
	BlackTri.IsBrowsing = true;
	
	ShowEditorOverlayers();
	
	//BlackTri.SetClientUrl(clientUrl);	
}

function EditorInvalidHtml(){
	$(".editor_popup_message1").fadeOut();
	
	$(".editor_popup_message3 > .close_popup").hide();
	$(".editor_popup_message3").fadeIn();
}
function EditorDomReady(){
    Log('Editor domready event called');
	//$(".editor_popup_message1 > .close_popup").show();
	
	//show preloading message
	$('.preloading_message').fadeIn();
	
	//removing the pop-up
	//RemoveEditorPreloader();
	
	//start editor loading
	EditorLoaded(false, true);

}

/* todo thoroughly test EditorLoaded() cases*/
function EditorLoaded(manualClose, domReady) {
    Log('Editor loaded event called');

    var manualClose = manualClose || false;
    var domReady = domReady || false;

    if (manualClose)
    {
        //hide preloading message
        $('.preloading_message').fadeOut();
    }

    var View = BTeditorVars.view;
    BTShowUnload = false;
	Log("!!!!!!!!!!!!!!!!!! View", View);
    if (View != 'test') {
        //check for messages

        ShowEditorPreloadMessage();
    }

    if (!BlackTri.IsBrowsing)
    {
        var tabContext = $('#menu_tabs');
        if (!BlackTri.IsInited)
        {
            /* prevent window.onbeforeunload showing dialog*/
            /* todo Tried setting this flag only in ajax calls but IE keeps triggering onbeforeunload when html editor opens*/
            BTShowUnload = false;

            //init tabing functions
            InitTabs();
            //attach tab events
            AttachTabEvents();

            var clientUrl = BTeditorVars.test_url;
            var testType = View == 'wizard' ? 'new' : 'edit';

            BlackTri.Init(clientUrl, testType);

            //init perso
            BlackTri.personalization.init();

            BlackTri.deviceType.init();

            var firstTabClick = false;
            //if not editable trigger new variant
            if (View == 'edit') {
                if (!editorTabsBuilt){
                    BuildEditorTabs();
                    //select first tab
                    firstTabClick = setTimeout(function () {
                        var tabContext = $('#menu_tabs');
                        tabContext.find('.zone_tabs .tab:eq(1)').trigger('click');
                    }, 150);
                }
            } else {
                $('.zone_tabs .new', tabContext).trigger('click');
            }

            if (typeof (savedVdata) !== 'undefined' && savedVdata) {
                var htm = '<input type="hidden" value="' + newTestUrl + '" name="newTestUrl" />'
                $('#frmVisualABStep3').append(htm);
                BlackTri.inlineURLEditor.showKeepChangesMessage(savedVdata)
            } else {
                hideOrKeepSmsMenu(BTVariantsData);
            }

        }
        else
        {
            if (!domReady && BlackTri.IsDeviceSwitching)
            {
                BlackTri.IsDeviceSwitching = false;
                reloadTest();
            }
        }
    }
    else
        reloadTest();

    function reloadTest() {
        BlackTri.Reload(clientUrl, testType);
        //apply current variant
        setTimeout(function () {
            var tabContext = $('#menu_tabs');
            tabContext.find('.zone_tabs .selected').trigger('click');
        }, 150);
    }
}

/* only used in edit view*/
var editorTabsBuilt = false;
function BuildEditorTabs() {
	
	editorTabsBuilt = true;
    var tabContext = $('#menu_tabs');

    var This = tabContext.find('.zone_tabs .new');

    var activePageData= BTVariantsData.pages[BTVariantsData.activePage];
    $.each(activePageData.variants, function(key, variant) {
        /* Log('Adding new tab ' + newTabID);*/

        var variantLabel = variant.name;
        var newTabID = key;
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
        bindTabHover(tabContext, newTab);
    })
    ResizeTabContainer();
    tabContext.find('.default').addClass('selected')
}


function InitTabs() {

    var vablpname = BTeditorVars.test_url;

    Log('Init Tabs for', vablpname);

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
                var lastStepContext = $("#vab2_4");
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
}

/* todo needs  a rewrite*/

function InitTrackingCodes() {
    Log("Getting test tracking codes");
    if (BTIsEditable) {
        if (BTTrackingCodeData) {

            var lastStepContext = $("#vab2_4");
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
   
    var tabContext = $('#menu_tabs');
    var tc = tabContext.find(".tabs_container");
    var zt = tc.find('.zone_tabs');

    $('.zone_tabs .new', tabContext).unbind('click', AddNewVariantTab).click(AddNewVariantTab);
    $('.zone_tabs .default', tabContext).unbind('click', SelectVariantTab).click(SelectVariantTab);

    //set scrollers clicks
    var clickInitiated = false;
    $('#menu_tabs .left').unbind('click').click(function() {
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
    $('#menu_tabs .right').unbind('click').click(function() {
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
/* fix conflict with new objectModel if variant index has been used before */
function getNextTabNum() {
    var variantNum = 1;
    var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
    
    if (typeof (activePageData) !== 'undefined') {
        var variants = activePageData.variants;
        if (variants && !$.isEmptyObject(variants)) {
            $.each(variants, function (key) {
                var currNum = 1 * key.split('_').pop();
                variantNum = currNum >= variantNum ? currNum + 1 : variantNum;
            });
        }
    }

    return variantNum;
}

function AddNewVariantTab() {
    if ($('#variant_tabs .tab').length < 3 || parseInt($('#allowed-variants').val()) !== 1) {
        Log('Add new tab');
        //var context = $("#vab2_2");
        var tabContext = $('#menu_tabs');

        var tabNum = getNextTabNum();
        var tabId = 'variant_' + tabNum;

        var newTab = $(this).clone(false, false).removeClass('new').attr('id', tabId);

        var variantLabel = NewVariant + ' ' + tabNum;

        var menu = $('.menu_template .menu_container').clone();

        BlackTri.createNewVariant(tabId, variantLabel);

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
        bindTabHover(tabContext, newTab);

        DisablePopupNotice = true;
    }
}

function bindTabHover(tabContext, newTab) {
    newTab.find('.menu_container').hover(
            function(evt) {
                /* prevent menu display on inactive tabs*/
                if (!newTab.hasClass('selected')) {
                    return;
                }
                var parentW = $(this).parent().outerWidth(false);
                $(this).removeClass('menu_hover').addClass('menu_hover').find('.menu').show().css({left: -parentW + 20, width: parentW > BTMenuWidth ? parentW + 15 : BTMenuWidth});
                tabContext.find('.tabs_container').height(300);

                var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
                var disableDeleteLink = $.map(activePageData.variants, function () {
                    return true;
                }).length < 2;

            $(this).find('.delete_variant').toggleClass('disabled',disableDeleteLink);
        },
        function (evt) {
            $(this).removeClass('menu_hover').find('.menu').hide();
            tabContext.find('.tabs_container').css('height', 'auto');
            $(this).parent().removeClass('hover_fix');
        })
        .click(function (evt) {
            tabContext.find('.tabs_container').css('height', 'auto');

        });
}


function SelectVariantTab(e) {
    /* fix bug clicking on menu container not stopping propogation */
    if (e) {
        var $tgt = $(e.target);
        if ($tgt.closest('.menu_container').length) {
            return;
        }
    }

    var tabContext = $('#menu_tabs');
    var tabId = $(this).attr('id');

    Log('Current tab id: ' + tabId);
    //deselect last tab
    tabContext.find('.zone_tabs div').removeClass('selected');
    //select clicked tab
    $(this).addClass('selected');

    Log('Select tab ' + tabId);
    BlackTri.SelectTab(tabId);
    BlackTri.personalization.bindRuleClick();
}

function RenameVisualABVariant(o) {

    var tab = $(o).closest('.tab');
    var idx = tab.index();

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

    var tabId = $('#variant_tabs .selected').attr('id');

    if (idx >= 1)
    {

        BlackTri.history.store('rename_variant', 'var', null);

        //save name to array
        BlackTri.RenameTab(tabId, name);

        Log('Renaming tab id ', tabId, 'to', name);
        $('.zone_tabs > div').eq(idx).find('>a').html(name);
        ResizeTabContainer();

        CloseEditorPopups();
    }
}

/* todo switch out onclick for more generic CloseEditorPopups() and deprecate this function*/

function OpenDeleteVariantPopup(o) {
    var $link = $(o);
    if ($link.is('.disabled')) {
        return;
    }
    $(".editor_overlayer").show();
    /* in case loading popup still showing*/
    $('.editor_popup_loading').hide();

    $('#confirm_delete_variant_popup').show();
    var $tab = $link.closest('.tab').addClass('confirm_delete');
}

function RemoveVisualABVariant() {

    var $tab = $('#menu_tabs').find('.confirm_delete');
    var tabId = $tab.attr('id');


    $tab.fadeOut(250, '', function() {
        var $prev = $tab.prev();
        $tab.hide();
        var historyData = {
            tab: $tab,
            prev: $prev
        }

        BlackTri.history.store('delete_variant', 'var', historyData);

        /* delete variant object */
        $.each(BTVariantsData.pages, function(idp, page){
            delete BTVariantsData.pages[idp].variants[tabId];
        });
        $prev.click().find('.history_undo').removeClass('disabled');

        ResizeTabContainer();
    });

    CloseEditorPopups();

}

function CloseEditorPopups() {
    $('.editor_popup_loading:visible').hide();
    $(".editor_overlayer").hide();
    /* remove any action classes if closing as a cancel*/
    $('.zone_tabs .confirm_delete').removeClass('confirm_delete');
    $('#edit_url_popup').removeAttr('style');

}

/* todo combine these 2 functions into one */
function EditVisualABVariantCustomCSS(o)
{
    var tabId = $(o).closest('.tab').attr('id');
    BlackTri.EditCustomCSS(tabId);
}

function EditVisualABVariantCustomJS(o)
{
    var tabId = $(o).closest('.tab').attr('id');
    BlackTri.EditCustomJS(tabId);
}

function ResizeTabContainer() {

    var tabContext = $('#menu_tabs');
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
    // var context = $("#vab2_2");
    var tabContext = $('#menu_tabs');
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
    $('.tracking_approach').val('1');
}

function ConversionGoal(check)
{
    var localContext = $('#vab2_4');
    if (BTTestType == 'split')
        localContext = $('#ab2_2');
    var trackingapproach = GetTrackingApproach();

    $('.trackingcode_ocpc, .ocpt_control, .ocpt_variant, .ocpt_success', localContext).hide();

    if (trackingapproach == "OCPC")
    {
        $('.trackingcode_ocpc, .ocpc_headline').show();
        $('.ocpc > .spc').show();

        /* only in wizard view */
        if (BTeditorVars.view == 'wizard') {
            if (ClientCodeExists)
            {
                $('.trackingcode_ocpc, .select-3-4').hide();
            }
        }

    }
    else if (trackingapproach == "OCPT")
    {
        $('.trackingcode_ocpt, .ocpt_headline, .ocpt_control').show();//variant show only for split test

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

function ShowEditorPreloadMessage() {
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
    BlackTri.personalization.init();
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
		var out = ["[EDITOR-NEW]:"];
		for (var i = 0; i < arguments.length; i++)
			out.push(arguments[i]);
		window.console.log.apply(window.console, out);
    }
}
function trimUrl(url)
{
    if (url.length > 65)
        url = url.substring(0, 65) + "...";
    return url;
}

// Verifies if the testtype is SMS to disable or leave enabled the smartmessage menu in variant tabs
function hideOrKeepSmsMenu(vdata) {
    var smsTest = vdata.isNewSMSTest || false;
    if (!smsTest) {
        $('.menu_sms_item').removeClass('drop-nav');
        $('.menu_sms_item').find('ul.dropdown').remove();
        $('a#open_sms_menu').addClass('disabled').removeClass('drop-nav dropdown_link menu_sms_item');
        $('a#open_sms_menu').find('span#open').remove();
    }else{
        BTVariantsData.isNewSmsTest = true;
    }
}

BlackTri.inlineURLEditor = {
    animation: null,
    hidetime: null,
    original: null,
    // If the test is a MPT, calls the corresponding function in BT-multipagetest.js (Or just calls postSavedData)
    saveNewUrl: function () {
        var newUrl = $('#user_url_input').val();

        if (newUrl.toString() === this.original) {
            $('.edit_project_url').fadeOut(99, function () {
                $('#user_url_bg').css('display', 'block');
            });
        } else {
            BTVariantsData.pages[BTVariantsData.activePage].url = newUrl;
            this.postSavedData(false);
        }
    },
    // saves the current dom_code and loads the new URL in the editor
    postSavedData: function (url) {
        var newUrl = url ? url : $('#user_url_input').val();
        var vdata = BTVariantsData;
        var redirect = '';

        $.each(vdata.pages, function (idp, page) {
            $.each(page.variants, function (idv, variant) {
                var domcode = variant.dom_modification_code;
                vdata.pages[idp].variants[idv].dom_modification_code['[JS]'] = escape(domcode['[JS]']);
                vdata.pages[idp].variants[idv].dom_modification_code['[SMS]'] = escape(domcode['[SMS]']);
                vdata.pages[idp].variants[idv].dom_modification_code['[CSS]'] = escape(domcode['[CSS]']);
            });
        });

        if (BTeditorVars.view === 'edit') {
            redirect = window.location.href;
        } else {
            redirect = BTeditorVars.BaseSslUrl + (BTeditorVars.BaseSslUrl.substring(BTeditorVars.BaseSslUrl.length - 1) === '/' ? '' : '/') + 'editor?url=' + encodeURIComponent(newUrl);
        }

        if (newUrl.indexOf('https://') !== -1){
            redirect = redirect.replace('http://', 'https://').replace('?&redir=yes', '');
        }
						
        var editedUrlData = JSON.stringify({
            'newUrl': newUrl,
            'vData': vdata,
            'gData': BTeditorVars.goalsData
        });
        
        var saveUrl = BTeditorVars.BaseSslUrl;
        if (BTeditorVars.NoSSL) {
            saveUrl = saveUrl.replace('https://', 'http://');
        }
		
        $.ajax({
            type: "POST",
            url: saveUrl + "editor/saveEditorDataToSession",
            cache: false,
            data: {
                'newUrlData': editedUrlData
            },
        }).done(function (res) {
            document.location = redirect;
        }).fail(function () {
            console.log('Error: The VariantsData could not be saved before reload');
            document.location = redirect;
        });
    },
    // Depending on the number of variants saved for the previous URL we need to create them accordingly
    appendNewVariant: function (idv, vname) {
        var tabContext = $('#menu_tabs');
        var This = tabContext.find('.zone_tabs .new');
        var newTab = This.clone(false, false).removeClass('new').attr('id', idv);
        var menu = $('.menu_template .menu_container').clone();

        newTab.find('a').html(vname);
        newTab.append(menu);
        newTab.click(SelectVariantTab);
        This.before(newTab);

        bindTabHover(tabContext, newTab);
        ResizeTabContainer();
    },
    // If we are in "wizard"mode and the client wants to keep the old changes, we create the necessart variants and apply the changes
    applyVdataChanges: function (vdata) {
        var self = this;
        
        $.each(vdata.pages, function (idp, page) {
            $.each(page.variants, function (idv, variant) {
                var domcode = variant.dom_modification_code;
                var js = unescape(domcode['[JS]']);
                var css = unescape(domcode['[CSS]']);
                var sms = unescape(domcode['[SMS]']);
                var sms_html = unescape(domcode['[SMS_HTML]'])

                vdata.pages[idp].variants[idv].dom_modification_code['[JS]'] = js !== 'null' ? js : '';
                vdata.pages[idp].variants[idv].dom_modification_code['[CSS]'] = css !== 'null' ? css : '';

                if (sms !== 'undefined' && sms !== 'null') {
                    vdata.pages[idp].variants[idv].dom_modification_code['[SMS]'] = sms;
                }

                if (sms_html !== 'undefined' && sms_html !== 'null') {
                    vdata.pages[idp].variants[idv].dom_modification_code['[SMS_HTML]'] = sms_html;
                }

                hideOrKeepSmsMenu(vdata);
                $('#variant_tabs').find('.tab').not('.default').first().trigger('click');
            });
        });

        $('#menu_tabs').find('.tab').not('.original').not('.new').remove();
        BTVariantsData = vdata;
        BuildEditorTabs();
        setTimeout(function () {
            $('#menu_tabs').find('.zone_tabs .tab:eq(1)').trigger('click');
        }, 150);

        var firstv = false;
        var activePageData = BTVariantsData.pages[BTVariantsData.activePage];

        $.each(activePageData.variants, function (idv, variant) {
            if (!firstv) {
                firstv = idv;
                $('#menu_tabs').find('#variant_1').find('a').first().text(variant.name);
                ResizeTabContainer();
            }
        });
        
        $('.tab#' + firstv).trigger('click');
        bt_mptest_config.setMptNamesAndUrls();
    },
    // When the users has changed the URL we ask him if he wants to keep the changes made previously (if any)
    showKeepChangesMessage: function (vdata) {
        var self = this;

        if ($(".editor_popup_loading").filter(':visible').length > 0) {
            setTimeout(function () {
                self.showKeepChangesMessage(vdata);
            }, 99);
        } else if (BTeditorVars.isMpt) {
            setTimeout(function () {
                self.applyVdataChanges(vdata);
            }, 99);
        } else {
            $('input#editor_keep_changes').off('click');
            $('input#editor_undo_changes').off('click');

            $('#keep_changes_overlay').fadeIn(99);
            $('#keep_changes_wrapper').fadeIn(99, function () {
                $('input#editor_keep_changes').on('click', function () {
                    self.applyVdataChanges(vdata);
                    $('#keep_changes_overlay, #keep_changes_wrapper').fadeOut(99);
                });

                $('input#editor_undo_changes').on('click', function () {
                    hideOrKeepSmsMenu(vdata);
                    if (BTeditorVars.view === 'edit') {
                        $.each(vdata.pages, function (idp, page) {
                            $.each(page.variants, function (idv, variant) {
                                var $tab = $('.tab#' + idv);
                                $tab.fadeOut(0, '', function () {
                                    $tab.hide();
                                    delete BTVariantsData.pages[idp].variants[idv];
                                    ResizeTabContainer();
                                });
                            });
                        });

                        var variant_id = 'variant_1';
                        var variant_label = NewVariant + ' 1';

                        self.appendNewVariant(variant_id, variant_label);
                        BlackTri.createNewVariant(variant_id, variant_label);

                        $('.tab#' + variant_id).trigger('click');
                    }
                    $('#keep_changes_overlay, #keep_changes_wrapper').fadeOut(99);
                });
            });
        }
    },
    // Set the functionality for the URL container/text field.
    init: function () {
        var self = this;
        this.original = $('#user_url_input').val();

        // On mouse over we show the border and the pencil icon
        $('.editor_url_container #user_url_bg').on('mouseover', function () {
            $(this).stop(true).animate({
                'margin': '0',
                'padding': '1px'
            }, 299);
            $('.editor_url_container .url_pencil').stop().fadeIn(299);
            $('.editor_url_container .url_pencil').stop().animate({
                'opacity': '1'
            }, 299);
        });

        // On mouse leave hide the border and the pencil icon
        $('.editor_url_container #user_url_bg').on('mouseleave', function () {
            $(this).stop(true).animate({
                'margin': '1px 0 0 1px',
                'padding': '0'
            }, 249);
            $('.editor_url_container .url_pencil').stop().fadeOut(199);
        });

        // When clicking on the URL we display the input and the save/cancel icons
        $('#user_url_bg').on('click', function () {
            $(this).fadeOut(99, function () {
                $('.edit_project_url').css('display', 'block');
            });
        });

        // If the user press the "esc" key inside the URL input we hide it, if press the "enter" key, the URL is saved
        $('#user_url_input').on('keyup', function (e) {
            var code = e.keyCode ? e.keyCode : e.which;
            if (code === 27) {
                $('.edit_project_url').fadeOut(99, function () {
                    $('#user_url_bg').css('display', 'block');
                });
            } else if (code === 13) {
                clearTimeout(self.hidetime);
                self.saveNewUrl();
            }
        });

        // When clicking anywhere in the document, if the input is visible, it is hidden along with the save/cancel icons.
        $('#editor_wrap *').on('click focus', function (e) {
            if ($('#user_url_input').css('display') !== 'none') {
                e.stopPropagation();
                var cancel = e.type === 'click' && $(this).attr('id') === 'url_cancel';
                if (cancel || !$(this).hasClass('edit_project_url')) {
                    self.hidetime = setTimeout(function () {
                        $('.edit_project_url').fadeOut(99, function () {
                            $('#user_url_bg').css('display', 'block');
                        });
                    }, 99);
                }
            }
        });

        // When the "save" icon is clicked we save the URL and reload the editor
        $('.url_edit_icon#url_save').on('click', function (e) {
            clearTimeout(self.hidetime);
            self.saveNewUrl();
        });
    }
};
