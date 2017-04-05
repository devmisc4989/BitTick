<?php

/*
 * 
 *  function for edit profile validation
 */

function lang_redirect($url) {
    $CI = & get_instance();
    redirect($CI->config->item('base_ssl_url') . $CI->config->item('language_abbr') . '/' . $url . '/');
}

?>