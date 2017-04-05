<!DOCTYPE html >
<html >
    <head>

        <meta charset="utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= $page_title; ?></title>
        <script type="text/javascript">
            var isIE = false;
        </script>
        <!-- Conditionals not supported in IE 10 -->
        <!--[if IE]>
        <script type="text/javascript">
            var isIE=true;
    
        </script>
        <![endif]-->

        <script type="text/javascript">
            var path = '<?php echo $this->config->item('base_ssl_url'); ?>';/* used by validation engine*/
            var num = 1;
            //var variantid = '1_';
            var EmptyNamePH = '<?= $this->lang->line('Please enter the name here'); ?>';
            var NewVariant = '<?= $this->lang->line('Variant label'); ?>';
            var EnableLog = true; /* used in Log() function*/
            var DisablePopupNotice = false;

            var BTIsEditable = true;
            var BTCurrentTab = 0;
            var BTRenamingIndex = 0;
            var BTMenuWidth = 150;
            var BTTestType = "visual";//or split
            var BTTenant = '<?= $tenant ?>';
            /* todo not sure what this is*/
            var BTCurrentApproach = 1;
            var BTEditorUrl = '<?= $editor_url ?>';

            var BTeditorVars = <?= json_encode($BTEditorVars); ?>;

<?php
if ($this->config->item('nicEditButtonPath') != "") {
    echo 'var btoIconPath = "' . $this->config->item('nicEditButtonPath') . '";';
}
?>


        </script>
        <?php if ($exist_test_data) { ?>
            <!-- Only prints for existing tests-->
            <script>
                var BTVariantsData = <?= $exist_test_data['variantsdata'] ?>;
                var BTTrackingCodeData =<?= json_encode($exist_test_data['trackingcodedata']) ?>;
                var BTCurrentGoals =<?= json_encode($exist_test_data['tracked_goals']) ?>;
                var BTCurrentApproach = <?= $exist_test_data['tracking_approach'] ?>;
                var BTCurrentPersomode = <?= json_encode($exist_test_data['persomode']) ?>;
                var BTControlRuleId = <?= json_encode($exist_test_data['controlrule']) ?>;
            </script>
        <?php } ?>
        <?php
        // print each css files
        if (isset($css)) {
            foreach ($css as $cssUrl) {
                $href = $basesslurl . $cssUrl;
                echo "<link type='text/css' href='$href' rel='stylesheet'/>\n";
            }
        }
        ?>

        <script type='text/javascript' src='<?php echo $basesslurl . 'js/jQuery-lib/jQuery-1.8.3.min.js' ?>'></script>

        <?php
        // print each javascript files
        if (isset($js)) {
            foreach ($js as $jsUrl) {
                $src = $basesslurl . $jsUrl;
                echo "<script type='text/javascript' src='$src'></script>\n";
            }
        }
        if (isset($others)) {
            echo $others;
        }

        if(isset($faviconurl)) {  
        ?>
        <link rel="Shortcut Icon" type="image/x-icon" href="<?php echo $faviconurl ?>" />
<?php
}
?>


<?php //  google analytics for blacktri
if ($tenant == 'blacktri') {
    ?>
            <script type="text/javascript">
                      var _gaq = _gaq || [];
                      _gaq.push(['_setAccount', 'UA-1870369-3']);
                      _gaq.push(['_trackPageview']);

                      (function() {
                          var ga = document.createElement('script');
                          ga.type = 'text/javascript';
                          ga.async = true;
                          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                          var s = document.getElementsByTagName('script')[0];
                          s.parentNode.insertBefore(ga, s);
                      })();
            </script>
            <link href='https://fonts.googleapis.com/css?family=Ubuntu:400,300,500,700' rel='stylesheet' type='text/css'>
        <?php } ?>



        <?php // special styles for etracker
        if ($tenant == 'etracker') {
            ?>
            <link href="<?php echo $basesslurl . 'css/etracker.css' ?>" rel="stylesheet" type="text/css" />
        <?php } ?>

        <?php // special styles for Default Blacktri
        if ($tenant == 'blacktri') {
            ?>
            <link href="<?php echo $basesslurl . 'css/blacktri.css' ?>" rel="stylesheet" type="text/css" />
<?php } ?>

    </head>
    <body id="editor_page" class="<?php echo $tenant ?>">

