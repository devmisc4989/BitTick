<?php
if(function_exists(get_instance)) {
    $CI = & get_instance();
    $tenant = $CI->config->item('tenant');
}

$lang['title_collection'] = "Projektdetails - ";
$lang['testtype_split'] = "Split-URL-Test";
$lang['testtype_visual'] = "Visueller A/B-Test";
$lang['testtype_multipage'] = "Mehrseitiger Test";
$lang['testtype_sms'] = "Smart Messaging Kampagne";
$lang['testtype_teaser'] = "Teaser Test";
$lang['lcd_previouslink'] = "< Ältere Ergebnisse";
$lang['lcd_nextlink'] = "Neuere Ergebnisse >";
$lang['lcd_description'] = "Sie finden hier alle Details des Projekts, sowie die Optimierungsergebnisse der Originalseite und aller Varianten.";
$lang['control_page_name'] = "Originalseite";
$lang['control is winner'] = "Die Originalseite gewinnt.";

$lang['tt_back_overview'] = 'Zurück zur Übersicht';

$lang['tto_intro_headline'] = "Diese Übersicht zeigt alle Artikel an, für die Sie Headline-A/B-Tests erstellt haben.";
$lang['tto_btn_newtest'] = "Headline-A/B-Test erstellen";
$lang['tto_link_preview'] = "Vorschau öffnen";

$lang['smry_headline_paused'] = "Das Projekt ist pausiert, es findet zur Zeit keine Optimierung statt.";

$lang['smry_headline_unverified'] = "Der Einbau des Trackingcodes wurde noch nicht überprüft.";
$lang['smry_subline_unverified'] = "<a href=\"javascript://\" class=\"collectionVerify\">Bitte holen Sie das nach indem Sie hier klicken.</a>";

$lang['smry_headline_noevents'] = "Es wurden bisher keine Aufrufe der Seiten im Projekt gezählt.";
$lang['smry_subline_noevents'] = "Dies kann daran liegen, dass der Trackingcode noch nicht oder nicht korrekt eingebunden wurde.";

$lang['smry_headline_controlwinner'] = "Der Test hat leider keine Verbesserung ergeben.";
$lang['smry_subline_controlwinner'] = "Keine Variante hat eine bessere Konversionsrate als die Originalseite (%s %%).";

$lang['smry_headline_variantwinner'] = "Der Test hat eine Verbesserung von %s %% ermittelt!";
$lang['smry_subline_variantwinner'] = "Die erfolgreichste Seite ist die Variante %s.";

$lang['smry_headline_nosignificance'] = "Bisher hat keine Variante ein besseres Ergebnis als die Originalseite. Der Test läuft aber noch.";
$lang['smry_subline_testtime'] = "Die verbleibende Laufzeit ist <b>etwa %s Tage.</b>";
$lang['smry_subline_testtime_morethan6months'] = "Die verbleibende Laufzeit ist <b>länger als 6 Monate.</b>";
$lang['smry_subline_testtime_3to6months'] = "Die verbleibende Laufzeit ist <b>3 bis 6 Monate.</b>";
$lang['smry_subline_testtime_1to3months'] = "Die verbleibende Laufzeit ist <b>1 bis 3 Monate.</b>";
$lang['smry_subline_testtime_nodata'] = "Es sind noch nicht genug Daten erfasst worden um die verbleibende Laufzeit zu schätzen.";
$lang['variant_testtime_nodata'] = "Noch nicht genug Daten";

$lang['smry_headline_oneleader'] = "Die Variante %s konvertiert um %s %% besser als die Originalseite. Der Test läuft aber noch.";
$lang['smry_headline_multleaders'] = "Mehrere Varianten konvertieren besser als die Origialseite, die beste um %s %%. Der Test läuft aber noch.";

/*** Perso headline and subline ***/
$lang['smry_headline_single_nosms'] = "Ausspielen von segmentierten Varianten.";
$lang['smry_subline_single_nosms'] = "Die Originalseite wird nicht angezeigt.";
$lang['smry_headline_sms_nosingle'] = "Ausspielen von Varianten mit Smart Messages.";
$lang['smry_subline_sms_nosingle'] = "Die Originalseite wird nicht angezeigt.";
$lang['smry_headline_single_and_sms'] = "Ausspielen von segmentierten Varianten mit Smart Messages.";
$lang['smry_subline_single_and_sms'] = "Die Originalseite wird nicht angezeigt.";

