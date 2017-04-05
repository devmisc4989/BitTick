<?php
/**
* Library to provide multiple databases
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Multidb {

    private $clientdb;

    public function __construct($params=false) {
        $this->setClientDbConnection($params);
    }

    public function setClientDbConnection($params) {

        $CI = & get_instance();
        lazyLoadDB();
        if(is_array($params)) {
            $clientDbName = $params['dbname'];
            $clientDbServer = $params['dbserver'];
        }
        else {
            $clientDbName = $CI->config->item('CLIENT_DB_NAME');
            $clientDbServer = $CI->config->item('CLIENT_DB_SERVER');            
        }
        $useDefault = false;
        if(!$clientDbName)
            $useDefault = true;
        if($clientDbName == 'localhost')
            $useDefault = true;
        if($clientDbName == 'default')
            $useDefault = true;
        if($clientDbName == 'NA')
            $useDefault = true;

        if($useDefault) {
            // use the default database
            dblog_debug("MultiDB/use database default");
            $CLIENT_DB = $CI->load->database('default', TRUE);
        }
        else {
            $clientDbUsername = $CI->db->username;
            $clientDbPassword = $CI->db->password;

            $config['hostname'] = $clientDbServer;
            $config['username'] = $clientDbUsername;
            $config['password'] = $clientDbPassword;
            $config['database'] = $clientDbName;
            $config['dbdriver'] = "mysql";
            $config['dbprefix'] = "";
            $config['pconnect'] = FALSE;
            $config['db_debug'] = TRUE;
            $config['cache_on'] = FALSE;
            $config['cachedir'] = "";
            $config['char_set'] = "utf8";
            $config['dbcollat'] = "utf8_general_ci";

            dblog_debug("MultiDB/use database $clientDbName");
            $CLIENT_DB = $CI->load->database($config,true);
        } 
        $this->clientdb = $CLIENT_DB;
    }

    public function getClientDb() {
        return $this->clientdb;
    }

}