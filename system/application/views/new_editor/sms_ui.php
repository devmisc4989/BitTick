<!DOCTYPE html>
<html data-ng-app="sms">

<head>
    <meta charset="utf-8"/>
    <title><?=$pageTitle?></title>
    <link rel="stylesheet" href="<?=$baseurl?>js/bootstrap-3.2.0-dist/css/bootstrap.css"/>
    <link rel="stylesheet" href="<?=$baseurl?>css/font-awesome/css/font-awesome.min.css"/>

    <link rel="stylesheet" href="<?=$baseurl?>css/universal-styles.css"/>
    <link rel="stylesheet" href="<?=$baseurl?>css/sms-ui/sms-ui-styles.css"/>
    <link rel="stylesheet" href="<?=$baseurl?>css/validationEngine.jquery.css"/>

    <script src="<?=$baseurl?>js/jQuery-lib/jquery-1.11.1.js"></script>
    <script src="<?=$baseurl?>js/angular-resources/angular-1.2.18.js" ></script>
    <script src="<?=$baseurl?>js/angular-resources/angular-1.2.18-route.min.js" ></script>
    <script src="<?=$baseurl?>js/angular-resources/angular-1.2.18-sanitize.min.js" ></script>
    <script src="<?=$baseurl?>jsi18n/jqueryValidationEngine.js" ></script>
    <script src="<?=$baseurl?>js/jquery.validationEngine-2.6.2.js"></script>
    <!--<script src="<?/*=$baseurl*/?>js/angular-resources/ui-bootstrap-tpls-0.11.0.min.js"></script>-->
    <script type="text/javascript">
        var sms_translations = <?= json_encode($translations); ?>;
        var sms_features = <?= json_encode($sms_features); ?>;
        var jsDir       = '<?= $jsdir ?>';
        var baseurl     = '<?= $baseurl ?>';
        var isEditor    = <?= $isEditor ?>;
    </script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-app.js"></script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-directives.js"></script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-services.js"></script>
    <script type="text/javascript" src="<?= $jsdir ?>sms-ui-controllers.js"></script>

</head>

<body class="etracker">
<div ng-view></div>

<script type="text/ng-template" id="sms-rules-selection">
    <?php $this->load->view('sms-ui/sms-ui-rules-selection');?>
</script>
<script type="text/ng-template" id="sms-templates-selection">
    <?php $this->load->view('sms-ui/sms-ui-templates-selection');?>
</script>
<script type="text/ng-template" id="sms-message-config">
    <?php $this->load->view('sms-ui/sms-ui-message-config');?>
</script>

<script type="text/ng-template" id="sms-select-url">
    <?php $sms_form_data=array('action'=>$postNewSmsUrl);?>
    <?php $this->load->view('sms-ui/sms-ui-select-url',$sms_form_data );?>
</script>

</body>

</html>
