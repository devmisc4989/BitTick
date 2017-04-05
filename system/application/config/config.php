<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  |--------------------------------------------------------------------------
  | Configuration profile ID
  |--------------------------------------------------------------------------
  |
  | ID for set of machine dependent configuration variables. Allows for centralized
  | selection of variables and avoids need to un-comment.
 */

define('configuration_profile', 3);
// loglevel constants
define('LOG_LEVEL_DEBUG', 1);
define('LOG_LEVEL_INFO', 2);
define('LOG_LEVEL_ERROR', 3);
$config['TRACECODE'] = 'NA';

if (configuration_profile == 3) {
    // DEVELOPMENT
    // for eckhard's test system
    $config['base_url'] = "http://blacktri-dev.de/";
    $config['base_ssl_url'] = "https://blacktri-dev.de/";
    $config['image_url'] = "http://blacktri-dev.de/images/";
    $config['image_ssl_url'] = "https://blacktri-dev.de/images/";
    // variable for dynamic script creation
    $config['script_url'] = "blacktri-dev.de/js/";
    $config['website_phase'] = "LAUNCH";
    $config['editor_url'] = "https://opt.blacktri-dev.de/";
    $config['document_domain'] = "blacktri-dev.de";
    // URL of parent page which opens the application in an iFrame		
    $config['trackinglib_host'] = "blacktri-dev.de/js/";
    //$config['tenant'] = 'etracker';
    //$config['tenant']	= 'dvlight';
    $config['tenant'] = 'blacktri';
    $config['apcdisabled'] = true;
    $config['nicEditButtonPath'] = "/js/nicEdit/nicEditorIcons.gif";

    // system wide loglevel to use
    $config['LOGLEVEL'] = LOG_LEVEL_DEBUG;
    $config['ADMIN_EMAIL'] = 'schneider.eckhard@gmail.com';

    $config['editor_visitor_proceed_url'] = $config['base_ssl_url'] . "de/registrieren/";
    $config['editor_visitor_cancel_url'] = $config['base_url'] . "de/";

    // autentication for application (optional)
    //$config['basicauth_user']	= 'blacktri';
    //$config['basicauth_password']	= 'bto2014';
    // logging-path for opt-editor-proxy
    $config['opt_log_base_path']	= '/Users/eschneid/tmp';	
    $config['opt_cookie_base_path']	= '/Users/eschneid/tmp';	
    // list of collection codes for which each request will be attached a random number. this allows to track 
    // each request and conversion in google analytics and compare with results in database
    //$config['COLLECTIONS_WITH_RANDOM_URLS'] = "BT-1ebf13f8934b91b91005356f11fdc,BT-dummy";
    $config['COLLECTIONS_WITH_RANDOM_URLS'] = "BT-dummy";

    // pdf creation with tcpdf
    define('K_PATH_MAIN', 'K:\Documents\03_projekt\blacktri\trunk\htdocs\tcpdf\\');
    define('K_PATH_URL', 'http://blacktri-dev.de/tcpdf/');

    $config['available_goals'] = array('1', '2', '3', '12', '13', '14');

    // GeoNames username
    $config['geonames_username'] = 'blacktri';
    $config['geonames_testing_ip'] = '181.58.138.2'; //CO -> Departamento de Caldas -> Manizales
    //$config['geonames_testing_ip'] = '209.132.232.92'; // US -> California -> Los Angeles
    //$config['geonames_testing_ip'] = '141.53.15.170'; // schwerin, meckpomm
    //$config['geonames_testing_ip'] = '37.5.124.144'; //Hamburg

    //$config['geonames_testing_ip'] = '129.69.8.151'; //stuttgart, BW
    //$config['geonames_testing_ip'] = '188.174.109.118'; // DE -> Bayern -> München
    //$config['geonames_testing_ip'] = '160.45.170.10'; // DE -> berlin
    //$config['geonames_testing_ip'] = '141.89.68.50'; // DE -> potsdam, brandenburg
    //$config['geonames_testing_ip'] = '134.102.50.219'; // DE -> bremen
    //$config['geonames_testing_ip'] = '134.176.3.240'; // DE -> giessen, hessen
    //$config['geonames_testing_ip'] = '134.76.20.193'; // DE -> göttingen, niedersachsen
    //$config['geonames_testing_ip'] = '134.147.64.11'; // DE -> bochum, NRW
    //$config['geonames_testing_ip'] = '134.93.178.2'; // DE -> Mainz, RP
    //$config['geonames_testing_ip'] = '134.96.7.179'; // DE -> Saarbrücken, SL
    //$config['geonames_testing_ip'] = '139.18.1.45'; // DE -> leipzig, sachsen
    //$config['geonames_testing_ip'] = '141.44.1.34'; // DE -> magdeburg, sachsen-anhalt
    //$config['geonames_testing_ip'] = '134.245.12.21'; // DE -> kiel, schleswig-holstein
    //$config['geonames_testing_ip'] = '194.95.117.239'; // DE -> erfurt, thüringen
    //$config['geolite2_citydb'] = 'C:/03_projekt/bto/experimental/GEOLITE/GeoLite2-City.mmdb';
    $config['geolite2_citydb'] = '/Users/eschneid/Documents/03_projekt/bto/experimental/GEOLITE/GeoLite2-City.mmdb';
    
    //$config['bt_apiclientid'] = "1"; // This is the "blacktri" api_clientid (From the API_CLIENT table).
    $config['bt_apiclientid'] = "19794"; // This is the "blacktri" api_clientid (From the API_CLIENT table).
    $config['bt_apikey'] = "eckhard";
    $config['bt_apisecret'] = "eckhard";

    $config['sms_template_path'] = "//blacktri-dev.de/";
    $config['etracker_product_upgrade'] = "https://www.etracker.com/de/produkte/upgrade/targeting-suite.html";
    //$config['experimental_file_path'] = "C:/03_projekt/blacktri-hg/experimental/";
    $config['experimental_file_path'] = "/Users/eschneid/Documents/03_projekt/blacktri-hg/experimental/";    
    $config['unittest_baseurl'] = "http://unittest.blacktri-dev.de/";
    $config['etracker_rta_url'] = "https://ws.etracker.com/api/rest/v2/realtime/user?";

} elseif (configuration_profile == 4) {
    // for MCons live system
    $config['base_url'] = "http://www.blacktri.com/";
    $config['base_ssl_url'] = "https://www.blacktri.com/";
    $config['image_url'] = "http://www.blacktri.com/images/";
    $config['image_ssl_url'] = "https://www.blacktri.com/images/";
    // variable for dynamic script creation
    $config['script_url'] = "opt.blacktri.com/js/";
    $config['website_phase'] = "LAUNCH";
    $config['editor_url'] = "https://opt.blacktri.com/";
    $config['document_domain'] = "blacktri.com";
    $config['trackinglib_host'] = "blacktri-a.akamaihd.net/js/";
    $config['basicauth_user'] = 'blacktri';
    $config['basicauth_password'] = 'bto2014';
    $config['apcdisabled'] = false;
    $config['tenant'] = 'blacktri';
    $config['nicEditButtonPath'] = "/js/nicEdit/nicEditorIcons.gif";

    // system wide loglevel to use
    $config['LOGLEVEL'] = LOG_LEVEL_INFO;
    $config['ADMIN_EMAIL'] = 'schneider.eckhard@gmail.com';

    $config['editor_visitor_proceed_url'] = $config['base_ssl_url'] . "de/registrieren/";
    $config['editor_visitor_cancel_url'] = $config['base_url'] . "de/";

    // logging-path for opt-editor-proxy
    $config['opt_log_base_path']    = '/www/bm/blacktri_50356/live/htdocs/storage';   
    $config['opt_cookie_base_path'] = '/www/bm/blacktri_50356/live/htdocs/storage';

    // list of collection codes for which each request will be attached a random number. this allows to track 
    // each request and conversion in google analytics and compare with results in database
    //$config['COLLECTIONS_WITH_RANDOM_URLS'] = "BT-1ebf13f8934b91b91005356f11fdc,BT-dummy";
    $config['COLLECTIONS_WITH_RANDOM_URLS'] = "BT-653ec3bbdb06702408399f65c67ac,BT-611e6af89b7205193d225d15be160";
    $config['geonames_username'] = 'blacktri';
    $config['geolite2_citydb'] = '/www/bm/blacktri_50356/live/storage/GeoLite2-City.mmdb';

    // goals for this profile
    $config['available_goals'] = array('1', '2', '3', '12', '13', '14');

    $config['geonames_username'] = 'blacktri';
    $config['geolite2_citydb'] = '/www/bm/blacktri_50356/live/storage/GeoLite2-City.mmdb';

    $config['bt_apiclientid'] = "1"; // This is the "blacktri" api_clientid (From the API_CLIENT table).
    $config['bt_apikey'] = "eckhard";
    $config['bt_apisecret'] = "eckhard";
}
if (configuration_profile == 5) {
    // QA
    $config['base_url'] = "http://blacktri-qa.mcon.net/";
    $config['base_ssl_url'] = "https://blacktri-qa.mcon.net/";
    $config['image_url'] = "http://blacktri-qa.mcon.net/images/";
    $config['image_ssl_url'] = "https://blacktri-qa.mcon.net/images/";
    // variable for dynamic script creation
    $config['script_url'] = "blacktri-qa.mcon.net/js/";
    $config['website_phase'] = "LAUNCH";
    $config['editor_url'] = "https://blacktri-opt-qa.mcon.net/";
    $config['document_domain'] = "mcon.net";
    $config['trackinglib_host'] = "blacktri-qa.mcon.net/js/";
    //$config['tenant'] = 'etracker';
    //$config['tenant']	= 'dvlight';
    $config['tenant']	= 'blacktri';
    // autentication for application (optional)
    $config['basicauth_user'] = 'blacktri';
    $config['basicauth_password'] = 'bto2014';
    $config['apcdisabled'] = false;
    $config['nicEditButtonPath'] = "/js/nicEdit/nicEditorIcons.gif";

    // system wide loglevel to use
    $config['LOGLEVEL'] = LOG_LEVEL_DEBUG;
    $config['ADMIN_EMAIL'] = 'schneider.eckhard@gmail.com';

    $config['editor_visitor_proceed_url'] = $config['base_ssl_url'] . "de/registrieren/";
    $config['editor_visitor_cancel_url'] = $config['base_url'] . "de/";

    // logging-path for opt-editor-proxy
    $config['opt_log_base_path']    = '/www/bm/blacktri_50356/qa/htdocs/storage';   
    $config['opt_cookie_base_path'] = '/www/bm/blacktri_50356/qa/htdocs/storage';

    // list of collection codes for which each request will be attached a random number. this allows to track 
    // each request and conversion in google analytics and compare with results in database
    //$config['COLLECTIONS_WITH_RANDOM_URLS'] = "BT-1ebf13f8934b91b91005356f11fdc,BT-dummy";
    $config['COLLECTIONS_WITH_RANDOM_URLS'] = "BT-dummy";

    // pdf creation with tcpdf
    define('K_PATH_MAIN', '/mnt/bm-optimizer-app-01-live-vg01/lv01/www/bm/blacktri_50356/qa/htdocs/tcpdf/');
    define('K_PATH_URL', 'http://blacktri-qa.mcon.net/tcpdf/');
    $storage_path = "/mnt/bm-optimizer-app-01-live-vg01/lv01/www/bm/blacktri_50356/live/storage";
    define('K_PATH_FONTS', $storage_path . '/tcpdf/fonts/');
    define('K_PATH_CACHE', $storage_path . '/tcpdf/cache/');
    define('K_PATH_FILES', $storage_path . '/tcpdf/files/');

    $config['available_goals'] = array('1', '2', '3', '12', '13', '14');

    $config['geonames_username'] = 'blacktri';
    $config['geolite2_citydb'] = '/www/bm/blacktri_50356/live/storage/GeoLite2-City.mmdb';

    $config['bt_apiclientid'] = "1"; // This is the "blacktri" api_clientid (From the API_CLIENT table).
    $config['bt_apikey'] = "eckhard";
    $config['bt_apisecret'] = "eckhard";

}
if (configuration_profile == 6) {
    require_once "/etc/tracking/config_blacktri_base.inc.php";
}

