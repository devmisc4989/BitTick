<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$this->load->view("genericmailheader");
?>
<h1 style="font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:bold; color:#111;"><?php echo $this->lang->line('emails_confirmationmail_salutation') ?><?php echo ($firstname); ?>,</h1>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#555; line-height:22px;"><?php echo $this->lang->line('emails_newpw_text1') ?>
    <br><br><b><?php echo $newpassword; ?></b>
    <br><br><?php echo $this->lang->line('emails_newpw_text2') ?>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_confirmationmailtext2') ?></p>
<p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_validationmailfooter') ?><br />
    <?php
    $this->load->view("genericmailfooter");
    ?>
