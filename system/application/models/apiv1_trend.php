<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'apiv1_core.php';

final class apiv1_trend extends apiv1_core {

    protected $account;
    protected $project;
    protected $decisiongroup;
    private $end;
    private $ismpt;
    private $goalid;
    private $goaltype;
    private $entries;
    private $interval;
    private $dateadd;
    private $datesub;
    private $firstevent;
    private $lastevent;
    private $currentpoint;
    private $available_intervals;
    private $dbname = false;
    private $dbserver = false;

    function __construct() {
        parent::__construct();
        $this->load->helper('apiv1');

        $this->available_intervals = array(
            OPT_TREND_DAY,
            OPT_TREND_HOUR,
            OPT_TREND_5MINUTE,
            OPT_TREND_MINUTE,
        );
    }

    /**
     * if the attribute name is in the $commonAttribs array (parent), sets the corresponding 
     * value passed as parameter
     * @param String $attrib - the attribute name
     * @param String $value - value to be set for the attribute name
     */
    public function __set($attrib, $value) {
        if (in_array($attrib, $this->commonAttribs)) {
            $this->$attrib = $value;
        }
    }

    /**
     * As the TREND resource is read-only, the method to be called is always getTrend()
     * but as we need to set the account and project id's, the setParametersReturnMethod() has to be
     * called anyway.
     * @param array $uri - the URL sections mapped in an array as key=>value pairs
     * @return JSON * returned by the corresponding method
     * @throws Exception - if the user is not a valid tenant or client
     */
    public function index($uri) {
        $method = $this->setParametersReturnMethod($uri);
        if ($method != 'getTrend') {
            throw new Exception($method, 404001);
        }
        return self::getTrend();
    }

    /**
     * If the end date is set in the request parameters, converts that string to miliseconds and then back
     * to Y-m-d H:i:s format to ensure that comparissons work fine
     * If the end date is not set, gets the last event date from the request evetns table.
     */
    private function setEndDate() {
        if (array_key_exists('end', $this->requestParameters)) {
            $end = $this->requestParameters['end'];
        } else {
            $end = self::runEventsEdgeQuery('DESC', TRUE);
        }

        $e = new DateTime($end);
        $this->end = $e->format('Y-m-d H:i:s');
    }

    /**
     * Sets the first and last events based on the request_events table
     */
    private function setFirstAndLastEvents() {
        $this->firstevent = self::runEventsEdgeQuery('ASC');
        $this->lastevent = self::runEventsEdgeQuery('DESC');
    }

    /**
     * Depending on if there is a decision group id sent as parameter, we get the first valid date of the
     * given group or project, it can be either the creation date or the restart date.
     * The result complements the sql query to be valid and avoid errors or warnings
     */
    private function getValidComparissonDate() {
        if (is_numeric($this->decisiongroup) && $this->decisiongroup > 0) {
            $query = $this->db->select('creation_date, restart_date')
                    ->from('page_group')
                    ->where('page_groupid', $this->decisiongroup)
                    ->get();

            if ($query->num_rows() > 0) {
                $row = $query->row();
                if ($row->restart_date == NULL) {
                    return $row->creation_date;
                }
                return $row->restart_date;
            }
        } else {
            $query = $this->db->select('testtype, restart_date')
                    ->from('landingpage_collection')
                    ->where('landingpage_collectionid', $this->project)
                    ->get();

            if ($query->num_rows() > 0) {
                $this->ismpt = $query->row()->testtype == OPT_TESTTYPE_MULTIPAGE;
                return $query->row()->restart_date;
            }
        }
        return date('Y-m-d');
    }

    /**
     * Depending on if we are getting the first or last event, runs the query to get the corresponding date.
     * @param String $order - Either 'ASC' or 'DESC'
     * @return String - the date of the first or the last event
     */
    private function runEventsEdgeQuery($order, $end = FALSE) {
        $starttime = microtime(true);
        $this->load->library('multidb', array(
            'dbname' => $this->dbname,
            'dbserver' => $this->dbserver));
        $CLIENT_DB = $this->multidb->getClientDb();
        $compare = self::getValidComparissonDate();

        if (is_numeric($this->decisiongroup) && $this->decisiongroup > 0) {
            $CLIENT_DB->where('page_groupid', $this->decisiongroup);
        }

        $query = $CLIENT_DB->select('date as date')
                ->from('request_events')
                ->where('date >= ', $compare)
                ->where('landingpage_collectionid', $this->project)
                ->order_by('request_eventsid ' . $order)
                ->limit('1')
                ->get();

        $defaultPoint = date('Y-m-d');
        if (!$end) {
            $today = new DateTime($defaultPoint);
            $today->add(new DateInterval('P2D'));
            $defaultPoint = $today->format('Y-m-d');
        }
        $querytime = microtime(true) - $starttime;
        //dblog_debug("time: $querytime " . $CLIENT_DB->last_query());
        return ($query->num_rows() > 0) ? $query->row()->date : $defaultPoint;
    }

