<?php
$tenant = $this->config->item('tenant');

header('Content-Type:text/plain');

if ($tenant == 'dvlight') { // 
    ?>
    User-agent: *
    Disallow: /
    <?php
} elseif ($tenant == 'etracker') {
    ?>
    User-agent: *
    Disallow: /
    <?php
} else { // default definition for BlackTri
    ?>
    User-agent: *
    Allow: /
    <?php
}
?>



