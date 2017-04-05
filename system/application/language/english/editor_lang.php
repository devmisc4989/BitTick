<?php
$CI = & get_instance();
$tenant = $CI->config->item('tenant');

// wizard screens
//general
$lang['One step back'] = "One step back";
$lang['Help'] = "Help";

//screen 1
$lang['Create a test'] = "Create project";
$lang['Create a test description'] = "Select here whether you wish to create a visual A/B test or a split URL test.";
if($tenant=='etracker')
	$lang['Create a test description'] = "Select here whether you wish to create a visual A/B test, a split URL test or a Smart Message. ";
$lang['New A/B test'] = "New split URL test";
$lang['New A/B test description'] = "Finds the most successful from several HTML pages or templates for a page. It is well-suited if, for variants, 
server-side changes are required or if the Visual Editor is unable to load a page.";
$lang['Create A/B Test now!'] = "Create a split URL test";

$lang['Visual A/B test'] = "New visual A/B test";
$lang['Visual A/B test description'] = "Enables uncomplicated definition of variants for website texts, images or adverts using the Visual Editor. 
Moreover, it is suitable for creating variants of a page using CSS or JavaScript.";
$lang['Create Visual A/B Test now!'] = "Create a visual A/B test!";

$lang['New Multipage test'] = "New Multipage Test";
$lang['Multipage test description'] = "Create MP tests lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vulputate felis massa, vitae vulputate.";
$lang['Create Multipage Test now!'] = "Create Multipage test!";
$lang['Multipage page index'] = 'Page ';
$lang['Edit multipage test'] = 'Edit';
$lang['multipage popup title'] = 'Edit Multipage test';
$lang['multipage popup intro'] = 'lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vulputate felis massa, vitae vulputate.';
$lang['multipage popup add'] = 'Add a new page';
$lang['multipage popup delete'] = 'Delete';
$lang['multipage popup save'] = 'Save';
$lang['multipage delete title'] = 'Do you want to delete this page(s)?';
$lang['multipage delete info'] = 'This cannot be undone';
$lang['Mpt pageurl title'] = "Define the Name and the URL for the new page.";
$lang['Mpt name example'] = "Example: Home page or Shopping basket page";
$lang['Mpt enter name'] = "Enter the Name of the new page.";
$lang['Mpt name example'] = "Example: Home page or Shopping basket page";
$lang['Mpt name error'] = "This name is already assigned to another page";
$lang['Mpt url error'] = "You already created a page with this URL";
$lang['Url for MPT title'] = "URLs of multipage test";
$lang['Url for MPT intro'] = "lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vulputate felis massa, vitae vulputate.";

$lang['New Teaser test'] = "New Teaser test";
$lang['Teaser test description'] = "Create A/B tests for your media website's headlines, and improve click through rate, time on page and number of page impressions.";
$lang['Create Teaser Test now!'] = "Create Teaser test!";

$lang['New sms test'] = "New Smart Message";
$lang['New sms description'] = "Enables you to address specific visitor groups on your website. For example, you can offer your visitors an incentive not to leave the website.";
$lang['Create sms now!'] = "Create Smart Message";

//visual test substep 1
//$lang['Create Visual Test (Step 1 of 3)'] = "Visuellen A/B-Test erstellen (Schritt 1 von 3)";
$lang['Enter URL of your page'] = "Enter the URL of the original page here which you wish to optimise.";
$lang['Enter URL of your page description'] = "For this purpose, you can open the page in the browser and copy the URL from the address field.";
$lang['Link Example'] = "Example: http://www.mylandingpage.com or http://www.mylandingpage.com/article.jsp?id=4711";
$lang['Proceed to Editor'] = "Continue and open page now";

