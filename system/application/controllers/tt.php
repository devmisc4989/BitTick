<?php

/**
 * Controller to handle all teaser tests related function (CRUD)
 */
class tt extends CI_Controller {

    private $sdk;

    function __construct() {
        parent::__construct();
        doAutoload();

        $this->load->model('landingpagecollection');
        $this->load->model('mvt');
        $this->load->library('Dbconfiguration');
        $this->load->helper('apiv1');
        $this->load->helper('tt');

        define('API_URL', $this->config->item('base_ssl_url') . 'api/v1/', TRUE);
        require_once APPPATH . 'controllers/apiv1sdk.php';
        $this->sdk = new apiv1sdk();
        $this->sdk->__set('clientid', $this->session->userdata('sessionUserId'));
        $this->sdk->__set('apikey', $this->config->item('bt_apikey'));
        $this->sdk->__set('apisecret', $this->config->item('bt_apisecret'));
    }

    /**
     * No direct access without a function name, it redirects to the list of collections
     */
    function index() {
        redirect($this->config->item('base_ssl_url') . "lpc/cs/");
    }

    public function getTeaserTestInjectionCode() {
        $this->load->view('includes/tt_injection');
    }

    /**     * ************************************************************************************************
     * ******************** Teaser test Overview - LIST - Related functions ****************************
     * ******************************************************************************************************
     * Get the data necessary to display the new TT overview page.
     * @param int $lpcid - The collection ID
     */
    public function tt_overview($lpcid) {
        $this->lang->load('landingpagecollectiondetails');
        $this->lang->load('collectionoverview');
        $this->lang->load('welcome');
        $clientid = getClientIdForAction(FALSE);

        $baseurl = $this->config->item('base_url');
        $basesslurl = $this->config->item('base_ssl_url');

        if ($clientid) {
            try {
                $this->sdk->__set('clientid', $clientid);
                $project = $this->sdk->getProject($lpcid);
                $groups = self::tt_overview_getGroups($lpcid, $project);
                $no_tests = false;
                if (sizeof($groups) == 0) {
                    $no_tests = true;
                }

                $start_stop_id = 'tt_start';
                $start_stop_label = $project->visitors > 0 ? $this->lang->line('link_play') : $this->lang->line('link_start');
                if ($project->status == 'RUNNING') {
                    $start_stop_id = 'tt_stop';
                    $start_stop_label = $this->lang->line('link_pause');
                }

                $sd = strtotime($project->startdate);
                $ed = strtotime($project->enddate);
                $project->startdate = date('d.m.Y H:i:s', $sd);
                $project->enddate = date('d.m.Y H:i:s', $ed);

                $interface = FALSE;
                if (isset($project->config)) {
                    $config = json_decode($project->config);
                    $interface = $config->TT_INTERFACE_TYPE;
                }

                $data = self::tt_overview_headerData();
                $data += array(
                    'isTT' => TRUE,
                    'interface' => $interface,
                    'clientid' => $clientid,
                    'baseurl' => $baseurl,
                    'basesslurl' => $basesslurl,
                    'project' => $project,
                    'control_pattern' => $project->runpattern,
                    'groups' => $groups,
                    'no_tests' => $no_tests,
                    'start_stop_id' => $start_stop_id,
                    'start_stop_label' => $start_stop_label,
                    'has_timer' => hasCampaignTimer(),
                    'variantsdata' => 0,
                    'tracking_approach' => 0,
                    'trackingcodedata' => 0,
                    'tracked_goals' => 0,
                    'collectionid' => $lpcid,
                    'groupid' => 0,
                    'conversionGoalsParams' => $this->landingpagecollection->getEtrackerConversionGoalsParamValues(),
                );

                $tt_data = array(
                    'original' => array(0),
                    'variants' => array(0),
                );

                $html = $this->load->view('includes/protected_header', $data, TRUE);
                $html .= $this->load->view('protected/teasertestoverview', $data, TRUE);
                $html .= $this->load->view('client-testpage-popups/teasertests_popups.php', $tt_data, TRUE);
                $html .= $this->load->view('protected/test-page-add-ons.php', $data, TRUE);
                $html .= $this->load->view('includes/public_footer', $data, TRUE);

                if ($html) {
                    echo($html);
                    return;
                }
            } catch (Exception $ex) {
                $msg = $ex->getCode() . ', ' . $ex->getMessage();
                dblog_debug($msg);
            }
        }

        $this->session->set_userdata('login_targetpage', 'lpc/tto/' . $lpcid);
        lang_redirect('login');
    }