    /**
     * verifies if the parameter "entries" is set and if it is a number to set the corresponding value.
     * if it is not defined or <= 0, then the detault value (30) is set.
     * If it is defined but it's bigger that the maximum allowed value, the the maximum value is set (50).
     */
    private function setEntries() {
        $entries = $this->requestParameters['entries'];
        $valid = is_numeric($entries) && $entries > 0 ? (int) $entries : OPT_DEFAULT_TREND_ENTRIES;
        $this->entries = $valid <= OPT_MAX_TREND_ENTRIES ? $valid : OPT_MAX_TREND_ENTRIES;
    }

    /**
     * Verifies if the parameter "interval" is set by the user, if so, verifies that it has a valid value.
     * Then, depending on the interval value, sets the corresponding string to substract the number
     * of entries to determine the first date and the string to add one unit per loop ( plus 1 day, plus 5 minutes...)
     * @throws Exception - In case the value for "interval" is not in the array of available values.
     */
    private function setIntervals() {
        $this->interval = OPT_TREND_DAY;

        if (isset($this->requestParameters['interval'])) {
            $this->interval = $this->requestParameters['interval'];
            if (!in_array($this->interval, $this->available_intervals)) {
                throw new Exception("interval ($this->interval).", 400004);
            }
        }

        switch ($this->interval) {
            case OPT_TREND_5MINUTE:
            case OPT_TREND_MINUTE:
                $val = $this->interval == OPT_TREND_5MINUTE ? 5 : 1;
                $this->datesub = 'PT' . $val * ($this->entries) . 'M';
                $this->dateadd = 'PT' . $val . 'M';
                break;
            case OPT_TREND_HOUR:
                $this->datesub = 'PT' . ($this->entries) . 'H';
                $this->dateadd = 'PT1H';
                break;
            default :
                $this->datesub = 'P' . ($this->entries - 1) . 'D';
                $this->dateadd = 'P1D';
                break;
        }
    }

    /**
     * verifies if the parameter "goalid" is set and if it is a number to set the corresponding value.
     * FALSE is set by default.
     */
    private function setGoalId() {
        $goal = $this->requestParameters['goalid'];
        $this->goalid = is_numeric($goal) ? (int) $goal : FALSE;

        if (!$this->goalid) {
            $query = $this->db->select('collection_goal_id, type')
                    ->from('collection_goals')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('level', 1)
                    ->get();
        } else {
            $query = $this->db->select('type')
                    ->from('collection_goals')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('collection_goal_id', $this->goalid)
                    ->get();
        }

        if ($query->num_rows() > 0) {
            $this->goaltype = $query->row()->type;
            if (!$this->goalid) {
                $this->goalid = $query->row()->collection_goal_id;
            }
        }
    }

    /**
     * Sets the first date point
     */
    private function setCurrentPoint() {
        $last = $this->interval == OPT_TREND_DAY ? explode(' ', $this->end) : array($this->end);
        $first = new DateTime($last[0]);
        $first->sub(new DateInterval($this->datesub));
        $this->currentpoint = $first->format('Y-m-d H:i:s');
    }

    /**
     * @return array - each of the landing_page 's that belongs to the given project (id, name and type)
     */
    private function getDecisions() {
        $this->db->select('landing_pageid, pagetype, name')
                ->from('landing_page')
                ->where('landingpage_collectionid', $this->project);

        if (is_numeric($this->decisiongroup) && $this->decisiongroup > 0) {
            $this->db->where('page_groupid', $this->decisiongroup);
        }

        if ($this->ismpt) {
            $this->db->where('page_groupid', -1);
        }

        $query = $this->db->where('pagetype !=', 3)
                ->order_by('pagetype', 'ASC')
                ->get();

        return $query->result_array();
    }

