/**
 * Extends BlackTri object to add style editor capability
 * @requires BTStyleProps object
 */

var debug = console;
;
(function (bt) {
    "use strict";
    var $editor, $clientElement, $inputs, $tabs, $clientProps;
    var propertySubLabels=['Top','Right','Bottom','Left'];
    var fourSidedProps={
        margin:{subProps:['margin-top','margin-right','margin-bottom', 'margin-left']},
        padding: {subProps:['padding-top','padding-right','padding-bottom', 'padding-left']},
        'border-radius':{
            tagType:'input',
            subProps:['border-top-left-radius','border-top-right-radius','border-bottom-right-radius', 'border-bottom-left-radius'],
            subLabels:['Top Left','Top Right','Bottom Right','Bottom Left']
        },
        'border-width': {
            tagType:'input',
            subProps:['border-top-width','border-right-width','border-bottom-width','border-left-width'],
            subLabels: propertySubLabels
        },
        'border-style': {
            tagType:'select',
            subProps:['border-top-style','border-right-style','border-bottom-style','border-left-style'],
            subLabels: propertySubLabels
        },
        'border-color': {
            tagType:'input',
            subProps:['border-top-color','border-right-color','border-bottom-color','border-left-color'],
            subLabels: propertySubLabels
        }
    }

    var propertyControls='<div class="property_controls">' +
        '<button class="st_field_undo">Reset</button> ' +
        '<button class="st_field_apply">Apply</button>' +
        '</div> ';

    var styleEditor = {

        domPath: null,
        selectedTabId: null,
        open: function (evt) {
            evt.preventDefault();
            bt.HideClickEditorMenu();
            debug.clear();
			
            storeElementInlineStyle();
            styleEditor.loadCurrentStyles();
            styleEditor.domPath = bt.CurrentSelector;			
            styleEditor.selectedTabId = $('#variant_tabs .tab.selected').attr('id');
            setTagnameDisplay();
            $editor.show();

           /* cancel editing if click outside style editor */
            setTimeout(function(){
                $(document).on('click.styleEditor', function(evt){
                    var $tgt=$(evt.target);

                    if(!$tgt.closest('#st_editor').length && !$tgt.closest('.ui-cs-chromoselector').length){
                        if($editor.is(':visible')){
                            styleEditor.cancel();
                        }
                    }
                });
            },10);

        },
        save: function () {
            var cssStyleObj = createStyleObject();
            /* returns false if none changed*/
            if (cssStyleObj) {
                /* start by adding comment to be viewed in JS editor */
                var jsCode;
                bt.history.store('style_edit', 'code');

				jsCode = bt.generateScript('style', styleEditor.domPath, cssStyleObj);
				var codeSource= 'style_edit';
				/* use new method created in Blactri object to store new JS */
				bt.saveNewVariantCode('JS', jsCode,codeSource, styleEditor.domPath);
           }
             styleEditor.close();
        },

        loadCurrentStyles: function () {
            debug.groupCollapsed('[Style Editor] - Existing Styles');
            $inputs.each(function () {
                var $input = $(this);
                var prop = $input.data('css_prop');
                var propVal = styleEditor.getElCssVal(prop);

                if (isColorProp(prop)) {
                    $input.ColorPickerSetColor(propVal);
                }

                /* set input value*/
                $input.val(propVal);
                /* store original value */
                $input.data('default_val', propVal);

            });

            /* all values are set, check if we need to show sub properties */
            $inputs.filter('.master_property').each(function(){
                var $input=$(this), $field=$input.closest('.field'), $subProps=$field.find('.sub_property');
                var masterValue=$input.val();
                $subProps.each(function(){
                    if(masterValue !== $(this).val()){
                        /* trigger a click on radio to disable this input and open sub properties panel */
                        $field.find(':radio').not('.default').click();
                        /* break the each loop */
                        return false;
                    }

                })
            });


            debug.groupEnd();
        },
        cancel: function (/*evt*/) {
            /*evt.preventDefault();*/
            /* remove any changes user made*/
            $clientElement.removeAttr('style');
            /* reset original style if any*/
            var originalStyle = $clientElement.data('originalStyle');
            if (originalStyle) {
                $clientElement.attr('style', originalStyle);
            }
            styleEditor.close();
        },
        init: function () {
            $editor = $('#st_editor').draggable({
                handle:'h1',
                start: function(){
                    /* hide open colorpickers while dragging or they don't follow */
                    $inputs.filter('.color_input').chromoselector('hide');
                    //debug.log('STarting.......')
                   // $inputs.blur()
                }
            });
            /* todo finish layout styling and set cursor in CSS */
            $editor.find('h1').css('cursor','move');
            buildSubProperties();
            $inputs = $editor.find('[data-css_prop]');
            console.groupCollapsed('Style editor init');
            createOptionTags();
            appendControls();
            loadFourSideRadios();
            bindStyleEditorEvents();
            initEditorTabs();
            initColorpicker();
            console.groupEnd();
        },
        close: function () {
            $editor.hide();
            /* clean up */
            resetTabs();
            resetSubProperties();
            $editor.find('.modified').removeClass('modified');
            /* reset all display and default values just in case */
            $inputs.val('').data('default_val','');

            /* remove document click handler*/
            $(document).off('click.styleEditor');
            bt.HideActionLayer();
        },
        /**
         * Returns CSS value of element in client page to be displayed in style editor fields
         * @param prop CSS property
         * @returns string
         */
        getElCssVal:function(prop){
            var propVal = $clientProps[prop];
            /* some four sided properties like "margin" won't return a single value */
            /* convert rgb to hex for color properties*/
            if (isColorProp(prop)) {

                if (propVal.indexOf('rgb') > -1  ) {
                    /* todo short term hack for chrome needs more research- see bug BLAC-297 */
                    if(propVal.indexOf('rgba') === -1){
                        propVal = rgb2hex(propVal);
                    }

                }
            }

            if(prop == 'background-image'){
				
				//fix main links for inline css
                propVal=bt.codeEdit.fixEditLinks(propVal);
				
				//fix specific css file style links
				propVal=bt.codeEdit.fixEditCssLinks(propVal);
            }
            debug.log('[Calculated] Prop:', prop, ', Value: ', propVal);
           return propVal;
        }
    };

    function isColorProp(prop){
        return prop.indexOf('color')>-1;
    }

    function buildSubProperties(){

        $editor.find('.master_property').each(function(){
            var $input=$(this), masterProp= $input.data('css_prop');
            var propData=fourSidedProps[masterProp];
            var html='<div class="sub_properties" style="clear:both">';
            $.each(propData.subProps, function(idx, prop){
                html+='<span class="sub_prop_label">'+propData.subLabels[idx]+'</span>';
                var cssClass= isColorProp(prop) ? ' class="color_property sub_property" ':' class="sub_property" ';
                if(propData.tagType =='input'){
                    html+='<input type="text" data-css_prop="'+prop+'"' +cssClass +'>';
                }else{
                    html+='<select data-css_prop="'+prop+'"' +cssClass +'></select>';
                }

            });
            html+='</div>';
            $input.after(html);
        })


    }

    function loadFourSideRadios(){
        var inputs=$inputs.filter('.master_property').each(function(){

            var $input=$(this), $parent=$input.parent();
            /* radios are stored in script tag template*/
            var $radioGroup=$($('#st_editor_radios').html());
            var master_css_prop=$input.data('css_prop');
            /* store default css property for this input */
            $input.data('default_prop', master_css_prop);
            /* name radios the same */
            $radioGroup.find(':radio').attr('name', master_css_prop);

            //$input.parent().append();
            $input.after($radioGroup);

        });
    }

    function setTagnameDisplay(){
        var tagname=bt.CurrentTagName.toLowerCase();
        $('#st_tagname').html('&lt;'+tagname+'&gt;')
    }

    function initEditorTabs(){
       $tabs=$('#st_editor_tabs');
        $tabs.find('.tab').click(function(){
            var $tab=$(this);
            if(!$tab.is('.selected')){
                $tabs.find('.selected').removeClass('selected');
               $tab.addClass('selected').siblings();
                var contentId='#'+$tab.data('tab');
                $(contentId).addClass('selected');

            }
        })
    }

    function resetTabs(){
        $tabs.find('.selected').removeClass('selected');
        $tabs.find('.tab:first, .tab-content:first').addClass('selected');
    }

    function resetSubProperties(){
       $editor.find('.sub_properties').hide();
        $editor.find('input.default_radio').prop('checked',true);
       $inputs.filter('.master_property').prop('disabled',false);
    }

    function createStyleObject() {
        var $changedEls = $inputs.filter('.modified');
        if (!$changedEls.length) {
            return false;
        }
        var jQCssObj = {};
        $changedEls.each(function () {
            var cssProp = $(this).data('css_prop'),
                val = this.value;
            /* special value conditions */

            jQCssObj[cssProp] = val;
        });
        return jQCssObj;
    }

    function createOptionTags() {
        $editor.find('select').each(function () {

            var $sel = $(this),isSubProperty=$sel.is('.sub_property'), masterProperty;
            /* sub properties use same choices as master property*/
            if( isSubProperty){
                masterProperty = $sel.closest('.field').children('select').data('css_prop');
            }else{
                masterProperty=$sel.data('css_prop');
            }

            var    propObj = BTStyleProps[masterProperty];

            var optHtml = '<option value=""></option>';
            if (!propObj) {
                console.error('[style editor] Missing property for ', $sel.data('css_prop'));
                return;
            }
            $.each(propObj.values, function (i, val) {
                optHtml += '<option value="' + val + '">' + val + '</option>';
            });
            $sel.html(optHtml);
        })
    }

    function appendControls(){
        $inputs.parent().append(propertyControls)
    }

    /**
     * Store existing inline style so it can be reset on "Cancel"
     */
    function storeElementInlineStyle() {
		bt.com.sendMessage('btStoreElementInlineStyle');
    }

    function bindStyleEditorEvents() {
        $('.st_editor_open').click(function(evt){
			
			//build prop list to get them with postMessage
			var props = [];
			$inputs.each(function () {
				var $input = $(this);
				var prop = $input.data('css_prop');
				props.push(prop);
			});
			bt.com.sendCallBackMessage('btStyleEditorClick', function(elementStyleInfo){
				console.log('elementStyleInfo', elementStyleInfo);
				$clientProps = elementStyleInfo.props;
				styleEditor.open(evt);
			}, {props: props, fourSidedProps: fourSidedProps});
		});
        $('#st_editor_cancel').click(styleEditor.cancel);
        $('#st_editor_save').click(styleEditor.save);

        $('.four_side_radios :radio').change(function(){
            var $field=$(this).closest('.field');
            var showSubs=$(this).val() !='all';
            $field.find(':input.master_property').prop('disabled', showSubs);
            $field.find('.sub_properties').slideToggle();

        });
        /* reset button for field */
        $('.st_field_undo').click(function(){
            $(this).closest('.field').find(':input[data-css_prop]').each(function(){
                var $input=$(this);
                $input.val($input.data('default_val')).change();
            });

        });
        /* toggle an "Apply" button for text inputs */
        $inputs.filter('input').not('.color_input').focus(function(){
            $(this).closest('.field').addClass('active_edit');
        }).blur(function(){
            $(this).closest('.field').removeClass('active_edit');
        });

        $inputs.change(function () {
            var cProp = $(this).data('css_prop');
            if (cProp === 'background-image') {
                var val = $(this).val().replace(/ /g, '');
                val = (val.lastIndexOf('url(', 0) !== 0) ? "url('" + val + "')" : val;
                $(this).val(val);
            }
            
            var $input = $(this),
                currVal = $input.val(),
                default_val = $input.data('default_val'),
                prop = $input.data('css_prop'),
                isModified = currVal != default_val;
				
	            /* update client element */
				bt.com.sendMessage('btStyleEditorSetCss', prop, currVal);

            /* track changed values with class*/
            $input.toggleClass('modified', isModified);

            $input.closest('.field').toggleClass('modified', isModified);
            /* adjust the hover frame size for all but color properties*/
            if(!isColorProp(prop)){
                bt.internalPositionHoverFrame();
            }

        });
    }

    function initColorpicker() {

        function colorUpdate(){
            /* "this" is the input bound to color picker */
            $(this).change();
        }

        $('#st_color, #st_bg_color, #st_border_color,.color_property').chromoselector({
            preview: true,
            update: colorUpdate
        });


    }

    function rgb2hex(rgb) {
       var val=rgb;
        rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        return "#" +
            ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
            ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
            ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2);
    }


    bt.styleEditor = styleEditor;
    $(function () {
        bt.styleEditor.init();
    })


})(BlackTri);

