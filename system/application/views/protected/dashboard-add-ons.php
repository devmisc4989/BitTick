<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('editor');
$this->lang->load('personalization');
$available_goals = $this->config->item('available_goals');
$tenant = $this->config->item('tenant');

$smsClass = ($smsLevel == 'disabled') ? 'disabled' : '';
$confClass = ($smsLevel == 'hidden' && $tenant == 'etracker') ? 'large' : '';
$btnClass = ($smsLevel == 'hidden' || $tenant != 'etracker') ? 'large' : 'short';
$containerClass = ($smsLevel != 'hidden' && $tenant == 'etracker') ? 'wider-confirmation' : '';
$clickSms = ($smsLevel == 'disabled' || $smsLevel == 'hidden') ? 'javascript:void(0);' : 'createSmartMessage()';

$addDelClass = ($allowedVariants == -1) ? '' : 'disabled';
$clickDel = ($allowedVariants == -1) ? 'DeleteVariant(1);' : 'javascript:void(0);';
$clickAdd = ($allowedVariants == -1) ? 'javascript:AddVariant();' : 'javascript:void(0);';

if($testingLevel == 'disabled'){
    $clickVisual = 'javascript:void(0);';
    $clickSplit = 'javascript:void(0);';
}else{
    $testingLevel = '';
    $clickVisual = 'CreateVisualAB(1)';
    $clickSplit = 'CreateAB(1)';
}

if($multipageLevel == 'disabled'){
    $multipageButtonId = 'disabled';
}else{
    $multipageLevel = '';
    $multipageButtonId = 'dash-create-mptest';
}

if($teasertestLevel == 'disabled'){
    $teasertestButtonId = 'disabled';
}else{
    $teasertestLevel = '';
    $teasertestButtonId = 'dash-create-teasertest';
}

echo '<input id="etracker_product_upgrade" type="hidden" value="' . $this->config->item('etracker_product_upgrade') . '" />';
echo '<input id="current_testing_level" type="hidden" value="' . $testingLevel . '" />';
echo '<input id="current_sms_level" type="hidden" value="' . $smsLevel . '" />';
?>      

<script type="text/javascript">
    var bt_clickgoals_vars = {
        goalType: '',
        goalPrefix: '',
        goalLabel: '',
        goalTagLabel: '',
        pleaseSelect: "<?= $this->lang->line('Choose Target Page'); ?>",
        testtypeSplit: true,
        testtypeVisual: false,
        testtypeMultipage: false
    };
</script>

