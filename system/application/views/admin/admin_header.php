<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>
<html>
    <head>
        <link href="<?php echo $baseurl; ?>css/administration.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <header>
            <h1>Admin</h1>
            <a href="<?php echo $baseurl; ?>admin/traceLog/">Trace Log</a>
            <span>|</span>
            <a href="<?php echo $baseurl; ?>admin/createTraceCode/">New Trace Code</a>
            <span>|</span>
            <a href="<?php echo $baseurl; ?>admin/multiVariateTest/">Multivariate Test</a>
            <span>|</span>
            <a href="<?php echo $baseurl; ?>admin/createTeaserTest/">New Teaser Test</a>
            <span>|</span>
            <a href="<?php echo $baseurl; ?>admin/urlFilter/">URL Filter</a>
            <span>|</span>
            <a href="<?php echo $baseurl; ?>admin/configuration/">Configuration</a>
            <span>|</span>
            <a href="<?php echo $baseurl; ?>admin/clientDatabase/">New Client Database</a>
            <br><br>
        </header>

        <div id="wrapper">
