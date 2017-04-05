/* START BT CODE */
window.onerror = function(error) {
	if( typeof(ShowError) == "function" )
		ShowError(error);
};
window["BlackTri"] = new (function(jQuery){
		
	//local vars
	var This = this;
		
	var BlackTriMaxOrder = window.BlackTriMaxOrder || 2;
	var TabID = '';
	var Factors = {};
	var ControlItems = {};
	var Labels = {};
	var Ids = {};
	var customTags = ['[CSS]','[JS]']
	//bg mouse over color
	var movingElementClass = "bt_moving_element";
	var mouseOverEditorMenuID = "#BTMouseOverEditorMenu";
	var customCSSPlaceholderID = "BTCustomCSSPlaceHolder";
	var customJSPlaceholderID = "BTCustomJSPlaceHolder";
	var ontopClass = "ontop";
	var tmrMouseOver = 0;
	var showad_js = '\n<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>';
	var Editor = null;
	var EditorDesign = null;
	var CurrentMode = 'DESIGN';//possible values = SOURCE or DESIGN
	var CurrentSelector = "";
	var CurrentTagName = "";
	//last element colored
	var lastOver = null;
	var CurrentIndex = -1;
	
	this.isLoaded = false;
	var $ = this.jQuery = this.$ = window.jQuery;
	this.BlackTriCombinations =  1;
	var OriginalHTML = '';
	var skipAllActions = false;
	var currentItemProperties = null;
	var minEditorWidth = 622;
	var minEditorHeight = 324;
	var btTenant = BTTenant || 'blacktri';
	var clientWindow, clientDocument;
	this.clientDocument = null;
	this.clientWindow = null;
	
	var isIE = navigator.appName.indexOf("Microsoft") != -1;	

	/* public functions */
	this.Init = function(){
		Log('Initializing global editor functions');
		Log('Current tenant: ', btTenant);
		Log('Browser is ie: ', isIE);
		
		//moved to main code
		//StoreOriginalHTML();
		//DisableEvents(true);
		//RemovePreloader();
		
		InitPlugins();
		
		PlaceHtmlElements();
		
		EnableDragResize();
		
		InitGenericFunctions();
		
		InitHtmlEditor();
		
		InitHtmlDesignEditor();
		
		//reinitialize jquery again
		var $ = this.jQuery = this.$ = window.jQuery;
	}
	
	this.GetVariantsData = function()
	{
		var returnedData = {};
		var ridx = 0;
		for(tabId in Labels)
		{
			var name = String(Labels[tabId]);
			
			var factors = {};
			var fidx = 0;
			for(selector in Factors[tabId])
			{
				factors[String(fidx++)] = {path:String(selector), html:String(Factors[tabId][selector])};
			}
								
			returnedData[String(ridx++)] = {name:name, factors:factors, id: (Ids[tabId]||0) };
		}
		return returnedData;
	}
	//var Factors = {};	var ControlItems = {};	var Labels = {};
	///BTVariantsData[tabId] = { name: variantLabel, id: 0, ControlItems:{}, Factors:{} };	
	this.AddNewTab = function(tabId, variantLabel)
	{
		Log('Add new tab', tabId, variantLabel);
		Factors[tabId] = {};
		ControlItems[tabId] = {};
		Labels[tabId] = variantLabel;
	}
	//generate next label name
	this.GetNewLabel = function(label, idx)
	{
		Log('GetNewLabel: ', label, idx);
		var newLabel = label + ' ' + idx;
		for(var i=idx;i<100;i++)
		{
			newLabel = label + ' ' + i;
			var isOk = true;
			for(tabId in Labels)
			{
				var name = String(Labels[tabId]);
				if( name == newLabel )
				{
					isOk = false;
					break;
				}
			}
			if(isOk)
				break;
		}
		return newLabel;
	}
	this.SelectTab = function(tabId)
	{
		Log('Select tab', tabId);
		TabID = tabId;
		Log('Reloading editor for Variant tab id ' + TabID);
		
		//hide action layer if open
		HideActionLayer();
		
		HideEditetElementsFrame();
		
		LoadOriginalHTML();
				
		//RemovePreloader();
		if( tabId != 'variant_0' )
		{
			ProxyMouseEvents();
			//show proxy layer (moved here)
			ShowProxyLayer();
			
			//load variant
			LoadVariant(tabId);
		}
		else
		{			
			DisableProxyMouseEvents();
			//show proxy layer only
			ShowProxyLayer();
		}
		/*
		//only show original
		if( tabId != 'variant_0' )
		{
			LoadVariant(tabId);
			DisableEvents();
			
			LoadEditorMenuHTML();
			InitHtmlEditor();
			InitHtmlDesignEditor();
			SetupMainEditor();
			
			AttachEditorMenuEvents();
			
			RepositionAllFactors();
		}
		else
		{		
			DisableEvents(true);
		}
		*/
	}
	this.RenameTab = function(tabId, name)
	{
		Log('Rename tab', tabId, name);
		Labels[tabId] = name;
		Log('Labels', Labels);
	}
	this.RemoveTab = function(tabId)	
	{		
		Log('Remove tab', tabId);
		delete Factors[tabId];
		delete ControlItems[tabId];
		delete Labels[tabId];
		delete Ids[tabId];
		Log('All remove', Factors, ControlItems, Labels);		
	}
	this.EditCustomCSS = function(tabId)
	{
		Log('Editing custom css for ' + tabId);
		CurrentSelector = '[CSS]';
		CurrentMode = 'SOURCE';
		
		OpenEditor();
	}
	this.EditCustomJS = function(tabId)
	{
		Log('Editing custom javascript for ' + (tabId || TabID) );
		CurrentSelector = '[JS]';
		CurrentMode = 'SOURCE';
		
		OpenEditor();
	}	
	//load factors throught parent window (security issue)
	this.LoadFactors = function (FactorsData)
	{
		Log('Loading factors data', FactorsData);		
		Factors = FactorsData.factors;		
		Log('Factors done!');
		Labels = FactorsData.labels;
		Log('Labels done!');
		Ids = FactorsData.ids;
		Log('Ids done!');
		Log('Building control items...');
		for(variantid in Factors)
		{
			var variant = Factors[variantid];
			for(dompath in variant)
			{
				if( valueNotIn(dompath,customTags) )
					SaveControlItem(variantid, dompath, getElementHtml(dompath));
			}
		}
		Log('Done...');
	}
	
	//save editor content back to page
	function SaveFactor(TabID, Selector, editorHtml)
	{
		//Log('Save Factor', Factors[TabID], TabID,Selector,editorHtml);
		if(typeof(Factors[TabID])=='undefined')
			Factors[TabID] = {};
		Log('Save Factor', Factors[TabID], TabID,Selector,editorHtml);
		//save variant change
		Factors[TabID][Selector] = editorHtml;
		Log('Factors', Factors);
	}
	function SaveControlItem(TabID, Selector, originalHtml)
	{
		Log('SaveControlItem(', TabID, Selector, '): ', originalHtml);
		if(valueIn(Selector,customTags)) return;
		if(!ControlItems[TabID])
			ControlItems[TabID] = {};
		//store control value just in case, only for first time
		ControlItems[TabID][Selector] = originalHtml;		
	}
	function GetControlItem(TabID, Selector)
	{
		if(TabID=='') return;
		if(typeof(ControlItems[TabID])=='undefined') return;
		return (typeof(ControlItems[TabID][Selector]) == 'undefined')?undefined:String(ControlItems[TabID][Selector]);
	}
	function GetFactor(TabID, Selector)
	{
		if(TabID=='') return;
		if(typeof(Factors[TabID])=='undefined') return;
		return (typeof(Factors[TabID][Selector])=='undefined')?undefined:String(Factors[TabID][Selector]);
	}
		
	this.editorMoveElementSaveHtml = function()
	{
		Log("Save move position");
		//clone position to the target element
		var elm = jQuery(clientDocument).find(CurrentSelector)
			
		var jsContent = GetFactor(TabID, '[JS]')||'';
		var script = generateScript('move', CurrentSelector, {left:elm.css('left'), top:elm.css('top'), position: elm.css('position')});
		if(jsContent.length>0)
			jsContent+='\n';
		jsContent+=script;
		SaveFactor(TabID, '[JS]', jsContent);

		SetEditedElementFrame(CurrentSelector);
		
		//cancel draggable and and leave element at current position
		$('#BTMoveFrame').draggable("destroy")	
		This.ExecScriptOnClient('$("'+CurrentSelector+'").draggable("destroy");');
		
		//hide frame
		$('#BTMoveFrame').hide();
		HideActionLayer();		
	}
	this.editorMoveElementCancel = function()
	{
		Log("Cancel move position");
		
		var elm = jQuery(clientDocument).find(CurrentSelector)
		
		//cancel draggable and restore original position
		$('#BTMoveFrame').draggable("destroy");
		This.ExecScriptOnClient('$("'+CurrentSelector+'").draggable("destroy");');
		
		if( currentItemProperties )
			elm.css({left: currentItemProperties.left||'', top: currentItemProperties.top||'', position: currentItemProperties.position||'' });			
		currentItemProperties = null;
		
		$('#BTMoveFrame').hide();
		$('.editor_element_outline').show();
		HideActionLayer();		
	}	
	this.editorSaveHtml = function(html)
	{
		if( CurrentSelector == "" ) return;
		
		var editorHtml = '';
		if(typeof(html)=='undefined')
		{
			Log('Current mode: ' + CurrentMode);
			if(CurrentMode=='SOURCE') 
				editorHtml = Editor.getValue();
			else if(CurrentMode=='DESIGN')
				editorHtml = EditorDesign.instanceById('BTHtmlEditor').getContent();
			
			if(CurrentSelector=='[CSS]')
			{
				Log("returned css: " + editorHtml );
				SaveFactor(TabID, CurrentSelector, editorHtml)
				ShowFactor();
				return;
			}
			else if (CurrentSelector=='[JS]')
			{
				Log("returned javascript: " + editorHtml );
				Log("save [JS] factor and reload tab" );
								
				SaveFactor(TabID, '[JS]', editorHtml);
				
				This.SelectTab(TabID);
				return;
			}
			else		
				Log("returned html: " + editorHtml );
		}
		else
		{
			Log('Current mode: Moving Element, css/design editor are disabled');		
			editorHtml = html;
			Log("returned html: " + editorHtml );
		}
		//save control value only for html, not for css and js
		if( valueNotIn(CurrentSelector,customTags) )
		{
			var ControlItem = GetControlItem(TabID, CurrentSelector);
			Log('ControlItem: ', ControlItem);
			if(typeof(ControlItem) == 'undefined')
			{
				SaveControlItem(TabID, CurrentSelector, getElementHtml(CurrentSelector));
			}
		}
		else
			Log('No control item');
		
		if(editorHtml.length!=0)
		{
			var jsContent = GetFactor(TabID, '[JS]')||'';
				
			SaveFactor(TabID, CurrentSelector, editorHtml)
			var script = generateScript('replace', CurrentSelector, editorHtml);
			if(jsContent.length>0)
				jsContent+='\n';
			jsContent+=script;			
			SaveFactor(TabID, '[JS]', jsContent);
			ShowFactor(CurrentSelector, script);
		}
		else//remove
		{
			var jsContent = GetFactor(TabID, '[JS]')||'';
			
			SaveFactor(TabID, CurrentSelector, '');
			
			var script = generateScript('replace', CurrentSelector, '');
			if(jsContent.length>0)
				jsContent+='\n';
			jsContent+=script;			
			SaveFactor(TabID, '[JS]', jsContent);
			ShowFactor(CurrentSelector, script);
			//RemoveFactor(TabID);
		}
	}
	function generateScript(type, selector, content)
	{
		var script = '';
		switch(type)
		{
			case 'replace':
				if(selector == '[CSS]')
				{
					script = '$("<style/>").html(' + jQuery.toJSON(content) + ').appendTo("head");';
				}
				else
					script = '$("' + selector +'").replaceWith(' + jQuery.toJSON(content) + ');';
			break;
			
			case 'hide':
				script = '$("' + selector +'").css("visibility","hidden");';
			break;
			
			case 'remove':
				script = '$("' + selector +'").remove();';
			break;
			
			case 'move':
				script = '$("' + selector +'").css(' + jQuery.toJSON(content) + ');';
			break;
		}
		Log('generateScript: ', type, script);
		return script;
	}	
	function RemoveFactor()
	{
		if( CurrentSelector == "" ) return;
		if(CurrentSelector=='[CSS]')
		{
			Log("css empty, removing style element");
			delete Factors[TabID][CurrentSelector];
		}
		else if(CurrentSelector=='[JS]')
		{
			Log("css empty, removing script element");
			delete Factors[TabID][CurrentSelector];
		}
		else
		{
			Log("html empty, removing(hidding) element" );
			
			delete Factors[TabID][CurrentSelector];
			delete ControlItems[TabID][CurrentSelector];
			//simulate removal by using hide
			jQuery(CurrentSelector).hide();
			hideEditedElementFrame(CurrentSelector);
		}
	}

	function RemoveEditedElementFrame(jqSel){
		jQuery("div.editor_element_outline_changed[jqsel='"+jqSel+"']").remove();
	}
	function SetEditedElementFrame(jqSel){
		Log('SetEditedElementFrame ', jqSel);
		
		RemoveEditedElementFrame(jqSel);
		
		if( clientDocument.find(jqSel).size()==0 || !clientDocument.find(jqSel).is(':visible') )
		{
			Log('Element is not visible anymore or not exists, skipping frame');
			return;
		}
		
		var el = $('<div/>').addClass('editor_element_outline_changed').attr('jqsel',jqSel);
		el.appendTo($('.editor_proxy'));

		var o = clientDocument.find(jqSel);
		var off = o.offset();
		var w = o.outerWidth();
		var h = o.outerHeight();			
		//verify width and height to avoid scroll and clips
		var tw = $('.editor_proxy_scroll').width();
		if( w > tw - 4 )
			w = tw - 4;				
		var th = $('.editor_proxy_scroll').height();			
		if( h > th - 4)
			h = th - 4;
			
		//check left offset and fix width based on that fix
		if( off.left < 3 )
		{
			w-= 2 - off.left;
			off.left = 3;
		}
		//check top offset and fix width based on that fix
		if( off.top < 3 )
		{
			h-= 2 - off.top;
			off.top = 3;
		}
		
		el.css({left: off.left - 1, top: off.top - 1, width: w + 2, height: h + 2}).show();
	}
	
	this.ExecScriptOnClient = function(script){
		if( clientWindow[0] && typeof(clientWindow[0].btApplyChanges)=='function' )
			clientWindow[0].btApplyChanges(script);		
	}
	function ShowFactor(selector, script)
	{
		var workingSelector = selector || CurrentSelector;
		var workingScript = script || '';
		
		if(valueIn(workingSelector,customTags))
		{
			Log("Show custom " + workingSelector + " change");
			var htmlContentChange = GetFactor(TabID, workingSelector) || '';			
			if(workingSelector == '[CSS]' && htmlContentChange.length > 0)
			{
				//remove old css
				clientDocument.find('#' + customCSSPlaceholderID).remove();
				//create new
				var css = jQuery('<st' + 'yle/>').attr('id',customCSSPlaceholderID).attr('type','text/css').html(htmlContentChange)
				css.appendTo(clientDocument.find('head'));
			}
			else if(workingSelector == '[JS]' && htmlContentChange.length > 0)
			{
				//htmlContentChange = htmlContentChange.replace(rex, 'BlackTri.clientDocument.find(');
				if( clientWindow[0] && typeof(clientWindow[0].btApplyChanges)=='function' )
					clientWindow[0].btApplyChanges(htmlContentChange);
				else
					Log('Could not inject domchange code into client frame!');
			}
			
			This.editorCancel();
			return;
		
		}
		else
		{
			if(workingScript!='')
			{
				Log('We have a script change, running only this code, ' + script);
				
				//remove old js
				//jQuery('#' + customJSPlaceholderID).remove();

				//replace script
				//var rex = new RegExp("\\$\\(", "g");
				//workingScript = workingScript.replace(rex, 'BlackTri.clientDocument.find(');				
				/*jQuery('<script type="text/javascript" id="'+customJSPlaceholderID+'">' + workingScript + '</script>').appendTo('head');*/
				
				if( clientWindow[0] && typeof(clientWindow[0].btApplyChanges)=='function' )
					clientWindow[0].btApplyChanges(workingScript);
				else
					Log('Could not inject workingScript code into client frame!');
				
			}
			else
			{
				
			}
		}
		
		SetEditedElementFrame(workingSelector);

		RepositionAllFactors(workingSelector);
		
		This.editorCancel();
		
		/*		
		if(valueIn(workingSelector,customTags))
		{
			Log("Show custom " + workingSelector + " change");
			var htmlContentChange = GetFactor(TabID, workingSelector) || '';
			if(workingSelector == '[CSS]' && htmlContentChange.length > 0)
			{
				//remove old css
				jQuery('#' + customCSSPlaceholderID).remove();
				//create new
				var css = jQuery('<st' + 'yle/>').attr('id',customCSSPlaceholderID).attr('type','text/css').html(htmlContentChange)
				css.appendTo(jQuery('head'));
			}
			else if(workingSelector == '[JS]' && htmlContentChange.length > 0)
			{
				//remove old js
				jQuery('#' + customJSPlaceholderID).remove();
				//create new
				jQuery('<script type="text/javascript" id="'+customJSPlaceholderID+'">' + htmlContentChange + '</script>').appendTo('head');
				//var js = jQuery('<scr' + 'ipt/>').attr('id',customJSPlaceholderID).attr('type','text/javascript').html(htmlContentChange)
				//js.appendTo(jQuery('head'));
			}
			
			This.editorCancel();
			return;
		}		
		else
		{
			Log("Show factor html change for selector ", workingSelector);
			var ControlItem = GetControlItem(TabID, workingSelector);
			
			var isGoogleAdPh = ControlItem.indexOf('google_ad_slot')>0 && ControlItem.indexOf('google_ad_client')>0;		
			var htmlContentChange = GetFactor(TabID, workingSelector);
			//attach events
			var domNodes = jQuery(htmlContentChange);

			//change whole html if we have all data inside html tag
			if(htmlContentChange.length==0)
			{
				jQuery(workingSelector).remove();
				This.editorCancel();
				return;
			}
			else if(htmlContentChange.indexOf('<')==0)
			{
				if( isGoogleAdPh )//if ga ph then change <ins> tag html
				{
					jQuery(workingSelector).html(htmlContentChange);
				}
				else
				{
					jQuery(workingSelector).replaceWith(domNodes);
					//jQuery(CurrentSelector).outerHTML(htmlContentChange);
				}
			}
			else
			{
				jQuery(workingSelector).replaceWith(domNodes);
				//jQuery(CurrentSelector).replaceWith(htmlContentChange);
			}
			
			//remove old div data
			jQuery("div.mv_edit_div[jqsel='"+workingSelector+"']").remove()
			
			var targetEl = jQuery(workingSelector);
			targetEl.attr("jqsel", CurrentSelector);
			
			RepositionAllFactors();
		}
		This.editorCancel();
		*/
	}	
	function RepositionAllFactors(workingSelector){
		Log('Recalculate position for all factors in tab id ', TabID);
		var workingSelector = workingSelector || '';
		//reposition all changes in case it moves
		for(dompath in Factors[TabID])
		{
			if( dompath != workingSelector && valueNotIn(dompath,customTags) )
			{
				if( clientDocument.find(dompath).size()==0 || !clientDocument.find(dompath).is(':visible') )
				{
					Log('Element is not visible anymore or not exists, removing frame!');
					RemoveEditedElementFrame(dompath);
				}
				else
					SetEditedElementFrame(dompath);
			}
		}
	}
	
	//close editor pop-up
	this.editorCancel = function(){
		CloseEditor();
	}
	
	/* internal stuff */

	//new pop-up function, use container selector to open
	function OpenPopup(selector, showClose, callBack, opt)
	{
		jQuery = jQuery;
		if( typeof(showClose) == "object" )
			opt = showClose;
		if( typeof(showClose) == "undefined" || typeof(showClose) == "object" ) showClose = true;
		var opts = {
				margin:15,
				showCloseButton: showClose,
				autoScale: false,
				onCleanup: function(){
							jQuery('.formError').fadeTo("fast", 0.3, function() {
								jQuery(this).remove();
							});
							if( typeof(callBack) == "function" )
								callBack();
				}
		};
		jQuery.extend( opts, opt );
		jQuery("<a/>").attr("href", selector)
		.fancybox(opts)
		.trigger("click");
	}
		
	
	function AttachEditorMenuEvents(){
		//attach hide event
		jQuery(mouseOverEditorMenuID).hover( null, HideMouseOverEditorMenu );
	}
	
	
	//move html elements in place
	function PlaceHtmlElements(){
		Log('PlaceHtmlElements to the positions');
		$('#BTMoveFrame').appendTo( '.editor_action' );
		$('#BTFactorEditPopup').appendTo( '.editor_action' );
		$('#BTMouseOverEditorMenu').appendTo( '.editor_action' );
	}
	function InitGenericFunctions(){
		
		Log('InitGenericFunctions');
		$('.editor_action').click( function(ev){
			if( !$('#BTMouseOverEditorMenu').is(':visible') || ev.target !== this )
				return;
			
			HideClickEditorMenu();
			HideActionLayer();
		});
	}
	function EnableDragResize()
	{
		Log('EnableDragResize');
		var drag = jQuery('#BTFactorEditPopup');
		drag.draggable({
			handle:'label.BTEnableDraggable',
			containment: ".editor_action",
			scroll: false
		}).resizable({
			containment: '.editor_action',
			resize: function(evt, ui){
				
				//store width & height
				storeEditorWH(ui.size.width,ui.size.height);
				
				resizeEditor(ui.size.width,ui.size.height);
			},
			start: function(evt, ui){
			},
			minWidth: minEditorWidth,
			minHeight: minEditorHeight
		})
	}
	//initialize html editor then use setValue, refresh and set size to match what you need
	function InitHtmlEditor(){
		Log('Init html editor (CodeMirror)...');
		
		Editor = CodeMirror.fromTextArea(jQuery('#BTHtmlEditor')[0], {
			//mode: "text/html", seems not to work as it should
			tabMode: "indent",
			fixedGutter: true,
			lineNumbers: true,
			lineWrapping: true,
			  onCursorActivity: function() {
				Editor.setLineClass(hlLine, null, null);
				hlLine = Editor.setLineClass(Editor.getCursor().line, null, "activeline");
			  }
		});
		var hlLine = Editor.setLineClass(0, "activeline");
	}
	function InitHtmlDesignEditor(){
		Log('Init html design editor(nicEditor)', EditorDesign);
		//reinit nic editor
		if(EditorDesign && EditorDesign.instanceById('BTHtmlEditor'))
			EditorDesign.removeInstance(jQuery('#BTHtmlEditor')[0]);						
		EditorDesign = new nicEditor({maxHeight : 210});
	}
	function LoadVariant(tabId)
	{
		Log('Load ' + tabId + ' factors');
		Log(Factors);
		if( !Factors[tabId] ) return;
		
		CurrentSelector = '[CSS]';
		ShowFactor();
		CurrentSelector = '[JS]';
		ShowFactor();
		
		CurrentSelector = '';
		
		RepositionAllFactors();
		/*
		for(selector in Factors[tabId])
		{
			CurrentSelector = selector;
			ShowFactor();
		}
		*/
	}
	function DisableEvents(original)
	{
		var disableAll = original || false;
		Log('DisableEvents, Original tab: ', disableAll);
		//remove events
		var elements = jQuery("body *:not(.BTSkipElement):visible");
		
		//cancel links and clicks
		elements.unbind('click');
		if( disableAll )
		{
			elements
				.removeAttr('href')
				.removeAttr('onclick')
				.removeAttr('target')
				.removeAttr('action')
		}
		//disable forms
		jQuery('body form').submit(function(){return false;})
	}
	
	/*
	function SetupMainEditor()
	{
		//attach hover to all elements in page and make mouse over bg animation
		jQuery("body *:not(.BTSkipElement):visible")
			.hover( elementMouseOver, elementMouseOut )
			.click( ElementClick );			
	}
	*/
	
	/*
	var lastMouseMove = null;
	this.ProxyMouseMove = function(x,y){
		var o = document.elementFromPoint(x, y);
		if(lastMouseMove==o) return;
		/*
			X - window.pageXOffset, Y - window.pageYOffset
			
			if (receiver.nodeType == 3) { // Opera
				receiver = receiver.parentNode;
			}
		/
		Log('Mouse move ', x,' ' , y, ' ', o);
		if(lastMouseMove!=null)
			hideMouseOverFrame(lastMouseMove);
		
		lastMouseMove = o;
		setupMouseOverFrame(o);		
	}
	*/
	
	//MAIN UPDATES
	function ShowActionLayer(){
		Log('ShowActionLayer');
		$('.editor_action').show()
		skipAllActions = true;
		/*
		jQuery('#BTFactorEditPopup').find('.CodeMirror-wrap').show();
		//delayed refresh
		setTimeout( function(){
			Editor.refresh();
		}, 150);
		*/
	}
	function HideActionLayer(){
		Log('HideActionLayer');
		$('.editor_action').hide()
		skipAllActions = false;
	}
	function HideEditetElementsFrame(){
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
		if( $('.editor_proxy > div.editor_proxy_scroll').size() == 0 )
			$('.editor_proxy').append( '<div class="editor_proxy_scroll"/>');
		if( $('.editor_proxy > div.editor_element_outline').size() == 0 )
			$('.editor_proxy').append( '<div class="editor_element_outline"/>');
		
		var cph = $('.editor_proxy > div.editor_proxy_scroll');
		
		//helper function, used local
		function setProxyLayerHeight(){
			var clientHeight = getRealDocumentHeight(clientDocument) + 1;
			Log('Found client height: ', clientHeight);			
			//reset height
			cph.height(clientHeight);
		}
		setProxyLayerHeight();
		//reset scroll top
		cph.scrollTop(0);
				
		//proxy mouse scroll
		Log('Proxy mouse scroll');
		$('.editor_proxy').unbind('scroll').bind('scroll', function(evt){
			if( isIE && !$(this).is(':visible') ) return;
			clientDocument.scrollTop( $(this).scrollTop() );
		});
		
		//proxy mouse over
		var lastElementHovered = null;
		CurrentSelector = '';
		CurrentTagName = '';
		skipAllActions = false;
		
		Log('Set-up mouse over event and client html element dettection');		 
		$('.editor_proxy > div.editor_proxy_scroll').unbind('click').click( function(evt){
			if(skipAllActions) return;
			Log('Mouse proxy layer clicked');
			if(lastElementHovered!=null)
			{
				//get dom path
				CurrentSelector = GetDomPath(lastElementHovered);
				CurrentTagName = (lastElementHovered.tagName || '').toLowerCase();
				Log('Found selector: ', CurrentSelector);
				
				OpenClickEditorMenu	(evt, this);			
				//OpenEditor();
				
			}
			else
				Log('Clicked element is null, skipping');
		});
		
		
		//helper function used local
		function internalPositionHoverFrame(o){
			var el = $('.editor_element_outline');
			var o = $(o);
			var off = o.offset();			
			var w = o.outerWidth();
			var h = o.outerHeight();			
			//verify width and height to avoid scroll and clips
			var tw = $('.editor_proxy_scroll').width();
			if( w > tw - 4 )
				w = tw - 4;				
			var th = $('.editor_proxy_scroll').height();			
			if( h > th - 4)
				h = th - 4;
				
			//check left offset and fix width based on that fix
			if( off.left < 3 )
			{
				w-= 2 - off.left;
				off.left = 3;
			}
			//check top offset and fix width based on that fix
			if( off.top < 3 )
			{
				h-= 2 - off.top;
				off.top = 3;
			}
			
			el.css({left: off.left - 1, top: off.top - 1, width: w + 2, height: h + 2}).show();
		}		
		//mouse move events here
		$('.editor_proxy > div.editor_proxy_scroll').unbind('mousemove').mousemove( function(evt){
			if(skipAllActions) return;
			var off = $(this).offset();			
			var posx, posy;
			if (evt.pageX || evt.pageY) {
				posx = evt.pageX - clientDocument[0].body.scrollLeft - clientDocument[0].documentElement.scrollLeft;
				posy = evt.pageY - clientDocument[0].body.scrollTop - clientDocument[0].documentElement.scrollTop;
			}
			else if (evt.clientX || evt.clientY) {
				posx = evt.clientX - clientDocument[0].body.scrollLeft - clientDocument[0].documentElement.scrollLeft;
				posy = evt.clientY - clientDocument[0].body.scrollTop - clientDocument[0].documentElement.scrollTop;
			}
			//fix real mouse pos
			posx-=off.left;
			posy-=off.top;
			
			if( isIE )
				$(this).parent().hide();			
			//get element from mouse pos
			var o = clientDocument[0].elementFromPoint(posx, posy);
			//if( isIE )
			$(this).parent().show().scrollTop( clientDocument.scrollTop() );
				
			//if we are on the same element skip
			if( lastElementHovered == o ) return;
			
			//remove outline
			//if(lastElementHovered!=null)
			//	$(lastElementHovered).css('outline','');
				
			if(o){
				internalPositionHoverFrame(o);
				//$(o).css('outline','2px solid #F00');
				lastElementHovered = o;
			}
		})
		
					 
		Log('frameWindow: ', clientWindow);
		Log('frameDocument: ', clientDocument);
		
	}
		
	this.ClientStoreOriginalHtml = function(script){
		if( clientWindow[0] && typeof(clientWindow[0].btStoreOriginalHtml)=='function' )
			return clientWindow[0].btStoreOriginalHtml();
		else return [];
	}
	this.ClientLoadOriginalHtml = function(script){
		if( clientWindow[0] && typeof(clientWindow[0].btLoadOriginalHtml)=='function' )
			return clientWindow[0].btLoadOriginalHtml();
		else return [];
	}	
	function StoreOriginalHTML()
	{
		Log('Save original body html and set objects');
		var frameWindow = jQuery('#frame_editor')[0].contentWindow;
		
		clientWindow = jQuery(frameWindow);
		clientDocument = jQuery(frameWindow.document);
		This.clientDocument = clientDocument[0];
		This.clientWindow = clientWindow[0];
		
		clientDocument.find('#btLoadingScript').remove();
		
		This.ClientStoreOriginalHtml();
	}
	function LoadOriginalHTML()
	{
		Log('Load original body html');
		
		This.ClientLoadOriginalHtml();
		
		//remove old css
		clientDocument.find('#' + customCSSPlaceholderID).remove();
	}
	function ShowProxyLayer()
	{
		Log('Show proxy layer');
		$('.editor_proxy').show();
	}
	function HideProxyLayer()
	{
		Log('Hide proxy layer');
		$('.editor_proxy').hide();
	}
	
	this.EnableEditor = function()
	{
		/* this functions enables editor functionality */
		Log('Enable editor');
		ShowProxyLayer();		
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
	
	this.CreateNewTest = function(url)
	{
		/* this function creates new test */
		Log('CreateNew editor for url ', url);
		currentUrl = url;
		//clear variants and
		Log('Remove old objects and create new ones');
		
		delete(Factors);
		delete(ControlItems);
		delete(Labels);
		delete(Ids);
				
		TabID = '';
		Factors = {};
		ControlItems = {};
		Labels = {};
		Ids = {};
		
		StoreOriginalHTML();
	}
	this.LoadExistingTest = function(url)
	{
		Log('Load editor for url ', url);
		currentUrl = url;
		//clear variants and
		var notFound = false;
		if(typeof(window.BTVariantsData)=='undefined') notFound = true;
		if(notFound)
		{
			Log('Editor data should exist in page! Please check and try again!');
			return;
		}		
		
		//cleaning up
		delete(Factors);
		delete(ControlItems);
		delete(Labels);
		delete(Ids);
		
		Log('Load objects from page');
		TabID = '';
		Factors = window.BTVariantsData["factors"];
		ControlItems = {};
		Labels = window.BTVariantsData["labels"];
		Ids = window.BTVariantsData["ids"];
		
		StoreOriginalHTML();
	}
	
	function RemovePreloader()
	{
		RemoveEditorPreloader()
	}	
	function CloseEditor()
	{
		Log('CloseEditor');
		HideActionLayer();		
		//hide editor window
		jQuery('#BTFactorEditPopup').hide();
	}
	function OpenEditor()
	{
		//hide mouse editor menu
		HideClickEditorMenu();
		
		ShowActionLayer();
		
		var html = getElementHtml(CurrentSelector);
		Log("OpenEditor Selector: " + CurrentSelector, "Content: ", html);
		
		//show editor window
		jQuery('#BTFactorEditPopup').show();
		
		//hide all labels
		jQuery('#BTFactorEditPopup > label').hide();
		jQuery('.BTjQueryPath').css({'visibility':'hidden'});
		jQuery('#BTFactorEditPopup .BTEditorModes').show();
		if(CurrentSelector=='[CSS]')
		{
			jQuery('#BTFactorEditPopup > label.BTFactorEditPopupCss').show();
			jQuery('#BTFactorEditPopup .BTEditorModes').hide();
			Log('Html editor set css mode');
			
			jQuery('#BTHtmlEditor').val(html);			
			Editor.setOption('mode', 'css');
			
			CurrentMode = 'SOURCE';
			This.editorActionSetSourceMode();
		}
		else if(CurrentSelector=='[JS]')
		{
			jQuery('#BTFactorEditPopup > label.BTFactorEditPopupJs').show();
			jQuery('#BTFactorEditPopup .BTEditorModes').hide();
			Log('Html editor set javascript mode');
			
			jQuery('#BTHtmlEditor').val(html);
			Editor.setOption('mode', 'javascript');
						
			CurrentMode = 'SOURCE';
			This.editorActionSetSourceMode();
		}
		else
		{
			jQuery('#BTFactorEditPopup > label.BTFactorEditPopupHtml').show();
			Log('Html editor set html mode');
			
			//check for label
			//if( clientDocument.find(CurrentSelector)[0].tagName == 'LABEL'
			//reinit nic editor
			Log('Current tag name: ', CurrentTagName);
			if( valueIn(CurrentTagName, ['label']) )
			{
				jQuery('#BTHtmlEditor').val(html);				
				CurrentMode = 'SOURCE';
				This.editorActionSetSourceMode();
			}
			else
			{
				if(EditorDesign.instanceById('BTHtmlEditor'))
					EditorDesign.removeInstance(jQuery('#BTHtmlEditor')[0]);
					
				jQuery('#BTHtmlEditor').val(html);
				
				CurrentMode = 'DESIGN';
				This.editorActionSetDesignMode();
			}			
		}
		
		
		//resize editor to be sure
		var size = getEditorWH();
		resizeEditor(size.width, size.height);
		//center first time
		centerEditor();		
	}
	function OpenClickEditorMenu(evt, clickObj)
	{
		ShowActionLayer();
				
		Log('OpenClickEditorMenu');
		var menu = jQuery('#BTMouseOverEditorMenu');
		
		var off = $(clickObj).offset();			
		var posx, posy;
		
		
		if (evt.pageX || evt.pageY) {
			posx = evt.pageX - clientDocument[0].body.scrollLeft - clientDocument[0].documentElement.scrollLeft;
			posy = evt.pageY - clientDocument[0].body.scrollTop - clientDocument[0].documentElement.scrollTop;
		}
		else if (evt.clientX || evt.clientY) {
			posx = evt.clientX - clientDocument[0].body.scrollLeft - clientDocument[0].documentElement.scrollLeft;
			posy = evt.clientY - clientDocument[0].body.scrollTop - clientDocument[0].documentElement.scrollTop;
		}
		
		//fix real mouse pos
		posx-=off.left;
		posy-=off.top;
		/*
		var name = 'Height';
		var bh = Math.max(
					Math.max(clientDocument[0].body["scroll" + name], clientDocument[0].documentElement["scroll" + name]),
					Math.max(clientDocument[0].body["offset" + name], clientDocument[0].documentElement["offset" + name])
		)
		name = 'Width';
		var bw = Math.max(
					Math.max(clientDocument[0].body["scroll" + name], clientDocument[0].documentElement["scroll" + name]),
					Math.max(clientDocument[0].body["offset" + name], clientDocument[0].documentElement["offset" + name])
		)
		*/
		var bh = $(clientWindow[0]).height();
		var bw = $(clientWindow[0]).width();
		
		var dxMargin = 20;
		if(posx + menu.width() + dxMargin > bw )
			posx = bw - menu.width() - dxMargin;
		if(posy + menu.height() + dxMargin > bh)
			posy = bh - menu.height() - dxMargin;
		
		menu.css({left:posx, top:posy}).show();
	}
	
	function HideClickEditorMenu()
	{
		Log('HideClickEditorMenu');		
		var menu = jQuery('#BTMouseOverEditorMenu');
		menu.hide();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
	//external function to edit html
	this.editorActionSetSourceMode = function(copyContent)
	{
		//skip if we are in editor and with same mode
		if(CurrentMode=='SOURCE' && typeof(copyContent)!='undefined')
			return;
			
		Log('Set editor text mode');
		var content = jQuery('#BTFactorEditPopup');
		
		content.find('.BTEditorModes a').removeClass('selected');
		content.find('.BTEditorModes a:eq(0)').addClass('selected');
		
		//copy content from nic editor
		if(typeof(copyContent)!='undefined' && EditorDesign.instanceById('BTHtmlEditor'))
		{
			Log('Copy NicEditor content');
			var nicHtml = EditorDesign.instanceById('BTHtmlEditor').getContent();
			content.find('#BTHtmlEditor').val(nicHtml)
			Editor.setValue(nicHtml);
		}
		else
		{
			//copy info from editor placeholder
			Editor.setValue(content.find('#BTHtmlEditor').val());
		}
		//remove nick editor
		EditorDesign.removeInstance(content.find('#BTHtmlEditor')[0]);
		
		//remove design editor
		content.find('#BTHtmlEditor').removeClass('BTNicEditor');
				
		//show codemirror
		Editor.setSize(minEditorWidth, minEditorHeight);
		content.find('.CodeMirror-wrap').show();

		CurrentMode = 'SOURCE';
		
		var size = getEditorWH();
		resizeEditor(size.width, size.height);
	}
	this.editorActionSetDesignMode = function(copyContent)
	{
		//skip if we are in editor and with same mode
		if(CurrentMode=='DESIGN' && typeof(copyContent)!='undefined')
			return;
			
		Log('Set editor design mode');
		var content = jQuery('#BTFactorEditPopup');
		
		jQuery('#BTFactorEditPopup .BTEditorModes a').removeClass('selected');
		jQuery('#BTFactorEditPopup .BTEditorModes a:eq(1)').addClass('selected');
		
		//hide code mirror
		content.find('.CodeMirror-wrap').hide();
		
		//copy content from codemirror
		if(typeof(copyContent)!='undefined')
		{
			Log('Copy CodeMirror content');
			content.find('#BTHtmlEditor').val(Editor.getValue())
		}
		
		if(content.find('#BTHtmlEditor').size()>0)
		{
			content.find('#BTHtmlEditor').addClass('BTNicEditor');
			EditorDesign.panelInstance(content.find('#BTHtmlEditor')[0]);
		}
		else
			Log('Editor error, couldnt find content.find(#BTHtmlEditor)');
		CurrentMode = 'DESIGN';
		
		var size = getEditorWH();
		resizeEditor(size.width, size.height);
	}

	
	this.menuActionEditHtml = function()
	{		
		//moved to pop-up load load This.editorActionSetDesignMode() because of NicEditor
		OpenEditor();
	}
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
		MenuMoveElement();
	}
	//internal call
	function MenuMoveElement()
	{
		Log('Move element: ' + CurrentSelector);

		//hide mouse editor menu
		HideClickEditorMenu();
		
		
		var el = $('.editor_element_outline');
		var calculatedTop = parseFloat(el.css('top').replace('px','')) - clientDocument.scrollTop();
		var oPos = el.offset();
		$('#BTMoveFrame')
			.css({left: el.css('left'), top: calculatedTop, width: el.width(), height: el.height()})
			.show()
			.draggable({
				containment: $('#BTMoveFrame').parent(),
				scroll: false,
				start:MenuMoveElement_dragStart, 
				stop:MenuMoveElement_dragStop,
				drag:MenuMoveElement_drag
			});		
		
		//enable draggable on client frame
		//This.ExecScriptOnClient('$("'+CurrentSelector+'").parent().css({overflow:"auto", });');
		This.ExecScriptOnClient('$("'+CurrentSelector+'").draggable({cancel:""});');
		
		//get moved element reference
		if( clientWindow[0] && typeof(clientWindow[0].btSetCurrentElement)=='function' )
				clientWindow[0].btSetCurrentElement(CurrentSelector);
		
		var movedElement = jQuery(clientDocument).find(CurrentSelector);
		//save current item original properties
		currentItemProperties = { left: movedElement.css('left'), top: movedElement.css('top'), position: movedElement.css('position') };
		
		//hide outline
		$('.editor_element_outline').hide();
	
		/*
		function fixDraggableTopMousePos(evt){
			//fix mouse position
			var posy;
			if (evt.pageY) {
				posy = - clientDocument[0].body.scrollTop + clientDocument[0].documentElement.scrollTop;
			}
			else if (evt.clientY) {
				posy = - clientDocument[0].body.scrollTop + clientDocument[0].documentElement.scrollTop;
			}
			return posy;
		}
		*/
		
		//draggable helper functions
		function MenuMoveElement_drag(ev, ui)
		{
			if( clientWindow[0] && typeof(clientWindow[0].btDrag)=='function' )
				clientWindow[0].btDrag({clientX:ui.position.left, clientY: ui.position.top});
		}
		function MenuMoveElement_dragStart(ev, ui)
		{
			Log('Drag start');
			if( clientWindow[0] && typeof(clientWindow[0].btDragStart)=='function' )
				clientWindow[0].btDragStart({clientX:ui.position.left, clientY: ui.position.top});
		}
		function MenuMoveElement_dragStop(ev, ui)
		{
			Log('Drag stop');
			if( clientWindow[0] && typeof(clientWindow[0].btDragStop)=='function' )
				clientWindow[0].btDragStop({clientX:ui.position.left, clientY: ui.position.top});
		}
	}
	
	function MenuRemoveElement()
	{
		Log('Remove element: ' + CurrentSelector);
		
		var jsContent = GetFactor(TabID, '[JS]')||'';
		var script = generateScript('remove', CurrentSelector, '');
		if(jsContent.length>0)
			jsContent+='\n';
		jsContent+=script;
		
		SaveFactor(TabID, '[JS]', jsContent);
		
		//ShowFactor(CurrentSelector, script);
		
		//hide element
		jQuery(clientDocument).find(CurrentSelector).remove();
		
		RemoveEditedElementFrame(CurrentSelector);
		
		HideClickEditorMenu();
		
		HideActionLayer();
	}
	function MenuHideElement()
	{
		Log('Hide element: ' + CurrentSelector);
				
		//SaveFactor(TabID, CurrentSelector, editorHtml)
		var jsContent = GetFactor(TabID, '[JS]')||'';
		var script = generateScript('hide', CurrentSelector, '');
		if(jsContent.length>0)
			jsContent+='\n';
		jsContent+=script;
		SaveFactor(TabID, '[JS]', jsContent);

		//ShowFactor(CurrentSelector, script);
		
		//hide element
		jQuery(clientDocument).find(CurrentSelector).css('visibility','hidden');
		
		RemoveEditedElementFrame(CurrentSelector);

		HideClickEditorMenu();
		
		HideActionLayer();
	}

		
	/* helper functions, modified to handle custom CSS and JS */
	function centerEditor(){
		Log('centerEditor');
		var ph = jQuery('#BTFactorEditPopup');
		var w = jQuery('.editor_action').width();
		var h = jQuery('.editor_action').height();
		var top = (h - ph.height())/2;
		var left = (w - ph.width())/2;
		if(top<=30) top = 30;
		
		ph.css( {left: left, top: top} );
	}
	function resizeEditor(w, h){
		//Log('resizeEditor ',w, h);
		if(btTenant=="etracker")
		{
			jQuery('#BTFactorEditPopup').width(w).height(h);
			if(CurrentMode == 'DESIGN')
			{
				jQuery('.nicEdit-main').width(w - 8).css('min-height', h - 134);
				jQuery('#BTFactorEditPopup > div:eq(3)').width(w).css('min-height', h - 125);
				jQuery('#BTFactorEditPopup > div:eq(2)').width(w);
			}
			else if(CurrentMode == 'SOURCE')
			{
				Editor.setSize(w, h - 100);
				jQuery('#BTFactorEditPopup').find('.CodeMirror-wrap').show();
				//delayed refresh
				//setTimeout( function(){
				Editor.refresh();
				//}, 150);
			}
		}
		else//blacktri & others
		{
			jQuery('#BTFactorEditPopup').width(w).height(h);
			if(CurrentMode == 'DESIGN')
			{
				jQuery('.nicEdit-main').width(w - 30).css('min-height', h - 127);
				jQuery('#BTFactorEditPopup > div:eq(2)').width(w - 22).css('min-height', h - 114);
				jQuery('#BTFactorEditPopup > div:eq(1)').width(w - 22);
			}
			else if(CurrentMode == 'SOURCE')
			{
				Editor.setSize(w - 22, h - 89);
				jQuery('#BTFactorEditPopup').find('.CodeMirror-wrap').show();
				//delayed refresh
				//setTimeout( function(){
				Editor.refresh();
				//}, 150);
			}
		}
	}		
	function storeEditorWH(w,h)
	{
		//store width & height
		var o = {};
		o[btTenant + "_btew"] = new String(w);
		o[btTenant + "_bteh"] = new String(h);
		jQuery.Storage.set(o);
	}
	function getEditorWH()
	{
		var w = parseInt(jQuery.Storage.get(btTenant + "_btew"));
		var h = parseInt(jQuery.Storage.get(btTenant + "_bteh"));
		if(isNaN(w) || w < minEditorWidth)
			w = minEditorWidth;
		if(isNaN(h) || h < minEditorHeight)
			h = minEditorHeight;			
		return {'width': w, 'height':h}
	}
	function getElementHtml(jqSel)
	{
		var html = "";
		if(TabID=='') return html;
		if(valueIn(jqSel,customTags))
		{
			var htmlContentChange = GetFactor(TabID, jqSel);
			if(typeof(htmlContentChange)=='undefined')
				htmlContentChange = '';
			Log('getElementHtml: ', htmlContentChange)
			return htmlContentChange;
		}
		else
		{
			var element = jQuery(clientDocument).find(jqSel);
			//remove skipping elements
			var clone = element.clone().css('outline','');
			if( jQuery.trim(clone.attr('style')) =='')
				clone.removeAttr('style');
			//get html
			html = clone.outerHTML();		
			//filter google add
			if( clone.hasClass("google_ad_ph") )
			{
				html = clone.html();
				//remove google ad slot
				html = html.replace(/google_ad_slot\s*=\s*"\d+"$/i,'');
				//add google js include here
				html += showad_js;
			}
			Log('getElementHtml: ', html)
			return html
		}
		Log('getElementHtml: empty')
		return '';
	}
	function valueNotIn(val, targetArray)
	{
		var resp = false;
		for(i=0; i< targetArray.length; i++)
			if( val == targetArray[i] )
			{
				resp = true;
				break;
			}
		return !resp;
	}
	function valueIn(val, targetArray)
	{
		var resp = false;
		for(i=0; i< targetArray.length; i++)
			if( val == targetArray[i] )
			{
				resp = true;
				break;
			}
		return resp;
	}
	function getRealDocumentHeight(doc)
	{
		var body = doc.find('body')[0];
		var html = doc.find('html')[0];
		
		var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
		return height;
	}	
	function getSafeSelectorUrlString(data)
	{
		data = data.replace(/ /g, '{s}');
		data = data.replace(/#/g, '{d}');
		data = data.replace(/\(/g, '{o}');
		data = data.replace(/\)/g, '{c}');
		return data;
	}
	function GetDomPath(el)
	{
		var parts = [];		
		function processNode(o)
		{
			var childs = jQuery(o).parent().children( o.nodeName.toLowerCase() );														
			var idx = childs.index(o);
			var id = jQuery(o).attr('id') || '';
			if(id!='')
				parts.unshift( o.nodeName.toLowerCase() + '#' + id );
			else if(childs.size()>1)
				parts.unshift( o.nodeName.toLowerCase() + ':eq(' + idx + ')' );
			else
				parts.unshift( o.nodeName.toLowerCase() );								
		}		
		processNode(el);
		jQuery(el).parents().not('html').each(function(i, o){
			processNode(o);
		});

		return parts.join(' > ');
	}
	function replaceAll(find, replace, str)
	{
	  return str.replace(new RegExp(find, 'g'), replace);
	}		
	function bytesToSize(bytes, precision) {
		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		var posttxt = 0;
		if (bytes == 0) return 'n/a';
		while( bytes >= 1024 ) {
			posttxt++;
			bytes = bytes / 1024;
		}
		return parseInt(bytes).toFixed(precision) + " " + sizes[posttxt];
	};
	
	function Log()
	{
		if(window.console)
		{
			var args = arguments;
			var out = [];
			for(var i=0;i<args.length;i++)
				out.push('args[' + i + ']');			
			eval( 'window.console.log("[EDITOR]:",' + out.join(',') + ')');
		}
	}
	this.Log = Log;





























	
	/* mouse over */
	
	function elementMouseOver(o){
		
		if(skipAllActions) return;
		
		o.isDefaultPrevented() 	
		o.stopPropagation();
		
		setupMouseOverFrame(this);
		
		jQuery("div.mv_edit_div").hide();
		if( lastOver != null )
		{
			//restoreElementPosition(lastOver);
		}
		//if we are over fator item just show and return;
		if( jQuery(this).hasClass("bg_editor") )
		{
			positionEditLayer(this, true);
			return;
		}
				
		lastOver = jQuery(this);
	}
	function elementMouseOut(o){
		if(skipAllActions) return;
		
		//jQuery(this).removeClass(mouseOverClass);
		//restoreElementPosition(this);
		hideMouseOverFrame(o);
	};
	function setupMouseOverFrame(o){
		var mofTop = jQuery('.BTMouseOverTop');
		var mofBottom = jQuery('.BTMouseOverBottom');
		var mofLeft = jQuery('.BTMouseOverLeft');
		var mofRight = jQuery('.BTMouseOverRight');

		var offset = jQuery(o).offset();
		var w = jQuery(o).outerWidth();
		var h = jQuery(o).outerHeight();
		
		mofTop.hide()
			.width(w + 8)
			.height(1)
			.css({left: offset.left - 4, top: offset.top - 6})
			.show();
		mofBottom.hide()
			.width(w + 8)
			.height(1)
			.css({left: offset.left - 4, top: offset.top + h + 2})
			.show();
			
		mofLeft.hide()
			.width(1)
			.height(h + 8)
			.css({left: offset.left - 4, top: offset.top - 5})
			.show();
		mofRight.hide()
			.width(1)
			.height(h + 8)
			.css({left: offset.left + w + 2, top: offset.top - 5})
			.show();		
	}
	function hideMouseOverFrame(o)
	{
		//Log('hideMouseOverFrame: ', CurrentSelector);
		if(CurrentSelector=='')
			jQuery('.BTMouseOverTop, .BTMouseOverBottom, .BTMouseOverLeft, .BTMouseOverRight').hide();
	}
	
	function setupEditedElementFrame(jqSel)
	{
		if(jqSel=="" || jqSel==null || typeof(jqSel)=='undefined' || valueIn(jqSel,customTags) ) return;
		Log('Create edited element frame setupEditedElementFrame for ' + jqSel);
		var mofTop = mofBottom = mofLeft = mofRight = null;
		//check for attached edited frame
		if(jQuery('.BTEditedElementTop[jqsel="' + jqSel + '"]').size()>0)
		{
			Log('Edited frame found, getting existing elements');
			mofTop = jQuery('.BTEditedElementTop[jqsel="' + jqSel + '"]');
			mofBottom = jQuery('.BTEditedElementBottom[jqsel="' + jqSel + '"]');
			mofLeft = jQuery('.BTEditedElementLeft[jqsel="' + jqSel + '"]');
			mofRight = jQuery('.BTEditedElementRight[jqsel="' + jqSel + '"]');
		}
		else
		{
			Log('Edited frame not found, creating from existing elements');
			mofTop = jQuery('#BTHtmlTemplatesPlaceholder > .BTEditedElementTop').clone(false, true).attr('jqsel', jqSel).appendTo('body');
			mofBottom = jQuery('#BTHtmlTemplatesPlaceholder > .BTEditedElementBottom').clone(false, true).attr('jqsel', jqSel).appendTo('body');
			mofLeft = jQuery('#BTHtmlTemplatesPlaceholder > .BTEditedElementLeft').clone(false, true).attr('jqsel', jqSel).appendTo('body');
			mofRight = jQuery('#BTHtmlTemplatesPlaceholder > .BTEditedElementRight').clone(false, true).attr('jqsel', jqSel).appendTo('body');
		}
		var o = jQuery(jqSel);
		var offset = jQuery(o).offset();
		var w = jQuery(o).outerWidth() + 1;
		var h = jQuery(o).outerHeight() + 1;
		
		mofTop.hide()
			.width(w)
			.height(0)
			.css({left: offset.left, top: offset.top - 2})
			.show();
		mofBottom.hide()
			.width(w)
			.height(0)
			.css({left: offset.left, top: offset.top + h - 2})
			.show();
			
		mofLeft.hide()
			.width(0)
			.height(h)
			.css({left: offset.left, top: offset.top - 1})
			.show();
		mofRight.hide()
			.width(0)
			.height(h)
			.css({left: offset.left + w - 2, top: offset.top - 1})
			.show();		
	}
	function hideEditedElementFrame(jqSel)
	{
		if(jqSel=="" || jqSel==null || typeof(jqSel)=='undefined') return;
		Log('Remove edited element frame setupEditedElementFrame for ' + jqSel);
		jQuery('.BTEditedElementTop[jqsel="' + jqSel + '"], .BTEditedElementBottom[jqsel="' + jqSel + '"], .BTEditedElementLeft[jqsel="' + jqSel + '"], .BTEditedElementRight[jqsel="' + jqSel + '"]').remove();
	}




	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function InitPlugins() {
			Log('Init plugins');
			/* outerHTML plugn */
			(function(jQuery){				  
				jQuery.fn.extend({
					outerHTML : function( value ){
						//remove jqsel attribute
						// Replaces the content
						if( typeof value === "string" ){
							var jQuerythis = jQuery(this),
								jQueryparent = jQuerythis.parent();
								
							var replaceElements = function(){
								
								// For some reason sometimes images are not replaced properly so we get rid of them first
								var jQueryimg = jQuerythis.find("img");
								if( jQueryimg.length > 0 ){
									jQueryimg.remove();
								}
								
								var element;
								jQuery( value ).map(function(){
									element = jQuery(this);
									jQuerythis.replaceWith( element );
								})
								
								return element;
								
							}
							
							if( typeof(value) != "undefined" &&  value == "")
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
						}else{
							return jQuery("<div />").append(jQuery(this).clone().removeAttr("jqsel")).html();
						}
				
					}
				});
			})(jQuery);

			/*
			 * getStyleObject Plugin for jQuery JavaScript Library
			 * From: http://upshots.org/?p=112
			 */			
			(function($){
				$.fn.getStyleObject = function(){
					var dom = this.get(0);
					var style;
					var returns = {};
					if(window.getComputedStyle){
						var camelize = function(a,b){
							return b.toUpperCase();
						};
						style = window.getComputedStyle(dom, null);
						for(var i = 0, l = style.length; i < l; i++){
							var prop = style[i];
							var camel = prop.replace(/\-([a-z])/g, camelize);
							var val = style.getPropertyValue(prop);
							returns[camel] = val;
						};
						return returns;
					};
					if(style = dom.currentStyle){
						for(var prop in style){
							returns[prop] = style[prop];
						};
						return returns;
					};
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
				toString : function() {
					return "jPaq - A fully customizable JavaScript/JScript library created by Christopher West.";
				}
			};
			Array.prototype.forEach = Array.prototype.forEach || function(fn, objThis) {
				if(typeof fn != "function")
					throw new TypeError();
				for(var i = 0, len = this.length >>> 0; i < len; i++)
					if(i in this)
						fn.call(objThis, this[i], i, this);
			};
			Array.prototype.reduce = Array.prototype.reduce || function(fnCallback, initial) {
				if(typeof fnCallback != "function")
					throw new TypeError();
				// no value to return if no initial value and an empty array
				var len = this.length >>> 0, aLen = arguments.length;
				if(len == 0 && aLen == 1)
					throw new TypeError();
				var i = 0;
				if(aLen < 2)
					do {
						if (i in this) {
							initial = this[i++];
							break;
						}
						// if array contains no values, no initial value to return
						if(++i >= len)
							throw new TypeError();
					} while(1);
				for(; i < len; i++)
					if(i in this)
						initial = fnCallback.call(undefined, initial, this[i], i, this);
				return initial;
			};
			})();
						
			/**
			 * Storage plugin
			 * Provides a simple interface for storing data such as user preferences.
			 * Storage is useful for saving and retreiving data from the user's browser.
			 * For newer browsers, localStorage is used.
			 * If localStorage isn't supported, then cookies are used instead.
			 * Retrievable data is limited to the same domain as this file.
			 *
			 * Usage:
			 * This plugin extends jQuery by adding itself as a static method.
			 * $.Storage - is the class name, which represents the user's data store, whether it's cookies or local storage.
			 *             <code>if ($.Storage)</code> will tell you if the plugin is loaded.
			 * $.Storage.set("name", "value") - Stores a named value in the data store.
			 * $.Storage.set({"name1":"value1", "name2":"value2", etc}) - Stores multiple name/value pairs in the data store.
			 * $.Storage.get("name") - Retrieves the value of the given name from the data store.
			 * $.Storage.remove("name") - Permanently deletes the name/value pair from the data store.
			 *
			 * @author Dave Schindler
			 *
			 * Distributed under the MIT License
			 *
			 * Copyright (c) 2010 Dave Schindler
			 *
			 * Permission is hereby granted, free of charge, to any person obtaining a copy
			 * of this software and associated documentation files (the "Software"), to deal
			 * in the Software without restriction, including without limitation the rights
			 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
			 * copies of the Software, and to permit persons to whom the Software is
			 * furnished to do so, subject to the following conditions:
			 *
			 * The above copyright notice and this permission notice shall be included in
			 * all copies or substantial portions of the Software.
			 *
			 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
			 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
			 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
			 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
			 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
			 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
			 * THE SOFTWARE.
			 */
			(function($) {
				// Private data
				var isLS=false;//typeof window.localStorage!=='undefined';
				// Private functions
				function wls(n,v){var c;if(typeof n==="string"&&typeof v==="string"){localStorage[n]=v;return true;}else if(typeof n==="object"&&typeof v==="undefined"){for(c in n){if(n.hasOwnProperty(c)){localStorage[c]=n[c];}}return true;}return false;}
				function wc(n,v){var dt,e,c;dt=new Date();dt.setTime(dt.getTime()+31536000000);e="; expires="+dt.toGMTString();if(typeof n==="string"&&typeof v==="string"){document.cookie=n+"="+v+e+"; path=/";return true;}else if(typeof n==="object"&&typeof v==="undefined"){for(c in n) {if(n.hasOwnProperty(c)){document.cookie=c+"="+n[c]+e+"; path=/";}}return true;}return false;}
				function rls(n){return localStorage[n];}
				function rc(n){var nn, ca, i, c;nn=n+"=";ca=document.cookie.split(';');for(i=0;i<ca.length;i++){c=ca[i];while(c.charAt(0)===' '){c=c.substring(1,c.length);}if(c.indexOf(nn)===0){return c.substring(nn.length,c.length);}}return null;}
				function dls(n){return delete localStorage[n];}
				function dc(n){return wc(n,"",-1);}
			
				/**
				* Public API
				* $.Storage - Represents the user's data store, whether it's cookies or local storage.
				* $.Storage.set("name", "value") - Stores a named value in the data store.
				* $.Storage.set({"name1":"value1", "name2":"value2", etc}) - Stores multiple name/value pairs in the data store.
				* $.Storage.get("name") - Retrieves the value of the given name from the data store.
				* $.Storage.remove("name") - Permanently deletes the name/value pair from the data store.
				*/
				$.extend({
					Storage: {
						set: isLS ? wls : wc,
						get: isLS ? rls : rc,
						remove: isLS ? dls :dc
					}
				});
			})(jQuery);			
		};	


	//show jQuery version
	Log('jQuery version: ' + jQuery.fn.jquery);
	This.Init();
	
})(jQuery);

/*
 *
 * TERMS OF USE - EASING EQUATIONS
 * 
 * Open source under the BSD License. 
 * 
 * Copyright © 2001 Robert Penner
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list 
 * of conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * 
 * Neither the name of the author nor the names of contributors may be used to endorse 
 * or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *  COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 *  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 *  GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE. 
 *
 */

/*! jQuery JSON plugin 2.4.0 | code.google.com/p/jquery-json */
(function($){'use strict';var escape=/["\\\x00-\x1f\x7f-\x9f]/g,meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'},hasOwn=Object.prototype.hasOwnProperty;$.toJSON=typeof JSON==='object'&&JSON.stringify?JSON.stringify:function(o){if(o===null){return'null';}
var pairs,k,name,val,type=$.type(o);if(type==='undefined'){return undefined;}
if(type==='number'||type==='boolean'){return String(o);}
if(type==='string'){return $.quoteString(o);}
if(typeof o.toJSON==='function'){return $.toJSON(o.toJSON());}
if(type==='date'){var month=o.getUTCMonth()+1,day=o.getUTCDate(),year=o.getUTCFullYear(),hours=o.getUTCHours(),minutes=o.getUTCMinutes(),seconds=o.getUTCSeconds(),milli=o.getUTCMilliseconds();if(month<10){month='0'+month;}
if(day<10){day='0'+day;}
if(hours<10){hours='0'+hours;}
if(minutes<10){minutes='0'+minutes;}
if(seconds<10){seconds='0'+seconds;}
if(milli<100){milli='0'+milli;}
if(milli<10){milli='0'+milli;}
return'"'+year+'-'+month+'-'+day+'T'+
hours+':'+minutes+':'+seconds+'.'+milli+'Z"';}
pairs=[];if($.isArray(o)){for(k=0;k<o.length;k++){pairs.push($.toJSON(o[k])||'null');}
return'['+pairs.join(',')+']';}
if(typeof o==='object'){for(k in o){if(hasOwn.call(o,k)){type=typeof k;if(type==='number'){name='"'+k+'"';}else if(type==='string'){name=$.quoteString(k);}else{continue;}
type=typeof o[k];if(type!=='function'&&type!=='undefined'){val=$.toJSON(o[k]);pairs.push(name+':'+val);}}}
return'{'+pairs.join(',')+'}';}};$.evalJSON=typeof JSON==='object'&&JSON.parse?JSON.parse:function(str){return eval('('+str+')');};$.secureEvalJSON=typeof JSON==='object'&&JSON.parse?JSON.parse:function(str){var filtered=str.replace(/\\["\\\/bfnrtu]/g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,'');if(/^[\],:{}\s]*$/.test(filtered)){return eval('('+str+')');}
throw new SyntaxError('Error parsing JSON, source is not valid.');};$.quoteString=function(str){if(str.match(escape)){return'"'+str.replace(escape,function(a){var c=meta[a];if(typeof c==='string'){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+(c%16).toString(16);})+'"';}
return'"'+str+'"';};}(window.BlackTri.jQuery));

/* END BT CODE */
