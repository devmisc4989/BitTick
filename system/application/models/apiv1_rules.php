<?php

/**
 * This class inherits from the apiv1_core class which have some common variables and methods
 * for all of its childs
 */
require_once 'apiv1_core.php';

final class apiv1_rules extends apiv1_core {

    protected $account;
    protected $rule;
    protected $condition;
    // "Negation" (indication) is pased as a boolean and needs to be translated to INT
    protected $negation_array = array(
        0 => TRUE,
        1 => FALSE,
    );
    // maps values between the ones that the user sets and the ones to be saved into the DB - for conditions
    protected $type_array = array(
        'REFERRER_CONTAINS' => array(
            'value' => 'referrer_contains',
            'arg' => 'text',
        ),
        'URL_CONTAINS' => array(
            'value' => 'url_contains',
            'arg' => 'text',
        ),
        'SEARCH_IS' => array(
            'value' => 'search_is',
            'arg' => 'text',
        ),
        'TARGETPAGE_OPENED' => array(
            'value' => 'targetpage_opened',
            'arg' => 'text',
        ),
        'SOURCE_IS' => array(
            'value' => 'source_is',
            'arg' => array(
                'type_in' => 'TYPE_IN',
                'social' => 'SOCIAL',
                'organic_search' => 'ORGANIC_SEARCH',
                'paid_search' => 'PAID_SEARCH',
            ),
        ),
        'DEVICE_IS' => array(
            'value' => 'device_wurfl_is',
            'arg' => array(
                'STR_CC_ATTR_VALUE_DEVICE_TYPE_MOBILE_PHONE' => 'MOBILE',
                'STR_CC_ATTR_VALUE_DEVICE_TYPE_TABLET' => 'TABLET',
                'STR_CC_ATTR_VALUE_DEVICE_TYPE_DESKTOP' => 'DESKTOP',
            ),
        ),
        'IS_RETURNING' => array(
            'value' => 'is_returning',
            'arg' => array(
                'STC_CC_ATTR_VALUE_VISITOR_TYPE_1' => TRUE,
                'STC_CC_ATTR_VALUE_VISITOR_TYPE_2' => FALSE,
            ),
        ),
    );
    // To get the appropriate DB field given the name that the client sends.
    protected $dbfields = array(
        'id' => 'r.rule_id',
        'name' => 'r.name',
        'operation' => 'r.operation',
    );
    // Depending on the name in the client side we can get the DB field for rule conditions
    protected $conditionfields = array(
        'id' => 'rc.rule_condition_id',
        'negation' => 'rc.indication',
        'type' => 'rc.type',
        'arg' => 'rc.arg',
    );
    // Mandatory fields for rule insertion
    protected $mandatoryfields = array(
        'name',
        'operation',
    );
    // Mandatory fields for rule conditions insertion
    protected $mandatorycondition = array(
        'negation',
        'type',
        'arg',
    );
    // read only fields for both rules and conditions
    protected $readonlyfields = array(
        'id',
    );

