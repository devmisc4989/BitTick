var app= angular.module('sms',['ngRoute','ngSanitize']);

window.onload = function () {
    /* send test message to editor*/
    var data= {action:'uiLoad'};
    parent.postMessage(JSON.stringify(data),'*');
};

app.run(function($rootScope, $location, SmsData, $timeout,$document){
    $rootScope.translations = sms_translations;

    $rootScope.$on("heightChange", function(evt,data){
        //var msgData=angular.extend(data,{action: 'heightChange'});
        SmsData.sendMessage('heightChange', data);
    });

    /********   SHOW DEV DATA ****/
    $rootScope.DEVELOPMENT={
        showDevData:false,
        allowPreview:true,
        hideTemplateCloseButtons: true
    }
    
    /****************************/

    function loadFromEditor(e){
        var rData = $.parseJSON(e.data);
        if(rData.action === 'edit'){
            angular.extend(SmsData.sms, rData.ui);

            var templateRule = rData.ui.rule;
            var rules= SmsData.RULES.rules;

            if(!SmsData.activeRule){
                $.each(rules, function(_, rule){
                    if(rule.value === templateRule){
                        bt_logmsg('Found match');
                        SmsData.activeRule=rule;
                    }
                });
            }

            $.each(SmsData.RULES.template_groups, function(_,group){
                $.each(group.templates, function(_, template){
                    if(template.sms_template_id === SmsData.sms.template.id){
                        SmsData.activeTemplate = template;
                    }
                });
            });

            $timeout(function(){
                $location.path('/message-config/edit');
            });
        }

        if(rData.action === 'cancel'){
            SmsData.resetInEditor();
            $timeout(function(){
                 $location.path('/');
            });
        }

        if(rData.action === 'setHeight'){
            bt_logmsg('setHeight', rData.height);
            var parentH =  rData.height;
            $rootScope.$broadcast('parentHeightSet', parentH);
            var maxColumnsHeight= parentH-280;
            $rootScope.maxColHeight= maxColumnsHeight;
            $rootScope.parentH=parentH;
            $('.template-display-column').css('maxHeight', maxColumnsHeight);
            $('#sms-variants-config').css('maxHeight', maxColumnsHeight);
        }
    }
    
    // Some browsers will work with addEventListener, some others with attachEvent
    if (window.addEventListener) {
        addEventListener("message", loadFromEditor);
    } else if (window.attachEvent) {
        window.attachEvent("message", loadFromEditor);
    }
});

app.config(function($routeProvider){
    $routeProvider
        .when('/', {
            templateUrl: 'sms-rules-selection',
            controller: 'RulesController',
            resolve: {
                alltemplates: function (SmsData) {
                    return SmsData.getRules();
                }
            }
        }).when('/templates', {
            templateUrl: 'sms-templates-selection',
            controller: 'TemplatesController',
            resolve: {
                alltemplates: function (SmsData, $location, $q) {

                    if(!SmsData.activeRule){
                        bt_logmsg('Has no active rule in resolve');
                        var deferred =  $q.defer();
                        deferred.reject();
                        $location.path('/');
                        return deferred.promise;
                    }else{
                        return SmsData.getRules();
                    }

                }
            }
        }).when('/message-config/:edit?', {
            templateUrl: 'sms-message-config',
            controller: 'MessageConfigController',
            resolve: {
                smsTemplate: function (SmsData, $location, $q) {
                    var deferred =  $q.defer();
                    if(SmsData.sms.template){
                        bt_logmsg('Has template in route resolve');
                        deferred.resolve( SmsData.sms.template);
                    }else{
                        bt_logmsg('NO sms.template in resolve');
                        if(!SmsData.activeTemplate ){
                            $location.path('/');
                            deferred.reject();

                        }else{
                            var template_id=SmsData.activeTemplate. sms_template_id;
                           SmsData.getTemplateAttr(template_id).then(function(data){
                               SmsData.sms.template=data;
                                deferred.resolve(data);
                            });
                        }
                    }

                    return deferred.promise;
                }
            }
        }).when('/select-url', {
            templateUrl: 'sms-select-url',
            controller: 'SelectURLController',
            resolve:{
                smsData:function(SmsData, $location, $q){
                    var deferred =  $q.defer();
                    if(!SmsData.sms.template){
                        $location.path('/');
                        deferred.reject();
                    }else{
                        deferred.resolve();
                    }

                    return deferred.promise;
                }
            }
        }).otherwise({
            redirectTo: '/'
        });

});

app.filter('templateFilter',function(SmsData){
    return function(templates/*, contentType, messageType*/){
        return   $.map(templates, function( template){
            var isMatch =SmsData.templateMatchesSearch(template);
            if(isMatch){
                return template;
            }
        });
    };
});

bt_logmsg = function(msg){
    if(window.console){
        console.log(msg);
    }
};