// tracking code snippet
$config['trackingcode'] = "<script type=\"text/javascript\">\n  var _btCc = '%s';\n  document.write('<scr' + 'ipt type=\"text/javascript\" src=\"//%sgs.js\"></scr'+'ipt>');\n</script>";

// flag new features
$config['device_types'] = array(
    'Smartphone' => array(    
            'iphone5' => array(
                'name' => 'Apple Iphone 5',
                'ua' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X; en-us) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53',
                'width' => 320,
                'height' => 568,
                'dp' => 2
            ),
            'iphone6' => array(
                'name' => 'Apple Iphone 6',
                'ua' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4',
                'width' => 375,
                'height' => 667,
                'dp' => 2
            ),
            's3' => array(
                'name' => 'Samsung Galaxy SIII',
                'ua' => 'Mozilla/5.0 (Linux; U; Android 4.0; en-us; GT-I9300 Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
                'width' => 360,
                'height' => 640,
                'dp' => 2
            ),
            'note3' => array(
                'name' => 'Samsung Galaxy Note 3',
                'ua' => 'Mozilla/5.0 (Linux; U; Android 4.3; en-us; SM-N900T Build/JSS15J) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
                'width' => 360,
                'height' => 640,
                'dp' => 3
            )
        ),
            
    'Tablet' => array(
            'ipad' => array(
                'name' => 'Apple Ipad',
                'ua' => 'Mozilla/5.0 (iPad; CPU OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53',
                'width' => 1024,
                'height' => 768,
                'dp' => 2
            ),
            'nexus7' => array(
                'name' => 'Google Nexus 7',
                'ua' => 'Mozilla/5.0 (Linux; Android 4.3; Nexus 7 Build/JSS15Q) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2307.2 Mobile Safari/537.36',
                'width' => 960,
                'height' => 600,
                'dp' => 2
            )
        ),
    
    'Desktop' => array(            
            'desktop' => array(
                'name' => 'Desktop computer',
                'ua' => 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.17 Safari/537.36',
                'width' => 0,
                'height' => 0,
                'dp' => 1
            )
        )
);

