<?php

function trim_string($str, $len = 180) {
    if (strlen($str) > $len)
        return substr($str, 0, $len) . "...";
    return $str;
}

function trim_link($link, $len = 100) {
    //$CI =& get_instance();
    //remove www;
    $link = str_ireplace("http://", "", $link);
    $start = $link;
    $end = "";
    $endlength = config_item("max_link_trim_end_length");
    if (strlen($link) > $len + $endlength) {
        $start = substr($link, 0, $len);
        $end = "..." . substr($link, -$endlength);
    }
    return $start . $end;
}

// attach the param string to the url, taking into account wether URL already contains a ? or not
function attachQuerystring($url, $param) {
    if (!strpos($url, "?")) {
        return $url . "?" . $param;
    } else {
        return $url . "&" . $param;
    }
}

/**
 * This was added to array_map so it can be called on all elements of an array at once.
 * transforms the number of given minutes to seconds
 * @param Float $n - value in minutes
 * @return Float - the value in seconds
 */
function arrayMinutesToSeconds($n) {
    return $n != 'null' ? $n * 60 : $n;
}

?>