<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('notfound');

$tenant = $this->config->item('tenant');

if ($tenant == 'blacktri') {
    ?>
    <div id="title_bg">
        <div class="title-inner">
            <h2><?php echo $this->lang->line('notfound_head'); ?></h2>
        </div>
    </div>
    <div id="main_container">
        <div class="terms">
            <h3><?php echo $this->lang->line('notfound_head1'); ?></h3>
            <h4><a style="font-size:17px;" href="<?php echo $baseurl; ?>"><?php echo $this->lang->line('notfound_head2'); ?></a></h4>
        </div>
    </div>
    <?php
} else {
    ?>
    <h1>Object not found</h1>
    <h2>Error 404</h2>
    <?php
}
?>