    /**
     * We need to get all previous impressions and conversions before the first point in the range of 
     * dates provided by the user or before the last 30 days that the test were running.
     * keeping in mind that this has to be after the first_event date (creation_date, restart_date and/or first_date)
     * @param Array $decisions - all the decisions in the project
     * @param Array $goals - with all available goals (either combined or single)
     * @param type $point2 - The MAX date to search in
     * @return Array
     */
    private function getPointStatistics($decisions, $goals, $point1, $point2) {
        $this->load->library('multidb', array(
            'dbname' => $this->dbname,
            'dbserver' => $this->dbserver));
        $CLIENT_DB = $this->multidb->getClientDb();

        $imp = array();
        $cval = array();
        $conv = array();
        $g = array();

        foreach ($decisions as $decision) {
            $imp[$decision['landing_pageid']] = 0;

            foreach ($goals as $goal) {
                $conv[$decision['landing_pageid']] = array(
                    $goal['id'] => 0,
                );
                $cval[$decision['landing_pageid']] = array(
                    $goal['id'] => 0,
                );
            }
        }

        if (!self::isValidDatePoint()) {
            return array('imp' => $imp, 'conv' => $conv, 'cval' => $cval, 'valid' => FALSE);
        }

        $p1 = strtotime($point1);
        $f1 = strtotime($this->firstevent);
        $point1 = $p1 >= $f1 ? $point1 : $this->firstevent;
        $compDate2 = $point2 == $this->lastevent ? 'date <= ' : 'date < ';

        foreach ($goals as $goal) {
            $g[] = $goal['id'];
        }

        $firstP = date('Y-m-d', strtotime($point1));
        $secondP = date('Y-m-d', strtotime($point2));

        if ($point1 == $point2 || $firstP == $secondP) {
            if ($this->dateadd == 'P1D') {
                $point1 = $secondP;
            } else {
                $p1 = strtotime($point1) - 1;
                $point1 = date('Y-m-d H:i:s', $p1);
            }
        }

        foreach ($decisions as $decision) {
            $lpd = $decision['landing_pageid'];

            $dates = array(
                $point1 => 0,
                $point2 => 0
            );

            foreach ($dates as $date => $value) {
                $qImp = $CLIENT_DB->select('impressions')
                        ->from('request_events')
                        ->where('landing_pageid', $lpd)
                        ->where('date >= ', $this->firstevent)
                        ->where($compDate2, $date)
                        ->where('type', OPT_EVENT_IMPRESSION)
                        ->order_by('request_eventsid', 'DESC')
                        ->limit('1')
                        ->get();

                if ($qImp->num_rows() > 0) {
                    $dates[$date] = (int) $qImp->row()->impressions;
                }
            }
            $imp[$lpd] = $dates[$point2] - $dates[$point1];

            foreach ($g as $gid) {
                $qConv = $CLIENT_DB->select('goal_id, conversions, conversion_value_aggregation')
                        ->from('request_events')
                        ->where('landing_pageid', $lpd)
                        ->where('date >= ', $this->firstevent)
                        ->where($compDate2, $point2)
                        ->where('type = ', OPT_EVENT_CONVERSION)
                        ->where('goal_id', $gid)
                        ->order_by('request_eventsid', 'DESC')
                        ->limit('1')
                        ->get();

                if ($qConv->num_rows() > 0) {
                    $row = $qConv->row();
                    $conv[$lpd][$gid] = $row->conversions;
                    $cval[$lpd][$gid] += (float) $row->conversion_value_aggregation;
                }
            }
        }

        $result = array('imp' => $imp, 'conv' => $conv, 'cval' => $cval, 'valid' => TRUE);
        return $result;
    }

