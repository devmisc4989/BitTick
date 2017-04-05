<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Log in for etracker client</h2>
<div class="clear"><br /></div>
Enter etracker account ID
<form method="get" action="<?php echo $baseurl; ?>admin/doEtrackerLogin" target="_blank">
    <input type="text" name="accountid" />
    <input type="submit" value="Send" />
    <div class="clear"></div>
</form>
