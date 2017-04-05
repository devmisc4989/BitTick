<?php
$CI =& get_instance();
$tenant = $CI->config->item('tenant');

// wizard screens
//general
$lang['One step back'] = "Ein Schritt zurück";
$lang['Help'] = "Hilfe";
//screen 1
$lang['Create a test'] = "Projekt erstellen";
$lang['Create a test description'] = "Wählen Sie hier aus, ob Sie einen Visuellen A/B-Test oder einen Split-URL-Test anlegen wollen.";
if($tenant=='etracker')
	$lang['Create a test description'] = "Wählen Sie hier aus, ob Sie einen Visuellen A/B-Test, einen Split-URL-Test oder eine Smart Message anlegen wollen.";
$lang['New A/B test'] = "Neuer Split-URL-Test";
$lang['New A/B test description'] = "Findet unter mehreren HTML-Seiten oder Templates für eine Seite die erfolgreichste. Er ist gut geeignet wenn für Varianten 
serverseitige Änderungen erforderlich sind, oder falls der Visuelle Editor eine Seite nicht laden kann.";
$lang['Create A/B Test now!'] = "Split-URL-Test erstellen";

$lang['Visual A/B test'] = "Neuer Visueller A/B-Test";
$lang['Visual A/B test description'] = "Ermöglicht die unkomplizierte Definition von Varianten für Website-Texte, -Bilder oder -Anzeigen mit Hilfe des Visuellen Editors. 
Er ist außerdem geeignet, um per CSS oder Javascript Varianten einer Seite anzulegen.";
$lang['Create Visual A/B Test now!'] = "Visuellen A/B-Test erstellen";

$lang['New Multipage test'] = "Neuer Mehrseitiger Test";
$lang['Multipage test description'] = "Beziehen Sie mehr als eine Seite in einen Test ein (\"Funnel-Test\"), etwa um Registrierungs- oder Checkout-Prozesse zu optimieren.";
$lang['Create Multipage Test now!'] = "Mehrseitigen Test erstellen";
$lang['Multipage page index'] = 'Seite ';
$lang['Edit multipage test'] = 'Bearbeiten';
$lang['multipage popup title'] = 'Mehrseitigen Test bearbeiten';
$lang['multipage popup intro'] = 'Verwalten Sie hier die Seiten ihres mehrseitigen Tests.';
$lang['multipage popup add'] = 'Seite hinzufügen';
$lang['multipage popup delete'] = 'Löschen';
$lang['multipage popup save'] = 'Speichern';
$lang['multipage delete title'] = 'Wollen Sie die Seite(n) wirklich löschen?';
$lang['multipage delete info'] = 'Dies kann nicht rückgängig gemacht werden.';
$lang['Mpt pageurl title'] = "Geben Sie Name und URL der neuen Seite an.";
$lang['Mpt name example'] = "Beispiel: http://www.mylandingpage.com";
$lang['Mpt enter name'] = "Geben Sie den Namen der neuen Seite an.";
$lang['Mpt name example'] = "Example: Home page or Shopping basket page";
$lang['Mpt name error'] = "Dieser Name wird bereits für eine andere Seite verwendet";
$lang['Mpt url error'] = "Dieser URL wird bereits für eine andere Seite verwendet";
$lang['Url for MPT title'] = "URLs des mehrseitigen Tests";
$lang['Url for MPT intro'] = "Geben Sie für jede Seite des Tests an, für welche URLs er ausgespielt werden soll. Sie können das * Zeichen als Platzhalter verwenden.";

$lang['New Teaser test'] = "Neuer Teaser-Test";
$lang['Teaser test description'] = "Legen Sie mit wenigen Klicks A/B-Tests für die Headlines Ihrer Medien-Website and, und verbessern dadurch Klickrate, Verweildauer und Zahl der Page-Impressions Ihrer Artikel.";
$lang['Create Teaser Test now!'] = "Teaser-Test erstellen";

$lang['New sms test'] = "Neue Smart Message";
$lang['New sms description'] = "Ermöglicht Ihnen, bestimmte Besuchergruppen auf Ihrer Webseite anzusprechen. 
Sie können Ihren Besuchern z. B. einen Anreiz bieten, die Webseite nicht zu verlassen.";
$lang['Create sms now!'] = "Smart Message erstellen";


