<?php

class mvt extends CI_Model {

    function __construct() {
        parent::__construct();
        //load model
        $this->load->model('landingpagecollection');
        $this->load->model('optimisation');
        $this->load->model('user');
        // load mysql connection
        $this->load->database();
    }

    /**
     * load collection from database
     * 
     * @param Int $clientid
     * @param Int $lpcid
     * @return Array
     */
    function loadlandingpagecollection($clientid, $lpcid) {
        $factors = array();
        $url = $this->loadlandingpagecollectioncontrol($clientid, $lpcid);
        if ($lpcid > 0) {
            $fcs = $this->db->query("select * from mvt_factor where landingpage_collectionid = $lpcid");
            foreach ($fcs->result() as $fc) {
                $levels = array();
                $lvls = $this->db->query("select * from mvt_level where mvt_factor_id = {$fc->mvt_factor_id}");
                foreach ($lvls->result() as $lvl) {
                    $level = array();
                    $level["n"] = $lvl->name;
                    $level["v"] = html_entity_decode($lvl->level_content);
                    $level["i"] = $lvl->mvt_level_id;
                    $levels[] = $level;
                }
                $factors[$fc->dom_path] = array("id" => $fc->mvt_factor_id, "name" => $fc->name, "levels" => $levels);
            }
        }
        //get tracking code
        $tk = $this->getLPCTrackingCode($lpcid, OPT_TESTTYPE_MVT);
        $rs = $this->db->query("select name,testgoal from landingpage_collection where clientid = $clientid and landingpage_collectionid = $lpcid");
        $rs = $rs->result();

        return array("url" => $url, "lpcid" => $lpcid, "lpccode" => $tk["lpccode"],
            "lpctrackingcode_control" => $tk["lpctrackingcode_control"],
            "lpctrackingcode_success" => $tk["lpctrackingcode_success"],
            "testname" => $rs[0]->name,
            "testgoal" => $rs[0]->testgoal * 1,
            "factors" => $factors
        );
    }

    /**
     *  get collection name ( url );
     * 
     * @param Int $clientid
     * @param Int $lpcid
     * @return String
     */
    function loadlandingpagecollectioncontrol($clientid, $lpcid) {
        //get controll
        $factors = array();
        $rs = $this->db->query("select lp_url from landing_page A inner join landingpage_collection B on A.landingpage_collectionid = B.landingpage_collectionid where B.clientid = $clientid and B.landingpage_collectionid = $lpcid and A.pagetype = 1");
        $rs = $rs->result();
        $url = $rs[0]->lp_url;
        return $url;
    }

    /**
     * Gets the canonical url of the control variant given a LPC id for the given client
     * @param Int $clientid
     * @param Int $lpcid
     * @return String
     */
    function loadlandingpagecollectioncontrolpattern($clientid, $lpcid) {
        $rs = $this->db->query("select canonical_url from landing_page A "
                . "inner join landingpage_collection B on A.landingpage_collectionid = B.landingpage_collectionid "
                . "where B.clientid = $clientid and B.landingpage_collectionid = $lpcid and A.pagetype = 1");
        $rs = $rs->result();
        $url = $rs[0]->canonical_url;
        return $url;
    }

    /**
     *  get collection usefull details
     * 
     * @param Int $clientid
     * @param Int $lpcid
     * @return Array
     */
    function loadlandingpagecollectiondetails($clientid, $lpcid) {
        $factors = array();
        $sql = " SELECT lpc.tracking_approach, lpc.tracked_goals, lpc.testtype, lpc.start_date, lpc.end_date, " .
                " lpc.ignore_ip_blacklist, lpc.personalization_mode, lp.rule_id " .
                " FROM landingpage_collection lpc INNER JOIN landing_page lp ON lp.landingpage_collectionid = lpc.landingpage_collectionid " .
                " WHERE lpc.clientid = $clientid and lpc.landingpage_collectionid = $lpcid AND lp.pagetype = 1 ";
        $rs = $this->db->query($sql);
        $rs = $rs->result();
        return $rs[0];
    }

    /**
     * @param int $lpcid
     * @return int (1 or 0)
     */
    function LpcIsSmartMessage($lpcid) {
        $sql = "SELECT smartmessage FROM landingpage_collection WHERE landingpage_collectionid = ? ";
        $query = $this->db->query($sql, array($lpcid));
        return $query->row()->smartmessage;
    }

    /**
     * @param int $clientid
     * @return string -- the ; separated list of ip's in the blacklist (if any)
     */
    function getIpBlacklistByClient($clientid) {
        $sql = "SELECT ip_blacklist FROM client WHERE clientid = ? ";
        $query = $this->db->query($sql, array($clientid));
        return $query->row()->ip_blacklist;
    }

    function checktestname($testname, $uid) {
        $uid*=1;
        $testname = strtolower($testname);
        if (trim($testname) == "" || $uid == 0)
            return false;
        $sql = "select count(*) as count from landingpage_collection where LOWER(name) = '$testname' and clientid = $uid";
        $query = $this->db->query($sql, array($email, $ownemail));
        $count = $query->row()->count;
        return $count <= 0;
    }

    /*
     *  example function: retrieve total number of impressions from a collection
     */

    function testfunction($collectionid) {
        $query = "select sum(impressions) from landing_page where landingpage_collectionid=$collectionid";
        $result = mysql_query($query);
        $resultArray = mysql_fetch_row($result);
        return $resultArray[0];
    }

    /*
     * helper-function
     * generate tracked goals for a new test, based on the goals checked
     */