    /**
     * First it sets the local variables end, entries and goalid (if any)
     * then gets all decision for the given project in an array
     * stablish the first date based on the "end" date and the number of requested entries.
     * loops throught every entrie (30 as default), and for every decisions gets the number of impressions
     * and conversions for the current loop-date by calling the getRequestEvents method.
     * the sum of impresions and conversions for every decision is stored in the array $imp and $conv
     * and those values are passed as parameters to the getRequestEvents method so it can calculate
     * the aggregated CR
     * 
     * after all, the $trend array will contain the current date (loop) as index and the impressions, conversions
     * and aggregated CR for every decision
     */
    private function getTrend() {
        $this->clientOwnsProject();
        self::getClientDatabaseConfiguration();
        self::setFirstAndLastEvents();
        self::setEntries();
        self::setIntervals();
        self::setEndDate();
        self::setGoalId();
        self::setCurrentPoint();

        $decisions = self::getDecisions();
        $goals = self::getAvailableGoals();
        $prev = self::getPointStatistics($decisions, $goals, $this->firstevent, $this->currentpoint);

        $trend = array();
        $conv = $prev['conv'];
        $imp = $prev['imp'];
        $values = $prev['cval'];

        for ($i = 0; $i < $this->entries; $i++) {
            $auxpoint = array();
            $last = $this->interval == OPT_TREND_DAY ? ($i == ($this->entries - 1)) : FALSE;
            $next = new DateTime($this->currentpoint);
            $next->add(new DateInterval($this->dateadd));
            $nextPoint = ($last) ? $this->end : $next->format('Y-m-d H:i:s');

            $stat = self::getPointStatistics($decisions, $goals, $this->currentpoint, $nextPoint);

            foreach ($decisions as $decision) {
                $name = $decision['pagetype'] == OPT_PAGETYPE_CTRL ? 'control' : $decision['name'];
                $decisionid = $decision['landing_pageid'];

                // $imp, $conv and $values are PASSED BY REFERENCE (Values are updated in the getCombinedStatistics method)
                $combinedstat = self::getCombinedStatistics($decisionid, $goals, $imp, $conv, $values, $stat);

                $auxpoint += array(
                    $name => $combinedstat
                );
            }

            if ($this->goaltype == GOAL_TYPE_COMBINED) {
                $auxpoint = self::getCombinedConversionRates($auxpoint);
            }

            $trend[$this->currentpoint] = $auxpoint;
            $this->currentpoint = $nextPoint;
        }

        return $this->successResponse(200, $trend);
    }

    /**
     * Returns the corresponding statistics given a decision and one or more goals (depending on if it is a comgined goal or a single goal
     * @param Int $decisionid - the control or variant ID
     * @param Array $goals - with all available goals (either combined or single)
     * @param Array $imp - Array with all impressions for all variants *** PARAMETER PASSED BY REFERENCE ***
     * @param Array $conv - Array with all conversions for all variants *** PARAMETER PASSED BY REFERENCE ***
     * @param Array $values - Array with all conversion values for ToP goals per variant *** PARAMETER PASSED BY REFERENCE ***
     * @param Array $stat - Array with all the statistics for the current time interval per variant per goal
     * @return Array
     */
    private function getCombinedStatistics($decisionid, $goals, &$imp, &$conv, &$values, $stat) {
        $auxConv = 0;
        $auxVal = 0;
        $auxCr = 0;

        $imp[$decisionid] += $stat['imp'][$decisionid];

        $combinedstat = array(
            'impressions' => $stat['imp'][$decisionid],
        );

        foreach ($goals as $ind => $goal) {
            $c = $stat['conv'][$decisionid][$goal['id']];
            $v = $stat['cval'][$decisionid][$goal['id']];

            $conv[$decisionid][$goal['id']] = $c;
            $values[$decisionid][$goal['id']] = $v;

            if (!$stat['valid']) {
                $cr = NULL;
            } else {
                switch ($goal['type']) {
                    case GOAL_TYPE_TIMEONPAGE:
                        $cr = $conv[$decisionid][$goal['id']] == 0 ? 0 : (double) $values[$decisionid][$goal['id']] / (double) $conv[$decisionid][$goal['id']];
                        break;
                    case GOAL_TYPE_PI_LIFT:
                        $cr = $conv[$decisionid][$goal['id']] == 0 ? 0 : (double) $values[$decisionid][$goal['id']] / (double) $conv[$decisionid][$goal['id']];
                        break;
                    default :
                        $cr = $imp[$decisionid] == 0 ? 0 : (double) $conv[$decisionid][$goal['id']] / (double) $imp[$decisionid];
                        break;
                }
                $formattedCr = sprintf("%.4f", $cr);
            }

            if ($this->goaltype == GOAL_TYPE_COMBINED) {
                $combinedstat['values'][$goal['id']] = $v;
                $combinedstat['conversions'][$goal['id']] = $c;
                $combinedstat['aggregatedcr'][$goal['id']] = $formattedCr;
                $combinedstat['pageid'] = $decisionid;
                $combinedstat['goals'][$goal['id']] = array(
                    'type' => $goal['type'],
                    'goalid' => $goal['id'],
                    'conversions' => $c,
                    'aggregatedcr' => $formattedCr,
                );
            } else {
                $auxConv += $c;
                $auxVal += $v;
                $auxCr += $cr;
            }
        }

        if ($this->goaltype != GOAL_TYPE_COMBINED) {
            $combinedstat['values'] = $auxVal;
            $combinedstat['conversions'] = $auxConv;
            $combinedstat['aggregatedcr'] = !$stat['valid'] ? NULL : $auxCr;
        }
        return $combinedstat;
    }