<!-- Wizard steps here -->        
<div style="display:none;">        
    <div class="confirmation confirmation-user  <?php echo $containerClass; ?>" id="ws1">
        <h1><?php echo $this->lang->line('Create a test'); ?></h1>
        <div><?php echo $this->lang->line('Create a test description'); ?><br><?php echo splink('wizard_step1'); ?></div>

        <div id="project_type_table" class="<?= $tenant ?>">
            
            <div class="project_type_tr">
                
                <div class="project_type_td confirmation-field  <?php echo $confClass . ' ' . $tenant . ' ' . $testingLevel; ?>" id="vab">
                    <label><?php echo $this->lang->line('Visual A/B test'); ?></label>
                    <span class="testype-rocket  <?php echo $tenant . ' ' . $testingLevel; ?>">
                        <i class="fa fa-rocket"></i>
                    </span>
                    <div class="clear"></div>
                    <div class="ws1_text"><?php echo $this->lang->line('Visual A/B test description'); ?></div>
                </div>

                <div class="project_type_td confirmation-field cf-right  <?php echo $confClass . ' ' . $tenant . ' ' . $testingLevel; ?>" id="ab1">
                    <label><?php echo $this->lang->line('New A/B test'); ?></label>
                    <span class="testype-rocket  <?php echo $tenant . ' ' . $testingLevel; ?>">
                        <i class="fa fa-rocket"></i>
                    </span>
                    <div class="clear"></div>
                    <div class="ws1_text"><?php echo $this->lang->line('New A/B test description'); ?></div>
                </div>
                
            </div>

            <div class="clear"></div>
            <div class="project_type_tr">
                <div class="project_type_td confirmation-field  <?= $confClass . ' ' . $tenant . ' ' . $testingLevel; ?>"> 
                    <input type="button" class="testBtn button ok nomargin  <?= $btnClass . ' ' . $testingLevel; ?>" 
                           value="<?= $this->lang->line('Create Visual A/B Test now!'); ?>" onclick="<?= $clickVisual; ?>"/>
                    <div class="clear"></div>
                </div>

                <div class="project_type_td confirmation-field cf-right  <?= $confClass . ' ' . $tenant . ' ' . $testingLevel; ?>"> 
                    <input type="button" class="testBtn button ok nomargin <?= $btnClass . ' ' . $testingLevel; ?>" 
                           value="<?= $this->lang->line('Create A/B Test now!'); ?>" onclick="<?= $clickSplit; ?>"/>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="clear"></div>
            
            <div class="project_type_tr">
                <div class="project_type_td confirmation-field <?php echo $confClass . ' ' . $tenant . ' ' . $multipageLevel; ?>" id="mpt1">
                    <label><?= $this->lang->line('New Multipage test'); ?></label>
                    <div class="clear"></div>
                    <div class="ws1_text"><?= $this->lang->line('Multipage test description'); ?></div>
                </div>
                
                <div class="project_type_td confirmation-field cf-right <?php echo $confClass . ' ' . $tenant . ' ' . $teasertestLevel; ?>" id="tt1">
                    <label><?= $this->lang->line('New Teaser test'); ?></label>
                    <div class="clear"></div>
                    <div class="ws1_text"><?= $this->lang->line('Teaser test description'); ?></div>
                </div>
            </div>
                
            <div class="project_type_tr">
                <div class="project_type_td confirmation-field"> 
                    <input type="button" id="<?= $multipageButtonId; ?>" class="testBtn button ok nomargin <?= $btnClass . ' ' . $multipageLevel; ?>" 
                           value="<?= $this->lang->line('Create Multipage Test now!'); ?>"/>
                    <div class="clear"></div>
                </div>
                
                <div class="project_type_td confirmation-field cf-right"> 
                    <input type="button" id="<?= $teasertestButtonId; ?>" class="testBtn button ok nomargin <?= $btnClass . ' ' . $teasertestLevel; ?>" 
                           value="<?= $this->lang->line('Create Teaser Test now!'); ?>"/>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
            
        </div>

    </div>

    <!-- Visual AB Test -->

    <div class="confirmation confirmation-user" id="vab2_1">
        <h1><?php echo $this->lang->line('Create Visual A/B Test (Step 1 of 4)'); ?></h1>
        <form id="frmVisualABStep1" name="frmVisualABStep1" method="post" action="javascript:void(0);">
            <div class="confirmation-field w100">
                <label><?php echo $this->lang->line('Enter URL of your page'); ?></label>							
                <div><?php echo $this->lang->line('Enter URL of your page description'); ?> <?php echo splink('wizard_step2'); ?></div>
                <input type="text" class="<?php if (editor_url_validation) { ?>validate[custom[urlsmall]] <?php } ?>textbox" style="width:690px" name="vablpname" id="vablpname" value="" placeholder="http://"/>
                <?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                    <div class="orange-star">*</div>
                <?php } ?>
                <div class="popup-textinfo"><?php echo $this->lang->line('Link Example'); ?></div>

                    <!--div><label><input type="checkbox"  id="disable_scripts" value="yes"> <?php echo $this->lang->line('Disable all scripts in page'); ?></label></div>
                    <div class="popup-textinfo"><?php echo $this->lang->line('Disable all scripts in page info'); ?></div-->

                <div class="ctrl-buttons <?php
                // special styles for etracker 
                if ($tenant == 'etracker') {
                    ?>
                         etracker-float-right
                     <?php } ?>"> 
                    <div class="links">
                        <a href="javascript:void(0)" onclick="CreateVisualAB(0)" class="editor_back"><?php echo $this->lang->line('headline'); ?></a>
                    </div>
                    <input type="submit" class="button ok" value="<?php echo $this->lang->line('Editor Visitor Page Heading'); ?>"/>
                </div>
            </div>	
        </form>			
    </div>



