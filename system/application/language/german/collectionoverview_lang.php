<?php

$CI = & get_instance();
$tenant = $CI->config->item('tenant');

if ($tenant != 'etracker')
    $lang['headline'] = "Dashboard";
else
    $lang['headline'] = "Projektübersicht";
$lang['description'] = "Diese Übersicht zeigt für jedes Ihrer Projekte die erzielte Konversionsrate und den Status der Optimierung an. 
Klicken Sie auf den \"Wählen\"-Link unter \"Aktion\", um die Projekte zu bearbeiten, zu löschen, neuzustarten etc.";


$lang['action_delete'] = "Projekt löschen";
$lang['action_pause'] = "Projekt anhalten";
$lang['action_play'] = "Projekt fortsetzen";
$lang['action_start'] = "Projekt starten";
$lang['action_verify'] = "Trackingcode überprüfen";
$lang['action_show_code'] = "Trackingcode anzeigen";
$lang['action_status'] = "";
$lang['action_show_details'] = "Projektdetails zeigen";

$lang['tooltip_running'] = "Das Projekt läuft";
$lang['tooltip_paused'] = "Das Projekt ist angehalten";

$lang['remaining time'] = "Verbleibende Zeit:";
$lang['testtime'] = "Etwa %s Tage";
$lang['testtime_morethan6months'] = "Länger als 6 Monate";
$lang['testtime_3to6months'] = "3 bis 6 Monate";
$lang['testtime_1to3months'] = "1 bis 3 Monate";
$lang['testtime_nodata'] = "Noch zu wenige Daten";

$lang['testtype_split'] = "Split-URL-Test";
$lang['testtype_visual'] = "Visueller A/B-Test";
$lang['testtype_teaser'] = "Teaser Test";
$lang['testtype_multipage'] = "Mehrseitiger Test";
$lang['testtype_sms'] = "Smart Messaging Kampagne";

$lang['deletecollection_headline'] = "Wollen Sie das Projekt wirklich löschen?";
$lang['deletecollection_subline'] = "Alle Einstellungen und Messergebnisse werden entfernt, dies kann nicht rückgängig gemacht werden.";

//tracking code pop-up
$lang['Verify code'] = "Trackingcode überprüfen";
$lang['Please wait until your code gets verified'] = "Einen Moment bitte, Ihre Seite wird geladen...";
$lang['Tracking code missing'] = "Der Trackingcode wurde nicht gefunden " . splink('trackingcodemissing');
$lang['Tracking code verified'] = "Sie haben den Trackingcode korrekt eingebaut!";
$lang['Tracking code instructions'] = "
<br>Bitte überprüfen Sie in der Vorschau, ob alle Seiten korrekt angezeigt werden.
<br>Aktivieren Sie anschließend das Projekt durch Klick auf \"Projekt starten\".<br>" . splink('trackingcodeincluded');
$lang['Click here to close'] = "Schließen";

$lang['Please wait until your code gets loaded'] = "Der Code wird geladen...";
$lang['Show code'] = "Trackingcode anzeigen";

/* * *****************************************************************
 * DUPLICATE TEST
 * **************************************************************** */
$lang['Duplicate_Test'] = "Projekt duplizieren";
$lang['Duplicate_Title'] = "Projekt duplizieren: <strong id=\"test-name-title\"></strong>.";
$lang['Duplicate_Copyof'] = "Kopie von ";
$lang['Duplicate_Info'] = "Bitte geben Sie einen Namen für das neue Projekt ein.";
$lang['Duplicate_Error_Title'] = "Ein Fehler ist aufgetreten...";
$lang['Duplicate_Error_Content'] = "Wir konnten keine Kopie des Projekts anlegen, bitte kontaktieren Sie unseren Support.";
$lang['Duplicate_Wait_Title'] = "Einen Moment Geduld bitte...";
$lang['Duplicate_Wait_Content'] = "";

$lang['conflict layer popup'] = array(
    'confitm_title' => "Es gibt Konflikte - wirklich starten?",
    'title' => "Konflikt zwischen Projekten",
    'subtitle' => "Projekt",
    'table name' => "Projektname",
    'table type' => "Projekt-Typ",
    'table problem' => "Problem",
    'conflict intro' => array(
        0 => "",
        1 => "Das Projekt wird nicht ausgespielt, weil es für die gleiche URL wie andere Prokekte konfiguriert wurde.",
        2 => "Wenn dieses Projekt gestartet wird, dann werden mindestens die folgenden Projekte, die für die gleiche URL konfiguriert sind, nicht mehr ausgespielt.",
    ),
    'table conflicts' => array(
        0 => "",
        1 => "Nur ein Smart-Messaging-Projekt pro URL ist möglich.",
        2 => "Ein Split-URL-Test kann nur exklusiv für eine URL konfiguriert sein.",
    ),
);

/* * *****************************************************************
 * TEASER TESTS
 * **************************************************************** */
$lang['tt original title'] = "URL der Startseite";
$lang['tt original text'] = "Geben Sie hier die URL Ihrer Startseite ein - dies wird für die Vorschau benötigt.";
$lang['tt link example'] = "Beispiel: http://www.newsseite.de";
$lang['tt interface title'] = "Anlage von Headline-A/B-Tests";
$lang['tt_interface text'] = "Erstellung von Headline-A/B-Tests (kann später nicht mehr geändert werden)";
$lang['tt interface options'] = array(
    'API' => "Integriert in Content Management System",
    'UI' => "Externe Verwaltung über BlackTri-Web-Applikation",
);
$lang['tt_headlines_title'] = "Headline-Original und -Varianten";
$lang['tt_headlines_main_label'] = "Headline-Original";
$lang['tt_headlines_main_intro'] = "Kopieren Sie die originale Headline aus dem HTML und fügen Sie sie hier ein";
$lang['tt_headlines_variant_label'] = "Headline-Variante";
$lang['tt_headlines_variant_permanent'] = "Use for permanent deliver";
$lang['tt_headlines_variant_add'] = "Eine weitere Variante hinzufügen";
$lang['tt_headlines_variant_delete'] = "Variante löschen";
$lang['tt_headlines_variant_back'] = "Teaser test overview";
$lang['tt_headlines_variant_save'] = "Speichern";

$lang['tt_delete_confirm_title'] = "Wollen Sie den Test wirklich löschen?";
$lang['tt_delete_confirm_text'] = "Alle Einstellungen und Messergebnisse werden entfernt, dies kann nicht rückgängig gemacht werden.";

$lang['tt config layer'] = array(
    'wizard' => array(
        'title' => "Teaser Test",
        'btn_save' => "Speichern und Projekt anlegen",
        'btn_cancel' => "Zurück",
    ),
    'edit' => array(
        'title' => "Teaser Test",
        'btn_save' => "Änderungen speichern",
        'btn_cancel' => "Abbrechen",
    ),
);