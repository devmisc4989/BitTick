<?php
require_once 'vendor/autoload.php';
use GeoIp2\Database\Reader;

class bto extends CI_Controller {
    
    
    private $visitor = array();

    function __construct() {
        parent::__construct();
        $this->load->model('optimisation');
        $this->load->library('email');
        $this->load->library('Jshandler');
    }

    /*
     * controller for diagnose mode
     */

    public function diagnose() {
        // read, normalize and sanitize input
        $tracecode = $this->input->get('tracecode', TRUE) ? urldecode($this->input->get('tracecode', TRUE)) : 'NA';
        if (!ctype_alnum($tracecode))
            $tracecode = 'NA';
        dblog_debug('<b>BTODIA-10-010/--- bto diagnose processing start ---</b>');

        $dg = array();
        $dg['v'] = $this->input->get('v', TRUE) ? urldecode($this->input->get('v', TRUE)) : 'NA';
        if (!ctype_alnum($dg['v']))
            $dg['v'] = 'NA';
        $dg['et_pagename'] = $this->input->get('et_pagename', TRUE) ? $this->input->get('et_pagename', TRUE) : 'NA';
        $qrs = urldecode($this->input->get('qrs'));
        if ($qrs == "undefined") {
            $qrs = "NA";
        }
        if (empty($qrs)) {
            $qrs = "NA";
        }
        if (trim($qrs) == '') {
            $qrs = "NA";
        }
        $dg['noWS'] = $this->input->get('noWS', TRUE) ? urldecode($this->input->get('noWS', TRUE)) : 'NA';
        $dg['qrs'] = $qrs;

        // remove the attached patameters that identify< the diagnose mode from the URL
        $pageurl = urldecode($this->input->get('pg')); // url of the page which triggers the request
        $pageurl = str_replace("?_trt=true","",$pageurl);
        $pageurl = str_replace("&_trt=true","",$pageurl);
        $pageurl = str_replace("&tracecode=" . $tracecode,"",$pageurl);
        $dg['pg'] = $pageurl;
        
        $dg["cc"] = $this->input->get('cc', TRUE) ? urldecode($this->input->get('cc', TRUE)) : 'NA';
        if (!ctype_alnum($dg["clienthash"]))
            $dg["clienthash"] = 'NA';
        $dg["ecl"] = $this->input->get('ecl', TRUE) ? urldecode($this->input->get('ecl', TRUE)) : 'NA'; // list of collection codes separated by : 
        // now perform some checks
        // is the clientcode blocked for some reason? if so, which one?
        //$clientstatus = $this->optimisation->getclientstatus($dg['cc']);      
        //$dg["clientstatus"] = $clientstatus;
        $dg['client'] = $this->optimisation->getClient($dg['cc']);
        // check for active tests
        $dg['hasActiveTests'] = $this->optimisation->hasActiveLandingpageCollections($dg['cc']);

        // check for matching tests
        $matching_tests = $this->optimisation->getMatchingLandingPagesForClient($dg['cc'], $dg["pg"], $dg['et_pagename'], true);
        $dg['matching_tests'] = $matching_tests['data'];
        // retrieve number of matching tests
        $number_match = 0;
        $first_matching_testid = -1;
        foreach ($dg['matching_tests'] as $mt) {
            if ($mt[2] == 1) {
                $number_match++;
            }
        }
        if ($number_match > 1)
            $number_match = 2;
        $dg['match'] = $number_match;
        // save diagnose data
        if ($tracecode != 'NA')
            $this->optimisation->saveDiagnoseData($dg, $tracecode);

        echo "// bto diagnose mode";
    }

    /*
     * front controller for optimisation web service
     *  process input, dispatch requests
     */

    public function d() {
        global $visitor;

        dblog_debug('<b>BTO-10-010/--- bto webservice request processing start ---</b>');
        
        // parse request and fill visitor array
        $visitor = $this->getVisitorDataFromRequest();
        // sanity check
        if (!ctype_alnum($visitor["clienthash"]))
            return;
        dblog_debug("BTO-10-014/ tracking-code\ncc:" . $visitor["clienthash"]);

        // optionally show debug information on device and geolocation
        if($this->uri->segment(3) == "visitor") {
            $this->showVisitorDebuggingData($visitor);
            die();
        }

        // analyze querystring values that have impact on conversions (cv,ct,cl,et_target,et_tonr,et_tval)
        $request = array();
        $conversionDispatchResult = $this->dispatchConversionParameters($visitor);
        $visitor["is_pageview"] = $conversionDispatchResult['is_pageview'];
        $visitor["action_type"] = $conversionDispatchResult['action_type'];
        $visitor['conversion_link'] = $conversionDispatchResult['conversion_link'];
        dblog_debug('BTO-10-013/ action-type:' . $visitor["action_type"] . ' conversion-qualifier:' . $visitor['conversion_link'] . ' is_pageview:' . $visitor["is_pageview"]);

        // retrieve data for the client from Cache/DB
        $clientstatus = $this->optimisation->getclientstatus($visitor["clienthash"]);
        // set client specifig configuration values
        $this->setClientConfigValues($clientstatus['config']);
        // activate db logging based on configured IP
        $this->checkIpRestrictedDebugging($visitor["ip"]);

        $visitor["clientid"] = $clientstatus['clientid'];
        $visitor["account_key2"] = $clientstatus['account_key2']; // we need this for personalization
        dblog_debug('BTO-10-013/ client:' . $visitor["clientid"]);
        
        // check wether this client is active, has not exceeded the quota and has active tests
        $requestIsValid = $this->checkRequestValidity($clientstatus);
        $visitor['isIpBlacklisted'] = $this->getClientIPBlacklistStatus($visitor["ip"],$clientstatus['ip_blacklist']);
        
        // if invalid request: show the no-further-requests response and stop here, 
        // except if this is a preview request
        if (!$requestIsValid && !$visitor["preview"]) {
            dblog_debug('BTO-30-011/ client with this trackingcode not active, no quota or no tests');
            $data = array(
                'collectioncode' => $visitor["collectioncode"],
                'clientstatus' => $clientstatus
            );
            $this->load->view('webservice_nows', $data);
            return;                
        }

        // retrieve all projcts that match the current URL or et_pagename
        $matchingpages = $this->optimisation->getMatchingLandingPagesForClient($visitor["clienthash"], $visitor["pageurl"], $visitor["et_pagename"]);            
        // eventually activate debugging when a specific project is requested
        $debugProjectId = $this->config->item('DEBUG_PROJECT_ID');
        if(isset($debugProjectId)) {
            foreach($matchingpages['data'] as $entry) {
                if($entry['ci'] == $this->config->item('DEBUG_PROJECT_ID')) {
                    $this->config->set_item('LOGLEVEL', LOG_LEVEL_DEBUG);
                    dblog_debug('<b>BTO-10-010/--- bto webservice debugging start ---</b>');
                }
            }                    
        }
        // retrieve an array of events from the pages matching the current URL
        $matchingresult = $this->getEventsFromMatchingURLs($matchingpages['data'],$visitor);
        $trackingEvents = $matchingresult['trackingEvents'];
        $visitor['collectionIdsForThisUrl'] = $matchingresult['collectionIdsForThisUrl'];

        // add events to the array which we retrieve from "action goals" (klicks and similar) 
        $trackingEvents = array_merge($trackingEvents,$this->getEventsFromActionGoals($visitor));

        // add events to the array which we retrieve from thr teaser test click history 
        $trackingEvents = array_merge($trackingEvents,$this->getEventsFromTeasertestClickHistory($visitor));

        // add events to the array which we retrieve from querystring parameters in case 
        // of a split test redirection (this could be a webtracking event) 
        $trackingEvents = array_merge($trackingEvents,$this->getEventsFromQuerystring($visitor));

        // get a visitor ID from cookies or false if new visitor
        $visitor['visitorid'] = $this->getVisitorId($visitor);

        // in case we are in preview mode, delete the events array and add only one event special for the preview
        if (isset($visitor["preview"]) && ($visitor["preview"])) {
            $trackingEvents = null;
            $event = array();
            $event['eventtype'] = "impression";
            $event['collectionid'] = $visitor['collectionIdForThisUrl'];
            $trackingEvents[] = $event;
        }
        
        dblog_debug("BTO-60-010/ now process a list of " . count($trackingEvents) . " events to process " . print_r($trackingEvents,true));

        // iterate over all events and evaluate them. For each, derive an action code which will result
        // in processing something and/or producing a response
        $webserviceResponse = array();
        $webserviceResponse['type'] = false;    // stores the type of result:
                                                // - 'REDIRECT': one redirect as result of one split test
                                                // - 'INJECTION': dom injection as result of one or more visual tests and/or smart messages
        $webserviceResponse['webtracking'] = false; // stores an array with data to be passed to webtracking
        $webserviceResponse['dom_code'] = false; // stores an aggregated domcode to be injected, result of one or more Visual Tests and/Smart Messages
        $webserviceResponse['redirect'] = false; // stores a redirect URL to be injected, result of a split test
        $webserviceResponse['dom_triggered_activation'] = false; // stores an array for conditional injections
        $visitor['webserviceResponse'] = $webserviceResponse;

        foreach ($trackingEvents as $event) {
            $cstatus = $event['collectiondetails'];
            if ($event['eventtype'] == 'impression') {
                if($cstatus['smartmessage'] == 1)
                    $event['smart_messages'] = true;
                else
                    $event['smart_messages'] = false;
            }

            if (!$visitor["preview"]) {
                // get some more data that will be needed to process the event
                if(isset($event['collectioncode'])) {
                    $cstatus = $this->optimisation->getcollectionstatus($event['collectioncode']);                
                    $event['collectiondetails'] = $cstatus;
                    $event["collectionid"] = $cstatus["collectionid"];
                }
                if(isset($event['collectionid'])) {
                    $cstatus = $this->optimisation->getcollectionstatusById($event['collectionid']);                
                    $event['collectiondetails'] = $cstatus;
                    $event["collectioncode"] = $cstatus["collectioncode"];
                }
                $event["testtype"] = $cstatus["testtype"]; 

                dblog_debug("BTO-60-011/ process event:" . print_r($event, true));
                // get visitor history, does not apply for teaser tests
                $history = $this->getVisitorHistoryInCollection($visitor,$event);                  
                if ($event['eventtype'] == 'impression') {
                    if($event['testtype'] == OPT_TESTTYPE_TEASER) {
                        $event = $this->retrieveActionForTeasertestEvent($event,$history,$visitor);
                        $action = $event['action'];
                    }
                    else {
                        $event = $this->retrieveActionForImpressionEvent($event,$history,$visitor);
                        $action = $event['action'];
                    }
                    // if we have an impression event, there might be some event handlers to be injected for certain
                    // types of goals. Retrieve information which will later be passed to the webservice view
                    $event['impressionEventJSEventHandlers'] = $this->jshandler->getJSEventHandlersForImpressionEvent($visitor,$event);
                    $event['impressionEventDomTriggeredGoalHandlers'] = $this->jshandler->getDomTriggeredGoalHandlersForImpressionEvent($visitor,$event);
                }
                if ($event['eventtype'] == 'conversion') {
                    if($visitor['visitorid']) { // only if visitorid (previous impression)
                        $event = $this->retrieveActionForConversionEvent($event,$history,$visitor["collectionIdsForThisUrl"]);
                        $action = $event['action'];                   
                    }
                    else {
                        $action = "no_operation";                        
                    }
                }
                if($event['eventtype'] == 'persogoal') {
                    // ensure we have a visitor ID
                    if(!$visitor['visitorid']) {
                        $visitorid = $this->optimisation->insertvisitorid();
                        $this->setVisitorId($visitorid);
                        dblog_debug("BTO/-60-009 new visitorid: $visitorid");                                           
                    }
                    $action = "process_persogoal";
                }
                if($event['eventtype'] == 'updatable_conversion') {
                    $action = "process_updatable_conversion";
                }
                if($event['eventtype'] == 'webtracking') {
                    $action = "process_webtracking";
                }
            } else {
                dblog_debug('BTO-60-011b/ preview action for landing page id ' . $visitor["lpid"]);
                $action = "deliver_preview";
            }

            // use action variable to dispatch the request
            dblog_debug('BTO-60-012/ dispatch done, action: ' . $action . " event:" . print_r($event,true));
            switch($action) {
                case "deliver_old_variant":
                    $visitor['webserviceResponse'] = $this->handleOldVariantResponse($event,$visitor);
                    break;
                case "deliver_new_landing_page":
                    $visitor['webserviceResponse'] = $this->handleNewVariantResponse($event,$visitor);
                    break;
                case "deliver_teaser_test_injections":
                    $visitor['webserviceResponse'] = $this->handleTeaserTestResponse($event,$visitor);
                    break;
                case "process_webtracking":
                    $visitor['webserviceResponse'] = $this->handleWebtrackingResponse($event,$visitor);
                    break;
                case "process_conversion":
                    $this->processconversion($event['collectionid'], $event['collectioncode'], $event['oldlandingpageid'], $event['goalid'], $event['goallevel'], $visitor);
                    break;
                case "process_updatable_conversion":
                    $this->processUpdatableConversion($event, $visitor);
                    break;
                case "count_deferred_impression":
                    $this->countDeferredImpression($event['oldlandingpageid'],$visitor, $event["collectionid"], $event["collectioncode"], $event["collectiondetails"]["sample_time"]);
                    break;
                case "process_persogoal":
                    $this->processPersogoalHistory($event['goalid'], $visitor);
                    break;
                case "deliver_preview":
                    $previewResponse = $this->handlePreviewResponse($visitor);
                    break;
            }
        } // end foreach

        // if one of the events has produced a meaningful response, it has been stored in webserviceResponse
        // deliver a no-operation-response if not
        $responseType = $visitor['webserviceResponse']['type'];
        $responseExcludes = $visitor['webserviceResponse']['exclude'];
        if(isset($previewResponse)) {
            echo $previewResponse;
        }
        else if($responseType) {
            $visitor['webserviceResponse']['visitorid'] = $visitor['visitorid'];
            dblog_debug('BTO-60-013/ rendering response - injection or redirection');
            echo $this->load->view('webservice', array('response' => $visitor['webserviceResponse']),true);            
        }
        else if(sizeof($responseExcludes) > 0) {
            dblog_debug('BTO-60-013/ rendering response - exclude');
            echo $this->load->view('webservice', array('response' => $visitor['webserviceResponse']),true);            
        }
        else {
            dblog_debug('BTO-60-013/ rendering response - no operation');
            echo $this->load->view('webservice_noop', array('visitorid' => $visitor["visitorid"]),true);            
        }
    }