//editor url validation
define('editor_url_validation', true);

// client roles
define('CLIENT_ROLE_NORMAL', 0); // normal client
define('CLIENT_ROLE_MASTER', 1); // master account client
define('CLIENT_ROLE_SUB', 2); // sub account client
//fb keys
$config['fb_app_id'] = '209192762481478';
$config['fb_secret'] = '049adea719b28c324f6bc4c55cff72f3';
$config['fb_disabled'] = true;

// WURFL device detection
$config['wurfl_db'] = 'wurfl';

// Allocation values as saved in the DB and displayed to the user
$config['allocations'] = array(
    '1' => '100%',
    '0.5' => '50%',
    '0.2' => '20%',
    '0.1' => '10%',
    '0.05' => '5%',
    '0.01' => '1%',
    '0.005' => '0.5%',
    '0.001' => '0.1%',
);

// API calls to create goals need to have the following goal names
$config['api_goals'] = array(
    '1' => 'ENGAGEMENT',
    '2' => 'AFFILIATE',
    '3' => 'TARGETPAGE',
    '12' => 'LINKURL',
    '13' => 'CUSTOMJS',
    '14' => 'TIMEONPAGE',
    '15' => 'CLICK',
    '16' => 'COMBINED',
    '17' => 'PI_LIFT',
);

// Goal level are returned from the API as strings, we need to convert it to integer
$config['api_goal_level'] = array(
    '0' => 'SECONDARY',
    '1' => 'PRIMARY',
);

// API persomode is defined different: the index is for the visual editor, the value is for the API requests/retrieves
$config['api_persomode'] = array(
    '0' => 'NONE',
    '1' => 'COMPLETE',
    '2' => 'SINGLE',
);

// Client status in the API is handled diferently. This allows a "translation" between visual mode and api data
$config['api_clientstatus'] = array(
    '0' => 'UNSET',
    '1' => 'ACTIVE',
    '2' => 'CANCELLED',
    '3' => 'HIBERNATED',
    '6' => 'FULL'
);

//mail
$config['MAIL_PROTOCOL'] = 'mail';
$config['MAIL_NOREPLY_SENDER'] = "noreply@blacktri.com";
$config['MAIL_NOREPLY_NAME'] = "BlackTri Optimizer";
$config['MAIL_ECKHARD_SENDER'] = "eckhard.schneider@blacktri.com";
$config['MAIL_ECKHARD_NAME'] = "Eckhard Schneider";
$config['MAIL_SUPPORT_SENDER'] = "support@blacktri.com";
$config['MAIL_SUPPORT_NAME'] = "BlackTri Optimizer Support";
$config['MAIL_DIVOLUTION_SENDER'] = "abtester@divolution.com";
$config['MAIL_DIVOLUTION_NAME'] = "DIVOLUTION A/B TESTER";


