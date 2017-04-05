<?php

class landingpagecollection extends CI_Model {

    function __construct() {
        parent::__construct();
        // load mysql connection
        $this->load->database();
    }

    /**
     * In Visual mode, some LPC related controllers, requires some data from the client table that is not
     * returned by the API
     * @param Ind $clientid
     * @return Array
     */
    public function getCustomClientData($clientid){
        $query = $this->db->select('userplan')
                ->from('client')
                ->where('clientid', $clientid)
                ->get();
        return $query->row();
    }
    
    /**
     * in VISUAL mode, there are some fields from the LPC table that are not returned by the API
     * so we need to get it with this custom query, it returns the LPC code, the smartmessage field
     * value, among others
     * @param Int $lpcid - The project ID
     * @return Array.
     */
    public function getCustomLpcData($lpcid){
        $query = $this->db->select('lpc.code, lpc.smartmessage, lpc.tracking_approach, lpc.tracked_goals, c.status')
                ->from('landingpage_collection lpc')
                ->join('client c', 'c.clientid = lpc.clientid', 'INNER')
                ->where('landingpage_collectionid', $lpcid)
                ->get();
        return $query->row();
    }
    
    /**
     * Given a landing page ID, returns its dom_modification_code (or an empty string)
     * @param Ind $lpd
     * @return String
     */
    public function getVariantDomCode($lpd){
        $query = $this->db->select('dom_modification_code')
                ->from('landing_page')
                ->where('landing_pageid', $lpd)
                ->get();
        
        if($query->num_rows() > 0){
            return json_decode($query->row()->dom_modification_code);
        }
        return '';
    }

    /**
     * Returns either the MIN or the MAX date from the request_events table for a given project
     * @param Int $lpcid - the project id
     * @param Int $groupid - the if it is a TT, gets the group ID
     * @param String $minimax - Either 'MIN' or 'MAX'
     * @return String - the first or last event date.
     */
    public function getReferenceEvent($lpcid, $groupid, $minimax = 'MAX') {
        $this->load->library('multidb');
        $CLIENT_DB = $this->multidb->getClientDb();
        $select = $minimax == 'MIN' ? 'MIN(date) as date' : 'MAX(date) as date';

        if ($groupid != -1) {
            $q1 = $this->db->select('page_groupid, restart_date')
                    ->from('page_group')
                    ->where('landingpage_collectionid', $lpcid)
                    ->where('page_groupid', $groupid)
                    ->get();

            $g = $q1->row()->page_groupid;
            $d = $q1->row()->restart_date;

            $q2 = $CLIENT_DB->select($select)
                    ->from('request_events')
                    ->where('landingpage_collectionid', $lpcid)
                    ->where('page_groupid', $g)
                    ->where('date >', $d)
                    ->get();
        } else {
            $q1 = $this->db->select('restart_date')
                    ->from('landingpage_collection')
                    ->where('landingpage_collectionid', $lpcid)
                    ->get();

            $d = $q1->row()->restart_date;

            $q2 = $CLIENT_DB->select($select)
                    ->from('request_events')
                    ->where('landingpage_collectionid', $lpcid)
                    ->where('date >', $d)
                    ->get();
        }

        if ($q2->num_rows() > 0 && $q2->row()->date != NULL) {
            return date('Y-m-d H:i:s', strtotime($q2->row()->date));
        } else if ($minimax == 'MIN') {
            if ($d != NULL) {
                return date('Y-m-d H:i:s', strtotime($d));
            }
        }

        return date('Y-m-d');
    }

    /*     * ************************************************ DUPLICATE TEST ****************************************** */

    /**
     * first it confirms that the lpc id exists, then it calls the method to duplicate the LPC
     * @param integer $lpcid
     * @param integer $clientid
     * @param string $testname
     * @return boolean
     */
    function duplicateTest($lpcid, $clientid, $testname) {
        $sql = " SELECT count(*) as count FROM landingpage_collection WHERE landingpage_collectionid = ? AND clientid = ? ";
        $query = $this->db->query($sql, array($lpcid, $clientid));
        $count = $query->row()->count;

        if ($count == 1) {
            return self::duplicateLandinpageCollection($lpcid, $testname);
        } else {
            return FALSE;
        }
    }

