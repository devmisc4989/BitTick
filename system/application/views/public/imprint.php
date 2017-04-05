<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('imprint');
?>
<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('imprint_head'); ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <?php echo $this->lang->line('imprint_copy'); ?>
        <br>
        <?php echo $this->lang->line('imprint_address'); ?>
    </div>
</div>
