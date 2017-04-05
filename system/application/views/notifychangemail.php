<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
$validationurl = $baseurl . $purl[$lg]['confirm'] . md5($clientid);
$mydate = date($this->lang->line('dateformat'));
$mytime = date($this->lang->line('timeformat'));
$this->load->view("genericmailheader");
?>
<h1 style="font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:bold; color:#111;"><?php echo $this->lang->line('emails_confirmationmail_salutation') ?><?php echo ($name); ?>,</h1>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#555; line-height:22px;"><?php printf($this->lang->line('emails_changenotifytext1'), $mydate, $mytime) ?></p>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_validationmailfooter') ?><br />
    <?php
    $this->load->view("genericmailfooter");
    ?>
