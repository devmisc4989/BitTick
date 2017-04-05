<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>
<link href="<?php echo $baseurl ?>js/jtable/themes/lightcolor/blue/jtable.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="<?php echo $baseurl ?>js/jtable/css/jquery-ui.custom.css" />

<script type="text/javascript" src="<?php echo $baseurl ?>js/jtable/jquery.js"></script>
<script type="text/javascript" src="<?php echo $baseurl ?>js/jtable/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $baseurl ?>js/jtable/jquery.jtable.js"></script>
<script type="text/javascript" src="<?php echo $baseurl ?>js/jtable/jq-json.js"></script>
<script type="text/javascript" src="<?php echo $baseurl ?>js/admin/urlFilter.js"></script>

<input type="hidden" value="<?php echo $baseurl; ?>" id="base-url" />
<div class="clear"></div>

<div id="url-list"></div>