;
(function (bt) {
    "use strict";

    var code_mirror_opts = {
        tabMode: "indent",
        fixedGutter: true,
        lineNumbers: true,
        lineWrapping: true,
        smartIndent:true,
		textHover: bt.codeMirrorTextOver
    };
    /* text editor uses jhtmlarea plugin*/
    var htmlAreaOpts={
        toolbar:[],
        css: BTeditorVars.css_reset
    }
    var $editor, $codeArea, $htmlFrameEls;
    var minEditorWidth = 622;
    var minEditorHeight = 324;

    var edit = {
        editmode: null,
        stripTextEditStyle:true, /* flag for removing all inline style for "text edit"*/
        code_editor: null,
        html_editor:null,
        codemir_opts: code_mirror_opts,
        /* called from Blactri.init */
        init: function () {
			$editor=$('#BTFactorEditPopup').draggable({
				handle:'label.BTEnableDraggable',
				scroll: false
			}).resizable({
				resize: function(evt, ui){
					storeEditorWH(ui.size.width,ui.size.height);
					edit.resize(ui.size);

				},
				start: function(evt, ui){
				},
				minWidth: minEditorWidth,
				minHeight: minEditorHeight
			});
			$codeArea=$('#code_blocks');
			/* uses localStorage to store last dimensions used */
			var defaultSize=getEditorWH();

			$editor.css(defaultSize);
			edit.code_editor = CodeMirror.fromTextArea($('#BTCodeEditor')[0], edit.codemir_opts);
			edit.html_editor=$('#BTHtmlEditor').htmlarea(htmlAreaOpts);

			/* set htmlarea plugin dimensions to % so they adjust to resizing */
			$htmlFrameEls=$('.jHtmlArea, .jHtmlArea iframe').css({height:'100%', width:'100%'})
        },
        /* used to display html as code */
        html: function () {
            edit.editmode = 'html';
            /* todo investigate using getElementHtml(jqSel) or deprecate it*/
            var html = bt.CurrentElementInfo.outerHTML;
			html = fixInlineHtmlLinksEdit(html);
            edit.open(html);
        },
        js: function () {
            edit.editmode = 'javascript';
            var code = edit.getDomModCode();
            edit.open(code);
        },
        css: function () {
            edit.editmode = 'css';
            var code = edit.getDomModCode();
            edit.open(code);
        },

        getDomModKey:function(){
            return edit.editmode === 'css' ? '[CSS]' : '[JS]';
        },

        getDomModCode: function () {
            var dom_mod_key = edit.getDomModKey();
            var code = bt.variant.dom_modification_code[dom_mod_key];
            return code ? code : '';
        },

        toggleEditors:function(){
            var showHtmlEdit= edit.editmode === 'text';
            $('#code_editing').toggle(!showHtmlEdit);
            $('#html_editing').toggle(showHtmlEdit);

        },
        text: function () {
            edit.editmode='text';
            /* want element innerhtml */
			bt.com.sendCallBackMessage('btGetElementText', function(code){
				
				edit.open(code);
				// fixes strange bug that hides iFrame parent when click "proceed" in editor and then "cancel"
				$('#textEditIframe').parent().show();
				
			}, edit.stripTextEditStyle);
        },
        open: function (code) {
            edit.setTitle();
            bt.HideClickEditorMenu();
            edit.toggleEditors();
            edit.resize();
            /* show editng popup before any refresh can occur*/
            $editor.show();
            edit.resize();

            /* use codemirror for all modes except "text" mode*/
            if(edit.editmode !=='text'){

                var editor = edit.code_editor;
                editor.setValue(code);

                if (edit.editmode != 'html') {
                    editor.setOption('mode', edit.editmode);
                }else{
                    editor.setOption('mode', 'htmlmixed');
                }
                editor.refresh();
            }else{
                /* use WYSIWYG */
                edit.html_editor.htmlarea('html', code);
            }
           centerEditor();
        },
        resize: function (size) {
            /* resizeable already tracks dimensions*/
            var editor_height= (size && size.height ) ? size.height : $editor.height();
            var code_height=editor_height-100;
            $codeArea.height(code_height);
            $htmlFrameEls.height(code_height);
            edit.code_editor.setSize($codeArea.width(),code_height);

        },
        setTitle: function () {
            $('.codeEdit-title').hide().filter('[data-code=' + edit.editmode + ']').css('display', 'block');
        },
        save: function () {

           if(edit.editmode === 'css' || edit.editmode === 'javascript'){
               var dom_mod_key=edit.getDomModKey();
               var code_to_save=edit.code_editor.getValue();
               bt.history.store('edit_'+edit.editmode, 'code');
               bt.variant.dom_modification_code[dom_mod_key]=code_to_save;

           }else{
               /* todo GetDomPath not needed when merge with BLAC-283 can access element stored data there*/
               var domPathSelector=bt.CurrentSelector;//bt.GetDomPath(bt.$el[0]);
			   console.log('domPathSelector', domPathSelector);
               var new_html, script, codeSource;
               if(edit.editmode=='html'){
                   new_html= edit.code_editor.getValue();
                   script = bt.generateScript('replace', domPathSelector, new_html);
                   codeSource='html_edit';
               }else{

                   var $div=$('<div></div>');
                   new_html= edit.html_editor.htmlarea('html');
                   new_html = new_html.replace(/(<br>|< br>|< br >|<br >|<br \/>|<br\/>)$/, '');
                   debug.clear();

                   $div.html(new_html).find('[data-style]').each(function(){
                       var $el=$(this), style=$el.data('style');
                       $el.attr('style',style).removeAttr('data-style')
                   });
                   new_html=fixInlineHtmlLinksEdit($div.html());

                   script=bt.generateScript('text', domPathSelector, new_html);
                   codeSource='text_edit';
               }
               /* store history for "undo" */
               bt.history.store(/*'code_edit'*/ codeSource,'code');
               bt.saveNewVariantCode('JS', script, codeSource, domPathSelector);

           }
            $editor.hide();
            /* todo create better way to reload page */
            $('#variant_tabs .selected').click();
        },
		fixEditLinks: fixInlineHtmlLinksEdit,
		fixSaveLinks: fixInlineHtmlLinksSave,
		fixEditCssLinks: fixInlineCssLinksEdit

    }

    /* helper functions, modified to handle custom CSS and JS */
    function centerEditor(){
        /*Log('centerEditor');*/
        var ph = jQuery('#BTFactorEditPopup');
        var w = jQuery('#editor_wrap').width();
        var h = jQuery('.editor_action').height();
        var top = (h - ph.height())/2;
        var left = (w - ph.width())/2;
        if(top<=30) top = 30;

        ph.css( {left: left, top: top} );
    }

    function storeEditorWH(w,h)
    {
        var btTenant = BTTenant || 'blacktri';
        //store width & height
        var o = {};
        o[btTenant + "_btew"] = new String(w);
        o[btTenant + "_bteh"] = new String(h);
        jQuery.Storage.set(o);
    }
    function getEditorWH()
    {
        var btTenant = BTTenant || 'blacktri';
        var w = parseInt(jQuery.Storage.get(btTenant + "_btew"));
        var h = parseInt(jQuery.Storage.get(btTenant + "_bteh"));
        if(isNaN(w) || w < minEditorWidth)
            w = minEditorWidth;
        if(isNaN(h) || h < minEditorHeight)
            h = minEditorHeight;
        return {'width': w, 'height':h}
    }
	
	function fixInlineCssLinksEdit(text){
		text = text.replace(BTeditorVars.EditorBaseURL, '/');
		return text;
	}
	function fixInlineHtmlLinksEdit(html){

		//fix btproxy links https://opt.blacktri-dev.de/btproxy/http/
		for(;;){
			if(html.indexOf(BTeditorVars.EditorProxyURL)>-1){
				html = html.replace(BTeditorVars.EditorProxyURL + 'http/', 'http://');
				html = html.replace(BTeditorVars.EditorProxyURL + 'https/', 'https://');
			}
			else if(html.indexOf(BTeditorVars.EditorBaseURL)>-1){
				html = html.replace(BTeditorVars.EditorBaseURL, '/');
				html = html.replace(BTeditorVars.EditorBaseURL, '/');
			}
			else 
				break;
		}
		return html;
	}
	function fixInlineHtmlLinksSave(html){
		html = html.replace(linksRegEx, function(url){
			//double check for fake domain.tld links
			if(url.indexOf('http://')==0){
				url = url.replace("http://", BTeditorVars.EditorProxyURL + 'http/');
			}
			else if(url.indexOf('https://')==0){
				url = url.replace("https://", BTeditorVars.EditorProxyURL + 'https/');
			}
			else if(url.indexOf('//')==0){
				url = url.replace('//', BTeditorVars.EditorProxyURL + document.location.protocol.replace(':','') + '/');
			}
			return url;
		});
		return html;
	}
	
	var linksRegEx = /((?:(http|https|):?\/\/(?:(?:[a-zA-Z0-9\$\-\_\.\+\!\*\'\(\)\,\;\?\&\=]|(?:\%[a-fA-F0-9]{2})){1,64}(?:\:(?:[a-zA-Z0-9\$\-\_\.\+\!\*\'\(\)\,\;\?\&\=]|(?:\%[a-fA-F0-9]{2})){1,25})?\@)?)?((?:(?:[a-zA-Z0-9][a-zA-Z0-9\-]{0,64}\.)+(?:(?:aero|arpa|asia|a[cdefgilmnoqrstuwxz])|(?:biz|b[abdefghijmnorstvwyz])|(?:cat|com|coop|c[acdfghiklmnoruvxyz])|d[ejkmoz]|(?:edu|e[cegrstu])|f[ijkmor]|(?:gov|g[abdefghilmnpqrstuwy])|h[kmnrtu]|(?:info|int|i[delmnoqrst])|(?:jobs|j[emop])|k[eghimnrwyz]|l[abcikrstuvy]|(?:mil|mobi|museum|m[acdghklmnopqrstuvwxyz])|(?:name|net|n[acefgilopruz])|(?:org|om)|(?:pro|p[aefghklmnrstwy])|qa|r[eouw]|s[abcdeghijklmnortuvyz]|(?:tel|travel|t[cdfghjklmnoprtvwz])|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw]))|(?:(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9])\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9]|0)\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[1-9]|0)\.(?:25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[1-9][0-9]|[0-9])))(?:\:\d{1,5})?)(\/(?:(?:[a-zA-Z0-9\;\/\?\:\@\&\=\#\~\-\.\+\!\*\'\(\)\,\_])|(?:\%[a-fA-F0-9]{2}))*)?(?:\b|$)/gi

    bt.codeEdit = edit;
	
})(BlackTri);
