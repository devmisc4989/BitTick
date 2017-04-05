<?php

class lpc extends CI_Controller {

    public $rowinsert = '';
    public $info;
    private $sdk;
    private $mode = 'EDIT'; //  "CREATE" If $lpcid is not set or = 0
    private $isTimeGoal;
    private $isPILiftGoal;
    private $defaultGoal = FALSE;
    private $testypeNames = array();
    private $testypeCodes = array();

    function __construct() {
        parent::__construct();
        doAutoload(); // load resources from autoload_helper
        $this->load->model('landingpagecollection');
        $this->load->model('optimisation');
        $this->load->model('adminmodel');
        $this->load->model('user');
        $this->load->library('log');
        $this->load->library('Dbconfiguration');
        $this->load->helper('apiv1');
        $this->load->helper('string');
        $this->load->helper('featurematrix');
        $this->load->helper('allocation');
        $this->load->helper('goals');
        $this->load->model('mvt');

        define('API_URL', $this->config->item('base_ssl_url') . 'api/v1/', TRUE);
        require_once APPPATH . 'controllers/apiv1sdk.php';
        $this->sdk = new apiv1sdk();
        //$this->sdk->__set('proxyurl', "http://127.0.0.1:8888/");
        $this->sdk->__set('clientid', $this->session->userdata('sessionUserId'));
        $this->sdk->__set('apikey', $this->config->item('bt_apikey'));
        $this->sdk->__set('apisecret', $this->config->item('bt_apisecret'));

        $this->testypeCodes = array(
            'SPLIT' => OPT_TESTTYPE_SPLIT,
            'VISUAL' => OPT_TESTTYPE_VISUALAB,
            'TEASERTEST' => OPT_TESTTYPE_TEASER,
            'MULTIPAGE' => OPT_TESTTYPE_MULTIPAGE,
        );
        
        $this->testypeNames = array(
            OPT_TESTTYPE_SPLIT => $this->lang->line('testtype_split'),
            OPT_TESTTYPE_VISUALAB => $this->lang->line('testtype_visual'),
            OPT_TESTTYPE_TEASER => $this->lang->line('testtype_teaser'),
            OPT_TESTTYPE_MULTIPAGE => $this->lang->line('testtype_multipage'),
        );
    }

    function index() {
        $clientid = $this->session->userdata('sessionUserId');
        if (!$clientid) {            //session checking{
            lang_redirect('login');
        } else {
            $login_targetpage = $this->session->userdata('login_targetpage');
            if (!$login_targetpage) {
                redirect($this->config->item('base_ssl_url') . "lpc/cs/");
            } else {
                $this->session->unset_userdata('login_targetpage');
                redirect($login_targetpage);
            }
        }
    }

    /*     * **************************************************************** */

    /**
     * Prepares and shows the page with the list of collections for the looged client
     * loads the CSS and JS files, get the list of projects with their respective data, 
     * loads the corresponding views
     */
    public function cs() {
        $clientid = getClientIdForActionFromUrl(3);
        $this->session->unset_userdata('newUrlData');
        $this->session->unset_userdata('site_info');
        $this->session->unset_userdata('clickGoals');
        $this->session->unset_userdata('goalsToDelete');

        if ($clientid) {
            $this->config->load('personalization');
            $this->lang->load('personalization');
            $this->lang->load('collectionoverview');

            $custom = $this->landingpagecollection->getCustomClientData($clientid);
            $userplan = $custom->userplan;
            $clientAccount = $this->sdk->getAccount($this->session->userdata('sessionUserId'));
            $conversionGoalsParams = $this->landingpagecollection->getEtrackerConversionGoalsParamValues();

            $csdata = array(
                'title' => $this->lang->line('title_collectionoverview'),
                'clientid' => $clientid,
                'status' => array_search($clientAccount->status, $this->config->item('api_clientstatus')),
                'quota' => $clientAccount->quota,
                'firstname' => $clientAccount->firstname,
                'ip_blacklist' => $clientAccount->ipblacklist,
                'used_quota' => $clientAccount->usedquota,
                'quota_reset_dayinmonth' => $clientAccount->quotaresetdayinmonth,
                'email_validated' => (int) $clientAccount->emailvalidated,
                'createddate' => $clientAccount->createddate,
                'userplan' => $userplan,
                'headimg' => base_url() . "images/logo_sml.png",
                'smsLevel' => getSmSLevel(),
                'multipageLevel' => getMultipageLevel(),
                'teasertestLevel' => getTeasertestLevel(),
                'has_timer' => hasCampaignTimer(),
                'perso_level' => getPersoLevel(),
                'testingLevel' => getTestingLevel(),
                'allowedVariants' => getAllowedVariantsAmount(),
                'conversionGoalsParams' => $conversionGoalsParams,
                'conflictLayerLang' => $this->lang->line('conflict layer popup')
            );

            $csdata += self::getListOfCollections($clientid);
            $csdata += self::getCssAndJsForCollectionList($csdata['account']);

            if ($csdata['account'] == 0) {
                $csdata += array(
                    'no_tests' => TRUE,
                    'success' => 0,
                    'title' => $this->lang->line('title_homepage'),
                );
            }

            $this->load->view('includes/protected_header', $csdata);

            $loginstatus = $this->session->userdata('sessionLoginStatus');
            if ($loginstatus == LOGIN_STATUS_FULL) {
                $this->load->view('protected/collectionoverview.php', $csdata);
            }
            if ($loginstatus == LOGIN_STATUS_LIMITED) {
                $this->load->view('protected/hibernated.php', $csdata);
            }

            $this->load->view('client-testpage-popups/dashboard_project_conflict.php', $csdata);
            $this->load->view('client-testpage-popups/personalization_popup.php', $csdata);
            $this->load->view('protected/dashboard-add-ons.php', $csdata);
            $this->load->view('includes/public_footer');
        } else {
            $this->session->set_userdata('login_targetpage', $this->config->item('base_ssl_url') . 'lpc/cs');
            lang_redirect('login');
        }
    }

    /**
     * This method is called via AJAX (see js/conflict_layer.js)
     * first it determines if there are project with conflicts based on the active projects and the ID of the project being evaluated-
     * If there are conflicts, creates a list of project with relevant data to be displayed in a table in the "conflict layer" popup 
     * The returned array contains the followint items
     *      collection: the name of the project being evaluated
     *      impressions: The number of impressions to determine if the "confirmation" button should say "continue" or "start" (project) 
     *      conflicts: a flag to determine if there are conflicts or not
     *      projects: array with each of the conflicted project's data including the ID, the name and the problem code.
     *      code: this is the conflict that generates the project being evaluated
     *          1: means that the project cannot be delivered
     *          2: means that other projects with the same URL won't be delivered.
     */
    public function getProjectConflicts() {
        $this->lang->load('collectionoverview');

        $lpcid = $this->input->post('lpcid');
        $clientid = $this->session->userdata('sessionUserId');
        $projects = $this->optimisation->getActiveProjectsOnSameUrl($clientid, $lpcid);
        $result = $this->optimisation->determineCollectionConflicts($projects, $lpcid);

        if ($result['hasConflicts']) {
            
            $affectedProjects = $result['affectedProjects'];
            
            $list = array(
                'lpc_name' => '',
                'impressions' => 0,
                'conflicts' => TRUE,
                'projects' => array(),
                'code' => $result['errorCode'],
            );

            foreach ($projects as $project) {
                $problem = 0;
                
                if($project['collectionid'] == $lpcid){
                    $list['lpc_name'] = $project['name'];
                    $list['impressions'] = $project['impressions'];
                }

                if ($result['errorCode'] == 2) {
                    $keys = array_keys($affectedProjects);
                    if (!in_array($project['collectionid'], $keys)) {
                        continue;
                    } else {
                        $problem = $affectedProjects[$project['collectionid']];
                        if ($problem == 0) {
                            continue;
                        }
                    }
                } else if ($project['collectionid'] == $lpcid) {
                    $problem = $affectedProjects[$lpcid];
                }

                $p = array(
                    'lpcid' => $project['collectionid'],
                    'name' => $project['name'],
                    'problem' => $problem,
                );
                
                if ($project['isSmartMessage'] == 1) {
                    $p['type'] = $this->lang->line('testtype_sms');
                } else {
                    $p['type'] = $this->testypeNames[$project['testtype']];
                }

                $list['projects'][] = $p;
            }
        } else {
            $list = array(
                'conflicts' => FALSE,
            );
        }

        echo json_encode($list);
        return;
    }

