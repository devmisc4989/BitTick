<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Create empty Teaser Test</h2>
<div class="clear"><br /></div>
<form method="get" action="<?php echo $baseurl; ?>admin/doCreateTeaserTest">
Client ID:<br>
    <input type="text" name="clientid" />
<br><br>Main URL:<br>
    <input type="text" name="mainurl" />
<br><br>URL Pattern:<br>
    <input type="text" name="runpattern" />
<br><br>Name:<br>
    <input type="text" name="name" />
    <input type="submit" value="Create" />
    <div class="clear"></div>
</form>
