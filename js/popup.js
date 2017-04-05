/***************************/
//@Author: Adrian "yEnS" Mato Gondelle
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!					
/***************************/

//SETTING UP OUR POPUP
//0 means disabled; 1 means enabled;
var popupStatus = 0;

//loading popup with jQuery magic!
function loadPopup(divid){
	$('html, body').animate({
		scrollTop: $("#scrollToHere").offset().top
		}, 100);
	//loads popup only if it is disabled
	if(popupStatus==0){
		$("#backgroundPopup").css({
			"opacity": "0.7"
		});
		$("#backgroundPopup").fadeIn("slow");
		$(divid).fadeIn("slow");
		popupStatus = 1;
	}
	//$.validationEngine.closePrompt('.formError',true);
	//try to remove validation engine errors first to avoid bugs
	$('.formError').remove();
}

//disabling popup with jQuery magic!
function disablePopup(divid){
	//disables popup only if it is enabled
		$("#backgroundPopup").fadeOut("slow");
		$(divid).fadeOut("slow");
		popupStatus = 0;
		//$.validationEngine.closePrompt('.formError',true);
		//try to remove validation engine errors first to avoid bugs
		$('.formError').remove();
}

//centering popup
function centerPopup(divid){
	//request data for centering
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = $(divid).height();
	var popupWidth = $(divid).width();
	
	$(divid).css({
		"position": "absolute",
		"top": windowHeight/2-popupHeight/3,
		"left": windowWidth/2-popupWidth/2
	});
	//only need force for IE6
	
	$("#backgroundPopup").css({
		"height": windowHeight
	});
	
}

//CONTROLLING EVENTS IN jQuery
$(document).ready(function(){
	//LOADING POPUP
	//Click the button event!
	/* event moved to signup.php */
	$("#button").click(function(){
		//centering with css
		centerPopup('#popupContact');
		//load popup
		loadPopup('#popupContact');
	});

	//Click the button event for confirm delete collection
	
	$(".collectionDelete").click(function() {
		/*
		$('html, body').animate({
			scrollTop: $("#scrollToHere").offset().top
			}, 200);
		*/
		//getting id of deleted item	
		var collectionid = $(this).attr('collectionid');
		var clientid = $(this).attr('clientid');
		document.getElementById('deleteid').value=collectionid;
		OpenPopup('#deleteConfirm');
		//centering with css
		//centerPopup('#deleteConfirm');
		//load popup
		//loadPopup('#deleteConfirm');
	});
	/*
	//Click the button event for restart collection
	$("#collectionRestart").click(function(){
		$('html, body').animate({
			scrollTop: $("#scrollToHere").offset().top
			}, 200);
		//centering with css
		centerPopup('#restartCollection');
		//load popup
		loadPopup('#restartCollection');
	});			
	*/
	//CLOSING POPUP
	//Click the x event!
	$("#popupContactClose").click(function(){
		disablePopup("#popupContact");
	});
	//Click out event!
	$("#backgroundPopup").click(function(){
		disablePopup("#popupContact");
		disablePopup("#deleteConfirm");
		disablePopup("#restartCollection");
	});
	//Press Escape event!
	$(document).keypress(function(e){
		if(e.keyCode==27 && popupStatus==1){
			disablePopup("#popupContact");
			disablePopup("#deleteConfirm");
			disablePopup("#restartCollection");
		}
	});
});

//cancel subscription
function cancelsubscripton()
{
	var path= document.getElementById('path').value;
	$.post(path+"users/cancelsub/",
			function(data)
	        {
				//centering with css
				centerPopup('#popupContact');
				//load popup
				loadPopup('#popupContact');
	        }
	 );
}

