<?php

/**
 * Controller for all user related things, like log in, register, change user data etc.
 * Enter description here ...
 * @author eschneid
 *
 */
Class Users extends CI_Controller {

    private $sdk;
    public $rowinsert = '';

    function __construct() {
        parent::__construct();
        doAutoload(); // load resources from autoload_helper
        $this->load->model('user');
        $this->lang->load('profile');
        $this->load->library('email');
        $this->load->library('encrypt');
        $this->load->helper('sv');
        $this->load->library('log');
        $this->load->helper('fb');

        define('API_URL', $this->config->item('base_ssl_url') . 'api/v1/', TRUE);
        require_once APPPATH . 'controllers/apiv1sdk.php';
        $this->sdk = new apiv1sdk();
        $this->sdk->__set('clientid', $this->session->userdata('sessionUserId'));
        $this->sdk->__set('apikey', $this->config->item('bt_apikey'));
        $this->sdk->__set('apisecret', $this->config->item('bt_apisecret'));
    }

    /*
     * 
     *  controller for signup form
     */

    function su() {
        //if($this->config->item('tenant') == 'etracker') redirect("/pages/notfound/"); // no access for etracker
        // if user is already logged in, redirect to dashboard
        $clientid = $this->session->userdata('sessionUserId');
        if ($clientid) {
            redirect($this->config->item('base_ssl_url') . "lpc/cs/");
        }

        $userplan = str_replace("~", "/", $this->uri->segment(3));
        // css array
        $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_ssl_url() . 'css/template.css';
        $pageid['css'] = $arrCss;
        // js array	
        $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
        $pageid['js'] = $arrJavascript;
        // additional css
        $pageid['others'] = "<style type='text/css'>
                        * 
                        label.error { color: red;font-size: 11px;  }
	</style>
	<script>	
                        $(document).ready(function() {
                            // SUCCESS AJAX CALL, replace 'success: false,' by:     success : function() { callSuccessFunction() }, 
                            $('#signupform').validationEngine()
                        });
                        var path ='" . base_ssl_url() . "';
	</script>";
        // page title
        $pageid['title'] = $this->lang->line('title_signup');
        $pageid['description'] = $this->lang->line('signup_metadescription');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activesignup'] = "class=active";
        $pageid['userplan'] = $userplan;
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('user/signup', $pageid);
        $this->load->view('includes/public_footer');
    }

    /*
     * 
     *  controller for signup form
     */

    function sutest() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker

            
// if user is already logged in, redirect to dashboard
        $clientid = $this->session->userdata('sessionUserId');
        if ($clientid) {
            redirect($this->config->item('base_ssl_url') . "lpc/cs/");
        }
        $plan = $planarray['PLAN_STARTER'];
        // css array
        $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_ssl_url() . 'css/template.css';
        $pageid['css'] = $arrCss;

        // js array
        //$arrJavascript[] = base_ssl_url() . 'js/jQuery.XDomainRequest.js';
        $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
        $pageid['js'] = $arrJavascript;
        // additional css
        $pageid['others'] = "<style type='text/css'>
                    * label.error { color: red;font-size: 11px;  }
                    html { background-color:white; }
	#signup_container { height:auto; width:596px; padding:0; }
	.signup-left {margin:0px;}
	#inner_bg { background:none; background-color:#FFF; }
	.signup-left-mdle { padding:5px 0; }
	.create-my-account { margin-bottom:5px; }
	.signup-field { padding: 0 0 20px 50px; margin:0 0 15px }
	.signup-field label { padding: 10px 0 0; }
	.signup-info { padding: 0 0 15px 56px; }
                    #terms p { width:640px; }
            </style>
            <script>	
                //document.domain = 'blacktri-dev.de';
                $(document).ready(function() {
                    // SUCCESS AJAX CALL, replace 'success: false,' by:     success : function() { callSuccessFunction() }, 
                    $('#signupform').validationEngine()
                });
                var path ='" . base_ssl_url() . "';
            </script>";
        // page title
        $pageid['title'] = $this->lang->line('title_signup');
        $pageid['activesignup'] = "class=active";
        $pageid['force_ssl'] = true;
        $userplan['userplan'] = $plan;

        $pageid['hidenavi'] = true;
        $userplan['testsignup'] = true;

        $this->load->view('includes/public_header', $pageid);
        $this->load->view('user/signup_test', $userplan);
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for check availabilty of username
     */

    function ca() {
        $validateValue = $this->input->get('fieldValue');
        $validateId = $this->input->get('fieldId');
        /* RETURN VALUE */
        $arrayToJs[0] = $validateId;
        $arrayToJs[1] = $this->user->checkavailabilty($validateValue);
        echo '["' . $arrayToJs[0] . '", ' . $arrayToJs[1] . ']';  // RETURN ARRAY WITH ERROR
    }

    /*
     *  controller for check availabilty of email from registration and profile form
     *  an email is unavailable if:
     *  	the email has been validated
     *  	the email has not been validated, but the user has logged in using the email at least once 
     */

    function ce() {
        $validateValue = $this->input->get('fieldValue');
        $validateId = $this->input->get('fieldId');
        /* RETURN VALUE */
        $arrayToJs[0] = $validateId;
        // take into account that when user is logged in and this function is called from the profile form,
        // the user's own email shall not be marked as "in use" by someone
        $email = "invalidemail";
        $clientid = $this->session->userdata('sessionUserId');
        if ($clientid) {
            $profile = $this->user->clientdatabyid($clientid);
            $email = $profile['email'];
        }
        $arrayToJs[1] = $this->user->checkemailavailable($validateValue, $email);

        //set origin for inter protocol calls for test signup page https -> http
        $this->output->set_header("Access-Control-Allow-Origin: *");

        echo '["' . $arrayToJs[0] . '", ' . $arrayToJs[1] . ']';  // RETURN ARRAY WITH ERROR
    }

    /*
     *
     *  controller for user signup action
     */

    function signup() {
        $this->lang->load('signup');
        $data = array();
        // encrypt password
        $data['password'] = md5($this->input->post('passwordvalue'));
        $clearpassword = $this->input->post('passwordvalue');
        $data['firstname'] = $this->input->post('firstname');
        $data['lastname'] = $this->input->post('lastname');
        $data['email'] = $this->input->post('email');
        $tenant = $this->config->item('tenant');
        $data['tenant'] = $tenant;
        //$userplan = $this->input->post('userplan');
        $test_user = $this->input->get('istest') == 'yes';

        // use BASIC PLAN
        $planarray = $this->config->item('PLAN');
        $plan_key = 'PLAN_BASIC'; // default for test registration
        $data['userplan'] = $planarray[$plan_key];
        $plan = $data['userplan'];
        $planinfo = $this->config->item('PLAN_INFO');
        $data['quota'] = $planinfo[$plan_key]['quota'];
        
        $data['email_validated'] = CLIENT_EMAIL_NOT_VALIDATED;
        $data['referrer'] = $this->session->userdata("c_referrer");
        //die($data['referrer']);
        $data['status'] = CLIENT_STATUS_ACTIVE;
        // encript banking details
        $currentDate = date('Y-m-d H:i:s');
        $data['createddate'] = $currentDate;
        $data['quota_reset_dayinmonth'] = date('j');
        // serverside validations
        $errmsg = signupvalidation($this->input->post('firstname'), $this->input->post('lastname'), $this->input->post('email'), $this->input->post('passwordvalue'), $this->input->post('retypepassword'));

        // check serverside validations status for mandatory fields
        if ($errmsg == '') {
            // check email availability 
            $errmsg = $this->user->checkemailavailable($this->input->post('email'));
            if ($errmsg == 'true') {
                // insert entry to table
                $insertid = $this->user->signup($data);
                $email_raw = $this->input->post('email');
                $this->sendvalidationmail($insertid, $email_raw, $data['firstname'], "confirm");
                // set session if valid entry
                $data = $this->user->userlogin($data['email'], $clearpassword);
                // send email to administrator
                $this->sendregistrationnotification($insertid, $tenant);
                // forward to welcome page after successful registration
                // during closed beta phase, forward user to confirmation page
                if ($test_user === true) {
                    redirect(base_url() . '/pages/stud');
                } else {
                    redirect($this->config->item('base_ssl_url') . "lpc/cs/");
                }
            } else {
                // email exists
                $this->sue($this->lang->line('title_mailregisterederror'), $plan);
            }
        } else { //  mandatory fields serverside validation fails
            $this->sue($errmsg, $plan);
        }
    }

    /*
     *  controller for signin page
     */

    function si() {
        // the login page can be called with a parameter that forces to display
        // a password error on load
        $showErrorOnLoad = $this->input->get('error') == 'true';
        // css array
        $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_ssl_url() . 'css/template.css';
        $arrCss[] = base_ssl_url() . 'css/popup_new.css';
        $pageid['css'] = $arrCss;
        // js array
        $arrJavascript[] = base_ssl_url() . 'jsi18n/validatelogin';
        $arrJavascript[] = base_ssl_url() . 'js/validatelogin.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery-latest.js';
        $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'js/popup.js';
        $pageid['js'] = $arrJavascript;
        // additional css
        $pageid['others'] = "<style type='text/css'>
                * 
                label.error { color: red;font-size: 11px;  }
            </style>";
        // page title
        $pageid['title'] = $this->lang->line('title_login');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activesi'] = "class=active";
        if ($showErrorOnLoad)
            $pageid['showErrorOnLoad'] = "true";
        else
            $pageid['showErrorOnLoad'] = "false";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/login');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for signing in action
     */

    function signin() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        $externallogin = $this->input->post('externallogin');
        $currentDate = date('Y-m-d H:i:s');
        $data = $this->user->userlogin($email, $password);       

        // track logging
        if ($data == 0) {
            // success
            $clientid = $this->session->userdata('sessionUserId');
            $loginstatus = $this->session->userdata('sessionUserId');
            dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LOGIN, "success", $clientid);
        } else {
            // fail
            dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LOGIN, "fail");
        }

        // if externallogin is set, then redirect to /lpc/cs on success and to login page on error
        if ($externallogin == "true") {
            if ($data == 0)
                redirect($this->config->item('base_ssl_url') . "lpc/cs/");
            else {
                $baseurl = $this->config->item('base_ssl_url');
                $lg = $this->config->item('language');
                $purl = $this->config->item('page_url');
                // construct URL to login page and force display of error message
                $loginurl = $baseurl . $purl[$lg]['login'] . "?error=true";
                redirect($loginurl);
            }
        } else {
            // if no login from external, then return response to Login Ajax call
            echo $data;
        }
    }

    /*
     *  controller for fbsigning in action
     */

    function fbsignin() {
        $fbCookie = ProcessFBCookie();
        $email = $this->input->post('email');
        $uid = $this->input->post('id');
        //if (fbCookie.Count > 0 && fbCookie["validated"] == "true" && !string.IsNullOrEmpty(Req["email"]) && !string.IsNullOrEmpty(Req["id"]))
        if ($fbCookie["validated"] && !empty($email) && !empty($uid)) {
            //check if emai exists first
            //if email does not exists create new user, errmsg = true
            if ($this->user->checkemailavailable($email)) {
                $data = array();
                $data['username'] = $email; //assign email to user name for FB users
                $data['password'] = ''; //ignore password for FB users
                $data['lastname'] = $this->input->post('last_name');
                $data['firstname'] = $this->input->post('first_name');
                $data['email'] = $email;
                $data['fb_id'] = $uid;
                $data['userplan'] = '3'; //default user plan
                $data['email_validated'] = CLIENT_EMAIL_VALIDATED; //email should be validated by FB
                $data['status'] = '1'; // during closed beta, set status to 5 instead to 1 //approved by FB
                $currentDate = date('Y-m-d H:i:s');
                $data['createddate'] = $currentDate;

                $insertid = $this->user->signup($data);
                if ($insertid > 0) {
                    //autologin user
                    $this->user->userloginfb($uid, $email);
                    dblog_message(LOG_LEVEL_INFO, LOG_TYPE_SIGNUP, "facebook signup", $insertid);
                    echo $this->user->userloginfb($uid, $email);
                } else {
                    dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LOGIN, "Could not create user! Data: " . print_r($data));
                }
            } else {
                //email exists assign FB account if not exists allready
                $this->user->fbattachaccount($uid, $email);
                //autologin user after
                echo $this->user->userloginfb($uid, $email);
            }
        } else {
            dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LOGIN, $nvc["fb_fraud"]);
        }
        /* FB Fields
          email	shaddow11ro@gmail.com
          first_name	Gigi
          gender	male
          hometown[id]	114304211920174
          hometown[name]	Bucharest, Romania
          id	100002438484956
          last_name	Marga
          link	http://www.facebook.com/profile.php?id=100002438484956
          locale	en_US
          location[id]	114304211920174
          location[name]	Bucharest, Romania
          name	Gigi Marga
          timezone	3
          updated_time	2011-06-07T13:09:17+0000
         */
    }

    /**
     * controller to display home page after registration during closed beta
     */
    private function breg() {
        $pageid['title'] = $this->lang->line('title_betaregconfirm');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/betaregconfirm');
        $this->load->view('includes/public_footer');
    }

