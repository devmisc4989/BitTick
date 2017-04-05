<?php

/**
 * This class inherits from the apiv1_core class which have some common variables and methods
 * for all of its childs
 */
require_once 'apiv1_core.php';

class apiv1_decisiongroups extends apiv1_core {

    protected $project;
    protected $account;
    private $decisiongroup;
    // To get a "status" value given its name
    protected $status_array = array(
        0 => 'UNSET',
        1 => 'PAUSED',
        2 => 'RUNNING',
    );
    // Gets the "result" value given its name
    protected $result_array = array(
        1 => 'NONE',
        2 => 'LOST',
        3 => 'WON',
    );
    // To get the appropriate DB field given the name that the client sends.
    protected $dbfields = array(
        'id' => 'pg.page_groupid',
        'name' => 'pg.name',
        'status' => 'pg.status',
        'createddate' => 'pg.creation_date',
        'restartdate' => 'pg.restart_date',
        'mainurl' => 'lp.lp_url',
        'runpattern' => 'lp.canonical_url',
        'visitors' => 'stat.impressions',
        'conversions' => 'stat.conversions',
        'conversionrate' => 'stat.cr',
        'result' => 'pg.progress',
    );
    // Field that are mandatory when creating or editing groups
    protected $mandatoryfields = array(
        'name',
    );
    // When creating or editing groups, users can't set the next fields because they are read-only
    protected $readonlyfields = array(
        'id',
        'createddate',
        'restartdate',
        'status',
        'visitors',
        'conversions',
        'conversionrate',
        'result',
        'originalid',
        'winnerid',
        'winnername',
        'uplift',
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
     * Verifies the entire requested URL, sets the corresponding values (account id, project id, decisiongroup id...)  
     * and returns the method name (getDecisionGroups, postDecisionGroup...) which is the first element 
     * of the last URL pair
     * 
     * example:
     * GET BASEURL/account/4511/project/7445/decisiongroup/11 
     *  - sets $this->account = 4511
     *  - sets $this->project =7445
     *  - sets $this->decisiongroup =11
     *  - returns "getDecisiongroup" as the method name
     * 
     * @param array $uri
     * @return string - the method name
     * @throws Exception
     */
    protected function setValuesReturnMethod($uri) {
        $meth = '';
        $actions = array('start', 'stop', 'restart');

        foreach ($uri as $key => $value) {
            if (in_array($key, $actions)) {
                $meth = ucfirst($key) . 'Decisiongroup';
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
     * ************************ (SINGULAR) GET DECISION GROUP ***************************
     * ********************************************************************************* */

    /**
     * Singular "getDecisionGroup()"
     * given a decision group id, returns all related data after calling the method "getDecisionGroups" 
     * with the group ID as parameter
     * @return JSON
     */
    private function getDecisiongroup() {
        $groupid = is_numeric($this->decisiongroup) ? $this->decisiongroup : 0;
        return self::getDecisionGroups($groupid);
    }

    /*     * ******************************************************************************
     * ***************** (PLURAL) GET DECISION GROUPS RELATED METHODS ********************
     * ********************************************************************************* */

    /**
     * builds the query and complements it by calling addParametersToQuery() - to add the custom filters
     *      and sort order stablished by the client in the url (for example: sort=id&type=VISUAL)
     * Then returns the results depending on if the user set custom fields or not (returns all of them)
     *      this is evaluated in customGroupFields()
     * 
     * @return JSON
     */
    private function getDecisiongroups($pgid = FALSE) {
        $this->db->select('pg.page_groupid, pg.name, pg.status, pg.progress, pg.creation_date, pg.restart_date, pg.last_sample_date, ' .
                ' pg.sample_time, lp.originalid, lp.mainurl, lp.runpattern, stat.impressions, stat.conversions, stat.cr ');

        $lp = "(SELECT page_groupid AS pgid, landing_pageid AS originalid, lp_url AS mainurl, canonical_url AS runpattern FROM landing_page  " .
                " WHERE landingpage_collectionid = $this->project AND pagetype = 1 GROUP BY page_groupid) lp ";

        $stat = "(SELECT page_groupid, SUM(impressions) AS impressions, SUM(conversions) AS conversions, " .
                " CAST(AVG(cr) AS DECIMAL(8,6)) AS cr FROM landing_page WHERE landingpage_collectionid = $this->project " .
                " GROUP BY page_groupid) stat ";

        $this->db->from('page_group pg')
                ->join('landingpage_collection lpc', 'lpc.landingpage_collectionid = pg.landingpage_collectionid', 'INNER')
                ->join($lp, 'lp.pgid = pg.page_groupid', 'INNER')
                ->join($stat, 'stat.page_groupid = pg.page_groupid', 'INNER');

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = lpc.clientid', 'INNER')
                    ->where('lpc.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('lpc.clientid', $this->clientid);
        }

        if (!$pgid) {
            parent::addParametersToQuery();
        } else {
            $this->db->where('pg.page_groupid', $pgid);
        }

        $query = $this->db->group_by('pg.page_groupid')
                ->get();

        if ($query->num_rows() <= 0) {
            throw new Exception('', 404001);
        }

        $res = array();
        foreach ($query->result() as $q) {
            $r = self::returnGroupFields($q, FALSE);
            $res[] = $r;
        }

        $r = $pgid ? $res[0] : $res;
        return $this->successResponse(200, $r);
    }

    /**
     * As the user can select custom fields to be returned, this method calls the corresponding method
     * to do so, if there are customParameters calls "customGroupFields()" or else "allGroupFIelds()"
     * @param Array $q - The current row for the result array
     * @return Array
     */
    private function returnGroupFields($q) {
        $id = $q->page_groupid;
        $statistics = self::getGroupStatistics($id);
        $winner = self::getGroupWinner($id, $q);

        $result = ($q->progress == 2) ? $winner['winner_cr'] : 'NONE';
        if ($winner['winner_cr'] != 'NA' && $q->progress == 2) {
            $result = $winner['winner_cr'] == 0 ? 'LOST' : 'WON';
        }

        if ($q->progress == 2) {
            $cr = $winner['control_cr'] ? $winner['control_cr'] : $winner['winner_cr'];
        } else {
            $cr = $statistics['conversionrate'];
        }

        return array(
            'id' => $q->page_groupid,
            'name' => $q->name,
            'mainurl' => $q->mainurl,
            'runpattern' => $q->runpattern,
            'createddate' => $q->creation_date,
            'status' => $this->status_array[$q->status],
            'visitors' => $statistics['visitors'],
            'conversions' => $statistics['conversions'],
            'conversionrate' => $cr,
            'result' => $result,
            'originalid' => $q->originalid,
            'winnerid' => $winner['winnerid'],
            'winnername' => $winner['winnername'],
            'uplift' => $winner['uplift'],
        );
    }

    /**
     * Given a page group id, returns the sum of impressions, the sum of conversions
     * and the average of conversionrates
     * @param Int $pgid
     * @return Array
     */
    private function getGroupStatistics($pgid) {
        $query = $this->db->select('SUM(impressions) AS visitors, SUM(conversions) AS conversions', FALSE)
                ->from('landing_page')
                ->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $pgid)
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
     * Verifies if the group is significant continues.
     * 
     * Gets the Conversion Rate, the id, the name and the pagetype for the winner variant and for the control
     * and returns the uplift for the group, the name and the ID of the winner variant.
     * 
     * @param Int $pgid - the current group id (being evaluated in the foreach loop)
     * @param Array $row - the current result "row" being evaluated
     * @return Array - with the id, name and uplift if there is a winner variant
     */
    private function getGroupWinner($pgid, $row) {
        $progress = $row->progress;

        if ($progress != '2') {
            return array('winnerid' => -1, 'winnername' => 'NA', 'winner_cr' => -1, 'uplift' => -1);
        }

        $max = self::getHigherGroupCr($pgid);
        $query = $this->db->select('landing_pageid, name, pagetype, CAST(cr AS DECIMAL(8,6)) AS cr', FALSE)
                ->from('landing_page')
                ->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $pgid)
                ->get();

        $winner_cr = 0;
        $winner_id = -1;
        $winner_name = 'NA';
        $control_winner = FALSE;

        foreach ($query->result() as $q) {
            if ($q->pagetype == OPT_PAGETYPE_CTRL) {
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
     * Gets the higher conversionrate from a set of variants given a page group (PG) id
     * @param Int $pgid
     * @return float
     */
    private function getHigherGroupCr($pgid) {
        $query1 = $this->db->select('CAST(MAX(cr) AS DECIMAL(8,6)) AS max', FALSE)
                ->from('landing_page')
                ->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $pgid)
                ->get();
        return $query1->row()->max;
    }

    /*     * ******************************************************************************
     * ******************** POST DECISION GROUP RELATED METHODS *************************
     * ********************************************************************************* */

    /**
     * the methods "clientOwnsAccount" "clientOwnsProject" will throw an exception in case the logged 
     * user can't access the account id set in the URL or is not the owner of the given project
     * If the mandatory fields are set, and no read-only fields are set, inserts the new groupd into the DB
     * @return JSON
     * @throws Exception - If the project could not be inserted
     */
    private function postDecisiongroup() {
        $this->clientOwnsAccount();
        $this->clientOwnsProject();
        $this->checkMandatoryFields(FALSE, 400700);
        $this->checkReadOnlyFields(FALSE, 400701);

        $param = json_decode($this->requestParameters);
        $group = array(
            'name' => strip_tags($param->name),
            'landingpage_collectionid' => $this->project,
            'restart_date' => date('Y-m-d H:i:s'),
        );
        $this->db->insert('page_group', $group);

        $pgid = $this->db->insert_id();

        if (!is_int($pgid) && $pgid <= 0) {
            throw new Exception('The decision group could not be created', 500);
        }
        $this->optimisation->flushCollectionCache($this->project);
        return self::insertGroupControl($pgid, $param->name);
    }

    /**
     * After the new group has been created, this method is called to create a new "control"
     * decision for it.
     * @param Int $pgid
     * @param string $headline - the name (w/o HTML tags) of the group which will be the same as the control
     * @return JSON
     * @throws Exception If there goes something wring creating the control for the decision group
     */
    private function insertGroupControl($pgid, $headline) {
        $param = json_decode($this->requestParameters);
        $mainurl = '';
        $runpattern = '';
        $domcode = json_encode(array(
            '[TT_HL]' => $headline,
        ));

        if (isset($param->mainurl)) {
            $domcode = NULL;
            $mainurl = $param->mainurl;
            $runpattern = $param->runpattern;
        }

        $control = array(
            'name' => isset($param->isMpt) && $param->isMpt ? '' : strip_tags($headline),
            'status' => OPT_PAGESTATUS_ACTIVE,
            'pagetype' => OPT_PAGETYPE_CTRL,
            'lp_url' => $mainurl,
            'canonical_url' => $runpattern,
            'landingpage_collectionid' => $this->project,
            'page_groupid' => $pgid,
            'dom_modification_code' => $domcode,
        );
        $this->db->insert('landing_page', $control);

        if ($this->db->insert_id() > 0) {
            return $this->successResponse(200, $pgid);
        }

        $this->decisiongroup = $pgid;
        self::deleteDecisionGroup();
        throw new Exception('The control for the decision group could not be created', 500);
    }

    /*     * ******************************************************************************
     * ***************************** PUT DECISION GROUP **********************************
     * ********************************************************************************* */

    /**
     * the methods "clientOwnsAccount" "clientOwnsProject" will throw an exception in case the logged 
     * user can't access the account id set in the URL or is not the owner of the given project
     * If the mandatory fields are set, and no read-only fields are set, updates the group
     * @return JSON
     * @throws Exception - If the project could not be inserted
     */
    private function putDecisiongroup() {
        $this->clientOwnsAccount();
        $this->clientOwnsProject();
        $this->checkMandatoryFields(FALSE, 400700);
        $this->checkReadOnlyFields(FALSE, 400701);

        $param = json_decode($this->requestParameters);
        $group = array(
            'name' => strip_tags($param->name),
        );

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->update('page_group', $group);

        $this->optimisation->flushCollectionCache($this->project);
        return self::updateGroupControl($param->name);
    }

    /**
     * After the group has been updated, we need to update the name of the group control variant
     * and the corresponding dom_modification_code.
     * @param string $headline - the name (w/o HTML tags) of the group which will be the same as the control
     * @return JSON
     * @throws Exception If there goes something wring creating the control for the decision group
     */
    private function updateGroupControl($headline) {
        $param = json_decode($this->requestParameters);
        $mainurl = '';
        $runpattern = '';

        if (isset($param->mainurl)) {
            $domcode = NULL;
            $mainurl = $param->mainurl;
            $runpattern = $param->runpattern;
        } else {
            $query = $this->db->select('dom_modification_code')
                    ->from('landing_page')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('page_groupid', $this->decisiongroup)
                    ->where('pagetype', 1)
                    ->get();
            $domcode = json_decode($query->row()->dom_modification_code, TRUE);
            $domcode['[TT_HL]'] = $headline;
        }

        $control = array(
            'name' => isset($param->isMpt) && $param->isMpt ? '' : strip_tags($headline),
            'dom_modification_code' => json_encode($domcode),
        );

        if (isset($param->isMpt) && $param->isMpt) {
            $control['lp_url'] = $mainurl;
        }

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->where('pagetype', 1)
                ->update('landing_page', $control);

        return $this->successResponse(200);
    }

    /*     * ******************************************************************************
     * ***** METHODS TO HANDLE POST ACTIONS FOR DECISION GROUPS (start, stop, restart) *******
     * ********************************************************************************* */

    /**
     * Verifies that the user can access the given project, if so, updates the status and last_sample_date
     * in the corresponding group row to set it as "running"
     * @return JSON
     */
    private function postStartDecisiongroup() {
        $this->clientOwnsProject();

        $this->db->where("landingpage_collectionid", $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->set('last_sample_date', date('Y-m-d H:i:s'))
                ->set('status', 2)
                ->update('page_group');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /**
     * Verifies that the user can access the given project, if so, updates the status and sample_time
     * in the corresponding group row to set it as "paused"
     * @return JSON
     */
    private function postStopDecisiongroup() {
        $this->clientOwnsProject();

        $this->db->where("landingpage_collectionid", $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->set('sample_time', 'sample_time + UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_sample_date)', FALSE)
                ->set('status', 1)
                ->update('page_group');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /**
     * restores the default fields for the given group id
     * @return JSON
     */
    private function postRestartDecisiongroup() {
        $this->clientOwnsProject();
        $lp = array();

        $query = $this->db->select('landing_pageid')
                ->from('landing_page')
                ->where('page_groupid', $this->decisiongroup)
                ->get();

        foreach ($query->result() as $q) {
            $lp[] = $q->landing_pageid;
        }

        $this->db->where("landingpage_collectionid", $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->set('cr', 0)
                ->set('arpv', 0)
                ->set('z_score', 0)
                ->set('revenue', 0)
                ->set('conversions', 0)
                ->set('impressions', 0)
                ->set('is_maximum', 0)
                ->set('conversion_value_aggregation', 0)
                ->set('conversion_value_square_aggregation', 0)
                ->set('standard_deviation', 0)
                ->update('landing_page');

        $this->db->where('landingpage_collectionid', $this->project)
                ->where_in('landing_pageid', $lp)
                ->set('conversions', 0)
                ->set('conversion_value_aggregation', 0)
                ->set('conversion_value_square_aggregation', 0)
                ->set('standard_deviation', 0)
                ->update('collection_goal_conversions');

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->set('progress', 1)
                ->set('sample_time', 0)
                ->set('restart_date', date('Y-m-d H:i:s'))
                ->set('last_sample_date', date('Y-m-d H:i:s'))
                ->update('page_group');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(201);
    }

    /*     * ******************************************************************************
     * ****************************** DELETE DECISION GROUP *****************************
     * ********************************************************************************* */

    /**
     * first, it verifies that the current client is the owner of the project that the group belongs to-
     * Then, deletes the landing page and the group itself from the corresponding tables
     */
    private function deleteDecisiongroup() {
        $this->clientOwnsProject();

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->delete('landing_page');

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('page_groupid', $this->decisiongroup)
                ->delete('page_group');

        $this->optimisation->flushCollectionCache($this->project);
        return $this->successResponse(200);
    }

}
