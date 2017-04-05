<?php

/**
 * This class inherits from the apiv1_core class which have some common variables and methods
 * for all of its childs
 */
require_once 'apiv1_core.php';

final class apiv1_projects extends apiv1_core {

    protected $project;
    protected $rule;
    protected $account;
    // ipblacklist works inverse, so 0 represents TRUE and 1 represents FALSE
    protected $ipblacklisting_array = array(
        0 => TRUE,
        1 => FALSE,
    );
    // To get the corresponding "testype" value given its  name
    protected $type_array = array(
        1 => 'SPLIT',
        3 => 'VISUAL',
        4 => 'TEASERTEST',
        5 => 'REMOTE',
        6 => 'MULTIPAGE'
    );
    // To get a "status" value given its name
    protected $status_array = array(
        0 => 'UNSET',
        1 => 'PAUSED',
        2 => 'RUNNING',
    );
    // To get a "personalization_mode" value given its name
    protected $personalizationmode_array = array(
        0 => 'NONE',
        1 => 'COMPLETE',
        2 => 'SINGLE',
    );
    // To get the autopilot code given its name
    protected $autopilot_array = array(
        0 => 'PAUSED',
        1 => 'RUNNING',
    );
    // To get the appropriate DB field given the name that the client sends.
    protected $dbfields = array(
        'id' => 'lpc.landingpage_collectionid',
        'name' => 'lpc.name',
        'type' => 'lpc.testtype',
        'config' => 'lpc.config',
        'status' => 'lpc.status',
        'createddate' => 'lpc.creation_date',
        'startdate' => 'lpc.start_date',
        'enddate' => 'lpc.end_date',
        'allocation' => 'lpc.allocation',
        'personalizationmode' => 'lpc.personalization_mode',
        'ipblacklisting' => 'lpc.ignore_ip_blacklist',
        'result' => 'lpc.progress',
        'devicetype' => 'lpc.device_type',
        'mainurl' => 'lp.lp_url',
        'runpattern' => 'lp.canonical_url',
        'ruleid' => 'lp.rule_id',
        'visitors' => 'stat.impressions',
        'conversions' => 'stat.conversions',
        'conversionrate' => 'stat.cr',
    );
    // Field that are mandatory when creating or editing projects
    protected $mandatoryfields = array(
        'type',
        'mainurl',
        'runpattern',
        'name',
    );
    // When creating or editing projects, users can't set the next fields because they are read-only
    protected $readonlyfields = array(
        'id',
        'previewurl',
        'createddate',
        'restartdate',
        'remainingdays',
        'status',
        'visitors',
        'conversions',
        'conversionrate',
        'result',
        'originalid',
        'winnerid',
        'winnername',
        'uplift',
        'autopilot',
    );
    // This fields can be modified only by tenants
    protected $tenantfields = array(
        'devicetype',
    );
    // Array map of features available when creating or editing projects
    private $projectFeatures = array(
        'type' => array(
            'VISUAL' => 'visualtest',
            'SPLIT' => 'splittest',
            'TEASERTEST' => 'teasertest',
            'MULTIPAGE' => 'multipagetest'
        ),
        'personalizationmode' => array(
            'NONE' => TRUE,
            'COMPLETE' => 'personalization',
            'SINGLE' => 'personalization',
        ),
        'startdate' => 'startenddate',
        'enddate' => 'startenddate',
    );

    function __construct() {
        parent::__construct();
        $this->load->helper('apiv1');
        $this->config->load('config');
    }

    /**
     * if the attribute name is in the $commonAttribs array (parent), sets the corresponding 
     * value passed as parameter
     * @param String $attrib - the attribute name
     * @param TYPE $value - value to be set for the attribute name
     */
    public function __set($attrib, $value) {
        if (in_array($attrib, $this->commonAttribs)) {
            $this->$attrib = $value;
        }
    }

    /*     * ******************************************************************************
     * *********************************** MAIN METHODS *********************************
     * ********************************************************************************* */

    /**
     * First, it verifies that the requested resource exists and can be accessed (By calling the
     * "setValuesReturnMethod" function, which will throw an exception otherwise.
     * the method "clientOwnsAccount" will throw an exception in case the logged user can't access
     * the account id set in the URL
     * 
     * @param array $uri - the URL sections mapped in an array as key=>value pairs
     * @return JSON * returned by the corresponding method
     * @throws Exception
     */
    public function index($uri) {
        $method = self::setValuesReturnMethod($uri);
        $this->clientOwnsAccount();
        return self::$method();
    }

    /**
     * Verifies the entire requested URL, sets the corresponding values (account id, project id...)  and 
     * returns the method name (getProjects, postProject...) which is the first element of the last URL pair
     * 
     * If the logged user is a tenant, and the parameter "account" is not one of his clients OR if the
     * logged user is a client and the "account" parameter is not equal to his clientid, throws an exeption
     * 
     * 
     * example:
     * GET BASEURL/account/4511/project/7445 
     *  - sets $this->account = 4511
     *  - sets $this->project =7445
     *  - returns "getProject" as the method name
     * 
     * @param array $uri
     * @return string - the method name
     * @throws Exception
     */
    protected function setValuesReturnMethod($uri) {
        $meth = '';
        $actions = array('start', 'stop', 'restart');

        foreach ($uri as $key => $value) {
            if ($key == 'autopilot') {
                $meth = ucfirst($value) . 'Autopilot';
            } else if (in_array($key, $actions)) {
                $meth = ucfirst($key) . 'Project';
            } else {
                $this->$key = $value;
                $meth = ucfirst($key);
            }
        }

        $method = strtolower($this->requestMethod) . $meth;
        if (!method_exists(get_class(), $method)) {
            throw new Exception($method, 404001);
        }
        return $method;
    }

