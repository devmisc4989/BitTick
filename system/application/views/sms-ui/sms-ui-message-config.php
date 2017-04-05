<div  sms-preview  class="sms-ui-preview-overlay ng-hide" ng-show="showPreview">

    <div ng-if="showPreview" class="preview-message html-preview  {{template.message_type}}">
        <span preview-message class="button button-ok  preview-message-button" >{{translations.buttons.hide_preview}}</span>
    </div>

</div>

<div sms-preview class="sms-preview ng-hide"  ng-show="showPreview">

    <div id="html-preview"><!--JS injected--></div>

</div>

<div id="config-ui" class="sms-ui full-size border">

    <h4 class="module-heading">{{translations.headings.message_config}}</h4>

    <p>
        {{translations.descriptions.message_config_1}}
    </p>
    <div class="row">
        <form id="bt_sms_config_form">
            <div id="sms-config" class="col-sm-12 form-horizontal" id="sms-variants-config" max-column>
                <div id="sms-config-intro" ng-if="intro" ng-bind-html="intro"></div>
                <!-- <div data-ng-repeat="area in template.areas" class="list-group-item attributes-list-item">-->
                <div data-ng-repeat="area in template.areas" class="attributes-list-item">
                    <h4 ng-if="area.label" style="color:red">{{area.label}}</h4>

                    <div ng-if="area.selector" area-selector="{{area.selector.type}}"></div>
                    <div ng-repeat="block in area.blocks">
                        <div data-ng-repeat="field in block.attr">
                            <div attribute-field depends="{{area.selector.name}}"></div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </form>
        <div class="clearfix"></div>

        <div class="col-sm-12" style="min-height: 110px">

            <hr ng-if="template.areas.length">
            <div><strong>{{translations.descriptions.preview}}</strong> {{translations.descriptions.preview_2}}</div>
            <div style="text-align: center">
                <img class="preview-image" ng-src="{{templatethumb_url}}">
            </div>

            <div ng-if="showBranding" id="powered-by-etracker">
                <a href="<?=$this->config->item('etracker_product_upgrade');?>" target="_blank">
                    <span class="power-by-rocket etracker-rocket">
                        <i class="fa fa-rocket"></i>
                    </span>
                    <span class="power-by-text">
                        {{translations.messages.powered_by_1}}<br>{{translations.messages.powered_by_2}}
                    </span>
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="action-buttons right">
            <span  ng-if="DEVELOPMENT.allowPreview" class="pull-left">
                <a class="preview-link" ng-click="htmlPreview()">{{translations.descriptions.show_full_screen}}</a>
            </span>
            <span  class="button button-cancel" ng-click="cancel()">
                <span >{{translations.buttons.return_to_design}}</span>
            </span>
            <span  ng-if="!isEditor" class="button button-ok" ng-click="next()">{{translations.buttons.select_url}}</span>
            <span  ng-if="isEditor" class="button button-ok" ng-click="saveToEditor()">{{translations.buttons.save_edit_changes}}</span>

        </div>
    </div>
    <div class="clearfix" end="config"></div>


</div>
