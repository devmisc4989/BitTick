<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Wywy {

    private $visitor; // holds the bto visitor array;

    // Constructor takes the optimisation webservice visitor array as an argument
    public function __construct($visitor) {
        $this->visitor = $visitor;
    }

    // retrieve wether a specific commercial has been aired or not
    public function commercialAired($commercial) {
        $results = $this->getWywyResults();
        $res = false;
        foreach($results as $wr) {
            if($commercial == $wr['channelid']) {
                $res = true;
                $break;                
            }
            if($commercial == $wr['customerid']) {
                $res = true;
                $break;                
            }
        }
        return $res;
    }

    private function getWywyResults () {
        $CI = &get_instance();
        $CI->load->database();
        $sql = "SELECT * FROM int_wywy
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $res = mysql_query($sql);
        $wywyresults = array();
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $wywyresults[] = $row;
        }
        return($wywyresults);
    }

}