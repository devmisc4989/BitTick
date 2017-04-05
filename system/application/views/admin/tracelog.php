<?php
$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'base_url' : 'base_ssl_url';
$baseurl = $this->config->item($protocol);
?>

<h2>Tracelog</h2>
<div class="clear"><br /></div>
<form method="get" action="<?php echo $baseurl; ?>admin/traceLog">
    <input type="text" name="code" value="<?php echo $tracecode; ?>" />
    <input type="submit" value="Refresh" />
    <div class="clear"></div>
</form>

<?php
$count = 0;
if (isset($log))
    $count = count($log);

echo "<b class=\"entries\">" . $count . " log entries</b><br><br>";
?>

<table border="1">
    <tr>
        <th>Log-ID</th><th>Timestamp</th><th>Message</th>
    </tr>
    <?php
    foreach ($log as $row) {
        echo "<tr>";
        echo "<td>" . $row['loggingid'] . "</td><td nowrap>" . $row['timestamp'] . "</td><td>" . str_replace("\n", "<br>", $row['message']) . "</td>";
        echo "</tr>";
    }
    ?>
</table>
<div class="clear"><br /></div>
