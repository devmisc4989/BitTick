<?php

$CI = &get_instance();
$CI->lang->load('sms-ui');
$ruleImagePath = base_ssl_url() . 'images/sms/rules/';

$config['sms_rules'] = array(
    array(
        'value' => 'exit_intent',
        'label' => $CI->lang->line('sms rule exit_intent'),
        'message_types' => array('slider', 'popup'),
        'thumbnail_url' => $ruleImagePath . 'exit-intent-rule-etracker.png'
    ),
    array(
        'value' => 'always_on',
        'label' => $CI->lang->line('sms rule always_on'),
        // 8/23/2014 charlie made message types match master message_types array
        //'message_types' => array('message_bar', 'slider', 'embeded-box', 'floating-ad', 'peel-away'),
        'message_types' => array('message_bar', 'slider'),
        'thumbnail_url' => $ruleImagePath . 'always-on-rule-etracker.png'
    ),
    array(
        'value' => 'greeter',
        'label' => $CI->lang->line('sms rule greeter'),
        'message_types' => array('message_bar', 'slider', 'popup'),
        'durations' => array(0, 1 ,5, 10, 20, 50, 100),
        'thumbnail_url' => $ruleImagePath . 'greeter-rule-etracker.png'
    ),
    array(
        'value' => 'attn_grabber',
        'label' => $CI->lang->line('sms rule attn_grabber'),
        'message_types' => array('message_bar', 'slider', 'popup'),
        'durations' => array(5, 10, 20, 50, 100),
        'thumbnail_url' => $ruleImagePath . 'attention-grabber-rule-etracker.png'
    ),
);
$config['message_types'] = array(
    array(
        'value' => 'popup',
        'label' => $CI->lang->line('sms type popup'),
    ),
    array(
        'value' => 'message_bar',
        'label' => $CI->lang->line('sms type message_bar'),
    ),
    array(
        'value' => 'slider',
        'label' => $CI->lang->line('sms type slider'),
    ),
    array(
        'value' => 'embedded_box',
        'label' => $CI->lang->line('sms type embedded_box'),
    ),
    array(
        'value' => 'floating_ad',
        'label' => $CI->lang->line('sms type floating_ad'),
    ),
    array(
        'value' => 'peel_away',
        'label' => $CI->lang->line('sms type peel_away'),
    ),
);
$config['content_types'] = array(
    array(
        'value' => 'tip',
        'label' => $CI->lang->line('sms content tip'),
    ),
    array(
        'value' => 'teaser',
        'label' => $CI->lang->line('sms content teaser'),
    ),
    array(
        'value' => 'sale',
        'label' => $CI->lang->line('sms content sale'),
    ),
    array(
        'value' => 'optin',
        'label' => $CI->lang->line('sms content optin'),
    ),
    array(
        'value' => 'callback',
        'label' => $CI->lang->line('sms content callback'),
    ),
    array(
        'value' => 'download',
        'label' => $CI->lang->line('sms content download'),
    ),
);

// 'tip;teaser;sale;optin;callback;download'

/*
 * ------------- CONFIGURATION DATA FOR IMPORT OF 	SMS_TEMPLATE DATABASE TABLE
 */

/* design/template groups
 * [0] = primary key in sms_template_group
 * [1] = name of css file to include in xml template
 */

$g0 = array(7, array('common_popup.css'));
$g1 = array(1, array('common_popup.css', 'design1.css'));
$g2 = array(2, array('common_popup.css', 'design2.css'));
$g3 = array(3, array('common_popup.css', 'design3.css'));
$g4 = array(4, array('common_popup.css', 'design4.css'));
$g5 = array(5, array('common_popup.css', 'design5.css'));
$g6 = array(6, array('common_popup.css', 'design6.css'));
$g7 = array(8, array('common_popup.css', 'design7.css'));
$g8 = array(9, array('common_popup.css', 'design8.css'));
$g9 = array(10, array('common_popup.css', 'design9.css'));
$g91 = array(1, array('common_messagebar.css', 'design91.css'));
$g92 = array(2, array('common_messagebar.css', 'design92.css'));
$g93 = array(3, array('common_messagebar.css', 'design93.css'));
$g94 = array(4, array('common_messagebar.css', 'design94.css'));
$g95 = array(5, array('common_messagebar.css', 'design95.css'));
$g96 = array(6, array('common_messagebar.css', 'design96.css'));
$g97 = array(8, array('common_messagebar.css', 'design97.css'));
$g98 = array(9, array('common_messagebar.css', 'design98.css'));
$g99 = array(10, array('common_messagebar.css', 'design99.css'));
$g991 = array(1, array('common_slider.css', 'design991.css'));
$g992 = array(2, array('common_slider.css', 'design992.css'));
$g993 = array(3, array('common_slider.css', 'design993.css'));
$g997 = array(8, array('common_slider.css', 'design997.css'));
$g999 = array(10, array('common_slider.css', 'design999.css'));
$g9991 = array(1, array('common_slider_img.css', 'design991.css'));
$g9993 = array(3, array('common_slider_img.css', 'design993.css'));
$g9998 = array(9, array('common_slider_img.css', 'design998.css'));

/****** POPUPS *********/
$t01_Custom_Popup = array(
    'xml' => 'custom_popup.xml',
    'content_types' => 'tip;teaser;sale;optin;callback;download',
    'description' => 'Custom',
    'sort_order' => 5,
    'message_type' => 'popup'
);
$t01_Custom_Popup_Img = array(
    'xml' => '00_Popup_Custom_Img.xml',
    'content_types' => 'tip;teaser;sale;optin;callback;download',
    'description' => 'Customimg',
    'sort_order' => 5,
    'message_type' => 'popup'
);