//visual test substep 1
//$lang['Create Visual Test (Step 1 of 3)'] = "Visuellen A/B-Test erstellen (Schritt 1 von 3)";
$lang['Enter URL of your page'] = "Geben Sie hier die URL der Originalseite ein, die Sie optimieren wollen.";
$lang['Enter URL of your page description'] = "Sie können dazu die Seite im Browser öffnen und die URL aus der Adresszeile kopieren.";
$lang['Link Example'] = "Beispiel: http://www.mylandingpage.com oder http://www.mylandingpage.com/article.jsp?id=4711";
$lang['Proceed to Editor'] = "Weiter und Seite jetzt öffnen";

//visual test substep 2
//$lang['Create Visual Test (Step 2 of 3)'] = "Visuellen A/B-Test erstellen (Schritt 2 von 3)";
$lang['Loading page...'] = "Seite wird geladen...";
$lang['Loading page... description'] = "Bitte einen Moment Geduld, wir laden die Seite und bereiten sie zum Bearbeiten auf.";
$lang['You can edit the page now'] = "Sie können nun Varianten für Ihr Projekt erstellen.";
$lang['You can edit the page now description'] = "Verändern Sie einzelne Elemente für Tests. Zum Erzeugen von Testvarianten bewegen Sie die Maus über Bereiche, die Sie variieren wollen. 
Mit einem Mausklick darauf öffnen Sie ein Menü von dem aus Sie das Element bearbeiten können.";
if($tenant=='etracker')
	$lang['You can edit the page now description'] = "Verändern Sie einzelne Elemente für Tests oder bearbeiten Sie Smart Messages. 
	Zum Erzeugen von Testvarianten bewegen Sie die Maus über Bereiche, die Sie variieren wollen. 
	Mit einem Mausklick darauf öffnen Sie ein Menü von dem aus Sie das Element bearbeiten können.";

$lang['Do not show this message'] = "Diesen Hinweis nicht mehr anzeigen";
$lang['close and proceed editing'] = "Dialog schließen und Seite jetzt bearbeiten";

$lang['Proceed'] = "Weiter";
$lang['Start Test'] = "Test jetzt starten";

$lang['editor_mode_browse'] = "Navigieren";
$lang['editor_mode_edit'] = "Bearbeiten";

//$lang['Too many page variants'] = "Zu viele Varianten";
//$lang['You should delete some variants or factor'] = "Sie sollten Testelemente entfernen damit der Test nicht zu lange dauert.";
//$lang['Combination']="Kombination";
//$lang['Combinations']="Kombinationen";

//visual test substep 3
$lang['Create Visual Test (Step 3 of 3)'] = "Visuellen A/B-Test erstellen (Schritt 3 von 3)";
$lang['Choose a name for your test'] = "Projektname";

//$lang["Choose the conversion goal"] = "Wählen Sie die Art der Konversion aus";
//$lang["Choose the conversion goal description"] = "Welche Useraktion soll als Erfolg bzw. Konversion gewertet werden?";
//$lang["User opens success page"] = "Aufruf einer Bestätigungs- bzw. \"Danke\"-Seite";
//$lang["User clicks Google Adsense Ad"] = "Event-Tracking, d.h. Klick auf eine Google Adsense-Anzeige, einen Link, etc.";
//$lang['Enter URL of your success page'] = "Geben Sie hier die URL der Bestätigungs- bzw. \"Danke\"-Seite ein";
//$lang['Enter URL of your success page description'] = "Das ist die Seite, die nach erfolgter Konversion aufgerufen wird, 
//also bspw. die \"Danke\"-Seite nach einer Bestellung.";
//$lang['Success Link Example'] = "Beispiel: http://www.mylandingpage.com/success.php";
//$lang['Test Name Example'] = "Beispiel: A/B-Test Produktseite";
//$lang['Control page code'] = "Tracking-Code für die Originalseite";
//$lang['Variant page code'] = "Tracking-Code für die Varianten-Seiten";
//$lang['Success page code'] = "Tracking-Code für die Bestätigungs- bzw. \"Danke\"-Seite";
$lang['Save and create test'] = "Speichern und Projekt erstellen";
$lang['Save test'] = "Änderungen speichern";

//$lang['Info headline tracking code'] = "Bauen Sie nun den Test in Ihre Website ein.";
//$lang['Then click on save'] = "Klicken Sie anschließend auf \"Speichern und Test erstellen\"";