    /**
     * @return the css and js array to add to be added to the array of data to load the views
     */
    private function tt_overview_headerData() {
        $basesslurl = $this->config->item('base_ssl_url');
        $data = array(
            'css' => array(
                $basesslurl . 'css/validationEngine.jquery.css',
                $basesslurl . 'css/template.css',
                $basesslurl . 'css/tt-overview.css',
                $basesslurl . 'js/datepicker/css/datepicker.css',
                $basesslurl . 'css/font-awesome/css/font-awesome.min.css',
            ),
            'js' => array(
                $basesslurl . 'jsi18n/validatelogin',
                $basesslurl . 'js/validatelogin.js',
                $basesslurl . 'js/validateplaymode.js',
                $basesslurl . 'jsi18n/jqueryValidationEngine.js',
                $basesslurl . 'js/jquery.validationEngine.js',
                $basesslurl . 'js/resetconnection.js',
                $basesslurl . 'js/jquery.Storage.js',
                $basesslurl . 'js/jquery.json-2.3.js',
                $basesslurl . 'js/jquery.remember-state.js',
                $basesslurl . 'js/datepicker/js/datepicker.js',
                $basesslurl . 'js/dash_testpage_wizards/BT-editor-additional-scripts.js',
                $basesslurl . 'js/BT-common/BT-teasertest.js',
            ),
        );
        return $data;
    }

    /**
     * Get the array with the groups and the corresponding options to be displayed at the bottom
     * of the page.
     * @param Int $lpcid - the project id
     * @param Array $project - the project array of values
     * @return Array
     */
    private function tt_overview_getGroups($lpcid, $project) {
        try {
            $groups = $this->sdk->getDecisionGroups($lpcid);            
        }
        catch (Exception $e) {
            $groups = array();   
        }
        $reverse = array_reverse($groups);
        $group_data = array();

        $config = $this->dbconfiguration->getDefaultProjectConfiguration();
        if (isset($project->config) && $project->config != NULL) {
            $config = json_decode($project->config, TRUE);
        }

        foreach ($reverse as $group) {
            $group_action = 1;
            $group_label = $this->lang->line('table_tt_action_start');
            $icon_title = $this->lang->line('tooltip_paused');
            $icon_src = 'images/collection_paused.png';

            if ($group->status == 'RUNNING') {
                $group_action = 0;
                $group_label = $this->lang->line('table_tt_action_pause');
                $icon_title = $this->lang->line('tooltip_running');
                $icon_src = 'images/collection_running.png';
            } else if ($group->status == 'PAUSED') {
                $group_label = $this->lang->line('table_tt_action_play');
            }

            $clientid = getClientIdForAction(FALSE);
            $href = $this->config->item('base_ssl_url') . 'lpc/lcd/' . $lpcid . '/' . $clientid . '/-1/' . $group->id;
            $options = getGroupMenuOptions($config['TT_INTERFACE_TYPE'], $group_action, $group_label, $href);
            $result = getGroupResult($group);
            $age = getGroupAge($group->createddate);

            $oClass = ' tto_original ';
            $winner = '';
            if ($group->result == 'WON') {
                $oClass += ' tto_oh ';
                $headlines = $this->sdk->getDecisions($lpcid, FALSE, $group->id);
                foreach ($headlines as $headline) {
                    if ($headline->result == 'WON') {
                        $winner = '<br /><span class="tto_wh">' . mb_strimwidth($headline->name, 0, 45, "...") . '</span>';
                        break;
                    }
                }
            }

            $clicks = self::tt_overview_clicks($lpcid, $group->id);

            $group_data[] = array(
                'id' => $group->id,
                'headline' => '<a href="' . $href . '" class="' . $oClass . '">' . mb_strimwidth($group->name, 0, 45, "...") . '</a>' . $winner,
                'views_clicks' => $group->visitors . ' / ' . $clicks,
                'ctr' => $group->conversionrate . '%',
                'age' => $age,
                'result' => $result,
                'rec_icon' => '<img title="' . $icon_title . '" alt="" src="' . $this->config->item('base_ssl_url') . $icon_src . '">',
                'options' => $options,
            );
        }

        return $group_data;
    }

    /**
     * In order to get the number of clicks for the given group, we need to:
     *  1. Get all the goals for the given project
     *  2. Determine which is the "CLICK" goal
     *  3. Get the list of decisions for the given group filtering by goal id (CLICK goal)
     * @param Int $lpcid - The collection ID
     * @param Int $groupid - The group ID
     * @return int - the result of the element "conversions" in the array of decisions which is the number of clicks per group
     */
    private function tt_overview_clicks($lpcid, $groupid) {
        $total_clicks = 0;
        $click_id = FALSE;
        $goals = $this->sdk->getGoals($lpcid);

        foreach ($goals as $goal) {
            if ($goal->type == 'CLICK' && $goal->status == 'ACTIVE') {
                $click_id = $goal->id;
                break;
            }
        }

        if ($click_id) {
            $filter = 'goalid=' . $click_id;
            $decisions = $this->sdk->getDecisions($lpcid, $filter, $groupid);
            foreach ($decisions as $decision) {
                $total_clicks += (int) ($decision->conversions) > 0 ? $decision->conversions * 1 : 0;
            }
        }

        return $total_clicks;
    }

