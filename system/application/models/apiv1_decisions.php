<?php

/**
 * This class inherits from the apiv1_core class which have some common variables and methods
 * for all of its childs
 */
require_once 'apiv1_core.php';

final class apiv1_decisions extends apiv1_core {

    protected $project;
    protected $rule;
    protected $account;
    protected $decision;
    protected $goal_type;
    protected $is_timeonpage = FALSE;
    protected $is_pi_lift = FALSE;
    protected $decisiongroup = -1;
    // To get the corresponding "pagetype" value for a variant given its value
    protected $decision_type = array(
        1 => 'CONTROL',
        2 => 'VARIANT',
    );
    // To get the appropriate DB field given the name that the client sends.
    protected $dbfields = array(
        'id' => 'lp.landing_pageid',
        'url' => 'lp.lp_url',
        'name' => 'lp.name',
        'conversionrate' => 'lp.cr',
        'ruleid' => 'lp.rule_id',
    );
    // Field that are mandatory when creating or editing decisions
    protected $mandatoryfields = array(
        'name',
    );
    // When creating or editing decisions, users can't set the next fields because they are read-only
    protected $readonlyfields = array(
        'id',
        'previewurl',
        'type',
        'result',
        'visitors',
        'conversions',
        'conversionrate',
        'confidence',
        'distribution',
    );

