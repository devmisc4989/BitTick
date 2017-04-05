<?php

require 'oauth.php';

function signRequestUrl($apikey, $apisecret, $url, $method) {
    $consumer = new OAuthConsumer($apikey, $apisecret);
    $sig_method = new OAuthSignatureMethod_HMAC_SHA1;
    $token = null;  //token is null because we're doing 2-leg
    $req = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url);
    $req->sign_request($sig_method, $consumer, $token);
    $signedUrl = $req->to_url();
    return $signedUrl;
}
