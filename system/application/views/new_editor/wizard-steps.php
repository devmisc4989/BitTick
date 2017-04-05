<div id="wizard_step_wrap" class="hide">

    <div class="confirmation confirmation-user" id="vab2_1">
        <h1><?php echo $this->lang->line('Create Visual A/B Test (Step 1 of 4)'); ?></h1>
        <form id="frmVisualABStep1" name="frmVisualABStep1" method="post" onsubmit="return false;">
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


                <div class="ctrl-buttons <?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                         etracker-float-right
                     <?php } ?>">
                    <input type="button" class="button cancel_step" onclick="CancelEditing()" value="<?php echo $this->lang->line('headline'); ?>"/>
                    <input type="submit" class="button next_step" value="<?php echo $this->lang->line('Proceed to Editor'); ?>"/>
                </div>
            </div>
        </form>
    </div>

    <?php
    /*     * ********************************************PERSONALIZATION*************************************** */
    echo '<input type="hidden" value="' . $tenant . '" id="current-tenant" />';
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
    <div class="confirmation confirmation-user" id="vab2_3">
        <h1 class="left-float-title"><?php echo $this->lang->line('Perso nav title'); ?></h1>
        <div style="clear:both"></div>

        <form method="post" id="frmVisualABStep3" class="<?php echo $tenant; ?>" action="javascript:void(0)">
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
                        <a href="javascript:void(0)" onclick="$.fancybox.close()" class="editor_back"><?php echo $this->lang->line('Abbrechen'); ?></a>
                    </div>
                    <input type="submit" class="button ok" value="<?php echo $this->lang->line('Personalization_Save_Changes'); ?>"/>
                </div>
                <?php if ($tenant == 'etracker') { ?></div><?php } ?>
        </form>
    </div>
    <!-- *********************************************END PERSONALIZATION************************************************** -->

    <div class="confirmation confirmation-user wizard_step_nameurl" id="vab2_4">
        <h1><?php echo $this->lang->line('Editor test customization'); ?></h1>
        <form id="frmVisualABStep4" name="frmVisualABStep4" method="post" action="javascript:void(0);">
            <div class="confirmation-field w100">
                <input type="hidden" name="testurl" id="testurl" value="<?= $client_url ?>"/>
                <input type="hidden" name="lpccode" id="lpccode" />
                <input type="hidden" name="variantdata" id="variantdata" />
                <input type="hidden" name="savestep" id="savestep" />
                <input type="hidden" name="tracking_approach" id="tracking_approach" class="tracking_approach" value="1" /><!-- default value = 1-OCPC, 2-OCPT -->
                <input type="hidden" name="device_type" id="device_type" />
                <label <?php
