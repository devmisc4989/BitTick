<?php
if(function_exists(get_instance)) {
    $CI = & get_instance();
    $tenant = $CI->config->item('tenant');
}

$lang['title_collection'] = "Project details - ";
$lang['testtype_split'] = "Split URL Test";
$lang['testtype_visual'] = "Visual A/B Test";
$lang['testtype_teaser'] = "Teaser Test";
$lang['testtype_multipage'] = "Multipage Test";
$lang['testtype_sms'] = "Smart Messaging Campaign";
$lang['lcd_previouslink'] = "< Older results";
$lang['lcd_nextlink'] = "Newer results >";
$lang['lcd_description'] = "You can find all project details here as well as the optimisation results for the original page and all variants.";
$lang['control_page_name'] = "Original page";
$lang['control is winner'] = "The original page wins.";

$lang['tt_back_overview'] = 'Back to overview';

$lang['tto_intro_headline'] = "This overview shows all articles for which headline A/B tests have been created.";
$lang['tto_btn_newtest'] = "Create headline A/B test";
$lang['tto_link_preview'] = "Show preview";

$lang['smry_headline_paused'] = "The project has been paused, no optimisation is currently taking place.";

$lang['smry_headline_unverified'] = "The installation of the Tracking Code has not been tested.";
$lang['smry_subline_unverified'] = "<a href=\"javascript://\" class=\”collectionVerify\">Please catch this up by clicking here.</a>";

$lang['smry_headline_noevents'] = "So far, no calls of the page have been counted within the project.";
$lang['smry_subline_noevents'] = "This may be because the Tracking Code was not yet or was incorrectly 
integrated into the page to be tested.";

$lang['smry_headline_controlwinner'] = "Unfortunately, the test has not resulted in any improvements.";
$lang['smry_subline_controlwinner'] = "No variant has a better conversion rate than the original page (%s %%). This result has an accuracy of %s %%";

$lang['smry_headline_variantwinner'] = "The test has established an improvement of %s %%";
$lang['smry_subline_variantwinner'] = "The most successful page is the variant %s. This result has an accuracy of %s %%";

$lang['smry_headline_nosignificance'] = "So far, no variant has had a better result than the original page. However, the test is still running.";
$lang['smry_subline_testtime'] = "The remaining test running period is <b>approximately %s days.</b>";
$lang['smry_subline_testtime_morethan6months'] = "The remaining test running period is <b>longer than 6 months.</b>";
$lang['smry_subline_testtime_3to6months'] = "The remaining test running period is <b>3 to 6 months.</b>";
$lang['smry_subline_testtime_1to3months'] = "The remaining test running period is <b>1 to 3 months.</b>";
$lang['smry_subline_testtime_nodata'] = "Not enough data has been recorded yet in order to enable an estimation of the remaining test period.";
$lang['variant_testtime_nodata'] = "Not enough data yet";

$lang['smry_headline_oneleader'] = "Variant %s converts %s %% better than the original page. However, the test is still running.";
$lang['smry_headline_multleaders'] = "Several variants convert better than the original page, the best by %s %%. However, the test is still running.";

/*** Perso headline and subline ***/
$lang['smry_headline_single_nosms'] = "Delivering segmented variants.";
$lang['smry_subline_single_nosms'] = "The original page is not displayed.";
$lang['smry_headline_sms_nosingle'] = "Delivering variants with Smart Messages.";
$lang['smry_subline_sms_nosingle'] = "The original page is not displayed.";
$lang['smry_headline_single_and_sms'] = "Delivering segmented variants with Smart Messages.";
$lang['smry_subline_single_and_sms'] = "The original page is not displayed.";

$lang['autopilot_is_active'] = "The autopilot is active (underperforming variants will not be delivered).";
$lang['autopilot_activate'] = "Activate autopilot.";
$lang['autopilot_is_stopped'] = "The autopilot is not active, all variants (even those underperforming) will be delivered.";
$lang['autopilot_stop'] = "Deactivate autopilot.";



//action
$lang["Preview Control"] = "Original page preview";
$lang["Preview Variant"] = "Preview of this variant";

$lang["link_edit"] = "Edit project";
$lang["link_restart"] = "Restart project";
$lang["link_start"] = "Start project";
$lang["link_play"] = "Continue project";
$lang["link_pause"] = "Stop project";

$lang["Original und Varianten"] = "Original and variants";
$lang["Testseite"] = "Project name and URL";
$lang["Tracking-Code"] = "Tracking Code";
$lang["Goals"] = "Conversion goals";
$lang["Sandbox export"] = "Export for Sandbox";
$lang["Time interval label"] = "Time Interval";

$lang["Available time intervals"] = array(
    OPT_TREND_DAY => "1 Day",
    OPT_TREND_HOUR => "1 Hour",
    OPT_TREND_5MINUTE => "s Minutes",
    OPT_TREND_MINUTE => "1 Minute",
);

/* * *****************************************************************
 * PERSONALIZATION
 * **************************************************************** */
