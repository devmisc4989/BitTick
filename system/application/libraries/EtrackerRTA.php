<?php
/**
* Library to encapsulate personalisation functionality of the etracker real time API
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class EtrackerRTA {
    private $visitor; // holds the bto visitor array;

    // Constructor takes the optimisation webservice visitor array as an argument
    public function __construct($visitor) {
        $this->visitor = $visitor;
    }

    // verifies generic conditions
    public function conditionIs($args) {
        $attributeName = $args['attribute'];
        $targetValue = $args['value'];
        $comparator = $args['comparator'];
        $value = $this->getVisitorRtaAttributeValue($attributeName);
        dblog_debug("$attributeName $targetValue $comparator $value");
        if(!$value)
            return false;
        
        // we must handle attributes of type datetime a bit different, so evaluate the type first
        if(in_array($attributeName,array('engagement_lifetime','engagement_recency')))
            $is_datetime = true;
        else
            $is_datetime = false;
        if($is_datetime) {
            if($comparator=='EQUALS')
                $comparator = 'EQUALS_DATETIME';
            if($comparator=='NOT_EQUALS')
                $comparator = 'NOT_EQUALS_DATETIME';
        }
        $one_day_value = 24*3600*1000;
        // evaluate result depending on comparator
        switch($comparator) {
            case 'EQUALS':
                $result = ($value == $targetValue);
                break;
            case 'EQUALS_DATETIME':
                $targetValue = $this->date2UtcTime($targetValue);
                $result = (($targetValue <= $value) && ($value <= ($targetValue+$one_day_value)));
                break;
            case 'NOT_EQUALS':
                $result = ($value != $targetValue);
                break;
            case 'NOT_EQUALS_DATETIME':
                $targetValue = $this->date2UtcTime($targetValue);
                $result = !(($targetValue <= $value) && ($value <= ($targetValue+$one_day_value)));
                break;
            case 'TO':
                $targetValue = $this->date2UtcTime($targetValue);
                $result = ($value <= ($targetValue + $one_day_value));
                break;
            case 'FROM':
                $targetValue = $this->date2UtcTime($targetValue);
                dblog_debug("value:$value target:$targetValue");
                $result = ($value >= $targetValue);
                break;
            case 'GREATER_THAN':
                $result = ($value >= $targetValue);
                break;
            case 'LESS_THAN':
                $result = ($value <= $targetValue);
                break;
            default:
                $result = false;
        }
        return $result;
    }

    // helper function - convert a date to milliseconds UTC
    private function date2UtcTime($date) {
        $time = strtotime($date) * 1000;
        return $time;
    }

    /*
     * Basic functionality --------------------------------------------------
     */

    // helper function: retrieve RTA attribute data for the current visitor
    private function getVisitorRtaAttributeValue($attributeName) {
        $v = $this->visitor;
        if (empty($v['account_key2']) || empty($v['et_coid'])) {
            return FALSE;
        }
        $params = array(
            'sessiontime' => $v["sessiontime"],
            'session_pageimpressions' => $v["session_pageimpressions"],
            'etcc_cmp' => $v["etcc_cmp"],
            'etcc_cust' => $v["etcc_cust"],
            'ec_order' => $v["ec_order"],
            'etcc_newsletter' => $v["etcc_newsletter"],
            'referer' => $v["referer"],
            'returning' => $v["returning"],
        );
        $rta = $this->getEtrackerRTAData($v['account_key2'],$v['et_coid'],$params);
        if(!array_key_exists($attributeName, $rta))
            return false;
        else
            return ($rta[$attributeName]);
    }

    /*
     * Retrieve data for this visitor from etracker RTA. Store the result in APC memory cache.
     */
    //function getEtrackerRTAData($account_key2,$et_coid,$sessiontime,$session_pageimpressions) {
    private function getEtrackerRTAData($account_key2,$et_coid,$params) {
        $sessiontime = $params['sessiontime'];
        $session_pageimpressions = $params['session_pageimpressions'];
        $referer = $params['referer'];
        $etcc_cmp = $params['etcc_cmp'];
        
        $key = "rta_" . $et_coid;
        apch_delete($key);
        if($et_coid == 'NA')
            $entries = false;
        else
            $entries = getValueFromCache($key);
        if(!$entries) {
            $rtaJsonResponse = $this->getRTAResponse($account_key2,$et_coid,$sessiontime,$session_pageimpressions,$referer,$etcc_cmp);
            if(!$rtaJsonResponse)
                return false;
            if(is_array($rtaJsonResponse)) {
                $entriesDefault = $this->decodeRTAResponse($rtaJsonResponse[0]);
                $entriesDevice = $this->decodeRTAResponse($rtaJsonResponse[1]);
                $entries = array_merge($entriesDefault,$entriesDevice);
            }
            else {
                $entries = $this->decodeRTAResponse($rtaJsonResponse);
            }
            storeValueInCache($key,$entries);                        
        }
        
        // overwrite etracker return values depending on values tracked in the sdc and pdc cookie

       // if visitor is purchaser (ec_order==1)
        // and RTA returns purchaser_type = STC_CC_ATTR_VALUE_PURCHASER_TYPE_1
        // then set purchaser_type = STC_CC_ATTR_VALUE_PURCHASER_TYPE_2
        if(($params['ec_order']==1) && ($entries['purchaser_type']=='STC_CC_ATTR_VALUE_PURCHASER_TYPE_1')) 
            $entries['purchaser_type']='STC_CC_ATTR_VALUE_PURCHASER_TYPE_2';

        // if visitor is purchaser (ec_order==1)
        // and RTA returns time_since_last_order_seg = STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01
        // then set time_since_last_order_seg = STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_02
        if(($params['ec_order']==1) && ($entries['time_since_last_order_seg']=='STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01')) 
            $entries['time_since_last_order_seg']='STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_02';

        // if visitor is newsletter recipient (etcc_newsletter]==1)
        // then set is_newsletter_recipient = STC_CC_ATTR_VALUE_NEWSLETTER_1
        if($params['etcc_newsletter']==1) 
            $entries['is_newsletter_recipient']='STC_CC_ATTR_VALUE_NEWSLETTER_1';

        // if visitor is customer (etcc_cust==1)
        // then set customer_type = STC_CC_ATTR_VALUE_CUSTOMER_TYPE_1
        if($params['etcc_cust']==1) 
            $entries['customer_type']='STC_CC_ATTR_VALUE_CUSTOMER_TYPE_1';

        // if visitor is returning (returning==1)
        // then set visitor_type = STC_CC_ATTR_VALUE_VISITOR_TYPE_1
        if($params['returning']==1) 
            $entries['visitor_type']='STC_CC_ATTR_VALUE_VISITOR_TYPE_1';

        // if visitor is returning (returning==1)
        // and RTA returns visit_count_seg=STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01
        // then set visit_count_seg=STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_02
        if(($params['returning']==1) && ($entries['visit_count_seg']=='STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01')) 
            $entries['visit_count_seg']='STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_02';

        dblog_debug("EtrackerRTA/getEtrackerRTAData:\n" . print_r($entries,true));
        return $entries;

    }

    /*
     * Contact the etracker RealTimeAPI and retrieve result for the given user
     * $et and $et_coid define the visotor and the client
     * $session_time (time in seconds) and $page_impressions are values of the current session
     * For unittesting purposes the API URL can be mocked by passing "et_unittest" for $et
     */
    private function getRTAResponse($et,$et_coid,$session_time,$page_impressions=-1,$referer,$etcc_cmp) {
        $CI = & get_instance();
        if($et == 'et_unittest')
            $rtaUrl = $CI->config->item('unittest_baseurl') . "etracker_rta_mock.js?";
        else
            $rtaUrl = $CI->config->item('etracker_rta_url');

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        //$et = "JENR2LdEq+0pjCQKNjeTEH+/xs0nM/GpWbBFwGXVK2w=";
        // create json object to pass as POST body
        $body = '{"sessionDetails": 
            {
            "sessionTime":'. $session_time . ',
            "pageImpressions":' . $page_impressions . ',
            "userAgent" :"' . $user_agent . '",
            "campaign" :"' . $etcc_cmp . '",
            "referrerDomain" :"' . $referer . '"}}';
        $url = $rtaUrl . "quota=suppress_counting&et=" . rawurlencode($et)  . "&_et_coid=" . $et_coid;  
        dblog_debug("EtrackerRTA/rtaurl: $url $body");
        $CI->load->library('curl');
        $CI->curl->create($url);
        $CI->curl->options(array(
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_USERAGENT=>$user_agent,
            CURLOPT_FAILONERROR=>false,
            CURLOPT_HTTPHEADER=>array("Accept:application/json","Content-type: application/json"),            
        ));
        $CI->curl->post($body);
        $response = $CI->curl->execute();
        dblog_debug("$response");
        $curlInfo = $CI->curl->info;
        $responsecode = $curlInfo['http_code'];

        if($responsecode == 200) {
            $rtaResponse = $response;
        }
        else if($responsecode == 404) {
            $rtaResponse = (array($this->getRTADefaultResponse(),$response));
        }
        else {
            $rtaResponse = false;
        }
        dblog_debug("EtrackerRTA/response: " . print_r($rtaResponse,true));
        return $rtaResponse;
    }

    /*
     * Decode the response of etracker RTA and derive an associative array from it
     * Which attributes are to be retrieved is configured in array $att_list below
     */
     private function decodeRTAResponse($jsonResponseString) {
        $rta_result = array();
        $result = json_decode(utf8_encode($jsonResponseString));
        if(isset($result)) {
            $status = $result->status;
            if(($status=='success') || ($status=='error')) {
                $header = $result->header;
                $data = $result->data;
                for($i=0;$i<sizeof($header);$i++) {
                    $rta_result[$header[$i]] = $data[$i];
                }
            }
        }
        return $rta_result;
    }

    // Provide a default response which is used in case RTA does not return a visitor profile
    private function getRTADefaultResponse() {
        $currentTime = number_format(time() * 1000,0,".","");     
        $response = <<< EOT
{
    "status": "success",
    "version": 2,
    "msg": "Could not get a valid user. Returning a standard profile.",
    "header":
[
    "customer_type",
    "purchaser_type",
    "is_newsletter_recipient",
    "visitor_type",
    "visit_count_seg",
    "time_since_last_order_seg",
    "avg_order_value_seg",
    "frequency_seg",
    "engagement_lifetime"
],
"data": 
    [
        "STC_CC_ATTR_VALUE_CUSTOMER_TYPE_2",
        "STC_CC_ATTR_VALUE_PURCHASER_TYPE_1",
        "STC_CC_ATTR_VALUE_NEWSLETTER_2",
        "STC_CC_ATTR_VALUE_VISITOR_TYPE_2",
        "STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01",
        "STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01",
        "STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_01",
        "STC_CC_ATTR_VALUE_FREQUENCY_SEG_01",
        $currentTime
    ]
}
EOT;
        return $response;
    } 
}