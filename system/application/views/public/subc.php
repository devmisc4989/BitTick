<?php
$this->lang->load('newsletter');
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
?>
<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('subc_confirmtopline'); ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <h3><?php echo $this->lang->line('subc_confirmheadline'); ?></h3>   
        <p><?php echo $this->lang->line('subc_confirmcopy'); ?></p>
    </div>

</div>