    /**
     * Returns either the current goal id and type or the available "combined goals" in case
     * the current goaltype is "COMBINED"
     * @return Array - containins the id and type of the available goal (s)
     */
    private function getAvailableGoals() {
        $goals = array();

        if (is_numeric($this->goalid) && $this->goalid > 0) {
            $goals[0] = array(
                'id' => $this->goalid,
                'type' => $this->goaltype,
            );
        }

        if ($this->goaltype == GOAL_TYPE_COMBINED) {
            $query = $this->db->select('collection_goal_id, type')
                    ->from('collection_goals')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('type != ', GOAL_TYPE_COMBINED)
                    ->get();

            if ($query->num_rows() > 0) {
                $goals = array();
                foreach ($query->result() as $q) {
                    $goals[] = array(
                        'id' => $q->collection_goal_id,
                        'type' => $q->type,
                    );
                }
            }
        }

        return $goals;
    }

    /**
     * verifies if the current evaluated date is >= than the first event (restart_date or first impression)
     * @return BOOL
     */
    private function isValidDatePoint() {
        $d = strtotime($this->currentpoint);
        $f = new DateTime($this->firstevent);
        $f->sub(new DateInterval($this->dateadd));
        $l = new DateTime($this->lastevent);

        $first = strtotime($f->format('Y-m-d H:i:s'));
        $last = strtotime($l->format('Y-m-d H:i:s'));

        if ($d < $first || $d > $last) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * If the goal type of the request is "COMBINED", we need to perform a custom calculation for the conversion rate
     * It calls the method $this->optimisation->deriveCombinedGoalConversions() to get the CR for the combined goal
     * @param Array $auxpoint 
     * @return array
     * @throws Exception
     */
    private function getCombinedConversionRates($auxpoint) {
        $transformationRule = $this->config->item('COMBINED_GOAL_COMBINATION_RULE');
        $formatted = array();
        $resultset = array();

        // check if all CRs is null - in which case we do not need to calculate combined CR
        $hasOnlyNullCRs = true;

        foreach ($auxpoint as $key => $value) {
            $auxConv = 0;
            $goals = array();

            foreach ($value['goals'] as $g) {
                $auxConv += $value['conversions'][$g['goalid']];
                $goals[$g['goalid']] = array(
                    'goalid' => $g['goalid'],
                    'type' => $g['type'],
                    'standard_deviation' => 0,
                    'cr' => $g['aggregatedcr'],
                );
                if ($g['aggregatedcr'] !== null) {
                    $hasOnlyNullCRs = false;
                }
            }

            $goals[$this->goalid] = array(
                'goalid' => $this->goalid,
                'type' => GOAL_TYPE_COMBINED,
            );

            $resultset[$value['pageid']] = array(
                'landing_pageid' => $value['pageid'],
                'imressions' => $value['impressions'],
                'conversions' => $auxConv,
                'goals' => $goals,
            );
        }

        if ($hasOnlyNullCRs) {
            $stats = $resultset;
        } else {
            $stats = $this->optimisation->deriveCombinedGoalConversions($resultset, $transformationRule);
        }

        foreach ($stats as $key => $value) {
            foreach ($auxpoint as $page => $content) {
                if ($key == $content['pageid']) {
                    $formatted[$page] = array(
                        'aggregatedcr' => $hasOnlyNullCRs ? null : $stats[$key]['goals'][$this->goalid]['cr'],
                        'impressions' => $content['impressions'],
                        'conversions' => $hasOnlyNullCRs ? null : $stats[$key]['conversions'],
                        'values' => $content['values'],
                    );
                }
            }
        }

        return $formatted;
    }

    /**
     * Returns configuration for the client specific database 
     * @return array
     */
    private function getClientDatabaseConfiguration() {
        $query = $this->db->select('config')
                ->from('client')
                ->where('clientid', $this->account)
                ->get();
        if ($query->num_rows() <= 0) {
            return false;
        }
        $configuration = json_decode($query->row()->config, true);
        if (is_array($configuration)) {
            $dbname = $configuration['CLIENT_DB_NAME'];
            if (isset($dbname)) {
                $dbserver = $configuration['CLIENT_DB_SERVER'];
                if (isset($dbserver)) {
                    $this->dbname = $dbname;
                    $this->dbserver = $dbserver;
                    return true;
                }
            }
        }
        return false;
    }

}