//visual test substep 2
//$lang['Create Visual Test (Step 2 of 3)'] = "Visuellen A/B-Test erstellen (Schritt 2 von 3)";
$lang['Loading page...'] = "Page loading...";
$lang['Loading page... description'] = "Please wait a moment, the page is being loaded and prepared for processing.";
$lang['You can edit the page now'] = "You can now edit and create variants for your project.";
$lang['You can edit the page now description'] = "Change individual elements for variant pages. Click with the mouse on them to open a menu from which you can process the element.";
if($tenant=='etracker')
	$lang['You can edit the page now description'] = "Change individual elements for tests or edit Smart Messages. Click with the mouse on them to open a menu from which you can process the element.";

$lang['Do not show this message'] = "Do not display this note any longer";
$lang['close and proceed editing'] = "Close the dialogue and process the page now";

$lang['Proceed'] = "Continue";
$lang['Start Test'] = "Start the test now";

$lang['editor_mode_browse'] = "Navigate";
$lang['editor_mode_edit'] = "Edit";


//$lang['Too many page variants'] = "Zu viele Varianten";
//$lang['You should delete some variants or factor'] = "Sie sollten Testelemente entfernen damit der Test nicht zu lange dauert.";
//$lang['Combination']="Kombination";
//$lang['Combinations']="Kombinationen";

//visual test substep 3
$lang['Create Visual Test (Step 3 of 3)'] = "Create visual A/B test (step 3 of 3)";
$lang['Choose a name for your test'] = "Project name";

//$lang["Choose the conversion goal"] = "WÃ¤hlen Sie die Art der Konversion aus";
//$lang["Choose the conversion goal description"] = "Welche Useraktion soll als Erfolg bzw. Konversion gewertet werden?";
//$lang["User opens success page"] = "Aufruf einer BestÃ¤tigungs- bzw. \"Danke\"-Seite";
//$lang["User clicks Google Adsense Ad"] = "Event-Tracking, d.h. Klick auf eine Google Adsense-Anzeige, einen Link, etc.";
//$lang['Enter URL of your success page'] = "Geben Sie hier die URL der BestÃ¤tigungs- bzw. \"Danke\"-Seite ein";
//$lang['Enter URL of your success page description'] = "Das ist die Seite, die nach erfolgter Konversion aufgerufen wird, 
//also bspw. die \"Danke\"-Seite nach einer Bestellung.";
//$lang['Success Link Example'] = "Beispiel: http://www.mylandingpage.com/success.php";
//$lang['Test Name Example'] = "Beispiel: A/B-Test Produktseite";
//$lang['Control page code'] = "Tracking-Code fÃ¼r die Originalseite";
//$lang['Variant page code'] = "Tracking-Code fÃ¼r die Varianten-Seiten";
//$lang['Success page code'] = "Tracking-Code fÃ¼r die BestÃ¤tigungs- bzw. \"Danke\"-Seite";
$lang['Save and create test'] = "Save and create project";
$lang['Save test'] = "Save changes";

//$lang['Info headline tracking code'] = "Bauen Sie nun den Test in Ihre Website ein.";
//$lang['Then click on save'] = "Klicken Sie anschlieÃŸend auf \"Speichern und Test erstellen\"";

//$lang["Insert tracking code mvt successpage"] = "FÃ¼gen Sie die beiden Tracking-Codes in Ihre Seiten ein.";
//$lang["Insert tracking code mvt successpage description"] = "FÃ¼gen Sie den oberen Code in die Originalseite ein, am besten unmittelbar hinter dem Ã¶ffnenden &lt;head&gt;-Tag, wenn das nicht geht
//soweit oben im Quelltext wie mÃ¶glich. FÃ¼gen Sie den unteren Code in Ihre BestÃ¤tigungs- bzw. \"Danke\"-Seite ein.";

//visual ab test substep 1
$lang['Create Visual A/B Test (Step 1 of 4)'] = "Define the URL";
//visual ab substep 2
//$lang['Create Visual A/B Test (Step 2 of 4)'] = "Define the URL";
$lang['Original Source'] = "Original page";
$lang['New Variant'] = "New variant";
$lang['Rename visual ab variant'] = "Rename variant";
$lang['Remove visual ab variant'] = "Delete variant";
$lang['Rename visual ab button'] = "Rename";
$lang['Cancel rename visual ab button'] = "Cancel";
$lang['Edit custom css'] = "Own CSS";
$lang['Edit custom js'] = "Own JavaScript";
$lang['Undo change'] = "Undo Change";

