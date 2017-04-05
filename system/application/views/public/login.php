<?php
$baseurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('login');
$this->lang->load('validatelogin');

//die($showErrorOnLoad);

if ($showErrorOnLoad == "true")
    $errormessage = $this->lang->line('validatelogin_inputerror');
else
    $errormessage = "";

$cta_link = $baseurl . $purl[$lg]['register'] . '100';
?>
<div id="scrollToHere"></div>
<div id="inner_bg">
    <form name="frmLogin" id="frmLogin" action="<?php echo $baseurl; ?>users/signin/" method="post">
        <div id="login_container">
            <div id="login_top"></div>
            <div id="login_mdle">
                <h2><?php echo $this->lang->line('title_signin'); ?></h2>
                <div class="error-message" id="msg"><?php echo $errormessage; ?></div>
                <div class="label"></div>
                <div class="login-field">
                    <label><?php echo $this->lang->line('table_email'); ?></label> 
                    <input type="text" id="email" name="email" class="validate[required] login-textbox" />
                </div>
                <div class="login-field">
                    <label><?php echo $this->lang->line('table_password'); ?></label>
                    <input type="password" id="password" class="validate[required] text-input login-textbox" name="password" />
                </div>
                <input type="submit" id="signin_button" class="but signin disable" name="Submit" value="<?php echo $this->lang->line('button_signin'); ?>"/>
                <div class="cant-access"><a href="javascript://" onclick="SavePopupHtml();
                OpenPopup('#recoverpwd');"><?php echo $this->lang->line('link_forgotpassword'); ?></a></div>
            </div>
            <div id="login_btm"></div>
            <div class="signup-now"><a href="<?php echo $cta_link; ?>"><?php echo $this->lang->line('title_tosignup'); ?></a></div>
        </div>
        <input type="hidden" name="path" value="<?php echo $baseurl; ?>"/>
        <input type="hidden" name="externallogin" value="true"/>
    </form>
</div>
<!--POPUP for password remainder-->
<div style="display:none;">
    <div class="confirmation confirmation-user" id="recoverpwd">
        <div id="resend-message">
            <form name='frmPassword' method="post" onsubmit="return false">
                <h1><?php echo $this->lang->line('title_cantsignin'); ?></h1>
                <div><?php echo $this->lang->line('title_cantsigninoptions'); ?></div>
                <div class="error-message w100" id="error-message"></div>
                <div class="confirmation-field w100">
                    <label><?php echo $this->lang->line('title_cantsignin_inputfield'); ?></label>
                    <br />
                    <input type="text" id="forgot_username" name="forgot_username" class="login-textbox" title="Enter username or email" />
                </div>
                <input type="submit" class="but submit clear" value="<?php echo $this->lang->line('button_send'); ?>" onclick="passwordRemainder()"/>
                <div class="cant-access"><a href="javascript://" onclick="ClosePopup()"><?php echo $this->lang->line('title_cantsignin_close'); ?></a></div>
            </form>
        </div>
        <div id='email_success' style="display: none">
            <input type="submit" class="but submit clear" value="<?php echo $this->lang->line('button_ok'); ?>" onclick="ClosePopup()"/>
        </div>
    </div>
    <input type="hidden" id="resend-message-copy" />
</div>
<!--END POPUP-->
<script type="text/javascript">
// enable button if javascript enabled
            $('#signin_button').removeClass('but signin disable').addClass('but signin');
            $('#signin_button').attr("disabled", "");
            click_handler_signin_button = function() {
                if (buttonEl.getAttribute('type') == 'submit') {
                    loginValidate();
                }
            }
            document.getElementById("signin_button").disabled = false;
            document.getElementById('email').focus();
            function loginValidate()
            {
                //move to load
                //$("#frmLogin").validationEngine({returnIsValid:true,scroll:false})
                loginValidation();
                //$.validationEngine.closePrompt('.formError',true);
            }


            var path = "<?php echo $baseurl ?>";

            $("#frmLogin").validationEngine({returnIsValid: true, scroll: false})

            function SavePopupHtml()
            {
                if ($('#resend-message-copy').val() != '')
                {
                    $('#email_success').hide();
                    $('#resend-message').html($('#resend-message-copy').val());
                    $("#frmLogin").validationEngine({returnIsValid: true, scroll: false})
                }
                else
                    $('#resend-message-copy').val($('#resend-message').html());
            }
</script>