/*********** POPUPS WITHOUT AN IMAGE *************/
$t00_Popup_Text_Button_1 = array(
    'xml' => '00_Popup_Text_Button_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline',
    'sort_order' => 10,
    'message_type' => 'popup'
);
$t00_Popup_Text_Button_2 = array(
    'xml' => '00_Popup_Text_Button_2.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Button;URL',
    'sort_order' => 20,
    'message_type' => 'popup'
);
$t00_Popup_Text_Button_3 = array(
    'xml' => '00_Popup_Text_Button_3.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL',
    'sort_order' => 30,
    'message_type' => 'popup'
);
$t00_Popup_Text_Button_4 = array(
    'xml' => '00_Popup_Text_Button_4.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL',
    'sort_order' => 40,
    'message_type' => 'popup'
);
$t00_Popup_Text_Mail = array(
    'xml' => '00_Popup_Text_Mail.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button',
    'sort_order' => 50,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bullets = array(
    'xml' => '00_Popup_Text_Bullets.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Three Sublines;Button;URL',
    'sort_order' => 60,
    'message_type' => 'popup'
);
$t00_Popup_Text_Movie_1 = array(
    'xml' => '00_Popup_Text_Movie_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 70,
    'message_type' => 'popup'
);
$t00_Popup_Text_Movie_2 = array(
    'xml' => '00_Popup_Text_Movie_2.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 70,
    'message_type' => 'popup'
);
$t00_Popup_Text_Movie_3 = array(
    'xml' => '00_Popup_Text_Movie_3.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 70,
    'message_type' => 'popup'
);
$t00_Popup_Text_Movie_7 = array(
    'xml' => '00_Popup_Text_Movie_7.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 70,
    'message_type' => 'popup'
);
$t00_Popup_Text_Movie_9 = array(
    'xml' => '00_Popup_Text_Movie_9.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 70,
    'message_type' => 'popup'
);
$t00_Popup_Text_Download_1 = array(
    'xml' => '00_Popup_Text_Download_1.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 80,
    'message_type' => 'popup'
);
$t00_Popup_Text_Download_2 = array(
    'xml' => '00_Popup_Text_Download_2.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 80,
    'message_type' => 'popup'
);
$t00_Popup_Text_Download_3 = array(
    'xml' => '00_Popup_Text_Download_3.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 80,
    'message_type' => 'popup'
);
$t00_Popup_Text_Download_7 = array(
    'xml' => '00_Popup_Text_Download_7.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 80,
    'message_type' => 'popup'
);
$t00_Popup_Text_Download_9 = array(
    'xml' => '00_Popup_Text_Download_9.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 80,
    'message_type' => 'popup'
);

/*************** POPUPS WITH AN IMG AT THE RIGHT (FOR DESIGN 4 IT IS AT THE LEFT) *******************/
$t00_Popup_Text_Bild_Button_1_1 = array(
    'xml' => '00_Popup_Text_Bild_Button_1_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_1_2 = array(
    'xml' => '00_Popup_Text_Bild_Button_1_2.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_1_3 = array(
    'xml' => '00_Popup_Text_Bild_Button_1_3.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 110,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_1_4 = array(
    'xml' => '00_Popup_Text_Bild_Button_1_4.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 120,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_4_1 = array(
    'xml' => '00_Popup_Text_Bild_Button_4_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_4_2 = array(
    'xml' => '00_Popup_Text_Bild_Button_4_2.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_4_3 = array(
    'xml' => '00_Popup_Text_Bild_Button_4_3.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 110,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_4_4 = array(
    'xml' => '00_Popup_Text_Bild_Button_4_4.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 120,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_6_2 = array(
    'xml' => '00_Popup_Text_Bild_Button_6_2.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_6_3 = array(
    'xml' => '00_Popup_Text_Bild_Button_6_3.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 110,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_6_4 = array(
    'xml' => '00_Popup_Text_Bild_Button_6_4.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 120,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_8_1 = array(
    'xml' => '00_Popup_Text_Bild_Button_8_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_8_2 = array(
    'xml' => '00_Popup_Text_Bild_Button_8_2.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 90,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_8_3 = array(
    'xml' => '00_Popup_Text_Bild_Button_8_3.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 110,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Button_8_4 = array(
    'xml' => '00_Popup_Text_Bild_Button_8_4.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 120,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Mail_1 = array(
    'xml' => '00_Popup_Text_Bild_Mail_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button;Image',
    'sort_order' => 130,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Mail_4 = array(
    'xml' => '00_Popup_Text_Bild_Mail_4.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button;Image',
    'sort_order' => 130,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Mail_6 = array(
    'xml' => '00_Popup_Text_Bild_Mail_6.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button;Image',
    'sort_order' => 130,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Mail_8 = array(
    'xml' => '00_Popup_Text_Bild_Mail_8.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button;Image',
    'sort_order' => 130,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Bullets_1 = array(
    'xml' => '00_Popup_Text_Bild_Bullets_1.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Three Sublines;Button;URL;Image',
    'sort_order' => 140,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Bullets_4 = array(
    'xml' => '00_Popup_Text_Bild_Bullets_4.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Three Sublines;Button;URL;Image',
    'sort_order' => 140,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Bullets_6 = array(
    'xml' => '00_Popup_Text_Bild_Bullets_6.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Three Sublines;Button;URL;Image',
    'sort_order' => 140,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Bullets_8 = array(
    'xml' => '00_Popup_Text_Bild_Bullets_8.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Three Sublines;Button;URL;Image',
    'sort_order' => 140,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Movie_1 = array(
    'xml' => '00_Popup_Text_Bild_Movie_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 150,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Movie_3 = array(
    'xml' => '00_Popup_Text_Bild_Movie_3.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 150,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Movie_4 = array(
    'xml' => '00_Popup_Text_Bild_Movie_4.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 150,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Movie_6 = array(
    'xml' => '00_Popup_Text_Bild_Movie_6.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 150,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Movie_8 = array(
    'xml' => '00_Popup_Text_Bild_Movie_8.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 150,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Download_1 = array(
    'xml' => '00_Popup_Text_Bild_Download_1.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 160,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Download_3 = array(
    'xml' => '00_Popup_Text_Bild_Download_3.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 160,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Download_4 = array(
    'xml' => '00_Popup_Text_Bild_Download_4.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 160,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Download_6 = array(
    'xml' => '00_Popup_Text_Bild_Download_6.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 160,
    'message_type' => 'popup'
);
$t00_Popup_Text_Bild_Download_8 = array(
    'xml' => '00_Popup_Text_Bild_Download_8.xml',
    'content_types' => 'download',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 160,
    'message_type' => 'popup'
);


/****** SLIDER *********/
$t00_Slider_Button_1 = array(
    'xml' => '00_slider_Button_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline',
    'sort_order' => 110,
    'message_type' => 'slider'
);
$t00_Slider_Button_2 = array(
    'xml' => '00_slider_Button_2.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Button;URL',
    'sort_order' => 120,
    'message_type' => 'slider'
);
$t00_Slider_Button_3 = array(
    'xml' => '00_slider_Button_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Two Buttons;URL',
    'sort_order' => 130,
    'message_type' => 'slider'
);
$t00_Slider_Button_4= array(
    'xml' => '00_slider_Button_4.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Two Buttons;URL',
    'sort_order' => 140,
    'message_type' => 'slider'
);
$t00_Slider_Mail = array(
    'xml' => '00_slider_Mail.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button',
    'sort_order' => 150,
    'message_type' => 'slider'
);
$t00_Slider_Bullets = array(
    'xml' => '00_slider_Bullets.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Three Sublines;Button;URL',
    'sort_order' => 160,
    'message_type' => 'slider'
);
$t00_Slider_Movie_1 = array(
    'xml' => '00_slider_Movie_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 170,
    'message_type' => 'slider'
);
$t00_Slider_Movie_2 = array(
    'xml' => '00_slider_Movie_2.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 170,
    'message_type' => 'slider'
);
$t00_Slider_Movie_3 = array(
    'xml' => '00_slider_Movie_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 170,
    'message_type' => 'slider'
);
$t00_Slider_Movie_7 = array(
    'xml' => '00_slider_Movie_7.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 170,
    'message_type' => 'slider'
);
$t00_Slider_Movie_9 = array(
    'xml' => '00_slider_Movie_9.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL',
    'sort_order' => 170,
    'message_type' => 'slider'
);
$t00_Slider_Download_1 = array(
    'xml' => '00_slider_Download_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 180,
    'message_type' => 'slider'
);
$t00_Slider_Download_2 = array(
    'xml' => '00_slider_Download_2.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 180,
    'message_type' => 'slider'
);
$t00_Slider_Download_3 = array(
    'xml' => '00_slider_Download_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 180,
    'message_type' => 'slider'
);
$t00_Slider_Download_7 = array(
    'xml' => '00_slider_Download_7.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 180,
    'message_type' => 'slider'
);
$t00_Slider_Download_9 = array(
    'xml' => '00_slider_Download_9.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL',
    'sort_order' => 180,
    'message_type' => 'slider'
);

/****** SLIDER WITH IMAGES AT THE RIGHT *********/
$t00_Slider_Bild_Button_1_1 = array(
    'xml' => '00_slider_Bild_Button_1_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 190,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_1_2 = array(
    'xml' => '00_slider_Bild_Button_1_2.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Button;URL;Image',
    'sort_order' => 191,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_1_3 = array(
    'xml' => '00_slider_Bild_Button_1_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 192,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_1_4= array(
    'xml' => '00_slider_Bild_Button_1_4.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 193,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_8_1 = array(
    'xml' => '00_slider_Bild_Button_8_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Image',
    'sort_order' => 190,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_8_2 = array(
    'xml' => '00_slider_Bild_Button_8_2.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Button;URL;Image',
    'sort_order' => 191,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_8_3 = array(
    'xml' => '00_slider_Bild_Button_8_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 192,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Button_8_4= array(
    'xml' => '00_slider_Bild_Button_8_4.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Subline;Two Buttons;URL;Image',
    'sort_order' => 193,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Mail_1 = array(
    'xml' => '00_slider_Bild_Mail_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button;Image',
    'sort_order' => 194,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Mail_8 = array(
    'xml' => '00_slider_Bild_Mail_8.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button;Image',
    'sort_order' => 194,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Bullets_1 = array(
    'xml' => '00_slider_Bild_Bullets_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Three Sublines;Button;URL;Image',
    'sort_order' => 195,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Bullets_8 = array(
    'xml' => '00_slider_Bild_Bullets_8.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Three Sublines;Button;URL;Image',
    'sort_order' => 195,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Movie_1_1 = array(
    'xml' => '00_slider_Bild_Movie_1_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 196,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Movie_1_3 = array(
    'xml' => '00_slider_Bild_Movie_1_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 196,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Movie_8_1 = array(
    'xml' => '00_slider_Bild_Movie_8_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Video Icon;Video-URL;Image',
    'sort_order' => 196,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Download_1_1 = array(
    'xml' => '00_slider_Bild_Download_1_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 197,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Download_1_3 = array(
    'xml' => '00_slider_Bild_Download_1_3.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 197,
    'message_type' => 'slider'
);
$t00_Slider_Bild_Download_8_1 = array(
    'xml' => '00_slider_Bild_Download_8_1.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Download Icon;Download-URL;Image',
    'sort_order' => 197,
    'message_type' => 'slider'
);

/****** MESSAGE BARS *********/
$t00_MessageBar_Button_1= array(
    'xml' => '00_MessageBar_Button_1.xml',
    'content_types' => 'tip;teaser;sale',
    'description' => 'Headline;Subline',
    'sort_order' => 200,
    'message_type' => 'message_bar'
);
$t00_MessageBar_Button_2 = array(
    'xml' => '00_MessageBar_Button_2.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Button;URL',
    'sort_order' => 210,
    'message_type' => 'message_bar'
);
$t00_MessageBar_Button_3 = array(
    'xml' => '00_MessageBar_Button_3.xml',
    'content_types' => 'tip;teaser;sale;download',
    'description' => 'Headline;Subline;Two Buttons;URL',
    'sort_order' => 220,
    'message_type' => 'message_bar'
);
$t00_MessageBar_Mail = array(
    'xml' => '00_MessageBar_Mail.xml',
    'content_types' => 'optin',
    'description' => 'Headline;Textfield;Button',
    'sort_order' => 230,
    'message_type' => 'message_bar'
);

/*
 * full template list
 * [0] = name/internal ID of template
 * [1] = master template
 * [2] = design
 * [3] = filename of thumbnail
 * [4] = filename of preview
 * [5] = semicolon-separated list of userplan-IDs for which the template is invisible (optional)
 */

$templates = array(
    array('t1_01_Custom_Popup', $t01_Custom_Popup, $g0, 'Custom_Popup_klein.png', 'Custom_Popup.png','610'),
    array('t1_01_Custom_Img_Popup', $t01_Custom_Popup_Img, $g0, 'Custom_Popup_Img_klein.png', 'Custom_Popup_Img.png','610'),
    /* popups 1 */
    array('t1_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g1, 'Temp1_01_Popup_Text_Button_1_klein.png', 'Temp1_01_Popup_Text_Button_1.png'),
    array('t2_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g2, 'Temp2_01_Popup_Text_Button_1_klein.png', 'Temp2_01_Popup_Text_Button_1.png'),
    array('t3_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g3, 'Temp3_01_Popup_Text_Button_1_klein.png', 'Temp3_01_Popup_Text_Button_1.png'),
    array('t4_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g4, 'Temp4_01_Popup_Text_Button_1_klein.png', 'Temp4_01_Popup_Text_Button_1.png'),
    array('t5_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g5, 'Temp5_01_Popup_Text_Button_1_klein.png', 'Temp5_01_Popup_Text_Button_1.png'),
    array('t7_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g7, 'Temp7_01_Popup_Text_Button_1_klein.png', 'Temp7_01_Popup_Text_Button_1.png'),
    array('t9_01_Popup_Text_Button_1', $t00_Popup_Text_Button_1, $g9, 'Temp9_01_Popup_Text_Button_1_klein.png', 'Temp9_01_Popup_Text_Button_1.png'),
    /* popups 2 */
    array('t1_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g1, 'Temp1_01_Popup_Text_Button_2_klein.png', 'Temp1_01_Popup_Text_Button_2.png'),
    array('t2_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g2, 'Temp2_01_Popup_Text_Button_2_klein.png', 'Temp2_01_Popup_Text_Button_2.png'),
    array('t3_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g3, 'Temp3_01_Popup_Text_Button_2_klein.png', 'Temp3_01_Popup_Text_Button_2.png'),
    array('t4_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g4, 'Temp4_01_Popup_Text_Button_2_klein.png', 'Temp4_01_Popup_Text_Button_2.png'),
    array('t5_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g5, 'Temp5_01_Popup_Text_Button_2_klein.png', 'Temp5_01_Popup_Text_Button_2.png'),
    array('t7_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g7, 'Temp7_01_Popup_Text_Button_2_klein.png', 'Temp7_01_Popup_Text_Button_2.png'),
    array('t9_01_Popup_Text_Button_2', $t00_Popup_Text_Button_2, $g9, 'Temp9_01_Popup_Text_Button_2_klein.png', 'Temp9_01_Popup_Text_Button_2.png'),
    /* popups 3 */
    array('t1_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g1, 'Temp1_01_Popup_Text_Button_3_klein.png', 'Temp1_01_Popup_Text_Button_3.png'),
    array('t2_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g2, 'Temp2_01_Popup_Text_Button_3_klein.png', 'Temp2_01_Popup_Text_Button_3.png'),
    array('t3_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g3, 'Temp3_01_Popup_Text_Button_3_klein.png', 'Temp3_01_Popup_Text_Button_3.png'),
    array('t4_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g4, 'Temp4_01_Popup_Text_Button_3_klein.png', 'Temp4_01_Popup_Text_Button_3.png'),
    array('t5_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g5, 'Temp5_01_Popup_Text_Button_3_klein.png', 'Temp5_01_Popup_Text_Button_3.png'),
    array('t7_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g7, 'Temp7_01_Popup_Text_Button_3_klein.png', 'Temp7_01_Popup_Text_Button_3.png'),
    array('t9_01_Popup_Text_Button_3', $t00_Popup_Text_Button_3, $g9, 'Temp9_01_Popup_Text_Button_3_klein.png', 'Temp9_01_Popup_Text_Button_3.png'),
    /* popups 4 */
    array('t1_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g1, 'Temp1_01_Popup_Text_Button_4_klein.png', 'Temp1_01_Popup_Text_Button_4.png'),
    array('t2_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g2, 'Temp2_01_Popup_Text_Button_4_klein.png', 'Temp2_01_Popup_Text_Button_4.png'),
    array('t3_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g3, 'Temp3_01_Popup_Text_Button_4_klein.png', 'Temp3_01_Popup_Text_Button_4.png'),
    array('t4_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g4, 'Temp4_01_Popup_Text_Button_4_klein.png', 'Temp4_01_Popup_Text_Button_4.png'),
    array('t5_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g5, 'Temp5_01_Popup_Text_Button_4_klein.png', 'Temp5_01_Popup_Text_Button_4.png'),
    array('t7_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g7, 'Temp7_01_Popup_Text_Button_4_klein.png', 'Temp7_01_Popup_Text_Button_4.png'),
    array('t9_01_Popup_Text_Button_4', $t00_Popup_Text_Button_4, $g9, 'Temp9_01_Popup_Text_Button_4_klein.png', 'Temp9_01_Popup_Text_Button_4.png'),
    /* popups 5 */
    array('t1_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g1, 'Temp1_02_Popup_Text_Mail_1_klein.png', 'Temp1_02_Popup_Text_Mail_1.png'),
    array('t2_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g2, 'Temp2_02_Popup_Text_Mail_1_klein.png', 'Temp2_02_Popup_Text_Mail_1.png'),
    array('t3_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g3, 'Temp3_02_Popup_Text_Mail_1_klein.png', 'Temp3_02_Popup_Text_Mail_1.png'),
    array('t4_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g4, 'Temp4_02_Popup_Text_Mail_1_klein.png', 'Temp4_02_Popup_Text_Mail_1.png'),
    array('t5_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g5, 'Temp5_02_Popup_Text_Mail_1_klein.png', 'Temp5_02_Popup_Text_Mail_1.png'),
    array('t7_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g7, 'Temp7_02_Popup_Text_Mail_1_klein.png', 'Temp7_02_Popup_Text_Mail_1.png'),
    array('t9_02_Popup_Text_Mail_1', $t00_Popup_Text_Mail, $g9, 'Temp9_02_Popup_Text_Mail_1_klein.png', 'Temp9_02_Popup_Text_Mail_1.png'),
    /* popups 6 */
    array('t1_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g1, 'Temp1_03_Popup_Text_Bullets_klein.png', 'Temp1_03_Popup_Text_Bullets.png'),
    array('t2_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g2, 'Temp2_03_Popup_Text_Bullets_klein.png', 'Temp2_03_Popup_Text_Bullets.png'),
    array('t3_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g3, 'Temp3_03_Popup_Text_Bullets_klein.png', 'Temp3_03_Popup_Text_Bullets.png'),
    array('t4_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g4, 'Temp4_03_Popup_Text_Bullets_klein.png', 'Temp4_03_Popup_Text_Bullets.png'),
    array('t5_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g5, 'Temp5_03_Popup_Text_Bullets_klein.png', 'Temp5_03_Popup_Text_Bullets.png'),
    array('t7_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g7, 'Temp7_03_Popup_Text_Bullets_klein.png', 'Temp7_03_Popup_Text_Bullets.png'),
    array('t9_03_Popup_Text_Bullets', $t00_Popup_Text_Bullets, $g9, 'Temp9_03_Popup_Text_Bullets_klein.png', 'Temp9_03_Popup_Text_Bullets.png'),
    /* popups 7 */
    array('t1_04_Popup_Text_Download', $t00_Popup_Text_Download_1, $g1, 'Temp1_04_Popup_Text_Download_klein.png', 'Temp1_04_Popup_Text_Download.png'),
    array('t2_04_Popup_Text_Download', $t00_Popup_Text_Download_2, $g2, 'Temp2_04_Popup_Text_Download_klein.png', 'Temp2_04_Popup_Text_Download.png'),
    array('t3_04_Popup_Text_Download', $t00_Popup_Text_Download_3, $g3, 'Temp3_04_Popup_Text_Download_klein.png', 'Temp3_04_Popup_Text_Download.png'),
    array('t4_04_Popup_Text_Download', $t00_Popup_Text_Download_1, $g4, 'Temp4_04_Popup_Text_Download_klein.png', 'Temp4_04_Popup_Text_Download.png'),
    array('t5_04_Popup_Text_Download', $t00_Popup_Text_Download_1, $g5, 'Temp5_04_Popup_Text_Download_klein.png', 'Temp5_04_Popup_Text_Download.png'),
    array('t7_04_Popup_Text_Download', $t00_Popup_Text_Download_7, $g7, 'Temp7_04_Popup_Text_Download_klein.png', 'Temp7_04_Popup_Text_Download.png'),
    array('t9_04_Popup_Text_Download', $t00_Popup_Text_Download_9, $g9, 'Temp9_04_Popup_Text_Download_klein.png', 'Temp9_04_Popup_Text_Download.png'),
    /* popups 8 */
    array('t1_04_Popup_Text_Movie', $t00_Popup_Text_Movie_1, $g1, 'Temp1_04_Popup_Text_Movie_klein.png', 'Temp1_04_Popup_Text_Movie.png'),
    array('t2_04_Popup_Text_Movie', $t00_Popup_Text_Movie_2, $g2, 'Temp2_04_Popup_Text_Movie_klein.png', 'Temp2_04_Popup_Text_Movie.png'),
    array('t3_04_Popup_Text_Movie', $t00_Popup_Text_Movie_3, $g3, 'Temp3_04_Popup_Text_Movie_klein.png', 'Temp3_04_Popup_Text_Movie.png'),
    array('t4_04_Popup_Text_Movie', $t00_Popup_Text_Movie_1, $g4, 'Temp4_04_Popup_Text_Movie_klein.png', 'Temp4_04_Popup_Text_Movie.png'),
    array('t5_04_Popup_Text_Movie', $t00_Popup_Text_Movie_1, $g5, 'Temp5_04_Popup_Text_Movie_klein.png', 'Temp5_04_Popup_Text_Movie.png'),
    array('t7_04_Popup_Text_Movie', $t00_Popup_Text_Movie_7, $g7, 'Temp7_04_Popup_Text_Movie_klein.png', 'Temp7_04_Popup_Text_Movie.png'),
    array('t9_04_Popup_Text_Movie', $t00_Popup_Text_Movie_9, $g9, 'Temp9_04_Popup_Text_Movie_klein.png', 'Temp9_04_Popup_Text_Movie.png'),
    /* Right side img popups 1 */
    array('t1_01_Popup_Text_Bild_Button_1', $t00_Popup_Text_Bild_Button_1_1, $g1, 'Temp1_05_Popup_Text_Bild_Button_1_klein.png', 'Temp1_05_Popup_Text_Bild_Button_1.png'),
    array('t3_01_Popup_Text_Bild_Button_1', $t00_Popup_Text_Bild_Button_1_1, $g3, 'Temp3_05_Popup_Text_Bild_Button_1_klein.png', 'Temp3_05_Popup_Text_Bild_Button_1.png'),
    array('t4_05_Popup_Text_Bild_Button_1', $t00_Popup_Text_Bild_Button_4_1, $g4, 'Temp4_05_Popup_Text_Bild_Button_1_klein.png', 'Temp4_05_Popup_Text_Bild_Button_1.png'),
    array('t8_01_Popup_Text_Bild_Button_1', $t00_Popup_Text_Bild_Button_8_1, $g8, 'Temp8_01_Popup_Text_Button_1_klein.png', 'Temp8_01_Popup_Text_Button_1.png'),
    /* Right side img popups 2 */
    array('t1_01_Popup_Text_Bild_Button_2', $t00_Popup_Text_Bild_Button_1_2, $g1, 'Temp1_05_Popup_Text_Bild_Button_2_klein.png', 'Temp1_05_Popup_Text_Bild_Button_2.png'),
    array('t3_01_Popup_Text_Bild_Button_2', $t00_Popup_Text_Bild_Button_1_2, $g3, 'Temp3_05_Popup_Text_Bild_Button_2_klein.png', 'Temp3_05_Popup_Text_Bild_Button_2.png'),
    array('t4_05_Popup_Text_Bild_Button_2', $t00_Popup_Text_Bild_Button_4_2, $g4, 'Temp4_05_Popup_Text_Bild_Button_2_klein.png', 'Temp4_05_Popup_Text_Bild_Button_2.png'),
    array('t6_05_Popup_Text_Bild_Button_2', $t00_Popup_Text_Bild_Button_6_2, $g6, 'Temp6_05_Popup_Text_Bild_Button_2_klein.png', 'Temp6_05_Popup_Text_Bild_Button_2.png'),
    array('t8_01_Popup_Text_Bild_Button_2', $t00_Popup_Text_Bild_Button_8_2, $g8, 'Temp8_01_Popup_Text_Button_2_klein.png', 'Temp8_01_Popup_Text_Button_2.png'),
    /* Right side img popups 3 */
    array('t1_01_Popup_Text_Bild_Button_3', $t00_Popup_Text_Bild_Button_1_3, $g1, 'Temp1_05_Popup_Text_Bild_Button_3_klein.png', 'Temp1_05_Popup_Text_Bild_Button_3.png'),
    array('t3_01_Popup_Text_Bild_Button_3', $t00_Popup_Text_Bild_Button_1_3, $g3, 'Temp3_05_Popup_Text_Bild_Button_3_klein.png', 'Temp3_05_Popup_Text_Bild_Button_3.png'),
    array('t4_05_Popup_Text_Bild_Button_3', $t00_Popup_Text_Bild_Button_4_3, $g4, 'Temp4_05_Popup_Text_Bild_Button_3_klein.png', 'Temp4_05_Popup_Text_Bild_Button_3.png'),
    array('t6_05_Popup_Text_Bild_Button_3', $t00_Popup_Text_Bild_Button_6_3, $g6, 'Temp6_05_Popup_Text_Bild_Button_3_klein.png', 'Temp6_05_Popup_Text_Bild_Button_3.png'),
    array('t8_01_Popup_Text_Bild_Button_3', $t00_Popup_Text_Bild_Button_8_3, $g8, 'Temp8_01_Popup_Text_Button_3_klein.png', 'Temp8_01_Popup_Text_Button_3.png'),
    /* Right side img popups 4 */
    array('t1_01_Popup_Text_Bild_Button_4', $t00_Popup_Text_Bild_Button_1_4, $g1, 'Temp1_05_Popup_Text_Bild_Button_4_klein.png', 'Temp1_05_Popup_Text_Bild_Button_4.png'),
    array('t3_01_Popup_Text_Bild_Button_4', $t00_Popup_Text_Bild_Button_1_4, $g3, 'Temp3_05_Popup_Text_Bild_Button_4_klein.png', 'Temp3_05_Popup_Text_Bild_Button_4.png'),
    array('t4_05_Popup_Text_Bild_Button_4', $t00_Popup_Text_Bild_Button_4_4, $g4, 'Temp4_05_Popup_Text_Bild_Button_4_klein.png', 'Temp4_05_Popup_Text_Bild_Button_4.png'),
    array('t6_05_Popup_Text_Bild_Button_4', $t00_Popup_Text_Bild_Button_6_4, $g6, 'Temp6_05_Popup_Text_Bild_Button_4_klein.png', 'Temp6_05_Popup_Text_Bild_Button_4.png'),
    array('t8_01_Popup_Text_Bild_Button_4', $t00_Popup_Text_Bild_Button_8_4, $g8, 'Temp8_01_Popup_Text_Button_4_klein.png', 'Temp8_01_Popup_Text_Button_4.png'),
    /* Right side img popups 5 */
    array('t1_01_Popup_Text_Bild_Mail_1', $t00_Popup_Text_Bild_Mail_1, $g1, 'Temp1_06_Popup_Text_Bild_Mail_1_klein.png', 'Temp1_06_Popup_Text_Bild_Mail_1.png'),
    array('t3_01_Popup_Text_Bild_Mail_1', $t00_Popup_Text_Bild_Mail_1, $g3, 'Temp3_06_Popup_Text_Bild_Mail_1_klein.png', 'Temp3_06_Popup_Text_Bild_Mail_1.png'),
    array('t4_06_Popup_Text_Bild_Mail_1', $t00_Popup_Text_Bild_Mail_4, $g4, 'Temp4_06_Popup_Text_Bild_Mail_1_klein.png', 'Temp4_06_Popup_Text_Bild_Mail_1.png'),
    array('t6_06_Popup_Text_Bild_Mail_1', $t00_Popup_Text_Bild_Mail_6, $g6, 'Temp6_06_Popup_Text_Bild_Mail_1_klein.png', 'Temp6_06_Popup_Text_Bild_Mail_1.png'),
    array('t8_02_Popup_Text_Bild_Mail_1', $t00_Popup_Text_Bild_Mail_8, $g8, 'Temp8_02_Popup_Text_Mail_1_klein.png', 'Temp8_02_Popup_Text_Mail_1.png'),
    /* Right side img popups 6 */
    array('t1_01_Popup_Text_Bild_Bullets', $t00_Popup_Text_Bild_Bullets_1, $g1, 'Temp1_07_Popup_Text_Bild_Bullets_klein.png', 'Temp1_07_Popup_Text_Bild_Bullets.png'),
    array('t3_01_Popup_Text_Bild_Bullets', $t00_Popup_Text_Bild_Bullets_1, $g3, 'Temp3_07_Popup_Text_Bild_Bullets_klein.png', 'Temp3_07_Popup_Text_Bild_Bullets.png'),
    array('t4_07_Popup_Text_Bild_Bullets', $t00_Popup_Text_Bild_Bullets_4, $g4, 'Temp4_07_Popup_Text_Bild_Bullets_klein.png', 'Temp4_07_Popup_Text_Bild_Bullets.png'),
    array('t6_07_Popup_Text_Bild_Bullets', $t00_Popup_Text_Bild_Bullets_6, $g6, 'Temp6_07_Popup_Text_Bild_Bullets_klein.png', 'Temp6_07_Popup_Text_Bild_Bullets.png'),
    array('t8_03_Popup_Text_Bild_Bullets', $t00_Popup_Text_Bild_Bullets_8, $g8, 'Temp8_03_Popup_Text_Bullets_klein.png', 'Temp8_03_Popup_Text_Bullets.png'),
    /* Right side img popups 7 */
    array('t1_01_Popup_Text_Bild_Movie', $t00_Popup_Text_Bild_Movie_1, $g1, 'Temp1_08_Popup_Text_Bild_Movie_klein.png', 'Temp1_08_Popup_Text_Bild_Movie.png'),
    array('t3_01_Popup_Text_Bild_Movie', $t00_Popup_Text_Bild_Movie_3, $g3, 'Temp3_08_Popup_Text_Bild_Movie_klein.png', 'Temp3_08_Popup_Text_Bild_Movie.png'),
    array('t4_08_Popup_Text_Bild_Movie', $t00_Popup_Text_Bild_Movie_4, $g4, 'Temp4_08_Popup_Text_Bild_Movie_klein.png', 'Temp4_08_Popup_Text_Bild_Movie.png'),
    array('t6_08_Popup_Text_Bild_Movie', $t00_Popup_Text_Bild_Movie_6, $g6, 'Temp6_08_Popup_Text_Bild_Movie_klein.png', 'Temp6_08_Popup_Text_Bild_Movie.png'),
    array('t8_04_Popup_Text_Bild_Movie', $t00_Popup_Text_Bild_Movie_8, $g8, 'Temp8_04_Popup_Text_Movie_klein.png', 'Temp8_04_Popup_Text_Movie.png'),
    /* Right side img popups 8 */
    array('t1_01_Popup_Text_Bild_Download', $t00_Popup_Text_Bild_Download_1, $g1, 'Temp1_08_Popup_Text_Bild_Download_klein.png', 'Temp1_08_Popup_Text_Bild_Download.png'),
    array('t3_01_Popup_Text_Bild_Download', $t00_Popup_Text_Bild_Download_3, $g3, 'Temp3_08_Popup_Text_Bild_Download_klein.png', 'Temp3_08_Popup_Text_Bild_Download.png'),
    array('t4_08_Popup_Text_Bild_Download', $t00_Popup_Text_Bild_Download_4, $g4, 'Temp4_08_Popup_Text_Bild_Download_klein.png', 'Temp4_08_Popup_Text_Bild_Download.png'),
    array('t6_08_Popup_Text_Bild_Download', $t00_Popup_Text_Bild_Download_6, $g6, 'Temp6_08_Popup_Text_Bild_Download_klein.png', 'Temp6_04_Popup_Text_Bild_Download.png'),
    array('t8_04_Popup_Text_Bild_Download', $t00_Popup_Text_Bild_Download_8, $g8, 'Temp8_04_Popup_Text_Download_klein.png', 'Temp8_04_Popup_Text_Download.png'),
    /* Message bar 1 */
    array('t1_01_MessageBar_Text', $t00_MessageBar_Button_1, $g91, 'Temp1_01_MessageBar_Text_klein.png', 'Temp1_01_MessageBar_Text.png'),
    array('t2_01_MessageBar_Text', $t00_MessageBar_Button_1, $g92, 'Temp2_01_MessageBar_Text_klein.png', 'Temp2_01_MessageBar_Text.png'),
    array('t3_01_MessageBar_Text', $t00_MessageBar_Button_1, $g93, 'Temp3_01_MessageBar_Text_klein.png', 'Temp3_01_MessageBar_Text.png'),
    array('t4_01_MessageBar_Text', $t00_MessageBar_Button_1, $g94, 'Temp4_01_MessageBar_Text_klein.png', 'Temp4_01_MessageBar_Text.png'),
    array('t5_01_MessageBar_Text', $t00_MessageBar_Button_1, $g95, 'Temp5_01_MessageBar_Text_klein.png', 'Temp5_01_MessageBar_Text.png'),
    array('t6_01_MessageBar_Text', $t00_MessageBar_Button_1, $g96, 'Temp6_01_MessageBar_Text_klein.png', 'Temp6_01_MessageBar_Text.png'),
    array('t7_01_MessageBar_Text', $t00_MessageBar_Button_1, $g97, 'Temp7_01_MessageBar_Text_klein.png', 'Temp7_01_MessageBar_Text.png'),
    array('t8_01_MessageBar_Text', $t00_MessageBar_Button_1, $g98, 'Temp8_01_MessageBar_Text_klein.png', 'Temp8_01_MessageBar_Text.png'),
    array('t9_01_MessageBar_Text', $t00_MessageBar_Button_1, $g99, 'Temp9_01_MessageBar_Text_klein.png', 'Temp9_01_MessageBar_Text.png'),
    /* Message bar 2 */
    array('t1_02_MessageBar_Button', $t00_MessageBar_Button_2, $g91, 'Temp1_02_MessageBar_Button_klein.png', 'Temp1_02_MessageBar_Button.png'),
    array('t2_02_MessageBar_Button', $t00_MessageBar_Button_2, $g92, 'Temp2_02_MessageBar_Button_klein.png', 'Temp2_02_MessageBar_Button.png'),
    array('t3_02_MessageBar_Button', $t00_MessageBar_Button_2, $g93, 'Temp3_02_MessageBar_Button_klein.png', 'Temp3_02_MessageBar_Button.png'),
    array('t4_02_MessageBar_Button', $t00_MessageBar_Button_2, $g94, 'Temp4_02_MessageBar_Button_klein.png', 'Temp4_02_MessageBar_Button.png'),
    array('t5_02_MessageBar_Button', $t00_MessageBar_Button_2, $g95, 'Temp5_02_MessageBar_Button_klein.png', 'Temp5_02_MessageBar_Button.png'),
    array('t6_02_MessageBar_Button', $t00_MessageBar_Button_2, $g96, 'Temp6_02_MessageBar_Button_klein.png', 'Temp6_02_MessageBar_Button.png'),
    array('t7_02_MessageBar_Button', $t00_MessageBar_Button_2, $g97, 'Temp7_02_MessageBar_Button_klein.png', 'Temp7_02_MessageBar_Button.png'),
    array('t8_02_MessageBar_Button', $t00_MessageBar_Button_2, $g98, 'Temp8_02_MessageBar_Button_klein.png', 'Temp8_02_MessageBar_Button.png'),
    array('t9_02_MessageBar_Button', $t00_MessageBar_Button_2, $g99, 'Temp9_02_MessageBar_Button_klein.png', 'Temp9_02_MessageBar_Button.png'),
    /* Message bar 3 */
    array('t1_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g91, 'Temp1_03_MessageBar_2Button_klein.png', 'Temp1_03_MessageBar_2Button.png'),
    array('t2_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g92, 'Temp2_03_MessageBar_2Button_klein.png', 'Temp2_03_MessageBar_2Button.png'),
    array('t3_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g93, 'Temp3_03_MessageBar_2Button_klein.png', 'Temp3_03_MessageBar_2Button.png'),
    array('t4_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g94, 'Temp4_03_MessageBar_2Button_klein.png', 'Temp4_03_MessageBar_2Button.png'),
    array('t5_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g95, 'Temp5_03_MessageBar_2Button_klein.png', 'Temp5_03_MessageBar_2Button.png'),
    array('t6_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g96, 'Temp6_03_MessageBar_2Button_klein.png', 'Temp6_03_MessageBar_2Button.png'),
    array('t7_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g97, 'Temp7_03_MessageBar_2Button_klein.png', 'Temp7_03_MessageBar_2Button.png'),
    array('t8_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g98, 'Temp8_03_MessageBar_2Button_klein.png', 'Temp8_03_MessageBar_2Button.png'),
    array('t9_03_MessageBar_2Button', $t00_MessageBar_Button_3, $g99, 'Temp9_03_MessageBar_2Button_klein.png', 'Temp9_03_MessageBar_2Button.png'),
    /* Message bar 4 */
    array('t1_04_MessageBar_Mail', $t00_MessageBar_Mail, $g91, 'Temp1_04_MessageBar_Mail_klein.png', 'Temp1_04_MessageBar_Mail.png'),
    array('t2_04_MessageBar_Mail', $t00_MessageBar_Mail, $g92, 'Temp2_04_MessageBar_Mail_klein.png', 'Temp2_04_MessageBar_Mail.png'),
    array('t3_04_MessageBar_Mail', $t00_MessageBar_Mail, $g93, 'Temp3_04_MessageBar_Mail_klein.png', 'Temp3_04_MessageBar_Mail.png'),
    array('t4_04_MessageBar_Mail', $t00_MessageBar_Mail, $g94, 'Temp4_04_MessageBar_Mail_klein.png', 'Temp4_04_MessageBar_Mail.png'),
    array('t5_04_MessageBar_Mail', $t00_MessageBar_Mail, $g95, 'Temp5_04_MessageBar_Mail_klein.png', 'Temp5_04_MessageBar_Mail.png'),
    array('t6_04_MessageBar_Mail', $t00_MessageBar_Mail, $g96, 'Temp6_04_MessageBar_Mail_klein.png', 'Temp6_04_MessageBar_Mail.png'),
    array('t8_04_MessageBar_Mail', $t00_MessageBar_Mail, $g98, 'Temp8_04_MessageBar_Mail_klein.png', 'Temp8_04_MessageBar_Mail.png'),
    /* Slider 1 */
    array('t1_01_Slider_Text_Button_1', $t00_Slider_Button_1, $g991, 'Temp1_01_Slider_Text_Button_1_klein.png', 'Temp1_01_Slider_Text_Button_1.png'),
    array('t2_01_Slider_Text_Button_1', $t00_Slider_Button_1, $g992, 'Temp2_01_Slider_Text_Button_1_klein.png', 'Temp2_01_Slider_Text_Button_1.png'),
    array('t3_01_Slider_Text_Button_1', $t00_Slider_Button_1, $g993, 'Temp3_01_Slider_Text_Button_1_klein.png', 'Temp3_01_Slider_Text_Button_1.png'),
    array('t7_01_Slider_Text_Button_1', $t00_Slider_Button_1, $g997, 'Temp7_01_Slider_Text_Button_1_klein.png', 'Temp7_01_Slider_Text_Button_1.png'),
    array('t9_01_Slider_Text_Button_1', $t00_Slider_Button_1, $g999, 'Temp9_01_Slider_Text_Button_1_klein.png', 'Temp9_01_Slider_Text_Button_1.png'),
    /* Slider 2 */
    array('t1_01_Slider_Text_Button_2', $t00_Slider_Button_2, $g991, 'Temp1_01_Slider_Text_Button_2_klein.png', 'Temp1_01_Slider_Text_Button_2.png'),
    array('t2_01_Slider_Text_Button_2', $t00_Slider_Button_2, $g992, 'Temp2_01_Slider_Text_Button_2_klein.png', 'Temp2_01_Slider_Text_Button_2.png'),
    array('t3_01_Slider_Text_Button_2', $t00_Slider_Button_2, $g993, 'Temp3_01_Slider_Text_Button_2_klein.png', 'Temp3_01_Slider_Text_Button_2.png'),
    array('t7_01_Slider_Text_Button_2', $t00_Slider_Button_2, $g997, 'Temp7_01_Slider_Text_Button_2_klein.png', 'Temp7_01_Slider_Text_Button_2.png'),
    array('t9_01_Slider_Text_Button_2', $t00_Slider_Button_2, $g999, 'Temp9_01_Slider_Text_Button_2_klein.png', 'Temp9_01_Slider_Text_Button_2.png'),
    /* Slider 3 */
    array('t1_01_Slider_Text_Button_3', $t00_Slider_Button_3, $g991, 'Temp1_01_Slider_Text_Button_3_klein.png', 'Temp1_01_Slider_Text_Button_3.png'),
    array('t2_01_Slider_Text_Button_3', $t00_Slider_Button_3, $g992, 'Temp2_01_Slider_Text_Button_3_klein.png', 'Temp2_01_Slider_Text_Button_3.png'),
    array('t3_01_Slider_Text_Button_3', $t00_Slider_Button_3, $g993, 'Temp3_01_Slider_Text_Button_3_klein.png', 'Temp3_01_Slider_Text_Button_3.png'),
    array('t7_01_Slider_Text_Button_3', $t00_Slider_Button_3, $g997, 'Temp7_01_Slider_Text_Button_3_klein.png', 'Temp7_01_Slider_Text_Button_3.png'),
    array('t9_01_Slider_Text_Button_3', $t00_Slider_Button_3, $g999, 'Temp9_01_Slider_Text_Button_3_klein.png', 'Temp9_01_Slider_Text_Button_3.png'),
    /* Slider 4 */
    array('t1_01_Slider_Text_Button_4', $t00_Slider_Button_4, $g991, 'Temp1_01_Slider_Text_Button_4_klein.png', 'Temp1_01_Slider_Text_Button_4.png'),
    array('t2_01_Slider_Text_Button_4', $t00_Slider_Button_4, $g992, 'Temp2_01_Slider_Text_Button_4_klein.png', 'Temp2_01_Slider_Text_Button_4.png'),
    array('t3_01_Slider_Text_Button_4', $t00_Slider_Button_4, $g993, 'Temp3_01_Slider_Text_Button_4_klein.png', 'Temp3_01_Slider_Text_Button_4.png'),
    array('t7_01_Slider_Text_Button_4', $t00_Slider_Button_4, $g997, 'Temp7_01_Slider_Text_Button_4_klein.png', 'Temp7_01_Slider_Text_Button_4.png'),
    array('t9_01_Slider_Text_Button_4', $t00_Slider_Button_4, $g999, 'Temp9_01_Slider_Text_Button_4_klein.png', 'Temp9_01_Slider_Text_Button_4.png'),
    /* Slider 5 */
    array('t1_02_Slider_Text_Mail_1', $t00_Slider_Mail, $g991, 'Temp1_02_Slider_Text_Mail_1_klein.png', 'Temp1_02_Slider_Text_Mail_1.png'),
    array('t2_02_Slider_Text_Mail_1', $t00_Slider_Mail, $g992, 'Temp2_02_Slider_Text_Mail_1_klein.png', 'Temp2_02_Slider_Text_Mail_1.png'),
    array('t3_02_Slider_Text_Mail_1', $t00_Slider_Mail, $g993, 'Temp3_02_Slider_Text_Mail_1_klein.png', 'Temp3_02_Slider_Text_Mail_1.png'),
    array('t7_02_Slider_Text_Mail_1', $t00_Slider_Mail, $g997, 'Temp7_02_Slider_Text_Mail_1_klein.png', 'Temp7_02_Slider_Text_Mail_1.png'),
    array('t9_02_Slider_Text_Mail_1', $t00_Slider_Mail, $g999, 'Temp9_02_Slider_Text_Mail_1_klein.png', 'Temp9_02_Slider_Text_Mail_1.png'),
    /* Slider 6 */
    array('t1_03_Slider_Text_Bullets', $t00_Slider_Bullets, $g991, 'Temp1_03_Slider_Text_Bullets_klein.png', 'Temp1_03_Slider_Text_Bullets.png'),
    array('t2_03_Slider_Text_Bullets', $t00_Slider_Bullets, $g992, 'Temp2_03_Slider_Text_Bullets_klein.png', 'Temp2_03_Slider_Text_Bullets.png'),
    array('t3_03_Slider_Text_Bullets', $t00_Slider_Bullets, $g993, 'Temp3_03_Slider_Text_Bullets_klein.png', 'Temp3_03_Slider_Text_Bullets.png'),
    array('t7_03_Slider_Text_Bullets', $t00_Slider_Bullets, $g997, 'Temp7_03_Slider_Text_Bullets_klein.png', 'Temp7_03_Slider_Text_Bullets.png'),
    array('t9_03_Slider_Text_Bullets', $t00_Slider_Bullets, $g999, 'Temp9_03_Slider_Text_Bullets_klein.png', 'Temp9_03_Slider_Text_Bullets.png'),
    /* Slider 7 */
    array('t1_04_Slider_Text_Download', $t00_Slider_Download_1, $g991, 'Temp1_04_Slider_Text_Download_klein.png', 'Temp1_04_Slider_Text_Download.png'),
    array('t2_04_Slider_Text_Download', $t00_Slider_Download_2, $g992, 'Temp2_04_Slider_Text_Download_klein.png', 'Temp2_04_Slider_Text_Download.png'),
    array('t3_04_Slider_Text_Download', $t00_Slider_Download_3, $g993, 'Temp3_04_Slider_Text_Download_klein.png', 'Temp3_04_Slider_Text_Download.png'),
    array('t7_04_Slider_Text_Download', $t00_Slider_Download_7, $g997, 'Temp7_04_Slider_Text_Download_klein.png', 'Temp7_04_Slider_Text_Download.png'),
    array('t9_04_Slider_Text_Download', $t00_Slider_Download_9, $g999, 'Temp9_04_Slider_Text_Download_klein.png', 'Temp9_04_Slider_Text_Download.png'),
    /* Slider 8 */
    array('t1_04_Slider_Text_Movie', $t00_Slider_Movie_1, $g991, 'Temp1_04_Slider_Text_Movie_klein.png', 'Temp1_04_Slider_Text_Movie.png'),
    array('t2_04_Slider_Text_Movie', $t00_Slider_Movie_2, $g992, 'Temp2_04_Slider_Text_Movie_klein.png', 'Temp2_04_Slider_Text_Movie.png'),
    array('t3_04_Slider_Text_Movie', $t00_Slider_Movie_3, $g993, 'Temp3_04_Slider_Text_Movie_klein.png', 'Temp3_04_Slider_Text_Movie.png'),
    array('t7_04_Slider_Text_Movie', $t00_Slider_Movie_7, $g997, 'Temp7_04_Slider_Text_Movie_klein.png', 'Temp7_04_Slider_Text_Movie.png'),
    array('t9_04_Slider_Text_Movie', $t00_Slider_Movie_9, $g999, 'Temp9_04_Slider_Text_Movie_klein.png', 'Temp9_04_Slider_Text_Movie.png'),
    /* IMG Slider 1 */
    array('t1_05_Slider_Text_Button_1', $t00_Slider_Bild_Button_1_1, $g9991, 'Temp1_05_Slider_Text_Button_1.png', 'Temp1_05_Slider_Text_Button_1.png'),
    array('t3_05_Slider_Text_Button_1', $t00_Slider_Bild_Button_1_1, $g9993, 'Temp3_05_Slider_Text_Bild_Button_1_klein.png', 'Temp3_05_Slider_Text_Bild_Button_1.png'),
    array('t8_05_Slider_Text_Button_1', $t00_Slider_Bild_Button_8_1, $g9998, 'Temp8_01_Slider_Text_Button_1_klein.png', 'Temp8_01_Slider_Text_Button_1.png'),
    /* IMG Slider 2 */
    array('t1_05_Slider_Text_Button_2', $t00_Slider_Bild_Button_1_2, $g9991, 'Temp1_05_Slider_Text_Button_2.png', 'Temp1_05_Slider_Text_Button_2.png'),
    array('t3_05_Slider_Text_Button_2', $t00_Slider_Bild_Button_1_2, $g9993, 'Temp3_05_Slider_Text_Bild_Button_2_klein.png', 'Temp3_05_Slider_Text_Bild_Button_2.png'),
    array('t8_05_Slider_Text_Button_2', $t00_Slider_Bild_Button_8_2, $g9998, 'Temp8_01_Slider_Text_Button_2_klein.png', 'Temp8_01_Slider_Text_Button_2.png'),
    /* IMG Slider 3 */
    array('t1_05_Slider_Text_Button_3', $t00_Slider_Bild_Button_1_3, $g9991, 'Temp1_05_Slider_Text_Button_3.png', 'Temp1_05_Slider_Text_Button_3.png'),
    array('t3_05_Slider_Text_Button_3', $t00_Slider_Bild_Button_1_3, $g9993, 'Temp3_05_Slider_Text_Bild_Button_3_klein.png', 'Temp3_05_Slider_Text_Bild_Button_3.png'),
    array('t8_05_Slider_Text_Button_3', $t00_Slider_Bild_Button_8_3, $g9998, 'Temp8_01_Slider_Text_Button_3_klein.png', 'Temp8_01_Slider_Text_Button_3.png'),
    /* IMG Slider 4 */
    array('t1_05_Slider_Text_Button_4', $t00_Slider_Bild_Button_1_4, $g9991, 'Temp1_05_Slider_Text_Button_4.png', 'Temp1_05_Slider_Text_Button_4.png'),
    array('t3_05_Slider_Text_Button_4', $t00_Slider_Bild_Button_1_4, $g9993, 'Temp3_05_Slider_Text_Bild_Button_4_klein.png', 'Temp3_05_Slider_Text_Bild_Button_4.png'),
    array('t8_05_Slider_Text_Button_4', $t00_Slider_Bild_Button_8_4, $g9998, 'Temp8_01_Slider_Text_Button_4_klein.png', 'Temp8_01_Slider_Text_Button_4.png'),
    /* IMG Slider 5 */
    array('t1_06_Slider_Text_Mail_1', $t00_Slider_Bild_Mail_1, $g9991, 'Temp1_06_Slider_Text_Mail_1.png', 'Temp1_06_Slider_Text_Mail_1.png'),
    array('t3_06_Slider_Text_Mail_1', $t00_Slider_Bild_Mail_1, $g9993, 'Temp3_06_Slider_Text_Bild_Mail_1_klein.png', 'Temp3_06_Slider_Text_Bild_Mail_1.png'),
    array('t8_06_Slider_Text_Mail_1', $t00_Slider_Bild_Mail_8, $g9998, 'Temp8_02_Slider_Text_Mail_1_klein.png', 'Temp8_02_Slider_Text_Mail_1.png'),
    /* IMG Slider 6 */
    array('t1_07_Slider_Text_Bullets', $t00_Slider_Bild_Bullets_1, $g9991, 'Temp1_07_Slider_Text_Bullets.png', 'Temp1_07_Slider_Text_Bullets.png'),
    array('t3_07_Slider_Text_Bullets', $t00_Slider_Bild_Bullets_1, $g9993, 'Temp3_07_Slider_Text_Bild_Bullets_klein.png', 'Temp3_07_Slider_Text_Bild_Bullets.png'),
    array('t8_07_Slider_Text_Bullets', $t00_Slider_Bild_Bullets_8, $g9998, 'Temp8_03_Slider_Text_Bullets_klein.png', 'Temp8_03_Slider_Text_Bullets.png'),
    /* IMG Slider 7 */
    array('t1_08_Slider_Text_Download', $t00_Slider_Bild_Download_1_1, $g9991, 'Temp1_08_Slider_Text_Download_klein.png', 'Temp1_08_Slider_Text_Download.png'),
    array('t3_08_Slider_Text_Download', $t00_Slider_Bild_Download_1_3, $g9993, 'Temp3_08_Slider_Text_Bild_Download_klein.png', 'Temp3_08_Slider_Text_Bild_Download.png'),
    array('t8_08_Slider_Text_Download', $t00_Slider_Bild_Download_8_1, $g9998, 'Temp8_04_Slider_Text_Download_klein.png', 'Temp8_04_Slider_Text_Download.png'),
    /* IMG Slider 8 */
    array('t1_08_Slider_Text_Movie', $t00_Slider_Bild_Movie_1_1, $g9991, 'Temp1_08_Slider_Text_Movie.png', 'Temp1_08_Slider_Text_Movie.png'),
    array('t3_08_Slider_Text_Movie', $t00_Slider_Bild_Movie_1_3, $g9993, 'Temp3_08_Slider_Text_Bild_Movie_klein.png', 'Temp3_08_Slider_Text_Bild_Movie.png'),
    array('t8_08_Slider_Text_Movie', $t00_Slider_Bild_Movie_8_1, $g9998, 'Temp8_04_Slider_Text_Movie_klein.png', 'Temp8_04_Slider_Text_Movie.png'),
);
$config['sms_templates'] = $templates;
