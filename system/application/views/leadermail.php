<?php
$baseurl = $this->config->item('base_url');
$imageurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
$this->load->view("genericmailheader");
$link_color = "#7ea515";
if ($this->config->item('tenant') == 'dvlight') {
    $link_color = "#5FC1FF";
}
?>
<h1 style="font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:bold; color:#111;"><?php echo $uplift . $this->lang->line('emails_leader_headline') . " " . $collectionname; ?></h1>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#555; line-height:22px;">
    <?php echo $this->lang->line('emails_salutation') . $clientname . "," ?><br>
    <?php echo $this->lang->line('emails_leader_copy1') ?><br>
    <?php echo $this->lang->line('emails_leader_copy2') . "<b>$controlcr</b>" ?><br>
    <?php echo $this->lang->line('emails_leader_copy3') . "<b>$leadercr</b> :" ?>
</p>
<a href="<?php echo $leaderurl; ?>" style="font-family:Arial, Helvetica, sans-serif; color:<?php echo $link_color ?>; text-decoration:underline;"><?php echo $leaderurl; ?></a>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#555; line-height:22px;"><?php echo $this->lang->line('emails_linktotest') ?></p>
<a href="<?php echo $testdetailurl; ?>" style="font-family:Arial, Helvetica, sans-serif; color:<?php echo $link_color ?>; text-decoration:underline;"><?php echo $testdetailurl; ?></a>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_validationmailtext2') ?></p>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_validationmailfooter') ?><br />
    <?php
    $this->load->view("genericmailfooter");
    ?>
