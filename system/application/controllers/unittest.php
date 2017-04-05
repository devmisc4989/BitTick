<?php

class unittest extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('apimodel');
        $this->load->model('optimisation');
        $this->load->model('landingpagecollection');
        $this->load->model('persomodel');
        $this->load->library('session');
        $this->load->helper('oauth');
        $this->load->library('calculation');
    }

    function index() {
    }

    /*
     * Delete the memory-cache for a given client. used to do this remotely (e.g. from automated tests)
     */

    function flushApcCache($cc) {
        $apiuser = authenticate();
        //dblog_debug("flush $cc");
        $this->optimisation->flushAPCCacheForClient($cc);
        //echo "OK";
    }

    function flushCollectionCache($collectionid) {
        $apiuser = authenticate();
        //dblog_debug("flush $cc");
        $this->optimisation->flushCollectionCache($cc);
        //echo "OK";
    }

    function flushTeasertestDeliveryPlanCache($visitorid,$collectionid) {
        $key = "ttdp_" . $visitorid . "_" . $collectionid;
        apch_delete($key);
        echo "OK";
    }

    /*
     * Return the IP of the remote client
     */

    function getVisitorIp() {
        echo $_SERVER['REMOTE_ADDR'];
    }

    /*
     * Help function for TC
     * Set a cookie cntcookie with a clients subid to test wether the optimization web service bto/d 
     * takes care of the cookie (set for users who do not want to be tracked by etracker)
     */

    function cntcookie($subid) {
        setcookie('cntcookie', 'dummy1111,' . $subid . ',dummy2222', time() + 31536000);
        echo "// unittest cntcookie $subid";
    }   

    /*
     *  Test the pattern matching function
     *  $pattern: string with a pattern
     *  $urls: array with urls to test against pattern
     *  return: array in same order as $urls with true/false for each result
     */
    function testContainsPattern() {
        $body = unserialize(file_get_contents('php://input'));
        $pattern = $body['pattern'];
        //echo $pattern;
        $urls = $body['urls'];
        $result = array();
        foreach($urls as $url) {
            $r = $this->optimisation->containsPattern($url, $pattern);
            $result[] = ($r==true) ? '1' : '0';
        }
        echo serialize($result);
    } 

    function showSession() {
        echo "userid: " . $this->session->userdata('sessionUserId');
        echo "editorcollectionid: " . $this->session->userdata("editor_collectionid");
    }

    function setSessionVariable($name,$value) {
        $this->session->set_userdata($name,$value);
    }

    function calcZscore() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize(Calculation::calcZscore($data[0],$data[1],$data[2]));
    }

    function getConfidence() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize(Calculation::getConfidence($data));
    }

    function getProjectKPI() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize($this->optimisation->getProjectKPI($data[0],$data[1],$data[2]));
    }

    function transformProjectKPIResultset() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize($this->optimisation->transformProjectKPIResultset($data));
    }

    function calculateKpiResultsetConversionrates() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize($this->optimisation->calculateKpiResultsetConversionrates($data[0],$data[1]));
    }

    function deriveResultForPages() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize($this->optimisation->deriveResultForPages($data));
    }

    function updateSlotsWithoutProgressChange() {
        $data = unserialize(file_get_contents('php://input'));
        echo serialize($this->optimisation->updateSlotsWithoutProgressChange($data['0'],$data['1']));
    }

    function determineCollectionConflicts() {
        $context = unserialize(file_get_contents('php://input'));
        $clientid = $context['clientid'];
        $collectionid = $context['collectionid'];
        $projects = $this->optimisation->getActiveProjectsOnSameUrl($clientid, $collectionid);
        $result = $this->optimisation->determineCollectionConflicts($projects,$collectionid);
        echo serialize($result);        
    }      

    function updateSlots() {
        $context = unserialize(file_get_contents('php://input'));
        $mode = $context[0];
        $collectionid = $context[1];
        $winnerid = $context[2];
        $page_groupid = $context[3];
        $result = $this->optimisation->updateslots($mode, $collectionid, $winnerid, $page_groupid);
        echo serialize($result);        
    }      

    function getCollectionStatus() {
        $context = unserialize(file_get_contents('php://input'));
        $collectionid = $context[0];
        $result = $this->optimisation->getcollectionstatusById($collectionid);
        echo serialize($result);        
    }      


}

?>