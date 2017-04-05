<?php
$CI = & get_instance();
$tenant = $CI->config->item('tenant');


$lang['Perso popup title'] = "Select and edit segmentation rule";
$lang['Perso popup introductory text'] = "If you wish to deliver projects for specific visitor groups, determine the segmentation rule here.";
if($tenant=='etracker')	
	$lang['Perso popup introductory text'] = "If you wish to deliver Smart Messages and tests for specific visitor groups, determine the segmentation rule here.";
$lang['Perso rule list title'] = "Segmentation rules";
$lang['Perso not enabled title'] = "Personalization (not enabled)";
$lang['Perso not enabled'] = "Your account is not entitled to use personalization. In order to access personalization please contact your account manager or use our online form here:";
$lang['Perso enable now'] = "Implement upgrade now and segment variants";
$lang['Perso enable perso type'] = "Please contact our support to enable this condition type.";
$lang['Perso nav title'] = "Determine segmentation";
$lang['Perso edit campaign'] = "You can deliver projects for specific visitor groups. Define the segmentation form here. If you wish to deliver all the projects for the same visitor group, select, for example, the third option. If you wish to differentiate more precisely, use the third option. Use the pen icon then to determine a segmentation rule.";
if($tenant=='etracker')	
	$lang['Perso edit campaign'] = "You can deliver Smart Messages and tests for specific visitor groups. Define the segmentation form here. If you wish to deliver all the projects for the same visitor group, select, for example, the third option. If you wish to differentiate more precisely, use the third option. Use the pen icon then to determine a segmentation rule.";
$lang['Perso new campaign'] = $lang['Perso edit campaign'];
$lang['Perso no personalization'] = "Without segmentation";
$lang['Perso complete test'] = "Segment all variants";
$lang['Perso single variant'] = "Segment variants individually";
/***/
$lang['Perso noperso intro'] = "The variants of this project are delivered to all visitors.";
$lang['Perso complete intro'] = "All variants for the project are delivered to one defined visitor group. Click on the pen icon to define this visitor group with a segmentation rule.";
$lang['Perso single intro'] = "All project variants can be delivered to different visitor groups. Click on the pen icon to define this visitor group with a segmentation rule.";
$lang['Perso has sms'] = "Since this campaign contains a SmartMessage, the original page will not be delivered to avoid meaningless results";
/***/
$lang['Perso table title'] = "Segmentation rule";
$lang['Perso unpersonalized'] = "(not segmented)";
/***/
$lang['Perso error title'] = "Please fix the following error(s)";
$lang['Perso error selectrule'] = "Create or select a rule from the list";
$lang['Perso error rulename'] = "Enter a name for the test";
$lang['Perso error nocondition'] = "You have to add at least one condition for every rule";
$lang['Perso error condition'] = "Select a value for every condition in the list";
$lang['Perso error parameter'] = "Select or enter a valid parameter for every condition";
/***/
$lang['Perso del condition'] = "Delete this condition";
$lang['Perso confirm del rule'] = "Are you sure you want to delete this rule ";
$lang['Perso button delete rule'] = "Delete Rule";
$lang['Perso confirm cancel title'] = "Unsaved changes detected.";
$lang['Perso confirm cancel'] = "There are unsaved changes, do you want to discard and exit?";
/***/
$lang['Perso rule label'] = "Rule";
$lang['Perso add rule'] = "Add Rule";
$lang['Perso del rule'] = "Remove selected rule";
$lang['Perso select rule'] = "Determine rule";
$lang['Perso not defined'] = "You have not yet created a segmentation rule.";
$lang['Perso please start'] = "Please select \"Add rule\" and define a rule.";
$lang['Perso rule name'] = "Rule name";
$lang['Perso rule valid'] = "Use the rule if the following is valid:";
$lang['Perso and condition'] = "All conditions apply";
$lang['Perso title condition'] = "Conditions";
$lang['Perso add condition'] = "Add a Condition";
$lang['Perso or condition'] = "One of the conditions applies";
/***/
$lang['Perso equal'] = "equal";
$lang['Perso not equal'] = "not equal";
$lang['Perso greater'] = "greater than";
$lang['Perso less'] = "less than";
$lang['Perso to'] = "earlier than";
$lang['Perso from'] = "later than";

$lang['Perso day singular'] = "Day";
$lang['Perso day plural'] = "Days";

$lang['Perso option select'] = "Select...";
$lang['Perso option select tt'] = "Select at least one condition for every rule.";
$lang['Perso option select country'] = "Select Country...";
$lang['Perso option select state'] = "Select State...";
$lang['Perso option select region'] = "Select Region/County...";
$lang['Perso option select city'] = "Select City...";


$lang['Perso origin'] = "Origin";
$lang['Perso purchase behavior'] = "Purchase behaviour";
$lang['Perso user profile'] = "User profile";
$lang['Perso visit behavior'] = "Visit behaviour";
$lang['Perso technology'] = "Technology";
$lang['Perso location'] = "Visitor location";

