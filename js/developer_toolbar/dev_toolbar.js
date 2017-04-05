/**
 * Created by charlie tomlinson on 3/7/14.
 * Used in conjunction with Greasemonkey/Tampermonkey user script to give developers an add-on toolbar for simple
 * UI switching
 *
 * To load from Greasemonkey  or to try it in console use:
 *   var _bt$= window.jQuery ||  window.BTJQuery || window.BlacTri.jQuery;
 *   _bt$.getScript('https://blacktri-dev.de/js/developer_toolbar/dev_toolbar.js');
 *
 *   works on
 *   https://blacktri-dev.de
 *   https://opt.blacktri-dev.de
 */

;(function(){

    var isInIframe = (parent !== window);
    /* don't want this loading in editor iframe*/
    if(isInIframe){
        return;
    }


    var _bt$= window.jQuery ||  window.BTJQuery;


    _bt$(function($){
        "use strict";

        var protocol=location.protocol;
        var dev_data={
            /* CI controller sends tenant and lang cookies and toolbar html/style */
            ajax_url: protocol+'//blacktri-dev.de/index.php/developer_ajax',
            toolbar_script_url:protocol+'//blacktri-dev.de/js/developer_toolbar/dev_toolbar.js',

            /* this part is stored in localStorage*/
            local: {
                /* login*/
                user:null,
                pass:null,
                hideToolbar:false
            }
        }

        var localData=getLocalData();
        var $toolbar;
        var isLoginPage=location.host == "blacktri-dev.de" && location.href.indexOf('/login') >-1;
        /* merge data from localStorage*/
        if(localData){
            $.extend(dev_data.local,localData);
        }

        $.getJSON(dev_data.ajax_url,function(res){
            $.extend(dev_data,res.dev_data);
            $('body').append(res.toolbar);
            $toolbar=$('#dev_toolbar');
            toolbar_init();
        });

        function toolbar_init(){
            /* start a log group to consolidate logs in other scripts*/
            console.group('Toolbar logs');
            $('button.switch_environment').click(switchEnvironment);
            $('button#dev_tool_opt_url').click(loadOptBlacktriUrl);
            setDomainDisplay();

            $('#dev_user_form').submit(devUserFormSubmit);
            $('#dev_login_button').click(devlogButtonClick);
            $('#dev_toolbar_tab').click(function(){
                dev_data.local.hideToolbar= !dev_data.local.hideToolbar;
                storeLocalData();
                toggleToolbarDisplay();
                
            });

            $('#dev_path').click(function(){
                alert("PATH:\n\n"+dev_data.branch_path);
            });

            //$('#dev_editor_wiz').click(loadEditorWizardMode);
            $('#dev_editor_wiz,#dev_editor_visitor').click(function(){
                var force_visitor=$(this).data('force_visitor')==1;
                loadEditorWizardMode(force_visitor);
            })
            editorBTVariantsDataToolbar();

            if(dev_data.branch_path){
                console.info('BRANCH PATH: ',dev_data.branch_path);
            }

            if(dev_data.local.hideToolbar){
                toggleToolbarDisplay();
            }
            /* end logging group*/
            console.groupEnd();
        }



        function switchEnvironment(){
            var $btn=$(this), env_switch=$btn.data('env_switch');
            switch(env_switch){
                case 'tenant':
                    var newTenant=dev_data.tenant ==='blacktri' ?'etracker' : 'blacktri';
                    //location.search='tenant='+newTenant;
                    setLocationSearch('tenant',newTenant);
                    break;
                case 'lang':
                    var newLang=dev_data.lang_abbr ==='en' ?'de' : 'en';
                    //location.search='lg='+newLang;
                    setLocationSearch('lg',newLang);
                    break;
            }
        }

        function loadEditorWizardMode(is_visitor_mode){
            var url = window.prompt("URL of page to load:",'http://www.domain_a.com/lpc1.html');
            if(url){
                var editor_url='https://blacktri-dev.de/editor';
                if(is_visitor_mode){
                    editor_url+= '/visitor';
                }
                location.href=editor_url+"?url="+url;
            }
        }

        function loadOptBlacktriUrl(){
            var url = window.prompt("URL of page to load:",'http://');
            if(url){
                location.href="https://opt.blacktri-dev.de?blacktriurl="+url;
            }
        }

        function devlogButtonClick(){
            var local=dev_data.local;
            console.dir(dev_data.local)
            if( !local.user || !local.pass){
                $('#dev_user_form').show()
            }else{
                devLogin();
            }
        }

        function devUserFormSubmit(e){
            e.preventDefault();
            /*if(!dev_data.local){
                dev_data.local={} ;
            }*/
            dev_data.local.user=$('#dev_user').val();
            dev_data.local.pass=$('#dev_pass').val();
            storeLocalData();
            devLogin();

        }

        function devLogin(){
            var local=dev_data.local;
            $('#email').val(local.user);
            $('#password').val(local.pass);

            $('#signin_button').click();
        }

        function setDomainDisplay(){
            var host=location.host;
            console.log(' Host = ', host);
            if(host.indexOf('opt.blacktri') >-1){
                $('.dev_tool_dash_buttons').hide();
            }
            /* only show login button when needed*/
            /*if(location.host == "blacktri-dev.de" && location.href.indexOf('/login') >-1){*/
            if(isLoginPage){
                $('#dev_login_button').show();
                $('.dev_tool_dash_buttons').hide();
            }
        }

        function getLocalData(){
            var local=localStorage.getItem('BlactriDev');
            if(!local){
                return false;
            }
            return JSON.parse(local);
        }

        function toggleToolbarDisplay(){

            var isHidden= !dev_data.local.hideToolbar;
            $toolbar.toggleClass('dev_toolbar_hidden');
            var $tab=$('#dev_toolbar_tab');
            var tabText= isHidden ? $tab.data('isVisible_text') : $tab.data('isHidden_text');
            $tab.text(tabText);
        }

        function storeLocalData(){
            localStorage.setItem('BlactriDev', JSON.stringify(dev_data.local));
        }

        function getQueryVariable(variable) {
            var query = window.location.search.substring(1);
            var vars = query.split('&');
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split('=');
                if (decodeURIComponent(pair[0]) == variable) {
                    return decodeURIComponent(pair[1]);
                }
            }
            //console.log('Query variable %s not found', variable);
            return false;
        }

        function setLocationSearch(type, value){
            var urlParam=getQueryVariable('url');
            var newSearch=type+'='+value
            if(urlParam){
               location.search= 'url='+urlParam +'&'+ newSearch;
            }else{
                location.search=  newSearch;
            }
        }

        function editorBTVariantsDataToolbar(){

            if(!$('#editor_top').length){
                return
            }
            var dev='<div style="background:#FFF89F; padding:20px; position: absolute;left:50%; top:0; width: 600px; margin-left:-300px;z-index: 5000">' +
                '<button type="button" id="show_variant_code" style="padding: 6px 10px; font-wight:bold; border: 1px solid #444">Toggle BTVariantsData Obj Display</button>' +
                '<button onclick="$(this).parent().hide();" style="float: right;padding: 6px 10px; font-wight:bold; border: 1px solid #444">Hide</button> ' +
                '<textarea id="variant_code" style="width:620px; margin:10px -10px; height:600px; display:none; font-size: 13px; font-family: \'monotype\'"></textarea>' +
                '</div>';
            $('#editor_top').append(dev);
            $('#show_variant_code').click(function(){
                $('#variant_code').val(JSON.stringify(/*BlackTri.variant*/BTVariantsData, null, '\t')).slideToggle();

            })
        }


    });



})();