//visual ab substep 3 (personalization)
$lang['Create Visual A/B Test (Step 3 of 4)'] = "Create visual A/B test (Step 3 of 4)";
//$lang["Insert tracking code visual A/B successpage"] = "FÃ¼gen Sie die beiden Tracking-Codes in Ihre Seiten ein.";
//$lang["Insert tracking code visual A/B successpage description"] = "FÃ¼gen Sie den oberen Code in die Originalseite ein, mÃ¶glichst (aber nicht zwingend) unmittelbar hinter dem Ã¶ffnenden &lt;head&gt;-Tag. FÃ¼gen Sie den unteren Code in Ihre BestÃ¤tigungs- bzw. \"Danke\"-Seite ein.";
//$lang["Insert tracking code visual A/B event"] = "FÃ¼gen Sie den speziellen Tracking-Code in Ihre Testseite ein.";
//$lang["Insert tracking code visual A/B event description"] = "Wenn es schwierig ist, die Seiten fÃ¼r den Test Ã¼ber ihre URL festzulegen, dann 
//kÃ¶nnen Sie im Website-Template statt des Ã¼blichen Trackingcodes diesen speziellen Code ausspielen und damit
//die Testseite kenntlich machen. Der Code soll mÃ¶glichst (aber nicht zwingend) unmittelbar hinter dem Ã¶ffnenden &lt;head&gt;-Tag stehen.";

$lang['Add url pattern'] = "Add a URL";
$lang['Url pattern options'] = array(
    'include' => "Run On",
    'exclude' => "Exclude",
);

//visual ab substep 4
//$lang['Create Visual A/B Test (Step 4 of 4)'] = "Create visual A/B test (step 4 of 4)";
$lang['Add conversion goals to your test'] = "Determine conversion goals here";
$lang['Add conversion goals to your test description'] = "With this selection, you define which of your visitors’ actions are evaluated as conversions. You can 
	define several conversion goals, also several of the same type. If a visitor carries out any single of these actions, he is viewed as converted.";
$lang['Create new goal'] = "Add a goal";
$lang['Add archived goal'] = "Add an archived goal";
$lang['Choose Target Page'] = "Please select...";

$lang['Available Goals'] = array(
    '1' => "Engagement",
    '11' => "Acting on SmartMessaging",
    '2' => "Affiliate advert",
    '3' => "Target page access",
    '12' => "Target link access",
    '13' => "Self-defined JavaScript goal",
    '14' => "Time on page",
    '15' => "Teaser click rate",
    '16' => "Ranking overall",
    '17' => "Created Page Impressions",
    '5' => "etracker ecommerce Ziel",
    '6' => "etracker Website Ziel",
    '7' => "etracker et_target Ziel",
    '8' => "etracker goal 'Product viewed'",
    '9' => "etracker goal 'Product in the shopping basket'",
    '10' => "etracker goal 'Order'",
);

$lang['Primary goal label'] = "Primary Goal";
$lang['Secondary goal label'] = "Secondary Goal";
$lang['Goal menu action'] = "Action";
$lang['Set as primary goal'] = "Make Primary";
$lang['Goal menu archive'] = "Archive goal";
$lang['Goal menu edit'] = "Edit goal";

$lang['Goal create title'] = "Add conversion goal";
$lang['Goal create add'] = "Add goal";
$lang['Goal details title'] = "Goal details";
$lang['Goal details sub'] = "";
$lang['Goal details change'] = "Aply";
$lang['Goal details intro'] = "Choose the goal type and enter name and confiuration values if necessary.";

$lang['Goal reactivate title'] = "Archived Goals";
$lang['Goal reactivate sub'] = "Recover archived goals and their conversions";
$lang['Goal reactivate intro'] = "Choose an archived goal to recover it to your project";
$lang['Goal reactivate link'] = "Recover";