    /**
     * Makes a copy of the given LPC and if everything goes well, calls the method to create a copy of each of its variants.
     * @param integer $lpcid
     * @param string $testname
     */
    private function duplicateLandinpageCollection($lpcid, $testname) {
        $sql = " INSERT INTO landingpage_collection " .
                " (testtype, tracking_approach, referrer_regex, testgoal, tracked_goals, mvt_testorder, optimization_mode, start_date, " .
                " end_date, autopilot, allocation, clientid, ignore_ip_blacklist, personalization_mode, smartmessage) " .
                " SELECT testtype, tracking_approach, referrer_regex, testgoal, tracked_goals, mvt_testorder, optimization_mode, start_date, " .
                " end_date, autopilot, allocation, clientid, ignore_ip_blacklist, personalization_mode, smartmessage " .
                " FROM landingpage_collection WHERE landingpage_collectionid = ? ";
        $this->db->query($sql, array($lpcid));
        $newlpcid = $this->db->insert_id();

        if (is_int($newlpcid) && $newlpcid > 0) {
            self::updateDuplicatedLpc($newlpcid, $testname);
            return self::duplicateLandingPages($lpcid, $newlpcid);
        }else{
            return FALSE;
        }
    }

    /**
     * After duplicating the LPC -- fields "restart date", "status", "progress", etc are set as if it was a new test.
     * @param integer $newlpcid
     */
    private function updateDuplicatedLpc($newlpcid, $testname) {
        $code = 'BT-' . md5($newlpcid);
        $sql = " UPDATE landingpage_collection SET name = ?, code = ?, status = 1, progress = 1, " .
                " restart_date = '2010-01-01 00:00:00', last_sample_date = null, sample_time = 0 " .
                " WHERE landingpage_collectionid = ? ";
        $this->db->query($sql, array($testname, $code, $newlpcid));
    }

    /**
     * first it gets the array of LP for the given LPC and for each of them, creates a new entry in LP with the same data
     * @param integer $lpcid
     * @return type
     */
    private function duplicateLandingPages($lpcid, $newlpcid){
        $lpa = self::landingPagesToDuplicate($lpcid);

        foreach ($lpa as $lp) {
            $args = array(
                'name' => $lp['name'],
                'status' => $lp['status'],
                'lp_url' => $lp['lp_url'],
                'canonical_url' => $lp['canonical_url'],
                'pagetype' => $lp['pagetype'],
                'revenue' => $lp['revenue'],
                'arpv' => $lp['arpv'],
                'rotation_slot_begin' => $lp['rotation_slot_begin'],
                'rotation_slot_end' => $lp['rotation_slot_end'],
                'dom_modification_code' => $lp['dom_modification_code'],
                'mvt_order' => $lp['mvt_order'],
                'rule_id' => $lp['rule_id'],
                'landingpage_collectionid' => $newlpcid,
            );
            $this->db->insert('landing_page', $args);
            $lpd = $this->db->insert_id();

            if(!is_int($lpd) || $lpd < 1){
                $sql1 = " DELETE FROM landing_page WHERE landingpage_collectionid = ? ";
                $this->db->query($sql1, array($newlpcid));
                $sql2 = " DELETE FROM landingpage_collection WHERE landingpage_collectionid = ? ";
                $this->db->query($sql2, array($newlpcid));
                return FALSE;
            }
            self::duplicateSmartMessage($newlpcid, $lp['landing_pageid'], $lpd);
        }
        self::updateDuplicatedLandingpage($newlpcid);
        return self::duplicateLpcGoals($lpcid, $newlpcid);
    }

