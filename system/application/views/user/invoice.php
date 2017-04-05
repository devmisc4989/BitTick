<?php
$baseurl = $this->config->item('base_url');
// encryption library
$this->load->library('encrypt');
?>
<style type="text/css">
    * 
    label.error { color: red;font-size: 11px;  }
</style>
<!--	script validate form	-->
<script>
    $(document).ready(function() {
        // SUCCESS AJAX CALL, replace "success: false," by:     success : function() { callSuccessFunction() }, 
        $("#frmInvoice").validationEngine()
    });
    var path = "<?php echo $baseurl ?>";
</script>
<form name="frmInvoice" id="frmInvoice" method="post" action="<?php echo $baseurl; ?>users/setuserinvoice/">
    <?php
    foreach ($invoiceDetails as $invoiceData) {
        ?>
        <div class="whitebox">
            <div class="title"><?php echo $this->lang->line('title_invoice'); ?>
                <a class="cancel-subscription" href="<?php echo $baseurl; ?>users/quistionnaire/"><?php echo $this->lang->line('link_cancel'); ?></a>
            </div>
            <div class="error-message"><?php if (isset($errMsg)) echo $errMsg; ?></div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_firstname'); ?></label>
                    <input class="validate[required,custom[noSpecialCaracters],length[0,50]] textbox"  type="text" id="firstname" name="firstname" value="<?php echo $invoiceData->billing_firstname; ?>"/>
                </div>
                <div class="field">
                    <label><?php echo $this->lang->line('table_lastname'); ?></label>
                    <input type="text" id="lastname" name="lastname" class="validate[required,length[0,50]] textbox" value="<?php echo $invoiceData->billing_lastname; ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_company'); ?></label>
                    <input type="text" id="company" name="company" class="validate[required,length[0,50]] textbox" value="<?php echo $invoiceData->billing_company; ?>"/>
                </div>
                <div class="field">
                    <label><?php echo $this->lang->line('table_address'); ?></label>
                    <input type="text" id="address" name="address" class="validate[required,length[0,50]] textbox" value="<?php echo $invoiceData->billing_address; ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_zip'); ?></label>
                    <input type="text" id="zipcode" name="zipcode" class="validate[required,custom[onlyNumber],length[0,50]] textbox" value="<?php echo $invoiceData->billing_zip; ?>"/>
                </div>
                <div class="field">
                    <label><?php echo $this->lang->line('table_city'); ?></label>
                    <input type="text" id="city" name="city" class="validate[required,length[0,40]] textbox" value="<?php echo $invoiceData->billing_city; ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_vatno'); ?></label>
                    <input type="text" id="vatno" name="vatno" class="validate[required,custom[onlyNumber],length[0,50]] textbox" value="<?php echo $this->encrypt->decode($invoiceData->billing_vatno); ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_account'); ?></label>
                    <input type="text" id="accountno" name="accountno" class="validate[required,length[0,50]] textbox" value="<?php echo $this->encrypt->decode($invoiceData->billing_accountno); ?>"/>
                </div>
                <div class="field">
                    <label><?php echo $this->lang->line('table_bankcode'); ?></label>
                    <input type="text" id="bankcode" class="textbox" name="bankcode" value="<?php echo $this->encrypt->decode($invoiceData->billing_bankno); ?>"/>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('table_bank'); ?></label>
                    <input type="text" id="bank" name="bank" class="validate[required,length[0,50]] textbox" value="<?php echo $this->encrypt->decode($invoiceData->billing_bank); ?>"/>
                </div>
                <div class="field">
                    <label><?php echo $this->lang->line('table_accountholder'); ?></label>
                    <input type="text" id="accountholder" name="accountholder" class="validate[required,length[0,50]] textbox" value="<?php echo $invoiceData->billing_holder; ?>"/>
                </div>
            </div>
            <input type="submit" name="submit" class="button save" id="submit" value="<?php echo $this->lang->line('button_save'); ?>"/>
            <input type="reset" name="cancel" id="cancel" class="button-grey cancel" value="<?php echo $this->lang->line('button_cancel'); ?>" onclick="location.href = '<?php echo $baseurl; ?>lpc/cs'"/>
        </div>
        <?php
    }
    ?>
</form>