    /*     * ******************************************************************************
     * ***** COMMON METHODS for both, getProject (project details) and getProjects (project list) *****
     * ********************************************************************************* */

    /**
     * Given a project (LPC) id, returns the sum of impressions, the sum of conversions
     * and the average of conversionrates
     * @param Int $lpcid
     * @return Array
     */
    private function getProjectStatistics($lpcid) {
        $query = $this->db->select('SUM(impressions) AS visitors, SUM(conversions) AS conversions', FALSE)
                ->from('landing_page')
                ->where('landingpage_collectionid', $lpcid)
                ->get();

        if ((int) $query->num_rows() > 0) {
            $row = $query->row();
            $cr = $row->visitors > 0 ? $row->conversions / $row->visitors : 0;
            return array(
                'visitors' => $row->visitors,
                'conversions' => $row->conversions,
                'conversionrate' => $cr,
            );
        }
        return array('visitors' => 0, 'conversions' => 0, 'conversionrate' => 0);
    }

    /**
     * Returns the remaining time for an A/B test to  run in days. If the value can not be calculated
     * then -1 is returned.
     * 
     * @param Int $lpcid
     * @param Array $row
     * @return remaining time in days or -1
     */
    private function getRemainingDays($lpcid, $row) {
        $query = $this->db->select('landing_pageid, pagetype, impressions, conversions, z_score')
                ->from('landing_page')
                ->where('landingpage_collectionid', $lpcid)
                ->get();

        $decisions = array();
        foreach ($query->result() as $q) {
            $decisions[] = $q;
        }

        // calculate bandwidth of the project (traffic per time)
        $sample_seconds = (int) $row->sample_time > 0 ? $row->sample_time : 0;
        $lastDate = $row->last_sample_date != NULL ? $row->last_sample_date : FALSE;
        $diff_seconds = $lastDate ? strtotime(date('Y-m-d H:i:s')) - strtotime($lastDate) : 0;

        if ($row->status == 2) {
            $sample_seconds += $diff_seconds;
        }

        $visitorcount = 0;
        $conversioncount = 0;
        $traffic_consuming_pages = 1; // 1 for the control

        foreach ($decisions as $d) {
            $visitorcount += $d->impressions;
            $conversioncount += $d->conversions;
            if ($d->pagetype == 2 && $d->z_score < $this->config->item('MAX_ZSCORE')) {
                $traffic_consuming_pages++;
            }
        }

        if ($sample_seconds == 0) {
            $variant_bandwidth = 0;
        } else {
            $variant_bandwidth = $visitorcount / ($sample_seconds * $traffic_consuming_pages);
        }

        $control_remaining_seconds = 0; // will be filled with the remaining runtime for the control page
        $collection_remaining_seconds = 0; // will be filled with the remaining runtime for the collection

        foreach ($decisions as $d) {

            if ($d->pagetype == OPT_PAGETYPE_CTRL) {
                continue;
            }

            $n = $d->impressions;
            $z = $d->z_score;
            $zMax = $this->config->item('MAX_ZSCORE');

            if ($z < $zMax) {
                if ($n < 10) {
                    // if not enough impressions for the page
                    $d->remaining_seconds = -1;
                } elseif ($variant_bandwidth == 0) {
                    // if no traffic at all for some reason (not sure if this can happen at all....)
                    $d->remaining_seconds = -1;
                } elseif ($z == 0) {
                    $d->remaining_seconds = -1;
                } else {
                    // all data in place to calculate remaining time
                    $nRest = round(pow(($zMax * sqrt($n) / $z), 2)) - $n;
                    if ($nRest < 0) {
                        $nRest = 0;
                    }
                    $d->remaining_seconds = $nRest / $variant_bandwidth;
                }
            } else {
                $d->remaining_seconds = 0;
            }

            // assumption: the control will need as many seconds as the variant with the longest runtime
            if ($control_remaining_seconds < $d->remaining_seconds) {
                $control_remaining_seconds = $d->remaining_seconds;
            }
            if ($d->remaining_seconds > 0) {
                $collection_remaining_seconds += $d->remaining_seconds;
            }
            // if at least one variant is not far enough for an estimation, the whole collection is not far enough 
            if ($d->remaining_seconds == -1) {
                $collection_remaining_seconds = -1;
            }
        }
        // if used sample time + approximate remaining time < minimum runtime of a test (7 days)
        // then show an approximation based on overall runtime of 7 days        
        // if test would run shorter than 7 days, make it at least 7 days
        if ($collection_remaining_seconds != -1) {
            $collection_remaining_seconds += $control_remaining_seconds;
            if (($sample_seconds + $collection_remaining_seconds) < $this->config->item('MIN_SAMPLE_TIME')) {
                $collection_remaining_seconds = $this->config->item('MIN_SAMPLE_TIME') - $sample_seconds;
            }
            $remainingdays = round(($collection_remaining_seconds) / 86400, 0);
        } else {
            $remainingdays = -1;
        }
        return $remainingdays;
    }