    function __construct() {
        parent::__construct();
        $this->load->helper('apiv1');
        $this->config->load('config');
        $this->load->library('calculation');
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

    /**
     * See: apiv1_core->setParametersReturnMethod()
     * it verifies that the requested method exists and returns its name or throws an exception
     * Then, verifies if the given project is a TEASERTEST, if so and no decisiongroup ID is set:
     *      if the requested method is "getDecisions" returns success with an empty response (array)
     *      If any other method is requested, throws an exception.
     */
    public function index($uri) {
        $method = $this->setParametersReturnMethod($uri);
        $issetDg = is_numeric($this->decisiongroup) && $this->decisiongroup > 0;

        if (self::projectIsTeaserTest() && !$issetDg) {
            switch ($method) {
                case 'getDecisions':
                    return $this->successResponse(200, array());
                default:
                    throw new Exception('decisiongroup (empty) -- This decision is part of a decision group', 400004);
            }
        }

        return self::$method();
    }

    /**
     * returns TRUE if the given project is of type TEASERTEST, or FALSE otherwise
     * @return boolean
     */
    private function projectIsTeaserTest() {
        $query = $this->db->select('testtype')
                ->from('landingpage_collection')
                ->where('landingpage_collectionid', $this->project)
                ->get();

        if ($query->row()->testtype == OPT_TESTTYPE_TEASER) {
            return TRUE;
        }

        if ($query->row()->testtype != OPT_TESTTYPE_MULTIPAGE) {
            $this->decisiongroup = -1;
        }
        return FALSE;
    }

    /*     * ******************************************************************************
     * ***************************** (SINGULAR) GET DECISION ******************************
     * ********************************************************************************* */

    /**
     * As the getDecisions (plural) method code is almost the same for this method, thisone just calls it with
     * the landing_page ID as parameter
     * @return JSON
     */
    private function getDecision() {
        if (is_numeric($this->decision) && $this->decision * 1 > 0) {
            return self::getDecisions($this->decision);
        } else {
            throw new Exception('decision (invalid ID)', 400004);
        }
    }

    /*     * ******************************************************************************
     * ***************** GET DECISIONS RELATED METHODS - Project Details ********************
     * ********************************************************************************* */

    /**
     * If this method is called from "getDecision()", returns only the data for the given variant (lpd)
     * or by default returns all decisions (original and variants) for a given project
     * filtering by result is an special case, if it is set in the requested parameters first we need to 
     * veryfy that the returned $result is equal to that parameter.
     * @param int $lpd (optional) the landing_page id -- when it is called from getDecision()
     * @return Array
     */
    private function getDecisions($lpd = FALSE) {
        $this->clientOwnsProject();
        $goalid = array_key_exists('goalid', $this->requestParameters) ? $this->requestParameters['goalid'] : FALSE;
        $isPrimaryGoal = self::isPrimaryGoal($goalid);


        $select = 'lp.landing_pageid, lp.lp_index, lp.name, lp.pagetype, lp.lp_url, lp.canonical_url, ctrl.lp_url AS ctrl_url, ' .
                ' lp.z_score, lp.is_maximum, lp.impressions, lp.dom_modification_code, lp.rule_id, lp.page_groupid, lp.allocation, ' .
                ' lpc.testtype, lpc.testtype, lpc.progress, lpc.personalization_mode, lpc.smartmessage';

        if ($isPrimaryGoal) {
            $select .= ', lp.conversions, lp.conversion_value_aggregation, lp.standard_deviation, ';
            if ($this->is_timeonpage) {
                $select .= ' CAST((lp.conversion_value_aggregation / lp.conversions) AS DECIMAL(30, 6)) AS cr ';
            } elseif ($this->is_pi_lift) {
                $select .= ' CAST((lp.conversion_value_aggregation / lp.conversions) AS DECIMAL(30, 6)) AS cr ';
            } else {
                $select .= ' CAST(lp.cr AS DECIMAL(8,6)) AS cr ';
            }
        } else {
            $select .= ', cg.conversions, cg.conversion_value_aggregation, cg.standard_deviation, ';
            if ($this->is_timeonpage) {
                $select .= ' CAST((cg.conversion_value_aggregation / cg.conversions) AS DECIMAL(30, 6)) AS cr ';
            } elseif ($this->is_pi_lift) {
                $select .= ' CAST((cg.conversion_value_aggregation / cg.conversions) AS DECIMAL(30, 6)) AS cr ';
            } else {
                $select .= ' CAST(cg.conversions / lp.impressions AS DECIMAL(8,6)) AS cr ';
            }
        }

        $ctrl = '(SELECT landingpage_collectionid AS lpcid, lp_url '
                . ' FROM landing_page '
                . ' WHERE pagetype = 1 AND landingpage_collectionid = ' . $this->project . ') ctrl';

        $cg = '(SELECT landing_pageid, goal_id, conversions, conversion_value_aggregation , standard_deviation '
                . ' FROM collection_goal_conversions '
                . ' WHERE landingpage_collectionid = ' . $this->project . ') cg';

        $this->db->select($select, FALSE)
                ->from('landing_page lp')
                ->join('landingpage_collection lpc', 'lpc.landingpage_collectionid = lp.landingpage_collectionid', 'INNER')
                ->join($ctrl, 'ctrl.lpcid = lp.landingpage_collectionid', 'INNER')
                ->where('lp.landingpage_collectionid', $this->project)
                ->where('lp.pagetype !=', 3)
                ->where('lp.page_groupid', $this->decisiongroup);

        if (!$isPrimaryGoal) {
            $this->db->join($cg, 'cg.landing_pageid = lp.landing_pageid', 'INNER');
            $this->db->where('cg.goal_id', $goalid);
        }

        self::addDecisionParameters();

        $query = $this->db->group_by('lp.landing_pageid')
                ->order_by('pagetype', 'ASC')
                ->order_by('landing_pageid', 'ASC')
                ->get();

        $pages = $isPrimaryGoal ? $query->result() : self::getResultsForSecondaryGoal($query->result());
        $decisions = array();
        foreach ($pages as $q) {
            if (!$lpd || $q->landing_pageid == $lpd) {
                $result = self::decisionResult($q);
                $addFields = !array_key_exists('result', $this->requestParameters) || $this->requestParameters['result'] == $result;

                if ($addFields) {
                    $decisions[] = self::allDecisionFields($q, $result);
                }
            }
        }

        $ret = $lpd ? $decisions[0] : $decisions;
        return $this->successResponse(200, $ret);
    }

    /**
     * When a user passes a goal id as parameter to filter results, this method tells whether
     * that goal is a primary one or not.
     * @param Int $goalid
     * @return boolean
     */
    private function isPrimaryGoal($goalid) {
        $ret = TRUE;
        $gid = is_numeric($goalid) && $goalid > 0;
        if ($gid) {
            $query = $this->db->select('level, type')
                    ->from('collection_goals')
                    ->where('collection_goal_id', $goalid)
                    ->where('landingpage_collectionid', $this->project)
                    ->get();
        } else {
            $query = $this->db->select('type')
                    ->from('collection_goals')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('level', 1)
                    ->get();
        }

        if ($query->num_rows() > 0) {
            $this->goal_type = $query->row()->type;
            $this->is_timeonpage = $query->row()->type == GOAL_TYPE_TIMEONPAGE;
            $this->is_pi_lift = $query->row()->type == GOAL_TYPE_PI_LIFT;
            if ($gid) {
                $ret = $query->row()->level == 1;
            }
        }
        return $ret;
    }

    /**
     * When getting Decisions, they can be filtered or ordered by some of the field names in the LP table
     */
    private function addDecisionParameters() {
        foreach ($this->requestParameters as $key => $value) {
            if ($key == 'sort') {
                $ascdsc = $value[0] == '-' ? 'DESC' : 'ASC';
                $key = $value[0] == '-' ? substr($value, 1) : $value;
                $ord = array_key_exists($key, $this->dbfields) ? $this->dbfields[$key] : 'lp.landing_pageid';
                $this->db->order_by($ord, $ascdsc);
            } else if (array_key_exists($key, $this->dbfields)) {
                $k = $this->dbfields[$key];
                $this->db->where($k, $value);
            }
        }
    }

    /**
     * ***** This method is called only if the requested result is filtered by a "secondary" goal.*******
     * Creates an array correctly formatted for the optimisation method "deriveResultForPates" which
     * will return the corresponding  values for z_score and is_maximum.
     * This values are replaced in the original query results and returned.
     * @param Object [MySQL query result] $queryresult - containing all data for every desicion
     * @return Object - the same parameter with z_score and is_maximum modified.
     */
    private function getResultsForSecondaryGoal($queryresult) {
        $old_pages = array();
        foreach ($queryresult as $res) {
            $row = array(
                'landing_pageid' => $res->landing_pageid,
                'pagetype' => $res->pagetype,
                'impressions' => $res->impressions,
                'conversions' => $res->conversions,
                'cr' => $res->cr,
                'aggregated_value' => $res->conversion_value_aggregation,
                'standard_deviation' => $res->standard_deviation,
                'goaltype' => $this->goal_type,
                'is_maximum' => '0',
            );
            $old_pages[] = $row;
        }

        $page_result = $this->optimisation->deriveResultForPages($old_pages);
        $new_pages = $page_result['new_pages'];

        foreach ($queryresult as &$res) {
            foreach ($new_pages as $key => $value) {
                if ($value['landing_pageid'] == $res->landing_pageid) {
                    $res->z_score = $value['z_score'];
                    $res->is_maximum = isset($value['is_maximum']) ? $value['is_maximum'] : 0;
                }
            }
        }
        return $queryresult;
    }

    /**
     * Called for every decision in the result set, returns the corresponding string value for the decision
     * "result" field
     * @param Array $q - the current row in the decision result set
     * @return string
     */
    private function decisionResult($q) {
        $result = 'LOST';
        $validType = in_array($q->testtype, array(OPT_TESTTYPE_SPLIT, OPT_TESTTYPE_VISUALAB,
            OPT_TESTTYPE_TEASER, OPT_TESTTYPE_MULTIPAGE));
        if (!$validType || $q->personalization_mode == OPT_PERSOMODE_SINGLE || $q->smartmessage == OPT_IS_SMS_TEST) {
            $result = 'NA';
        } else if ($q->is_maximum == 0) {
            $result = 'NONE';
        } else if ($q->is_maximum == 1) {
            $result = 'WON';
        }
        return $result;
    }

    /**
     * Returns all decision fields when going through every query result in the previous method
     * @param Array $q
     * @param String $result
     * @return Array
     */
    private function allDecisionFields($q, $result) {
        $modificationcode = json_decode($q->dom_modification_code, TRUE);

        $preview = $q->ctrl_url;
        $preview .= stripos($q->ctrl_url, '?') > 0 ? '&_p=t' : '?_p=t';
        $preview .= $q->pagetype == OPT_PAGETYPE_VRNT ? '&BT_lpid=' . $q->landing_pageid : '';

        return array(
            'id' => $q->landing_pageid,
            'variantindex' => $q->lp_index,
            'name' => $q->name,
            'url' => $q->lp_url,
            'previewurl' => $preview,
            'type' => $this->decision_type[$q->pagetype],
            'result' => $result,
            'visitors' => $q->impressions,
            'conversions' => $q->conversions,
            'conversionrate' => $q->cr,
            'confidence' => Calculation::getConfidence($q->z_score),
            'distribution' => 0,
            'jsinjection' => $modificationcode['[JS]'] ? $modificationcode['[JS]'] : '',
            'cssinjection' => $modificationcode['[CSS]'] ? $modificationcode['[CSS]'] : '',
            'ruleid' => $q->rule_id,
            'allocation' => $q->allocation,
        );
    }

    /*     * ******************************************************************************
     * **************** COMMON METHODS FOR POST AND PUT DECISION ***********************
     * ********************************************************************************* */

    /**
     * First, it gets the testtype and mainurl from the "getTestData()" method, 
     * 
     * then if it is set "ruleid" in the request object, it calls the parent class method "clientOwnsrule" 
     * to check if the rule exists and it belongs to the current client or one of the current tenant's clients.
     * 
     * then builds an array with the corresponding variant depending on the testtype,
     * the array contains the name, the lp_url, the rule_id (if any) and 
     * either the canonical_url for SPLIT tests, or the dom_modification_code for VISUAL tests
     * @return Either an Array with the variant data or FALSE in case of errors
     * @throws Exception - if the testtype is not valid
     */
    private function buildVariant($action) {
        $post_array = json_decode($this->requestParameters);
        $testdata = self::getTestData();

        $variant = array(
            'page_groupid' => $this->decisiongroup,
        );

        if (!$this->decision || $this->decision == NULL) {
            $variant['lp_index'] = self::getVariantIndex();
        }

        if (array_key_exists('ruleid', $post_array)) {
            $this->rule = $post_array->ruleid;
            $variant['rule_id'] = $this->rule;
            $this->rule > 0 ? $this->clientOwnsRule() : '';
        }

        if (array_key_exists('name', $post_array)) {
            $variant['name'] = strip_tags($post_array->name);
        }

        if (array_key_exists('allocation', $post_array)) {
            $variant['allocation'] = $post_array->allocation;
        }

        switch ($testdata['testtype']) {
            case OPT_TESTTYPE_SPLIT:
                if ($action == 'POST' || isset($post_array->url)) {
                    $variant['lp_url'] = $post_array->url;
                    $variant['canonical_url'] = $post_array->url;
                }
                break;
            case OPT_TESTTYPE_VISUALAB:
            case OPT_TESTTYPE_MULTIPAGE:
                $domcode = array();
                $js = isset($post_array->jsinjection) ? $post_array->jsinjection : FALSE;
                $css = isset($post_array->cssinjection) ? $post_array->cssinjection : FALSE;
                $smscode = self::getSmsCode();

                if (count($smscode) > 0 && $smscode['sms'] && $smscode['smshtml']) {
                    $domcode['[SMS]'] = $smscode['sms'];
                    $domcode['[SMS_HTML]'] = $smscode['smshtml'];
                }
                if ($js && !is_null($js)) {
                    $domcode['[JS]'] = $js;
                }
                if ($css && !is_null($css)) {
                    $domcode['[CSS]'] = $css;
                }

                if ($testdata['testtype'] == OPT_TESTTYPE_MULTIPAGE) {
                    $variant['lp_url'] = '';
                    $domcode = $this->decisiongroup == -1 ? array() : $domcode;
                } else {
                    $variant['lp_url'] = $testdata['mainurl'];
                }

                if ($action == 'POST' || count($domcode) > 0) {
                    $variant['dom_modification_code'] = count($domcode) > 0 ? json_encode($domcode) : NULL;
                }
                break;
            case OPT_TESTTYPE_TEASER:
                $domcode = array(
                    '[TT_HL]' => $post_array->name,
                );

                $js = isset($post_array->jsinjection) ? $post_array->jsinjection : FALSE;
                $css = isset($post_array->cssinjection) ? $post_array->cssinjection : FALSE;

                if ($js && !is_null($js)) {
                    $domcode['[JS]'] = $js;
                }
                if ($css && !is_null($css)) {
                    $domcode['[CSS]'] = $css;
                }

                if ($action == 'POST' || count($domcode) > 0) {
                    $variant['dom_modification_code'] = count($domcode) > 0 ? json_encode($domcode) : NULL;
                }
                break;
            default :
                throw new Exception('Invalid test type', 500);
        }

        return $variant;
    }

    /**
     * returns the testtype and mainurl (lp_url of the control) for the given project (LPC ID)
     * @return Either an array (with the testtype and mainurl) or FALSE
     * @throws Exception - if there aren't any results from the query
     */
    private function getTestData() {
        $query = $this->db->select('lpc.testtype, lp.lp_url')
                ->from('landingpage_collection lpc')
                ->join('landing_page lp', 'lp.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                ->where('lpc.landingpage_collectionid', $this->project)
                ->where('lp.pagetype', OPT_PAGETYPE_CTRL)
                ->get();

        if ($query->num_rows() <= 0) {
            throw new Exception('No data was found for the given project', 500);
        }

        return array(
            'testtype' => $query->row()->testtype,
            'mainurl' => $query->row()->lp_url,
        );
    }

    /**
     * checks if there is an [SMS] entry in the dom_modification_code for the current variant, if so
     * returns its value.
     */
    private function getSmsCode() {
        if (is_numeric($this->decision) && $this->decision > 0) {
            $query = $this->db->select('dom_modification_code')
                    ->from('landing_page')
                    ->where('landing_pageid', $this->decision)
                    ->get();

            $res = json_decode($query->row()->dom_modification_code);
            $sms = isset($res->{"[SMS]"}) ? $res->{"[SMS]"} : FALSE;
            $smshtml = isset($res->{"[SMS_HTML]"}) ? $res->{"[SMS_HTML]"} : FALSE;
            return array('sms' => $sms, 'smshtml' => $smshtml);
        }
        return array();
    }

    /**
     * Every variant in a group/project has an index (1, 2, 3...)
     * Now, before inserting a variant, we have to get the index for it
     * @return int - the latest index + 1
     */
    private function getVariantIndex() {
        $query = $this->db->select('MAX(lp_index) AS lp_index')
                ->from('landing_page')
                ->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->get();

        if ($query->num_rows() > 0) {
            return $query->row()->lp_index += 1;
        }
        return 1;
    }

    /*     * ******************************************************************************
     * ******************** POST DECISION RELATED METHODS *******************************
     * ********************************************************************************* */

    /**
     * Verifies the project ID, and the client credentials first
     * then calls the "buildVariant()" method to create an array with the corresponding variants data.
     * if the method returns an array (not FALSE), adds the LPC id and the pagetype to the variant array
     *      and inserts it into the DB.
     * @return JSON
     * @throws Exception - in case there is a problem insertint the new variant into the DB
     */
    private function postDecision() {
        $this->clientOwnsProject();
        self::verifyDecisionCount();
        $this->checkMandatoryFields(FALSE, 400300);
        $this->checkReadOnlyFields(FALSE, 400301);
        $decision = self::buildVariant('POST');
        $decision['landingpage_collectionid'] = $this->project;
        $decision['pagetype'] = OPT_PAGETYPE_VRNT;

        $this->db->insert('landing_page', $decision);
        $lpd = $this->db->insert_id();

        if (!is_int($lpd) || $lpd <= 0) {
            throw new Exception('Decision could not be created', 500);
        } else {
            $this->decision = $lpd;
            self::updateProgressWhenPostDecision();
        }

        $this->syncCollectionGoals();
        $this->optimisation->flushCollectionCache($this->project);
        $this->optimisation->flushKpiResultsForCollection($this->project);
        $this->optimisation->evaluateImpact($this->project, $this->decisiongroup);
        $this->optimisation->updateslots(OPT_SLOTS_EQUIDIST, $this->project, -1, $this->decisiongroup);
        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(200, $lpd);
    }

    /**
     * verifies that the new decision posted does not exceed the maximum number of allowed decisions per
     * project for the given client
     * @return boolean
     * @throws Exception - in case the pre-condition is not met.
     */
    private function verifyDecisionCount() {
        $feature_array = $this->getClientFeatures();
        if (is_null($feature_array) || trim((string) $feature_array) == '') {
            return TRUE;
        }

        $features = json_decode($feature_array);
        if (json_last_error() != JSON_ERROR_NONE) {
            return TRUE;
        }

        if (isset($features->numdecisions) && is_numeric($features->numdecisions)) {
            $cnt = self::getDecisionCount();
            if ($cnt >= $features->numdecisions) {
                throw new Exception("This project already has the maximum number of decisions allowed ($features->numdecisions)", 403006);
            }
        }
    }

    /**
     * @return Int - the number of decisions for the given project that the logged client has.
     * @throws Exception - If no resultsets has been returned when executing the query
     */
    private function getDecisionCount() {
        $query = $this->db->select('count(*) AS cnt')
                ->from('landing_page lp')
                ->join('landingpage_collection lpc', 'lp.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                ->join('client c', 'c.clientid = lpc.clientid', 'INNER')
                ->where('lp.pagetype', OPT_PAGETYPE_VRNT)
                ->where('lpc.landingpage_collectionid', $this->project)
                ->where('c.clientid', $this->account)
                ->get();

        if ($query->num_rows() <= 0) {
            throw new Exception('', 403203);
        }
        return $query->row()->cnt;
    }

    /**
     * When a new decision is added to aproject/group, we need to reset the progress of the correspondin
     * group/project to "not significant"
     */
    private function updateProgressWhenPostDecision() {
        if ($this->decisiongroup != -1) {
            $this->db->where("page_groupid", $this->decisiongroup)
                    ->set('progress', OPT_PROGRESS_NSIG)
                    ->update('page_group');
        } else {
            $this->db->where("landingpage_collectionid", $this->project)
                    ->set('progress', OPT_PROGRESS_NSIG)
                    ->update('landingpage_collection');
        }
    }

    /*     * ******************************************************************************
     * ******************** PUT DECISION RELATED METHODS *******************************
     * ********************************************************************************* */

    /**
     * calls the "buildVariant()" method to create the decision array
     * if the method returns the array (not FALSE), updates the corresponding row in the DB
     * @return JSON
     */
    private function putDecision() {
        $this->clientOwnsProject();

        $this->checkReadOnlyFields(FALSE, 400301);
        $decision = self::buildVariant('PUT');

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('landing_pageid', $this->decision)
                ->update('landing_page', $decision);

        $this->syncCollectionGoals();

        $this->optimisation->flushKpiResultsForCollection($this->project);
        $this->optimisation->flushCollectionCache($this->project);
        $this->optimisation->evaluateImpact($this->project, $this->decisiongroup);
        return $this->successResponse(200);
    }

    /*     * ******************************************************************************
     * *********************** DELETE DECISIONS RELATED METHODS ***********************
     * ********************************************************************************* */

    /**
     * Verifies the user credentials and that the project belongs to the current logged client
     * then deletes the decision given its ID passed in the URL ( /project/<id>/decision/<id> )
     * @return type
     */
    private function deleteDecision() {
        $this->clientOwnsProject();

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('landing_pageid', $this->decision)
                ->delete('landing_page');

        $this->syncCollectionGoals();

        $this->optimisation->flushCollectionCache($this->project);
        $this->optimisation->updateslots(OPT_SLOTS_EQUIDIST, $this->project, -1, $this->decisiongroup);
        $this->optimisation->evaluateImpact($this->project, $this->decisiongroup);
        return $this->successResponse(200);
    }

}
