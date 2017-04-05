<?
// set output content type to Javascript
header('Content-type: application/x-javascript');
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

if($testtype == OPT_TESTTYPE_SPLIT) {
	if(!$visitor['no_redirection'])
    	echo "_bt.redirect('" . $lp_url . "');\n";
}
if($testtype == OPT_TESTTYPE_VISUALAB) {
	echo "_bt.applyCollectionChanges($dom_code);\n";
}
if($testtype == OPT_TESTTYPE_MULTIPAGE) {
	echo "_bt.applyCollectionChanges($dom_code);\n";
}

?>
_bt.removePreloader();
// BTO webservice preview