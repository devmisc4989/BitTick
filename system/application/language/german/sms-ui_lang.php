<?php
$lang['sms_ui_rules']=array(
        /* key must match "value" in config/sms.php */
        'exit_intent'=> 'Der Besucher will Ihre Seite verlassen.',
        'greeter'=> 'Der Besucher ist gerade auf Ihre Seite gekommen.',
        'attn_grabber'=> 'Der Besucher ist unentschlossen oder inaktiv.',
        'always_on'=> 'Die Message wird unabhängig vom Verhalten des Besuchers immer angezeigt.'

);

/**
 * Section titles and headings
 */

$lang['sms_ui_headings'] = array(
    'select_trigger' => 'Auslöser festlegen',
    'select_design' => 'Layout erstellen',
    'message_config' => 'Layout konfigurieren',
    'content_type' => 'Content-Typ ',
    'message_type' => 'Anzeigen als ',
    'select_group' => 'Design auswählen',
    'select_template' => 'Vorlage auswählen',
    'web_integrate' => 'URL angeben'

);

/**
 * Steps buttons
 */
$lang['sms_ui_buttons'] = array(
    'rules_section_cancel' => 'Projekt auswählen',
    'select_design' => $lang['sms_ui_headings']['select_design'],
    'return_to_rules' => 'Auslöser festlegen',
    'configure_message' => 'Layout konfigurieren',
    'return_to_design' => 'Layout erstellen',
    'select_url' => 'URL angeben',
    'return_to_config' => 'Layout konfigurieren',
    'open_in_editor' => 'Visueller Editor',
    'hide_preview'=> 'Zum Schließen der Ansicht irgendwo klicken.',
    'save_edit_changes' => 'Änderungen speichern', /* for edit mode in editor */
    'cancel_edit' => 'Abbrechen',
    'cancel_add_sms' => 'Abbrechen' /* cancel "rules" section from editor */
);

/**
 * Section descriptions
 */
$lang['sms_ui_descriptions']=array(
    'select_rule' => 'General info about rules',
    'select_design' => 'Hier bestimmen Sie das generelle Design und Layout für die Message. Im Visuellen Editor können Sie später noch Texte und Farben individuell anpassen. Achten Sie hier insbesondere darauf, welche Zielgruppe Sie ansprechen möchten und auf welchen Geräten die Message ausgespielt werden soll.',
    'message_config' =>'Nehmen Sie hier weitere Einstellungen an Ihrem Layout vor. Im Visuellen Editor können Sie später noch Texte und Farben individuell anpassen. ',
    'web_integrate_1' => 'Geben Sie hier die URL der Seite ein, auf der die Message ausgespielt werden soll.',
    'web_integrate_2' => 'Sie können dazu die Seite im Browser öffnen und die URL aus der Adresszeile kopieren.',
    'url_example' => 'Beispiel: http://www.mylandingpage.com oder http://www.mylandingpage.com/article.jsp?id=4711',
    'duration_display_before'=>'Message wird angezeigt nach',
    'duration_display_after' => "Sekunden",
    'show_full_screen' => 'Vorschau im Vollbildmodus anzeigen',
    'preview' => 'Vorschau',
    'preview_2'=>'(die hier gemachten Einstellungen sind erst später im Visuellen Editor zu sehen)'
);

$lang['sms_ui_messages']=array(
    'show_all' => 'Alle anzeigen', /* used for dropdowns */
    'no_matching_results' => 'Keine passenden Layouts', /* used for group and template filtering */
    'powered_by_1' => 'powered by etracker',
    'powered_by_2' => 'Hinweis entfernen?'

);