//new pop-up function, use container selector to open
function OpenPopup(selector, showClose, callBack, opt)
{
	if( typeof(showClose) == "object" )
		opt = showClose;
	if( typeof(showClose) == "undefined" || typeof(showClose) == "object" ) showClose = true;
	var opts = {
			margin:15,
			showCloseButton: showClose,
			autoScale: false,
			onClosed: function(){
			},
			onCleanup: function(){
				        $('.formError').fadeTo("fast", 0.3, function() {
							$(this).remove();
						});
						if( typeof(callBack) == "function" )
							callBack();
			},
			hideOnOverlayClick: false,
			hideOnContentClick: false
	};
	$.extend( opts, opt );
	$("<a/>").attr("href", selector)
	.fancybox(opts)
	.trigger("click");
}
//close popup
function ClosePopup()
{
	$.fancybox.close();
}
//new help pop-up function, use container selector to open
function OpenPreview(link)
{
	OpenHelp(link, true);
}

//new help pop-up function, use container selector to open
function OpenHelp(link, fullScreen)
{
	//fancybox border
	var border = 10;
	//check if we are in an iframe and open helo in the main page
	try {
		if(parent != window)
		{
			parent.OpenHelp(link);
			return;
		}
	}catch(e){}
	
	var html = [];
	//add new help overlay
	html.push('<div id="fancybox-wrap-help" style="width: 760px; height: auto; top: 0px; left: 0px; display: none; outline: medium none; padding: 20px; position: absolute;z-index: 999101;">');
	html.push('	<div id="fancybox-outer">');
	html.push('		<div id="fancybox-bg-n" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-ne" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-e" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-se" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-s" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-sw" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-w" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-bg-nw" class="fancybox-bg"></div>');
	html.push('		<div id="fancybox-content" style="border-width: '+border+'px; width: 740px; height: auto;">');
	html.push('			<iframe scrolling="auto" frameborder="0" hspace="0" name="help-frame" id="fancybox-frame"></iframe>');		
	html.push('		</div>');
	html.push('		<a id="fancybox-close" style="display: inline;"></a>');
	html.push('	</div>');
	html.push('</div>');
	html = html.join('');
	$(html).appendTo('body');
	
	var main = $('#fancybox-wrap-help');
	var overlay = $('#fancybox-overlay');
	var content = main.find('#fancybox-content');
	var overlayWasVisible = overlay.is(':visible');
	//events
	main.find('#fancybox-close').click(function(){
		$(window).unbind('resize', HelpResizeAndPosition);
		if(overlayWasVisible) 
			$('#fancybox-wrap').stop(true).fadeTo('fast',1);
		else
			overlay.fadeOut();

		main.fadeOut('fast', function(){main.remove()});
	});
	
	var fs = fullScreen==undefined ? false: fullScreen;
	
	//resize
	HelpResizeAndPosition(fs);
	$(window).bind('resize', function(){ HelpResizeAndPosition(fs)} );
	//hide existing fancybox, opacity trick used to still keep fancybox resize events so we dont have positioning bugs
	if(overlayWasVisible)
		$('#fancybox-wrap').stop(true).fadeTo('fast',0.01);
				
	//show help box
	main.fadeIn();
	overlay.fadeIn();
	//set url
	main.find('#fancybox-frame').attr('src', link);
	
	//method to resize and center content
	function HelpResizeAndPosition(fs)
	{
		var win = $(window);
		var doc = $(document);
		var w = fs?win.width():840;
		
		if(w > win.width() )
			w-=4*border
		if(window.console) window.console.log('Window width: ' + win.width() + ', Current width: ' + w);
		var h = win.height();
		var l = (win.width() - w + (fs?1:2) * border)/2;
		
		main.css({left: l, top: win.scrollTop(), width: w - (fs?2:4) * border, height: h - (fs?2:4) * border});
		content.width(main.width()-2*border).height(main.height()-2*border);
		overlay.height(doc.height()).width(win.width()).css({'background-color':'#777777', 'cursor': 'pointer', 'opacity': 0.7});		
	}
}
function CloseHelp()
{
	var main = $('#fancybox-wrap-help');
	main.find('#fancybox-close').trigger('click');
}


function ResizeToWindow(selector, margin)
{
	var el = $(selector);
	var w = $(window).width(), h = $(window).height();
	if(typeof(margin)=="undefined") margin = 0;
	w = w - 2 * margin - ( 145 );
	h = h - 2 * margin - ( 80 );
	el.width(w).height(h);
}