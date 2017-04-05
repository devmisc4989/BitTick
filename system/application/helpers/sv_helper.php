<?php

/*
 * 
 *  function for edit profile validation
 */

function profilevalidation($firstname, $lastname, $email, $password, $confirmpassword) {
    $CI = & get_instance();

    $errmsg = '';
    if (trim($firstname) == '') {
        $errmsg = $CI->lang->line('error_firstname');
    } else if (trim($lastname) == '') {
        $errmsg = $CI->lang->line('error_lastname');
    } else if (trim($email) == '') {
        $errmsg = $CI->lang->line('error_email');
    } else if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,3})$/", $email)) {
        $errmsg = $CI->lang->line('error_invalidemail');
    } else if ($password != $confirmpassword) {
        $errmsg = $CI->lang->line('error_passwordmacthes');
    }
    return $errmsg;
}

/*
 * 
 *  function for edit invoice validation
 */

function invoicevalidation($firstname, $lastname, $company, $address, $zipcode, $city, $vatno, $accountno, $bankcode, $bank, $accountholder) {
    $CI = & get_instance();

    $errmsg = '';
    if (trim($firstname) == '') {
        $errmsg = $CI->lang->line('error_firstname');
    } else if (trim($lastname) == '') {
        $errmsg = $CI->lang->line('error_lastname');
    } else if (trim($company) == '') {
        $errmsg = $CI->lang->line('error_company');
    } else if (trim($address) == '') {
        $errmsg = $CI->lang->line('error_companyaddress');
    } else if (trim($zipcode) == '') {
        $errmsg = $CI->lang->line('error_zipcode');
    } else if (!is_numeric($zipcode)) {
        $errmsg = $CI->lang->line('error_zipvalidation');
    } else if (trim($city) == '') {
        $errmsg = $CI->lang->line('error_city');
    } else if (trim($vatno) == '') {
        $errmsg = $CI->lang->line('error_vatno');
    } else if (!is_numeric($vatno)) {
        $errmsg = $CI->lang->line('error_vatnovalidation');
    } else if (trim($accountno) == '') {
        $errmsg = $CI->lang->line('error_accountno');
    } else if (trim($bankcode) == '') {
        $errmsg = $CI->lang->line('error_bankcode');
    } else if (trim($bank) == '') {
        $errmsg = $CI->lang->line('error_bankname');
    } else if (trim($accountholder) == '') {
        $errmsg = $CI->lang->line('error_accholder');
    }
    return $errmsg;
}

/*
 * 
 *  function for signup
 */

function signupvalidation($firstname, $lastname, $email, $password, $cpassword) {
    $CI = & get_instance();

    $errmsg = '';
    if (trim($lastname) == '') {
        $errmsg = $CI->lang->line('error_lastname');
    } else if (trim($firstname) == '') {
        $errmsg = $CI->lang->line('error_firstname');
    } else if (trim($email) == '') {
        $errmsg = $CI->lang->line('error_email');
    } else if (!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,3})$/", $email)) {
        //echo $email;
        //die("ungültig");
        $errmsg = $CI->lang->line('error_invalidemail');
    } else if ($password != $cpassword) {
        $errmsg = $CI->lang->line('error_passwordmacthes');
    } else if (!preg_match('/^(?=^.{6,}$)^.*$/', $password)) {
        $errmsg = $CI->lang->line('error_pwdmin');
    }
    return $errmsg;
}

?>