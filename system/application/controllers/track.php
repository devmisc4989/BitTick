<?php
class track extends CI_Controller {
      
    private $visitor = array();
    private $errorMessage = "";

    function __construct() {
        parent::__construct();
        $this->load->model('optimisation');
    }

    // controller for to dispatch and handle tracking requests
    public function me() {
        dblog_debug('<b>TRACK-00-010/--- track webservice request processing start ---</b>');

        header('Content-type: application/x-javascript');
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

        $requestContext = $this->getRequestData();
        // retrieve data for the client from Cache/DB
        $clientstatus = $this->optimisation->getclientstatus($requestContext["clientCode"]);
        // set client specifig configuration values
        $this->setClientConfigValues($clientstatus['config']);

        // stop processing if invalid data
        if(!$requestContext) {
            echo "// invalid request: ". $this->errorMessage. "\n";
            dblog_debug("TRACK-00-012/ invalid request" . $this->errorMessage);
            return;
        }

        dblog_debug("TRACK-10-011/ request:" . print_r($requestContext,true));

        // dispatch the requests
        foreach($requestContext["events"] as $event) {
            $event["visitorId"] = $requestContext["visitorId"];
            if($event["eventType"] == "conversion") {
                $this->processConversionEvent($event);
                echo "// track conversion\n";                
            }
            if($event["eventType"] == "impression") {
                $this->processImpressionEvent($event);
                echo "// track impression\n";                
            }
        }

        echo "// track done";
    }

    // parse request querystring and extract data to result array
    // sanitize the contents of the request
    private function getRequestData() {

        $request = array();
        // encode serialized object
        $requestdata = json_decode($this->uri->segment(3));
        if(!isset($requestdata)) {
            $this->errorMessage .= " Could not decode JSON ";
            return false;            
        }

        if(!isset($requestdata->cc)) {
            $this->errorMessage .= " Parameter cc missing ";
            return false;            
        }
        if(!ctype_alnum($requestdata->cc)) {
            $this->errorMessage .= " Parameter cc must be alphanumeric ";
            return false;            

        }
        $request["clientCode"] = $requestdata->cc;
        if(!isset($requestdata->v)) {
            $this->errorMessage .= " Parameter v missing ";
            return false;                        
        }
        if(!ctype_alnum($requestdata->v)) {
            $this->errorMessage .= " Parameter v must be alphanumeric ";
            return false;                        
        }
        $request["visitorId"] = $requestdata->v;

        $events = array();
        if(!isset($requestdata->ev)) {
            $this->errorMessage .= " Empty array ev ";
            return false;                        
        }
        
        foreach($requestdata->ev as $event) {
            $eventArray = array();
            
            if(!isset($event->t)) {
                $this->errorMessage .= " Parameter t missing ";
                break;
            } // event type impression or conversion
            if(!($event->t=='c' || $event->t=='i')) {
                $this->errorMessage .= " Parameter t must be i or c ";
                break;
            }
            $eventArray['eventType'] = ($event->t == 'i') ? 'impression' : 'conversion';

            if(!isset($event->cid)) {
                $this->errorMessage .= " Parameter cid missing ";
                break;
            } // collectionid
            if(!is_numeric($event->cid)) {
                $this->errorMessage .= " Parameter cid must be numeric ";
                break;
            }
            $eventArray['collectionId'] = $event->cid;

            if(!isset($event->lpid)) {
                $this->errorMessage .= " Parameter lpid missing ";
                break;
            } // pageid
            if(!is_numeric($event->lpid)) {
                $this->errorMessage .= " Parameter lpid must be numeric ";
                break;
            }

            $eventArray['pageId'] = $event->lpid;

            if(isset($event->pgid)) {
                if(is_numeric($event->pgid)) {
                    $eventArray['pageGroupId'] = $event->pgid;
                }
            }

            if(isset($event->cgid)) {  // optional: goal id
                if(is_numeric($event->cgid)) {
                    $eventArray['collectionGoalId'] = $event->cgid;
                }
            }
            if(isset($event->cv)) { // optional: converison value
                if(is_numeric($event->cv)) {
                    $eventArray['conversionValue'] = $event->cv;
                }
            }
            $events[] = $eventArray;
        }

        if(sizeof($events)==0) {
            $this->errorMessage .= " empty events array ";
            return false;
        }

        $request["events"] = $events;
        return $request;
    }