//$lang["Insert tracking code mvt successpage"] = "Fügen Sie die beiden Tracking-Codes in Ihre Seiten ein.";
//$lang["Insert tracking code mvt successpage description"] = "Fügen Sie den oberen Code in die Originalseite ein, am besten unmittelbar hinter dem öffnenden &lt;head&gt;-Tag, wenn das nicht geht
//soweit oben im Quelltext wie möglich. Fügen Sie den unteren Code in Ihre Bestätigungs- bzw. \"Danke\"-Seite ein.";

//visual ab test substep 1
$lang['Create Visual A/B Test (Step 1 of 4)'] = "URL angeben";
//visual ab substep 2
//$lang['Create Visual A/B Test (Step 2 of 4)'] = "Visuellen A/B Test erstellen (Schritt 2 von 4)";
$lang['Original Source'] = "Originalseite";
$lang['New Variant'] = "Neue Variante";
$lang['Rename visual ab variant'] = "Variante umbenennen";
$lang['Remove visual ab variant'] = "Variante löschen";
$lang['Rename visual ab button'] = "Umbenennen";
$lang['Cancel rename visual ab button'] = "Abbrechen";
$lang['Edit custom css'] = "Eigenes CSS";
$lang['Edit custom js'] = "Eigenes Javascript";
$lang['Undo change']= "Änderung widerrufen";

//visual ab substep 3
$lang['Create Visual A/B Test (Step 3 of 4)'] = "Visuellen A/B Test erstellen (Schritt 3 von 4)";
//$lang["Insert tracking code visual A/B successpage"] = "Fügen Sie die beiden Tracking-Codes in Ihre Seiten ein.";
//$lang["Insert tracking code visual A/B successpage description"] = "Fügen Sie den oberen Code in die Originalseite ein, möglichst (aber nicht zwingend) unmittelbar hinter dem öffnenden &lt;head&gt;-Tag. Fügen Sie den unteren Code in Ihre Bestätigungs- bzw. \"Danke\"-Seite ein.";
//$lang["Insert tracking code visual A/B event"] = "Fügen Sie den speziellen Tracking-Code in Ihre Testseite ein.";
//$lang["Insert tracking code visual A/B event description"] = "Wenn es schwierig ist, die Seiten für den Test über ihre URL festzulegen, dann 
//können Sie im Website-Template statt des üblichen Trackingcodes diesen speziellen Code ausspielen und damit
//die Testseite kenntlich machen. Der Code soll möglichst (aber nicht zwingend) unmittelbar hinter dem öffnenden &lt;head&gt;-Tag stehen.";

$lang['Add url pattern'] = "Eine weitere URL hinzufügen";
$lang['Url pattern options'] = array(
    'include' => "URL für Projekt",
    'exclude' => "Ausgeschl. URL",
);

//visual ab substep 4
$lang['Create Visual A/B Test (Step 4 of 4)'] = "Visuellen A/B Test erstellen (Schritt 4 von 4)";
$lang['Add conversion goals to your test'] = "Legen Sie hier Konversionsziele fest";
$lang['Add conversion goals to your test description'] = "Mit dieser Auswahl geben Sie an, welche Aktionen Ihrer Besucher als Konversion gewertet werden. Sie können 
	mehrere Konversionsziele angeben, auch mehrere vom gleichen Typ.";
$lang['Create new goal'] = "Neues Ziel hinzufügen";
$lang['Add archived goal'] = "Archiviertes Ziel hinzufügen";
$lang['Choose Target Page'] = "Bitte wählen...";

$lang['Available Goals'] = array(
    '1' => "Engagement",
    '11' => "Reaktion auf SmartMessaging",
    '2' => "Affiliate-Anzeige",
    '3' => "Aufruf Zielseite",
    '12' => "Aufruf Ziel-Link",
    '13' => "Javascript-Event",
    '14' => "Seitenverweildauer",
    '15' => "Teaser-Klickrate",
    '16' => "Ranking Gesamt",
    '17' => "Erzeugte Page Impressions",
    '5' => "etracker ecommerce Ziel",
    '6' => "etracker Website Ziel",
    '7' => "etracker et_target Ziel",
    '8' => "etracker Ziel 'Produkt angesehen'",
    '9' => "etracker Ziel 'Produkt im Warenkorb'",
    '10' => "etracker Ziel 'Bestellung'",
);