$lang["Personalization_Status_0"] = "This campaign is not personalized";
$lang["Personalization_Status_1"] = "This test is delivered for dedicated visitors according to rule ";
$lang["Personalization_Status_2"] = "Variants are personalized, no control page is delivered";
$lang["Personalization_Not_Personalized"] = "Not Personalized.";
$lang["Personalization_Unpersonalized_change"] = "Unpersonalized (Change)";
$lang["Personalization_Confirm_Norule_Title"] = "No personalization will be set for this test";
$lang["Personalization_Confirm_Norule"] = "Are you sure you want to unset the personalization for this test?";
$lang["Personalization_Status_Link"] = "Change Personalization Type";
$lang["Personalization_Yes_Continue"] = "yes, conclude";
$lang["Personalization_Save_Changes"] = "Conclude segmentation";

/* * *****************************************************************
 * DIAGNOSIS MODE POP UP
 * **************************************************************** */
$lang["Diagnose_Mode"] = "Diagnosis";
$lang["Debug_Test"] = "Enter the URL of an original page for diagnosis";
$lang["Start_Diagnose"] = "Start diagnose";
$lang["Diagnose_Mode_Headline1"] = "If your project doesn’t deliver any variants, you can 
use the diagnosis to find out whether you have configured the project correctly and whether 
only one project is delivered per original page.<br>Enter the URL of an original page in the 
input field below. We will then test the project configuration for you.";
$lang["Diagnose_Mode_Headline3"] = "Click on \"Start\" to implement the diagnosis<br /><br /> ";

$lang["URL_Of_Page"] = "URL of an original page";
$lang["Back to details"] = "Project details";
$lang["Test_Now"] = "Start";
$lang["Close_Window"] = " Close ";
$lang["Please_Wait"] = "One moment please...";
$lang["Please_Wait_Message"] = "Please be patient for a moment whilst we collect the diagnosis data.";

/* * *****************************************************************
 * SIMPLE RESULT
 * **************************************************************** */
$lang["Diagnose_Result"] = "Diagnosis results";
$lang["Head_Issue1"] = "Result: Your project contingent has been used up.";
$lang["Head_Issue2"] = "Result: None of your projects are activated.";
$lang["Head_Issue5"] = "Result: A problem with the Tracking Code was found on your page.";
$lang["Head_Issue6"] = "Result: The Tracking Code for the page checked doesn’t belong to this account.";
if($tenant == 'etracker')	
	$lang["Head_Issue6"] = "Result: Account key 1 of the page tested doesn’t belong to this account.";

$lang["Copy_Issue2"] = "You must activate a project in order to deliver the variants. For this purpose, select Start project on the project details page.";
$lang["Copy_Issue5"] = "We were unable to collect any diagnosis data. Please ensure that the Tracking Code is correctly integrated into
	the page checked.";
$lang["Copy_Issue6"] = "In the page, we found account key 1 <strong id=\"client-code-tested\"></strong>, but account key 1 for this account is 
	<strong id=\"client-code-login\"></strong>. <br />For security reasons, you can only retrieve diagnosis data for pages with your own account key 1.";

$lang["Copy_Status1"] = "Your project contingent is <strong class=\"quota\"></strong>  unique visitors and has been used up. <br />
	Please contact our sales team for information about our editions.";
$lang["Copy_Status6"] = "Your monthly project contingent of <strong class=\"quota\"></strong> unique visitors has been used up. <br />
	The next contingent is available from <strong class=\"reset-date\"></strong>. <br />
	If you would like to upgrade, our sales team would be happy to help you.";

/* * *****************************************************************
 * RESULT MATCH
 * **************************************************************** */
$lang['Match_Intro'] = "If the URL entered matches your project configuration, the variants can be delivered.";
$lang['Match_Intro_Etpagename'] = "The tested page incorporates a JavaScript variable \"et_pagename\". If this variable or the URL entered matches your project configuration, the variants can be delivered.";
$lang['Page_Url_Title'] = "URL of the tested page:";
$lang['Et_Pagename_Title'] = "et_pagename of the tested page:";
$lang['Checked_Test_Title'] = "We have checked the following running projects for matching";

$lang['back'] = "Back";

$lang['Table_Testname'] = "Project name";
$lang['Table_Testpage'] = "URL of the page(s)";
$lang['Table_Result'] = "Result";
$lang['Table_Match'] = "<b>Matching, project can be delivered</b>";
$lang['Table_Match_Conflict'] = "<b>Matching, but there is a conflict:</b>";
$lang['Table_Nomatch'] = "No matching";
$lang['Table_Conflict_Sms'] = "Only one Smart Message deliverable per page.";
$lang['Table_Conflict_Split'] = "Split tests can be delivered exclusively only.";

$lang["Head_Match0"] = "Result: No project includes the original page checked.";
$lang["Head_Match1"] = "Result: Precisely one project includes the original page checked.";
$lang["Head_Match2"] = "Result: Several projects include the original page checked";
$lang["Copy_Delivery0"] = "The current project <strong>%s</strong> can be delivered on this page.";
$lang["Copy_Delivery1"] = "The current project <strong>%s</strong> is not active or does not match the URL, and will not be delivered on this page.";
$lang["Copy_Delivery2"] = "The current project <strong>%s</strong> matches the URL, but will not be delivered due to a conflict with other projects.";