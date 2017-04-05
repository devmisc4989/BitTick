<?php

/*
 * if database library is not loaded yet, do so now
 * this helps to avoid loading the library in places where not necessary
 */

function lazyLoadDB() {
    $CI = & get_instance();
    if (!isset($CI->db)) {
        $CI->load->database();
    }
}

/*
 * Custom logging function to log into database
 */

function dblog_message($loglevel, $type, $message, $clientid = -1) {
    lazyLoadDB();
    $CI = & get_instance();
    $global_level = $CI->config->item('LOGLEVEL');
    // if a tracecode is set, set level to debug
    $tracecode = $CI->config->item('TRACECODE');
    if ($tracecode != 'NA') {
        $global_level = LOG_LEVEL_DEBUG;
    }
    if ($loglevel >= $global_level) {
        $data = array(
            'timestamp' => date('Y-m-d H:i:s'),
            'loglevel' => $loglevel,
            'type' => $type,
            'message' => $message,
            'tracecode' => $tracecode,
            'clientid' => $clientid
        );
        $CI->db->insert('logging', $data);
    }
}

function dblog_debug($message) {
    dblog_message(LOG_LEVEL_DEBUG, LOG_TYPE_MISC, $message, -1);
}

function dblog_info($message) {
    dblog_message(LOG_LEVEL_INFO, LOG_TYPE_MISC, $message, -1);
}

/*
 * handle a mysql error and log it
 */

function trackMysqlError($fname) {
    if (mysql_errno() != 0) {
        log_message('error', 'ERROR ' . mysql_errno() . ' in ' . $fname . ': ' . mysql_error());
    }
}

/*
 * Function to create a canonical URL from a given URL
 */

function canonicalUrl($url) {
    $parsedurl = parse_url(trim($url));
    $canonicalurl = "";
    if (!$parsedurl) { // if the URL is invalid, use it as it is for canonical URL
        $canonicalurl = $url;
    } else {
        if (isset($parsedurl["scheme"])) {
            $canonicalurl = $parsedurl["scheme"] . "://";
        }
        if (isset($parsedurl["host"])) {
            $canonicalurl = $canonicalurl . $parsedurl["host"];
        }
        if (isset($parsedurl["path"])) {
            $canonicalurl = $canonicalurl . $parsedurl["path"];
        }
        if (isset($parsedurl["query"])) {
            $canonicalurl = $canonicalurl . '?' . $parsedurl["query"];
        }
    }
    //removed
    //$canonicalurl = trim($canonicalurl,' /');
    return $canonicalurl;
}

/*
 * Function to calculate the current billing period of a client based on her create date
 * $createmysqldate is a datetime format from database (Y-m-d H:i:s)
 */

function computeBillingPeriod($createmysqldate) {
    $da = date_parse($createmysqldate);
    $create_day = $da['day'];
    // if create_day > 28, reduce it to avoid hassle with 30-day periods in february...
    if ($create_day > 28)
        $create_day = 28;
    $today_da = date_parse(date('Y-m-d H:i:s'));
    $today_day = $today_da['day'];
    $today_month = $today_da['month'];
    $today_year = $today_da['year'];
    // now depending on wether current day in month is smaller or bigger than day of creation,
    // compute first and last day of period
    if ($create_day < $today_day) {
        $start_month = $today_month;
        $start_year = $today_year;
        $end_month = $start_month + 1;
        $end_year = $start_year;
        if ($end_month == 13) {
            $end_month = 1;
            $end_year++;
        }
    } else {
        $start_month = $today_month - 1;
        $start_year = $today_year;
        $end_month = $today_month;
        $end_year = $start_year;
        if ($start_month == 0) {
            $start_month = 12;
            $end_year--;
        }
    }
    $start_date = new DateTime("$start_year-$start_month-$create_day");
    $mysql_start_date = $start_date->format('Y-m-d H:i:s');
    $end_date = new DateTime("$end_year-$end_month-$create_day 23:59:59");
    $end_date->sub(new DateInterval('P1D'));
    $mysql_end_date = $end_date->format('Y-m-d H:i:s');

    $ret = array();
    $ret['startdate'] = $mysql_start_date;
    $ret['startdate_day'] = $create_day;
    $ret['startdate_month'] = $start_month;
    $ret['startdate_year'] = $start_year;

    $ret['enddate'] = $mysql_end_date;
    $end_sa = date_parse($mysql_end_date);
    $ret['enddate_day'] = $end_sa['day'];
    $ret['enddate_month'] = $end_sa['month'];
    $ret['enddate_year'] = $end_sa['year'];

    return $ret;
}