    /**
     * returns the array of LP that belongs to the given LPC (id)
     * @param integer $lpcid
     */
    private function landingPagesToDuplicate($lpcid) {
        $sql.= " SELECT landing_pageid, name, status, lp_url, canonical_url, pagetype, revenue, arpv, rotation_slot_begin, " .
                " rotation_slot_end, dom_modification_code, mvt_order, rule_id " .
                " FROM landing_page WHERE landingpage_collectionid = ? ORDER BY landing_pageid ASC ";
        $query = $this->db->query($sql, array($lpcid));
        return $query->result_array();
    }

    /**
     * Updates the duplicated LP to set its columns with the default data and the name in the control page
     * @param integer $newlpcid
     */
    private function updateDuplicatedLandingpage($newlpcid) {
        $sql = " UPDATE landing_page SET is_maximum = 0, impressions = 0, conversions = 0, cr = 0, z_score = 0 " .
                " WHERE landingpage_collectionid = ? ";
        $this->db->query($sql, array($newlpcid));

        $sql = " UPDATE landing_page SET name = ? WHERE pagetype = 1 AND landingpage_collectionid = ? ";
        $this->db->query($sql, array($newlpcid . '_control', $newlpcid));
    }

    /**
     * duplicates the smart messages and sms attributes from the given old LP
     * @param int $newlpcid
     * @param int $oldlpd
     * @param int $newlpd
     */
    private function duplicateSmartMessage($newlpcid, $oldlpd, $newlpd) {
        $sql = " INSERT INTO smart_message (sms_template_id, name, landing_pageid, landingpage_collectionid, rule_type, rule_args, sms_structure) " .
                " SELECT sms_template_id, name, $newlpd, $newlpcid, rule_type, rule_args, sms_structure FROM smart_message WHERE landing_pageid = ? ";
        $this->db->query($sql, array($oldlpd));
        $smsid = $this->db->insert_id();

        $sql = " INSERT INTO sms_attribute (smart_message_id, name, type, int_value, string_value, text_value, enum_value) " .
                " SELECT $smsid, name, type, int_value, string_value, text_value, enum_value FROM sms_attribute " .
                " WHERE smart_message_id IN (SELECT smart_message_id FROM smart_message WHERE landing_pageid = ?) ";
        $this->db->query($sql, array($oldlpd));
    }

    /**
     * If everything went well in the previous steps, duplicates the collection goals entry for the given LPC id and returns it
     * @param integer $lpcid
     * @param integer $newlpcid
     * @return boolean (false) or the new LPC id
     */
    private function duplicateLpcGoals($lpcid, $newlpcid) {
        $goals = self::goalsToDuplicate($lpcid);
        foreach ($goals as $goal) {
            $args = array(
                'type' => $goal['type'],
                'arg1' => $goal['arg1'],
                'status' => $goal['status'],
                'level' => $goal['level'],
                'page_groupid' => $goal['page_groupid'],
                'landingpage_collectionid' => $newlpcid,
            );
            $this->db->insert('collection_goals', $args);
        }
        return $newlpcid;
    }

