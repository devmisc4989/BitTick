<div class="confirmation confirmation-user" id="ab2_2">

    <h1><?php echo $this->lang->line('Edit A/B Test (Step 2 of 2)') ?> IS THIS FILE DEPRECATED?</h1>
    <form id="frmABStep2" class="frmAB" name="frmABStep2" method="post" onsubmit="return false;">
        <div class="confirmation-field w100">
            <input type="hidden" name="user_url" id="user_url" />
            <input type="hidden" name="testurl" id="testurl" />
            <input type="hidden" name="savestep" id="savestep" />

            <input type="hidden" name="tracking_approach" id="tracking_approach" class="tracking_approach" value="1" /><!-- default value = 1-OCPC, 2-OCPT -->
            <label><?php echo $this->lang->line('Choose a name for your test'); ?></label>
            <input type="text"  maxlength="100" class="textbox validate[required]" style="width:300px" name="collectionpagename" id="collectionpagename" value="<?php echo $collectionname; ?>"/>
            <div style="clear:both"></div>
            <div class="popup-textinfo"><?php echo $this->lang->line('Test Name Example'); ?></div>

            <label><?php echo $this->lang->line('Choose the conversion goal'); ?></label>
            <div><?php echo $this->lang->line('Choose the conversion goal description'); ?><br><?php echo splink('wizard_step4a'); ?></div>

            <div class="conversion_goals">
                <label><input type="checkbox" name="conversiongoal[]" value="EC" onclick="ConversionGoal(this)" class="goal validate[minCheckbox[1]]" id="cgcheck1"/> <?php echo $this->lang->line('Engagement'); ?> <?php echo splink('wizard_step23a'); ?></label>
                <label><input type="checkbox" name="conversiongoal[]" value="AC" onclick="ConversionGoal(this)" class="goal validate[minCheckbox[1]]" id="cgcheck2"/> <?php echo $this->lang->line('Affiliate goal'); ?> <?php echo splink('wizard_step23b'); ?></label>
                <label><input type="checkbox" name="conversiongoal[]" value="SPC" onclick="ConversionGoal(this)" class="goal validate[minCheckbox[1]]" id="cgcheck3"/> <?php echo $this->lang->line('Success page goal'); ?> <?php echo splink('wizard_step23c'); ?></label>
                <label><input type="checkbox" name="conversiongoal[]" value="CC" onclick="ConversionGoal(this)" class="goal validate[minCheckbox[1]]" id="cgcheck4"/> <?php echo $this->lang->line('Custom goal'); ?> <?php echo splink('wizard_step23d'); ?></label>
                <!-- tracking_approach -->
            </div>


            <div class="ocpc">
                <div class="ocpc_headline hide">
                    <div class="headline">
                        <label><?php echo $this->lang->line('Pattern fur die Originalseite'); ?></label>
                        <div><?php echo $this->lang->line('Geben Sie die URL der zu testenden seite'); ?></div>
                    </div>
                    <div class="switch_approach">
                        <?php echo splink('wizard_step23e', 'TrackingApproach(\'OCPT\')'); ?>
                    </div>

                    <input type="text"  maxlength="100" class="textbox validate[required]" style="width:400px" name="control_pattern" id="control_pattern" value="<?= $control_pattern ?>"/><div style="clear:both"></div>
                    <div class="popup-textinfo"><?php echo $this->lang->line('Control Page Example'); ?></div>
                </div>
                <!--div class="trackingcode_ocpc hide">
                        <label><?php echo $this->lang->line('OCPC Tracking code description title'); ?></label>
                        <div><?php echo $this->lang->line('OCPC Tracking code description'); ?><br><?php echo splink('wizard_step4c'); ?></div>
                            <textarea class="textbox trackingcode w100" name="trackingcode_ocpc" id="trackingcode_ocpc" resize="none" rows="6"></textarea>
                    </div-->
            </div>

            <div class="ocpt">
                <div class="ocpt_headline">
                    <div class="switch_approach">
                        <?php echo splink('wizard_step23f', 'TrackingApproach(\'OCPC\')'); ?>
                    </div>
                </div>
            </div>

            <div class="ctrl-buttons">
                <input type="submit" class="button ok" value="<?php echo $this->lang->line('Save goals'); ?>">
            </div>
        </div>
    </form>
</div>