    /**
     * Verifies if the project is significant (progress = 2) if it is not an SMS test, if the perso mode is not SINGLE
     * and the test type is SPLIT or VISUAL (1 or 3) ... continues.
     * 
     * Gets the Conversion Rate, the id, the name and the pagetype for the winner variant and for the control
     * and returns the uplift for the project, the name and the ID of the winner variant.
     * 
     * @param Int $lpcid
     * @param Array $row
     * @return Array - with the id, name and uplift if there is a winner variant
     */
    private function getWinner($lpcid, $row) {
        if (is_null($row->testtype) || is_null($row->progress) || is_null($row->smartmessage) || is_null($row->personalization_mode)) {
            $row = self::winnerRequiredFields($lpcid);
        }
        $type = $row->testtype;
        $progress = $row->progress;
        $sms = $row->smartmessage;
        $perso = $row->personalization_mode;

        $validType = in_array($type, array(OPT_TESTTYPE_SPLIT, OPT_TESTTYPE_VISUALAB));

        if (!$validType || $progress != OPT_PROGRESS_SIG || $sms == OPT_IS_SMS_TEST || $perso == OPT_PERSOMODE_SINGLE) {
            return array('winnerid' => -1, 'winnername' => 'NA', 'winner_cr' => -1, 'uplift' => -1);
        }

        $max = self::getHigherCr($lpcid);
        $query = $this->db->select('landing_pageid, name, pagetype, CAST(cr AS DECIMAL(8,6)) AS cr', FALSE)
                ->from('landing_page')
                ->where('landingpage_collectionid', $lpcid)
                ->get();

        $winner_cr = 0;
        $winner_id = -1;
        $winner_name = 'NA';
        $control_winner = FALSE;

        foreach ($query->result() as $q) {
            if ($q->pagetype == 1) {
                $control_cr = $q->cr > 0 ? $q->cr : 1;
                $control_winner = $q->cr == $max ? TRUE : FALSE;
            } else if ($q->cr == $max) {
                $winner_cr = $q->cr;
                $winner_id = $q->landing_pageid;
                $winner_name = $q->name;
            }
        }

        $uplift = ($winner_cr - $control_cr) / $control_cr;
        return array(
            'winnerid' => $winner_id,
            'winnername' => $winner_name,
            'winner_cr' => $winner_cr,
            'control_cr' => $control_winner ? $control_cr : FALSE,
            'uplift' => $uplift
        );
    }

    /**
     * If within the custom fields the user sets in the URL there are not the fields type, progress, sms or perso,
     * this method gets the corresponding values for the given test (LPC ID) to determine the winner of
     * the project (if any)
     * @param Int $lpcid
     * @return Array
     */
    private function winnerRequiredFields($lpcid) {
        $query = $this->db->select('testtype, progress, smartmessage, personalization_mode')
                ->from('landingpage_collection')
                ->where('landingpage_collectionid', $lpcid)
                ->get();

        $row = $query->row();

        $res = json_encode(array(
            'testtype' => $row->testtype,
            'progress' => $row->progress,
            'smartmessage' => $row->smartmessage,
            'personalization_mode' => $row->personalization_mode
        ));
        return json_decode($res);
    }

    /**
     * Gets the higher conversionrate from a set of variants given a project (LPC) id
     * @param Int $lpcid
     * @return float
     */
    private function getHigherCr($lpcid) {
        $query1 = $this->db->select('CAST(MAX(cr) AS DECIMAL(8,6)) AS max', FALSE)
                ->from('landing_page')
                ->where('landingpage_collectionid', $lpcid)
                ->get();
        return $query1->row()->max;
    }

    /**
     * As the user can select custom fields to be returned, this methods calls the corresponding method
     * to do so, if there are customParameters calls "customProjectFields()" or else "allProjectFIelds()"
     * @param Array $q - The current row for the result array
     * @param Boolean $projectDetails - If it is called from "getProject" it always returns all the fields
     * @return Array
     */
    private function returnPojectFields($q, $projectDetails = FALSE) {
        $id = $q->landingpage_collectionid;
        $statistics = self::getProjectStatistics($id);
        $winner = self::getWinner($id, $q);
        $remainingdays = self::getRemainingDays($id, $q);

        if ($q->smartmessage == OPT_IS_SMS_TEST || $q->personalization_mode == OPT_PERSOMODE_SINGLE) {
            $result = 'NA';
        } else {
            $result = ($q->progress == OPT_PROGRESS_SIG) ? $winner['winner_cr'] : 'NONE';
            if ($winner['winner_cr'] !== 'NA' && $q->progress == OPT_PROGRESS_SIG) {
                $result = $winner['winner_cr'] == 0 ? 'LOST' : 'WON';
            }
        }

        if ($this->requestParameters['fields'] && !$projectDetails) {
            return self::customProjectFields($q, $result, $winner, $statistics, $remainingdays);
        } else {
            return self::allProjectFields($q, $result, $winner, $statistics, $remainingdays);
        }
    }

