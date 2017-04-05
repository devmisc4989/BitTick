<!DOCTYPE html>
<html data-ng-app="sms">

<head>
    <meta charset="utf-8"/>
    <title><?=$pageTitle?></title>
    <link rel="stylesheet" href="<?=$baseurl?>js/bootstrap-3.2.0-dist/css/bootstrap.css"/>
    <link rel="stylesheet" href="<?=$baseurl?>css/universal-styles.css"/>
    <link rel="stylesheet" href="<?=$baseurl?>css/sms-ui/sms-ui-styles.css"/>

    <script src="<?=$baseurl?>js/jQuery-lib/jquery-1.11.1.js"></script>
    <script src="<?=$baseurl?>js/angular-resources/angular-1.2.18.js" ></script>
    <script src="<?=$baseurl?>js/angular-resources/angular-1.2.18-route.min.js" ></script>
    <script src="<?=$baseurl?>js/angular-resources/ui-bootstrap-tpls-0.11.0.min.js"></script>
    <script type="text/javascript">
        var jsDir       = '<?=$jsdir?>';
        var baseurl     = '<?=$baseurl?>';
        var isEditor    = <?=$isEditor?>;
    </script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-app.js"></script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-directives.js"></script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-services.js"></script>
    <style>
        .checkbox, .radio {
            margin-bottom: 0
        }

        .alert {

            margin-bottom: 10px;
            padding: 8px 15px;
        }

        #overlay, #overlay_bg {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            top: 0;
            z-index: 100;
        }

        #overlay_bg {
            background: #000050;
            opacity: .6
        }

        #mock_page {
            background: #ffffff;
            width: 960px;
            position: absolute;
            left: 50%;
            margin-left: -480px;
            top: 10px;
            bottom: 10px;
            z-index: 1000;
            border-radius: 10px;
            padding: 10px
        }

        .active .thumbnail {
            background: green
        }
    </style>

</head>

<body ng-controller="MainCtrl" class="etracker">
<!--Overlay for Preview -->
<div ng-init="showoverlay=false" id="overlay" ng-show="showoverlay">
    <div id="overlay_bg"></div>
    <div id="mock_page">
        <button class="btn btn-warning pull-right" data-ng-click="showoverlay=false"
                style="z-index: 991120; position: absolute; right: 0; top: 0">Close
        </button>
        <div style="position: absolute; bottom: 10px; left: 20px; right: 10px;">
            <h3 class="pull-left">Mock Page</h3>
        </div>
        <div id="sms_content" style="position: relative; height: 100%"></div>
    </div>

</div>
<div class="container-fluid" style="margin: 0 10px;">

    <div class="row">

        <div class="col-md-12">
            <span class="pull-right"><br>
                <button class="btn btn-warning " type="button" data-ng-click="cancelUI()">Cancel</button>
            </span>
            <h3>Create SmartMessage</h3>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-4">
                    <form novalidate name="sms_form">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h4>Select a Rule</h4>
                                <div data-ng-repeat="rule in data.rules">
                                    <div class="col-md-6">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" value="{{rule.value}}" data-ng-model="sms.rule"
                                                       ng-change="setRuleActive(rule)">
                                                {{rule.label}}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group" ng-show="rule.durations && rule.value==sms.rule">
                                        <div>
                                            Duration: <select data-ng-options="o for o in rule.durations"
                                                              ng-model="sms.duration"></select>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </li>
                            <li class="list-group-item" ng-show="sms.rule">
                                <h4>Select Message Type</h4>

                                <div class="col-md-6" data-ng-repeat="msg in data.message_types">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" value="{{msg.value}}" data-ng-model="sms.message_type"
                                                   ng-change="updateTemplates()"
                                                   ng-disabled="disableMessageType(msg.value)">
                                            {{msg.label}}
                                        </label>
                                    </div>
                                </div>
                                <div class="clearfix"></div>

                            </li>
                            <li class="list-group-item" ng-show="sms.message_type">
                                <h4>Select Content Type</h4>

                                <div class="col-md-6" data-ng-repeat="cont in data.content_types">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" ng-model="cont.selected"
                                                   ng-change="updateTemplates()">
                                            {{cont.label}}
                                        </label>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </li>

                        </ul>

                    </form>
                    <!--<h4>SMS Data
                        <small>(Bound to form live)</small>
                    </h4>
                    <pre>{{sms|json}}
                    </pre>-->
                </div>
                <div class="col-md-4">

                    <div>
                        <h3>Templates
                            <small> Click template to make ajax request for attributes and create form</small>
                        </h3>
                        <div class="col-md-3" data-ng-repeat="template in templates">

                            <small>M_type: {{template.message_type}}<br>C_type: {{template.content_type}}</small>
                            <div data-ng-click="getTemplateData(template.sms_template_id)" style="cursor: pointer"
                                 ng-class="{active: template.sms_template_id==currTemplate.id}">

                                <div class="thumbnail">
                                    <img style="width: 100%" ng-src="{{template.thumbnail_url}}">
                                </div>
                                <div><strong>{{template.name}}</strong></div>
                            </div>


                        </div>
                        <h4 ng-hide="templates.length" class="text-danger">No templates matching selections</h4>
                    </div>
                    <div class="clearfix"></div>

                    <!--<h4>Current template data</h4>
                    <pre>{{currTemplate|json}}</pre>-->

                </div>
                <div class="col-md-4" ng-show="currTemplate.areas">

                    <h4>Configure your message for: {{currTemplate.label}}</h4>

                    <form class="form" name="liveTemplate">
                        <div class="list-group">
                            <div data-ng-repeat="area in currTemplate.areas" class="list-group-item">
                                <span class="label label-default pull-right">Area {{$index+1}}</span>
                                <h4 ng-if="area.label" style="color:red">{{area.label}}</h4>

                                <div ng-if="area.selector" area-selector="{{area.selector.type}}"></div>
                                <div style="height: 15px"></div>
                                <div ng-repeat="block in area.blocks">
                                    <div data-ng-repeat="field in block.attr">
                                        <div attribute-field depends="{{area.selector.name}}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="button" data-ng-click="getHtml()">Preview</button>
                            <button class="btn btn-success" type="button" data-ng-click="saveSms()">Save</button>
                            <button class="btn btn-default" type="button" data-ng-click="cancelUI()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/ng-template" id="newSmsModal.html">
        <?php $action= 'action="'.$postNewSmsUrl.'"';?>
        <form name="urlform.form" <?=$action;?> method="post" novalidate >
            <input type="hidden" ng-value="form.sms" name="sms">
            <input type="hidden" ng-value="form.template" name="template">
            <div class="modal-header">
                <h3 class="modal-title">Enter page URL</h3>
            </div>
            <div class="modal-body">

                    <div class="form-group">
                        <label >URL</label>
                        <input type="text" class="form-control" name="url" ng-model="form.url" placeholder="http://" required>
                    </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary"   ng-disabled="urlform.form.$invalid">Proceed</button>
                <button type="button" class="btn btn-warning" ng-click="cancel()">Cancel</button>
            </div>
        </form>
    </script>

</body>

</html>
