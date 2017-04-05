<?php

require 'oauth.php';

/**
 * Authenticate request with 2-legged OAuth 1.0
 * If authenticastion is unseccessful, request is died with error message
 * If authentication is successful, function returns array with data of apiuser which is authenticating
 */
function authenticate() {

    //$debug = false; // set debug to true to switch off authentication
    $debug = false; // set debug to true to switch off authentication
    $authorized = false;

    $CI = & get_instance();
    $CI->load->model('shared');

    $apikey = $CI->input->get('oauth_consumer_key');
    // retrieve secret for key via a db-lookup. As not yet implemented, we use hard coded values
    $secret = $CI->shared->getAPISecret($apikey);
    if ($secret) {
        $apiuser = array('key' => $apikey, 'secret' => $secret);
        $authorized = true;
    }
    if ($debug && $authorized) {
        return($apiuser);
    } else {
        $consumer = new OAuthConsumer($apikey, $secret);
        $sig_method = new OAuthSignatureMethod_HMAC_SHA1;
        $method = $CI->input->server('REQUEST_METHOD');
        $protocol = 'http://';//($CI->input->server('HTTPS') != '' || $CI->input->server('HTTP_REQUEST_TYPE') == 'SSL') ? 'https://' : 'http://';
        $host = $CI->input->server('HTTP_X_FORWARDED_HOST') ? $CI->input->server('HTTP_X_FORWARDED_HOST') : $CI->input->server('SERVER_NAME');
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $host = $_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW'] . "@" . $host;
        }
        $url = $protocol . $host . $CI->input->server('REQUEST_URI');
        $sig = $CI->input->get('oauth_signature');
        $req = OAuthRequest::from_request($method, $url);
        //token is null because we're doing 2-leg
        $valid = $sig_method->check_signature($req, $consumer, null, $sig);
        // check timestamp
        $timediff = time() - $CI->input->get('oauth_timestamp');
        if ($timediff > 60)
            $valid = false;
        if ($valid)
            return($apiuser);
        else {
            header('unauthorized request', true, 401);
            die('unauthorized request');
        }
    }
}