    /**
     * There is a link in the headlines submenu to edit the original and variants directly in the
     * teaser test overview page, when it is clicked, this function is called via AJAX to return the
     * corresponding headline (group) details including the control and variant names
     * @return JSON
     */
    public function tt_overview_getGroupDetails() {
        $clientid = getClientIdForActionFromUrl(FALSE);
        $groupid = $this->input->post('groupid');
        $lpcid = $this->input->post('collectionid');

        if (!is_numeric($clientid) || $clientid < 1) {
            return $this->config->item('base_ssl_url') . $this->config->item('language_abbr') . '/login/';
        }

        $tt_data = array(
            'original' => array(),
            'variants' => array(),
        );

        try {
            $variantDetails = $this->sdk->getDecisions($lpcid, FALSE, $groupid);

            foreach ($variantDetails as $key => $value) {
                $value->groupid = $groupid; // we need to add the groupid to each decision for the edit-variants-popup
                if ($value->type == 'CONTROL') {
                    $tt_data['original'] = $value;
                } else {
                    $tt_data['variants'][] = $value;
                }
            }

            echo json_encode($tt_data);
        } catch (Exception $ex) {
            dblog_debug('TT_ERROR: ' . $ex->getMessage());
            echo json_encode($tt_data);
        }
        return;
    }

