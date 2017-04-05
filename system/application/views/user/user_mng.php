<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('profile');
$this->lang->load('editor');

$planCodes = array_flip($this->config->item('PLAN'));
$myPlanCode = $planCodes[$userplan];
if(isset($myPlanCode)) {
    $planInfo = $this->config->item('PLAN_INFO');
    $myPlanName = $planInfo[$planCodes[$userplan]]['name'];
}
else {
    $myPlanName = "---";
}
$myPlanQuota = $quota;
$myUsedQuota = $usedquota;
$myQuotaUsage = sprintf($this->lang->line('account quota usage'),$myUsedQuota);    
$quotaUsageUrl = $basesslurl . "users/shq/";

if ($status == 'ACTIVE') {
    $created = strtotime($createddate);
    $diff = ceil(($created + 2592000 - time()) / 86400);
    $diff = ($diff < 0) ? 0 : $diff;

    $orderurl = $basesslurl . "users/order/";
    if ($diff > 0) {
        $accountStatus = sprintf($this->lang->line('error_timetotest_profile_page'), $diff, $orderurl);
    } else {
        $accountStatus = sprintf($this->lang->line('error_testexceeded_profile_page'),$orderurl);
    }
    // do not show the notification in the first few days
    if($diff > 28) {
        $accountStatus = $this->lang->line('account test phase');        
    }
} else {
    $accountStatus = $this->lang->line('account plan') . " <span class='green'>$myPlanName</span>";
}


?>
<div id="main_container">
    <div class="whitebox">
        <!--	script validate form	-->
        <script>
            $(document).ready(function() {
                // SUCCESS AJAX CALL, replace "success: false," by:     success : function() { callSuccessFunction() }, 
                $("#frmProfile").validationEngine();
				$('#trackingcode_ocpc').bind('focus click', function(){
					this.select();
				});
            });
            // JUST AN EXAMPLE OF VALIDATIN CUSTOM FUNCTIONS : funcCall[validate2fields]
            function validate2fields() {
                if ($("#firstname").val() == "" || $("#lastname").val() == "") {
                    return true;
                } else {
                    return false;
                }
            }
            var path = "<?php echo $basesslurl ?>";
        </script>

        <?php
        if ($plan_haserror && ($this->config->item('tenant') != 'dvlight')) {
            ?>
            <div class="notification">
                <ul>
                    <li class="icon_2"><?php echo $plan_errmsg; ?></li>
                </ul>
            </div>
            <?php
        }
        ?>

        <div class="title"><?php echo $this->lang->line('profile_headline'); ?>
            <!--
            <a class="cancel-subscription" href="<?php echo $baseurl; ?>users/unsubscribe/"><?php echo $this->lang->line('profile_cancel'); ?></a>
            -->
        </div>
        <div class="error-message"><?php if (isset($errMsg)) echo $errMsg; ?></div>
        
        <div class="account_left_side" style="width:50%; float:left;">
        
        <form name="frmProfile" id="frmProfile" method="post" action="<?php echo $basesslurl; ?>users/updateprofile/">
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_company'); ?></label>
                    <input class="validate[required,minSize[0],maxSize[128]] textbox"  type="text" id="firstname" name="firstname" value="<?php echo $firstname; ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_lastname'); ?></label>
                    <input type="text" id="lastname" name="lastname" class="validate[required,minSize[0],maxSize[128]] textbox" value="<?php echo $lastname; ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php
                        echo $this->lang->line('table_email');
                        ?></label>
                    <input type="text" id="email" name="email" class="validate[required,custom1[email],ajax[ajaxEmail]] text-input textbox" value="<?php echo $email; ?>"/>
                    <?php
                    if (!$emailvalidated) {
                        // email not validated
                        $lang_line = $this->lang->line('link_sendactivation');
                        echo "<div id='resendMail'>" . $this->lang->line('profile_emailnotvalidated') . "<a class='send-link' onclick='emailResend(\"$publicid\", 1)'>$lang_line</a></div>";
                    }
                    ?>      	
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_password'); ?></label>
                    <input type="password" id="password" name="password" class="text-input textbox"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_cpassword'); ?></label>
                    <input class="validate[equals[password]] text-input textbox" type="password" id="confirmpassword" name="confirmpassword" />
                </div>
            </div>

            <input type="submit" class="button save" name="submit" id="submit" value="<?php echo $this->lang->line('button_save'); ?>"/>
            <input type="reset" name="cancel" id="cancel" class="button-grey cancel" value="<?php echo $this->lang->line('button_cancel'); ?>" onclick="location.href = '<?php echo $basesslurl . "lpc/cs/" . $clientid; ?>'"/>
            <input type="hidden" id="path" name="path" value="<?php echo $baseurl; ?>"/>
            <input type="hidden" name="clientid" value="<?php echo $clientid; ?>"/>
        </form>
    </div>

    <div class="account_right_side">
        
        <div class="box">
        <h3><?php echo $this->lang->line('account status headline'); ?></h3>
        <p><?php echo $accountStatus; ?></p>
        </div>
        
        <div class="box">
        <h3><?php echo $this->lang->line('account quota headline'); ?></h3>
        <p><?php echo sprintf($this->lang->line('account quota info'),$myPlanQuota); ?><br />
        <?php echo $myQuotaUsage; ?>
        <br><a href="<?= $quotaUsageUrl ?>"><?php echo $this->lang->line('account quota details link'); ?></a>
        </div>    

        <div class="box">
        <h3><?php echo $this->lang->line('account api headline'); ?></h3>
        <p><?php echo sprintf($this->lang->line('account api info'),$this->lang->line('api_link_helpsupport_target')); ?><br />
        <?php echo sprintf($this->lang->line('account api key'),$apikey); ?><br />
        <?php echo sprintf($this->lang->line('account api secret'),$apisecret); ?></p>
        </div>    

        <div class="box">
        <h3><?php echo $this->lang->line('account tracking code'); ?></h3>
        <p><?php echo $this->lang->line('OCPC Tracking code description'); ?><br><?php echo splink('wizard_step4c'); ?></p>
        <textarea class="textbox trackingcode w100" wrap="off" name="trackingcode_ocpc" id="trackingcode_ocpc" rows="4"><?php echo $trackingcode ?></textarea>
        </div>    
    </div>        

    </div>
</div>