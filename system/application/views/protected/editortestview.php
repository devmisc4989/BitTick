<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
?>
<h3>We display the number of impressions of all pages in the collection here:</h3>
<h1>
    <?php
    echo $totalimpressions;
    ?>
</h1>