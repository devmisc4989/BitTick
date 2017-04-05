<?php
/**
* Handle configuration values stored in database fields for accounts and projects
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Dbconfiguration {
    
    private $accountConfiguration;
    private $projectConfiguration;
    public $defaultAccountConfiguration = array(
        'CLIENT_DB_NAME' => 'NA', // specific DB for client request data
        'CLIENT_DB_SERVER' => 'localhost', // specific DB server for client request data
        'TT_CTR_WEIGHT' => 'NA', // relative weight of click goal in teaser test
        'TT_TOP_WEIGHT' => 'NA', // relative weight of time on page goal in teaser test
        'TT_PILIFT_WEIGHT' => 'NA', // relative weight of page impression lift in teaser test
        'WA_INTEGRATION' => 'NONE', // web analytics integration - one of NONE, GTM (google tag manager), ETRACKER
        'WA_GTM_DATALAYER_NAME' => 'NONE', // in case we have a gtm integration, this configures the name of the 
            // datalayer in case it is different from "dataLayer". 'NONE' means we use the default
        'DEBUG_PROJECT_ID' => 'NA', // set logelvel to debug for a certain project
        'DEBUG_CLIENT_IP' => 'NA', // set logelvel to debug for a certain IP or IP range
    );
    public $defaultProjectConfiguration = array(
        'TT_INTERFACE_TYPE' => 'API', // how teasertests integrate 
        'DEFERRED_IMPRESSION_SELECTOR' => 'NA',
        'DEFERRED_IMPRESSION_SELECTOR_ACTION' => 'not_used',
        'IP_FILTER_IPLIST' => '',
        'IP_FILTER_ACTION' => 'not_used',
        'IP_FILTER_SCOPE' => 'all',
    );

    public function getDefaultProjectConfiguration() {
        return $this->defaultProjectConfiguration;
    }

    // return account-config as created form serialized string
    public function createAccountConfigurationFromString($accountid, $configString) {
        $this->createConfigurationFromString('account', $accountid, $configString);
        return($this->accountConfiguration[$accountid]);
    }

    // return project-config as created form serialized string
    public function createProjectConfigurationFromString($projectid, $configString) {
        $this->createConfigurationFromString('project', $projectid, $configString);
        return($this->projectConfiguration[$projectid]);
    }

    public function createAccountConfigurationFromDatabase($accountid) {
        
    }

    public function saveAccountConfigurationToDatabase($accountid) {
        
    }

    public function createProjectConfigurationFromDatabase($project) {
        
    }

    /**
     * Given a "project" array, creates a new element called "config" and adds the corresponding fields to it. 
     * (At first it will be only TT_INTERFACE_TYPE, later it could contain start_date, allocation, among others)
     * every time it adds the new element to the $config array, removes the unnecessary element from the $project array.
     * @param Array $project - containing the name, mainurl, allocation, etcetera...
     * @param JSON $oldConfig - If we are in "edit" mode, it contains the old configuration in JSON
     * @return Array
     */
    public function saveProjectConfigurationToDatabase($project, $oldConfig = FALSE) {
        $fields = array_keys($this->defaultProjectConfiguration);
        $config = $oldConfig ? json_decode($oldConfig, TRUE) : array();

        foreach ($project as $key => $value) {
            if (in_array($key, $fields)) {
                $config[$key] = $value;
                unset($project[$key]);
            }
        }
        $project['config'] = json_encode($config);
        return $project;
    }

    // create config from serialized string
    private function createConfigurationFromString($type, $id, $configString) {
        if($type=='account') 
            $default = $this->defaultAccountConfiguration;
        else
            $default = $this->defaultProjectConfiguration;

        $myconfig = json_decode($configString,true);
        if(!is_array($myconfig))
            $myconfig = array();
        $targetConfiguration = array();
        foreach($default as $key=>$value) {
            if(isset($myconfig[$key])) {
                $targetConfiguration[$key] = $myconfig[$key];
            }
            else {
                $targetConfiguration[$key] = $value;                
            }
        }

        if($type=='account')
            $this->accountConfiguration[$id] = $targetConfiguration;
        else
            $this->projectConfiguration[$id] = $targetConfiguration;
    }

    // take an array with config parameters (key-value) and create or modify the CI config with it
    public function createCodeigniterAccountConfiguration($configArray) {
        $allowedConfigItems = $this->defaultAccountConfiguration;
        $CI = & get_instance();
        foreach ($configArray as $key => $value) {
            if(array_key_exists($key, $allowedConfigItems)) {
                if($value != 'NA') {
                    switch($key) {
                        case 'CLIENT_DB_NAME':
                            $CI->config->set_item($key, $value);
                            break;
                        case 'CLIENT_DB_SERVER':
                            $CI->config->set_item($key, $value);
                            break;
                        case 'WA_INTEGRATION':
                            $CI->config->set_item($key, $value);
                            break;
                        case 'WA_GTM_DATALAYER_NAME':
                            $CI->config->set_item($key, $value);
                            break;
                        case 'DEBUG_PROJECT_ID':
                            $CI->config->set_item($key, $value);
                            break;
                        case 'DEBUG_CLIENT_IP':
                            $CI->config->set_item($key, $value);
                            break;
                        case 'TT_CTR_WEIGHT':
                            $combinedGoal = $CI->config->item('COMBINED_GOAL_COMBINATION_RULE');
                            $combinedGoal[GOAL_TYPE_CLICK] = $value;
                            $CI->config->set_item('COMBINED_GOAL_COMBINATION_RULE', $combinedGoal);
                            break;                                                      
                        case 'TT_TOP_WEIGHT':
                            $combinedGoal = $CI->config->item('COMBINED_GOAL_COMBINATION_RULE');
                            $combinedGoal[GOAL_TYPE_TIMEONPAGE] = $value;
                            $CI->config->set_item('COMBINED_GOAL_COMBINATION_RULE', $combinedGoal);
                            break;                                                      
                        case 'TT_PILIFT_WEIGHT':
                            $combinedGoal = $CI->config->item('COMBINED_GOAL_COMBINATION_RULE');
                            $combinedGoal[GOAL_TYPE_PI_LIFT] = $value;
                            $CI->config->set_item('COMBINED_GOAL_COMBINATION_RULE', $combinedGoal);
                            break;                                                      
                    }                                                
                }
            }
        }
    }

}