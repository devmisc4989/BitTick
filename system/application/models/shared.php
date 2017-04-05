<?php

class Shared extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /*
     * debug function, not for production
     */

    function mvtlpdebug($landing_pageid) {
        $sql = "select lp.name as name, lp.mvt_order as mvt_order, mf.dom_path as path, ml.level_content as content
			from mvt_level_to_page mlp,landing_page lp, mvt_level ml, mvt_factor mf
			where mlp.landing_pageid=lp.landing_pageid
			and mlp.mvt_level_id=ml.mvt_level_id
			and ml.mvt_factor_id=mf.mvt_factor_id
			and lp.landing_pageid= $landing_pageid";
        //die($sql);
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $entries[] = $row;
        }
        return($entries);
    }

    /*
     * authenticate admin-user
     */

    function authenticateAdminUser($username, $password, $role) {
        $sql = "select count(*) as cnt from admin_users where username='$username'
				and password='$password' and status=1 and role='$role' limit 1";        
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        $numusers = $row['cnt'];
        if ($numusers == 1)
            return(true);
        else
            return(false);
    }

    /*
     * return password of a user who is an API user
     */

    function getAPISecret($username) {
        $sql = "select password from admin_users where username='$username'
				and status=1 and role='api' limit 1";
        //echo $sql;
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        if ($row) {
            $pwd = $row['password'];
            return $pwd;
        } else {
            return false;
        }
    }

    /*
     * create a string representstion of a personalization rule. This string is a line of PHP code
     * which can be executed in an eval statement.
     * Return false if an argument contains invalid characters and thus might be potentially harmful
     * since the result is executed with eval, the sanity check must be very strict
     */
    function compileRule($rule_id) {
        $sql = "select operation from rule where rule_id=$rule_id";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        $rule = array();
        $operation = $row['operation'];
        
        $conditions = array();
        $sql = "select * from rule_condition where rule_id=$rule_id";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $conditions[] = $row;
        }
        
        $phpcode = '$result = ((1==1) ';
        foreach($conditions as $cond) {
            $type = $cond['type'];
            $arg = $cond['arg'];
            // dispatch the rule type
            if($type == 'useragent_contains') {
                // only alphanumeric allowed
                if (!ctype_alnum($arg))
                    return false;
                $codeline = '(!stripos($vprofile["user-agent"],"' . $arg . '") === false)';
            }
            if($type == 'segment_isset') {
                // only alphanumeric allowed
                if (!ctype_alnum($arg))
                    return false;
                $codeline = '(in_array("' . $arg . '",$vprofile["segments"]))';
            }
            
            if($operation == 'AND') {
                $phpcode .= " AND " . $codeline;
            }
        }
        $phpcode .= ") ? true : false;";
        
        return $phpcode;
    }
    
    
}

?>