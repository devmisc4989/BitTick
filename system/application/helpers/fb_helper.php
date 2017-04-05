<?php

/*
 * 
 *  function for getting and processing FB cookie
 */

function ProcessFBCookie() {
    $CI = &get_instance();
    //get cookie, based on app id
    $fbc = $_COOKIE["fbs_" . $CI->config->item('fb_app_id')];
    $fbc = trim($fbc, '\\"'); //remove " from cookie
    $nvc = array();
    parse_str($fbc, $nvc);
    ksort($nvc);
    if (!empty($nvc)) {
        $sHashCheck = "";
        foreach ($nvc as $key => $val) {
            if ($key != "sig")
                $sHashCheck.= $key . '=' . $val;
        }
        $sHashCheck.=$CI->config->item('fb_secret');
        $sHash = strtolower(md5($sHashCheck));
        //check hash			 
        if (!empty($sHash) && $sHash == $nvc["sig"]) {
            $nvc["validated"] = true;
        } else {
            $nvc["fb_fraud"] = "Post: " . print_r($_REQUEST, true) . "\nCookie: " . $fbc + "\nIP: " . $_SERVER["REMOTE_ADDR"];
            $nvc["validated"] = false;
        }
    }
    else
        $nvc["validated"] = false;
    return $nvc;
}

?>