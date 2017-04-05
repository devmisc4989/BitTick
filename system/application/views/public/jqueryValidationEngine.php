<?php
// set output content type to Javascript
header('Content-type: application/x-javascript; charset=' . config_item('charset'));
//header('Expires: Thu, 15 Apr 2020 20:00:00 GMT');
$this->lang->load('jqueryValidationEngine');
?>
(function($) {
$.fn.validationEngineLanguage = function() {};
$.validationEngineLanguage = {
newLang: function() {
$.validationEngineLanguage.allRules = 	{
"required":{ 
"regex":"none",
"alertText":"<?php echo $this->lang->line('required_alertText'); ?>",
"alertTextCheckboxMultiple":"<?php echo $this->lang->line('required_alertTextCheckboxMultiple'); ?>",
"alertTextCheckboxe":"<?php echo $this->lang->line('required_alertTextCheckboxe'); ?>"},
"minSize": {
"regex": "none",
"alertText": "* Minimum ",
"alertText2": "<?php echo $this->lang->line('length_alertText3'); ?>"
},
"maxSize": {
"regex": "none",
"alertText": "* Maximum ",
"alertText2": "<?php echo $this->lang->line('length_alertText3'); ?>"
},
"length":{
"regex":"none",
"alertText":"<?php echo $this->lang->line('length_alertText'); ?>",
"alertText2":"<?php echo $this->lang->line('length_alertText2'); ?>",
"alertText3": "<?php echo $this->lang->line('length_alertText3'); ?>"},
"maxCheckbox":{
"regex":"none",
"alertText":"Checks allowed Exceeded"},	
"minCheckbox":{
"regex":"none",
"alertText":"<?php echo $this->lang->line('minCheckbox_alertText'); ?>",
"alertText2":""},	
"confirm":{
"regex":"none",
"alertText":"<?php echo $this->lang->line('confirm_alertText'); ?>"},		
"equals":{
"regex":"none",
"alertText":"<?php echo $this->lang->line('confirm_alertText'); ?>"},		
"telephone":{
"regex":/^[0-9\-\(\)\ ]+$/,
"alertText":"<?php echo $this->lang->line('telephone_alertText'); ?>"},	
"email":{
"regex": /^([A-Za-z0-9_\-\.\'])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/,
"alertText":"<?php echo $this->lang->line('email_alertText'); ?>"},	
"teaseremail":{
"regex": /^([A-Za-z0-9_\-\.\'])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/,
"alertText":"<?php echo $this->lang->line('teaseremail_alertText'); ?>"},	
"url": {
"regex": /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i,
"alertText":"<?php echo $this->lang->line('url_alertText'); ?>"},	
"urlsmall": {
"regex": /(^|\s)((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/gi,
"alertText":"<?php echo $this->lang->line('url_alertText'); ?>"},	
"date":{
"regex": /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/,
"alertText":"<?php echo $this->lang->line('date_alertText'); ?>"},
"onlyNumber":{
"regex":/^[0-9\ ]+$/,
"alertText":"<?php echo $this->lang->line('onlyNumber_alertText'); ?>"},	
"noSpecialCaracters":{
"regex":/^[0-9a-zA-Z]+$/,
"alertText":"<?php echo $this->lang->line('noSpecialCaracters_alertText'); ?>"},	
"ajaxUser":{
"url":path+"users/ca",
"alertTextOk":"<?php echo $this->lang->line('ajaxUser_alertTextOk'); ?>",	
"alertTextLoad":"<?php echo $this->lang->line('ajaxUser_alertTextLoad'); ?>",
"alertText":"<?php echo $this->lang->line('ajaxUser_alertText'); ?>"},	
"ajaxEmail":{
"url":path+"users/ce",
"alertTextOk":"<?php echo $this->lang->line('ajaxEmail_alertTextOk'); ?>",	
"alertTextLoad":"<?php echo $this->lang->line('ajaxEmail_alertTextLoad'); ?>",
"alertText":"<?php echo $this->lang->line('ajaxEmail_alertText'); ?>"},
"ajaxTestName":{
"url":path+"ue/cn",
"alertTextOk":"<?php echo $this->lang->line('ajaxTestName_alertTextOk'); ?>",	
"alertTextLoad":"<?php echo $this->lang->line('ajaxTestName_alertTextLoad'); ?>",
"alertText":"<?php echo $this->lang->line('ajaxTestName_alertText'); ?>"},	
"ajaxDomain":{
"url":path+"ue/domain",
"alertTextOk":"<?php echo $this->lang->line('ajaxDomain_alertTextOk'); ?>",	
"alertTextLoad":"<?php echo $this->lang->line('ajaxDomain_alertTextLoad'); ?>",
"alertText":"<?php echo $this->lang->line('ajaxDomain_alertText'); ?>"},
"onlyLetter":{
"regex":/^[a-zA-Z\ \']+$/,
"alertText":"<?php echo $this->lang->line('onlyLetter_alertText'); ?>"},
"validate2fields":{
"nname":"validate2fields",
"alertText":"<?php echo $this->lang->line('validate2fields_alertText'); ?>"},
"alphaNumSymb1":{
"regex":/^[a-zA-Z0-9&_\-]+$/,
"alertText":"<?php echo $this->lang->line('invalidCharsFound_alertText'); ?>"},
"alphaNumSymb2":{
"regex":/^[a-zA-Z0-9&_\*\.\?\/\:]+$/,
"alertText":"<?php echo $this->lang->line('invalidCharsFound_alertText'); ?>"},
"alphaNumBlank":{
"regex":/^[\ a-zA-Z0-9]+$/,
"alertText":"<?php echo $this->lang->line('invalidCharsFound_alertText'); ?>"},
"positiveReal":{
"regex":/^[0-9]+$/,
"alertText":"<?php echo $this->lang->line('positiveReal_alertText'); ?>"},
"queryString":{
"regex":/^[a-zA-Z0-9_\~\-\.]+=*[a-zA-Z0-9_\~\-\.\/]+$/,
"alertText":"<?php echo $this->lang->line('queryString_alertText'); ?>"},
"uniqueClickGoalName":{
"alertText":"<?php echo $this->lang->line('uniqueClickGoalsName_alertText'); ?>"},
"uniqueClickGoalSelector":{
"alertText":"<?php echo $this->lang->line('uniqueClickGoalsSelector_alertText'); ?>"},
}	

}
}
})(jQuery);

$(document).ready(function() {	
$.validationEngineLanguage.newLang()
});