BlackTri.exposeClientElements = function () {
    this.HideClickEditorMenu();
    this.HideActionLayer();
    $('.editor_proxy').hide();

}
;
(function (bt) {
    "use strict";
	
    var rearrange = {
        init: function () {
			bt.exposeClientElements();
			bt.com.sendMessage('btRearrange.init');
        },
		
		update: function(hasNewPosition){
			$('#save_rearrange').prop('disabled', !hasNewPosition);
		},
        destroy: function () {
            rearrange.restoreEditor();
            rearrange.hideControls();
            $('#save_rearrange').prop('disabled', true);
        },

        cancel: function () {
			bt.com.sendMessage('btRearrange.cancel');
        },

        save: function () {
			bt.com.sendCallBackMessage('btRearrangeGetCode', function(rearrangeInfo){
				bt.history.store('rearrange', 'code');
				if (rearrangeInfo.insertCode) {
					bt.saveNewVariantCode('JS', rearrangeInfo.code, 'rearrange', rearrangeInfo.eleDomPath);
				} else {
					console.info("Was not moved");
				}
				rearrange.destroy();
			});
        },
				
        setElementBorders: function (props) {
            var borderOffset = 5;
            var borderThickness = 3;
			if(props.elemOffset){
				$('#elem_border_left').css({top: props.elemOffset.top - borderOffset - props.scrolltop, height: props.elemH + ( 2 * borderOffset ), left: props.elemOffset.left - borderOffset });
				$('#elem_border_right').css({top: props.elemOffset.top - borderOffset - props.scrolltop, height: props.elemH + borderThickness + ( 2 * borderOffset ), left: props.elemOffset.left + props.elemW + borderOffset });
				$('#elem_border_bottom').css({top: props.elemOffset.top + props.elemH + borderOffset - props.scrolltop, width: props.elemW + ( 2 * borderOffset ), left: props.elemOffset.left - borderOffset});
				$('#elem_border_top').css({top: props.elemOffset.top - borderOffset - props.scrolltop, width: props.elemW + ( 2 * borderOffset ), left: props.elemOffset.left - borderOffset});
			};
        },
        showControls: function () {
            $('#rearrange_control, .client_mask, .client_border').show();
        },

        hideControls: function () {
            $('#rearrange_control, .client_mask, .client_border').hide();
        },

        setMasks: function (props) {
			var maskOffset = 8;
			var borderThickness = 3;
			
            $('.client_mask_left').css({width: props.minX - maskOffset, height: props.maxY + maskOffset});
            $('.client_mask_right').css({'left': props.maxX + maskOffset, height: props.maxY + maskOffset});
            $('.client_mask_bottom').css('top', props.maxY + maskOffset);
            $('.client_mask_top').css({height: props.minY - maskOffset, left: props.minX - maskOffset, width: (props.maxX + maskOffset) - (props.minX - maskOffset)});


            $('#rearrange_border_left').css({top: props.minY - maskOffset, height: props.maxY - props.minY + 2 * maskOffset, left: props.minX - maskOffset });
            $('#rearrange_border_top').css({top: props.minY - maskOffset, width: props.maxX - props.minX + 2 * maskOffset, left: props.minX - maskOffset });
            $('#rearrange_border_bottom').css({top: props.maxY + maskOffset, width: props.maxX - props.minX + 2 * maskOffset, left: props.minX - maskOffset });
            $('#rearrange_border_right').css({top: props.minY - maskOffset, height: props.maxY - props.minY + borderThickness + 2 * maskOffset, left: props.maxX + maskOffset });
        },
        restoreEditor: function () {
            $('#rearrange_control, .client_mask').hide();
            $('.editor_proxy').show();
        }
    }
	
    bt.rearrange = rearrange;
})(BlackTri);