    // handle an impression event
    private function processImpressionEvent($event) {
        dblog_debug("TRACK-60-013/ handle impression event");

        // get the visitor request history
        $visitorRequestEvents = $this->optimisation->getRequestEvents($event["visitorId"], $event["collectionId"],'2000-01-01 00:00:00');
        // filter for the page id and check for conversions and impressions
        $hasImpression = false;
        $isInDeliveryPlan = false;
        $clientid = -1;
        foreach($visitorRequestEvents as $page) {
            $clientid = $page[6];
            if(($page[1] == OPT_EVENT_IMPRESSION) && ($page[0] == $event["pageId"])) { 
                $hasImpression = true;
            }
            if(($page[1] == OPT_EVENT_DEFERRED_IMPRESSION) && ($page[0] == $event["pageId"])) { 
                $hasDeferredImpression = true;
                $groupid = -1;
            }
            if($page[1] == OPT_EVENT_TT_VISITOR) {
                // check wether the tracked page and group have been in delivery plan and thus should be counted
                $deliveryPlan = $this->optimisation->getTeaserTestDeliveryPlan($event["visitorId"], $event["collectionId"]);
                $deliveryPlan = array_flip($deliveryPlan[0]);
                if(array_key_exists($event["pageId"], $deliveryPlan)) {
                    $groupid = $deliveryPlan[$event["pageId"]];
                    $isInDeliveryPlan = true;
                }               
            }
        }
        // process the impression tracking event IF:
        // - no impression has been counted yet and page is in delivery plan for visitor OR
        // - deferred impression has been counted, but no impression
        $countImpression = false;
        if(!$hasImpression && $isInDeliveryPlan)
            $countImpression = true;
        if(!$hasImpression && $hasDeferredImpression)
            $countImpression = true;
        if($countImpression) {
            dblog_debug("TRACK-60-014/ process impression");
            // create a visitor array using what we have, so the legacy function countImpression can handle it...
            $visitor['visitorid'] = $event["visitorId"];
            $visitor['clientid'] = $clientid;
            $visitor['clienthash'] = 'NA';
            $visitor['referer'] = '';
            $visitor['queryString'] = '';
            $this->optimisation->countImpression($event["pageId"],$event["collectionId"],$visitor,0,false,$groupid);
            $this->optimisation->evaluateImpact($event["collectionId"],$groupid);
            $this->optimisation->flushRequestEventsCache($event["visitorId"], $event["collectionId"]);
        }
    }

    // handle a conversion request
    private function processConversionEvent($event) {
        dblog_debug("TRACK-60-015/ handle conversion event");

        // get the visitor request history
        $visitorRequestEvents = $this->optimisation->getRequestEvents($event["visitorId"], $event["collectionId"],'2000-01-01 00:00:00');
        // filter for the page id and check for conversions and impressions
        $hasImpression = false;
        $hasConversion = false;
        $conversionValue = 0;
        $conversionRequestEventsId = -1;
        $clientid = -1;
        $groupid = -1;
        foreach($visitorRequestEvents as $page) {
            if($page['0'] == $event["pageId"]) {
                if($page['1'] == 1) {
                    $hasImpression = true;
                    $clientid = $page['6'];
                    $groupid = $page['7'];                    
                }
                if(($page['1'] == 2) && ($page['4'] == $event["collectionGoalId"])) {
                    $hasConversion = true;
                    $conversionValue = $page['5'];
                    $conversionRequestEventsId = $page['3'];
                }
            }
        }

        // process the conversion tracking event IF:
        // - impression has been counted
        // - no conversion yet, OR conversion with different conversion value. then overwrite it.
        if($hasImpression) {
            if(!$hasConversion || ($hasConversion && $conversionValue!=$event["conversionValue"])) {
                dblog_debug("TRACK-60-016/ process conversion");
                $this->optimisation->updatableConversion(
                    $clientid,$event["collectionId"], 
                    $groupid, $event["pageId"], 
                    $event["collectionGoalId"], 
                    $event["visitorId"],
                    $conversionValue,
                    $event["conversionValue"],
                    $conversionRequestEventsId
                );
                // check if there is a combined goal in this test. If so, calculate it's conversion value
                $this->optimisation->evaluateImpact($event["collectionId"],$groupid);
            }
        }
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
}

?>