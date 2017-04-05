<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Enhanced Email Class
 *
 * Checks in config, wether email is supported or not. If not supported, the send() method
 * does nothing instead of send an email.
 * This helps avoiding error messages when testing locally.
 *
 */
class My_Email extends CI_Email {

    function My_Email($config = array()) {
        $CI = & get_instance();

        if ($CI->config->item('MAIL_PROTOCOL') === 'mail') {
            log_message('debug', "MyEmail: sending email via php.ini mail");
            $config['protocol'] = 'mail';
        } elseif ($CI->config->item('MAIL_PROTOCOL') === 'smtp') {
            log_message('debug', "MyEmail: sending email via smtp");
            $config['protocol'] = 'smtp';
            $config['smtp_host'] = $CI->config->item('MAIL_smtp_host');
            $config['smtp_user'] = $CI->config->item('MAIL_smtp_user');
            ;
            $config['smtp_pass'] = $CI->config->item('MAIL_smtp_pass');
            ;
            $config['smtp_port'] = $CI->config->item('MAIL_smtp_port');
            ;
        }
        parent::__construct($config);
        log_message('debug', "Overloaded Email Class Initialized");
    }

    function send() {
        $ret = parent::send();
        log_message('debug', 'sending mail now');
        log_message('debug', parent::print_debugger());
        return $ret;
    }

}