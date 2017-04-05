<?php

class ue extends CI_Controller {

    public $queryparam;
    private $sdk;

    function __construct() {
        parent::__construct();
        doAutoload();
        $this->load->library('curl');
        $this->load->model('mvt');
        $this->load->model('smsmodel');
        $this->load->model('landingpagecollection');
        $this->load->model('optimisation');
        $this->load->model('user');
        $this->load->helper('apiv1');
        $this->load->helper('allocation');
        $this->load->helper('goals');
        $this->load->library('Dbconfiguration');

        define('API_URL', $this->config->item('base_ssl_url') . 'api/v1/', TRUE);
        require_once APPPATH . 'controllers/apiv1sdk.php';
        $this->sdk = new apiv1sdk();
        $this->sdk->__set('clientid', $this->session->userdata('sessionUserId'));
        $this->sdk->__set('apikey', $this->config->item('bt_apikey'));
        $this->sdk->__set('apisecret', $this->config->item('bt_apisecret'));
    }

    function index() {
        // read all parameters
        $url = $this->input->get('url');
        $data['url'] = $this->fixUrlName($url);
        $data['domain'] = $this->getDomainFromUrl($url);

        $html = $this->getUrlHtml($url);
        $data['html'] = $html["html"];
        $data['head_tag'] = $html['head_tag'];
        $data['head'] = $html['head'];
        $data['body_tag'] = $html['body_tag'];
        $data['body'] = $html['body'];

        $data['contenttype'] = $html["ct"];
        $data['factors'] = $this->getfactors();
        $this->load->view("protected/editor", $data);
    }

    //loads all factors from session
    function fl() {
        $url = $this->input->get('url');
        $factors = $this->getfactors();
        echo json_encode($factors);
    }

    //opens up factor editor window based on jquery dom path
    function fc($qp = '') {
        $qp = urldecode($qp);
        $qp = str_replace("{s}", " ", $qp);
        $qp = str_replace("{d}", "#", $qp);
        $qp = str_replace("{o}", "(", $qp);
        $qp = str_replace("{c}", ")", $qp);

        $lpc = $this->session->userdata("lpc");
        $factor = $lpc["factors"][$qp];
        $data["qp"] = $qp;
        $data["factor"] = $factor;
        $this->load->view("protected/editor_factor", $data);
    }

    //remove a factor based on jquery dom path
    function fd() {
        $qp = urldecode($this->input->post("qp"));
        $lpc = $this->session->userdata("lpc");
        $factors = $lpc["factors"];
        if (!is_array($factors))
            $factors = array();
        $tmpfactors = array();
        //remove factor
        foreach ($factors as $key => $factor) {
            if (trim($key) != trim($qp))
                $tmpfactors[$key] = $factor;
        }
        //save back to factors
        $lpc["factors"] = $tmpfactors;
        //save back to session
        $this->session->set_userdata("lpc", $lpc);
    }

    //save a factor into session
    function fs() {

        $qp = urldecode($this->input->post("qp"));
        $fname = $this->input->post("fname");
        $mvt_level_ids = $this->input->post("mvt_level_id");
        $variants = $this->input->post("variant", false);
        $variantnames = $this->input->post("variantname");
        if (!is_array($mvt_level_ids))
            $mvt_level_ids = array();

        //get factor
        $lpc = $this->session->userdata("lpc");
        $factor = $lpc["factors"][$qp];
        //save name
        $factor["name"] = $fname;
        $factor["id"] = $factor["id"] * 1; //0 if new
        //save factor data
        $levels = array();
        for ($i = 0; $i < count($mvt_level_ids); $i++) {
            $level = array();
            $level["n"] = $variantnames[$i];
            $level["v"] = $this->mvt->filterVariantCode(html_entity_decode($variants[$i]));
            $level["i"] = $mvt_level_ids[$i];
            $levels[] = $level;
        }
        $factor["levels"] = $levels;
        $lpc["factors"][$qp] = $factor;
        $this->session->set_userdata("lpc", $lpc);
        //generate change code
        echo json_encode($factor);
    }

    //new collection init and tracking code
    function nc() {
        //get tracking code
        $tk = $this->mvt->getLPCTrackingCode(0);
        $url = $this->input->get('url');
        $tk["canonical_url"] = canonicalUrl($url);
        $tk["client_code_exists"] = $this->checkUrlClientCode($url);

        //if( !$this->session->userdata("lpc") ) //this was used for testing purpose
        $this->session->set_userdata("lpc", array("url" => $url, "lpcid" => 0, "lpccode" => $tk["lpccode"], "factors" => array()));
        echo json_encode($tk);
    }

