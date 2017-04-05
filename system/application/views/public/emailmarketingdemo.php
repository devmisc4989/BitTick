<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('imprint');
?>
<style>
p {
	font-family:'Ubuntu',​sans-serif;
	font-size:14px;
	line-height:20px;
}
.leftblock {
	float:left;
	margin-bottom:50px;
}
.rightblock {
	font-family:'Ubuntu',​sans-serif;
	font-size:14px;
	line-height:16px;
	float:left;
	margin-left:10px;
	width:300px;
}
</style>
<div id="title_bg">
    <div class="title-inner">
        <h2>Anwendungsbeispiel für <?php echo $clientname; ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <h3>Optimieren Sie die Landingpages Ihrer Kunden</h3>
        <p></p>
        <p>Email-Marketing-Systeme können beim Erstellen und Versenden der Emails auf Parameter wie Name und Anrede 
        bzw. Geschlecht des Empfängers zurückgreifen, oft stehen sogar noch weitergehende Daten zur Verfügung.</p>
        
        <p>Anstatt die Empfänger von einer <b>personalisierten Email</b> zu einer <b>statischen Landingpage</b> zu führen, sollten 
        diese Parameter idealerweise eingesetzt werden, um die Landingpage gleichermaßen mit <b>dynamischen Inhalten</b> zu befüllen und ggfs.
        sogar das <b>Aussehen</b> anzupassen.
        </p>
        
        <h3>Integration von <?php echo $productname; ?> mit der BlackTri Optimizer Plattform</h3>
        <p>Die Integration bietet folgende Funktionen:</p>
		<p><b>Einfügen von dynamischem Content</b> in die Landingpage, etwa dem Namen des Empfängers.</p>
		<p><b>Visueller Point-and-click-Editor</b> zum Erstellen von Variationen der Landingpage, etwa Ausblenden 
		von Elementen, Text- und Layoutänderungen etc.</p>
		<p><b>Ausspielen von Variationen</b> durch Injektion des Contents in eine bestehende Landingpage.</p>
        <p><b>Verwendung von Segmentierungsregeln</b>, also Variieren der Landingpage anhand von Daten innerhalb von 
        <?php echo $productname; ?> (etwa dem Geschlecht des Empfängers) oder anderer Eigenschaften, wie Endgerät oder 
        Verhalten des Empfängers in früheren Visits.
        
        <h3>Wie das ganze funktioniert?</h3>
        <p><b>Umgesetzt wird die Erweiterung ohne einen Eingriff in die Landingpage.</b></p>
        
		<p>Es muss einmalig ein kleiner statischer HTML-Code in die 
		Landingpages des Kunden eingesetzt werden, es sind keine weiteren technischen Änderungen auf Kundenseite notwendig.
		Unser System wird in Ihr Backoffice integriert und tritt für den Kunden nicht als zusätzliches System in Erscheinung.</p>
        
        <h3>Demo: So könnte es aussehen</h3>
        
        <p>Der unten gezeigte Screenshot eines Newsletter führt beim Klicken auf ein (fiktives) Beispiel einer Landingpage,
        in dem der <b>Name des Empfängers in die Headline</b> eingesetzt wird. Das Geschlecht wird 
        verwendet, um sämtliche <b>Angebote für Frauen auf der Landingpage auszublenden</b>. 
        </p>

        <p>Diese Änderungen werden von der <i>BlackTri Optimizer Plattform</i> in die Seite injiziert, es musste keine 
        Änderung am Code an der Seite vorgenommen werden! Zur Verdeutlichung zeigt der zweite Link die Originalseite der Landingpage.
        </p>
        
        <p>Dies ist nur ein Beispiel für eine Anwendung, die Plattform kann <b>flexibel an Ihre Wünsche</b> angepasst werden.</p>
    </div>
    
    <div class="leftblock">
    	<a href="http://www.eckhardschneider.de/bopdemos/landingpage_nl.html?_p=t&BT_lpid=3113" target="_blank"><img src="/images/demos/nl_tchibo.jpg"></a>
    </div>
    <div class="rightblock">
    	<a href="http://www.eckhardschneider.de/bopdemos/landingpage_nl.html" target="_blank">Hier klicken für die Originalversion der Landingpage.</a>
    </div>
</div>