    /**
     * Returns an array with the URL to all of the CSS and JS files used by the collection list page
     * @param Int $collectionCount - Number of collections found.
     * @return Array
     */
    private function getCssAndJsForCollectionList($collectionCount) {
        $tootltips = ($this->config->item('tenant') == 'etracker') ? 'js/tooltips/css/jquery.powertip.etracker.css' : 'js/tooltips/css/jquery.powertip.min.css';

        $arrCss[] = base_ssl_url() . $tootltips;
        $arrCss[] = base_ssl_url() . 'css/template.css';
        $arrCss[] = base_ssl_url() . 'css/editor.css';
        $arrCss[] = base_ssl_url() . 'css/font-awesome/css/font-awesome.min.css';
        $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_ssl_url() . 'css/editor/perso-rules.css';

        if (hasCampaignTimer()) {
            $arrCss[] = base_ssl_url() . 'js/datepicker/css/datepicker.css';
            if ($this->config->item('tenant') == 'etracker') {
                $arrCss[] = base_ssl_url() . 'js/datepicker/css/etracker.datepicker.css';
            }
            $arrJavascript[] = base_ssl_url() . 'js/datepicker/js/datepicker.js';
            $arrJavascript[] = base_ssl_url() . 'js/dash_testpage_wizards/BT-editor-additional-scripts.js';
        }

        $arrJavascript[] = base_ssl_url() . 'js/jquery.Storage.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.json-2.3.js';
        $arrJavascript[] = base_ssl_url() . 'js/tooltips/jquery.powertip.min.js';
        $arrJavascript[] = base_ssl_url() . 'js/BT-editor/perso/perso-rules.js';
        $arrJavascript[] = base_ssl_url() . 'js/BT-common/BT-teasertest.js';
        $arrJavascript[] = base_ssl_url() . 'js/BT-common/BT-multipagetest.js';
        $arrJavascript[] = base_ssl_url() . 'js/validateplaymode.js';
        $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.qtip-1.0.0-rc3.js';
        $arrJavascript[] = base_ssl_url() . 'js/conflict_layer.js';
        $arrJavascript[] = base_ssl_url() . 'js/BT-common/BT-urlpattern.js';
        $arrJavascript[] = base_ssl_url() . 'js/BT-common/BT-clickgoals.js';

        $others = FALSE;
        if ($collectionCount > 0) {
            $arrJavascript[] = base_ssl_url() . 'js/tablesort.js';
            $others = '
                    <style type="text/css">
                        * 
                        label.error { color: red;font-size: 11px;  }
                    </style>';
        }

        return array(
            'css' => $arrCss,
            'js' => $arrJavascript,
            'others' => $others,
        );
    }

    /**
     * gets the list of projects for a given client and evaluates the data, to return an array with their
     * IDs, type, name, status, progress, main url, visitors, conversions, conversion rate, etc.
     * @param Ind $clientid
     * @return Array
     */
    private function getListOfCollections($clientid) {
        $collections = $this->sdk->getProjects('sort=-id');

        if (!isset($collections) || count($collections) < 1) {
            return array(
                'landingpagestatus' => 0,
                'account' => 0,
            );
        }

        $tenant = $this->config->item('tenant');
        $outputlist = array();
        $thumbs = array(
            'NONE' => 'none',
            'LOST' => 'down',
            'WON' => 'up',
        );

        foreach ($collections as $collection) {
            $lpcid = $collection->id;
            $customdata = $this->landingpagecollection->getCustomLpcData($lpcid);

            $res = isset($thumbs[$collection->result]) ? $thumbs[$collection->result] : 'none';
            $result = '<a class="thumb_' . $res . '"></a>';

            $condition = $tenant == 'etracker' ? "(!= 'alwaysfalse')" : 'UNSET';
            if ($collection->status == $condition) {
                $status = OPT_PAGESTATUS_UNVERIFIED;
                $result = $this->lang->line('Is Trackingcode verified?') . '<br/>' .
                        '<a href="javascript://" class="collectionVerify help_link" collectionid="' . $lpcid . '" collectionstatus="' . $status . '">' .
                        $this->lang->line('Verify Trackingcode Link') . '</a><br/>' .
                        '<a href="javascript://" class="collectionShowCode help_link" collectionid="' . $lpcid . '" clientid="' . $clientid . '">' .
                        $this->lang->line('Click here to see tracking code!') . '</a>';
            } else if ($res == 'none') {
                $days = $collection->remainingdays;
                if ($days == -1) {
                    $result = $this->lang->line('remaining time') . "<br>" . $this->lang->line('testtime_nodata');
                } else if ($days > 180) {
                    $result = $this->lang->line('remaining time') . "<br>" . $this->lang->line('testtime_morethan6months');
                } else if ($days > 90 && $days <= 180) {
                    $result = $this->lang->line('remaining time') . "<br>" . $this->lang->line('testtime_3to6months');
                } else if ($days > 30 && $days <= 90) {
                    $result = $this->lang->line('remaining time') . "<br>" . $this->lang->line('testtime_1to3months');
                } else {
                    $result = $this->lang->line('remaining time') . "<br>" . sprintf($this->lang->line('testtime'), $days);
                }
            } else if ($res == 'up') {
                $result .= '<b>' . round(100 * $collection->uplift) . '%</b> ' . $this->lang->line('table_uplift');
            } else {
                $result .= 'Keine Verbesserung';
            }

            $testtype = $this->testypeCodes[$collection->type];
            
            $hasConflicts = false;
            if ($collection->status == 'RUNNING') {
                $projects = $this->optimisation->getActiveProjectsOnSameUrl($clientid, $lpcid);
                $conflictResult = $this->optimisation->determineCollectionConflicts($projects, $lpcid);
                $hasConflicts = $conflictResult['hasConflicts'];
            }

            $outputlist[] = array(
                'collectionid' => $lpcid,
                'name' => $collection->name,
                'status' => $collection->status == 'UNSET' ? 0 : ($collection->status == 'PAUSED' ? 1 : 2),
                'progress' => $collection->result == 'WON' || $collection->result == 'LOST' ? 2 : 1,
                'lp_url' => $collection->mainurl,
                'cr' => round(100 * $collection->conversionrate, 0) . '%',
                'visitorcount' => $collection->visitors,
                'conversioncount' => $collection->conversions,
                'testtype' => $testtype,
                'smartmessage' => $customdata->smartmessage,
                'result' => $result,
                'hasConflicts' => $hasConflicts
            );
        }

        $csdata = array(
            'landingpagestatus' => 1,
            'account' => sizeof($collections),
            'collections' => $outputlist,
        );

        return $csdata;
    }

    /*     * **************************************************************** */

    /**
     * New controller to display TeaserTest Overview page, it just loads the tt controller
     * and calls the corresponding method to do the rest.
     * @param type $lpcid
     */
    public function tto($lpcid = '') {
        if ((int) $lpcid > 0) {
            $this->session->set_userdata('tto_lpcid', $lpcid);
            $this->load->library('../controllers/tt');
            $tt = new $this->tt();
            $tt->tt_overview($lpcid);
        } else {
            redirect($this->config->item('base_ssl_url') . "lpc/cs/");
        }
    }

    /*     * **************************************************************** */

    /**
     *  controller for displaying collection details of a user
     */
    function lcd($collectionid = '', $clientid = '', $startdate = -1, $groupid = -1) {
        $this->session->unset_userdata('clickGoals');
        $this->session->unset_userdata('goalsToDelete');

        $this->lang->load('landingpagecollectiondetails');
        $clientid = getClientIdForActionFromUrl(4);
        $action = "";
        if ($this->input->get('dataonly') == 'yes')
            $action = "showdataonly";

        if ($clientid) {
            $this->sdk->__set('clientid', $clientid); // necessary if authentication in getClientIdForActionFromUrl
            // is done via apikey/secret instead form based.
            $html = $this->renderCollectionDetailsPage($collectionid, $startdate, $clientid, $action, $groupid);
            $this->session->set_userdata('editor_collectionid', $collectionid);
            if ($html) {
                echo($html);
            } else {
                $this->session->set_userdata('login_targetpage', 'lpc/lcd/' . $collectionid);
                lang_redirect('login');
            }
        } else {
            $this->session->set_userdata('login_targetpage', 'lpc/lcd/' . $collectionid);
            lang_redirect('login');
        }
    }

    /*     * **************************************************************** */

    /**
     * formats the corresponding LPC & LP data to create an array with the personalization info for the current
     * project, the returned array is used to show the perso mode and each project/variant rule id and name.
     * @param String $perso - project personalization mode (NONE, SINGLE, COMPLETE)
     * @param Array $variantDetails - All variants data (id, name, ruleid, etc...)
     * @return Array - formated array with the project perso mode, and each variant's name, rule id and rule name
     */
    private function getLpPersonalization($perso, $variantDetails, $ruleInfo) {
        $persolp = array();
        $persoMode = array_search($perso, $this->config->item('api_persomode'));

        foreach ($variantDetails as $variant) {
            $ruleName = '';
            foreach ($ruleInfo as $rule) {
                if ($rule->id == $variant->ruleid) {
                    $ruleName = $rule->name;
                }
            }

            $persolp[$variant->id] = array(
                'mode' => $persoMode ? $persoMode : 0,
                'name' => $variant->name,
                'rule_id' => $variant->ruleid,
                'rulename' => $ruleName,
            );
        }
        return $persolp;
    }

