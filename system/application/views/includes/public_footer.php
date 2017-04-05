<?php
$imgurl = $this->config->item('image_url');
$imgsslurl = $this->config->item('image_ssl_url');
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
$fbappid = $this->config->item('fb_app_id');
$tenant = $this->config->item('tenant');

// if etracker, do not display foter navi
if ($tenant == 'etracker') {
    $hidenavi = true; // hidenavi can be set by the controller as well, we re-use it here to enforce hiding in case of etracker
}
if ($action == 'showdataonly') {
    $hidenavi = true; 
}



// for pages which require SSL, set the base url to SSL
$pathname1 = $this->uri->segment(1);
$pathname2 = $this->uri->segment(2);
if (in_array($pathname1, $this->config->item('ssl_requiring_pageurls')) || in_array($pathname2, $this->config->item('ssl_requiring_pageurls'))) {
    $myimgurl = $imgsslurl;
    $mybaseurl = $basesslurl;
} else {
    $myimgurl = $imgurl;
    $mybaseurl = $baseurl;
}
?>
<?php
if (!isset($hidenavi))
    $hidenavi = false;

if (!$hidenavi) {
    ?>
    <div id="footer_wrap">
        <div id="footer">
            <div id="footer_nav">
                <ul>
                    <?php if ($tenant != 'dvlight') { ?>
                        <li><a href="<?php echo $baseurl . $purl[$lg]['terms'] ?>"><?php echo $this->lang->line('link_terms'); ?></a><span>|</span></li>
                        <li><a href="<?php echo $baseurl . $purl[$lg]['imprint'] ?>"><?php echo $this->lang->line('link_imprint'); ?></a><span>|</span></li>
                        <li><a href="<?php echo $baseurl . $purl[$lg]['about'] ?>"><?php echo $this->lang->line('link_about'); ?></a></li>
                    <?php } else {
                        ?>
                        <li><a href="http://abtester.divolution.com/privacy/"><?php echo $this->lang->line('link_terms'); ?></a><span>|</span></li>
                        <li><a href="http://abtester.divolution.com/imprint/"><?php echo $this->lang->line('link_imprint'); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <div id="footer_right">
                <?php if ($tenant != 'dvlight') { ?>
                    <img src="<?php echo $myimgurl ?>footer_logo.png" />
                <?php } else { ?>
                    <strong>DIVOLUTION - Digital Revolution Technology GmbH</strong><br />
                    Westerfeldstr. 8 - 32758 Detmold<br />
                    <label>Telefon:</label> +49 5231 301695<br />
                    <label>E-Mail:</label> <a href="mailto:abtester@divolution.com">abtester@divolution.com</a><br />
                    <label>Internet:</label> <a href="http://www.divolution.com" target="_blank">http://www.divolution.com</a>
                <?php } ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function RepositionFooter()
        {
            var htmlHeight = $("body").outerHeight(true);
            var screenHeight = $(window).height();
            var innerHeight = $("#header_wrap").outerHeight(true) + $("#inner_bg").outerHeight(true);
            if (htmlHeight <= screenHeight)
                $("#footer_wrap").css("margin-top", screenHeight - innerHeight - $("#footer_wrap").height());
        }
        RepositionFooter();
    </script>
<?php } ?>

<?php
//double session checking
$clientid = $this->session->userdata('sessionUserId');
if (!$clientid && !$this->config->item('fb_disabled')) {
    ?>
    <script type="text/javascript" src="<?= $baseurl ?>js/fb.js"></script>
    <div id="fb-root"></div>
    <script type="text/javascript">
        window.fbAsyncInit = function() {
            FB.init({
                appId: '<?= $fbappid ?>',
                status: true, // check login status
                cookie: true, // enable cookies to allow the server to access the session
                xfbml: false, // parse XFBML
                channelUrl: '<?= $baseurl ?>includes/fbchannel.html', // Custom Channel URL
                oauth: false //enables OAuth 2.0, disabled for now
            });
            SetupFB(FB);
        };
        (function() {
            var e = document.createElement('script');
            e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
            e.async = true;
            document.getElementById('fb-root').appendChild(e);
        }());
    </script>
    <?php
}
?>
</body>
</html>