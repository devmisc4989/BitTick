<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Configuration</h2>
<div class="clear"><br /></div>
<?
    if($config == 'error') {
        echo "An error occurred.";
    }
    else {
?>
<form method="post" action="<?php echo $baseurl; ?>admin/saveConfigurationValues">
    <textarea name="config" cols="80" rows="20"><?= $config ?></textarea>
    <input type="hidden" name="clientid" value="<?= $clientid ?>"/>
    <br><br>
    <input type="submit" value="Save" />
    <div class="clear"></div>
</form>
<?
    }
?>
