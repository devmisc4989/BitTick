<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$validationurl = $baseurl . $purl[$lg]['confirm'] . md5($clientid);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $this->lang->line('emails_approval_subject') ?></title>
    </head>
    <body>
        <table width="750" border="0" cellspacing="0" cellpadding="0" style="background-color:#043c58; padding:15px;">
            <tr>
                <td style="padding-bottom:10px;"><a href="#"><img src="<?php echo $imgurl ?>logo_mail.jpg"; border="0" /></a></td>
            </tr>
            <tr>
                <td colspan="2" style="background-color:#fff; padding:10px 30px;"><h1 style="font-family:Arial, Helvetica, sans-serif; font-size:26px; font-weight:bold; color:#111;"><?php echo $this->lang->line('emails_confirmationmail_salutation') ?><?php echo ($name); ?>,</h1>
                    <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#555; line-height:22px;"><?php echo $this->lang->line('emails_approval_text1') ?></p>
                    <p style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:normal; color:#4a483f; line-height:22px;"><?php echo $this->lang->line('emails_confirmationmailfooter') ?><br />
                </td>
            </tr>
        </table>
    </body>
</html>