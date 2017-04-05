<?php

/**
 * This class inherits from the apiv1_core class which have some common variables and methods
 * for all of its childs
 */
require_once 'apiv1_core.php';

final class apiv1_goals extends apiv1_core {

    protected $account;
    protected $project;
    protected $group;
    protected $goal;
    private $currentPrimaryGoalId = false;
    private $newPrimaryGoalId = false;
    // Available values for TYPE
    protected $type_array = array(
        1 => 'ENGAGEMENT',
        2 => 'AFFILIATE',
        3 => 'TARGETPAGE',
        8 => 'ET_VIEWPRODUCT',
        9 => 'ET_INSERTTOBASKET',
        10 => 'ET_ORDER',
        11 => 'SMS_FOLLOW',
        12 => 'LINKURL',
        13 => 'CUSTOMJS',
        14 => 'TIMEONPAGE',
        15 => 'CLICK',
        16 => 'COMBINED',
        17 => 'PI_LIFT',
    );
    // Goal types for which is necessary to send a parameter.
    protected $param_types = array(3, 12, 13, 15);
    // Available values for LEVEL
    protected $level_array = array(
        0 => 'SECONDARY',
        1 => 'PRIMARY',
    );
    // When "deleting" a goal it is set as "archived" (not actually deleted)
    protected $status_array = array(
        0 => 'ARCHIVED',
        1 => 'ACTIVE',
    );
    // To get the appropriate DB field given the name that the client sends.
    protected $dbfields = array(
        'id' => 'g.collection_goal_id',
        'type' => 'g.type',
        'param' => 'g.arg1',
        'name' => 'g.name',
        'status' => 'g.status',
    );
    // Only type is mandatory when setting goals
    protected $mandatoryfields = array(
        'type',
    );
    // ID is a read-only field as in every other resource
    protected $readonlyfields = array(
        'id',
        'status'
    );

