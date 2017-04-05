<?php

/**
 * URL of the main API class
 * 
 * provides the right URL to perform requests to the API itself.
 */
define('API_URL', 'https://www.blacktri.com/api/v1/', TRUE);

/**
 * Provides access to all available resources that can be found in the API
 * 
 * Al methods included in this class are accessible by any type of user (tenant and clients) and works
 * as an abstraction layer for the API itself. It contains multiple methods that enables users to
 * Create, Read, Update and Delete resources associated to his/her account like projects,
 * decisions, personalization rules, goals or his/her account details.
 */
class apiv1sdk {

    /**
     * Curl resource
     * 
     * Contains the CURL resource configuration to perform request remotely to the API
     * 
     * @var Resource
     */
    private $conn;

    /**
     * Variables available to be set by the user
     * 
     * Array of variables that can be set or modified by the user by calling the magic method __SET.
     * Variable names listed here are defined later as protected.
     * 
     * @var Array
     */
    private $attribs = array(
        'apikey', 'apisecret', 'proxyurl', 'timeout', 'clientid', 'apiurl'
    );

    /**
     * The user's api key
     * 
     * It's a string defined by the API automatically when a new user is created, it is stored in the DB and
     * identifies uniquely an API user
     * 
     * @var String
     */
    protected $apikey;

    /**
     * The user's api secret
     * 
     * A pair apikey/apisecret is required to perform any request to the API in the server, if this pair
     * does not match, an exception is thrown before any action takes place
     * 
     * @var String
     */
    protected $apisecret;

    /**
     * Aternative URL to use as a proxy
     * 
     * In case the user access the internet via a proxy this variable allows him to set its URL
     * 
     * @var String
     */
    protected $proxyurl;

    /**
     * Time to wait before the SDK stops waiting for a response
     * 
     * Depending on the internet connection speed and under other particular circunstances, the time
     * to wait for a response from the API can be increased or decreased.
     * 
     * @var Int
     */
    protected $timeout = 5000;

    /**
     * ID that uniquely identifies a user.
     * 
     * With every request, clients have to send this parameter in the URL, this allows the API to
     * access the right resource for the current user.
     * 
     * @var Int
     */
    protected $clientid;

    /**
     * Base URL of the REST API.
     * 
     * Using this parameter, the defined API_URL can be overwritten
     * 
     * @var String
     */
    protected $apiurl;

    /**
     * Set protected attribute values listed in the $attribs array
     * 
     * This "magic" method allows users to set or update the class variables from their local instance, only
     * the variables listed in the $attrib array can be set/updated
     * 
     * @param string $attrib
     * @param string/int $value
     */
    public function __set($attrib, $value) {
        if (in_array($attrib, $this->attribs)) {
            $this->$attrib = $value;
        }
    }

    /**
     * Gets the value of a protected attribute listed in $attribs
     * 
     * In case the user needs to know the current value for a particular protected variable, he can
     * call this method which will return it. (only if that variable is listed in the $attrib array)
     * 
     * @param string $attrib - the name of the attribute
     * @return type - the value given to that attribute
     */
    public function __get($attrib) {
        if (in_array($attrib, $this->attribs)) {
            return $this->$attrib;
        }
    }

    /**
     * Provides convenience login functionlality
     * 
     * This method is a convenience method to retrieve the account id for a given API key / API secret.
     * 
     * @param Array $data contains the apikey/secret and the expected usertype ("client"|"tenant")
     * @return Object containing the account id
     */
    public function login($data) {
        $path = "login";
        return self::performRequest($path, 'POST', $data);
    }

