<div class="confirmation confirmation-user" id="vab2_3">
    <h1><?php echo $this->lang->line('Edit Visual A/B Test (Step 2 of 3)'); ?></h1>
    <form id="frmVisualABStep3" name="frmVisualABStep3" method="post" onsubmit="return false;">
        <div class="confirmation-field w100">
            <input type="hidden" name="testurl" id="testurl" />
            <input type="hidden" name="lpccode" id="lpccode" />
            <input type="hidden" name="variantdata" id="variantdata" />
            <input type="hidden" name="savestep" id="savestep" />
            <input type="hidden" name="tracking_approach" id="tracking_approach" class="tracking_approach" value="1" /><!-- default value = 1-OCPC, 2-OCPT -->
            <label  <?php
            // special styles for etracker
            if ($tenant == 'etracker') {
                ?>
                class="label-step-3-4"
            <?php } ?>><?php echo $this->lang->line('Choose a name for your test'); ?><?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                    <div class="orange-star-3-4">*</div>
                <?php } ?></label>
            <!-- validate[required,ajax[ajaxTestName]] -->
            <input type="text"  maxlength="100" class="textbox validate[required]" style="width:300px" name="testname" id="testname" value=""/>
            <div style="clear:both"></div>

            <div class="popup-textinfo <?php if ($tenant == 'etracker') { ?> popup-textinfo-3-4 <?php } ?>">
                <?php echo $this->lang->line('Test Name Example'); ?>
            </div>

            <?php if ($tenant == 'etracker') { ?>
                <div class="line-3-4"></div>
            <?php } ?>

                <?php if ($tenant == 'blacktri') { ?>                    
                    <div id="tt_interface_container">
                        <div class="headline">
                            <label><?= $this->lang->line('tt interface title'); ?></label>
                            <div>
                                <div class="additional_project_config">
                                    <?php foreach ($this->lang->line('tt interface options') as $value => $label) { ?>
                                        <input class="tt_interface_radio" type="radio" id="tt_interface_<?= $value ?>" name="tt_interface_type" value="<?= $value ?>" checked="true">
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
                        <input type="text" class="validate[custom[urlsmall]] textbox" style="width:660px" name="tt_mainurl" id="tt_mainurl" placeholder="http://" value="<?= $project->mainurl ?>"/>
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

                    <?php 
                    $cpattern = json_decode($control_pattern, TRUE);
                    if ($cpattern == NULL) {
                        $cpattern = array(
                            0 => array(
                                'mode' => 'include',
                                'url' => $control_pattern != NULL ? $control_pattern : '',
                            ),
                        );
                    }
                    ?>
                    <div id="url_control_patterns">
                        <?php foreach ($cpattern as $i => $p) { ?>
                            <div class="url_pattern_element">
                                <input type="text"  maxlength="1024" class="textbox validate[required]" id="url_pattern_textbox" name="control_pattern[]" value="<?= $p['url'] ?>"/>
                                <select class="urlpattern_behavior" name="url_include[]">
                                <?php foreach ($this->lang->line('Url pattern options') as $ind => $value) {
                                       $selected = $ind == $p['mode'] ? ' selected="true" ' : '';
                                       echo '<option value="' . $ind . '" ' . $selected . '>' . $value . '</option>';
                                   }?>
                                </select>

                                <div class="url_pattern_remove">
                                    <input type="button" class="lp-button lp-delete lp_delete_4_4" />
                                </div>

                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                        </div>

                    <div style="clear:both"></div>
                    <div class="popup-textinfo <?php if ($tenant == 'etracker') { ?>popup-textinfo-3-4<?php } ?>">
                        <?php echo $this->lang->line('Control Page Example'); ?>
                    </div>

                    <div class="links">
                        <a id="wizard_add_url" class="button-4-4" href="javascript:void(0);"><?php echo $this->lang->line('Add url pattern'); ?></a>
                    </div>

                </div>
            </div>

                <?php if ($testtype == OPT_TESTTYPE_VISUALAB){ 
                    if(getConditionalActivationLevel() == 'available') {
                    ?>
                    <div id="bt_additional_config" class="additional_project_config">
                        <div class="headline">
                            <label class="bt_additional_label bt_additional_show">
                                <strong>► </strong><?= $this->lang->line('Additional settings title'); ?>
                            </label>
                            <div class="bt_additional_settings">
                                <?= $this->lang->line('Additional settings description'); ?>
                                <div class="clear"></div>
                                <textarea id="additional_dom_selector" name="additional_dom_selector"  class="textbox" /><?= $config_selector ?></textarea>
                                <select name="additional_dom_action" class="dom_action_select">
                                    <?php foreach ($this->lang->line('Additional settings options') as $key => $value) { 
                                        $selected = $key == $config_action ? ' selected="true" ' : '';
                                        ?>
                                        <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
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
                    
                    <div class="line-3-4"></div>
                    <div class="additional_project_config">
                        <div class="headline">
                            <label><?php echo $this->lang->line('Project schedule title'); ?></label>
                            <div>
                                <?php
                                    echo $this->lang->line('Project schedule description');
                                    $disabledStart = ($collectionstatus > 0) ? ' disabled ' : '';
                                    echo '<input id="datepicker_locale_month" type="hidden" value="' . $this->lang->line('Datepicker locale month') . '" />';
                                    echo '<input id="datepicker_locale_month_short" type="hidden" value="' . $this->lang->line('Datepicker locale month short') . '" />';
                                    echo '<input id="datepicker_locale_days" type="hidden" value="' . $this->lang->line('Datepicker locale days') . '" />';
                                    echo '<input id="datepicker_locale_days_short" type="hidden" value="' . $this->lang->line('Datepicker locale days short') . '" />';
                                    echo '<input id="datepicker_locale_days_min" type="hidden" value="' . $this->lang->line('Datepicker locale days min') . '" />';
                                    echo '<input id="lpc_current_start_time" type="hidden" value="' . $start_time  . '" />';
                                    echo '<input id="lpc_current_end_time" type="hidden" value="' . $end_time  . '" />';

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
                                           value="<?php echo $start_date ?>"/>
                                    
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
                                           value="<?php echo $end_date ?>"/>
                                    
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
                <?php } ?>

                    <?php if ($isTT) { ?>
                        <div class="allocation <?= $tenant ?>">

                            <label><?= $this->lang->line('How many visitors shall be allocated'); ?></label>
                            <div><?= $this->lang->line('How many visitors shall be allocated description'); ?></div>

                            <select class="dropdown " name="project_allocation" id="allocation">
                                <?php foreach ($this->config->item('allocations') as $val => $label) {
                                    $selected = $allocation == $val ? ' selected="true" ' : '';
                                    ?>
                                    <option value="<?= $val ?>" <?= $selected ?>><?= $label ?></option>
                                <?php } ?>
                            </select>

                            <div class="clear"></div>
                        </div>
                    <?php } ?>

            <div class="ocpt hide_for_tt">
                <div class="ocpt_headline">
                    <label><?php echo $this->lang->line('Pattern fur die Originalseite OCPT'); ?></label>
                    <div><?php echo $this->lang->line('Geben Sie die URL der zu testenden seite OCPT'); ?></div>
                    <div class="switch_approach">
                        <?php echo splink('wizard_step23f', 'TrackingApproach(\'OCPC\')'); ?>
                        <br /><br />
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
                        <a class="editor_back" onclick="jQuery('#fancybox-close').trigger('click');" href="javascript:void(0)"><?php echo $this->lang->line('Abbrechen und zurueck zum Details'); ?></a>
                    </div>
                    <input type="submit" class="button ok" value="<?php echo $this->lang->line('Save approach'); ?>">
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