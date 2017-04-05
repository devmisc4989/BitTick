<?php
$CI =& get_instance();
$tenant = $CI->config->item('tenant');

// localized format string for time and date
$lang['dateformat']  = "d.m.Y";
$lang['timeformat']  = "H:i";

// email confirmation page
$lang['emails_emailconfirmtopline']  = "Abschluss Ihrer Registrierung";
$lang['emails_emailconfirmheadline']  = "Vielen Dank, Ihre Email-Adresse wurde hiermit bestätigt.";
$lang['emails_emailconfirmcopy']  = "Wir können Sie nun per Email informieren wenn Ergebnisse Ihrer Optimierungen vorliegen oder Ihnen per Email ein neues Passwort zusenden.";
$lang['emails_emailconfirmbuttonheadline']  = "Klicken Sie hier";
$lang['emails_emailconfirmbuttonsubline']  = "um sich jetzt einzuloggen!";

// welcome email
$lang['emails_confirmationmailsubject']  = "Bestätigung Ihrer Registrierung bei BlackTri Optimizer";
$lang['emails_confirmationmail_salutation']  = "Guten Tag ";
$lang['emails_confirmationmailtext1']  = "Willkommen bei BlackTri Optimizer, wir freuen uns, dass Sie unser Produkt ausprobieren! 
<br>Um Ihre Registrierung abzuschließen, klicken Sie bitte auf den unten stehenden Link. Sollte der Link nicht funktionieren, dann kopieren Sie ihn bitte und fügen ihn in die Adresszeile Ihres Browsers ein:";
$lang['emails_confirmationmailtext2']  = "Bei Fragen kontaktieren Sie bitte unseren Support unter support@blacktri.com";
$lang['emails_confirmationmailfooter']  = "Ihr Team von BlackTri Media<br>Hochfeld 1b, 22607 Hamburg";
if($tenant == 'dvlight') {
	$lang['emails_confirmationmailsubject']  = "Bestätigung Ihrer Registrierung bei DIVOLUTION A/B Tester";
	$lang['emails_confirmationmailtext1']  = "Willkommen bei DIVOLUTION A/B Tester, wir freuen uns, dass Sie unser Produkt ausprobieren! 
	<br>Um Ihre Registrierung abzuschließen, klicken Sie bitte auf den unten stehenden Link. Sollte der Link nicht funktionieren, dann kopieren Sie ihn bitte und fügen ihn in die Adresszeile Ihres Browsers ein:";
	$lang['emails_confirmationmailtext2']  = "Bei Fragen kontaktieren Sie bitte unseren Support unter abtester@divolution.com";
	$lang['emails_confirmationmailfooter']  = "Das A/B Tester Team von DIVOLUTION<br>Westerfeldstr. 8<br>32758 Detmold";	
}

// validation email
$lang['emails_validationmailsubject']  = "BlackTri Optimizer - Bitte bestätigen Sie Ihre Emailadresse";
$lang['emails_validationmailtext1']  = "um Ihre Emailadresse zu bestätigen, klicken Sie bitte auf den unten stehenden Link. Sollte das nicht funktionieren, dann kopieren Sie ihn bitte und fügen ihn in die Adresszeile Ihres Browsers ein:";
$lang['emails_validationmailtext2']  = "Bei Fragen kontaktieren Sie bitte unseren Support unter support@blacktri.com";
$lang['emails_validationmailfooter']  = "Ihr Team von BlackTri Media<br>Hochfeld 1b, 22607 Hamburg";
if($tenant == 'dvlight') {
	$lang['emails_validationmailsubject']  = "DIVOLUTION A/B Tester - Bitte bestätigen Sie Ihre Emailadresse";
	$lang['emails_validationmailtext2']  = "Bei Fragen kontaktieren Sie bitte unseren Support unter abtester@divolution.com";
	$lang['emails_validationmailfooter']  = "Das A/B Tester Team von DIVOLUTION<br>Westerfeldstr. 8<br>32758 Detmold";	
}

// email confirmation error page
$lang['emails_emailconfirmtopline_error']  = "Abschluss Ihrer Registrierung";
$lang['emails_emailconfirmheadline_error']  = "Es ist leider ein Fehler aufgetreten.";
$lang['emails_emailconfirmcopy_error']  = "Wir konnte keine Registrierungsdaten finden, die zu Ihrer Emailadresse gehören. Bitte versuchen Sie, wie in der Email beschrieben, den Bestätigungslink in die 
Adresszeile Ihres Browsers zu kopieren.</p><p>Wenn dies nicht weiterhilft, wenden Sie sich bitte per Email an unseren Support: support@blacktri.com.</p>";
if($tenant == 'dvlight') {
	$lang['emails_emailconfirmcopy_error']  = "Wir konnte keine Registrierungsdaten finden, die zu Ihrer Emailadresse gehören. Bitte versuchen Sie, wie in der Email beschrieben, den Bestätigungslink in die 
	Adresszeile Ihres Browsers zu kopieren.</p><p>Wenn dies nicht weiterhilft, wenden Sie sich bitte per Email an unseren Support: abtester@divolution.com.</p>";
}

// email change notification mail
$lang['emails_changenotifysubject']  = "BlackTri Optimizer - Ihre Emailadresse wurde geändert";
$lang['emails_changenotifytext1']  = "Ihre Emailadresse wurde am %s um %s geändert. Bitte kontaktieren Sie uns, wenn diese Änderung durch jemand anderen ohne Ihr Wissen durchgeführt wurde.
								<br><b>Wenden Sie sich bitte per Email an unseren Support: support@blacktri.com.</b>";