    /*
     *  New unique visitor creating an impression event: evaluate a new page to deliver, recalculate db variables 
     *  Return "NA" if control shall be delivered or URL of page variant else
     */

    private function shownewpage($visitor, $event, $pages, $matchingpageids) {
        $collectionid = $event["collectionid"];
        $collectioncode = $event["collectioncode"];
        $sample_time = $event["collectiondetails"]["sample_time"];
        $deferred_counting = $event["deferred_counting"];
        $quota_count = $event['quota_count'];

        $this->optimisation->flushRequestEventsCache($visitor["visitorid"], $collectionid);
        
        // check for a variant id in cookie which shall be forced
        if($this->input->cookie('BT_lpid')) {
            $forceVariant = $this->input->cookie('BT_lpid');
            // does this decision belong to the project?
            if(isset($pages[$forceVariant])) {
                $matchingpageids = array($forceVariant);
            }
        }
        $result = $this->optimisation->selectnewpage($collectionid,$visitor,$deferred_counting,$pages,$matchingpageids,$quota_count,$ipBlacklisted);
        $input["oldlandingpageurl"] = $result["landingpageurl"];
        $input["oldlandingpageid"] = $result["landingpageid"];

        if(!$ipBlacklisted)
            $action = $this->optimisation->evaluateImpact($collectionid);
        // if it is control page
        if ($result["landingpagetype"] == OPT_PAGETYPE_CTRL) {
            return array(
                "dom_code" => "", 
                "landingpageurl" => "NA", 
                "landingpageid" => $result["landingpageid"], 
                "landingpagename" => $result["landingpagename"], 
                "landingpagetype" => $result["landingpagetype"]
            );
        } else {
            return array(
                "dom_code" => $result["dom_code"], 
                "landingpageurl" => $result["landingpageurl"], 
                "landingpageid" => $result["landingpageid"], 
                "landingpagename" => $result["landingpagename"], 
                "landingpagetype" => $result["landingpagetype"], 
                "rulename" => $result["rulename"],
                "sms_id" => $result["sms_id"],
            );
        }
    }
    
    private function countDeferredImpression($pageid, $visitor, $collectionid, $collectioncode, $sample_time) {
        $this->optimisation->flushRequestEventsCache($visitor["visitorid"], $collectionid);
        dblog_debug("countDeferredImpression, pageid:$pageid,collectionid:$collectionid, collectioncode:$collectioncode, sample_time:$sample_time");
        $this->optimisation->countImpression($pageid,$collectionid,$visitor,0,true);        
    }

    private function processPersogoalHistory($goalid, $visitor) {
        dblog_debug("BTO / processPersogoalHistory, goalid:$goalid,visitorid:" . $visitor['visitorid']);
        $this->optimisation->updateVisitorRuleConditionHistory($visitor['visitorid'],$goalid, $visitor['clientid']);        
    }

    /*
     *  New unique visitor creating a conversion event. Process the event and recalculate 
     *  The user environment variables and database.
     */
    private function processconversion($collectionid, $collectioncode, $pageid, $goalid, $goallevel, $visitor) {
        $this->optimisation->flushRequestEventsCache($visitor["visitorid"], $collectionid);
        // track conversion and event
        $this->optimisation->conversion($collectionid, $pageid, $goalid, $goallevel, $visitor);
        if($goallevel==1) { // changes in significance happen only for the primary goal
            $this->optimisation->evaluateImpact($collectionid);
        }
    }

    /*
     *  Updatable Conversion. As of now this can only be counting of page impression lift
     *  in a teaser test.
     */
    private function processUpdatableConversion($event, $visitor) {
        dblog_debug("BTO-60-034/ process updatable conversion");
        // get the visitor request history
        $visitorRequestEvents = $this->optimisation->getRequestEvents($visitor["visitorid"], $event["collectionid"],'2000-01-01 00:00:00');
        // filter for the page id and check for conversions and impressions
        $hasImpression = false;
        $hasConversion = false;
        $conversionValue = 0;
        $conversionRequestEventsId = -1;
        $groupid = -1;
        foreach($visitorRequestEvents as $page) {
            if($page['0'] == $event["pageid"]) {
                if($page['1'] == 1) {
                    $hasImpression = true;
                    $groupid = $page['7'];                    
                }
                if(($page['1'] == 2) && ($page['4'] == $event["goalid"])) {
                    $hasConversion = true;
                    $conversionValue = $page['5'];
                    $conversionRequestEventsId = $page['3'];
                }
            }
        }

        if($hasImpression) {
            if(!$hasConversion || ($hasConversion && $conversionValue!=$event["conversionValue"])) {
                $this->optimisation->updatableConversion(
                        $visitor["clientid"],
                        $event["collectionid"], 
                        $groupid, 
                        $event["pageid"], 
                        $event["goalid"], 
                        $visitor["visitorid"],
                        $conversionValue,
                        $event["conversionValue"],
                        $conversionRequestEventsId);
                // check if there is a combined goal in this test. If so, calculate it's conversion value
                $this->optimisation->evaluateImpact($event["collectionid"],$groupid);                    
            }
        }
    }

    /*
     * helper function: concat a URL and a querystring, handling ? and & correctly
     */

    private function concat_urlquery($url, $query) {
        $curlquery = $url;
        // assumption: empty querystring has value "NA"
        if ($query != "NA") {
            // is there a querystring in url?
            if (strrchr($url, '?')) {
                // yes
                $curlquery = $curlquery . '&' . $query;
            } else {
                //no querystring yet
                $curlquery = $curlquery . '?' . $query;
            }
        }
        return($curlquery);
    }

    /*
     * helper function: extract host + domain from the referrer
     */

    private function referrersource($refsource = '') {
        if ($refsource != '') {
            $refsource = $refsource;
            $output = explode("?", $refsource);
            $output = explode("/", $output[0]);
            if (($output[0] == "http:") || ($output[0] == "https:")) {
                $refsource = $output[0] . "//" . $output[2];
            } else {
                $refsource = $output[0];
            }
        }
        return $refsource;
    }

    /*
     * helper function: parse the URL from a variant or control and detect how to proceed with it
     * if it starts with a token (e.g. "?foo=bar") this indicates, that it shall be attached to
     * the current full URL of the control (meaning: the variant is the control with a querystring parameter attached)
     * if it does not start with the token, just take the URL ad attach the querystring from the control to it
     */

    private function constructRedirectUrl($storedVariantUrl, $currentPageUrl, $currentQueryString) {
        // first check wether the old landingpage is a "real" URL or a querystring fragment to be attached to the control
        // the latter is the case if the URL starts with an identifying token
        $tkn = $this->config->item('VARIANT_CONTROLRELOAD_TOKEN');
        if (substr_compare(trim($storedVariantUrl), $tkn, 0, strlen($tkn)) == 0) {
            // in this case, attach the given URL to the full URL of the control
            $redirecturl = str_replace($tkn, "", trim($storedVariantUrl)); // remove the token from the querystring fragment
            $redirecturl = $this->concat_urlquery($currentPageUrl, $redirecturl); // then attach to the querystring
            //$redirecturl = $this->concat_urlquery($redirecturl,"_p=t");
        } elseif ($storedVariantUrl == 'NA') { // if input URL is NA this means that it is the control and no redirect shall be executed
            $redirecturl = 'NA';
        } else {
            $redirecturl = $this->concat_urlquery($storedVariantUrl, $currentQueryString);
        }
        return $redirecturl;
    }
    
    /**********************************************************************
     * Personalization: Condition evaluation functions
     *********************************************************************/

    /*
     * return true if querystring contains substring provided
     * example: foo=bar
     */
    private function prs_querystring_is($val) {
        global $visitor;
        if(strpos($visitor['queryString'],$val) === false) {
            return false;
        }
        else {
            return true;
        }
    }
    
      /*
     * return true if URL or querystring contains substring provided
     * example: foobar
     */
    private function prs_url_contains($val) {
        global $visitor;
        $myurl = $visitor['pageurl'] . $visitor['queryString'];
        if(strpos($myurl,$val) === false) {
            return false;
        }
        else {
            return true;
        }
    }
    
    /**
     * Evaluate perso condition referrer_contains.
     * @global array $visitor
     * @param string $source
     * @return boolean
     */
    private function prs_referrer_contains($val) {
        global $visitor;
        if(strpos($visitor['referer'],$val) === false) {
            return false;
        }
        else {
            return true;
        }
    }
    
    /**
     * Depending on the expected $source, verifies the referer to tell if the rule applies or not.
     * @global array $visitor
     * @param string $source
     * @return boolean
     */
    private function prs_source_is($source) {
        global $visitor;
        $this->config->load('perso_user.php');
        switch ($source) {
            case 'type_in':
                return (strlen($visitor['referer']) == 0) ? TRUE : FALSE;
            case 'social' :
                return (preg_match($this->config->item('prs_social_regexp'), $visitor['referer']));
            case 'organic_search':
                return (preg_match($this->config->item('prs_organic_search_regexp'), $visitor['referer']));
            case 'paid_search':
                return (preg_match($this->config->item('prs_paid_search_regexp'), $visitor['referer']));
            default:
                return FALSE;
        }
    }
    
