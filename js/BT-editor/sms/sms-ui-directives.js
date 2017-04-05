app.directive('maxColumn', function($rootScope){
    return {
        restrict:'A',
        link:function(scope,elem,attrs){
            if($rootScope.maxColHeight){
                elem.css('maxHeight',$rootScope.maxColHeight );
            }
        }
    }
});
/**
 * used to monitor window height to adjust rules heights based on parent window
 */
app.directive('rulesHeight', function($rootScope){
    return {
        restrict:'A',
        link:function($scope, elem){

            var windowMax = window.isEditor ? 700 : 600;
            var rulesAdjuster = window.isEditor ? 170 : 130;

            function adjustRulesHeight(parentH){
                var smallRules = parentH < windowMax;
                if( smallRules ){
                    var rulesMaxHt = parentH - rulesAdjuster;
                    var style={'max-height':rulesMaxHt+'px'};
                    elem.css(style).addClass('small-rules');
                }else{
                    elem.removeAttr('style').removeClass('small-rules');
                }
            }
            $scope.$on('parentHeightSet', function(evt, parentH){
                adjustRulesHeight(parentH);
            });

            if($rootScope.parentH){
                adjustRulesHeight($rootScope.parentH);
            }
        }
    }
});

app.directive('smsPreview', function(SmsData, $timeout){
    return {
        restrict:'A',
        link:function(scope, elem){
            elem.click(function(e){
                e.stopPropagation();
                $timeout(function(){
                    scope.showPreview = false;
                    SmsData.sendMessage('closePreview');
                })
            })
        }
    }
});

app.directive('previewMessage',function($timeout){
    return {
        restrict:'A',
        link:function(scope, elem){
            $timeout(function(){
                elem.fadeOut();
            },5000);
        }
    }
});

/* used to trigger height change to send to parent window for vertical centering */
app.directive('end',function($timeout, SmsData, $rootScope){
    return {
        restrict:'A',
        link:function(scope,element,attrs){
            var view = attrs.end;
            $timeout(function(){
                var bodyH=$('body').height()
                var data={
                    view: view,
                    height: bodyH
                }
                scope.$emit("heightChange", data);
                /*if(view == 'rules'){
                    var parentWidowH = $rootScope.parentH;
                    alert(parentWidowH)
                    if( bodyH > parentWidowH){
                        $('.sms-rule img').addClass('small');
                    }
                }*/

            });
        }
    }
});

/**
 * Evens height of side by side template pairs
 */
app.directive('templateItem',function($timeout){
    return {
        restrict: 'C',
        link:function(scope,elem,attrs){

            if(scope.$last){
                if(scope.selected.messageType != 'message_bar'){
                    $timeout(function(){
                        var $templates=$('.template-item');
                        if($templates.length > 1){
                            for(i=0; i< $templates.length; i= i+2){
                                var maxHt= 0;
                                $templates.slice(i,i+2).each(function(idx){
                                    var thisHt=$(this).height()
                                    maxHt = thisHt > maxHt ? thisHt : maxHt;
                                }).height(maxHt);
                            }
                        }
                    });
                }
            }
        }
    }
});

app.directive('smsUi', function(SmsData){
    return {
        restrict:'C',
        link: function (scope, elem) {
            elem.prepend('<a id="fancybox-close"></a>');
            $('#fancybox-close').click(function(e){
                e.preventDefault();
                SmsData.sendMessage('close');
            });
        }
    }
});

app.directive('attributeField', function($compile){
    return {
        restrict:'A',
        link:function(scope, elem,attrs){
            var field=scope.field, html;
           switch (field.type){

               case 'pulldown':
                   html='<div select-field  label="'+field.label+'" model-prop="field">';
                   break;
               case 'enum':
                   html='<div select-field  label="'+field.label+'" model-prop="field">';
                   break;

               default :
                   /* 3 types passed "int","text" and "string" -- "text" uses <textarea>*/
                   html='<div text-field label="'+field.label+'" input-type="'+field.type+'">';

           }

            if(html){
                elem.html(html);
                $compile(elem.contents())(scope);
            }
        }
    }
});

app.directive('areaSelector', function($compile){
    return {
        restrict:'A',

        link:function(scope,elem,attrs){
            var html;
            var type=attrs.areaSelector;

            switch(type){
                case 'pulldown' :
                    html='<div select-field  label="{{area.selector.label}}" model-prop="area.selector">';
                    break;
                case 'checkbox' :
                    html='<div class="checkbox sms-ui-checkbox-container">' +
                        '     <label>' +
                       /* '       <input type="checkbox"  ng-model="area.selector.value"  ng-change="sms.allowSave=false">' +*/
                        '       <input type="checkbox"  ng-model="area.selector.value" ><strong>{{area.selector.label}}</strong>' +
                        '      </label>' +
                        '       <div class="spacer" ></div> ' +
                        '  </div>'
                    break;
                default :
                    html='<div ng-show="area.selector.label"><label>{{area.selector.label}}</label></div>'
            }

            elem.html(html);
            $compile(elem.contents())(scope)
        }

    }
})

/**
 * Directive to create either text input or textarea
 *  based on types "int","string" or "text"
 */
app.directive('textField', function(){
    return {
        restrict: 'A',
        replace: true,
        template: function (elem, attrs) {
            var type = attrs.inputType;
            /*var field='    <input ng-model="field.value" type="text" class="form-control"  ng-change="sms.allowSave=false">';*/
            // var field='    <input ng-model="field.value" type="text" class="form-control" >';
            // var field='    <input ng-model="field.value" type="text" class="sms-input" >';
            var field = '    <input ng-model="field.value" type="text" class="col-sm-8  validate[required]  ' + type + '" ng-keypress="validateNumeric($event)">';

            if (type === 'text') {
                // field='<textarea class="form-control col-sm-8"  ng-model="field.value"></textarea>'
                field = '<textarea class="col-sm-8  validate[required]"  ng-model="field.value"></textarea>';
            }

            return '<div class="form-group" data-ng-hide="area.selector && field.display_val != area.selector.value">' +
                    '      <label class="col-sm-3" >{{field.label}}</label>' +
                    field +
                    '    </div>';
        }
    };
});


app.directive('selectField', function(){
    return {
        restrict:'A',
        replace:true,
        template:function(elem, attrs){
            return '<div class="form-group"  data-ng-hide="area.selector && field.display_val != null && field.display_val != area.selector.value">' +
                '      <label  class="col-sm-3">'+attrs.label+'</label>' +
                /*'    <select  class="form-control"' +*/
                '    <select  class="sms-input col-sm-8"' +
                '       ng-model="'+attrs.modelProp+'.value" ' +
               /* '       ng-options="o.value as o.label for o in  '+attrs.modelProp+'.options" ng-change="sms.allowSave=false">' +*/
                '       ng-options="o.value as o.label for o in  '+attrs.modelProp+'.options" >' +
                '    </select>' +
                '   </div>';
        }
    }
});