;(function (bt) {
    "use strict";
	var $element,
		$iframeWrap,
		$iframeElement,
		deviceTypes = BTeditorVars.EditorDeviceTypes;
	var dt = {
		device: 'desktop',
		init:function(){
			$element = $('#device_select');
			$iframeWrap = $('#editor_iframe_wrap');
			$iframeElement = $('#frame_editor');
			var device = $element.val();
			$element.change(changeDevice);
			dt.setDevice(device, true);
			$(window).resize(resizeToMatch);
		},
		setDevice: function(device, skip){
			var skipIframe = skip || false;
			
			dt.device = device || 'desktop';
			$element.val(device);
			$('#frmVisualABStep4 #device_type').val(device);
			
			var deviceInfo = getDeviceInfo(device);
			var url = $iframeElement.attr('src');
			if(url)
			{
				if(deviceInfo.width == 0 && deviceInfo.height == 0)
				{
					$iframeWrap.width('').height('').removeClass('editor_iframe_device').css('left', '');
				}
				else
				{
					$iframeWrap.width(deviceInfo.width).height(deviceInfo.height).addClass('editor_iframe_device');
					$iframeWrap.css('left', ($(window).width()-deviceInfo.width)/2);
					var bodyH = deviceInfo.height + $('#editor_top').outerHeight();
					$('body').css('min-height', bodyH + 'px');
				}
				
				resizeToMatch();
				
				if(skipIframe) return;
				
				if(typeof EditorSwitchDevice == 'function')
					EditorSwitchDevice(device);
				
				var url = url.replace(/&device=([^&]+)/ig, "&device=" + device);
				$iframeElement.attr('src', url);
				$(".editor_overlayer").show();
			}
			else
				bt.Log('Invalid editor url!');
		},
	}
	
	//internals
	function resizeToMatch()
	{
		var $win = $(window);
		var pos = $iframeWrap.position();
		var newHeight = $('#editor_wrap').height() - pos.top - $('.bottom_border').height() - $('.top_border').height();
		if($iframeWrap.height() >= newHeight)
		{
			$iframeWrap.height(newHeight);
			$iframeWrap.css('overflow', 'auto');
		}
		else
		{
			$iframeWrap.height('')
			$iframeWrap.css('overflow', '');
		}
	}
	function getDeviceInfo(device){
		var deviceInfo = {width:0, height:0};
		$.each(deviceTypes, function(i, deviceList){
			$.each(deviceList, function(key, devInfo){
				if(key == device)
				{
					deviceInfo = devInfo
					return false;
				}
			});
		});
		//default device/desktop
		return deviceInfo;
	}
	function changeDevice(){
		var device = $element.val();
		dt.setDevice(device);
	}	
	
	bt.deviceType = dt;
})(BlackTri);
/*

     zoom: 0.25;
    -moz-transform: scale(0.25);
    -moz-transform-origin: 0 0;
    -o-transform: scale(0.25);
    -o-transform-origin: 0 0;
    -webkit-transform: scale(0.25);
    -webkit-transform-origin: 0 0;
	
*/