$lang['ENGAGEMENT_desc'] = "As soon as a visitor clicks on a link on the page or dispatches a form, this is evaluated as engagement. This 
goal is suitable for testing how the bounce rate can be reduced. Moreover, it generally leads to really fast test results.";
$lang['AFFILIATE_desc'] = "Select this goal if you wish to optimise the number of clicks on affiliate advertising media on your 
	page. We recognise the links as affiliate advertising media by comparing them with our database.";
$lang['TARGETPAGE_desc'] = "Select this goal if accessing a target page (such as a thank-you or a confirmation page) in a purchasing process
	should be evaluated as a conversion.";
$lang['LINKURL_desc'] = "Select this goal if accessing a target link with a given URL should be evaluated as a conversion.";
$lang['CUSTOMJS_desc'] = "By accessing the JavaScript _bt.trackCustomGoal(eventname) function from your HTML code, you can very flexibly 
	make certain visitor actions countable as conversions. Select this conversion goal if you wish to make use of this.";
$lang['TIMEONPAGE_desc'] = "Use this goal to measure and opitmize average time on page per visitor.";
$lang['CLICK_desc'] = "Click Goal Description Click Goal Description Click Goal Description Click Goal Description.";

//$lang['Etracker target page desc'] = "Sie kÃ¶nnen hier aus den von Ihnen in etracker definierten Website-Zielen eins auswÃ¤hlen, das als Konversion gewertet 
//	werden soll.";
$lang['smartmessaging desc'] = "If you use etracker Smart Messages, a conversion is deemed to have taken place if a visitor follows the action 
request (call to action) from the Smart Message.";
$lang['etracker viewProduct desc'] = "If you use the etracker eCommerce API, a conversion is triggered as soon as the 'viewProduct' event is transferred.";
$lang['etracker insertToBasket desc'] = "If you use the etracker eCommerce API, a conversion is triggered as soon as the 'insertToBasket' event is transferred.";
$lang['etracker order desc'] = "When you use the etracker eCommerce API, a conversion is triggered as soon as 
	the event “order” is transferred. Lead and sale are not distinguished.<br>A conversion is triggered, too, 
	when you have set the parameters et_target, et_tval, et_tonr and et_tsale in the tracking code.";

$lang['TARGETPAGE_field_desc'] = "Enter the target page URL. You can use the character * as a placeholder.";
$lang['LINKURL_field_desc'] = "Enter the target link URL. You can use the character * as a placeholder.";
$lang['CUSTOMJS_field_desc'] = "Enter the event name which is passed to function _bt.trackCustomGoal() as a parameter.";
$lang['CLICK_field_desc'] = "Click goal field description lorem ipsim dolor sit amet consecteur adipiscing.";

//ocpc
$lang["Pattern fur die Originalseite"] = "URL for the project page(s)";
$lang["url of testpage"] = "URL of the page(s)";
$lang["Geben Sie die URL der zu testenden seite"] = "Enter an URL for the page(s) you wish to deliver. You can use the character * as a placeholder.
Alternatively you can place a JavaScript variable et_pagename on the page and specify its contents here. This is then particularly useful
if the URL cannot be specified clearly (for example in a multi-stage checkout process).";
$lang["Control Page Example"] = "Example: */product.php";

//$lang["Pattern fur die Originalseite OCPT"] = "Die Testseite wird Ã¼ber einen eigenen Trackingcode festgelegt";
//$lang["Geben Sie die URL der zu testenden seite OCPT"] = "Die Testseite wird nicht Ã¼ber eine URL definiert, sondern indem
//das Seiten-Template der Testseite einen speziellen Trackingcode ausspielt (siehe MenÃ¼eintrag \"Trackingcode\")";


//$lang["Pattern fur die Danke-Seite"] = "(Teil-) URL der Danke- bzw. BestÃ¤tigungs-Seite";
//$lang["Geben Sie die URL der zu Danke-Seite"] = "Geben Sie die URL der Danke- oder BestÃ¤tigungs-Seite an. You can use the character * as a placeholder.";
//$lang["Success Page Example"] = "Beispiel: *confirmation*";

