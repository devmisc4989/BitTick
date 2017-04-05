<?php
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
// encryption library
$this->load->library('encrypt');

$this->lang->load('signup');
$this->lang->load('tc');
$tenant = $this->config->item('tenant');
?>
<div>
    <form name="signupform" id="signupform" method="post" action="<?php echo $basesslurl; ?>users/signup/?istest=yes">
        <div id="signup_container">
            <div class="signup-left">
                <div class="signup-title">
                    <div class="signup-no">1</div>
                    <h3><?php echo $this->lang->line('title_createaccount'); ?></h3>
                </div>
                <div class="signup-field">
                    <div class="signup-message red"><?php if (isset($errMsg)) echo $errMsg; ?></div>
                    <label><?php echo $this->lang->line('table_firstname'); ?></label>
                    <input type="text" name="firstname" id="firstname" value="<?php if ($this->input->post("firstname")) echo $this->input->post("firstname"); ?>"class="validate[required,minSize[0],maxSize[128]] textbox" />
                    <label><?php echo $this->lang->line('table_lastname'); ?></label>
                    <input type="text" name="lastname" id="lastname" class="validate[required,minSize[0],maxSize[128]] textbox" value="<?php if ($this->input->post("lastname")) echo $this->input->post("lastname"); ?>"/>
                    <label><?php echo $this->lang->line('table_email'); ?></label>
                    <input type="text" name="email" id="email" class="validate[required,custom1[email],ajax[ajaxEmail]] text-input textbox" value="<?php if ($this->input->post("email")) echo $this->input->post("email"); ?>"/>
                    <label><?php echo $this->lang->line('table_password'); ?></label>
                    <input class="validate[required,custom[noSpecialCaracters],minSize[6],maxSize[20]] text-input textbox" type="password" name="passwordvalue" id="passwordvalue"/>
                    <label><?php echo $this->lang->line('table_cpassword'); ?></label>
                    <input class="validate[required,equals[passwordvalue]] text-input textbox" type="password" name="retypepassword" id="retypepassword"/>
                </div> 
                <div class="signup-title">
                    <div class="signup-no">2</div>
                    <h3><?php echo $this->lang->line('title_billinfo'); ?></h3>
                </div>
                <div id="scrollToHere"></div>
                <div class="signup-info"><input type="checkbox" class="validate[minCheckbox[1]] checkbox" name="tccheckbox" id="tccheckbox"/> <?php echo $this->lang->line('signup_terms_before'); ?>
                    <?php
                    if ($tenant == 'dvlight') {
                        ?>
                        <a href="<?php echo $this->lang->line('signup_terms_divolution_pdf'); ?>" target="_blank" id="button"><?php echo $this->lang->line('link_terms'); ?></a>
                        <?php
                    } else {
                        ?>
                        <a href="javascript://" onclick="$('.formError').remove();
                            OpenPopup('#terms')" id="button"><?php echo $this->lang->line('link_terms'); ?></a>
                        <?php
                    }
                    ?>
                    <?php echo $this->lang->line('signup_terms_after'); ?></div>
                <input type="submit" name="signup" id="signin_button" class="but-big create-my-account disable" disabled="disabled" value="<?php echo $this->lang->line('signup_button'); ?>"/>
            </div>
        </div>
        <input type="hidden" id="path" name="path" value="<?php echo $baseurl; ?>"/>

</div>
<input type="hidden" name="userplan" id="userplan" value="<?php if (isset($userplan)) echo $userplan ?>">
</form>
</div>
<!--POPUP for password remainder-->
<div style="display:none;">
    <div class="confirmation confirmation-user" style="height:490px; width:660px;" id="terms">
        <div class="confirmation align-left"  style="height:460px;overflow:scroll; overflow-x: auto; padding:20px 0 0 0; width:670px">
            <div>
<?php echo $this->lang->line('tc_terms_copy'); ?>
            </div>
        </div>
    </div>
</div>
<!--END POPUP-->

<script type="text/javascript">
//enable button if javascript enabled
                    $('#signin_button').removeClass('but-big disable').addClass('but-big create-my-account');
                    $('#signin_button').attr("disabled", "");
                    document.getElementById('firstname').focus();
</script>