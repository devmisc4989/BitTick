<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('order');
?>

<script type="text/javascript">
    var path = "<?php echo $basesslurl; ?>";
    $(document).ready(function () {
        $('#orderForm').validationEngine();
    });
</script>

<div id="main_container">

    <div class="whitebox">

        <div class="head_line_container">
            <div class="title">
                <div class="head_line_title"><?= $this->lang->line('order_title'); ?></div>
            </div>
            <div class="head_line_context">
                <?= $this->lang->line('order_intro'); ?>
            </div>
            <div class="error-message"><?= isset($errMsg) ? $errMsg : ''; ?></div>
        </div>

        <form name="orderForm" id="orderForm" method="post" action="<?= $basesslurl; ?>users/orderconfirm/">
            <div class="field-wrap">
                <div class="field">
                    <label><?= $this->lang->line('order_email_field'); ?></label>
                    <div class="clearboth"></div>
                    <input class="validate[required,custom[email]] textbox"  type="text" id="email" name="email" />
                    <div class="clearboth"></div>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?= $this->lang->line('order_plan_field'); ?></label>
                    <div class="clearboth"></div>
                    <select id="userplan" name="userplan" class="dropdown validate[required]" >
                        <option value="basic-monthly">Basic Laufzeit monatlich (47€ pro Monat)</option>
                        <option value="basic-yearly">Basic Laufzeit 1 Jahr (39€ pro Monat)</option>
                        <option selected="selected" value="professional-monthly">Professional Laufzeit monatlich (119€ pro Monat)</option>
                        <option selected="selected" value="professional-yearly">Professional Laufzeit 1 Jahr (99€ pro Monat)</option>
                        <option value="enterprise">Enterprise (bitte rufen Sie mich zurück)</option>
                    </select>
                    <div class="clearboth"></div>
                </div>
            </div>
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('order_address_field'); ?></label>
                    <div class="clearboth"></div>
                    <textarea class="validate[required,minSize[10]]" id="address" name="address"></textarea>
                    <p class="address_tooltip">
                        <?= $this->lang->line('order_address_tootip') ?>
                    </p>
                    <div class="clearboth"></div>
                </div>
            </div>

            <input type="submit" class="button save" name="submit" id="submit" value="<?= $this->lang->line('order_submit'); ?>"/>
            <input type="hidden" name="basessl" id="basessl" value="<?= $basesslurl; ?>"/>
            <input type="hidden" name="clientid" value="<?= $clientid; ?>"/>
        </form>
    </div>
</div>