<!-- 
***************************************************************************************
**********************************SPLIT TESTS FORMS************************************
***************************************************************************************
-->
<script type="text/javascript">
                    var num = 1;
                    var variantid = '1_';
                    var BlackTriMaxCombinations = <?= $this->config->item('mvt_max_combinations') ?>;
                    var EmptyNamePH = '<?= $this->lang->line('Please enter the name here'); ?>';
                    var NewVariant = '<?= $this->lang->line('Variant label'); ?>';
                    var EnableLog = true;
                    var DisablePopupNotice = false;
                    var BTIsEditable = false;
                    var BTCurrentTab = 0;
                    var BTRenamingIndex = 0;
                    var BTMenuWidth = 150;
                    var BTTestType = "visual";//or split
                    var BTTenant = '<?= $tenant ?>';
                    var Is_etracker =<?php echo $tenant == "etracker" ? "true" : "false"; ?>;
                    var BTeditorVars = {
                        view: 'wizard',
                        FrameEditorBaseUrl: "<?php echo $this->config->item('editor_url'); ?>?blacktriurl=",
                        DocDomain: '<?= $editorurl = $this->config->item('document_domain') ?>',
                        conversionGoalsParams: <?php echo $conversionGoalsParams ?>,
                        ClientId: <?= $clientid ?>,
                        BaseSslUrl: "<?php echo $basesslurl ?>",
                        goalsData: <?php echo json_encode($collectionGoals); ?>,
                        // CollectionId :  <?php echo $collectionid ?>,
                        enterNameText: "<?php echo $this->lang->line('Please enter the name here'); ?>"

                    }
</script>
<script type="text/javascript" src="<?php echo $basesslurl ?>js/jquery.ba-postmessage.js"></script>

<!--<script type="text/javascript" src="<?php /*echo $basesslurl */?>js/dash_testpage_wizards/fancybox-editor/main.js"></script>-->
<script type="text/javascript" src="<?php echo $basesslurl ?>js/dash_testpage_wizards/BT-editor-common-functions.js"></script>
<script type="text/javascript" src="<?php echo $basesslurl ?>js/dash_testpage_wizards/wizard-only.js"></script>

