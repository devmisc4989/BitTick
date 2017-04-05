<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * This class gets all requests from the client, verifies the client credentials in the DB, instantiates
 * the appropriate class depending on the URL parameters, sets the class variables and calls its main 
 * method.
 * Once the main class method is called, it will handle the rest of the variables to be set and the 
 * appropriate method to be called depending on the rest of the parameters in the URL
 */
class apiv1 extends CI_Controller {

    const API_PREFIX = 'apiv1';

    private $resources = array(
        'goal' => '_goals',
        'trend' => '_trend',
        'decision' => '_decisions',
        'decisiongroup' => '_decisiongroups',
        'project' => '_projects',
        'rule' => '_rules',
        'account' => '_accounts'
    );

    function __construct() {
        parent::__construct();
        $this->load->model('apiv1_core');
    }

    /**
     * If called without any parameters, returns a 400 "Bad request" error
     */
    public function index() {
        header("Content-Type: application/json");
        header("HTTP/1.1 404", true, 404);

        $response = $this->apiv1_core->errorResponse(404001, '');
        echo json_encode($response);
        return;
    }

    /**
     * First, creates an associative array starting from the 3rd element (api/v1/HERE/) to verify the
     * method or class->method to be called.
     * if the request is to handle a "form" based login, calls the method postLogin.
     * or else, gets the user type (tenant or client) by verifying his credentials in the DB, then calls the 
     * method that will instantiate the corresponding class and will return the results of calling its methods.
     * if the user is not found in the DB, returns a 401 "Unauthorized" http error
     * 
     * @return JSON
     */
    public function handleRequests() {
        $response = null;
        try {
            $urlArray = $this->uri->uri_to_assoc(3);
            if (array_key_exists('login', $urlArray)) {
                $response = self::postLogin();
            } else {
                $login = self::clientLogin();
                $response = self::instantiateRequestedClass($urlArray, $login);
            }
        } catch (Exception $ex) {
            $response = $this->apiv1_core->errorResponse($ex->getCode(), $ex->getMessage());
        }

        $httpcode = substr($response['code'], 0, 3);

        $name = '';
        foreach ($urlArray as $key => $value) {
            $this->$key = $value;
            $name = ucfirst($key);
        }
        $method = strtolower($this->input->server('REQUEST_METHOD')) . $name;
        $reqParam = $this->input->get() ? $this->input->get() : file_get_contents("php://input");

        $msg = array(
            'resourceURL' => $this->uri->assoc_to_uri($urlArray),
            'className' => $response['className'],
            'methodName' => $method,
            'requestJSON' => json_encode($reqParam),
            'responseHTTP' => $httpcode,
            'responseJSON' => ($response['message']) ? json_encode($response) : json_encode($response['res']),
        );

        $logCid = isset($urlArray['account']) ? $urlArray['account'] : NULL;
        dblog_message(LOG_LEVEL_INFO, LOG_TYPE_API, "API: " . 
            $this->input->server('REQUEST_METHOD') . " " .
            $this->uri->assoc_to_uri($urlArray) . "\n" . print_r($msg, TRUE), $logCid);

        header("Content-Type: application/json");
        header("HTTP/1.1 $httpcode", true, $httpcode);

        if ($response['message']) {
            echo json_encode($response);
        } else {
            echo json_encode($response['res']);
        }
        return;
    }

    /**
     * logs the client in and returns the corresponding persmissions (api-tenant or api-client) and the
     * client ID in an array
     * @return array
     * @throws Exception
     */
    private function clientLogin() {
        $apikey = $this->input->server('PHP_AUTH_USER');
        $apisecret = $this->input->server('PHP_AUTH_PW');

        if (strlen(trim($apikey)) < 2 || strlen(trim($apisecret)) < 2) {
            throw new Exception('', 401000);
        }
        return $this->apiv1_core->clientLogin($apikey, $apisecret);
    }

    /**
     * This method is designed to handle form-based login, the user selects a usertype in the login form
     * (api-tenant or api-client), then the external server posts the data entered along with the
     * usertype to be verified here. 
     * If the login is succesful and the expected usertype is equal to the value returned by the parent
     * method "clientLogin", then returns the clientid and apikey
     * or else, returns a 401 "unauthorized" error.
     * @return JSON
     * @throws Exception
     */
    private function postLogin() {
        $param = json_decode(file_get_contents("php://input"));
        $res = $this->apiv1_core->clientLogin($param->apikey, $param->apisecret);

        if ($param->usertype != $res->usertype) {
            throw new Exception('', 401000);
        }

        $response = $this->apiv1_core->successResponse(200, $res->clientid);
        $response['className'] = 'apiv1_core';
        return $response;
    }

    /**
     * this method will instantiate the required class by verifying the rest of the URL parameters. 
     * It instantiates the appropriate class, sets the corresponding variables and calls its main method.
     * if the class name does not exist, returns a 400 "Bad request" error
     * 
     * @param Array $urlArray - the URL segmets mapped as an associative array
     * @param Array $res - contains the clientid, apikey, usertype and api_clientid of the logged user
     * @return JSON with the response object (returned by the method of the corresponding class)
     * @throws Exception
     */
    private function instantiateRequestedClass($urlArray, $res) {
        foreach ($this->resources as $key => $value) {
            if (array_key_exists($key, $urlArray) || array_key_exists($key . 's', $urlArray)) {
                $clssname = self::API_PREFIX . $value;
                break;
            }
        }

        if (!file_exists(APPPATH . "models/$clssname.php")) {
            throw new Exception('', 404001);
        }

        $this->load->model($clssname);

        $reqParam = $this->input->get() ? $this->input->get() : file_get_contents("php://input");

        $this->$clssname->__set('userip', $this->input->server('REMOTE_ADDR'));
        $this->$clssname->__set('apikey', $res->apikey);
        $this->$clssname->__set('clientid', $res->clientid);
        $this->$clssname->__set('usertype', $res->usertype);
        $this->$clssname->__set('apiclientid', $res->apiclientid);
        $this->$clssname->__set('requestMethod', $this->input->server('REQUEST_METHOD'));
        $this->$clssname->__set('requestParameters', $reqParam);

        $response = $this->$clssname->index($urlArray);
        $response['className'] = $clssname;
        return $response;
    }

}
