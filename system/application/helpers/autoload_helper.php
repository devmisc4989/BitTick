<?php

function doAutoload() {
    $CI = & get_instance();

    $CI->load->library('session');
    $CI->load->helper(array('form', 'url', 'date', 'support', 'redirect', 'ssl_url', 'oauth'));
    $CI->load->language(array('link', 'table', 'button', 'title', 'logmessage', 'error', 'emails', 'support'));

}

?>