<?php
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$this->lang->load('signup');
?>
<div style="margin-top:10px;width:600px;" class="confirmation confirmation-user" >
    <h1><?php echo $this->lang->line('title_test_user_info'); ?></h1>
    <h3><?php echo $this->lang->line('title_test_user_subline'); ?></h3>
    <div style="margin-top:10px;" ><?php echo $this->lang->line('desc_test_user_info'); ?></div>
    <div class="ctrl-buttons">
        <div class="links"><a href="javascript://" onclick="CancelSignUp()"><?php echo $this->lang->line('cancel_test_user_info'); ?></a></div>
        <a href="<?php echo $basesslurl; ?>users/sutest/"><input class="button ok" type="submit" value="<?php echo $this->lang->line('button_test_user_info'); ?>"></a>
    </div>	
</div>
<script>
            document.domain = '<?php echo $this->config->item('document_domain'); ?>';
            function CancelSignUp()
            {
                if (window.top && window.top.TestUserCanceled)
                    window.top.TestUserCanceled();
            }
</script>