    function getTrackedGoals($conversiongoal) {
        if (!is_array($conversiongoal) || count($conversiongoal) == 0)
            return "----";
        $ret = "";

        //conver to uppercase
        $conversiongoal = array_map('strtoupper', $conversiongoal);

        //for EC
        if (in_array("EC", $conversiongoal))
            $ret .= "E";
        else
            $ret .="-";
        //for AC
        if (in_array("AC", $conversiongoal))
            $ret .= "A";
        else
            $ret .="-";
        //for CC
        if (in_array("CC", $conversiongoal))
            $ret .= "C";
        else
            $ret .="-";
        //for SPC
        if (in_array("SPC", $conversiongoal))
            $ret .= "S";
        else
            $ret .="-";

        return $ret;
    }

    function getCovertedTrackedGoals($conversiongoal) {
        $ret = array();
        //conver to uppercase
        $conversiongoal = strtoupper($conversiongoal);
        //for EC
        if (strpos($conversiongoal, "E") !== false)
            $ret[] = "EC";
        //for AC
        if (strpos($conversiongoal, "A") !== false)
            $ret[] = "AC";
        //for CC
        if (strpos($conversiongoal, "C") !== false)
            $ret[] = "CC";
        //for SPC
        if (strpos($conversiongoal, "S") !== false)
            $ret[] = "SPC";

        return $ret;
    }

    /*
     * helper-function
     * generate and check landing page tracking code
     */

    function getLPCTrackingCode($lpid = 0, $testtype = OPT_TESTTYPE_MVT) {
        if ($lpid == 0) {
            $code = $this->generateLPCCode();
            $rs = $this->db->query("select count(*) as nr from landingpage_collection where code='$code'");
            $rs = $rs->result();
            if ($rs[0]->nr > 0)
                $code = $this->generateLPCCode();
            $testname = "";
            $testgoal = "";
        }
        else {
            $rs = $this->db->query("select code,name,testgoal from landingpage_collection where landingpage_collectionid=$lpid");
            $rs = $rs->result();
            $code = $rs[0]->code;
            $testname = $rs[0]->name;
            $testgoal = $rs[0]->testgoal * 1;
        }

        //get client hash
        $this->load->library('session');
        $clientid = $this->session->userdata('sessionUserId');
        $userdata = $this->user->clientdatabyid($clientid);

        $data["lpccode"] = $code;
        $data["lpctrackingcode_control"] = $this->generateTrackingCode($code, $testtype, 'control');
        $data["lpctrackingcode_success"] = $this->generateTrackingCode($code, $testtype, 'success');
        $data["lpctrackingcode_variant"] = $this->generateTrackingCode($code, $testtype, 'variant');
        $data["lpctrackingcode_ocpc"] = $this->generateTrackingCode($userdata['clientid_hash'], $testtype, 'ocpc');
        $data["testname"] = $testname;
        $data["testgoal"] = $testgoal;

        return $data;
    }

    /*
     * helper-function
     * generate landing page code
     */

    function generateTrackingCode($code, $testtype, $success = true, $ga = false) {
        return sprintf($this->config->item('trackingcode'), $code, $this->config->item('trackinglib_host'));
    }

    /*
     * helper-function
     * generate landing page code, can be used for A/B and MVT code generation
     */

    function generateLPCCode() {
        $code = $this->generateRandomString(32, "BT-");
        return $code;
    }

    /*
     * helper-function
     * generate random sequence strings md5 encoded with prefix and choosen length
     */

    function generateRandomString($size = 32, $prefix = "") {
        $id = uniqid($prefix, true) . "-" . time();
        $code = $prefix . substr(md5($id), 0, $size - strlen($prefix));
        return $code;
    }

    /*
     * helper-function
     * filter variant value code, ie removing google showad_js include.
     */

    function filterVariantCode($variant) {
        $variant = str_replace($this->config->item('google_showad_js'), '', $variant);
        return $variant;
    }

    function fixProxyBug($collectionid) {
        $base = rtrim($this->config->item('editor_url'), '/');
        $base = str_replace("/", "\\/", $base);
        lazyLoadDB();
        //$search = mysql_real_escape_string('https:\\/\\/application.etracker.com\\/dc\\/opt\\/index.php\\/http:');
        $search = mysql_real_escape_string($base . 'http:');
        $replace = mysql_real_escape_string('http:');
        $sql = "update landing_page set dom_modification_code = replace(dom_modification_code,'$search','$replace') where landingpage_collectionid=$collectionid";
        mysql_query($sql);
        //$search = mysql_real_escape_string('https:\\/\\/application.etracker.com\\/dc\\/opt\\/index.php\\/https:');
        $search = mysql_real_escape_string($base . 'https:');
        $replace = mysql_real_escape_string('https:');
        $sql = "update landing_page set dom_modification_code = replace(dom_modification_code,'$search','$replace') where landingpage_collectionid=$collectionid";
        mysql_query($sql);
        $base = str_replace("index.php\\/", "index.php", $base);
        $search = mysql_real_escape_string($base);
        //$search = mysql_real_escape_string('https:\\/\\/application.etracker.com\\/dc\\/opt\\/index.php');
        $replace = mysql_real_escape_string('');
        $sql = "update landing_page set dom_modification_code = replace(dom_modification_code,'$search','$replace') where landingpage_collectionid=$collectionid";
        mysql_query($sql);
        $search = mysql_real_escape_string('\\/btproxy\\/http\\/');
        $replace = mysql_real_escape_string('http:\\/\\/');
        $sql = "update landing_page set dom_modification_code = replace(dom_modification_code,'$search','$replace') where landingpage_collectionid=$collectionid";
        mysql_query($sql);
        $search = mysql_real_escape_string('\\/btproxy\\/https\\/');
        $replace = mysql_real_escape_string('https:\\/\\/');
        $sql = "update landing_page set dom_modification_code = replace(dom_modification_code,'$search','$replace') where landingpage_collectionid=$collectionid";
        mysql_query($sql);
    }

}

?>