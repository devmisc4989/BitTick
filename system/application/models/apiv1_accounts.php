<?php

/**
 * This class inherits from the apiv1_core class which have some common variables and methods
 * for all of its childs
 */
require_once 'apiv1_core.php';

final class apiv1_accounts extends apiv1_core {

    protected $account;
    protected $status_array = array(
        0 => 'UNSET',
        1 => 'ACTIVE',
        2 => 'CANCELLED',
        3 => 'HIBERNATED',
        6 => 'FULL'
    );
    // To get the appropriate DB field given the name that the client sends.
    protected $dbfields = array(
        'id' => 'c.clientid',
        'subid' => 'c.subid',
        'publicid' => 'c.clientid_hash',
        'custom1' => 'c.account_key2',
        'custom2' => 'c.account_collectionid',
        'custom3' => 'c.custom',
        'password' => 'c.password',
        'email' => 'c.email',
        'emailvalidated' => 'c.email_validated',
        'firstname' => 'c.firstname',
        'lastname' => 'c.lastname',
        'status' => 'c.status',
        'plan' => 'c.userplan',
        'features' => 'c.features',
        'createddate' => 'c.createddate',
        'quota' => 'c.quota',
        'usedquota' => 'c.used_quota',
        'freequota' => 'c.free_quota',
        'quotaresetdayinmonth' => 'c.quota_reset_dayinmonth',
        'ipblacklist' => 'c.ip_blacklist',
        'apikey' => 'ac.apikey',
        'apisecret' => 'ac.apisecret',
        'trackingcode' => NULL,
    );
    // Field that are mandatory when creating accounts
    protected $mandatoryfields = array(
        'status',
        'quota'
    );
    // When creating or editing accounts, tenants can't set the next fields because they are read-only
    protected $readonlyfields = array(
        'id',
        'apikey',
        'usedquota',
        'createddate',
        'quotaresetdayinmonth',
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
     * See: apiv1_core->setParametersReturnMethod()
     * it verifies that the requested method exists and returns its name or throws an exception
     */
    public function index($uri) {
        $method = $this->setParametersReturnMethod($uri);
        return self::$method();
    }

    /*     * ******************************************************************************
     * *************** COMMON METHODS (could apply for all actions (post, get, ...) ***************
     * ********************************************************************************* */

    /**
     * Verifies that the current logged client is the tenant for the given account (accountid)
     * @param Int $accountid
     * @return boolean
     * @throws Exception
     */
    private function IsClientTenant() {
        $query = $this->db->select('clientid')
                ->from('api_client')
                ->where('api_client_type', 2)
                ->where('clientid', $this->account)
                ->where('api_tenant', $this->apiclientid)
                ->get();

        if ($query->num_rows <= 0) {
            throw new Exception('', 403103);
        }
        return TRUE;
    }

    /*     * ******************************************************************************
     * ****************** COMMON METHODS FOR getAccount and getAccounts*******************
     * ********************************************************************************* */

    /**
     * If the URL hasn't the parameter "fields" (that sets custom DB fields to be retrieved), then the select
     *      statement will retrieve all the available fields for the Accounts Overview
     * builds the query and complements it by calling addParametersToQuery() - to add the custom filters
     *      and sort order stablished by the client in the url (for example: lastname=id&status=active)
     *      Only if the user is a tenant and he is not trying to retrieve only one account info
     * Runs the query and returns the result
     */
    private function accountQuery($client = FALSE) {
        if (!$this->requestParameters['fields'] || $client) {
            $this->db->select('c.clientid, c.subid, c.account_key2, c.account_collectionid, c.custom, c.clientid_hash, ' .
                    ' c.password, c.email, c.email_validated, c.firstname, c.lastname, c.status, c.userplan, ' .
                    ' c.features, c.createddate, c.quota, c.used_quota, c.quota_reset_dayinmonth, ' .
                    ' c.free_quota, c.ip_blacklist, ac.apikey, ac.apisecret ');
        }

        $ac = "(SELECT clientid, apikey, apisecret FROM api_client WHERE api_client_type = 2 ";
        $ac .= $this->usertype == 'api-client' ? " AND api_clientid = $this->apiclientid " : " AND api_tenant = $this->apiclientid ";
        $ac .= " GROUP BY clientid) ac ";

        $this->db->from('client c')
                ->join($ac, 'ac.clientid = c.clientid', 'INNER');

        if ($client) {
            $this->db->where('c.clientid', $client)
                    ->limit(1);
        } else {
            self::addParametersToQuery();
        }

        return $this->db->get();
    }

    /**
     * As the user (tenant) can select custom fields, this method determines if the response should have all
     * available fields or just the ones sent by the user in the URL 
     * for example  accounts?fields=id,firstname,lastname
     * The field "ID" is always required, so if it was not set by the user, it is added automatically when
     * retrieving data from the DB and here to return the corresponding value
     * 
     * @param array $q - a query result->row() array
     */
    private function customAccountFields($q) {
        $r = array();
        if ($this->requestParameters['fields'] && $this->usertype == 'api-tenant') {
            $fields = split(',', $this->requestParameters['fields']);

            foreach ($fields as $key) {

                if (!array_key_exists($key, $this->dbfields)) {
                    throw new Exception($key, 400003);
                }

                $k = $this->dbfields[$key];
                $keys = split('\.', $k);

                $r[$key] = ($key == 'trackingcode') ? self::getTrackingCode() : $q->$keys[1];
            }
        } else {
            $r['id'] = $q->clientid;
            $r['subid'] = $q->subid;
            $r['publicid'] = $q->clientid_hash;
            $r['custom1'] = $q->account_key2;
            $r['custom2'] = $q->account_collectionid;
            $r['custom3'] = $q->custom;
            $r['firstname'] = $q->firstname;
            $r['lastname'] = $q->lastname;
            $r['ipblacklist'] = $q->ip_blacklist;
            $r['email'] = $q->email;
            $r['emailvalidated'] = $q->email_validated;
            $r['firstname'] = $q->firstname;
            $r['lastname'] = $q->lastname;
            $r['status'] = $this->status_array[$q->status];
            $r['plan'] = $q->userplan;
            $r['createddate'] = $q->createddate;
            $r['quota'] = $q->quota;
            $r['usedquota'] = $q->used_quota;
            $r['freequota'] = $q->free_quota;
            $r['quotaresetdayinmonth'] = $q->quota_reset_dayinmonth;
            $r['ipblacklist'] = $q->ip_blacklist;
            $r['apikey'] = $q->apikey;
            $r['trackingcode'] = self::getTrackingCode($q->clientid_hash);

            if ($this->usertype == 'api-tenant') {
                $r['password'] = $q->password;
                $r['features'] = json_decode($q->features);
                $r['apisecret'] = $q->apisecret;
            }
        }
        return $r;
    }

    /**
     * If it is called without the publicid as parameter we need to get it from the DB by calling getPublicId()
     * @param String $publicid - clientid_hash
     * @return String - the tracking code to be added to the client page
     */
    private function getTrackingCode($publicid = FALSE) {
        if (!$publicid) {
            $publicid = self::getPublicId();
        }
        return sprintf($this->config->item('trackingcode'), $publicid, $this->config->item('trackinglib_host'));
    }

    /**
     * If getAccount is called with the parameter "trackingcode" but without the parameter "publicid", it is
     * necessary to return it from the DB to build the tracking code string
     * @return String
     */
    private function getPublicId() {
        $query = $this->db->select('clientid_hash')
                ->from('client')
                ->where('clientid', $this->clientid)
                ->get();

        return $query->row()->clientid_hash;
    }

    /*     * ******************************************************************************
     * ********************* (PLURAL) GET ACCOUNTS RELATED METHODS **********************
     * ********************************************************************************* */

    /**
     * Only allowed for TENANTS
     * call the "accountQuery" method to build and run the corresponding query, then organize the
     * result in an array and depending on if the user wants all accounts or only one, it returns
     * the corresponding object
     * 
     * @return JSON
     */
    private function getAccounts() {
        if ($this->usertype != 'api-tenant') {
            return $this->errorResponse(403103);
        }

        $accounts = array();
        $query = self::accountQuery();

        foreach ($query->result() as $q) {
            $r = self::customAccountFields($q);
            $accounts[] = $r;
        }

        return $this->successResponse(200, $accounts);
    }

    /*     * ******************************************************************************
     * ********************* (SINGULAR) GET ACCOUNT RELATED METHODS ********************
     * ********************************************************************************* */

    /**
     * Verifies that the logged user has access to the given account, if so, builds the quiery
     * by calling the  "accuntquery()" method, organizes the result and return it as array
     * 
     * the method "clientOwnsAccount" will throw an exception in case the logged user can't access
     * the account id set in the URL
     * @return JSON
     */
    private function getAccount() {
        $account = array();
        $this->clientOwnsAccount();

        $clientid = is_numeric($this->account) ? $this->account : 0;
        $query = self::accountQuery($clientid);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $q) {
                $r = self::customAccountFields($q);
                $account = $r;
            }
        } else {
            throw new Exception('', 403103);
        }

