<?php

// include PHP SDK to ensure usage of same object structure as for SDK
include (__DIR__ . "/../../../../clientsdks/apiv2-php-client/etrackerposdk.php");

class apiv2 extends CI_Controller {

    private $httpReturnCode = 0;
    private $errorInternalCode = 0;
    private $errorDescription = '';

    function __construct() {
        parent::__construct();
        $this->load->model('apimodel');
        $this->load->model('optimisation');
        $this->load->library('session');
    }

    function index() {
        
    }

    // dispatch an API v2 call and find the correct function to process the request
    function dispatch() {
        //  analyze the URI, sanitize and assign parameters
        $i = 3;
        $segments = array();
        while ($this->uri->segment($i)) {
            $segments[] = $this->uri->segment($i);
            $i++;
        }
        //print_r($segments);
        $apirealm = 'NA'; // which kind of api is requested?
        if (isset($segments[0])) {
            if (($segments[0] == 'management') || ($segments[0] == 'remote'))
                $apirealm = $segments[0];
        }
        $noun = 'NA'; // what noun does the request refer to?
        if (isset($segments[1])) {
            if ($segments[1] == 'accounts')
                $noun = $segments[1];
        }
        $accountid = 'NA'; // is the account-id set?
        if (isset($segments[2])) {
            if (ctype_alnum($segments[2]))
                $accountid = $segments[2];
        }
        if (isset($segments[3])) {
            if ($segments[3] == 'tests')
                $noun = $segments[3];
        }
        $testid = 'NA'; // is the test-id set?
        if (isset($segments[4])) {
            if (ctype_alnum($segments[4]))
                $testid = $segments[4];
        }
        if (isset($segments[5])) {
            if (($segments[5] == 'variants') || ($segments[5] == 'goals') || ($segments[5] == 'suggestions') || ($segments[5] == 'conversions'))
                $noun = $segments[5];
        }
        $variantid = 'NA';
        $goalid = 'NA';
        if (isset($segments[6])) {
            if (ctype_alnum($segments[6])) {
                if ($noun == 'variants')
                    $variantid = $segments[6];
                if ($noun == 'goals')
                    $goalid = $segments[6];
            }
        }
        // analyze the request method ("verb")
        $verb = $_SERVER['REQUEST_METHOD'];
        // get the POST body
        $body = file_get_contents('php://input');
        // get the qualifiers in the querystring
        $querystring = parse_url($url, PHP_URL_QUERY);

        // evaluate the desired function from the provided input parameters and validate correct semantics
        if ($apirealm == 'management') {
            if ($noun == 'accounts') {
                if ($verb == 'GET') {
                    $function = 'listAccounts';
                } elseif ($verb == 'POST') {
                    $function = 'insertAccount';
                } elseif ($verb == 'PUT') {
                    $function = 'updateAccount';
                } elseif ($verb == 'DELETE') {
                    $function = 'deleteAccount';
                } else {
                    $this->setError(422, 422040, "Invalid method for entity accounts");
                }
            } elseif ($noun == 'tests') {
                if ($verb == 'GET') {
                    if ($testid != 'NA')
                        $function = 'getTest';
                    else
                        $function = 'listTests';
                }
                elseif ($verb == 'POST') {
                    $function = 'insertTest';
                } elseif ($verb == 'PUT') {
                    $function = 'updateTest';
                } elseif ($verb == 'DELETE') {
                    $function = 'deleteTest';
                } else {
                    $this->setError(422, 422040, "Invalid method for entity tests");
                }
            } elseif ($noun == 'variants') {
                if ($verb == 'GET') {
                    $function = 'listVariants';
                } elseif ($verb == 'POST') {
                    $function = 'insertVariant';
                } elseif ($verb == 'PUT') {
                    $function = 'updateVariant';
                } elseif ($verb == 'DELETE') {
                    $function = 'deleteVariant';
                } else {
                    $this->setError(422, 422040, "Invalid method for entity variants");
                }
            } elseif ($noun == 'goals') {
                if ($verb == 'GET') {
                    $function = 'listGoals';
                } elseif ($verb == 'POST') {
                    $function = 'insertGoal';
                } elseif ($verb == 'PUT') {
                    $function = 'updateGoal';
                } elseif ($verb == 'DELETE') {
                    $function = 'deleteGoal';
                } else {
                    $this->setError(422, 422040, "Invalid method for entity goals");
                }
            } else {
                $this->setError(422, 422030, "No valid Resource Entity found (must be one of accounts, tests, variants, goals)");
            }
        } elseif ($apirealm == 'remote') {
            if ($noun == 'suggestions') {
                if ($verb == 'POST') {
                    $function = 'retrieveSuggestion';
                } else {
                    $this->setError(422, 422040, "Invalid method for entity suggestions");
                }
            } elseif ($noun == 'conversions') {
                if ($verb == 'POST') {
                    $function = 'handleConversion';
                } else {
                    $this->setError(422, 422040, "Invalid method for entity conversions");
                }
            } else {
                $this->setError(422, 422030, "No valid Resource Entity found  (must be one of suggestions, conversions)");
            }
        } else {
            $this->setError(422, 422020, "Invalid API prefix (must be one of management, remote)");
        }

        if ($this->errorInternalCode != 0) {
            // respond with an error
            $this->respondError();
            exit(0);
        } else {
            /*
              echo " fountion = " . $function;
              echo " noun = " . $noun;
              echo " accountid = " . $accountid;
              echo " testid = " . $testid;
              echo " variantid = " . $variantid;
              echo " goalid = " . $goalid;
              echo " verb = " . $verb;
              echo " body = " . $body;
             */
            // execute the function
            $response = $this->{$function}($accountid, $testid, $variantid, $goalid, $body);
            //print_r($response);
            if ($response == false) {
                $this->respondError();
            } else {
                $this->httpReturnCode = $response['code'];
                $this->respondSuccess($response['data']);
            }
        }
        die();
    }