// campaign ID to store registrations for the teaser phase reminder with
define('TEASER_CAMPAIGN_ID', 100);

// zscore indicating confidence
$config['MAX_ZSCORE'] = 1.65;
$config['MAX_CONFIDENCE'] = 0.95;
$config['MIN_IMPRESSIONS'] = 200;
$config['MIN_CONVERSIONS'] = 7;
$config['MIN_SAMPLE_TIME'] = 604800; // minimum runtime of test in seconds (7 days)
// percentage of impressions that the winner shall have after optimisation
$config['WINNER_SLOT'] = 1;
// minimum uplift in percent a leader variant must produce before the system sends a notification message
$config['MIN_UPLIFT_FOR_NOTIFICATION'] = 5;
// minimum users needed to calculate a pages remaining sample time
$config['min_users_needed_for_sample_calculation'] = 10;
// if a variant does not have a minumum uplift, we declare it a looser
$config['min_uplift_to_proceed'] = 0.2;

// token that indicates a variant contains not a complete URL but a part of the querystring to be attached to the control
$config['VARIANT_CONTROLRELOAD_TOKEN'] = "?";

// script to be removed from google ad factors
$config["google_showad_js"] = "\n<script type=\"text/javascript\" src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\"></script>";

//max display link length
$config["max_link_trim_length"] = 35;
$config["max_link_trim_end_length"] = 15;

// time in seconds to store collection and client data in memory cache
$config['COLLCACHETIME'] = 3600;
// time in seconds to store visitor session data in memory cache
$config['SESSIONCACHETIME'] = 1800;

// order in MVT tests to which combinations are taken into account (higher order effects will be ignored)
$config['mvt_max_order'] = 2;
$config['mvt_max_combinations'] = 100;
$config['mvt_max_display_combinations'] = 20;

$config['gaph_message_en'] = "<span class='BTSkipElement BTGAPh'>Due to technical reasons we only display a plcaholder for the Google Adsense block while editing</span>";
$config['gaph_message_de'] = "<span class='BTSkipElement BTGAPh'>Aus technischen Gründen wird zum Bearbeiten ein Platzhalter für den Google Adsense-Block angezeigt</span>";

//default number of chart lines
$config['chart_visible_lines'] = 5;

// constants for optimisation status values
define('OPT_TRACKING_APPROACH_OCPC', 1); // OCPC (one code per client) test
define('OPT_TRACKING_APPROACH_OCPT', 2); // OCPT (one code per test) test

define('OPT_MODE_CR', 1); // optimisation mode conversion rate
define('OPT_MODE_REV', 2); // optimisation mode revenue
define('OPT_TESTTYPE_SPLIT', 1); // test type split test (A/B test)
define('OPT_TESTTYPE_MVT', 2); // test type multivariate test
define('OPT_TESTTYPE_VISUALAB', 3); // test type multivariate test
define('OPT_TESTTYPE_TEASER', 4); // test type teaser test
define('OPT_TESTTYPE_REMOTE', 5); // test type remote test
define('OPT_TESTTYPE_MULTIPAGE', 6); // test type multipage test

define('OPT_IS_SMS_TEST', 1); // Flag to determine if a test is SMS (field smartmessage in the landingpage_collection)
define('OPT_MAX_TREND_ENTRIES', 50); // Limits the quantity of entries a user can retrieven from the trend resource
define('OPT_DEFAULT_TREND_ENTRIES', 30); // The default number of entries for the trend resource
define('OPT_TREND_DAY', 'DAY');
define('OPT_TREND_HOUR', 'HOUR');
define('OPT_TREND_5MINUTE', '5MINUTE');
define('OPT_TREND_MINUTE', 'MINUTE');

define('OPT_PERSOMODE_NONE', 0); // No personalization
define('OPT_PERSOMODE_COMPLETE', 1); // Personalize complete test
define('OPT_PERSOMODE_SINGLE', 2); // Personalize variants individually

define('OPT_TESTGOAL_SUCCESS_PAGE', 0); // test goal default setting
define('OPT_TESTGOAL_ADSENSE', 1); // test goal for google adsense
define('OPT_EVENT_IMPRESSION', 1); // type of event stored in request_events
define('OPT_EVENT_CONVERSION', 2);
define('OPT_EVENT_DEFERRED_IMPRESSION', 3);
define('OPT_EVENT_TT_VISITOR', 4);

define('OPT_PAGETYPE_CTRL', 1); // page is control page
define('OPT_PAGETYPE_VRNT', 2); // page is variant page
define('OPT_PAGETYPE_SCSS', 3); // page is success page
define('OPT_PAGETYPE_PERSOGOAL_TARGETPAGE', 4); // page is virtual rule condition of type target page
define('OPT_PAGESTATUS_UNVERIFIED', 0); // correct include of tracking code has not yet been verified. Initial status of each page
define('OPT_PAGESTATUS_PAUSED', 1); // page is paused
define('OPT_PAGESTATUS_ACTIVE', 2); // page is active
define('OPT_PROGRESS_UNKNOWN', 0); // progress of optimisation: intermediate state
define('OPT_PROGRESS_NSIG', 1); // progress of optimisation: not significant yet
define('OPT_PROGRESS_NSIG_LEAD', 3); // progress of optimisation: not significant yet, but one variant with better cr than control
define('OPT_PROGRESS_SIG', 2); // progress of optimisation: significant
define('OPT_PERFORMANCE_UNKNOWN', 0); // z-score of page is not high enough
define('OPT_PERFORMANCE_BETTER', 1); // page performs better then control or (if control) all variants
define('OPT_PERFORMANCE_WORSE', 2); // page performs worse then control or (if control) all variants
define('OPT_SLOTS_EQUIDIST', 1); // slots of pages shall be distributed equidistant, all pages have same likeliness
define('OPT_SLOTS_NOSIG', 2); // slots of pages shall be distributed such that pages with statistical significance are not delivered anymore
define('OPT_SLOTS_WINNER', 3); // slots of pages shall be distributed such that the winner is delivered more often than other pages
define('OPT_TRACKINGCODE_INCLUDED', 1); // return value which indicates that a trackingcode is included in the control page
define('OPT_TRACKINGCODE_MISSING', 2); // return value which indicates that a trackingcode is *not* included in the control page

