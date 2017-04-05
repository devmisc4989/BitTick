<?php
/**
* Manage JS snippets that represent handlers for conversion goals injected into a page impression
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Jshandler {

    public function __construct($visitor) {
        $CI = & get_instance();
        $CI->load->model('optimisation');
    }

    /*
     * In case this is an impression event with a project running on the current URL, we might 
     * need to to inject code to register event handlers, whch is needed for different types of
     * goals.
     * Create and return an array with information on what handlers should be injected and additional
     * information for them. This will be evaluated in our webservice-views and transformed to actual
     * JS code to register handlers and stuff. 
     * Caveat: NOT FOR SPLIT TESTS FOR NOW!!
     */
    public function getJSEventHandlersForImpressionEvent($visitor,$event) {
        if(!$visitor['collectionIdsForThisUrl'])  
            return false; // if this is no impression event
        $handlerData = array();
        // evaluate all goals specific for the current collectionid
        $CI = & get_instance();
        $goals = $CI->optimisation->getClientActionGoals($visitor["clientid"],$visitor['collectionIdsForThisUrl']);        
        foreach($goals['conversion_goals'] as $goal) {
            if($goal['type']==GOAL_TYPE_TIMEONPAGE) {
                $handlerData[] = array(
                    'handler' => 'TIMEONPAGE',
                    'goalid' => $goal['collection_goal_id']
                );
            }
            if(($goal['type']==GOAL_TYPE_CLICK)&&($event['collectiondetails']['testtype'] == OPT_TESTTYPE_TEASER)) {
                $handlerData[] = array(
                    'handler' => 'CLICKS',
                    'goalid' => $goal['collection_goal_id'],
                );                    
            }
        }
        if($event['collectiondetails']['testtype'] == OPT_TESTTYPE_TEASER) {
            $handlerData[] = array(
                'handler' => 'TT_VIEWS',
            );            
        }
        return $handlerData;
    }

    /*
     * Since click goals do not work when the element is not present at the time the click event is registered,
     * we use a similar approach as for dom-triggered-project activation (deferred impresseions). Click goal 
     * handlers are added to an array, and a timeout checks for exsitence of the element every 50ms 
     */
    public function getDomTriggeredGoalHandlersForImpressionEvent($visitor,$event) {
        if(!$visitor['collectionIdsForThisUrl'])  
            return false; // if this is no impression event
        $handlerData = array();
        // evaluate all goals specific for the current collectionid
        $CI = & get_instance();
        $goals = $CI->optimisation->getClientActionGoals($visitor["clientid"],$visitor['collectionIdsForThisUrl']);        
        foreach($goals['conversion_goals'] as $goal) {
            if($goal['type']==GOAL_TYPE_CLICK) {
                // for multi page tests, ensure that the goal handler is only delivered if the page (=== decisiongroup)
                // for the goal is delivered
                $renderGoalHandler = true;
                if($event['testtype'] == OPT_TESTTYPE_MULTIPAGE) {
                    if($goal['page_groupid'] != $event['groupid']) {
                        $renderGoalHandler = false;
                    }
                }
                if($renderGoalHandler) {
                    $handler = array(
                            'handler' => 'CLICKS',
                            'goalid' => $goal['collection_goal_id']
                        );   
                    $args = json_decode($goal['arg1'],true);
                    if(isset($args['selector'])) {
                        $handler['selector'] = $args['selector']; 
                    }
                    $handlerData[] = $handler;                    
                }
            }
        }
        return $handlerData;    }

    // render code to be injected for a click javascript handler
    public function getClickJSHandlerCode($collectionid, $pageid, $goalid, $selector) {
        $code = sprintf("$('<div class=\"_bt_eventtracker_arg\" data-eventtracker_handler=\"CLICKS\" data-eventtracker_cid=\"%s\" data-eventtracker_lpid=\"%s\" data-eventtracker_cgid=\"%s\" data-eventtracker_selector=\"%s\"><\/div>').appendTo('body');",
            $collectionid,$pageid,$goalid, $selector);
        return $code;
    }

    // render code to be injected for a time-on-page javascript handler
    public function getTimeonpageJSHandlerCode($collectionid, $pageid, $goalid) {
        if($pageid == 'NA') // this can happen when we have a teasertest and the overview page is loaded
            return "";
        $code = sprintf("$('<div class=\"_bt_eventtracker_arg\" data-eventtracker_handler=\"TIMEONPAGE\" data-eventtracker_cid=\"%s\" data-eventtracker_lpid=\"%s\" data-eventtracker_cgid=\"%s\"><\/div>').appendTo('body');",
            $collectionid,$pageid,$goalid);
        return $code;
    }

    // render code to be injected for a teasertest click javascript handler
    public function getTeasertestClickJSHandlerCode($collectionid, $pageid, $goalid) {
        $code = sprintf("$('<div class=\"_bt_eventtracker_arg\" data-eventtracker_handler=\"TT_CLICKS\" data-eventtracker_cid=\"%s\" data-eventtracker_lpid_exclude=\"%s\" data-eventtracker_cgid=\"%s\"><\/div>').appendTo('body');",
            $collectionid,$pageid,$goalid);
        return $code;
    }

    // render code to be injected for a teasertest view javascript handler
    public function getTeasertestViewJSHandlerCode($collectionid, $pageid) {
        $code = sprintf("$('<div class=\"_bt_eventtracker_arg\" data-eventtracker_handler=\"TT_VIEWS\" data-eventtracker_cid=\"%s\" data-eventtracker_lpid_exclude=\"%s\"><\/div>').appendTo('body');",
            $collectionid,$pageid);
        $code .= "_bt.discardTeasertestExclusionId();";
        return $code;
    }

    // render code to be injected to load additional script to execute JS handlers
    public function getJSHandlerScript() {
        $CI = & get_instance();
        $scripturl = str_replace("http:", "", $CI->config->item('base_url')) . "js/bt_event_tracker.js";
        $code = sprintf("$.ajaxSetup({cache:true});$.getScript('%s');",$scripturl);
        return $code;
    }

    // combine 2 dom injection codes
    public function combineDomCodes($code1,$code2) {
        $combinedCSScode = "";
        $combinedJScode = "";
        $combinedSmsHTMLcode = "";
        $combinedSmscode = "";
        $myCSScode = "";
        $myJScode = "";
        $mySmsHTMLcode = "";
        $mySmscode = "";
        $code = json_decode($code1);
        if($code) {
            if(isset($code->{'[CSS]'})) {                
                $combinedCSScode = $code->{'[CSS]'};
            }
            if(isset($code->{'[JS]'})) {
                $combinedJScode = $code->{'[JS]'};
            }
            if(isset($code->{'[SMS]'})) {
                $combinedSmscode = $code->{'[SMS]'};
            }
        }
        $code = json_decode($code2);
        if($code) {
            if(isset($code->{'[CSS]'})) {
                $myCSScode = $code->{'[CSS]'};
            }
            if(isset($code->{'[JS]'})) {
                $myJScode = $code->{'[JS]'};
            }
            if(isset($code->{'[SMS]'})) {
                $mySmscode = $code->{'[SMS]'};
            }
        }
        $combinedSmscode .= $mySmscode;
        $combinedJScode .= $myJScode;
        $combinedCSScode .= $myCSScode;

        $output = array();
        if(!empty($combinedSmscode))
            $output["[SMS]"] = $combinedSmscode;
        if(!empty($combinedJScode))
            $output["[JS]"] = $combinedJScode;
        if(!empty($combinedCSScode))
            $output["[CSS]"] = $combinedCSScode;
        return (json_encode($output));
    }

    // analyze array of JS event handlers and create one string containing all JS code
    public function getAllHandlerCodes($handlers) {
        $code = "";
       foreach($handlers as $collection) {
            foreach($collection['entries'] as $item) {
                if($item['handler'] == 'TIMEONPAGE') {
                    $code .= $this->getTimeonpageJSHandlerCode($collection['collectionid'],$collection['pageid'],$item['goalid']);
                    }
                if($item['handler'] == 'CLICKS') {
                    if($collection['testtype'] == OPT_TESTTYPE_TEASER) {
                        $code .= $this->getTeasertestClickJSHandlerCode($collection['collectionid'],$collection['pageid'],$item['goalid']);
                    }
                    else {
                    $code .= $this->getClickJSHandlerCode($collection['collectionid'],$collection['pageid'],$item['goalid'],$item['selector']);
                    }
                }
                if($item['handler'] == 'TT_VIEWS') {
                    $code .= $this->getTeasertestViewJSHandlerCode($collection['collectionid'],$collection['pageid']);
                }
            }
        }
        return ($code);
    }

    // get JS code representing dom triggered activations for deferred impressions and click goals
    public function getDeferredImpressionCodes($deferredImpressions) {
        if(!is_array($deferredImpressions))
            return "";
        $myjs = "var deferredJson = " . json_encode($deferredImpressions) . ";\n";
        $myjs .= "_bt.setConditionalActivationData(deferredJson);\n";
        return $myjs;
    }

}
