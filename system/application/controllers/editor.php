<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class editor extends CI_Controller {

    private $tenant;
    private $clientid;
    private $base_ssl_url;
    private $collectionid = FALSE;
    private $collectiontype = FALSE;
    private $client_url;
    private $control_pattern;

    /**
     * $edit_type choices are "edit" or "wizard"
     * "wizard" works for both registered clients and non registered guests
     */
    private $edit_type;
    private $editor_url;
    private $editor_proxy_url;
    private $is_visitor;
    private $new_sms = false;
    private $add_ons;
    private $sdk;
    private $device_type = 'desktop';
    private $nossl = false;
    private $tracking_code_present = false;
    private $final_client_url = '';

    function __construct() {
        parent::__construct();
        /**
         * Define global params
         */
        doAutoload(); // load resources from autoload_helper
        $this->tenant = $this->config->item('tenant');
        $this->clientid = $this->session->userdata('sessionUserId');
        $this->is_visitor = empty($this->clientid);
        $this->base_ssl_url = $this->config->item('base_ssl_url');
        $this->editor_url = $this->config->item('base_ssl_url') . 'editor/';
        $this->editor_proxy_url = $this->config->item('editor_url');
        $this->config->load('personalization');

        $this->loadLibraries();
        $this->loadModels();
        $this->add_ons = array(
            'styles_edit' => array('active' => TRUE)
        );

        define('API_URL', $this->config->item('base_ssl_url') . 'api/v1/', TRUE);
        require_once APPPATH . 'controllers/apiv1sdk.php';
        $this->sdk = new apiv1sdk();
        $this->sdk->__set('clientid', $this->session->userdata('sessionUserId'));
        $this->sdk->__set('apikey', $this->config->item('bt_apikey'));
        $this->sdk->__set('apisecret', $this->config->item('bt_apisecret'));
    }

    /**
     * Used to post new SMS data from form in views/new_editor/sms_ui.php
     */
    function postNewSMS() {
        $sms = $this->input->post('sms');
        $url = trim($this->input->post('url'));

        if ($sms && $url && $url != '') {
            $this->session->set_userdata('newSms', $sms);
            header('Location: ' . $this->base_ssl_url . 'editor?url=' . urlencode($url));
        }
    }

    /**
     * Loads SMS UI in standalone page
     */
    function createSMS() {
        if (!$this->clientid) {
            lang_redirect('login');
        }
        /* load SMS UI in standalone page that will post data to editor */
        $isEditor = FALSE;
        $this->loadSmsUi($isEditor);
    }

    /**
     * Loads the SMS UI in iFrame in editor
     */
    function sms() {
        $isEditor = TRUE;
        $this->loadSmsUi($isEditor);
    }

    /**
     * Loads the SMS UI in iFrame in dash
     */
    function sms_dash() {
        $isEditor = FALSE;
        $this->loadSmsUi($isEditor);
    }

    /**
     * Helper function to load SMS UI view either in editor iFrame or standalone page
     * @param bool $isEditor
     */
    private function loadSmsUi($isEditor = FALSE) {
        $this->lang->load('sms-ui');
        $this->load->helper('featurematrix_helper');

        $data['sms_features'] = array(
            'has_branding' => hasSmSBranding(),
            'allowed_triggers' => getSmSTrigger()
        );

        $data['jsdir'] = $this->base_ssl_url . '/js/BT-editor/sms/';
        $data['baseurl'] = $this->base_ssl_url;
        $data['isEditor'] = $isEditor ? 'true' : 'false';
        /* todo create lang config for SMS page title */
        $data['pageTitle'] = $this->tenant == 'blacktri' ? "Create SMS" : '';
        $data['postNewSmsUrl'] = $this->base_ssl_url . 'editor/postNewSMS';

        $data['translations'] = array(
            'headings' => $this->lang->line('sms_ui_headings'),
            'descriptions' => $this->lang->line('sms_ui_descriptions'),
            'buttons' => $this->lang->line('sms_ui_buttons'),
            'messages' => $this->lang->line('sms_ui_messages')
        );

        $this->load->view('new_editor/sms_ui', $data);
    }
    //create test
    public function index() {
        if (!$this->session->userdata('newUrlData')) {
            $this->session->unset_userdata('clickGoals');
            $this->session->unset_userdata('goalsToDelete');
        }
        $this->initWizard();
    }

    /* allows testing visitor version while still logged in */

    public function visitor() {
        $this->is_visitor = TRUE;
        $this->initWizard();
    }

    /**
     * Editor in "ewizard" mode (Create a new test)
     * @return boolean
     */
    private function initWizard() {
        if (!self::redirectNonSslScript(FALSE)) {
            return FALSE;
        }
        
        $edit_type = 'wizard';
        $client_url = $this->getUserInputURL();
        $this->setCriticalParams($edit_type, $client_url);
        $this->loadEditorHeader();
        $this->loadEditorBody(FALSE);
        $this->loadWizardSteps();
        $this->loadEditorAddOns();

        $this->load->view('client-testpage-popups/multipagetest_popups.php');
        $this->load->view('new_editor/editor_footer');
    }

    /**
     * Editor in "edit" mode, (modify an existing test)
     * @param Int $collectionid - the test id
     * @return type
     */
    public function edit($collectionid) {
        if (!$this->clientid) {
            lang_redirect('login');
        }
        
        if (!$this->session->userdata('newUrlData')) {
            $this->session->unset_userdata('clickGoals');
            $this->session->unset_userdata('goalsToDelete');
        }

        $edit_type = 'edit';
        $id = intval($collectionid);
        if (empty($collectionid) || !is_int($id) || $id < 1) {
            die("Invalid test ID");
        }

        $client_url = $this->getExistingTestUrl($collectionid);
        if (isset($client_url)) {
            $this->client_url = $client_url;
        }
        
        $newUrlData = $this->session->userdata('newUrlData');
        if ($newUrlData) {
            $newData = json_decode($newUrlData);
            $client_url = $newData->newUrl;
        }

        if (empty($client_url)) {
            die("Invalid client url");
        }
        $this->session->set_userdata('site_info', false);

        if (!self::redirectNonSslScript(TRUE)) {
            return FALSE;
        }

        $project = $this->sdk->getProject($collectionid);
        $this->collectiontype = $project->type;
        if ($project->devicetype) {
            $this->device_type = $project->devicetype;
        }

        /* ue controller needs this for saving existing tests */
        $this->session->set_userdata('editor_collectionid', $collectionid);

        $this->setCriticalParams($edit_type, $client_url, $collectionid);
        $this->loadEditorHeader();
        $this->loadEditorBody($project->type);
        $this->loadWizardSteps();
        $this->loadEditorAddOns();

        $this->load->view('client-testpage-popups/multipagetest_popups.php');
        $this->load->view('new_editor/editor_footer');
    }

    /**
     * This is a common function for "wizard" and "edit" mode, it verifies if the loaded page
     * to avoid cross domain policy issues.
     * it makes use of a custom <script> to be able to verify url parameter and other data
     * @return type
     */
    private function redirectNonSslScript($edit = FALSE){
        $client_url = $this->getUserInputURL();

        if (empty($client_url)) {
            $client_url = $this->client_url;
        }
		
        $newUrlData = $this->session->userdata('newUrlData') ? json_decode($this->session->userdata('newUrlData')) : '-1';

        if ($edit && $newUrlData != "-1" && $newUrlData->newUrl != $client_url) {
            $client_url = $newUrlData->newUrl;
        }

        // on some servers we can not identify wether the current editor URL uses SSL or not
        // because server vars don't tell us (e.g. when we use an SSL front loader). Thus we 
        // assume that the initial request of the editor is always with SSL, and we attach
        // a querystring parameter to store the protocol for future redirects
        $redirected = $this->input->get('redir') == 'yes';
        if(!$redirected) {
            $editorProtocolIsHttps = true;
        }
        else {
            $editorProtocolIsHttps = ($this->input->get('protocol') == 'ssl') ? true : false;
        }

        $siteInfo = $this->session->userdata('site_info');
        $processed = $siteInfo['url'] == $client_url;
        if (!$processed) {
            $siteInfo = $this->getUrlInfo($client_url);
            $this->session->set_userdata('site_info', $siteInfo);
        }

        $this->nossl = !$siteInfo['urlIsSecure'];
        $this->tracking_code_present = $siteInfo['hasTrackingCode'];

        $this->final_client_url = $siteInfo['redirectUrl'];

        $sessionNoRedirect = !$redirected;
        $secureButSslOff = $siteInfo['urlIsSecure'] && !$editorProtocolIsHttps;
        $notSecureButSslOn = !$siteInfo['urlIsSecure'] && $editorProtocolIsHttps;
        if ($edit) {
            $notSecureButSslOn = $notSecureButSslOn && !$siteInfo['redirectsToSsl'];
        }

        //moved override here to avoid other issues below
        if ($this->nossl) {
            $this->overrideNonSsl();
        }

        if ($sessionNoRedirect || $notSecureButSslOn || $secureButSslOff) {
            $data = array(
                'sessionNoRedirect' => $sessionNoRedirect ? 'TRUE' : 'FALSE',
                'notSecureButSslOn' => $notSecureButSslOn ? 'TRUE' : 'FALSE',
                'secureButSslOff' => $secureButSslOff ? 'TRUE' : 'FALSE',
                'editorProtocolIsHttps' => $editorProtocolIsHttps ? 'TRUE' : 'FALSE',
            );
            $this->load->view('new_editor/editor_redirect', $data);
            return FALSE;
        }
        return TRUE;
    }

    private function overrideNonSsl() {
        $this->base_ssl_url = $this->config->item('base_url');
        $this->editor_url = $this->config->item('base_url') . 'editor/';
        $this->editor_proxy_url = str_replace("https://", "http://", $this->config->item('editor_url'));
    }

    private function urlIsSecure($url) {
        return stripos($url, 'https://') !== FALSE;
    }

    private function getUrlInfo($url) {
        $ret = array();
        $ret['url'] = $url;
        $ret["urlIsSecure"] = $this->urlIsSecure($url);
        $ret['redirectsToSsl'] = false;
        $ret['hasTrackingCode'] = false;

        $this->load->library('curl');
        $response = $this->curl->create($url)
                ->option(CURLOPT_SSL_VERIFYHOST, false)
                ->option(CURLOPT_SSL_VERIFYPEER, false)
                ->option(CURLOPT_FOLLOWLOCATION, true)
                ->option(CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36")
                ->execute();

        //redirect check
        $info = $this->curl->info;
        if ($info['url'] != '') {
            $ret['redirectsToSsl'] = $this->urlIsSecure($info['url']);
			$ret['redirectUrl'] = $info['url'];
	        $ret["urlIsSecure"] = $this->urlIsSecure($info['url']);
        }

        //tracking code check
        $codeCheck = array("var _btCc", "/gs.js", "/bto.js");
        for ($i = 0; $i < count($codeCheck); $i++) {
            if (stripos($response, $codeCheck[$i]) !== FALSE) {
                $ret['hasTrackingCode'] = true;
                break;
            }
        }

        return $ret;
    }
	
    /**
     * Used only until add abilty to get client_url from a collection ID
     * All the javascript variables are currently hard coded
     */
    public function dev($edit_type = 'edit', $collectionid = 295) {

        /* simple authorization */
        if (!$this->clientid) {
            lang_redirect('login');
        }

        if ($edit_type == 'edit') {
            /* ue controller needs this for saving existing tests */
            $this->session->set_userdata('editor_collectionid', $collectionid);

            $client_url = $this->getExistingTestUrl($collectionid);
        } else {
            /* redirects to editor/invalid_url if bad or missing url entered */
            $client_url = $this->getUserInputURL();
        }

        $this->setCriticalParams($edit_type, $client_url, $collectionid);
        $this->loadEditorHeader();
        $this->loadEditorBody(FALSE);
        $this->loadWizardSteps();
        $this->loadEditorAddOns();
        $this->load->view('new_editor/editor_footer');
    }

    private function getExistingTestUrl($collectionid) {
        return $this->mvt->loadlandingpagecollectioncontrol($this->clientid, $collectionid);
    }

    private function getPageTitle() {
        if ($this->tenant == 'etracker') {
            return '';
        } else {
            return $this->lang->line('Editor Page Title');
        }
    }

    private function loadEditorHeader() {
        if (empty($this->edit_type) || empty($this->client_url)) {
            die('Critical Params not set');
        }
        /* testing upgrade from V2.3 to 4.0 */
        $this->codemirror_dir = 'js/codemirror-4.0';

        $header_resources = array(
            'page_title' => $this->getPageTitle(),
            'editor_url' => $this->editor_url,
            'tenant' => $this->tenant,
            'basesslurl' => $this->base_ssl_url,
            'css' => $this->getCSSList(),
            'js' => $this->getScriptList(),
            'BTEditorVars' => $this->getBTeditorVars(),
            'exist_test_data' => FALSE,
			'tracking_code_present' => $this->tracking_code_present,
			'nossl' => $this->nossl,
			'editor_proxy' => $this->editor_proxy_url
        );
        if ($this->edit_type == 'edit') {
            $header_resources['exist_test_data'] = $this->getExistingTestJSVars();
        }

        $this->load->view('new_editor/editor_header', $header_resources);
    }

    private function setCriticalParams($edit_type, $client_url, $collectionid = FALSE) {
        $this->edit_type = $edit_type;
        $this->client_url = $client_url;
        if ($collectionid !== FALSE) {
            $this->collectionid = $collectionid;
        }
        $this->control_pattern = $this->getControlPattern();
    }

    /**
     * Returns an array with necessary data for the editor to work
     * it contains the project ID, goals, the client id, some texts in the user defined languages, etc.
     * @return Array
     */
    private function getBTeditorVars() {
        $this->lang->load('editorUndoHistory');

        $collectionGoals = array();
        if (is_numeric($this->collectionid) && $this->collectionid > 0) {
            $goals = $this->sdk->getGoals($this->collectionid);
            foreach ($goals as $goal) {
                $type = array_search($goal->type, $this->config->item('api_goals'));
                if ($type) {
                    $collectionGoals[] = array(
                        'collection_goal_id' => $goal->id,
                        'type' => $goal->type,
                        'name' => $goal->name,
                        'pageid' => $goal->page,
                        'status' => $goal->status,
                        'arg1' => $goal->param != 'NA' ? $goal->param : '',
                    );
                }
            }
        }

        $isMpt = $this->edit_type == 'edit' ? $this->collectiontype == 'MULTIPAGE' : $this->session->userdata('isMpt');
        $BTEditorVars = array(
            'isEditor' => true,
            'view' => $this->edit_type,
            'FrameEditorBaseUrl' => $this->editor_proxy_url . "?blacktriurl=",
            'DocDomain' => $this->config->item('document_domain'),
            'conversionGoalsParams' => $this->landingpagecollection->getEtrackerConversionGoalsParamValues(),
            'ClientId' => $this->clientid,
            'BaseSslUrl' => $this->base_ssl_url,
            'goalsData' => $collectionGoals,
            'CollectionId' => $this->collectionid,
            'testPageUrl' => $this->base_ssl_url . 'lpc/lcd/',
            'test_url' => $this->client_url,
            'control_pattern' => $this->control_pattern,
            'enterNameText' => $this->lang->line('Please enter the name here'),
            'lang' => $this->config->item('language_abbr'),
            'is_etracker' => $this->tenant == 'etracker' ? TRUE : FALSE,
            'unload_message' => $this->lang->line('Editor Page Unload'),
            'add_ons' => $this->add_ons,
            'css_reset' => $this->base_ssl_url . 'css/editor/text-editor-reset.css',
            'EditorBaseURL' => $this->editor_proxy_url,
            'EditorProxyURL' => $this->editor_proxy_url . 'btproxy/',
            'show_sms' => isset($this->showSMS),
            'EditorRelURL' => '',
            'EditorDeviceTypes' => $this->config->item('device_types'),
            'EditorDeviceType' => $this->device_type,
            'isMpt' => $isMpt,
            'pagePrefix' => $this->lang->line('Multipage page index'),
            'undo_text' => array(
                'default' => $this->lang->line('Undo change'),
                'actions' => $this->lang->line('editor_undo')
            ),
			'NoSSL' => $this->nossl
        );

        if ($this->is_visitor) {
            $BTEditorVars['visitor_redirects'] = array(
                'proceed' => $this->config->item('editor_visitor_proceed_url'),
                'cancel' => $this->config->item('editor_visitor_cancel_url')
            );
        }

        if ($this->clientid) {
            $BTEditorVars['DashUrl'] = $this->base_ssl_url . 'lpc/cs/' . $this->clientid;
        }

        if ($this->new_sms) {
            $BTEditorVars['newsms'] = $this->new_sms;
        }

        return $BTEditorVars;
    }

    /**
     * This function is called via AJAX from the dashboard when clicking on "create Multipage Test"
     * see js/BT-common/BT-multipagetest.js
     */
    public function setOrUnsetMpTest() {
        if ($this->input->post('isMpt') == 'true') {
            $this->session->set_userdata('isMpt', TRUE);
        } else {
            $this->session->unset_userdata('isMpt');
        }
    }

    /**
     * When url changed and saved in UI it is saved in landingpage.canonical_url
     * @return mixed
     */
    private function getControlPattern(){
        if($this->collectionid){
            return $this->mvt->loadlandingpagecollectioncontrolpattern($this->clientid, $this->collectionid);
        }else{
            return $this->client_url;
        }
    }

    /**
     * first, it verifies that the collection and the client id are set (required in edit mode)
     * then it gets the current variant data, if it is not set, creates a new array with it, it will contain
     * necessary data to reproduce the project configuration in the editor, like dom modification code
     * (JS, CSS), each variant id and name, among other.
     * 
     * Aditionally, the returned array will contain the project personalization mode, the control variant
     * rule id (if set), tracked goals (deprecated), etc.
     * 
     * @return Array
     */
    private function getExistingTestJSVars() {
        if ($this->edit_type == 'edit') {
            if (!$this->collectionid || !$this->clientid) {
                die("Collection ID or clientId not set to get test data");
            }
        }
        
        if ($this->collectiontype == 'MULTIPAGE') {
            $pageGroups = $this->sdk->getDecisionGroups($this->collectionid);
        } else {
            $g = json_encode(array(
                'id' => NULL,
                'name' => 'page 1',
                'url' => $this->editor_url,
            ));
            $pageGroups = json_decode($g);
        }

        $variantsdata = $this->landingpagecollection->getEditorData($this->collectionid, $pageGroups);
        
        if (!$variantsdata) {
            $variantsdata = array(
                "editor_version" => 0.5,
                "activePage" => 'page_1',
                "pageCount" => 1,
                "pages" => array(
                    'page_1' => array(
                        'id' => null,
                        'name' => 'page 1',
                        'url' => $this->editor_url,
                        'variants' => array(),
                    )
                )
            );
            
            $decisions = $this->sdk->getDecisions($this->collectionid);

            $variantidx = 1;
            foreach ($decisions as $decision){
                if($decision->type == 'CONTROL'){
                    continue;
                }

                $variant = array(
                    'version' => 1,
                    'old_version' => 0.5,
                    'name' => $decision->name,
                    'id' => $decision->id,
                    'selectors' => new stdClass(),
                    'dom_modification_code' => array(
                        '[JS]' => strlen($decision->jsinjection) > 0 ? $decision->jsinjection : NULL,
                        '[CSS]' => strlen($decision->cssinjection) > 0 ? $decision->cssinjection : NULL,
                    ),
                );
                
                $variantsdata['pages']['group_1']['variants']['variant_' . $variantidx] = $variant;
                $variantidx++;
            }
        }
        
        $custom = $this->landingpagecollection->getCustomLpcData($this->collectionid);
        $project = $this->sdk->getProject($this->collectionid);
        $tk = $this->mvt->getLPCTrackingCode($this->collectionid, OPT_TESTTYPE_VISUALAB);
        
        $testdata = array(
            'controlrule' => $project->ruleid,
            'persomode' => array_search($project->personalizationmode, $this->config->item('api_persomode')),
            'variantsdata' => $variantsdata,
            'tracked_goals' => $this->mvt->getCovertedTrackedGoals($custom->tracked_goals),
            'tracking_approach' => $custom->tracking_approach,
            'ignore_ipblacklist' => (int) !$project->ipblacklisting,
            'trackingcodedata' => array(
                'lpccode' => $tk['lpccode'],
                'testgoal' => $tk['testgoal'],
                'testname' => $tk['testname'],
                'lpctrackingcode_control' => $tk['lpctrackingcode_control'],
                'lpctrackingcode_success' => $tk['lpctrackingcode_success'],
                'lpctrackingcode_variant' => $tk['lpctrackingcode_variant'],
                'lpctrackingcode_ocpc' => $tk['lpctrackingcode_ocpc']
            ),
        );
        return $testdata;
    }

    private function loadEditorBody($type) {

        $isMpt = $this->edit_type == 'edit' ? $this->collectiontype == 'MULTIPAGE' : $this->session->userdata('isMpt');
        $data = array(
            'edit_type' => $this->edit_type,
            'control_url' => $this->client_url,
            'control_pattern' => $this->control_pattern,
            'editor_url' => $this->editor_proxy_url,
            'tenant' => $this->tenant,
            'clientid' => $this->clientid,
            'is_visitor' => $this->is_visitor,
            'add_ons' => $this->add_ons,
            /* sms integration */
            'show_sms' => isset($this->showSMS),
            'is_smstest' => $this->mvt->LpcIsSmartMessage($this->collectionid),
            'is_mptest' => $isMpt,
            'allowed_variants' => getAllowedVariantsAmount(),
            'css_level' => getCustomCssLevel(),
            'js_level' => getCustomJsLevel(),
            'sms_level' => getSmSLevel(),
            'perso_level' => getPersoLevel(),
            'device_type' => $this->device_type,
            'basesslurl' => $this->base_ssl_url,
			'nossl' => $this->nossl,
			'sessionUrl' => $this->session->userdata('editor_referer'),
			'final_client_url' => $this->final_client_url
        );

        $this->load->view('new_editor/editor', $data);
        $this->load->view('new_editor/editor_popups');
        $this->load->view('client-testpage-popups/personalization_popup', $data);
    }

    private function loadWizardSteps() {
        $isMpt = $this->edit_type == 'edit' ? $this->collectiontype == 'MULTIPAGE' : $this->session->userdata('isMpt');
        $steps_data = array(
            'tenant' => $this->tenant,
            'testtype' => $isMpt ? OPT_TESTTYPE_MULTIPAGE : OPT_TESTTYPE_VISUALAB,
            'client_url' => $this->client_url,
            'available_goals' => $this->config->item('available_goals'),
            'ip_blacklist' => $this->mvt->getIpBlacklistByClient($this->clientid),
            'has_timer' => hasCampaignTimer(),
        );
        
        $this->load->view('new_editor/wizard-steps', $steps_data);
    }

    private function loadEditorAddOns() {
        if ($this->add_ons['styles_edit']['active']) {
            $this->load->view('new_editor/add-ons/element-styles-popup');
        }
    }

    private function loadModels() {
        $this->load->model('landingpagecollection');
        $this->load->model('mvt');
    }

    private function loadLibraries() {

        $this->load->library('form_validation');
        /* need for "splink" function */
        $this->load->helper('support_helper.php');
        $this->load->helper('string_helper.php');

        /* not sure which lang files needed, both of these in lpc controller */
        $this->lang->load('landingpagecollectiondetails');
        $this->lang->load('collectionoverview');

        //lang files
        $this->lang->load('editor');
        $this->lang->load('personalization');
        $this->lang->load('opt');
    }

    private function getScriptList() {
        $jsArray = array(
            'js/console.polyfill.js',
            'js/jquery.Storage.js',
            'js/jquery.json-2.3.js',
            'js/jtable/jquery-ui.js',
            /* 'js/jquery.simulate.js', */ /* not used in page itself */
            $this->codemirror_dir . '/lib/codemirror.js',
            $this->codemirror_dir . '/mode/xml/xml.js',
            $this->codemirror_dir . '/mode/javascript/javascript.js',
            $this->codemirror_dir . '/mode/css/css.js',
            $this->codemirror_dir . '/mode/htmlmixed/htmlmixed.js',
            $this->codemirror_dir . '/addon/hover/text-hover.js',
            'jsi18n/jqueryValidationEngine.js',
            'js/jquery.validationEngine.js',
            'js/fancybox/jquery.fancybox-1.3.4.js',
            'js/fancybox/jquery.easing-1.3.pack.js',
            'js/popup.js',
            /* 'js/nicEdit/nicEdit.js', */
            'js/jquery.ba-postmessage.js',
            /* load all the editor page functions */
            'js/BT-editor/main-new-editor.js',
            'js/BT-common/BT-clickgoals.js',
            'js/BT-common/BT-additionalconfig.js',
            'js/jHtmlArea-0.8.0.ExamplePlusSource/scripts/jHtmlArea-0.8.js',
            'js/BT-editor/BT-editor-communication.js',
            'js/BT-editor/BT-editor-new.js',
            'js/BT-editor/BT-editor-codeEdit.js',
            'js/BT-editor/BT-history-editor.js',
            'js/BT-editor/BT-editor-browsemode.js',
            'js/tooltips/jquery.powertip.min.js',
            'js/BT-editor/perso/perso-rules.js',
            'js/BT-editor/BT-editor-rearrange.js',
            'js/BT-editor/BT-editor-devicetype.js',
            'js/BT-common/BT-urlpattern.js',
            'js/BT-common/BT-multipagetest.js',
        );

        if (hasCampaignTimer()) {
            $jsArray[] = 'js/datepicker/js/datepicker.js';
            $jsArray[] = 'js/dash_testpage_wizards/BT-editor-additional-scripts.js';
        }

        /* colorpicker plugin */
        $jsArray[] = 'js/jQuery-colorpicker/js/colorpicker.js';
        $jsArray[] = 'js/chromoselector-2.1.5/chromoselector.min.js';

        /* new tag data stuff */
        $jsArray[] = 'js/BT-editor/editor-resources/html-tags-data-temporary.js';

        if ($this->add_ons['styles_edit']['active']) {
            $jsArray[] = 'js/BT-editor/editor-resources/style-properties.js';
            $jsArray[] = 'js/BT-editor/BT-styles-editor.js';
        }
        return $jsArray;
    }

    private function getCSSList() {
        $tootltips = ($this->tenant == 'etracker') ? 'js/tooltips/css/jquery.powertip.etracker.css' : 'js/tooltips/css/jquery.powertip.min.css';
        $cssArray = array(
            'css/style.css',
            'css/admin.css',
            'css/admin.css-lower_editor_z-Indexes.css',
            'css/editor-reset.css',
            'js/jquery-ui-1.9.1.custom/css/ui-lightness/jquery-ui.css',
            $this->codemirror_dir . '/lib/codemirror.css',
            $this->codemirror_dir . '/addon/hover/text-hover.css',
            'js/fancybox/jquery.fancybox-1.3.4.css',
            'css/popup_new.css',
            'css/validationEngine.jquery.css',
            'css/editor.css',
            'css/editor.css-lower_editor_z-Indexes.css',
            'css/editor/new_editor.css',
            'css/editor/perso-rules.css',
            'css/font-awesome/css/font-awesome.min.css',
            /* colorpicker */
            'js/jQuery-colorpicker/css/colorpicker.css',
            'js/chromoselector-2.1.5/chromoselector.css',
            $tootltips,
        );

        if (hasCampaignTimer()) {
            $cssArray[] = 'js/datepicker/css/datepicker.css';
            if ($this->config->item('tenant') == 'etracker') {
                $cssArray[] = 'js/datepicker/css/etracker.datepicker.css';
            }
        }
        return $cssArray;
    }

    public function invalid_url() {

        $client_url = $this->session->flashdata('client_url');
        $data = array(
            'url' => $client_url,
            'base_ssl_url' => $this->base_ssl_url
        );
        $this->load->view('new_editor/url_invalid', $data);
    }

    private function getUserInputURL() {

        $client_url = urldecode($this->input->get('url'));

        $client_url = prep_url($client_url);
        return $client_url;
    }

    function textEditIframe() {
        echo '<!DOCTYPE html><html><head><script>document.domain="' . $this->config->item('document_domain') . '";</script></head><body></body></html>';
    }

    /**
     * Load the personalization rules UI with the corresponding css and js
     */
    public function personalizationRules() {
        if (!$this->clientid) {
            lang_redirect('login');
        }

        $this->config->load('personalization');
        $tootltips = ($this->tenant == 'etracker') ? 'js/tooltips/css/jquery.powertip.etracker.css' : 'js/tooltips/css/jquery.powertip.min.css';
        $perso_resources = array(
            'basesslurl' => $this->base_ssl_url,
            'css' => array(
                'css/etracker.css',
                'css/editor/perso-rules.css',
                'css/validationEngine.jquery.css',
                'js/tooltips/css/jquery.powertip.min.css',
                $tootltips,
            ),
        );
        $this->load->view('new_editor/perso_rules_ui', $perso_resources);
    }

    /**
     * Called via AJAX: receives the goal name and the element selector and save both
     * in an array to be displayed in the layer "conversion goals"
     */
    function postNewClickgoal() {
        $lpcid = $this->input->post('lpcid');
        $goalid = $this->input->post('goalid');
        $pageid = $this->input->post('pageid');
        $gName = trim($this->input->post('gName'));
        $gSelector = trim($this->input->post('selector'));
        $goalArray = $this->session->userdata('clickGoals') != NULL ? $this->session->userdata('clickGoals') : array();
        $foundSelector = FALSE;

        foreach ($goalArray as $key => $goal) {
            if ($goal['lpcid'] == $lpcid && $goal['pageid'] == $pageid && $goal['selector'] == $gSelector) {
                $foundSelector = TRUE;
                $goalArray[$key]['name'] = $this->lang->line('Click goal prefix') . $gName;
                break;
            }
        }

        if (!$foundSelector) {
            $goalArray[] = array(
                'lpcid' => $lpcid,
                'goalid' => $goalid,
                'selector' => $gSelector,
                'pageid' => $pageid,
                'name' => $this->lang->line('Click goal prefix') . $gName,
            );
        }
        $this->session->set_userdata('clickGoals', $goalArray);
    }
    
    /**
     * Called via ajax (see BT-clickgoals.js/removeGoalFromElement),
     * If there is a integer LPCID, set an array of Goals To Be Deleted
     * Also, it removes the corresponding entry from the session variable of newly create CLICK goals
     */
    function unsetClickGoal() {
        $lpcid = $this->input->post('lpcid');
        $pageid = $this->input->post('pageid');
        $gSelector = trim($this->input->post('selector'));

        if ($lpcid && ($lpcid * 1) >= 1) {
            $goalsToDelete = $this->session->userdata('goalsToDelete') ? $this->session->userdata('goalsToDelete') : array();
            $goalsToDelete[] = array(
                'lpcid' => $lpcid,
                'pageid' => $pageid,
                'selector' => $gSelector,
            );
            $this->session->set_userdata('goalsToDelete', $goalsToDelete);
        }

        $goalArray = $this->session->userdata('clickGoals') != NULL ? $this->session->userdata('clickGoals') : array();
        foreach ($goalArray as $key => $goal) {
            if ($goal['lpcid'] == $lpcid && $goal['pageid'] == $pageid && $goal['selector'] == $gSelector) {
                unset($goalArray[$key]);
                break;
            }
        }
        $this->session->set_userdata('clickGoals', $goalArray);
    }

    /**
     * Gets a combination of all available goals along with the "click"goals saved in the session array.
     */
    public function getAvailableGoals() {
        $lpcid = $this->input->get('lpcid');
        $apigoals = $this->config->item('api_goals');
        $defGoals = $this->lang->line('Available Goals');

        $availableGoals = array(
            'highlighning' => $this->session->userdata('clickHighlightning') ? $this->session->userdata('clickHighlightning') : 'enabled',
            'nameLabel' => $this->lang->line('Enter goal name short'),
            'primaryLabel' => $this->lang->line('Primary goal label'),
            'secondaryLabel' => $this->lang->line('Secondary goal label'),
            'saved' => array(),
            'available' => array(),
        );

        foreach ($this->lang->line('Available Goals') as $key => $goal) {
            if (in_array($key, $this->config->item('available_goals'))) {
                $cg = $apigoals[$key];
                $availableGoals['available'][] = array(
                    'type' => $cg,
                    'label' => $defGoals[$key],
                    'level' => 'SECONDARY',
                    'levelLabel' => $this->lang->line('Secondary goal label'),
                    'goalid' => FALSE,
                    'name' => $goal,
                    'pageid' => FALSE,
                    'param' => $key,
                    'status' => 'ACTIVE',
                    'description' => $this->lang->line($cg . '_desc'),
                    'fieldDescription' => $this->lang->line($cg . '_field_desc'),
                );
            }
        }

        $editedGoals = array();
        foreach ($this->session->userdata('clickGoals') as $sg) {
            if ($sg['lpcid'] == $lpcid) {
                if ($sg['goalid'] * 1 > 0) {
                    $editedGoals[$sg['goalid']] = array(
                        'name' => $sg['name'],
                        'selector' => $sg['selector'],
                    );
                    continue;
                }

                $rand = hash('crc32', rand(1, 999)) . rand(1, 99);
                $availableGoals['available'][] = array(
                    'type' => 'CLICK',
                    'label' => $this->lang->line('Click goal name'),
                    'level' => 'SECONDARY',
                    'levelLabel' => $this->lang->line('Secondary goal label'),
                    'goalid' => $rand,
                    'name' => $sg['name'],
                    'pageid' => $sg['pageid'],
                    'param' => $sg['selector'],
                    'status' => 'ACTIVE',
                    'saved' => FALSE,
                    'description' => $this->lang->line('CLICK_desc'),
                    'fieldDescription' => $this->lang->line('CLICK_field_desc'),
                );
            }
        }

        if ($lpcid > 0) {
            $savedGoals = $this->sdk->getGoals($lpcid);
            $editedGoalIds = array_keys($editedGoals);

            foreach ($savedGoals as $goal) {
                if ($goal->type != 'CLICK' || ($goal->type == 'CLICK' && !self::savedGoalDeletedInSession($lpcid, $goal))) {
                    $gprefix = '';
                    $gtype = array_search($goal->type, $this->config->item('api_goals'));
                    $glabel = $defGoals[$gtype];
                    $param = isset($goal->param) ? $goal->param : $goal->type;

                    if ($goal->type == 'CLICK') {
                        $parameters = json_decode($goal->param);
                        $param = $parameters->selector;
                        $gprefix = $this->lang->line('Click goal prefix');
                        $glabel = $this->lang->line('Click goal name');
                    }
                    if ($goal->deleteddate != NULL) {
                        $d = strtotime($goal->deleteddate);
                        $goal->deleteddate = date('Y.m.d H:i', $d);
                    }

                    $item = array(
                        'type' => $goal->type,
                        'label' => $glabel,
                        'level' => $goal->level,
                        'levelLabel' => $goal->lavel == 'PRIMARY' ? $this->lang->line('Primary goal label') : $this->lang->line('Secondary goal label'),
                        'goalid' => $goal->id,
                        'name' => $gprefix . $goal->name,
                        'pageid' => $goal->page,
                        'param' => $param,
                        'status' => $goal->status,
                        'deleteddate' => $goal->deleteddate,
                        'description' => $this->lang->line($goal->type . '_desc'),
                        'fieldDescription' => $this->lang->line($goal->type . '_field_desc'),
                    );

                    if (in_array($goal->id, $editedGoalIds)) {
                        $item['name'] = $editedGoals[$goal->id]['name'];
                        $item['param'] = $editedGoals[$goal->id]['selector'];
                    }

                    $availableGoals['saved'][] = $item;
                    if ($goal->type == 'CLICK') {
                        $availableGoals['available'][] = $item;
                    }

                    self::setSavedAvailableGoals($goal, $availableGoals);
                }
            }
        }

        echo json_encode($availableGoals);
    }

    /**
     * If  a session goal is already saved, the "saved" attribute is set to TRUE
     * @param Array $goal
     * @param Array $availableGoals
     */
    private function setSavedAvailableGoals($goal, &$availableGoals) {
        $gprefix = $this->lang->line('Click goal prefix');
        if ($goal->type == 'CLICK') {
            foreach ($availableGoals['available'] as $key => $g) {
                if ($g['type'] == 'CLICK' && $g['name'] == $gprefix . $goal->name && $g['pageid'] == $goal->page) {
                    $availableGoals['available'][$key]['saved'] = TRUE;
                }
            }
        }
    }

    /**
     * Verifies if the current evaluated goal (SavedGoal) is set to be deleted in the current session
     * @param Int $lpcid
     * @param Object $savedGoal
     * @return boolean
     */
    private function savedGoalDeletedInSession($lpcid, $savedGoal) {
        $arg = json_decode($savedGoal->param);
        $goalsToDelete = $this->session->userdata('goalsToDelete');

        if (!$goalsToDelete) {
            return FALSE;
        }

        foreach ($goalsToDelete as $del) {
            if ($del['lpcid'] == $lpcid && $del['pageid'] == $savedGoal->page && $del['selector'] == $arg->selector) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Called via AJAX (see: BT-clickgoals.js/setOrUnsetHighlighning)
     */
    public function enableOrDisableHighlightning() {
        $this->session->set_userdata('clickHighlightning', $this->input->get('status'));
        echo 'OK';
    }

    /**
     * When a SSL redirect is performed in the editor, we have to save the current changes made by the user
     * to a session variable, so it is available when the editor is reloaded (see: BT-editor-new.js/postSavedData)
     */
    public function saveEditorDataToSession() {
        $newUrlData = $this->input->post('newUrlData');
        $this->session->set_userdata('newUrlData', $newUrlData);
    }

}