$lang['autopilot_is_active'] = "Der Autopilot ist aktiviert (nachweislich schlecht laufende Varianten werden nicht ausgespielt).";
$lang['autopilot_activate'] = "Autopilot einschalten.";
$lang['autopilot_is_stopped'] = "Der Autopilot ist nicht aktiviert, alle Varianten (auch schlecht laufende) werden ausgespielt.";
$lang['autopilot_stop'] = "Autopilot abschalten.";


//action
$lang["Preview Control"] = "Vorschau der Originalseite";
$lang["Preview Variant"] = "Vorschau dieser Variante";

$lang["link_edit"] = "Projekt bearbeiten";
$lang["link_restart"] = "Projekt neu starten";
$lang["link_start"] = "Projekt starten";
$lang["link_play"] = "Projekt fortsetzen";
$lang["link_pause"] = "Projekt anhalten";

$lang["Original und Varianten"] = "Original und Varianten";
$lang["Testseite"] = "Projektname und URL festlegen";
$lang["Tracking-Code"] = "Tracking-Code";
$lang["Goals"] = "Konversionsziele";
$lang["Time interval label"] = "Zeitintervall";
$lang["Sandbox export"] = "Für Sandbox exportieren";

$lang["Available time intervals"] = array(
    OPT_TREND_DAY => "Tage",
    OPT_TREND_HOUR => "Stunden",
    OPT_TREND_5MINUTE => "5 Minuten",
    OPT_TREND_MINUTE => "Minuten",
);

/* * *****************************************************************
 * PERSONALIZATION
 * **************************************************************** */
$lang["Personalization_Status_0"] = "Bisher wurde keine Segmentierung festgelegt";
$lang["Personalization_Status_1"] = "Dieses Projekt wird für ausgewählte Besucher ausgespielt gemäß der Segmentierungsregel: ";
$lang["Personalization_Status_2"] = "Varianten werden einzeln segmentiert, die Originalseite wird nicht ausgespielt.";
$lang["Personalization_Not_Personalized"] = "Ohne Segmentierung.";
$lang["Personalization_Unpersonalized_change"] = "Ohne Segmentierung";
$lang["Personalization_Confirm_Norule_Title"] = "Dieses Projekt wird nicht segmentiert.";
$lang["Personalization_Confirm_Norule"] = "Wollen Sie wirklich die Segmentierung für dieses Projekt entfernen?";
$lang["Personalization_Status_Link"] = "Segmentierung festlegen.";
$lang["Personalization_Yes_Continue"] = "Ja, abschließen";
$lang["Personalization_Save_Changes"] = "Segmentierung abschließen";

/* * *****************************************************************
 * DIAGNOSE MODE POP UP
 * **************************************************************** */
$lang["Diagnose_Mode"] = "Diagnose";
$lang["Debug_Test"] = "URL einer Originalseite für die Diagnose eingeben";
$lang["Start_Diagnose"] = "Diagnose starten";
$lang["Diagnose_Mode_Headline1"] = "Falls Ihr Projekt keine Varianten ausspielt, können Sie mit der Diagnose den Grund dafür herausfinden ob Sie das Projekt richtig konfiguriert haben und ob nur ein Projekt pro Originalseite ausgespielt wird.. 
	<br />Geben Sie im Eingabefeld unten die URL einer Originalseite ein. Wir werden die Projektkonfiguration für Sie überprüfen.";
$lang["Diagnose_Mode_Headline3"] = "Klicken Sie auf „Start“, um die Diagnose durchzuführen.<br /><br /> ";

$lang["URL_Of_Page"] = "URL einer Originalseite";
$lang["Back to details"] = "Projektdetails";
$lang["Test_Now"] = "Start";
$lang["Close_Window"] = " Schließen ";
$lang["Please_Wait"] = "Einen Moment bitte...";
$lang["Please_Wait_Message"] = "Bitte haben Sie kurz Geduld während wir die Diagnose-Daten sammeln.";

/* * *****************************************************************
 * RESULT SIMPLE
 * **************************************************************** */