    /**
     * Returns an array with the URL of all of the required CSS files for the collection details page
     */
    private function getCssArray($isTT) {
        $data = array();
        $tootltips = ($this->config->item('tenant') == 'etracker') ? 'js/tooltips/css/jquery.powertip.etracker.css' : 'js/tooltips/css/jquery.powertip.min.css';
        $data['css'][] = base_ssl_url() . 'css/validationEngine.jquery.css';
        $data['css'][] = base_ssl_url() . 'css/template.css';
        $data['css'][] = base_ssl_url() . 'css/editor.css';
        $data['css'][] = base_ssl_url() . 'css/editor/perso-rules.css';
        $data['css'][] = base_ssl_url() . 'css/font-awesome/css/font-awesome.min.css';
        $data['css'][] = base_ssl_url() . 'js/d3/c3.min.css';
        $data['css'][] = base_ssl_url() . $tootltips;
        $data['css'][] = base_ssl_url() . 'js/jquery-ui-1.9.1.custom/css/ui-lightness/jquery-ui.css';

        if (hasCampaignTimer()) {
            $data['css'][] = base_ssl_url() . 'js/datepicker/css/datepicker.css';
            if ($this->config->item('tenant') == 'etracker') {
                $data['css'][] = base_ssl_url() . 'js/datepicker/css/etracker.datepicker.css';
            }
        }
        if ($isTT) {
            $data['css'][] = base_ssl_url() . 'css/tt-overview.css';
        }
        return $data;
    }

    /**
     * Returns an array with the URL of all of the required JS files for the collection details page
     */
    private function getJsArray() {
        $data = array();
        $data['js'][] = base_ssl_url() . 'jsi18n/validatelogin';
        $data['js'][] = base_ssl_url() . 'js/validatelogin.js';
        $data['js'][] = base_ssl_url() . 'js/validateplaymode.js';
        $data['js'][] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
        $data['js'][] = base_ssl_url() . 'js/jquery.validationEngine.js';
        $data['js'][] = base_ssl_url() . 'js/jquery.qtip-1.0.0-rc3.js';
        $data['js'][] = base_ssl_url() . 'js/resetconnection.js';
        $data['js'][] = base_ssl_url() . 'js/jquery.Storage.js';
        $data['js'][] = base_ssl_url() . 'js/jquery.json-2.3.js';
        $data['js'][] = base_ssl_url() . 'js/tooltips/jquery.powertip.min.js';
        $data['js'][] = base_ssl_url() . 'js/BT-editor/perso/perso-rules.js';
        $data['js'][] = base_ssl_url() . 'js/BT_editor_wizard_diagnose_mode.js';
        $data['js'][] = base_ssl_url() . 'js/BT-common/BT-teasertest.js';
        $data['js'][] = base_ssl_url() . 'js/BT-common/BT-multipagetest.js';
        $data['js'][] = base_ssl_url() . 'js/jquery.remember-state.js';
        $data['js'][] = base_ssl_url() . 'js/d3/d3.min.js';
        $data['js'][] = base_ssl_url() . 'js/d3/c3.min.js';
        $data['js'][] = base_ssl_url() . 'js/BT-common/BT-cdchart.js';
        $data['js'][] = base_ssl_url() . 'js/jtable/jquery-ui.js';

        if (hasCampaignTimer()) {
            $data['js'][] = base_ssl_url() . 'js/datepicker/js/datepicker.js';
            $data['js'][] = base_ssl_url() . 'js/dash_testpage_wizards/BT-editor-additional-scripts.js';
        }
        return $data;
    }

    /**
     * Returns a String with additional CSS and JS code for the collection details page.
     * @param Array $projectDetails - Project data (id, name, type, etc...)
     * @return string
     */
    private function getAdditionalCssAndJs($projectDetails) {
        $type = $this->testypeCodes[$projectDetails->type];
        $data = array(
            'others' => '
                <style type="text/css">
                    * 
                    label.error { color: red;font-size: 11px;  }
                </style>
                <script type="text/javascript">
                    var collectionid = ' . $projectDetails->id . ';
                    var testtype = ' . $type . '										
                </script>',
        );

        return $data;
    }

    /**
     * Returns the text to be displayed as a headline and summary, depending on the project status,
     * the project winner, uplift, significance, etc...
     * if the project is an SMS or if it the perso mode is SINGLE, calls the getSummaryIfProjectIsSms() method
     * to return a different headline and subline
     * @param Array $projectDetails
     * @param Array $variantDetails
     * @param Array $pagelist - Contains every variant with its respective name (we only need the IDs)
     * @param Int $isSms - 1 if the project is an SMS, 0 otherwise
     * @return Array
     */
    private function getProjectSummary($projectDetails, $variantDetails, $pagelist, $isSms) {
        settype($isSms, 'boolean');

        $firstvariant = $variantDetails[0];
        if ($firstvariant->type == 'CONTROL') {
            $firstvariant = isset($variantDetails[1]) ? $variantDetails[1] : FALSE;
        }
        $confidence = $firstvariant ? $firstvariant->confidence : 0;

        $tenant = $this->config->item('tenant');
        $significance = number_format(100 * $confidence, 0);
        $NoSingleNoSms = $projectDetails->personalizationmode != 'SINGLE' && !$isSms;

        $data['display_summary'] = TRUE;
        if ($projectDetails->status == 'PAUSED') {
            $data['summary_headline'] = $this->lang->line('smry_headline_paused');
        } elseif ($projectDetails->status == 'UNSET' && $tenant != 'etracker') {
            $data['summary_headline'] = $this->lang->line('smry_headline_unverified');
            $data['summary_subline'] = $this->lang->line('smry_subline_unverified');
        } elseif ($projectDetails->visitors == 0) {
            $data['summary_headline'] = $this->lang->line('smry_headline_noevents');
            $data['summary_subline'] = $this->lang->line('smry_subline_noevents') . " " . splink('lcd_noevents');
        } else {
            if (!$NoSingleNoSms) {
                return self::getSummaryIfProjectIsSms($projectDetails, $isSms);
            }
            switch ($projectDetails->result) {
                case 'LOST':
                    $data['summary_headline'] = $this->lang->line('smry_headline_controlwinner');
                    $data['summary_subline'] = sprintf($this->lang->line('smry_subline_controlwinner'), $projectDetails->conversionrate);
                    break;
                case 'WON':
                    $uplift = round(100 * $projectDetails->uplift);
                    $data['summary_headline'] = sprintf($this->lang->line('smry_headline_variantwinner'), $uplift);
                    $winnerindex = $projectDetails->winnerid;
                    $winnername = $pagelist[$winnerindex];
                    $data['summary_subline'] = sprintf($this->lang->line('smry_subline_variantwinner'), $winnername);
                    break;
                case 'NONE':
                    if ($projectDetails->remainingdays > 180) {
                        $data['summary_subline'] = $this->lang->line('smry_subline_testtime_morethan6months');
                    } elseif (($projectDetails->remainingdays > 90) && ($projectDetails->remainingdays <= 180)) {
                        $data['summary_subline'] = $this->lang->line('smry_subline_testtime_3to6months');
                    } elseif (($projectDetails->remainingdays > 30) && ($projectDetails->remainingdays <= 90)) {
                        $data['summary_subline'] = $this->lang->line('smry_subline_testtime_1to3months');
                    } else if ($projectDetails->remainingdays == -1) {
                        $data['summary_subline'] = $this->lang->line('smry_subline_testtime_nodata');
                    } else {
                        $data['summary_subline'] = sprintf($this->lang->line('smry_subline_testtime'), $projectDetails->remainingdays);
                    }
                    $data['summary_headline'] = self::resultNoneSummaryHeadline($variantDetails);
                    break;
            }
        }

        return $data;
    }

    /**
     * If the project result is NONE, this method is called to return a headline with custom text
     * depending on the partial winner (or winners)
     * It verifies the control CR to calculate the winner(s) uplift.
     * @param Array $variantDetails - All variants data for a specific LPC id (returned by the API)
     * @return String
     */
    private function resultNoneSummaryHeadline($variantDetails) {
        $numleaders = 0;
        $controlcr = 1;
        $leader = array();
        $maxcr = 0;

        foreach ($variantDetails as $variant) {
            if ($variant->type == 'CONTROL') {
                $controlcr = $variant->conversionrate > 0 ? $variant->conversionrate : 1;
            }
            if ($variant->type == 'VARIANT' && $variant->result == 'WON') {
                $numleaders ++;
                $maxcr = $variant->conversionrate > $maxcr ? $variant->conversionrate : $maxcr;
                if ($maxcr == $variant->conversionrate) {
                    $leader = array(
                        'name' => $variant->name,
                        'cr' => $variant->conversionrate,
                    );
                }
            }
        }

        if ($numleaders == 0) {
            return $this->lang->line('smry_headline_nosignificance');
        } else {
            $uplift = ($leader['cr'] - $controlcr) / $controlcr;
            $leaderuplift = number_format(100 * $uplift, 0);
        }

        if ($numleaders == 1) {
            return sprintf($this->lang->line('smry_headline_oneleader'), $leader['name'], $leaderuplift);
        } else if ($numleaders > 1) {
            return sprintf($this->lang->line('smry_headline_multleaders'), $leaderuplift);
        }
    }