$lang['Primary goal label'] = "Primäres Ziel";
$lang['Secondary goal label'] = "Sekundäres Ziel";
$lang['Goal menu action'] = "Wählen";
$lang['Set as primary goal'] = "Als primär setzen";
$lang['Goal menu archive'] = "Archivieren";
$lang['Goal menu edit'] = "Bearbeiten";

$lang['Goal create title'] = "Ziel hinzufügen";
$lang['Goal create add'] = "Ziel hinzufügen";
$lang['Goal details title'] = "Zieldetails";
$lang['Goal details sub'] = "";
$lang['Goal details change'] = "Übernehmen";
$lang['Goal details intro'] = "Wählen Sie hier den Typ des Konversionsziels aus und vergeben Sie ggfs. einen Namen und Konfigurationswerte";

$lang['Goal reactivate title'] = "Archivierte Ziele";
$lang['Goal reactivate sub'] = "Archivierte Ziele und ihre Konversionen reaktivieren.";
$lang['Goal reactivate intro'] = "Wählen Sie hier ein archiviertes Ziel aus um es Ihrem Projekt wieder hinzuzufügen.";
$lang['Goal reactivate link'] = "Reaktivieren";

$lang['ENGAGEMENT_desc'] = "Sobald ein Besucher einen Link auf der Seite anklickt oder ein Formular abschickt, wird dies als Engagement gewertet. Dieses 
Ziel eignet sich, um zu testen, wie die Absprungrate reduziert werden kann. Darüber hinaus führt sie meist zu recht schnellen Test-Ergebnissen.";
$lang['AFFILIATE_desc'] = "Wählen Sie dieses Ziel, wenn Sie die Zahl der Klicks auf Affiliate-Werbemittel auf Ihrer Seite 
	optimieren wollen. Wir erkennen die Links durch Vergleich mit unserer Datenbank als Affiliate-Werbemittel.";
$lang['TARGETPAGE_desc'] = "Wählen Sie dieses Ziel, wenn das Aufrufen einer Zielseite (etwa einer Danke- oder Bestätigungsseite) in einem Kaufprozess
	als Konversion gewertet werden soll.";
$lang['LINKURL_desc'] = "Wählen Sie dieses Ziel, wenn der Klick auf einen Link mit einer bestimmten URL als Konversion gewertet werden soll.";
$lang['CUSTOMJS_desc'] = "Sie können durch Aufruf der JavaScript-Funktion _bt.trackCustomGoal(eventname) von Ihrem HTML-Code aus sehr flexibel 
	bestimmte Besucher-Aktionen als Konversion zählbar machen. Wählen Sie dieses Konversionsziel, wenn Sie das nutzen wollen.";
$lang['TIMEONPAGE_desc'] = "Anstelle der Konversionsrate wird die durchschnittliche Verweildauer der Besucher auf der Seite gemessen und optimiert.";
$lang['CLICK_desc'] = "Click Goal Description Click Goal Description Click Goal Description Click Goal Description.";

//$lang['Etracker target page desc'] = "Sie können hier aus den von Ihnen in etracker definierten Website-Zielen eins auswählen, das als Konversion gewertet 
//	werden soll.";
$lang['smartmessaging desc'] = "Wenn Sie etracker Smart Messages einsetzen, gilt es als Konversion, wenn ein Besucher 
	der Handlungsaufforderung (Call-to-Action) der Smart Message folgt.";
$lang['etracker viewProduct desc'] = "Wenn Sie die etracker E-Commerce API einsetzen, dann wird eine Konversion ausgelöst, sobald das Event 'viewProduct' übermittelt wird.";
$lang['etracker insertToBasket desc'] = "Wenn Sie die etracker E-Commerce API einsetzen, dann wird eine Konversion ausgelöst, sobald das Event 'insertToBasket' übermittelt wird.";
$lang['etracker order desc'] = "Wenn Sie die etracker E-Commerce API einsetzen, dann wird eine Konversion ausgelöst, sobald das Event „order“ übermittelt wird. 
	Zwischen Lead und Sale wird dabei nicht unterschieden.<br>Es wird ebenfalls eine Konversion ausgelöst, 
	wenn die Parameter et_target, et_tval, et_tonr und et_tsale im Tracking Code gesetzt sind.";

