<?php
$CI =& get_instance();
$tenant = $CI->config->item('tenant');


$lang['Perso popup title'] = "Segmentierungsregel auswählen und bearbeiten";
$lang['Perso popup introductory text'] = "Sie können Projekte für bestimmte Besuchergruppen ausspielen. Legen Sie hier die Form der Segmentierung fest.";
if($tenant=='etracker')	
	$lang['Perso popup introductory text'] = "Sie können Smart Messages und Tests für bestimmte Besuchergruppen ausspielen. Legen Sie hier die Form der Segmentierung fest.";
$lang['Perso rule list title'] = "Segmentierungsregeln";
$lang['Perso not enabled title'] = "Personalization (not enabled)";
$lang['Perso not enabled'] = "Your account is not entitled to use personalization. In order to access personalization please contact your account manager or use our online form here:";
$lang['Perso enable now'] = "Jetzt Upgrade durchführen und Varianten segmentieren";
$lang['Perso enable perso type'] = "Bitte wenden Sie sich an unseren Support, um diesen Bedingungstyp freizuschalten.";
$lang['Perso nav title'] = "Segmentierung festlegen";
$lang['Perso edit campaign'] = "Sie können Projekte für bestimmte Besuchergruppen ausspielen. Legen Sie hier die Form der Segmentierung fest. 
	Wenn Sie einen A/B-Test für bestimmte Besuchergruppe ausspielen wollen, wählen sie z. B. die dritte Option, wenn Sie einzelne Varianten an bestimmte Besuchergruppen ausspielen wollen, die zweite Option. Über das Plus-Symbol können Sie dann eine Segmentierungsregel festlegen.";
if($tenant=='etracker')	
	$lang['Perso edit campaign'] = "Sie können Smart Messages und Tests für bestimmte Besuchergruppen ausspielen. Legen Sie hier die Form der Segmentierung fest. 
	Wenn Sie alle Projekte für die gleiche Besuchergruppe ausspielen wollen, wählen sie z. B. die dritte Option, wenn Sie eine genauere Differenzierung 
	wünschen, die zweite Option. Über das Stift-Symbol können Sie dann eine Segmentierungsregel festlegen.";
$lang['Perso new campaign'] = $lang['Perso edit campaign'];
$lang['Perso no personalization'] = "Ohne Segmentierung";
$lang['Perso complete test'] = "A/B-Test für Besuchersegment ausspielen";
$lang['Perso single variant'] = "Einzelne Varianten segmentiert ausspielen ";
if($tenant=='etracker')	{
	$lang['Perso complete test'] = "Alle Varianten segmentieren";
	$lang['Perso single variant'] = "Varianten einzeln segmentieren";	
}
/***/
$lang['Perso noperso intro'] = "Die Varianten dieses Projektes werden an alle Besucher geliefert.";
$lang['Perso complete intro'] = "Das Projektes wird als A/B-Test für eine definierte Besuchergruppe ausgespielt. 
	Klicken Sie auf das Plus-Symbol, um diese Besuchergruppe mit einer Segmentierungsregel zu definieren.";
$lang['Perso single intro'] = "Jede Variante des Projektes wird an eine bestimmte Besuchergruppe ausgespielt 
	werden. Klicken Sie auf das Plus-Symbol, um eine Besuchergruppe mit einer Segmentierungsregel zu definieren.";
$lang['Perso has sms'] = "Dies ist ein Smart Messaging - Projekt, die Originalseite wird nicht ausgespielt weil dies Ergebnisse ohne Aussagekraft erzeugen würde.";
/***/
$lang['Perso table title'] = "Segmentierungsregel";
$lang['Perso unpersonalized'] = "(nicht personalisiert)";
/***/
$lang['Perso error title'] = "Please fix the following error(s)";
$lang['Perso error selectrule'] = "Create or select a rule from the list";
$lang['Perso error rulename'] = "Enter a name for the test";
$lang['Perso error nocondition'] = "You have to add at least one condition for every rule";
$lang['Perso error condition'] = "Select a value for every condition in the list";
$lang['Perso error parameter'] = "Select or enter a valid parameter for every condition";
/***/
$lang['Perso del condition'] = "Bedingung löschen";
$lang['Perso confirm del rule'] = "Wollen Sie die Segmentierungsregel wirklich löschen? Sie kann nicht wiederhergestellt werden.";
$lang['Perso button delete rule'] = "Segmentierungsregel löschen";
$lang['Perso confirm cancel title'] = "Änderungen gehen verloren";
$lang['Perso confirm cancel'] = "Sie haben Änderungen vorgenommen, wollen Sie wirklich ohne zu speichern abbrechen";
/***/
$lang['Perso rule label'] = "Regel";
$lang['Perso add rule'] = "Regel hinzufügen";
$lang['Perso del rule'] = "Ausgewählte Regel entfernen";
$lang['Perso select rule'] = "Regel festlegen";
$lang['Perso not defined'] = "Sie haben noch keine Segmentierungsregel erstellt.";
$lang['Perso please start'] = "Bitte wählen Sie \"Regel hinzufügen\" und definieren Sie eine Regel.";
$lang['Perso rule name'] = "Regelname";
$lang['Perso rule valid'] = "Regel anwenden, wenn Folgendes gilt:";
$lang['Perso and condition'] = "Alle Bedingungen treffen zu";
$lang['Perso title condition'] = "Bedingungen";
$lang['Perso add condition'] = "Bedingung hinzufügen";
$lang['Perso or condition'] = "Mindestens eine der Bedingungen trifft zu";
/***/
$lang['Perso equal'] = "ist gleich";
$lang['Perso not equal'] = "ist ungleich";
$lang['Perso greater'] = "ist größer als";
$lang['Perso less'] = "ist kleiner als";
$lang['Perso to'] = "ist früher als";
$lang['Perso from'] = "ist später als";

