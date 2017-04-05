<?php
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$this->lang->load('signup');
?>
<div style="margin-top:10px;width:600px;" class="confirmation confirmation-user" >
    <h1><?php echo $this->lang->line('title_test_user_done'); ?></h1>
    <h3><?php echo $this->lang->line('title_test_user_done_subline'); ?></h3>
    <div style="margin-top:10px;" ><?php echo $this->lang->line('desc_test_user_done'); ?></div>
    <div class="ctrl-buttons">
        <a href="javascript://" onclick="SignedUp();"><input class="button ok" type="submit" value="<?php echo $this->lang->line('button_test_user_done'); ?>"></a>
    </div>
</div>
<!-- 
<div id="inner_bg">
        <h3><?php echo $this->lang->line('title_test_user_done'); ?></h3>
    <p>
<?php echo $this->lang->line('desc_test_user_done'); ?>
    </p>
    <br />
    <a href="javascript://" onclick="SignedUp();"><input type="submit" value="<?php echo $this->lang->line('button_test_user_done'); ?>" class="create-my-account but-big" id="signin_button" name="signup"/></a>
</div>
-->
<script>
                    document.domain = '<?php echo $this->config->item('document_domain'); ?>';
                    function SignedUp()
                    {
                        if (window.top && window.top.TestUserRegistered)
                            window.top.TestUserRegistered();
                    }
</script>