define('OPT_COLLECTION_INVALID', 1);
define('OPT_COLLECTION_VALID', 2);

define('OPT_COLLECTION_EXPIRE_COOKIE', 0.083); //expire days of the cookie that stores collection code
// converison goal types
define('GOAL_TYPE_ENGAGEMENT', 1);
define('GOAL_TYPE_AFFILIATE', 2);
define('GOAL_TYPE_TARGETPAGE', 3);
define('GOAL_TYPE_CUSTOM', 4); // deprecated
define('GOAL_TYPE_ET_ECOMMERCE', 5);  // deprecated
define('GOAL_TYPE_ET_TARGETPAGE', 6); // deperecated
define('GOAL_TYPE_ET_TARGET', 7); // deprecated
define('GOAL_TYPE_ET_VIEWPRODUCT', 8);
define('GOAL_TYPE_ET_INSERTTOBASKET', 9);
define('GOAL_TYPE_ET_ORDER', 10);
define('GOAL_TYPE_SMS_FOLLOW', 11);
define('GOAL_TYPE_TARGETLINK', 12);
define('GOAL_TYPE_CUSTOM_JAVASCRIPT', 13);
define('GOAL_TYPE_TIMEONPAGE', 14);
define('GOAL_TYPE_CLICK', 15);
define('GOAL_TYPE_COMBINED', 16);
define('GOAL_TYPE_PI_LIFT', 17);
$config['SAME_PAGE_GOALS'] = array( // these are goals that only shall be counted if the conversion happens on the same page as the impression
   GOAL_TYPE_ENGAGEMENT,
   GOAL_TYPE_AFFILIATE,
   GOAL_TYPE_CUSTOM,
   GOAL_TYPE_SMS_FOLLOW,
   GOAL_TYPE_TIMEONPAGE,
   GOAL_TYPE_CLICK,
); 
$config['COMBINED_GOAL_COMBINATION_RULE'] = array(
    GOAL_TYPE_CLICK => 0.2, 
    GOAL_TYPE_TIMEONPAGE => 0.4,
    GOAL_TYPE_PI_LIFT => 0.4
);

define('SUBSCRIPTION_INACTIVE', 0);
define('SUBSCRIPTION_ACTIVE', 1);


$config['PLAN'] = array('PLAN_ALPHAVIP' => 100,
    'PLAN_BASIC' => 120,
    'PLAN_PROFESSIONAL' => 130,
    'PLAN_ENTERPRISE' => 140,
    'PLAN_SANDBOX' => 150
);

$config['PLAN_INFO'] = array();
$config['PLAN_INFO']['PLAN_BASIC']['name'] = "Basic";
$config['PLAN_INFO']['PLAN_BASIC']['quota'] = 20000;
$config['PLAN_INFO']['PLAN_PROFESSIONAL']['name'] = "Professional";
$config['PLAN_INFO']['PLAN_PROFESSIONAL']['quota'] = 80000;
$config['PLAN_INFO']['PLAN_ENTERPRISE']['name'] = "Enterprise";
$config['PLAN_INFO']['PLAN_ENTERPRISE']['quota'] = 100000;
$config['PLAN_INFO']['PLAN_SANDBOX']['name'] = "Sandbox";
$config['PLAN_INFO']['PLAN_SANDBOX']['quota'] = 100000;

define('CLIENT_EMAIL_VALIDATED', 1); // clients email is double-opt-in validated
define('CLIENT_EMAIL_NOT_VALIDATED', 0); // clients email is double-opt-in validated

define('CLIENT_STATUS_BETA_UNAPPROVED', 5); // client has registered for beta and needs approval
define('CLIENT_STATUS_ACTIVE', 1); // client is active in 30 day test phase
define('CLIENT_STATUS_CANCELLED', 2); // subscription is cancelled by BT
define('CLIENT_STATUS_HIBERNATED', 3); // subscription is paused (maybe because cliet has not payed feeds)
define('CLIENT_STATUS_PAYED', 6); // payed active subscription

define('LOGIN_STATUS_FULL', 1); // client can access all resources belonging to his plan
define('LOGIN_STATUS_LIMITED', 2); // client can access only parts of his resourcs because he has been hibernated
// codes for autoresponder messages
$config['AUTORESPONDER'] = array(
    'PR_HELLO' => 3,
    'PR_NEED_HELP' => 11,
    'PR_WARN' => 29,
    'PR_END' => 35
);

