<?php

class apimodel extends CI_Model {

    function __construct() {
        parent::__construct();
        // load mysql connection
        $this->load->database();
    }

    /*
     * insert or update client data
     */

    function saveClientData($data) {
        if ($data['quota'] == -1)
        	$data['quota'] = 100000;

        // check if user with this subid exists. if so, update.
        $currentDate = date('Y-m-d H:i:s');
        $subid = $data['subid'];
        $email = $data['email'];
        $quota = $data['quota'];
        $status = $data['status'];

        $sql = "SELECT clientid,clientid_hash,tenant,quota,quota_reset_dayinmonth,status 
			from client where subid = '$subid' limit 1";
        $result = mysql_query($sql);
        if ($result) {
            $row = mysql_fetch_array($result, MYSQL_ASSOC);
            $clientid = $row['clientid'];
            $clientid_hash = $row['clientid_hash'];
            $mytenant = $row['tenant'];
            $myquota = $row['quota'];
            $myquota_reset_dayinmonth = $row['quota_reset_dayinmonth'];
            $mystatus = $row['status'];
        }
        $ret = array();

        // dispatch
        if (empty($clientid)) {
            $action = 'insert';
        } elseif ($data['tenant'] != $mytenant) {
            $action = 'ignore';
        } else {
            $action = 'update';
        }

        $change = ""; // $change defines the transaction more business-wise. it is a comma-separated list of keywords
        // execute, save data
        if ($action == 'insert') {
            if ($status == 1) {
                $change .= "create,test,";
            } elseif ($status == 6) {
                $change .= "create,subscribe,";
            } else {
                $change .= "create,";
            }
            $data['createddate'] = $currentDate;
            $data['modifydate'] = $currentDate;
            $data['used_quota'] = 0;
            // always set reset_day_in_month on create - use contract_enddate if provided and create date if not
            if (isset($data['contract_enddate']) && ($data['contract_enddate'] != '')) {
                $contract_enddate = $data['contract_enddate'];
                $da = date_parse($data['contract_enddate']);
                $quota_reset_dayinmonth = $da['day']; //echo "dayinmomtn: $quota_reset_dayinmonth";			
            } else {
                $da = date_parse($currentDate);
                $quota_reset_dayinmonth = $da['day'];
            }
            unset($data['contract_enddate']); // remove this value as it is not stored in table client
            $data['quota_reset_dayinmonth'] = $quota_reset_dayinmonth;
            $this->db->insert('client', $data);
            $id = $this->db->insert_id();
            // if clientid_hash is not provided, create and set it
            if ($data['clientid_hash'] == '') {
                $this->db->set('clientid_hash', md5($id));
                $this->db->where('clientid', $id);
                $this->db->update('client');
                $clientid_hash = md5($id);
            } else {
                $clientid_hash = $data['clientid_hash'];
            }
            $ret['statuscode'] = "200";
            $ret['clientid_hash'] = $clientid_hash;

            // After inserting the new client, the next method creates the corresponding entry into the api_client table
            require_once APPPATH . 'models/userapi.php';
            $userapi = new userapi();
            $userapi->saveApiClient($id);
        }
        if ($action == 'update') {
            // always set reset_day_in_month on create - use contract_enddate if provided and create date if not
            if (isset($data['contract_enddate']) && ($data['contract_enddate'] != '')) {
                $contract_enddate = $data['contract_enddate'];
                $da = date_parse($data['contract_enddate']);
                $quota_reset_dayinmonth = $da['day'];
                $data['quota_reset_dayinmonth'] = $quota_reset_dayinmonth;
            }
            unset($data['contract_enddate']); // remove this value as it is not stored in table client

            $data['modifydate'] = $currentDate;
            //echo "quota: $quota myquota: $myquota status: $status mystatus: $mystatus day: $quota_reset_dayinmonth mysday: $myquota_reset_dayinmonth";
            // check for modifications in the values compared to current values and derive a business-wise description 
            // of this transaction for storage in the history-table (see below)
            if (($mystatus == 1) && ($status == 6)) {
                $data['used_quota'] = 0;
                $change .= "subscribe,";
            }
            if (($mystatus == 6) && ($status == 2)) {
                $change .= "cancel,";
            }
            if (($mystatus == 1) && ($status == 2)) {
                $change .= "cancel,";
            }
            if (($mystatus == 2) && ($status == 6)) {
                $change .= "subscribe,";
            }
            if (($mystatus == 2) && ($status == 1)) {
                $change .= "test,";
            }
            if ($mystatus == $status) {
                if ($quota > $myquota)
                    $change .= "upgrade,";
                if ($quota < $myquota)
                    $change .= "downgrade,";
                echo "quota_reset_dayinmonth $quota_reset_dayinmonth";
                if (isset($quota_reset_dayinmonth) && ($quota_reset_dayinmonth != '') && ($quota_reset_dayinmonth != $myquota_reset_dayinmonth))
                    $change .= "periodshift,";
            }
            $this->db->where('clientid', $clientid);
            $this->db->update('client', $data);
            apch_delete("cs_" . $clientid_hash); // cached in getclientstatus
            $ret['statuscode'] = "200";
            $ret['clientid_hash'] = $clientid_hash;

            // make a DB request to retrieve wether changes have been made with the update or not
            $sql = "SELECT clientid,clientid_hash,tenant,quota,quota_reset_dayinmonth,status 
				from client where subid = '$subid' limit 1";
            $result = mysql_query($sql);
            if ($result) {
                $row = mysql_fetch_array($result, MYSQL_ASSOC);
                $newquota = $row['quota'];
                $newquota_reset_dayinmonth = $row['quota_reset_dayinmonth'];
                $newstatus = $row['status'];

                if (($newquota != $myquota) || ($newquota_reset_dayinmonth != $myquota_reset_dayinmonth) || ($newstatus != $mystatus)) {
                    $updatechange = true;
                }
            }
        }
        if ($action == 'ignore') {
            $ret['statuscode'] = "401";
        }

        // save an entry in the history table if changes have been made
        if (($action == 'insert') ||
                (($action == 'update') && (isset($updatechange) && ($updatechange)))) {
            $history = array();
            $sql = "SELECT clientid from client where subid = '$subid'";
            $result = mysql_query($sql);
            $clientid = mysql_result($result, 0, 0);
            $history['modifydate'] = $data['modifydate'];
            $history['subid'] = $data['subid'];
            $history['quota'] = $data['quota'];
            $history['account_collectionid'] = $data['account_collectionid'];
            $history['status'] = $data['status'];
            $history['userplan'] = $data['userplan'];
            $history['clientid'] = $clientid;
            if ($contract_enddate != "")
                $history['contract_enddate'] = $contract_enddate;
            $history['change'] = $change;
            $this->db->insert('et_clientdata_history', $history);
        }
        //echo $change;
        return $ret;
    }

