<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * 	Act as a frontcontroller for several kinds of business logic
 * 	1. Identify language from URI and session-cookie and set the language before language files are loaded
 * 	2. Decide wether SSL should be used for the requested URI. This can not be decided from the request, since the hoster
 * 	(MCon) uses a "SSL Offloader", thus Apache always gets non-SSL-requests
 * 	3. Dispatch the development phase. A magic code in the URL can enable certian functionality not available yet to 
 * 	the public.
 */
class blacktri_filters {

    function blacktri_filters() {
        global $RTR;

        $tenant = $RTR->config->item('tenant');
        $basicauth_user = $RTR->config->item('basicauth_user');
        $basicauth_password = $RTR->config->item('basicauth_password');

        ////////////////// authentication for QA /////////////
/*
        if ($basicauth_user != '') {
            if (!($_SERVER['PHP_AUTH_USER'] == $basicauth_user && $_SERVER['PHP_AUTH_PW'] == $basicauth_password)) {
                header('WWW-Authenticate: Basic realm="BlackTri Optimizer"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Authentication error';
                exit;
            }
        }
*/
        ////////////////// no further filters if this is the optimisation webservice /////////////
        $controllername = $RTR->uri->segment(1);
        if ($controllername == 'bto')
            return;

        ////////////////// manage language /////////////
        // retrieve a valid locale $lang_abbr
        $lang_uri_abbr = $RTR->config->item('lang_uri_abbr');
        /* get the lang_abbr from uri segments */
        $lang_abbr = current($RTR->uri->segments);
        //print_r($RTR->uri->segments);die();
        /* check for invalid abbreviation */
        if (!isset($lang_uri_abbr[$lang_abbr])) {
            // check wether there is a language parameter added to the querystring
            $lang_abb_qrs = isset($_GET["lg"]) ? $_GET["lg"] : "NA";
            //echo "lang_abb_qrs $lang_abb_qrs";die();
            if (!isset($lang_uri_abbr[$lang_abb_qrs])) { // there is no language parameter in the querystring		
                // check wether language has been set in a session cookie
                // we use cookies instead of the session because the session is not yet loaded
                // at this time (before languages are loaded)
                $userlanguage_abbr = $this->getFilterCookie('BT_lg');
                if (!$userlanguage_abbr) {
                    // if no session value exists, use default language
                    $lang_abbr = $RTR->config->item('language_abbr');
                    $this->setFilterCookie('BT_lg', $lang_abbr);
                } else {
                    $lang_abbr = $userlanguage_abbr;
                }
            } else {
                // there is a language parameter in the querystring
                $lang_abbr = $lang_abb_qrs;
            }
        }

        $user_lang = $lang_uri_abbr[$lang_abbr];
        //echo "user_lang $user_lang";die();
        $this->setFilterCookie('BT_lg', $lang_abbr);

        /* reset config language to match the user language */
        $RTR->config->set_item('language', $user_lang);
        $RTR->config->set_item('language_abbr', $lang_abbr);

        ////////////////// manage tenant /////////////

        $mytenant = isset($_GET["tenant"]) ? $_GET["tenant"] : "NA";
        if ($mytenant == 'NA') {
            $mytenant = $this->getFilterCookie('BT_tenant');
            if ($mytenant != false) {
                $RTR->config->set_item('tenant', $mytenant);
            }
        } else {
            $RTR->config->set_item('tenant', $mytenant);
            $this->setFilterCookie('BT_tenant', $mytenant);
        }


        ////////////////// manage SSL /////////////
        // beginning from the first SSL request, the complete session shall be ssl encrypted.
        // check session cookie for ssl
        // if login form or registration form is requested, use ssl for all following requests
        $pathname = $RTR->uri->segment(2);
        /*
         * commented out because editor has a problem qwhen running in ssl mode to load pages not in ssl
          if(in_array($pathname,$RTR->config->item('ssl_requiring_pageurls'))) {
          // force ssl from now on in this session
          $this->setFilterCookie('BT_ssl',"y");
          $RTR->config->set_item('base_url', $RTR->config->item('base_ssl_url'));
          $RTR->config->set_item('image_url', $RTR->config->item('image_ssl_url'));
          }
          else {
          $sslcheck = $this->getFilterCookie('BT_ssl');
          if($sslcheck == "y") {
          // if value exists, use it
          $RTR->config->set_item('base_url', $RTR->config->item('base_ssl_url'));
          $RTR->config->set_item('image_url', $RTR->config->item('image_ssl_url'));
          }
          }
         */
        ////////////////// manage development phase /////////////
        // check wether phase has been set in a session cookie
        $magic_phase = $this->getFilterCookie('BT_mph');
        if (!empty($magic_phase)) {
            // if session value exists, override default value of website phase
            $magic_phases = $RTR->config->item('magic_phases');
            $website_phase = $magic_phases[$magic_phase];
            $RTR->config->set_item('website_phase', $website_phase);
        }
    }

    function setFilterCookie($name, $value) {
        setcookie($name, $value, 0, '/');
    }

    function getFilterCookie($name) {
        global $RTR;
        $ret = false;

        if ($name == 'BT_lg') {
            $langs = $RTR->config->item('language_abbrs');
            if (isset($_COOKIE['BT_lg'])) {
                $lg = $_COOKIE['BT_lg'];
                if (in_array($lg, $langs)) {
                    $ret = $lg;
                }
            }
        }

        if ($name == 'BT_tenant') {
            if (isset($_COOKIE['BT_tenant'])) {
                $tenant = $_COOKIE['BT_tenant'];
                if (in_array($tenant, array('blacktri', 'etracker'))) {
                    $ret = $tenant;
                }
            }
        }

        if ($name == 'BT_ssl') {
            $values = array("y", "n");
            if (isset($_COOKIE['BT_ssl'])) {
                $value = $_COOKIE['BT_ssl'];
                if (in_array($value, $values)) {
                    $ret = $value;
                }
            }
        }

        if ($name == 'BT_mph') {
            if (isset($_COOKIE['BT_mph'])) {
                $mph = $_COOKIE['BT_mph'];
                $phases = $RTR->config->item('magic_phases');
                if (isset($phases[$mph])) {
                    $ret = $mph;
                }
            }
        }

        if ($name == 'BT_tenant') {
            $values = array("blacktri", "etracker");
            if (isset($_COOKIE['BT_tenant'])) {
                $value = $_COOKIE['BT_tenant'];
                if (in_array($value, $values)) {
                    $ret = $value;
                }
            }
        }

        return $ret;
    }

}