$lang['Perso day singular'] = "Tag";
$lang['Perso day plural'] = "Tage";

$lang['Perso option select'] = "Bitte wählen...";
$lang['Perso option select tt'] = "Bitte wählen Sie mindestens eine Bedingung aus.";
$lang['Perso option select country'] = "Land wählen...";
$lang['Perso option select state'] = "Staat/Bundesland wählen...";
$lang['Perso option select region'] = "Region wählen...";
$lang['Perso option select city'] = "Ort wählen...";


$lang['Perso origin'] = "Herkunft";
$lang['Perso purchase behavior'] = "Kaufverhalten";
$lang['Perso user profile'] = "Besucherprofil";
$lang['Perso visit behavior'] = "Verhalten";
$lang['Perso technology'] = "Technik";
$lang['Perso location'] = "Geographischer Standort";

/***/
$lang['Perso querystring is'] = "URL enthält Zeichenkette";
$lang['Perso querystring is tt'] = "Segmentierung nach eingesetzten URL-Parametern";
$lang['Perso url contains'] = "URL enthält Zeichenkette";
$lang['Perso url contains tt'] = "Segmentierung nach eingesetzten URL-Parametern";
$lang['Perso referer is'] = "Herkunft/Pfad enthält";
$lang['Perso referer is tt'] = "Segmentierung nach Herkunftsdomain";
$lang['Perso trafficsource is'] = "Medium";
$lang['Perso trafficsource is tt'] = "Segmentierung nach Werbekanälen";
$lang['Perso source typein'] = "Type-In";
$lang['Perso source social'] = "Social Media";
$lang['Perso source organic'] = "SEO";
$lang['Perso source paid'] = "SEA";
$lang['Perso search is'] = "Suchbegriff ";
$lang['Perso search is tt'] = "Segmentierung nach eingegebenem Suchbegriff";
$lang['Perso time lastorder'] = "Zeit seit letzter Bestellung";
$lang['Perso time lastorder tt'] = "Segmentierung nach Zeitintervallen zwischen Bestellungen.";
$lang['Perso avg sales'] = "Durchschnittlicher Bestellwert ";
$lang['Perso avg sales tt'] = "Segmentierung nach der Höhe durchschnittlicher Bestellwerte in €";
$lang['Perso visitor isclient'] = "Besucher ist Kunde";
$lang['Perso visitor isclient tt'] = "Segmentierung nach Kunden und Nicht-Kunden";
$lang['Perso purchaser type'] = "Käufertyp";
$lang['Perso purchaser type tt'] = "Segmentierung entsprechend der ABC-Analyse:<br>
A = Sehr wichtiger Kunde<br>
B = Wichtiger Kunde<br>
C = Unwichtiger Kunde";
$lang['Perso visitor newsletter'] = "Besucher ist Newsletter-Abonnent";
$lang['Perso visitor newsletter tt'] = "Segmentierung nach Abonnenten und Nicht-Abonnenten";
$lang['Perso visit count'] = "Besuchshäufigkeit ";
$lang['Perso visit count tt'] = "Segmentierung nach der Anzahl der bisherigen Besuche:<br>
Niedrig = 2<br>
Mittel = 3-4<br>
Hoch = 5 und mehr";
$lang['Perso time visits'] = "Zeit seit letztem Besuch";
$lang['Perso time visits tt'] = "Segmentierung nach Zeitintervallen zwischen Besuchen";
$lang['Perso visitor returning'] = "Besucher ist wiederkehrender Besucher";
$lang['Perso visitor returning tt'] = "Segmentierung nach wiederkehrenden Besuchern und Erstbesuchern";
$lang['Perso targetpage opened'] = "Aufruf Zielseite";
$lang['Perso targetpage opened tt'] = "Segmentierung nach aufgerufender Zielseite. Sie können den * als Platzhalter verwenden.";
$lang['Perso goal insert'] = "etracker Ziel 'Produkt im Warenkorb'";
$lang['Perso goal insert tt'] = "Segmentierung nach erreichtem Website-Ziel";
$lang['Perso minimum session'] = "Mindestdauer der Session (Sekunden)";
$lang['Perso minimum session tt'] = "Segmentierung nach Mindestdauer der Session";
$lang['Perso device is'] = "Gerätetyp";
$lang['Perso device is tt'] = "Segmentierung nach Gerätetypen, mit denen auf die Webseite zugegriffen wird.";
$lang['Perso device tablet'] = "Tablet";
$lang['Perso device desktop'] = "Desktop";
$lang['Perso device mobile'] = "Handy";
$lang['Perso device other'] = "Andere";
$lang['Perso device os is'] = "Betriebssystem";
$lang['Perso device os is tt'] = "Segmentierung nach Betriebssystem des Gerätes, mit dem auf die Webseite zugegriffen wird.";
// GEO IP
$lang['Perso location is'] = "Standort ist";
$lang['Perso location is tt'] = "Segmentierung nach Land, Region und Stadt";
$lang['Perso is holiday'] = "Schulferien am Standort";
$lang['Perso is holiday tt'] = "Segmentierung nach Schulferien am Standort des Besuchers";