$lang['TARGETPAGE_field_desc'] = "Geben Sie die URL der Zielseite ein. Sie können das * Zeichen als Platzhalter verwenden.";
$lang['LINKURL_field_desc'] = "Geben Sie die URL des Links ein. Sie können das * Zeichen als Platzhalter verwenden.";
$lang['CUSTOMJS_field_desc'] = "Geben Sie hier den Namen des Events ein, der der Funktion _bt.trackCustomGoal() als Parameter übergeben wird.";
$lang['CLICK_field_desc'] = "Click goal field description lorem ipsim dolor sit amet consecteur adipiscing.";
//$lang['Etracker target page field description'] = "Wählen Sie hier den Namen des Website-Ziels aus.";
//$lang['Etracker ecommerce event field description'] = "Wählen Sie hier den Namen des Events aus.";
//$lang['Etracker et_target field description'] = "### noch zu klären ###";

//$lang['Choose Pagename'] = "Bitte wählen...";
//$lang['Choose Event'] = "Bitte wählen...";

//$lang["Engagement"] = "Engagement";
//$lang["Affiliate goal"] = "Klick auf Affiliate-Werbemittel";
//$lang["Success page goal"] = "Bestätigungs-/Danke-Seite";
//$lang["Custom goal"] = "Selbstdefiniertes (Javascript-)Ziel";




//ocpc
$lang["Pattern fur die Originalseite"] = "URL für die Seite(n) des Projekts";
$lang["url of testpage"] = "URL für die Seite(n)";
$lang["Geben Sie die URL der zu testenden seite"] = "Geben Sie an, für welche URLs das Projekt ausgespielt werden soll, bzw. welche URLs ausgeschlossen werden sollen. Sie können das * Zeichen als Platzhalter verwenden.
Alternativ können Sie in der Seite eine Javascript-Variable et_pagename setzen und deren Inhalt hier angeben.";
$lang["Control Page Example"] = "Beispiel: */product.php";

//$lang["Pattern fur die Originalseite OCPT"] = "Die Testseite wird über einen eigenen Trackingcode festgelegt";
//$lang["Geben Sie die URL der zu testenden seite OCPT"] = "Die Testseite wird nicht über eine URL definiert, sondern indem
//das Seiten-Template der Testseite einen speziellen Trackingcode ausspielt (siehe Menüeintrag \"Trackingcode\")";


//$lang["Pattern fur die Danke-Seite"] = "(Teil-) URL der Danke- bzw. Bestätigungs-Seite";
//$lang["Geben Sie die URL der zu Danke-Seite"] = "Geben Sie die URL der Danke- oder Bestätigungs-Seite an. Sie können das * Zeichen als Platzhalter verwenden.";
//$lang["Success Page Example"] = "Beispiel: *confirmation*";

$lang["OCPC Tracking code description title"] = "Fügen Sie nun den Tracking-Code in Ihre Seiten ein";
$lang["OCPC Tracking code description"] = "Fügen Sie (falls das nicht bereits geschehen ist) den Code in alle Ihre Seiten ein, möglichst (aber nicht zwingend) unmittelbar hinter dem 
öffnenden &lt;head&gt;-Tag.";
//ocpt
//$lang["Tracking-Code fur die Originalseite"] = "Eigener Tracking-Code für die Testseite";
//$lang["Tracking-Code fur die Varianten-Seiten"] = "Tracking-Code für die Varianten-Seiten";
//$lang["Tracking-Code fur die Bestatigungsseite"] = "Tracking-Code für die Danke- oder Bestatigungsseite";

// visual test factor dialogue
/*
$lang['Content variations'] = "Element-Varianten bearbeiten";
$lang['Factor Name'] = "Wählen Sie einen Namen für das Element";
$lang['Factor Name description'] = "Der Name wird in der Übersicht der Kombinationen benötigt.";
$lang['Enter name of factor here'] = "Beispiel: \"Überschrift\" oder \"Submit-Button\"";
$lang['Control'] = "Originalinhalt";
$lang['Control description'] = "Dieses Feld zeigt den HTML-Inhalt des Elementes an.";
$lang['Click sign'] = "Klicken Sie hier um eine Variante anzulegen.";
*/
$lang['Variant prefix'] = "v";
$lang['Variant label'] = "Variante ";

