<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$validationurl = $baseurl . $purl[$lg]['confirm'] . md5($clientid);

$logo = $imgurl . "logo_mail.jpg";
$header_bgcolor = "#00355d";
if ($this->config->item('tenant') == 'dvlight') {
    $logo = $imgurl . "logo_divolution.png";
    $header_bgcolor = "#373738";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo $this->lang->line('emails_validationmailsubject') ?></title>
    </head>
    <body>
        <table width="750" border="0" cellspacing="0" cellpadding="0" style="background-color:<?php echo $header_bgcolor ?>; padding:0px;">
            <tr>
                <td style="padding:10px;"><img src="<?php echo $logo ?>"; border="0" /></td>
            </tr>
            <tr>
                <td colspan="2" style="background-color:#fff; padding:5px 10px;">