// special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                        class="label-step-3-4"
                    <?php } ?>  >
                        <?php echo $this->lang->line('Choose a name for your test'); ?><?php
                        // special styles for etracker
                        if ($tenant == 'etracker') {
                            ?>
                        <div class="orange-star-3-4">*</div>
                    <?php } ?></label>
                <!-- validate[required,ajax[ajaxTestName]] -->
                <input type="text"  maxlength="100" class="textbox validate[required]" name="testname" id="testname" value=""/>
                <div style="clear:both"></div>

                <div class="popup-textinfo <?php if ($tenant == 'etracker') { ?> popup-textinfo-3-4 <?php } ?>">
                    <?= $this->lang->line('Test Name Example'); ?>
                </div>

                <?php if ($tenant == 'etracker') { ?>
                    <div class="line-3-4"></div>
                <?php } ?>

                <div class="ocpc">
                    <div class="ocpc_headline hide only_mpt">
                        <div class="headline">
                            <label><?= $this->lang->line('Url for MPT title'); ?></label>
                            <div><?= $this->lang->line('Url for MPT intro'); ?></div>
                            <div class="clear"></div>
                        </div>

                        <div id="urls_for_mpt">
                            <div class="mpt_wizard_pattern">
                                <input type="text" class="label_input" maxlength="15" disabled="true"/>
                                <input type="text" id="mpt_pattern" name="control_pattern[]" class="textbox validate[required]" maxlength="1024" />
                                <div class="clear"></div>
                            </div>
                        </div>

                    </div>

                    <div class="ocpc_headline hide non_mpt">
                        <div class="headline">
                            <label><?php echo $this->lang->line('Pattern fur die Originalseite'); ?></label>
                            <div><?php echo $this->lang->line('Geben Sie die URL der zu testenden seite'); ?></div>
                        </div>

                        <div id="url_control_patterns">
                            <div class="url_pattern_element">
                                <input type="text"  maxlength="1024" class="textbox validate[required]" id="url_pattern_textbox" name="control_pattern[]" value="<?= $control_pattern ?>"/>
                                <select class="urlpattern_behavior" name="url_include[]">
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

                <?php if ($testtype == OPT_TESTTYPE_VISUALAB){ 
                    if(getConditionalActivationLevel() == 'available') {
                            ?>
                            <div id="bt_additional_config" class="additional_project_config non_mpt">
                                <div class="headline">
                                    <label class="bt_additional_label bt_additional_show">
                                        <strong>► </strong><?= $this->lang->line('Additional settings title'); ?>
                                    </label>
                                    <div class="bt_additional_settings">
                                        <?= $this->lang->line('Additional settings description'); ?>
                                        <div class="clear"></div>
                                        <input id="additional_dom_selector" type="text" name="additional_dom_selector"  class="textbox" />
                                        <textarea id="additional_dom_selector" name="additional_dom_selector"  class="textbox" /></textarea>
                                        <select name="additional_dom_action" class="dom_action_select">
                                            <?php foreach ($this->lang->line('Additional settings options') as $key => $value) { ?>
                                                <option value="<?= $key ?>"><?= $value ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } ?>

                    <div class="line-3-4"></div>
                    <div class="additional_project_config">
                        <div class="headline">
                            <label><?php echo $this->lang->line('IP Filtering'); ?></label>
                            <div>
                                <?php echo $this->lang->line('IP Filtering description') . " " . splink('ipfiltering');?>

                                <div class="ip_filter_element">
                                    <input type="text"  maxlength="1024" class="textbox" id="ip_filter_list" name="ip_filter_list" value="<?php echo html_escape($ip_filter_list);?>" <?php if($ip_filter_action=="not_used" || $ip_filter_action=="") echo ' disabled="disabled"';?>/>
                                    <select class="ip_filter_action " name="ip_filter_action" id="ip_filter_action">
                                        <option value="not_used" <?php if($ip_filter_action=="not_used") echo ' selected="selected"';?>><?php echo $this->lang->line('Ignore IP address'); ?></option>
                                        <option value="allow" <?php if($ip_filter_action=="allow") echo ' selected="selected"';?>><?php echo $this->lang->line('Allow IP address'); ?></option>
                                        <option value="deny" <?php if($ip_filter_action=="deny") echo ' selected="selected"';?>><?php echo $this->lang->line('Exclude for IP address'); ?></option>
                                    </select>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>


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
                    <?php }
                        if($tenant == 'etracker'){
                        echo '<div class="line-3-4"></div>';
                    }
                    ?>
                </div>

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
                        </div>

                    </div>

                </div>

                <?php if ($tenant == 'etracker') { ?><div class="links-3-4"><?php } ?>
                    <div class="ctrl-buttons">
                        <div class="clear"></div>
                        <div class="links">
                            <a href="javascript:void(0)" onclick="CreateVisualAB(3, true)" class="editor_back">
                                <?php echo $this->lang->line('Perso nav title'); ?>
                            </a>
                        </div>
                        <input type="submit" class="button ok" value="<?php echo $this->lang->line('Edit Visual A/B Test (Step 3 of 3)'); ?>"/>
                        <div class="clear"></div>
                    </div>
                    <?php if ($tenant == 'etracker') { ?></div><?php } ?>
            </div>
        </form>
    </div>

    <div class="confirmation confirmation-user wizard_step_goals" id="vab2_5">
        <h1><?= $this->lang->line('Editor goals customization'); ?></h1>
        <form id="frmVisualABStep5" name="frmVisualABStep5" method="post" action="javascript:void(0);">

            <?php $this->load->view('includes/goals_form'); ?>

            <div class="links">
                <a class="button-4-4 button_addgoal goals_action_link" href="javascript:void(0)">
                    <?= $this->lang->line('Create new goal'); ?>
                </a>
                <input type="text" value="" id="nogoalcheck" name="nogoalcheck" class="nogoalcheck validate[required]" />
                <div class="clear"></div>
            </div>

            <div class="ctrl-buttons">
                <div class="links">
                    <a href="javascript:void(0)" onclick="CreateVisualAB(4, true)" class="editor_back">
                        <?= $this->lang->line('Edit Visual A/B Test (Step 2 of 3)'); ?></a>
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