//edit visual test
//$lang['Edit Visual Test (Step 1 of 2)'] = "Multivariaten Test bearbeiten (Schritt 1 von 2)";
//$lang['Edit Visual Test (Step 2 of 2)'] = "Multivariaten Test bearbeiten (Schritt 2 von 2)";

//edit visual a/b test
$lang['Edit Visual A/B Test (Step 1 of 3)'] = "Visueller Editor";
$lang['Edit Visual A/B Test (Step 2 of 3)'] = "Projektname und URL festlegen";
$lang['Edit Visual A/B Test (set allocation)'] = "Traffic-Verteilung";
$lang['Edit Visual A/B Test (Step 3 of 3)'] = "Konversionsziele festlegen";
$lang['Save variants'] = "Änderungen speichern";
$lang["Save approach"] = "Änderungen speichern";
$lang['Save goals'] = "Änderungen speichern";

// A/B
$lang["Create A/B Test (Step 1 of 4)"] = "URLs eingeben";
$lang["Edit A/B Test (Step 1 of 2)"] = "URLs eingeben";

$lang["Enter variants of your page"] = "Geben Sie hier die URL der Variante der Originalseite ein.";
$lang["Description variant"] = "Bitte geben Sie einen Namen sowie die URL der Variante ein.";
$lang["Add a variant"] = "Eine weitere Variante hinzufügen";

//$lang["Create A/B Test (Step 2 of 4)"] = "Personalization";

$lang["Create A/B Test (Step 3 of 4)"] = "Projektname und URL festlegen";
$lang["Create A/B Test (Step 4 of 4)"] = "Konversionsziele festlegen";

//deferred impressions (Additional settings)
$lang["Additional settings title"] = "Erweiterte Einstellungen";
$lang["Additional settings description"] = "Projekt ausspielen abhängig von DOM-Element (hier CSS-Pfad eingeben) oder abhöngig von Javascript-Ausdruck:";
$lang["Additional settings options"] = array(
    'not_used' => "immer ausspielen",
    'is_visible' => "ausspielen wenn Element sichtbar",
    'exists' => "ausspielen wenn Element vorhanden",
    'expression_true' => "ausspielen wenn JS-Ausdruck true ergibt"
);

//IP Blacklisting
$lang["IP blacksliting title"] = "IP Sperre";
$lang["IP blacksliting description"] = "Sie haben folgende IP-Nummern von der Zählung ausgeschlossen:";
$lang["IP blacksliting not ignored"] = "Die IP Sperre soll auch für dieses Projekt aktiv sein.";
$lang["IP blacksliting ignored"] = "Die IP Sperre soll für dieses Projekt nicht aktiv sein.";

//Project scheduler
$lang["Project schedule title"] = "Start und Endzeitpunkt für das Projekt";
$lang["Project schedule description"] = "Legen Sie fest, in welchem Zeitraum Seiten Ihres Projektes ausgeliefert werden sollen (Datum und Uhrzeit in MEZ).";
$lang["Project schedule start"] = "Start";
$lang["Project schedule end"] = "Ende";
$lang["Project schedule start tooltip"] = "Auswahl Startdatum";
$lang["Project schedule end tooltip"] = "Auswahl Endedatum";
$lang["Datepicker locale month"] = "Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember";
$lang["Datepicker locale month short"] = "Jan,Feb,Mar,Apr,Mai,Jun,Jul,Aug,Sep,Okt,Nov,Dez";
$lang["Datepicker locale days"] = "Sonntag,Montag,Dienstag,Mittwoch,Donnerstag,Freitag,Samstag";
$lang["Datepicker locale days short"] = "Son,Mon,Die,Mit,Don,Fre,Sam";
$lang["Datepicker locale days min"] = "So,Mo,Di,Mi,Do,Fr,Sa";

$lang["Insert the tracking code to your page"] = "Fügen Sie nun die Tracking-Codes in Ihre Seiten ein.";
$lang["Insert the tracking code to your page description"] = "Fügen Sie den oberen Code in die Originalseite ein, am besten unmittelbar hinter dem öffnenden &lt;head&gt;-Tag, wenn das nicht möglich
ist so weit oben im Quelltext wie möglich. Fügen Sie den unteren Code in Ihre Bestätigungs- bzw. \"Danke\"-Seite ein.";