    //new collection AB test init and tracking code
    function ncab() {
        $url = $this->input->get('url');
        $lpcid = $this->input->get('lpcid');
        $clientid = $this->session->userdata('sessionUserId');

        $lpc = $this->optimisation->getcollectionstatusById($lpcid);

        dblog_message(LOG_LEVEL_INFO, LOG_TYPE_EDITCOLLECTION, "testgoal:" . $lpc["testgoal"], $clientid);

        //get tracking code
        $tk = $this->mvt->getLPCTrackingCode($lpcid, OPT_TESTTYPE_SPLIT);
        $tk["testgoal"] = $lpc["testgoal"];

        //if( !$this->session->userdata("lpc") ) //this was used for testing purpose
        $this->session->set_userdata("lpc", array("url" => $url, "lpcid" => 0, "lpccode" => $tk["lpccode"], "testgoal" => $lpc["testgoal"], "factors" => array()));
        echo json_encode($tk);
    }

    //load landing page, it's used with 
    function lc($thisclientid = -1, $lpcid = 0) {

        $clientid = getClientIdForAction($thisclientid);
        $testdata = $this->mvt->loadlandingpagecollectiondetails($clientid, $lpcid);

        $tk = $this->mvt->getLPCTrackingCode($lpcid, OPT_TESTTYPE_VISUALAB); //OPT_TESTTYPE_VISUALAB this parameter is usualy ignored in the function body
        $trackingcodedata = array(
            'lpctrackingcode_control' => $tk['lpctrackingcode_control'], 'lpctrackingcode_success' => $tk['lpctrackingcode_success'], 'successpage' => $tk['successpage'], 'testgoal' => $tk['testgoal'], 'testname' => $tk['testname'], 'lpccode' => $tk['lpccode'], 'lpctrackingcode_variant' => $tk['lpctrackingcode_variant'], 'lpctrackingcode_ocpc' => $tk['lpctrackingcode_ocpc'],
            'tracking_approach' => $testdata->tracking_approach,
            'tracked_goals' => $this->mvt->getCovertedTrackedGoals($testdata->tracked_goals),
            'testtype' => $testdata->testtype
        );

        echo json_encode($trackingcodedata);
    }

    /*     * **************************************************************************************** */