    /**
     * Returns ALL of the available fields for the project
     * @param Array $q - The current row for the result array
     * @param String $result - either "WON", "LOST" or "NA"
     * @param Int $winner - The winner ID if any
     * @param Array $statistics - If there is not a winner, this contains the AVG conversion rate for all variants.
     * @return Array
     */
    private function allProjectFields($q, $result, $winner, $statistics, $remainingdays) {

        if ($q->progress == OPT_PROGRESS_SIG) {
            $cr = $winner['control_cr'] ? $winner['control_cr'] : $winner['winner_cr'];
        } else {
            $cr = $statistics['conversionrate'];
        }

        $isSms = $q->testtype == OPT_TESTTYPE_VISUALAB && $q->smartmessage == OPT_IS_SMS_TEST;

        $project = array(
            'id' => $q->landingpage_collectionid,
            'name' => $q->name,
            'mainurl' => $q->lp_url,
            'type' => $isSms ? 'SMARTMESSAGE' : $this->type_array[$q->testtype],
            'config' => $q->config,
            'runpattern' => $q->canonical_url,
            'createddate' => $q->creation_date,
            'startdate' => $q->start_date,
            'enddate' => $q->end_date,
            'restartdate' => $q->restart_date,
            'remainingdays' => $remainingdays,
            'status' => $this->status_array[$q->status],
            'visitors' => $statistics['visitors'],
            'conversions' => $statistics['conversions'],
            'conversionrate' => $cr,
            'result' => $result,
            'ruleid' => $q->rule_id,
            'originalid' => $q->originalid,
            'winnerid' => $winner['winnerid'],
            'winnername' => $winner['winnername'],
            'uplift' => $winner['uplift'],
            'autopilot' => $this->autopilot_array[$q->autopilot],
            'allocation' => $q->allocation * 100,
            'devicetype' => $q->device_type,
            'ipblacklisting' => $this->ipblacklisting_array[$q->ignore_ip_blacklist],
            'personalizationmode' => $this->personalizationmode_array[$q->personalization_mode],
        );

        if ($q->testtype == OPT_TESTTYPE_TEASER) {
            $amp = stripos($q->ctrl_url, '?') > 0 ? '&' : '?';
            $preview = $q->lp_url . $amp . '_p=t&BT_cid=' . $q->landingpage_collectionid;
            $project += array('previewurl' => $preview);
        }

        return $project;
    }

    /*     * ******************************************************************************
     * ************** (PLURAL) GET PROJECTS RELATED METHODS -- Project Overview *************
     * ********************************************************************************* */