if($tenant == 'dvlight') {
	$lang['emails_changenotifysubject']  = "DIVOLUTION A/B Tester - Ihre Emailadresse wurde geändert";
	$lang['emails_changenotifytext1']  = "Ihre Emailadresse wurde am %s um %s geändert. Bitte kontaktieren Sie uns, wenn diese Änderung durch jemand anderen ohne Ihr Wissen durchgeführt wurde.
									<br><b>Wenden Sie sich bitte per Email an unseren Support: abtester@divolution.com.</b>";
}

$lang['emails_linktotest'] = "Klicken Sie hier um die Testdetails zu sehen:";
$lang['emails_salutation'] = "Guten Tag ";

// leadermail
$lang['emails_leader_subject'] = "Zwischenstand: Eine Variante liegt in Führung!";
$lang['emails_leader_headline'] = " Steigerung der Konversion im Test ";
$lang['emails_leader_copy1'] = "eine der Varianten Ihres Tests hat eine bessere Konversionsrate als die Originalseite.
Möglicherweise wird eine noch erfolgreichere Variante gefunden, dazu muss der Test aber noch für einige Zeit laufen.";
$lang['emails_leader_copy2'] = "Die Originalseite hat eine Konversionsrate von ";
$lang['emails_leader_copy3'] = "Die folgende Variante hat eine Konversionsrate von ";

// winnermail
$lang['emails_winner_subject'] = "Test %s mit %s Verbesserung erfolgreich beendet!";
$lang['emails_winner_headline'] = "Test %s wurde erfolgreich beendet.";
$lang['emails_winner_copy1'] = "die folgende Variante hat mit <b>%s</b> die höchste Konversion aller getesteten Seiten:"; 
$lang['emails_winner_copy2'] = "Die Originalseite hat eine Konversion von <b>%s</b>, damit wurde eine Steigerung von <b>%s</b> erzielt.<br>
Dieses Ergebnis hat eine statistische Verlässlichkeit von 95%, es haben %s Besucher an dem Test teilgenommen.
";

// losermail
$lang['emails_loser_subject'] = "Test %s ohne Erfolg beendet.";
$lang['emails_loser_headline'] = "Im Test %s hat die Originalseite die beste Konversion.";
$lang['emails_loser_copy1'] = "die Originalseite hat mit <b>%s</b> die höchste Konversion aller getesteten Seiten. 
Dieses Ergebnis hat eine statistische Verlässlichkeit von 95%%, es haben %s Besucher an dem Test teilgenommen.";

// New password
$lang['emails_newpw_subject']  = "Ihr neues Passwort für BlackTri Optimizer";
$lang['emails_newpw_salutation']  = "Guten Tag ";
$lang['emails_newpw_text1']  = "Sie haben ein neues Passwort für BlackTri Optimizer angefordert. Ihr neues Passwort lautet:";
$lang['emails_newpw_text2']  = "Bitte ändern Sie es nach dem nächsten Einloggen!";
if($tenant == 'dvlight') {
	$lang['emails_newpw_subject']  = "Ihr neues Passwort für DIVOLUTION A/B Tester";
	$lang['emails_newpw_text1']  = "Sie haben ein neues Passwort für DIVOLUTION A/B Tester angefordert. Ihr neues Passwort lautet:";
}

// newsletter-opt-in
$lang['large_nloptin_head'] = "Möchten Sie regelmäßig unsere A/B-Testing-Praxistipps erhalten?";
$lang['large_nloptin_subline'] = "Tragen Sie sich in unseren Email-Verteiler ein und wir senden Ihnen unsere wertvollsten Infos:";
$lang['large_nloptin_bullet1'] = "A/B-Testing-Experimente";
$lang['large_nloptin_bullet2'] = "Anleitungen und Conversion-Tipps der Optimierungsprofis";
$lang['large_nloptin_bullet3'] = "Kein Spam, <strong>wir geben niemals Ihre Email-Adresse weiter!</strong>";
$lang['large_nloptin_cta'] = "Einfach Email-Adresse eingeben und <strong>\"Jetzt anmelden\"</strong> klicken!";
$lang['large_nloptin_submit'] = "Jetzt anmelden!";

// autoresponder messages
$lang['auto_subject_PR_HELLO']  = "Danke, dass Sie BlackTri Optimizer nutzen!";
$lang['auto_text_PR_HELLO']  = "Guten Tag %s,
<br>
<br>ich möchte mich persönlich bei Ihnen dafür bedanken, dass Sie unser Produkt ausprobieren. Wenn Sie irgendeine Art von Hilfe
benötigen, so lassen Sie es mich bitte wissen: schreiben Sie mir eine Email an eckhard.schneider@blacktri.com.
<br>
<br>Herzliche Grüße,
<br>Eckhard Schneider, Gründer
<br>BlackTri Media
<br>Hochfeld 1b, 22607 Hamburg";

$lang['auto_subject_PR_NEED_HELP']  = "Brauchen Sie Unterstützung bei BlackTri Optimizer?";
$lang['auto_text_PR_NEED_HELP']  = "#mailheader#<h1>Guten Tag %s,</h1>
<p>Sie haben sich vor kurzem für unseren Dienst angemeldet. Wenn Sie Probleme bei der Nutzung von BlackTri Optimizer
haben, so würden wir uns freuen, Ihre Fragen im Rahmen einer Online-Demo zu beantworten. Wenn Sie Interesse haben, so 
können wir gerne auch sehr kurzfristig einen Termin vereinbaren. Schreiben Sie uns einfach ein Mail an support@blacktri.com.</p>
<p>Herzliche Grüße,
<br>Ihr BlackTri Support-Team
<br>BlackTri Media
<br>Hochfeld 1b, 22607 Hamburg</p>#mailfooter#";