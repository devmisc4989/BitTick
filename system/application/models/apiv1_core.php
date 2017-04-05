<?php

class apiv1_core extends CI_Model {

    protected $apiclientid;
    protected $apikey;
    protected $clientid;
    protected $usertype;
    protected $userip;
    protected $projecttype;
    protected $requestMethod;
    protected $requestParameters;
    protected $commonAttribs = array(
        'apiclientid', 'apikey', 'clientid', 'usertype', 'userip', 'projecttype', 'requestMethod', 'requestParameters'
    );
    private $errorCodes = array(
        400003 => array(
            'message' => 'Invalid field/s: ',
        ),
        400004 => array(
            'message' => 'Invalid value supplied for ',
        ),
        400100 => array(
            'message' => 'Mandatory field/s for account missing: ',
        ),
        400101 => array(
            'message' => 'Read-only field/s for account not allowed to be set: ',
        ),
        400103 => array(
            'message' => 'Invalid field: not allowed to change ',
        ),
        400104 => array(
            'message' => 'Unique constraint violation: publicid must be unique',
        ),
        400105 => array(
            'message' => 'Unique constraint violation: email must be unique',
        ),
        400106 => array(
            'message' => 'Unique constraint violation: apikey must be unique',
        ),
        400200 => array(
            'message' => 'Mandatory field/s for project missing: ',
        ),
        400201 => array(
            'message' => 'Read only field/s for project not allowed to be set: ',
        ),
        400300 => array(
            'message' => 'Mandatory field/s for decision missing: ',
        ),
        400301 => array(
            'message' => 'Read only field/s for decision not allowed to be set: ',
        ),
        400400 => array(
            'message' => 'Mandatory field/s for rules missing: ',
        ),
        400401 => array(
            'message' => 'Read-only field/s for rules not allowed to be set: ',
        ),
        400500 => array(
            'message' => 'Mandatory field/s for rule conditions missing: ',
        ),
        400501 => array(
            'message' => 'Read-only field/s for rule conditions not allowed to be set: ',
        ),
        400600 => array(
            'message' => 'Mandatory field/s for goals missing: ',
        ),
        400601 => array(
            'message' => 'Read-only field/s for goals not allowed to be set: ',
        ),
        400700 => array(
            'message' => 'Mandatory field/s for page group missing: ',
        ),
        400701 => array(
            'message' => 'Read only field/s for page group not allowed to be set: ',
        ),
        401000 => array(
            'message' => 'Authentication error: Invalid api-key or api-secret.',
        ),
        403006 => array(
            'message' => 'No permission for feature/s: ',
        ),
        403102 => array(
            'message' => 'Authorization error, no permission to create account',
        ),
        403103 => array(
            'message' => 'Authorization error: Access to account denied.',
        ),
        403203 => array(
            'message' => 'Authorization error: Access to project denied',
        ),
        404001 => array(
            'message' => 'Resource not found: ',
        ),
        500 => array(
            'message' => 'Internal server error: ',
        ),
    );

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('optimisation');
    }

    /**
     * checks if the user with the corresponding credentials is in the DB, if so, 
     * returns an object with the the client type, clientid and apikey
     * @param string $apikey
     * @param string $apisecret
     * @return string - 'api-tenant' for administrator or 'api-client' for regular users
     * @throws Exception
     */
    public function clientLogin($apikey, $apisecret) {
        $query = $this->db->select("api_clientid, api_client_type, clientid")
                ->from("api_client")
                ->where("apikey", $apikey)
                ->where("apisecret", $apisecret)
                ->get();

        if (count($query->result()) != 1) {
            throw new Exception('', 401000);
        }

        $res = array();
        foreach ($query->result() as $q) {
            $res['apikey'] = $apikey;
            $res['clientid'] = $q->clientid;
            $res['usertype'] = $q->api_client_type == 1 ? 'api-tenant' : 'api-client';
            $res['apiclientid'] = $q->api_clientid;
            $res = json_encode($res);
        }
        return json_decode($res);
    }

    /**
     * Verifies the entire requested URL, sets the corresponding values  and returns the method
     * name (getAccounts, postGoal...) which is the first element of the last URL pair
     * 
     * This method is called by all subclasses except for PROJECTS, because there is an special case
     * handled directly by that class
     * $this->WHATEVER refers to a variable defined in the child class
     * the method "clientOwnsAccount" will throw an exception in case the logged user can't access
     * the account id set in the URL
     * 
     * example:
     * GET BASEURL/account/4511/project/111/goals
     *  - sets $this->account = 4511
     *  - sets $this->project = 111
     *  - returns "getGoals" as the method name
     * 
     * @param array $uri
     * @return String - the method name to be called in the child class INDEX method
     * @throws Exception - in case there is a problem with the credential of the current user or the method doesn't exist
     */
    protected function setParametersReturnMethod($uri) {
        $name = '';
        foreach ($uri as $key => $value) {
            $this->$key = $value;
            $name = ucfirst($key);
        }

        if (isset($this->project)) {
            $query = $this->db->select('testtype')
                    ->from('landingpage_collection')
                    ->where('landingpage_collectionid', $this->project)
                    ->get();

            $this->projecttype = $query->row()->testtype;
        }

        $method = strtolower($this->requestMethod) . $name;
        self::clientOwnsAccount($method);

        if (!method_exists($this, $method)) {
            throw new Exception($method, 404001);
        }

        return $method;
    }

    /**
     * This method verifies that the "/account/<id>" parameter in the URL is equal to the 
     * logged clientid value (if he is a client) OR, if he is a tenant, verifies that the above
     * parameter is the ID of one of his api_clients.
     * If the logged user is a tenant, and the requested method is one out of "postAccount" or
     * "getAccounts", it returns true, as the /account/<id> parameter is not set in such cases.
     * 
     * @param String $method - the name of the requested resource (getProjects, getGoals...)
     * @return boolean
     * @throws Exception - If the above validations are not met.
     */
    protected function clientOwnsAccount($method = NULL) {
        if ($this->usertype == 'api-tenant') {

            if ($method == 'postAccount' || $method == 'getAccounts') {
                return TRUE;
            }

            $query = $this->db->select('clientid')
                    ->from('api_client')
                    ->where('clientid', $this->account)
                    ->where('(api_tenant = ' . $this->apiclientid . ' OR api_clientid = ' . $this->apiclientid . ')')
                    ->get();

            if ($query->num_rows() > 0 && $query->row()->clientid == $this->account) {
                return TRUE;
            }
        } else if ($this->account == $this->clientid) {
            return TRUE;
        }

        $ex = $method == 'postAccount' ? 403102 : 403103;
        throw new Exception('', $ex);
    }

    /**
     * Verifies that the given LPC belongs to the current client to perform actions like deleting projects
     * if the logged client is a tenant he can access his clients projects as well
     * @return boolean
     * @throws Exception
     */
    protected function clientOwnsProject() {
        if ($this->project * 1 <= 0) {
            throw new Exception('', 404001);
        }

        $this->db->select('COUNT(*) AS cnt')
                ->from('landingpage_collection lpc')
                ->join('client c', 'c.clientid = lpc.clientid', 'INNER')
                ->where('lpc.landingpage_collectionid', $this->project);

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = lpc.clientid', 'INNER')
                    ->where('ac.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('c.clientid', $this->clientid);
        }

        $query = $this->db->get();

        if ($query->row()->cnt > 0) {
            return TRUE;
        }
        throw new Exception('', 403203);
    }

    /**
     * Verifies that the given Rule ID belongs to the current client to edit, delete or assign rules to projects
     * or decisions
     * @return boolean
     * @throws Exception - if the user (or tenant) does not own the perso rule
     */
    protected function clientOwnsRule($message = '', $httpcode = 403203) {
        $this->db->select('COUNT(*) AS cnt')
                ->from('rule r')
                ->join('client c', 'c.clientid = r.clientid', 'INNER')
                ->where('r.rule_id', $this->rule);

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = r.clientid', 'INNER')
                    ->where('ac.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('c.clientid', $this->clientid);
        }

        $query = $this->db->get();

        if ($query->row()->cnt > 0) {
            return TRUE;
        }
        throw new Exception($message, $httpcode);
    }

    /**
     * Returns the JSON representation of the features for the current client
     * @return JSON
     * @throws Exception - if the query does not return any results, the client does not exist ot there was an error
     */
    protected function getClientFeatures() {
        $query = $this->db->select('features')
                ->from('client')
                ->where('clientid', $this->account)
                ->get();

        if ($query->num_rows() <= 0) {
            throw new Exception('', 403006);
        }
        return $query->row()->features;
    }

    /**
     * As operations on clients are based on his client_id, we need to get the clientid_hash before calling
     * the flushCache method located in the optimization class
     * @param Int $clientid
     */
    protected function flushClientCache($clientid) {
        $query = $this->db->select('clientid_hash')
                ->from('client')
                ->where('clientid', $clientid)
                ->get();

        $clientcode = $query->row()->clientid_hash;
        $this->optimisation->flushAPCCacheForClient($clientcode);
    }

    /**
     * If there are parameters (get or post), adds one by one to the query to filter the results from the DB
     * if the KEY is "sort", adds an ORDER BY clause to the query
     * if the KEY is "fields", sets the query SELECT values (to limit the number of returned fields)
     * if the KEY is a DB field, adds a WHERE clause to filter the results
     */
    protected function addParametersToQuery() {
        foreach ($this->requestParameters as $key => $value) {
            if ($key == 'sort') {
                self::setSortParameters($value);
            } else if ($key == 'fields') {
                self::setFieldParameters($value);
            } else {
                self::setFilterParameters($key, $value);
            }
        }
    }

    /**
     * adds the corresponding ORDER BY clause to the query if there is a "sort" KEY in the URL
     * @param String $value - the name of the DB field to sort the results
     */
    protected function setSortParameters($value) {
        $ascdsc = $value[0] == '-' ? 'DESC' : 'ASC';
        $key = $ascdsc == 'DESC' ? substr($value, 1) : $value;

        if (!array_key_exists($key, $this->dbfields)) {
            throw new Exception($key, 400003);
        }
        $this->db->order_by($this->dbfields[$key], $ascdsc);
    }

    /**
     * adds the corresponding SELECT clause to limit the number of fields returned (passed in the URL)
     * If ID is not set in the fields passed by the user, it is added to it because it is mandatory
     * @param String $value - a comma separated string with the field names
     */
    protected function setFieldParameters($value) {
        $fields = split(',', $value);

        foreach ($fields as $key) {
            $key = str_replace(' ', '', $key);

            if (!array_key_exists($key, $this->dbfields)) {
                throw new Exception($key, 400003);
            }
            $this->db->select($this->dbfields[$key]);
        }
    }

    /**
     * adds the corresponding WHERE clause to filter the results
     * @param String $key - the name of the field to filter
     * @param String $value - the value that the field has to have
     */
    protected function setFilterParameters($key, $value) {
        if (!array_key_exists($key, $this->dbfields)) {
            throw new Exception($key, 400003);
        }

        $k = $this->dbfields[$key];
        $fieldarray = $key . '_array';
        if (is_array($this->{"$fieldarray"})) {
            $val = array_search($value, $this->{"$fieldarray"});
        } else {
            $val = $value;
        }
        $this->db->where($k, $val);
    }

    /**
     * Verifies that all of the mandatory fields are set by the user
     * @return array
     */
    protected function checkMandatoryFields($custom = FALSE, $ccode = 400) {
        $missing = '';
        $params = json_decode($this->requestParameters);
        $compare = $custom ? $custom : $this->mandatoryfields;

        foreach ($compare as $field) {
            if (!array_key_exists($field, $params)) {
                $missing .= $field . ', ';
            }
        }

        if ($missing != '') {
            throw new Exception($missing, $ccode);
        }
    }

    /**
     * Verifies that none of the read-only fields are set by the user when creating or editing 
     * resources like accounts or projects
     * @return  Array
     */
    protected function checkReadOnlyFields($custom = FALSE, $ccode = 400) {
        $found = '';
        $params = json_decode($this->requestParameters);
        $compare = $custom ? $custom : $this->readonlyfields;

        foreach ($params as $key => $value) {
            if (in_array($key, $compare)) {
                $found .= $key . ', ';
                dblog_debug("USER: $this->clientid ($this->account), trying to set read-only fields: $key ($value)");
            }
        }

        if ($found != '') {
            throw new Exception($found, $ccode);
        }
    }

    /**
     * First, it deletes unused entries in collection_goal_conversions
     * then, adds the corresponding entries to the collection_goal_conversions table for the given project
     *  which combination of landing_pageid and collection_goal_id are not set yet.
     */
    protected function syncCollectionGoals() {
        self::deleteUnusedGoalConversions();

        $query = $this->db->select('lpc.testtype, lp.landing_pageid, lp.page_groupid, cg.collection_goal_id')
                ->from('landingpage_collection lpc')
                ->join('collection_goals cg', 'cg.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                ->join('landing_page lp', 'lp.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                ->where('lpc.landingpage_collectionid', $this->project)
                ->where('cg.status', 1)
                ->get();

        foreach ($query->result() as $res) {
            $validVariant = TRUE;
            
            if ($res->testtype == OPT_TESTTYPE_MULTIPAGE) {
                $validVariant = $res->page_groupid == -1;
            } else if ($res->testtype == OPT_TESTTYPE_TEASER) {
                $validVariant = $res->page_groupid != -1;
            }

            if (!$validVariant) {
                continue;
            }

            $pageid = $res->landing_pageid;
            $goalid = $res->collection_goal_id;

            $q = $this->db->select('count(*) as cnt')
                    ->from('collection_goal_conversions')
                    ->where('landingpage_collectionid', $this->project)
                    ->where('landing_pageid', $pageid)
                    ->where('goal_id', $goalid)
                    ->get();

            if ($q->num_rows() <= 0 || $q->row()->cnt == 0) {
                $ins = array(
                    'landingpage_collectionid' => $this->project,
                    'landing_pageid' => $pageid,
                    'goal_id' => $goalid,
                );
                $this->db->insert('collection_goal_conversions', $ins);
            }
        }
    }

    /**
     * Deletes from the table collection_goal_conversions the rows which landing_pageid's does not
     * exist in the landing_page table AND which corresponding entry in collection_goals table is
     * not active (status != 1)
     */
    private function deleteUnusedGoalConversions() {
        $pages = array();
        $goals = array();

        $q1 = $this->db->select('landing_pageid')
                ->from('landing_page')
                ->where('landingpage_collectionid', $this->project)
                ->get();

        $q2 = $this->db->select('collection_goal_id')
                ->from('collection_goals')
                ->where('landingpage_collectionid', $this->project)
                ->get();

        foreach ($q1->result() as $res) {
            array_push($pages, $res->landing_pageid);
        }
        foreach ($q2->result() as $res) {
            array_push($goals, $res->collection_goal_id);
        }

        $this->db->where('landingpage_collectionid', $this->project)
                ->where('(landing_pageid NOT IN (' . join(',', $pages) . ') OR goal_id NOT IN (' . join(',', $goals) . '))')
                ->delete('collection_goal_conversions');
    }

    /**
     * returns a success message with the corresponding data to the client
     * @param int $httpcode - the http code to be returned
     * @param array $res - an array to be encoded in JSON and returned
     * @return * -- after echoing the JSON object
     */
    public function successResponse($httpcode, $res = FALSE) {
        return array(
            'code' => $httpcode,
            'res' => $res,
        );
    }

    /**
     * If something goes wrong, logs an error with the client IP address and his apikey
     * Then, returns the custom error message  with its corresponding http code
     * 
     * @param string $meth
     * @param int $code
     * @param string $param - for furter info about the error message (e. "fields missing: " . "type, name")
     * @return boolean - after echoing the entire error array, returns FALSE
     */
    public function errorResponse($code = 400, $param = '') {
        $msg = isset($this->errorCodes[$code]['message']) ? $this->errorCodes[$code]['message'] : '';

        $log = "API Error: IP=$this->userip, Message: $msg . $param";
        dblog_message(3, 270, $log, $this->clientid);

        return array(
            'code' => $code,
            'message' => $msg . $param,
        );
    }

}
