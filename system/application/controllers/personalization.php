<?php

/**
 * Personalization Controller.
 */
class personalization extends CI_Controller {

    private $clientid;
    private $ruleid;

    /**
     * first it checks if the user is logged in or else he is redirected to the login page
     * @return type
     */
    function __construct() {
        parent::__construct();
        $this->load->library('session');

        $this->clientid = $this->session->userdata('sessionUserId');
        if (!$this->clientid) {
            lang_redirect('login');
            return;
        }

        $this->load->model('persomodel');
        $this->load->model('optimisation');
        $this->load->model('user');
        $this->load->library('log');
        $this->load->library('curl');
        $this->load->helper('dbcache');
    }

    /**
     * Redirects to the default page (lpc collection list), this controller is to be called via ajax, not directly
     */
    public function index() {
        $login_targetpage = $this->session->userdata('login_targetpage');
        if (!$login_targetpage) {
            redirect($this->config->item('base_ssl_url') . "lpc/cs/");
        } else {
            $this->session->unset_userdata('login_targetpage');
            redirect($login_targetpage);
        }
    }

    /**
     * returns or echoes the current rules created by the logged user depending on if it is called by ajax or by another method
     * in the same class
     * @param boolean $ret
     * @return array
     */
    public function getCurrentRules($ret = false) {
        $rules = $this->persomodel->perso_getClientRules($this->clientid);
        $res = (!$rules['status']) ? array('status' => FALSE, 'message' => 'Previously created rules could not be retrieved.') : $rules;
        if ($ret) {
            return $res;
        }
        echo json_encode($res);
    }

    /**
     * returns the array of elements to be displayed in the drop down lists when selecting personalization rules
     * this includes previously created rules related to the user
     * @return array (JSON)
     */
    public function getRuleOptions() {
        $this->config->load('personalization');
        $rules = self::getCurrentRules(TRUE);
        $conds = $this->config->item('condition_name_select');

        $res = array(
            'status' => TRUE,
            'rules' => $rules,
            'conditions' => (object) $conds,
        );
        echo json_encode($res);
    }

    /**
     * first it verifies the rule name and the conditions sent to be saved or return an error message
     * @return boolean TRUE if the rule is inserted with its corresponding conditions
     */
    public function saveCurrentRule($editedRule = FALSE) {
        $rule = $editedRule ? json_decode($editedRule) : json_decode($this->input->post('rule'));
        $this->ruleid = $rule->id;
        $error = '';
        $error .= (strlen(trim($rule->name)) < 1) ? 'The rule name is not valid. ' : '';
        $error .= (count($rule->conditions) < 1) ? 'No conditions selected for the given rule' : '';
        if ($error != '') {
            echo json_encode(array('status' => FALSE, 'message' => $error));
            return false;
        }

        $ruleargs = array($rule->name, $rule->operation, $this->clientid, 'emptycode');
        $this->ruleid = self::insertUpdateRule($rule->id, $ruleargs);
        if (!is_int($this->ruleid * 1) || ($this->ruleid * 1) < 1) {
            echo json_encode(array('status' => FALSE, 'message' => 'error creating the Rule - ' . $this->ruleid));
            return false;
        }

        $errorcond = 0;
        foreach ($rule->conditions as $key => $conds) {
            $errorcond += self::saveRuleConditions($key, $conds);
        }

        $phpcode = self::compileRule($this->ruleid);
        $this->persomodel->perso_addPhpCode($phpcode, $this->ruleid);
        $this->optimisation->flushAPCCacheForRule($this->ruleid);

        if ($editedRule) {
            return;
        }

        if ($errorcond > 0) {
            echo json_encode(array('status' => FALSE, 'message' => 'there was an error saving the conditions for the given rule'));
            return false;
        } else {
            echo json_encode(array(
                'status' => TRUE,
                'ruleid' => $this->ruleid,
                'rules' => $this->persomodel->perso_getClientRules($this->clientid)
            ));
        }
    }

