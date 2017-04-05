<?php
$this->lang->load('newsletter');
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
?>
<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('subc_verifiedtopline'); ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <h3><?php echo $this->lang->line('subc_verifiedheadline'); ?></h3>   
        <p><?php echo $this->lang->line('subc_verifiedcopy'); ?></p>
    </div>

</div>
