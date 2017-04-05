<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Client specific Database</h2>
<div class="clear"><br /></div>
Select Client ID to create DB for.
<form method="post" action="<?php echo $baseurl; ?>admin/createClientDatabase">
    <input type="text" name="clientid" />
    <input type="submit" value="Start" />
    <div class="clear"></div>
</form>
