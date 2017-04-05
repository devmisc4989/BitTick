<?
header('Content-type: application/x-javascript');

$preview_cssurl = str_replace("http:", "", $this->config->item('base_url')) . "css/tt_preview.css";
$preview_scripturl = str_replace("http:", "", $this->config->item('base_url')) . "js/tt_preview.js";
$getScript = sprintf("$.ajaxSetup({cache:true});$.getScript('%s');",$preview_scripturl);

$dom_modification_code = json_encode(array(
    "[JS]" => $getScript,
));

?>
var head  = document.getElementsByTagName('head')[0];
var link  = document.createElement('link');
link.rel  = 'stylesheet';
link.type = 'text/css';
link.href = '<?= $preview_cssurl ?>';
link.media = 'all';
head.appendChild(link);

var injectionJson = '<? echo json_encode($previewData); ?>';
var collectionid = <?= $collectionid ?>;
var ttInterfaceType = '<?= $interfaceType ?>';
_bt.applyCollectionChanges(<?= $dom_modification_code ?>);
_bt.setReady(); 
// BTO TT preview