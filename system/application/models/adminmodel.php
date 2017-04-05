<?php

class adminmodel extends CI_Model {

    function __construct() {
        parent::__construct();
        // load mysql connection
        $this->load->database();
    }

    /*
     * save tracecode 
     */

    function setTracecode($code) {
        $this->db->insert('tracecode', array('code' => $code));
    }

    /*
     * get all log entires for a given tracecode
     */

    function getLogByTracecode($code) {
        $sql = "select * from logging where tracecode='$code' 
		and timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
		order by loggingid desc limit 100";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $entries[] = $row;
        }
        return($entries);
    }

    function getNewLeads() {
        $sql = "select email,date from lead order by date desc limit 20";
        $res = mysql_query($sql);

        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            print_r($row);
            $entries[] = $row;
        }
        return($entries);
    }

    // provide a list of all new tests from the last 24 hours
    function getLatestTests() {
        $sql = "SELECT client.email as Kunde, landingpage_collection.name as Testname, count(request_eventsid) as Requests
FROM request_events,client, landingpage_collection
where date > DATE_SUB(CURDATE(), INTERVAL 1 day)
and client.clientid = request_events.clientid
and landingpage_collection.landingpage_collectionid = request_events.landingpage_collectionid
group by request_events.landingpage_collectionid";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $entries[] = $row;
        }
        return($entries);
    }

    /*     * ******************************************************************************************************************************
     * ********************MULTIVARIATE TEST METHODS: LIST, EDIT, UPDATE, DELETE **********************************
     * ********************************************************************************************************************************* */

    /*
     * List the tests where landingpage_collection type = 2
     */

    function getTestList($order, $l1, $l2) {
        $l1 = is_numeric($l1) ? $l1 : 0;
        $l2 = is_numeric($l2) ? $l2 : 0;
        $select = " SELECT lc.landingpage_collectionid, lc.name, lc.code, lc.status, lp.lp_url, lp.canonical_url, c.clientid_hash ";
        $from = " FROM landingpage_collection lc  ";
        $from.= " INNER JOIN landing_page lp ON lp.landingpage_collectionid = lc.landingpage_collectionid ";
        $from.= " INNER JOIN client c ON c.clientid = lc.clientid ";
        $where = " WHERE lc.testtype = 2 ";
        $by = " GROUP BY lc.landingpage_collectionid ORDER BY  $order ";
        $limit = " LIMIT " . (int) $l1 . ", " . (int) $l2;

        $sql = $select . $from . $where . $by . $limit;
        $query1 = $this->db->query($sql);

        $res1 = array();
        foreach ($query1->result() as $q1) {
            $r = array();
            $r["landingpage_collectionid"] = $q1->landingpage_collectionid;
            $r["name"] = $q1->name;
            $r["code"] = $q1->code;
            $r["status"] = $q1->status == 1 ? 'Paused' : 'Active';
            $r["lp_url"] = $q1->lp_url;
            $r["canonical_url"] = $q1->canonical_url;
            $r["clientid_hash"] = $q1->clientid_hash;
            $res1[] = $r;
        }

        $sql = $select . $from . $where . $by;
        $query2 = $this->db->query($sql);
        $cnt = count($query2->result());

        return array('Result' => 'OK', 'Records' => $res1, 'TotalRecordCount' => $cnt);
    }

    /*
     * Returns all the LP, mvt factors, mvt level, mvt levels_to pate given a LPC ID
     */

    function getTestsByLPC($lpcid) {
        $sql = "SELECT c.clientid_hash, lpc.name, lpc.status, lp.landing_pageid, lp.lp_url, lp.canonical_url ";
        $sql.= " FROM client c INNER JOIN landingpage_collection lpc ON lpc.clientid = c.clientid ";
        $sql.= " INNER JOIN landing_page lp ON lp.landingpage_collectionid = lpc.landingpage_collectionid ";
        $sql.= " WHERE lpc.landingpage_collectionid = ? AND lp.pagetype = 1 LIMIT 1 ";
        $query1 = $this->db->query($sql, array($lpcid));

        $res1 = array();
        foreach ($query1->result() as $q1) {
            $r = array();
            $r["clientid_hash"] = $q1->clientid_hash;
            $r["name"] = $q1->name;
            $r["status"] = $q1->status;
            $r["landing_pageid"] = $q1->landing_pageid;
            $r["lp_url"] = $q1->lp_url;
            $r["canonical_url"] = $q1->canonical_url;
            $res1[] = $r;
        }

        // gets the factors per LPC id
        $sql2 = " SELECT mvt_factor_id, name, dom_path FROM mvt_factor WHERE landingpage_collectionid = ? ";
        $query2 = $this->db->query($sql2, array($lpcid));

        $res2 = array();
        foreach ($query2->result() as $q1) {
            $r = array();
            $r["mvt_factor_id"] = $q1->mvt_factor_id;
            $r["name"] = $q1->name;
            $r["dom_path"] = $q1->dom_path;
            $res2[] = $r;
        }

        // gets the levels per LPC id
        $sql3 = " SELECT mvt_level_id, mvt_factor_id, name, level_content FROM mvt_level WHERE landingpage_collectionid = ? ";
        $query3 = $this->db->query($sql3, array($lpcid));

        $res3 = array();
        foreach ($query3->result() as $q1) {
            $r = array();
            $r["mvt_level_id"] = $q1->mvt_level_id;
            $r["mvt_factor_id"] = $q1->mvt_factor_id;
            $r["name"] = $q1->name;
            $r["level_content"] = $q1->level_content;
            $res3[] = $r;
        }

        return array('Result' => 'OK', 'Lpc' => $res1, 'Factors' => $res2, 'Levels' => $res3);
    }

    /*
     * Verifies that the client code entered by the user exist into the DB
     * if it existes return the clientid, if not, returns false
     */

    function verifyClientCode($ccode) {
        $sql = " SELECT clientid FROM client WHERE clientid_hash = ? ";
        $query = $this->db->query($sql, array($ccode));

        if (count($query->result()) == 1) {
            $clid = 0;
            foreach ($query->result() as $q1) {
                $clid = $q1->clientid;
            }
            return $clid;
        }

        return false;
    }

    /*
     * Creates a new entry in the table landingpage_collection for the new MVT test
     * @return The last inserted ID
     */

    function createMvtLPC($name, $status, $clid) {
        $start_date = date('Y-m-d H:i:s');
        $restart_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s", mktime()) . " + 365 day"));
        $sql = "INSERT INTO landingpage_collection(name, status, testtype, progress, optimization_mode, restart_date, start_date, end_date, clientid) ";
        $sql.= " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?) ";
        $this->db->query($sql, array($name, $status, 2, 1, 1, $restart_date, $start_date, $end_date, $clid));
        return $this->db->insert_id();
    }

    /*
     * Updates the info into the landingpage_collection table with the edited name, status and client id
     */

    function updateMvtLPC($name, $status, $clid, $lpcid) {
        $sql = "UPDATE landingpage_collection SET name = ?, status = ?, clientid = ? WHERE landingpage_collectionid = ? ";
        $this->db->query($sql, array($name, $status, $clid, $lpcid));
    }

    /*
     * Updates the code column for the given lpc row (by id)
     */

    function updateCodeLPC($lpcode, $lpcid) {
        $sql = " UPDATE landingpage_collection SET code = ? WHERE landingpage_collectionid = ? ";
        $this->db->query($sql, array($lpcode, $lpcid));
    }

    /*
     * If something went wrong or we want to delete de entire landingpage_collection
     */

    function deleteMvtLPC($lpcid) {
        if ((int) $lpcid > 0) {
            $sql1 = " DELETE FROM mvt_level_to_page WHERE landingpage_collectionid = ? ";
            $this->db->query($sql1, array($lpcid));

            $sql2 = " DELETE FROM mvt_level WHERE landingpage_collectionid = ? ";
            $this->db->query($sql2, array($lpcid));

            $sql3 = " DELETE FROM mvt_factor WHERE landingpage_collectionid = ? ";
            $this->db->query($sql3, array($lpcid));

            $sql4 = " DELETE FROM landing_page WHERE landingpage_collectionid = ? ";
            $this->db->query($sql4, array($lpcid));

            $sql5 = " DELETE FROM landingpage_collection WHERE landingpage_collectionid = ? ";
            $this->db->query($sql5, array($lpcid));

            return array('Result' => 'OK');
        }

        return array('Result' => 'ERROR', 'Message' => 'Error deleting the Test (LPC)');
    }

    /*
     * Creates the corresponding entries in the table landing_page
     * First it checks if there is a row already in the DB (if it is an EDITION), if so returns the id of the LP
     * if it is an edition, updates the lp_url in the landing_pages.that are not the control (variants)
     */

    function createMvtLP($tname, $url1, $url2, $ptype, $tcode, $lpcid) {
        $sql1 = " SELECT landing_pageid FROM landing_page ";
        $sql1.= " WHERE name = ? AND pagetype = ? AND dom_modification_code = ? AND  landingpage_collectionid = ? LIMIT 1 ";
        $query1 = $this->db->query($sql1, array($tname, $ptype, $tcode, $lpcid));

        if (count($query1->result()) == 1) {

            foreach ($query1->result() as $q1) {
                $lpd = $q1->landing_pageid;
            }

            $sql2 = "UPDATE landing_page SET lp_url = ? WHERE pagetype != 1 AND landingpage_collectionid = ? ";
            $this->db->query($sql2, array($url1, $lpcid));
            return $lpd;
        }

        $sql3 = " INSERT INTO landing_page  ";
        $sql3.= " (name, status, lp_url, canonical_url, pagetype, dom_modification_code, landingpage_collectionid) ";
        $sql3.= " VALUES(?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql3, array($tname, 2, $url1, $url2, $ptype, $tcode, $lpcid));
        return $this->db->insert_id();
    }

    /*
     * updates the status,  lp_url and the canonical url in the control (original)
     */

    function updateMvtLP($url1, $url2, $lpd) {
        $sql = "UPDATE landing_page SET lp_url = ?, canonical_url = ? WHERE landing_pageid = ? ";
        $this->db->query($sql, array($url1, $url2, $lpd));
    }

    /*
     * gets the content of each level related to the id of the landing_page that contains that code to update the LP rows.
     */

    function LandingPageByLevel($lpcid) {
        $sql = " SELECT f.name as factor, e.level_content, e.name, lp.landing_pageid FROM mvt_factor f ";
        $sql.= " INNER JOIN mvt_level e ON e.mvt_factor_id = f.mvt_factor_id ";
        $sql.= " INNER JOIN mvt_level_to_page l2p ON l2p.mvt_level_id = e.mvt_level_id ";
        $sql.= " INNER JOIN landing_page lp ON lp.landing_pageid = l2p.landing_pageid ";
        $sql.= " WHERE e.landingpage_collectionid = ? ORDER BY l2p.landing_pageid DESC, e.mvt_level_id ASC ";
        $query = $this->db->query($sql, array($lpcid));

        $res = array();
        if (count($query->result()) > 0) {
            foreach ($query->result() as $q1) {
                $r = array();
                $r['factor'] = $q1->factor;
                $r['name'] = $q1->name;
                $r['level_content'] = $q1->level_content;
                $r['landing_pageid'] = $q1->landing_pageid;
                $res[] = $r;
            }
        }
        return $res;
    }

    /*
     * updates the name and dom_modification_code into the landing_page table given the ID
     */

    function updateLpDomCode($name, $code, $lpd) {
        $sql = " UPDATE landing_page SET name = ?, dom_modification_code = ? WHERE landing_pageid = ? ";
        $this->db->query($sql, array($name, $code, $lpd));
    }

    /*
     * Creates the corresponding entries in the mvt_factor table
     * first it checks in the mvt_factor table if there is already a row containin all data, if so, it returns the id of that row (EDITING)
     */

    function createMvtFactor($lpd, $name, $selector, $lpcid) {
        $sql1 = " SELECT mvt_factor_id FROM mvt_factor ";
        $sql1.= " WHERE landing_pageid = ? AND name = ? AND dom_path = ? AND landingpage_collectionid = ? LIMIT 1 ";
        $query1 = $this->db->query($sql1, array($lpd, $name, $selector, $lpcid));

        if (count($query1->result()) == 1) {
            foreach ($query1->result() as $q1) {
                $fid = $q1->mvt_factor_id;
            }
            return $fid;
        }

        $sql2 = "INSERT INTO mvt_factor(landing_pageid, name, dom_path, landingpage_collectionid) VALUES(?, ?, ?, ?)";
        $this->db->query($sql2, array($lpd, $name, $selector, $lpcid));
        return $this->db->insert_id();
    }

    /*
     * Updates the content of the mvt_factor given a factor ID
     */

    function updateMvtFactor($lpd, $name, $selector, $lpcid, $fid) {
        $sql = " UPDATE mvt_factor SET landing_pageid = ?, name = ?, dom_path = ?, landingpage_collectionid = ? ";
        $sql.= " WHERE mvt_factor_id = ? ";
        $this->db->query($sql, array($lpd, $name, $selector, $lpcid, $fid));
    }

    /*
     * First gets all the related levels to be deleted too, then deleted the actual factor from the DB
     */

    function deleteMvtFactor($fact) {
        $sql = " SELECT mvt_level_id FROM mvt_level WHERE mvt_factor_id = ? ";
        $query = $this->db->query($sql, array($fact));

        foreach ($query->result() as $q1) {
            self::deleteMvtLevel($q1->mvt_level_id);
        }

        $sql2 = " DELETE FROM mvt_factor WHERE mvt_factor_id = ? ";
        $this->db->query($sql2, array($fact));
    }

    /*
     * Creates the corresponding entries for the mvt_level table
     * first it checks if it's an edition all data will be already there, so itl will return the level id in that case
     */

    function createMvtLevel($factorid, $name, $content, $lpcid) {
        $sql1 = " SELECT mvt_level_id FROM mvt_level ";
        $sql1.= " WHERE mvt_factor_id = ? AND name = ? AND level_content = ? AND landingpage_collectionid = ? LIMIT 1 ";
        $query1 = $this->db->query($sql1, array($factorid, $name, $content, $lpcid));

        if (count($query1->result()) == 1) {
            foreach ($query1->result() as $q1) {
                $lid = $q1->mvt_level_id;
            }
            return $lid;
        }

        $sql2 = " INSERT INTO mvt_level(mvt_factor_id, name, level_content, landingpage_collectionid) VALUES(?, ?, ?, ?)";
        $this->db->query($sql2, array($factorid, $name, $content, $lpcid));
        return $this->db->insert_id();
    }

    /*
     * Updates the mvt_level table given a level Id
     */

    function updateMvtLevel($name, $content, $lpcid, $factorid, $level) {
        $sql = " UPDATE mvt_level SET name = ?, level_content = ?, landingpage_collectionid = ? ";
        $sql.= " WHERE mvt_factor_id = ? AND mvt_level_id = ? ";
        $this->db->query($sql, array($name, $content, $lpcid, $factorid, $level));
    }

    /*
     * Deletes the level from the database depending on the level id
     * also deletes the associated level_to_pages and landing_pages rows.
     */

    function deleteMvtLevel($lev) {
        $sql = " DELETE FROM landing_page WHERE landing_pageid IN ";
        $sql.= " (SELECT landing_pageid FROM mvt_level_to_page WHERE mvt_level_id = ?) ";
        $this->db->query($sql, array($lev));

        $sql2 = " DELETE FROM mvt_level_to_page WHERE mvt_level_id = ? ";
        $this->db->query($sql2, array($lev));

        $sql3 = " DELETE FROM mvt_level WHERE mvt_level_id = ? ";
        $this->db->query($sql3, array($lev));
    }

    /*
     * Gets the mvt level ID that matches the content (level_content) and the LP id in the mvt_factor where
     * this LP id is the original or "control"
     */

    function getMtvLevelByContent($content, $lpdoriginal) {
        $sql = " SELECT e.mvt_level_id FROM mvt_level e INNER JOIN mvt_factor f ON f.mvt_factor_id = e.mvt_factor_id ";
        $sql.= " WHERE e.level_content = ? AND f.landing_pageid = ? LIMIT 1 ";
        $query = $this->db->query($sql, array($content, $lpdoriginal));

        if (count($query->result()) == 1) {
            $mvtid = 0;
            foreach ($query->result() as $q1) {
                $mvtid = $q1->mvt_level_id;
            }
            return $mvtid;
        }
        return false;
    }

    /*
     * Inserts the correspnding rows to the mvt_level_to_page
     * But first it checks if the corresponding rows are created already (if it is an edition)
     */

    function createMvtLevelToPage($lpd, $levelid, $lpcid) {
        $sql1 = " SELECT mvt_level_to_page_id FROM mvt_level_to_page ";
        $sql1.= " WHERE landing_pageid = ? AND mvt_level_id = ? AND landingpage_collectionid = ? LIMIT 1 ";
        $query1 = $this->db->query($sql1, array($lpd, $levelid, $lpcid));

        if (count($query1->result()) == 1) {
            foreach ($query1->result() as $q1) {
                $lid = $q1->mvt_level_to_page_id;
            }
            return $lid;
        }

        $sql2 = "INSERT INTO mvt_level_to_page(landing_pageid, mvt_level_id, landingpage_collectionid) VALUES(?, ?, ?) ";
        $this->db->query($sql2, array($lpd, $levelid, $lpcid));
        return $this->db->insert_id();
    }

    /*
     * Client configuration
     */
    public function getClientConfigurationValues($clientid) {
        $this->db->select('config');
        $this->db->from('client c');
        $this->db->where('clientid', $clientid)->limit(1);
        $query = $this->db->get();
        $row = $query->row(); 
        if($query->num_rows() == 1) {
            $configArray = json_decode($row->config,true);
            if(is_array($configArray)) {
                return $configArray;
            }
            else {
                return "";
            }
        }
        else {
            return "error";
        }
    }

    public function saveClientConfigurationValues($clientid,$configArray) {
        if($configArray == "") {
            $serializedConfig = "";
        }
        else if(!is_array($configArray)) {
            $serializedConfig = "";
        }
        else {
            $serializedConfig = json_encode($configArray);   
            $data = array(
                'config' => $serializedConfig
            );
            $this->db->where('clientid', $clientid);
            $this->db->update('client', $data);
        }
        $this->db->select('clientid_hash');
        $this->db->from('client c');
        $this->db->where('clientid', $clientid)->limit(1);
        $query = $this->db->get();
        $row = $query->row(); 
        if($query->num_rows() == 1) {
            $clientcode = $row->clientid_hash;
            return $clientcode;
        }
        else {
            return false;            
        }
    }

    // management of client specific databases
    public function getClientDatabaseNames() {
        $sql = "show databases";
        $query = $this->db->query($sql);

        $res = array();
        foreach ($query->result() as $q) {
            $db = $q->Database;
            // filter only databases that start with a specific prefix and contain a client ID number
            $db = str_replace(CLIENT_DB_PREFIX, "", $db);
            if(is_numeric($db)) {
                $res[] = $db;
            }
        }
        return $res;
    }

    public function createClientDatabase($clientid,$dbserver) {
        // retrieve primary keys so the autoincrement values can be set accordingly. This ensures
        // that we can move all existing entries to the new tables later on
        $request_eventsid = 1;
        $sql = "select request_eventsid from request_events where clientid=$clientid order by request_eventsid desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $request_eventsid = $query->row()->request_eventsid + 1000;            
        }
        $visitorid = 1;
        $sql = "select visitor from request_events where clientid=$clientid order by visitor desc limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $visitorid = $query->row()->visitor + 1000;            
        }
        $delivery_planid = 1;
        $sql = "select delivery_planid from delivery_plan dp, landingpage_collection lpc
            where dp.landingpage_collectionid = lpc.landingpage_collectionid
            and lpc.clientid=$clientid
            order by delivery_planid desc
            limit 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            $delivery_planid = $query->row()->delivery_planid + 1000;            
        }

        $this->config->load('client_db');
        $dbname = CLIENT_DB_PREFIX . $clientid;
        // create DB
        $sql = sprintf($this->config->item('client_db_create_script_db'),$dbname);
        $this->db->query($sql);
        // connect to DB
        $this->load->library('multidb',array(
            'dbname' => $dbname,
            'dbserver' => $dbserver));
        $CLIENT_DB = $this->multidb->getClientDb();
        // create request_events table
        $sql = sprintf($this->config->item('client_db_create_script_request_events'),$request_eventsid);
        $CLIENT_DB->query($sql);
        // create delivery_plan table
        $sql = sprintf($this->config->item('client_db_create_script_delivery_plan'),$delivery_planid);
        $CLIENT_DB->query($sql);
        // create visitor table
        $sql = sprintf($this->config->item('client_db_create_script_visitor'),$visitorid);
        $CLIENT_DB->query($sql);
    }

    public function verifyClientDatabase($clientid) {
        $dbname = CLIENT_DB_PREFIX . $clientid;
        if(!$this->verifyClientDatabaseTable($dbname, "request_events", "request_eventsid"))
            return false;
        if(!$this->verifyClientDatabaseTable($dbname, "delivery_plan", "delivery_planid"))
            return false;
        if(!$this->verifyClientDatabaseTable($dbname, "visitor", "visitorid"))
            return false;

        return true;
    }

    private function verifyClientDatabaseTable($dbname, $table, $field) {
        $sql = "show columns from $dbname.$table";
        $query = $this->db->query($sql);
        $res = array();
        foreach ($query->result() as $q) {
            $column = $q->Field;
            if($column == $field)
                return true;  
        }
        return false;      
    }

    /**
     * Lists the smart message given a template id to be displayed as a child table in the admin area
     */
    function listSmsByTemplate($template, $order, $l1, $l2) {
        $l1 = is_numeric($l1) ? $l1 : 0;
        $l2 = is_numeric($l2) ? $l2 : 0;
        $select = " SELECT c.clientid_hash, c.subid, l.name as lpc, p.name as lp FROM client c INNER JOIN landingpage_collection l ON l.clientid = c.clientid "
                . " INNER JOIN landing_page p ON p.landingpage_collectionid = l.landingpage_collectionid "
                . " INNER JOIN smart_message s ON s.landing_pageid = p.landing_pageid "
                . " INNER JOIN sms_template t ON t.sms_template_id = s.sms_template_id "
                . " WHERE s.sms_template_id = ? ORDER BY  $order ";
        $limit = " LIMIT " . (int) $l1 . ", " . (int) $l2;

        $sql = $select . $limit;
        $query1 = $this->db->query($sql, array($template));

        $res1 = array();
        foreach ($query1->result() as $q1) {
            $r = array();
            $r["clientid_hash"] = $q1->clientid_hash;
            $r["subid"] = $q1->subid;
            $r["lpc"] = $q1->lpc;
            $r["lp"] = $q1->lp;
            $res1[] = $r;
        }

        $sql = $select;
        $query2 = $this->db->query($sql, array($template));
        $cnt = count($query2->result());

        return array('Result' => 'OK', 'Records' => $res1, 'TotalRecordCount' => $cnt);
    }

    /**
     * @return array -- the array of available SMS template group id's.
     */
    function listSmsGroups() {
        $sql = " SELECT sms_template_group_id FROM sms_template_group ";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return array('group' => array('sms_template_group_id' => 0));
    }

    /**
     * updates the SMS template in the DB
     */
    function updateSmsTemplate($args) {
        $sql = "UPDATE sms_template SET xml_content = ?, name = ?, message_type = ?, content_type = ?, thumbnail_url = ?, previewimage_url = ?, " .
                " description = ?, sms_template_group_id = ?, sort_order = ? "
                . " WHERE sms_template_id = ? ";
        $this->db->query($sql, $args);
    }

    /**
     * Inserts a new row in sms_template
     */
    function createSmsTemplate($args) {
        $sql = "INSERT INTO sms_template(xml_content, name, message_type, content_type, thumbnail_url, previewimage_url, " .
                " description, sms_template_group_id, sort_order) " .
                " VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, $args);
    }

    /**
     * deletes a template given its ID
     */
    function deleteSmsTemplate($template) {
        if ((int) $template > 0) {
            $sql1 = " DELETE FROM sms_template WHERE sms_template_id = ? "
                    . " AND sms_template_id NOT IN (SELECT sms_template_id FROM smart_message) ";
            $this->db->query($sql1, array($template));
            return array('Result' => 'OK');
        }

        return array('Result' => 'ERROR', 'Message' => 'Error deleting the SMS (TEMPLATEID)');
    }

    /*     * **************************************************************************************************************************** */


    /*     * ******************************************************************************************************************************
     * ************************************* URL FILTER METHODS: LIST, EDIT, UPDATE, DELETE ******************************************
     * ********************************************************************************************************************************* */

    /**
     * Returns the list of URL patterns located in the corresponding table
     */
    function getUrlPatterns($order, $l1, $l2) {
        $l1 = is_numeric($l1) ? $l1 : 0;
        $l2 = is_numeric($l2) ? $l2 : 0;
        $select = " SELECT id, url, pattern FROM url_filter ORDER BY $order ";
        $limit = " LIMIT " . (int) $l1 . ", " . (int) $l2;

        $query1 = $this->db->query($select . $limit);
        $query2 = $this->db->query($select);
        $cnt = count($query2->result());

        return array('Result' => 'OK', 'Records' => $query1->result(), 'TotalRecordCount' => $cnt);
    }

    /**
     * inserts a new row in url_filter
     */
    function insertUrlPattern($args) {
        $sql = " INSERT INTO url_filter(url, pattern) VALUES (?,  ?) ";
        $this->db->query($sql, $args);
        return array('Result' => 'OK');
    }

    /**
     * updates the patterm with the corresponding arguments
     */
    function updateUrlPatterns($args) {
        $sql = " UPDATE url_filter SET url = ?, pattern = ? WHERE id = ? ";
        $this->db->query($sql, $args);
        return array('Result' => 'OK');
    }

    /**
     * Deletes the corresponding url_filter row depending on the ID passed
     */
    function deleteUrlPattern($id) {
        $sql = "DELETE FROM url_filter WHERE id = ? ";
        $this->db->query($sql, array($id));
        return array('Result' => 'OK');
    }

    /*     * **************************************************************************************************************************** */

    /*
     * provide a list of users from table client who match the following criteria:
     * - they have not yet received a mail with code $code (see table emails)
     * - they are not blacklisted (client.blacklisted)
     * - the number of days since they registered until today matches $daysSinceReg
     * - the field client.status is $status
     */

    function getAutoresponderRecipients($code, $daysSinceReg, $status) {
        // compute date of yesterday in mysql format
        $dates = computeDateFromDays($daysSinceReg);
        $startdate = $dates['startdate'];
        $enddate = $dates['enddate'];
        $sql = "select clientid,email,firstname from client 
			where createddate > '$startdate'
			and createddate < '$enddate'
			and status = $status
			and email_validated <> 0
			and tenant = 'blacktri'
			and blacklisted = 0
			and not exists (select 1 from emails where client.clientid = emails.clientid 
			and emails.code='$code')";
        //echo $sql;
        $res = mysql_query($sql);
        $entries = array();
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $entries[] = $row;
        }
        return($entries);
    }

    /*
     * after sending an autoresponder mail, save this fact in the DB to avoid sending it multiple times
     */

    function saveAutoresponderStatus($clientid, $code) {
        $sql = "insert into emails (code,clientid,timestamp)
			values ('$code',$clientid,now())";
        $res = mysql_query($sql);
    }

    /*
     * retrieve list of all active clients
     */

    function getActiveClients() {
        $sql = "SELECT clientid FROM `client` where status in (1,3,6)";
        //echo $sql;
        $res = mysql_query($sql);
        $entries = array();
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $entries[] = $row;
        }
        return($entries);
    }

    function updateUsedQuota($clientid, $startdate, $enddate) {
        $sql = "select count(*) as c from request_events r 
            USE INDEX(date_idx) 
            where r.clientid=$clientid and r.date >'$startdate' 
            and r.date<'$enddate'";
        $result = mysql_query($sql);
        $count = mysql_result($result, 0, 0);
        echo $clientid . " " . $count . "<br>";
    }

    /*
     * reset quota for all clients that have a reset date of today and where the last reset 
     * is older than 2 days
     */

    function resetQuotas() {
        // select all clients to reset
        // if the current day is last day of the month, make sure that clients are selected  where
        // the day in month to reset is > current day
        //$maxdays = date('t',mktime(0, 0, 0, 2, 28, 2013));
        //$currentday = date('j',mktime(0, 0, 0, 2, 28, 2013));
        $maxdays = date('t');
        $currentday = date('j');
        $daylist = "($currentday)";
        if ($currentday == $maxdays) {
            if ($currentday == 28)
                $daylist = "(28,29,30,31)";
            if ($currentday == 29)
                $daylist = "(29,30,31)";
            if ($currentday == 30)
                $daylist = "(30,31)";
            if ($currentday == 31)
                $daylist = "(31)";
        }

        $sql = "SELECT clientid_hash FROM `client` where ";
        $sql .= "((last_quota_reset_date is null and quota_reset_dayinmonth in $daylist) or "; // clients in their first month that have to be resetted today
        $sql .= "datediff(now(),last_quota_reset_date) > 31 or "; // old clients that have not been resetted for a month (because the job did not run)
        $sql .= "((datediff(now(),createddate) > 31) and (last_quota_reset_date is null)) or "; // new clients that have not been resetted before
        $sql .= "(datediff(now(),last_quota_reset_date) > 1 and quota_reset_dayinmonth in $daylist)) "; // old clients that have to be restted today
        $sql .= "and status=6";
        $res = mysql_query($sql);
        $entries = array();
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $cc = $row['clientid_hash'];
            $sql = "update client set used_quota=0,last_quota_reset_date=now() where clientid_hash ='$cc'";
            mysql_query($sql);
            apch_delete("cs_" . $cc); // cached in getclientstatus
        }
    }

    /*
     * heartbeat: check the database for monitoring reasons
     */

    function heartbeat() {
        $salt = substr(md5(uniqid()), 0, 4);
        $this->db->where('code', $salt);
        $this->db->delete('tracecode');
        $this->db->insert('tracecode', array('code' => $salt));
        $sql = "select count(*) as c from tracecode where code='$salt'";
        $result = mysql_query($sql);
        $count = mysql_result($result, 0, 0);
        if ($count == 1)
            $result = true;
        else
            $result = false;
        $this->db->where('code', $salt);
        $this->db->delete('tracecode');
        return $result;
    }

    // migration function: create table entries of collection_goal_conversions
    function createGoalConversions() {
        // update collection_goals and set one arbitrary goal as primary
        $sql = "select landingpage_collectionid from landingpage_collection";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            // for each collection, select one goal and make it primary
            $collectionid = $row['landingpage_collectionid'];
            $sql = "select collection_goal_id from collection_goals 
                where landingpage_collectionid=$collectionid
                and status=1
                limit 1";
            $res2 = mysql_query($sql);
            $goal_id = mysql_result($res2, 0, 0);
            if(!empty($goal_id)) {
                echo ".";
                $sql = "update collection_goals set level=1 where collection_goal_id=$goal_id";
                mysql_query($sql);
            }
        }
        echo "\nInitialized collection_goals with one goal as primary\n";

        // create entries in collection_goals_conversions
        $sql = "select lpc.landingpage_collectionid, lp.landing_pageid, cg.collection_goal_id
            from landingpage_collection lpc, landing_page lp, collection_goals cg
            where lpc.landingpage_collectionid = lp.landingpage_collectionid
            and lpc.landingpage_collectionid = cg.landingpage_collectionid";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $collectionid = $row['landingpage_collectionid'];;
            $pageid = $row['landing_pageid'];
            $goalid = $row['collection_goal_id'];
            $sql = "insert into collection_goal_conversions 
                (landingpage_collectionid, landing_pageid,goal_id) values ($collectionid, $pageid,$goalid)";
            mysql_query($sql);
            echo ".";
        }
        echo "\nCreated entries in collection_goal_conversions\n";
    }

    /**
     * gets all of the clients id's from the client table that are not already into the api_client table
     * Now, With each client id calls the saveApiClient() method of the userapi class, to insert
     * the corresponding entries into the api_client table
     */
    public function createApiUsersFromClients() {
        require_once APPPATH . 'models/userapi.php';
        $userapi = new userapi();

        $q1 = $this->db->select('clientid')
                ->from('client')
                ->where('clientid NOT IN (SELECT clientid FROM api_client)')
                ->get();

        foreach ($q1->result() as $q) {
            $userapi->saveApiClient($q->clientid);
        }

        echo 'done. Go to <a href="/admin">Admin Index</a>';
    }

    /*
     * Migration function: create projects according to BLAC-543
     */
    public function migrateBlac543() {
        $sql = "select landingpage_collectionid from landingpage_collection order by landingpage_collectionid desc";
        $query = $this->db->query($sql);
        foreach ($query->result() as $row) {
            $collectionid = $row->landingpage_collectionid;
            echo "Converting $collectionid ";
            $this->migrateProjectBlac543($collectionid);
            echo " done<br>\n";
        }        
    }

    public function migrateProjectBlac543($collectionid) {
        // get project type, create/restart date
        $sql = "select testtype, creation_date, restart_date
            from landingpage_collection
            where landingpage_collectionid=$collectionid";
        $query = $this->db->query($sql);
        $row = $query->row(); 
        if($query->num_rows() == 1) {
            $testtype = $row->testtype;
            $creation_date = $row->creation_date;
            $restart_date = $row->restart_date;
            if(empty($restart_date))
                $startDate = $creation_date;
            else 
                $startDate = $restart_date;
            if($testtype==1 || $testtype==3) {
                $groupid = -1;
                // handle normal tests (split/visual)
                // retrieve all relevant entries from request_events
                $this->migrateDecisiongroupBlac543($collectionid,$groupid,$startDate);
            }
            else {
                // teaser test
                $sql = "select page_groupid, creation_date, restart_date from page_group
                    where landingpage_collectionid=$collectionid";
                $query1 = $this->db->query($sql);
                foreach ($query1->result() as $row) {
                    $groupid = $row->page_groupid;
                    $creation_date = $row->creation_date;
                    $restart_date = $row->restart_date;
                    if(empty($restart_date))
                        $startDate = $creation_date;
                    else 
                        $startDate = $restart_date;
                    $this->migrateDecisiongroupBlac543($collectionid,$groupid,$startDate);
                }
            }
        }
        else {
            dblog_debug("migrateProjectBlac543: Invalid result for $collectionid");
            return;
        }
    }

    // helper: migrate a single decisiongroup
    private function migrateDecisiongroupBlac543($collectionid,$groupid,$startDate) {
        $sql = "select request_eventsid from request_events 
            where landingpage_collectionid=$collectionid
            and date >= '$startDate'
            and page_groupid = $groupid
            order by request_eventsid asc";
        $query1 = $this->db->query($sql);
        /*
        if($query1->num_rows() > 1000) {
            echo "too many rows...";
            return;
        }
        */
        foreach ($query1->result() as $row1) {
            $eventsid = $row1->request_eventsid;

            $sql = "select type,conversion_value,landing_pageid,goal_id from request_events 
                where request_eventsid=$eventsid";
            $query3 = $this->db->query($sql);
            $row3 = $query3->row(); 

            $pageid = $row3->landing_pageid;
            $type = $row3->type;
            $conversionValue = $row3->conversion_value;
            $goalid = $row3->goal_id;
            if(empty($goalid))
                $goalid=9999;
            if($type==1) {
                if(!isset($requests[$pageid]['impressions']))
                    $requests[$pageid]['impressions'] = 0;
                $requests[$pageid]['impressions']++;

                $impressions = $requests[$pageid]['impressions'];                
                $conversions = 0;                
                $conversion_value_aggregation = 0;                
            }
            else {
                if(!isset($requests[$pageid][$goalid]['conversions']))
                    $requests[$pageid][$goalid]['conversions'] = 0;
                if(!isset($requests[$pageid][$goalid]['conversion_value_aggregation']))
                    $requests[$pageid][$goalid]['conversion_value_aggregation'] = 0;
                $requests[$pageid][$goalid]['conversions']++;                                         
                $requests[$pageid][$goalid]['conversion_value_aggregation'] += $conversionValue;                                         

                $impressions = $requests[$pageid]['impressions'];                
                $conversions = $requests[$pageid][$goalid]['conversions'];                
                $conversion_value_aggregation = $requests[$pageid][$goalid]['conversion_value_aggregation'];                
            }
            $sql = "update request_events set impressions=$impressions, 
                conversions=$conversions, 
                conversion_value_aggregation=$conversion_value_aggregation 
                where request_eventsid=$eventsid";
//echo "$sql<br>\n";
            $query2 = $this->db->query($sql);
            echo ".";
        }    
    }
}

?>