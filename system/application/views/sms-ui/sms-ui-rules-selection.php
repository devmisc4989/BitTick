<div class="sms-ui border">

    <h4 class="module-heading">{{translations.headings.select_trigger}}</h4>
    <!--<div class="spacer"></div>-->
    <div class="row">
        <div rules-height id="rules">
            <div class="col-sm-6 sms-rule-outer  rule_{{$index}}" data-ng-repeat="rule in rules" ng-class="{'even': $index % 2 ==1}">
                <div ng-if="rule.disabled" class="rule-upgrade-mask">
                    <a class="upgrade-link etracker-rocket" href="<?=$this->config->item('etracker_product_upgrade');?>" target="_blank">
                        <i class="fa fa-rocket"></i>
                    </a>
                </div>
               <div class="sms_rule_wrap">
                    <div class="sms-rule" ng-class="{'bottom-border': $index < 2, disabled: rule.disabled, pointer: !rule.disabled}">
                        <label>
                            <img ng-src="{{rule.thumbnail_url}}">
                            <input type="radio" value="{{rule.value}}" data-ng-model="sms.rule" ng-change="setActiveRule(rule)" ng-disabled="rule.disabled">
                            {{rule.label}}:<br>

                        </label>
                        <div class="rule_discription">{{rule.description}}</div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>

        <div class="col-sm-12">
            <div class="sms-rule" ng-show="activeRule.durations">
                <div class="form-group">
                    <label>{{translations.descriptions.duration_display_before}}: </label>
                    <select data-ng-options="o for o in activeRule.durations" ng-model="sms.duration"></select> {{translations.descriptions.duration_display_after}}
                </div>
            </div>
        </div>

        <div class="col-sm-12"><div class="action-buttons right ">
            <span class="button button-cancel" ng-click="cancel()">
                <span ng-if="!isEditor">{{translations.buttons.rules_section_cancel}}</span>
                <span ng-if="isEditor">{{translations.buttons.cancel_add_sms}}</span>
            </span>
            <span class="button button-ok" ng-class="{disabled: !activeRule}" ng-click="next()">{{translations.buttons.select_design}}</span>
            <div class="clearfix"></div>
        </div>
        </div>
        <div class="clearfix" end="rules"></div>
    </div>
</div>

<!--<pre>{{translations|json}}</pre>-->