/*sms rules*/
$lang['sms rule exit_intent'] = "Exit Intent";
$lang['sms rule always_on'] = "Always On";
$lang['sms rule greeter'] = "Greeter";
$lang['sms rule attn_grabber'] = "Attention Grabber";
/*sms types*/
$lang['sms type popup'] = "Popup";
$lang['sms type message_bar'] = "Message Bar";
$lang['sms type slider'] = "Slider";
$lang['sms type embedded_box'] = "Embedded Box";
$lang['sms type floating_ad'] = "Floating Ad";
$lang['sms type peel_away'] = "Peel Away";
/*sms content type*/
$lang['sms content tip'] = "Hinweis";
$lang['sms content teaser'] = "Produkt-Teaser";
$lang['sms content sale'] = "Aktion/Angebot";
$lang['sms content optin'] = "Newsletter-Anmeldung";
$lang['sms content callback'] = "Rückrufbitte";
$lang['sms content download'] = "Download-Formular";

/*description for templates*/
$lang['sms template descriptions'] = array(
    'Custom' => "Binden Sie Ihre eigenes Popup per iframe ein.",
    'Customimg' => "Smart Message mit vollflächiger Grafik.",
    'Headline' => "Headline",
    'Subline' => "Subline",
    'Button' => "Button",
    'Two Buttons' => "Zwei Buttons",
    'Three Sublines' => "Drei Sublines",
    'Download Icon' => "Download-Grafik",
    'Download-URL' => "Download-URL",
    'URL' => "URL",
    'Textfield' => "Email-Eingabe",
    'Video Icon' => "Video-Grafik",
    'Video-URL' => "Video-URL",
    'Image' => "Bild",
);
/***/
/* Into Descriptions for specific templates */
//$lang['sms_intro_t1_01_Custom_Popup'] = "Beschreibung für das <b>Custom-Popup</b>.";
$lang['sms_intro_t1_02_Popup_Text_Mail_1'] = 'Hier nehmen Sie die Konfiguration Ihres Newsletter-Templates vor. <a href="https://www.etracker.com/blog/smart-message-newsletter-templates" target="_blank">In unserem Blog haben wir Ihnen zusammengestellt</a>, welche Anbieter wir aktuell unterstützen und wie Sie die nötigen Angaben zur Konfiguration der Smart Message erhalten.';
$lang['sms_intro_t2_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t3_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t4_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t5_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t1_01_Popup_Text_Bild_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t3_01_Popup_Text_Bild_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t4_01_Popup_Text_Bild_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t6_01_Popup_Text_Bild_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t1_04_MessageBar_Mail'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t2_04_MessageBar_Mail'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t3_04_MessageBar_Mail'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t4_04_MessageBar_Mail'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t5_04_MessageBar_Mail'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t6_04_MessageBar_Mail'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t1_01_Slider_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t2_01_Slider_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t3_01_Slider_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t1_06_Slider_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t3_06_Slider_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];

/* label text for template configuration */
$lang['sms template labels'] = array(
    'name of email provider' => "Email-Marketing-System:",
    'sms label btn url' => "URL des Call-to-Action Buttons:",
    'sms label form url' => "Action-URL des Email-Providers:",
    'sms ecircle_gid' => "gid:",
    'sms redirect url if ok' => "URL für Weiterleitung nach erfolgreicher Anmeldung:",
    'sms redirect url on error' => "URL für Weiterleitung im Fehlerfall:",
    'sms label target url' => "Ziel-URL:",
    'sms label image url' => "URL des Bildes:",
    'sms label download url' => "URL des Downloads:",
    'sms label download icon' => "URL der Download-Grafik:",
    'sms label video url' => "URL des Videos:",
    'sms label video icon' => "URL der Video-Grafik:",
    'sms label vertical alignment' => "Ausrichtung:",
    'sms label vertical align options' => "Mitte|_center;Oben|_top;Unten|_bottom",
    'sms label side positioning' => "Positionierung",
    'sms label side position options' => "Left|_left;Right|_right",
    'sms label height of custom popup' => "Höhe des Popups (Pixel):",
    'sms label width of custom popup' => "Breite des Popups (Pixel):"
);
