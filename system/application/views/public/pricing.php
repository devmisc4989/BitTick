<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$purl = $this->config->item('page_url');
$lg = $this->config->item('language');
$this->lang->load('price');
$plans = $this->config->item('PLAN');

$regurl_starter = $basesslurl . $purl[$lg]['register'] . $plans['PLAN_STARTER'];
$regurl_basic = $basesslurl . $purl[$lg]['register'] . $plans['PLAN_BASIC'];
$regurl_professional = $basesslurl . $purl[$lg]['register'] . $plans['PLAN_PROFESSIONAL'];
$regurl_enterprise = "mailto:" . $this->lang->line('plan_mailto') .
    "?subject=" . $this->lang->line('plan_enterprise_mailto_subject') .
    "&amp;body=" . $this->lang->line('plan_mailto_body');
$regurl_teasertool = "mailto:" . $this->lang->line('plan_mailto') .
    "?subject=" . $this->lang->line('plan_teasertool_mailto_subject') .
    "&amp;body=" . $this->lang->line('plan_mailto_body');
?>
<div class="pricing_zone">
    <div class="pricing-container">
        <h1><?php echo $this->lang->line('price_trialmessage'); ?></h1>
        <h5><?php echo $this->lang->line('price_signupmessage'); ?></h5>
        <div class="pricing-wrap">
            <div class="pricing-box"><span class="pricing-plan"><?php echo $this->lang->line('plan_headline_1'); ?></span>
                <h5><?php echo $this->lang->line('plan_pricepoint_1'); ?></h5>
                <h4 class="superscript_text"><?php echo $this->lang->line('plan_pricepoint_ann_1'); ?></h4>
                <h4><?php echo $this->lang->line('plan_subline_1'); ?></h4>
                <h3><?php echo $this->lang->line('plan_1feature_1'); ?></h3>
                <h3><?php echo $this->lang->line('plan_2feature_1'); ?></h3>
				<a href="<?php echo $regurl_starter; ?>" class="button signup1"><?php echo $this->lang->line('button_signup'); ?></a>
            </div>
            <div class="pricing-box-big"><span class="pricing-plan-big"><?php echo $this->lang->line('plan_headline_2'); ?></span>
                <h5><?php echo $this->lang->line('plan_pricepoint_2'); ?></h5>
                <h4 class="superscript_text"><?php echo $this->lang->line('plan_pricepoint_ann_2'); ?></h4>
                <h4><?php echo $this->lang->line('plan_subline_2'); ?></h4>
                <h3><?php echo $this->lang->line('plan_1feature_2'); ?></h3>
                <h3><?php echo $this->lang->line('plan_2feature_2'); ?></h3>
				<a href="<?php echo $regurl_basic; ?>" class="button signup1"><?php echo $this->lang->line('button_signup'); ?></a>
            </div>
            <div class="pricing-box"><span class="pricing-plan"><?php echo $this->lang->line('plan_headline_3'); ?></span>
                <h5><?php echo $this->lang->line('plan_pricepoint_3'); ?></h5>
                <h4><?php echo $this->lang->line('plan_subline_3'); ?></h4>
                <h3><?php echo $this->lang->line('plan_1feature_3'); ?></h3>
                <h3><?php echo $this->lang->line('plan_2feature_3'); ?></h3>
				<a href="<?php echo $regurl_enterprise; ?>" class="button signup1"><?php echo $this->lang->line('plan_sendrequest'); ?></a>
            </div>
            <div class="pricing-box"><span class="pricing-plan"><?php echo $this->lang->line('plan_headline_4'); ?></span>
                <h5><?php echo $this->lang->line('plan_pricepoint_4'); ?></h5>
                <h4><?php echo $this->lang->line('plan_subline_4'); ?></h4>
                <h3><?php echo $this->lang->line('plan_1feature_4'); ?></h3>
                <h3><?php echo $this->lang->line('plan_2feature_4'); ?></h3>
				<a href="<?php echo $regurl_teasertool; ?>" class="button signup1"><?php echo $this->lang->line('plan_sendrequest'); ?></a>
            </div>
        </div>
        <div class="superscript_description"><?php echo $this->lang->line('price_annual_note'); ?></div>
    </div>
