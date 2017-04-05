<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Set TraceCode</h2>
<div class="clear"><br /></div>
<form method="get" action="<?php echo $baseurl; ?>admin/saveTraceCode">
    <input type="text" name="code" />
    <input type="submit" value="Save" />
    <div class="clear"></div>
</form>