/***/
$lang['Perso querystring is'] = "URL Parameter";
$lang['Perso querystring is tt'] = "Segmenting by used URL parameters";
$lang['Perso url contains'] = "URL contains";
$lang['Perso url contains tt'] = "Segmenting by used URL parameters";
$lang['Perso referer is'] = "Origin/Path contains";
$lang['Perso referer is tt'] = "Segmenting by domain of origin";
$lang['Perso trafficsource is'] = "Medium";
$lang['Perso trafficsource is tt'] = "Segmenting by adversiting channels";
$lang['Perso source typein'] = "Type-In";
$lang['Perso source social'] = "Social Media";
$lang['Perso source organic'] = "SEO";
$lang['Perso source paid'] = "SEA";
$lang['Perso search is'] = "Search term";
$lang['Perso search is tt'] = "Segmenting by entered search term";
$lang['Perso time lastorder'] = "Time since last order";
$lang['Perso time lastorder tt'] = "Segmenting by time intervals between orders";
$lang['Perso avg sales'] = "Average order value";
$lang['Perso avg sales tt'] = "Segmenting by amounts of average order values in €";
$lang['Perso visitor isclient'] = "Visitor is customer";
$lang['Perso visitor isclient tt'] = "Segmenting by customers and non-customers ";
$lang['Perso purchaser type'] = "Purchaser type";
$lang['Perso purchaser type tt'] = "Segmenting according to ABC analysis:<br>
A = Very important purchaser<br>
B = Important purchaser<br>
C = Unimportant purchaser";
$lang['Perso visitor newsletter'] = "Visitor is newsletter subscriber";
$lang['Perso visitor newsletter tt'] = "Segmenting by subscribers and non-subscribers";
$lang['Perso visit count'] = "Visit frequency";
$lang['Perso visit count tt'] = "Segmenting by counts of previous visits:<br>
Low = 2<br>
Medium = 3-4<br>
HIgh = 5 and more<br>";
$lang['Perso time visits'] = "Time since last visit";
$lang['Perso time visits tt'] = "Segmenting by time intervals between visits";
$lang['Perso visitor returning'] = "Visitor is returning visitor";
$lang['Perso visitor returning tt'] = "Segmenting by returning visitors and new visitors";
$lang['Perso targetpage opened'] = "Target page accessed";
$lang['Perso targetpage opened tt'] = "Segmenting by accessed target page. Use the * sign as a wildcard.";
$lang['Perso goal insert'] = "etracker target 'Product in the shopping basket' reached";
$lang['Perso goal insert tt'] = "Segmenting by reached wegsite target";
$lang['Perso minimum session'] = "Minimum session time (seconds)";
$lang['Perso minimum session tt'] = "Segmenting by minimum session time";
$lang['Perso device is'] = "Device type";
$lang['Perso device is tt'] = "Segmenting by device types that access the website.";
$lang['Perso device tablet'] = "Tablet";
$lang['Perso device desktop'] = "Desktop";
$lang['Perso device mobile'] = "Mobile phone";
$lang['Perso device other'] = "Others";
$lang['Perso device os is'] = "Operating system";
$lang['Perso device os is tt'] = "Segmenting by operating system of the device that accesses the website.";
// GEO IP
$lang['Perso location is'] = "Location is";
$lang['Perso location is tt'] = "Segmenting by visitor location";
$lang['Perso is holiday'] = "Holidays";
$lang['Perso is holiday tt'] = "Segmenting by holidays at visitor location";



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
$lang['Perso yes'] = "Yes";
$lang['Perso no'] = "No";
$lang['Perso discard'] = "Discard changes";
$lang['Perso back'] = "Cancel";

// attributes for etracker RealTimeAPI
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_01'] = "No order";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_02'] = "1-30 days";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_03'] = "31-90 days";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_04'] = "91 days – 12 months";
$lang['STC_CC_ATTR_VALUE_TIME_SINCE_LAST_ORDER_SEG_05'] = "More than 12 months";

$lang['STC_CC_ATTR_VALUE_AVG_ORDER_VALUE_SEG_12'] = "More than 4000";

$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_1'] = "No purchase";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_2'] = "Only one purchase";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_3'] = "C";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_4'] = "B";
$lang['STC_CC_ATTR_VALUE_PURCHASER_TYPE_5'] = "A";

$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_01'] = "Only one visit";
$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_02'] = "Low";
$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_03'] = "Medium";
$lang['STC_CC_ATTR_VALUE_VISIT_COUNT_SEG_04'] = "High";

$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_01'] = "Less than 1 day";
$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_02'] = "1-7 days";
$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_03'] = "7-30 days";
$lang['STC_CC_ATTR_VALUE_FREQUENCY_SEG_04'] = "More than 30 days";