$lang["OCPC Tracking code description title"] = "Now insert the Tracking Code into your pages";
$lang["OCPC Tracking code description"] = "If possible, insert the code in all of your pages (if you have not already done so, but not mandatorily) directly behind the 
opening &lt;head&gt;-Tag.";
//ocpt
//$lang["Tracking-Code fur die Originalseite"] = "Eigener Tracking-Code fÃ¼r die Testseite";
//$lang["Tracking-Code fur die Varianten-Seiten"] = "Tracking-Code fÃ¼r die Varianten-Seiten";
//$lang["Tracking-Code fur die Bestatigungsseite"] = "Tracking-Code fÃ¼r die Danke- oder Bestatigungsseite";
// visual test factor dialogue
/*
$lang['Content variations'] = "Process element variants";
$lang['Factor Name'] = "Select a name for the element";
$lang['Factor Name description'] = "The name is needed in the overview of the combinations.";
$lang['Enter name of factor here'] = "Example: \"heading\" or \"Submit-Button\"";
$lang['Control'] = "Original contents";
$lang['Control description'] = "This field displays the HTML contents of the element.";
$lang['Click sign'] = "Click here to create a variant.";
*/
$lang['Variant prefix'] = "v";
$lang['Variant label'] = "Variant ";

//edit visual test
//$lang['Edit Visual Test (Step 1 of 2)'] = "Multivariaten Test bearbeiten (Schritt 1 von 2)";
//$lang['Edit Visual Test (Step 2 of 2)'] = "Multivariaten Test bearbeiten (Schritt 2 von 2)";

//edit visual a/b test
$lang['Edit Visual A/B Test (Step 1 of 3)'] = "Edit Visual A/B test";
$lang['Edit Visual A/B Test (Step 2 of 3)'] = "Determine project name and URL";
$lang['Edit Visual A/B Test (set allocation)'] = "Traffic Allocation";
$lang['Edit Visual A/B Test (Step 3 of 3)'] = "Determine conversion goals";
$lang['Save variants'] = "Save changes";
$lang["Save approach"] = "Save changes";
$lang['Save goals'] = "Save changes";

// A/B
$lang["Create A/B Test (Step 1 of 4)"] = "Enter URLs";
$lang["Edit A/B Test (Step 1 of 2)"] = "Enter URLs";

$lang["Enter variants of your page"] = "Enter the URL of the variant of the original page here.";
$lang["Description variant"] = "Please enter a name and the URL of the variant.";
$lang["Add a variant"] = "Add a further variant";

$lang["Create A/B Test (Step 3 of 4)"] = "Determine project name and URL";
$lang["Create A/B Test (Step 4 of 4)"] = "Determine conversion goals";

//$lang["Create A/B Test (Step 2 of 3)"] = "Create split URL test (Step 2 of 3)";
//$lang["Edit A/B Test (Step 2 of 2)"] = "Process split URL test";
//$lang["Create A/B Test (Step 3 of 4)"] = "Determine project name and URL";

//$lang["Create A/B Test (Step 3 of 3)"] = "Create split URL test (Step 3 of 3)";
//$lang["Edit A/B Test (Step 2 of 2)"] = "Split-URL-Test bearbeiten";

//deferred impressions (Additional settings)
$lang["Additional settings title"] = "Advanced Settings";
$lang["Additional settings description"] = "Delivery of project depending on DOM element (enter CSS path) or javascript expression:";
$lang["Additional settings options"] = array(
    'not_used' => "always deliver",
    'is_visible' => "deliver when element visible",
    'exists' => "deliver when element exists",
    'expression_true' => "deliver when JS expression true"
);

//IP Blacklisting
$lang["IP blacksliting title"] = "IP Blacklisting";
$lang["IP blacksliting description"] = "You configured to block the following IP addresses for your account:";
$lang["IP blacksliting not ignored"] = "IP Blacklisting shall be in effect  for this project.";
$lang["IP blacksliting ignored"] = "No IP Blacklisting for this project.";

