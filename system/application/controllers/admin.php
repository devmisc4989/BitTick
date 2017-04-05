<?php

class admin extends CI_Controller {

    private $sdk;

    function __construct() {
        parent::__construct();
        doAutoload(); // load resources from autoload_helper
        $this->load->model('adminmodel');
        $this->load->model('shared');
        $this->load->model('user');
        $this->load->model('optimisation');
        $this->load->model('landingpagecollection');
        define('API_URL', $this->config->item('base_ssl_url') . 'api/v1/', TRUE);
        require_once APPPATH . 'controllers/apiv1sdk.php';
        $this->sdk = new apiv1sdk();
        //$this->sdk->__set('proxyurl', "http://127.0.0.1:8888/");
        $this->sdk->__set('apikey', $this->config->item('bt_apikey'));
        $this->sdk->__set('apisecret', $this->config->item('bt_apisecret'));
    }

    function index() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/home');
        $this->load->view('admin/admin_footer');
    }

    function tc() {
        $code = substr(md5(uniqid()), 0, 4);
        setcookie("tracecode", $code, time() + 3600, "/");
        echo "Trace-Code: $code";
    }

    /*
     * create a transaction code to be attached to a page request. it will be passed to the BTO webservice and 
     * trigger logging of messages in the database table "logging" with level "TRACE"
     * after the code is created, redirect to the controller which displays logging entries by tracecode
     */

    function createTraceCode() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/createTraceCode');
        $this->load->view('admin/admin_footer');
    }

    function saveTraceCode() {
        if (!$this->isAuthenticated())
            exit;
        // check if tracecode is provided
        $code = $this->input->get('code');
        if ($code) {
            // if code is present, execute query
            $result = $this->adminmodel->setTracecode($code);
            redirect($this->config->item('base_ssl_url') . 'admin/traceLog/?code=' . $code);
        }
    }

    /**
     * New feature, Multivariate testing
     */
    function multiVariateTest() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/multiVariateTest');
        $this->load->view('admin/admin_footer');
    }

    /**
     * Create a new empty teaser test for a client
     */
    function createTeaserTest() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/createTeaserTest');
        $this->load->view('admin/admin_footer');
    }

    // execute the create
    function doCreateTeaserTest() {
        if (!$this->isAuthenticated())
            exit;
        $clientid = $this->input->get('clientid');
        $mainurl = $this->input->get('mainurl');
        $runpattern = $this->input->get('runpattern');
        $name = $this->input->get('name');
        $html = $this->load->view('admin/admin_header','',true);
        if ($clientid) {
            //$this->sdk->__set('proxyurl', "http://127.0.0.1:8888/");
            $this->sdk->__set('clientid', $clientid);
            try {
                $account = $this->sdk->getAccount($clientid);
                $myproject = array(
                    'type' => 'TEASERTEST',
                    'mainurl' => $mainurl,
                    'runpattern' => $runpattern,
                    'name' => $name,
                );
                $projectid = $this->sdk->createProject($myproject);
                $mygoal = array(
                    'type' => 'TIMEONPAGE',
                    'param' => '',
                    'level' => 'SECONDARY'
                );
                $goalid = $this->sdk->createGoal($projectid,$mygoal);
                $mygoal = array(
                    'type' => 'PI_LIFT',
                    'param' => '',
                    'level' => 'SECONDARY'
                );
                $goalid = $this->sdk->createGoal($projectid,$mygoal);
                $mygoal = array(
                    'type' => 'CLICK',
                    'param' => '',
                    'level' => 'SECONDARY'
                );
                $goalid = $this->sdk->createGoal($projectid,$mygoal);
                $mygoal = array(
                    'type' => 'COMBINED',
                    'param' => '',
                    'level' => 'PRIMARY'
                );
                $goalid = $this->sdk->createGoal($projectid,$mygoal);
                $this->sdk->startProject($projectid);
                $html .= "Teaser Test with ID $projectid created.";
            }
            catch (Exception $e) {
                $html .= $e->getMessage();
            }
        }
        $html .= $this->load->view('admin/admin_footer','',true);
        echo $html;
    }
    /**
     * New feature, log in on behalf of etracker client
     */
    // show form
    function etrackerLogin() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/etrackerLogin');
        $this->load->view('admin/admin_footer');
    }

    // execute the login
    function doEtrackerLogin() {
        if (!$this->isAuthenticated())
            exit;
        $accountid = $this->input->get('accountid');
        $this->load->library('OAuthLoader');
        if ($accountid) {
            //$url = $this->config->item('base_ssl_url') . "api/signin/$accountid/";
            $url = "https://application.etracker.com/dc/api/signin/$accountid/";
            $key = "etracker";
            $secret = $this->shared->getAPISecret('etracker');
            // call the api
            $consumer = new OAuthConsumer($key, $secret);
            $sig_method = new OAuthSignatureMethod_HMAC_SHA1;
            $token = null;  //token is null because we're doing 2-leg
            $req = OAuthRequest::from_consumer_and_token($consumer, $token, 'GET', $url);
            $req->sign_request($sig_method, $consumer, $token);
            $signedUrl = $req->to_url();
            redirect($signedUrl);
        }
    }

    /**
     * New feature, SMS Administration
     */
    function urlFilter() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/urlFilter');
        $this->load->view('admin/admin_footer');
    }

    /**
     * select client ID for configuratin
     */
    function configuration() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/configuration');
        $this->load->view('admin/admin_footer');
    }

    function configurationValues() {
        if (!$this->isAuthenticated())
            exit;
        $clientid = $this->input->get('clientid');
        $result = $this->adminmodel->getClientConfigurationValues($clientid);
        if($result != 'error') {
            if(is_array($result)) {
                $config = "";
                foreach($result as $key=>$value) {
                    $config .= "$key=$value\n";
                }
            }
            else {
                $config = "";                
            }
        }
        else {
            $config = 'error';
        }
        $data = array(
            'clientid'=>$clientid,
            'config'=>$config
        );
        $this->load->view('admin/admin_header');
        $this->load->view('admin/configurationValues',$data);
        $this->load->view('admin/admin_footer');
    }

    function saveConfigurationValues() {
        if (!$this->isAuthenticated())
            exit;
        $clientid = $this->input->post('clientid');
        $config = trim($this->input->post('config'));

        $lines = explode("\n",$config);
        $configArray = array();
        foreach($lines as $line) {
            $attribute = explode("=",$line);
            $configArray[$attribute[0]] = $attribute[1];
        }
        if(is_array($configArray)) {
            $clientcode = $this->adminmodel->saveClientConfigurationValues($clientid,$configArray);
            if($clientcode) {
                dblog_debug('--- reload getclientstatus, clientid_hash=' . $clientcode);
                $this->optimisation->getclientstatus($clientcode,true);
            }
        }
        $url = $this->config->item('base_ssl_url') . "admin/configurationValues?clientid=$clientid";
        redirect($url);    
    } 

    /**
     * select client ID for new database
     */
    function clientDatabase() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->view('admin/admin_header');
        $this->load->view('admin/clientDatabase');
        $this->load->view('admin/admin_footer');
    }

    function createClientDatabase() {
        if (!$this->isAuthenticated())
            exit;
        $clientid = $this->input->post('clientid');
        $error = false;

        // ensure that client does not have a specific DB and no configuration for this
        $result = $this->adminmodel->getClientConfigurationValues($clientid);
        if($result != 'error') {
            if(isset($result['CLIENT_DB'])) {
                $error = true;
                $message = "A database is already set for client $clientid: CLIENT_DB=" . $result['CLIENT_DB'];
            }
            else {
                $dbs = $this->adminmodel->getClientDatabaseNames();
                if(in_array($clientid, $dbs)) {
                    $error = true;
                    $message = "Database for this clientid exists.";                    
                }
                else {
                    $dbserver = $this->db->hostname;
                    $this->adminmodel->createClientDatabase($clientid,$dbserver); 
                    $result = $this->adminmodel->verifyClientDatabase($clientid);
                    if($result) {
                        // add configuration for new DB on client
                        $dbname = CLIENT_DB_PREFIX . $clientid;
                        $configArray = $this->adminmodel->getClientConfigurationValues($clientid);
                        $configArray['CLIENT_DB_NAME'] = $dbname;
                        $configArray['CLIENT_DB_SERVER'] = $dbserver;
                        $clientcode = $this->adminmodel->saveClientConfigurationValues($clientid,$configArray);
                        // flush cache
                        $client = $this->optimisation->flushAPCCacheForClient($clientcode);
                        $message = "Database successfully created.";                    
                    }
                    else {
                        $error = true;
                        $message = "Error while creating database.";                                            
                    }
                }
            }
        }
        else {
            $error = true;
            $message = "An errror occurred while reading configuration for client $clientid";
        }

        $output = $this->load->view('admin/admin_header','',true);
        if($error) {
            $output .=  "<h1>Error</h1>";
        }
        else {
            $output .=  "<h1>Success</h1>";            
        }
        $output .=  $message;
        $output .=  $this->load->view('admin/admin_footer','',true); 
        echo $output;           
    }     


    /*
     * Heartbeat function: used for monitoring to see if the application is alive and can access the database
     */

    function heartbeat() {
        $result = $this->adminmodel->heartbeat();
        if ($result)
            echo "OK";
        else
            echo "ERROR";
    }

    /*
     * display entries from the logging table which match a given tracecode
     */

    function traceLog() {
        if (!$this->isAuthenticated())
            exit;
        // check if tracecode is provided
        $code = $this->input->get('code');
        if ($code) {
            // if code is present, execute query
            $result = $this->adminmodel->getLogByTracecode($code);
        }
        $data = array();
        $data['tracecode'] = $code;
        $data['log'] = $result;
        $this->load->view('admin/admin_header');
        $this->load->view('admin/tracelog', $data);
        $this->load->view('admin/admin_footer');
    }

    /*
     * send one out of a series of mails for registered users which have not yet purchased a plan.
     * on certain days after registration, different kinds of mails are being sent
     * request: /admin/sendautorespondermail/<code>
     * <code>: specifies which kind of message to send.
     */

    function sendautorespondermail() {
        if (!$this->isAuthenticated())
            exit;

        // retrieve list of users to send mail to
        $code = $this->uri->segment(3);
        $autoresponder = $this->config->item('AUTORESPONDER');
        $daysSinceReg = $autoresponder[$code];
        $list = $this->adminmodel->getAutoresponderRecipients($code, $daysSinceReg, CLIENT_STATUS_ACTIVE);

        $log = "messagetype $code, " . count($list) . " recipients";
        dblog_message(LOG_LEVEL_INFO, LOG_TYPE_AUTORESPONDERMAIL, $log, -1);

        $mailsubject = $this->lang->line('auto_subject_' . $code);
        $text = $this->lang->line('auto_text_' . $code);
        $text = str_replace("#mailheader#", $this->load->view("genericmailheader", "", true), $text);
        $text = str_replace("<p>", "<p style=\"font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;\">", $text);
        $text = str_replace("<h1>", "<h1 style=\"font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:bold; color:#111;\">", $text);
        $text = str_replace("#mailfooter#", $this->load->view("genericmailfooter", "", true), $text);

        // send the mails
        $this->load->library('email');
        foreach ($list as $recipient) {
            $body = sprintf($text, $recipient['firstname']);
            if ($code == "PR_HELLO")
                $this->email->from($this->config->item('MAIL_ECKHARD_SENDER'), $this->config->item('MAIL_ECKHARD_NAME'));
            else
                $this->email->from($this->config->item('MAIL_SUPPORT_SENDER'), $this->config->item('MAIL_SUPPORT_NAME'));
            $this->email->to($recipient['email']);
            $this->email->bcc($this->config->item('ADMIN_EMAIL'));
            $this->email->subject($mailsubject);
            $this->email->set_mailtype('html');
            $this->email->message($body);
            $mail = $this->email->send();
            //echo($body);return;
            // mark this mail sent for this recipient
            $this->adminmodel->saveAutoresponderStatus($recipient['clientid'], $code);
        }
    }

    /*
     * calculate number of used quota for each client and store it in table client
     */

    function updateUsedQuota() {
        if (!$this->isAuthenticated())
            exit;
        $clientlist = $this->adminmodel->getActiveClients();
        //print_r($clientlist); 
        foreach ($clientlist as $client) {
            $data = $this->user->clientdatabyid($client[clientid]);
            // retrieve period to check for client
            if ($data['status'] == CLIENT_STATUS_ACTIVE)
                $mydate = $data['createddate'];
            else
                $mydate = $data['subscriptionstartdate'];
            if ($mydate == NULL)
                $mydate = $data['createddate'];
            $billingdate = computeBillingPeriod($mydate);
            $startdate = $billingdate['startdate'];
            $enddate = $billingdate['enddate'];
            $this->adminmodel->updateUsedQuota($client[clientid], $startdate, $enddate);
        }
    }

    /*
     * Reset used quota for clients
     */

    function resetUsedQuota() {
        if (!$this->isAuthenticated())
            exit;
        $this->adminmodel->resetQuotas();
        echo "// reset";
    }

    /*     * *************************************************MULTIVARIATE TEST ************************************************** */

    /*
     * Lists the tests where landingpage_collection type = 2
     */

    function listMvtTests() {
        if (!$this->isAuthenticated())
            exit;

        $order = ($this->input->post('order')) ? mysql_real_escape_string($this->input->post('order')) : 'creation_date DESC';
        $l1 = $this->input->post('l1');
        $l2 = $this->input->post('l2');
        $results = $this->adminmodel->getTestList($order, $l1, $l2);
        echo json_encode($results);
    }

    /*
     * When the user clics on edit, it send the lpc id to this controller, and accordign to that id, this returns the info
     * for that lpc including LP, mvt(factors, level, levels to page).
     */

    function listTestByLPC() {
        if (!$this->isAuthenticated())
            exit;

        $lpcid = $this->input->post('lpcid');
        $results = $this->adminmodel->getTestsByLPC($lpcid);
        echo json_encode($results);
    }

    /*     * ******************************************************************************************************************************
     * ****************************************** CREATE AND EDIT TESTS *******************************************************
     * ********************************************************************************************************************************* */

    /*
     * Creates a new MVT test
     * first it verifies that the client code exists into the DB, 
     * then creates the new landingpage_collection entry.
     * then the landing_pagesm MVT_factors, MVT_levels  entries if any
     */

    function createMvtTests() {
        if (!$this->isAuthenticated())
            exit;

        $isEdit = $this->input->post('isEdition') == 1 ? TRUE : FALSE;
        $edited = json_decode($this->input->post('edited'));
        $ccode = $this->input->post('ccode');
        $lcstatus = $this->input->post('lcstatus');
        $lcname = $this->input->post('lcname');
        $lpurl = $this->input->post('lpurl');
        $lpcanonical = $this->input->post('lpcanonical');
        $factors = json_decode($this->input->post('factors'));

        // Verifies that the client code exists in the DB
        $clid = (int) $this->adminmodel->verifyClientCode($ccode);
        if (!$clid) {
            echo 'ERROR: The Client Code does not exist (CCODE).';
            return;
        }

        // If it is a new test, Inserts a new row in the LPC table; Else, the lpc id was sent via ajax
        if (!$isEdit) {
            $lpcid = $this->adminmodel->createMvtLPC($lcname, $lcstatus, $clid);
            if (!is_int($lpcid) || $lpcid < 1) {
                echo 'ERROR: There was a problem trying to create the test (LPCID).';
                return;
            }

            // Inserts the "original" Landing_page for the test
            $lpdoriginal = $this->adminmodel->createMvtLP('Original', $lpurl, $lpcanonical, 1, NULL, $lpcid);
            if (!is_int($lpdoriginal)) {
                $this->adminmodel->deleteMvtLPC($lpcid);
                echo 'ERROR: There was a problem trying to create the test (LP_ORIGINAL).';
                return;
            }
        } else {
            // updates the LPC and control LP rows with possible changes.
            $lpcid = $this->input->post('lpcid');
            $this->adminmodel->updateMvtLPC($lcname, $lcstatus, $clid, $lpcid);
            $lpdoriginal = $this->input->post('lporiginal');
            $this->adminmodel->updateMvtLP($lpurl, $lpcanonical, $lpdoriginal);
        }

        // Updates the LPC with the BT-MD5(id) code
        $lpcode = 'BT-' . md5($lpcid);
        $this->adminmodel->updateCodeLPC($lpcode, $lpcid);


        ////////////////// Factor/Levels insert/update://///////////////////////

        if ($isEdit) {
            //if we are editing a test we need first to update the factor and levels before continue.
            self::editFactorsAndLevels($lpdoriginal, $lpcid, $edited);
        }

        $nameComb = array(); // array of combinations to insert later in the landing_page table.
        $codeComb = array(); // array of combinations to insert later in the landing_page table.

        foreach ($factors as $key => $value) {
            // Calls the model to insert each factor into the DB expecting an INT as resul (last inserted ID), if it's not, returns an error
            $factorid = $this->adminmodel->createMvtFactor($lpdoriginal, $value->name, $value->selector, $lpcid);
            if (!is_int($factorid) && !$isEdit) {
                $this->adminmodel->deleteMvtLPC($lpcid);
                echo 'ERROR: There was a problem trying to create the test (FACTOR).';
                return;
                break;
            }

            $level = 1; // Index to create the level names (v1, v2...)
            $nameComb[$key][0] = 'O';
            $codeComb[$key][0] = '';
            foreach ($value->levels as $k => $v) {
                $name = 'v' . $level;
                $nameComb[$key][$k + 1] = $value->name . ':' . $name;
                $codeComb[$key][$k + 1] = $v->code;

                // calls the model to insert each level per factor expecting the last inserted Id as result, if it's not, returns an error.
                $levelid = $this->adminmodel->createMvtLevel($factorid, $name, $v->code, $lpcid);
                if (!is_int($levelid) && !$isEdit) {
                    $this->adminmodel->deleteMvtLPC($lpcid);
                    echo 'ERROR: There was a problem trying to create the test (LEVEL).';
                    return;
                    break;
                }
                $level++;
            }
        }

        ////////////////////////////// NOW we will create the landing_page and mvt_level_to_page rows
        // combines the array of names and dom codes
        $lpname = self::combineMvt($nameComb);
        $lpcode = self::combineMvt($codeComb);

        // goes through the array of names to create a new row in the LP table per each combination
        foreach ($lpname as $key => $value) {

            // Concatenates every dom modification code combined in the $lpcode array & every name combined in the $lpname array
            $testname = '';
            $domcode = '';
            foreach ($value as $k => $v) {
                if ($v != 'O') {
                    if (strlen($testname) > 0) {
                        $domcode.= "\n";
                        $testname.= ",";
                    }
                    $testname .= $v;
                    $domcode .= $lpcode[$key][$k];
                }
            }

            // If the code is not an empty sttring (O, O, O combination) inserts the variants into the landing_page table
            if (strlen($domcode) > 0) {
                $fullcode = '{"[JS]":' . json_encode($domcode) . '}';

                // gets the last inserted LP id, if it is not an integer returns an error
                $lpd = $this->adminmodel->createMvtLP($testname, $lpurl, NULL, 2, $fullcode, $lpcid);
                if (!is_int($lpd) && !$isEdit) {
                    $this->adminmodel->deleteMvtLPC($lpcid);
                    echo 'ERROR: There was a problem trying to create the test (LP_VARIANT).';
                    return;
                    break;
                }
            }

            // Now goes through the array to create the mvt_level_to_page rows per  landing_page (1 to n)
            foreach ($value as $k0 => $v0) {
                if ($v0 != 'O') {
                    // gets the id of the level that matches the code and the 
                    $lev = $this->adminmodel->getMtvLevelByContent($lpcode[$key][$k0], $lpdoriginal);

                    // gets the last inserted mvt_level_to_page id, if it's not an integers, returns an error.
                    $ltpid = $this->adminmodel->createMvtLevelToPage($lpd, $lev, $lpcid);
                    if (!is_int($ltpid) && !$isEdit) {
                        $this->adminmodel->deleteMvtLPC($lpcid);
                        echo 'ERROR: There was a problem trying to create the test (MVT LTP).';
                        return;
                        break;
                    }
                }
            }
        }

        // set rotationslots to equidistant values
        $this->optimisation->updateSlotsWithoutProgressChange($lpcid);
        // Clear the cache
        $this->optimisation->flushCollectionCache($lpcid);
    }

    /*
     * if we are editing a test first we edit the current factors/levels 
     * and then return the result to continue creating the new combinations
     */

    function editFactorsAndLevels($lpdoriginal, $lpcid, $edited = array()) {
        foreach ($edited as $key => $value) {
            $this->adminmodel->updateMvtFactor($lpdoriginal, $value->name, $value->selector, $lpcid, $value->idf);

            $level = 1; // Index to create the level names (v1, v2...)
            $factor = $value->idf;
            foreach ($value->levels as $k => $v) {
                $name = 'v' . $level;
                $this->adminmodel->updateMvtLevel($name, $v->code, $lpcid, $factor, $v->idl);
                $level++;
            }
        }

        // gets the array of level_content/landing_page id pairs to update the dom_modification_code in each variant LP
        $lpcode = $this->adminmodel->LandingPageByLevel($lpcid);
        $newcode = '';
        $newname = '';

        for ($i = 0; $i < count($lpcode); $i++) {

            $newcode.= $lpcode[$i]['level_content'];
            $newname.= $lpcode[$i]['factor'] . ':' . $lpcode[$i]['name'];

            // if the next elements hasn't the same lp id, updates the current lp row with the code of the levels.
            if (!$lpcode[$i + 1] || $lpcode[$i]['landing_pageid'] != $lpcode[$i + 1]['landing_pageid']) {
                $fullcode = '{"[JS]":' . json_encode($newcode) . '}';
                $this->adminmodel->updateLpDomCode($newname, $fullcode, $lpcode[$i]['landing_pageid']);
                $newcode = '';
                $newname = '';
            } else {
                $newcode .= "\n";
                $newname .= ",";
            }
        }
    }

    /*
     * Function to combine the different levels per factor
     * taken from http://www.farinspace.com/php-array-combinations/
     */

    function combineMvt($data, &$all = array(), $group = array(), $val = null, $i = 0) {
        if (isset($val)) {
            array_push($group, $val);
        }

        if ($i >= count($data)) {
            array_push($all, $group);
        } else {
            foreach ($data[$i] as $v) {
                self::combineMvt($data, $all, $group, $v, $i + 1);
            }
        }

        return $all;
    }

    /*
     * Returns true if the $str is found in the array
     */

    function search_array($array, $str) {
        for ($i = 0; $i < count($array); $i++) {
            if (in_array($str, $array[$i])) {
                return true;
                break;
            }
        }
        return false;
    }

    /*
     * calls the model method to delete all the levels related to this factor id and then deletes the factor itself.
     */

    function deleteMvtFactor() {
        if (!$this->isAuthenticated())
            exit;

        $fact = $this->input->post('factor');
        $this->adminmodel->deleteMvtFactor($fact);
    }

    /*
     * Calls the model to delete the level and its associated level_to_page and landing_page rows
     */

    function deleteMvtLevel() {
        if (!$this->isAuthenticated())
            exit;

        $lev = $this->input->post('level');
        $this->adminmodel->deleteMvtLevel($lev);
    }

    /*
     * Deletes the entire MVT from LPC, LP, MVT, etc...
     */

    function deleteEntireMvt() {
        if (!$this->isAuthenticated())
            exit;

        $lpcid = $this->input->post('element');
        echo json_encode($this->adminmodel->deleteMvtLPC($lpcid));
    }

    /*     * **************************************************************************************************************************** */


    /*     * ***************************************************************************************************************************** 
     * ************************************************* SMART MESSAGING  *********************************************************** 
     * ******************************************************************************************************************************** */

    /*
     * Create the sms_template database table from iomport data
     */

    public function createTemplatesFromXml() {
        if (!$this->isAuthenticated())
            exit;
        echo "import start<br>";
        $this->config->load('sms');
        $templates = $this->config->item('sms_templates');
        $experimental_path = $this->config->item('experimental_file_path');

        foreach ($templates as $template) {
            //print_r($template);
            // load the xml file
            $filename = $experimental_path . "sms/xml/" . $template[1]['xml'];
            echo "import " . $template[1]['xml'] . "<br>";
            $myxml = file_get_contents($filename);
            if ($myxml === false) {
                echo "error reading XML for main template " . $template[1]['xml'];
                break;
            } else {
                echo "...done<br>";
            }

            // insert the mail include into the XML
            $mailxmlinclude = file_get_contents($experimental_path . "sms/xml/mail_include.xml");
            $myxml = str_replace("#mail_include#", $mailxmlinclude, $myxml);

            if (count($template[2][1]) <= 0) {
                echo "error reading CSS (Empty array)";
                break;
            } else {
                echo "...done<br>";
            }

            // insert css into xml
            $headcss = '<style>@import "#template_path#images/sms/css/cleanslate.css"; ';
            foreach ($template[2][1] as $css) {
                $headcss .= file_get_contents($experimental_path . "sms/css/" . $css);
            }
            $headcss .= '</style>';
            $myxml = str_replace("#css#", str_replace('\'', '', $headcss), $myxml);

            // create data object to save
            $data = array();
            $data['xml_content'] = str_replace("'", "\'", $myxml);
            $data['name'] = $template[0];
            //$data['description'] = $htmldescription;
            $data['description'] = $template[1]['description'];
            $data['message_type'] = $template[1]['message_type'];
            $data['content_type'] = $template[1]['content_types'];
            $data['thumbnail_url'] = $template[3];
            $data['previewimage_url'] = $template[4];
            $data['sort_order'] = $template[1]['sort_order'];
            $data['sms_template_group_id'] = $template[2][0];
            $blocked_userplans = isset($template['5']) ? $template['5'] : '';
            $data['blocked_userplans'] = $blocked_userplans;
            $this->adminmodel->saveSmsTemplate($data);
            //print_r($data);
        }

        echo "import end<br>";
    }

    /*
     * List the template groups to be shown in the corresponding table
     */

    function listTemplateGroups() {
        if (!$this->isAuthenticated())
            exit;

        $order = ($this->input->post('order')) ? mysql_real_escape_string($this->input->post('order')) : 'sort_order ASC';
        $l1 = $this->input->post('l1');
        $l2 = $this->input->post('l2');
        $results = $this->adminmodel->listTemplateGroups($order, $l1, $l2);
        echo json_encode($results);
    }

    /**
     * returns the array of arguments properly organized to be updated or inserted in table sms_template_group
     */
    private function getGroupArgs($row) {
        $order = (is_numeric((int) $row['sort_order'])) ? $row['sort_order'] : 0;
        $args = array(
            urldecode($row['thumbnail_url']),
            $order,
        );
        if ($row['sms_template_group_id']) {
            array_push($args, $row['sms_template_group_id']);
        }
        return $args;
    }

    /**
     * Updates the template group with the new thumbnail and sort_order for the given group id
     */
    function updateTemplateGroups() {
        if (!$this->isAuthenticated())
            exit;

        $row = $this->input->post('row');
        $this->adminmodel->updateTemplateGroups(self::getGroupArgs($row));
        echo json_encode(array(Result => 'OK'));
    }

    /**
     * Updates the template group with the new thumbnail and sort_order for the given group id
     */
    function createTemplateGroups() {
        if (!$this->isAuthenticated())
            exit;

        $row = $this->input->post('row');
        $this->adminmodel->createTemplateGroup(self::getGroupArgs($row));
        echo json_encode(array(Result => 'OK'));
    }

    /**
     * Calls the model method to delete a template group
     */
    function deleteTemplateGroup() {
        if (!$this->isAuthenticated())
            exit;

        $group = $this->input->post('element');
        echo json_encode($this->adminmodel->deleteTemplateGroup($group));
    }

    /**
     * Lists the tests where landingpage_collection type = 2
     */
    function listSmsTemplates() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->config('sms');
        $order = ($this->input->post('order')) ? mysql_real_escape_string($this->input->post('order')) : 'sort_order ASC';
        $l1 = $this->input->post('l1');
        $l2 = $this->input->post('l2');
        $results = $this->adminmodel->getSmsTemplates($order, $l1, $l2);
        echo json_encode($results);
    }

    /**
     * Given a template ID, lists the smart messages
     */
    function listSmsByTemplate() {
        if (!$this->isAuthenticated())
            exit;

        $order = ($this->input->post('order')) ? mysql_real_escape_string($this->input->post('order')) : 'clientid_hash ASC';
        $l1 = $this->input->post('l1');
        $l2 = $this->input->post('l2');
        $template = $this->input->get('template');
        $results = $this->adminmodel->listSmsByTemplate($template, $order, $l1, $l2);
        echo json_encode($results);
    }

    /**
     * returns the array of options to be displayed when selecting the message type for every template
     */
    function listSmsTypes() {
        $this->load->config('sms');
        $smstypes = $this->config->item('message_types');
        $smst = array();

        foreach ($smstypes as $t) {
            $smst[] = array(
                DisplayText => $t['value'],
                Value => $t['value'],
            );
        }
        echo json_encode(array(Result => 'OK', Options => $smst));
    }

    /**
     * Returns the array of  available groups (id's) to be shown in the <select>
     */
    function listSmsGroups() {
        $groups = $this->adminmodel->listSmsGroups();
        $smsg = array();
        foreach ($groups as $g) {
            $smsg[] = array(
                DisplayText => $g['sms_template_group_id'],
                Value => $g['sms_template_group_id'],
            );
        }
        echo json_encode(array(Result => 'OK', Options => $smsg));
    }

    /**
     * returns the array of content types
     */
    function listContentTypes() {
        $this->load->config('sms');
        $contenttypes = $this->config->item('content_types');
        $content = array();
        foreach ($contenttypes as $c) {
            $content[] = array(
                DisplayText => $c['value'],
                Value => $c['value'],
            );
        }
        echo json_encode(array(Result => 'OK', Options => $content));
    }

    /**
     * returns the array of arguments to be inserted/updated in the DB
     */
    function getTemplateArguments($row) {
        $order = (is_numeric((int) $row['sort_order'])) ? $row['sort_order'] : 0;
        $group = (is_numeric((int) $row['sms_template_group_id'])) ? $row['sms_template_group_id'] : 0;

        $this->load->config('sms');
        $contenttypes = $this->config->item('content_types');
        $content = '';
        foreach ($contenttypes as $c) {
            if ($row[$c['value']]) {
                $content.= (strlen($content) > 0) ? ';' : '';
                $content.= $c['value'];
            }
        }

        $args = array(
            urldecode($row['xml_content']),
            urldecode($row['name']),
            urldecode($row['message_type']),
            $content,
            urldecode($row['thumbnail_url']),
            urldecode($row['previewimage_url']),
            urldecode($row['description']),
            $group,
            $order
        );
        if ($row['sms_template_id']) {
            array_push($args, $row['sms_template_id']);
        }
        return $args;
    }

    /**
     * Calls the model method to insert a new row in sms_template
     */
    function createSmsTemplate() {
        if (!$this->isAuthenticated())
            exit;

        $row = $this->input->post('row');
        $this->adminmodel->createSmsTemplate(self::getTemplateArguments($row));
        echo json_encode(array(Result => 'OK'));
    }

    /**
     * Calls the model method to update the template in the DB
     */
    function editSmsTemplate() {
        if (!$this->isAuthenticated())
            exit;

        $row = $this->input->post('row');
        $this->adminmodel->updateSmsTemplate(self::getTemplateArguments($row));
        echo json_encode(array(Result => 'OK'));
    }

    /**
     * Calls the model method to delete a template given its ID
     */
    function deleteSmsTemplate() {
        if (!$this->isAuthenticated())
            exit;

        $template = $this->input->post('element');
        echo json_encode($this->adminmodel->deleteSmsTemplate($template));
    }

    /*     * **************************************************************************************************************************** */


    /*     * ***************************************************************************************************************************** 
     * ************************************************* PERSONALIZATION *********************************************************** 
     * ******************************************************************************************************************************** */

    /**
     * Lists the url_patterns located in the DB
     */
    function listUrlPatterns() {
        if (!$this->isAuthenticated())
            exit;

        $order = ($this->input->post('order')) ? mysql_real_escape_string($this->input->post('order')) : 'url ASC';
        $l1 = $this->input->post('l1');
        $l2 = $this->input->post('l2');
        $results = $this->adminmodel->getUrlPatterns($order, $l1, $l2);
        echo json_encode($results);
    }

    /**
     * Returns the argment array to be inserted/updated in url_filter
     */
    function getUrlArguments($row) {
        $args = array(
            urldecode($row['url']),
            urldecode($row['pattern']),
        );
        if ($row['id']) {
            array_push($args, $row['id']);
        }
        return $args;
    }

    /**
     * Calls the corresponding model method to insert a new url_filter
     */
    function insertUrlPatterns() {
        if (!$this->isAuthenticated())
            exit;

        $row = $this->input->post('row');
        $res = $this->adminmodel->insertUrlPattern(self::getUrlArguments($row));
        echo json_encode($res);
    }

    /**
     * Calls the corresponding model method to update the url_filter table with the appropriate argument array
     */
    function updateUrlPatterns() {
        if (!$this->isAuthenticated())
            exit;

        $row = $this->input->post('row');
        $res = $this->adminmodel->updateUrlPatterns(self::getUrlArguments($row));
        echo json_encode($res);
    }

    /**
     * Calls the corresponding model method to delete the url_filter row
     */
    function deleteUrlPatterns() {
        if (!$this->isAuthenticated())
            exit;

        $id = $this->input->post('element');
        echo json_encode($this->adminmodel->deleteUrlPattern($id));
    }

    /*     * **************************************************************************************************************************** */

    /*
     * produce a report on daily activities to be mailed to admin
     */

    function mailreport() {
        if (!$this->isAuthenticated())
            exit;
        $clients = $this->sdk->getAccounts();
        $output = "<table border='1'><tr><td>Kunde</td><td>Testname</td><td>Requests</td></tr>";
        foreach($clients as $client) {
            $clientid = $client->id;
            $clientName = $client->email;
            $data = $this->user->getUsedQuotaData($clientid,'yesterday');
            if(sizeof($data[0]['usage']) > 0) {
                foreach($data[0]['usage'] as $projectusage) {
                    $projectname = $projectusage['name'];
                    $quota = $projectusage['usage'];

                    $kunde = str_pad(substr($clientName, 0, 40), 40, ' ');
                    $testname = str_pad(substr($projectusage['name'], 0, 30), 30, ' ');
                    $requests = str_pad(substr($projectusage['usage'], 0, 5), 5, ' ');
                    $output .= "<tr><td>$kunde</td><td>$testname</td><td>$requests</td></tr>";

                } 
            }
        }
        $output .= "</table>";
        echo $output;
    }

    function logout() {
        $this->load->library('session');
        $this->session->set_userdata('isAdminAccess', FALSE);
    }

    /*
     * Helper function, manages simple authentication with Basic Auth
     */

    private function isAuthenticated() {
        $auth = $this->session->userdata('isAdminAccess');
        if ($auth) {
            if ($auth == TRUE) {
                return TRUE;
            }
        } else {
            if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];
                if ($this->shared->authenticateAdminUser($username, $password, 'admin')) {
                    $this->session->set_userdata('isAdminAccess', TRUE);
                    return TRUE;
                }
            }
        }
        header('WWW-Authenticate: Basic realm="My Realm"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Authorization error';
        exit;
    }

    /**
     * Generates a string with a defined config line for the given $filename as parameter (for perso regex)
     * (i.e: https://blacktri-dev.de/admin/generateRegexFromXml?file=searchengine)
     */
    public function generateRegexFromXml() {
        if (!$this->isAuthenticated())
            exit;
        $filename = $this->input->get('file');
        $xml = simplexml_load_file($this->config->item('base_ssl_url') . '/xml/' . $filename . '.xml');
        $conf = '';
        foreach ($xml->$filename as $element) {
            foreach ($element->regexp as $regex) {
                $conf .= strlen($conf) > 0 ? '|' : '$config["prs_' . $filename . '"] = \'~\b(';
                $conf .= $regex;
            }
        }
        $conf .= ")\b~i';";
        echo $conf;
    }

    /**
     * Specifically for searchengine.xml (maybe others later) 
     * to get the querystring parameter which contains the search term
     */
    public function generateArrayFromXml() {
        if (!$this->isAuthenticated())
            exit;
        $filename = $this->input->get('file');
        $xml = simplexml_load_file($this->config->item('base_ssl_url') . '/xml/' . $filename . '.xml');
        $conf = "\$config['prs_organic_search_array'] = array ( <br />";
        foreach ($xml->$filename as $element) {
            $conf .= "'$element->url' => array ( <br />" .
                    " 'url' => array ( <br /> ";
            foreach ($element->regexp as $regex) {
                $conf .= "'" . str_replace('\\', '', $regex) . "', <br />";
            }
            $conf .= " ), <br />" .
                    " 'param' => array ( <br /> ";
            foreach ($element->param as $param) {
                $p = ($param == '' ) ? 'x' : $param;
                $conf .= "'$p', <br />";
            }
            $conf .= " ), <br /> ), <br />";
        }
        $conf .= ");";
        echo $conf;
    }

    // migration function
    public function createGoalConversions() {
        if (!$this->isAuthenticated())
            exit;
        $this->adminmodel->createGoalConversions();
    }
    
    /**
     * alls the model method to export all current clients to api_client table so they can make use of the API
     */
    public function createApiUsersFromClients() {
        if (!$this->isAuthenticated())
            exit;
        echo 'Exporting clients to API_CLIENT table... <br />';
        $this->adminmodel->createApiUsersFromClients();
    }

    // migration function for BLAC-543
    public function migrateBlac543() {
        if (!$this->isAuthenticated())
            exit;
        $this->adminmodel->migrateBlac543();
    }

    // migration function for BLAC-543
    public function migrateProjectBlac543($collectionid) {
        if (!$this->isAuthenticated())
            exit;
        $this->adminmodel->migrateProjectBlac543($collectionid);
    }

    //******************************
    // agregation of daily used quota for all projects and clients in the past
    public function aggregateCompleteUsedQuota($clientid=-1) {
        if (!$this->isAuthenticated())
            exit;
        echo "Starting complete aggregation of used quota<br>\n";
        $this->load->library('queue');
        $clients = $this->sdk->getAccounts();
        foreach($clients as $client) {
            $doProcess = true;
            if($clientid != -1) {
                if($client->id != $clientid) {
                    $doProcess = false;
                }
            }
            if($doProcess) {
            $active = false;
                if($client->status=='ACTIVE')
                    $active=true;
                if($client->status=='FULL')
                    $active=true;
                if($active) {
                    $clientDbConnection = $this->user->getClientDbConnection($client->id);
                    $this->sdk->__set('clientid', $client->id);
                    $projects = $this->sdk->getProjects();
                    foreach($projects as $project) {
                        $projectid = $project->id;
                        // calculate number of days since creation
                        $createdDateObject = new DateTime($project->createddate);
                        $createdDate = new DateTime($createdDateObject->format('Y-m-d'));
                        $nowObject = new DateTime();
                        $now = new DateTime($nowObject->format('Y-m-d'));
                        $difference = $now->diff($createdDate, true);
                        $dateDiff = $difference->days;
                        // only proceed if at least one day old
                        if($dateDiff < 1) {
                            break;                        
                        }

                        $entries = $this->user->countUsedQuotaEntries($client->id,$projectid);
                        if($entries < $dateDiff) {
                            for($i=0; $i<($dateDiff); $i++) {
                                $calculationDate = clone $createdDate;
                                $interval = 'P' . $i . 'D';
                                $calculationDate->add(new DateInterval($interval));
                                $data = array(
                                    'clientid' => $client->id,
                                    'projectid' => $project->id,
                                    'projecttype' => $project->type,
                                    'projectname' => $project->name,
                                    'dbconnection' => $clientDbConnection,
                                    'date' => $calculationDate->format('Y-m-d 00:00:00')                                
                                );
                                $this->queue->push('aggregate_quota',$data);
                            }
                        }
                    }
                }                
            }
        }
        echo "<br>\nready<br>\n";
    }

    // aggregate used quota for yesterday
    public function aggregateIncrementalUsedQuota() {
        if (!$this->isAuthenticated())
            exit;
        $clients = $this->sdk->getAccounts();
        $this->load->library('queue');
        foreach($clients as $client) {
            $active = false;
            if($client->status=='ACTIVE')
                $active=true;
            if($client->status=='FULL')
                $active=true;
            if($active) {
                $clientDbConnection = $this->user->getClientDbConnection($client->id);
                $this->sdk->__set('clientid', $client->id);
                $projects = $this->sdk->getProjects();
                foreach($projects as $project) {
                    $projectid = $project->id;
                    // calculate number of days since creation
                    $createdDateObject = new DateTime($project->createddate);
                    $createdDate = new DateTime($createdDateObject->format('Y-m-d'));
                    $nowObject = new DateTime();
                    $now = new DateTime($nowObject->format('Y-m-d'));
                    $difference = $now->diff($createdDate, true);
                    $dateDiff = $difference->days;
                    // only proceed if at least one day old
                    if($dateDiff < 1) {
                        break;                        
                    }
                    // aggregate for yesterday
                    $yesterday = clone $now;
                    $yesterday->sub(new DateInterval('P1D'));
                    $data = array(
                        'clientid' => $client->id,
                        'projectid' => $project->id,
                        'projecttype' => $project->type,
                        'projectname' => $project->name,
                        'dbconnection' => $clientDbConnection,
                        'date' => $yesterday->format('Y-m-d 00:00:00')                                
                    );
                    $this->queue->push('aggregate_quota',$data);
                }
            }
        }
        echo "\nready\n";
    }

    //******************************
    // queue worker

    public function workQueue() {
        if (!$this->isAuthenticated())
            exit;

        $this->load->library('queue');
        while($entry = $this->queue->pop()) {
            // dispatch the entry and assign to responsible function
            switch($entry['key']) {
                case 'aggregate_quota':
                    $this->user->aggregateUsedQuota($entry['data']);
                break; 
            }
            $this->queue->finalize($entry['queue_id']);            
        }
    }

}
?>