<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('demo');
?>

<script type="text/javascript">
    var path = "<?php echo $basesslurl; ?>";
    $(document).ready(function () {
        $('#orderForm').validationEngine();
    });
</script>

<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('demo head'); ?></h2>
    </div>
</div>
<div id="main_container" style="margin-bottom:100px;">
    <div class="whitebox">

        <div class="terms">
            <h3><?php echo $this->lang->line('demo subline'); ?></h3>
            <p><?php echo $this->lang->line('demo intro'); ?></p>
        </div>

        <form name="orderForm" id="orderForm" method="post" action="<?= $basesslurl; ?>users/democonfirm/">
            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('demo label name'); ?></label>
                    <div class="clearboth"></div>
                    <input class="validate[required,minSize[0],maxSize[128]] textbox"  type="text" id="name" name="name" />
                    <div class="clearboth"></div>
                </div>
            </div>

            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('demo label email'); ?></label>
                    <div class="clearboth"></div>
                    <input class="validate[required,custom[email]] textbox"  type="text" id="email" name="email" />
                    <div class="clearboth"></div>
                </div>
            </div>

            <div class="field-wrap">
                <div class="field">
                    <label><?php echo $this->lang->line('demo label message'); ?></label>
                    <div class="clearboth"></div>
                    <textarea id="message" name="message"></textarea>
                    <div class="clearboth"></div>
                </div>
            </div>

            <input type="submit" class="button signup1" name="submit" id="submit" value="<?php echo $this->lang->line('demo submit'); ?>"/>
            <input type="hidden" name="basessl" id="basessl" value="<?= $basesslurl; ?>"/>
            <input type="hidden" name="clientid" value="<?= $clientid; ?>"/>
        </form>
    </div>
</div>