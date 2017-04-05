<?php

/*
 * Return level for personalization
 * hidden:access shall not be show in menues, links etc.
 * disabled: access shall be show disabled / greyed in menues, links etc., but not accessible
 * available: access is granted
 */
function getPersoLevel() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
      case 600: $level='disabled';break; // testing
      case 601: $level='disabled';break; // testing + lite upgrade
    	case 610: $level='disabled';break; // onsite light
        case 620: $level='available';break; // onsite
        case 630: $level='available';break; // multichannel
    	case 900: $level='disabled';break; // testing
    	case 901: $level='disabled';break; // testing
       	case 902: $level='disabled';break; // testing
       	case 903: $level='disabled';break; // testing
       	case 904: $level='disabled';break; // testing
       	default: $level='available';break; 
    }
    return $level;
}

/*
 * Return level for smart messaging
 * hidden:access shall not be show in menues, links etc.
 * disabled: access shall be show disabled / greyed in menues, links etc., but not accessible
 * available: access is granted
 */
function getSmSLevel() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
      case 600: $level='disabled';break; // testing
      case 601: $level='available';break; // testing + lite upgrade
    	case 610: $level='available';break; // onsite light
        case 620: $level='available';break; // onsite
        case 630: $level='available';break; // multichannel
    	case 900: $level='disabled';break; // testing
    	case 901: $level='disabled';break; // testing
       	case 902: $level='disabled';break; // testing
       	case 903: $level='disabled';break; // testing
       	case 904: $level='disabled';break; // testing
       	default: $level='hidden';break; 
    }
    return $level;
}

/*
 * Return available triggering rules for smart messaging
 * Return: an array of strings containing one or more out of
 * - exit_intent
 * - always_on
 * - greeter
 * - attn_grabber
 */
function getSmSTrigger() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 600: $triggers = array() ;break; // testing
      case 601: $triggers = array('exit_intent','greeter') ;break; // testing + lite upgrade
      case 610: $triggers = array('exit_intent','greeter') ;break; // onsite light
        case 620: $triggers = array('exit_intent','always_on','greeter','attn_grabber') ;break; // onsite
        case 630: $triggers = array('exit_intent','always_on','greeter','attn_grabber') ;break; // multichannel
    	case 900: $triggers = array() ;break; // testing
    	case 901: $triggers = array() ;break; // testing
       	case 902: $triggers = array() ;break; // testing
       	case 903: $triggers = array() ;break; // testing
       	case 904: $triggers = array() ;break; // testing
       	default: $triggers=array();break; 
    }
    return $triggers;
}

/*
 * Return level for creation of Visual A/B tests and split tests
 * hidden:access shall not be show in menues, links etc.
 * disabled: access shall be show disabled / greyed in menues, links etc., but not accessible
 * available: access is granted
 */
function getTestingLevel() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 610: $level='disabled';break; // onsite light
       	default: $level='available';break; 
    }
    return $level;
}

/* 
 * Return level for Multipage Test
 */
function getMultipageLevel() {
  $CI = & get_instance();
  $plan = $CI->session->userdata('userplan');
  switch($plan) {
    case 140: $level='available';break; // enterprise
    default: $level='disabled';break; 
  }
  return $level;
}

/* 
 * Return level for Teaser Test
 */
function getTeasertestLevel() {
  $CI = & get_instance();
  $plan = $CI->session->userdata('userplan');
  switch($plan) {
    case 140: $level='available';break; // enterprise
    default: $level='disabled';break; 
  }
  return $level;
}

/* 
 * Return level for API access
 */
function getApiLevel() {
  $CI = & get_instance();
  $plan = $CI->session->userdata('userplan');
  switch($plan) {
    case 140: $level='available';break; // enterprise
    default: $level='disabled';break; 
  }
  return $level;
}

/* 
 * Return level for conditional activation
 */
function getConditionalActivationLevel() {
  $CI = & get_instance();
  $plan = $CI->session->userdata('userplan');
  switch($plan) {
    case 140: $level='available';break; // enterprise
    default: $level='disabled';break; 
  }
  return $level;
}

/* 
 * Return level for geo ip targeting
 */
function getGeoIpLevel() {
  $CI = & get_instance();
  $plan = $CI->session->userdata('userplan');
  switch($plan) {
    case 140: $level='available';break; // enterprise
    default: $level='disabled';break; 
  }
  return $level;
}

/* 
 * Return level for sandbox export
 */
function getSandboxExportLevel() {
  $CI = & get_instance();
  $plan = $CI->session->userdata('userplan');
  switch($plan) {
    case 150: $level='available';break; // sandbox
    default: $level='disabled';break; 
  }
  return $level;
}


/*
 * Return wether smart messaging templates shall have a "Powered by etracker" branding
 * true|false
 */
function hasSmSBranding() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
      case 601: $level=TRUE;break; // testing + lite upgrade
      case 610: $level=TRUE;break; // onsite light
        case 620: $level=FALSE;break; // onsite
        case 630: $level=FALSE;break; // multichannel
       	default: $level=FALSE;break; 
    }
    return $level;
}

/*
 * Return wether only limited smart messaging templates shall ba available
 * true|false (true means: limited!)
 */
function hasSmSTemplateLimitation() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
      case 601: $level=TRUE;break; // testing + lite
      case 610: $level=TRUE;break; // onsite light
      case 620: $level=FALSE;break; // onsite
      case 630: $level=FALSE;break; // multichannel
      default: $level=FALSE;break; 
    }
    return $level;
}

/*
 * Return wether start/end-date and time for test/campaigns shall be configurable
 * true|false
 */
function hasCampaignTimer() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 610: $level=FALSE;break; // onsite light
       	default: $level=TRUE;break; 
    }
    return $level;
}

/*
 * Return level for access to "Custom CSS" function in variant tab menu
 * hidden: menu entry shall not be shown.
 * disabled: menu entry shall be disabled / greyed, but not accessible
 * available: access is granted
 */
function getCustomCssLevel() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 610: $level='disabled';break; // onsite light
       	default: $level='available';break; 
    }
    return $level;
}

/*
 * Return level for access to "Custom JS" function in variant tab menu
 * hidden: menu entry shall not be shown.
 * disabled: menu entry shall be disabled / greyed, but not accessible
 * available: access is granted
 */
function getCustomJsLevel() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 610: $level='disabled';break; // onsite light
       	default: $level='available';break; 
    }
    return $level;
}

/*
 * Return number of allowed variants per test
 * if -1: unlimited number available
 */
function getAllowedVariantsAmount() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 610: $level=1;break; // onsite light
       	default: $level=-1;break; 
    }
    return $level;
}

/*
 * Return number of allowed active tests
 * if -1: unlimited number available
 */
function getAllowedActiveTestsAmount() {
    $CI = & get_instance();
	$plan = $CI->session->userdata('userplan');
    switch($plan) {
    	case 610: $level=1;break; // onsite light
       	default: $level=-1;break; 
    }
    return $level;
}

/*
 * store featurematrix/plan-id in session
 */
function setFeatureMatrix($plan) {
    $CI = & get_instance();
	$CI->session->set_userdata('userplan', $plan);
}

/*
 * remove featurematrix from session
 */
function unsetFeatureMatrix() {
    $CI = & get_instance();
	$CI->session->set_userdata('userplan', $plan);
}

?>