    function __construct() {
        parent::__construct();
        $this->load->helper('apiv1');
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
     * First it retrieves the personalization object saved into the DB (if any) and verifies that feature
     * "personalization" is available for the current client-
     * 
     * See: apiv1_core->setParametersReturnMethod()
     * it verifies that the requested method exists and returns its name or throws an exception
     * 
     * @param type $uri - the URL mapped as an array
     * @return the result after calling the corresponding method
     * @throws Exception - in case "personalization" is not available in the feature array for the user.
     */
    public function index($uri) {
        $method = $this->setParametersReturnMethod($uri);
        
        $features = json_decode($this->getclientFeatures());
        if (json_last_error() == JSON_ERROR_NONE) {
            if (isset($features->personalization) && !$features->personalization) {
                throw new Exception('personalization', 403006);
            }
        }

        return self::$method();
    }

    /*     * ******************************************************************************
     * **************************** GET RULES related methods ******************************
     * ********************************************************************************* */

    /**
     * Plural "getRules()" 
     * gets all rules for the current client or all clients of the current tenant and returns a success response
     * @return JSON
     */
    private function getRules($ruleid = FALSE) {

        $this->db->select('r.rule_id, r.name, r.operation')
                ->from('rule r')
                ->order_by('r.name', 'ASC');

        if (is_numeric($ruleid) && $ruleid > 0) {
            $this->db->where('r.rule_id', $ruleid);
        }

        if ($this->usertype == 'api-tenant') {
            $this->db->join('api_client ac', 'ac.clientid = r.clientid', 'INNER')
                    ->where('ac.clientid', $this->account)
                    ->where('(ac.api_tenant = ' . $this->apiclientid . ' OR ac.api_clientid = ' . $this->apiclientid . ')');
        } else {
            $this->db->where('r.clientid', $this->clientid);
        }

        $query = $this->db->get();

        if ($ruleid && $query->num_rows() <= 0) {
            throw new Exception('', 404001);
        }

        $rules = array();
        foreach ($query->result() as $rule) {
            $c = array();
            $c['id'] = $rule->rule_id;
            $c['name'] = $rule->name;
            $c['operation'] = $rule->operation;
            $rules[] = $c;
        }

        $ret = $ruleid ? $rules[0] : $rules;
        return parent::successResponse(200, $ret);
    }

    /**
     * Singular "getRule()"
     * given a rule id, returns all related data after calling the method "getRules" with the rule ID as parameter
     * @return JSON
     */
    private function getRule() {
        $ruleid = is_numeric($this->rule) ? $this->rule : 0;
        return self::getRules($ruleid);
    }

    /*     * ******************************************************************************
     * ************************* GET CONDITIONS related methods ***************************
     * ********************************************************************************* */

    /**
     * Given a value, returns the corresponding "TYPE" value that the client can set:
     *      i.e. if $type is "device_wurfl_is", the returned value is "DEVICE_IS"
     * @param String $type - the value to search in the mapping array
     * @return String
     */
    private function getMappedType($type) {
        foreach ($this->type_array as $key => $value) {
            if ($value['value'] == $type) {
                return $key;
            }
        }
        return FALSE;
    }

    /**
     * Given a condition argument, returns the corresponding "ARG" value that the client can set:
     *      i.e. if $arg is "STR_CC_ATTR_VALUE_DEVICE_TYPE_MOBILE_PHONE", the returned value is "MOBILE"
     * @param type $type - the type name as of given by the client (DEVICE_IS...)
     * @param String $arg - the value to search in the mapping array
     * @return String
     */
    private function getMappedArg($type, $arg) {
        $args = $this->type_array[$type]['arg'];
        if (is_array($args)) {
            foreach ($args as $key => $value) {
                if ($key == $arg) {
                    return $value;
                }
            }
        }
        return $arg;
    }

    /**
     * Verifies the rule ID and the user credentials first, if the user is not the owner of the rule, returns an error
     * Then gets all conditions for the given rule and returns the results
     * If parameter $cond is set, it only returns the data for that only condition
     * @param type $cond - if it is called from "getcondition" it only returns the data for the given condition ID
     * @return JSON
     * 
     */
    private function getConditions($cond = FALSE) {
        $this->clientOwnsRule();
        $conditions = array();

        $this->db->select('rule_condition_id, indication, type, arg')
                ->from('rule_condition')
                ->where('rule_id', $this->rule)
                ->order_by('rule_condition_id', 'ASC');

        if ($cond) {
            $this->db->where('rule_condition_id', $cond);
        }

        $query = $this->db->get();

        if ($query->num_rows() <= 0) {
            throw new Exception('', 404001);
        }

        foreach ($query->result() as $q) {
            $r = array();

            $r['id'] = $q->rule_condition_id;
            $r['negation'] = $q->indication == 0 ? TRUE : FALSE;
            $r['type'] = self::getMappedType($q->type);
            $r['arg'] = self::getMappedArg($r['type'], $q->arg);
            $conditions[] = $r;
        }

        $ret = $cond ? $conditions[0] : $conditions;
        return $this->successResponse(200, $ret);
    }

    /**
     * Calls the method "getConditions()" with the given condition id as parameter.
     * @return JSON
     */
    private function getCondition() {
        $cond = is_numeric($this->condition) ? $this->condition : 0;
        return self::getConditions($cond);
    }

    /*     * ******************************************************************************
     * ********************** COMMON METHODS for post and put RULES ***********************
     * ********************************************************************************* */

    /**
     * Works for both, rules and conditions.
     * it goes throught everyposted fields and verifies if is its valu is valid (calling the helper functions)
     * if everything is OK and the field names are set in the dbfields array returns an array
     * ready to be inserted or updated into the DB
     * @param String $table - either "rule" or "condition"
     * @param Int $ccode
     * @return Array
     * @throws Exception - if there are invalid field parameters
     */
    private function setQueryArray($table = 'rule') {
        $post_array = json_decode($this->requestParameters);
        $ret_array = array();
        $fields = $table == 'rule' ? $this->dbfields : $this->conditionfields;

        $invalid_field = '';
        $invalid_value = '';

        foreach ($post_array as $key => $value) {

            $helper = 'rule_valid_' . $key;

            if (!array_key_exists($key, $fields)) {
                $invalid_field .= "$key, ";
                continue;
            } else if ($key == 'arg') {
                $type = $post_array->type;
                $invalid_value .= (!$helper($type, $value)) ? $key . ': "' . $value . '", ' : '';
            } else if (function_exists($helper) && !$helper($value)) {
                $invalid_value .= $key . ': "' . $value . '", ';
                continue;
            }

            $k = split('\.', $fields[$key]);
            $fieldarray = $key . '_array';

            if ($key == 'type') {
                $val = $this->type_array[$post_array->type]['value'];
            } else if ($key == 'arg') {
                $args = $this->type_array[$type]['arg'];
                $val = is_array($args) ? array_search($value, $args) : $value;
            } else {
                $val = (is_array($this->{"$fieldarray"})) ? array_search($value, $this->{"$fieldarray"}) : $value;
            }

            $ret_array[$k[1]] = $val;
        }

        if (trim($invalid_field) != '') {
            throw new Exception($invalid_field, 400003);
        }
        if (trim($invalid_value) != '') {
            throw new Exception($invalid_value, 400004);
        }

        return $ret_array;
    }

    /*     * *************************************************************************** */

    /**
     * it verifies that mandatory fields are set and no read-only fuelds were set by the client
     * then builds the array representing the new rule to be inserted and verifies the query result
     * if the insert_id is in returns success, or error otherwise.
     * @return JSON
     * @throws Exception - if the inserted rule id is not int or it is < 0 (there is an error)
     */
    private function postRule() {
        $this->checkMandatoryFields(FALSE, 400400);
        $this->checkReadOnlyFields(FALSE, 400401);
        $rule = self::setQueryArray('rule');

        $rule['clientid'] = $this->account;
        $this->db->insert('rule', $rule);
        $ruleid = $this->db->insert_id();

        if (!is_int($ruleid) || $ruleid <= 0) {
            throw new Exception('Rule could not be created', 500);
        }
        $this->rule = $ruleid;
        $this->optimisation->flushAPCCacheForRule($this->rule);
        return $this->successResponse(200, $ruleid);
    }

    /**
     * Updates a rule after validating that no read-only fields has been set by the user
     * @return JSON
     */
    private function putRule() {
        $this->clientOwnsRule();

        $this->checkReadOnlyFields(FALSE, 400401);
        $rule = self::setQueryArray('rule');

        $this->db->where('rule_id', $this->rule)
                ->update('rule', $rule);
        $this->optimisation->flushAPCCacheForRule($this->rule);
        return $this->successResponse(200);
    }

    /**
     * Deletes all conditions for the given rule and then, the rule itself.
     * @return JSON
     */
    private function deleteRule() {
        $this->clientOwnsRule();

        $this->db->where('rule_id', $this->rule)
                ->delete('rule_condition');

        $this->db->where('rule_id', $this->rule)
                ->delete('rule');

        $this->optimisation->flushAPCCacheForRule($this->rule);
        return $this->successResponse(200);
    }

    /*     * ******************************************************************************
     * ******************** COMMON METHODS for post and put CONDITION ********************
     * ********************************************************************************* */

    /**
     * retrieves all condition for the current rule and creates the corresponding PHP code to be evaluated
     * when users visits the client's page
     * @return String - The phpcode ready to be inserted in the corresponding field in the DB (table rule)
     */
    private function updatePhpCode() {
        $conds = self::getRuleInfo();
        $phpcode = '';
        foreach ($conds as $cond) {
            if (($cond['type'] == 'targetpage_opened') || ($cond['type'] == 'insert_basket')) {
                $cond['arg'] = $cond['rule_condition_id'];
            }

            $op = ($cond['operation'] == 'AND') ? '&&' : '||';
            $phpcode .= (strlen($phpcode) > 0) ? $op . ' ' : '';
            $phpcode .= ((int) $cond['indication'] == 0) ? ' !' : '';
            $phpcode .= '$this->prs_' . $cond['type'] . '("' . $cond['arg'] . '") ';
        }

        $this->db->where('rule_id', $this->rule)
                ->set('phpcode', '$persoResult=' . $phpcode . ';')
                ->update('rule');
    }

    /**
     * returns all necessary data from rule and rule_condition to create/update the phpcode for the current RULE
     * @return ARRAU
     */
    private function getRuleInfo() {
        $query = $this->db->select('r.operation, c.indication, c.type, c.arg, c.rule_condition_id')
                ->from('rule r')
                ->join('rule_condition c', 'c.rule_id = r.rule_id')
                ->where('r.rule_id', $this->rule)
                ->get();

        return $query->result_array();
    }

    /*     * ******************************************************************************
     * ******************** POST, PUT and DELETE methods for rule conditions ********************
     * ********************************************************************************* */

    /**
     * Given a rule ID, inserts a new condition for it
     * It first verifies that all mandatory fields has been set and no read-only fields are set
     * @return JSON
     * @throws Exception - in case there is an error trying to insert the condition
     */
    private function postCondition() {
        $this->clientOwnsRule();

        $this->checkMandatoryFields($this->mandatorycondition, 400500);
        $this->checkReadOnlyFields(FALSE, 400501);
        $condition = self::setQueryArray('condition');

        $condition['rule_id'] = $this->rule;
        $this->db->insert('rule_condition', $condition);

        $conditionid = $this->db->insert_id();
        if (!is_int($conditionid) || $conditionid <= 0) {
            throw new Exception('Condition could not be created', 500);
        }

        self::updatePhpCode();
        $this->optimisation->flushAPCCacheForRule($this->rule);
        return $this->successResponse(200, $conditionid);
    }

    /**
     * Given a condition id, updates its data into the DB
     * first it verifies that no read-only fields has been set
     * @return JSON
     */
    private function putCondition() {
        $conditionid = is_numeric($this->condition) ? $this->condition : 0;

        $this->clientOwnsRule();
        $this->checkReadOnlyFields(FALSE, 400501);
        $condition = self::setQueryArray('condition');

        $this->db->where('rule_condition_id', $conditionid)
                ->update('rule_condition', $condition);

        self::updatePhpCode();
        $this->optimisation->flushAPCCacheForRule($this->rule);
        return $this->successResponse(200);
    }

    /**
     * Deletes the condition given its ID
     * @return JSON
     */
    private function deleteCondition() {
        $this->clientOwnsRule();

        $this->db->where('rule_condition_id', $this->condition)
                ->where('rule_id', $this->rule)
                ->delete('rule_condition');

        self::updatePhpCode();
        $this->optimisation->flushAPCCacheForRule($this->rule);
        return $this->successResponse(200);
    }

}
