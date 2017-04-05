<?
// set output content type to Javascript
header('Content-type: application/x-javascript');
// If visitorId exists (!='NA'), then set the 3rd party and 1st party cookie, or overwrite it if already existing
if (is_numeric($visitorid)) {
    //create 3rd party coockies
    setcookie('GS3_v', $visitorid, time() + 31536000);
    // create 1stparty cookies
    ?>
    _bt.setCookie('GS1_v','<?php echo ($visitorid); ?>',365);
    <?php
}
?>
_bt.setReady('done');	
_bt.removePreloader();
// BTO webservice noop