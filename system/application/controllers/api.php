<?php

class api extends CI_Controller {

    function __construct() {
        parent::__construct();
        doAutoload(); // load resources from autoload_helper
        $this->load->model('apimodel');
        $this->load->model('shared');
        $this->load->model('optimisation');
        $this->load->helper('featurematrix');
    }

    function index() {
        
    }
    
    /*
     * Updated function with new field free_quota
     */
    function saveEtrackerClientData_v2() {
        return $this->saveEtrackerClientData(2);
    }
    

    /*
     * Insert or update a client
     * If client exists, it is updated, inserted if not.
     * data is provided as JSON post body
     */

    function saveEtrackerClientData($version=1) {
        $apiuser = authenticate();

        $body = urldecode(file_get_contents('php://input'));
        $json_in = json_decode($body, true);
        $statuscode = "200";
        $errmsg = "";
        if (empty($json_in['subid'])) {
            $statuscode = "501";
            $errmsg = "missing field: subid";
        }
        else {
        	$json['subid'] = $json_in['subid'];
        }

        if (empty($json_in['userplan'])) {
            $statuscode = "501";
            $errmsg = "missing field: userplan";
        }
        else {
        	$json['userplan'] = $json_in['userplan'];
        }
        if (empty($json_in['status'])) {
            $statuscode = "501";
            $errmsg = "missing field: status";
        }
        else {
        	$json['status'] = $json_in['status'];
        }
        
        if (empty($json_in['accountcollection'])) {
            $json_in['accountcollection'] = "";
        }
        $json['account_collectionid'] = $json_in['accountcollection'];

        if (!isset($json_in['quota'])) {
            $json_in['quota'] = "-1";
        }
        $json['quota'] = $json_in['quota'];
        
        // new fields for api version 2.0
        if($version==2) {
            if (!isset($json_in['free_quota'])) {
                $json_in['free_quota'] = 0;
            }
            $json['free_quota'] = $json_in['free_quota'];
            if (!isset($json_in['account_key2'])) {
            	$statuscode = "501";
            	$errmsg = "missing field: account_key2";
            }
            $json['account_key2'] = $json_in['account_key2'];
        }
        
        if (empty($json_in['contract_enddate'])) {
            $json_in['contract_enddate'] = "";
        }
        $json['contract_enddate'] = $json_in['contract_enddate'];
        
        if (empty($json_in['password'])) {
            $json_in['password'] = "";
        } else {
            $json_in['password'] = md5($json_in['password']);
        }
        $json['password'] = $json_in['password'];
        
        if (empty($json_in['clientcode'])) {
            $json_in['clientid_hash'] = "";
        } else {
            $json_in['clientid_hash'] = $json_in['clientcode'];
        }
        $json['clientid_hash'] = $json_in['clientid_hash'];
        
        $json['ip_blacklist'] = $json_in['ip_blacklist'];
        
        if ($apiuser['key'] == 'etracker')
            $json['tenant'] = 'etracker';

        // dummy values	
        $json['lastname'] = "NA";
        $json['firstname'] = "NA";
        $json['referrer'] = '';
        $json['email'] = 'NA';
        $json['email_validated'] = CLIENT_EMAIL_VALIDATED;
        if ($statuscode != 200) {
            $json_out = array("statuscode" => $statuscode, "error" => $errmsg);
            echo json_encode($json_out);
        } else {
            $result = $this->apimodel->saveClientData($json);
            if ($result['statuscode'] == "200") {
                $json_out = array("statuscode" => "200", "clientcode" => $result['clientid_hash']);
                echo json_encode($json_out);
            } else {
                switch ($result) {
                    case "401" :
                        $errmsg = "no permission to update user with this subid";
                        break;
                }
                $json_out = array("statuscode" => $result, "error" => $errmsg);
                echo json_encode($json_out);
            }
        }
    }

    /*
     * retrieve client data as JSON array
     * sub-id is provided in URL
     */

    function getClientData() {
        $apiuser = authenticate();
        if ($apiuser['key'] == 'etracker')
            $tenant = "etracker";
        else
            $tenant = "blacktri";
        $CI = & get_instance();
        $thisSubId = $CI->uri->segment(3);
        if (empty($thisSubId)) {
            $json_out = array("statuscode" => "500", "error" => "subid missing");
            echo json_encode($json_out);
        } else {
            $result = $this->apimodel->getClientData($thisSubId, $tenant);
            if ($result == "401") {
                $json_out = array("statuscode" => "401", "error" => "permission denied to access this subid");
                echo json_encode($json_out);
            } elseif ($result == "404") {
                $json_out = array("statuscode" => "404", "error" => "no client with this subid found");
                echo json_encode($json_out);
            } else {
                $json_out = array("statuscode" => "200", "data" => $result);
                echo json_encode($json_out);
            }
        }
    }

    /*
     * Insert or update website targets for a given client
     */

