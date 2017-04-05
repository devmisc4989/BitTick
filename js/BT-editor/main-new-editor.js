/* START BT CODE */

var CANCELLOG = false;
var debug = console;

window.onerror = function(error) {
    if (typeof (ShowError) == "function")
        ShowError(error);
};
window["BlackTri"] = new (function(jQuery) {

    this.version = 1; /* todo figure out where version created */
	this.IsBrowsing = false;
	this.IsInited = false;
	this.IsDeviceSwitching = false;
    //local vars
    var This = this;
    this.variantKey = '';

    this.variant = null;
    /* todo customTags should be ready to DEPRECATE*/
    var customTags = ['[CSS]', '[JS]'];
    /* used when saving data so we don't save an unedited variant */
    this.variantRequiredFields = ['[CSS]', '[JS]', '[SMS_HTML]', '[SMS]'];/* matches customTags for now, but hwen add SMS or other features it may not*/

    var customCSSPlaceholderID = "BTCustomCSSPlaceHolder";

    var showad_js = '\n<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>';


	/* code edit */
	this.CurrentTagName = "";
	this.CurrentElementInfo = false;
	this.CurrentSelector = "";
	this.ClientBaseHref = "";
	/* end code edit */

    var $ = jQuery;

    var skipAllActions = false;
    var currentItemProperties = null;

    var btTenant = BTTenant || 'blacktri';
    var currentClientHeight = 0;
	var firstTimeSkipLoadHtml = true;
	
	var clientDocumentHeight = 0;
    /* public functions */
    this.Init = function(clientUrl, testType) {
		if(This.IsInited)
		{
	        console.warn('BLACTRI INIT Called more then onece! Please check for errors!');
			return;			
		}
        console.warn('BLACTRI INIT')
		This.IsInited = true;
        This.jQuery = This.$ = window.jQuery;
        This.prepTest(clientUrl, testType);

        InitPlugins();
        EnableDragResize();
        InitGenericFunctions();
        //grab base href from client window frame
        BTeditorVars.EditorRelURL = getClientBaseHref();

        This.codeEdit.init();

        This.com.init();
        /* initialize undo methods*/
        This.history.init();
		This.browseMode.init('editor');
    }
	this.Reload = function(clientUrl, testType){
		Log('Editor Reload ', clientUrl);
        This.prepTest(clientUrl, testType);
        //grab base href from client window frame
        BTeditorVars.EditorRelURL = getClientBaseHref();
				
		This.browseMode.init('editor');
	}

    /* NEW  new variant data  model */
    this.createNewVariant = function(key, label) {
        console.warn('CREATE NEW VARIANT')
        This.browseMode.switchMode('editor');

        var pageIndex = 'page_1';
        if (BTVariantsData.pages && !$.isEmptyObject(BTVariantsData.pages)) {
            pageIndex = BTVariantsData.activePage;
        }else{
            BTVariantsData.activePage = pageIndex;
            BTVariantsData.pages[pageIndex] = {
                id: null,
                name: BTeditorVars.pagePrefix + ' 1',
                url: BTeditorVars.test_url,
                variants: {}
            };
        }

        $.each(BTVariantsData.pages, function (idp, page) {
            BTVariantsData.pages[idp].variants[key] = {
                version: This.version,
                name: label,
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
        
        This.variant = BTVariantsData.pages[pageIndex].variants[key];

        if (BTeditorVars.newsms) {
            var sms = BTeditorVars.newsms;
            $.extend(This.variant.sms, {id: sms.id, template: sms.template, ui: sms.sms});
            This.variant.dom_modification_code['[SMS_HTML]'] = BTeditorVars.newsms.html;
            BTeditorVars.newsms = null;
        }
    };

    this.GetVariantsData = function() {
        return BTVariantsData;
    };

    // Add/update/delete the perso rule id for the given variant or all of the variants if tabId = 0 or tabId = null (sets the rule to null)
    this.modifyPersoRule = function (tabId, ruleid, rulename) {
        if (tabId === null) {
            $('#perso-complete-ruleid').val(0);
        } else if (parseInt(tabId) === 0) {
            $('#perso-complete-ruleid').val(ruleid);
        } else {
            $.each(BTVariantsData.pages, function (ind, page) {
                BTVariantsData.pages[ind].variants[tabId].persorule = {
                    id: ruleid,
                    name: rulename,
                    type: 2
                };
            });
        }
    };

    this.SelectTab = function (tabId) {
        This.browseMode.switchMode('editor');
        /* todo don't like this primitive, only used when saving html , refactor with html editing upgrades*/
        this.variantKey = tabId;
        This.variant = BTVariantsData.pages[BTVariantsData.activePage].variants[tabId];
        BlackTri.history.setUI();
        //hide action layer if open
        HideActionLayer();
        HideEditetElementsFrame();
        LoadOriginalHTML();
        if (tabId != 'variant_0')
        {
            ProxyMouseEvents();
            ShowProxyLayer();
            LoadVariant();
        }
        else
        {
            DisableProxyMouseEvents();
            //show proxy layer only
            ShowProxyLayer();
        }
        
        if (typeof(BTVariantsData['isNewSMSTest']) !== 'undefined' && BTVariantsData['isNewSMSTest']) {
            $('.menu_container .menu_sms_item').fadeIn('0');
        }

    }
    this.RenameTab = function(tabId, name)
    {
        Log('Rename tab', tabId, name);
        $.each(BTVariantsData.pages, function(idp, page){
            BTVariantsData.pages[idp].variants[tabId].name = name;
        })
    }

    this.editorMoveElementSaveHtml = function ()
    {
		This.com.sendCallBackMessage('btEditorMoveElementSaveHtml', function(elemInfo){
			resetProxyAfterMove();
			
			This.history.store('move_element', 'elem');
	
			var script = generateScript('move', This.CurrentSelector, elemInfo);
			var codeSource = 'move';
			This.saveNewVariantCode('JS', script, codeSource, This.CurrentSelector);
	
			//cancel draggable and and leave element at current position
			$('#BTMoveFrame').draggable("destroy")
			This.ExecScriptOnClient('$("' + This.CurrentSelector + '").draggable("destroy");');
	
			//hide frame
			$('#BTMoveFrame').hide();
			HideActionLayer();
		});
    }

    function resetProxyAfterMove() {
        var scroll = $(window).scrollTop();

        $('#editor_wrap').height('100%');
        $('#editor_top').removeClass('fixed');
        $('.editor_proxy').removeClass('move_mode').scrollTop(scroll);
    }
    this.editorMoveElementCancel = function()
    {
		This.com.sendMessage('btEditorMoveElementCancel', currentItemProperties);
		
		resetProxyAfterMove();
		//var elm = jQuery(clientDocument).find(This.CurrentSelector)

		//cancel draggable and restore original position
		$('#BTMoveFrame').draggable("destroy");
		
		This.ExecScriptOnClient('$("' + This.CurrentSelector + '").draggable("destroy");');

		currentItemProperties = null;

		$('#BTMoveFrame').hide();
		$('.editor_element_outline').show();
		HideActionLayer();
    }
    function generateScript(type, selector, content, property)
    {
        var script = '';
        switch (type)
        {
            case 'replace':
                if (selector == '[CSS]')
                {
                    script = '$("<style/>").html(' + jQuery.toJSON(content) + ').appendTo("head");';
                }
                else
                    script = '$("' + selector + '").replaceWith(' + jQuery.toJSON(content) + ');';
                break;

                /* "text" is actually all innerhtml */
            case 'text':
                script = '$("' + selector + '").html(' + jQuery.toJSON(content) + ');';
                break;

            case 'hide':
                script = '$("' + selector + '").css("visibility","hidden");';
                break;

            case 'remove':
                script = '$("' + selector + '").remove();';
                break;

            case 'move':
                script = '_bt.moveElement("' + selector + '",' + jQuery.toJSON(content) + ');';
                break;            
            case 'style':
                script = '_bt.setStyle("' + selector + '",' + jQuery.toJSON(content) + ');';
                break;
            
            case 'attribute':
                script = '$("' + selector + '").attr("' + property + '",' + jQuery.toJSON(content) + ');';
                break;
        }
        //Log('generateScript: ', type, script);
        return script;
    }
    This.generateScript = generateScript;

    this.ExecScriptOnClient = function(script) {
		This.com.sendMessage('btApplyChanges', script);
    }
    this.ExecFunctionOnClient = function() {
		var params = [].splice.apply(arguments,[0, arguments.length]);
		This.com.sendMessage.apply(null, params);
    }

    function ShowFactor(selector, script)
    {
        var workingSelector = selector || This.CurrentSelector;
        var workingScript = script || '';

        if (valueIn(workingSelector, customTags))
        {

            var htmlContentChange = This.variant.dom_modification_code[workingSelector] || '';
            if (workingSelector == '[CSS]'){
				if(htmlContentChange.length > 0)
					This.com.sendMessage('btAddCustomCssElement', customCSSPlaceholderID, htmlContentChange);
				else
					This.com.sendMessage('btRemoveCustomCssElement', customCSSPlaceholderID);					
            }
            else if (workingSelector == '[JS]' && htmlContentChange.length > 0){
				This.com.sendMessage('btApplyChanges',htmlContentChange);
            }
            This.editorCancel();
            return;
        }
        else
        {
            if (workingScript != '')
            {
                Log('We have a script change, running only this code, ' + script);
				This.com.sendMessage('btApplyChanges',workingScript);
            }
        }
        This.editorCancel();
    }

    //close editor pop-up
    this.editorCancel = function() {
        CloseEditor();
    }

    function InitGenericFunctions() {

        $('.editor_action').click(function(ev) {
            if (!$('#BTMouseOverEditorMenu').is(':visible') || ev.target !== this)
                return;

            HideClickEditorMenu();
            HideActionLayer();
        });
    }

    function LoadVariant(tabId)
    {
        Log('Load ' + tabId + ' factors');

        This.CurrentSelector = '[CSS]';
        ShowFactor();
        This.CurrentSelector = '[JS]';
        ShowFactor();
        This.CurrentSelector = '';
		This.CurrentElementInfo = false;
    }
    function DisableEvents(original)
    {
        var disableAll = original || false;
        Log('DisableEvents, Original tab: ', disableAll);
        //remove events
        var elements = jQuery("body *:not(.BTSkipElement):visible");

        //cancel links and clicks
        elements.unbind('click');
        if (disableAll)
        {
            elements
                    .removeAttr('href')
                    .removeAttr('onclick')
                    .removeAttr('target')
                    .removeAttr('action')
        }
        //disable forms
        jQuery('body form').submit(function() {
            return false;
        })
    }

    //MAIN UPDATES
    function ShowActionLayer() {
        /*Log('ShowActionLayer');*/
        $('.editor_action').show()
        skipAllActions = true;

    }
    function HideActionLayer() {
        Log('HideActionLayer');
        $('.editor_action').hide()
        skipAllActions = false;
    }
    this.HideActionLayer = HideActionLayer;
    function HideEditetElementsFrame() {
        Log('HideEditetElementsFrame');
        $('.editor_element_outline_changed, .editor_element_outline').remove();
    }
    function DisableProxyMouseEvents()
    {
        Log('DisableProxyMouseEvents');
        $('.editor_proxy > div.editor_proxy_scroll').unbind();
    }
    function ProxyMouseEvents()
    {
        Log('ProxyMouseEvents');
        //create proxy element with scroll
        if ($('.editor_proxy > div.editor_proxy_scroll').length == 0) {
            $('.editor_proxy').append('<div class="editor_proxy_scroll"/>');
        }
        if ($('.editor_proxy > div.editor_element_outline').length == 0) {
            $('.editor_proxy').append('<div class="editor_element_outline"/>');
        }

        if ($('.editor_proxy').find('#tag_ident').length <= 0) {
            var tag_ident = '<div id="tag_ident" style="position: absolute;top:0px; left: 0; height: 0px;display:none">' +
                    '<span id="tag_ident_tagname" style="background: #095377;position: absolute; top:-8px;left: 0;color:#ffffff; font-weight: bold; font-size: 11px; padding: 0 2px;"></span></div>';


            $('.editor_proxy').append(tag_ident);
        }

        var cph = $('.editor_proxy > div.editor_proxy_scroll');

        //helper function, used local
        function setProxyLayerHeight() {
            var clientHeight = getRealDocumentHeight() + 1;
            //reset height
            cph.height(clientHeight);
            if (clientHeight != currentClientHeight)
            {
                Log('Found client height: ', clientHeight);
                //reset height
                cph.height(clientHeight);
                currentClientHeight = clientHeight;
            }
        }

        setProxyLayerHeight();
        /* poll client page every second to check height changes*/
        if (!This.checkHeightTimer) {
            This.checkHeightTimer = setInterval(setProxyLayerHeight, 1000);
        }
        //reset scroll top
        cph.scrollTop(0);

        //proxy mouse scroll
        Log('Proxy mouse scroll');
        $('.editor_proxy').unbind('scroll').bind('scroll', function(evt) {
            if (isIE && !$(this).is(':visible')) return;
			This.com.sendMessage('btScrollTop', $(this).scrollTop());
        });

        //proxy mouse over
        This.CurrentSelector = '';
        This.CurrentTagName = '';
		This.CurrentElementInfo = false;
        skipAllActions = false;

        Log('Set-up mouse over event and client html element dettection');
        $('.editor_proxy > div.editor_proxy_scroll').unbind('click').click(function(evt) {
            if (skipAllActions)
                return;
            Log('Mouse proxy layer clicked');
            //if(lastElementHovered!=null)
            if (This.CurrentSelector != '') {
                This.com.sendCallBackMessage('btClickElement', function (smsInfo, elementInfo) {

                    //resave current element info with click enabled
                    This.CurrentElementInfo = elementInfo;
                    bt_clickgoals_config.checkClickedElementMenu(This.CurrentSelector);
                    OpenClickEditorMenu(evt, elementInfo);
                });
            } else {
                Log('Clicked element is null, skipping');
            }
        });


        //helper function used local
        function internalPositionHoverFrame() {
            if (!This.CurrentElementInfo) {
                return false;
            }
			
            var el = $('.editor_element_outline');
            var tagIdentifier = '&lt;' + This.CurrentTagName + '&gt;';
			
            var off = This.CurrentElementInfo.offset;
            var w = This.CurrentElementInfo.outerWidth;
            var h = This.CurrentElementInfo.outerHeight;
            //verify width and height to avoid scroll and clips
            var tw = $('.editor_proxy_scroll').width();
            if (w > tw - 4)
                w = tw - 4;
            var th = $('.editor_proxy_scroll').height();
            if (h > th - 4)
                h = th - 4;

            //check left offset and fix width based on that fix
            if (off.left < 3)
            {
                w -= 2 - off.left;
                off.left = 3;
            }
            //check top offset and fix width based on that fix
            if (off.top < 3)
            {
                h -= 2 - off.top;
                off.top = 3;
            }

            el.css({left: off.left - 1, top: off.top - 1, width: w + 2, height: h + 2}).show();

            /* tag identifier */
            var $tag_ident = $('#tag_ident');
            /* place above the hover frame unless element is at top of page*/
            var tagIdentTop = off.top - 12 >= 10 ? off.top - 12 : off.top + 6;

            $tag_ident.css({left: off.left - 4, top: tagIdentTop}).show();
            
            if (bt_clickgoals_config.showHighlight) {
                $('.tag_clickgoal').fadeIn(0);
                $('.tag_clickgoal').each(function () {
                    var ol = $(this).css('left').replace(/px/, '');
                    var ot = $(this).css('top').replace(/px/, '');
                    var sameTag = This.CurrentTagName.toLowerCase() === $(this).data('tagname');
                    var samePos = parseInt(ol) === parseInt(off.left - 4) && parseInt(ot) === parseInt(tagIdentTop);

                    if (This.CurrentSelector.indexOf('#') >= 0) {
                        sameTag = sameTag || This.CurrentSelector === $(this).data('tagname');
                    }

                    if (samePos) {
                        $(this).fadeOut(0);
                    }

                    if (sameTag && samePos) {
                        tagIdentifier += '&nbsp|&nbsp' + bt_clickgoals_vars.goalTagLabel;
                        return false;
                    }
                });
            }

            $('#tag_ident_tagname').html(tagIdentifier);
        }
		
        This.internalPositionHoverFrame = internalPositionHoverFrame;
        //mouse move events here
        $('.editor_proxy > div.editor_proxy_scroll').unbind('mousemove').mousemove(function(evt) {
            if (skipAllActions)
                return;
            var off = $(this).offset();
            var posx, posy;
            if (evt.pageX || evt.pageY) {
                posx = evt.pageX;
                posy = evt.pageY;
            }
            else if (evt.clientX || evt.clientY) {
                posx = evt.clientX;
                posy = evt.clientY;
            }
            //fix real mouse pos
            posx -= off.left;
            posy -= off.top;

			//if(isIE)
			//	This.HideProxyLayer();
			This.com.sendMessage('btElementFromPoint', posx, posy, isIE);
        });
    }
	this.HideProxyLayer = function(){
		$('.editor_proxy').hide();
	}
	this.ShowProxyLayer = function(){
		$('.editor_proxy').show();
	}
   this.ProxyMouseEvents = ProxyMouseEvents;
	this.setCurrentElementInfo = function(selector, tagName, elementInfo){
        This.CurrentSelector = selector; // This.clientElement is replaced by This.CurrentSelector
        This.CurrentTagName = tagName;
		This.CurrentElementInfo = elementInfo;
		
		if(This.CurrentElementInfo)
			This.setActiveElement();
	}
    this.setActiveElement = function(is_parent) {
		var is_parent = is_parent || false;		
        $('#parent_selector').toggleClass('disabled', This.CurrentElementInfo.disableParentSelect);
        This.internalPositionHoverFrame();
        if (is_parent) {
            setTagnameDescription(This.CurrentTagName);
        }

    }
	
	this.activateParentElement = function(){		
		This.com.sendMessage('btActivateParentElement');
	}
    this.setClientBaseHref = function (baseHref) {
        This.ClientBaseHref = baseHref;
    },

   this.ClientStoreOriginalHtml = function(script) {
		This.com.sendMessage('btStoreOriginalHtml');
    }
    this.ClientLoadOriginalHtml = function(script) {
		This.com.sendMessage('btLoadOriginalHtml');
    }
    function getClientBaseHref() {
		return This.ClientBaseHref;
    }
    function LoadOriginalHTML()
    {
		if(firstTimeSkipLoadHtml)
		{
			//avoid loading original html unless user does an action first
			firstTimeSkipLoadHtml = false;		
			return;
		}
		
        Log('Load original body html');
        This.ClientLoadOriginalHtml();
   }
    
    function ShowProxyLayer(){
        $('.editor_proxy').show();
    }
    this.ShowProxyLayer = ShowProxyLayer;
    
    function HideProxyLayer(){
        $('.editor_proxy').hide();
    }

    this.DisableEditor = function()
    {
        /* this functions disables editor functionality */
        Log('Disable editor');
        HideActionLayer();
        HideProxyLayer();
    }
    this.GetCurrentUrl = function()
    {
        return currentUrl;
    }

    /**
     * Replaces "CreateNewTest" and "LoadExistingTest" by combining them
     * @param url
     * @param testType
     */
    this.prepTest = function(url, testType) {
        currentUrl = url;
        if (testType == 'new') {
            /* create global BTVariantsData object */
            BTVariantsData = {
                pageCount: 1,
                pages: {}
            };
            /* add flag to identify if this is a new SMS type test*/
            if(BTeditorVars.newsms){
                BTVariantsData.isNewSMSTest = true;
            }

        }
        //StoreOriginalHTML();
    }

    function CloseEditor()
    {
        HideActionLayer();
        //hide editor window
        jQuery('#BTFactorEditPopup').hide();
    }

    function EnableDragResize() {
        $('#edit_url_popup').draggable({
            handle: 'h1'
        })
    }

    function showTagSpecificMenuItems($menu, tagname) {
        /* hide tag specific menu links by default */
        var $tagRelatedLinks = $menu.find('a.tag_specific').removeClass('show');
        /* show if tags match*/
        $tagRelatedLinks.filter(function() {
            return $(this).data('tag') === tagname;
        }).addClass('show');

    }

    /**
     * Used to edit <a href> or <img src> in popup
     * @param string type "image" or "link"
     */
    This.editUrlAttribute = function(type) {

        $(".editor_overlayer").show();

        var attr = type === 'link' ? 'href' : 'src';
        var $popup = $('#edit_url_popup').show();
        $popup.find('.edit_url_text').text(function(){
            var dataVar = type === 'link' ? 'text_link' : 'text_image';
            return $(this).data(dataVar);
        });


        /* store element data, for some reason This.$el changes when saving*/
        This.urlEditData = {
            type: type
        }

        var currAttrVal = This.CurrentElementInfo[attr] || '';

        $('#edit_url_input').val(currAttrVal);
        HideClickEditorMenu();
        HideActionLayer();

    }

    /**
     * Used to save edited <a href> or <img src> from popup
     */
    This.saveUrlAttribute = function() {
        var attr = This.urlEditData.type === 'link' ? 'href' : 'src';
        var newAttrVal = $('#edit_url_input').val();
        var selector = This.CurrentSelector;
        var script = generateScript('attribute', selector, newAttrVal, attr);
        /* IMPORTANT to store history before saving new code */
        var historyAction = 'edit_' + This.urlEditData.type;
        This.history.store(historyAction, 'code');
        This.saveNewVariantCode('JS', script, 'elem_url_edit', selector);

        $('#menu_tabs .tab.selected').click();
        CloseEditorPopups();
    }

    function setTagnameDescription(tagname) {
        var menuHeadingHtml = '&lt;' + tagname + '&gt; ' + (BT_HtmlTags[tagname].description || '');
        $('.BTMouseOverEditorMenu_header').html(menuHeadingHtml);
    }
    function OpenClickEditorMenu(evt, clientInfo)
    {
		//evt, this, This.CurrentTagName
		var tagname = This.CurrentTagName;
		
        ShowActionLayer();
        var menu = This.elementEditMenu = jQuery('#BTMouseOverEditorMenu');
        showTagSpecificMenuItems(menu,tagname);

        if (BTeditorVars.add_ons.styles_edit.active) {
            setTagnameDescription(tagname);
        }
		
        var off = This.CurrentElementInfo.offset;
		var ifrOff = jQuery('#frame_editor').offset();
        var posx, posy;

        if (evt.pageX || evt.pageY) {
            posx = evt.pageX - clientInfo.scrollLeft;
            posy = evt.pageY - clientInfo.scrollTop;
        }
        else if (evt.clientX || evt.clientY) {
            posx = evt.clientX - clientInfo.scrollLeft;
            posy = evt.clientY - clientInfo.scrollTop;
        }

        //fix real mouse pos
        //posx -= ifrOff.left;
        posy = posy - ifrOff.top + off.top;

        var bh = jQuery('#frame_editor').height();
        var bw = jQuery('#frame_editor').width();
		
        var dxMargin = 20;
        if (posx + menu.width() + dxMargin > bw)
            posx = bw - menu.width() - dxMargin;
        if (posy + menu.height() + dxMargin > bh)
            posy = bh - menu.height() - dxMargin;

        menu.css({left: posx, top: posy}).show();
        /* check if this is a main SMS element */
       /* var isSMSMainElement = This.$el.is('[id^=_bt_sms]');*/
        /*var isSMSMainElement = This.sms.isSMSMainElement(This.$el);
        console.clear();
        console.log('SMS Main el =', isSMSMainElement);
        menu.toggleClass('disable_sms_editing',isSMSMainElement);*/

    }

    function HideClickEditorMenu()
    {
        var menu = jQuery('#BTMouseOverEditorMenu');
        menu.hide();
    }
    this.HideClickEditorMenu = HideClickEditorMenu;

    this.menuActionHideElement = function()
    {
        MenuHideElement();
    }
    this.menuActionRemoveElement = function()
    {
        MenuRemoveElement();
    }
    this.menuActionMoveElement = function()
    {
		This.com.sendCallBackMessage('btMenuMoveElementClick', function(moveInfo){
	        MenuMoveElement(moveInfo);
		}, This.CurrentSelector);
    }
    //internal call
    function MenuMoveElement(moveInfo)
    {
        //hide mouse editor menu
        HideClickEditorMenu();

        /* hide the little blue tag identifer or it stays in same place*/
        $('#tag_ident').hide();

        /* experimental method to change window to full size */
        var $proxy = $('.editor_proxy')
        var scroll = $proxy.scrollTop();
        
        var el = $('.editor_element_outline');
        var calculatedTop = parseFloat(el.css('top').replace('px', '')) - moveInfo.scrollTop;
        var oPos = el.offset();
        $('#BTMoveFrame')
                .css({left: el.css('left'), top: calculatedTop, width: el.width(), height: el.height()})
                .show()
                .draggable({
                    containment: '.editor_proxy',
                    scroll: true,
                    scrollSensitivity: 200,
                    start: MenuMoveElement_dragStart,
                    stop: MenuMoveElement_dragStop,
                    drag: MenuMoveElement_drag
                });

        //enable draggable on client frame
        This.ExecScriptOnClient('$("' + This.CurrentSelector + '").draggable({cancel:""});');
		
        //save current item original properties
        currentItemProperties = moveInfo.currentItemProperties;
		
        //hide outline
        $('.editor_element_outline').hide();


        var dragStartTop = null;
        var dragStartScrollTop = null;
        var $proxy;
        var maxScroll;
        var prox_ht;

        //draggable helper functions
        function MenuMoveElement_drag(ev, ui) {
            //if (clientWindow[0] && typeof (clientWindow[0].btDrag) === 'function') {
            //    clientWindow[0].btDrag({clientX: ui.position.left, clientY: ui.position.top});
            //}
			This.com.sendMessage('btDrag', {clientX: ui.position.left, clientY: ui.position.top});
        }
        function MenuMoveElement_dragStart(ev, ui) {
            //if (clientWindow[0] && typeof (clientWindow[0].btDragStart) === 'function') {
            //    clientWindow[0].btDragStart({clientX: ui.position.left, clientY: ui.position.top});
            //}
			This.com.sendMessage('btDragStart', {clientX: ui.position.left, clientY: ui.position.top});
        }
        function MenuMoveElement_dragStop(ev, ui) {
            //if (clientWindow[0] && typeof (clientWindow[0].btDragStop) === 'function') {
            //    clientWindow[0].btDragStop({clientX: ui.position.left, clientY: ui.position.top});
            //}
			This.com.sendMessage('btDragStop', {clientX: ui.position.left, clientY: ui.position.top});
        }
    }

    function MenuRemoveElement()
    {
        Log('Remove element: ' + This.CurrentSelector);
        This.history.store('remove_element', 'elem');
        var script = generateScript('remove', This.CurrentSelector, '');

        var codeSource = 'remove';
        This.saveNewVariantCode('JS', script, codeSource, This.CurrentSelector);
		This.ExecScriptOnClient(script);
		
        HideClickEditorMenu();

        HideActionLayer();
    }

    /**
     * Stores code within BTVariantsData object
     * @param type string "JS", "CSS" etc
     * @param newCode
     * @param source "style_edit, move, html_edit etc"
     * @param domSelector
     */

    this.saveNewVariantCode = function(type, newCode, source, domSelector) {

        var codeKey = '[' + type + ']';
        var variant = This.variant;
        var variantLocation;
        var isUserCustomCode = !source && !domSelector;
        if (!isUserCustomCode) {
            if (!variant.selectors[domSelector]) {
                variant.selectors[domSelector] = {};
            }
            if (!variant.selectors[domSelector][source]) {
                variant.selectors[domSelector][source] = {};
            }
            if (!variant.selectors[domSelector][source][codeKey]) {
                variant.selectors[domSelector][source][codeKey] = '';
            }
            variantLocation = variant.selectors[domSelector][source];
        } else {
        	variantLocation = variant.dom_modification_code;
        }

        var existCode = variantLocation[codeKey] || '';
        existCode += existCode.length ? '\n' : '';
		
		if(isUserCustomCode)
	        variantLocation[codeKey] = optimizeCode(existCode, newCode, This.CurrentSelector);
		else
			variantLocation[codeKey] = existCode + newCode;
		
        /* run function again for JS code so we store it in variant['JS'] also */
        if (!isUserCustomCode) {
            This.saveNewVariantCode(type, newCode);
        }

    }
	function optimizeCode(existCode, newCode, domSelector) {
		//console.warn('Checking '+domSelector+' for chaincall.', existCode, newCode);
		if(newCode.indexOf('_bt.') != -1)
			return optimizeBtGeneratedCode(existCode, newCode, domSelector);		
		else
			return optimizejQueryCode(existCode, newCode, domSelector);
	}
	function optimizejQueryCode(existCode, newCode, domSelector) {
		//console.warn('Checking '+domSelector+' for chaincall.', existCode, newCode);
		var newCodeSelector = '$("' + domSelector + '").';
		var codeLinesBySelector = [];
		var selectorIndexes = getSelectorCodeIndexes(existCode, newCodeSelector);		
		//get original code lines for replaces
		for(var i = 0; i< selectorIndexes.length; i++)
		{
			var codeLineData = selectorIndexes[i];
			codeLineData.originalCodeLine = existCode.substring(codeLineData.from, codeLineData.to);
		}
		//do replaces
		for(var i = 0; i< selectorIndexes.length; i++)
		{
			var codeLineData = selectorIndexes[i];
			if(codeLineData.originalCodeLine.indexOf('.replaceWith(') != -1 && newCode.indexOf('.replaceWith(') != -1)
			{
				existCode = removeCodeLine(existCode, codeLineData.originalCodeLine);
			}
			else if(codeLineData.originalCodeLine.indexOf('.html(') != -1 && newCode.indexOf('.html(') != -1)
			{
				existCode = removeCodeLine(existCode, codeLineData.originalCodeLine);
			}
			//add to chaincode
			else if((codeLineData.originalCodeLine.indexOf('.css(') != -1 || codeLineData.originalCodeLine.indexOf('.attr(') != -1)
					 && 
					(newCode.indexOf('.css(') != -1 || newCode.indexOf('.attr(') != -1))
			{
				var checkPos = codeLineData.originalCodeLine.lastIndexOf(');');
				if(checkPos!=-1)
				{
					existCode = removeCodeLine(existCode, codeLineData.originalCodeLine);
					var newChainCode = newCode.replace(newCodeSelector, ".");
					newCode = codeLineData.originalCodeLine.substring(0, checkPos + 1) + newChainCode;
				}
				else
					Log('conditions not found, adding new code at end');
			}
		}
		return existCode + newCode;
	}	
	function optimizeBtGeneratedCode(existCode, newCode, domSelector){
		//console.warn('Checking '+domSelector+' for chaincall.', existCode, newCode);
		var newCodeSelector = '("' + domSelector + '",';
		var codeLinesBySelector = [];
		var selectorIndexes = getSelectorCodeIndexes(existCode, newCodeSelector);		
		//get original code lines for replaces
		for(var i = 0; i< selectorIndexes.length; i++)
		{
			var codeLineData = selectorIndexes[i];
			var btFuncFrom = existCode.lastIndexOf('_bt.', codeLineData.from);
			if(btFuncFrom != -1)
				codeLineData.from = btFuncFrom;
			codeLineData.originalCodeLine = existCode.substring(codeLineData.from, codeLineData.to);
		}
		//do replaces
		for(var i = 0; i< selectorIndexes.length; i++)
		{
			var codeLineData = selectorIndexes[i];
			if(codeLineData.originalCodeLine.indexOf('.moveElement(') != -1 && newCode.indexOf('.moveElement(') != -1)
			{
				existCode = removeCodeLine(existCode, codeLineData.originalCodeLine);
			}
            /*
			if(codeLineData.originalCodeLine.indexOf('.setStyle(') != -1 && newCode.indexOf('.setStyle(') != -1)
			{
				existCode = removeCodeLine(existCode, codeLineData.originalCodeLine);
			}
            */
		}
		return existCode + newCode;
	}
	function removeCodeLine(allCode, removedCode)
	{
		
		allCode = allCode.replace(removedCode + "\n", '');
		allCode = allCode.replace(removedCode, '');
		return allCode;
	}
	function getSelectorCodeIndexes(code, selectorCode){
		var startIndex = 0, searchStrLen = selectorCode.length, endOfCodePos = -1;
		var index, indices = [];
		while ((index = code.indexOf(selectorCode, startIndex)) > -1) {
			//get end of code
			endOfCodePos = code.indexOf(');', index + 1);
			if(endOfCodePos != -1)
			{
                var nextString = code.substr(endOfCodePos + 2, 2);
                while(nextString == "\\\"" || nextString == "\'")
                {
                    endOfCodePos = code.indexOf(');', endOfCodePos + 3);
                    if(endOfCodePos == -1) break;
                    nextString = code.substr(endOfCodePos + 2, 2);
                }       
				indices.push({from: index, to: endOfCodePos + 3});
				startIndex = endOfCodePos + 3;
			}
			else
				startIndex = index + searchStrLen;
		}
		return indices;
	}
	
    function MenuHideElement() {
        BlackTri.history.store('hide_element', 'elem');
        var script = generateScript('hide', This.CurrentSelector, '');
        var codeSource = 'hide';
        This.saveNewVariantCode('JS', script, codeSource, This.CurrentSelector);
		This.ExecScriptOnClient(script);

        HideClickEditorMenu();
        HideActionLayer();
    }

    function valueNotIn(val, targetArray)
    {
        var resp = false;
        for (i = 0; i < targetArray.length; i++)
            if (val == targetArray[i])
            {
                resp = true;
                break;
            }
        return !resp;
    }
    function valueIn(val, targetArray)
    {
        var resp = false;
        for (i = 0; i < targetArray.length; i++)
            if (val == targetArray[i])
            {
                resp = true;
                break;
            }
        return resp;
    }
    function getRealDocumentHeight()
    {
        return clientDocumentHeight;
    }
	this.setRealDocumentHeight = function(height){
		clientDocumentHeight = height;
	}
	
	/*
    function GetDomPath(el, isSMS)
    {
        var parts = [];
		var foundId = false;
        function processNode(o)
        {
            var childs = jQuery(o).parent().children(o.nodeName.toLowerCase());
            var idx = childs.index(o);
            var id = jQuery(o).attr('id') || '';			
            if (id != '')
			{
				//test multiple id existance
				var isMultiple = false;
				if (clientWindow[0] && typeof (clientWindow[0].checkForMultipleId) === 'function')
					isMultiple = clientWindow[0].checkForMultipleId(id);
				
				if(isMultiple)
				{
					var multipleIdx = -1;
					if (clientWindow[0] && typeof (clientWindow[0].getMultipleIdIndex) === 'function')
						multipleIdx = clientWindow[0].getMultipleIdIndex(id, el);
					parts.unshift('[id='+id+']'+(multipleIdx!=-1?':eq(' + multipleIdx + ')':''));
				}
				else
				{
					parts.unshift('#' + id);
				}
				foundId = true;
			}
            else if (childs.size() > 1)
                parts.unshift(o.nodeName.toLowerCase() + (idx!=-1?':eq(' + idx + ')':''));
            else
                parts.unshift(o.nodeName.toLowerCase());
        }
        processNode(el);
        jQuery(el).parents().not('html').each(function(i, o) {
			if(foundId) return false;
            processNode(o);
            if(isSMS){
                if(o.id){
                    return false;
                }
            }
        });
        return parts.join(' > ');
    }
	*/
    /* make public for other extensions */
		
	This.codeMirrorTextOver = function(cm,tk,mouse){
		/*
		if (clientWindow[0] && typeof (clientWindow[0].unhighLightSelector) === 'function')
			clientWindow[0].unhighLightSelector(btTenant);
		*/
		This.com.sendMessage('unhighLightSelector', btTenant);
		
		if(tk && tk.token)
		{
			var token = tk.token;
			if(token.type == 'string' && token.string != '')
			{
				var foundToken = token.string;
				
				if(foundToken.substring(0,1) == '"' && foundToken.substring(-1,1) == '"')
					foundToken = foundToken.substring(1, foundToken.length - 1);
				else if(foundToken.substring(0,1) == "'" && foundToken.substring(-1,1) == "'")
					foundToken = foundToken.substring(1, foundToken.length - 1);
					
				This.com.sendMessage('highLightSelector', foundToken, btTenant);

				/* TODO
				if (clientWindow[0] && typeof (clientWindow[0].highLightSelector) === 'function')
				{					
					return clientWindow[0].highLightSelector( foundToken, btTenant ) == true ? 1 : -1;
				}
				*/
			}
		}
	}	
    function replaceAll(find, replace, str)
    {
        return str.replace(new RegExp(find, 'g'), replace);
    }
    function bytesToSize(bytes, precision) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        var posttxt = 0;
        if (bytes == 0)
            return 'n/a';
        while (bytes >= 1024) {
            posttxt++;
            bytes = bytes / 1024;
        }
        return parseInt(bytes).toFixed(precision) + " " + sizes[posttxt];
    };

    function Log()
    {

        /* shut this off for now */
        if (window.CANCELLOG) {
            return;
        }
        if (window&&window.console&&typeof window.console.log == 'function')
        {			
            var args = arguments;
            var out = ["[EDITOR]:"];
            for (var i = 0; i < arguments.length; i++)
                out.push(arguments[i]);
			window.console.log.apply(window.console, out);
        }
    }
    this.Log = Log;


    function InitPlugins() {
        Log('Init plugins');
        /* outerHTML plugn */
        (function(jQuery) {
            jQuery.fn.extend({
                outerHTML: function(value) {
                    //remove jqsel attribute
                    // Replaces the content
                    if (typeof value === "string") {
                        var jQuerythis = jQuery(this),
                                jQueryparent = jQuerythis.parent();

                        var replaceElements = function() {

                            // For some reason sometimes images are not replaced properly so we get rid of them first
                            var jQueryimg = jQuerythis.find("img");
                            if (jQueryimg.length > 0) {
                                jQueryimg.remove();
                            }

                            var element;
                            jQuery(value).map(function() {
                                element = jQuery(this);
                                jQuerythis.replaceWith(element);
                            })

                            return element;

                        }

                        if (typeof (value) != "undefined" && value == "")
                        {
                            jQuery(this).hide();//.css("visibility", "hidden");//.html('');
                            return jQuery(this);
                        }
                        else
                        {
                            jQuery(this).show();//.css("visibility", "visible");
                            return replaceElements();
                        }

                        // Returns the value
                    } else {
                        return jQuery("<div />").append(jQuery(this).clone().removeAttr("jqsel")).html();
                    }

                }
            });
        })(jQuery);

        /*
         * getStyleObject Plugin for jQuery JavaScript Library
         * From: http://upshots.org/?p=112
         */
        (function($) {
            $.fn.getStyleObject = function() {
                var dom = this.get(0);
                var style;
                var returns = {};
                if (window.getComputedStyle) {
                    var camelize = function(a, b) {
                        return b.toUpperCase();
                    };
                    style = window.getComputedStyle(dom, null);
                    for (var i = 0, l = style.length; i < l; i++) {
                        var prop = style[i];
                        var camel = prop.replace(/\-([a-z])/g, camelize);
                        var val = style.getPropertyValue(prop);
                        returns[camel] = val;
                    }
                    ;
                    return returns;
                }
                ;
                if (style = dom.currentStyle) {
                    for (var prop in style) {
                        returns[prop] = style[prop];
                    }
                    ;
                    return returns;
                }
                ;
                return this.css();
            }
        })(jQuery);

        /*
         * http://jpaq.org/
         *
         * Copyright (c) 2011 Christopher West
         * Licensed under the MIT license.
         * http://jpaq.org/license/
         *
         * Version: 1.0.6.0011
         * Revised: April 6, 2011
         */
        (function() {
            jPaq = {
                toString: function() {
                    return "jPaq - A fully customizable JavaScript/JScript library created by Christopher West.";
                }
            };
            Array.prototype.forEach = Array.prototype.forEach || function(fn, objThis) {
                if (typeof fn != "function")
                    throw new TypeError();
                for (var i = 0, len = this.length >>> 0; i < len; i++)
                    if (i in this)
                        fn.call(objThis, this[i], i, this);
            };
            Array.prototype.reduce = Array.prototype.reduce || function(fnCallback, initial) {
                if (typeof fnCallback != "function")
                    throw new TypeError();
                // no value to return if no initial value and an empty array
                var len = this.length >>> 0, aLen = arguments.length;
                if (len == 0 && aLen == 1)
                    throw new TypeError();
                var i = 0;
                if (aLen < 2)
                    do {
                        if (i in this) {
                            initial = this[i++];
                            break;
                        }
                        // if array contains no values, no initial value to return
                        if (++i >= len)
                            throw new TypeError();
                    } while (1);
                for (; i < len; i++)
                    if (i in this)
                        initial = fnCallback.call(undefined, initial, this[i], i, this);
                return initial;
            };
        })();


    }
    ;


})(jQuery);