$lang["Diagnose_Result"] = "Ergebnis der Diagnose";
$lang["Head_Issue1"] = "Ergebnis: Ihr Projekt-Kontingent ist aufgebraucht.";
$lang["Head_Issue2"] = "Ergebnis: Keines Ihrer Projekte ist aktiviert.";
$lang["Head_Issue5"] = "Ergebnis: Es wurde ein Problem mit dem Tracking Code auf Ihrer Seite festgestellt.";
$lang["Head_Issue6"] = "Ergebnis: Der Tracking-Code der überprüften Seite gehört nicht zu diesem Account.";

$lang["Copy_Issue2"] = "Sie müssen ein Projekt aktivieren, damit Varianten ausgespielt werden. Wählen Sie dazu auf der Projektdetailseite Projekt starten.";
$lang["Copy_Issue5"] = "Wir konnten keine Diagnosedaten sammeln. Bitte stellen Sie sicher, dass der Tracking Code korrekt in die überprüfte
	Seite integriert ist.";
$lang["Copy_Issue6"] = "In der Seite finden wir die Kunden-ID <strong id=\"client-code-tested\"></strong>, aber die Kunden-ID dieses Accounts ist 
	<strong id=\"client-code-login\"></strong>. <br />Aus Sicherheitsgründen können Sie nur Diagnosedaten für Seiten mit Ihrer eigenen Kunden-ID abrufen.";

if($tenant == 'etracker') {
	$lang["Head_Issue6"] = "Ergebnis: Account-Schlüssel 1 der überprüften Seite gehört nicht zu diesem Account.";
	$lang["Copy_Issue6"] = "In der Seite finden wir den Account-Schlüssel 1 <strong id=\"client-code-tested\"></strong>, aber der Account-Schlüssel 1 dieses Accounts ist 
		<strong id=\"client-code-login\"></strong>. <br />Aus Sicherheitsgründen können Sie nur Diagnosedaten für Seiten mit Ihrem eigenen Account-Schlüssel 1 abrufen.";
}

$lang["Copy_Status1"] = "Ihr Projekt-Kontingent beträgt <strong class=\"quota\"></strong>  Unique Visitors und ist aufgebraucht. <br />
	Bitte kontaktieren Sie unsere Sales-Team um sich über unsere Editionen zu informieren.";
$lang["Copy_Status6"] = "Ihr monatliches Projekt-Kontingent von <strong class=\"quota\"></strong> Unique Visitors ist aufgebraucht. <br />
	Das nächste Kontingent ist ab <strong class=\"reset-date\"></strong> verfügbar. <br />
	Wenn Sie ein Upgrade wünschen, hilft unser Sales-Team Ihnen gerne weiter.";

/* * *****************************************************************
 * RESULT MATCH
 * **************************************************************** */
$lang['Match_Intro'] = "Wenn es eine Übereinstimmung der eingegebenen URL mit einer Ihrer
	Projektkonfiguration gibt, dann können die Varianten ausgespielt werden.";
$lang['Match_Intro_Etpagename'] = "Die überprüfte Seite enthält eine Javascript-Variable \"et_pagename\". Wenn es eine Übereinstimmung dieser Variablen oder der eingegebenen URL mit einer Projektkonfiguration gibt, dann können die Varianten ausgespielt werden.";
$lang['Page_Url_Title'] = "URL der überprüften Seite:";
$lang['Et_Pagename_Title'] = "et_pagename der überprüften Seite:";
$lang['Checked_Test_Title'] = 'Wir haben folgende laufende Projekte auf Übereinstimmung überprüft:';

$lang['back'] = "Zurück";

$lang['Table_Testname'] = "Projektname";
$lang['Table_Testpage'] = "URL der Seite(n)";
$lang['Table_Result'] = "Ergebnis";
$lang['Table_Match'] = "<b>Übereinstimmung, Projekt kann ausgespielt werden</b>";
$lang['Table_Match_Conflict'] = "<b>Übereinstimmung, aber Konflikt:</b>";
$lang['Table_Nomatch'] = "Keine Übereinstimmung";
$lang['Table_Conflict_Sms'] = "Nur eine Smartmessage pro Seite möglich.";
$lang['Table_Conflict_Split'] = "Split-Tests können nur exklusiv ausgespielt werden.";