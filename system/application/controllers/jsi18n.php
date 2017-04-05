<?php

/**
 * 
 * Controller to load views that deliver language dependent Javascript
 * @author eschneid
 *
 */
Class jsi18n extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function validatelogin() {
        $this->load->view('public/validatelogin');
    }

    function jqueryValidationEngine() {
        $this->load->view('public/jqueryValidationEngine');
    }

}

?>