    function saveWebsiteTargets() {
        $apiuser = authenticate();

        $body = urldecode(file_get_contents('php://input'));
        $json = json_decode($body, true);

        $statuscode = "200";
        $errmsg = "";
        if (empty($json['subid'])) {
            $statuscode = "501";
            $errmsg = "missing field: subid";
        }
        if (empty($json['targets'])) {
            $statuscode = "501";
            $errmsg = "missing field: targets";
        }
        if ($statuscode == "200") {
            if (empty($json['targets'][0])) {
                $statuscode = "501";
                $errmsg = "no target found";
            }
        }

        if ($apiuser['key'] == 'etracker')
            $json['tenant'] = 'etracker';

        if ($statuscode != 200) {
            $json_out = array("statuscode" => $statuscode, "error" => $errmsg);
            echo json_encode($json_out);
        } else {
            $result = $this->apimodel->saveWebsiteTargets($json);
            if ($result == "401") {
                $json_out = array("statuscode" => "401", "error" => "permission denied to access this subid");
                echo json_encode($json_out);
            } elseif ($result == "404") {
                $json_out = array("statuscode" => "404", "error" => "no client with this subid found");
                echo json_encode($json_out);
            } else {
                $json_out = array("statuscode" => "200");
                echo json_encode($json_out);
            }
        }
    }

    /*
     * sign a client in through a signed api request
     */

    function signin() {
        $this->load->helper('featurematrix');
        
        $apiuser = authenticate();
        if ($apiuser['key'] == 'etracker')
            $tenant = "etracker";
        $CI = & get_instance();
        $thisSubId = $CI->uri->segment(3);
        if (empty($thisSubId)) {
            $json_out = array("statuscode" => "500", "error" => "subid missing");
            echo json_encode($json_out);
        } else {
            $result = $this->apimodel->getClientData($thisSubId, $tenant, "FULL");
            if ($result == "401") {
                $json_out = array("statuscode" => "401", "error" => "permission denied to access this subid");
                echo json_encode($json_out);
            } elseif ($result == "404") {
                $json_out = array("statuscode" => "404", "error" => "no client with this subid found");
                echo json_encode($json_out);
            } else {
                $this->session->set_userdata('sessionUserId', $result['clientid']);
                $this->session->set_userdata('sessionLoginStatus', LOGIN_STATUS_FULL);
                $this->session->set_userdata('sessionUserRole', $result['role']);
                $this->session->set_userdata('sessionFirstName', $result['firstname']);
                setFeatureMatrix($result['userplan']);                
                redirect($CI->config->item('base_ssl_url') . "lpc/cs/" . $result['clientid']);
            }
        }
    }

    /*
     * Interface for Wywy TV commercial data
     */
    function saveWywyData() {
        $statuscode = "200 OK";
        $response = "Data saved successfully";
        //echo "1";

        $body = urldecode(file_get_contents('php://input'));
        $json_in = json_decode($body, true);
        if(!is_array($json_in)) {
            $statuscode = "500 Server Error";
            $response = "Invalid Data";
        }
        else { // check security token
            $secret = $this->shared->getAPISecret('wywy');
            if($secret != $json_in['securityToken']) {
                $statuscode = "401 Access Denied";
                $response = "Access Denied";                
            }
            else { // sanitize all input values and save data
                $data = array(
                    "timestamp" => 0,
                    "channelid" => "NA",
                    "customerid" => "NA",
                    "commercialid" => "NA",
                    "channelname" => "NA",
                    "commercialname" => "NA",
                );
                $allowed_chars = array(' ','-','_');
                if(is_numeric($json_in['timestamp'])) {
                    $data['timestamp'] = date('Y-m-d H:i:s',$json_in['timestamp']);
                }
                if(ctype_alnum($json_in['channelID'])) {
                    $data['channelid'] = $json_in['channelID'];
                }
                if(ctype_alnum($json_in['customerID'])) {
                    $data['customerid'] = $json_in['customerID'];
                }
                if(ctype_alnum($json_in['commercialID'])) {
                    $data['commercialid'] = $json_in['commercialID'];
                }
                $rep = str_replace($allowed_chars,'',$json_in['channelName']);
                if(ctype_alnum($rep)) {
                    $data['channelname'] = $json_in['channelName'];
                }
                $rep = str_replace($allowed_chars,'',$json_in['commercialName']);
                if(ctype_alnum($rep)) {
                    $data['commercialname'] = $json_in['commercialName'];
                }
                // check mandatory fields
                $datamissing = false;
                if($data['timestamp'] == 0) {
                    $datamissing = true;
                }
                if($data['channelid'] == 'NA') {
                    $datamissing = true;
                }
                if($data['customerid'] == 'NA') {
                    $datamissing = true;
                }
                if($data['commercialid'] == 'NA') {
                    $datamissing = true;
                }
                if($data['channelname'] == 'NA') {
                    $datamissing = true;
                }
                if($data['commercialname'] == 'NA') {
                    $datamissing = true;
                }
                if($datamissing) {
                    $statuscode = "400 Invalid data";
                    $response = "Missing or invalid data";                                    
                }
                else {
                    $this->apimodel->saveWywyData($data);                    
                }
            }
        }
    
        header("HTTP/1.1 $statuscode");
        echo $response;       
    }

}

?>