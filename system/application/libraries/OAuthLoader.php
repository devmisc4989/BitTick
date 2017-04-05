<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OAuthLoader
{
    public function __construct()
    {
        require_once APPPATH.'helpers/oauth.php';
    }
}