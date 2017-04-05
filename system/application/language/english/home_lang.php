<?php
$CI =& get_instance();
$website_phase = $CI->config->item('website_phase');

// home.php
$lang['home_heading'] = "Ist Ihre Konkurrenz dank <span>A/B-Testing</span> erfolgreicher als Sie?";
$lang['home_headdescription'] = "BlackTri Optimizer ist so einfach, dass damit jeder sein Web-Projekt selber optimieren kann.";
if($website_phase == 'LAUNCH') {
	$lang['home_calltoaction_headline'] = "Jetzt testen!";
	$lang['home_calltoaction_subline'] = "30 Tage kostenlos ohne Risiko ausprobieren";
}
else {
	$lang['home_calltoaction_headline'] = "Ja, ich will auch A/B-Testen!";
	$lang['home_calltoaction_subline'] = "Hier klicken und kostenlos registrieren";
}

$lang['home_slider_image1'] = "launchpage_A.jpg";
$lang['home_slider_caption1'] = "Welche Landingpage bringt die <span>meisten Registrierungen?</span>";
$lang['home_slider_image2'] = "launchpage_B.jpg";
$lang['home_slider_caption2'] = "Welche Landingpage bringt die <span>meisten Registrierungen?</span>";
$lang['home_slider_image3'] = "shoppingcart_A.jpg";
$lang['home_slider_caption3'] = "Welche Änderung bringt <span>mehr Sales in Ihrem Web-Shop?</span>";
$lang['home_slider_image4'] = "shoppingcart_B.jpg";
$lang['home_slider_caption4'] = "Welche Änderung bringt <span>mehr Sales in Ihrem Web-Shop?</span>";
$lang['home_slider_image5'] = "adsense_A.jpg";
$lang['home_slider_caption5'] = "Laufen <span>Text- besser als Image-Anzeigen</span> in Google AdSense?";
$lang['home_slider_image6'] = "adsense_B.jpg";
$lang['home_slider_caption6'] = "Laufen <span>Text- besser als Image-Anzeigen</span> in Google AdSense?";

$lang['home_logos'] = "Diese Unternehmen setzen BlackTri Optimizer ein";

$lang['home_testimonials'] = "Deshalb setzen unsere Kunden BlackTri Optimizer ein:";
$lang['home_testimonial_copy1'] = "<i>\"BlackTri hat uns durch die einfache Handhabung und unkomplizierte Implementierung überzeugt. 
	Ein A/B-Test ist damit schnell erstellt. Mit jedem Test verbessern wir die Conversionrate. 
	Zum Glück gibt es BlackTri !\"</i><br><br><strong>Thomas Hönscheid, Teamleiter Direct Distribution, OnVista Bank GmbH</strong>";
$lang['home_testimonial_image1'] = "home/thomas_hoenscheid_small.jpg";
$lang['home_testimonial_copy2'] = "<i>\"So schnell und einfach zu so eindeutigen Ergebnissen - sehr empfehlenswert! 
	Mit BlackTri konnten wir die Facebook-Seite eines Kunden so optimieren, dass sie fast 200 Prozent mehr Fans 
	generiert hat.\"</i><br><br><strong>Christoph Mecke, Managing Partner, LIQUID CAMPAIGN</strong>";
$lang['home_testimonial_image2'] = "home/christoph_mecke_small.jpg";

// teaser
$lang['home_teasericon1'] = "icons_1.jpg";
$lang['home_teaserhead1'] = "Alle Ergebnisse in Echtzeit";
$lang['home_teaser1'] = "Jede Konversion eines Besuchers ist sofort im Dashboard zu sehen, Sie werden per Email über wichtige Änderungen informiert, die erfolgreichste Variante wird per Autopilot gefunden und aktiviert. So behalten Sie den Überblick ohne sich täglich einwählen zu müssen.";
$lang['home_teasericon2'] = "icons_2.jpg";
$lang['home_teaserhead2'] = "Was bringt mehr – Adsense oder Affiliate-Anzeigen?";
$lang['home_teaser2'] = "BlackTri Optimizer ist die einzige Software, mit der Sie herausfinden können, ob Ihre Google Adsense- und Affiliate-Anzeigen optimal plaziert sind, und welcher Anzeigentyp die meisten Klicks generiert.";
$lang['home_teasericon3'] = "icons_3.jpg";
$lang['home_teaserhead3'] = "Made in Germany";
$lang['home_teaser3'] = "BlackTri Optimizer speichert Daten auf Servern in Deutschland, bietet Ihnen deutschsprachige Dokumentation und eine deutschsprachige Benutzeroberfläche.";
$lang['home_teasericon4'] = "icons_4.jpg";
$lang['home_teaserhead4'] = "Optimiert jedes Web-Projekt zu einem unschlagbarem Preis!";
$lang['home_teaser4'] = "Keine andere A/B-Testing-Software ist bei so geringem Preis so vielseitig: BlackTri Optimizer steigert den Erfolg von Landingpages und Facebook Fan- Gates, verbessert die Absprungrate im Sales-Prozess Ihres Shops, findet die beste Monetarisierung Ihrer Nischen- Website.";
$lang['home_teasericon5'] = "icons_5.jpg";
$lang['home_teaserhead5'] = "Einfache und intuitive Benutzung";
$lang['home_teaser5'] = "Mit dem visuellen Editor von BlackTri Optimizer erstellen Sie Tests ohne zu programmieren. Sie müssen nichts konfigurieren und kein Statistik- Experte sein, die Bedienung ist kinderleicht und intuitiv. So sparen Sie Kosten weil Sie endlich selber optimieren können.";
$lang['home_teasericon6'] = "icons_6.jpg";
$lang['home_teaserhead6'] = "Optimieren Sie Ihr Webprojekt mit A/B-Testing";
$lang['home_teaser6'] = "BlackTri Optimizer ist eine web-basierte Software, mit der Sie die Konversion Ihrer Website durch A/B-Testing massiv verbessern können. BlackTri Optimizer spielt unterschiedliche Varianten einer Seite aus und findet für Sie die Variante mit dem größten Erfolg. So holen Sie aus Ihrem teuer bezahlten Traffic mehr Profit heraus!";

// bottom container
if($website_phase == 'LAUNCH') {
	$lang['home_container1'] = "Unschlagbarer Preis";
	$lang['home_containerdescription1'] = "Tarife ab 9,-€ pro Monat. Ein Monat Kündigungsfrist, Zahlung auf Rechnung. <br>30 Tage kostenlose Testphase.";
}
else {
	$lang['home_container1'] = "A/B-Testing für alle";
	$lang['home_containerdescription1'] = "BlackTri Optimizer ist die einfachste und vielseitigste A/B-Testing-Software auf dem Markt!";
}
$lang['home_container2'] = "Sie können sofort loslegen ";
$lang['home_containerdescription2'] = "BlackTri Optimizer enthält nur Funktionen, die Sie wirklich brauchen, für alle Aufgaben erhalten Sie Anleitungen und Hilfetexte";
$lang['home_container3'] = "Tests in Minuten erstellen";
$lang['home_containerdescription3'] = "Mit dem visuellen Editor editieren Sie Headlines, Texte und Bilder direkt in der Seite";


/* End of file number_lang.php */
/* Location: ./system/language/english/number_lang.php */
