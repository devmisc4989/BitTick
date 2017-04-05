app.controller('RulesController',function($scope,alltemplates,SmsData, $location){
    var IS_EDITOR = window.isEditor;

    var scope={
        activeRule : SmsData.activeRule,
        sms : SmsData.sms,
        isEditor: IS_EDITOR,
        rules:processDisabledRules( alltemplates.rules),
        setActiveRule : function(rule){
            $scope.activeRule = rule;
            SmsData.setActiveRule(rule);
        },
        next:function(){
            if($scope.activeRule){
                $location.path('/templates');
            }
        },
        cancel :function(){
            SmsData.sendMessage('close') ;
        }
    }


    angular.extend($scope, scope);

    function processDisabledRules(rules){
        /* passed as global from php controller */
        var allowedRules= sms_features.allowed_triggers;
        $.each(rules, function(_, rule){
            if($.inArray( rule.value, allowedRules ) ===-1){
                bt_logmsg('Rule =', rule.value)
                rule.disabled = true;
            }
        });
        return rules;
    }

});

app.controller('SelectURLController',function($scope,SmsData, $location, $rootScope){

    function saveSmsToSession(){
        var sms= SmsData.sms;
        sms.message_type =SmsData.activeTemplate.message_type;

        SmsData.saveSms(sms, function(resp){

            var uiData={
                id: resp.sms_id,
                html:resp.sms_html,
                sms:angular.copy(sms),
                template:angular.copy(SmsData.activeTemplate)
            }

            scope.formData.editorPostData = angular.toJson(uiData);
        });
    }
    
    // conditionally adds http:// to the URL entered by the user, then validates the form
    $("#create_sms_form").on('submit', function () {
        var url = $('#url-input').val().replace(/ /g, '');
        if (url.lastIndexOf('http', 0) !== 0) {
            var prefix = url.indexOf('//') === 0 ? 'http:' : 'http://';
            $('#url-input').val(prefix + url);
        }
        if ($("#create_sms_form").validationEngine('validate')) {
            return true;
        } else {
            $('#url-input').off('focus');
            if ($('#url-input').val() === 'http://') {
                $('#url-input').on('focus', function () {
                    $(this).val('');
                });
            }
        }
        return false;
    });

    var scope = {
        formData: {
            editorPostData: null
        },
        url: 'http://',
        cancel: function () {
            $location.path('/message-config');
        }
    };

    /** DEVELOPMENT ONLY **/
    if($rootScope.DEVELOPMENT.showDevData){
        scope.url='http://blacktri.com';
    }
    /**********************/
    angular.extend($scope, scope);
    saveSmsToSession();
});

app.controller('TemplatesController',function($scope,alltemplates,SmsData, $location){

    var scope={
        baseurl:baseurl,
        template_groups : alltemplates.template_groups,
        contentTypes    : alltemplates.content_types,
        messageTypes    : getMessageTypes(),
        activeRule      : SmsData.activeRule,
        selected: SmsData.selectedDropDowns,
        sms             : SmsData.sms,
        showPreview:false,
        activeGroup : SmsData.activeGroup,
        activeTemplate: SmsData.activeTemplate,
        setActiveTemplate:function(template){
            SmsData.setActiveTemplate(template);
            $scope.activeTemplate = template;
        },
        setActiveGroup:function(group){
            SmsData.setActiveGroup(group);
            $scope.activeGroup =  SmsData.activeGroup ;
            $scope.activeTemplate = null;
        },
        fullPreview : function(template){
            $scope.showPreview = true;
        },

        searchTemplates : filterGroups,
        next : function(){
            if(SmsData.activeTemplate){
                $location.path('/message-config');
            }
        },
        cancel : function(){
            $location.path('/');
        }
    };



    function setInitialMessageType() {
        var DEFAULT_MESSAGE_TYPE = 'popup';

        if (typeof (SmsData.sms.message_type) !== 'undefined' && SmsData.sms.message_type) {
            scope.selected.messageType = SmsData.sms.message_type;
        } else {
            var message;
            if ($.inArray(DEFAULT_MESSAGE_TYPE, scope.activeRule.message_types) !== -1) {
                message = DEFAULT_MESSAGE_TYPE;
            } else {
                message = scope.activeRule.message_types[0];
            }

            scope.selected.messageType = message;
        }
    }

    setInitialMessageType();

    filterGroups();

    angular.extend($scope, scope);



    function filterGroups(){
        var ruleMessageTypes=scope.activeRule.message_types;
        var matchingGroups=$.map(alltemplates.template_groups, function(group){
            var matchingTemplates= $.map(group.templates,function(template){
                if(scope.activeTemplate){
                    if(template.sms_template_id === scope.activeTemplate.id){
                        angular.extend(scope.activeGroup,group);
                    }
                }
                if(SmsData.templateMatchesSearch(template)){
                    return true;
                }
            });
            if (matchingTemplates.length){
                return group;
            }
        });

        $scope.filteredGroups= matchingGroups;
        if(matchingGroups.length && !scope.activeGroup.templates.length){
            angular.extend(scope.activeGroup , matchingGroups[0]);
            bt_logmsg('Should set first group');
        }
    }


    function getMessageTypes(){
        var allowedTypes = SmsData.activeRule.message_types;
        var results=$.map(alltemplates.message_types, function(item){
            if($.inArray(item.value, allowedTypes) >-1){
                return item;
            }
        });
        return results;
    }

    function setActiveGroup(){
        if(!SmsData.activeGroup){

           var group = scope.template_groups[0];
           SmsData.setActiveGroup(group);
           scope./*model.*/activeGroup=group;
        }

    }

});