// constants for logging types 
define('LOG_TYPE_SIGNUP', 100);
define('LOG_TYPE_CCONFIRMEMAIL', 110);
define('LOG_TYPE_LOGIN', 120);
define('LOG_TYPE_LOGOUT', 130);
define('LOG_TYPE_PWDREMAINDER', 140);
define('LOG_TYPE_VALIDATIONMAIL', 150);
define('LOG_TYPE_PROTECTEDBYCLIENT', 160);
define('LOG_TYPE_LPDETAILSPAGE', 170);
define('LOG_TYPE_NEWCOLLECTION', 180);
define('LOG_TYPE_EDITCOLLECTION', 190);
define('LOG_TYPE_DELETECOLLECTION', 200);
define('LOG_TYPE_PAUSECOLLECTION', 210);
define('LOG_TYPE_PLAYCOLLECTION', 220);
define('LOG_TYPE_RESTARTCOLLECTION', 230);
define('LOG_TYPE_PAUSEPAGE', 240);
define('LOG_TYPE_PLAYPAGE', 250);
define('LOG_TYPE_DELETEPAGE', 260);
define('LOG_TYPE_MISC', 270);
define('LOG_TYPE_DELETECOLLECTIONLANDINGPAGE', 280);
define('LOG_TYPE_PAUSECOLLECTIONLANDINGPAGE', 290);
define('LOG_TYPE_PLAYCOLLECTIONLANDINGPAGE', 300);
define('LOG_TYPE_VERIFYTRACKINGCODE', 310);
define('LOG_TYPE_COLLECTION_WORKFLOWCHANGE', 320);
define('LOG_TYPE_AUTORESPONDERMAIL', 330);
define('LOG_TYPE_API', 330);

// confidence value
$config['CONFIDENCEVALUE'] = array('0.0' => 0.50, '0.1' => '0.54', '0.2' => 0.58, '0.3' => 0.62, '0.4' => 0.66, '0.5' => 0.69, '0.6' => 0.73, '0.7' => 0.76,
    '0.8' => 0.79, '0.9' => '0.82', '1' => 0.84, '1.1' => 0.86, '1.2' => 0.88, '1.3' => 0.90, '1.4' => 0.92, '1.5' => 0.93, '1.6' => 0.95, '1.7' => 0.96, '1.8' => 0.96, '1.9' => 0.97);
// chart
if ($config['tenant'] == "etracker")
    $config['COLORS'] = array("#2e3f73", "#eb983b", "#663350", "#806e4e", "#bf3030", "#549f38");
else
    $config['COLORS'] = array("#000000", "#50CC33", "#9933CC", "#80a033", "#E42D1A", "#1BAAEC");

define('SMALLCHART_WIDTH', 900);
define('SMALLCHART_HEIGHT', 250);
define('SMALLCHART_YSTEPS', 8);
define('CHART_TITLE_COLOR', '#6F8F0B');
define('CHART_TITLE_COLOR_DVLIGHT', '#FF7200');
define('CHART_TITLE_COLOR_ETRACKER', '#000000');
define('CHART_AXIS_COLOR', '#cae1eb');
define('CHART_GRID_COLOR', '#e8e9ea');
define('BIGCHART_WIDTH', 900);
;
define('BIGCHART_HEIGHT', 300);
define('BIGCHART_YSTEPS', 10);
define('DETAILSPAGE_NUMDATALINES', 5); // number of displayed pages in chart on landingpage details page
// seo friendly URLs to pages for all available countrieds
$german_pageurls = array(
    'home' => 'de/',
    'tour' => 'de/features/',
    'help' => 'de/hilfe/',
    'plans' => 'de/preise/',
    'blog' => 'blog/',
    'login' => 'de/login/',
    'logout' => 'de/logout/',
    'register' => 'de/registrieren/',
    'confirm' => 'de/bestaetigen/',
    'mytests' => 'de/meinetests/',
    'terms' => 'de/agb/',
    'imprint' => 'de/impressum/',
    'about' => 'de/unternehmen/',
    'cases' => 'de/erfolgreiche-ab-tests/');

$english_pageurls = array(
    'home' => 'en/',
    'tour' => 'en/tour/',
    'help' => 'en/help/',
    'plans' => 'en/plans/',
    'blog' => 'en/blog/',
    'login' => 'en/login/',
    'logout' => 'en/logout/',
    'register' => 'en/register/',
    'confirm' => 'en/confirm/',
    'mytests' => 'en/mytests/',
    'terms' => 'en/terms/',
    'imprint' => 'en/imprint/',
    'about' => 'en/about/',
    'cases' => 'en/successful-ab-tests/');

$config['page_url'] = array('german' => $german_pageurls, 'english' => $english_pageurls);
$config['language_abbrs'] = array("en", "de");
$config['ssl_requiring_pageurls'] = array("login", "register", "registrieren", "gup", 
	"lpc","hilfe", "order", "orderconfirm", "shq", "demo", "democonfirm", "account_setting", "user_mng");
//$config['ssl_requiring_pageurls'] = array();
// map for development phases and connected magic words (for manually selecting the dev phase)
$config['magic_phases'] = array('2398wf34' => 'TEASER', '3450gq90' => 'BETA', '5jfr7464' => 'LAUNCH');

// list of substrings of affiliate URLs for conversion tracking
$config['affiliate_url_pattern'] = array(
    array("/ad\.zanox\.com\/ppc/i", "Zanox"),
    array("/clix\.superclix\.de\/clix\//i", "SuperClix"),
    array("/tradedoubler.com\/click/i", "Tradedoubler"),
    array("/tc\.tradetracker\.net\/\?c/i", "TradeTracker"),
    array("/dpbolvw\.net\/click/i", "Commission Junction"),
    array("/partners\.webmasterplan\.com\/click\.asp/i", "affilinet"),
    array("/track\.webgains\.com\/click\.html/i", "Webgains"),
    array("/belboon\.de\/tracking/i", "Belboon"),
    array("/action\.metaffiliation\.com\/suivi\.php/i", "NetAffiliation"),
    array("/adcell\.de\/click/i", "ADCELL"),
    array("/ds1\.nl\/c\//i", "Daisycon"),
    array("/klick\.affiliwelt\.net\/klick\.php/i", "affiliwelt.net"),
    array("/rcm-\w+\.amazon\.\w+\/e\/cm\?/i", "Amazon Einzeltitel"),
    array("/www\.amazon\.\w+\/gp\/product/", "Amazon Textlink"),
    array("/googleads\.g\.doubleclick\.net\/pagead/i", "GoogleAdsense"),
    array("/hop\.clickbank\.net/i", "Clickbank")
);

