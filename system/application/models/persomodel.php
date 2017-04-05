<?php

/**
 * Smart Messaging Model
 */
class persomodel extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * return the array of current rules with the respective conditions
     * @return array
     */
    public function perso_getClientRules($clientid, $ruleid) {
        $params = array(
            $clientid,
        );

        $sql1 = "SELECT rule_id, name, operation FROM rule WHERE clientid = ? ";
        if ($ruleid) {
            $params[] = $ruleid;
            $sql1 .= " AND rule_id = ? ";
        }
        $sql1 .= " ORDER BY name ";

        $query1 = $this->db->query($sql1, $params);
        $rules = $query1->result_array();

        $conds = array();
        foreach ($rules as $rule) {
            $c = array();
            $sql2 = "SELECT rule_condition_id, indication, type, arg FROM rule_condition WHERE rule_id = ? ";
            $query2 = $this->db->query($sql2, array($rule['rule_id']));
            $c[$rule['rule_id']] = $query2->result_array();
            $conds[] = $c;
        }
        return array('status' => TRUE, 'rules' => $rules, 'conds' => $conds);
    }

    /**
     * @param integer $ruleid -
     * @return array - the set of rule conditions matching the rule ID passed as parameter
     */
    public function perso_getRuleConditions($ruleid) {
        $sql = "SELECT r.operation, c.indication, c.type, c.arg, c.rule_condition_id FROM rule r INNER JOIN rule_condition c ON c.rule_id = r.rule_id WHERE r.rule_id = ?";
        $query = $this->db->query($sql, array($ruleid));
        return $query->result_array();
    }

    /**
     * @param array $args
     * @return integer - the last inserted rule id
     */
    public function perso_insertRule($args) {
        $sql = "INSERT INTO rule(name, operation, clientid, phpcode) VALUES(?, ?, ?, ?)";
        $this->db->query($sql, $args);
        return $this->db->insert_id();
    }

    /**
     * @param string $phpcode -- the generated PHP code to be updated in the rule table
     * @param integer $ruleid
     */
    public function perso_addPhpCode($phpcode, $ruleid) {
        $sql = "UPDATE rule SET phpcode = ? WHERE rule_id = ?";
        $this->db->query($sql, array($phpcode, $ruleid));
    }

    /**
     * @param array $args
     */
    public function perso_updateRule($args) {
        $sql = "UPDATE rule SET name = ?, operation = ?, clientid = ?, phpcode = ? WHERE rule_id = ?";
        $this->db->query($sql, $args);
    }

    /**
     * @param integer $ruleid
     */
    public function perso_deleteRule($ruleid, $clientid) {
        self::perso_deleteRuleFromLp($ruleid, $clientid);
        self::perso_deleteAllRuleConditions($ruleid, $clientid);
        $sql = "DELETE FROM rule WHERE rule_id = ? and clientid = ? ";
        $this->db->query($sql, array($ruleid, $clientid));
    }

    /**
     * @param array $args
     * @return integer - the last inserted condition id
     */
    public function perso_insertCondition($args) {
        $sql = "INSERT INTO rule_condition(indication, type, arg, rule_id) VALUES(?, ?, ?, ?)";
        $this->db->query($sql, $args);
        return $this->db->insert_id();
    }

    /**
     * @param array $args
     */
    public function perso_updateCondition($args) {
        $sql = "UPDATE rule_condition SET indication = ?, type = ?, arg = ? WHERE rule_condition_id = ? ";
        $this->db->query($sql, $args);
    }

    /**
     * @param integer $idc - the rule_condition id to be deleted
     */
    public function perso_deleteCondition($idc, $clientid) {
        $sql = "DELETE FROM rule_condition WHERE rule_condition_id = ? AND rule_id IN(SELECT rule_id FROM rule WHERE clientid = ?) ";
        $this->db->query($sql, array($idc, $clientid));
    }

    /**
     * update the rule_id to zero ( 0 ) in every landing page where the given $ruleid is set (When it is deleted)
     * Also, if the rule_id is zero for every single variant in a test, the personalization_mode is set to 0 (NONE) for the entire test.
     * @param int $ruleid
     * @param int $clientid
     */
    private function perso_deleteRuleFromLp($ruleid, $clientid) {
        $sql1 = "UPDATE landing_page SET rule_id = 0 WHERE rule_id = ? AND landingpage_collectionid IN " .
                " (SELECT landingpage_collectionid FROM landingpage_collection WHERE clientid = ? ) ";
        $this->db->query($sql1, array($ruleid, $clientid));

        $sql2 = " SELECT DISTINCT lpc.landingpage_collectionid FROM landingpage_collection lpc " .
                " INNER JOIN landing_page lp ON lp.landingpage_collectionid = lpc.landingpage_collectionid " .
                " WHERE lpc.clientid = ? AND lp.rule_id > 0 ";
        $query = $this->db->query($sql2, array($clientid));

        $lpcids = array(0);
        foreach ($query->result() as $q) {
            $lpcids[] = $q->landingpage_collectionid;
        }
        $lpcid = join(",", $lpcids);

        $sql3 = " UPDATE landingpage_collection SET personalization_mode = 0 WHERE clientid = ? AND landingpage_collectionid NOT IN ($lpcid)";
        $this->db->query($sql3, array($clientid));
    }

    /**
     * @param integer $ruleid the rule id to delete all its conditions
     */
    private function perso_deleteAllRuleConditions($ruleid, $clientid) {
        $sql = "DELETE FROM rule_condition WHERE rule_id = ? AND rule_id IN (SELECT rule_id FROM rule WHERE clientid = ?) ";
        $this->db->query($sql, array($ruleid, $clientid));
    }

}
