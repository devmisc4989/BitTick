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
<script type="text/javascript" src="<?php echo $baseurl ?>js/admin/multivariateTest.js"></script>

<input type="hidden" value="<?php echo $baseurl; ?>" id="base-url" />
<div class="clear"></div>

<div id="test-list"></div>

<div class="edit-dialogs" id="new-test-dialog" title="Multivariate Test Details">
    <p class="validateTips"></p>
    <form>
        <fieldset>
            <label for="c-code">Client Code:</label>
            <input type="text" id="c-code" class="text ui-widget-content ui-corner-all"/>
            <div class="clear"></div>

            <label for="lc-status">Status:</label>
            <select id="lc-status" class="text ui-widget-content ui-corner-all">
                <option value="1">Paused</option>
                <option value="2">Active</option>
            </select>
            <div class="clear"></div>

            <label for="lc-name">Test Name:</label>
            <input type="text" id="lc-name" value="" class="text ui-widget-content ui-corner-all"/>
            <div class="clear"></div>

            <label for="lp-url">Main Page URL:</label>
            <input type="text" id="lp-url" value="" class="text ui-widget-content ui-corner-all"/>
            <div class="clear"></div>

            <label for="lp-canonical">URL Pattern:</label>
            <input type="text" id="lp-canonical" value="" class="text ui-widget-content ui-corner-all"/>
            <div class="separator"></div>
        </fieldset>

        <div id="new-factors"></div>

        <a class="add-test-feature" id="add-factor"> + Add Factor</a>
        <div class="clear"></div>
    </form>
</div>