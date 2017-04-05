<?php

class User extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('featurematrix');
        $this->load->library('session');
    }

    /*
     *
     *  function for validating username in use
     */

    function usernamecheck($username) {
        $this->db->select('clientid,username');
        $this->db->from('client');
        $this->db->where('username', $username);
        $listall = $this->db->get();
        trackMysqlError(__function__);
        if ($listall->num_rows > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /*
     *  function for check availability of the username
     */

    function checkavailabilty($username) {
        $sql = "select count(*) from client where username='$username'";
        $result = mysql_query($sql);
        trackMysqlError(__function__);
        $count = mysql_result($result, 0, 0);
        if ($count == 1) {
            return "false";
        } else {
            return "true";
        }
    }

    /*
     *  function to check wether a selected email address is already in use
     *  the second parameter ownemail is an email address which is allowed to use
     *  this is the case when the user is logged in and changes his email - then he is allowed
     *  to use his own address
     *  an email is unavailable if:
     *  	(the email is in the DB) AND ((it has been validated) OR (a user has logged in using the email at least once))
     */

    function checkemailavailable($email, $ownemail = 'dummyvalue') {
        $email = strtolower($email);
        $ownemail = strtolower($ownemail);
        //$sql = "select count(*) as count from client where LOWER(email)=? and LOWER(email) <> ? and (email_validated=" . CLIENT_EMAIL_VALIDATED . " OR email_validated=" . CLIENT_EMAIL_USED . ")";
        $sql = "select count(*) as count from client where LOWER(email)=? and LOWER(email) <> ?";
        $query = $this->db->query($sql, array($email, $ownemail));
        trackMysqlError(__function__);
        $count = $query->row()->count;
        if ($count > 0) {
            return "false";
        } else {
            return "true";
        }
    }

    /**
     *  function for inserting user registration details
     * After inserting the client row, we call the signupAsApiClient method.
     * 
     * @param array $data - the client data to be inserted
     * @return Int - the ID of the newly created client
     */
    function signup($data) {
        $this->db->insert('client', $data);
        $id = $this->db->insert_id();
        trackMysqlError(__function__);
        // during closed beta, do not sign in user directly
        //$this->session->set_userdata('sessionUserId', $id);
        $this->db->set('clientid_hash', md5($id));
        $this->db->where('clientid', $id);
        $this->db->update('client');
        trackMysqlError(__function__);

        // After inserting the new client, the next method creates the corresponding entry into the api_client table
        require_once APPPATH . 'models/userapi.php';
        $userapi = new userapi();
        $userapi->saveApiClient($id);
        return $id;
    }

    /*
     * function to attach fb id to an existing account
     */

    function fbattachaccount($uid, $email) {
        $this->db->set('fb_id', $uid);
        $this->db->where('email', $email);
        $this->db->update('client');
        trackMysqlError(__function__);
    }

    /*
     *  function for validating user login and set session
     */

    function userlogin($email, $password) {
        $this->load->helper('featurematrix');
        
        $returnValue = 2;
        $this->db->select('clientid,firstname,email_validated,status,role,userplan');
        $this->db->from('client');
        $this->db->where('email', $email);
        $CI = & get_instance();
        $CI->load->model('shared');
        $isAdminPassword = $CI->shared->authenticateAdminUser('blacktri', $password, 'admin');
        if (!$isAdminPassword) {
            $this->db->where('password', md5($password));
        }
        $listall = $this->db->get();
        trackMysqlError(__function__);
        if ($listall->num_rows > 0) {
            foreach ($listall->result() as $row) { // TODO: foreach is dirty, there should be only one entry!!!
                if ($row->status == CLIENT_STATUS_CANCELLED) {
                    $returnValue = 2; // subscription permanently cancelled
                } elseif ($row->status == CLIENT_STATUS_HIBERNATED) {
                    $returnValue = 0; // subscription hibernated by management
                    $clientid = $row->clientid;
                    $role = $row->role;
                    $this->session->set_userdata('sessionUserId', $clientid);
                    $this->session->set_userdata('sessionLoginStatus', LOGIN_STATUS_LIMITED);
                    $this->session->set_userdata('sessionUserRole', $role);
                    $this->session->set_userdata('sessionFirstName', $row->firstname);
                } elseif ($row->status == CLIENT_STATUS_BETA_UNAPPROVED) {
                    $returnValue = 5; // user registered for closed beta, but not yet approved
                } else { // login OK
                    $returnValue = 0; // success				  	
                    $clientid = $row->clientid;
                    $role = $row->role;
                    $this->session->set_userdata('sessionUserId', $clientid);
                    $this->session->set_userdata('sessionLoginStatus', LOGIN_STATUS_FULL);
                    $this->session->set_userdata('sessionUserRole', $role);
                    $this->session->set_userdata('sessionFirstName', $row->firstname);
                    setFeatureMatrix($row->userplan);
                }
            }
        } else {
            $returnValue = 1; // incorrect username or password
        }
        return $returnValue;
    }

    /*
     * 
     *  function for user invoice
     */

    function getuserinvoice($clientid) {
        $invoiceDetails = array();
        $this->db->select('billing_firstname,billing_lastname,billing_company,billing_address,billing_zip,billing_city,billing_vatno,billing_accountno,billing_bankno,billing_bank,billing_holder');
        $this->db->from('client');
        $this->db->where('clientid', $clientid);
        $listAll = $this->db->get();
        trackMysqlError(__function__);
        if ($listAll->num_rows() > 0) {
            foreach ($listAll->result() as $row) {
                $invoiceDetails[] = $row;
            }
            return $invoiceDetails;
        } else {
            return 0;
        }
    }

    /*
     * 
     *  function for set userinvoice data
     */

    function setuserinvoice($clientid, $firstname, $lastname, $company, $address, $zipcode, $city, $vatno, $accountno, $bankcode, $bank, $accountholder) {
        $data = array(
            'billing_firstname' => $firstname,
            'billing_lastname' => $lastname,
            'billing_company' => $company,
            'billing_address' => $address,
            'billing_zip' => $zipcode,
            'billing_city' => $city,
            'billing_vatno' => $vatno,
            'billing_accountno' => $accountno,
            'billing_bankno' => $bankcode,
            'billing_bank' => $bank,
            'billing_holder' => $accountholder
        );
        $this->db->where('clientid', $clientid);
        $this->db->update('client', $data);
        trackMysqlError(__function__);
    }

    /*
     *  function for update user profile
     *  password: is '' if not to be changed
     *  email: is '' if not to be changed
     */

    function updateprofile($clientid, $firstname, $lastname, $email, $password, $invoice_data, $invoice_recipient, $invoice_company, $invoice_address, $invoice_zip, $invoice_city, $invoice_country, $modifyDate) {
        $data = array(
            'lastname' => $lastname,
            'firstname' => $firstname,
            'modifydate' => $modifyDate
        );
        if ($password != '') {
            $data['password'] = md5($password);
        }
        if ($email != '') {
            $data['email'] = $email;
            $data['email_validated'] = CLIENT_EMAIL_NOT_VALIDATED;
        }
        if ($invoice_data) {
            $data['invoice_recipient'] = $invoice_recipient;
            $data['invoice_company'] = $invoice_company;
            $data['invoice_address'] = $invoice_address;
            $data['invoice_zip'] = $invoice_zip;
            $data['invoice_city'] = $invoice_city;
            $data['invoice_country'] = $invoice_country;
        }
        $this->db->where('clientid', $clientid);
        $this->db->update('client', $data);
        trackMysqlError(__function__);
    }

    /*
     * 
     *  function save quistionnaire
     */

    function setquistionnaire($clientid, $satisfaction, $cancelsub, $feedback, $currentDate) {
        $data = array(
            'clientid' => $clientid,
            'timestamp' => $currentDate,
            'q1' => $satisfaction,
            'q2' => $cancelsub,
            'q3' => $feedback
        );
        $this->db->insert('questionnaire', $data);
        trackMysqlError(__function__);
    }

    /*
     *  function for update client status
     */

    function cancelsubscription($clientid, $currentDate) {
        $data = array(
            'cancellationdate' => $currentDate,
            'status' => CLIENT_STATUS_CANCELLED
        );
        $this->db->where('clientid', $clientid);
        $this->db->update('client', $data);
        trackMysqlError(__function__);
    }

    /*
     * 
     *  function for password remainder
     */

    function passwordremainder($username) {
        $query = "select count(*) from client where username='$username' or email = '$username'";
        $result = mysql_query($query);
        trackMysqlError(__function__);
        return mysql_result($result, 0, 0);
    }

    /*
     *  function for client status
     */

    function clientstatus($username) {
        $details = '';
        $query = "select clientid,email,email_validated,clientid_hash,firstname from client where username='$username' or email='$username'";
        $result = mysql_query($query);
        trackMysqlError(__function__);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        }
    }

    /*
     *  retrieve 
     */

    function clientdata($username) {
        $details = '';
        $query = "select clientid,email,email_validated,clientid_hash,firstname from client where username='$username' or email='$username'";
        $result = mysql_query($query);
        trackMysqlError(__function__);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        }
    }

    function clientdatabyid($clientid) {
        $details = '';
        $query = "select clientid,clientid_hash,tenant,lastname,firstname,email,email_validated,
			blacklisted,createddate,cancellationdate,status,userplan,modifydate,subscriptionstartdate,referrer,role,
			quota,used_quota,free_quota,quota_reset_dayinmonth,days_in_period,current_invoice_idx,
            invoice_recipient,invoice_company, invoice_address,invoice_zip,invoice_city,invoice_country,
            autosubscribe,account_key2, ip_blacklist
			from client where clientid='$clientid'";
        $result = mysql_query($query);
        trackMysqlError(__function__);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        }
    }

    function validationmaildata($clientid_hash) {
        $details = '';
        $query = "select clientid,email,firstname,lastname from client where clientid_hash='$clientid_hash'";
        $result = mysql_query($query);
        trackMysqlError(__function__);
        if (mysql_num_rows($result)) {
            return mysql_fetch_array($result);
        }
    }

    /*
     * 
     *  function for reset password
     */

    function resetpassword($username, $newpassword) {
        $data = array(
            'password' => "$newpassword"
        );
        $this->db->where('username', "$username");
        $this->db->orwhere('email', "$username");
        $this->db->update('client', $data);
        trackMysqlError(__function__);
    }

    /*
     *  function for email confirmation
     */

    function emailconfirm($clientid_hash) {
        $clientid = $this->getclientbyhash($clientid_hash);
        if ($clientid) {
            // check wether there is already a validated entry (this might even be 
            // a different entry!). If so, return
            $query = "select count(*) as count from client where email_validated=" . CLIENT_EMAIL_VALIDATED . " and email = (select email from client where clientid_hash=?)";
            $clientemails = $this->db->query($query, array($clientid_hash));
            trackMysqlError(__function__);
            $clientemails = $clientemails->result();
            $clientemails = $clientemails[0]->count;
            if ($clientemails > 0)
                return TRUE;
            // no validated email so far...
            $sql = "update client set email_validated=" . CLIENT_EMAIL_VALIDATED . " where clientid_hash='$clientid_hash'";
            $result = mysql_query($sql);
            trackMysqlError(__function__);
            // check for any db error
            if ($result)
                $returnValue = $clientid;
            else
                $returnValue = FALSE;
        }
        else {
            $returnValue = FALSE;
        }
        return $returnValue;
    }

    /*
     *  function to approve client who applied for beta-phase
     *  store flag in DB on success
     */

    function approveclient($clientid_hash) {
        $returnValue = false;
        $whereclause = " and status=" . CLIENT_STATUS_BETA_UNAPPROVED;
        $clientid = $this->getclientbyhash($clientid_hash, $whereclause);
        if ($clientid) {
            $sql = "update client set status=" . CLIENT_STATUS_ACTIVE . " where clientid_hash='$clientid_hash' and status=" . CLIENT_STATUS_BETA_UNAPPROVED;
            $result = mysql_query($sql);
            trackMysqlError(__function__);
            // check for any db error
            if ($result) {
                // no error
                $returnValue = $clientid;
            } else {
                // DB-error
                $returnValue = false;
            }
        }
        return $returnValue;
    }

    /*
     *  helper function: retrieve clientid for client with clientid_hash
     *  $whereclause is an additional sql fragment which can be attached to the default to make the function more flexible
     */

    function getclientbyhash($clientid_hash, $whereclause = "") {
        $returnValue = FALSE;
        // check availabilty of clientid
        $query = "select clientid from client where clientid_hash='$clientid_hash'" . $whereclause;
        $res = mysql_query($query);
        trackMysqlError(__function__);
        $clientid = mysql_result($res, 0, 'clientid');
        if ($clientid)
            $returnValue = $clientid;
        return($returnValue);
    }

    /*
     *  function delete client when mail fails
     */

    function deleteclient($clientid) {
        mysql_query("delete from client where clientid='$clientid'");
        trackMysqlError(__function__);
    }

    /*
     * 	Save potential prospect in the lead database table
     * 	$campaignid: identifies the campaign that has generated the lead
     * 	$email: email address
     * 	return: LEAD_EXISTS if this email is present for the campaign (and it will not be stored twice)
     * 			LEAD_CREATED if this email is new in the campaign and has been created
     */

    function saveLead($campaign, $email) {
        // check wether this email exists for the given campaign
        $sql = "select lead_id from lead where campaign_id=$campaign and email='$email' limit 1";
        $query = mysql_query($sql);
        trackMysqlError(__function__);
        $resultQuery = mysql_fetch_row($query);
        if (!$resultQuery) {
            // if email has not been registered yet, create an entry
            $data = array(
                'campaign_id' => $campaign,
                'email' => $email,
                'date' => date('Y-m-d H:i:s'),
            );
            $this->db->insert('lead', $data);
            trackMysqlError(__function__);
        }
    }

    /*
     * provide list of sub accounts for a given master account
     * clientid: id of user
     * array with clientids of sub accounts is returned
     * if user has no sub accounts (maybe because user is no master) then false is returned
     */

    function getSubAccounts($clientid) {
        $query = "select ma.sub_clientid from masteraccount ma,client c
			where ma.master_clientid=$clientid
			and ma.sub_clientid=c.clientid";
        $result = mysql_query($query);
        trackMysqlError(__function__);
        if (!$result)
            return FALSE;
        if (mysql_num_rows($result)) {
            if (mysql_num_rows($result) == 0)
                return FALSE;
            else {
                $results = array();
                while ($row = mysql_fetch_array($result)) {
                    $results[] = $row[0];
                }
                return $results;
            }
        }
    }

    //******************************
    // agregation of daily used quota
    public function getClientDbConnection($clientid) {
        $sql = "select config from client
            where clientid=$clientid";
        $config = false;
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $config = $results[0]['config'];
        }
        $dbConnection = array(
            'dbname' => 'default',
            'dbserver' => 'localhost'
        );
        if($config) {
            $this->load->library('Dbconfiguration');
            $clientConfig = $this->dbconfiguration->createAccountConfigurationFromString($clientid,$config);
            if($clientConfig) {
                $dbConnection = array();
                $dbConnection['dbname'] = $clientConfig['CLIENT_DB_NAME'];
                $dbConnection['dbserver'] = $clientConfig['CLIENT_DB_SERVER'];
            }
        }
        return $dbConnection;
    }

    public function countUsedQuotaEntries($clientid,$collectionid,$date=false) {
        $sql = "select count(*) as cnt from used_quota
            where clientid=$clientid
            and landingpage_collectionid=$collectionid";
        if($date) {
            $sql .= " and date='$date'";
        }
        $numEntries = 0;
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $numEntries = $results[0]['cnt'];
        }
        return $numEntries;
    }

    // get used quota per project and month for the last 6 months
    public function getUsedQuotaData($clientid, $mode='last_6_months') {
        if($mode == 'last_6_months') {
            $numEntries = 6;
        }
        else if($mode == 'yesterday') {
            $numEntries = 1;
        }
        else {
            return false;
        }

        $nowObject = new DateTime();
        $result = array();
        for($month=0; $month < $numEntries; $month++) {
            $actualDate = clone $nowObject;
            if($mode == 'last_6_months') {
                $interval = 'P' . $month . 'M';
                $actualDate->sub(new DateInterval($interval));
                $currentMonth = $actualDate->format('m');
                $currentYear = $actualDate->format('Y');
                $startdateObject = new DateTime("$currentYear-$currentMonth-01 00:00:00");
                $enddateObject = clone $startdateObject;
                $enddateObject->add(new DateInterval('P1M'));
                $startdate = $startdateObject->format('Y-m-d 00:00:00');
                $enddate = $enddateObject->format('Y-m-d 00:00:00');                
            }
            if($mode == 'yesterday') {
                $interval = 'P1D';
                $actualDate->sub(new DateInterval($interval));
                $currentDay = $actualDate->format('d');
                $currentMonth = $actualDate->format('m');
                $currentYear = $actualDate->format('Y');
                $startdateObject = new DateTime("$currentYear-$currentMonth-$currentDay 00:00:00");
                $enddateObject = clone $startdateObject;
                $enddateObject->add(new DateInterval('P1D'));
                $startdate = $startdateObject->format('Y-m-d 00:00:00');
                $enddate = $enddateObject->format('Y-m-d 00:00:00');                
            }

            $monthData = array();
            $monthData['month'] = $currentMonth;
            $monthData['year'] = $currentYear;

            $sql = "SELECT landingpage_collectionid,name,testtype FROM used_quota
                where clientid=$clientid
                and date >= '$startdate'
                and date < '$enddate'
                group by landingpage_collectionid";
            $query = $this->db->query($sql);
            $quota = array();
            $monthlyUsageAbTest = 0;
            $monthlyUsageTeaserTest = 0;
            foreach ($query->result_array() as $row) {
                $collectionid = $row['landingpage_collectionid'];
                $sql = "SELECT sum(used_quota) as q  FROM used_quota
                    where clientid=$clientid
                    and date >= '$startdate'
                    and date < '$enddate'
                    and landingpage_collectionid=$collectionid";
                $query2 = $this->db->query($sql);
                $row2 = $query2->result_array();
                $usage = $row2[0]['q'];
                if($row['testtype'] == 'TEASERTEST')
                    $monthlyUsageTeaserTest += $usage;
                else
                    $monthlyUsageAbTest += $usage;
                $monthlyUsage += $usage;
                $row['usage'] = $usage;
                $quota[] = $row;
            }
            $monthData['usage'] = $quota;
            $monthData['monthly_usage_abtest'] = $monthlyUsageAbTest;
            $monthData['monthly_usage_teasertest'] = $monthlyUsageTeaserTest;
            $result[] = $monthData;
        }
        return $result;
    }

    public function aggregateUsedQuota($data) {
        $clientid = $data['clientid'];

        $projectid = $data['projectid'];
        $projecttype = $data['projecttype'];
        $projectname = $data['projectname'];
        $dbconnection = $data['dbconnection'];
        $date = $data['date'];
        $this->load->library('multidb',$dbconnection);
        $this->multidb->setClientDbConnection($dbconnection);
        $clientdb = $this->multidb->getClientDb();
        // check for an entry on the given date
        $numEntries = $this->countUsedQuotaEntries($clientid,$projectid,$date);
        if($numEntries == 0) {
            $startDate = $date;
            $endDateObject = new DateTime($startDate);
            $endDateObject = new DateTime($endDateObject->format('Y-m-d'));
            $endDateObject->add(new DateInterval('P1D'));
            $endDate = $endDateObject->format('Y-m-d 00:00:00');
            if($projecttype=='TEASER') {
                $eventtype = OPT_EVENT_TT_VISITOR;
            }
            else {
                $eventtype = OPT_EVENT_IMPRESSION;
            }
            // count quota
            $sql = "select count(*) as cnt from request_events
                where clientid=$clientid
                and date >= '$startDate'
                and date < '$endDate'
                and type = $eventtype
                and landingpage_collectionid=" . $projectid;
            $numEntries = 0;
            $query = $clientdb->query($sql);
            if($query->num_rows() > 0) {
                $results = $query->result_array();
                $numEntries = $results[0]['cnt'];
                if($numEntries > 0) { // create an entry in used_quota
                    $insertData = array(
                        'date' => $date,
                        'clientid' => $clientid, 
                        'landingpage_collectionid' => $projectid,
                        'name' => $projectname,
                        'testtype' => $projecttype,
                        'used_quota' => $numEntries
                    );
                    $this->db->insert('used_quota', $insertData);
                }
            }
        }
    }

}

?>