$lang['Perso wywy'] = "Wywy TV-Spot-Daten";
$lang['Perso wywy commercial aired'] = "Werbespot soeben ausgespielt";
$lang['Perso wywy commercial aired tt'] = "Geben Sie die Kunden-ID, Spot-ID, Kundenname oder Spot-Name ein.";




$lang['Perso browser is'] = "Browser is";
$lang['Perso browser is tt'] = "Browser is tooltip";
$lang['Perso city is'] = "City is";
$lang['Perso city is tt'] = "City is tooltip";
$lang['Perso cookie is'] = "Cookie has value";
$lang['Perso cookie is tt'] = "Cookie has value tooltip";
$lang['Perso country is'] = "Country is";
$lang['Perso country is tt'] = "Country is tooltip";
$lang['Perso custumer address'] = "Customer address";
$lang['Perso custumer address tt'] = "Customer address tooltip";
$lang['Perso et campaign'] = "Etracker campaign";
$lang['Perso et campaign tt'] = "Etracker campaign tooltip";
$lang['Perso goal sale'] = "Goal \"Sale\" reached";
$lang['Perso goal sale tt'] = "Goal \"Sale\" reached tooltip";
$lang['Perso goal seen'] = "Goal \"Product seen\" reached";
$lang['Perso goal seen tt'] = "Goal \"Product seen\" reached tooltip";
$lang['Perso minimum pages'] = "Minimum pages";
$lang['Perso minimum pages tt'] = "Minimum pages tooltip";
$lang['Perso os is'] = "OS is";
$lang['Perso os is tt'] = "OS is tooltip";
$lang['Perso page variable'] = "Page custom variable is";
$lang['Perso page variable tt'] = "Page custom variable is tooltip";
$lang['Perso sms followed'] = "Smart Message followed";
$lang['Perso sms followed tt'] = "Smart Message followed tooltip";
$lang['Perso visitor variable'] = "Visitor custom variable is";
$lang['Perso visitor variable tt'] = "Visitor custom variable is tooltip";
/****/
$lang['Perso yes'] = "Ja";
$lang['Perso no'] = "Nein";
$lang['Perso discard'] = "Ohne Speichern beenden";
$lang['Perso back'] = "Abbrechen";

// attributes for etracker RealTimeAPI
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01'] = "Keine Bestellung";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_02'] = "1-31 Tage";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_03'] = "31-90 Tage";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_04'] = "91 Tage – 12 Monate";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_05'] = "Mehr als 12 Monate";

$lang['STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_12'] = "Mehr als 4000";

$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_1'] = "Kein Kauf";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_2'] = "Nur ein Kauf";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_3'] = "C";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_4'] = "B";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_5'] = "A";

$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01'] = "Nur ein Besuch";
$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_02'] = "Niedrig";
$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_03'] = "Mittel";
$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_04'] = "Hoch";

$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_01'] = "Weniger als 1 Tag";
$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_02'] = "1-7 Tage";
$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_03'] = "7-30 Tage";
$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_04'] = "Mehr als 30 Tage";