        return $this->successResponse(200, $account);
    }

    /*     * ******************************************************************************
     * ******************* COMMON METHODS for POST and PUT account ************************
     * ********************************************************************************* */

    /**
     * goes throught every element in the requestParameters array to create a valid client and 
     * apiclient objects to be inserted or updated  later in the DB
     * @return array -  an array containing the client and apiclient arrays
     * @throws Exception - if there are invalid field parameters
     */
    private function setAccountArray() {
        $post_array = json_decode($this->requestParameters);
        $apifields = array('apisecret');
        $apiclient = array();
        $client = array();

        $invalid_field = '';
        $invalid_value = '';

        foreach ($post_array as $key => $value) {
            $helper = 'account_valid_' . $key;

            if (function_exists($helper) && !$helper($value) || (!array_key_exists($key, $this->dbfields))) {
                $invalid_value .= $key . ': "' . $value . '", ';
                continue;
            }

            $k = split('\.', $this->dbfields[$key]);
            $fieldarray = $key . '_array';
            $val = (is_array($this->{"$fieldarray"})) ? array_search($value, $this->{"$fieldarray"}) : $value;

            if (in_array($key, $apifields)) {
                $apiclient[$k[1]] = $val;
            } else if (array_key_exists($key, $this->dbfields)) {
                $client[$k[1]] = $key == 'features' ? json_encode($val) : $val;
            } else {
                $invalid_field .= "$key, ";
            }
        }

        if (trim($invalid_field) != '') {
            throw new Exception($invalid_field, 400003);
        }
        if (trim($invalid_value) != '') {
            throw new Exception($invalid_value, 400004);
        }

        return array('client' => $client, 'apiclient' => $apiclient);
    }

    /**
     * Verifiesif there is an "email" paramater in the parameters and if it is not assigned to any other client
     *   for the current tenant
     * If a clientid is send as parameter (EDITION), it only verifies other client accounts.
     * @param Int $clientid
     * @return boolean
     * @throws Exception in case the email is not unique for clients with the same tenant
     */
    private function validUniqueEmail() {
        $params = json_decode($this->requestParameters);

        if (array_key_exists('email', $params)) {

            if (trim($params->email) == '') {
                return TRUE;
            }

            $this->db->select('c.clientid')
                    ->from('client c')
                    ->join('api_client ac', 'ac.clientid = c.clientid', 'INNER')
                    ->where('c.email', $params->email)
                    ->where('ac.api_tenant', $this->apiclientid);

            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                throw new Exception('', 400105);
            }
        }
    }

    /**
     * Verifies that the publicid is unique for all clients.
     * If a clientid is send as parameter (EDITION), it only verifies other client accounts.
     * @param Int $clientid
     * @return boolean
     * @throws Exception - in case the public id is not unique within all clients
     */
    private function validUniquePublicid() {
        $params = json_decode($this->requestParameters);

        if (array_key_exists('publicid', $params)) {

            if (trim($params->publicid) == '') {
                return TRUE;
            }

            $this->db->select('clientid')
                    ->from('client')
                    ->where('clientid_hash', $params->publicid);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                throw new Exception('', 400104);
            }
        }
    }

    /*     * ******************************************************************************
     * *************** POST ACCOUNT RELATED METHODS - Create account data ******************
     * ********************************************************************************* */

    /**
     * Only allowed for TENANTS
     * Verifies that the requestParameters array has the mandatory fields and does not have any
     * read-only fields  or throws an exception (see checkMandatoryFields and checkReadOnlyFields)
     * Also, verifies the uniqueness of the public ID and the email address.
     * the generates the client and api_client arrays to be inserted later
     * It first inserts the new client row entry and if the query is succesful calls the corresponding method
     * to insert the new row in the table api_client
     * @return JSON
     * @throws Exception - is the user is not a tenant or if there is an error inserting the new account
     */
    private function postAccount() {

        if ($this->usertype != 'api-tenant') {
            throw new Exception('', 403102);
        }

        $this->checkMandatoryFields(FALSE, 400100);
        $this->checkReadOnlyFields(FALSE, 400101);
        $this->validUniquePublicid();
        $this->validUniqueEmail();
        $accountArray = self::setAccountArray();

        $client = $accountArray['client'];
        $apiclient = $accountArray['apiclient'];

        $today = getdate();
        $resetday = $today['mday'] > 28 ? 28 : $today['mday'];

        $client['createddate'] = date('Y-m-d H:i:s');
        $client['used_quota'] = 0;
        $client['quota_reset_dayinmonth'] = $resetday;

        $this->db->insert('client', $client);
        $clientid = $this->db->insert_id();

        if (!is_int($clientid) || $clientid <= 0) {
            throw new Exception('The account could not be created', 500);
        } else if (!array_key_exists('clientid_hash', $client)) {
            $md5 = md5($clientid);
            $hash = array('clientid_hash' => base_convert($md5, 10, 36));
            $this->db->where('clientid', $clientid)
                    ->update('client', $hash);
        }

        $this->flushClientCache($clientid);
        return self::insertApiClient($apiclient, $clientid);
    }

    /**
     * Adds the client_type (2), the tenant ID (the current user) and the clientid to the array, then 
     * performs an insert query and verifies the result to return the corresponding response
     * If the insertion went well.
     * @param Array $apiclient - array of key - value pairs to be inserted in the api_client table
     * @param Int $clientid
     * @return JSON
     * @throws Exception - if there is an error creating the new apiclient row
     */
    private function insertApiClient($apiclient, $clientid) {
        $apiclient['api_client_type'] = 2;
        $apiclient['api_tenant'] = $this->apiclientid;
        $apiclient['clientid'] = $clientid;

        $this->db->insert('api_client', $apiclient);
        $api_clientid = $this->db->insert_id();

        if (!is_int($api_clientid) || $api_clientid <= 0) {
            throw new Exception('the API account could not be created', 500);
        } else {
            self::verifyApiKey($api_clientid);
        }

        return $this->successResponse(200, $clientid);
    }

    /**
     * Once the api_client has been registered, this method gets the inserted ID and tries to update 
     * the corresponding row with its CRC32 hash as its APIKEY, if the query fails, calls itself with a second
     * parameter called "recursive" so it can track a maximum number of calls.
     * If the parameter "recursive" is set, it adds the microtime and a random number to the api_clientid so it can
     * ensure uniqueness before tring to update the APIKEY again.
     * @param Int $api_clientid - the last inserted api clientid
     * @param bool/int $recursive - keeps track of a maximum number of recursive calls
     * @throws Exception - in case the recursion limit is reached and the row can't be updated
     */
    private function verifyApiKey($api_clientid, $recursive = FALSE) {
        $m = $api_clientid;

        if ($recursive && $recursive < 10) {
            $m .= '-' . microtime(TRUE) . '-' . rand(1, 9999);
        } else if ($recursive) {
            throw new Exception(' (account::verifyApiKey) recursion limit exceeded', 400106);
        }

        $query = $this->db->select('clientid')
                ->from('api_client')
                ->where('apikey', hash('crc32', $m))
                ->get();

        if ($query->num_rows() > 0) {
            dblog_debug('APIKEY is already in use by clientid: ' . $query->row()->clientid);
            $rec = is_numeric($recursive) ? $recursive++ : 1;
            self::verifyApiKey($api_clientid, $rec);
        } else {
            $hash = array('apikey' => hash('crc32', $m));
            $this->db->where('api_clientid', $api_clientid)
                    ->update('api_client', $hash);
        }
    }

    /*     * ******************************************************************************
     * ***************** PUT ACCOUNT RELATED METHODS - Edit Account Data *******************
     * ********************************************************************************* */

    /**
     * Depending on the usertype, calls the corresponding method.
     * @return JSON -- returned by the corresponding method.
     */
    private function putAccount() {
        if ($this->usertype == 'api-tenant') {
            return self::updateAccountByTenant();
        } else {
            return self::updateOwnerAccount();
        }
    }

    /**
     * If the current user is a tenant, he can edit his client's accounts.
     * first it verifies that the account to be edited is part of the tenat's clients
     * There are just a few read-only fields for tenants and some other that has to be unique
     * @return JSON
     */
    private function updateAccountByTenant() {
        self::isClientTenant();

        $this->checkReadOnlyFields(FALSE, 400101);
        $this->checkReadOnlyFields(array('publicid'), 400103);
        $this->validUniquePublicid();
        $this->validUniqueEmail();

        $res = self::setAccountArray();

        $this->db->where('clientid', $this->account)
                ->update('client', $res['client']);

        $this->db->where('clientid', $this->account)
                ->update('api_client', $res['apiclient']);

        $this->flushClientCache($this->account);
        return $this->successResponse(200);
    }

    /**
     * Verifies that only available fields has been set by the client and updates the corresponding 
     * row in the DB
     * the method "clientOwnsAccount" will throw an exception in case the logged user can't access
     * the account id set in the URL
     * @throws Exception - if there are fields that can't be updated by a client.
     */
    private function updateOwnerAccount() {
        $this->clientOwnsAccount();
        $available = array('firstname', 'lastname', 'ipblacklist');

        $post_array = json_decode($this->requestParameters);
        $invalid = '';
        $client = array();

        foreach ($post_array as $key => $value) {
            if (in_array($key, $available) && array_key_exists($key, $this->dbfields)) {
                $k = split('\.', $this->dbfields[$key]);
                $client[$k[1]] = $value;
            } else {
                $invalid .= $key . ', ';
            }
        }

        if ($invalid != '') {
            throw new Exception($invalid, 400101);
        } else {
            $this->db->where('clientid', $this->clientid)
                    ->update('client', $client);

            $this->flushClientCache($this->clientid);
            return $this->successResponse(200);
        }
    }

}