    /**     * *************************************************************************************************
     * ************************************* CREATE NEW TEASER TEST***********************************
     * *******************************************************************************************************
     * creates a new teaser test project, this function is called via AJAX, and it uses API calls to do so.
     * When the project has been created, it returns the URL parameters to redirect the user to the
     * project details page.
     * @return type
     */
    public function tt_create() {
        $clientid = getClientIdForActionFromUrl(FALSE);

        if (!is_numeric($clientid) || $clientid < 1) {
            return $this->config->item('base_ssl_url') . $this->config->item('language_abbr') . '/login/';
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

        $p = array(
            'name' => $this->input->post("testname"),
            'type' => 'TEASERTEST',
            'TT_INTERFACE_TYPE' => $this->input->post("tt_interface_type"),
            'mainurl' => $this->input->post('tt_mainurl'),
            'runpattern' => $runpattern,
            'allocation' => (!$allocation || $allocation < 0 || $allocation > 1) ? 100 : $allocation * 100,
            'startdate' => $start_date,
            'enddate' => $end_date,
            'ipblacklisting' => $this->input->post('ignore_ip_blacklist') == 0 ? TRUE : FALSE,
            'personalizationmode' => 'NONE',
            'ruleid' => 0,
        );
        $project = $this->dbconfiguration->saveProjectConfigurationToDatabase($p);

        try {
            $lpcid = $this->sdk->createProject($project);
            $this->mvt->fixProxyBug($lpcid);
            self::tt_create_goals($lpcid);
            echo $this->config->item('base_ssl_url') . 'lpc/tto/' . $lpcid;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * Automatically creates the 4 required goals for teasertests
     * @param Int $lpcid - The collection ID
     */
    private function tt_create_goals($lpcid) {
        $goals = array(
            array(
                'type' => 'COMBINED',
                'level' => 'PRIMARY',
            ),
            array(
                'type' => 'CLICK',
                'level' => 'SECONDARY',
            ),
            array(
                'type' => 'TIMEONPAGE',
                'level' => 'SECONDARY',
            ),
            array(
                'type' => 'PI_LIFT',
                'level' => 'SECONDARY',
            ),
        );

        foreach ($goals as $goal) {
            $this->sdk->createGoal($lpcid, $goal);
        }
    }

    /**     * *************************************************************************************************
     * ********************************* CREATE OR UPDATE A GROUP **********************************
     * *******************************************************************************************************
     * updates a teaser test project, this function is called via AJAX, and it uses API calls to do so.
     * @return void
     */
    public function tt_group_create_or_update() {
        $clientid = getClientIdForActionFromUrl(FALSE);
        $lpcid = $this->session->userdata('tto_lpcid');

        if (!is_numeric($clientid) || $clientid < 1) {
            return FALSE;
        }

        $headlines = $this->input->post('tt_variant_headlines');
        if (count($headlines) <= 0) {
            echo 'No variants has been provided, No group/variants has been created';
            return;
        }

        try {
            $gid = $this->input->post('tt_groupid');
            $name = $this->input->post('tt_control_headline');
            $used_variants = FALSE;
            $group = array(
                'name' => mb_strimwidth($name, 0, 127, ''),
            );

            if ((int) ($gid * 1 ) > 0) {
                $used_variants = array();
                $gid = $this->input->post('tt_groupid');
                $this->sdk->updateDecisionGroup($lpcid, $gid, $group);
            } else {
                $gid = $this->sdk->createDecisionGroup($lpcid, $group);
                $this->sdk->startDecisionGroup($lpcid, $gid);
            }

            $variant_ids = $this->input->post('tt_variant_id');
            $ind = 0;

            foreach ($headlines as $headline) {
                if (trim($headline) != '') {
                    $lpd = $variant_ids[$ind];
                    $variant = array(
                        'name' => $headline,
                    );

                    if ((int) ($lpd * 1) > 0) {
                        array_push($used_variants, $lpd);
                        $this->sdk->updateDecision($lpcid, $lpd, $variant, $gid);
                    } else {
                        $newLP = $this->sdk->createDecision($lpcid, $variant, $gid);
                        array_push($used_variants, $newLP);
                    }
                }
                $ind ++;
            }

            if ($used_variants && is_array($used_variants)) {
                $old_variants = $this->sdk->getDecisions($lpcid, FALSE, $gid);
                foreach ($old_variants as $variant) {
                    if ($variant->type != 'CONTROL' && !in_array($variant->id, $used_variants)) {
                        $this->sdk->deleteDecision($lpcid, $variant->id, $gid);
                    }
                }
            }

            echo 'OK';
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**     * *************************************************************************************************
     * ************************************* UPDATE  TEASER TEST****************************************
     * *******************************************************************************************************
     * updates a teaser test project, this function is called via AJAX, and it uses API calls to do so.
     * @return void
     */
    public function tt_update() {
        $clientid = getClientIdForActionFromUrl(FALSE);
        $lpcid = $this->session->userdata('tto_lpcid');

        if (!is_numeric($clientid) || $clientid < 1) {
            return $this->config->item('base_ssl_url') . $this->config->item('language_abbr') . '/login/';
        }

        $old_project = $this->sdk->getProject($lpcid);
        $old_config = $old_project->config;
        $allocation = $this->input->post("project_allocation");
        $start_date = date('Y-m-d H:i:s', strtotime($this->input->post("lpc_start_date") . ' ' . $this->input->post("lpc_start_time")));
        $end_date = date('Y-m-d H:i:s', strtotime($this->input->post("lpc_end_date") . ' ' . $this->input->post("lpc_end_time")));
        $runpattern = setRunPatternArray($this->input->post('control_pattern'), $this->input->post('url_include'));

        $p = array(
            'name' => $this->input->post("testname"),
            'mainurl' => $this->input->post('tt_mainurl'),
            'runpattern' => $runpattern,
            'allocation' => (!$allocation || $allocation < 0 || $allocation > 1) ? 100 : $allocation * 100,
            'startdate' => $start_date,
            'enddate' => $end_date,
        );
        $project = $this->dbconfiguration->saveProjectConfigurationToDatabase($p, $old_config);

        try {
            $this->sdk->updateProject($lpcid, $project);
            echo 'OK';
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /*     * *************************************************************************************************
     * ************************************* ADDITIONAL FUNCTIONS ***********************************
     * ***************************************************************************************************** */

    /**
     * Called via AJAX, this method starts/stops the given group
     * @return void
     */
    public function tt_group_toggle() {
        try {
            $lpcid = $this->input->post('collectionid');
            $action = $this->input->post('action');
            $groupid = $this->input->post('groupid');
            $clientid = getClientIdForAction(FALSE);

            if (!$clientid) {
                return(false);
            }

            if ($action == 0) {
                $this->sdk->stopDecisionGroup($lpcid, $groupid);
            } else if ($action == 1) {
                $this->sdk->startDecisionGroup($lpcid, $groupid);
            }

            echo $action . "-" . $lpcid;
            return;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * Called via AJAX, this method restarts the given group.
     * @return void
     */
    public function tt_group_restart() {
        try {
            $lpcid = $this->input->post('collectionid');
            $groupid = $this->input->post('groupid');
            $clientid = getClientIdForAction(FALSE);

            if (!$clientid) {
                return(false);
            }

            $this->sdk->restartDecisionGroup($lpcid, $groupid);

            echo $lpcid;
            return;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * Called via AJAX, this method deletes the given group
     * @return void
     */
    public function tt_group_delete() {
        try {
            $lpcid = $this->input->post('collectionid');
            $groupid = $this->input->post('groupid');
            $clientid = getClientIdForAction(FALSE);

            if (!$clientid) {
                return(false);
            }
            $this->sdk->deleteDecisionGroup($lpcid, $groupid);
            return;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

}