/*
 * helper function: get the start and end date for a given number of days
 */

function computeDateFromDays($days) {
    $today_date = new DateTime();
    $mysql_today_date = $today_date->format('Y-m-d');
    $mysql_today_date .= ' 00:00:00';
    //echo $mysql_today_date;
    $start_date = new DateTime();
    $start_date->sub(new DateInterval('P' . $days . 'D'));
    $end_date = $start_date;
    $mysql_start_date = $start_date->format('Y-m-d');
    $mysql_start_date .= ' 00:00:00';
    $mysql_end_date = $end_date->format('Y-m-d');
    $mysql_end_date .= ' 23:59:59';
    //echo $mysql_past_date; 
    $ret = array();
    $ret['startdate'] = $mysql_start_date;
    $ret['enddate'] = $mysql_end_date;
    return $ret;
}

/*
 * Retrieve No of unique users in actual billing period for a given client
 */

function checkClientQuota($clientid) {
    // get create_date
    lazyLoadDB();
    $CI = & get_instance();
    $sql = "SELECT createddate FROM client where clientid=?";
    $query = $CI->db->query($sql, $clientid);
    $mysql_create_date = $query->row()->createddate;
    $period = computeBillingPeriod($mysql_create_date);

    $sql = "select count(*) as count from request_events 
			where clientid = ? 
			and type = 1
			and date > ?
			and date < ?
			";
    $query = $CI->db->query($sql, array($clientid, $period[startdate], $period[enddate]));
    $count = $query->row()->count;
    return $count;
}

/*
 * Retrieve the client ID for which to perform an action on an admin page
 * Take into account:
 * 		clientid specified in the URL
 * 		clientid of the user who is currently logged in (session)
 * 		permissions of the user (is she a master account with permissions
 * 		for the specified clientid?)
 */

function getClientIdForAction($thisClientId) {
    // if a client is is set as additional parameter, check if the logged in user
    // is master client of the client to display tests for.
    // if so, display the page.
    $CI = & get_instance();
    $CI->load->library('session');
    $onlineClientId = $CI->session->userdata('sessionUserId');
    $clientid = $onlineClientId; // default: show page for logged in user
    if ($thisClientId) {
        if ($onlineClientId) {
            //echo "master: $onlineClientId sub:$thisClientId";
            $subAccounts = $CI->user->getSubAccounts($onlineClientId);
            if ($subAccounts) {
                if (in_array($thisClientId, $subAccounts)) {
                    $clientid = $thisClientId; // default: show page for logged in user
                }
            }
        }
    }
    return $clientid;
}

/*
 * Helper function:
 * read clientid from URL and call getClientIdForAction
 * 		$segmentid: ID of segment in URL where to find the clientid 
 */

function getClientIdForActionFromUrl($segmentid = 3) {
    $CI = & get_instance();
    $thisClientId = $CI->uri->segment($segmentid);
    $clientid = getClientIdForAction($thisClientId);
    // if failure: try to retrieve client via apikey/secret
    if(!$clientid) {
        $cert = $CI->input->get('cert');
        if($cert) {
            $apidata = explode(":",base64_decode(urldecode($cert)));
            $apikey = $apidata[0];
            $apisecret = $apidata[1];
            $query = $CI->db->select("clientid")
                    ->from("api_client")
                    ->where("apikey", $apikey)
                    ->where("apisecret", $apisecret)
                    ->limit(1)
                    ->get();
            foreach ($query->result() as $q) {
                $clientid  = $q->clientid;
                $CI->session->set_userdata('sessionUserId', $clientid);
                $CI->session->set_userdata('sessionLoginStatus', LOGIN_STATUS_FULL);
            }
        }
    } 
    return $clientid;
}

/*
 * return details for a plan for a given plan id
 */

function getPlanDetails($planId) {
    $ret = array();

    $CI = & get_instance();
    $plans = $CI->config->item('PLAN');
    $plan_data = $CI->config->item('PLAN_INFO');
    $plan_key = array_search($planId, $plans);
    $ret['plan_name'] = $plan_data[$plan_key]['name'];
    $plan_quota = $plan_data[$plan_key]['quota'];
    $ret['plan_quota'] = $plan_quota;
    $ret['plan_quota_name'] = $plan_quota > 1E10 ? $CI->lang->line('unlimited') : $plan_quota;

    return $ret;
}
?>