</div>
<div id="main_container">
    <div class="table_line">
		<div class="pricing-title">
			<h1><?php echo $this->lang->line('price_mainmessage'); ?> </h1>
		</div>
		
        <table class="table table100">
            <tbody>
            <tr class="table-title">
                <td style="width:220px;"></td>
                <td><?= $this->lang->line('plan_headline_1'); ?></td>
                <td><?= $this->lang->line('plan_headline_2'); ?></td>
                <td><?= $this->lang->line('plan_headline_3'); ?></td>
                <td style="width:120px;" class="table-last"><?= $this->lang->line('plan_headline_4'); ?></td>
            </tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('visual editor'); ?></td>       <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('split test'); ?></td>          <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('teaser test'); ?></td>          <td></td>                 <td></td>                <td></td>                 <td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('code editor'); ?></td>         <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('preview mobile'); ?></td>      <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('autopilot'); ?></td>           <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('navigation mode'); ?></td>     <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('preview mode'); ?></td>        <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('segmentation'); ?></td>        <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('targeting'); ?></td>           <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('tag manager'); ?></td>         <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('click goals'); ?></td>         <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('top goals'); ?></td>         <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('url goals'); ?></td>           <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('unlimited tests'); ?></td>     <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('email support'); ?></td>       <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('user manual'); ?></td>         <td class="checkbox"></td><td class="checkbox"></td><td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('multipage test'); ?></td>      <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('multivariate test'); ?></td>   <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('conditional activation'); ?></td><td></td>             <td></td>                   <td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('geoip targeting'); ?></td>     <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('rest api'); ?></td>            <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('snippet on premise'); ?></td>            <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('phone support'); ?></td>       <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            <tr class="table-row"><td class="table-first"><?= $this->lang->line('training'); ?></td>            <td></td>               <td></td>                   <td class="checkbox"></td><td class="table-last checkbox"></td></tr>
            </tbody>
        </table>
    </div>

    <div class="column-left">
        <div class="pricing-des padding-r">
            <h3><?php echo $this->lang->line('price_submessagehead1'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage1'); ?> </p>
        </div>
        <div class="pricing-des">
            <h3><?php echo$this->lang->line('price_submessagehead3'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage3'); ?></p>
        </div>
        <div class="pricing-des">
            <h3><?php echo $this->lang->line('price_submessagehead9'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage9'); ?> </p>
        </div>
        <div class="pricing-des padding-r">
            <h3><?php echo $this->lang->line('price_submessagehead6'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage6'); ?> </p>
        </div>
    </div>
    <div class="column-right">
        <div class="pricing-des padding-r">
            <h3><?php echo $this->lang->line('price_submessagehead2'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage2'); ?> </p>
        </div>
        <div class="pricing-des">
            <h3><?php echo $this->lang->line('price_submessagehead7'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage7'); ?> </p>
        </div>
        <div class="pricing-des">
            <h3><?php echo$this->lang->line('price_submessagehead8'); ?></h3>
            <p><?php printf($this->lang->line('price_submessage8'),$this->config->item('base_ssl_url') . "/users/order"); ?></p>
        </div>
        <div class="pricing-des padding-r">
            <h3><?php echo $this->lang->line('price_submessagehead11'); ?></h3>
            <p><?php echo $this->lang->line('price_submessage11'); ?> </p>
        </div>
    </div>
    <div class="safe-container">
        <h4><?php echo $this->lang->line('price_safesecurehead'); ?></h4>
        <p><?php echo $this->lang->line('price_safesecuredescription'); ?></p>
    </div>
</div>
