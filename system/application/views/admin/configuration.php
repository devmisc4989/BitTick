<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Configuration</h2>
<div class="clear"><br /></div>
Select Client ID to configure.
<form method="get" action="<?php echo $baseurl; ?>admin/configurationValues">
    <input type="text" name="clientid" />
    <input type="hidden" name="mode" value="show" />
    <input type="submit" value="Start" />
    <div class="clear"></div>
</form>