var path = '';
app.controller('MessageConfigController',function($scope,SmsData,smsTemplate, $location, $route, $rootScope){

    var IS_EDITOR = window.isEditor;
    var IS_EDITOR_EDIT_MODE = $route.current.params.edit;


    function saveToEditor() {
        if (checkValidation()) {
            SmsData.sms.template = scope.template;

            if (!IS_EDITOR_EDIT_MODE) {
                SmsData.sms.message_type = SmsData.activeTemplate.message_type;
            }
            
            SmsData.saveSms(SmsData.sms, /* scope.template,*/ function (resp) {
                if (!resp.status) {
                    alert(resp.error);
                    return;
                }
                
                var uiData = {
                    id: resp.sms_id,
                    html: resp.sms_html,
                    sms: angular.copy(SmsData.sms),
                    template: angular.copy(scope.template)
                };

                scope.editorPostData = angular.toJson(uiData);
                if (IS_EDITOR) {
                    SmsData.sendMessage('save', angular.copy(uiData));

                    /* reset editor otherwise if user deletes SMS it will open at this step*/
                    SmsData.resetInEditor();
                }

            });
        }
    }

    var checkValidation = function () {
        var $visibleInput = $('#bt_sms_config_form').find('input[type="text"]').filter(':visible');
        var $visibleTextArea = $('#bt_sms_config_form').find('textarea').filter(':visible');
        if ($visibleInput.length > 0 || $visibleTextArea.length > 0) {
            if ($('#bt_sms_config_form').validationEngine('validate')) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    };

    var scope={
        intro:SmsData.activeTemplate.intro || false,
        isEditor: IS_EDITOR,
        isEditMode:IS_EDITOR_EDIT_MODE,
        editorPostData: null,
        showBranding : sms_features.has_branding,
        template : smsTemplate,
        templatethumb_url  : baseurl+SmsData.activeTemplate.thumbnail_url,
        cancel : function(){
            SmsData.storeAttributes(scope.template);
                $location.path('/templates');

        },
        validateNumeric: function (event) {
            var $elem = $(event.target);
            if ($elem.hasClass('int')) {
                var numberReg = /[\d]/;
                var codes = [0, 8, 9, 32];
                var kpressed = (document.all) ? event.keyCode : event.which;

                if ($.inArray(kpressed, codes) >= 0)
                    return true;

                var te = String.fromCharCode(kpressed);
                if (numberReg.test(te)) {
                    return true;
                } else {
                    event.preventDefault();
                    return false;
                }
            } else {
                return true;
            }
        },
        next: function () {
            if (checkValidation()) {
                $location.path('/select-url');
            }
        },
        showPreview:false,

        htmlPreview:function(){
            SmsData.getTemplateHtml(scope.template, function(html){
                var $sms=$('<div></div>').append(html);

                $('#html-preview').html($sms);
                /* prevent defualts within sms html */
                $('#html-preview *').on('click submit',function(e){
                    e.preventDefault();
                })
                $('#_bt_sms_overlay').addClass('no_show');
                if($rootScope.DEVELOPMENT.hideTemplateCloseButtons){
                   $('#_bt_sms_outer_close').attr('style','display:none!important');
                }
                $sms.find('#_bt_sms_main_container, #_bt_sms_wrapper').each(function(){
                    $(this).show();
                    var position=$(this).css('position');
                    if(position == 'fixed'){
                        $(this).css('position','absolute')
                    }
                });

                SmsData.sendMessage('smsPreview');

                $scope.showPreview=true;
            });
        },
        /**
         * Used in edit mode within editor
         */
        saveToEditor:saveToEditor
    }
    angular.extend($scope , scope);

});