    /**
     * When there is a combination on the project properties for SMS and a particular perso mode, the headline
     * and summary changes accordingly
     * 
     * @param Array $projectDetails
     * @param boolean $isSms - TRUE if the project is an SMS
     */
    private function getSummaryIfProjectIsSms($projectDetails, $isSms) {
        $singleNoSms = $projectDetails->personalizationmode == 'SINGLE' && !$isSms;
        $singleAndSms = $projectDetails->personalizationmode == 'SINGLE' && $isSms;
        $smsNoSingle = $isSms && $projectDetails->personalizationmode != 'SINGLE';
        $data['display_summary'] = TRUE;
        if ($projectDetails->visitors > 0) {
            if ($singleNoSms) {
                $data['summary_headline'] = $this->lang->line('smry_headline_single_nosms');
                $data['summary_subline'] = $this->lang->line('smry_subline_single_nosms');
            } else if ($smsNoSingle) {
                $data['summary_headline'] = $this->lang->line('smry_headline_sms_nosingle');
                $data['summary_subline'] = $this->lang->line('smry_subline_sms_nosingle');
            } else if ($singleAndSms) {
                $data['summary_headline'] = $this->lang->line('smry_headline_single_and_sms');
                $data['summary_subline'] = $this->lang->line('smry_subline_single_and_sms');
            }
        }
        return $data;
    }

    /**
     * Given an LPC id, returns all of the saved collection goals for it.
     * As the array returned by the API has a different format that the one used in visual mode, this 
     * method format the returned array accordingly
     * @param Int $lpcid 
     * @return Array - containing each of the project goals with its ID, type and arguments.
     */
    private function getCollectionGoals($lpcid, $urlGetGoal) {
        $collectionGoals = array();
        $goals = $this->sdk->getGoals($lpcid);
        $goalType = FALSE;

        foreach ($goals as $goal) {
            $type = array_search($goal->type, $this->config->item('api_goals'));
            $level = array_search($goal->level, $this->config->item('api_goal_level'));
            if ($type && $goal->status == 'ACTIVE') {
                $collectionGoals[] = array(
                    'collection_goal_id' => $goal->id,
                    'type' => $type,
                    'arg1' => $goal->param != 'NA' ? $goal->param : '',
                    'level' => $level,
                    'name' => $goal->name,
                    'pageid' => $goal->page,
                    'status' => $goal->status,
                    'selected' => $urlGetGoal == $goal->id ? TRUE : FALSE
                );
                $this->defaultGoal = $urlGetGoal == $goal->id ? $goal->id : FALSE;
                if ($goalType == FALSE) {
                    $goalType = $urlGetGoal == $goal->id ? $type : FALSE;
                }
            }
        }

        if (!$urlGetGoal) {
            $collectionGoals[0]['selected'] = TRUE;
            $goalType = $collectionGoals[0]['type'];
            $this->defaultGoal = $collectionGoals[0]['collection_goal_id'];
        }

        $this->isTimeGoal = $goalType == GOAL_TYPE_TIMEONPAGE ? TRUE : FALSE;
        $this->isPILiftGoal = $goalType == GOAL_TYPE_PI_LIFT ? TRUE : FALSE;


        $data['collectionGoals'] = $collectionGoals;
        return $data;
    }

    /**
     * Returns an array with the tracking code for all of the elements of the LPC (variants, goals, etc)
     * @param Int $lpcid - The project ID
     * @return Array
     */
    private function getTrackingCodeData($lpcid) {
        $tk = $this->mvt->getLPCTrackingCode($lpcid, OPT_TESTTYPE_VISUALAB);
        $trackingcodedata = array(
            'lpctrackingcode_control' => $tk['lpctrackingcode_control'],
            'lpctrackingcode_success' => $tk['lpctrackingcode_success'],
            'testgoal' => $tk['testgoal'],
            'testname' => $tk['testname'],
            'lpccode' => $tk['lpccode'],
            'lpctrackingcode_variant' => $tk['lpctrackingcode_variant'],
            'lpctrackingcode_ocpc' => $tk['lpctrackingcode_ocpc']
        );

        $data['trackingcodedata'] = json_encode($trackingcodedata);
        return $data;
    }

    /**
     * returns an indexed array with all control and variants related data, like name, pagetype, id, conversions, etc...
     * and the number of variants in the given project ($vcount);
     * @param type $projectDetails - Project data (returned by the API) including id, type, remaining days, etc
     * @param Array $variantDetails - variant data (returned by the API) including variant id, pagetype, impresisons, etc...
     * @param Array $persolp - personalization data for all variants
     * @return Array
     */
    private function getOutputData($projectDetails, $variantDetails, $persolp) {
        $outputlist = array();
        $vcount = 0;

        $thumbs = array(
            'NONE' => 'none',
            'LOST' => 'down',
            'WON' => 'up',
        );

        $controlCr = 0;

        foreach ($variantDetails as $variant) {
            if($variant->type == 'CONTROL')
                $controlCr = $variant->conversionrate;
            if ($this->isTimeGoal) {
                $cr = self::getFormattedCR($variant->conversionrate / 60);
            } elseif ($this->isPILiftGoal) {
                $cr = number_format((float) $variant->conversionrate, 1);
            } else {
                $cr = number_format((float) 100 * $variant->conversionrate, 1) . '%';
            }

            $item = array(
                'id' => $variant->id,
                'name' => $variant->name,
                'pagetype' => $variant->type == 'CONTROL' ? 1 : 2,
                'variantindex' => $variant->variantindex,
                'impressions' => $variant->visitors,
                'conversions' => $variant->conversions,
                'rulename' => $persolp[$variant->id]['rulename'],
                'rule_id' => $variant->ruleid,
                'cr' => $cr,
            );

            $res = isset($thumbs[$variant->result]) ? $thumbs[$variant->result] : 'none';
            switch($res) {
                case 'WON':
                    $res = 'up';
                    break;
                case 'LOST':
                    $res = 'down';
                    break;
            }
            if ($item['pagetype'] == OPT_PAGETYPE_CTRL) {
                $won = $res == 'up' ? $this->lang->line('control is winner') : '';
                $result = '<a class="thumb_' . $res . '"></a>' . $won;
                $status = OPT_PAGESTATUS_ACTIVE;
            } else if ($item['pagetype'] == OPT_PAGETYPE_VRNT) {
                $vcount ++;
                $result = '<a class="thumb_' . $res . '"></a>';
                $daysleft = is_numeric($projectDetails->remainingdays) ? $projectDetails->remainingdays : 0;
                $item['lp_url'] = $projectDetails->mainurl;
                if($projectDetails->type == 'SPLIT'){
                    $item['lp_url'] = $variant->url;
                }

                if (($daysleft >= 0 && $res == 'none') || ($projectDetails->type == 'TEASERTEST' && $res == 'none')) {
                    $confidence = floor($variant->confidence * 100);
                    $width = 75 * $variant->confidence;
                    $result .= '<div class="progress">'
                            . '         <div class="msg">' . $this->lang->line('table_progress') . ':</div>'
                            . '         <div class="bg" style="width:' . $width . 'px"></div>'
                            . '         <div class="value">' . $confidence . '%</div>'
                            . '     </div>';
                } else if ($res == 'none') {
                    $result = $this->lang->line('variant_testtime_nodata');
                } else if ($res == "up") {
                    if($controlCr == 0) {
                        $uplift = 0;
                    }
                    else {
                        $uplift = round(100 * $variant->conversionrate / $controlCr - 100, 2);
                    }
                    $width = 75 * $uplift;
                    $result .= "<b>" . round(100 * $uplift / 100) . "%</b> " . $this->lang->line('table_uplift');
                }
                $status = $variant->status == 'PAUSED' ? 1 : 2;
            } else {
                continue;
            }

            $item['status'] = $status;
            $item['result'] = $result;
            $outputlist[] = $item;
        }

        $data = array(
            'landingpages' => $outputlist,
            'variantcount' => $vcount,
        );

        return $data;
    }

