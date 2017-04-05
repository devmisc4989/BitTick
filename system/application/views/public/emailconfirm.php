<?php
$this->lang->load('emails');
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
?>
<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('emails_emailconfirmtopline'); ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <h3><?php echo $this->lang->line('emails_emailconfirmheadline'); ?></h3>   
        <p><?php echo $this->lang->line('emails_emailconfirmcopy'); ?></p>
    </div>

    <?php if ($type == 'confirm') { ?>
        <a href="<?php echo $basesslurl . $purl[$lg]['login'] ?>" class="see-plans"><?php echo $this->lang->line('emails_emailconfirmbuttonheadline'); ?><span><?php echo $this->lang->line('emails_emailconfirmbuttonsubline'); ?></span></a>
            <?php } ?>
</div>