    // helper function: response with success message + data payload
    private function respondSuccess($data) {
        $json = json_encode($data);
        header("Content-Type: application/json");
        header('HTTP/1.1 ' . $this->httpReturnCode, true, $this->httpReturnCode);
        echo $json;
    }

    // helper function: response with an error
    private function respondError() {
        $res = array();
        $res['code'] = $this->errorInternalCode;
        $res['description'] = $this->errorDescription;
        $json = json_encode($res);
        header("Content-Type: application/json");
        header('HTTP/1.1 ' . $this->httpReturnCode, true, $this->httpReturnCode);
        echo $json;
    }

    // helper function: handle errors
    private function setError($httpCode, $internalCode, $description) {
        $this->httpReturnCode = $httpCode;
        $this->errorInternalCode = $internalCode;
        $this->errorDescription = $description;
    }

    // function for call GET /management/accounts/<client-id>/tests/
    private function listTests($accountid, $testid, $variantid, $goalid, $body) {
        // get qualifier
        $CI = & get_instance();
        $level = $CI->input->get('level');
        //echo "level: $level";
        //echo "testlist";
        $testIdList = $this->apimodel->getTests($accountid);
        //print_r($testIdList);
        $list = array();
        foreach ($testIdList as $testId) {

            if ($level == 'LIST') {
                $list[] = array('id' => $testId);
            } else {
                $mytest = new ApiTest($testId);
                $list[] = $mytest->toArray();
            }
        }
        $response = array('code' => 200, 'data' => $list);
        return $response;
    }

    // function for call GET /management/accounts/<client-id>/tests/<test-id>/
    private function getTest($accountid, $testid, $variantid, $goalid, $body) {
        try {
            $mytest = new ApiTest($testid);
        } catch (Exception $e) {
            if ($e->getCode() == 404) {
                $this->setError(404, 404, 'test id does not exist');
                return false;
            } else {
                $this->setError(500, 500, 'internal server error');
                return false;
            }
        }
        // success
        $response = array('code' => 200, 'data' => $mytest->toArray());
        return $response;
    }

}

/*
 * Inherits from the SDK-API Test class and adds functions for database access
 */

class ApiTest extends Test {

    public function __construct($testId = null) {
        parent::__construct();

        if ($testId != null) { // create test from database			
            $CI = & get_instance();
            $test = $CI->optimisation->getNotificationData($testId);
            if ($test == false) {
                throw new Exception('test not found', 404);
            }
            $controlid = $test['controlindex'];
            $controlpage = $test['pages'][$controlid];
            $this->setId($testId);
            $this->setName($test['collectionname']);
            $this->setPattern($controlpage['canonical_url']);
            $this->setAllocation($test['allocation']);
            $this->setCreated($test['creation_date']);
            $this->setResetted($test['restart_date']);
            $this->setVisitorcount($test['visitorcount']);
            $this->setConversioncount($test['conversioncount']);
            switch ($test['collectiontesttype']) {
                case 1:
                    $this->setType('SPLIT');
                    break;
                case 3:
                    $this->setType('VISUAL');
                    break;
                case 4:
                    $this->setType('REMOTE');
                    break;
            }
            switch ($test['status']) {
                case 0:
                    $this->setStatus('UNVERIFIED');
                    break;
                case 1:
                    $this->setStatus('PAUSED');
                    break;
                case 2:
                    $this->setStatus('RUNNING');
                    break;
            }
            switch ($test['progress']) {
                case 1:
                    $this->setWorkflow('UNKNOWN');
                    break;
                case 2:
                    $this->setWorkflow('READY');
                    break;
                case 3:
                    $this->setWorkflow('LEADER');
                    break;
            }
            switch ($test['autopilot']) {
                case 0:
                    $this->setAutopilot('OFF');
                    break;
                case 1:
                    $this->setAutopilot('ON');
                    break;
            }
        }
    }

}

?>