<div style="display:none;">        

    <!-- AB Test -->          
    <div class="confirmation confirmation-user" id="ab2_1">
        <h1><?php echo $this->lang->line('Create A/B Test (Step 1 of 4)'); ?></h1>
        <form id="frmABStep1" class="frmAB" name="frmABStep1" method="post" action="javascript:void(0);">
            <div class="confirmation-field w100">
                <label><?php echo $this->lang->line('Enter URL of your page'); ?></label>							
                <div><?php echo $this->lang->line('Enter URL of your page description'); ?> <?php echo splink('wizard_ab_step2a'); ?></div>
                <input type="text" class="validate[custom[urlsmall]] textbox" style="width:660px" name="controlpagename" id="controlpagename" placeholder="http://"/><?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                    <div class="star_url_1_3">*</div>
                <?php } ?>
                <div class="popup-textinfo" style="clear: both;"><?php echo $this->lang->line('Link Example'); ?></div>

                <label><?php echo $this->lang->line('Enter variants of your page'); ?></label>							
                <div><?php echo $this->lang->line('Description variant'); ?> <?php echo splink('wizard_ab_step2b'); ?></div>
                <input type="hidden" value="1_" name="variantpagehid" id="variantpagehid"/>
                <input type="hidden" value="0" name="split_test_persomode" id="split_test_persomode" />
                <input type="hidden" id="variant_persorule_0" name="variant_persorule_0" title="" value="0" />
                
                <div id="ABVariants">					   
                    <div id="ABVariant1">
                        <div  style="float:left; width: 670px;" class="clonedInput">
                            <input type="text" class="textbox textfocus" style="width:660px" name="11" id="variantname1" value="<?php echo $this->lang->line('Please enter the name here'); ?>"/>
                            <input type="text" class="validate[required] textbox" style="width:660px" name="1" id="variantpagename1" value="http://"/>
                            <input type="hidden" class="split_test_rule_input" id="variant_persorule_1" name="variant_persorule_1" value="0" />
                            
                                <?php
                            // special styles for etracker
                            if ($tenant == 'etracker') {
                                ?>
                                <div class="star_url_1_3_2">*</div>
                            <?php } ?> 														
                        </div>						
                        <div class="lp-delete-cont">
                            <input type="button" class="lp-button lp-delete lp_delete_url_1_3  <?php echo $addDelClass; ?>" onclick="<?php echo $clickDel; ?>" id="btnDel1"/>
                        </div>
                    </div>
                </div>
                <div style="clear: both;">
                    <br>
                    <label class="addvariant">
                        <a class="button-4-4 split-url-1-3  <?php echo $addDelClass; ?>" href="<?php echo $clickAdd; ?>"><?php echo $this->lang->line('Add a variant'); ?></a>
                    </label>
                </div>

                <div class="ctrl-buttons <?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                         etracker-float-right
                     <?php } ?>"> 
                    <div class="links">
                        <a href="javascript:void(0)" onclick="CreateAB(0)" class="editor_back"><?php echo $this->lang->line('Create a test'); ?></a>
                    </div>
                    <input type="submit" class="<?php
                    // special styles for etracker
                    if ($tenant == 'etracker') {
                        ?>
                               ok-1-3-url-split
                           <?php } ?>button ok" value="<?php echo $this->lang->line('Perso nav title'); ?>"/>
                </div>
            </div>	
        </form>			
    </div>

    <?php
    /*     * ********************************************PERSONALIZATION*************************************** */
    echo '<input type="hidden" value="' . $this->lang->line('') . '" id="perso-" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso new campaign') . '" id="perso-new-campaign" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso edit campaign') . '" id="perso-edit-campaign" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso noperso intro') . '" id="perso-noperso-intro" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso complete intro') . '" id="perso-complete-intro" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso single intro') . '" id="perso-single-intro" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso has sms') . '" id="perso-has-sms" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso table title') . '" id="perso-table-title" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso unpersonalized') . '" id="perso-unpersonalized" />';
    echo '<input type="hidden" value="' . $this->lang->line('Variant label') . '" id="perso-variant-label" />';
    $persoClass = ($perso_level == 'disabled') ? 'disabled' : '';
    ?>
    <div class="confirmation confirmation-user" id="ab2_2">
        <h1 class="left-float-title"><?php echo $this->lang->line('Perso nav title'); ?></h1>
        <div style="clear:both"></div>

        <form method="post" id="frmABStep2" name="frmABStep2" class="frmAB  <?php echo $tenant; ?>" action="javascript:void(0)">
            <div class="headline">
                <p id="perso-headline" class="<?php echo $tenant; ?>"><?php echo $this->lang->line('Perso new campaign'); ?></p>
                <div style="clear:both"></div>

                <div id="steps-perso-radio-container">
                    <input class="perso-type-radio" id="perso-type-0"  checked type="radio" name="perso-type-selection" value="0" />
                    <label class="perso-type-label <?php echo $tenant; ?>" for="perso-type-0">
                        <?php echo $this->lang->line('Perso no personalization'); ?>
                    </label>
                    <div style="clear:both"></div>

                    <input class="perso-type-radio" id="perso-type-2" <?php echo $persoClass; ?> type="radio" name="perso-type-selection" value="2" />
                    <label class="perso-type-label <?php echo $persoClass . '  ' . $tenant; ?>" for="perso-type-2">
                        <?php echo $this->lang->line('Perso single variant'); ?>
                    </label>
                    <div style="clear:both"></div>

                    <input class="perso-type-radio" id="perso-type-1" <?php echo $persoClass; ?> type="radio" name="perso-type-selection" value="1" />
                    <label class="perso-type-label <?php echo $persoClass . '  ' . $tenant; ?>" for="perso-type-1">
                        <?php echo $this->lang->line('Perso complete test'); ?>
                    </label>
                    <div style="clear:both"></div>
                </div>

                <div id="steps-perso-upgrade-container">
                    <?php if ($perso_level == 'disabled') { ?>
                        <a class="step-product-upgrade  <?php echo $tenant; ?>" target="_blank" href="<?php echo $this->config->item('etracker_product_upgrade') ?>" >
                            <span class="upgrade-text"><?php echo $this->lang->line('Perso enable now'); ?></span>
                            <span class="upgrade-rocket  <?php echo $tenant ?>">
                                <i class="fa fa-rocket"></i>
                            </span>
                        </a>
                    <?php } ?>
                </div>
                <div style="clear:both"></div>
            </div>
            <div style="clear:both"></div>

            <div id="step-perso-bottom-container" class="<?php echo $tenant; ?>">
                <span id="perso-copy-text" class="<?php echo $tenant; ?>"></span>

                <div id="perso-complete-rule" class="<?php echo $tenant; ?>">
                    <div id="edit-rule-link-container" class="<?php echo $tenant; ?>">
                        <a href="javascript:void(0)" class="rule-list-link perso_wizard_link"><?php echo $this->lang->line('Perso table title'); ?>: </a>
                        <div style="clear:both"></div>
                    </div>
                </div>

                <div id="perso-table-container" class="<?php echo $tenant; ?>"></div>
                <input type="hidden" id="perso-complete-ruleid" name="perso-complete-ruleid" value="0" />
                <div style="clear:both"></div>
            </div>

            <?php if ($tenant == 'etracker') { ?><div class="links-3-4"><?php } ?>
                <div class="ctrl-buttons">
                    <div class="links">
                        <a href="javascript:void(0)" onclick="CreateAB(1)" class="editor_back"><?php echo $this->lang->line('Create A/B Test (Step 1 of 4)'); ?></a>
                    </div>
                    <input type="submit" class="button ok" value="<?php echo $this->lang->line('Create A/B Test (Step 3 of 4)'); ?>"/>
                </div>
                <?php if ($tenant == 'etracker') { ?></div><?php } ?>
        </form>
    </div>
    <!-- *********************************************END PERSONALIZATION************************************************** -->


    <div class="confirmation confirmation-user" id="ab2_3">

        <h1><?php echo $this->lang->line('Create A/B Test (Step 3 of 4)') ?></h1>
        <form id="frmABStep3" class="frmAB" name="frmABStep3" method="post" onsubmit="return false;">
            <div class="confirmation-field w100">
                <input type="hidden" name="user_url" id="user_url" />
                <input type="hidden" name="testurl" id="testurl" />

                <input type="hidden" name="tracking_approach" id="tracking_approach" class="tracking_approach" value="1" /><!-- default value = 1-OCPC, 2-OCPT -->
                <label <?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                        class="label-step-3-4"
                    <?php } ?> ><?php echo $this->lang->line('Choose a name for your test'); ?><?php
                        // special styles for etracker
                        if ($tenant == 'etracker') {
                            ?>
                        <div class="orange-star-3-4">*</div>
                    <?php } ?> </label>
                <!-- validate[required,ajax[ajaxTestName]] -->
                <input type="text"  maxlength="100" class="textbox validate[required]" style="width:300px" name="testname" id="testname" value=""/>
                <div style="clear:both"></div>
                <div class="popup-textinfo <?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                         popup-textinfo-3-4
                     <?php } ?>"><?php echo $this->lang->line('Test Name Example'); ?></div>
                     <?php
                     // special styles for etracker
                     if ($tenant == 'etracker') {
                         ?>
                    <div class="line-3-4"></div>
                <?php } ?>
                    
                    

                <?php if ($tenant == 'blacktri') { ?>
                    <div id="tt_interface_container">
                        <div class="headline">
                            <label><?= $this->lang->line('tt interface title'); ?></label>
                            <div>
                                <?= $this->lang->line('tt_interface text') ?>
                                <div class="additional_project_config">
                                    <?php foreach ($this->lang->line('tt interface options') as $value => $label) { ?>
                                    <input type="radio" id="tt_interface_<?= $value ?>" name="tt_interface_type" value="<?= $value ?>" checked="true">
                                        <label class="tt_interface_label" for="tt_interface_<?= $value ?>">
                                            <?= $label ?>
                                        </label>
                                        <div class="clear"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tt_mainurl_container">                                                
                        <div class="headline">
                            <label><?= $this->lang->line('tt original title'); ?></label>
                            <div><?= $this->lang->line('tt original text'); ?></div>
                        </div>
                        <input type="text" class="validate[custom[urlsmall]] textbox" name="tt_mainurl" id="tt_mainurl" placeholder="http://"/>
                        <div style="clear:both"></div>
                        <div class="popup-textinfo">
                            <?= $this->lang->line('tt link example'); ?>
                        </div>
                    </div>
                <?php } ?>

                <div class="ocpc">                    
                    <div class="ocpc_headline hide">                            
                        <div class="headline">
                            <label><?php echo $this->lang->line('Pattern fur die Originalseite'); ?></label>
                            <div><?php echo $this->lang->line('Geben Sie die URL der zu testenden seite'); ?></div>
                        </div>

                        <div id="url_control_patterns">
                            <div class="url_pattern_element">
                                <input type="text"  maxlength="1024" class="textbox validate[required]" id="url_pattern_textbox" name="control_pattern[]" value="<?= $control_pattern ?>"/>
                                
                                <select class="urlpattern_behavior etracker_config_dropdown" name="url_include[]">
                                    <?php
                                    foreach ($this->lang->line('Url pattern options') as $key => $value) {
                                        echo '<option value="' . $key . '">' . $value . '</option>';
                                    }
                                    ?>
                                </select>

                                <div class="url_pattern_remove">
                                    <input type="button" class="lp-button lp-delete lp_delete_4_4" />
                                </div>
                                
                                <div class="clear"></div>
                            </div>
                        </div>                        
                            
                        <div style="clear:both"></div>
                        <div class="popup-textinfo <?php if ($tenant == 'etracker') { ?>popup-textinfo-3-4<?php } ?>">
                            <?php echo $this->lang->line('Control Page Example'); ?>
                        </div>

                        <div class="links">
                            <a id="wizard_add_url" class="button-4-4" href="javascript:void(0);"><?php echo $this->lang->line('Add url pattern'); ?></a>
                        </div>
                        
                    </div>
                    
                    <?php if ($tenant == 'etracker') { ?>
                        <div class="line-3-4"></div>
                    <?php } ?>
                </div>
                    
                <div class="additional_project_config">
                    <div class="headline">
                        <label><?= $this->lang->line('IP Filtering'); ?></label>
                        <div>
                            <?= $this->lang->line('IP Filtering description') . " " . splink('ipfiltering'); ?>

                            <div class="ip_filter_element">
                                <input type="text"  maxlength="1024" class="textbox" id="ip_filter_list" name="ip_filter_list" value="" />
                                <select class="ip_filter_action " name="ip_filter_action" id="ip_filter_action">
                                    <option value="not_used">
                                        <?= $this->lang->line('Ignore IP address'); ?>
                                    </option>
                                    <option value="allow">
                                        <?= $this->lang->line('Allow IP address'); ?>
                                    </option>
                                    <option value="deny">
                                        <?= $this->lang->line('Exclude for IP address'); ?>
                                    </option>
                                </select>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="line-3-4"></div>

                <?php if ($has_timer) { ?>
                    <div class="additional_project_config">
                        <div class="headline">
                            <label><?php echo $this->lang->line('Project schedule title'); ?></label>
                            <div>
                                <?php
                                    echo $this->lang->line('Project schedule description');
                                    echo '<input id="datepicker_locale_month" type="hidden" value="' . $this->lang->line('Datepicker locale month') . '" />';
                                    echo '<input id="datepicker_locale_month_short" type="hidden" value="' . $this->lang->line('Datepicker locale month short') . '" />';
                                    echo '<input id="datepicker_locale_days" type="hidden" value="' . $this->lang->line('Datepicker locale days') . '" />';
                                    echo '<input id="datepicker_locale_days_short" type="hidden" value="' . $this->lang->line('Datepicker locale days short') . '" />';
                                    echo '<input id="datepicker_locale_days_min" type="hidden" value="' . $this->lang->line('Datepicker locale days min') . '" />';
                                    echo '<input id="lpc_current_start_time" type="hidden" value="' . date('H:i:s')  . '" />';
                                    echo '<input id="lpc_current_end_time" type="hidden" value="23:00:00" />';

                                    $endDate = date('d.m.Y', strtotime('+1 years'));
                                    $timeOpt = '';
                                    for ($i = 0; $i < 24; $i++) {
                                        $opt = (($i < 10) ? '0' . $i : $i) . ':00:00';
                                        $timeOpt .= '<option value="' . $opt . '">' . $opt . '</option>';
                                    }
                                ?>
                                <div id="start_end_options">
                                    <label class="start_end_label">
                                        <?php echo $this->lang->line('Project schedule start'); ?>:
                                    </label>
                                    
                                    <input type="text"  class="start_end_text" name="lpc_start_date" id="lpc_start_date"
                                           value="<?php echo date('d.m.Y') ?>"/>
                                    
                                    <a id="lpc_start_calendar" class="calendar_icon" href="javascript:void(0);">
                                        <i class="fa fa-calendar " title="<?php echo $this->lang->line('Project schedule start tooltip'); ?>"></i>
                                    </a>
                                    
                                    <div class="lpc_timeframe lpc_start_calendar" id="lpc_start_timeframe">
                                        <div class="calendar">
                                            <div id="lpc_start_datepicker" class="lpc_datepicker"></div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
    
                                    <select class="start_end_select" name="lpc_start_time" id="lpc_start_time">
                                        <?php echo $timeOpt; ?>
                                    </select>
                                    <div class="clear"></div>
                                    
                                    <label class="start_end_label">
                                        <?php echo $this->lang->line('Project schedule end'); ?>:
                                    </label>
                                    
                                    <input type="text"  class="start_end_text" name="lpc_end_date" id="lpc_end_date" 
                                           value="<?php echo $endDate; ?> "/>
                                    
                                    <a id="lpc_end_calendar" class="calendar_icon" href="javascript:void(0);">
                                        <i class="fa fa-calendar " title="<?php echo $this->lang->line('Project schedule end tooltip'); ?>"></i>
                                    </a>
                                    
                                    <div class="lpc_timeframe lpc_end_calendar" id="lpc_end_timeframe">
                                        <div class="calendar">
                                            <div id="lpc_end_datepicker" class="lpc_datepicker"></div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    
                                    <select class="start_end_select" name="lpc_end_time" id="lpc_end_time">
                                        <?php echo $timeOpt; ?>
                                    </select>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                        if ($tenant == 'etracker') {
                            echo'<br /><div class="line-3-4"></div>';
                        }
                    }
                    ?>
                    <div class="allocation <?= $tenant ?>">

                        <label><?php echo $this->lang->line('How many visitors shall be allocated'); ?></label>
                        <div><?php echo $this->lang->line('How many visitors shall be allocated description'); ?></div>
                        <select class="dropdown " name="allocation" id="allocation">
                            <?php foreach ($this->config->item('allocations') as $val => $label) { ?>
                                <option value="<?= $val ?>"><?= $label ?></option>
                            <?php } ?>
                        </select>
                        <div class="clear"></div>
                    </div>  
                        
                    <?php
                    if ($tenant == 'etracker') {
                        echo'<br /><div class="line-3-4"></div>';
                    }
                    ?>

                <div class="ocpt hide">
                    <div class="ocpt_headline hide">

                        <!-- visual test -->
                        <div class="headline h1" condition="all the others">
                            <label><?php echo $this->lang->line('Insert tracking code visual A/B event'); ?></label>
                            <div><?php echo $this->lang->line('Insert tracking code visual A/B event description'); ?></div>
                        </div>                                                
                        <div class="headline h2 hide" condition="EC & SPC, SPC">
                            <label><?php echo $this->lang->line('Insert tracking code visual A/B successpage'); ?></label>
                            <div><?php echo $this->lang->line('Insert tracking code visual A/B successpage description'); ?></div>
                        </div>                            
                    </div>
                    <div class="trackingcode_ocpt">
                        <div class="ocpt_control hide">
                            <label><?php echo $this->lang->line('Tracking-Code fur die Originalseite'); ?></label>
                            <div><?php echo splink('wizard_step4c'); ?></div>
                            <textarea class="textbox trackingcode w100" name="trackingcode_control" id="trackingcode_control" resize="none" rows="6"></textarea>
                        </div>
                    </div>
                </div>                    
                <?php
// special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                    <div class="links-3-4">
                    <?php } ?>   
                    <div class="ctrl-buttons"> 
                        <div class="links">
                            <a href="javascript:void(0)" onclick="CreateAB(2)" class="editor_back"><?php echo $this->lang->line('Perso nav title'); ?></a>
                        </div>
                        <input type="submit" class="button ok" <?php
                        // special styles for etracker
                        if ($tenant == 'etracker') {
                            ?>
                                   id="ok-3-4"
                               <?php } ?> value="<?php echo $this->lang->line('Create A/B Test (Step 4 of 4)'); ?>"/>
                    </div>

                    <?php
