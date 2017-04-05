<?php
$CI =& get_instance();
$tenant = $CI->config->item('tenant');

$lang['welcome_salutation'] = "Hallo %s, vielen Dank dass Sie BlackTri Optimizer verwenden!";
$lang['welcome_getstarted'] = "So geht's los:";
$lang['welcome_getstartedlink'] = "Hier klicken und den ersten Test anlegen";
$lang['welcome_getstarted_headline_link'] = "Hier klicken und den ersten Headline-Test anlegen";
$lang['welcome_getstartedpara'] = "Oder Sie werfen zunächst einen Blick in die Dokumentation:";
$lang['welcome_videodoc_text'] = "(Einführungsvideo)";
$lang['welcome_videodoc_target'] = "https://blacktri.zendesk.com/";
$lang['welcome_userdoc_text'] = "(Benutzerhandbuch)";
$lang['welcome_userdoc_target'] = "/de/hilfe/";
$lang['welcome_9steps_text'] = "(Anleitung: Erste Schritte)";
$lang['welcome_9steps_target'] = "http://www.blacktri.com/blog/hilfe/anleitungen/wie-sie-in-9-schritten-ihr-web-projekt-optimieren/";

$lang['welcome_getstartedhead'] = "Es gibt viele Einsatzgebiete für BlackTri Optimizer";
$lang['welcome_getstartedheadpara'] = "Unten sehen Sie einige Beispiele, welche Arten von Websites Sie optimieren können.";

// image handling
$lang['welcome_thumbnailpara1'] = "Lorem ipsum dolor sit amet consectetur adipisicing elit,sed do eiusmod";
$lang['welcome_thumbnailpara2'] = "Lorem ipsum dolor sit amet, conse ctetur adipisicing elit, sed do eiusmod tempor incididunt";
$lang['welcome_thumbnailpara3'] = "Lorem ipsum dolor sit amet, conse ctetur adipisicing elit, sed do eiusmod tempor incididunt";

// help-container
$lang['welcome_helphead'] = "Und Sie können noch mehr machen:";
$lang['welcome_helplink1'] = "E-Commerce";
$lang['welcome_helppara1'] = "Verbessern Sie Produktseiten und den Checkout-Prozess Ihres Shops.";
$lang['welcome_helplink2'] = "Leadgenerierung";
$lang['welcome_helppara2'] = "Verringern Sie die Zahl der Absprünge bei Ihren Opt-In-Formularen.";
$lang['welcome_helplink3'] = "Affiliate-Links";
$lang['welcome_helppara3'] = "Mehr Klicks auf Affiliate-Links auf Ihrer Website.";

if($tenant == 'dvlight') {	
	$lang['welcome_salutation'] = "Hallo %s, vielen Dank dass Sie DIVOLUTION A/B Tester verwenden!";
	$lang['welcome_userdoc_target'] = "http://abtester.divolution.com/help/#userguide";
	$lang['welcome_9steps_target'] = "http://abtester.divolution.com/help/";
}