    /**
     * If the URL hasn't the parameter "fields" (that sets custom DB fields to be retrieved), then the select
     *      statement will retrieve all the available fields for the projects overview
     * builds the query and complements it by calling addParametersToQuery() - to add the custom filters
     *      and sort order stablished by the client in the url (for example: sort=id&type=VISUAL)
     * Then returns the results depending on if the user set custom fields or not (returns all of them)
     *      this is evaluated in customProjectFields()
     * 
     * @return JSON
     */
    private function getProjects() {
        if (!$this->requestParameters['fields']) {
            $this->db->select('lpc.landingpage_collectionid, lpc.testtype, lpc.name, lpc.config, lpc.creation_date, lpc.start_date, ' .
                    ' lpc.end_date, lpc.restart_date, lpc.status, lpc.progress, lpc.autopilot, lpc.allocation, lpc.last_sample_date, lpc.sample_time, ' .
                    ' lpc.personalization_mode, lpc.smartmessage, lpc.ignore_ip_blacklist, lpc.device_type, lp.rule_id, ' .
                    ' lp.originalid, lp.lp_url, lp.canonical_url, stat.impressions, stat.conversions, stat.cr ');
        } else {
            $this->requestParameters['fields'] = rtrim($this->requestParameters['fields'], ',');
        }

        $lp = "(SELECT landingpage_collectionid, landing_pageid AS originalid, lp_url, canonical_url, rule_id, allocation " .
                " FROM landing_page WHERE pagetype = 1 AND page_groupid = -1 " .
                " GROUP BY landingpage_collectionid ORDER BY landing_pageid ASC ) lp ";

        $stat = "(SELECT landingpage_collectionid, SUM(impressions) AS impressions, " .
                " SUM(conversions) AS conversions, CAST(AVG(cr) AS DECIMAL(8,6)) AS cr " .
                " FROM landing_page GROUP BY landingpage_collectionid) stat ";

        $this->db->from('landingpage_collection lpc')
                ->join($lp, 'lp.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                ->join($stat, 'stat.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER');

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = lpc.clientid', 'INNER')
                    ->where('lpc.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('lpc.clientid', $this->clientid);
        }

        parent::addParametersToQuery();

        $validtypes = array(OPT_TESTTYPE_SPLIT, OPT_TESTTYPE_VISUALAB, OPT_TESTTYPE_TEASER, OPT_TESTTYPE_MULTIPAGE);
        $query = $this->db->where_in('testtype', $validtypes)
                ->group_by('lpc.landingpage_collectionid')
                ->get();

        $res = array();
        foreach ($query->result() as $q) {
            $r = self::returnPojectFields($q, FALSE);
            $res[] = $r;
        }
        return $this->successResponse(200, $res);
    }

    /**
     * If the field "smartmessage" is not in the field list, verifies if the given test is "Visual" (3) and if
     * it is an SMS (1) to return the appropriate testtype
     * @param Int $lpcid
     * @return Boolean
     */
    private function isSmartMessage($lpcid) {
        $query = $this->db->select('testtype, smartmessage')
                ->from('landingpage_collection')
                ->where('landingpage_collectionid', $lpcid)
                ->get();
        $row = $query->row();
        return $row->testtype == OPT_TESTTYPE_VISUALAB && $row->smartmessage == OPT_IS_SMS_TEST;
    }

    /**
     * As the user can select custom fields, this method returns only thos fields set by the User in the URL
     * for example  management/getProjects?fields=id,name,visitors
     * Note: The field "ID" is always required, so if it was not set by the user, it is added automatically when
     * retrieving data from the DB and here (in this method) to return the corresponding value
     * @param Array $q - The current row for the result array
     * @return array
     */
    private function customProjectFields($q, $result, $winner, $statistics, $remainingdays) {
        $r = array();
        $fields = split(',', $this->requestParameters['fields']);

        $isSms = FALSE;
        if (in_array('type', $fields)) {
            $isSms = self::isSmartMessage($q->landingpage_collectionid);
        }

        if ($q->progress == OPT_PROGRESS_SIG) {
            $cr = $winner['control_cr'] ? $winner['control_cr'] : $winner['winner_cr'];
        } else {
            $cr = $statistics['conversionrate'];
        }

        foreach ($fields as $key) {

            if (!array_key_exists($key, $this->dbfields)) {
                throw new Exception($key, 400003);
            }

            $k = $this->dbfields[$key];
            $keys = split('\.', $k);

            $fieldarray = $key . '_array';
            $customFields = array(
                'result' => $result,
                'remainingdays' => $remainingdays,
                'conversionrate' => $cr
            );

            if (array_key_exists($key, $customFields)) {
                $r[$key] = $customFields[$key];
            } else if ($key == 'type') {
                $r['type'] = $isSms ? 'SMARTMESSAGE' : $this->type_array[$q->testtype];
            } else if (is_array($this->{"$fieldarray"})) {
                $r[$key] = $this->{"$fieldarray"}[$q->$keys[1]];
            } else if (array_key_exists($key, $statistics)) {
                $r[$key] = $statistics[$key];
            } else if (array_key_exists($key, $winner)) {
                $r[$key] = $winner[$key];
            } else if ($key == 'allocation') {
                $r[$key] = $q->allocation * 100;
            } else {
                $r[$key] = $q->$keys[1];
            }
        }
        return $r;
    }

    /*     * ******************************************************************************
     * ************* (SINGULAR) GET PROJECT RELATED METHODS - Project Details ****************
     * ********************************************************************************* */

    /**
     * SINGULAR "getProject"
     * this method returns all project related data given a project ID in the $requestParameters array.
     * first, it verifies that the project ID is valid and that it belongs to the logged client
     *      If it doesn't, returns a 401 "unauthorized" error
     * Then searchs into the DB and if it does not found anything returns a 404 "not found" error
     * If everything goes well, returns a 200 "success" message with the project data.
     * @return type
     * @throws Exception - if there aren't any projects with the given LPCID
     */
    private function getProject() {
        $this->clientOwnsProject();

        $select = ('lpc.landingpage_collectionid, lpc.testtype, lpc.name, lpc.config, lpc.creation_date, lpc.start_date, ' .
                ' lpc.end_date, lpc.restart_date, lpc.status, lpc.progress, lpc.autopilot, lpc.allocation, lpc.last_sample_date, lpc.sample_time, ' .
                ' lpc.personalization_mode, lpc.smartmessage, lpc.ignore_ip_blacklist, lpc.device_type, lp.rule_id, ' .
                ' lp.landing_pageid AS originalid, lp.lp_url, lp.canonical_url ');

        $lpcid = mysql_real_escape_string($this->project);
        $lp = "(SELECT landing_pageid, landingpage_collectionid, lp_url, canonical_url, rule_id, allocation " .
                " FROM landing_page WHERE landingpage_collectionid = $lpcid AND pagetype = 1 AND page_groupid = -1 " .
                " GROUP BY landingpage_collectionid ORDER BY landing_pageid ASC LIMIT 1 ) lp ";

        $this->db->select($select)
                ->from('landingpage_collection lpc')
                ->join($lp, 'lp.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                ->where('lpc.landingpage_collectionid', $this->project);

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = lpc.clientid', 'INNER')
                    ->where('lpc.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('lpc.clientid', $this->clientid);
        }

        $query = $this->db->get();

        if ($query->num_rows() <= 0) {
            throw new Exception('', 403203);
        }

        $row = $query->row();
        $res = self::returnPojectFields($row, TRUE);
        return $this->successResponse(200, $res);
    }

    /*     * ******************************************************************************
     * ***************** COMMON METHODS FOR postProject and editProject *********************
     * ********************************************************************************* */

    /**
     * goes throught every element in the requestParameters array and determines whether it belongs to
     * a project or a variant to create the corresponding object to be inserted or updated later depending
     * on the method requested (postProject or putProject)
     * the $helper variable will check if there is a function in apiv1_helper called 'project_valid_xxxx'
     *     where xxxx is the name of the parameter (i.e. project_valid_mainurl)
     *     If the function exists, checks if the value for that parameter is valid or else returns an error.
     * 
     * With every iteration over the posted array of fields, it verifies that fields set are available in the
     * feature DB field for the current client.
     * 
     * ******** NOTE ********* : feature array is retrieved here to avoid doing it in the foreach loop
     * (I was tempted to do it in the "verifyProjectFeatures()" method)
     * 
     * EDIT: if the field "ruleid" is set, verified that the rule belongs to the current user or to one of
     * the current tenat's users-
     * Also, if ruleid is set to 0 or is not set, the 
     * 
     * @return array -  an array containing the project and the control arrays
     * @throws Exception - in case there are one or more invalid fields
     */
    private function setProjectArray() {
        $feature_array = $this->getClientFeatures();
        $post_array = json_decode($this->requestParameters);
        $lpfields = array('mainurl', 'runpattern', 'ruleid');
        $project = array();
        $control = array();

        $invalid_field = '';
        $invalid_value = '';

        if (array_key_exists('ruleid', $post_array)) {
            $this->rule = $post_array->ruleid;
            $this->rule > 0 ? $this->clientOwnsRule() : '';
        }

        foreach ($post_array as $key => $value) {
            self::verifyProjectFeatures($feature_array, $key, $value);

            if ($key == 'runpattern') {
                json_decode($value);
                if (json_last_error() != JSON_ERROR_NONE) {
                    $value = json_encode(array(array(
                        'mode' => 'include',
                        'url' => canonicalUrl($value),
                    )));
                }
            }

            $helper = 'project_valid_' . $key;
            if (function_exists($helper) && !$helper($value) || (!array_key_exists($key, $this->dbfields))) {
                $invalid_value .= $key . ': "' . $value . '", ';
                continue;
            }
            $k = split('\.', $this->dbfields[$key]);
            $fieldarray = $key . '_array';
            $val = (is_array($this->{"$fieldarray"})) ? array_search($value, $this->{"$fieldarray"}) : $value;

            if ($key == 'type' && $value == 'SMARTMESSAGE') {
                $val = OPT_TESTTYPE_VISUALAB;
            }

            if (in_array($key, $lpfields)) {
                $control[$k[1]] = $val;
            } else if (array_key_exists($key, $this->dbfields)) {
                $project[$k[1]] = $val;
            } else {
                $invalid_field.= "$key, ";
            }
        }

        if (trim($invalid_field) != '') {
            throw new Exception($invalid_field, 400003);
        }
        if (trim($invalid_value) != '') {
            throw new Exception($invalid_value, 400004);
        }

        if (array_key_exists('allocation', $project)) {
            $project['allocation'] /= 100;
        }

        return array('status' => 'OK', 'project' => $project, 'control' => $control);
    }

    /**
     * When creating or editing projects, this method verifies that fields posted are available for the 
     * current user to be set by checking the "features" array saved into the DB for the given clientid
     * this method is called for every iteration in the setProjectArrayMethod
     * @return boolean (TRUE) - if the features array is null, empty, not a valid JSON
     * @throws Exception - in case there are fields posted that are not part of the features array
     */
    private function verifyProjectFeatures($feature_array, $key, $value) {
        $features = json_decode($feature_array);
        if (json_last_error() != JSON_ERROR_NONE) {
            return TRUE;
        }

        $error = '';
        if (array_key_exists($key, $this->projectFeatures)) {
            $ft = $this->projectFeatures[$key];
            $item = is_array($ft) ? $ft[$value] : $ft;

            if (is_bool($item)) {
                return TRUE;
            }
            $error .= isset($features->$item) && !$features->$item ? $item . ', ' : '';
        }

        if ($error != '') {
            throw new Exception($error, 403006);
        }
    }

    /*     * ******************************************************************************
     * *************** POST PROJECT RELATED METHODS - Create New Project *******************
     * ********************************************************************************* */

    /**
     * the method "clientOwnsAccount" will throw an exception in case the logged user can't access
     * the account id set in the URL
     * If the mandatory fields are set, and no read-only fields are set,
     * Calls the method setProjectArray to build the corresponding key/value pairs to insert a new project
     * and the control, then calls the corresponding methods to do so.
     * @return JSON
     * @throws Exception - If the project could not be inserted (the inserted id is not int)
     */
    private function postProject() {
        $this->clientOwnsAccount();
        $this->checkMandatoryFields(FALSE, 400200);
        $this->checkReadOnlyFields(FALSE, 400201);
        if ($this->usertype != 'api-tenant') {
            $this->checkReadOnlyFields($this->tenantfields, 400201);
        }
        $projectArray = self::setProjectArray();

        $project = $projectArray['project'];
        $control = $projectArray['control'];

        $personalized = isset($project['personalization_mode']);
        if ($project['testtype'] == OPT_TESTTYPE_TEASER && $personalized && $project['personalization_mode'] != OPT_PERSOMODE_NONE) {
            throw new Exception('personalizationmode', 400004);
        }

        $completePerso = ($personalized && $project['personalization_mode'] == OPT_PERSOMODE_COMPLETE);
        $noControlRule = (!isset($control['rule_id']) || $control['rule_id'] <= 0);
        if ($completePerso && $noControlRule) {
            throw new Exception('When personalizationmode is set to COMPLETE, a valid ruleid is mandatory', 400200);
        }

        $lpcid = self::insertProject($project);

        if (!is_int($lpcid) || $lpcid <= 0) {
            throw new Exception('The project could not be created', 500);
        }
        $response = self::insertControl($control, $lpcid);
        $this->optimisation->flushCollectionCache($lpcid);
        return $response;
    }

    /**
     * gets the array of key->value pairs to insert a new LPC, complements the required fields and
     * performs the insert
     * @param array $project
     * @return Int - The inserted LPC id
     */
    private function insertProject($project) {
        $project['status'] = 1;
        $project['progress'] = OPT_PROGRESS_NSIG;
        $project['optimization_mode'] = 1;
        $project['clientid'] = $this->account;
        $project['start_date'] = $project['start_date'] ? $project['start_date'] : date('y-m-d H:i:s');
        $project['restart_date'] = date('y-m-d H:i:s');
        $project['end_date'] = $project['end_date'] ? $project['end_date'] : date('Y-m-d', strtotime('+366 days'));

        $this->db->insert('landingpage_collection', $project);
        $lpcid = $this->db->insert_id();

        $this->db->where('landingpage_collectionid', $lpcid)
                ->update('landingpage_collection', array('code' => 'BT-' . md5($lpcid)));

        return $lpcid;
    }

    /**
     * complements the required fields to insert the control variant and verifies the LP id to return
     * a success message along with the LPC id or in case the control variant could not be inserted, 
     * deletes the project and returns an error
     * @param Array $control - array of field sent from the client (lp_url, canonical...
     * @param INT $lpcid
     * @return JSON
     * @throws Exception - if the last inserted id is not an integer (error)
     */
    private function insertControl($control, $lpcid) {
        $control['landingpage_collectionid'] = $lpcid;
        $control['name'] = $lpcid . '_control';
        $control['pagetype'] = 1;

        $this->db->insert('landing_page', $control);
        $lpd = $this->db->insert_id();

        if (!is_int($lpd) || $lpd <= 0) {
            $this->project = $lpcid;
            self::deleteProject();
            throw new Exception('The control variant could not be created', 500);
        }
        $this->syncCollectionGoals();
        return $this->successResponse(200, $lpcid);
    }

    /*     * ******************************************************************************
     * ***************** PUT PROJECT RELATED METHODS - Edit Project Data *********************
     * ********************************************************************************* */

    /**
     * varifies that the project id is valid and it belongs to the logged client, if so, calls the method
     * setProjectArray to build the corresponding key/value pairs to update the project itself and the 
     * control variant, the calls the corresponding methods to do so.
     * if the project does not belong to the current client, returns a 401 "unauthorized" error
     * @return JSON
     */
    private function putProject() {
        $this->clientOwnsProject();
        $this->checkReadOnlyFields(FALSE, 400201);

        if ($this->usertype != 'api-tenant') {
            $this->checkReadOnlyFields($this->tenantfields, 400201);
        }
        $projectArray = self::setProjectArray();

        $project = $projectArray['project'];
        $control = $projectArray['control'];

        $completePerso = (isset($project['personalization_mode']) && $project['personalization_mode'] == OPT_PERSOMODE_COMPLETE);
        $noControlRule = (!isset($control['rule_id']) || $control['rule_id'] <= 0);
        if ($completePerso && $noControlRule) {
            self::verifyControlRule();
        }

        if (count($project) > 0) {
            $this->db->where('landingpage_collectionid', $this->project)
                    ->update('landingpage_collection', $project);
        }

        if (count($control) > 0) {
            $this->db->where('landingpage_collectionid', $this->project)
                    ->where('pagetype', OPT_PAGETYPE_CTRL)
                    ->update('landing_page', $control);
        }

        $this->optimisation->flushKpiResultsForCollection($this->project);
        $this->optimisation->evaluateImpactAfterCollectionChange($this->project);
        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(200);
    }

    /**
     * If the edited project has a "COMPLETE" personalizationmode, this method gets the current rule_id 
     * of the control variant for the project to check if it is valid, 
     * @throws Exception - If the rule_id is not defined or it is 0
     */
    private function verifyControlRule() {
        $query = $this->db->select('lp.rule_id')
                ->from('landing_page lp')
                ->join('landingpage_collection lpc', 'lpc.landingpage_collectionid = lp.landingpage_collectionid', 'INNER')
                ->where('lp.landingpage_collectionid', $this->project)
                ->where('lp.pagetype', OPT_PAGETYPE_CTRL)
                ->get();

        $ruleid = $query->row()->rule_id;
        if (!is_numeric($ruleid) || $ruleid <= 0) {
            throw new Exception('When personalizationmode is set to COMPLETE, a valid ruleid is mandatory', 400200);
        }
    }

    /*     * ******************************************************************************
     * ************************* DELETE PROJECT RELATED METHODS *************************
     * ********************************************************************************* */

    /**
     * first, it verifies that the current client is the owner of the project to be deleted-
     * Then, gets the collection code from the DB to flush the cache later on. then it performs
     * the sql queries to delete references from all related tables like collection_goals, landing_page, etc.
     * success delete operations returns 204 "no content" code
     * If the account id is not equal to the saved clientid or the project ID does not belong to the
     * client, returns a 401 "unauthorized" error
     */
    private function deleteProject() {
        $this->clientOwnsProject();

        $q = $this->db->select('code')
                ->from('landingpage_collection')
                ->where('landingpage_collectionid', $this->project)
                ->get();
        $code = $q->row()->code;

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('mvt_level_to_page');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('mvt_level');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('mvt_factor');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('collection_goals');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('collection_goal_conversions');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('page_group');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('landing_page');

        $this->db->where('landingpage_collectionid', $this->project)
                ->delete('landingpage_collection');

        $this->syncCollectionGoals();
        $this->optimisation->flushCollectionCacheByCode($code);
        return $this->successResponse(200);
    }

    /*     * ******************************************************************************
     * ****** METHODS TO HANDLE POST ACTIONS FOR PROJECTS (start, stop, autopilot, etc) ********
     * ********************************************************************************* */

    /**
     * After getting the client features array, if it is set, it verifies that the number of active projects for 
     * the current client does not exceed the maximum number set in the DB 
     * @return boolean
     * @throws Exception - if the project has already the maximum available projects running
     */
    private function verifyActiveProjects() {
        $feature_array = $this->getClientFeatures();
        if (is_null($feature_array) || trim((string) $feature_array) == '') {
            return TRUE;
        }

        $features = json_decode($feature_array);
        if (json_last_error() != JSON_ERROR_NONE) {
            return TRUE;
        }

        if (isset($features->numactiveprojects) && is_numeric($features->numactiveprojects)) {
            $cnt = self::getActiveProjectsCount();
            if ($cnt >= $features->numactiveprojects) {
                throw new Exception("You can only have $features->numactiveprojects active projects at a time", 403006);
            }
        }
    }

    /**
     * @return Int - The number of active project for the current user
     */
    private function getActiveProjectsCount() {
        $query = $this->db->select('COUNT(*) AS cnt')
                ->from('landingpage_collection')
                ->where('clientid', $this->account)
                ->where('status', 2)
                ->get();

        return $query->row()->cnt;
    }

    /**
     * verifies the project ID to be started.
     * If the user is not logged in or he's not the owner of the project returns a 401 "unauthorized" error
     * In case everything is OK, updates the project status to 2 (ACTIVE)
     * @return JSON
     */
    private function postStartProject() {
        $this->clientOwnsProject();
        self::verifyActiveProjects();

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('last_sample_date', date('Y-m-d H:i:s'))
                ->set('status', 2)
                ->update('landingpage_collection');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /**
     * verifies the project ID to be stopped (paused).
     * If the user is not logged in or he's not the owner of the project returns a 401 "unauthorized" error
     * In case everything is OK, updates the project status to 1 (PAUSED)
     * @return JSON
     */
    private function postStopProject() {
        $this->clientOwnsProject();

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('sample_time', 'sample_time + UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_sample_date)', FALSE)
                ->set('status', 1)
                ->update('landingpage_collection');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /**
     * restores the default fields for the given project id
     * @return JSON
     */
    private function postRestartProject() {
        $this->clientOwnsProject();

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('cr', 0)
                ->set('arpv', 0)
                ->set('z_score', 0)
                ->set('revenue', 0)
                ->set('conversions', 0)
                ->set('conversion_value_aggregation', 0)
                ->set('conversion_value_square_aggregation', 0)
                ->set('standard_deviation', 0)
                ->set('impressions', 0)
                ->set('is_maximum', 0)
                ->update('landing_page');

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('conversions', 0)
                ->set('conversion_value_aggregation', 0)
                ->set('conversion_value_square_aggregation', 0)
                ->set('standard_deviation', 0)
                ->update('collection_goal_conversions');

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('progress', 1)
                ->set('sample_time', 0)
                ->set('restart_date', date('Y-m-d H:i:s'))
                ->set('last_sample_date', date('Y-m-d H:i:s'))
                ->update('landingpage_collection');

        $this->optimisation->updateSlotsWithoutProgressChange($this->project);
        $this->optimisation->flushKpiResultsForCollection($this->project);
        $this->optimisation->evaluateImpactAfterCollectionChange($this->project);
        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /**
     * verifies the project ID of the autopilot to be activated.
     * If the user is not logged in or he's not the owner of the project returns a 401 "unauthorized" error
     * In case everything is OK, updates the project autopilot to 1 (ACTIVATED)
     * @return JSON
     */
    private function postStartAutopilot() {
        $this->clientOwnsProject();

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('autopilot', 1)
                ->update('landingpage_collection');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /**
     * verifies the project ID of the autopilot to be de-activated.
     * If the user is not logged in or he's not the owner of the project returns a 401 "unauthorized" error
     * In case everything is OK, updates the project autopilot to 0 (DEACTIVATED)
     * @return JSON
     */
    private function postStopAutopilot() {
        $this->clientOwnsProject();

        $this->db->where("landingpage_collectionid", $this->project)
                ->set('autopilot', 0)
                ->update('landingpage_collection');

        $this->optimisation->updateslots(OPT_SLOTS_EQUIDIST, $this->project);
        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

}