    /*
     * insert or update etracker website targets for client 
     */

    function saveWebsiteTargets($data) {
        // check if dataset is OK and data may be saved
        $subid = $data['subid'];
        $tenant = $data['tenant'];
        $sql = "SELECT count(*) as cnt FROM `client` where subid = '$subid'";
        $result = mysql_query($sql);
        $count = mysql_result($result, 0, 0);
        if ($count == 0)
            return "404";
        $sql = "SELECT count(*) as cnt FROM `client` where subid = '$subid' and tenant='$tenant'";
        $result = mysql_query($sql);
        $count = mysql_result($result, 0, 0);
        if ($count == 0)
            return "401";

        // update targets
        $et_targets = implode(",", $data['targets']);
        $sql = "SELECT clientid from client where subid = '$subid'";
        $result = mysql_query($sql);
        $clientid = mysql_result($result, 0, 0);
        $this->db->where('clientid', $clientid);
        $mydata = array("et_targets" => $et_targets);
        $this->db->update('client', $mydata);

        return "200";
    }

    /*
     * check if data for new client are valid and may be saved
     */

    function getClientData($subid, $tenant, $mode = "LIMITED") {
        // is tenant legible to access data from this client?
        $sql = "SELECT count(*) as cnt FROM `client` where subid = '$subid' and tenant!='$tenant'";
        $result = mysql_query($sql);
        $count = mysql_result($result, 0, 0);
        if ($count > 0)
            return "401";

        // get number of active tests for the client
        $sql = "select count(*) from landingpage_collection lc, client c
			where lc.status=2
			and lc.clientid = c.clientid
			and c.subid='$subid'";
        //echo $sql;
        $result = mysql_query($sql);
        $testcount = mysql_result($result, 0, 0);

        // retrieve all other client data				
        $query = $this->db->get_where('client', array('subid' => $subid), 1);
        $row = $query->row_array();
        if (empty($row))
            return "404";

        if ($mode == "LIMITED") {
            $ret = array();
            $ret['subid'] = $row['subid'];
            $ret['et_targets'] = $row['et_targets'];
            $ret['lastname'] = $row['lastname'];
            $ret['firstname'] = $row['firstname'];
            $ret['email'] = $row['email'];
            $ret['status'] = $row['status'];
            $ret['userplan'] = $row['userplan'];
            $ret['quota'] = $row['quota'];
            $ret['used_quota'] = $row['used_quota'];
            $ret['quota_reset_dayinmonth'] = $row['quota_reset_dayinmonth'];
            $ret['last_quota_reset_date'] = $row['last_quota_reset_date'];
            $ret['active_tests'] = $testcount;

            return($ret);
        }
        if ($mode == "FULL") {
            return($row);
        }
    }

    /*
     * save data of partner Wywy with data of aired commercials
     */
    function saveWywyData($data) {
        if(is_array($data)) {
            $this->db->insert('int_wywy', $data);            
        }
    }

    /*
     * get IDs of all tests for a given account
     */

    function getTests($accountId) {
        $ret = array();
        $sql = "select landingpage_collectionid from landingpage_collection lc,client c
			where c.clientid_hash = '$accountId'
			and c.clientid = lc.clientid order by landingpage_collectionid desc";
        $res = mysql_query($sql);
        trackMysqlError(__function__);
        //echo $sql;
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $ret[] = $row['landingpage_collectionid'];
        }
        return($ret);
    }

}

// class end here
?>