    /**
     * Returns the cr in the next format: "mm:ss" or "hh:mm:ss" -- it is called if the evaluated goal is "Time on page".
     * @param type numeric - either the variant CR or the aggregated CR, depending on the caller method
     * @return Float/Int
     */
    private function getFormattedCR($rate) {
        if ($rate == NULL) {
            return NULL;
        }

        $intMin = floor($rate);
        $deciMin = $rate - $intMin;
        $hours = str_pad(floor($intMin / 60), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(floor($intMin % 60), 2, '0', STR_PAD_LEFT);
        $seconds = str_pad(floor($deciMin * 60), 2, '0', STR_PAD_LEFT);
        return ($rate >= 60 ? $hours . ':' : '') . $minutes . ':' . $seconds;
    }

    /**
     * Returns an array with Personalization data for the project
     * @param Array $projectDetails
     * @param Array $persolp
     * @param Array $ruleInfo
     * @return Array
     */
    private function getPersoData($projectDetails, $persolp, $ruleInfo) {
        $conversionGoalsParams = $this->landingpagecollection->getEtrackerConversionGoalsParamValues();
        $persoMode = array_search($projectDetails->personalizationmode, $this->config->item('api_persomode'));

        $ruleName = '';
        foreach ($ruleInfo as $rule) {
            if ($rule->id == $projectDetails->ruleid) {
                $ruleName = $rule->name;
            }
        }

        $data = array(
            'persoid' => $projectDetails->ruleid,
            'persolp' => json_encode($persolp),
            'persolevel' => getPersoLevel(),
            'persomode' => $persoMode,
            'personame' => $ruleName,
            'conversionGoalsParams' => $conversionGoalsParams,
        );
        return $data;
    }

    /**
     * 	helper function: create the HTML for controller lcd and for updatecollectiondetails
     * 	This is needed because both render the same, but the first just echoies it, the second
     * 	retrieves it with POST and then echoes it
     */
    public function renderCollectionDetailsPage($collectionid, $startdate, $clientid, $action = "", $groupid = -1) {
        try {
            $clientData = $this->sdk->getAccount($clientid);
            if ($clientData->status == 'UNSET') {
                return FALSE;
            }

            $isTT = FALSE;
            $isMpt = FALSE;
            $urlGetGoal = $this->input->get('goalid');
            $goalFilter = $urlGetGoal ? 'goalid=' . $urlGetGoal : FALSE;
            $customData = $this->landingpagecollection->getCustomLpcData($collectionid);

            $ruleInfo = $this->sdk->getRules();
            $projectDetails = $this->sdk->getProject($collectionid);
            $config = json_decode($projectDetails->config);
            
            $controlUrl = $projectDetails->mainurl;
            $groupDetails = FALSE;
            
            if ($projectDetails->type == 'TEASERTEST') {
                $groupDetails = $this->sdk->getDecisionGroup($collectionid, $groupid);
                $isTT = TRUE;
            } else if ($projectDetails->type == 'MULTIPAGE') {
                $isMpt = TRUE;
                $controlUrl = array();
                $groupDetails = $this->sdk->getDecisionGroups($collectionid);
                foreach ($groupDetails AS $group) {
                    $mainUrl = $group->mainurl;
                    $variants = $this->sdk->getDecisions($collectionid, FALSE, $group->id);
                    foreach ($variants as $variant) {
                        $controlUrl[$mainUrl][$variant->variantindex] = $variant->id;
                    }
                }
            }
            
            $variantDetails = $this->sdk->getDecisions($collectionid, $goalFilter, $groupid);
            $persolp = self::getLpPersonalization($projectDetails->personalizationmode, $variantDetails, $ruleInfo);
            $pagelist = array();
            foreach ($persolp as $key => $value) {
                $pagelist[$key] = $value['name'];
            }

            $myDetails = $isTT ? $groupDetails : $projectDetails;
            $status = $myDetails->status == 'UNSET' ? 0 : ($myDetails->status == 'PAUSED' ? 1 : 2);
            $visitors = $myDetails->visitors;

            $g = json_encode(array(
                'id' => '-1',
                'name' => 'page 1',
                'url' => $projectDetails->mainurl,
            ));
            $pageGroups = json_decode($g);

            $ctrlAllocation = 0;
            foreach ($variantDetails as $variant) {
                if ($variant->type == 'CONTROL') {
                    $ctrlAllocation = $variant->allocation;
                    break;
                }
            }

            $data = array(
                'isTT' => $isTT,
                'isMpt' => $isMpt,
                'action' => $action,
                'title' => $this->lang->line('title_collectiondetails'),
                'clientid' => $clientid,
                'collectionid' => $collectionid,
                'originalid' => $projectDetails->originalid,
                'groupid' => $groupid,
                'groupDetails' => $groupDetails,
                'code' => $customData->code,
                'testtype' => $this->testypeCodes[$projectDetails->type],
                'interface' => isset($config->TT_INTERFACE_TYPE) ? $config->TT_INTERFACE_TYPE : FALSE,
                'config_selector' => isset($config->DEFERRED_IMPRESSION_SELECTOR) ? $config->DEFERRED_IMPRESSION_SELECTOR : '',
                'config_action' => isset($config->DEFERRED_IMPRESSION_SELECTOR_ACTION) ? $config->DEFERRED_IMPRESSION_SELECTOR_ACTION : '',
                'ip_filter_list' => isset($config->IP_FILTER_IPLIST) ? $config->IP_FILTER_IPLIST : '',
                'ip_filter_action' => isset($config->IP_FILTER_ACTION) ? $config->IP_FILTER_ACTION : 'not_used',
                'ip_filter_scope' => isset($config->IP_FILTER_SCOPE) ? $config->IP_FILTER_SCOPE : 'all',
                'autopilot' => $projectDetails->autopilot == 'PAUSED' ? 0 : 1,
                'allocation' => $projectDetails->allocation / 100,
                'ctrlAllocation' => $ctrlAllocation,
                'has_timer' => hasCampaignTimer(),
                'start_date' => date('d.m.Y', strtotime($projectDetails->startdate)),
                'start_time' => date('H:i:s', strtotime($projectDetails->startdate)),
                'end_date' => date('d.m.Y', strtotime($projectDetails->enddate)),
                'end_time' => date('H:i:s', strtotime($projectDetails->enddate)),
                'collectioncode' => $customData->code,
                'collectionname' => $projectDetails->name,
                'collectionstatus' => $status,
                'control_url' => $projectDetails->mainurl,
                'controlpageurl' => $controlUrl,
                'control_pattern' => $projectDetails->runpattern,
                'tracking_approach' => $customData->tracking_approach,
                'tracked_goals' => $this->mvt->getCovertedTrackedGoals($customData->tracked_goals),
                'visitorcount' => $visitors,
                'ip_blacklist' => $clientData->ipblacklist,
                'smscount' => $customData->smartmessage,
                'smartmessage' => $customData->smartmessage,
                'display_summary' => TRUE,
                'display_status' => $isTT ? FALSE : TRUE,
                'ignore_ip_blacklist' => $projectDetails->ipblacklisting ? 0 : 1,
                'breadcrumb_url_1' => base_ssl_url() . 'lpc/lcd/' . $collectionid,
                'breadcrumb_text_1' => $projectDetails->name,
                'headimg' => base_ssl_url() . 'images/logo_sml.png',
                'variantsdata' => $this->landingpagecollection->getEditorData($collectionid, $pageGroups),
            );

            if ($isTT) {
                $data['summary_headline'] = $groupDetails->name;
            } else {
                $data += self::getProjectSummary($projectDetails, $variantDetails, $pagelist, $customData->smartmessage);
            }

            $persodata = self::getPersoData($projectDetails, $persolp, $ruleInfo);
            $data += self::getCssArray($isTT);
            $data += self::getJsArray();
            $data += self::getAdditionalCssAndJs($projectDetails);
            $data += self::getCollectionGoals($collectionid, $urlGetGoal);
            $data += self::getTrackingCodeData($collectionid);
            $data += self::getOutputData($projectDetails, $variantDetails, $persolp);
            $data += $persodata;
            $persodata['collectionid'] = $collectionid;
            $html = $this->load->view('includes/protected_header', $data, TRUE);
            $html .= $this->load->view('protected/landingpagecollectiondetails.php', $data, TRUE);

            $html .= $this->load->view('client-testpage-popups/details_page_personalization.php', $persodata, TRUE);
            $html .= $this->load->view('client-testpage-popups/personalization_popup', NULL, TRUE);
            $html .= $this->load->view('protected/test-page-add-ons.php', $data, TRUE);

            if ($isTT) {
                $tt_data = array(
                    'original' => array(),
                    'variants' => array(),
                );
                
                foreach ($variantDetails as $key => $value) {
                    if ($value->type == 'CONTROL') {
                        $tt_data['original'] = $value;
                    } else {
                        $tt_data['variants'][] = $value;
                    }
                }
                
                $html .= $this->load->view('client-testpage-popups/teasertests_popups.php', $tt_data, TRUE);
            }
            
            $html .= $this->load->view('includes/public_footer', $data, TRUE);

            return $html;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /*     * *************************************************************************** */

    /**
     * This function is called via AJAX (see: BT-cdchart.js) to get all impressions/conversions
     * statistics for every variant in a project
     */
    public function getCdChart() {
        try {
            $start = $this->input->get('start') ? $this->input->get('start') : -1;
            $lpcid = $this->input->get('lpcid');
            $groupid = $this->input->get('groupid');
            $goalid = $this->input->get('goalid');
            $interval = $this->input->get('timeinterval');
            $firstdatapoint = $this->landingpagecollection->getReferenceEvent($lpcid, $groupid, 'MIN');
            $lastdatapoint = $this->landingpagecollection->getReferenceEvent($lpcid, $groupid, 'MAX');

            // get goals and set type of goal
            $this->getCollectionGoals($lpcid,$goalid);

            $nextdate = FALSE;
            $previousdate = FALSE;
            
            $variantDetails = $this->sdk->getDecisions($lpcid, $goalid, $groupid);

            $param_startdate = $start == -1 ? date('Y-m-d', strtotime($lastdatapoint)) : date('Y-m-d', strtotime($start));
            $default_startdate = date('Y-m-d', strtotime($lastdatapoint . ' -29 days'));
            $startdate = date('Y-m-d', min(array(strtotime($param_startdate), strtotime($default_startdate))));

            if (strtotime($startdate) > strtotime($firstdatapoint)) {
                $previousdate = date('Y-m-d', strtotime($startdate . ' -30 days'));
            }
            if (strtotime($startdate . ' +30 days') < strtotime($lastdatapoint)) {
                $nextdate = date('Y-m-d', strtotime($startdate . ' +30 days'));
            }

            $enddate = $nextdate == NULL ? $lastdatapoint : date('Y-m-d', strtotime($startdate . ' +29 days'));

            $trendFilter = $groupid == -1 ? 'end=' . $enddate : '';
            $trendFilter .= $goalid && strlen($trendFilter) > 0 ? '&' : '';
            $trendFilter .= $goalid ? 'goalid=' . $goalid : '';
            $trendFilter .= $interval && strlen($trendFilter) > 0 ? '&' : '';
            $trendFilter .= $interval ? 'interval=' . $interval : '';
            $trend = $this->sdk->getTrend($lpcid, $trendFilter, $groupid);
            $maxCR = 0;
            $maxTraffic = 0;

            $impressions = $impcopy = array();
            $crs = array();
            foreach ($trend as $date => $decisions) {
                $imp = 0;
                foreach ($decisions as $name => $decision) {
                    $imp += $decision->impressions;

                    if (is_null($decision->aggregatedcr)) {                        
                        $cr = 'null';
                    } else if ($this->isTimeGoal) {
                        $cr = sprintf("%.2f", $decision->aggregatedcr / 60);
                    } else if ($this->isPILiftGoal) {
                        $cr = number_format($decision->aggregatedcr, 1);
                    } else {
                        $cr = number_format(100 * $decision->aggregatedcr, 2);
                    }
                    $maxCR = ($cr != 'null' && $cr > $maxCR) ? $cr : $maxCR;
                    $crs[$name] = is_string($crs[$name]) ? $crs[$name] . ',' . $cr : $cr;
                }
                array_push($impressions, $imp);
                $maxTraffic = $imp > $maxTraffic ? $imp : $maxTraffic;
            }
            
            $impcopy = $impressions;

            if ($interval && $interval != OPT_TREND_DAY) {
                switch ($interval) {
                    case OPT_TREND_5MINUTE:
                    case OPT_TREND_MINUTE:
                        $val = $interval == OPT_TREND_5MINUTE ? 5 : 1;
                        $datesub = 'PT' . $val * 29 . 'M';
                        break;
                    case OPT_TREND_HOUR:
                        $datesub = 'PT29H';
                        break;
                }
                $ed = new DateTime($enddate);
                $ed->sub(new DateInterval($datesub));
                $startdate = $ed->format('Y-m-d H:i:s');
            }

            if ($this->isTimeGoal && $maxCR <= 1) {
                foreach ($crs as &$cr) {
                    $conversionrates = split(',', $cr);
                    $newcr = array_map('arrayMinutesToSeconds', $conversionrates);
                    $cr = implode(',', $newcr);
                }
                $maxCR *= 60;
            }

            if ($maxTraffic > 0) {
                $scale = 0.3 * $maxCR / $maxTraffic;
                foreach ($impressions as &$impression) {
                    $impression = $impression * $scale;
                }
                unset($impression);
            }

            $charttitle = NULL;
            $charttitle = date('d.m.Y', strtotime($startdate)) . ' - ' . date('d.m.Y', strtotime($enddate));

            $chartdata = array(
                'previousdate' => $previousdate,
                'nextdate' => $nextdate,
                'charttitle' => $charttitle,
                'imptitle' => $this->lang->line('table_traffic_tooltip'),
                'crtitle' => $this->lang->line('table_cr'),
            );
            $chartdata += self::getChartProperties($variantDetails, $startdate, $enddate, $interval, $impcopy, $crs, $maxCR);

            echo json_encode($chartdata);
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * Returns an array with all data necessary to build the chart animation.
     * this includes:
     *      - the chart title ( 01.01.2015 - 30.01.2015 )
     *      - the tooltips (on mouse hover it shows the variant name and its CR), 
     *      - square amount (chart blue squares X and Y)
     *      - axis values,
     *      - line width and color (per each variant).
     * @param Array $variants - variantID => variantName
     * @param String $startdate - the first date to be displayed in the chart
     * @param String $enddate - the last date to be displayed in the chart
     * @param Array $impressions - containing the total number of impressions for every day (the sum)
     * @param Array $conversions - contains X subarrays with the number of conversions PER day PER variant (X variants)
     * @param Float $maxCR - The maximum value out of all conversion rates considering all variants
     * @return Array
     */
    private function getChartProperties($variants, $startdate, $enddate, $selectedInterval, $impressions, $conversions, $maxCR) {
        $dateformat = 'd.m.Y';
        $axisformat = 'd';
        $axisadd = 'days';
        $axismult = 1;

        if ($selectedInterval && $selectedInterval != OPT_TREND_DAY) {
            $dateformat = 'd.m.Y H:i';
            $interval = 0;
            $m = date('i', strtotime($startdate));

            switch ($selectedInterval) {
                case OPT_TREND_5MINUTE:
                    $axismult = 5;
                    $axisadd = 'minutes';
                    $axisformat = 'i';
                    $interval = $m % 5;
                    break;
                case OPT_TREND_MINUTE:
                    $axismult = 1;
                    $axisadd = 'minutes';
                    $axisformat = 'i';
                    break;
                case OPT_TREND_HOUR:
                    $axisadd = 'hours';
                    $axisformat = 'H';
                    $interval = $m;
                    break;
            }
            $start = new DateTime($startdate);
            $start->sub(new DateInterval('PT' . $interval . 'M'));
            $startdate = $start->format('Y-m-d H:i');

            $end = new DateTime($enddate);
            $end->sub(new DateInterval('PT' . $interval . 'M'));
            $enddate = $end->format('Y-m-d H:i');
        }
        
        $y_ceil = $this->isTimeGoal ? 60 : 100;

        $x_axis = '';
        for ($i = 0; $i < 30; $i++) {
            $x_axis .= $i > 0 ? ',' : '';
            $val = $i * $axismult;
            $x_axis .= date($axisformat, strtotime($startdate . " +$val $axisadd"));
        }

        $maxY = $maxCR > 0 ? ceil($maxCR) : $y_ceil;
        $yLegend = $this->lang->line('table_y_legend');

        if ($this->isTimeGoal) {
            if ($maxY <= 1) {
                $maxY *= 60;
                $yLegend = $this->lang->line('table_y_seconds_leyend');
            } else {
                $yLegend = $this->lang->line('table_y_minutes_leyend');
            }
        } else if ($maxY % 5 > 0) {
            $maxY += 5 - $maxY % 5;
        }
        if ($this->isPILiftGoal) {
            $yLegend = $this->lang->line('table_y_pi_legend');
        }

        $chartproperties = array(
            'x_labels' => $x_axis,
            'y_legend' => $yLegend,
            'y_min' => 0,
            'y_max' => $maxY,
            'maximp' => max($impressions),
            'impressions' => implode(",", $impressions),
            'conversions' => array(),
            'vnames' => array(),
            'colors' => array(),
        );

        foreach ($conversions as $key => $value) {
            $val = $value;
            $chartproperties['conversions'][] = $val;
        }

        $count = 0;
        $colors = $this->config->item('COLORS');

        foreach ($variants as $variant) {
            $pagename = $variant->type == 'CONTROL' ? $this->lang->line('table_controlpagename') : $variant->name;
            $chartproperties['vnames'][] = $pagename;
            $chartproperties['colors'][] = $colors[($count) % sizeof($colors)];
            $count++;
        }
        $chartproperties['colors'][] = '#BCD4EE';

        return $chartproperties;
    }

    /*     * *************************************************************************** */

    /**
     * gets all parameters sent from the JS front end and depending on if it is an edition or a new test, calls
     * the corresponding methods
     * 
     * @return void - if everything goes well, echoes the last inserted LPC id (or -1) if the clientid is not set
     */
    public function save() {
        $clientid = getClientIdForActionFromUrl();
        $persolevel = getPersoLevel();
        $savestep = $this->input->post('savestep');

        if (!$clientid) {
            echo "-1";
            return;
        }

        try {
            $lpcid = $this->input->post('collectionid');
            dblog_debug($savestep . ' -- ' . $lpcid);
            if ($savestep != 'goals' && ($savestep == 'approach' || !$lpcid)) {
                $lpcid = self::createOrUpdateSplitProject($persolevel, $lpcid * 1);
                dblog_debug('CREATE');
            }

            if ($savestep == 'allocation') {
                $lpcid = $this->session->userdata("editor_collectionid");
                saveProjectAllocation($lpcid, $this->sdk);
                echo $lpcid;
                return FALSE;
            }


            if ($savestep != 'goals') {
                dblog_debug('VARIANTS');
                self::saveSplitVariants($lpcid, $persolevel);
            }

            if ($savestep == 'goals' || !$this->input->post('collectionid')) {
                dblog_debug('GOALS: ' . $lpcid);
                saveProjectGoals($this->sdk, $lpcid);
            }
            echo $lpcid;
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
     * @param String $persolevel - either "available" or "disabled" depending on the userplan
     * @param type $lpcid - The LPC id to be updated or 0 if the project is new
     * @return Int - The new LPC id or the current LPC id (dependin on if "lpcid" was 0 or >0
     */
    private function createOrUpdateSplitProject($persolevel, $lpcid = 0) {
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
            'runpattern' => $runpattern,
            'allocation' => (!$allocation || $allocation < 0 || $allocation > 1) ? 100 : $allocation * 100,
            'startdate' => $start_date,
            'enddate' => $end_date,
            'ipblacklisting' => $this->input->post("ignore_ip_blacklist") == 0 ? TRUE : FALSE,
            'IP_FILTER_IPLIST' => $this->input->post('ip_filter_list'),
            'IP_FILTER_ACTION' => $this->input->post('ip_filter_action'),
            'IP_FILTER_SCOPE' => $this->input->post('ip_filter_scope')
        );

        if ($lpcid == 0) {
            $p = self::addCreateProjectFields($project, $persolevel);
            $project = $this->dbconfiguration->saveProjectConfigurationToDatabase($p, FALSE);
            $lpcid = $this->sdk->createProject($project);
            return $lpcid;
        } else {
            if ($this->input->post("controlpagename")) {
                $project['mainurl'] = $this->input->post("controlpagename");
            }
            $old_project = $this->sdk->getProject($lpcid);
            $project = $this->dbconfiguration->saveProjectConfigurationToDatabase($project, $old_project->config);
            $this->sdk->updateProject($lpcid, $project);
            return $lpcid;
        }
    }

    /**
     * When creating a new project, it is necessary to add required fields as well as RW fields that are set only on "create" mode
     * like type, mainurl, persomode and control rule ID (if any)
     * @param array $project - the current project array to be inserted with name, runpattern, startdate, etc..
     * @param String $persolevel - either "available" or disabled
     */
    private function addCreateProjectFields($project, $persolevel) {
        $persomode = $this->input->post("perso-type-selection") != 0 ? $this->input->post("perso-type-selection") : 'NONE';
        if ($persomode != 'NONE') {
            $persomode = $persomode == 1 ? 'COMPLETE' : 'SINGLE';
        }
        $cRule = $this->input->post("variant_persorule_0");
        $controlRule = ($persomode == 'COMPLETE' && (int) $cRule > 0 && $persolevel != 'disabled') ? $cRule : 0;

        $project['name'] = $this->input->post("testname");
        $project['type'] = 'SPLIT';
        $project['mainurl'] = $this->input->post("testurl");
        $project['personalizationmode'] = ($persolevel == 'disabled') ? 'NONE' : $persomode;
        $project['ruleid'] = $controlRule;
        return $project;
    }

    /**
     * First, it deletes all pending variants passed as parameter.
     * then, it updates all variants set in the parameter "variantpagehidold"
     * If there are new variants (in create or edit mode) creates them.
     * @param Int $lpcid - the project ID
     * @param String $persolevel - "available" or "disabled" dependin on the client's userplan
     */
    private function saveSplitVariants($lpcid, $persolevel) {
        $v_index = 0;
        $equalAllocation = TRUE;

        if ($this->mode == 'EDIT') {
            $equalAllocation = self::deleteVariantsUpdateAllocations($lpcid);
        }

        $oldVariants = $this->input->post('variantpagehidold') ? $this->input->post('variantpagehidold') : FALSE;

        if ($oldVariants) {
            $oldIds = preg_split('@_@', $oldVariants, NULL, PREG_SPLIT_NO_EMPTY);
            foreach ($oldIds as $oldId) {
                $v_index ++;
                $vname = $this->input->post('variantold' . $oldId) ? $this->input->post('variantold' . $oldId) : 'Variant ' . $v_index;
                $decision = array(
                    'name' => $vname,
                    'url' => canonicalUrl($this->input->post('variantpageold' . $oldId)),
                );
                $this->sdk->updateDecision($lpcid, $oldId, $decision);
            }
        }

        $newVariants = $this->input->post('variantpagehid') ? $this->input->post('variantpagehid') : '';
        if ($newVariants != '') {
            $newIds = preg_split('@_@', $newVariants, NULL, PREG_SPLIT_NO_EMPTY);
            foreach ($newIds as $newId) {
                $v_index ++;
                $vname = $this->input->post($newId . $newId) ? $this->input->post($newId . $newId) : 'Variant ' . $v_index;
                $ruleid = $this->input->post('variant_persorule_' . $newId) ? $this->input->post('variant_persorule_' . $newId) : 0;
                $decision = array(
                    'name' => $vname,
                    'url' => canonicalUrl($this->input->post($newId)),
                    'ruleid' => $persolevel != 'disabled' ? $ruleid : 0,
                    'allocation' => $equalAllocation ? 1 : 0,
                );

                $this->sdk->createDecision($lpcid, $decision);
            }
        }
    }

    /**
     * If there are variants to delete, delete them and then updates the rest of the variant's allocation
     * @param int $lpcid
     * @return bool
     */
    private function deleteVariantsUpdateAllocations($lpcid) {
        $delVariants = $this->input->post("deleteid") ? $this->input->post("deleteid") : FALSE;

        $project = $this->sdk->getProject($lpcid);
        $totalVariants = 0;
        $firstAllocation = -1;
        $equalAllocation = TRUE;
        $unusedAllocations = 0;
        $variants = $this->sdk->getDecisions($lpcid);
        $ctrlAllocation = 0;

        foreach ($variants as $variant) {
            if ($variant->type == 'CONTROL') {
                $ctrlAllocation = $variant->allocation;
                break;
            }
        }

        $allocations = array();
        if ($ctrlAllocation > 0) {
            $allocations[$project->originalid] = $ctrlAllocation;
            $firstAllocation = $ctrlAllocation;
            $totalVariants ++;
        } else {
            $equalAllocation = FALSE;
        }

        foreach ($variants as $variant) {
            if ($variant->type == 'VARIANT') {
                $equalAllocation &= $variant->allocation == $firstAllocation;

                if ($variant->allocation > 0) {
                    $totalVariants ++;
                    $allocations[$variant->id] = $variant->allocation;
                }
            }
        }

        if (!$delVariants) {
            return $equalAllocation;
        }

        $delIds = array();
        if ($delVariants) {
            $delIds = preg_split('@_@', $delVariants, NULL, PREG_SPLIT_NO_EMPTY);
            foreach ($delIds as $delId) {
                if (isset($allocations[$delId])) {
                    $unusedAllocations += $allocations[$delId];
                    $totalVariants --;
                    unset($allocations[$delId]);
                }
                $this->sdk->deleteDecision($lpcid, $delId);
            }
        }

        if (!$equalAllocation && $unusedAllocations > 0) {
            $var = array();
            $alloc = array();
            $splitted = $unusedAllocations / $totalVariants;

            if ($ctrlAllocation > 0) {
                $var[] = $project->originalid;
                $alloc[] = $ctrlAllocation + $splitted;
            }

            foreach ($variants as $variant) {
                if ($variant->type == 'VARIANT' && in_array($variant->id, array_keys($allocations))) {
                    $var[] = $variant->id;
                    $alloc[] = $variant->allocation;
                }
            }

            if (count($var) > 0) {
                saveProjectAllocation($lpcid, $this->sdk, TRUE, $var, $alloc);
            }
        }

        return $equalAllocation;
    }

    /*     * *************************************************************************** */

    /**
     *  Calls the corresponding SDK method to stop or start the project (pause/restart)
     * depending on the $action requested, if everyrhing goes well, echoes the action and the lpcid
     * (e.g. 0-127)
     * 
     * @return VOID/BOOLEAN - FALSE if the clientid is not set.
     */
    function playpausecollection() {
        try {
            $collectionid = $this->input->post('collectionid');
            $clientid = getClientIdForAction(FALSE);
            $action = $this->input->post('action');

            if (!$clientid) {
                return(false);
            }

            if ($action == 0) {
                $this->sdk->stopProject($collectionid);
            } else if ($action == 1) {
                $this->sdk->startProject($collectionid);
            }

            echo $action . "-" . $collectionid;
            return;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * Depending on the action (0 / 1), starts or stops the autopilot by calling the corresponding
     * SDK method
     * NOTE: the passed parameter $action is the current state, so if it is 0, that means that this method
     * has to activate it, if it is 1, this method will pause it.
     * The returned value is inverse to the $action, because that will be the current state after performing
     * the corresponding action.
     * @return String action-collectionid (e.g. 0-127)
     */
    function toggleautopilot() {
        $collectionid = $this->input->post('collectionid');
        $clientid = getClientIdForAction($this->input->post('clientid'));
        $action = $this->input->post('action');

        if (!$clientid) {
            return(false);
        }

        if ($action == 1) {
            $this->sdk->stopAutopilot($collectionid);
            $ret = 0;
        } else {
            $this->sdk->startAutopilot($collectionid);
            $ret = 1;
        }

        echo $ret . "-" . $collectionid;
    }

    /**
     * Deletes a project by calling the corresponding API method.
     * the APC cache deletion is made in the API project controller.
     */
    function deletecollection() {
        try {
            $clientid = getClientIdForAction($this->input->post('clientid'));
            $collectionid = $this->input->post('collectionid');
            $this->sdk->deleteProject($collectionid);
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * calls the corresponding SDK method to restart the project.
     * The API will reset the impressions, conversions, set the restart date to now(), etc.
     * @return type
     */
    function updatecollections() {
        try {
            $collectionid = $this->input->post('collectionid');
            $clientid = getClientIdForAction($this->input->post('clientid'));

            $this->sdk->restartProject($collectionid);

            echo TRUE;
            return;
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo $msg;
        }
    }

    /**
     * Updates the rules in the LP table and the personalization_mode in the LPC table
     */
    public function updateLpRules() {
        $lpcid = $this->input->post('lpcid');
        $persomode = $this->input->post('persomode');
        $lpdata = json_decode($this->input->post('lpdata'));

        $this->landingpagecollection->updateLpcPersomode($lpcid, $persomode);
        foreach ($lpdata as $lpd => $value) {
            $this->landingpagecollection->updateLandingpagePerso($lpd, $value->rule_id);
        }
        $this->optimisation->flushCollectionCache($lpcid);
        echo TRUE;
    }

    /*
     *  controller for duplicate collection
     */

    function duplicatecollection() {
        $thisClientId = $this->input->post('clientid');
        $clientid = getClientIdForAction($thisClientId);
        $testid = $this->input->post('testid');
        $testname = $this->input->post('testname');
        $colid = $this->landingpagecollection->duplicateTest($testid, $clientid, $testname);
        if ($colid) {
            // use the API to care for duplication of collection_goal_conversions
            $goals = $this->sdk->getGoals($colid);
            if($goals) {
                $mygoal = get_object_vars($goals[0]);
                $id = $mygoal['id'];
                unset($mygoal['id']);
                $this->sdk->updateGoal($colid,$id,$mygoal);
            }
            $this->optimisation->updateslots(OPT_SLOTS_EQUIDIST, $colid);
            echo $colid;
            return 0;
        }
        echo -1;
    }

    /*
     * 
     *  function find out chart start point or end point
     */

    function graphpoints($up = '', $collectionid = '') {
        header('Content-type: application/x-javascript');
        ?>
        chart();
        function chart()
        {
        var path=document.getElementById('path').value;
        countval = $("#count").val();
        splitval =  countval.split("-");  
        splitcount = splitval.length; 
        var up = "<?php echo $up; ?>";
        colorids    = "0-";
        referlist="";
        varids  = $("#control").val()+"-";
        for(var i=0;i<=splitcount-2;i++)
        {
        id = "variant_"+splitval[i];
        if($('input[name='+id+']').attr('checked'))
        {
        var colorid = $('input[name='+id+']').attr('id');
        colorids    = colorids+colorid+"-";
        varids  = varids+splitval[i]+"-";
        }
        }
        var count = $("#refercount").val();
        for(var i=1;i<=count;i++)
        {
        id="refer_"+i;
        if($('input[name='+id+']').attr('checked'))
        {
        var refer = $('#'+id).val()+"~checked-";
        referlist = referlist+refer;
        }
        else
        {
        var refer = $('#'+id).val()+"~unchecked-";
        referlist = referlist+refer;
        }
        }
        $("#eventname").val("load")
        referlist=referlist.replace(/\//g, ' ');
        filter = $('#filter').val();
        collectionid= $('#collectionid').val();
        tmp = findSWF("chart");
        calltype='webservice';
        var x= tmp.reload(path+"lpc/chartdata/" + up + "/" + varids + "/" + colorids + "/"  + collectionid + "/" + calltype +"/"+ referlist + "/" + filter +"/");
        }
        <?php
    }

    /*
     * Create a tracecode for the diagnose controller (see below)
     * Returns the created tracecode
     */

    function createTracecode() {
        $clientid = $this->session->userdata('sessionUserId');
        if (!$clientid) {
            exit(0);
        }
        $code = substr(md5(uniqid()), 0, 4);
        $this->adminmodel->setTracecode($code);
        echo $code; // DM: changed from return $code; to echo $code; for the AJAX request
    }

    /*
     * Ajax-Response for Diagnose feature
     * After /bto/diagnose has been called, this controller reads the content in table tracecode
     * (written by /bto/diagnose) and sends a JSON response to the frontend for display of the diagnose results
     * 
     * // DM: Changed $tracecode=$this->input->get... for $tracecode=$this->input->post... (POST parameters for AJAX calls)
     */
    function trt() {
        $tracecode = $this->input->post('tracecode', TRUE) ? urldecode($this->input->post('tracecode', TRUE)) : 'NA';
        $mycollectionid = $this->input->post('collectionid', TRUE) ? urldecode($this->input->post('collectionid', TRUE)) : 'NA';
        if (!ctype_alnum($tracecode))
            $tracecode = 'NA';
            //echo "tracecode: $tracecode";
        $response = array();

        $td = $this->optimisation->getTracecodeData($tracecode);
        session_write_close(); // DM: Added by Eckhard to handle a request "collission" issue
        if ($td) {
            $response['issue'] = 0; // init
            $response['delivery_status'] = 1; // init
            $webserviceresponded = false; // init
            // if field content is not filled, go in a cycle of sleeping and waking up again
            // wait max. 16 seconds
            for ($i = 0; $i < 2; $i++) {
                sleep(pow(2, $i));
                $td = $this->optimisation->getTracecodeData($tracecode); // refresh tracecode data
                if ($td['content'] != '') {
                    $webserviceresponded = true; // indicates that bto/diagnose has responded and written content
                    // to the tracecode table
                    break;
                }
            }
            if ($webserviceresponded) {
                $content = $td['content'];
                $client = $content['client'];
                $loggedin_client = $this->user->clientdatabyid($this->session->userdata('sessionUserId'));
                //print_r($td);
                // check for quota exceeded
                if (is_array($client) && ($client['used_quota'] >= $client['quota'])) {
                    $response['issue'] = 1;
                    $response['client_status'] = $client['status'];
                    $response['quota'] = $client['quota'];
                    $response['quota_reset_date'] = $client['quota_reset_dayinmonth'];
                } elseif ($loggedin_client['clientid_hash'] != $content['cc']) { // check wether logged in client has permission for the test page
                    $response['issue'] = 6;
                    $response['clientcode_login'] = $loggedin_client['clientid_hash'];
                    $response['clientcode_tested'] = $content['cc'];
                } elseif ($content['hasActiveTests'] == false) { //  check for active tests
                    $response['issue'] = 2;
                } else {
                    $response['match'] = $content['match'];
                    $response['et_pagename'] = $content['et_pagename'];
                    // iterate over matching tests and check for conflicts
                    $matching_tests = array();
                    $finalCheckArray = array();
                    foreach($content['matching_tests'] as $matching) {
                        $clientid = $client['clientid'];
                        $collectionid = $matching[3];
                        $collectionstatus = $this->optimisation->getcollectionstatusById($collectionid,$clientid);
                        $conflictCheckEvent = array(
                            'collectionid' => $collectionid,
                            'name' => '',
                            'pattern' => '',
                            'testtype' => $collectionstatus['testtype'],
                            'status' => $collectionstatus['status'],
                            'isSmartMessage' => (($collectionstatus['smartmessage'] == 1) ? true:false)
                        );
                        $checkArray = $finalCheckArray;
                        $checkArray[] = $conflictCheckEvent;
                        $result = $this->optimisation->determineCollectionConflicts($checkArray,$collectionid);
                        if(!$result['hasConflicts']) {
                            $finalCheckArray[] = $conflictCheckEvent;
                            $matching[4] = 0;
                            if($mycollectionid == $matching[3])
                                $response['delivery_status'] = 0; // current project is delivered
                        }
                        else {
                            $matching[4] = $result['affectedProjects'][$collectionid];
                            if($mycollectionid == $matching[3])
                                $response['delivery_status'] = 2; // current project is not delivered due to conflicts
                        }
                        $matching_tests[] = $matching; 
                    } 
                    $response['matching_tests'] = $matching_tests;
                }
            } else {
                $response['issue'] = 5; // indicates: no response from webservice, probem with trackingcode
            }
        } else {
            // fatal error, no tracecode entry found
            $response['issue'] = -1;
        }
        $response_json = json_encode($response);
        echo $response_json;
    }

    // export the project as a static JS file for use with BlackTri Optimizer Sandbox
    public function sandboxExport($collectionid, $clientid) {
        try {
            $clientData = $this->sdk->getAccount($clientid);
            if ($clientData->status == 'UNSET') {
                return FALSE;
            }

            $goalFilter = FALSE;
            $customData = $this->landingpagecollection->getCustomLpcData($collectionid);
            $projectDetails = $this->sdk->getProject($collectionid);
            $variantDetails = $this->sdk->getDecisions($collectionid, $goalFilter, -1);

            // only for visual and split tests
            if(($projectDetails->type!='SPLIT')&&($projectDetails->type!='VISUAL')) {
                echo "// no project to export";
            }
            else {
                $data = array(
                    'projectDetails' => $projectDetails,
                    'variantDetails' => $variantDetails
                );
                $this->load->view('protected/sandboxExport', $data);                
            }
        } catch (Exception $ex) {
            $msg = $ex->getCode() . ', ' . $ex->getMessage();
            dblog_debug($msg);
            echo "// " . $msg;
        }
    }


}

// class ends here
?>