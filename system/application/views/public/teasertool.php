<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$purl = $this->config->item('page_url');
$lg = $this->config->item('language');
$this->lang->load('price');
$this->lang->load('home');

$cta_link = $basesslurl . $purl[$lg]['register'] . '110';
$demo_link = $basesslurl . "users/demo";

?>
<div class="pricing_zone">
    <div class="pricing-container">
        <h1>Mehr Reichweite, mehr Anzeigenumsatz</h1>
        <h5>Headline-A/B-Testing und -Analytics für Medienwebsites</h5>

<script src="//fast.wistia.com/embed/medias/bo2jrjefqx.jsonp" async></script>
<script src="//fast.wistia.com/assets/external/E-v1.js" async></script>
<div class="wistia_embed wistia_async_bo2jrjefqx" style="height:320px;width:522px;margin:auto;margin-top:15px;">&nbsp;</div>

    </div>
</div>
<div id="main_container" style="text-align:center;">
    <div class="pricing-title"  style="text-align:center;width:860px;">
        <h1>Artikelüberschriften ganz einfach optimieren</h1>
    </div>
    
    <div class="terms"  style="text-align:center;" >

    <h3>Verschenken Sie kein Potential durch unwirksame Headlines</h3>
    <p>Die Überschrift eines Artikels entscheidet maßgeblich darüber, ob der Leser den Artikel überhaupt öffnet. Durch Headline-Optimierung kann deshalb der journalistische und wirtschaftliche Erfolg einer Medien-Website deutlich gesteigert werden.</p>

    <h3>Spielend einfach durch Integration in Ihr Redaktionssystem</h3>
    <p>Mit dem Teasertool von BlackTri können Redakteure spielend einfach A/B-Tests für Headlines durchführen und so die erfolgreichste Überschrift finden – und zwar mit einem Klick, und ohne das Content-Management-System verlassen zu müssen.</p>

    <h3>Längere Verweildauern, mehr Page Impressions</h3>
    <p>Der statistische Algorithmus von BlackTri favorisiert Überschriften, die nicht nur eine hohe Klickrate haben, sondern bei denen der Besucher lange auf der Seite verweilt und viele zusätzliche Page Impressions generiert. Auf diese Weise wird “Clickbait” vermieden, und stattdessen die Reichweite und Bindung der Website zum Leser verbessert.</p>
    


    </div>

    <div class="cta_homepage_area"  style="text-align:center;">
        <h1><?php echo $this->lang->line('home_cta_area_head'); ?></h1>
        <h4><?php echo $this->lang->line('home_cta_area_subline'); ?></h4>
        <a class="button signup1" style="margin-left:200px" href="<?= $cta_link ?>"><?php echo $this->lang->line('home_cta_area_button_test'); ?></a>
        <a class="button signup1" style="float:right;margin-right:200px;" href="<?= $demo_link ?>"><?php echo $this->lang->line('home_cta_area_button_demo'); ?></a>    
    </div>



</div>