// client specific databases
define('CLIENT_DB_PREFIX', "btodb_"); // DBs have names like btodb_2

/*
  |--------------------------------------------------------------------------
  | Index File
  |--------------------------------------------------------------------------
  |
  | Typically this will be your index.php file, unless you've renamed it to
  | something else. If you are using mod_rewrite to remove the page set this
  | variable so that it is blank.
  |
 */

$config['index_page'] = "";

/* setting default date format  */

$config['date_format'] = "Y-m-d";
/*  encryption key */
$config['encryption_key'] = "as890p2q3oijg034p984oijsfjasdf9q243";
/*
  |--------------------------------------------------------------------------
  | URI PROTOCOL
  |--------------------------------------------------------------------------
  |
  | This item determines which server global should be used to retrieve the
  | URI string.  The default setting of "AUTO" works for most servers.
  | If your links do not seem to work, try one of the other delicious flavors:
  |
  | 'AUTO'			Default - auto detects
  | 'PATH_INFO'		Uses the PATH_INFO
  | 'QUERY_STRING'	Uses the QUERY_STRING
  | 'REQUEST_URI'		Uses the REQUEST_URI
  | 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
  |
 */
//$config['uri_protocol']	= "AUTO";
$config['uri_protocol'] = "PATH_INFO";

/*
  |--------------------------------------------------------------------------
  | URL suffix
  |--------------------------------------------------------------------------
  |
  | This option allows you to add a suffix to all URLs generated by CodeIgniter.
  | For more information please see the user guide:
  |
  | http://codeigniter.com/user_guide/general/urls.html
 */

$config['url_suffix'] = "";

/*
  |--------------------------------------------------------------------------
  | Default Language
  |--------------------------------------------------------------------------
  |
  | This determines which set of language files should be used. Make sure
  | there is an available translation if you intend to use something other
  | than english.
  |
 */
//$config['language']	= "english";
$config['language'] = "german";

//default language abbreviation
//$config['language_abbr'] = "en";
$config['language_abbr'] = "de";

//set available language abbreviations
//$config['lang_uri_abbr'] = array("de" => "german");
$config['lang_uri_abbr'] = array("de" => "german", "en" => "english");
//ignore this language abbreviation
$config['lang_ignore'] = "xx";
/*
  |--------------------------------------------------------------------------
  | Default Character Set
  |--------------------------------------------------------------------------
  |
  | This determines which character set is used by default in various methods
  | that require a character set to be provided.
  |
 */
$config['charset'] = "UTF-8";
//$config['charset'] = "iso-8859-1";

/*
  |--------------------------------------------------------------------------
  | Enable/Disable System Hooks
  |--------------------------------------------------------------------------
  |
  | If you would like to use the "hooks" feature you must enable it by
  | setting this variable to TRUE (boolean).  See the user guide for details.
  |
 */
$config['enable_hooks'] = TRUE;

/*
 * 
 *  confidence value
 */
$config['CONFIDENCEVALUE'] = array('0.0' => 0.50, '0.1' => '0.54', '0.2' => 0.58, '0.3' => 0.62, '0.4' => 0.66, '0.5' => 0.69, '0.6' => 0.73, '0.7' => 0.76,
    '0.8' => 0.79, '0.9' => '0.82', '1' => 0.84, '1.1' => 0.86, '1.2' => 0.88, '1.3' => 0.90, '1.4' => 0.92, '1.5' => 0.93, '1.6' => 0.95, '1.7' => 0.96, '1.8' => 0.96, '1.9' => 0.97);

/*
  |--------------------------------------------------------------------------
  | Class Extension Prefix
  |--------------------------------------------------------------------------
  |
  | This item allows you to set the filename/classname prefix when extending
  | native libraries.  For more information please see the user guide:
  |
  | http://codeigniter.com/user_guide/general/core_classes.html
  | http://codeigniter.com/user_guide/general/creating_libraries.html
  |
 */
$config['subclass_prefix'] = 'MY_';


/*
  |--------------------------------------------------------------------------
  | Allowed URL Characters
  |--------------------------------------------------------------------------
  |
  | This lets you specify with a regular expression which characters are permitted
  | within your URLs.  When someone tries to submit a URL with disallowed
  | characters they will get a warning message.
  |
  | As a security measure you are STRONGLY encouraged to restrict URLs to
  | as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
  |
  | Leave blank to allow all characters -- but only if you are insane.
  |
  | DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
  |
 */
$config['permitted_uri_chars'] = '';


/*
  |--------------------------------------------------------------------------
  | Enable Query Strings
  |--------------------------------------------------------------------------
  |
  | By default CodeIgniter uses search-engine friendly segment based URLs:
  | example.com/who/what/where/
  |
  | You can optionally enable standard query string based URLs:
  | example.com?who=me&what=something&where=here
  |
  | Options are: TRUE or FALSE (boolean)
  |
  | The other items let you set the query string "words" that will
  | invoke your controllers and its functions:
  | example.com/index.php?c=controller&m=function
  |
  | Please note that some of the helpers won't work as expected when
  | this feature is enabled, since CodeIgniter is designed primarily to
  | use segment based URLs.
  |
 */
