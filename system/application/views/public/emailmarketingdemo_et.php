<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('imprint');
?>
<style>
p {
	font-normal;
	line-height:20px;
	
}
h4 {
	margin-top:20px;
	margin-bottom:10px;
	font-weight:bold;
}
.leftblock {
	float:left;
	margin-bottom:50px;
	margin-left:20px;
}
.rightblock {
	font-family:'Ubuntu',​sans-serif;
	font-size:14px;
	line-height:16px;
	float:left;
	margin-left:10px;
	width:300px;
}
.copy {
	margin-left:20px;
	margin-bottom:30px;
	width:900px;
}
</style>



<div id="main_container">
        

            <div class="title"><div class="head_line_title">Anwendungsbeispiel für <?php echo $clientname; ?></div></div>
        
        
    <div class="copy">
        <h4>Optimieren Sie die Landingpages Ihrer Kunden</h4>
        <p></p>
        <p>Email-Marketing-Systeme können beim Erstellen und Versenden der Emails auf Parameter wie Name und Anrede 
        bzw. Geschlecht des Empfängers zurückgreifen, oft stehen sogar noch weitergehende Daten zur Verfügung.</p>
        
        <p>Anstatt die Empfänger von einer <b>personalisierten Email</b> zu einer <b>statischen Landingpage</b> zu führen, sollten 
        diese Parameter idealerweise eingesetzt werden, um die Landingpage gleichermaßen mit <b>dynamischen Inhalten</b> zu befüllen und ggfs.
        sogar das <strong>Aussehen</strong> anzupassen.
        </p>
        
        <h4>Integration von <?php echo $productname; ?> mit etracker Testing + Targeting / BlackTri</h4>
        <p>Die Integration bietet folgende Funktionen:</p>
		<p><b>Einfügen von dynamischem Content</b> in die Landingpage, etwa dem Namen des Empfängers.</p>
		<p><b>Visueller Point-and-click-Editor</b> zum Erstellen von Variationen der Landingpage, etwa Ausblenden 
		von Elementen, Text- und Layoutänderungen etc.</p>
		<p><b>Ausspielen von Variationen</b> durch Injektion des Contents in eine bestehende Landingpage.</p>
        <p><b>Verwendung von Segmentierungsregeln</b>, also Variieren der Landingpage anhand von Daten innerhalb von 
        <?php echo $productname; ?> (etwa dem Geschlecht des Empfängers) oder anderer Eigenschaften, wie Endgerät oder 
        Verhalten des Empfängers in früheren Visits.
        
        <h4>Wie das ganze funktioniert?</h4>
        <p><b>Umgesetzt wird die Erweiterung ohne einen Eingriff in die Landingpage.</b></p>
        
		<p>Es muss einmalig ein Trackingcode in die 
		Landingpages des Kunden eingesetzt werden, es sind keine weiteren technischen Änderungen auf Kundenseite notwendig.</p>
        
        <h4>Demo: So könnte es aussehen</h4>
        
        <p>Der unten gezeigte Screenshot eines Newsletter führt beim Klicken auf ein (fiktives) Beispiel einer Landingpage,
        in dem der <b>Name des Empfängers in die Headline</b> eingesetzt wird. Das Geschlecht wird 
        verwendet, um sämtliche <b>Angebote für Frauen auf der Landingpage auszublenden</b>. 
        </p>

        <p>Diese Änderungen werden von <i>etracker / BlackTri</i> in die Seite injiziert, es musste keine 
        Änderung am Code an der Seite vorgenommen werden! Zur Verdeutlichung zeigt der zweite Link die Originalseite der Landingpage.
        </p>
    </div>        

    <div class="leftblock">
    	<a href="http://www.eckhardschneider.de/bopdemos/landingpage_nl.html?_p=t&BT_lpid=3113" target="_blank"><img src="/images/demos/nl_tchibo.jpg"></a>
    </div>
    <div class="rightblock">
    	<a href="http://www.eckhardschneider.de/bopdemos/landingpage_nl.html" target="_blank">Hier klicken für die Originalversion der Landingpage.</a>
    </div>
    	</div>
    
    
</div>
