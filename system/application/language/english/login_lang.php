<?php
$CI =& get_instance();
$tenant = $CI->config->item('tenant');

// login
if($tenant == 'dvlight')
	$lang['title_signin'] = "<span>DIVOLUTION® A/B Tester</span> Login";
else
	$lang['title_signin'] = "<span>BlackTri Optimizer</span> Login";
$lang['button_signin'] = "Jetzt anmelden";
$lang['link_forgotpassword'] = "Passwort vergessen?";
$lang['title_tosignup'] = "Sie haben noch kein Benutzerkonto? <br>Jetzt 30 Tage kostenlos testen!";

// forgot password
$lang['title_cantsignin']  = "Passwort vergessen?";
$lang['title_cantsigninoptions']  = "Geben Sie Ihre Email-Adresse ein und wir senden Ihnen eine Email mit einem neuen Passwort.";
$lang['title_cantsignin_inputfield']  = "Email-Adresse";
$lang['title_cantsignin_close']  = "Zurück zum Login-Formular.";

// logout
$lang['title_logoutheadline']  = "Sie wurden erfolgreich abgemeldet.";
$lang['title_logoutdescription']  = "";