//Project scheduler
$lang["Project schedule title"] = "Start and end time of the project";
$lang["Project schedule description"] = "Define the period for which the pages of your project are to be delivered (date and time in CET).";
$lang["Project schedule start"] = "Start";
$lang["Project schedule end"] = "End";
$lang["Project schedule start tooltip"] = "Select the start date";
$lang["Project schedule end tooltip"] = "Select the end date";
$lang["Datepicker locale month"] = "January,February,March,April,May,June,July,August,September,October,November,December";
$lang["Datepicker locale month short"] = "Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec";
$lang["Datepicker locale days"] = "Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday";
$lang["Datepicker locale days short"] = "Sun,Mon,Tue,Wed,Thu,Fri,Sat";
$lang["Datepicker locale days min"] = "Su,Mo,Tu,We,Th,Fr,Sa";

$lang["Insert the tracking code to your page"] = "Now insert the Tracking Codes into your pages.";
$lang["Insert the tracking code to your page description"] = "Insert the upper code into the original page, ideally directly behind the opening &lt;head&gt;-Tag, if not possible
as close as possible to the top in the source text. Insert the lower code to your confirmation or \"Thank-you\" page.";

$lang["Insert tracking code ab event description"] = "Insert the upper code into the original page, ideally directly behind the opening &lt;head&gt;-Tag. 
Insert the lower code into each of your variant pages in precisely the same manner.";
$lang["Insert three tracking codes to your page description"] = "Insert the upper code into the original page, ideally directly behind the 
opening &lt;head&gt;-Tag, if not possible as close as possible to the top in the source text. Insert the centre code in precisely the same manner into each of your 
variant pages and the lower code into your confirmation or \"Thank-you\" page.";

$lang["Please enter the name here"] = "(Optional name for the variant)";
//$lang["tooltip_variant"] = "tooltip_variant";
//$lang["Description"] = "Description";$lang["Description"] = "Description";


//user test editor
$lang['Test Create Visual A/B Test (Step 1 of 3)'] = "Create visual A/B test (Step 1 of 3)";
//visual ab substep 2
$lang['Test Create Visual A/B Test (Step 2 of 3)'] = "Create visual A/B test (Step 2 of 3)";
$lang['Test Create Visual A/B Test (Step 3 of 3)'] = "Create visual A/B test (Step 3 of 3)";


$lang['Seite jetzt bearbeiten.'] = "Process the page now.";
$lang['Abbrechen und zurueck zum Dashboard'] = "Cancel and return to the dashboard";
$lang['Abbrechen und zurueck zum Details'] = "Cancel";
$lang['Abbrechen'] = "Cancel";
$lang['Rename variant'] = "Rename variant";

$lang['How many visitors shall be allocated'] = "Number of project participants";
$lang['How many visitors shall be allocated description'] = "Enter here the percentage of all visitors to the original page who should participate in the project.";

$lang['Allocation for each variant'] = "Traffic allocation for variants";
$lang['Allocation for each variant intro'] = "Select the traffic distribution per variant via the sliders or input fields.";
$lang['Allocation reset link'] = "Reset Distribution.";

$lang['Disable all scripts in page'] = "Disable all scripts on the page";
$lang['Disable all scripts in page info'] = "If the editor doesn’t work with the current configuration, you can disable all scripts by using this checkbox and try again.";

/* confirm delete variant */

$lang['Confirm delete variant heading'] = "Delete variant";
$lang['Confirm delete variant copy'] = "Do you wish to delete this variant irrevocably? All changes made to the variant by you are not saved.";
//$lang['Cancel delete variant']='Cancel' - Use "Abbrechen"
$lang['Confirm variant delete button']= "Delete";


$lang['Editor Page Unload']="Do you really wish to exit the page? Changes may be lost";/* message used in window.onbeforeunload dialog*/
$lang['Editor Visitor Page Heading']="Visual editor"; /* heading vistor sees at top of page*/
$lang['Editor Page Title'] = "BlackTri Visual AB Editor"; /* for title tag */

