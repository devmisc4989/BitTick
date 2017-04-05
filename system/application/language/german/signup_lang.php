<?php
$CI =& get_instance();
$tenant = $CI->config->item('tenant');

if($tenant == 'dvlight')
	$lang['signup_thankshead'] = "Wir freuen uns, dass Sie A/B Tester testen!";
else
	$lang['signup_thankshead'] = "Wir freuen uns, dass Sie BlackTri testen!";
$lang['signup_thanksspan'] = "&nbsp";
$lang['title_unamepwd'] = "Wählen Sie einen Benutzernamen und ein Passwort";
$lang['title_billinfo'] = "Bestätigen Sie nun Ihre Anmeldung";
$lang['signup_terms_before'] = "Ich habe die Regelungen zu ";
$lang['signup_terms_after'] = " gelesen und erkenne sie an.";

$lang['signup_terms_divolution_pdf'] = "http://abtester.divolution.com/wp-content/uploads/2013/07/DIVOLUTION-AGB-AB-Tester-Stand-01.07.2013.pdf";

$lang['title_test_user_info'] = "Melden Sie sich jetzt kostenlos an";
$lang['title_test_user_subline'] = "Nur noch wenige Minuten bis zu Ihrem ersten Test!";

if($tenant == 'dvlight')
	$lang['desc_test_user_info'] = "Um Ihren Test speichern zu können benötigen Sie ein A/B Tester Benutzerkonto. Das ist kostenlos und risikofrei:
<br/><br/>Sie können 30 Tage lang völlig ohne Verpflichtungen testen. Wenn Sie A/B Tester danach nicht 
weiterbenutzen möchten, dann lassen Sie die Testphase einfach verstreichen – eine Kündigung ist nicht nötig. Wenn Sie mit A/B Tester zufrieden sind, 
dann buchen Sie anschließend einen Tarif. Erst dann benötigen wir Ihre Zahldaten.";
else 
	$lang['desc_test_user_info'] = "Um Ihren Test speichern zu können benötigen Sie ein BlackTri Optimizer Benutzerkonto. Das ist kostenlos und risikofrei:
<br/><br/>Sie können 30 Tage lang völlig ohne Verpflichtungen testen. Wenn Sie BlackTri Optimizer danach nicht 
weiterbenutzen möchten, dann lassen Sie die Testphase einfach verstreichen – eine Kündigung ist nicht nötig. Wenn Sie mit BlackTri Optimizer zufrieden sind, 
dann buchen Sie anschließend einen Tarif. Erst dann benötigen wir Ihre Zahldaten.";

$lang['button_test_user_info'] = "Registrieren";
$lang['cancel_test_user_info'] = "Abbrechen";

$lang['title_test_user_done'] = "Vielen Dank für Ihre Registrierung";
$lang['title_test_user_done_subline'] = "Sie können jetzt loslegen!";
$lang['desc_test_user_done'] = "Wir haben Ihre Daten gespeichert und werden Ihnen in wenigen Minuten eine Bestätigungs-Email zusenden. Bitte folgen Sie den
Hinweisen in dieser Email um die Registrierung abzuschließen.<br/><br/>Sie können aber schon jetzt sofort den Test speichern und starten!";
$lang['button_test_user_done'] = "Test fertigstellen";


$lang['signup_thanksdescription'] = "Sofort nach der Anmeldung können Sie Ihren ersten A/B-Test erstellen!";
$lang['signup_totaluser'] = "Keine Überraschungen";
$lang['signup_everyweek'] = "Sie gehen keine Verpflichtungen ein, und wir erfragen Ihre Zahlungsdaten erst nach der 30-Tage-Testphase.";
$lang['signup_secure'] = "Garantierter Datenschutz";
$lang['signup_securedescription'] = "Wir werden zu keiner Zeit Ihre Daten weitergeben. Sollten Sie Ihre Registrierung beenden wollen, werden Ihre Daten gelöscht.";

$lang['title_createaccount'] = "Legen Sie hier Ihren kostenlosen Account an";
$lang['signup_button'] = "Anmeldung absenden";

$lang['beta_topline'] = "Registrierung zum Beta-Programm";
$lang['beta_headline'] = "Vielen Dank für Ihre Registrierung!";
$lang['beta_copy'] = "
<p>Wir haben Ihre Daten gespeichert und werden Ihnen in wenigen Minuten eine Bestätigungs-Email zusenden. Bitte folgen Sie den
Hinweisen in dieser Email um die Registrierung abzuschließen.</p>
<p>Wir werden Ihr Kunden-Konto nach einer Prüfung in den nächsten Tagen für Sie freischalten.</p>
<p><b>Was geschieht als nächstes?</b><br>
Sobald wir Ihr Konto freigeschaltet haben werden wir Sie mit einer weiteren Email informieren. Sie können sich dann mit dem von Ihnen gewählten Usernamen und Passwort einloggen.</p>";

// signup errors
$lang['title_mailsenderror'] = "Mail sending failed";
$lang['title_mailregisterederror'] = "Diese Emailadresse wird bereits von einem anderen Benutzer verwendet.";
$lang['title_usernameerror'] = "Username not available";
$lang['title_dberror'] = "Database Error!Please try later";