    /**
     * Given a Rule id, returns the PHP code to be inserted in the rule table
     * @param type $ruleid
     */
    public function compileRule($ruleid) {
        $conds = $this->persomodel->perso_getRuleConditions($ruleid);
        $this->config->load('personalization');
        $phpcode = '';
        foreach ($conds as $cond) {
            // some conditions (those that refer to the visitor history by goals) need to be treated
            // different in that they are not evaluated with every request, bit a lookup to table
            // perso_goal_history is made
            if (($cond['type'] == 'targetpage_opened') || ($cond['type'] == 'insert_basket')) {
                $cond['arg'] = $cond['rule_condition_id'];
            }

            $arg = $cond['arg'];

            $arguments = json_decode($arg);
            if (isset($arguments->value)) {
                $sub = json_decode($arguments->value);
                if (isset($sub->value) && isset($sub->translated)) {
                    $arguments->value = $sub->value;
                    $arg = json_encode($arguments);
                }
            }

            $op = ($cond['operation'] == 'AND') ? '&&' : '||';
            $meth = true;
            $phpcode .= (strlen($phpcode) > 0) ? $op . ' ' : '';
            $phpcode .= ($cond['indication'] != 0) ? '' : ' !';
            $phpcode .= ($meth) ? '$this->prs_' . $cond['type'] . '(\'' . $arg . '\') ' : '1==1 ';
        }
        return ('$persoResult=' . $phpcode . ';');
    }

    /*
     * compile and save a rule - needed for unittesting only
     */

    public function compileAndSaveRule($ruleid) {
        dblog_debug('unittest: debugandsaverule');
        $phpcode = $this->compileRule($ruleid);
        $this->persomodel->perso_addPhpCode($phpcode, $ruleid);
        $this->optimisation->flushAPCCacheForRule($ruleid);
    }

    /**
     * @param array  $ruleargs array of arguments to insert or update the rule in the DB
     * @return integer -- the rule id (new or previous)
     */
    private function insertUpdateRule($ruleid, $ruleargs) {
        if (substr_count($ruleid, 'new') > 0) {
            $this->optimisation->flushAPCCacheForRule($ruleid);
            return $this->persomodel->perso_insertRule($ruleargs);
        } else {
            array_push($ruleargs, $ruleid);
            $this->persomodel->perso_updateRule($ruleargs);
            $this->optimisation->flushAPCCacheForRule($ruleid);
            return $ruleid;
        }
    }

    /**
     * Foreach rule, saves its corresponding conditions, validating/sanitizing first
     * @param array $conds
     */
    private function saveRuleConditions($key, $conds) {
        if (strlen(trim($conds->type)) > 0) {
            $this->config->load('personalization');
            $condlist = $this->config->item('condition_name_select');

            $this->optimisation->flushAPCCacheForRule($this->ruleid);
            if (!self::verifyConditions($condlist, $conds->type, $conds->arg)) {
                return 0;
            }

            $indication = $conds->indication == 'NOT_EQUALS' ? 0 : 1;
            $type = $conds->type;
            $arg = $conds->arg;

            $condsargs = array(
                $indication,
                $type,
                self::formatConditionArguments($type, $arg),
            );

            if (substr_count($key, 'NEW') > 0) {
                array_push($condsargs, $this->ruleid);
                $condid = $this->persomodel->perso_insertCondition($condsargs);
                return (!is_int($condid) || $condid < 1) ? 1 : 0;
            } else {
                array_push($condsargs, $key);
                $this->persomodel->perso_updateCondition($condsargs);
                return 0;
            }
        }
        return 0;
    }

    /**
     * Gets the condition type  and its arguments and if the type is "location_is", sorts the corresponding condition arguments by place hierachy
     * first the country, state, region, city and the lang argument at the end.
     * @param String $type
     * @param JSON/String $arguments
     * @return JSON/String
     */
    private function formatConditionArguments($type, $arguments) {
        $arg = $arguments;
        if ($type == 'location_is') {
            $arguments = json_decode($arguments);
            $arg = array(
                $arguments->geocountry->code => $arguments->geocountry->name,
                $arguments->geostate->code => $arguments->geostate->name,
                $arguments->georegion->code => $arguments->georegion->name,
                $arguments->geocity->code => $arguments->geocity->name,
                'lang' => $arguments->lang,
            );
            return json_encode($arg);
        }
        return $arg;
    }

    /**
     * Verifies that the condition exists and sanitizes the arguments entered by the user for every rule to be saved
     */
    private function verifyConditions($condlist, $type, $arg) {
        $ret = FALSE;
        $regs = array(
            'alphaNumSymb1' => '/^[a-zA-Z0-9&_\-]+$/',
            'alphaNumSymb2' => '/^[a-zA-Z0-9&_\*\.\?\/\:]+$/',
            'alphaNumBlank' => '/^[\ a-zA-Z0-9]+$/',
            'positiveReal' => '/^[0-9]+$/',
            'queryString' => '/^[a-zA-Z0-9_\~\-\.]+=*[a-zA-Z0-9_\~\-\.\/]+$/',
        );

        foreach ($condlist['groups'] as $key => $value) {
            foreach ($value['elements'] as $cond => $value) {
                if ($cond == $type) {
                    $ret = ($value['validate'] != NULL) ? preg_match($regs[$value['validate']], $arg) : TRUE;
                    return $ret;
                }
            }
        }

        return $ret;
    }

