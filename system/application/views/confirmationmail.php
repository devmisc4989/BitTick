<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$validationurl = $baseurl . $purl[$lg]['confirm'] . md5($clientid);
$this->load->view("genericmailheader");
$link_color = "#7ea515";
if ($this->config->item('tenant') == 'dvlight') {
    $link_color = "#5FC1FF";
}
?>
<h1 style="font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:bold; color:#111;"><?php echo $this->lang->line('emails_confirmationmail_salutation') ?><?php echo ($name); ?>,</h1>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#555; line-height:22px;"><?php echo $this->lang->line('emails_confirmationmailtext1') ?></p>
<a href="<?php echo $validationurl ?>" style="font-family:Arial, Helvetica, sans-serif; color:<?php echo $link_color ?>; text-decoration:underline;"><?php echo $validationurl ?></a>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_confirmationmailtext2') ?></p>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_confirmationmailfooter') ?><br />
    <?php
    $this->load->view("genericmailfooter");
    ?>
