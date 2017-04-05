<?php

/**
 * Descriptions for UI rules
 */
$lang['sms_ui_rules'] = array(
    /* key must match "value" in config/sms.php */
    'exit_intent' => 'The visitor wishes to exit your page.',
    'greeter' => 'The visitor has just come to your page.',
    'attn_grabber' => 'The visitor is indecisive or inactive.',
    'always_on' => 'The message is displayed always, independent of the visitor\'s behaviour.'

);

/**
 * Section titles and headings
 */

$lang['sms_ui_headings'] = array(
    'select_trigger' => 'Determine the trigger',
    'select_design' => 'Create layout',
    'message_config' => 'Configure layout',
    'content_type' => 'Content Type',
    'message_type' => 'Display as',
    'select_group' => 'Select Design',
    'select_template' => 'Select Template',
    'web_integrate' => 'Define the URL',


);

/**
 * Steps buttons
 */
$lang['sms_ui_buttons'] = array(
    'rules_section_cancel' => 'Select project',
    'select_design' => $lang['sms_ui_headings']['select_design'],
    'return_to_rules' => 'Determine the trigger',
    'configure_message' => 'Configure layout',
    'return_to_design' => 'Create layout',
    'select_url' => 'Define the URL',
    'return_to_config' => 'Configure layout',
    'open_in_editor' => 'Visual Editor',
    'hide_preview'=> 'Click anywhere to hide preview',
    'save_edit_changes' => 'Save Changes', /* for edit mode in editor */
    'cancel_edit' => 'Cancel Edit SMS',
    'cancel_add_sms' => 'Cancel Add SMS' /* cancel "rules" section from editor */
);

/**
 * Section descriptions
 */
$lang['sms_ui_descriptions']=array(
    'select_rule' => 'General info about rules',
    'select_design' => 'You determine the basic design and layout for the message here. You can still use the Visual Editor later to adapt texts and colours at a later stage. Pay special attention to the target group you wish to address here and which devices the message should be played on.',
    'message_config' =>'Where you make the settings for the selected message. Other changes, such as in text or color you can use the visual editor ',
    'web_integrate_1' => 'Enter the URL of the page here on which the message should be played.',
    'web_integrate_2' => 'For this purpose, you can open the page in the browser and copy the URL from the address field.',
    'url_example' => 'Example: http://www.mylandingpage.com oder http://www.mylandingpage.com/article.jsp?id=4711',
    'duration_display_before'=>'The message is displayed after',
    'duration_display_after' => "seconds",
    'show_full_screen' => 'Display in full screen mode',
    'preview' => 'Preview',
    'preview_2'=>'(the settings made here can only be seen later in the Visual Editor)'
);

$lang['sms_ui_messages']=array(
    'show_all' => 'Show All', /* used for dropdowns */
    'no_matching_results' => 'No Matching Results', /* used for group and template filtering */
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
$lang['sms content tip'] = "Note";
$lang['sms content teaser'] = "Product teaser";
$lang['sms content sale'] = "Promotion/offer";
$lang['sms content optin'] = "Newsletter registration";
$lang['sms content callback'] = "Callback";
$lang['sms content download'] = "Download offer";

/*description for templates*/
$lang['sms template descriptions'] = array(
    'Custom' => "Embed your own popup within an Iframe",
    'Customimg' => "Smart message with full size image",
    'Headline' => "Headline",
    'Subline' => "Subline",
    'Button' => "Button",
    'Two Buttons' => "Two Buttons",
    'Three Sublines' => "Three Sublines",
    'Download Icon' => "Download Icon",
    'Download-URL' => "Download-URL",
    'URL' => "URL",
    'Textfield' => "Email Input",
    'Video Icon' => "Video Icon",
    'Video-URL' => "Video-URL",
    'Image' => "Image",
);
/***/

/* Intro description for specific templates */
$lang['sms_intro_t1_02_Popup_Text_Mail_1'] = 'Here you can configure your newsletter template. <a href="https://www.etracker.com/blog/smart-message-newsletter-templates" target="_blank">In our blog</a> we have prepared a list of all supported newsletter providers and their respective configuration manuals.';
$lang['sms_intro_t2_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t3_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t4_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t5_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t7_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t8_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
$lang['sms_intro_t9_02_Popup_Text_Mail_1'] = $lang['sms_intro_t1_02_Popup_Text_Mail_1'];
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

$lang['sms template labels'] = array(
    'name of email provider' => "Email Marketing System:",
    'sms label btn url' => "URL of the button link:",
    'sms label form url' => "Action URL of email provider:",
    'sms ecircle_gid' => "gid:",
    'sms redirect url if ok' => "URL of redirect on successful signup:",
    'sms redirect url on error' => "URL of redirect on error:",
    'sms label target url' => "Target URL:",
    'sms label image url' => "Image URL:",
    'sms label download url' => "URL of the file to download:",
    'sms label download icon' => "URL of the download icon:",
    'sms label video url' => "Video URL:",
    'sms label video icon' => "URL of the video icon:",
    'sms label vertical alignment' => "Vertical alignment:",
    'sms label vertical align options' => "Center|_center;Top|_top;Bottom|_bottom",
    'sms label side positioning' => "Positioning:",
    'sms label side position options' => "Left|_left;Right|_right",
    'sms label height of custom popup' => "Height of popup (pixel):",
    'sms label width of custom popup' => "Width of popup (pixel):"
);
