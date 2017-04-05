<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$tenant = $this->config->item('tenant');

// for pages which require SSL, set the base url to SSL
$pathname1 = $this->uri->segment(1);
$pathname2 = $this->uri->segment(2);
if (in_array($pathname1, $this->config->item('ssl_requiring_pageurls')) || in_array($pathname2, $this->config->item('ssl_requiring_pageurls'))) {
    $mybaseurl = $basesslurl;
} else {
    $mybaseurl = $baseurl;
}
if (isset($force_ssl))
    if ($force_ssl)
        $mybaseurl = $basesslurl;

header('X-UA-Compatible: IE=9');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=IE9"/>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo config_item('charset'); ?>" />
        <meta http-equiv="Content-language" content="de" />
        <meta name="google-site-verification" content="2zM_710VjIeJwaO4ezq79erJe75jcBOPLNtBOk5nWEs" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <link href="<?php echo $mybaseurl . 'css/style.css' ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo $mybaseurl . 'js/fancybox/jquery.fancybox-1.3.4.css' ?>" rel="stylesheet" type="text/css" />
        <link href="<?php echo $mybaseurl . 'css/popup_new.css' ?>" rel="stylesheet" type="text/css" />
        <!--[if IE 7]>
        <link href="<?php echo $mybaseurl . 'css/ie7.css' ?>" rel="stylesheet" type="text/css"  />
        <![endif]-->
        <?php
        // set page title
        if ($tenant == 'dvlight')
            $title = "A/B Tester | Dashboard | DIVOLUTION&reg; - intelligent optimization";
        if (isset($title)) {
            echo "\n<title>" . $title . "</title>";
        }
        // set page description
        if (isset($description)) {
            echo "\n<meta name=\"description\" content=\"$description\" />\n";
        }
        // print each css files
        if (isset($css)) {
            foreach ($css as $cssUrl) {
                echo "<link type='text/css' href='$cssUrl' rel='stylesheet'/>";
            }
        }
        ?>
        <script type='text/javascript' src='<?php echo $mybaseurl . 'js/jquery-latest.js' ?>'></script>
        <?php
        // print each javascript files
        if (isset($js)) {
            foreach ($js as $jsUrl) {
                echo "<script type='text/javascript' src='$jsUrl'></script>\n";
            }
        }
        if (isset($others)) {
            echo $others;
        }
        ?>
        <script type='text/javascript' src='<?php echo $mybaseurl . 'js/fancybox/jquery.fancybox-1.3.4.js' ?>'></script>
        <script type='text/javascript' src='<?php echo $mybaseurl . 'js/fancybox/jquery.easing-1.3.pack.js' ?>'></script>
        <script type='text/javascript' src='<?php echo $mybaseurl . 'js/popup.js' ?>'></script>

        <?php
        //  google analytics for blacktri
        if ($tenant == 'blacktri') {
            ?>
            <script type="text/javascript">

                var _gaq = _gaq || [];
                _gaq.push(['_setAccount', 'UA-27812300-1']);
                _gaq.push(['_trackPageview', location.pathname + location.search + location.hash]);

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

            <?php
            // special styles for Divolution light
            if ($tenant == 'dvlight') {
                ?>
                <link href="<?php echo $mybaseurl . 'css/dvlight.css' ?>" rel="stylesheet" type="text/css" />	
            <?php } ?>

            <?php
            // special styles for etracker
            if ($tenant == 'etracker') {
                ?>
                <link href="<?php echo $mybaseurl . 'css/etracker.css' ?>" rel="stylesheet" type="text/css" />	
                <script type='text/javascript' src='<?php echo $mybaseurl . 'js/etracker.js' ?>'></script>
        <?php } ?>

    </head>
    <body class="<?php echo $tenant ?>">
        <?php
        if (!isset($hidenavi)) {
            include ('topnavi.php');
        } elseif (!$hidenavi)
            include ('topnavi.php');
        ?>