// function index ends here

    /*
     * 
     *  controller for temporary welcome(signup welcome)
     */

    function suw($emailStatus) {
        $data1['success'] = 0;
        $data1['email_validated'] = $clientdata['email_validated'];
        $pageid['title'] = $this->lang->line('title_homepage');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $data1['firstname'] = $this->session->userdata('sessionFirstName');
        $this->load->view('includes/protected_header', $pageid);
        $this->load->view('protected/welcome', $data1);
        $this->load->view('protected/editor_wizard.php', $data);
        $this->load->view('includes/public_footer');

        /* 		
          $data1['success'] = $emailStatus;
          $pageid['css'] = $arrCss;
          $pageid['js'] = $arrJavascript;
          $pageid['title'] = $this->lang->line('title_homepage');
          $this->load->view('includes/protected_header',$pageid);
          $this->load->view('protected/welcome',$data1);
          $this->load->view('includes/public_footer');
         */
    }

    /*
     *  controller for signup error
     * 
     */

    function sue($errmsg, $plan) {
        // css array
        $arrCss[] = base_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_url() . 'css/template.css';
        $pageid['css'] = $arrCss;
        // js array	
        $arrJavascript[] = base_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_url() . 'js/jquery.validationEngine.js';
        $pageid['js'] = $arrJavascript;
        // additional css
        $pageid['others'] = "<style type='text/css'>
                * 
                label.error { color: red;font-size: 11px;  }
            </style>
            <script>	
                $(document).ready(function() {
                    // SUCCESS AJAX CALL, replace 'success: false,' by:     success : function() { callSuccessFunction() }, 
                    $('#signupform').validationEngine()
                });
                var path ='" . base_url() . "';
            </script>";
        // page title
        $pageid['title'] = $this->lang->line('title_signup');
        $data['errMsg'] = $errmsg;
        $data['userplan'] = $plan;
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activesignup'] = "class=active";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('user/signup', $data);
        $this->load->view('includes/public_footer');
    }

    /*
     *  send validation mail
     *  clientid: ID of client
     *  email: email address to be sent to
     *  name: Name to be used in salutation in email
     *  type: "confirm": confirmation mail after registration,
     *  	"validate": validation mail after changing email,
     *  	"resendvalidate": validationmail after resending it from the profile page
     *  	"notifychange": information to old email that a new email has been chosen
     */

    private function sendvalidationmail($clientid, $email, $name, $type = 'confirm') {
        $data["clientid"] = $clientid;
        $data["name"] = $name;

        // select view from type
        if ($type == 'confirm') {
            $view = 'confirmationmail';
            $subject = $this->lang->line('emails_confirmationmailsubject');
            $this->email->from($this->config->item('MAIL_ECKHARD_SENDER'), $this->config->item('MAIL_ECKHARD_NAME'));
        } elseif ($type == 'validate') {
            $view = 'validationmail';
            $subject = $this->lang->line('emails_validationmailsubject');
            $this->email->from($this->config->item('MAIL_NOREPLY_SENDER'), $this->config->item('MAIL_NOREPLY_NAME'));
        } else {
            $view = 'notifychangemail';
            $subject = $this->lang->line('emails_changenotifysubject');
            $this->email->from($this->config->item('MAIL_NOREPLY_SENDER'), $this->config->item('MAIL_NOREPLY_NAME'));
        }
        if ($this->config->item('tenant') == 'dvlight') {
            $this->email->from($this->config->item('MAIL_DIVOLUTION_SENDER'), $this->config->item('MAIL_DIVOLUTION_NAME'));
        }
        //$view = 'validationmail';
        $message = $this->load->view($view, $data, true);

        // mail function goes here
        $this->email->to($email);
        $this->email->bcc($this->config->item('ADMIN_EMAIL'));
        $this->email->subject($subject);
        $this->email->set_mailtype('html');
        $this->email->message($message);
        $mail = $this->email->send();
        //echo($message);return;
        log_message('debug', 'validationmail: send email to ' . $email);
        // check mail status
        $mail = true; // for some reason the email class always returns false, even if the mail could be sent
        if (!$mail) {
            dblog_message(LOG_LEVEL_ERROR, LOG_TYPE_VALIDATIONMAIL, "mail could not be sent, clientid: $clientid");
            return 'fail';
        } else {
            return 'success';
        }
    }

    function test() {
        /*
          $pageid['title'] = $this->lang->line('title_signup');
          $pageid['activesignup'] = "class=active";
          $userplan['userplan'] = $plan;

          $pageid['hidenavi'] = true;
          $userplan['testsignup'] = true;

          $this->load->view('includes/public_header',$pageid);
          $this->load->view('user/signup_test',$userplan);
          $this->load->view('includes/public_footer');
         */

        $this->sendregistrationnotification(2, 'blacktri');
    }

    /*
     * send a notificationmail to the administrator
     * during betaphase: contains approval-link
     * parameters: 
     * $clientid: clientid in table client
     */

    function testreg($id) {
        $this->sendregistrationnotification($id);
    }

    private function sendregistrationnotification($clientid, $tenant) {
        $profileData = $this->user->clientdatabyid($clientid);
        $message = "New Registration:";
        $message .= "\nName:" . $profileData['firstname'] . " " . $profileData['lastname'];
        $message .= "\nEmail:" . $profileData['email'];
        $myplan = getPlanDetails($profileData['userplan']);
        $message .= "\nPlan:" . $myplan['plan_name'];
        if ($tenant == 'dvlight') {
            $this->email->from($this->config->item('MAIL_DIVOLUTION_SENDER'), $this->config->item('MAIL_DIVOLUTION_NAME'));
            $this->email->to('m.beck@divolution.com,abtester@divolution.com');
            $this->email->subject("Neue A/B Tester Registrierung");
        } else {
            $this->email->to($this->config->item('ADMIN_EMAIL'));
            $this->email->from($this->config->item('MAIL_NOREPLY_SENDER'), 'BlackTri Optimizer');
            $this->email->subject("New BlackTri Registration");
        }
        $this->email->bcc($this->config->item('ADMIN_EMAIL'));
        $this->email->set_mailtype('text');
        $this->email->message($message);
        $mail = $this->email->send();
        //echo($message);
    }

    /*
     * 
     *  controller  for forgot password
     */

    function updatepassword() {
        $username = $this->input->post('username');
        $returnValue = '';
        // check for username availabilty
        $count = $this->user->passwordremainder($username);
        if ($count == 1) {
            // check for client status
            $details = $this->user->clientstatus($username);
            if ($details['email_validated'] == 1) {
                // generate 10 characters random password
                $length = 10;
                $vowels = 'aeuy';
                $consonants = '23456789bdghjmnpqrstvzAEUY';
                $newpassword = '';
                $alt = time() % 2;
                for ($i = 0; $i < $length; $i++) {
                    if ($alt == 1) {
                        $newpassword .= $consonants[(rand() % strlen($consonants))];
                        $alt = 0;
                    } else {
                        $newpassword .= $vowels[(rand() % strlen($vowels))];
                        $alt = 1;
                    }
                }
                $updatepwd = $this->user->resetpassword($username, md5($newpassword));
                // mail function goes here
                $this->email->to($details['email']);
                $this->email->bcc($this->config->item('ADMIN_EMAIL'));
                $this->email->from($this->config->item('MAIL_NOREPLY_SENDER'), 'BlackTri Optimizer');
                if ($this->config->item('tenant') == 'dvlight') {
                    $this->email->from($this->config->item('MAIL_DIVOLUTION_SENDER'), $this->config->item('MAIL_DIVOLUTION_NAME'));
                }
                $this->email->subject($this->lang->line('emails_newpw_subject'));

                $data["newpassword"] = $newpassword;
                $data["firstname"] = $details['firstname'];

                $message = $this->load->view('passwordmail', $data, true);

                $this->email->set_mailtype('html');
                $this->email->message($message);
                $mail = $this->email->send();
                //echo $this->email->print_debugger();
                // check mail status
                if ($mail) {
                    // track logging
                    $msg = $username;
                    dblog_message(LOG_LEVEL_INFO, LOG_TYPE_PWDREMAINDER, $msg);
                    $returnValue = 2;
                } else {
                    $returnValue = 1;
                }
            } else {
                // email not validated, display link to resend validation mail
                $clientid_hash = $details['clientid_hash'];
                $lang_line = $this->lang->line('link_sendactivation');
                $returnValue = "<a class='send-link' onclick='emailsuccess(\"$clientid_hash\", 1)'>$lang_line</a>";
            }
        } else {
            // email or username not found
            $returnValue = '0'; //'Username or email not found';
        }
        echo $returnValue;
    }

    /*
     *  controller for first time email confirmation
     */

    function ecc() {
        $this->ec('confirm');
    }

    /*
     *  controller for email confirmation
     *  it is called directly as well as via controller ecc
     *  controller ec validates the email and displays a view saying that the email is verified
     *  controll ecc calls this controller ec('confirm') and lets it display a view
     *  that tells the user to log in (used for first time email confirmation)
     */

    function ec($type = 'validate') {
        $clientid_hash = str_replace("~", "/", $this->uri->segment(3));
        if ($clientid_hash == '') {
            $msg = "email conformation fail";
            dblog_message(LOG_LEVEL_INFO, LOG_TYPE_CCONFIRMEMAIL, $msg, '');
            // page title
            $pageid['title'] = $this->lang->line('title_emailconfirmationerrorpage');
            $pageid['headimg'] = base_url() . "images/logo_sml.png";
            $this->load->view('includes/public_header', $pageid);
            $this->load->view('public/emailerror');
            $this->load->view('includes/public_footer');
        } else {
            $clientid = $this->user->emailconfirm($clientid_hash);
            // status updation status
            if ($clientid != FALSE) {
                $msg = "email conformation successful";
                dblog_message(LOG_LEVEL_INFO, LOG_TYPE_CCONFIRMEMAIL, $msg, $clientid);
                // page title
                $pageid['title'] = $this->lang->line('title_emailconfirmationpage');
                $pageid['headimg'] = base_url() . "images/logo_sml.png";
                $pageid['type'] = $type;
                $this->load->view('includes/public_header', $pageid);
                $this->load->view('public/emailconfirm');
                $this->load->view('includes/public_footer');
            } else {
                $msg = "email conformation fail";
                dblog_message(LOG_LEVEL_INFO, LOG_TYPE_CCONFIRMEMAIL, $msg, $clientid);
                // page title
                $pageid['title'] = $this->lang->line('title_emailconfirmationerrorpage');
                $pageid['headimg'] = base_url() . "images/logo_sml.png";
                $this->load->view('includes/public_header', $pageid);
                $this->load->view('public/emailerror');
                $this->load->view('includes/public_footer');
            }
        }
    }

    /*
     * 
     *  controller for emailsuccess function
     */

    function emailsuccess() {
        $clientid_hash = $this->input->post('clientid_hash');
        log_message('debug', "clintid_hash:" . $clientid_hash);
        $clientdata = $this->user->validationmaildata($clientid_hash);
        dblog_debug($clientid_hash . '#' . $clientdata["email"] . '#' . $clientdata["firstname"] . '#' . $clientdata["clientid"] . '#');
        $data = $this->sendvalidationmail($clientdata["clientid"], $clientdata["email"], $clientdata["firstname"], "validate");
        echo $data;
    }

    /*
     *
     *  controller for validating username in use
     */

    function usernamecheckvalue() {
        $username = $this->input->post('username');
        $data = $this->user->usernamecheck($username);
        echo json_encode($data);
    }

    /*
     * 
     *  controller for retrieve user invoice data
     *  CURRENTLY NOT USED
     */

    function gui() {
        $clientid = $this->session->userdata('sessionUserId');
        $userData['invoiceDetails'] = $this->user->getuserinvoice($clientid);
        // js array	
        $arrJavascript[] = base_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_url() . 'js/jquery.validationEngine.js';
        $pageid['js'] = $arrJavascript;
        // css array
        $arrCss[] = base_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_url() . 'css/template.css';
        $pageid['css'] = $arrCss;
        // additional css
        $pageid['others'] = '<style type="text/css">
                * 
                label.error { color: red;font-size: 11px;  }
            </style>';
        // page title
        $pageid['title'] = $this->lang->line('title_invoice') . $this->lang->line('title_divsimbol') . $this->lang->line('title_sitename');
        $this->load->view('includes/protected_header', $pageid);
        $this->load->view('user/invoice', $userData);
        $this->load->view('includes/protected_footer');
    }

    /*
     * 
     *  controller for insert user invoice data
     *  CURRENTLY NOT USED
     */

    function setuserinvoice() {
        //session checking
        $clientid = $this->session->userdata('sessionUserId');
        if ($clientid) {
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $company = $this->input->post('company');
            $address = $this->input->post('address');
            $zipcode = $this->input->post('zipcode');
            $city = $this->input->post('city');
            $vatno = $this->encrypt->encode($this->input->post('vatno'));
            $accountno = $this->encrypt->encode($this->input->post('accountno'));
            $bankcode = $this->encrypt->encode($this->input->post('bankcode'));
            $bank = $this->encrypt->encode($this->input->post('bank'));
            $accountholder = $this->input->post('accountholder');
            // serverside validations
            $errmsg = invoicevalidation($firstname, $lastname, $company, $address, $zipcode, $city, $this->input->post('vatno'), $this->input->post('accountno'), $this->input->post('bankcode'), $this->input->post('bank'), $accountholder);
            if ($errmsg == '') {
                $this->user->setuserinvoice($clientid, $firstname, $lastname, $company, $address, $zipcode, $city, $vatno, $accountno, $bankcode, $bank, $accountholder);
                redirect($this->config->item('base_ssl_url') . "lpc/cs/");
            } else {
                $userData['invoiceDetails'] = $this->user->getuserinvoice($clientid);
                $userData['errMsg'] = $errmsg;
                // js array	
                $arrJavascript[] = base_url() . 'jsi18n/jqueryValidationEngine.js';
                $arrJavascript[] = base_url() . 'js/jquery.validationEngine.js';
                $pageid['js'] = $arrJavascript;
                // css array
                $arrCss[] = base_url() . 'css/validationEngine.jquery.css';
                $arrCss[] = base_url() . 'css/template.css';
                $pageid['css'] = $arrCss;
                // additional css
                $pageid['others'] = '<style type="text/css">
                        * 
                        label.error { color: red;font-size: 11px;  }
                    </style>';
                // page title
                $pageid['title'] = $this->lang->line('title_invoice') . $this->lang->line('title_divsimbol') . $this->lang->line('title_sitename');
                $this->load->view('includes/protected_header', $pageid);
                $this->load->view('user/invoice', $userData);
                $this->load->view('includes/protected_footer');
            }
        } else {
            redirect('lpc');
        }
    }

    /*
     *  controller for edit user profile
     */

    function gup() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
        $clientid = getClientIdForActionFromUrl(3);
        if ($clientid) {
            $account = $this->sdk->getAccount($clientid);
            $profileData['clientid'] = $account->id;
            $profileData['createddate'] = $account->createddate;
            $profileData['status'] = $account->status;
            $profileData['userplan'] = $account->plan;
            $profileData['quota'] = $account->quota;
            $profileData['quotaresetdayinmonth'] = $account->quotaresetdayinmonth;
            $profileData['firstname'] = $account->firstname;
            $profileData['lastname'] = $account->lastname;
            $profileData['email'] = $account->email;
            $profileData['emailvalidated'] = $account->emailvalidated;
            $profileData['publicid'] = $account->publicid;
            if(getApiLevel() == 'disabled') {
                $profileData['apikey'] = $this->lang->line('not available');
                $profileData['apisecret'] = $this->lang->line('not available');                
            }
            else {
                $profileData['apikey'] = $account->apikey;
                $profileData['apisecret'] = $account->apisecret;                                
            }
            $profileData['trackingcode'] = $account->trackingcode;

            $usageData = $this->user->getUsedQuotaData($clientid);
            $profileData['usedquota'] = $usageData[0]['monthly_usage_abtest'] + $usageData[0]['monthly_usage_teasertest'];

            // js array	
            $arrJavascript[] = base_ssl_url() . 'jsi18n/validatelogin';
            $arrJavascript[] = base_ssl_url() . 'js/validatelogin.js';
            $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
            $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
            $profileData['js'] = $arrJavascript;
            // css array
            $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
            $arrCss[] = base_ssl_url() . 'css/template.css';
            $arrCss[] = base_ssl_url() . 'css/font-awesome/css/font-awesome.css';
            $profileData['css'] = $arrCss;
            // additional css
            $profileData['others'] = '<style type="text/css">
                    * 
                    label.error { color: red;font-size: 11px;  }
                </style>';
            // page title
            $profileData['title'] = $this->lang->line('title_profile');
            $profileData['headimg'] = base_url() . "images/logo_sml.png";
            $this->load->view('includes/protected_header', $profileData);
            $this->load->view('user/profile', $profileData);
            $this->load->view('includes/public_footer');
        } else {
            redirect('lpc');
        }
    }


    /*
     *  controller for account_setting profile
     */

    function account_setting() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
        $clientid = getClientIdForActionFromUrl(3);
        if ($clientid) {
            $account = $this->sdk->getAccount($clientid);
            $profileData['clientid'] = $account->id;
            $profileData['company_name'] = "Test Company"; // should be changed
            $profileData['publicid'] = $account->publicid;
            if(getApiLevel() == 'disabled') {
                $profileData['apikey'] = $this->lang->line('not available');
                $profileData['apisecret'] = $this->lang->line('not available');                
            }
            else {
                $profileData['apikey'] = $account->apikey;
                $profileData['apisecret'] = $account->apisecret;                                
            }
            $profileData['trackingcode'] = $account->trackingcode;

            $usageData = $this->user->getUsedQuotaData($clientid);
            $profileData['usedquota'] = $usageData[0]['monthly_usage_abtest'] + $usageData[0]['monthly_usage_teasertest'];

            // js array 
            $arrJavascript[] = base_ssl_url() . 'jsi18n/validatelogin';
            $arrJavascript[] = base_ssl_url() . 'js/validatelogin.js';
            $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
            $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
            $profileData['js'] = $arrJavascript;
            // css array
            $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
            $arrCss[] = base_ssl_url() . 'css/template.css';
            $arrCss[] = base_ssl_url() . 'css/font-awesome/css/font-awesome.css';
            $profileData['css'] = $arrCss;
            // additional css
            $profileData['others'] = '<style type="text/css">
                    * 
                    label.error { color: red;font-size: 11px;  }
                </style>';
            // page title
            $profileData['title'] = $this->lang->line('title_profile');
            $profileData['headimg'] = base_url() . "images/logo_sml.png";
            $this->load->view('includes/protected_header', $profileData);
            $this->load->view('user/account_setting', $profileData);
            $this->load->view('includes/public_footer');
        } else {
            redirect('lpc');
        }
    }
   
    /*
     *  controller for user management profile
     */

    function user_mng() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
        $clientid = getClientIdForActionFromUrl(3);
        if ($clientid) {
            $account = $this->sdk->getAccount($clientid);
            $profileData['clientid'] = $account->id;
            if(getApiLevel() == 'disabled') {
                $profileData['apikey'] = $this->lang->line('not available');
                $profileData['apisecret'] = $this->lang->line('not available');                
            }
            else {
                $profileData['apikey'] = $account->apikey;
                $profileData['apisecret'] = $account->apisecret;                                
            }
            $profileData['trackingcode'] = $account->trackingcode;

            $usageData = $this->user->getUsedQuotaData($clientid);
            $profileData['usedquota'] = $usageData[0]['monthly_usage_abtest'] + $usageData[0]['monthly_usage_teasertest'];

            // js array 
            $arrJavascript[] = base_ssl_url() . 'jsi18n/validatelogin';
            $arrJavascript[] = base_ssl_url() . 'js/validatelogin.js';
            $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
            $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
            $profileData['js'] = $arrJavascript;
            // css array
            $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
            $arrCss[] = base_ssl_url() . 'css/template.css';
            $arrCss[] = base_ssl_url() . 'css/font-awesome/css/font-awesome.css';
            $profileData['css'] = $arrCss;
            // additional css
            $profileData['others'] = '<style type="text/css">
                    * 
                    label.error { color: red;font-size: 11px;  }
                </style>';
            // page title
            $profileData['title'] = $this->lang->line('title_profile');
            $profileData['headimg'] = base_url() . "images/logo_sml.png";
            $this->load->view('includes/protected_header', $profileData);
            $this->load->view('user/profile', $profileData);
            $this->load->view('includes/public_footer');
        } else {
            redirect('lpc');
        }
    }

    /*
     * Show data for the current plan and invoices
     */

    function account() {
        $clientid = getClientIdForActionFromUrl(3);
        if ($clientid) {
            $data['profileDetails'] = $this->user->clientdatabyid($clientid);
            // css array
            $arrCss[] = base_url() . 'css/validationEngine.jquery.css';
            $arrCss[] = base_url() . 'css/template.css';
            $pageid['css'] = $arrCss;
            // js array
            $arrJavascript[] = base_url() . 'js/validateplaymode.js';
            $arrJavascript[] = base_url() . 'jsi18n/jqueryValidationEngine.js';
            $arrJavascript[] = base_url() . 'js/jquery.validationEngine.js';
            $arrJavascript[] = base_url() . 'js/jquery.qtip-1.0.0-rc3.js';
            $data['js'] = $arrJavascript;
            // additional css
            $pageid['others'] = '
                    <style type="text/css">
                        * 
                        label.error { color: red;font-size: 11px;  }
                    </style>';
            // page title
            $data['title'] = $this->lang->line('title_collectionoverview');
            $data['headimg'] = base_url() . "images/logo_sml.png";

            $this->load->view('includes/protected_header', $data);
            $this->load->view('user/account', $data);
            $this->load->view('includes/public_footer');
        }
    }

    /*
     * Show overview page of sub sccounts for given master account
     */

    function subaccounts() {
        // check authentication
        $clientid = $this->session->userdata('sessionUserId');
        $clientdata = $this->user->clientdatabyid($clientid);
        $client_role = $clientdata['role'];
        if ($client_role != CLIENT_ROLE_MASTER) {
            redirect($this->config->item('base_ssl_url') . "lpc/cs/");
            return;
        }
        // create array with data of all subsccounts
        $accountlist = $this->user->getSubAccounts($clientid);
        $accounts = array();
        $plans = $this->config->item('PLAN');
        $plan_data = $this->config->item('PLAN_INFO');

        $this->lang->load('account');

        foreach ($accountlist as $cid) {
            $result = $this->user->clientdatabyid($cid);
            $plan_key = array_search($result['userplan'], $plans);
            $result['plan_name'] = $plan_data[$plan_key]['name'];
            $pq = $plan_data[$plan_key]['quota'];
            $result['plan_quota'] = $pq > 1E10 ? $this->lang->line('unlimited') : $pq;
            $accounts[] = $result;
        }
        $data['accounts'] = $accounts;

        // css array
        $arrCss[] = base_url() . 'css/validationEngine.jquery.css';
        $arrCss[] = base_url() . 'css/template.css';
        $pageid['css'] = $arrCss;
        // js array
        $arrJavascript[] = base_url() . 'js/validateplaymode.js';
        $arrJavascript[] = base_url() . 'jsi18n/jqueryValidationEngine.js';
        $arrJavascript[] = base_url() . 'js/jquery.validationEngine.js';
        $arrJavascript[] = base_url() . 'js/jquery.qtip-1.0.0-rc3.js';
        $data['js'] = $arrJavascript;
        // additional css
        $pageid['others'] = '<style type="text/css">
                    * 
                    label.error { color: red;font-size: 11px;  }
            </style>';
        // page title
        $data['title'] = $this->lang->line('title_collectionoverview');
        $data['headimg'] = base_url() . "images/logo_sml.png";

        $this->load->view('includes/protected_header', $data);
        $this->load->view('user/subaccounts', $data);
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for update profile
     */

    function updateprofile() {
        //session checking
        $thisClientId = $this->input->post('clientid');
        $clientid = getClientIdForAction($thisClientId);
        if ($clientid) {
            $firstname = $this->input->post('firstname');
            $lastname = $this->input->post('lastname');
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            $confirmpassword = $this->input->post('confirmpassword');
            $invoice_data = ($this->input->post('invoice_data') == 'true') ? true : false;
            $invoice_recipient = $this->input->post('invoice_recipient');
            $invoice_company = $this->input->post('invoice_company');
            $invoice_address = $this->input->post('invoice_address');
            $invoice_zip = $this->input->post('invoice_zip');
            $invoice_city = $this->input->post('invoice_city');
            $invoice_country = $this->input->post('invoice_country');

            $modifyDate = date('Y-m-d H:i:s');
            // serverside validations
            $errmsg = profilevalidation($firstname, $lastname, $email, md5($password), md5($confirmpassword));
            if ($errmsg == '') {
                // check wether email has changed or not
                $profile = $this->user->clientdatabyid($clientid);
                $oldemail = $profile['email'];
                // if changed resend activation link
                if ($email != $oldemail) {
                    // send validation mail to new email
                    $this->sendvalidationmail($clientid, $email, $firstname, "validate");
                    // send notification mail to old email
                    $this->sendvalidationmail($clientid, $oldemail, $firstname, "notifychange");
                } else {
                    // if email is not changed, then delete it because this indicates to the updateprofile()
                    // that the status of the email shall not be reset to "not validated"
                    $email = '';
                }
                $this->user->updateprofile($clientid, $firstname, $lastname, $email, $password, $invoice_data, $invoice_recipient, $invoice_company, $invoice_address, $invoice_zip, $invoice_city, $invoice_country, $modifyDate);
                redirect($this->config->item('base_ssl_url') . "lpc/cs/" . $clientid . '/');
            } else {
                $profileData['profileDetails'] = $this->user->clientdatabyid($clientid);
                $profileData['clientid'] = $clientid;
                $profileData['errMsg'] = $errmsg;
                // js array	
                $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';
                $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
                $profileData['js'] = $arrJavascript;
                // css array
                $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
                $arrCss[] = base_ssl_url() . 'css/template.css';
                $profileData['css'] = $arrCss;
                // additional css
                $profileData['others'] = '<style type="text/css">
                        * 
                        label.error { color: red;font-size: 11px;  }
                    </style>';
                // page title
                $profileData['title'] = $this->lang->line('title_profile') . $this->lang->line('title_divsimbol') . $this->lang->line('title_sitename');
                $profileData['force_ssl'] = true;
                // force profile page to be displayed with SSL
                $this->load->view('includes/protected_header', $profileData);
                $this->load->view('user/profile', $profileData);
                $this->load->view('includes/public_footer');
            }
        } else {
            redirect('lpc');
        }
    }

    /*
     * 
     *  controller for cancel questionnaire
     */

    function unsubscribe() {
        //session checking
        $clientid = $this->session->userdata('sessionUserId');
        if ($clientid) {
            // css array
            //$arrCss[] = base_url() . 'css/popup_new.css';
            $pageid['css'] = $arrCss;
            // js array
            //$arrJavascript[] = base_url() . 'js/popup.js';
            $pageid['js'] = $arrJavascript;
            // page title
            $pageid['title'] = $this->lang->line('title_cancelsub');
            ;
            $this->load->view('includes/protected_header', $pageid);
            $this->load->view('user/questionnaire');
            $this->load->view('includes/public_footer');
        } else {
            redirect('lpc');
        }
    }

    /*
     * 
     *  controller for cancel subscription
     */

    function cancelsub() {
        $clientid = $this->session->userdata('sessionUserId');
        $currentDate = date('Y-m-d H:i:s');
        $this->user->cancelsubscription($clientid, $currentDate);
        echo 1;
    }

    /*
     * 
     *  controller for set quistionnaire
     */

    function setquistionnaire() {
        $clientid = $this->session->userdata('sessionUserId');
        $satisfaction = $this->input->post('satisfaction');
        $cancelsub = $this->input->post('cancelsub');
        $feedback = $this->input->post('feedback');
        $currentDate = date('Y-m-d H:i:s');
        $this->user->setquistionnaire($clientid, $satisfaction, $cancelsub, $feedback, $currentDate);

        $baseurl = $this->config->item('base_url');
        $lg = $this->config->item('language');
        $purl = $this->config->item('page_url');
        $logouturl = $baseurl . $purl[$lg]['logout'];
        redirect($logouturl);
    }

    /*
     *  controller for email teaser
     */

    function tae() {
        $email = $this->input->get('email');
        $status = $this->user->saveLead(TEASER_CAMPAIGN_ID, $email);
        echo("<script>trackConversion();</script>");
    }

    /**
     * Loads the "order plan" page
     */
    function order() {
        $clientid = $this->session->userdata('sessionUserId');

        if ($clientid) {

            $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';

            $arrJavascript[] = base_ssl_url() . 'js/order.js';
            $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
            $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';

            $data['css'] = $arrCss;
            $data['js'] = $arrJavascript;
            $data['title'] = $this->lang->line('title_order');

            $this->load->view('includes/protected_header', $data);
            $this->load->view('protected/order.php');
            $this->load->view('includes/public_footer');
        } else {
            $this->session->set_userdata('login_targetpage', $this->config->item('base_ssl_url') . 'users/order');
            lang_redirect('login');
        }
    }

    /**
     * After submitting the order form, this method sends the corresponding email to the site admin
     * with the client information
     */
    function orderconfirm() {
        if ($this->input->post('email')) {
            $email = $this->input->post('email');
            $userplan = $this->input->post('userplan');
            $clientid = $this->session->userdata('sessionUserId');

            $message = 'Client ID: ' . $clientid
                    . ' Email: ' . $email
                    . ' Userplan: ' . $userplan
                    . ' Invoice Address: ' . $this->input->post('address');

            $this->email->to($this->config->item['ADMIN_EMAIL']);
            $this->email->subject('BlackTri Order.');
            $this->email->set_mailtype('text');
            $this->email->message($message);
            $this->email->send();

            dblog_debug('Order mail sent from ' . $email . '  Message: ' . $message);

            $this->load->view('includes/protected_header');
            $this->load->view('protected/orderconfirm.php');
            $this->load->view('includes/public_footer');
        } else {
            header('Location: ' . base_ssl_url() . 'users/order');
        }
    }

    /**
     * shows used quota
     */
    function shq() {
        $clientid = $this->session->userdata('sessionUserId');

        if ($clientid) {

            $usageData = $this->user->getUsedQuotaData($clientid);
            $data = array(
                'usageData' => $usageData,
            );
            $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';
            $arrJavascript[] = base_ssl_url() . 'js/order.js';
            $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
            $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';

            $data['css'] = $arrCss;
            $data['js'] = $arrJavascript;
            $data['title'] = $this->lang->line('title_order');

            $this->load->view('includes/protected_header', $data);
            $this->load->view('user/used_quota.php',$data);
            $this->load->view('includes/public_footer');
        } else {
            $this->session->set_userdata('login_targetpage', $this->config->item('base_ssl_url') . 'users/order');
            lang_redirect('login');
        }
    }

    /**
     * Loads the "request demo" page
     */
    function demo() {

        $arrCss[] = base_ssl_url() . 'css/validationEngine.jquery.css';

        $arrJavascript[] = base_ssl_url() . 'js/order.js';
        $arrJavascript[] = base_ssl_url() . 'js/jquery.validationEngine.js';
        $arrJavascript[] = base_ssl_url() . 'jsi18n/jqueryValidationEngine.js';

        $data['css'] = $arrCss;
        $data['js'] = $arrJavascript;
        $data['title'] = $this->lang->line('title_demo');

        $this->load->view('includes/public_header', $data);
        $this->load->view('public/request_demo.php');
        $this->load->view('includes/public_footer');
    }

    /**
     * After submitting the order form, this method sends the corresponding email to the site admin
     * with the client information
     */
    function democonfirm() {
        if ($this->input->post('email')) {
            $email = $this->input->post('email');
            $name = $this->input->post('name');
            $message = $this->input->post('message');

            $mymessage = ' Email: ' . $email
                    . ' Name: ' . $name
                    . ' Message: ' . $message;

            $this->email->to($this->config->item['ADMIN_EMAIL']);
            $this->email->subject('BlackTri Demo.');
            $this->email->set_mailtype('text');
            $this->email->message($mymessage);
            $this->email->send();

            dblog_debug('Demo request mail sent from ' . $email . '  Message: ' . $mymessage);

        $this->load->view('includes/public_header', $data);
        $this->load->view('public/request_demo_confirm.php');
        $this->load->view('includes/public_footer');
        } else {
            header('Location: ' . base_ssl_url() . 'users/demo');
        }
    }


    /*
     *  function for user logout
     */

    function logout() {
        dblog_message(LOG_LEVEL_INFO, LOG_TYPE_LOGOUT, "", $clientid);
        $this->session->unset_userdata('sessionUserId');
        $this->session->unset_userdata('sessionUserRole');
        $this->session->unset_userdata('editor_collectionid');
        $this->session->unset_userdata('sessionLoginStatus');
        unsetFeatureMatrix();
        // page title
        $pageid['title'] = $this->lang->line('title_logout');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/logout');
        $this->load->view('includes/public_footer');
    }

}

?>