    /**
     * gets all parameters sent from the JS front end and depending on the savestep calls the corresponding
     * methods to create/update the project, the variants and the project goals
     * 
     * @return void - if everything goes well, echos the path containing the LPC id and the clientid (341/2)
     */
    public function ls() {
        $lpcid = $this->input->get("isnew") == 'yes' ? 0 : $this->session->userdata("editor_collectionid");
        $clientid = getClientIdForActionFromUrl();
        $savestep = $this->input->post('savestep');
        $persolevel = getPersoLevel();

        if (!is_numeric($clientid) || $clientid < 1) {
            return;
        }

        if ($savestep == "" || $savestep == NULL) {
            $savestep = "all";
        }

        try {
            $isMpt = $this->input->post('lpc_isMpt') == 'true';

            if ($savestep == 'allocation') {
                saveProjectAllocation($lpcid, $this->sdk);
                echo $lpcid . "/" . $clientid . "/";
                return FALSE;
            }

            if ($savestep == 'all' || $savestep == 'approach') {
                $lpcid = self::createOrUpdateVisualProject($persolevel, $lpcid);
            }

            if ($savestep != 'all' && $this->input->post('device_type')) {
                $project = array(
                    'devicetype' => $this->input->post('device_type')
                );
                self::createOrUpdateVisualProject(FALSE, $lpcid, $project);
            }

            if ($savestep != 'all' && $this->input->post('testurl')) {
                $project = array(
                    'mainurl' => $this->input->post('testurl'),
                );
                self::createOrUpdateVisualProject(FALSE, $lpcid, $project);
            }

            if (($savestep == 'all' || $savestep == 'variants') && $isMpt) {
                self::createOrUpdateDecisionGroupsForMpt($lpcid, $persolevel, $savestep);
            } else if ($savestep == 'all' || $savestep == 'variants') {
                self::saveProjectVariants($lpcid, $persolevel);
            }

            if ($savestep == 'variants' && !$isMpt) {
                self::setGoalsInEditMode($lpcid, -1, -1);
            }

            if (($savestep == 'all' && !$isMpt) || $savestep == 'goals') {
                saveProjectGoals($this->sdk, $lpcid);
            }

            $this->mvt->fixProxyBug($lpcid);
            $this->session->unset_userdata('clickGoals');
            $this->session->unset_userdata('goalsToDelete');

            echo $lpcid . "/" . $clientid . "/";
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * This method builds the project array depending on the savestep, if the LPC id is already defined, it only 
     * add fields that are available in create and edit mode like name, runpattern, startdate, etc.
     * If the LPC id is 0, calls the  addCreateProjectFields() method to add the rest of fields that are availabel ONLY 
     * in "create" mode like, testtype, mainurl, persomode and control RULE id
     * 
     * A third parameter could be sent (specifically from "saveProjectVariants") with the field "devicetype" because
     * when editing variants, the editor is the only place where users can edit that value.
     * 
     * @param String $persolevel - either "available" or "disabled" depending on the userplan
     * @param Int $lpcid - The LPC id to be updated or 0 if the project is new
     * @param Array $customfields - May contain a custom $project array with certain fields to be updated
     * @return Int - The new LPC id or the current LPC id (dependin on if "lpcid" was 0 or >0
     */
    private function createOrUpdateVisualProject($persolevel, $lpcid = 0, $customfields = FALSE) {
        if ($customfields) {
            $this->sdk->updateProject($lpcid, $customfields);
            return;
        }

        $allocation = $this->input->post("allocation");

        if ($this->input->post("lpc_start_date")) {
            $start_date = date('Y-m-d H:i:s', strtotime($this->input->post("lpc_start_date") . ' ' . $this->input->post("lpc_start_time")));
            $end_date = date('Y-m-d H:i:s', strtotime($this->input->post("lpc_end_date") . ' ' . $this->input->post("lpc_end_time")));
        } else {
            $start_date = date('Y-m-d H:i:s');
            $end_date = '2020-12-31 23:00:00';
        }

        $runpattern = setRunPatternArray($this->input->post('control_pattern'), $this->input->post('url_include'));

        $project = array(
            'name' => $this->input->post("testname"),
            'runpattern' => $runpattern,
            'allocation' => (!$allocation || $allocation < 0 || $allocation > 1) ? 100 : $allocation * 100,
            'startdate' => $start_date,
            'enddate' => $end_date,
            'ipblacklisting' => $this->input->post('ignore_ip_blacklist') == 0 ? TRUE : FALSE,
            'DEFERRED_IMPRESSION_SELECTOR' => $this->input->post('additional_dom_selector'),
            'DEFERRED_IMPRESSION_SELECTOR_ACTION' => $this->input->post('additional_dom_action'),
            'IP_FILTER_IPLIST' => $this->input->post('ip_filter_list'),
            'IP_FILTER_ACTION' => $this->input->post('ip_filter_action'),
            'IP_FILTER_SCOPE' => $this->input->post('ip_filter_scope')
        );

        if ($this->input->post('device_type')) {
            $project['devicetype'] = $this->input->post('device_type');
        }

        if ($lpcid == 0) {
            $p = self::addCreateProjectFields($project, $persolevel);
            $project = $this->dbconfiguration->saveProjectConfigurationToDatabase($p, FALSE);
            $lpcid = $this->sdk->createProject($project);
            return $lpcid;
        } else {
            $old_project = $this->sdk->getProject($lpcid);
            $project = $this->dbconfiguration->saveProjectConfigurationToDatabase($project, $old_project->config);
            $this->sdk->updateProject($lpcid, $project);
            return $lpcid;
        }
    }

    /**
     * If the testtype us MPT we need to update or create the corresponing groups
     * @param int $lpcid
     * @param int $persolevel
     * @param String $savestep either 'all' or 'variants' - to check if we should save the goals or not
     * @return int
     */
    private function createOrUpdateDecisionGroupsForMpt($lpcid, $persolevel, $savestep) {
        $variantData = json_decode($this->input->post('variantdata'));
        $pages = $variantData->pages;
        $count = 0;

        $oldGroups = array();
        $newGroups = array();
        $previousGroups = $this->sdk->getDecisionGroups($lpcid);

        foreach ($previousGroups as $pregroup) {
            array_push($oldGroups, $pregroup->id);
        }

        foreach ($pages as $ind => $page) {
            $pName = preg_replace('/\s+/u', '', $page->name);
            $runPattern = $this->input->post('mpt_' . $pName);
            $group = array(
                'isMpt' => TRUE,
                'name' => isset($page->name) ? $page->name : 'G' . $count,
                'mainurl' => $page->url,
            );

            if (in_array($page->id, $oldGroups)) {
                array_push($newGroups, $page->id);
                $this->sdk->updateDecisionGroup($lpcid, $page->id, $group);
                $gid = $page->id;
                if ($savestep != 'variants') {
                    saveProjectGoals($this->sdk, $lpcid, $gid, $gid);
                } else {
                    self::setGoalsInEditMode($lpcid, $gid, $gid);
                }
            } else {
                $group['runpattern'] = $runPattern ? $runPattern : $page->url;
                $gid = $this->sdk->createDecisionGroup($lpcid, $group);
                $this->sdk->startDecisionGroup($lpcid, $gid);
                if ($savestep != 'variants') {
                    saveProjectGoals($this->sdk, $lpcid, $gid, $ind);
                } else {
                    self::setGoalsInEditMode($lpcid, $gid, $ind);
                }
            }

            self::saveProjectVariants($lpcid, $persolevel, $gid, $group['name']);
            $count ++;
        }

        $unusedGroups = array_diff($oldGroups, $newGroups);
        foreach ($unusedGroups as $ug) {
            $this->sdk->deleteDecisionGroup($lpcid, $ug);
        }

        self::saveProjectVariants($lpcid, $persolevel);
        $this->session->unset_userdata('otherGoalsSaved');
        return $lpcid;
    }

    /**
     * When creating a new project, it is necessary to add required fields as well as RW fields that are set only on "create" mode
     * like type, mainurl, persomode and control rule ID (if any)
     * @param array $project - the current project array to be inserted with name, runpattern, startdate, etc..
     * @param String $persolevel - either "available" or disabled
     */
    private function addCreateProjectFields($project, $persolevel) {
        $variantData = json_decode($this->input->post("variantdata"));

        $persomode = $this->input->post("perso-type-selection") != 0 ? $this->input->post("perso-type-selection") : 'NONE';
        if ($persomode != 'NONE') {
            $persomode = $persomode == 1 ? 'COMPLETE' : 'SINGLE';
        }
        $cRule = $this->input->post("perso-complete-ruleid");
        $controlRule = ($persomode == 'COMPLETE' && (int) $cRule > 0 && $persolevel != 'disabled') ? $cRule : 0;
        $isMpt = $this->session->userdata('isMpt') ? TRUE : FALSE;

        $project['type'] = $isMpt ? 'MULTIPAGE' : 'VISUAL';
        $project['mainurl'] = $isMpt ? $variantData->pages->page_1->url : $this->input->post("testurl");
        $project['personalizationmode'] = ($persolevel == 'disabled') ? 'NONE' : $persomode;
        $project['ruleid'] = $controlRule;
        return $project;
    }

    /**
     * Retrieves a list of previous variants for the given LPC to compare later with used variants and delete non used ones,
     * if there are variants to be saved=>
     *      if the variant does not have an ID (is new), creates a new variant via the API
     *      then updates the old or the new variant with the ruleid(if available), the JScode and CSScode
     *      then compares the oldvariants array with the newvariants array to determine which variants has to be deleted
     * @param Int $lpcid - the project ID
     * @param String $persolevel - "available" or "disabled" dependin on the client's userplan
     * @param Int $gid - the group id
     */
    private function saveProjectVariants($lpcid, $persolevel, $gid = -1, $gname = '') {
        $savestep = $this->input->post('savestep');
        $isMpt = $this->input->post('lpc_isMpt') == 'true';
        $variantData = json_decode($this->input->post("variantdata"));
        $smartmessage = (isset($variantData->isNewSMSTest) && $variantData->isNewSMSTest) ? 1 : 0;
        $previousVariants = $this->sdk->getDecisions($lpcid, FALSE, $gid);
        $oldVariants = array();
        $newVariants = array();
        $totalVariants = 1;
        $smsData = array();
        $project = $this->sdk->getProject($lpcid);

        $allocations = array();
        $sumAllocation = 0;

        $ctrlAllocation = 0;
        foreach ($previousVariants as $variant) {
            if ($variant->type == 'CONTROL') {
                $ctrlAllocation = $variant->allocation;
                break;
            }
        }

        if ($ctrlAllocation > 0) {
            $allocations[$project->originalid] = $ctrlAllocation;
            $sumAllocation += $ctrlAllocation;
        }

        $this->smsmodel->updateSmsInProject($lpcid, $smartmessage);

        foreach ($previousVariants as $prevariant) {
            if ($prevariant->type == 'VARIANT') {
                $totalVariants ++;
                array_push($oldVariants, $prevariant->id);

                if ($prevariant->allocation > 0) {
                    $allocations[$prevariant->id] = $prevariant->allocation;
                    $sumAllocation += $prevariant->allocation;
                }
            }
        }

        foreach ($variantData->pages as $idp => $page) {
            if ($gid != -1 && $gname != $page->name) {
                continue;
            }

            foreach ($page->variants as $idv => $variant) {
                if ((int) $variant->id == 0) {
                    $decision = array(
                        'name' => $variant->name,
                    );

                    if ($savestep == 'variants') {
                        $alloc = $sumAllocation == $totalVariants ? 1 : 0;
                        $decision['allocation'] = $alloc;
                    }

                    $variant->id = (int) $this->sdk->createDecision($lpcid, $decision, $gid);
                } else if ($this->input->post('lpc_isMpt') == 'true' && $gid == -1 && isset($variant->variantindex)) {
                    foreach ($previousVariants as $prevariant) {
                        if ($prevariant->variantindex == $variant->variantindex) {
                            $variant->id = $prevariant->id;
                        }
                    }
                }

                $jscode = $variant->dom_modification_code->{'[JS]'};
                $csscode = $variant->dom_modification_code->{'[CSS]'};

                $decision = array(
                    'name' => $variant->name,
                    'jsinjection' => $jscode,
                    'cssinjection' => $csscode,
                );

                if (!$isMpt || ($isMpt && $savestep == 'all')) {
                    // retrieve the personalization rule from the variant section in the POST ($variantData)
                    $projectVariant = $variantData->variants->{$idv};
                    $ruleid = ($projectVariant->persorule != null && $persolevel != 'disabled') ? $projectVariant->persorule->id : 0;
                    $decision['ruleid'] = $ruleid;
                }

                $this->sdk->updateDecision($lpcid, $variant->id, $decision, $gid);
                array_push($newVariants, $variant->id);

                if ($smartmessage == 1) {
                    $smsData = $this->smsmodel->updateVariantWithSms($lpcid, $variant, $smsData);
                }
            }

            if ($gid == -1) {
                break;
            }
        }

        $doUpdate = $sumAllocation != $totalVariants;
        self::deleteVariantsUpdateAllocation($project, $ctrlAllocation, $allocations, $newVariants, $oldVariants, $doUpdate, $gid);
    }

    /**
     * First it deleted unused variants.
     * 
     * If the allocation array is empty, that means that all previous variants with allocation != 0 has been deleted.
     *      Then, updates all NEW variants setting the allocation = 1.
     *      As supposed, previous variants that has not being deleted has an allocation = 0, they are kept the same...
     * 
     * If the allocation array is not empty, then  it divides the deleted variants allocation between all
     * previous variants (which has an allocation != 0) and update each of them 
     * @param object $project
     * @param array $allocations
     * @param array $newVariants
     * @param array $oldVariants
     * @param bool $doUpdate
     * @param int $gid
     */
    private function deleteVariantsUpdateAllocation($project, $ctrlAllocation, $allocations, $newVariants, $oldVariants, $doUpdate, $gid = -1) {
        $lpcid = $this->session->userdata("editor_collectionid");
        $unusedVariants = array_diff($oldVariants, $newVariants);
        $unusedAllocations = self::deleteUnusedVariants($lpcid, $gid, $unusedVariants, $allocations);

        if (!$doUpdate) {
            return FALSE;
        }

        if (count($allocations) == 0) {
            foreach ($newVariants as $lpd) {
                if (!in_array($lpd, $oldVariants)) {
                    $decision = array(
                        'allocation' => 1,
                    );
                    $this->sdk->updateDecision($lpcid, $lpd, $decision);
                }
            }
        } else if ($unusedAllocations > 0) {
            $divisor = count($oldVariants) - count($unusedVariants);
            $divisor += $ctrlAllocation > 0 ? 1 : 0;
            $splitted = $unusedAllocations / $divisor;

            if ($ctrlAllocation > 0) {
                $decision = array(
                    'allocation' => ($ctrlAllocation + $splitted),
                );
                $this->sdk->updateDecision($lpcid, $project->originalid, $decision);
            }

            foreach ($newVariants as $lpd) {
                if (in_array($lpd, $oldVariants)) {
                    $variant = $this->sdk->getDecision($lpcid, $lpd);
                    $decision = array(
                        'allocation' => ($variant->allocation + $splitted),
                    );
                    $this->sdk->updateDecision($lpcid, $lpd, $decision);
                }
            }
        }
    }

    /**
     * Deletes previous saved variants that has been "deleted" in the editor
     * @param int $lpcid
     * @param int $gid
     * @param array $unusedVariants
     * @param array $allocations
     */
    private function deleteUnusedVariants($lpcid, $gid, $unusedVariants, &$allocations) {
        $unusedAllocations = 0;
        foreach ($unusedVariants as $uv) {
            if (in_array($uv, array_keys($allocations))) {
                $unusedAllocations += $allocations[$uv];
                unset($allocations[$uv]);
            }
            $this->sdk->deleteDecision($lpcid, $uv, $gid);
        }

        return $unusedAllocations;
    }

    /**
     * When editing a project (Original and variants) every click goal created in the editor
     * have to be assigned to the current project when saving the changes.
     * This function emulates the array of goals that is sent from the form "conversion goals" and then
     * calls the "saveProjectGoals"
     * @param Int $lpcid - The project ID
     * @param int $groupid - If it is an MPT
     * @param int $groupindex - see the foreach loop in createOrUpdateDecisionGroupsForMpt()
     */
    private function setGoalsInEditMode($lpcid, $groupid = -1, $groupindex = FALSE) {
        $sessionGoals = $this->session->userdata('clickGoals');
        $goalsToDelete = $this->session->userdata('goalsToDelete');
        $foundGroupId = FALSE;

        foreach ($sessionGoals as $g) {
            if ($g['lpcid'] == $lpcid && $g['pageid'] == $groupindex && !$g['goalid']) {
                $foundGroupId = TRUE;
                break;
            }
        }

        if ((!$sessionGoals || !$foundGroupId) && !$goalsToDelete) {
            return FALSE;
        }

        $ids = array();
        $types = array();
        $levels = array();
        $clickNames = array();
        $clickPages = array();
        $clickSelectors = array();
        $clicksDeleted = array();
        $savedGoals = $this->sdk->getGoals($lpcid);

        $remainingGoals = (count($savedGoals) + count($sessionGoals)) - count($goalsToDelete);

        $index = 0;
        foreach ($savedGoals as $goal) {
            $gType = array_search($goal->type, $this->config->item('api_goals'));
            $gLevel = $goal->level;

            if ($goal->page == $groupid && $gType == GOAL_TYPE_CLICK) {
                $arg = json_decode($goal->param);
                $deleteThisGoal = FALSE;

                foreach ($goalsToDelete as $del) {
                    if ($del['lpcid'] == $lpcid && $del['pageid'] == $groupid && $del['selector'] == $arg->selector) {
                        $deleteThisGoal = TRUE;
                        break;
                    }
                }

                foreach ($sessionGoals as $g) {
                    if ($g['goalid'] == $goal->id) {
                        $goal->name = $g['name'];
                        $arg->selector = $g['selector'];
                    }
                }

                if (!$deleteThisGoal || ($remainingGoals <= 0 && $gLevel == 'PRIMARY')) {
                    $ids[] = $goal->id;
                    $types[] = 'CLICK';
                    $levels[] = $goal->level;
                    $clickNames[] = $goal->name;
                    $clickSelectors[] = $arg->selector;
                    $clickPages[] = is_int($goal->group) && $goal->group >= 1 ? $g['pageid'] : '-1';
                } else if ($deleteThisGoal) {
                    $clicksDeleted[] = $goal->id;
                }
            }

            $index ++;
        }

        foreach ($sessionGoals as $g) {
            if ($g['lpcid'] == $lpcid && $g['pageid'] == $groupindex && !$g['goalid']) {
                $ids[] = false;
                $types[] = 'CLICK';
                $levels[] = 'SECONDARY';
                $clickNames[] = $g['name'];
                $clickSelectors[] = $g['selector'];
                $clickPages[] = $g['pageid'];

                $index ++;
            }
        }

        $customPost = array(
            'ids' => $ids,
            'types' => $types,
            'levels' => $levels,
            'clickNames' => $clickNames,
            'clickPages' => $clickPages,
            'clickSelectors' => $clickSelectors,
            'clicksDeleted' => $clicksDeleted,
        );

        if ($index >= 1) {
            saveProjectGoals($this->sdk, $lpcid, $groupid, $groupindex, $customPost);
        }
    }

    /*     * **************************************************************************************** */

    function cn() {
        $validateValue = $this->input->get('fieldValue');
        $validateId = $this->input->get('fieldId');
        $clientid = $this->session->userdata('sessionUserId');
        /* RETURN VALUE */
        $arrayToJs[0] = $validateId;
        //check for unique valid test name

        $arrayToJs[1] = $this->mvt->checktestname($validateValue, $clientid) ? "true" : "false";
        echo '["' . $arrayToJs[0] . '", ' . $arrayToJs[1] . ']';  // RETURN ARRAY WITH ERROR
    }

    /*
     * 
     * controller for read all parameters that are passed
     */

    function getfactors($url) {
        $lpc = $this->session->userdata("lpc");
        $factors = $lpc["factors"];
        if (!is_array($factors))
            $factors = array();
        return $factors;
    }

    function readparam() {
        $url = $this->input->server('REQUEST_URI');
        $parts = parse_url($url);
        parse_str($parts['query'], $allqueryparam);
        return $allqueryparam;
    }

// function readparam ends here

    /*
     * 
     * controller for validating domains
     */

    function domain() {
        $validateValue = $this->input->get('fieldValue');
        $validateId = $this->input->get('fieldId');

        $domain = $this->getDomainFromUrl($validateValue);
        /* RETURN VALUE */
        $arrayToJs[0] = $validateId;
        $arrayToJs[1] = (gethostbyname($domain) == $domain) ? "false" : "true";
        echo '[' . json_encode($arrayToJs[0]) . ', ' . $arrayToJs[1] . ']';  // RETURN ARRAY WITH ERROR		
    }

    function fixUrlName($url) {
        if (substr($url, 0, 4) != "http") {
            if (substr($url, 0, 4) != "www.")
                $url = "http://www." . $url;
            else
                $url = "http://" . $url;
        }
        return($url);
    }

    function checkUrlClientCode($url) {
        $html = $this->curl->simple_get($url);
        //get client hash
        $clientid = $this->session->userdata('sessionUserId');
        $userdata = $this->user->clientdatabyid($clientid);
        if (stripos($html, $userdata['clientid_hash']) !== false)
            return 'true';
        return 'false';
    }

    function getUrlHtml($url) {
        $html = $this->curl->simple_get($url);
        $ct = $this->curl->info['content_type'];
        $urldata = parse_url($url);
        $domain = $urldata["host"];

        $replaces = array(
            '/<!DOCTYPE[^>]+>/ix' /* remove doctype */,
            '/<html[^>]+>/ix', '/<\/html>/ix' /* remove html tag */
        );
        //replaces
        $html = preg_replace($replaces, '', $html);
        //other fixes
        $html = str_ireplace("src=\"/", "src=\"http://$domain/", $html);
        $html = str_ireplace("href=\"/", "href=\"http://$domain/", $html);
        $html = str_ireplace("src='/", "src='http://$domain/", $html);
        $html = str_ireplace("href='/", "href='http://$domain/", $html);
        //replace from javascript
        $html = str_ireplace("='/", "='/http://$domain/", $html);

        //get head content
        preg_match_all('@(<head[^>]*?>)(.*?)</head>@six', $html, $matches);
        $head_all = $matches[0][0];
        $head_tag = $matches[1][0];
        $head = $matches[2][0];

        //filter jquery word
        $head = str_ireplace("jquery-", "jqdummy", $head);
        $head = str_ireplace("popup.js", "jqdummy.js", $head);

        //get body content
        $body_all = str_ireplace(array($head_all, "</head>"), "", $html);
        preg_match_all('@<body[^>]*?>@six', $body_all, $matches);
        $body_tag = $matches[0][0];
        ;
        $body = str_ireplace(array($body_tag, '</body>'), "", $body_all);

        $data["head_tag"] = $head_tag;
        $data["head"] = $head;
        $data["body_tag"] = $body_tag;
        $data["body"] = $body;

        $data["ct"] = $ct;
        return $data;
    }

    function getDomainFromUrl($url) {
        $urldata = parse_url($url);
        return($urldata["host"]);
    }

}

?>