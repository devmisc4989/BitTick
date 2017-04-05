<?php
$canonicalUrls = json_decode($projectDetails->runpattern,true);
if(!$canonicalUrls) {
    $canonicalUrls = array(
        array('mode' => 'include','url' => $projectDetails->runpattern)
    );
}
$includeUrls = array();
$excludeUrls = array();
foreach($canonicalUrls as $url) {
    if($url['mode']=='include')
        $includeUrls[] = "'" . $url['url'] . "'";
    else
        $excludeUrls[] = "'" . $url['url'] . "'";
}
$includeUrlsString = implode(",",$includeUrls);
$excludeUrlsString = implode(",",$excludeUrls);

?>
var mytest = {
    name : '<?= $projectDetails->name ?>',
    id : <?= $projectDetails->id ?>,
    type : '<?= $projectDetails->type ?>',
    delivery : {
        include_urls : [<?= $includeUrlsString ?>],
        exclude_urls : [<?= $excludeUrlsString ?>]
    },
    variants : [
<?php
    // last entry in array may have no trailing comma, so derive wether we need it or not from the
    // number of variants
    $numVariants = sizeof($variantDetails)-1; 

    $variantCount = 0;
    foreach($variantDetails as $variant) {
        if($variant->type != 'CONTROL') {
            $jsinjection = str_replace('\\"','\\\\"', $variant->jsinjection);
            $jsinjection = preg_replace( "/\r|\n/", "\\\n", $jsinjection );
            $cssinjection = $variant->cssinjection;
            $cssinjection = preg_replace( "/\r|\n/", "\\\n", $cssinjection );
?>
        {
            id : <?= $variant->id ?>,
            name : '<?= $variant->name ?>',
            jsinjection : '<?= $jsinjection ?>',
            cssinjection : '<?= $cssinjection ?>'
        }<?php
            $variantCount++;
            if($variantCount<$numVariants)
                echo ",";
            echo "\n";
        }
    }
?>
    ]
    
};
_bt_testdef.tests.push(mytest);