    /**
     * returns the array of goals that belongs to the given LPC (id)
     * @param integer $lpcid
     */
    private function goalsToDuplicate($lpcid) {
        $sql = "select * from collection_goals 
            where landingpage_collectionid=$lpcid
            and status=1";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*********************************************** END DUPLICATE TEST *******************************************/	

    //load landing page details for web service
    function landingpagedetails($lpid) {
        $select = "SELECT lp.lp_url, lp.dom_modification_code, lpc.testtype FROM landing_page lp inner join landingpage_collection lpc on lp.landingpage_collectionid = lpc.landingpage_collectionid WHERE landing_pageid = $lpid";
        $result = mysql_query($select);
        $result = mysql_fetch_array($result);
        return $result;
    }

    function getEtrackerConversionGoalsParamValues() {
        $data['6'] = array('Page 1', 'Page 2', 'Page 3');
        $data['5'] = array('viewProduct', 'insertToBasket', 'order');
        return json_encode($data);
    }

    /**
     * Builds the editor_variants_data object based on different tables like LPC, LP, rule, smart_message
     * @param Int $id - the project id
     * @return boolean/JSON
     */
    function getEditorData($id, $groups) {
        $index = 1;
        $data = array(
            'version' => 1,
            'activePage' => 'page_1',
            'pages' => array(),
        );

        foreach ($groups as $ind => &$group) {
            $filterGroup = $group->id * 1 >= 1 ? $group->id : '-1';
            $data['pages']['page_' . $index] = array(
                'id' => $filterGroup,
                'name' => $group->name,
                'url' => $group->mainurl,
                'variants' => array(),
            );

            $query = $this->db->select('lpc.personalization_mode, lp.landing_pageid, lp.lp_index, lp.name, lp.dom_modification_code, '
                            . 'lp.allocation, sms.smart_message_id, sms.sms_structure, r.rule_id, r.name as rulename, r.operation')
                    ->from('landingpage_collection lpc')
                    ->join('landing_page lp', 'lp.landingpage_collectionid = lpc.landingpage_collectionid', 'INNER')
                    ->join('rule r', 'r.rule_id = lp.rule_id', 'LEFT OUTER')
                    ->join('smart_message sms', 'sms.landing_pageid = lp.landing_pageid', 'LEFT OUTER')
                    ->where('lp.landingpage_collectionid', $id)
                    ->where('lp.pagetype', 2)
                    ->where('page_groupid', $filterGroup)
                    ->order_by('lp.landing_pageid', 'ASC')
                    ->get();

            if ($query->num_rows() > 0) {
                $ind = 1;

                foreach ($query->result() as $q) {
                    $vIndex = 'variant_' . $ind;

                    $domcode = json_decode($q->dom_modification_code, TRUE);
                    if ($domcode == NULL) {
                        $domcode = array(
                            '[JS]' => '',
                            '[CSS]' => '',
                        );
                    }

                    $data['pages']['page_' . $index]['variants'][$vIndex] = array(
                        'version' => 1,
                        'name' => $q->name,
                        'id' => $q->landing_pageid,
                        'variantindex' => $q->lp_index,
                        'selectors' => new stdClass(),
                        'sms' => array(
                            'id' => NULL,
                            'template' => NULL,
                        ),
                        'dom_modification_code' => $domcode,
                        'persorule' => NULL,
                        'allocation' => $q->allocation,
                    );

                    if ($q->personalization_mode != '0' && is_numeric($q->rule_id)) {
                        $data['variants'][$vIndex]['persorule'] = array(
                            'id' => $q->rule_id,
                            'name' => $q->rulename,
                            'type' => $q->personalization_mode,
                        );
                    }

                    if (is_numeric($q->smart_message_id) && (1 * $q->smart_message_id) > 0) {
                        $sms = json_decode($q->sms_structure);

                        $data['variants'][$vIndex]['sms'] = array(
                            'id' => $q->smart_message_id,
                            'template' => $sms->template,
                            'ui' => $sms->sms,
                        );
                    }
                    $ind ++;
                }
            }
        }
        $data['pageCount'] = $index;
        return json_encode($data);
    }

    /**
     * updates the rule ID for the given LP (id)
     * @param integer $lpd
     * @param integer $ruleid
     */
    public function updateLandingpagePerso($lpd, $ruleid) {
        $sql = "UPDATE landing_page SET rule_id = ? WHERE landing_pageid = ? ";
        $this->db->query($sql, array($ruleid, $lpd));
    }

    /**
     * updates the personalization_mode for the given LPC (id)
     * @param integer $lpcid
     * @param integer $persomode
     */
    public function updateLpcPersomode($lpcid, $persomode) {
        $sql = "UPDATE landingpage_collection SET personalization_mode = ? WHERE landingpage_collectionid = ? ";
        $this->db->query($sql, array($persomode, $lpcid));
    }
}

// class end here
?>