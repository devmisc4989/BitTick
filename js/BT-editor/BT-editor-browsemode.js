;(function (bt) {
    "use strict";
	var $element;
	var bm = {
		mode: 'editor', /* editor, browse */
		init:function(mode){
			$element = $('#switch_mode');
			bm.setMode(mode);
		},
		switchMode: function(mode){
			var currentMode = mode || 'editor';
			if(bm.mode == mode) return;			
			bm.setMode(currentMode);
		},
		setMode: function(mode){
			bt.mode = mode || 'editor';			
			$element.show();
			$element.find('#editor_mode_' + mode).attr('checked', true);
			if(mode=='browse')
			{				
				$('.editor_proxy').hide();
			}
			else
			{
				$('.editor_proxy').show();
			}
			bm.mode = mode;
			bm.sendClientMode(mode);
		},
		sendClientMode: function(mode){
			bt.ExecFunctionOnClient('btSwitchMode', mode);
		}
	}
	
	bt.browseMode = bm;
})(BlackTri);