(function($) {
    /* modified plugin from http://stackoverflow.com/questions/2655925/how-to-apply-important-using-css*/
    if ($.fn.setstyle) {
        return;
    }

    // Escape regex chars with \
    var escape = function(text) {
        return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
    };

    // For those who need them (< IE 9), add support for CSS functions
    var isStyleFuncSupported = !!CSSStyleDeclaration.prototype.getPropertyValue;
    if (!isStyleFuncSupported) {
        CSSStyleDeclaration.prototype.getPropertyValue = function(a) {
            return this.getAttribute(a);
        };
        CSSStyleDeclaration.prototype.setProperty = function(styleName, value, priority) {
            this.setAttribute(styleName, value);
            var priority = typeof priority != 'undefined' ? priority : '';
            if (priority != '') {
                // Add priority manually
                var rule = new RegExp(escape(styleName) + '\\s*:\\s*' + escape(value) +
                    '(\\s*;)?', 'gmi');
                this.cssText =
                    this.cssText.replace(rule, styleName + ': ' + value + ' !' + priority + ';');
            }
        };
        CSSStyleDeclaration.prototype.removeProperty = function(a) {
            return this.removeAttribute(a);
        };
        CSSStyleDeclaration.prototype.getPropertyPriority = function(styleName) {
            var rule = new RegExp(escape(styleName) + '\\s*:\\s*[^\\s]*\\s*!important(\\s*;)?',
                'gmi');
            return rule.test(this.cssText) ? 'important' : '';
        }
    }

    // The style function
    //$.fn.setstyle = function(styleName, value, priority) {
    $.fn.setstyle = function(styleObj, priority) {
        // DOM node
        var node = this.get(0);
        // Ensure we have a DOM node
        if (typeof node == 'undefined') {
            return this;
        }

        if ($.isEmptyObject(styleObj)){alert('empty')
            return this;
        }
        // CSSStyleDeclaration
        var style = this.get(0).style;
        // Setter

        $.each(styleObj, function(styleName,value){

            if (typeof styleName != 'undefined') {
                if (typeof value != 'undefined') {
                    // Set style property
                    priority = typeof priority != 'undefined' ? priority : '';
                    style.setProperty(styleName, value, priority);
                }
            }

        });
    };
})(jQuery);
