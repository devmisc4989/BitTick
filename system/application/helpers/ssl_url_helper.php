<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


/**
 * Base SSL URL
 *
 * Returns the "base_ssl_url" item from your config file
 *
 * @access	public
 * @return	string
 */
if (!function_exists('base_ssl_url')) {

    function base_ssl_url() {
        $CI = & get_instance();
        return $CI->config->slash_item('base_ssl_url');
    }

}

