<?php

class optimisation extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->library('calculation');
        // DB is not loaded on init, only when needed (see helper function lazyLoadDB()
    }

    // helper for making cache debugging more fine grained
    private function dblog_cachedebug($msg) {
        //dblog_debug($msg);
    }

    /*
     * flush APC cache
     * delete all entries for a given collection and it's client from the memory cache after 
     * the client changed something in the collection
     */
    function flushCollectionCache($collectionid) {
        lazyLoadDB();
        self::dblog_cachedebug('reload CollectionCache, collectionid=' . $collectionid);
        $sql = "select lc.code, c.clientid_hash, c.clientid from landingpage_collection lc, client c
            where lc.clientid = c.clientid
            and lc.landingpage_collectionid=$collectionid limit 1";
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $collectioncode = $row['code'];
            $clientid_hash = $row['clientid_hash'];
            $clientid = $row['clientid'];
            self::dblog_cachedebug('--- reload getcollectionstatus, collectioncode=' . $collectioncode);
            $this->getcollectionstatus($collectioncode,true);
            self::dblog_cachedebug('--- reload getMatchingLandingPagesForClient, clientid_hash=' . $clientid_hash);
            $this->getMatchingLandingPagesForClient($clientid_hash,'dummy','dummy',false,true);
            self::dblog_cachedebug('--- reload getclientstatus, clientid_hash=' . $clientid_hash);
            $this->getclientstatus($clientid_hash,true);
            self::dblog_cachedebug('--- reload getClientActionGoals, clientid=' . $clientid);
            $this->getClientActionGoals($clientid,false,true);
            self::dblog_cachedebug('--- reload getcollectionIdForCode: getKpiResultset');
            $this->getcollectionIdForCode(true);
            $this->flushKpiResultsForCollection($collectionid);                
        }
    }

    /*
     * Delete cached kpiResults for a given collection
     */
    function flushKpiResultsForCollection($collectionid) {
        $sql = "select page_groupid from page_group
            where landingpage_collectionid=$collectionid";
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $key = "pkpi_" . $collectionid . "_" . $row['page_groupid'];
            self::dblog_cachedebug('--- reload getProjectKPI, ' . $key);
            $this->getProjectKPI($collectionid,$row['page_groupid'],false,true);
        }
        $key = "pkpi_" . $collectionid . "_-1";
        self::dblog_cachedebug('--- reload getProjectKPI, ' . $key);
        $this->getProjectKPI($collectionid,-1,false,true);
    }

    /*
     * flush APC cache for collection only
     */
    function flushCollectionCacheByCode($collectioncode) {
        lazyLoadDB();
        self::dblog_cachedebug('--- reload getcollectionstatus, collectioncode=' . $collectioncode);
        $this->getcollectionstatus($collectioncode,true);
        self::dblog_cachedebug('--- reload getcollectionIdForCode: code_2_id_map');
        $this->getcollectionIdForCode(true);
    }

    /*
     * flush APC cache
     * delete entry for a given visitor and collection
     */

    function flushRequestEventsCache($visitorid, $collectionid) {
        $apckey = $visitorid . "_" . $collectionid;
        dblog_debug('flushVisitedpagesCache, key=' . $apckey);
        apch_delete($apckey);
    }

    /*
     * flush APC cache
     * delete all entries for a given client
     */

    function flushAPCCacheForClient($clientcode) {
        lazyLoadDB();
        self::dblog_cachedebug('flushAPCCacheForClient, clientcode=' . $clientcode);
        $sql = "select clientid from client
			where clientid_hash = '$clientcode'
			limit 1";
        $query = $this->db->query($sql);    
        foreach ($query->result_array() as $row) {
            $clientid = $row['clientid'];
            self::dblog_cachedebug('--- reload getcollectionIdForCode: code_2_id_map');
            $this->getcollectionIdForCode(true);
            self::dblog_cachedebug('--- reload getMatchingLandingPagesForClient, clientid_hash=' . $clientcode);
            $this->getMatchingLandingPagesForClient($clientcode,'dummy','dummy',false,true);
            self::dblog_cachedebug('--- reload getclientstatus, clientid_hash=' . $clientcode);
            $this->getclientstatus($clientcode,true);
            self::dblog_cachedebug('--- reload getClientActionGoals, clientid=' . $clientid);
            $this->getClientActionGoals($clientid,false,true);

            $sql = "select lc.code,lc.landingpage_collectionid from landingpage_collection lc, client c
                where lc.clientid = c.clientid
                and c.clientid_hash='$clientcode'";
            $query2 = $this->db->query($sql);    
            foreach ($query2->result_array() as $row) {
                self::dblog_cachedebug('--- reload getcollectionstatus, collectioncode=' . $row['code']);
                $this->getcollectionstatus($row['code'],true);
                $this->flushKpiResultsForCollection($row['landingpage_collectionid']);
            }
        }
    }

    /*
     * flush APC cache
     * delete all entries for a given personalization rule
     */

    function flushAPCCacheForRule($ruleid) {
        lazyLoadDB();
        dblog_debug('flushAPCCacheForRule, rule_id=' . $ruleid);
        $sql = "select c.clientid_hash,c.clientid from client c, rule r
			where c.clientid = r.clientid
			and r.rule_id='$ruleid'";
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $clientid_hash = $row['clientid_hash'];
            $clientid = $row['clientid'];
            self::dblog_cachedebug('--- reload getMatchingLandingPagesForClient, clientid_hash=' . $clientid_hash);
            $this->getMatchingLandingPagesForClient($clientid_hash,'dummy','dummy',false,true);
            self::dblog_cachedebug('--- reload getClientActionGoals, clientid=' . $clientid);
            $this->getClientActionGoals($clientid,false,true);
        }
        $sql = "select lpc.code from landingpage_collection lpc, landing_page lp, rule r
            where lp.rule_id = r.rule_id
            and lpc.landingpage_collectionid = lp.landingpage_collectionid
            and r.rule_id='$ruleid'";
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $collectioncode = $row['code'];
            self::dblog_cachedebug('--- reload getcollectionstatus, collectioncode=' . $collectioncode);
            $this->getcollectionstatus($collectioncode,true);
        }
    }

    // validate a tracecode
    function getTracecodeData($code) {
        lazyLoadDB();
        $sql = "select * from tracecode where code='$code'
                and createdate >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $res = mysql_query($sql);
        trackMysqlError(__function__);
        if ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $content = unserialize(base64_decode($row['content']));
            if (is_array($content))
                $row['content'] = $content;
            return $row;
        }
        else {
            return false;
        }
    }

    // function to help dispatcing of OCPC-trackongcodes
    // returns all landingpages from active tests for client that include the given substring as canonical_url
    function getMatchingLandingPagesForClient($clienthash, $canonical_pageurl, $pagename, $diagnose = false, $reload=false) {
        // try to get cached data from APC
        $key = $clienthash;
        $entries = getValueFromCache($key);
        $recalculate = ((!$entries) || ($reload));
        if($recalculate) {
            // step 1: select control and variant pages
            // value 0 in field gi is a constant dummy-placeholder which indicates the goal id (see below), which does not apply here
            $full_entries = array();
            $sql = "select lp.canonical_url as cu, lp.pagetype as pt, lc.landingpage_collectionid as ci, 
				lc.code as co, 0 as gi, lc.name as cn, lc.testtype as tt, lp.page_groupid as pg
				from landing_page lp, landingpage_collection lc, client c
				where lp.landingpage_collectionid = lc.landingpage_collectionid
				and lc.clientid = c.clientid
				and c.clientid_hash = '$clienthash'
				and ((c.status = 1) or (c.status = 6))
				and lc.status = 2
				and lp.pagetype=1
				and lc.tracking_approach = 1
				and length(lp.canonical_url) > 0
                order by lp.landingpage_collectionid desc";
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $full_entries[] = $row;
            }
            // step2: select target-page goals
            // pt=pagetype is set to 3 indicating that this is a target page goal and not a control or variant page
            $sql = "select cg.arg1 as cu, 3 as pt, lc.landingpage_collectionid as ci, lc.code as co, 
                cg.collection_goal_id as gi, cg.level as gl, lc.name as cn, 'NA' as tt, 0 as pg
                from landingpage_collection lc,collection_goals cg, client c
                where lc.landingpage_collectionid = cg.landingpage_collectionid
                and cg.type=3
                and cg.status=1
                and lc.clientid=c.clientid
                and lc.status = 2
                and c.clientid_hash='$clienthash'";
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $full_entries[] = $row;
            }
            // step3: select perso conditions of type target-page-goal
            // pt=pagetype is set to 4 indicating that this is a perso condition target-page goal and not a control or variant page
            $sql = "select rc.arg as cu, 4 as pt, 0 as ci, 0 as co,
                rc.rule_condition_id as gi, '' as cn, 'NA' as tt, 0 as pg
                from rule_condition rc, rule r, client c
                where rc.rule_id = r.rule_id
                and rc.type = 'targetpage_opened'
                and r.clientid = c.clientid
                and c.clientid_hash='$clienthash'";
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $full_entries[] = $row;
            }
            // step4: store clientid in cache
            $sql = "select clientid as csh from client where clientid_hash='$clienthash' limit 1";
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $clientid = $row['csh'];
            }

            $entries = array();
            $entries['clientid'] = $clientid;
            $entries['data'] = $full_entries;

            storeValueInCache($key,$entries);                        
        }
        $mydata = array();
        dblog_debug("BTO/OPT-30-021/ compare URL against stored test URLs for client\nurl:" . $canonical_pageurl . "\npagename:" . $pagename);
        foreach ($entries['data'] as $entry) {
            // handling of the canonical URL has been expanded from single string to an array.
            // to be downward compatible we do an upcast if necessary
            $canonicalUrls = json_decode($entry['cu'],true);
            if(!$canonicalUrls) {
                $canonicalUrls = array(
                    array('mode' => 'include','url' => $entry['cu'])
                );
            }
            // distinguish between normal mode and diagnose mode (and then providing more data)
            if (!$diagnose) {
                switch ($entry['pt']) {
                    case 1:
                        $pagetype = "original";
                        break;
                    case 2:
                        $pagetype = "variante";
                        break;
                    case 3:
                        $pagetype = "target page goal";
                        break;
                }
                $collectionid = $entry['ci'];
                if (isset($canonical_pageurl)) {
                    if ($this->pageUrlMatchesProjectUrls($canonical_pageurl,$canonicalUrls)) {
                        dblog_debug("BTO/OPT-30-022/ (MATCH / $pagetype / $collectionid) URL vs. " . print_r($canonicalUrls,true));
                        $mydata[] = $entry;
                    } else {
                        dblog_debug("BTO/OPT-30-022/ (FAIL / $pagetype / $collectionid) URL vs. " . print_r($canonicalUrls,true));
                    }
                }
                if ($pagename != 'NA') {
                    if ($this->pageUrlMatchesProjectUrls($pagename,$canonicalUrls)) {
                        dblog_debug("BTO/OPT-30-022/ (MATCH / $pagetype / $collectionid) pagename vs. " . print_r($canonicalUrls,true));
                        $mydata[] = $entry;
                    } else {
                        dblog_debug("BTO/OPT-30-022/ (FAIL / $pagetype / $collectionid) pagename vs. " . print_r($canonicalUrls,true));
                    }
                }
            } else { // diagnose mode
                // only use test page patterns, no target page goal patterns
                if ($entry['pt'] == 1) {
                    // check for match
                    $match = 0;
                    if ($this->pageUrlMatchesProjectUrls($canonical_pageurl,$canonicalUrls))
                        $match = 1;
                    if ($this->pageUrlMatchesProjectUrls($pagename,$canonicalUrls))
                        $match = 1;
                    $mydata[] = array($entry['cn'], $canonicalUrls, $match, $entry['ci']);
                }
                //print_r($entry);
            }
        }

        $myentries = array();
        $myentries['clientid'] = $entries['clientid'];
        $myentries['data'] = $mydata;

        return($myentries);
    }

    // helper funciton. check wether te current page matches one of the project URL patterns
    // of a given project.
    // we have a match if
    // - at least one of the project Urls with mode=include matches
    // - none of the project Urls with mode=exclude matches
    private function pageUrlMatchesProjectUrls($url,$canonicalUrls) {
        $includeCount=0;
        $excludeCount=0;
        foreach($canonicalUrls as $projectUrlPattern) {
            if($this->containsPattern($url,$projectUrlPattern['url'])) {
                if($projectUrlPattern['mode'] == 'include')
                    $includeCount++;
                else
                    $excludeCount++;
            }
        }
        if(($includeCount > 0) && ($excludeCount==0))
            return true;
        else
            return false;
    }

    // helper function
    // $url: URL to check for pattern. Must be canonicalized
    // $pattern: URL-Pattern, must be canonicalized
    // return true is $pattern is a substring of $url

    function containsPattern($url, $pattern) {
        $pattern = trim($pattern);
        if (substr($pattern, 0, 1) == '*')
            $startsWithWildcard = true;
        else
            $startsWithWildcard = false;
        if (substr($pattern, strlen($pattern) - 1, 1) == '*')
            $endsWithWildcard = true;
        else
            $endsWithWildcard = false;
        $url = trim($url);
        // normalize pattern and url with respect to trailing /
        $patternEndsWithSlashAsterisk = (strrpos($pattern,"/*") + strlen("/*") === strlen($pattern));
            // when the pattern ends with "/*" we should not remove the trailing slash from the URL
        if(!$patternEndsWithSlashAsterisk) {
            $url = rtrim($url, "/");
            $url = str_replace("/?", "?", $url);
        }
        $pattern = rtrim($pattern, "/");
        $pattern = str_replace("/?", "?", $pattern);

/*
echo "patternEndsWithSlashAsterisk:$patternEndsWithSlashAsterisk<br>";
echo "startsWithWildcard:$startsWithWildcard<br>";
echo "endsWithWildcard:$endsWithWildcard<br>";
echo "url:$url<br>";
echo "pattern:$pattern<br>";
*/

        $match = true;
        $pl = strlen($pattern) - 1;
        $ul = strlen($url);
        // if pattern starts with ! treat it as a regular expression
        if (substr($pattern, 0, 1) == '!') {
            // left ! indicates that pattern shall be treated as regex
            $mypattern = substr($pattern, 1, $pl);
            if (preg_match($mypattern, $url) == 1) {
                $match = true;
            } else {
                $match = false;
            }
        }
        // if no regex, then use wildcard matching
        else {
            // if pattern contains no ? then match against the URL without the querystring
            //if((strpos($pattern, "?") === false) && !($startsWithWildcard || $endsWithWildcard)) {
            if ((strpos($pattern, "?") === false) && !$endsWithWildcard) {
                // remove querystring if present
                $parts = explode('?', $url);
                $url = $parts[0];
                //$pattern = rtrim($pattern,"/");
            }
            // if pattern contains no protocol, then match against the URL without protocol
            if (strpos($pattern, "http") === false) {
                // remove protocol if present
                $url = str_replace("http://", "", $url);
                $url = str_replace("https://", "", $url);
            }

            // escape meta charcaters of shell wildcard syntax
            $pattern = str_replace("[", "\[", $pattern);
            $pattern = str_replace("]", "\]", $pattern);
            $pattern = str_replace("{", "\{", $pattern);
            $pattern = str_replace("}", "\}", $pattern);
            $pattern = str_replace("?", "\?", $pattern);
            $match = fnmatch($pattern, $url);
        }
        return $match;
    }

    /*
     * check status of this client and return array with several status informations 
     * In case client is not active, has no tests -> return 0, else 1
     */

    function getclientstatus($clienthash,$reload=false) {
        // try to get cached data from APC
        $key = "cs_" . $clienthash;
        $entries = getValueFromCache($key);
        $recalculate = ((!$entries) || ($reload));
        if($recalculate) {
            lazyLoadDB();
            // get number of active tests
            $sql = "select count(*) as cnt
					from landingpage_collection lc, client c
					where lc.clientid = c.clientid
					and c.clientid_hash = '$clienthash'
					and lc.status = 2";
            $activetests = 0;
            $query = $this->db->query($sql);
            if($query->num_rows() > 0) {
                $results = $query->result_array();
                $activetests = $results[0]['cnt'];
            }
            // get status and subid of client
            $sql = "select subid,status,quota,used_quota,ip_blacklist,account_key2,clientid,config
					from client
					where clientid_hash = '$clienthash' limit 1";
            $status = -1;
            $subid = 'NA';
            $quota = -1;
            $used_quota = -1;
            $query = $this->db->query($sql);
            if($query->num_rows() > 0) {
                $results = $query->result_array();
                $row = $results[0];
                $status = $row['status'];
                $subid = $row['subid'];
                $clientid = $row['clientid'];
                $quota = $row['quota'];
                $used_quota = $row['used_quota'];
                $ip_blacklist = $row['ip_blacklist'];
                $account_key2 = $row['account_key2'];
                $config = $row['config'];
                $this->load->library('dbconfiguration');
                $accountconfig = $this->dbconfiguration->createAccountConfigurationFromString($clientid,$config);
            }
            $entries = array();
            $entries['activetests'] = $activetests;
            $entries['status'] = $status;
            $entries['subid'] = $subid;
            $entries['clientid'] = $clientid;
            $entries['quota'] = $quota;
            $entries['used_quota'] = $used_quota;
            $entries['ip_blacklist'] = $ip_blacklist;
            $entries['account_key2'] = $account_key2;
            $entries['config'] = $accountconfig;
            storeValueInCache($key,$entries);                        
        }
        return $entries;
    }

    // for diagnose mode: check wether there are active tests for the client
    function hasActiveLandingpageCollections($clienthash) {
        $sql = "select count(*) as cnt
				from landingpage_collection lc, client c
				where lc.clientid = c.clientid
				and c.clientid_hash = '$clienthash'
				and lc.status = 2";
        $count = 0;
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $count = $results[0]['cnt'];
        }
        if ($count > 0)
            return true;
        else
            return false;
    }

    function getClient($clienthash) {
        lazyLoadDB();
        $sql = "select * from client where clientid_hash='$clienthash' limit 1";
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $row = $results[0];
            return $row;
        }
        else {
            return false;
        }
    }

    /*
     *  check status of collection and client + additional information for subsequent queries
     *  select by external Code of the collection
     */

    function getcollectionstatus($collectioncode,$reload=false) {
//dblog_info("getcollectionstatus $collectioncode,$reload");
        $key = "cc_" . $collectioncode;
        $entries = getValueFromCache($key);
        $recalculate = ((!$entries) || ($reload));
        if($recalculate) {
            $entries = array();
            lazyLoadDB();
            $sql = "SELECT lc.status AS ls,c.status AS cs,c.clientid AS cid,c.userplan AS up,lc.landingpage_collectionid AS lid,
				lc.optimization_mode AS om, lc.progress AS pr, lc.testtype AS tt,lc.sample_time AS st, 
				(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(lc.last_sample_date)) AS dt, lp.lp_url AS u, lc.tracked_goals AS tg, 
				lc.referrer_regex AS rr,lc.name AS nm,lc.allocation AS alc,lc.ignore_ip_blacklist AS ibl,
				lc.deferred_impressions AS di, lc.personalization_mode AS pm, lc.smartmessage as sms, lc.start_date as sdt, 
                lc.end_date as edt, lc.code as lpcd, lc.autopilot as ap, lc.config as cfg, lc.restart_date as rsd
				FROM landingpage_collection lc, client c, landing_page lp 
				where c.clientid = lc.clientid
				and lc.code='$collectioncode' 
				and lc.landingpage_collectionid = lp.landingpage_collectionid
				AND lp.pagetype = 1
				LIMIT 1";
            $query = $this->db->query($sql);
            if($query->num_rows() > 0) {
                $results = $query->result_array();
                $entries = $results[0];
                $collectionid = $entries['lid'];
            	$configstring = $entries['cfg'];
            	$this->load->library('dbconfiguration');
            	$projectconfig = $this->dbconfiguration->createProjectConfigurationFromString($collectionid,$configstring);
            	$entries['cfg'] = $projectconfig;
            // extract configuration values from serialized configuration string
            }
            else {
                // if the collection does not exist, try to delete the cache-key and exit
                apch_delete($key);
                return (false);
            }

            // add landingpage information to the resultset. control is always the first. Only select 
            // landing_pages that are not refrenced by pagegroups of teasertests
            $sql = "select lp_url,pagetype,landing_page.landing_pageid,status,dom_modification_code,landing_page.name,phpcode,
                rule.name as rname,rotation_slot_begin,rotation_slot_end, smart_message.smart_message_id as sms_id, 
                allocation, is_maximum 
                from landing_page 
                left join rule on landing_page.rule_id = rule.rule_id
                left join smart_message on landing_page.landing_pageid = smart_message.landing_pageid
                where landing_page.landingpage_collectionid=$collectionid 
                and page_groupid=-1
                and (pagetype = 1 or pagetype = 2) 
                order by pagetype ASC";
            $lpages = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $pageid = $row['landing_pageid'];
                $row['dom_modification_code'] = $this->prepareDomcodeForDelivery($row['dom_modification_code']);
                $lpages[$pageid] = $row;
            }
            $entries['pages'] = $lpages;

            // if this is a teasertest, add pagegroups and referenced landingpages to the resultset
            $sql = "select page_groupid, name, progress
                from page_group
                where status=2
                and landingpage_collectionid=$collectionid";
            $groups = array();
            $query = $this->db->query($sql);
            foreach ($query->result_array() as $row) {
                $group = $row;
                $groupid = $row['page_groupid'];
                $sql = "select * from landing_page where page_groupid=$groupid order by pagetype asc";
                $lpages = array();
                $query2 = $this->db->query($sql);
                foreach ($query2->result_array() as $row_groups) {
                    $page = array(
                        'landing_pageid' => $row_groups['landing_pageid'],
                        'name' => $row_groups['name'],
                        'pagetype' => $row_groups['pagetype'],
                        'dom_code' => $row_groups['dom_modification_code'],
                        'allocation' => $row_groups['allocation'],
                        'is_maximum' => $row_groups['is_maximum'],
                        'rotation_slot_begin' => $row_groups['rotation_slot_begin'],
                        'rotation_slot_end' => $row_groups['rotation_slot_end'],
                    );
                    $lpages[$row_groups['landing_pageid']] = $page;
                    if($row_groups['pagetype'] == OPT_PAGETYPE_CTRL) {
                        $controlid = $row_groups['landing_pageid'];
                    }
                }
                $group['pages'] = $lpages;
                $group['controlid'] = $controlid;
                $groups[$groupid] = $group;
            }
            $entries['groups'] = $groups;
            storeValueInCache($key,$entries);             
        }

        $r = array();
        $r["collectionstatus"] = $entries['ls'];
        $r["clientstatus"] = $entries['cs'];
        $r["clientid"] = $entries['cid'];
        $r["userplan"] = $entries['up'];
        $r["collectionid"] = $entries['lid'];
        $r["optimizationmode"] = $entries['om'];
        $r["progress"] = $entries['pr'];
        $r["testtype"] = $entries['tt']; //1 - A/B, 2 - MVT
        $r["sample_time"] = $entries['st'];
        $r["diff_time"] = $entries['dt'];
        if($r["collectionstatus"] == OPT_PAGESTATUS_ACTIVE) 
            $r["sample_time"] += $r["diff_time"];
        $r["lp_url"] = $entries['u'];
        $r["tracked_goals"] = $entries['tg'];
        $r["referrer_regex"] = $entries['rr'];
        $r["collectionname"] = $entries['nm'];
        $r["allocation"] = $entries['alc'];
        $r["ignore_ip_blacklist"] = $entries['ibl'];
        $r["deferred_impressions"] = $entries['di'];
        $r["personalization_mode"] = $entries['pm'];
        $r["smartmessage"] = $entries['sms'];
        $r["start_date"] = $entries['sdt'];
        $r["end_date"] = $entries['edt'];
        $r["collectioncode"] = $entries['lpcd'];
        $r["code"] = $entries['lpcd'];
        $r["autopilot"] = $entries['ap'];
        $r["restart_date"] = $entries['rsd'];
        $r["landingpages"] = $entries['pages'];
        $r["config"] = $entries['cfg'];
        $r["page_groups"] = $entries['groups'];
        return $r;
    }
    
    /*
     * Retrieves all perso conditions which a given visitor has converted for
     * @return an array with IDs of all goals
     */
    function getVisitorRuleConditionHistory($visitorid,$clientid) {
        // try to get cached data from APC
        $key = "rcs_" . $visitorid . "_" . $clientid;
        $entries = getValueFromCache($key);
        if(!$entries) {
            lazyLoadDB();
            $sql = "select rule_condition_id from visitor_condition_history 
                where visitorid = $visitorid
                and clientid = $clientid
                and status = 1";
            $query = $this->db->query($sql);
            $entries = array();
            foreach ($query->result_array() as $row) {
                $entries[] = $row['rule_condition_id'];
            }
            storeValueInCache($key,$entries);                        
        }
        return $entries;
    }

    /*
     * Provides information wether a certain visitor has reached a personalization
     * goal specified by the rule condition id 
     * @return 1=visitor has reached goal. 0=visitor has not yet reached goal.
     */

    function getVisitorRuleConditionStatus($visitorid,$conditionid,$clientid) {
        $entries = $this->getVisitorRuleConditionHistory($visitorid,$clientid);
        if(in_array($conditionid, $entries)) 
            return 1;
        else
            return 0;
    }

    /*
     * update the visitor perso goal history
     */
    function updateVisitorRuleConditionHistory($visitorid,$conditionid,$clientid) {
        lazyLoadDB();
        $sql = "replace into visitor_condition_history
            set rule_condition_id=$conditionid,status=1,visitorid=$visitorid,clientid=$clientid";
        $query = $this->db->query($sql);
        // flush the cache    
        $key = "rcs_" . $visitorid . "_" . $clientid;
        apch_delete($key);      
    }

    /***********************************************************************************/
    //      Functions for Teaser Tests
    /***********************************************************************************/
    /*
     * Get information for visitor and collection for teaser tests: which pages shall be delivered for which page group? 
     */
    public function getTeaserTestDeliveryPlan($visitorid,$collectionid) {
        // try to get cached data from APC
        $key = "ttdp_" . $visitorid . "_" . $collectionid;
        $entries = getValueFromCache($key);
        if(!$entries) {
            $sql = "select delivery_plan,date 
                from delivery_plan
                where visitorid=$visitorid
                and landingpage_collectionid=$collectionid
                order by delivery_planid desc
                limit 1";
            $deliveryPlan = array();
            $deliveryPlanDate = "";
            $this->load->library('multidb');
            $CLIENT_DB = $this->multidb->getClientDb();
            $query = $CLIENT_DB->query($sql);
            if($query->num_rows() > 0) {
                $results = $query->result_array();
                $row = $results[0];
                $deliveryPlan = unserialize($row['delivery_plan']);
                $deliveryPlanDate = $row['date'];
                if(!is_array($deliveryPlan)) {
                    $deliveryPlan = array();
                }
            }
            $entries = array($deliveryPlan,$deliveryPlanDate);
            storeValueInCache($key,$entries);                        
        }
        // in case entry is older than 30 days, count quota
        $deliveryPlan = $entries[0];
        $deliveryPlanDate = $entries[1];
        if(sizeof($deliveryPlan) > 0) {
            $now = time(); 
            $ddate = strtotime($deliveryPlanDate);
            $datediff = $now - $ddate;
            $diffdays = floor($datediff/(60*60*24));  
            if($diffdays > 30) {
                // set new date
                $now = date('Y-m-d H:i:s');
                $sql = "update delivery_plan
                    set date='$now'
                    where visitorid=$visitorid 
                    and landingpage_collectionid=$collectionid";
                $this->load->library('multidb');
                $CLIENT_DB = $this->multidb->getClientDb();
                $CLIENT_DB->query($sql);
                // store in cache
                $entries = array($deliveryPlan,$now);
                storeValueInCache($key,$entries);                        
                // increment quota
                $this->incrementClientQuotaByCollectionid($collectionid);
            }        
        }
        return $entries;
    }

    /*
     * make A/B-selection of a page in a group for a visitor 
     */
    public function selectNewPageForGroup($clientid,$visitorid,$collectionid,$groupid,$pages) {
        // select a random page from the group's pages
        $resultpage = $this->getRandomPage($pages);
        return $resultpage['landing_pageid']; 
    }

    /*
     * Save updated plan data in cache
     */
    public function refreshTeaserTestDeliveryPlan($plan,$visitorid,$collectionid,$clientid) {
        // save the result in request_events
        $this->load->library('multidb');
        $CLIENT_DB = $this->multidb->getClientDb();
        $currentPlanArray = $this->getTeaserTestDeliveryPlan($visitorid,$collectionid);
        $currentPlan = $currentPlanArray[0];
        if(sizeof($currentPlan) == 0) { // create a new entry in request_events and delivery_plan
            $this->createRequestEvent(
                OPT_EVENT_TT_VISITOR, // delivery plan 
                null, // goalid
                0, // impressions
                0, // conversions
                0, // conversionvalue
                0, // conversionvalueAggregation
                $visitorid,
                $collectionid,
                $clientid,
                -1, // no pageid needed
                -1);
            $data = array(
                'landingpage_collectionid' => $collectionid,
                'visitorid' => $visitorid,
                'date' => date('Y-m-d H:i:s'),
                'delivery_plan' => serialize($plan)
            );
            $CLIENT_DB->insert('delivery_plan', $data);
            $this->incrementClientQuota($clientid);
        }
        else {
            $sql = "update delivery_plan
                set delivery_plan='" . serialize($plan) . "'
                where visitorid=$visitorid and landingpage_collectionid=$collectionid";
            $CLIENT_DB->query($sql);
        }
        $key = "ttdp_" . $visitorid . "_" . $collectionid;
        $value = array($plan,date('Y-m-d H:i:s'));
        storeValueInCache($key,$value);
    }

    /***********************************************************************************/
    /***********************************************************************************/

    /*
     * get landing page preview data
     */
    function getPreviewData($lpid, $cc) {
        lazyLoadDB();
        $sql = "select lc.testtype,lp.lp_url,lp.dom_modification_code from landing_page lp, landingpage_collection lc, client c
            where lp.landing_pageid = $lpid
            and lc.landingpage_collectionid = lp.landingpage_collectionid
            and lc.clientid = c.clientid
            and c.clientid_hash = '$cc'
            limit 1";
        $sqlCheck = mysql_query($sql);
        trackMysqlError(__function__);
        $resultCheck = mysql_fetch_row($sqlCheck);

        $r = array();
        $r["testtype"] = $resultCheck[0];
        $r["lp_url"] = $resultCheck[1];
        $r["dom_code"] = $this->prepareDomcodeForDelivery($resultCheck[2]);

        return $r;
    }

    /*
     *  check status of collection and client + additional information for subsequent queries
     *  select by internal ID of collection
     */

    function getcollectionstatusById($collectionid) {
        $entries = $this->getcollectionIdForCode();
        return $this->getcollectionstatus($entries[$collectionid]);
    }

    /*
     *  retrieve a mapping of colectioncodes vs. collectionid
     */

    function getcollectionIdForCode($reload=false) {
        // get the mapping of landingpage_collection.landingpage_collectionid to landingpage_collection.code
        $key = "code_2_id_map";
        $entries = getValueFromCache($key);
        $recalculate = ((!$entries) || ($reload));
        if($recalculate) {
            lazyLoadDB();
            $entries = array();
            $sql = "select landingpage_collectionid,code from landingpage_collection
                order by landingpage_collectionid desc";
            $query = $this->db->query($sql);    
            foreach ($query->result_array() as $row) {
                $id = $row['landingpage_collectionid'];
                $code = $row['code'];
                $entries[$id] = $code;
            }
            storeValueInCache($key,$entries);                        
        }
        return $entries;
    }

    /*
     * get all action goals for a client in two blocks:
     * - conversion goals
     * - personalization goals
     *
     * For conversion goals:
     * only return goals whch are active, that belong to a collection which is running, and to a client which is active
     * only return goals that belong to a webservice request that does not indicate a page impression, because target page goals
     * are taken care of in getMatchingLandingPagesForClient()
     *
     * For personalization goals:
     * return all goals for the given client. Currently only goal "insert into basket" is relevant here
     *
     * return array contains a list of goals with the following values each:
     * - type
     * - arg1 (NA is not defined)
     */

    function getClientActionGoals($clientid,$collectionids=false,$reload=false) {
        $key = "ag_" . $clientid;
        $entries = getValueFromCache($key);
        $recalculate = ((!$entries) || ($reload));
        if($recalculate) {
            lazyLoadDB();
            $sql = "select cg.collection_goal_id,cg.type,cg.arg1,cg.level,lc.code,cg.landingpage_collectionid,
                cg.page_groupid
				from collection_goals cg,landingpage_collection lc,client c
				where cg.status=1
				and cg.type != " . GOAL_TYPE_TARGETPAGE . "
				and cg.landingpage_collectionid=lc.landingpage_collectionid
				and lc.status=2
				and lc.clientid=c.clientid
				and c.clientid=$clientid
				and (c.status=1 or c.status=6)";
            $conversiongoals = array();
            $query = $this->db->query($sql);    
            foreach ($query->result_array() as $row) {
                $conversiongoals[] = $row;
            }

            $sql = "select rc.rule_condition_id,rc.type,rc.arg
                from rule_condition rc, rule r
                where rc.rule_id = r.rule_id
                and type='insert_basket'
                and r.clientid = $clientid";
            $persogoals = array();
            $query = $this->db->query($sql);    
            foreach ($query->result_array() as $row) {
                $persogoals[] = $row;
            }

            $entries = array(
                'conversion_goals' => $conversiongoals,
                'perso_goals' => $persogoals
            );
            storeValueInCache($key,$entries);                                }

        if($collectionids) {
            $cgoals = $entries['conversion_goals'];
            $cgoalsForProject = array();
            foreach($cgoals as $goal) {
                if(in_array($goal['landingpage_collectionid'], $collectionids)) {
                    $cgoalsForProject[] = $goal;
                }
            }
            $entries['conversion_goals'] = $cgoalsForProject;
        }

        return $entries;
    }

    /*
     * Get an array with all impressions, conversions and CRs of a collection or a group in a collection.
     * The CRs are calculated on the fly (since they are not stored anywhere for secondary goals), for
     * Combined goals a helper function is used to derive the combined value from the set of conversions.
     * Structure of the result array:
     *      <pageid>                ID of the page
     *          landing_pageid:     pageid again
     *          pagetype:           1|2 meaning control|variant
     *          impressions:        impressions on the page
     *          goals:              array
     *              <goalid>
     *                  goalid:     goalid again
     *                  type:       goal-type (e.g. 15 is time-on-page)
     *                  level:      0|1 meaning secondary | primary
     *                  z_score:    z-score
     *                  is_maximum: 0|1|2 indicating result of this variant
     *                  conversions:            Conversions for the goal
     *                  aggregated_value:       aggregated conversion value
     *                  aggregated_square_value:Intermediate value for standard-deviation
     *                  standard_deviation:     Standard deviation of the CR
     *                  cr:                     Conversion rate   
     */
    public function getProjectKPI($collectionid,$groupid,$combinationRule = false,$reload=false) {
        lazyLoadDB();
        if(!isset($collectionid))
            return false;
        if(!isset($groupid))
            return false;
        $key = "pkpi_" . $collectionid . "_" . $groupid;
        $transformedResultset = getValueFromCache($key);
        $recalculate = ((!$transformedResultset) || ($reload));
        if($recalculate) {
            $sql = "select lp.landing_pageid, lp.pagetype, lp.impressions, lp.conversions as lp_conv, lp.allocation,
                lp.conversion_value_aggregation as lp_cva, lp.conversion_value_square_aggregation as lp_cvsqa, lp.standard_deviation as lp_stddev,
                lp.z_score, lp.is_maximum, cgc.id as cgc_id, cgc.conversions as cgc_conv, 
                cgc.conversion_value_aggregation as cgc_cva, cgc.conversion_value_square_aggregation as cgc_cvsqa,  cgc.standard_deviation as cgc_stddev,
                cg.collection_goal_id, cg.type as cg_type, cg.level
                from landing_page lp, landingpage_collection lpc, collection_goal_conversions cgc, collection_goals cg
                where lp.landingpage_collectionid=lpc.landingpage_collectionid
                and cgc.landing_pageid=lp.landing_pageid
                and cgc.goal_id=cg.collection_goal_id
                and lp.landingpage_collectionid=$collectionid
                and lp.page_groupid=$groupid";
            $query = $this->db->query($sql);
            $flatResult = array();
            foreach ($query->result_array() as $row) {
                $flatResult[] = $row;
            }
            $transformedResultset = $this->transformProjectKPIResultset($flatResult);
            $transformedResultset = $this->calculateKpiResultsetConversionrates($transformedResultset,$combinationRule);
            storeValueInCache($key,$transformedResultset);                        
        }
        return ($transformedResultset);
    }

    /*
     * Helper function (public so it can be tested)
     * Transforms a flat resultset as retrieved in getProjectKPI
     */
    public function transformProjectKPIResultset($resultset) {
        $pages = array();
        foreach($resultset as $row) {
            $pageid = $row['landing_pageid'];
            $goalid = $row['collection_goal_id'];
            $pages[$pageid]['landing_pageid'] = $pageid;
            $pages[$pageid]['pagetype'] = $row['pagetype'];
            $pages[$pageid]['impressions'] = $row['impressions'];
            $pages[$pageid]['allocation'] = $row['allocation'];
            $pages[$pageid]['goals'][$goalid]['goalid'] = $row['collection_goal_id'];
            $pages[$pageid]['goals'][$goalid]['type'] = $row['cg_type'];
            $pages[$pageid]['goals'][$goalid]['level'] = $row['level'];
            $pages[$pageid]['goals'][$goalid]['is_maximum'] = $row['is_maximum'];
            if($row['level'] == 1) { // primary goal
                $pages[$pageid]['goals'][$goalid]['z_score'] = $row['z_score'];
                $pages[$pageid]['goals'][$goalid]['conversions'] = $row['lp_conv'];
                $pages[$pageid]['goals'][$goalid]['aggregated_value'] = $row['lp_cva'];
                $pages[$pageid]['goals'][$goalid]['aggregated_square_value'] = $row['lp_cvsqa'];
                $pages[$pageid]['goals'][$goalid]['standard_deviation'] = $row['lp_stddev'];
            }
            else {
                $pages[$pageid]['goals'][$goalid]['conversions'] = $row['cgc_conv'];
                $pages[$pageid]['goals'][$goalid]['aggregated_value'] = $row['cgc_cva'];                
                $pages[$pageid]['goals'][$goalid]['aggregated_square_value'] = $row['cgc_cvsqa'];
                $pages[$pageid]['goals'][$goalid]['standard_deviation'] = $row['cgc_stddev'];
            }
            $pages[$pageid]['goals'][$goalid]['cr'] = ($row['impressions']==0) ? 0 : ($pages[$pageid]['goals'][$goalid]['aggregated_value'] / $row['impressions']);
        }
        return ($pages);
    }

    /* 
     * Calculate conversion rates for a given kpiResultset (public so it can be tested)
     */
    public function calculateKpiResultsetConversionrates($resultset,$combinationRule=false) {
        foreach($resultset as $page) {
            foreach($page['goals'] as $goal) {
                if($goal['type'] != GOAL_TYPE_COMBINED) {                   
                    $cr = Calculation::getConversionRate($page['impressions'],$goal['conversions'],$goal['aggregated_value'],$goal['type']);
                    $resultset[$page['landing_pageid']]['goals'][$goal['goalid']]['cr'] = $cr;
                    // for goals with bernoulli-distribution (almost all), calculate the standard dev.
                    if(($goal['type'] != GOAL_TYPE_TIMEONPAGE) && ($goal['type'] != GOAL_TYPE_PI_LIFT)) {
                        $stddev = sqrt($cr * (1-$cr));
                        $resultset[$page['landing_pageid']]['goals'][$goal['goalid']]['standard_deviation'] = $stddev;
                    }
                }
            }
        }

        if(!$combinationRule)
            $combinationRule = $this->config->item('COMBINED_GOAL_COMBINATION_RULE'); // we use a default for the moment
        $combinedGoalResultset = $this->deriveCombinedGoalConversions($resultset,$combinationRule);
        if(!$combinedGoalResultset)
            return ($resultset);
        else
            return ($combinedGoalResultset);
    }

    // reset the is_maximum values in kpiResultset. This is necessary in some situations to force
    // the system re-evaluate the results and eventually update conversionslots etc.
    public function resetDecisionresultsInKpiResultset($collectionid,$page_groupid) {
        $key = "pkpi_" . $collectionid . "_" . $page_groupid;
        $kpiResult = $this->getProjectKPI($collectionid,$page_groupid);
        foreach($kpiResult as $page) {
            foreach($page['goals'] as $goal) {
                if($goal['level'] == 1) { // primary goals                   
                    $kpiResult[$page['landing_pageid']]['goals'][$goal['goalid']]['is_maximum'] = 0;
                }
            }
        }
        storeValueInCache($key,$kpiResult); 
    }

    /*
     * Derive conversion rate of a combined goal from the conversion rates in a KPI resultset as 
     * returned from transformProjectKPIResultset.
     * An array with a transformation rule is used as input:
     * - array of entries with key=goal-type-ID and value=weight (value between 0 and 1)
     * - the algorithm checks wich of these goals are available in the set and normalizes the weights accordingly
     * $resultset can be as used in getKpiResultset, or a subset. The following attributes are needed:
     *      <pageid>                ID of the page
     *          impressions:        impressions on the page
     *          goals:              array
     *              <goalid>
     *                  type:       goal-type (e.g. 15 is time-on-page)
     *                  standard_deviation:     Standard deviation of the CR
     *                  cr:                     Conversion rate   
     *
     * return the updated resultset, or false if no combined goal is contained and resultset is unchanged
     */

    public function deriveCombinedGoalConversions($resultset,$transformationRule) {
        // check wether a combined goal is contained at all, and get it's ID
        // plus get the IDs of the goals specified in the transformaitonRule
        $page = reset($resultset);

        $combinedGoalId = false;
        $goalWeights = array();
        foreach($page['goals'] as $goal) {
            if(in_array($goal['type'], array_keys($transformationRule))) {
                $goalid = $goal['goalid'];
                $goalWeights[$goal['goalid']] = array(
                    'type' => $goal['type'],
                    'weight' => $transformationRule[$goal['type']]
                    );
            }
            if($goal['type']==GOAL_TYPE_COMBINED)
                $combinedGoalId = $goal['goalid'];
        }
        if(!$combinedGoalId)
            return false;

        // normalize the weights to a sum of 1
        $sum = 0;
        foreach($goalWeights as $weight)
            $sum += $weight['weight'];
        $goalWeightkeys = array_keys($goalWeights);
        foreach($goalWeightkeys as $key) {
            $type = $goalWeights[$key]['type'];
            $transformationRule[$type] = $transformationRule[$type] / $sum;
            // get the maximum value of the goal converison over all pages
            $maximumValue = 0;
            foreach ($resultset as $page) {
                if ($page['goals'][$key]['cr'] > $maximumValue) {
                    $maximumValue = $page['goals'][$key]['cr'];
                }
            }
            // normalize and scale weights
            if($maximumValue==0)
                $goalWeights[$key]['weight'] = 0;
            else
                $goalWeights[$key]['weight'] = $goalWeights[$key]['weight'] / ($sum * $maximumValue);
        }
        // calculate conversion rate, conversions, agregated value and standard deviation for combined goals
        foreach($resultset as $page) {
            $combinedCr = 0;
            $stdErrCombinedSum = 0;
            $numCombinedConversions = 0; // use minimum of conversion number of all included goals
            
            foreach($goalWeightkeys as $key) {
                $auxCr = $page['goals'][$key]['cr'];
                $goaltype = $page['goals'][$key]['type'];
                $combinedCr += $auxCr * $goalWeights[$key]['weight'];
                // standard deviation for combined goal is root of sum of squares of stddev of single goals
                $stddev = $page['goals'][$key]['standard_deviation'];
                if(($goaltype == GOAL_TYPE_TIMEONPAGE) || ($goaltype == GOAL_TYPE_PI_LIFT)) {
                    $stdErr = $stddev / $auxCr;
                } else {
                    $stdErr = $stddev / ($auxCr * sqrt($page['impressions'])) ;
                }
                $stdErrCombinedSum += pow($stdErr * $transformationRule[$goaltype],2);
                if($numCombinedConversions == 0) {
                    $numCombinedConversions = $page['goals'][$key]['conversions'];
                }
                if($page['goals'][$key]['conversions'] < $numCombinedConversions) {
                    $numCombinedConversions = $page['goals'][$key]['conversions'];
                }        
            }
            $resultset[$page['landing_pageid']]['goals'][$combinedGoalId]['cr'] = $combinedCr;
            $resultset[$page['landing_pageid']]['goals'][$combinedGoalId]['conversions'] = $numCombinedConversions;
            $resultset[$page['landing_pageid']]['goals'][$combinedGoalId]['standard_deviation'] = 
                sqrt($stdErrCombinedSum) * $combinedCr;
        }
        return $resultset;
    }

    /*
     * Helper function - get the ID of the primary Goal in a kpiResultset
     */
    private function getPrimaryGoalIdFromKpiResultset($resultset) {
        foreach($resultset as $page) {
            foreach($page['goals'] as $goal) {
                if($goal['level'] == 1)
                    return($goal['goalid']);
            }
        }
    }

    /*
     *  function for insert visitorid 
     */

    function insertvisitorid() {
        $this->load->library('multidb');
        $CLIENT_DB = $this->multidb->getClientDb();
        $data = array('pagecode' => 999);
        $CLIENT_DB->insert('visitor', $data);
        return $CLIENT_DB->insert_id();
    }
    
    function getRequestEvents($visitorid, $collectionid, $restartDate) {
        $key = $visitorid . "_" . $collectionid;
        $entries = getValueFromCache($key);
        if(!$entries) {
            $this->load->library('multidb');
            $CLIENT_DB = $this->multidb->getClientDb();
            $CLIENT_DB->select('re.landing_pageid as pageid,re.type, re.visitor,re.landingpage_collectionid as lpid,
                    re.request_eventsid as rid, re.goal_id as gid, re.conversion_value as cv,re.clientid as cid,
                    re.page_groupid as pgid, re.visitor as vid, re.conversion_value_aggregation as cvag')
                    ->from('request_events re')
                    ->where('re.date >= \'' . $restartDate . '\'')
                    ->where('re.landingpage_collectionid',$collectionid)
                    ->where('re.visitor',$visitorid)
                    ->order_by('rid','desc'); 
            $query = $CLIENT_DB->get();
            $entries = array();
            foreach ($query->result() as $res) {
                $entries[] = array(
                    $res->pageid,
                    $res->type,
                    $res->lpid,
                    $res->rid,
                    $res->gid,
                    $res->cv,
                    $res->cid,
                    $res->pgid,
                    $res->vid,
                    $res->cvag
                );
            }
            storeValueInCache($key,$entries);                        
        }
        return $entries;
    }

    /*
     * Create an entry in request_events
     */
    private function createRequestEvent($type,$goalid,$impressions, $conversions, $conversionValue, 
            $conversionValueAggregation, $visitorid,
        $collectionid,$clientid,$pageid,$groupid=-1) {
        $insertData = array(
            'date' => date('Y-m-d H:i:s'),
            'type' => $type, 
            'goal_id' => $goalid,
            'impressions' => $impressions,
            'conversions' => $conversions,
            'conversion_value' => $conversionValue,
            'conversion_value_aggregation' => $conversionValueAggregation,
            'visitor' => $visitorid,
            'landingpage_collectionid' => $collectionid,
            'clientid' => $clientid,
            'landing_pageid' => $pageid,
            'page_groupid' => $groupid
        );
        $this->load->library('multidb');
        $CLIENT_DB = $this->multidb->getClientDb();
        $CLIENT_DB->insert('request_events', $insertData);
        return $CLIENT_DB->insert_id();;
    }

    /*
     * Update an existing request_event
     */
    private function updateRequestEvent($id,$data) {
        $this->load->library('multidb');
        $CLIENT_DB = $this->multidb->getClientDb();
        $CLIENT_DB->where('request_eventsid', $id);
        $CLIENT_DB->update('request_events', $data);
    }

    /*
     * retrieve latest conversion-type request_event for a given landing_page and goal
     */
    private function getLatestConversionEvent($pageid, $goalid) {
        $key = "re_latest_" . $pageid . "_" . $goalid;
        $entries = getValueFromCache($key);
        if(!$entries) {
            $this->load->library('multidb');
            $CLIENT_DB = $this->multidb->getClientDb();
            $CLIENT_DB->select('re.landing_pageid as pageid,re.type, re.landingpage_collectionid as lpid,
                    re.request_eventsid as rid, re.goal_id as gid, re.conversion_value as cv,re.clientid as cid,
                    re.page_groupid as pgid, re.visitor as vid, re.conversion_value_aggregation as cvag')
                    ->from('request_events re')
                    ->where('re.type',OPT_EVENT_CONVERSION)
                    ->where('re.landing_pageid',$pageid)
                    ->where('re.goal_id',$goalid)
                    ->order_by('rid','desc')->limit(1); 
            $query = $CLIENT_DB->get();
            $entries = array();
            foreach ($query->result() as $res) {
                $entries[] = array(
                    $res->pageid,
                    $res->type,
                    $res->lpid,
                    $res->rid,
                    $res->gid,
                    $res->cv,
                    $res->cid,
                    $res->pgid,
                    $res->vid,
                    $res->cvag
                );
            }
            storeValueInCache($key,$entries);                        
        }
        if(is_array($entries))
            return $entries[0];
        else
            return false;        
    }

            
    /*
     * select a new page from the given collection to be displayed, using a random selection
     * return ID if new page
     * $matchingpageids: if -1, all pages from the test are fine. If not, then it is an array holding IDs of
     * pages that are OK. The slots need to be recalculated then.
     */

    function selectnewpage($collectionid,$visitor,$deferred_counting,$pages,$matchingpageids,$quota_count,$ipBlacklisted) {
    	if(($matchingpageids != -1) && (sizeof($matchingpageids) > 1)) {   // if an array of ids is provided, we need to normalize the remaining 
    						          // pages to 100% probabiliy
                                        // if only one ID is provided, always use this 

    		$prob = 0; // value of probaility that is covered by the remaining pages
    		foreach($matchingpageids as $pid) {
    			if($pages[$pid]['pagetype'] != 3) // we do not have "success pages" anymore....
   					$prob += $pages[$pid]['rotation_slot_end'] - $pages[$pid]['rotation_slot_begin'];
   			}
    		// now normalize the remaining pages
    		$offset = 0;
    		foreach($matchingpageids as $pid) {
    			$width = $pages[$pid]['rotation_slot_end'] - $pages[$pid]['rotation_slot_begin'];
    			//echo " width:$width ";
    			$normalized_width = $width / $prob;
    			$pages[$pid]['rotation_slot_begin'] = $offset;
    			$pages[$pid]['rotation_slot_end'] = $offset + $normalized_width;
    			$offset = $pages[$pid]['rotation_slot_end'];
   			}    		
    	}
    	
        $resultpage = $this->getRandomPage($pages,$matchingpageids);
    	$r = array();
        $r["landingpageid"] = $resultpage['landing_pageid'];
        $r["landingpagetype"] = $resultpage['pagetype'];
        $r["landingpageurl"] = $resultpage['lp_url'];
        $r["dom_code"] = $this->prepareDomcodeForDelivery($resultpage['dom_modification_code']);
        $r["rulename"] = $resultpage['rname'];
        $r["sms_id"] = $resultpage['sms_id'];
        $r["landingpagename"] = $resultpage['name'];
        if ($r["landingpagetype"] == OPT_PAGETYPE_CTRL) {
            $r["landingpagename"] = "Original";
        }

        // the impression shall be counted if
        // - deferred_counting=true (which measns the complete project has a deferred impression delivery method)
        // - the delivered variant is a smart message
        $is_deferred_counting = false;
        if($deferred_counting) {
            $is_deferred_counting = true;
        }
        if(isset($r["sms_id"])) {
            if($r["sms_id"] > 0)
                $is_deferred_counting = true;
        }
        if(!$ipBlacklisted) {
            $this->countImpression($r["landingpageid"],$collectionid,$visitor,$is_deferred_counting,$quota_count);            
        }
        return $r;
    }

    /*
     * Make a random selection from an array of pages, based on the rotation_slots
     */
    private function getRandomPage($pages,$matchingpageids=-1) {
        // ensure that always a result is returned
        if($matchingpageids != -1) {
            if(sizeof($matchingpageids) == 1) {
                return $pages[$matchingpageids[0]];
            }            
        }

        // get random umber between 0 and 1 and ensure that $slot is neither exactly 0 nor 1, which 
        // would lead to empty resultset
        $slot = mt_rand(0, 100000) / 100000;
        if ($slot == 0)
            $slot = 0.0001;
        if ($slot == 1)
            $slot = 0.9999;
        
        $resultpage = array();
        // iterate over the pages and get the matching. take into account if an array to filter has been provided or not.
        foreach($pages as $page) {
            if($page['rotation_slot_begin'] <= $slot) {
                if($page['rotation_slot_end'] > $slot) {
                    if($matchingpageids != -1) {
                        if(in_array($page['landing_pageid'],$matchingpageids)) {
                            $resultpage = $page;
                            break;
                        }
                    }
                    else {
                        $resultpage = $page;
                        break;
                    }
                }
            }
        }

        return $resultpage;        
    }
    
    /*
     * count an impression after it has been selected. In case it is a deferred impression,
     * do not count but store it in the request_events table to retain the kind of page the visitor has seen
     */
     function countImpression($pageid,$collectionid,$visitor,$deferred_counting,$quota_count,$groupid=-1) {
        lazyLoadDB();
     	$clientid = $visitor['clientid'];
        $clientid_hash = $visitor['clienthash'];
        // store impression and CR if no deferred impression tracking
        if(!$deferred_counting) {
            // get actual impressuins/conversions
            $kpiResultset = $this->getProjectKPI($collectionid,$groupid);

            $kpiResultset[$pageid]['impressions'] += 1;
            $kpiResultset = $this->calculateKpiResultsetConversionrates($kpiResultset);
            $key = "pkpi_" . $collectionid . "_" . $groupid;
            storeValueInCache($key,$kpiResultset);         

            $primaryGoalId = $this->getPrimaryGoalIdFromKpiResultset($kpiResultset);
            $cr = $kpiResultset[$pageid]['goals'][$primaryGoalId]['cr'];
            $impressions = $kpiResultset[$pageid]['impressions'];
        	$sql = "update landing_page set impressions=impressions+1,cr=$cr where landing_pageid=$pageid";
            $this->db->query($sql);    
            if(isset($quota_count)) {
            	if($quota_count) {
                    $this->incrementClientQuota($clientid);
            	}
            }
        }
        if(!$deferred_counting)
            $request_event_type = OPT_EVENT_IMPRESSION; // impression
        else {
            $request_event_type = OPT_EVENT_DEFERRED_IMPRESSION; // deferred impression
            dblog_debug('BTO/OPT/ selectnewpage(): impression is deferred, no counting');
        }
        // insert into request events 
        $this->createRequestEvent(
            $request_event_type, 
            null, // goalid
            $impressions,
            0, // conversions
            0, // conversionvalue
            0, // conversionvalueAggregation
            $visitor["visitorid"],
            $collectionid,
            $clientid,
            $pageid,
            $groupid
        );
    }

    /*
     * Increment quota for client
     */
    private function incrementClientQuota($clientid) {
        return; // deactivate counting quota

        $sql = "update client set used_quota=used_quota+1 where clientid=$clientid";
        $this->db->query($sql);    
        // checkif used_quota has exceeded quota
        $sql = "SELECT quota,used_quota from client where clientid=$clientid";
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $row = $results[0];
            $quota = $row['quota'];
            $used_quota = $row['used_quota'];
        }
        // if so, delete the cache so that the next time a request is made this will return an error
        if ($quota <= $used_quota) {
            $key = "cs_" . $clientid_hash;
            apch_delete($key); // cached in getclientstatus
        }                   
    }

    /* 
     * Increment quota for client for specifi collection given
     */
    private function incrementClientQuotaByCollectionid($collectionid) {
        return; // deactivate counting quota

        $sql = "SELECT clientid from landingpage_collection where
            landingpage_collectionid=$collectionid";
        $query = $this->db->query($sql);
        if($query->num_rows() > 0) {
            $results = $query->result_array();
            $row = $results[0];
            $clientid = $row['clientid'];
            $this->incrementClientQuota($clientid);
        }
    }
    
    /*
     *  store a conversion
     *  input : array with user information
     *  level : 1 = primary conversion. 0 = secondary conversion
     */

    function conversion($collectionid, $pageid, $goalid, $level, $visitor) {
        lazyLoadDB();
        // get actual impressuins/conversions
        $kpiResultset = $this->getProjectKPI($collectionid,-1);
        $kpiResultset[$pageid]['goals'][$goalid]['conversions'] += 1;
        $kpiResultset[$pageid]['goals'][$goalid]['aggregated_value'] += 1;
        $kpiResultset = $this->calculateKpiResultsetConversionrates($kpiResultset);
        $key = "pkpi_" . $collectionid . "_-1";
        storeValueInCache($key,$kpiResultset);    

        $impressions = $kpiResultset[$pageid]['impressions'];     
        $conversions = $kpiResultset[$pageid]['goals'][$goalid]['conversions'];     
        $conversionValueAggregation = $kpiResultset[$pageid]['goals'][$goalid]['aggregated_value'];     
        $cr = $kpiResultset[$pageid]['goals'][$goalid]['cr'];     
        $cvag = $kpiResultset[$pageid]['goals'][$goalid]['aggregated_value']; 
        $level = $kpiResultset[$pageid]['goals'][$goalid]['level']; 
        // for primary conversions, update conversion rate in landing_page
        if ($level == 1) {
        	$sql = "update landing_page set conversions=$conversions,
                conversion_value_aggregation=$cvag,
                cr=$cr where landing_pageid=$pageid";
        	$this->db->query($sql);
        } else { 
            // for secondary conversions, update conversion in collection_goal_conversions
            $sql = "update collection_goal_conversions set conversions=$conversions,
                    conversion_value_aggregation=$cvag
                where landingpage_collectionid=$collectionid
                and landing_pageid=$pageid
                and goal_id=$goalid";
            $this->db->query($sql);
        }
        // insert into request events 
        $this->createRequestEvent(
            OPT_EVENT_CONVERSION, // type=conversion
            $goalid,
            $impressions,
            $conversions,
            1, // conversionvalue
            $conversionValueAggregation,
            $visitor["visitorid"],
            $collectionid,
            $visitor["clientid"],
            $pageid
        );
    }

    /*
     *  store a conversion coming from the track controller
     *  since parameters come from the tracking request they must be validated
     *  the conversion value can be updated since this is used for measuring time etc.
     *  this function (other than conversion()) handles conversion values nstead of only binary converisons
     *  level : 1 = primary conversion. 0 = secondary conversion
     */

    function updatableConversion($clientid,$collectionid, $groupid, $pageid, $goalid, $visitorid,$currentVisitorConversionValue,$newVisitorConversionValue,$conversionRequestEventsId) {
        lazyLoadDB();
        // get actual impressuins/conversions
        $kpiResultset = $this->getProjectKPI($collectionid,$groupid);
        $level = $kpiResultset[$pageid]['goals'][$goalid]['level']; 
        $conversions = $kpiResultset[$pageid]['goals'][$goalid]['conversions']; 
        $cvag = $kpiResultset[$pageid]['goals'][$goalid]['aggregated_value']; 
        $cvsqag = $kpiResultset[$pageid]['goals'][$goalid]['aggregated_square_value']; 
        $impressions = $kpiResultset[$pageid]['impressions'];
        // add the difference of currentConversionValue to newConversionValue to the aggregation
        // same for square values
        // add a conversion only if this is a new conversion (if $conversionRequestEventsId is -1)
        $conversionValueIncrement = $newVisitorConversionValue - $currentVisitorConversionValue;
        $cvag += $conversionValueIncrement;
        $cvsqag = $cvsqag - 
            pow($currentVisitorConversionValue,2) +
            pow($newVisitorConversionValue,2);
        if($conversionRequestEventsId == -1) 
            $conversions += 1;

        $kpiResultset[$pageid]['goals'][$goalid]['conversions'] = $conversions;
        $kpiResultset[$pageid]['goals'][$goalid]['aggregated_value'] = $cvag;
        $kpiResultset[$pageid]['goals'][$goalid]['aggregated_square_value'] = $cvsqag;

        $kpiResultset = $this->calculateKpiResultsetConversionrates($kpiResultset);
        $key = "pkpi_" . $collectionid . "_" . $groupid;
        $cr = $kpiResultset[$pageid]['goals'][$goalid]['cr']; 

        // calculate standard deviations
        $goalType = $kpiResultset[$pageid]['goals'][$goalid]['type'];
        $stddev = Calculation::calcStdDev($cr, $cvag, $cvsqag , $conversions, $goalType);
        $kpiResultset[$pageid]['goals'][$goalid]['standard_deviation'] = $stddev;
        storeValueInCache($key,$kpiResultset);    
        // distinguish primary and secondary goals
        if($level == 1) { // primary
            $sql = "update landing_page set conversions=$conversions,
                conversion_value_aggregation=$cvag,
                conversion_value_square_aggregation=$cvsqag,
                standard_deviation=$stddev, 
                cr=$cr where landing_pageid=$pageid";
            $this->db->query($sql);
        }
        else { // secondary
            $sql = "update collection_goal_conversions set conversions=$conversions,
                conversion_value_aggregation=$cvag,
                conversion_value_square_aggregation=$cvsqag,
                standard_deviation=$stddev 
                where landingpage_collectionid=$collectionid
                and landing_pageid=$pageid
                and goal_id=$goalid";
            $this->db->query($sql);
        }
        // in case we do an update, retrieve the request_events entry for this conversion
        // take into account that there might be conversion fr other visitors and the same
        // goal/page which have been created after this one, so the update must touch 2 request_events:
        // the current one for updating conversion_value, and the newest one for updating conversion_value_aggregation.
        // This is not a completely clean solution - when being strict *all* conversion coming after this one must be 
        // updated with respect to  conversion_value_aggregation, but this would be too time consuming. After migrating to
        // ElasticSearch this will not be necessary anyways, because aggregations will be made on the fly and not being 
        // prepared during tracking itself.
        if($conversionRequestEventsId != -1) {
            $visitorRequestEvents = $this->getRequestEvents($visitorid,$collectionid,'2000-01-01 00:00:00');
            $i = 0;
            foreach($visitorRequestEvents as $vre) {
                if($vre['3']==$conversionRequestEventsId && $vre['5']!=$newVisitorConversionValue) {
                    $currentConversionValueAggregation = $vre['9'] + $conversionValueIncrement;
                    $data = array(
                        'conversion_value' => $newVisitorConversionValue,
                        'conversion_value_aggregation' => $currentConversionValueAggregation
                    );
                    // update in DB and in cache
                    $this->updateRequestEvent($conversionRequestEventsId,$data);
                    $visitorRequestEvents[$i]['5'] = $newVisitorConversionValue;
                    $visitorRequestEvents[$i]['9'] = $currentConversionValueAggregation;
                    $key = $visitorid . "_" . $collectionid;
                    storeValueInCache($key,$visitorRequestEvents);
                    break;
                }
                $i++;
            }
            // find the newest request_events for this page and goal
            $latestEvent = $this->getLatestConversionEvent($pageid, $goalid);
            if($latestEvent) {
                $latestEventsId = $latestEvent[3];
                if($latestEventsId != $conversionRequestEventsId) { // if current entry is not newest
                    $data = array(
                        'conversion_value_aggregation' => $cvag
                    );
                    // update in DB
                    $this->updateRequestEvent($latestEventsId,$data);
                }
            }
        }
        else {
            // insert into request events 
            $eventid = $this->createRequestEvent(
                OPT_EVENT_CONVERSION, // type=conversion
                $goalid,
                $impressions,
                $conversions,
                $newVisitorConversionValue, // conversionvalue
                $cvag, // conversionvalueAggregation
                $visitorid,
                $collectionid,
                $clientid,
                $pageid,
                $groupid
            );
            $this->flushRequestEventsCache($visitorid, $collectionid);
            // create a new cache entry for getLatestConversionEvent
            $key = "re_latest_" . $pageid . "_" . $goalid;
            $entry = array(array(
                $pageid,
                OPT_EVENT_CONVERSION,
                $collectionid,
                $eventid,
                $goalid,
                $newVisitorConversionValue,
                $cvag,
                $groupid,
                $visitorid,
                $cvag
            ));
            storeValueInCache($key,$entry);                        
        }
        // in case there is a combined goal, store a conversion for this as well
        $this->storeCombinedConversion($clientid,$collectionid, $groupid, $pageid, $visitorid);
    }    

    /*
     * Check if a collection or group contains a combined goal
     * and if so, store a conversion for it
     */
    private function storeCombinedConversion($clientid,$collectionid, $groupid, $pageid, $visitorid) {
        lazyLoadDB();
        // get actual impressuins/conversions
        $kpiResultset = $this->getProjectKPI($collectionid,$groupid);
        foreach($kpiResultset as $page) {
            foreach($page['goals'] as $goal) {
                if($goal['type']==GOAL_TYPE_COMBINED) {
                    $pageid = $page['landing_pageid'];
                    $goalid = $goal['goalid'];
                    $cr = $kpiResultset[$pageid]['goals'][$goalid]['cr'];
                    $conversions = $kpiResultset[$pageid]['goals'][$goalid]['conversions'];
                    $impressions = $kpiResultset[$pageid]['impressions'];
                    $level = $kpiResultset[$pageid]['goals'][$goalid]['level']; 
                    $cvag = $cr * $conversions;
                    $stddev = $kpiResultset[$pageid]['goals'][$goalid]['standard_deviation'];
                    
                    // distinguish primary and secondary
                    if($level == 1) { // primary
                        $sql = "update landing_page set conversions=$conversions,
                            conversion_value_aggregation=$cvag,
                            impressions=$impressions,
                            standard_deviation=$stddev,
                            cr=$cr where landing_pageid=$pageid";
                        $this->db->query($sql);
                    }
                    else { // secondary
                        $sql = "update collection_goal_conversions set conversions=$conversions,
                            conversion_value_aggregation=$cvag,
                            standard_deviation=$stddev
                            where landingpage_collectionid=$collectionid
                            and landing_pageid=$pageid
                            and goal_id=$goalid";
                        $this->db->query($sql);
                    }
                }
            } 
        }       
    }

    /*
     *  Evaluate impact of an impression or conversion to all pages in a collection with regards to
     *  conversion rate, z-score and is_maximum values (indicates wether a page
     *  performs better or worse compared with the control)
     */

    function evaluateImpact($collectionid, $groupid=-1) {
        // make a "flat" array from the kpiresult, only using the primary goal
        $collectionstatus = $this->getcollectionstatusById($collectionid);
        $autopilot = $collectionstatus['autopilot'];
        $sample_time = $collectionstatus['sample_time'];
        $kpiResult = $this->getProjectKPI($collectionid,$groupid);
        $old_pages = array();
        $primaryGoalId = 0;
        foreach($kpiResult as $page) {
            $oldPage = array(
                'landing_pageid'=>$page['landing_pageid'],
                'pagetype'=>$page['pagetype'],
                'impressions'=>$page['impressions'],
                'allocation'=>$page['allocation'],
            );
            foreach($page['goals'] as $goal) {
                if($goal['level']==1) {
                    $oldPage['conversions'] = $goal['conversions'];
                    $oldPage['cr'] = $goal['cr'];
                    $oldPage['aggregated_value'] = $goal['aggregated_value'];
                    $oldPage['standard_deviation'] = $goal['standard_deviation'];
                    $oldPage['z_score'] = $goal['z_score'];
                    $oldPage['is_maximum'] = $goal['is_maximum'];
                    $oldPage['goaltype'] = $goal['type'];
                    $primaryGoalId = $goal['goalid'];
                }
            }
            $old_pages[] = $oldPage;
        }

        $collectionstatus = $this->getcollectionstatusById($collectionid);
        if($collectionstatus['testtype'] == OPT_TESTTYPE_TEASER)
            $isTeaserTest = true;
        else
            $isTeaserTest = false;

        if($groupid!=-1)
            $old_progress = $collectionstatus['page_groups'][$groupid]['progress'];
        else
            $old_progress = $collectionstatus['progress'];


        // for all tests except teasertest: handle decision allocation such that decisions with 
        // allocation=0 are treated as if they were not present
        if(!$isTeaserTest) {
            $old_pages_with_nonzero_allocation = array();
            foreach($old_pages as $page) {
                if($page['allocation'] > 0)
                    $old_pages_with_nonzero_allocation[] = $page;
            }
            $old_pages = $old_pages_with_nonzero_allocation;
        }

        if($old_progress != OPT_PROGRESS_SIG && $old_progress != OPT_PROGRESS_NSIG_LEAD)
            $old_progress = OPT_PROGRESS_NSIG;

        // if minimum sample time is not reached, then only conversion-rate and z_score shall 
        // be updated, but not progress and is_maximum (as these values control wether a test continues
        // to run or wether a variant receives traffic etc.
        if (!isset($sample_time))
            $sample_time = 0;
        if ($sample_time < $this->config->item('MIN_SAMPLE_TIME'))
            $min_sample_time_reached = false;
        else
            $min_sample_time_reached = true;
        // This does NOT apply for teasertests
        if($isTeaserTest)
            $min_sample_time_reached = true;

        // calculate new values for z-score and is_maximum
        $pagesResult = $this->deriveResultForPages($old_pages);
        if(!$pagesResult)
            return;

        $new_pages = $pagesResult['new_pages'];
        $all_variants_worse_than_control = $pagesResult['all_variants_worse_than_control'];
        $one_variant_better_than_control = $pagesResult['one_variant_better_than_control'];
        $all_variants_significant = $pagesResult['all_variants_significant'];
        $all_variants_insignificant = $pagesResult['all_variants_insignificant'];
        
        /*
        dblog_debug("all_variants_worse_than_control $all_variants_worse_than_control 
            one_variant_better_than_control $one_variant_better_than_control 
            all_variants_insignificant $all_variants_insignificant
            all_variants_significant $all_variants_significant");
            */

        // loop over pages and update every page which has a changed z-score or is_maximum value
        $change_in_page_significance = false;
        for($i=0;$i<sizeof($old_pages);$i++) {
            if(($old_pages[$i]['z_score'] != $new_pages[$i]['z_score']) ||
                ($old_pages[$i]['is_maximum'] != $new_pages[$i]['is_maximum'])) {
                if($min_sample_time_reached) {
                    $pageid = $new_pages[$i]['landing_pageid'];
                    $sql = "update landing_page set z_score=" . $new_pages[$i]['z_score'] . ", is_maximum=" . 
                        $new_pages[$i]['is_maximum'] . " where landing_pageid = $pageid";
                    $kpiResult[$pageid]['goals'][$primaryGoalId]['z_score'] = $new_pages[$i]['z_score'];
                    $kpiResult[$pageid]['goals'][$primaryGoalId]['is_maximum'] = $new_pages[$i]['is_maximum'];
                }
                else {
                    $sql = "update landing_page set z_score=" . $new_pages[$i]['z_score'] . " where landing_pageid = " . $new_pages[$i]['landing_pageid'];                    
                    $kpiResult[$pageid]['goals'][$primaryGoalId]['z_score'] = $new_pages[$i]['z_score'];
                }
                $this->db->query($sql);
                $key = "pkpi_" . $collectionid . "_" . $groupid;
                storeValueInCache($key,$kpiResult);
            }
            if($old_pages[$i]['is_maximum'] != $new_pages[$i]['is_maximum'])
                $change_in_page_significance = true;
        }

        // derive if status change of test or not
        // this overwrites the result of change_in_page_significance as well
        if($old_progress == OPT_PROGRESS_NSIG && $all_variants_significant) {
            $test_changed_to_significant = true;
            $change_in_page_significance = true;
        }
        else {
            $test_changed_to_significant = false;            

        }
        if($old_progress == OPT_PROGRESS_SIG && !$all_variants_significant) {
            $test_changed_to_non_significant = true;
            $change_in_page_significance = true;
        }
        else {
            $test_changed_to_non_significant = false;            
        }

        // if the test changed to significant, update progress
        if($test_changed_to_significant) {
            if($min_sample_time_reached) {
                if($groupid!=-1) {
                    $sql = "update page_group set progress=" . OPT_PROGRESS_SIG . " where page_groupid=$groupid";
                }
                else {
                    $sql = "update landingpage_collection set progress=" . OPT_PROGRESS_SIG . " where landingpage_collectionid=$collectionid";                    
                }
                $this->db->query($sql); 
                // flush cache since progress is retrieved from cache
                self::dblog_cachedebug('--- reload getcollectionstatus, collectioncode=' . $collectionstatus['collectioncode']);
                $this->getcollectionstatus($collectionstatus['collectioncode'],true);
            }

        }
        if($test_changed_to_non_significant) {
            if($groupid!=-1) {
                $sql = "update page_group set progress=" . OPT_PROGRESS_NSIG . " where page_groupid=$groupid";
            }
            else {
                $sql = "update landingpage_collection set progress=" . OPT_PROGRESS_NSIG . " where landingpage_collectionid=$collectionid";
            }
            $this->db->query($sql);                
            // flush cache since progress is retrieved from cache
            self::dblog_cachedebug('--- reload getcollectionstatus, collectioncode=' . $collectionstatus['collectioncode']);
            $this->getcollectionstatus($collectionstatus['collectioncode'],true);
        }        // if the test changed to non-significant, update progress
        // if one of the variants has changed significance, but the test as a whole is not, change slots
        $slotRefreshDone = false;
        if(!$all_variants_significant && $change_in_page_significance) {
            $this->getcollectionstatus($collectionstatus['collectioncode'],true);
            if($autopilot==1)
                $this->updateslots(OPT_SLOTS_NOSIG,$collectionid,-1,$groupid); 
            $slotRefreshDone = true;          
        }

        // if result of single variants or complete project has not changed, we still must check wether slots
        // need to be refreshed. This can happen when
        // - a winning decision is deleted and the second best is the new winner
        // - allocation in a non-significant project has changed by user via the API
        // while doing the check, identify a winning variant (there might be none, though)
        $slotRefreshNeeded = false;

        if($all_variants_significant && $change_in_page_significance) // slot update always needed in this case
            $slotRefreshNeeded = true;
        
        $best_cr = 0;
        $winnerid = -1;
        foreach ($old_pages as $page) {
            if($groupid!=-1) {
                $page = $collectionstatus['page_groups'][$groupid]['pages'][$page['landing_pageid']];
            }
            else {
                $page = $collectionstatus['landingpages'][$page['landing_pageid']];
            }
            $distance = $page['rotation_slot_end'] - $page['rotation_slot_begin'];
            $deviation = abs($distance - $page['allocation']);
            if($deviation>0.01) {
                $slotRefreshNeeded = true;
            }
        }
        // check for a winner;
        $best_cr = 0;
        $winnerid = -1;
        for($i=0;$i<sizeof($old_pages);$i++) {
            if($new_pages[$i]['cr'] > $best_cr) {
                $best_cr = $new_pages[$i]['cr'];
                $winnerid = $new_pages[$i]['landing_pageid'];
            }
        }            

        // decide which mode for the slot update we need (applied only when comparison above shows we need it)
        if($all_variants_insignificant)
            $slotRefreshMode = OPT_SLOTS_EQUIDIST;
        if(!$all_variants_insignificant&&!$all_variants_significant)
            $slotRefreshMode = OPT_SLOTS_NOSIG;
        if($all_variants_significant)
            $slotRefreshMode = OPT_SLOTS_WINNER;
        if($all_variants_significant && $change_in_page_significance)
            $slotRefreshMode = OPT_SLOTS_WINNER;

        if($slotRefreshNeeded&&!$slotRefreshDone) {
            $this->getcollectionstatus($collectionstatus['collectioncode'],true);
            if($autopilot==1)
                $this->updateslots($slotRefreshMode,$collectionid,$winnerid,$groupid);                
        }
    }

    /*
     * For an array of pages derive the relevant values to evaluate sttaus
     * of a test (z-score, is_maximum) and ad them to the array.
     * Input: array of pages. Columns:
     * - impressions
     * - conversions
     * - cr
     * - landing_pageid
     * - pagetype
     * - z_score (optional)
     * - is_maximum (optional)
     * Returns the array with updated values of z_score and is_maximum for each page
     * plus some flags for the overall status
     */ 
    public function deriveResultForPages($old_pages) {
        if(sizeof($old_pages)==0)
            return false;

        // find the control and derive test significance
        $controlindex = -1;
        for($i=0;$i<sizeof($old_pages);$i++) {
            if($old_pages[$i]['pagetype']==OPT_PAGETYPE_CTRL)
                $controlindex = $i;
        }

        if((sizeof($old_pages)==1)||($controlindex==-1)) {
            $result = array(
                'new_pages' => $old_pages,
                'all_variants_worse_than_control' => false,
                'one_variant_better_than_control' => false,
                'all_variants_significant' => false,
                'all_variants_insignificant' => true,
            );  
            return $result;            
        }

        $control = $old_pages[$controlindex];
        //$impressions_control = $control['impressions'];
        //$conversions_control = $control['conversions'];
        $cr_control = $control['cr'];
        $new_pages = array();
        $all_variants_worse_than_control = true; // tested while looping variants
        $one_variant_better_than_control = false; // tested while looping variants
        $all_variants_significant = true; // tested while looping variants
        $all_variants_insignificant = true; // tested while looping variants
        foreach($old_pages as $page) {

            $controlData = array(
                'impressions' => $control['impressions'],
                'conversions' => $control['conversions'],
                'cr' => $control['cr'],
                'aggregated_value' => $control['aggregated_value'],
                'standard_deviation' => $control['standard_deviation']
            );
            $variantData = array(
                'impressions' => $page['impressions'],
                'conversions' => $page['conversions'],
                'cr' => $page['cr'],
                'aggregated_value' => $page['aggregated_value'],
                'standard_deviation' => $page['standard_deviation']
            );
            $z_score = Calculation::calcZscore($controlData, $variantData, $page['goaltype']);
            $page['z_score'] = $z_score;
            // derive is_maximum for variants, meaning: check wether variant is clearly better or worse the control
            if($page['pagetype'] == 2) { // only for variants
                if($page['z_score'] >= $this->config->item('MAX_ZSCORE')) { // variant significant?
                    if($page['cr'] > $cr_control) {
                        $page['is_maximum'] = OPT_PERFORMANCE_BETTER;
                        // if at least one variant beats the control, $one_variant_better_than_control is true
                        $one_variant_better_than_control = true;
                        $all_variants_worse_than_control = false;
                    }
                    else {
                        $page['is_maximum'] = OPT_PERFORMANCE_WORSE;
                    }
                    $all_variants_insignificant = false;
                }
                else { // if at least one variant is not significant, then $all_variants_worse_than_control can not be true
                    $all_variants_worse_than_control = false;
                    $all_variants_significant = false;
                    $page['is_maximum'] = OPT_PERFORMANCE_UNKNOWN;
                }
            }
            $new_pages[] = $page;
        }
        // set the new is_maximum for the control
        $new_pages[$controlindex]['is_maximum'] = OPT_PERFORMANCE_UNKNOWN;
        if($all_variants_worse_than_control) {
            $new_pages[$controlindex]['is_maximum'] = OPT_PERFORMANCE_BETTER;
        }
        if($one_variant_better_than_control) {
            $new_pages[$controlindex]['is_maximum'] = OPT_PERFORMANCE_WORSE;
        }  

        $result = array(
            'new_pages' => $new_pages,
            'all_variants_worse_than_control' => $all_variants_worse_than_control,
            'one_variant_better_than_control' => $one_variant_better_than_control,
            'all_variants_significant' => $all_variants_significant,
            'all_variants_insignificant' => $all_variants_insignificant,
        );  
        return $result;
        
    } 

    /*
     * More or less useless, just forwards to evaluateImpact
     */

    function evaluateImpactAfterCollectionChange($collectionid) {
        $this->evaluateImpact($collectionid);
    }

    /*
     *  function for update rotation slot when ismaximum set to 1
     *  mode: defines how slots shall be recalculated
     *      OPT_SLOTS_EQUIDIST = all pages shall get the same slot width / likeliness
     *      OPT_SLOTS_WINNER = the winning page shall get a higher sloth width, all other pages share thre rest
     *      OPT_SLOTS_NOSIG = pages that are statistically confident do get no slot width (are not called anymore), all other pages share thre rest
     *  winnerid = in case mode = OPT_SLOTS_WINNER this value indicates the winning page 
     */

    function updateslots($mode, $collectionid, $winnerid = -1, $page_groupid = -1) {
        if(($mode!=OPT_SLOTS_EQUIDIST)&&($mode!=OPT_SLOTS_NOSIG)&&($mode!=OPT_SLOTS_WINNER))
            return false;

        if(($mode==OPT_SLOTS_WINNER)&&($winnerid==-1))
            return false;
        
        // get some project attributes
        $collection = $this->getcollectionstatusById($collectionid);
        $testtype = $collection['testtype'];

        $decisions = array();
        if($testtype != OPT_TESTTYPE_TEASER) {
            $decisionsInCollectionStatus = $collection['landingpages'];
        }
        else {
            $decisionsInCollectionStatus = $collection['page_groups'][$page_groupid]['pages'];
        }
        foreach($decisionsInCollectionStatus as $decision) {
            $decisions[] = $decision;
        }            
        $this->load->helper('allocation');

        if ($mode == OPT_SLOTS_WINNER) {
            // care for edge case: winner-decision has allocation of 0
            // behave like OPT_SLOTS_EQUIDIST in such a case
            $decisionsClone = $decisions;
            $winnerAllocation = 0;
            for($i=0; $i < sizeof($decisions); $i++) {
                if($decisions[$i]['landing_pageid']!=$winnerid) {
                    $decisions[$i]['allocation'] = 0;
                }
                else {
                    $winnerAllocation = $decisions[$i]['allocation'];
                    $decisions[$i]['allocation'] = 1;
                }
            }
            if($winnerAllocation == 0)
                $decisions = $decisionsClone;
            if($testtype != OPT_TESTTYPE_TEASER)
                $decisions = normalizeDecisionAllocation($decisions, $testtype);
        } 
        else if ($mode == OPT_SLOTS_EQUIDIST) {
            // for all project types except teasertests: distribute slots according to allocation
            $decisions = normalizeDecisionAllocation($decisions, $testtype);
        } 
        else if ($mode == OPT_SLOTS_NOSIG) {
            // care for edge case: non-significant dsecisions have allocation of 0
            // behave like OPT_SLOTS_EQUIDIST in such a case
            $decisionsClone = $decisions;
            $sumNonSignificantDecisionAllocations = 0;
            for($i=0; $i < sizeof($decisions); $i++) {
                if(($decisions[$i]['is_maximum']!=OPT_PERFORMANCE_UNKNOWN)&&($decisions[$i]['pagetype']==OPT_PAGETYPE_VRNT)) {
                    $decisions[$i]['allocation'] = 0; ;
                }
                else {
                    $sumNonSignificantDecisionAllocations += $decisions[$i]['allocation'];
                }
            }
            if($sumNonSignificantDecisionAllocations == 0)
                $decisions = $decisionsClone;

            $decisions = normalizeDecisionAllocation($decisions, $testtype);
        }
        // update decision slots
        $slotStart = 0;
        foreach ($decisions as $row) {
            $pageid = $row['landing_pageid'];
            $slotEnd = $slotStart + $row['allocation'];
            $sql = "UPDATE landing_page SET rotation_slot_begin=$slotStart,rotation_slot_end=$slotEnd 
                where landing_pageid=$pageid
                and page_groupid=$page_groupid";
            $query = $this->db->query($sql);
            $slotStart = $slotEnd;
        }
        // refresh cache
        $collectioncode = $collection['code'];
        $this->getcollectionstatus($collectioncode,true);
    }

    /**
     * Update rotation slots without changing the mode that the system is in
     * Uses $this->updateslots()
     * If a collcetion is in mode non-significant, then distribute all traffic to the non.significant pages equally
     * If a collcetion is significant, then identify the winner and distribiute more traffic to this page
     * If the winning page is not active, then distribute all traffic equally
     */
    function updateSlotsWithoutProgressChange($collectionid, $groupid=-1) {
        // retrieve progress and winner-id
        $sql = "select cr,landing_pageid,is_maximum,pagetype from landing_page 
            where landingpage_collectionid=$collectionid 
            and pagetype!=3 
            and status=2
            and page_groupid=$groupid";
        $pages = array();
        $controlindex = -1;
        $winnerindex = -1;
        $i = 0;
        $bestcr = 0;
        $testIsSignificant = true;
        $query = $this->db->query($sql);
        foreach ($query->result_array() as $row) {
            $pages[] = $row;
            if($row['pagetype']==1)
                $controlindex = $i;
            if($row['cr'] > $bestcr) {
                $bestcr = $row['cr'];
                $winnerindex = $i;
            }
            if($row['is_maximum'] == 0)
                $testIsSignificant = false;
            $i++;
        }

        // if nonsignificance, distribute equally
        if (!$testIsSignificant) {
            dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LPDETAILSPAGE, "OPT_SLOTS_NOSIG", -1);
            $this->updateslots(OPT_SLOTS_NOSIG, $collectionid, -1, $groupid);
            //echo "rotate OPT_SLOTS_NOSIG";
        } else {
            // if significance set slots for winner
            dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LPDETAILSPAGE, "OPT_SLOTS_WINNER", -1);
            $winnerid = $pages[$winnerindex]['landing_pageid'];
            $this->updateslots(OPT_SLOTS_WINNER, $collectionid, $winnerpageid, $groupid);
        }
    }    

    /*
     * Save data from diagnose controller to tracecode table
     * dg: array with diagnose data
     * tracecode: code of tracecode
     */
    function saveDiagnoseData($diagnose, $tracecode) {
        lazyLoadDB();
        // check if there is a valid tracecode entry to update
        $sql = "select count(*) as cnt,tracecodeid from tracecode where code='$tracecode'
                and createdate >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res, MYSQL_ASSOC);
        $numcodes = $row['cnt'];
        $tid = $row['tracecodeid'];
        if ($numcodes > 0) {
            // make the update
            $diagnosedata = base64_encode(serialize($diagnose));
            $data = array(
                'content' => $diagnosedata
            );
            $this->db->where('tracecodeid', $tid);
            $this->db->update('tracecode', $data);
            return(true);
        } else {
            return(false);
        }
    }
    
    /*
    * Get a list of projects potentially running on the same URL as a given project
    * - Compares the project URL pattern for equality to check wether projects are running on the same URL
    * - This list can be used as input for function determineCollectionConflicts
    * - Retrieves only running projects, but includes the given project even if it is paused
    */
    public function getActiveProjectsOnSameUrl($clientid, $collectionid) {
        // get all active maxtching projects plus the specified one (even if not active)
        // TODO: select for lp.page_groupid = -1 as soon as this field is introduced
        $sql = "select lpc.landingpage_collectionid,lpc.status,lp.canonical_url,
            lpc.testtype,lpc.smartmessage,lpc.name
            from landingpage_collection lpc, landing_page lp
            where lpc.clientid = $clientid
            and lpc.landingpage_collectionid = lp.landingpage_collectionid
            and lp.pagetype = 1
            and lp.canonical_url is not null
            and lp.canonical_url != ''
            order by lpc.landingpage_collectionid desc";
        $res = mysql_query($sql);

        $activeProjects = array();
        while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
            $id = $row['landingpage_collectionid'];
            $projectdata = array(
                'collectionid' => $id,
                'name' => $row['name'],
                'pattern' => $row['canonical_url'],
                'testtype' => $row['testtype'],
                'status' => $row['status'],
                'isSmartMessage' => (($row['smartmessage'] == 1) ? true : false),
            );
            if(($projectdata['status']==OPT_PAGESTATUS_ACTIVE) || ($projectdata['collectionid']==$collectionid)) {
                $activeProjects[$id] = $projectdata;                
            }
        }
        $mypattern = $activeProjects[$collectionid]['pattern'];
        $activeMatchingProjects = array();
        $i = 0;
        foreach($activeProjects as $key=>$value) {
            if($activeProjects[$key]['pattern'] == $mypattern) {
                $activeMatchingProjects[] = $value;
            }
        }

        return $activeMatchingProjects;
    }

    /*
    * - Determines wether there is a conflict between collectionid and any of the provided projects
    * - Array projects must have attributes as in: 
        'collectionid' => $id,
        'name' => $row['name'],
        'pattern' => $row['canonical_url'],
        'testtype' => $row['testtype'],
        'status' => $row['status'],
        'isSmartMessage' => (($row['smartmessage'] == 1) ? true:false)
    * - The collection referred to by collectionid may be active or inactive.
    * - If the collection is active, the function determines wether it is in fact not delivered due to conflicts
    * - If it is not active,  the function determines wether there are conflicts so it would not be 
    *   delivered or it would cause currently active projects to be not delivered anymore
    * - If it is not active,  the function determines wether there are conflicts so it would not be delivered
    * - The function returns an array
    * hasConflicts: true/false
    * errorCode:
    * 0 = no Error
    * 1 = project not delivered
    * 2 = other projects not delivered
    * affectedProjects: array with affected projects. The key is project ID, value is a reason:
    * 0 = no Reason because no problem
    * 1 = only one SmS can be delivered
    * 2 = Split tests can only run exclusively  
    */
    public function determineCollectionConflicts($activeMatchingProjects, $collectionid) {
        // get the index of the given project in the list of projects
        $indexOfMyProject = -1;
        $i=0;
        foreach($activeMatchingProjects as $project) {
            if($project['collectionid'] == $collectionid) {
                $indexOfMyProject = $i;
            }
            $i++;
        }
        if($indexOfMyProject == -1) 
            return false;

        // we now have an array of all matching projects, plus the index where in this array the
        // given project is
        // determine the final result in 2 steps:
        // 1. Create a result array from element 0 up to the last element before the given project
        // - check wether there is a conflict or not when adding the given project
        // - if the project is active and there is a conflict, the resultig error code is "project not delivered", otherwise "no error"
        // - if the project is inactive and there is a conflict, the resultig error code is "project not delivered"
        // - if the project is inactive and there is no conflict, proceed with step 2
        // 2. Create a result array from all elements, but excluding the given project
        // - create an alternative result array from all elements, including the given project
        // - compare the 2 arrays. If they are equal (with the exception of the given project) the result is "no error"
        // - if they are not equal, the rror code is "other projects not delivered"
        $resultArray = array();
        for($i=0; $i < $indexOfMyProject; $i++) {
            if($this->hasConflicts($resultArray,$activeMatchingProjects[$i]) == 0) {
                $resultArray[] = $activeMatchingProjects[$i];
            }
        }
        $conflictInStep1 = $this->hasConflicts($resultArray,$activeMatchingProjects[$indexOfMyProject]);
        if($activeMatchingProjects[$indexOfMyProject]['status'] == OPT_PAGESTATUS_ACTIVE) {
            if($conflictInStep1 != 0) {
                // active and conflict
                $result = array(
                    'hasConflicts' => true,
                    'errorCode' => 1, // project not delivered
                    'affectedProjects' => array(
                        $collectionid => $conflictInStep1
                    )                        
                );
            }
            else {
                // active and no conflict
                $result = array(
                    'hasConflicts' => false,
                    'errorCode' => 0,
                    'affectedProjects' => array(
                        $collectionid => 0
                    )                        
                );                    
            }
        }
        else {
            if($conflictInStep1 != 0) {
                // inactive and conflict
                $result = array(
                    'hasConflicts' => true,
                    'errorCode' => 1, // project not delivered
                    'affectedProjects' => array(
                        $collectionid => $conflictInStep1
                    )                        
                );
            }
            else {
                // STEP 2
                $resultArray2 = $resultArray; // make a copy and create a result array without the given project
                $resultArray[] = $activeMatchingProjects[$indexOfMyProject]; // add the given project to the original array
                $indexOfMyProjectInResultArray = sizeof($resultArray) - 1;
                $affectedProjects = array();
                for($i=$indexOfMyProject+1; $i < sizeof($activeMatchingProjects); $i++) {
                    $conflictInResultArray = $this->hasConflicts($resultArray,$activeMatchingProjects[$i]);
                    $conflictInResultArray2 = $this->hasConflicts($resultArray2,$activeMatchingProjects[$i]);
                    if($conflictInResultArray == 0) {
                        $resultArray[] = $activeMatchingProjects[$i];
                    }
                    if($conflictInResultArray2 == 0) {
                        $resultArray2[] = $activeMatchingProjects[$i];
                    }
                    if($conflictInResultArray != $conflictInResultArray2) {
                        $affectedProjects[$activeMatchingProjects[$i]['collectionid']] = $conflictInResultArray;                                                                        
                    }
                }
                if(sizeof($affectedProjects) > 0) {
                    $result = array(
                        'hasConflicts' => true,
                        'errorCode' => 2,
                        'affectedProjects' => $affectedProjects                    
                    );                    
                }
                else {
                    $result = array(
                        'hasConflicts' => false,
                        'errorCode' => 0
                    );                                        
                }
            }
        }
        return $result;
    }

    /*
    * Helper function: check wether a project has conflicts with other projects running on the same URL
    * We expect haystack to contain arrays of the same type as needle with the following attributes:
        'collectionid' => $id,
        'name' => $row['name'],
        'pattern' => $row['canonical_url'],
        'testtype' => $row['testtype'],
        'status' => $row['status'],
        'isSmartMessage' => (($row['smartmessage'] == 1) ? true:false)
    * 
    */
    private function hasConflicts($haystack,$needle) {
        $isSmartMessage = $needle['isSmartMessage'];
        $isSplitTest = ($needle['testtype'] == OPT_TESTTYPE_SPLIT) ? true : false; 
        $isVisualTest = false;
        if($needle['testtype'] == OPT_TESTTYPE_VISUALAB)
            $isVisualTest = true;
        if($needle['testtype'] == OPT_TESTTYPE_TEASER)
            $isVisualTest = true;
        if($needle['testtype'] == OPT_TESTTYPE_MULTIPAGE)
            $isVisualTest = true;
        foreach($haystack as $project) {
            if($project['testtype'] == OPT_TESTTYPE_SPLIT) {
                return(2); // Split tests can only run exclusively, no element can be added
            }
            else { // visual test. could be smart message
                if($isSplitTest) {
                    return(2); // Split tests can only run exclusively                    
                }
                if($isSmartMessage) {
                    if($project['isSmartMessage']) {
                        return(1); // only one SmS can be delivered                                            
                    }
                }
            }
        }
        return(0); // no conflict
    }
    
    /**
     * Helper function: dom_modification_code contains several attributes with code for this variant, reqeite the
     * dommodification_code such that it can be injected:
     * 1. Remove attribute [SMS_HTML] if present
     * 2. Ensure that attribute [SMS] is the first, if present.
     * 
     * ********************************************************************************************************
     * ******** (array) json_decode is to easily unset [SMS_HTML] and move [SMS] to the first position *********
     * ********************************************************************************************************
     */
    private function prepareDomcodeForDelivery($dom_code) {
        $dom = (array) json_decode($dom_code);
        unset($dom['[SMS_HTML]']);
        reset($dom);
        if (key($dom) != '[SMS]' && array_key_exists('[SMS]', $dom)) {
            $dom = array('[SMS]' => $dom['[SMS]']) + $dom;
        }
        return json_encode($dom);
    }

}

// class end here
?>