    /**
     * For tenants only: retrieves all client's account information
     * 
     * If the logged user is a tenant, he can call this method to get a list of  his client's accounts with their 
     * respective information including the client id, first name, last name, email, status, user plan, etc.
     * 
     * The list can be searched/filtered with the following querystring qualifiers
     * subid, publicid, custom1, custom2, custom3, apikey, apisecret, email, emailvalidated, status.
     * 
     * The result list can be sorted by providing the qualifier "sort" with one of the following attributes
     * publicid, custom1, custom2, custom3, apikey, email, createddate
     * A dash can be added to the attribute to indicate descending order.
     * 
     * e.g. getAccounts(status=ACTIVE&sort-publidid)
     * 
     * @param String $filter "&" separated key=value pairs to filter and sort results
     * @return Object Containing the list of accouts and details for each of them
     */
    public function getAccounts($filter = FALSE) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "accounts" . $f;
        return self::performRequest($path);
    }

    /**
     * Retrieves a particular account information
     * 
     * If this method is called by a tenant, the provided parameter "accountid" has to match one of his clients,
     * if it is called by a client, if thas to match his own ID, otherwise, the API will throw an exception
     * 
     * @param Int $accountid - the user account id to retrieve the data for
     * @return Object Containing all the details for the specified account
     */
    public function getAccount($accountid) {
        $path = "account/$accountid";
        return self::performRequest($path);
    }

    /**
     * Allows a tenant to create a new account
     * 
     * If a tenant needs to create a new account, he can send an array with all required data as parameter
     * to this method, including i.e. his first name, last name, status, quota, etc.
     * 
     * @param Array $account - an array with the required data to create a new account
     * @return Object Containing the new created account id
     */
    public function createAccount($account) {
        $path = "account";
        return self::performRequest($path, 'POST', $account);
    }

    /**
     * Updates an account information
     * 
     * Tenants can update one of his client's account information by sending  the account Id and the 
     * array with the new data as parameters to this method
     * 
     * @param Int $accountid the account ID
     * @param Array $account Array with all the required account data
     * @return Object Containing the response from the server after trying to edit the account details
     */
    public function updateAccount($accountid, $account) {
        $path = "account/$accountid";
        return self::performRequest($path, 'PUT', $account);
    }

    /**
     * Returns an object with a list of projects and its corresponding data
     * 
     * If the user is a tenant, this method get the information of all of the projects that he or his clients
     * have created.
     * If the user is a client, returns the projects that has been created himself.
     * 
     * Results can be searched/filtered with the following querystring qualifiers that refer to values of the 
     * resource attributes "type" and "status" (e.g, type=SPLIT&status=RUNNING).
     * 
     * The result list can be sorted by providing the qualifier sort with one of the project attributes. 
     * A dash can be added to the attribute to indicate descending order (e.g. sort=-createddate).
     * 
     * The fields contained in the result set can be determined or limited by providing a parameter "fields" 
     * with a comma separated list of field names. (e.g. fields=id,name,mainurl,visitors,conversions)
     * 
     * @param String $filter - "&" separated key=value pairs to filter and sort and limit results
     * @return Object Containing a list of project with their respective details
     */
    public function getProjects($filter = FALSE) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "account/$this->clientid/projects" . $f;
        return self::performRequest($path);
    }

    /**
     * Returns all data given a project id
     * 
     * Receives a project ID as parameter and returns a JSON object with all the project
     * related data
     * 
     * @param Int $projectid The ID of the project to retrieve the data for
     * @return Object Containing the details for the specified project
     */
    public function getProject($projectid) {
        $path = "account/$this->clientid/project/$projectid";
        return self::performRequest($path);
    }

    /**
     * Creates a new project.
     * 
     * Users can create project by calling this method with an array containing all required data including
     * the project type, mainurl, run pattern, name, etc.
     * 
     * @param Array $project contains all required fields to create a new project
     * @return Object Containing the new created project id
     */
    public function createProject($project) {
        $path = "account/$this->clientid/project";
        return self::performRequest($path, 'POST', $project);
    }

    /**
     * Updates a project information
     * 
     * To update a project information, users have to pass the project id and the array of related data
     * to this method.
     * 
     * @param Int $idproject the ID of the project to be updated
     * @param Array $project contains all edited fields to be updated 
     * @return Object Containing the response from the server after trying to edit the project details
     */
    public function updateProject($idproject, $project) {
        $path = "account/$this->clientid/project/$idproject";
        return self::performRequest($path, 'PUT', $project);
    }

    /**
     * Deletes a project given a project ID
     * 
     * @param Int $projectid ID of the project to be deleted
     * @return Object Containing the response from the server after trying to delete the sepecified project
     */
    public function deleteProject($projectid) {
        $path = "account/$this->clientid/project/$projectid";
        return self::performRequest($path, 'DELETE');
    }

    /**
     * given a project ID, starts the project by updating the necessary fields in the DB (like setting the
     * status to 1)
     * 
     * @param Int $projectid
     * @return Object Containing  the response from the server after trying to start the project
     */
    public function startProject($projectid) {
        $path = "account/$this->clientid/project/$projectid/start";
        return self::performRequest($path, 'POST');
    }

    /**
     * given a project ID, stops the project by updating the necessary fields in the DB (like setting the
     * status to 0)
     * 
     * @param Int $projectid
     * @return Object Containing the response from the server after trying to stop the project
     */
    public function stopProject($projectid) {
        $path = "account/$this->clientid/project/$projectid/stop";
        return self::performRequest($path, 'POST');
    }

    /**
     * given a project ID, restarts the project by updating the necessary fields in the DB (like resetting
     * conversions, impressions, etc)
     * 
     * @param Int $projectid The ID of the project to be restarted
     * @return Object Containing  the response from the server after trying to restart the project
     */
    public function restartProject($projectid) {
        $path = "account/$this->clientid/project/$projectid/restart";
        return self::performRequest($path, 'POST');
    }

    /**
     * given a project ID, starts the autopilot by setting its value to 1
     * 
     * @param Int $projectid The ID of the project to start the autopilot for
     * @return Object Containing the response from the server after trying to start the autopilot for the project
     */
    public function startAutopilot($projectid) {
        $path = "account/$this->clientid/project/$projectid/autopilot/start";
        return self::performRequest($path, 'POST');
    }

    /**
     * given a project ID, stops the autopilot by setting its value to 0
     * 
     * @param Int $projectid The ID of the project to stop the autopilot for
     * @return Object Containing the response from the server after trying to stop the atopilot for the project
     */
    public function stopAutopilot($projectid) {
        $path = "account/$this->clientid/project/$projectid/autopilot/stop";
        return self::performRequest($path, 'POST');
    }

    /**
     * Get a list of decision_groups that the current user has created.
     * 
     * It can be filtered and sorted by one or more of the available fields (e.g, status=RUNNING&sort=name).
     * 
     * @param Int $projectid - the ID of the project that the decision group belongs to
     * @param String $filter - "&" separated key=value pairs to filter, sort and limit results
     * @return Object containig the list of created decision groups
     */
    public function getDecisionGroups($projectid, $filter = FALSE) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "account/$this->clientid/project/$projectid/decisiongroups" . $f;
        return self::performRequest($path);
    }

    /**
     * Returns all data given a projec id and a group decision id
     * 
     * Receives a project ID and a decision group id as parameters and returns a JSON object with
     * all of the decision group data
     * 
     * @param Int $projectid - The ID of the project to that the decision group belongs to
     * @param type $decisiongroupid - the decision group id
     * @return Object Containing the details for the specified project
     */
    public function getDecisionGroup($projectid, $decisiongroupid) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup/$decisiongroupid";
        return self::performRequest($path);
    }

    /**
     * Creates a new decision group
     * 
     * Users can create decision groups by calling this method with a project ID and an array containing 
     * all required data for the decision group (name)
     * 
     * @param Int $projectid - The ID of the project that the new decision group will be associated to
     * @param Array $decisiongroup - containing mandatory fields ( array(name => 'groupname'); )
     * @return The new decision group ID
     */
    public function createDecisionGroup($projectid, $decisiongroup) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup";
        return self::performRequest($path, 'POST', $decisiongroup);
    }

    /**
     * Updates a decision group data
     * 
     * Users can create decision groups by calling this method with a project ID and an array containing 
     * all required data for the decision group (name)
     * 
     * @param Int $projectid - The ID of the project that the new decision group will be associated to
     * @param Array $decisiongroup - containing mandatory fields ( array(name => 'groupname'); )
     * @return The new decision group ID
     */
    public function updateDecisionGroup($projectid, $decisiongroupid, $decisiongroup) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup/$decisiongroupid";
        return self::performRequest($path, 'PUT', $decisiongroup);
    }

    /**
     * Deletes a decision group given a project and a decision group ID
     * 
     * @param Int $projectid The ID of the project to delete the decision group for
     * @param Int $decisiongroupid The ID of the decision group to be deleted
     * @return Object Containing the response from the server after trying to delete the given decision
     */
    public function deleteDecisionGroup($projectid, $decisiongroupid) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup/$decisiongroupid";
        return self::performRequest($path, 'DELETE');
    }

    /**
     * given a project ID and a decision group ID, starts the group by updating the necessary fields in the DB 
     * (like setting the status to "RUNNING")
     * 
     * @param Int $projectid
     * @param Int $decisiongroupid The ID of the decision group to be started
     * @return Object Containing  the response from the server after trying to start the group
     */
    public function startDecisionGroup($projectid, $decisiongroupid) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup/$decisiongroupid/start";
        return self::performRequest($path, 'POST');
    }

    /**
     * given a project ID and a decision group ID, stops the group by updating the necessary fields in the DB 
     * (like setting the status to "PAUSED")
     * 
     * @param Int $projectid
     * @param Int $decisiongroupid The ID of the decision group to be paused
     * @return Object Containing the response from the server after trying to stop the group
     */
    public function stopDecisionGroup($projectid, $decisiongroupid) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup/$decisiongroupid/stop";
        return self::performRequest($path, 'POST');
    }

    /**
     * given a project ID and a decision group ID, restarts the group by updating the necessary fields in the DB.
     * 
     * @param Int $projectid The ID of the project to be restarted
     * @param Int $decisiongroupid The ID of the decision group to be restarted
     * @return Object Containing  the response from the server after trying to restart the group
     */
    public function restartDecisionGroup($projectid, $decisiongroupid) {
        $path = "account/$this->clientid/project/$projectid/decisiongroup/$decisiongroupid/restart";
        return self::performRequest($path, 'POST');
    }

    /**
     * Returns a list of decisions for the given project
     * 
     * The result list can be sorted by providing the qualifier "sort" with one of the following attributes:
     * "name", "conversions", A dash can be added to the attribute to indicate descending order
     * (e.g. sort=-name).
     * The list can be searched/filtered with the following querystring qualifiers that refer to values of the 
     * resource attributes: "result". (e.g. sort=-name&result=WON)
     * 
     * @param Int $projectid The id of the project to retrieve all decisions from
     * @param String $filter - URL parameters to filter or sort results ( name=MyVariatn&sort=-id )
     * @return Object Containing the list of decisions with their respective details for the given project
     */
    public function getDecisions($projectid, $filter = FALSE, $decisiongroupid = -1) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "account/$this->clientid/project/$projectid";
        $path .= $decisiongroupid != -1 ? "/decisiongroup/$decisiongroupid" : "";
        $path .= "/decisions" . $f;
        return self::performRequest($path);
    }

    /**
     * Returns all data given a decision id
     * 
     * The decision object contains the same data returned for the list of decisions in the previous method
     * only that this is for a single decision.
     * 
     * @param Int $projectid the id of the project to retrieve the decision info from
     * @param Int $decisionid the id of the decision
     * @param Int $decisiongroupid (optional) If the decision is part of a group, the group id has to be passed
     * @return Object Containing all details for the specified decision for the given project
     */
    public function getDecision($projectid, $decisionid, $filter = FALSE, $decisiongroupid = -1) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "account/$this->clientid/project/$projectid";
        $path .= $decisiongroupid != -1 ? "/decisiongroup/$decisiongroupid" : "";
        $path .= "/decision/$decisionid" . $f;
        return self::performRequest($path);
    }

    /**
     * Creates a new decision which will be associated with the given project
     * 
     * @param Int $projectid - the project ID which decisions belongs to
     * @param Array $decision - contains each decision field to be created in the DB
     * @param Int $decisiongroupid (optional) If the decision is part of a group, the group id has to be passed
     * @return Object Containing the new created decision id
     */
    public function createDecision($projectid, $decision, $decisiongroupid = -1) {
        $path = "account/$this->clientid/project/$projectid";
        $path .= $decisiongroupid != -1 ? "/decisiongroup/$decisiongroupid" : "";
        $path .= "/decision";
        return self::performRequest($path, 'POST', $decision);
    }

    /**
     * Updates a decision with the data passed as parameter
     * 
     * @param Int $projectid The ID of the project to update the decision for
     * @param Int $decisionid The ID of the decision to be updated
     * @param Array $decision - contains all required fields to be updated in the DB
     * @param Int $decisiongroupid (optional) If the decision is part of a group, the group id has to be passed
     * @return Object Containing the response from the server after trying to edit the decision details
     */
    public function updateDecision($projectid, $decisionid, $decision, $decisiongroupid = -1) {
        $path = "account/$this->clientid/project/$projectid";
        $path .= $decisiongroupid != -1 ? "/decisiongroup/$decisiongroupid" : "";
        $path .= "/decision/$decisionid";
        return self::performRequest($path, 'PUT', $decision);
    }

    /**
     * Deletes a decision given a project and a decision ID
     * 
     * @param Int $projectid The ID of the project to delete the decision for
     * @param Int $decisionid The ID of the decision to be deleted
     * @param Int $decisiongroupid (optional) If the decision is part of a group, the group id has to be passed
     * @return Object Containing the response from the server after trying to delete the given decision
     */
    public function deleteDecision($projectid, $decisionid, $decisiongroupid = -1) {
        $path = "account/$this->clientid/project/$projectid";
        $path .= $decisiongroupid != -1 ? "/decisiongroup/$decisiongroupid" : "";
        $path .= "/decision/$decisionid";
        return self::performRequest($path, 'DELETE');
    }

    /**
     * Returns an object with a list of goals assigned to a particular project
     * 
     * @param Int $projectid The ID of the project to retrieve the goals for
     * @param Int $groupid (Optional), the page group id that the goal is associated to
     * @return Object Containing the list of goals with their respective details for the given project
     */
    public function getGoals($projectid, $filter = FALSE, $groupid = -1) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "account/$this->clientid/project/$projectid/group/$groupid/goals" . $f;
        return self::performRequest($path);
    }

    /**
     * gets a particular goal information
     * 
     * @param Int $projectid The ID of the project to retrieve the goal data for
     * @param Int $goalid The ID od the goal itself
     * @param Int $groupid (Optional), the page group id that the goal is associated to
     * @return Object Containing all details for the specified goal for the given project
     */
    public function getGoal($projectid, $goalid, $groupid = -1) {
        $path = "account/$this->clientid/project/$projectid/group/$groupid/goal/$goalid";
        return self::performRequest($path);
    }

    /**
     * Creates a relation betwen a goal and a project
     * 
     * @param Int $projectid The ID of the project to assign the goal for
     * @param Array $goal The ID of the goal itself
     * @param Int $groupid (Optional), the page group id that the goal is associated to
     * @return Object Containing  the new created goal id
     */
    public function createGoal($projectid, $goal, $groupid = -1) {
        $path = "account/$this->clientid/project/$projectid/group/$groupid/goal";
        return self::performRequest($path, 'POST', $goal);
    }

    /**
     * Updates  a goal entry with the new data passed as parameter
     * 
     * @param Int $projectid The ID of the project to update the goal for
     * @param Int $goalid The ID of the goal to be related to the given project
     * @param Array $goal Contains all required data for the goal
     * @param Int $groupid (Optional), the page group id that the goal is associated to
     * @return Object Containing the response from the server after trying to update the goal details for the given project
     */
    public function updateGoal($projectid, $goalid, $goal, $groupid = -1) {
        $path = "account/$this->clientid/project/$projectid/group/$groupid/goal/$goalid";
        return self::performRequest($path, 'PUT', $goal);
    }

    /**
     * Set a goals as "archived" from a project 
     * 
     * @param Int $projectid The ID of the project to delete the goal for
     * @param Int $goalid The ID of the goal to be deleted
     * @param Int $groupid (Optional), the page group id that the goal is associated to
     * @return Object Containing the response from the server after trying to delete a goal from a given project
     */
    public function deleteGoal($projectid, $goalid, $groupid =  -1) {
        $path = "account/$this->clientid/project/$projectid/group/$groupid/goal/$goalid";
        return self::performRequest($path, 'DELETE');
    }

    /**
     * re-activates a goal that had been "deleted" (archived)
     * 
     * @param Int $projectid The ID of the project to reactivate the goal for
     * @param Int $goalid The ID of the goal to be reactivated
     * @param Int $groupid (Optional), the page group id that the goal is associated to
     * @return Object Containing the response from the server after trying to reactivate the goal for a given project
     */
    public function reactivateGoal($projectid, $goalid, $groupid =  -1) {
        $path = "account/$this->clientid/project/$projectid/group/$groupid/goal/$goalid/reactivate";
        return self::performRequest($path, 'POST');
    }

    /**
     * Gets an object containing a list of rules for the logged client
     * 
     * If the client is a tenant, he can get the rules that has been created by himself or by all of his
     * clients.
     * 
     * @return Object Containing a list of rules with their respective details
     */
    public function getRules() {
        $path = "account/$this->clientid/rules";
        return self::performRequest($path);
    }

    /**
     * Gets all data for a particular rule
     * 
     * @param Int $ruleid The ID of the rule to retrieve the data for
     * @return Object Containing all details for a specific rule
     */
    public function getRule($ruleid) {
        $path = "account/$this->clientid/rule/$ruleid";
        return self::performRequest($path);
    }

    /**
     * Creates a new rule with the respective data sent as parameter
     * 
     * @param Array $rule Contains the rule required data (name, operation)
     * @return Object Containing  the new created rule id
     */
    public function createRule($rule) {
        $path = "account/$this->clientid/rule";
        return self::performRequest($path, 'POST', $rule);
    }

    /**
     * Given a rule ID updates it data.
     * 
     * The $rule array contains the name of the rule and the operation (AND/OR)
     * 
     * @param Int $ruleid The ID of the rule to be updated
     * @param Array $rule Contains the rule required data
     * @return Object Containing the response from the server after trying to update the details for the given rule
     */
    public function updateRule($ruleid, $rule) {
        $path = "account/$this->clientid/rule/$ruleid";
        return self::performRequest($path, 'PUT', $rule);
    }

    /**
     * Given a rule ID, sends a request to be deleted from the DB
     * 
     * @param Int $ruleid The ID of the rule to be deleted
     * @return Object Containing  the response from the server after trying to delete the specified rule
     */
    public function deleteRule($ruleid) {
        $path = "account/$this->clientid/rule/$ruleid";
        return self::performRequest($path, 'DELETE');
    }

    /**
     * Returns an object with a list of conditions and the respective data.
     * 
     * Each element of the response object contains the value of the "negation" field (boolean), 
     * the type (String) and the arguments (String).
     * 
     * @param Int $ruleid
     * @return Object Containing a list of conditions with their respective details for a given rule
     */
    public function getConditions($ruleid) {
        $path = "account/$this->clientid/rule/$ruleid/conditions";
        return self::performRequest($path);
    }

    /**
     * Returns all data for the given condition.
     * 
     * Returned object contains the value of the "negation" field (boolean), the type (String) and the 
     * arguments (String).
     * 
     * @param Int $ruleid The ID of the rule that the condition belongs to
     * @param Int $conditionid The ID of the condition itself
     * @return Object Containing all details given a condition for the specified rule
     */
    public function getCondition($ruleid, $conditionid) {
        $path = "account/$this->clientid/rule/$ruleid/condition/$conditionid";
        return self::performRequest($path);
    }

    /**
     * creates a new condition for the given rule
     * 
     * The condition array (second parameter) has to contain the valu of the negation attribute (Boolean), 
     * the type of the condition(String) and optionally the arguments(String)
     * 
     * @param Int $ruleid The ID of the rule to create the condition for
     * @param Array $condition An array containing all required fields to create a new condition
     * @return Object Containing  the new created condition id
     */
    public function createCondition($ruleid, $condition) {
        $path = "account/$this->clientid/rule/$ruleid/condition";
        return self::performRequest($path, 'POST', $condition);
    }

    /**
     * Updates all or some of the data for a particular decision
     * 
     * @param Int $ruleid The ID of the rule that the condition belogs to
     * @param Int $conditionid The ID of the condition to be updated
     * @param Array $condition Contains the required data to update the condition in the DB
     * @return Object Containing the response from the server after trying to update the details for a condition
     */
    public function updateCondition($ruleid, $conditionid, $condition) {
        $path = "account/$this->clientid/rule/$ruleid/condition/$conditionid";
        return self::performRequest($path, 'PUT', $condition);
    }

    /**
     * Given a rule ID and a condition ID, deletes the particular condition which is part of the given rule
     * 
     * @param Int $ruleid The ID of the rule that the condition belongs to
     * @param Int $conditionid The ID of the condition to be deleted
     * @return Object Containing the response from the server after trying to delete a condition for a given rule
     */
    public function deleteCondition($ruleid, $conditionid) {
        $path = "account/$this->clientid/rule/$ruleid/condition/$conditionid";
        return self::performRequest($path, 'DELETE');
    }

    /**
     * Returns an object containing statistical data for the given project
     * 
     * In the server, the project and its decisions are evaluated to determine the amount of impressions,
     * conversions and aggregated conversion rate to create an object with statistical data for a period
     * of time.
     * Users can filter results to retrieve a custom number of entries, to define an end date or a particular
     * goal ID, e.g: getTrend(456, entries=50,enddate=2015-01-31,goalid=74);
     * This will return statistics for the project with ID = 456 which goal id =  74
     *  and a total of 50 entries from 2014-12-21 to 2015-01-31 .
     * 
     * @param Int $projectid The Id of the project to return the statistics for
     * @param String $filter users can use custom filters to restrict the returned results
     * @param Int $decisiongroupid to support teasertests
     * @return Object Containing a set of date points and details for a period of time given a project id
     */
    public function getTrend($projectid, $filter = FALSE, $decisiongroupid = -1) {
        $f = '';
        if ($filter) {
            $filter = str_replace(' ', '%20', $filter);
            $f = '/?' . str_replace('?', '', $filter);
        }
        $path = "account/$this->clientid/project/$projectid";
        $path .= $decisiongroupid != -1 ? "/decisiongroup/$decisiongroupid" : "";
        $path .= "/trend" . $f;
        return self::performRequest($path);
    }

    /**
     * Perform request to the server with CURL
     * 
     * Initializes and complements a CURL resource with all necessary data to perform a request to the API
     * including the URL of the resource, the proxy (if any), authentication credentials for the user, http
     * header with the corresponding content type (json). then performs the request and sends the response
     * to the method "handleResponse" to be verified.
     * 
     * @param string path relative path with the corresponding key/value pairs to complement the api url
     * @param string method either GET, POST, PUT or DELETE
     * @param string body set of parameters to be sent with the request (Valid for POST and PUT)
     * @return Object Containing the response from the server after handling the request
     */
    private function performRequest($path = '', $method = 'GET', $body = '') {
        $meth = strtoupper($method);
        if(isset($this->apiurl))
            $fullURL = $this->apiurl . $path;
        else
            $fullURL = API_URL . $path;
        $this->conn = curl_init($fullURL);
        if (isset($this->proxyurl)) {
            curl_setopt($this->conn, CURLOPT_PROXY, $this->proxyurl);
        }

        curl_setopt($this->conn, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->conn, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($this->conn, CURLOPT_TIMEOUT_MS, $this->timeout);
        curl_setopt($this->conn, CURLOPT_FAILONERROR, false);
        curl_setopt($this->conn, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->conn, CURLOPT_USERPWD, "$this->apikey:$this->apisecret");
        curl_setopt($this->conn, CURLOPT_HTTPHEADER, array(
            "Accept:application/json",
            "Content-type: application/json",
        ));

        curl_setopt($this->conn, CURLOPT_CUSTOMREQUEST, $meth);
        if ($meth == 'POST' || $meth == 'PUT') {
            curl_setopt($this->conn, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $res = curl_exec($this->conn);
        return self::handleResponse(json_decode($res), $meth, $path);
    }

    /**
     * Verifies the response from the server after performing a request
     * 
     * Depending on the http method sent, the http code in the response is verified to detect if there was
     * an error, if so, an exception is thrown, otherwise, the response object is returned to the user.
     * 
     * @param Object $res the response from the API
     * @param String $meth the http method (POST, GET, PUT, DELETE)
     * @return Object the response from the server
     * @throws Exception in case the http code is not as expected depending in the http method.
     */
    private function handleResponse($res, $meth, $path) {
        $httpCode = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);
        curl_close($this->conn);
        $exception = FALSE;
        switch ($meth) {
            case 'POST':
                $exception = ($httpCode != 200 && $httpCode != 201) ? TRUE : FALSE;
                break;
            case 'PUT':
                $exception = $httpCode != 200 ? TRUE : FALSE;
                break;
            case 'DELETE':
                $exception = $httpCode != 200 ? TRUE : FALSE;
                break;
            default:
                $exception = $httpCode != 200 ? TRUE : FALSE;
                break;
        }

        if ($exception) {
            throw new Exception("$res->message", $res->code);
        }
        return $res;
    }

}
