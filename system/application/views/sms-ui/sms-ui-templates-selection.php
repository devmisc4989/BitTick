<div class="sms-ui-preview-overlay" ng-show="showPreview" class="ng-hide"></div>
<div sms-preview class="sms-preview image-preview"  ng-show="showPreview" class="ng-hide">

    <div class="sms-preview-image">
        <img ng-if="showPreview" ng-src="{{baseurl + activeTemplate.previewimage_url}}">
    </div>

    <div ng-if="showPreview" class="preview-message {{template.message_type}}">
        <span preview-message class="button button-ok preview-message-button" >{{translations.buttons.hide_preview}}</span>
    </div>

</div>

<div id="template-ui" class="sms-ui full-size border">

    <h4 class="module-heading">{{translations.headings.select_design}}</h4>

    <p>
        {{translations.descriptions.select_design}}
    </p>
    <hr>
    <div class="row">
        <div class="col-sm-6">
            <span class="bt-badge">1</span>
            <strong>{{translations.headings.content_type}}</strong>
            <select class="template-dropdown" data-ng-options="o.value as o.label for o in contentTypes" ng-model="selected.contentType"  ng-change="searchTemplates()">
                <option value="">{{translations.messages.show_all}}</option>
            </select>
        </div>

        <div class="col-sm-6">
            <span class="bt-badge">2</span>
            <strong>{{translations.headings.message_type}}</strong>
            <select data-ng-options="o.value as o.label for o in messageTypes" ng-model="selected.messageType" ng-change="searchTemplates()">

            </select>
        </div>

    </div>
    <hr>
    <div class="row">

        <div id="template_groups_column" class="col-sm-3  template-display-column" max-column>
            <div class="template-display-column-inner">
                <div class="template-column-heading">
                    <span class="bt-badge">3</span>
                    <strong>{{translations.headings.select_group}}</strong>
                </div>
                <div class="spacer"></div>
                <div class="template-group" ng-repeat="item in filteredGroups"
                     ng-click="setActiveGroup(item)">
                    <img ng-src="{{baseurl + item.thumbnail_url}}"
                     ng-class="{'border': item.sms_template_group_id==activeGroup.sms_template_group_id}">
                </div>
                <div ng-show="!filteredGroups.length">
                    {{translations.messages.no_matching_results}}
                </div>

                <pre ng-if="DEVELOPMENT.showDevData">Rule message types:<br>{{activeRule.message_types|json}}</pre>
            </div>
        </div>
        <div id="templates_column" class="col-sm-9  template-display-column left-border" max-column>

            <div class="template-display-column-inner">
                <div class="template-column-heading">
                    <span class="bt-badge">4</span>
                    <strong>{{translations.headings.select_template}}</strong>
                </div>
                <div class="clearfix spacer"></div>


                        <div class="template-item-wrap pointer"
                             ng-class="{border: activeTemplate.sms_template_id == template.sms_template_id, 'col-sm-12':  template.message_type == 'message_bar' , 'col-sm-6': template.message_type != 'message_bar'  }"
                             ng-repeat="template in templates=(activeGroup.templates | templateFilter)"
                             ng-click="setActiveTemplate(template)">

                    <div class="template-item" ng-class="{'message_bar':template.message_type == 'message_bar'}">
                        <div  ng-if="DEVELOPMENT.showDevData"><code style="font-weight: bold"> BT template ID {{template.sms_template_id}}</code> </div>
                        <div><img ng-src="{{baseurl + template.thumbnail_url}}" class="{{template.message_type}}"></div>
                        <div class="template-info">
                            <a ng-if="DEVELOPMENT.allowPreview" class="preview-link" ng-click="fullPreview(template)">{{translations.descriptions.show_full_screen}}</a>

                            <div class="template-details">

                               <div ng-if="DEVELOPMENT.showDevData">
                                   <pre>Message type: {{template.message_type}}</pre>
                                   <pre>Content Type: {{template.content_type}}</pre>
                               </div>
                                <div class="col-md-12" ng-bind-html="template.description"></div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div ng-show="!templates.length">
                    {{translations.messages.no_matching_results}}
                </div>
                <div class="clearfix"></div>
            </div>

        </div>


    </div>
    <div class="action-buttons right col-sm-12">
        <span class="button button-cancel" ng-click="cancel()">{{translations.buttons.return_to_rules}}</span>
        <span class="button button-ok" ng-class="{disabled: !activeTemplate}" ng-click="next()">{{translations.buttons.configure_message}}</span>

        <div class="clearfix"></div>
    </div>
    <div class="clearfix" end="design"></div>

</div>
