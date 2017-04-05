<?php
$baseurl = $this->config->item('base_url');
$baseurlssl = $this->config->item('base_ssl_url');
$imgurl = $this->config->item('image_url');
?>
<div id="main_container">
    <div class="whitebox" id="scrollToHere">

        <div class="welcome-container">
            <h2><?php printf($this->lang->line('error_hibernation_saluation'), $firstname); ?></h2>
            <p><?php echo $this->lang->line('error_hibernation_copy'); ?></p>
            <div class="get-started"><?php echo $this->lang->line('error_hibernation_action1'); ?>
                <a class="popup" href="<?php echo $this->lang->line('error_hibernation_mailto') . $clientid; ?>"><?php echo $this->lang->line('error_hibernation_action2'); ?></a>
            </div>
        </div>



    </div>
</div>