    /**
     * verifies if the referer querystring contains the desired search term
     * @global array $visitor
     * @param string $searchterm
     * @return boolean
     */
    private function prs_search_is($searchterm) {
        $this->config->load('perso_user.php');
        global $visitor;
        $referer = $visitor['referer'];
        if (strlen($visitor['referer']) < 5) {
            return FALSE;
        }

        if (preg_match($this->config->item('prs_organic_search_regexp'), $referer) || preg_match($this->config->item('prs_paid_search_regexp'), $referer)) {
            foreach ($this->config->item('prs_organic_search_array') as $key => $value) {
                $found = self::evaluateRefererQueryString($value, $referer, $searchterm);
                if ($found) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    // verifies condition purchase_type against etracker RTA attribute att_purchaser_type
    private function prs_purchaser_type($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->isPurchaserType($segment);
    }
    
     // verifies condition last_order_time against etracker RTA attribute att_time_since_last_order_seg
    private function prs_last_order_time($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->lastOrderTimeIs($segment);
    }
    
    // verifies condition avg_sales against etracker RTA attribute att_avg_order_value_segment
    private function prs_avg_sales($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->averageSalesIs($segment);
    }
    
    // verifies condition is_client against etracker RTA attribute att_customer_type
    private function prs_is_client($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->isClient($segment);
    }
    
    // verifies condition is_newsletter_subscriber against etracker RTA attribute att_is_registered_newsletter_recipient
    private function prs_is_newsletter_subscriber($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->isNewsletterSubscriber($segment);
    }
    
    // verifies condition visit_count against etracker RTA attribute att_visit_count_segment
    private function prs_visit_count($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->visitCountIs($segment);
    }

    // verifies condition time_between_visits against etracker RTA attribute att_frequency_segment
    private function prs_time_between_visits($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->timeBetweenVisitsIs($segment);
    }

    // verifies condition is_returning against etracker RTA attribute att_recurrent_user
    private function prs_is_returning($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->isReturningVisitor($segment);
    }
    
     // verifies condition device_is against etracker RTA attribute att_device_type
    private function prs_device_is($segment) {
        global $visitor;
        $this->load->library('EtrackerRTA',$visitor);
        return $this->etrackerrta->deviceIs($segment);
    }
    
    // verifies condition device_wurfl_is against WURFL database
    private function prs_device_wurfl_is($segment) {
        global $visitor;
        $this->load->library('Wurfl',$visitor);
        if($this->wurfl->getDeviceType() == $segment) 
            return true;
        else
            return false;
    }

    // verifies condition device_os_wurfl_is against WURFL database
    private function prs_device_os_wurfl_is($segment) {
        global $visitor;
        $this->load->library('Wurfl',$visitor);
        if($this->wurfl->getDeviceOs() == $segment) 
            return true;
        else
            return false;
    }

    /**
     * verifies condition minimum_session_time against session time from data collector
     * @global array $visitor
     * @param float time
     * @return boolean
     */
    private function prs_minimum_session_time($time) {
        global $visitor;
        $mytime = $visitor['sessiontime'];
        dblog_debug("compare time($time) vs. actual ($mytime)");
        if($time <= $mytime)
            return TRUE;
        else
            return FALSE;       
    }

    /**
     * verify condtion targetpage_openend against the history of rule condition goals for the visitor
     * @global array $visitor
     * @param int $conditionid refers to rul_condition.rule_condition_id 
     * @return boolean
     */
    private function prs_targetpage_opened($conditionid) {
        global $visitor;
        $status = $this->optimisation->getVisitorRuleConditionStatus($visitor['visitorid'],$conditionid,$visitor['clientid']);
        if($status == 1)
            return TRUE;
        else
            return FALSE;       
    }
    
    /**
     * verify condtion insert_basket against the history of rule condition goals for the visitor
     * @global array $visitor
     * @param int $conditionid refers to rul_condition.rule_condition_id 
     * @return boolean
     */
    private function prs_insert_basket($conditionid) {
        global $visitor;
        $status = $this->optimisation->getVisitorRuleConditionStatus($visitor['visitorid'],$conditionid);
        if($status == 1)
            return TRUE;
        else
            return FALSE;       
    }
    
    /**
     * verifies every url string located in the config file to compare with the referer, if it is container, evaluates the corresponding
     * parameter character (q=, query=, ...) and separates the search term from the referer.
     * Then compares this term with the expected searc_term and returls TRUE if they are equal.
     * @param array $value
     * @param string $referer
     * @param string $searchterm
     */
    private function evaluateRefererQueryString($value, $referer, $searchterm) {
        foreach ($value['url'] as $url) {
            if (strpos($referer, $url) !== false) {
                foreach ($value['param'] as $param) {
                    if (strpos($referer, $param) !== false) {
                        $query = split($param . '=', $referer);
                        if (trim(urldecode($query[1])) == trim(urldecode($searchterm))) {
                            return TRUE;
                        }
                    }
                }
                return FALSE;
            }
        }
        return FALSE;
    }  

    // verifies condition tv_commercial_aired against Wywy API
    private function prs_tv_commercial_aired($commercial) {
        global $visitor;
        $this->load->library('Wywy',$visitor);
        return $this->wywy->commercialAired($commercial);
    }

    /**
     * Gets the user IP address, if there isn't one accessible returns false inmediately
     * Then, based on the IP, verifies in the GEO IP DB if the IP is from the specific location stablished in the rule arguments
     * if so, returns TRUE
     * 
     * @param JSON string - $arguments
     * @return boolean
     */
    private function prs_location_is($arguments) {
        global $visitor;
        $args = json_decode($arguments);

        $client_ip = $visitor['ip'] != '127.0.0.1' ? $visitor['ip'] : $this->config->item('geonames_testing_ip');

        if (!$client_ip) {
            return FALSE;
        }

        try {
            $reader = new Reader($this->config->item('geolite2_citydb'));
            $record = $reader->city($client_ip);
            
            $lang = isset($args->lang) && $args->lang == 'de' ? 'de' : 'en';
            $ret = TRUE;
            $ind = 0;

            $condition = array(
                0 => $record->country->isoCode,
                1 => $record->mostSpecificSubdivision->names[$lang] ? : $record->mostSpecificSubdivision->name,
                3 => $record->city->names[$lang],
            );

            foreach ($args as $key => $value) {
                $val = trim($value);
                if ($ind != 2 && $key != 'lang' && isset($value) && strlen($val) > 0) {
                    $ret = $ret && ($condition[$ind] == $val);
                }
                $ind ++;
            }
            return $ret;
        }
        catch (exception $e) {
            dblog_debug('EXCEPTION (Location_is): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Derive location of user form IP address and compare against a table of 
     * holidays. Currently only available for germany.
     * Returns true if the current date is in a holiday for the visitors location.
     */
    private function prs_is_holiday($segment) {
        global $visitor;
        $client_ip = $visitor['ip'] != '127.0.0.1' ? $visitor['ip'] : $this->config->item('geonames_testing_ip');

        if (!$client_ip) {
            return FALSE;
        }

        try {
            $reader = new Reader($this->config->item('geolite2_citydb'));
            $record = $reader->city($client_ip);
            
            $lang = 'de';
            $ret = TRUE;
            $ind = 0;

            $condition = array(
                0 => $record->country->isoCode,
                1 => $record->mostSpecificSubdivision->names[$lang] ? : $record->mostSpecificSubdivision->name,
                3 => $record->city->names[$lang],
            );

            $this->config->load('perso_holidays.php');

            //print_r($condition);
            // check wether country is DE, because we have holiday infirmation only for germany
            if($condition[0] != 'DE')
                return false;
            $state = $condition[1];

            $hdtable = $this->config->item('perso_holiday_data');

            /*
            // printer friendly output for quality assurance
            foreach($hdtable as $key=>$value) {
                echo "<br><b>$key:</b>"; 
                foreach($value as $v) {
                    echo "<br>.... " . $v[0] . ": " . date('d.m.Y' , strtotime($v[1])) . " - " . date('d.m.Y' , strtotime($v[2]));  
                }
            }
            die();
            */

            if(!isset($hdtable[$state]))
                return false;
            $statedata = $hdtable[$state];
            //print_r($statedata);
            // iterate over holidays in given state and check wether one matches current date
            $now = strtotime("now");
            //$now = strtotime("2015-03-04 15:23:01");
            foreach($statedata as $season) {
                //echo "season";print_r($season);
                $startdate = strtotime($season[1] . ' 00:00:00');
                $enddate = strtotime($season[2] . ' 23:59:59');
                if(($startdate <= $now) && ($now <= $enddate)) {
                    return true;
                }
            }

            // nothing found
            return false;
        }
        catch (exception $e) {
            dblog_debug('EXCEPTION (Location_is): ' . $e->getMessage());
            return false;
        }
    }

    /*
     * Parse the request and derive attributes of the current vsitor
     */
    private function getVisitorDataFromRequest() {
        $myVisitor = array();

        $tracecode = (isset($_COOKIE['tracecode']) ? $_COOKIE['tracecode'] : '');
        // if tracecode cookie is found, and it can be validated against the database, we must set loglevel to debug for this code
        if ($tracecode) {
            echo "//traceode found $tracecode\n";
            // check if tracecode is valid (meaning; set by an agent in the support backend)
            $tracecodedata = $this->optimisation->getTracecodeData($tracecode);
            if ($tracecodedata)
                $this->config->set_item('TRACECODE', $tracecode); // force logging with DEBUG       
        }

        $myVisitor["clientid"] = "NA"; // will be filled in a later step
        $myVisitor["clienthash"] = ($this->input->get('cc', TRUE) !== false) ? urldecode($this->input->get('cc', TRUE)) : 'NA';
        $myVisitor["v"] = $this->input->get('v', TRUE) ? urldecode($this->input->get('v', TRUE)) : 'NA'; // visitor id from 1st party cookie
        $myVisitor["GS3_v"] = $this->input->cookie('GS3_v', TRUE) ? $this->input->cookie('GS3_v', TRUE) : 'NA'; // visotor id from 3rd party cookie
        // sanity check
        if (!ctype_alnum($myVisitor["v"]))
            $myVisitor["v"] = "NA";
        if (!ctype_alnum($myVisitor["GS3_v"]))
            $myVisitor["GS3_v"] = "NA";

        dblog_debug('BTO-10-011/ 1st party visitor-id:' . $myVisitor["v"] . " 3rd party visitor-id:" . $myVisitor["GS3_v"]);

        $myVisitor["et_pagename"] = ($this->input->get('et_pagename', TRUE) !== false) ? $this->input->get('et_pagename', TRUE) : 'NA';

        $qrs = urldecode($this->input->get('qrs'));
        $excludeList = ($this->input->get('ecl', TRUE) !== false) ? urldecode($this->input->get('ecl', TRUE)) : ''; // list of collection codes separated by : 
        $excludeList = trim($excludeList," :");
        $myVisitor["exclude_list"] = array_filter(explode(":",$excludeList));
        $myVisitor["no_redirection"] = ($this->input->get('_nrd', TRUE) !== false) ? urldecode($this->input->get('_nrd', TRUE)) : false; // no-redirection-parameter 
        // normalize querystring to "NA" if empty
        if ($qrs == "undefined") {
            $qrs = "NA";
        }
        if (empty($qrs)) {
            $qrs = "NA";
        }
        if (trim($qrs) == '') {
            $qrs = "NA";
        }
        $myVisitor["queryString"] = $qrs; // 
        //parse query string to array
        $queryString = array();
        $myVisitor["preview"] = false;
        $myVisitor["splitTestWebtrackingValues"] = false;
        if ($qrs != "NA") {
            parse_str($qrs, $queryString);
            //check to see if we have preview
            if (isset($queryString["_p"]) && ($queryString["_p"] == 't')) {
                $myVisitor["preview"] = true;
            }
            if (isset($queryString["BT_lpid"])) {
                $myVisitor["lpid"] = $queryString["BT_lpid"];
                setcookie('BT_lpid', $myVisitor["lpid"], 0); // save in cookie for forcing delivery of variant
            }
            if (isset($queryString["BT_cid"])) {
                $myVisitor["cid"] = $queryString["BT_cid"];
            }

            // parse values for webtracking after split test redirection
            if (isset($queryString["_bt_projectid"])&&isset($queryString["_bt_decisionid"])) {
                $myVisitor["splitTestWebtrackingValues"] = true;
                $myVisitor["_bt_projectid"] = $queryString["_bt_projectid"];
                $myVisitor["_bt_decisionid"] = $queryString["_bt_decisionid"];
                $myVisitor["_bt_projectname"] = 'NA'; // init 
                $myVisitor["_bt_decisionname"] = 'NA'; // init
                if (isset($queryString["_bt_projectname"])) {
                    $myVisitor["_bt_projectname"] = $queryString["_bt_projectname"];
                }
                if (isset($queryString["_bt_decisionname"])) {
                    $myVisitor["_bt_decisionname"] = $queryString["_bt_decisionname"];
                }
            }
        }
        
        // get information from session data collector
        $sdcstring = urldecode($this->input->get('sdc'));
        dblog_debug('BTO-10-012/ sdc-string:' . $sdcstring);
        $sdc = json_decode($sdcstring,true);
        if(isset($sdc)) {
            $myVisitor["referer"] = isset($sdc['rfr']) ? $sdc['rfr'] : 'NA'; // referrer of the landing page, including a query string
            $myVisitor["sessiontime"] = isset($sdc['time']) ? floor($sdc['time'] / 1000) : 0; // referrer of the landing page, including a query string
            $myVisitor["et_coid"] = isset($sdc['et_coid']) ? $sdc['et_coid'] : 'NA'; // etracker visitor-id
            $myVisitor["session_pageimpressions"] = isset($sdc['pi']) ? $sdc['pi'] : 0; // number of page impressions;
            $myVisitor["request_ttest_lpid"] = isset($sdc['ttest_lpid']) ? $sdc['ttest_lpid'] : 0; // clicked article in teaser test;
            $myVisitor["request_ttest"] = isset($sdc['ttest']) ? $sdc['ttest'] : null; // article click hstory with pi number in teaser test;
        }

        $myVisitor["pageurl"] = urldecode($this->input->get('pg')); // url of the page which triggers the request
        $myVisitor["user-agent"] = $_SERVER['HTTP_USER_AGENT'];
        dblog_debug('BTO-10-012/ referrer:' . $myVisitor["referer"] . "\npage-url:" . $myVisitor["pageurl"]);
        // we de not want revenue optimisation at the moment, so we dactivate it by setting rv to -1
        $myVisitor["rv"] = -1;

        $myVisitor["referrerhost"] = $this->referrersource($visitor["referer"]);        
        $myVisitor["ip"] = $this->retrieveClientIPAddress();  

        // parse conversion values
        $myVisitor['conversion'] = ($this->input->get('cv', true) !== false) ? $this->input->get('cv') : 'NA'; // cv=0 indicates page request, 
        // cv=1 indicates an additiona Javascript request / Conversion
        $myVisitor['conversion_type'] = ($this->input->get('ct', true) !== false) ? $this->input->get('ct') : 'NA'; // type of conversion
        $myVisitor['conversion_link'] = ($this->input->get('cl', true) !== false) ? $this->input->get('cl') : 'NA'; // tracked link URL
        $et_target = ($this->input->get('et_target', TRUE) !== false) ? $this->input->get('et_target', TRUE) : 'NA';
        $et_tval = ($this->input->get('et_tval', TRUE) !== false) ? $this->input->get('et_tval', TRUE) : 'NA';
        $et_tonr = ($this->input->get('et_tonr', TRUE) !== false) ? $this->input->get('et_tonr', TRUE) : 'NA';
        $et_tsale = ($this->input->get('et_tsale', TRUE) !== false) ? $this->input->get('et_tsale', TRUE) : 'NA';
        // if all of these variables are set, then this indicates a sale event when using etracker as tracking system
        if (($et_target != 'NA') && ($et_tval != 'NA') && ($et_tonr != 'NA'))
            $myVisitor["etracker_WA_sale"] = true;
        else
            $myVisitor["etracker_WA_sale"] = false;

        return $myVisitor;      
    }

    /*
     * Output debugging output on visitor device and location
     */
    private function showVisitorDebuggingData($visitor) {
        dblog_debug("BTO-60-010/ debugging request to show visitor data - stop here");
        echo "<br>----- Visitor ------<br>\n";
        print_r($visitor);
        // location data
        $client_ip = $visitor['ip'] != '127.0.0.1' ? $visitor['ip'] : $this->config->item('geonames_testing_ip');
        $reader = new Reader($this->config->item('geolite2_citydb'));
        $record = $reader->city($client_ip);
        echo "\n<br>----- Location ------<br>\n";
        print_r($record->city->names['de']);
        // device data
        echo "\n<br>----- Device ------<br>\n";
        $this->load->library('Wurfl',$visitor);
        echo "Device type: " . $this->wurfl->getDeviceType() . "\n<br>"; 
        $this->wurfl->showCapabilities(); 
    } 

    /*
     * Derive an aggregated result on whether we have a conversion, what type, and wether this request 
     * is a page impression or not
     */
    private function dispatchConversionParameters($visitor) {
        $is_pageview = true; // indicates default: this is a view of a page and no action on the page
        $action_type = 0; // indicates a potential conversion if 1
        $conversion_link = $visitor['conversion_link']; // for some cases this value will be updated

        if ($visitor['conversion_type'] != 'NA') {
            if ($visitor['conversion_type'] == 1) {
                $action_type = 1;
                $is_pageview = false;
            }
            if ((($visitor['conversion_type'] == 1) || ($visitor['conversion_type'] == 2)) && ($visitor['conversion_link'] != 'NA')) {
                // clicked link or form - check for affiliate-pattern
                $action_type = 1;
                foreach ($this->config->item('affiliate_url_pattern') as $pattern) {
                    if (preg_match($pattern[0], $visitor['conversion_link']) == 1) {
                        $action_type = 2;
                        $conversion_link = $pattern[1];
                        $is_pageview = false;
                    }
                }
            }
            if ($visitor['conversion_type'] == 3) {
                // DEPRECATED
                //$conversion_type = 'SPC';     
            }
            if ($visitor['conversion_type'] == 4) { // call of _bt.trackConversion()
                $action_type = 4;
                $is_pageview = false;
            }
            if ($visitor['conversion_type'] == 5) { // etracker ecommerce API call
                $action_type = 5;
                $ar = explode(":", $visitor['conversion_link']);
                $conversion_link = strtolower($ar[0]);
                $is_pageview = false;
            }
            if ($visitor['conversion_type'] == 6) { // etracker Wrapper call
                $action_type = 6;
                $is_pageview = false;
            }
            if ($visitor['conversion_type'] == 7) { // deferred impression call
                $action_type = 7;
                $is_pageview = true;
            }
            if ($visitor['conversion_type'] == 8) { // smartmessage follow event
                $action_type = 8;
                $is_pageview = false;
            }
            if ($visitor['conversion_type'] == 9) { // call of _bt.trackCustomGoal
                $action_type = 9;
                $is_pageview = false;
            }
        } else {
            // if ct is not set, then this is an impression call (user opens a page with the tracking code on it
            // this might be a conversion additionally on the following conditions:
            // 1. the URL or et_pagename match a target page goal. This is taken care of by function
            //      optimisation->getMatcingandingpagesForClient
            // 2. Certain etracker variables are set which indicates a sale. In this case we set ct=6 to ensure that
            //      this is handled as a potential conversion later on besides the impression itself
            if ($visitor["etracker_WA_sale"]) {
                $action_type = 6;
            }
        }

        $result = array(
            'is_pageview' => $is_pageview,
            'action_type' => $action_type,
            'conversion_link' => $conversion_link,
        );   

        return $result;  
    }

    /* Read config array from client table and create codeignter config items from it
     */
    private function setClientConfigValues($configArray) {
        if($configArray) {
            if(is_array($configArray)) {
                $this->load->library('Dbconfiguration');
                $this->dbconfiguration->createCodeigniterAccountConfiguration($configArray);
            }
        }
    }

    /*
     * Check wether request is valid. Possible reasons for not being valid:
     * - Client-Code unknown
     * - No quota
     * - No running tests for client
     * - Visotor has etracker opt-out-Cookie
     */
    private function checkRequestValidity($clientstatus) {
        $validstatus=true;
        if ($clientstatus['activetests'] == 0) {// no active tests
            $validstatus = false;
            dblog_debug('BTO-30-010a/ no active tests');
        }
        if (($clientstatus['status'] != '1') && $clientstatus['status'] != '6') {// no active account
            $validstatus = false;
            dblog_debug('BTO-30-010a/ no active account');
        }
        if ($clientstatus['quota'] <= $clientstatus['used_quota']) {// quota exceeded
            $validstatus = false;
            dblog_debug('BTO-30-010a/ quota exceeded');
        }
        if (isset($_COOKIE['cntcookie'])) { // etracker do-not-track cookie found for this client
            $cookievars = explode(',', $_COOKIE['cntcookie']);
            if (in_array($clientstatus['subid'], $cookievars)) {
                $validstatus = false;
                dblog_debug('BTO-30-010a/ etracker do-not-track-cookie found');
            }
        }
        return $validstatus;        
    }

    /*
     * Check if visitor IP matches the blacklist string provided from the client
     */
    private function getClientIPBlacklistStatus($ipaddress,$blacklistString) {
        $isIpBlacklisted = $this->verifyIPMatch($ipaddress,$blacklistString);
        if($isIpBlacklisted) {
            dblog_debug("BTO-30-010b/ client ip-blacklisting for this IP detected:" . $ipaddress);
        }
        return $isIpBlacklisted;
    }

    /*
     * eventually activate debug logging for the current IP
     */
    private function checkIpRestrictedDebugging($ipaddress) {
        $debugClientIp = $this->config->item('DEBUG_CLIENT_IP');
        if(isset($debugClientIp)) {
            $activateDebugging = $this->verifyIPMatch($ipaddress,$debugClientIp);
            if($activateDebugging) {
                $this->config->set_item('LOGLEVEL', LOG_LEVEL_DEBUG);
                dblog_debug('<b>BTO-10-010/--- bto webservice debugging start ---</b>');
            }
        }
    }

    /*
     * Check wether an IP address is contained in a pattern(semicolon separated list of IP left-substrings)
     */
    private function verifyIPMatch($ipaddress,$pattern) {
        $isMatch = false; // init
        $ip_list = explode(";", $pattern);
        if (is_array($ip_list) && $pattern != '') {
            foreach ($ip_list as $ip) {
                $length = strlen($ip);
                if (($length>0) && (strncmp($ip, $ipaddress, $length) == 0)) {
                    $isMatch = true;
                }
            }
        }
        return $isMatch;
    }

    /*
     * Derive IP address of visitor from available request headers
     * Return NA if not available.
     */
    private function retrieveClientIPAddress() {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'NA';  
        dblog_debug("BTO detected IP address:$ipaddress");
        return $ipaddress;
    }

    /*
     * Iterate over the array of matching pages and create an array of impression- and conversion-
     * events from it.
     * entries can be one impression event and/or multiple target goal conversion events 
     */
    private function getEventsFromMatchingURLs($mypages,$visitor) {
        $trackingEvents = array();
        $pageViewEvents = array();
        $collectionIdsForThisUrl = array(); // ID of projects that will be delivered for this URL

        // only proceed if there are any pages to process
        if (!isset($mypages) || (count($mypages) == 0)) {
            dblog_debug('BTO-30-030/ no tests found running on this URL or pagename');
            $result = array(
                'trackingEvents' => $trackingEvents,
                'collectionIdsForThisUrl' => $collectionIdsForThisUrl,
            );
            return $result;
        }

        dblog_debug('BTO-30-030/ 1 or more matching pages found, now creating list of impression and/or conversion events to be processed');
        $addedImpressionEvent = false;
        $event = array();
        //echo "pages "; print_r($mypages);
        foreach ($mypages as $page) {
            $collectionstatus = $this->optimisation->getcollectionstatus($page["co"]);
            $event['collectioncode'] = $page["co"];
            $event['allocation'] = $collectionstatus['allocation'];
            $event['collectiondetails'] = $collectionstatus;
            $event["collectionid"] = $collectionstatus["collectionid"];
            $event["testtype"] = $collectionstatus["testtype"]; 
            $event["groupid"] = $page["pg"]; // only for multipage tests 
            $event["deferred_injection"] = false; // default: variants get injected immediately
            $event["deferred_counting"] = false; // default: variants get counted immediately when page is delivered
            if($page['pt'] == OPT_PAGETYPE_PERSOGOAL_TARGETPAGE) {
                // handle rule conditions of type taget page
                dblog_debug('BTO-30-031a/ found rule condition target page for pattern ' . $page['cu']);
                $event = array();
                $event['eventtype'] = "persogoal";
                $event['goalid'] = $page['gi'];
                $trackingEvents[] = $event;
            }
            else { // handle real pages and conversion goals of type target page

                // do some checks for each of the pages/tests
                dblog_debug('BTO-30-031/ attributes of test with code ' . $page["co"] . ":\n" . print_r($collectionstatus, true));
                // check for ip_blacklisting
                if (($visitor['isIpBlacklisted'] && $collectionstatus['ignore_ip_blacklist'] == 0)) {
                    $event['ipBlacklisted'] = true;
                    dblog_debug('BTO-30-038/ do not count for this test because of ip-blacklisting match');
                }
                else {
                    $event['ipBlacklisted'] = false;
                }
                // check for OK client status and test status
                if ($collectionstatus['collectionstatus'] == OPT_COLLECTION_VALID && ($collectionstatus['clientstatus'] == CLIENT_STATUS_ACTIVE || $collectionstatus['clientstatus'] == CLIENT_STATUS_PAYED)) {
                    $testStatusValid = true;
                }
                else {
                    $testStatusValid = false;
                    dblog_debug('BTO-30-037/ ignore this test (either not running or client not valid');
                }
                // check if start and and end date of test match the current date/time
                $startdate = $collectionstatus['start_date'];
                $now = date("Y-m-d H:i:s");
                $enddate = $collectionstatus['end_date'];
                
                if( (strtotime($startdate) <= strtotime($now)) &&
                    (strtotime($now) <= strtotime($enddate))) {
                    $timeMatch = true;
                }
                else {
                    $timeMatch = false;
                    dblog_debug('BTO-30-039/ ignore this test because start/end date not matching');
                }
                
                // now if all checks are valid...
                if($testStatusValid && $timeMatch) {
                    if ($page['pt'] == OPT_PAGETYPE_CTRL) { // projects running on this URL
                        // set some data that will be needed to process the event
                        $event['eventtype'] = "impression";
                        // check wether the visitor is allocated for the project
                        if (in_array($page["co"],$visitor['exclude_list']))  {
                            $excludeList = true;                            
                            dblog_debug('BTO-30-034/ discard impression event, test is on exclude-list for this visitor (' . $visitor['exclude_list'] . ')');
                        }
                        else {
                            $excludeList = false;                            
                        }
                        
                        // check wether this is a split test and the _nrd flag is set
                        if (($event["testtype"] == OPT_TESTTYPE_SPLIT) && $visitor['no_redirection']) {
                            $unallowedSplitTest = true;
                            dblog_debug('BTO-30-034/ discard impression event, _nrd flag set for split test');
                        }
                        else {
                            $unallowedSplitTest = false;
                        }

                        // eventually add the project to the list of events for this URL
                        if (!$excludeList && !$unallowedSplitTest) {
                            // add the event only if this is an actual pageview and no conflicts occur
                            if($visitor["is_pageview"]) {
                                // manage additional array only for conflct management
                                $conflictCheckEvent = array(
                                    'collectionid' => $event['collectionid'],
                                    'name' => '',
                                    'pattern' => '',
                                    'testtype' => $event['testtype'],
                                    'status' => 2,
                                    'isSmartMessage' => (($event['collectiondetails']['smartmessage'] == 1) ? true:false)
                                );
                                $checkArray = $pageViewEvents;
                                $checkArray[] = $conflictCheckEvent;
                                $result = $this->optimisation->determineCollectionConflicts($checkArray,$event['collectionid']);
                                if(!$result['hasConflicts']) {
                                    // determine wether the impression/injection is deferred or not
                                    $deferredSelectorAction = $event['collectiondetails']['config']['DEFERRED_IMPRESSION_SELECTOR_ACTION'];
                                    $df = false;
                                    if($deferredSelectorAction=='is_visible')
                                        $df=true;
                                    if($deferredSelectorAction=='exists')
                                        $df=true;
                                    if($deferredSelectorAction=='expression_true')
                                        $df=true;
                                    if($df) {
                                        $event["deferred_injection"] = true; 
                                        $event["deferred_counting"] = true; 
                                        $event['deferred_selector'] = $event['collectiondetails']['config']['DEFERRED_IMPRESSION_SELECTOR'];
                                        $event['deferred_selector_action'] = $event['collectiondetails']['config']['DEFERRED_IMPRESSION_SELECTOR_ACTION'];
                                    }                                  
                                    $trackingEvents[] = $event;
                                    $pageViewEvents[] = $conflictCheckEvent;
                                    dblog_debug('BTO-30-034/ add impression event');
                                }
                                else {
                                    dblog_debug('BTO-30-034/ did not add impression event due to conflict: ' . print_r($conflictCheckEvent,true) . print_r($result,true));                                    
                                }
                            }
                            // add the project to the list of tests for this URL because it COULD run there
                            $collectionIdsForThisUrl[] = $page["ci"]; 
                        }
                    } 
                    elseif ($page['pt'] == OPT_PAGETYPE_SCSS) { // target page goals defined for this URL
                        $event['collectioncode'] = $page["co"];
                        $event['eventtype'] = "conversion";
                        $event['goalid'] = $page["gi"];
                        $event['goallevel'] = $page['gl'];
                        $trackingEvents[] = $event;
                        dblog_debug("BTO-30-035/ treat this as a target page event");
                    } else {
                        dblog_debug("BTO-30-036/ ignore this page/test");
                    }                            
                }
            }
        }
        $result = array(
            'trackingEvents' => $trackingEvents,
            'collectionIdsForThisUrl' => $collectionIdsForThisUrl,
        );
        return $result;        
    }

    /*
     * Retrieve events from actions that are not page impressions
     * actions like clicks etc.. Everything other than page impressions.
     * several kinds of goals are possible:
     * - conversion goals
     * - personalization goals (similar, but handled a bit differently)
     * - the "page impression lift"    
     */
    private function getEventsFromActionGoals($visitor) {
        $trackingEvents = array();

        if (($visitor["action_type"] == 0) || ($visitor["clientid"] == 'NA')) {
            return $trackingEvents;
        }

        $goals = $this->optimisation->getClientActionGoals($visitor["clientid"]);
        $conversiongoals = $goals['conversion_goals'];
        dblog_debug('BTO-40-010/ check action-type ' . $visitor["action_type"] . ' agains goals: ' . print_r($conversiongoals, true));
        foreach ($conversiongoals as $goal) {

            $goalid = 0;
            if (($visitor["action_type"] == 1) && ($goal['type'] == GOAL_TYPE_ENGAGEMENT)) {
                $goalid = $goal['collection_goal_id'];
                $arg1 = "";
                $goalname = "engagement";
            }
            if (($visitor["action_type"] == 1) && ($goal['type'] == GOAL_TYPE_TARGETLINK)) {
                if($this->optimisation->containsPattern($visitor['conversion_link'],$goal['arg1'])) {
                    $goalid = $goal['collection_goal_id'];
                    $arg1 = $visitor['conversion_link'];;
                    $goalname = "target link";                        
                }
            }
            if (($visitor["action_type"] == 2) && ($goal['type'] == GOAL_TYPE_AFFILIATE)) {
                $goalid = $goal['collection_goal_id'];
                $arg1 = $visitor['conversion_link'];
                $goalname = "affiliate-klick";
            }
            if (($visitor["action_type"] == 4) && ($goal['type'] == GOAL_TYPE_CUSTOM_JAVASCRIPT)) {
                $goalid = $goal['collection_goal_id'];
                $arg1 = "";
                $goalname = "custom javascript";
            }
            if (($visitor["action_type"] == 9) && ($goal['type'] == GOAL_TYPE_CUSTOM_JAVASCRIPT)) {
                //dblog_debug('goalurl:' . $goal['arg1'] . ' argument:'. $visitor['conversion_link']);
                if($visitor['conversion_link'] == $goal['arg1']) {
                    $goalid = $goal['collection_goal_id'];
                    $arg1 = $visitor['conversion_link'];;
                    $goalname = "custom javascript event";                        
                }
            }
            if ($visitor["action_type"] == 5) {
                $cl = strtolower($visitor['conversion_link']);
                if (($goal['type'] == GOAL_TYPE_ET_VIEWPRODUCT) && ($cl == 'viewproduct')) {
                    $goalid = $goal['collection_goal_id'];
                    $arg1 = $visitor['conversion_link'];
                    $goalname = "etracker productseen";
                }
                if (($goal['type'] == GOAL_TYPE_ET_INSERTTOBASKET) && ($cl == 'inserttobasket')) {
                    $goalid = $goal['collection_goal_id'];
                    $arg1 = $visitor['conversion_link'];
                    $goalname = "etracker inserttobasket";
                }
                if (($goal['type'] == GOAL_TYPE_ET_ORDER) && ($cl == 'order')) {
                    $goalid = $goal['collection_goal_id'];
                    $arg1 = $visitor['conversion_link'];
                    $goalname = "etracker order";
                }
            }
            if ($visitor["action_type"] == 6) {
                if ($goal['type'] == GOAL_TYPE_ET_ORDER && $visitor["etracker_WA_sale"]) {
                    $goalid = $goal['collection_goal_id'];
                    $arg1 = "";
                    $goalname = "etracker order";
                }
            }
            if (($visitor["action_type"] == 8) && ($goal['type'] == GOAL_TYPE_SMS_FOLLOW)) {
                $goalid = $goal['collection_goal_id'];
                $arg1 = "";
                $goalname = "smartmessage follow";
            }
            if ($goalid != 0) {
                $event['collectioncode'] = $goal['code'];
                $collectionstatus = $this->optimisation->getcollectionstatus($event['collectioncode']);
                $event['allocation'] = $collectionstatus['allocation'];
                $event['collectiondetails'] = $collectionstatus;
                $event["collectionid"] = $collectionstatus["collectionid"];
                $event["testtype"] = $collectionstatus["testtype"]; 
                $event['eventtype'] = "conversion";
                $event['goalid'] = $goalid;
                $event['goallevel'] = $goal['level'];
                $event['conversion_link'] = $arg1;
                $event['goal_type'] = $goal['type'];
                dblog_debug('BTO-40-011/ add goal event ' . $goalname . ' ' . $event['conversion_link']);
                $trackingEvents[] = $event;
            }
        }

        $persogoals = $goals['perso_goals'];
        dblog_debug('BTO-40-010/ check action-type agains perso-goals: ' . print_r($persogoals, true));

        foreach ($persogoals as $goal) {
            if ($visitor["action_type"] == 5) {
                $cl = strtolower($visitor['conversion_link']);
                if (($goal['type'] == 'insert_basket') && ($cl == 'inserttobasket')) {
                    $event = array();
                    $event['eventtype'] = "persogoal";
                    $event['goalid'] = $goal['rule_condition_id'];
                    $trackingEvents[] = $event;
                }
            }
        }
        return $trackingEvents;
    }

    /*
     * Retrieve events from querystring parameters
     * currently this could be a plain webtracking event in case we have a redirection
     * in a split test
     */
    private function getEventsFromQuerystring($visitor) {
        $trackingEvents = array();

        if ($visitor["splitTestWebtrackingValues"]) {
            $event = array();
            $event['eventtype'] = "webtracking";
            $event['_bt_projectid'] = $visitor['_bt_projectid'];
            $event['_bt_decisionid'] = $visitor['_bt_decisionid'];
            $event['_bt_projectname'] = $visitor['_bt_projectname'];
            $event['_bt_decisionname'] = $visitor['_bt_decisionname'];
            dblog_debug('BTO-40-011/ add webtracking event');
            $trackingEvents[] = $event;
        }

        return $trackingEvents;
    }

    private function getEventsFromTeasertestClickHistory($visitor) {
        $trackingEvents = array();

        if ((!$visitor["is_pageview"]) || ($visitor["clientid"] == 'NA')) {
            return $trackingEvents;
        }
        $goals = $this->optimisation->getClientActionGoals($visitor["clientid"]);
        foreach($goals['conversion_goals'] as $goal) {            
            if ($goal['type'] == GOAL_TYPE_PI_LIFT) {
                $history = $visitor["request_ttest"]["history"];
                $collectionid = $visitor["request_ttest"]["cid"];
                $currentPageImpression = $visitor['session_pageimpressions'];
                foreach($history as $key => $value) {
                    $event = array();
                    $event['eventtype'] = "updatable_conversion";
                    $event['collectionid'] = $collectionid;
                    $event['goalid'] = $goal['collection_goal_id'];
                    $event['pageid'] = $key;
                    $event['conversionValue'] = $currentPageImpression - $value['pi'];
                    $trackingEvents[] = $event;                                            
                }           
            }
        }
        return $trackingEvents;
    }

    /*
     * Derive a visitor ID for the current visitor from cookies
     */
    private function getVisitorId($visitor) {
        $visitorid = false; // init
        
        // check if we need a new visitor because neither a 1st party nor a 3rd party cookie could be found
        $cookie1stParty = $visitor["v"];
        $cookie3rdParty = $visitor["GS3_v"];
        $vid = $cookie1stParty;
        $isnew = false;
        if ($vid == "NA") {
            $vid = $cookie3rdParty;
        } else {
            dblog_debug("BTO-50-010/ returning visitor with ID: $vid");
        }

        if($vid == 'NA') {
            dblog_debug("BTO-50-010/ new visitor");
            return false;
        }
        else {
            return $vid;
        }
    }





    /*
     * Helper function to create a data object that is injected in a page for teaser test preview
     * Takes the full collection information retrieved from oprtimisation->getCollectionStatus
     * and derive a JS data structure which can be precessed from JS code 
     */
    private function constructTeasertestPreviewData($collectiondata) {
        $previewData = array();
        foreach($collectiondata['page_groups'] as $group) {
            $groupElement = array();
            foreach($group['pages'] as $decision) {
                $id = $decision['landing_pageid'];
                $domCode = json_decode($decision['dom_code']);
                $headline = $domCode->{'[TT_HL]'};
                $type = $decision['pagetype'];
                $decisionElement = array();
                $decisionElement['id'] = $decision['landing_pageid'];
                $label = strip_tags($headline);
                if(strlen($label) > 40) {
                    $label = substr($label,0,20) . "..." . substr($label, -20);
                }
                $decisionElement['lbl'] = $label;                    
                $decisionElement['text'] = $headline;                    
                if($type == OPT_PAGETYPE_CTRL) {
                    $groupElement['ctrl'] = $decisionElement;
                }
                else {
                    $groupElement['vrnts'][] = $decisionElement;
                }
            }
            $previewData[] = $groupElement;
        }
        return $previewData;
    }

    /*
     * Deliver the webservice response in case of action "deliver_old_variant"
     */
    private function handleOldVariantResponse($event,$visitor) {
        $webserviceResponse = $visitor['webserviceResponse'];

        if (!$event['ipFilterMatch']) {
            dblog_debug("BTO-60-030/ project not delivered for this IP");
            return $webserviceResponse;
        }

        $handlerData = array();
        $handlerData['entries'] = $event["impressionEventJSEventHandlers"];
        $handlerData['collectionid'] = $event["collectionid"];
        $handlerData['pageid'] = $event["oldlandingpageid"];
        $handlerData['testtype'] = $event["testtype"];

        $domTriggeredGoalHandlers = $event["impressionEventDomTriggeredGoalHandlers"];
        foreach($domTriggeredGoalHandlers as $handler) {
            $webserviceResponse['dom_triggered_activation'][] = array(
                'type' => $handler['handler'],
                'action' => 'exists',
                'selector' => $handler['selector'],
                'goalid' => $handler['goalid'],
                'collectionid' => $event["collectionid"],
                'landingpageid' => $event["oldlandingpageid"],
            );
        }

        // in case this is a multipage test, retrieve the dom-injection data from the corresponding variant in the
        // decisiongropup specified by event[groupid]
        if($event["oldpagetype"] == OPT_PAGETYPE_VRNT) {
            if($event['testtype'] == OPT_TESTTYPE_MULTIPAGE) {
                $pages = $event['collectiondetails']['page_groups'][$event['groupid']]['pages'];
                foreach($pages as $mypage) {
                    if($mypage['name'] == $event["oldlandingpagename"]) {
                        $event["olddom_code"] = $mypage['dom_code'];
                    }
                }
            }
        }
        $webserviceResponse['impressionEventJSEventHandlers'][$event["collectionid"]] = $handlerData;
        if ($event["testtype"] == OPT_TESTTYPE_SPLIT) { // A/B test
            $redirecturl = $this->constructRedirectUrl($event["oldlandingpageurl"], $visitor["pageurl"], $visitor["queryString"]);
            $webserviceResponse['type'] = 'REDIRECT';
            $webserviceResponse['wa_integration'] = $this->config->item('WA_INTEGRATION');
            $webserviceResponse['new_variant'] = false;
            $webserviceResponse['webtracking'][] = array(
                'collectioncode' => $event["collectioncode"],
                'collectionid' => $event["collectionid"],
                'collectionname' => $event["collectiondetails"]["collectionname"],
                'landingpageid' => $event["oldlandingpageid"],
                'landingpagename' => $event["oldlandingpagename"],
                'pagetype' => $event["oldpagetype"],
                'rulename' => $event["oldlandingpagerulename"],
                'referrer' => $visitor["referer"],
            );
            $webserviceResponse['redirect'] = $redirecturl;
            $webserviceResponse['splittestpagetype'] = $event["oldpagetype"];
            dblog_debug('BTO-60-030/ split-test old variant: ' . $event["oldlandingpageid"] . '/' . $event["oldlandingpagename"]);
        } else if (($event["testtype"] == OPT_TESTTYPE_MVT) 
            || ($event["testtype"] == OPT_TESTTYPE_VISUALAB)
            || ($event["testtype"] == OPT_TESTTYPE_MULTIPAGE)) {// MVT test
            $webserviceResponse['type'] = 'INJECTION';
            $webserviceResponse['wa_integration'] = $this->config->item('WA_INTEGRATION');
            $webserviceResponse['new_variant'] = false;
            $webserviceResponse['webtracking'][] = array(
                'collectioncode' => $event["collectioncode"],
                'collectionid' => $event["collectionid"],
                'collectionname' => $event["collectiondetails"]["collectionname"],
                'landingpageid' => $event["oldlandingpageid"],
                'landingpagename' => $event["oldlandingpagename"],
                'pagetype' => $page["landingpagetype"],
                'smart_messages' => $event["smart_messages"],
                'rulename' => $rulename
            );
            // distinguish between deferred impressions (dom_code is stored for later use in object) or impression
            // dom_code is passed and injected immediately
            if($event["deferred_injection"]) {
                $webserviceResponse['dom_triggered_activation'][] = array(
                    'type' => 'project_activation',
                    'selector' => $event["deferred_selector"],
                    'action' => $event["deferred_selector_action"],
                    'dom_code' => $event["olddom_code"],
                    'collectionid' => $event["collectionid"],
                    'landingpageid' => $event["oldlandingpageid"],
                );
                dblog_debug('BTO-60-031/ deferred visual test old variant: ' . $page["landingpageid"]);
            }
            else {
                $webserviceResponse['dom_code'] = $this->jshandler->combineDomCodes($webserviceResponse['dom_code'],$event["olddom_code"]);
                dblog_debug('BTO-60-031/ visual test old variant: ' . $page["landingpageid"]);
            }
        }  
        return $webserviceResponse;      
    }  

    /*
     * Deliver the webservice response in case of action "deliver_teaser_test_injections"
     */
    private function handleTeaserTestResponse($event,$visitor) {
        $collectionid = $event["collectionid"];
        $webserviceResponse = $visitor['webserviceResponse'];            
        $pageGroups = $event['collectiondetails']['page_groups'];
        // retrieve array with assignment page_group->landing_page for this visitor
        $deliveryPlan = $this->getTeaserTestDeliveryPlan($visitor['visitorid'],$visitor['clientid'],$event);
        // dispatch between interface-type = 'UI' vs. 'API' which impacts the kind of injected JS/CSS
        if($event['collectiondetails']['config']['TT_INTERFACE_TYPE'] == 'API') {
            $interfaceTypeIsApi = true;
            dblog_debug('BTO-60-040/ teaser test interface: API');
        }
        else {
            $interfaceTypeIsApi = false;
            $injectionDefinition = array();           
            dblog_debug('BTO-60-040/ teaser test interface: UI');
        }
        // create a combined dom_modification_code from this
        foreach($deliveryPlan as $key => $value) {
            $groupid = $key; 
            $pageid = $value;
            $domcodeEmpty = true;
            $groupControlId = $pageGroups[$groupid]['controlid'];
            $code = json_decode($pageGroups[$groupid]['pages'][$pageid]['dom_code']);                
            // create a concatenation of the injected CSS and JS of all variants
            if($code) {
                if(isset($code->{'[CSS]'})) {
                    if(!empty($code->{'[CSS]'})) {
                        $combinedCSScode = $combinedCSScode . $code->{'[CSS]'};
                    }
                }
                if(isset($code->{'[JS]'})) {
                    if(!empty($code->{'[JS]'})) {
                        $combinedJScode = $combinedJScode . $code->{'[JS]'};
                    }
                }
            }
            // for api-integration, this means that our convention of how dom_code in teasertests
            // are created should be used
            if($interfaceTypeIsApi) {
                if($pageid != $groupControlId) {
                    // assumption: elements in HTML are wrapped in divs and have an ID constructed from the 
                    // pageid like this: _bt_<pageid>
                    $snippetTemplate = ".-bt-%s-%s {display:none;} .-bt-%s-%s {display:inherit !important;}";
                    $combinedCSScode .= sprintf($snippetTemplate,$collectionid,$groupControlId,$collectionid,$pageid);
                }
            }
            else {
                // create an array with headline-text used for thre selector, plus variant text is applicable
                $ctrlCode = json_decode($pageGroups[$groupid]['pages'][$groupControlId]['dom_code']);
                $ctrl = $ctrlCode->{'[TT_HL]'};
                if($pageid != $groupControlId) {
                    $vrnt = $code->{'[TT_HL]'};
                }
                else {
                    $vrnt = false;
                }
                $injectionDefinitionElement = array(
                    'id' => $pageid,
                    'ctrl' => $ctrl,
                    'vrnt' => $vrnt
                );
                $injectionDefinition[] = $injectionDefinitionElement;

            }
        }

        // for teaser tests with interface-type=UI, add array with selectors and JS-script to custom-JS segment
        if(!$interfaceTypeIsApi) {
            $myjs = "var injectionJson = '" . json_encode($injectionDefinition) . "';var collectionid = %d;\n";
            $myjs .= file_get_contents($this->config->item('base_ssl_url') . 'tt/getTeaserTestInjectionCode');
            $myjs = sprintf($myjs,$collectionid);
            $combinedJScode = $myjs . $combinedJScode;
        }

        // add event handlers for view-tracking, click-tracking, time-on-page-tracking
        // ... is ths an overview page or a details page? can be detected from a visitor attribute, 
        // taken intially from the SDC json/cookie 
        $teasertestPageType = 'overview';
        if(isset($visitor['request_ttest_lpid'])) {
            if($visitor['request_ttest_lpid'] != 0) {
                $teasertestPageType = 'detail';
            }
        }

        if($teasertestPageType == 'detail')
            $trackingExcludeId = $visitor['request_ttest_lpid'];
        else
            $trackingExcludeId = 'NA';

        $dom_modification_code = json_encode(array(
            "[CSS]" => $combinedCSScode,
            "[JS]" => $combinedJScode,
        ));
        $webserviceResponse['dom_code'] = $this->jshandler->combineDomCodes($webserviceResponse['dom_code'],$dom_modification_code);

        $handlerData = array();
        $handlerData['entries'] = $event["impressionEventJSEventHandlers"];
        $handlerData['collectionid'] = $event["collectionid"];
        $handlerData['pageid'] = $trackingExcludeId;
        $handlerData['testtype'] = OPT_TESTTYPE_TEASER;
        $webserviceResponse['impressionEventJSEventHandlers'][$event["collectionid"]] = $handlerData;                

        $webserviceResponse['type'] = 'INJECTION';
        return $webserviceResponse;      
    }

    /*
     * Retrieve an array for a given visitor and collection which determines pages to
     * be delivered for groups
     */
    private function getTeaserTestDeliveryPlan($visitorid,$clientid,$event) {
        // it is determined in advance for a visitor which page from each group she will be delivered.
        // get this delivery-plan (from cache) and compare it to the current page group configuration.
        // In case there have been changes since we determined the delivery plan, synchronize.
        $pageGroups = $event['collectiondetails']['page_groups'];
        $deliveryPlanArray = $this->optimisation->getTeaserTestDeliveryPlan($visitorid,$event['collectionid']);       
        $deliveryPlan = $deliveryPlanArray[0];
        // find all groups in plan that are not in test or where the selectd
        // page is not valid anymore and must be refreshed. remove these from plan
        $planIds = array_keys($deliveryPlan);
        $planHasChanged = false;
        foreach($planIds as $id) {
            if(!isset($pageGroups[$id])) {
                unset($deliveryPlan[$id]);
                $planHasChanged = true;                
            }
            else {
                $pageid = $deliveryPlan[$id];
                if(!isset($pageid, $pageGroups[$id]['pages'][$pageid])) {
                    unset($deliveryPlan[$id]);
                    $planHasChanged = true;                
                }
            }
        }

        // find all groups in test that are missing in delivery Plan and select a page
        $pageGroupdIds = array_keys($pageGroups);
        foreach($pageGroupdIds as $id) {
            if(!isset($deliveryPlan[$id])) {
                $newGroupData = $this->optimisation->selectNewPageForGroup($clientid,$visitorid,$event['collectionid'],$id,$pageGroups[$id]['pages']);
                $deliveryPlan[$id] = $newGroupData;
                $planHasChanged = true;                
            }
        }

        // save updated plan
        if($planHasChanged)
            $this->optimisation->refreshTeaserTestDeliveryPlan($deliveryPlan,$visitorid,$event['collectionid'],$clientid);

        return $deliveryPlan;
    }

    /*
     * Deliver the webservice response in case of action "deliver_new_landing_page"
     */
    private function handleNewVariantResponse($event,$visitor) {
        $webserviceResponse = $visitor['webserviceResponse'];
        if (!$event['allocatedInProject']) {
            $webserviceResponse['exclude'][] = $event["collectioncode"];
            dblog_debug("BTO-60-033/ visitor not allocated to test " . $event["collectioncode"]);
            return $webserviceResponse;
        }

        if (!$event['ipFilterMatch']) {
            dblog_debug("BTO-60-033/ project not delivered for this IP");
            return $webserviceResponse;
        }

        $cstatus = $event['collectiondetails'];
        // we will pass an array with all pages where the rule matched as selection base.
        // or -1 if all pages are fine (because no single page personalization)
        if($event['smart_messages'] || ($cstatus['personalization_mode'] == 2)) {
            $matchingpageids = $event['matching_pageids']; 
        }
        else {
            $matchingpageids = -1;
        }
        $page = $this->shownewpage($visitor, $event, $cstatus['landingpages'],$matchingpageids);
        // in case this is a multipage test, retrieve the dom-injection data from the corresponding variant in the
        // decisiongropup specified by event[groupid]
        if($page['landingpagetype'] == OPT_PAGETYPE_VRNT) {
            if($event['testtype'] == OPT_TESTTYPE_MULTIPAGE) {
                $pages = $event['collectiondetails']['page_groups'][$event['groupid']]['pages'];
                foreach($pages as $mypage) {
                    if($mypage['name'] == $page['landingpagename']) {
                        $page['dom_code'] = $mypage['dom_code'];
                    }
                }
            }
        }
        $rulename = "";
        if($event["collectiondetails"]["personalization_mode"] == 1) {
            $rulename = $event["rname"];
        }
        if($event["collectiondetails"]["personalization_mode"] == 2) {
            $rulename = $page["rulename"];
        }
        $handlerData = array();
        $handlerData['entries'] = $event["impressionEventJSEventHandlers"];
        $handlerData['collectionid'] = $event["collectionid"];
        $handlerData['pageid'] = $page["landingpageid"];
        $handlerData['testtype'] = $event["testtype"];
        $webserviceResponse['impressionEventJSEventHandlers'][$event["collectionid"]] = $handlerData;                

        $domTriggeredGoalHandlers = $event["impressionEventDomTriggeredGoalHandlers"];
        foreach($domTriggeredGoalHandlers as $handler) {
            $webserviceResponse['dom_triggered_activation'][] = array(
                'type' => $handler['handler'],
                'action' => 'exists',
                'selector' => $handler['selector'],
                'goalid' => $handler['goalid'],
                'collectionid' => $event["collectionid"],
                'landingpageid' => $page["landingpageid"],
            );
        }

        if ($event["testtype"] == OPT_TESTTYPE_SPLIT) { // A/B test
            $redirecturl = $this->constructRedirectUrl($page["landingpageurl"], $visitor["pageurl"], $visitor["queryString"]);
            //set oldlandingpaheid and oldlandingpageurl to the values for the new page to display
            $webserviceResponse['type'] = 'REDIRECT';
            $webserviceResponse['wa_integration'] = $this->config->item('WA_INTEGRATION');
            $webserviceResponse['new_variant'] = true;
            $webserviceResponse['webtracking'][] = array(
                'collectioncode' => $event["collectioncode"],
                'collectionid' => $event["collectionid"],
                'collectionname' => $event["collectiondetails"]["collectionname"],
                'landingpageid' => $page["landingpageid"],
                'landingpagename' => $page["landingpagename"],
                'pagetype' => $page["landingpagetype"],
                'rulename' => $rulename,
                'referrer' => $visitor["referer"],
            );
            $webserviceResponse['redirect'] = $redirecturl;
            $webserviceResponse['splittestpagetype'] = $page["landingpagetype"];
            dblog_debug('BTO-60-032/ split-test new page: ' . $page["landingpageid"] . '/' . $page['landingpagename']);
        } else {// Visual test
            $webserviceResponse['type'] = 'INJECTION';
            $webserviceResponse['wa_integration'] = $this->config->item('WA_INTEGRATION');
            $webserviceResponse['new_variant'] = true;
            $webserviceResponse['webtracking'][] = array(
                'collectioncode' => $event["collectioncode"],
                'collectionid' => $event["collectionid"],
                'collectionname' => $event["collectiondetails"]["collectionname"],
                'landingpageid' => $page["landingpageid"],
                'landingpagename' => $page["landingpagename"],
                'pagetype' => $page["landingpagetype"],
                'smart_messages' => $event["smart_messages"],
                'rulename' => $rulename
            );
            // distinguish between deferred impressions (dom_code is stored for later use in object) or impression
            // dom_code is passed and injected immediately
            if($event["deferred_injection"]) {
                $webserviceResponse['dom_triggered_activation'][] = array(
                    'type' => 'project_activation',
                    'selector' => $event["deferred_selector"],
                    'action' => $event["deferred_selector_action"],
                    'dom_code' => $page["dom_code"],
                    'collectionid' => $event["collectionid"],
                    'landingpageid' => $page["landingpageid"],
                );
                dblog_debug('BTO-60-033/ deferred visual test new page: ' . $page["landingpageid"]);
            }
            else {
                $webserviceResponse['dom_code'] = $this->jshandler->combineDomCodes($webserviceResponse['dom_code'],$page["dom_code"]);
                dblog_debug('BTO-60-033/ visual test new page: ' . $page["landingpageid"]);
            }
        }
        return $webserviceResponse;
    }

    private function handleWebtrackingResponse($event, $visitor) {
        dblog_debug("BTO / handleWebtrackingResponse:" . print_r($event,true));

        $webserviceResponse = $visitor['webserviceResponse'];
        $webserviceResponse['type'] = 'INJECTION';
        $webserviceResponse['wa_integration'] = $this->config->item('WA_INTEGRATION');
        $webserviceResponse['new_variant'] = true;
        $webserviceResponse['webtracking'][] = array(
            'collectionid' => $event["_bt_projectid"],
            'collectionname' => $event["_bt_projectname"],
            'landingpageid' => $event["_bt_decisionid"],
            'landingpagename' => $event["_bt_decisionname"],
        );

        return $webserviceResponse;
    }

    /*
     * Deliver the webservice response in case of action "deliver_preview"
     */
    private function handlePreviewResponse($visitor) {
        $webserviceResponse = false;
        // distinguish between Teaser Tests and other projects
        $isTeaserTest = false;
        $interfaceType = false;
        if(isset($visitor["cid"])) {
            $previewProject = $this->optimisation->getcollectionstatusById($visitor["cid"]);
            if(is_array($previewProject)) {
                if($previewProject['testtype'] == OPT_TESTTYPE_TEASER) {
                    $config = $previewProject['config'];
                    $isTeaserTest = true;
                    $interfaceType = $config['TT_INTERFACE_TYPE'];
                }
            }
        }

        if($isTeaserTest) {
            $previewData = $this->constructTeasertestPreviewData($previewProject);
            $data = array(
                'action' => "deliver_preview",
                'collectionid' => $visitor["cid"],
                'previewData' => $previewData,
                'interfaceType' => $interfaceType,
            );
            $webserviceResponse = $this->load->view('webservice_ttpreview', $data,true);
            dblog_debug($webserviceResponse);
            dblog_debug('BTO-60-013/show teasertest preview');
        }
        else {
            $result = $this->optimisation->getPreviewData($visitor["lpid"], $visitor["clienthash"]);
            if ($result) {
                $data = array(
                    'testtype' => $result["testtype"],
                    'lp_url' => $redirecturl = $this->constructRedirectUrl($result['lp_url'], $visitor["pageurl"], $visitor["queryString"]),
                    'dom_code' => $result["dom_code"],
                    'visitor' => $visitor
                );
                $webserviceResponse = $this->load->view('webservice_preview', $data,true);
                dblog_debug('BTO-60-013/show preview');
            }
        }
        return $webserviceResponse;
    }

    /*
     * Retrieve the history of the current visitor in a given collection/project
     * - Has she visited the collection already?
     * - If so, what was the variant she got delivered?
     * -- When the visitors personalization profile changes over time, she could have created 
     * -- multiple impressions. We always use the latest. 
     * - Was ist a deferred impression or a "real" impression?
     * -- Multiple deferred impressions are possible as well
     * - Has she converted in the collection?
     * -- Only one converison is possible
     */
    private function getVisitorHistoryInCollection($visitor,$event) {
        // this function does not apply for type = teaser test
        if($event['testtype'] == OPT_TESTTYPE_TEASER) {
            dblog_debug("BTO-60-011b/ visitedpages: TEASER_TEST not applicable here.");   
            return false;
        } 

        $visitorHasDeferredImpression = false;
        $newestDeferredImpressionid = false;
        $visitorHasImpression = false;
        $newestimpressionid = false;
        $visitorHasConversion = false;

        // retrieve history if a visitorid is available
        if($visitor["visitorid"]) {
            // retrieve visitor history in this test, show him the same page as in previous requests
            $visitorRequestEvents = $this->optimisation->getRequestEvents($visitor["visitorid"], $event['collectionid'], $event['collectiondetails']['restart_date']);
            dblog_debug("BTO-60-011b/ visitorRequestEvents:" . print_r($visitorRequestEvents, true));   

            foreach($visitorRequestEvents as $vp) {
                if(($vp[1]==1) && !$visitorHasImpression) {
                    $visitorHasImpression = true;
                    $newestimpressionid = $vp[0];
                }
                if($vp[1]==2) {
                    // check if the same conversion like the current has already been counted
                    if($vp[4]==$event["goalid"])
                    $visitorHasConversion = true;
                }
                if(($vp[1]==3) && !$newestDeferredImpressionid) {
                    $visitorHasDeferredImpression = true;
                    $newestDeferredImpressionid = $vp[0];
                }
            }
        }
        
        // log result                
        if ($visitorHasDeferredImpression)
            $message = "deferredImpression YES, ";
        else 
            $message = "deferredImpression NO, ";
        if ($visitorHasImpression)
            $message .= "Impression YES, ";
        else 
            $message .= "Impression NO, ";
        if ($visitorHasConversion)
            $message .= "Conversion YES, ";
        else 
            $message .= "Conversion NO, ";
        dblog_debug("BTO/OPT-60-020 visitor: " . $message); 

        return array(
            'visitorHasDeferredImpression' => $visitorHasDeferredImpression,
            'newestDeferredImpressionid' => $newestDeferredImpressionid,
            'visitorHasImpression' => $visitorHasImpression,
            'newestimpressionid' => $newestimpressionid,
            'visitorHasConversion' => $visitorHasConversion,
        ); 
    }

    /*
     * Compute the action for the current impression event from it's data and the visitor history
     */
    private function retrieveActionForImpressionEvent($event,$visitorHistory,$visitor) {
        $action_type = $visitor['action_type'];
        // in case personalization_mode == 1, check rule for this test, and eventually discard this event
        $persoResult = true;
        $collectiondetails = $event['collectiondetails'];
        $visitorHasImpression = $visitorHistory['visitorHasImpression'];
        $newestimpressionid = $visitorHistory['newestimpressionid'];
        $visitorHasDeferredImpression = $visitorHistory['visitorHasDeferredImpression'];
        $newestDeferredImpressionid = $visitorHistory['newestDeferredImpressionid'];
        $visitorHasConversion = $visitorHistory['visitorHasConversion'];
        $responseEvent = $event;

        // prefill event with data for IP filtering. will be evaluated later in the process
        $ipList = $event['collectiondetails']['config']['IP_FILTER_IPLIST'];
        $ipAction = $event['collectiondetails']['config']['IP_FILTER_ACTION'];
        $isIpMatch = $this->verifyIPMatch($visitor["ip"],$ipList);
        $responseEvent['ipFilterMatch'] = true; // means: project shall be delivered and/or counted
        if($ipAction=='allow') {
            if($isIpMatch)
                $responseEvent['ipFilterMatch'] = true;
            else
                $responseEvent['ipFilterMatch'] = false;
        }
        if($ipAction=='deny') {
            if($isIpMatch)
                $responseEvent['ipFilterMatch'] = false;
            else
                $responseEvent['ipFilterMatch'] = true;
        }
        if($ipAction=='not_used') {
            $responseEvent['ipFilterMatch'] = true;
        }
        dblog_debug("BTO/-70-010 ip filtering settings IPs=$ipList, action=$ipAction, ipFilterMatch=" . $responseEvent['ipFilterMatch']);

        if($collectiondetails['personalization_mode'] == 1) {
            $control = reset($collectiondetails['landingpages']);
            $rule = $control['phpcode'];
            eval($rule);
            if($persoResult)
                dblog_debug("BTO/-70-010 persomode = complete test, result TRUE, rule: $rule");
            else              
                dblog_debug("BTO/-70-010 persomode = complete test, result FALSE, rule: $rule");              
        }
        if($persoResult) { 
            if($action_type == 7) { // deferred view-event
                if(!$visitorHasImpression && $visitorHasDeferredImpression) {
                    $action = "count_deferred_impression";
                    $responseEvent['oldlandingpageid'] = $newestDeferredImpressionid;
                }
                else {
                    $action = "no_operation";
                }
            }
            else {
                $action = "deliver_new_landing_page"; // init
                if($visitorHasImpression || $visitorHasDeferredImpression) {
                    // check wether the last seen page matches the current context
                    // try to get an impression first
                    $pageid = -1;
                    if($visitorHasImpression) {
                        $page = $collectiondetails['landingpages'][$newestimpressionid];
                        if($collectiondetails['personalization_mode'] == 2)
                            eval($page['phpcode']);
                        else 
                            $persoResult = true;
                        if($persoResult) {
                            $pageid = $newestimpressionid;
                        }
                    }
                    if($pageid == -1) { // try deferred impression
                        $page = $collectiondetails['landingpages'][$newestDeferredImpressionid];
                        if($collectiondetails['personalization_mode'] == 2)
                            eval($page['phpcode']);
                        else 
                            $persoResult = true;
                        eval($page['phpcode']);
                        if($persoResult) {
                            $pageid = $newestDeferredImpressionid;
                        }
                    }
                    if($pageid == -1) { // if still no seen page can be used, we need to select a new page
                                        // but it shall not be counted in the quota
                        $responseEvent['quota_count'] = false;
                    }
                    else { // we deliver an old page
                        $action = "deliver_old_variant";
                        $responseEvent['oldlandingpageid'] = $pageid;
                        $lpid = $responseEvent['oldlandingpageid'];
                        if ($collectiondetails['landingpages'][$lpid]['pagetype'] == OPT_PAGETYPE_CTRL) {
                            $responseEvent['oldlandingpagename'] = "Original";
                        }
                        else {
                            $responseEvent['oldlandingpagename'] = $collectiondetails['landingpages'][$lpid]['name'];
                        }
                        $responseEvent['olddom_code'] = $collectiondetails['landingpages'][$lpid]['dom_modification_code'];
                        $responseEvent['oldpagetype'] = $collectiondetails['landingpages'][$lpid]['pagetype'];
                        $responseEvent['oldlandingpageurl'] = $collectiondetails['landingpages'][$lpid]['lp_url'];                                
                        if($collectiondetails['personalization_mode'] == 0) {
                            // if no perso clear this
                            $responseEvent['oldlandingpagerulename'] = "";                                
                        }
                        if($collectiondetails['personalization_mode'] == 1) {
                            // if perso = complete test, use rule name of the control
                            $responseEvent['oldlandingpagerulename'] = $control['rname'];
                        }
                        if($collectiondetails['personalization_mode'] == 2) {
                            // if perso = complete test, use rule name of the delivered variant
                        $responseEvent['oldlandingpagerulename'] = $collectiondetails['landingpages'][$lpid]['rname'];                                
                        }
                    }
                }
                if($action == "deliver_new_landing_page") {
                    // evaluate allocation for this visitor
                    $max = 1000000;
                    $rnd = mt_rand(0, $max);
                    if (($event["allocation"] * $max) >= $rnd) {
                        $responseEvent["allocatedInProject"] = true;
                    } else {
                        $responseEvent["allocatedInProject"] = false;
                    }
                    if($responseEvent["allocatedInProject"]) {
                        // ensure we have a visitor ID
                        if(!$visitor['visitorid']) {
                            $visitorid = $this->optimisation->insertvisitorid();
                            $this->setVisitorId($visitorid);
                            dblog_debug("BTO/-70-009 new visitorid: $visitorid");                                           
                        }

                        if(!isset($responseEvent['quota_count'])) {
                            $responseEvent['quota_count'] = true;
                        }
                        // in case we have a smart message, no control shall be delivered, so we need to remove it.
                        // if this is a test with perso-mode=2 the control will be filtered out anyway (see below) so 
                        // we need to do this filter only for smartmessage==true AND personalization_mode!=2
                        if($event['smart_messages'] && ($collectiondetails['personalization_mode'] != 2)) {
                            $allpages = $collectiondetails['landingpages'];
                            $matchingpageids = array();
                            foreach($allpages as $page) {
                                if($page['pagetype'] == 2) {
                                    $matchingpageids[] = $page['landing_pageid'];
                                }                               
                            }
                            $responseEvent['matching_pageids'] = $matchingpageids;                                  
                        }
                        if($collectiondetails['personalization_mode'] == 1) { // test is complete-personalized
                            // if perso = complete test, use rule name of the control
                            $responseEvent['rname'] = $control['rname'];
                        }
                        if($collectiondetails['personalization_mode'] == 2) { // test is single-page-personalized
                            // TBD: take into account previous requests / visitedpages
                            // evaluate rules of all variants in test, leave out control
                            $allpages = $collectiondetails['landingpages'];
                            $matchingpageids = array();
                            foreach($allpages as $page) {
                                if($page['pagetype'] == 2) {
                                    eval($page['phpcode']); // filter all unmatching variants out
                                    if($persoResult) {
                                        dblog_debug("BTO/-70-010 persomode = single variant, result TRUE, rule:" . $page['phpcode']);                                           
                                        $matchingpageids[] = $page['landing_pageid'];
                                    }
                                    else {
                                        dblog_debug("BTO/-70-010 persomode = single variant, result FALSE, rule:" . $page['phpcode']);                                                                                          
                                    }
                                }                               
                            }
                            $nr_available_variants = sizeof($allpages)-1;
                            $nr_matching_variants = sizeof($matchingpageids);
                            dblog_debug("BTO/-70-011 persomode = single variants. $nr_matching_variants from $nr_available_variants matching.");
                            
                            if(!empty($matchingpageids)) {
                                $action = "deliver_new_landing_page"; // display a new landing page
                                $responseEvent['matching_pageids'] = $matchingpageids;
                            }
                            else {
                                $action = "no_operation"; // no variant macthes the current visitor
                            }
                        }   
                        else {
                            $action = "deliver_new_landing_page"; // then display a new landing page
                        }                           
                    }
                    else { // not allocated
                        $action = "deliver_new_landing_page";
                    }
                }
            }
        }
        else { // impression filtered away due to personalization
            $action = "no_operation";
        }
        $responseEvent['action'] = $action;
        return $responseEvent;
    }

    /*
     * Compute the action for the current teasertest impression event from it's data and the visitor history
     */
    private function retrieveActionForTeasertestEvent($event,$visitorHistory,$visitor) {
        $responseEvent = $event;
        // ensure we have a visitor ID
        if(!$visitor['visitorid']) {
            $visitorid = $this->optimisation->insertvisitorid();
            $this->setVisitorId($visitorid);
            dblog_debug("BTO/-70-009 new visitorid: $visitorid");                                           
        }
        $responseEvent['action'] = "deliver_teaser_test_injections";
        return $responseEvent;
    }

    /*
     * Set the visitor id in the global visitor array
     */
    private function setVisitorId($visitorid) {
        global $visitor;  
        $visitor['visitorid'] =   $visitorid;    
    }

    /*
     * Compute the action for the current conversion event from it's data and the visitor history
     */
    private function retrieveActionForConversionEvent($event,$visitorHistory,$collectionIdsForThisUrl) {
        $visitorHasImpression = $visitorHistory['visitorHasImpression'];
        $newestimpressionid = $visitorHistory['newestimpressionid'];
        $visitorHasConversion = $visitorHistory['visitorHasConversion'];
        $responseEvent = $event;

        if (!$visitorHasImpression) { // ... if user has not yet requested the control page before, then a conversion is invalid
            $action = "no_operation"; // do nothing
        } else {
            // check if user has already converted
            if ($visitorHasConversion) {
                $action = "no_operation"; // do nothing
            } else {
                // check if the conversio is a "same-page-conversion". if so, check wether the actual page/test is one 
                // that the vistor has an impression for.
                // we do not do this check for split url tests, because it is a bit more difficult
                // (in case the conversion happens on a variant page) and probably not that necessary.
                if(in_array($event['goal_type'],$this->config->item('SAME_PAGE_GOALS')) && $event["testtype"] != OPT_TESTTYPE_SPLIT) {
                    if(in_array($event['collectionid'],$collectionIdsForThisUrl)) {
                        $action = "process_conversion";
                        $responseEvent['oldlandingpageid'] = $newestimpressionid;                                
                    }
                    else {
                        $action = "no_operation"; // do nothing
                    }
                }
                else {
                    $action = "process_conversion";
                    $responseEvent['oldlandingpageid'] = $newestimpressionid;                                
                }
            }
        }
        $responseEvent['action'] = $action;
        return $responseEvent;
    }

    /*****************************************************************************************
    // Functions for teaser tests


    /*****************************************************************************************
     ****************************** Public testing methods for perso ******************************
     ****************************************************************************************/    
    public function testSourceIs(){
        global $visitor;
        $visitor['referer'] = $this->input->get('referer');
        echo self::prs_source_is($this->input->get('param'));
    }
    
    public function testSearchIs(){
        global $visitor;
        $visitor['referer'] = $this->input->get('referer');
        echo self::prs_search_is($this->input->get('param'));
    }

    function test() {
    }

}

?>