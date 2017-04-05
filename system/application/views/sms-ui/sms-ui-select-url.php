<div class="sms-ui border">
    <h4 class="module-heading">{{translations.headings.web_integrate}}</h4>
    <p class="ubuntu">
        <strong>{{translations.descriptions.web_integrate_1}}</strong><br>
        {{translations.descriptions.web_integrate_2}}
    </p>
    <hr>
    <form method="post" action="<?= $action ?>" name="create_sms_form" id="create_sms_form" target="_parent">
        <input type="hidden" name="sms" ng-value="formData.editorPostData">
        <div id="url-controls">
            <input id="url-input" name="url" class="validate[custom[urlsmall]]" placeholder="http://">
        </div>
        <p class="help-block ubuntu">{{translations.descriptions.url_example}}</p>
        <div class="action-buttons right col-sm-12">
            <span class="button button-cancel" ng-click="cancel()">{{translations.buttons.return_to_config}}</span>
            <input type="submit" class="button button-ok" value="{{translations.buttons.open_in_editor}}">
            <div class="clearfix"></div>
        </div>

    </form>
    <div class="clearfix" end="url"></div>
</div>