    /**
     * calls the model method to delete the given rule
     */
    public function deleteSelectedRule($rule = FALSE) {
        $clientid = $this->session->userdata('sessionUserId');
        $this->optimisation->flushAPCCacheForRule();
        $this->ruleid = $rule ? $rule : $this->input->post('ruleid');
        $this->optimisation->flushAPCCacheForRule($this->ruleid);
        $this->persomodel->perso_deleteRule($this->ruleid, $clientid);
    }

    /**
     * calls the corresponding model method to delete the given condition by its id
     */
    public function deleteSelectedCondition($cond = FALSE) {
        $clientid = $this->session->userdata('sessionUserId');
        $cid = $cond ? $cond : $this->input->post('idc');
        $this->persomodel->perso_deleteCondition($cid, $clientid);
    }

    /**
     * if the country names array is saved in the cache, it returns it inmediately, or else, save all country names
     * into an array, save it into the cache and returns it to the JS method via AJAX
     */
    public function getGeoCountries() {
        $lang = $this->config->item('language') == 'german' ? 'de' : 'en';
        $paramLang = $lang == 'de' ? '&lang=de' : '';
        $geoCountries = dbcache_fetch('geoCountries', $lang);
        if(strlen($geoCountries < 100))
            $geoCountries = false;
        if (!$geoCountries) {
            $geoC = array();
            $json = $this->curl->_simple_call('GET', 'http://api.geonames.org/countryInfoJSON?username=' . $this->config->item('geonames_username') . $paramLang);
            $countries = json_decode($json);

            foreach ($countries->geonames as $country) {
                $geoC[$country->countryName] = array(
                    'geonameId' => $country->geonameId,
                    'code' => $country->countryCode,
                );
            }
            ksort($geoC);
            $geoCountries = json_encode($geoC);

            $ttl = 30 * 24 * 60 * 60;
            dbcache_store('geoCountries', $geoCountries, $ttl, $lang);
        }
        echo $geoCountries;
    }

    /**
     * Given a country ID, a state ID or a region ID, 
     * returns all of its children (states/regions/cities) as an array with its geonameId as key and a sub-array with 
     * its names and the flag "hasChildren" as values.
     * The "hasChildren" flag is useful when we are retrieving states childrens to determine if those are cities or counties/regions
     */
    public function getGeoChildren() {
        $geonameId = $this->input->get('geonameId');
        $lang = $this->config->item('language') == 'german' ? 'de' : 'en';
        $paramLang = $lang == 'de' ? '&lang=de' : '';

        $geoChildren = dbcache_fetch($geonameId, $lang);

        if (!$geoChildren) {
            $uname = 'username=' . $this->config->item('geonames_username');
            $json = $this->curl->_simple_call('GET', 'http://api.geonames.org/childrenJSON?geonameId=' . $geonameId . '&maxRows=1000&' . $uname . $paramLang);

            $childrens = json_decode($json);

            $geoC = NULL;
            if (count($childrens->geonames) > 0) {
                $firstChild = $childrens->geonames[0]->geonameId;
                $sub = $this->curl->_simple_call('GET', 'http://api.geonames.org/childrenJSON?geonameId=' . $firstChild . '&maxRows=1&' . $uname);
                $childs = json_decode($sub);
                $hasChildren = count($childs->geonames) > 0 ? 1 : NULL;

                $geoC = array();
                foreach ($childrens->geonames as $children) {
                    $geoC[$children->name] = $children->geonameId;
                }
                ksort($geoC);
            }

            $ret = json_encode(array('geoChildren' => $geoC, 'hasChildren' => $hasChildren));

            $ttl = 30 * 24 * 60 * 60;
            dbcache_store($geonameId, $ret, $ttl, $lang);
            echo $ret;
        } else {
            $geo = json_decode($geoChildren, TRUE);
            foreach ($geo['geoChildren'] as $key => $value) {
                if (!is_numeric($key) && is_numeric($value)) {
                    echo $geoChildren;
                    return;
                }
                break;
            }
            $geo['geoChildren'] = array_flip($geo['geoChildren']);
            ksort($geo['geoChildren']);
            echo json_encode($geo);
        }
    }

}