    function __construct() {
        parent::__construct();
        $this->load->helper('apiv1');
        $this->param_types = array(
            GOAL_TYPE_TARGETPAGE,
            GOAL_TYPE_TARGETLINK,
            GOAL_TYPE_CUSTOM_JAVASCRIPT,
            GOAL_TYPE_CLICK,
        );
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
     * See: apiv1_core->setParametersReturnMethod()
     * it verifies that the requested method exists and returns its name or throws an exception
     */
    public function index($uri) {
        $method = $this->setParametersReturnMethod($uri);
        return self::$method();
    }

    /*     * ******************************************************************************
     * **************************** GET GOALS related methods ******************************
     * ********************************************************************************* */

    /**
     * Plural "getGoals()" 
     * gets all goals created by the current client or by the client specified in /account/<id> of the 
     * logged tenant
     * @return JSON
     */
    private function getGoals($goalid = FALSE) {

        $this->db->select('g.collection_goal_id, g.page_groupid, g.type, g.arg1, g.level, g.name, g.status, g.deleteddate')
                ->from('collection_goals g')
                ->join('landingpage_collection lpc', 'lpc.landingpage_collectionid = g.landingpage_collectionid', 'INNER')
                ->where('lpc.landingpage_collectionid', $this->project);

        if (isset($this->group) && $this->group != -1) {
            $this->db->where('g.page_group_id', $this->group);
        }

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = lpc.clientid')
                    ->where('lpc.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('lpc.clientid', $this->clientid);
        }

        if (is_numeric($goalid) && $goalid > 0) {
            $this->db->where('g.collection_goal_id', $goalid);
        } else {
            $this->addParametersToQuery();
        }

        $query = $this->db->order_by('level', 'DESC')
                ->get();
        if ($query->num_rows() <= 0) {
            throw new Exception('', 404001);
        }

        $goals = array();
        foreach ($query->result() as $goal) {
            $g = array();
            $g['id'] = $goal->collection_goal_id;
            $g['page'] = $goal->page_groupid;
            $g['type'] = $this->type_array[$goal->type];
            $g['param'] = $goal->arg1;
            $g['level'] = $this->level_array[$goal->level];
            $g['name'] = $goal->name;
            $g['status'] = $this->status_array[$goal->status];
            $g['deleteddate'] = $goal->deleteddate;
            $goals[] = $g;
        }

        $ret = $goalid ? $goals[0] : $goals;
        return parent::successResponse(200, $ret);
    }

    /**
     * Singular "getGoal()"
     * given a goal id, returns all related data after calling the method "getGoals" with the goal ID as parameter
     * @return JSON
     */
    private function getGoal() {
        $goalid = is_numeric($this->goal) ? $this->goal : 0;
        return self::getGoals($goalid);
    }

    /*     * ******************************************************************************
     * ********************** COMMON METHODS FOR PUT and POST *************************
     * ********************************************************************************* */

    /**
     * Determines whether the "param" field is mandatory depending on the TYPE, also sets the
     * Goal array to be inserted or updated
     * @throws Exception - in case there is an error 
     */
    private function setGoalsArray($action) {
        $post_array = json_decode($this->requestParameters);
        $type = array_search($post_array->type, $this->type_array);
        $name = array_key_exists('name', $post_array) ? $post_array->name : '';
        $param = 'NA';

        if ($action == 'POST' && !$type) {
            throw new Exception('type: "' . $post_array->type . '"', 400004);
        }

        if (in_array($type, $this->param_types) && $this->projecttype != OPT_TESTTYPE_TEASER) {
            $param = array_key_exists('param', $post_array) ? $post_array->param : FALSE;
            if (!$param || trim($param) == '') {
                throw new Exception('For this goal type, the field "param" is mandatory.', 400600);
            }
        }

        return array(
            'type' => $type,
            'arg1' => $param,
            'name' => $name,
        );
    }

    /*     * ******************************************************************************
     * ************************** PUT/POST GOAL related methods ***************************
     * ********************************************************************************* */

    private function postGoal() {
        return self::insertOrUpdateGoal('POST');
    }

    private function putGoal() {
        return self::insertOrUpdateGoal('PUT');
    }

    /**
     * Inserts or updates the given goal in the collection_goals table after validating all given information
     * It is important to note that GOALS can't be updated by its ID, because it could lead to incorrect data, so.
     *  - It first "deletes" the goal (sets its status to 0)
     *  - then, it verifies if an identical goal already exists for the given project (it could be the case that the type and/or arg1 changed)
     *  - If there is a goal with the same data, it update it  (basically, it sets the status back to 1)
     *  - Or else, creates a new goal (leaving the status of the current one = 0)
     *                   because it has changed and althought the goal id was sent as parameter, it is not the same and can't be "updated"
     * 
     * @param String $action - Either 'PUT' or 'POST'
     * @return JSON
     * @throws Exception - in case the row cannot be updated
     */
    private function insertOrUpdateGoal($action = 'PUT') {
        $post_array = json_decode($this->requestParameters);
        $this->clientOwnsProject();
        $this->checkReadOnlyFields(FALSE, 400601);

        self::getCurrentPrimaryGoal();

        if ($action == 'POST') {
            $this->checkMandatoryFields(FALSE, 400600);
        }

        $goal = self::setGoalsArray($action);
        $goal['status'] = 1;

        if (array_key_exists('level', $post_array)) {
            if ($post_array->level == 'PRIMARY') {
                if ($action == 'PUT') {
                    $this->newPrimaryGoalId = $this->goal;
                }
            }
        }

        if (isset($this->group) && $this->group != -1) {
            $goal['page_groupid'] = $this->group;
        }
        $goal['landingpage_collectionid'] = $this->project;
        $goal['deleteddate'] = NULL;

        if ($this->goal == NULL) {
            $this->goal = self::goalAlreadyExists();
        }

        if ($this->goal) {
            self::updateExistingGoal($goal);
        } else {
            $this->db->insert('collection_goals', $goal);
            $this->goal = $this->db->insert_id();
            if ($this->goal > 0) {
                $this->flushClientCache($this->account);
            }
        }

        if (is_numeric($this->goal) && $this->goal > 0) {

            $this->syncCollectionGoals();

            if (!array_key_exists('level', $post_array) || $post_array->level == 'PRIMARY') {
                self::setPrimaryGoal();
            }

            self::setDefaultPrimaryGoalIfNotSet();
            return $this->successResponse(200, $this->goal);
        }

        throw new Exception('Could not add or update the goal for the given project', 500);
    }

    /**
     * We only check if the goals posted (as NEW, not when editing one)
     * is of type "ENAGEMENT" or "AFFILIATE. -- if so, we re-activate it.
     * For Any other goal we create a new one, no matter if it is similar to other already active/archived
     * @return boolean
     */
    private function goalAlreadyExists() {
        $post_array = json_decode($this->requestParameters);
        $type = array_search($post_array->type, $this->type_array);

        if ($type === GOAL_TYPE_ENGAGEMENT || $type === GOAL_TYPE_AFFILIATE) {
            $query = $this->db->select('collection_goal_id')
                    ->from('collection_goals')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('type', $type)
                    ->get();

            if ($query->num_rows() > 0) {
                return $query->row()->collection_goal_id;
            }
        }
        return FALSE;
    }

    /**
     * Performs the query to update a goal if it is confirmed that it already exists (after calling POST or PUT goal)
     * @param Array $g - Containing the lpcid, type, arg1, status as in the DB structure
     * @return VOID
     */
    private function updateExistingGoal($g) {
        $this->db->where('collection_goal_id', $this->goal)
                ->where('landingpage_collectionid', $this->project);

        if (isset($this->group) && $this->group != -1) {
            $this->db->where('page_groupid', $this->group);
        }

        $this->db->update('collection_goals', $g);

        $this->flushClientCache($this->account);
        return;
    }

    /*     * ******************************************************************************
     * *********** DELETE (ARCHIVE) GOAL RELATED METHODS *************
     * ********************************************************************************* */

    /**
     * first, it verifies that the current client is the owner of the project that the goal is assigned to
     * @return JSON
     */
    private function deleteGoal($sync = TRUE) {
        $this->clientOwnsProject();

        $data = array(
            'status' => 0,
            'deleteddate' => date('Y-m-d H:i:s'),
        );

        $this->db->where('collection_goal_id', $this->goal)
                ->where('landingpage_collectionid', $this->project)
                ->where('status != ', 0);

        if (isset($this->group) && $this->group != -1) {
            $this->db->where('page_groupid', $this->group);
        }

        $this->db->update('collection_goals', $data);

        $this->flushClientCache($this->account);
        if ($sync) {
            $this->syncCollectionGoals();
            self::setDefaultPrimaryGoalIfNotSet();
        }

        return $this->successResponse(200);
    }

    /*     * ******************************************************************************
     * **************** REACTIVATE GOAL RELATED METHODS ****************
     * ********************************************************************************* */

    /**
     * first, it verifies that the current client is the owner of the project that the goal is assigned to
     * then sets the status to "ACTIVE" (1 in the DB)
     * @return JSON
     */
    private function postReactivate($sync = TRUE) {
        $this->clientOwnsProject();

        $this->db->where('collection_goal_id', $this->goal)
                ->where('landingpage_collectionid', $this->project);

        if (isset($this->group) && $this->group != -1) {
            $this->db->where('page_groupid', $this->group);
        }

        $st = array_search('ACTIVE', $this->status_array);
        $this->db->update('collection_goals', array('status' => $st));

        $this->flushClientCache($this->account);
        if ($sync) {
            $this->syncCollectionGoals();
            self::setDefaultPrimaryGoalIfNotSet();
        }

        return $this->successResponse(200);
    }

    /*     * ******************************************************************************
     * ******************* PRIMARY/SECONDARY GOALS RELATED METHODS ********************
     * ********************************************************************************* */

    /**
     * Whenever we insert, update or delete a goal, we make sure that there is always
     * a PRIMARY goal (in case there are goals for the given project
     * @return boolean
     */
    private function setDefaultPrimaryGoalIfNotSet() {
        $q1 = $this->db->select('count(*) AS cnt')
                ->from('collection_goals')
                ->where('landingpage_collectionid', $this->project)
                ->where('status', 1)
                ->where('level', 1)
                ->get();

        if ($q1->num_rows() > 0 && $q1->row()->cnt > 0) {
            return FALSE;
        }

        $this->db->where('landingpage_collectionid', $this->project)
                ->update('collection_goals', array('level' => 0));

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('status', 1)
                ->limit(1)
                ->update('collection_goals', array('level' => 1));
    }

    /**
     * First, it verifies that the selected new "primary" goal exists and it is active - returns FALSE otherwise
     * Then, it gets the current "primary" goal for the given project, if it is equal to the newone, returns
     *      FALSE inmediately because no further actions are required.
     * Then, updates the collection_goals table to set all of the current goals as "secondary" by
     *      setting its "level" to 0, after that, sets the new goal as primary by setting its "level" to 1.
     * After that, calls the method "updateProjectStatistics" to modify the corresponding values in 
     *      tables landing_page and collection_goal_conversions
     * @return boolean
     */
    private function setPrimaryGoal() {
        $q1 = $this->db->select('count(*) AS cnt')
                ->from('collection_goals')
                ->where('collection_goal_id', $this->goal)
                ->where('status', 1)
                ->get();

        if ($q1->num_rows() <= 0 || $q1->row()->cnt == 0) {
            return FALSE;
        }

        //$old_goalid = $q2->num_rows() <= 0 ? FALSE : $q2->row()->collection_goal_id;
        $this->db->where('landingpage_collectionid', $this->project)
                ->update('collection_goals', array('level' => 0));

        $this->db->where('collection_goal_id', $this->goal)
                ->update('collection_goals', array('level' => 1));

        self::updatePageStatistics($this->currentPrimaryGoalId);
        $this->optimisation->flushKpiResultsForCollection($this->project);
        $this->optimisation->evaluateImpact($this->project);
        $this->optimisation->flushCollectionCache($this->project);
    }

    /**
     * Retrieve the ID of the current primary goal. This is necessary to determine the correct action
     * if a new goal is set to primary
     */
    private function getCurrentPrimaryGoal() {
        $q = $this->db->select('collection_goal_id')
                ->from('collection_goals')
                ->where('landingpage_collectionid', $this->project)
                ->where('level', 1)
                ->where('status', 1)
                ->get();
        if ($q->num_rows() > 0) {
            $this->currentPrimaryGoalId = $q->row()->collection_goal_id;
        }
    }

    /**
     * Copies the page statistics from landing_page to the "old" goal row in collection_goal_conversions 
     * Then, Copies the statistics of the new primary goal from collection_goal_conversions to landing_page
     * @param Int $old_goalid - the "old" primary goal
     */
    private function updatePageStatistics($old_goalid) {
        if ($old_goalid == $this->goal) {
            return false;
        }

        $old_goal_deleted = !$old_goalid ? TRUE : FALSE;
        $query = $this->db->select('landing_pageid, impressions, conversions, conversion_value_aggregation,
                conversion_value_square_aggregation, standard_deviation')
                ->from('landing_page')
                ->where_in('pagetype', array(1, 2))
                ->where('landingpage_collectionid', $this->project)
                ->get();

        foreach ($query->result() as $res) {
            $pageid = $res->landing_pageid;
            $impressions = $res->impressions;
            $conversions = $res->conversions;
            $aggregation = $res->conversion_value_aggregation;
            $square_aggregation = $res->conversion_value_square_aggregation;
            $stdv = $res->standard_deviation;

            if (!$old_goal_deleted) {
                $cr = $impressions == 0 ? 0 : $aggregation / $impressions;
                $up = array(
                    'conversions' => $conversions,
                    'conversion_value_aggregation' => $aggregation,
                    'conversion_value_square_aggregation' => $square_aggregation,
                    'standard_deviation' => $stdv
                );
                $this->db->where('landing_pageid', $pageid)
                        ->where('goal_id', $old_goalid)
                        ->update('collection_goal_conversions', $up);
            }

            $q = $this->db->select('conversions, conversion_value_aggregation, 
                    conversion_value_square_aggregation, standard_deviation')
                    ->from('collection_goal_conversions')
                    ->where('landing_pageid', $pageid)
                    ->where('goal_id', $this->goal)
                    ->get();

            if ($q->num_rows() > 0) {
                $conversions = $q->row()->conversions;
                $aggregation = $q->row()->conversion_value_aggregation;
                $square_aggregation = $q->row()->conversion_value_square_aggregation;
                $stdv = $q->row()->standard_deviation;
                $cr = $impressions == 0 ? 0 : $q->row()->conversion_value_aggregation / $impressions;
                $up = array(
                    'conversions' => $conversions,
                    'conversion_value_aggregation' => $aggregation,
                    'cr' => $cr,
                    'conversion_value_square_aggregation' => $square_aggregation,
                    'standard_deviation' => $stdv
                );
                $this->db->where('landing_pageid', $pageid)
                        ->update('landing_page', $up);
            }
        }
    }

}
