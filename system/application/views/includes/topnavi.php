<?php
// get language dependent page urls
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
$tenant = $this->config->item('tenant');

// if etracker, do not display no navigation header at all
if ($tenant != 'etracker') {

    //session checking
    // if clientid is not set from controller, use clientid from logged in user, if present
    if (!$clientid) {
        $clientid = $this->session->userdata('sessionUserId');
    }
    // if user is loggedin, set homepage == collection overview page
    // and store login-status in session to allow the wordpress blog to retrieve this
    //session_start();
    if ($clientid) {
        $home_url = $basesslurl . 'lpc/cs';
        $_SESSION['user_login_status'] = 'loggedin';

        $CI = & get_instance();
        if (!isset($CI->user)) {
            $CI->load->model('user');
        }
        $data = $CI->user->clientdatabyid($clientid);
        $client_email = $data['email'];
        $client_role = $data['role'];
        $client_first_name = $data['firstname'];

        if($client_role == CLIENT_ROLE_MASTER){
            $client_role_label = $this->lang->line('label_master');
        }
        elseif($client_role == CLIENT_ROLE_NORMAL){
            $client_role_label = $this->lang->line('label_normal');
        }
        else{
            $client_role_label = $this->lang->line('label_sub');
        }
    } else {
        $home_url = $baseurl . $purl[$lg]['home'];
        $_SESSION['user_login_status'] = 'loggedout';
    }

    $link_home = $home_url;
    $link_tour = $baseurl . $purl[$lg]['tour'];
    $link_plans = $baseurl . $purl[$lg]['plans'];
    $link_help = $this->lang->line('url_helpsupport');
    $link_blog = $baseurl . $purl[$lg]['blog'];
    $appname = "";
    ?>
    <div id="header_wrap">
        <div id="header">
            <div class="logotype"><a href="<?php echo $link_home ?>">Blacktri</a></div><span class="appname"><?php echo $appname ?></span>
            <div id="main_nav">
                <ul>
                    <li><a href="<?php echo $link_home; ?>" <?php if (isset($activehome)) echo $activehome ?>><?php echo $this->lang->line('link_home'); ?></a></li>
                    <li><a href="<?php echo $link_tour; ?>" <?php if (isset($activetr)) echo $activetr ?>><?php echo $this->lang->line('link_tour'); ?></a></li>
                    <li><a href="<?php echo $link_plans; ?>" <?php if (isset($activeplans)) echo $activeplans ?>><?php echo $this->lang->line('link_plans'); ?></a></li>
                    <li><a href="<?php echo $link_help; ?> " target="_new"><?php echo $this->lang->line('link_helpsupport'); ?></a></li>
                    <!-- <li><a href="<?php echo $link_blog; ?>"><?php echo $this->lang->line('link_blog'); ?></a></li> -->
                    <?php
                    if ($clientid) {
                        ?>
                        <li><a href="<?php echo $baseurl . $purl[$lg]['logout']; ?>"><?php echo $this->lang->line('link_logout'); ?></a></li>
                        <?php
                    } else {
                        ?>
                        <li><a href="<?php echo $basesslurl . $purl[$lg]['login']; ?>" <?php if (isset($activesi)) echo $activesi ?>><?php echo $this->lang->line('link_signin'); ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <?php
// if user is logged in, display protected navigation 
    if ($clientid) {
        ?>
        <div id="admin_header">
            <div class="admin-header-box">
                <div class="admin-header-left">
                    <div class="admin-nav">
                        <ul id="top_dash_menu">
                            <li class="dropdown">
                                <a class="dropbtn" href="<?php echo $basesslurl; ?>lpc/cs/<?php echo $clientid; ?>/">
                                    <?php echo $this->lang->line('link_clientarea') . " (" . $client_email . ")"; ?>
                                    <i class="fa fa-icon fa-chevron-down"></i>
                                </a>
                                <ul class="dropdown-content">
                                  <li>
                                    <a href="#"><?php echo $client_email; ?></a>
                                  </li>
                                  <li>
                                    <a href="#"><?php echo "Blue Wale"; ?></a>
                                  </li>
                                </ul>                                                                
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="admin-header-right">
                    <div class="admin-nav">
                        <ul id="top_profile_menu">
                            <li class="dropdown">
                                <?php
                                if ($client_role == CLIENT_ROLE_MASTER) {
                                    ?>
                                    <a href="<?php echo $basesslurl; ?>users/subaccounts/"><?php echo $this->lang->line('link_subaccounts'); ?></a>&nbsp;|&nbsp;<?php
                                }
                                ?><!-- <a 
                                href="<?php echo $basesslurl . "users/account/" . $clientid; ?>"><?php echo $this->lang->line('link_account'); ?></a>&nbsp;|&nbsp;-->
                                <a class="dropbtn" href="#"><?php echo "$client_first_name ($client_role_label)"; ?>
                                    <i class="fa fa-icon fa-chevron-down"></i>
                                </a>
                                <ul class="dropdown-content">
                                  <li><a href="<?php echo $basesslurl . "users/gup/" . $clientid; ?>"><?php echo $this->lang->line('link_profile'); ?></a>
                                  </li>
                                  <li>
                                    <a href="<?php echo $basesslurl . "users/account_setting/" . $clientid; ?>"><?php echo $this->lang->line('link_account_setting'); ?></a>
                                  </li>
                                  <li>
                                    <a href="<?php echo $basesslurl . "users/user_mng/" . $clientid; ?>"><?php echo $this->lang->line('link_user_management'); ?></a>
                                  </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>		
        </div>
        <?php
    }
} // end if etracker
?>