$lang["Insert tracking code ab event description"] = "Fügen Sie den oberen Code in die Originalseite ein, am besten unmittelbar hinter dem öffnenden &lt;head&gt;-Tag. 
Fügen Sie den unteren Code genauso in jede Ihrer Varianten-Seite ein.";
$lang["Insert three tracking codes to your page description"] = "Fügen Sie den oberen Code in die Originalseite ein, am besten unmittelbar hinter dem 
öffnenden &lt;head&gt;-Tag, wenn das nicht möglich ist so weit oben im Quelltext wie möglich. Fügen Sie den mittleren Code genauso in jede Ihrer 
Varianten-Seite ein, und den unteren Code in Ihre Bestätigungs- bzw. \"Danke\"-Seite.";

$lang["Please enter the name here"] = "(Optionaler Name für die Variante)";
//$lang["tooltip_variant"] = "tooltip_variant";
//$lang["Description"] = "Description";$lang["Description"] = "Description";


//user test editor
$lang['Test Create Visual A/B Test (Step 1 of 3)'] = "Visuellen A/B Test erstellen (Schritt 1 von 3)";
//visual ab substep 2
$lang['Test Create Visual A/B Test (Step 2 of 3)'] = "Visuellen A/B Test erstellen (Schritt 2 von 3)";
$lang['Test Create Visual A/B Test (Step 3 of 3)'] = "Visuellen A/B Test erstellen (Schritt 3 von 3)";


$lang['Seite jetzt bearbeiten.'] = "Seite jetzt bearbeiten.";
$lang['Abbrechen und zurueck zum Dashboard'] = 'Abbrechen und zurück zum Dashboard';
$lang['Abbrechen und zurueck zum Details'] = 'Abbrechen';
$lang['Abbrechen'] = 'Abbrechen';
$lang['Rename variant'] = 'Variante umbenennen';

$lang['How many visitors shall be allocated'] = 'Anzahl der Projektteilnehmer';
$lang['How many visitors shall be allocated description'] = 'Geben Sie hier an, wieviel Prozent aller Besucher der Originalseite am Projekt teilnehmen sollen.';

$lang['Allocation for each variant'] = "Traffic-Verteilung für Varianten";
$lang['Allocation for each variant intro'] = "Wählen Sie über die Schieberegler oder Eingabefelder aus, 
    aus, in welchem Anteil die Varianten an Besucher ausgespielt werden.";
$lang['Allocation reset link'] = "Verteilung zurücksetzen.";

$lang['Disable all scripts in page'] = 'Disable all scripts in page';
$lang['Disable all scripts in page info'] = 'In case editor doesnt work with current configuration, you can disable all scripts by using this checkbox and try again.';

/* confirm delete variant */

$lang['Confirm delete variant heading'] = 'Variante löschen';
$lang['Confirm delete variant copy'] = 'Möchten Sie diese Variante endgültig löschen? Alle von Ihnen gemachten Änderungen an der Variante gehen verloren.';
//$lang['Cancel delete variant']='Cancel' - Use "Abbrechen"
$lang['Confirm variant delete button']= 'Löschen';

$lang['Editor Page Unload']="Wollen Sie die Seite wirklich verlassen? Änderungen gehen möglicherweise verloren!";/* message used in window.onbeforeunload dialog*/
$lang['Editor Visitor Page Heading']="Visueller Editor";/* heading vistor sees at top of page*/
$lang['Editor Page Title']="BlackTri Visual AB Editor";/* for title tag*/

$lang['Editor Visitor Next Step Button']="Jetzt BlackTri 30 Tage testen";
$lang['Editor Visitor Cancel Button']="Abbrechen";
if($tenant == 'etracker') {
	$lang['Editor Visitor Next Step Button']="Jetzt Page Optimizer 21 Tage testen";
	$lang['Editor Visitor Cancel Button']="Abbrechen";
}
/* popup for add variant tab when prior variants not edited */
$lang['Editor Edit variants before create new - title']="Die Variante entspricht dem Original";
$lang['Editor Edit variants before create new - text'] ="Bevor Sie neue Varianten anlegen sollten Sie Änderungen vornehmen, um unerwünschte Ergebnisse im Test zu vermeiden.";