$config['allow_get_array'] = TRUE;
$config['enable_query_strings'] = TRUE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd'; // experimental not currently in use

/*
  |--------------------------------------------------------------------------
  | Error Logging Threshold
  |--------------------------------------------------------------------------
  |
  | If you have enabled error logging, you can set an error threshold to
  | determine what gets logged. Threshold options are:
  | You can enable error logging by setting a threshold over zero. The
  | threshold determines what gets logged. Threshold options are:
  |
  |	0 = Disables logging, Error logging TURNED OFF
  |	1 = Error Messages (including PHP errors)
  |	2 = Debug Messages
  |	3 = Informational Messages
  |	4 = All Messages
  |
  | For a live site you'll usually only enable Errors (1) to be logged otherwise
  | your log files will fill up very fast.
  |
 */
$config['log_threshold'] = 1;

/*
  |--------------------------------------------------------------------------
  | Error Logging Directory Path
  |--------------------------------------------------------------------------
  |
  | Leave this BLANK unless you would like to set something other than the default
  | system/logs/ folder.  Use a full server path with trailing slash.
  |
 */
$config['log_path'] = '';

/*
  |--------------------------------------------------------------------------
  | Date Format for Logs
  |--------------------------------------------------------------------------
  |
  | Each item that is logged has an associated date. You can use PHP date
  | codes to set your own date formatting
  |
 */
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
  |--------------------------------------------------------------------------
  | Cache Directory Path
  |--------------------------------------------------------------------------
  |
  | Leave this BLANK unless you would like to set something other than the default
  | system/cache/ folder.  Use a full server path with trailing slash.
  |
 */
$config['cache_path'] = '';

/*
  |--------------------------------------------------------------------------
  | Encryption Key
  |--------------------------------------------------------------------------
  |
  | If you use the Encryption class or the Sessions class with encryption
  | enabled you MUST set an encryption key.  See the user guide for info.
  |
 */
$config['encryption_key'] = "1";

/*
  |--------------------------------------------------------------------------
  | Session Variables
  |--------------------------------------------------------------------------
  |
  | 'session_cookie_name' = the name you want for the cookie
  | 'encrypt_sess_cookie' = TRUE/FALSE (boolean).  Whether to encrypt the cookie
  | 'session_expiration'  = the number of SECONDS you want the session to last.
  |  by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.
  | 'time_to_update'		= how many seconds between CI refreshing Session Information
  |
 */
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;
$config['sess_encrypt_cookie'] = FALSE;
$config['sess_use_database'] = FALSE;
$config['sess_table_name'] = 'ci_sessions';
$config['sess_match_ip'] = FALSE;
$config['sess_match_useragent'] = TRUE;
$config['sess_time_to_update'] = 300;

/*
  |--------------------------------------------------------------------------
  | Cookie Related Variables
  |--------------------------------------------------------------------------
  |
  | 'cookie_prefix' = Set a prefix if you need to avoid collisions
  | 'cookie_domain' = Set to .your-domain.com for site-wide cookies
  | 'cookie_path'   =  Typically will be a forward slash
  |
 */
$config['cookie_prefix'] = "";
$config['cookie_domain'] = "";
$config['cookie_path'] = "/";

/*
  |--------------------------------------------------------------------------
  | Global XSS Filtering
  |--------------------------------------------------------------------------
  |
  | Determines whether the XSS filter is always active when GET, POST or
  | COOKIE data is encountered
  |
 */
$config['global_xss_filtering'] = FALSE;

/*
  |--------------------------------------------------------------------------
  | Output Compression
  |--------------------------------------------------------------------------
  |
  | Enables Gzip output compression for faster page loads.  When enabled,
  | the output class will test whether your server supports Gzip.
  | Even if it does, however, not all browsers support compression
  | so enable only if you are reasonably sure your visitors can handle it.
  |
  | VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
  | means you are prematurely outputting something to your browser. It could
  | even be a line of whitespace at the end of one of your scripts.  For
  | compression to work, nothing can be sent before the output buffer is called
  | by the output class.  Do not "echo" any values with compression enabled.
  |
 */
$config['compress_output'] = FALSE;

/*
  |--------------------------------------------------------------------------
  | Master Time Reference
  |--------------------------------------------------------------------------
  |
  | Options are "local" or "gmt".  This pref tells the system whether to use
  | your server's local time as the master "now" reference, or convert it to
  | GMT.  See the "date helper" page of the user guide for information
  | regarding date handling.
  |
 */
$config['time_reference'] = 'local';


/*
  |--------------------------------------------------------------------------
  | Rewrite PHP Short Tags
  |--------------------------------------------------------------------------
  |
  | If your PHP installation does not have short tag support enabled CI
  | can rewrite the tags on-the-fly, enabling you to utilize that syntax
  | in your view files.  Options are TRUE or FALSE (boolean)
  |
 */
$config['rewrite_short_tags'] = FALSE;


/*
  |--------------------------------------------------------------------------
  | Reverse Proxy IPs
  |--------------------------------------------------------------------------
  |
  | If your server is behind a reverse proxy, you must whitelist the proxy IP
  | addresses from which CodeIgniter should trust the HTTP_X_FORWARDED_FOR
  | header in order to properly identify the visitor's IP address.
  | Comma-delimited, e.g. '10.0.1.200,10.0.1.201'
  |
 */
$config['proxy_ips'] = '';

/* End of file config.php */
/* Location: ./system/application/config/config.php */
// add a few more config entries
