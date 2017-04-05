<?php

$CI = & get_instance();
$tenant = $CI->config->item('tenant');

if ($tenant != 'etracker')
    $lang['headline'] = "Dashboard";
else
    $lang['headline'] = "Project overview";
$lang['description'] = "This overview shows the conversion rate achieved and the optimisation status for each of your tests. 
Click on the \"Select\" link under \"Action\" to process, delete, restart etc. the tests";


$lang['action_delete'] = "Delete project";
$lang['action_pause'] = "Stop project";
$lang['action_play'] = "Continue project";
$lang['action_start'] = "Start project";
$lang['action_verify'] = "Test Tracking Code";
$lang['action_show_code'] = "Display Tracking Code";
$lang['action_status'] = "";
$lang['action_show_details'] = "Display project details";

$lang['tooltip_running'] = "The project is running";
$lang['tooltip_paused'] = "The project has been stopped";

$lang['remaining time'] = "Remaining time:";
$lang['testtime'] = "Roughly %s days";
$lang['testtime_morethan6months'] = "Longer than 6 months";
$lang['testtime_3to6months'] = "3 to 6 months";
$lang['testtime_1to3months'] = "1 to 3 months";
$lang['testtime_nodata'] = "Still insufficient data";

$lang['testtype_split'] = "Split URL Test";
$lang['testtype_visual'] = "Visual A/B Test";
$lang['testtype_teaser'] = "Teaser Test";
$lang['testtype_multipage'] = "Multipage Test";
$lang['testtype_sms'] = "Smart Messaging Campaign";

$lang['deletecollection_headline'] = "Do you really wish to delete the project?";
$lang['deletecollection_subline'] = "All settings and measurement results will be removed, this cannot be undone.";

//tracking code pop-up
$lang['Verify code'] = "Test Tracking Code";
$lang['Please wait until your code gets verified'] = "Please wait, your page is loading...";
$lang['Tracking code missing'] = "The Tracking Code could not be found " . splink('trackingcodemissing');
$lang['Tracking code verified'] = "You have installed the Tracking Code correctly!";
$lang['Tracking code instructions'] = "
<br>Please check in the preview whether all pages are displayed correctly.
<br>Following this, activate the test by clicking on \"Start test\".<br>" . splink('trackingcodeincluded');
$lang['Click here to close'] = "Close";

$lang['Please wait until your code gets loaded'] = "The code is loading...";
$lang['Show code'] = "Display Tracking Code";

/* * *****************************************************************
 * DUPLICATE TEST
 * **************************************************************** */
$lang['Duplicate_Test'] = "Duplicate project";
$lang['Duplicate_Title'] = "Duplicate project: <strong id=\"test-name-title\"></strong>.";
$lang['Duplicate_Copyof'] = "Copy of ";
$lang['Duplicate_Info'] = "Please specify a name for the new project";
$lang['Duplicate_Error_Title'] = "An error has occurred ...";
$lang['Duplicate_Error_Content'] = "We couldn’t create a copy of the test, please contact our Support.";
$lang['Duplicate_Wait_Title'] = "Please wait a moment...";
$lang['Duplicate_Wait_Content'] = "";

$lang['conflict layer popup'] = array(
    'confitm_title' => "Conflict Warning - Start the project?",
    'title' => "Conflict Warning",
    'subtitle' => "Project",
    'table name' => "Project Name",
    'table type' => "Project Type",
    'table problem' => "Problem",
    'conflict intro' => array(
        0 => "",
        1 => "The project will not be delivered because it is configured to run on the same URL as other projects.",
        2 => "Starting the project will result in at least the following projects not being delivered.",
    ),
    'table conflicts' => array(
        0 => "",
        1 => "Only one Smart Message can be delivered per page.",
        2 => "A Split URL Test can be delivered only exlusively per page.",
    ),
);


/* * *****************************************************************
 * TEASER TESTS
 * **************************************************************** */
$lang['tt original title'] = "URL of start page";
$lang['tt original text'] = "Enter the URL of your website start page";
$lang['tt link example'] = "Example: http://www.mylandingpage.com";
$lang['tt interface title'] = "Management of Headline A/B tests";
$lang['tt_interface text'] = "How are you going to create and edit headline A/B tests (this cannot be changed once the test has been created) ?";
$lang['tt interface options'] = array(
    'API' => "Integrated in Content Management System",
    'UI' => "Externally managed in BlackTri application",
);
$lang['tt_headlines_title'] = "Manage Headlines for Article";
$lang['tt_headlines_main_label'] = "Headline original";
$lang['tt_headlines_main_intro'] = "Copy the headline from the HTML and enter it into this field";
$lang['tt_headlines_variant_label'] = "Enter alternative headlines";
$lang['tt_headlines_variant_permanent'] = "Use for permanent deliver";
$lang['tt_headlines_variant_add'] = "Add a variant";
$lang['tt_headlines_variant_delete'] = "Delete this variant";
$lang['tt_headlines_variant_back'] = "Teaser test overview";
$lang['tt_headlines_variant_save'] = "Save";

$lang['tt_delete_confirm_title'] = "Do you really want to delte this TT?";
$lang['tt_delete_confirm_text'] = "This operation cannot be undone...";

$lang['tt config layer'] = array(
    'wizard' => array(
        'title' => "Teaser Test",
        'btn_save' => "Save and create test",
        'btn_cancel' => "Create project",
    ),
    'edit' => array(
        'title' => "Teaser Test",
        'btn_save' => "Save changes",
        'btn_cancel' => "Cancel",
    ),
);