$lang['Edit html'] = 'HTML bearbeiten';
$lang['Edit html source...'] = 'HTML bearbeiten';
$lang['Edit element text'] = 'Text bearbeiten';
$lang['Edit text'] = 'Text bearbeiten';
$lang['Edit custom css...'] = 'CSS bearbeiten';
$lang['Edit custom js...'] = 'Javascript bearbeiten';
$lang['Save'] = 'Speichern';
$lang['Cancel'] = 'Abbrechen';
$lang['Edit link']="Link bearbeiten";
$lang['Save link'] = "Speichern";
$lang['Enter link URL']="Geben Sie hier die neue Link-URL ein.";

$lang['Track click goals']="Klicks tracken";
$lang['Edit click goals']="Klick-Ziele bearbeiten";
$lang['Highlight goals hide']="Klick-Ziele nicht hervorheben";
$lang['Highlight goals display']="Klick-Ziele hervorheben";
$lang['Enter goal name']="Name des Konversionsziels";
$lang['Enter goal name short']="Name";
$lang['Enter goal selector short']="CSS-Selektor";
$lang['Click goal name']="Klick";
$lang['Click goal prefix']="Klick: ";
$lang['CLick goal taglabel'] = "Klick";
$lang['Click goal advanced']="Erweiterte Einstellungen";
$lang['Click goal remove'] = "Tracking beenden";

$lang['Edit image']="Bild-URL bearbeiten";
$lang['Save image'] = "Speichern";
$lang['Enter image URL']="Geben Sie hier die neue Bild-URL ein.";
$lang['Hide element'] = 'Element verbergen';
$lang['Remove element'] = 'Element entfernen';
$lang['Move element'] = 'Element verschieben';
$lang['Edit Styles']="CSS bearbeiten";
$lang['Rearrange element'] = "Elemente umsortieren";
$lang['Select parent element'] = "Elternelement auswählen";
//$lang['Remove factor'] = 'Remove factor';
$lang['Editor SOURCE'] = 'Source';
$lang['Editor DESIGN'] = 'Design';
$lang['Click to copy to clipboard'] = 'Click to copy jQuery path to clipboard';

$lang['Smart message']="Smart Messaging";
$lang['Smart message Add']="Hinzufügen";
$lang['Smart message Hide']="Verbergen";
$lang['Smart message Show']="Zeigen";
$lang['Smart message Edit']="Bearbeiten";
$lang['Smart message Delete']="Löschen";

/* update missing */
$lang['Editor page heading']="Visueller Editor";
$lang['Editor test customization']="Projektname und URL festlegen";
$lang['Editor goals customization']="Konversionsziele festlegen";

$lang['Rearrange control title'] = "Elemente umsortieren";
$lang['Rearrange control description'] = "Ziehen Sie das Element mit der Maus an die gewünschte Position.";

$lang['You can now edit the page. Some elements are still downloaded.'] = 'Sie können die Seite jetzt bearbeiten. Einige Elemente werden noch nachgeladen.';

$lang['Error on page load'] = 'Fehler beim Laden der Seite';
$lang['Client page has invalid html'] = 'Das Dokument kann nicht geöffnet werden weil das HTML fehlerhaft ist.';

$lang['Device type to load with'] = 'Seite mit Endgerät öffnen:';
$lang['Smartphone'] = 'Smartphone';
$lang['Tablet'] = 'Tablet';
$lang['Desktop'] = 'Desktop';

$lang['change url title'] = 'Änderungen behalten?';
$lang['change utl text'] = 'Sie haben eine oder mehrere Varianten Ihres Projektes im Visuellen Editor 
    bearbeitet, diese Änderungen sind möglicherweise auf der neuen Seite nicht mehr anwendbar. 
    Möchten Sie die Änderungen dennoch behalten, oder sollen sie verworfen werden?';
$lang['change url keep'] = 'Änderungen behalten';
$lang['change url undo'] = 'Änderungen verwerfen';

$lang['IP Filtering'] = 'Projekt für IP-Adressen ausspielen';
$lang['IP Filtering description'] = 'Geben Sie hier eine oder mehrere IP-Adressen an und wählen Sie, ob nur für diese das Projekt ausgespielt werden soll, oder ob sie vom Ausspielen ausgeschlossen werden sollen.';

$lang['Ignore IP address'] = 'Projekt unabhängig von IP ausspielen';
$lang['Allow IP address'] = 'Für angegebene IPs ausspielen';
$lang['Exclude for IP address'] = 'Projekt für angegebene IPs ausschließen';