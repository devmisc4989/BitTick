;(function (bt) {
    "use strict";

    var $ui, /* iframe wrapper */
        $smsElem; /* sms element in client page*/

    var sms_url=BTeditorVars.BaseSslUrl+'editor/sms/'

    var sms = {
        show_sms: BTeditorVars.show_sms,
        ui:null,
        sendUI:function(data){
            sms.ui.postMessage(JSON.stringify(data),'*');
        },
        receiveUI: function (evt) {
            var rData = $.parseJSON(evt.data);
            var action = rData.action;
            if (action) {
                if (!sms.UIactions[action]) {
                    alert('[DEV Error] Action ' + action + ' not set up');
                } else {
                    var data = rData.uiData || null;
                    sms.UIactions[action](data);
                }
            } else {
                /* strange issue ocurs here when loading https://blacktri-dev.de/editor?url=http%3A%2F%2Fwww.google.de
                 * It seems like google page is able to pass an event also
                 * see BLAC-311 for more */
            }
        },
        init: function () {
            sms.upgradeOldTests();
            $ui=$('#sms_ui');
            sms.ui=$('#sms_ui_frame')[0].contentWindow;
            sms.sendHeightToUI();
            if (sms.show_sms) {
                $ui.fadeIn('slow');
            }
        },
        close:function(){
            sms.sendUI({action:'cancel'});
            sms.hideUI();
        },

        sendHeightToUI:function(){
            var currHeight=$(window).height();
            var heightData= {action: 'setHeight', height: currHeight};
            sms.sendUI(heightData);
        },

        showUI:function(){
            $('#full_editor_overlay').show();
            $ui.fadeIn('slow');
            sms.sendHeightToUI();
        },

        hideUI:function(){
            $('#full_editor_overlay').fadeOut('slow');
            $ui.fadeOut('slow');
        },

        loadSmsUiframe:function(variantId){
            var url=sms_url;
            if(variantId){
                alert('Not set up for variant id yet')
            }
        },
        /* make old tests SMS compatible */
        upgradeOldTests: function () {
            var activePageData = BTVariantsData.pages[BTVariantsData.activePage];
            if (typeof (activePageData) !== 'undefined') {
                $.each(activePageData.variants, function () {
                    if (!this.sms) {
                        this.sms = {id: null, template: null}
                    }
                });
            }
        },
        setNavState:function($tab){
            var variant=bt.variant;
            var hasSMS = typeof (variant.sms.template) === 'object' && variant.sms.template !== null;
            /* toggling class "has_sms" on tab controls display of various nav links*/
            $tab.toggleClass('has_sms', hasSMS);

            if($smsElem){
                var isVisible=$smsElem.is(':visible');

                $tab.find('.sms_toggle a').text(function(){
                    /* translations stored as data attribute */
                    var textData=$(this).data();
                    return isVisible ? textData.hide : textData.show;
                });
            }
        },
        toggle_sms:function(){
           $smsElem.toggle();
        },
        delete_sms:function(callback){
            bt.history.store('delete_sms','sms');
             var url=BTeditorVars.BaseSslUrl + 'sms/deleteSms';
            $.post(url,{smsid:bt.variant.sms.id}, function(data){

                if(data.status){
                    $smsElem.remove();
                    bt.variant.sms={
                        id: null,
                        template:null
                    };
                    delete bt.variant.dom_modification_code['[SMS]'];
                    delete bt.variant.dom_modification_code['[SMS_HTML]'];
                    if(callback){
                        callback();
                    }
                }else{
                    alert("AJAX server error delete SMS");
                }
            },'json');
        },

        saveSmsElementStyle:function(cssStyleObj){

            var domPath= bt.GetDomPath(bt.$el[0], true);
            var smsHtml=bt.variant.dom_modification_code['[SMS_HTML]'];
            var $div=$('<div></div>').append(smsHtml);
            $div.find(domPath).setstyle(cssStyleObj, 'important');
            bt.variant.dom_modification_code['[SMS_HTML]'] = $div.html();
            console.log('style', cssStyleObj)
            console.log(domPath)
        },

        undelete:function(smsid, callback){
            var url=BTeditorVars.BaseSslUrl + 'sms/undeleteSms';
             $.post(url,{smsid:smsid},callback,'json').error(function(){
                alert('Undelete ajax error');
            });
        },
        /* passes sms data back to UI */
        edit_sms:function(){
            var uiData={
                action:'edit',
                ui: bt.variant.sms.ui,
                template:bt.variant.sms.template
            }

            //$ui.show();
            sms.showUI();
            sms.sendUI(uiData);
        },
        insertSMS:function(data){
            bt.history.store('save_sms','sms');
            function doInsert(){
                bt.variant.dom_modification_code['[SMS_HTML]']=data.html;
                $.extend(bt.variant.sms, {ui: data.sms, template: data.template, id: data.id});

                /* click event will load page again and subsequently call sms.insertHtml() below*/
                $('#menu_tabs .tab.selected').click();
            }

            /* delete existing one first due to session state saving*/
            if(bt.variant.sms.id){
                sms.delete_sms(doInsert);
            }else{
                doInsert();
            }
        },
        /* gets called from BlackTri.selectTab as well as above */
        insertHtml:function(){
            /* could call smartmessaging method here */
                var SMS_html=bt.variant.dom_modification_code['[SMS_HTML]'];
                if(SMS_html){
                    var $div=$smsElem=$('<div>'+SMS_html+'</div>');
                    $smsElem=$div.children().show();
                    /* remove protocol from iFrame URL so it uses "https" or it gets blocked for secure content */
                    $smsElem.find('iframe').attr('src',function(_ , src){
                        src = src.replace(/^http[s]?\:/, '');
                        return src;
                    });

                    $(bt.clientDocument).find('body').append($smsElem);
                }else{
                    $smsElem=false;
                }
        },
        new_sms:function(){
            sms.loadSmsUiframe(false);
            //$ui.fadeIn('slow');
            sms.showUI();
        },
        /**
         * Tests if element in editor is any part of an SMS
         * @returns {boolean}
         */
        isSmsElem:function(){
            if(!$smsElem){
                return false
            }else{
                return bt.$el.closest($smsElem).length > 0;
            }
        },
        /**
         * Tests if element is a main outer element of an SMS
         * Used to prevent certain editor actions to display in element dropdown menu in editor
         * @param $el
         * @returns {boolean}
         */
        isSMSMainElement:function($el){
            var id= $el[0].id;
            if(!id){
                return false;
            }else{
                var smsMainEls=['_bt_sms_inner_container', '_bt_sms_wrapper','_bt_sms_overlay','_bt_sms_main_container'];
                return $.inArray( id , smsMainEls ) > -1;
            }

        },
        /* represents event messages sent from  UI in iFrame */
        UIactions:{
            close:function(data){
                /* simple close method in UI iif needed */
                sms.close();
            },
            uiLoad:function(){
                /* fires when UI frame loads */
            },

            save:function(data){
                sms.insertSMS(data);
                sms.close();
            },
            heightChange:function(data){
                if(data.view === 'rules'){
                    data.height = 600
                }
                centerSmsUI(data.height);
            },
            smsPreview:function(data){
                $ui.css('top',0);
            },
            closePreview:function(data){
                $ui.css('top', $ui.data('top'));
            }
        }
    }
    
    // Some browsers will work with addEventListener, some others with attachEvent
    if (window.addEventListener) {
        addEventListener("message", sms.receiveUI);
    } else if (window.attachEvent) {
        window.attachEvent("message", sms.receiveUI);
    }

    function centerSmsUI( uiHt){
        var windowH=$(window).height();
        var $ui=$('#sms_ui');
        var top =((windowH -uiHt) /2) - 30;
        if(top < 0){
            top = 0
        }
        $ui.css({top: top}).data('top', top);
    }

    bt.sms = sms;

})(BlackTri);
