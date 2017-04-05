<?php
$this->lang->load('emails');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('demo');
?>
<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('demo head'); ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <h3><?php echo $this->lang->line('demo confirm subline'); ?></h3>   
        <p><?php echo $this->lang->line('demo confirm copy'); ?></p>
    </div>
</div>