$lang['Editor Visitor Next Step Button']="Test BlackTri now for 30 days";
$lang['Editor Visitor Cancel Button']="Cancel";
if($tenant == 'etracker') {
	$lang['Editor Visitor Next Step Button']="Test Page Optimizer now for 21 days";
	$lang['Editor Visitor Cancel Button']="Cancel";
}
$lang['Smart message'] = "Smart Message";
$lang['Smart message Add'] = "Add";
$lang['Smart message Hide'] = "Hide";
$lang['Smart message Show'] = "Show";
$lang['Smart message Edit'] = "Edit";
$lang['Smart message Delete'] = "Delete";

/* Editing actions */
$lang['Edit html'] = 'Edit HTML';
$lang['Edit html source...'] = 'Edit Element HTML';
$lang['Edit element text'] = 'Edit Element Text';
$lang['Edit Styles']="Edit Styles";
$lang['Edit text'] = 'Edit Text';
$lang['Edit custom css...'] = 'Edit CSS';
$lang['Edit custom js...'] = 'Edit Javascript';
$lang['Save'] = 'Save';
$lang['Cancel'] = 'Cancel';

$lang['Hide element'] = 'Hide element';
$lang['Remove element'] = 'Remove element';
$lang['Move element'] = 'Move element';
$lang['Select parent element'] = 'Select parent';
$lang['Editor SOURCE'] = 'Source';
$lang['Editor DESIGN'] = 'Design';
$lang['Click to copy to clipboard'] = 'Click to copy jQuery path to clipboard';
$lang['Edit link']="Edit Link";
$lang['Save link'] = "Save changes";
$lang['Enter link URL']="Enter link URL";

$lang['Track click goals']="Track click goals";
$lang['Edit click goals']="Edit click goals";
$lang['Highlight goals hide']="Hide Goals";
$lang['Highlight goals display']="Highlight goals";
$lang['Enter goal name']="Enter Goal Name";
$lang['Enter goal name short']="Goal Name";
$lang['Enter goal selector short']="Dom Selector";
$lang['Click goal name']="Click goal";
$lang['Click goal prefix']="Click Goal: ";
$lang['CLick goal taglabel'] = "Click";
$lang['Click goal advanced'] = "Advanced";
$lang['Click goal remove'] = "Remove Goal";

$lang['Edit image']="Edit Image";
$lang['Save image'] = "Save changes";
$lang['Enter image URL']="Enter image URL";

/* update missing */
$lang['Editor page heading']="Visual Editor";
$lang['Editor test customization']="Determine project name and URL";
$lang['Editor goals customization']="Editor goals customization";

$lang['Rearrange control title'] = "Reposition Element";
$lang['Rearrange control description'] = "Drag element within parent";
$lang['Rearrange element'] = "Reposition Element";

$lang['You can now edit the page. Some elements are still downloaded.'] = 'You can now edit the page. Some elements are still downloading.';

$lang['Error on page load'] = 'Error while loading the page';
$lang['Client page has invalid html'] = 'The document could not be loaded because the HTML is corrupt.';

$lang['Device type to load with'] = 'Device type to load with';
$lang['Smartphone'] = 'Smartphone';
$lang['Tablet'] = 'Tablet';
$lang['Desktop'] = 'Desktop';

$lang['change url title'] = 'Keep changes made in editor?';
$lang['change utl text'] = 'You have created changes in one or more variants for the page, '
        . 'these changes might not be applicable anymore after changing the page URL. Do you want to proceed keeping the changes, '
        . 'or should we discard all changes so you can start from a blank site';
$lang['change url keep'] = 'Keep the changes';
$lang['change url undo'] = 'Undo all changes';

$lang['IP Filtering'] = 'Deliver project depending on IP address';
$lang['IP Filtering description'] = 'Enter one or more IPs and choose wether the project shall be delivered exclusively for those IPs, or they shall be excluded from the project.';

$lang['Ignore IP address'] = 'Ignore IP address';
$lang['Allow IP address'] = 'Allow IP address';
$lang['Exclude for IP address'] = 'Exclude for IP address';