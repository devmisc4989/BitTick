<?php

/*
 * This helper will validate input values for the given parameters in the apiv1 models
 * functions are self-explanatory, all of them returns TRUE or FALSE.
 * 
 * The Conditions class will only contain the type/arg array for rule conditions validation
 */

class Conditions {

    // This is to be used for rule/condition validation
    public static $rules = array(
        'REFERRER_CONTAINS' => 'is_alphanum_symbol1',
        'URL_CONTAINS' => 'is_alphanum_symbol1',
        'SEARCH_IS' => 'is_alphanum_blanks',
        'TARGETPAGE_OPENED' => 'is_alphanum_symbol2',
        'IS_RETURNING' => 'bool',
        'SOURCE_IS' => array(
            'TYPE_IN',
            'SOCIAL',
            'ORGANIC_SEARCH',
            'PAID_SEARCH',
        ),
        'DEVICE_IS' => array(
            'MOBILE',
            'TABLET',
            'DESKTOP',
        ),
    );

}

function is_valid_date($val) {
    $pattern = '/^(\d{4})-(\d{2})-(\d{2})\s{1}(\d{2}):(\d{2}):(\d{2})$/';
    return preg_match($pattern, $val);
}

function is_valid_boolean($val) {
    return is_bool($val) || strtoupper($val) == 'TRUE' || strtoupper($val) == 'FALSE';
}

function is_alphanum_symbol1($val) {
    $pattern = '/^[A-Za-z0-9_\&\-]+$/i';
    return preg_match($pattern, $val);
}

function is_alphanum_symbol2($val) {
    $pattern = '/^[A-Za-z0-9._\*\&\-]+$/i';
    return preg_match($pattern, $val);
}

function is_alphanum_blanks($val) {
    $pattern = '/^[A-Za-z0-9\s]+$/i';
    return preg_match($pattern, $val);
}

function project_valid_type($val) {
    $typearray = array('VISUAL', 'SPLIT', 'REMOTE', 'SMARTMESSAGE', 'TEASERTEST','MULTIPAGE');
    return in_array($val, $typearray);
}

function project_valid_mainurl($val) {
    return strpos($val, 'http://', 0) === 0 || strpos($val, 'https://', 0) === 0;
}

function project_valid_startdate($val) {
    return is_valid_date($val);
}

function project_valid_enddate($val) {
    return is_valid_date($val);
}

function project_valid_allocation($val) {
    return $val > 0 && $val <= 100;
}

function project_valid_personalizationmode($val) {
    $persoarray = array('NONE', 'COMPLETE', 'SINGLE');
    return in_array($val, $persoarray);
}

function project_valid_ipblacklisting($val) {
    return is_valid_boolean($val);
}

function project_valid_ruleid($val) {
    return is_numeric($val) && $val >= 0;
}

function account_valid_email($val) {
    return trim($val) == '' || filter_var($val, FILTER_VALIDATE_EMAIL);
}

function account_valid_emailvalidated($val) {
    return is_valid_boolean($val);
}

function account_valid_status($val) {
    $statusarray = array('EVALUATION', 'FULL', 'HIBERNATED', 'CANCELLED');
    return in_array($val, $statusarray);
}

function account_valid_quota($val) {
    return is_numeric($val) && is_int($val * 1);
}

function account_valid_freequota($val) {
    return is_numeric($val) && is_int($val * 1);
}

function rule_valid_name($val) {
    return strlen(trim($val)) < 1 ? FALSE : TRUE;
}

function rule_valid_operation($val) {
    return $val == 'AND' || $val == 'OR';
}

function rule_valid_negation($val) {
    return is_valid_boolean($val);
}

function rule_valid_type($val) {
    return array_key_exists($val, Conditions::$rules);
}

/**
 * It is necessary to validate first the type and then the arg so we can get the valid options from the
 * Conditions::$rules array (if any). 
 * @param String $type
 * @param String $arg
 * @return boolean
 */
function rule_valid_arg($type, $arg) {
    $ruletype = Conditions::$rules[$type];

    if (is_array($ruletype)) {
        return in_array($arg, $ruletype);
    } else if ($ruletype == 'bool') {
        return is_valid_boolean($arg);
    } else if (function_exists($ruletype)) {
        return $ruletype($arg);
    }

    return TRUE;
}

/**
 * If the user has defined multiple runpatterns with the respective rule (include by default)
 * we set the JSON string to be sent via the API to be saved as an array into the DB
 * @param Array $control_pattern - the set of control patterns (*, *lpc.html)
 * @param Array $pattern_include - the set of rules (include / exclude)
 */
function setRunPatternArray($control_pattern, $pattern_include) {
    if (gettype($control_pattern) == 'array') {
        $new_pattern = array();
        foreach ($control_pattern as $index => $pattern) {
            $new_pattern[] = array(
                'mode' => isset($pattern_include[$index]) ? $pattern_include[$index] : 'include',
                'url' => $pattern,
            );
        }
        return json_encode($new_pattern);
    } else {
        return $control_pattern;
    }
}