// special styles for etracker
                    if ($tenant == 'etracker') {
                        ?>
                    </div>
                <?php } ?>  
            </div>	
        </form>         
    </div>

    <div class="confirmation confirmation-user" id="ab2_4">
        <h1><?php echo $this->lang->line('Create A/B Test (Step 4 of 4)') ?></h1>
        <form id="frmABStep4" class="frmAB" name="frmABStep4" method="post" action="javascript:void(0);">

            <?php $this->load->view('includes/goals_form'); ?>

            <div class="links">
                <a class="button-4-4 button_addgoal goals_action_link" href="javascript:void(0)">
                    <?= $this->lang->line('Create new goal'); ?>
                </a>
                <div class="clear"></div>
            </div>

            <div class="ctrl-buttons"> 
                <div class="links">
                    <a href="javascript:void(0)" onclick="CreateAB(3)" class="editor_back">
                        <?= $this->lang->line('Create A/B Test (Step 3 of 4)'); ?>
                    </a>
                </div>
                <input type="submit" class="button ok" value="<?= $this->lang->line('Save and create test'); ?>"/>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </form>         
    </div>

    <?php
    $this->load->view('client-testpage-popups/goal_details');
    $this->load->view('client-testpage-popups/goal_reactivate');
    ?>

</div>          
</div>