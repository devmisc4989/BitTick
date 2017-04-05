
app.factory('SmsData', function($http, /*GetThumbs,*/ $location){

    // make basessl protocol relative
    baseurl = baseurl.replace('https:','').replace('http:','');
    var saveUrl=baseurl+'sms/saveSms';
    var rulesDataUrl=baseurl+'sms/getTemplateList';

    function getTemplateResourceUrl(id){
        return baseurl+'sms/getTemplateAttributes/?tempid='+id;
    }
    /* removed the annoying "alert" message, changed by a console log message*/
    function ajaxError(data){
        if (typeof (window.console) !== 'undefined') {
            window.console.log('AJAX Error!\n\nStatus: ' + data.status + '\n\n' + data.statusText);
        }
    }

    function templateMatchesSearch(template){
        var isMatch;

        var allowedMessageTypes =sms.activeRule.message_types;

        if($.inArray(template.message_type, allowedMessageTypes) === -1){
            isMatch = false;
            return isMatch;
        }

        var contentType=sms.selectedDropDowns.contentType;
        var messageType=sms.selectedDropDowns.messageType;
        /* show all when nothing selected */
        if(!contentType && !messageType){
            isMatch =true;
        }
        /* when only contentType selected */
        if(contentType && !messageType){
            isMatch= $.inArray(contentType, template.content_type) > -1;
        }
        /* when both selected */
        if(contentType && messageType){
            isMatch= template.message_type === messageType && $.inArray(contentType, template.content_type) > -1;
        }
        /* when only messageType selected */
        if(!contentType && messageType){
            isMatch= template.message_type === messageType
        }

        return isMatch;
    }

    var defaultDuration = 20;

    var sms={

        sms:{
           duration : defaultDuration,
           template : null,
           rule : null
        },
        activeRule:null,
        activeGroup:{templates:[]},
        activeTemplate:null,
        selectedDropDowns:{
            contentType:null,
            messageType:null
        },
        templateMatchesSearch: templateMatchesSearch,
        setActiveRule:function(rule){
            /* reset stored group and template */
            sms.setActiveGroup(null);
            sms.setActiveTemplate(null);
            angular.extend(sms.selectedDropDowns, {contentType:null,messageType:null});
            sms.activeRule = rule;
            sms.sms.rule = rule.value;
        },
        setActiveGroup:function(group){
            /* reset any existing stored template */
            sms.setActiveTemplate(null);
            sms.activeGroup = group ? group : {templates:[]};

        },
        setActiveTemplate: function(template){
            sms.sms.template=null;
            sms.activeTemplate = template;
        },

        setTemplateDropDowns:function(selected){
            angular.extend(sms.selectedDropDowns, selected);
        },

        resetInEditor:function(){
            sms.setActiveRule(false);
            $location.path('/');
        },

        sendMessage: function (action, data) {
            var messageData = {
                action: action
            };
            if (data) {
                messageData.uiData = data;
                bt_logmsg('Sending message', data);
            }
            parent.postMessage(JSON.stringify(messageData), '*');
        },
        getRules: function (callback) {
            return $http.get(rulesDataUrl, {cache: true}).then(function (res) {

                sms.RULES = res.data;
                return res.data;
            }, ajaxError);
        },
        getTemplateAttr: function (id, callback) {
            var url = getTemplateResourceUrl(id);

            return $http.get(url, {cache: true}).then(function (res) {
                var template = res.data;
                sms.mergeAttributesHistory(template);
                return res.data;
            }, ajaxError);
        },
        getTemplateHtml:function(templateData, callback){
            var url=baseurl+'sms/getPreview';

            var data={
                id: templateData.id,
                selectors:{},
                attributes:{}
            }



            /* flatten data to simpler objects*/
            $.each(templateData.areas, function(_,area){
                if(area.blocks){
                    $.each(area.blocks, function(_,block){
                        if(block.attr){
                            $.each(block.attr, function(_,attr){
                                data.attributes[attr.name]={type:attr.type, value:attr.value}
                            });
                        }
                    });
                }

                if(area.selector && area.selector.type !=='active'){
                    data.selectors[area.selector.name]={type:area.selector.type, value:area.selector.value};
                }
            });

            $http.post(url,data).then(function(res){ return res.data},ajaxError).then(callback);
        },
        storeAttributes:function(template){
            if(!template.areas){
                return;
            }
            $.each(template.areas, function(_,area){
                if(area.blocks){
                    $.each(area.blocks, function(_,block){
                        $.each(block.attr, function(_,attr){
                            sms.attrHistory[attr.name]=attr.value;
                        });
                    });
                }

            });

        },
        attrHistory:{},
        mergeAttributesHistory:function(newTemplate){
            $.each(newTemplate.areas, function(_,area){
                if(area.blocks){
                    $.each(area.blocks, function(_,block){
                        $.each(block.attr, function(_,attr){
                            if(sms.attrHistory.hasOwnProperty(attr.name)){
                                attr.value=sms.attrHistory[attr.name];
                            }

                        });
                    });
                }
            });
            return newTemplate;
        },

        saveSms:function(sms, /*templateData,*/ callback){

            var templateData=sms.template;
            var data={
                id: templateData.id,
                selectors:{},
                attributes:{}
            };

            this.storeAttributes(sms.template);

            /* flatten data to simpler objects*/
            $.each(templateData.areas, function(_,area){
                var SendAttributes=true;
                if(area.selector && area.selector.type ==='checkbox'){
                    SendAttributes=area.selector.value;
                }
                if(SendAttributes){
                    if(area.blocks){
                        $.each(area.blocks, function(_,block){
                            if(block.attr){

                                $.each(block.attr, function(_,attr){
                                    data.attributes[attr.name]={type:attr.type,db_type:attr.db_type, value:attr.value}
                                });
                            }
                        });
                    }

                    if(area.selector && area.selector.type !=='active'){
                        data.selectors[area.selector.name]={type:area.selector.type, value:area.selector.value};
                    }
                }
            });

            var postData={
                sms:sms,
                template:data,
                ui:{template:templateData, sms:sms}
            }

            return $http.post(saveUrl, postData).then(function(res){return res.data;},ajaxError).then(callback);

        }
    }

    return sms;
});