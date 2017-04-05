<div class="confirmation confirmation-user" id="ab2_1">
    <h1><?php echo $this->lang->line('Edit A/B Test (Step 1 of 2)'); ?></h1>
    <form id="frmABStep1" class="frmAB" name="frmABStep1" method="post" onsubmit="return false;">
        <div class="confirmation-field w100">
            <input type="hidden" name="collectionid" id="collectionid" value="<?php echo $collectionid; ?>"/>

            <label><?php echo $this->lang->line('Enter URL of your page'); ?></label>
            <div><?php echo $this->lang->line('Enter URL of your page description'); ?> <?php echo splink('wizard_ab_step2a'); ?></div>
            <input type="text" class="validate[required,custom[urlsmall]] textbox" style="width:660px" name="controlpagename" id="controlpagename" value="<?php echo $controlpageurl; ?>"/><?php
            // special styles for etracker
            if ($tenant == 'etracker') {
                ?>
                <div class="star_url_1_3">*</div>
            <?php } ?>
            <div class="popup-textinfo" style="clear: both;"><?php echo $this->lang->line('Link Example'); ?></div>

            <label><?php echo $this->lang->line('Enter variants of your page'); ?></label>
            <div><?php echo $this->lang->line('Description variant'); ?> <?php echo splink('wizard_ab_step2b'); ?></div>

            <?php
            $oldid = '';
            if (count($landingpages) == 0) {
                ?>
                <input type="hidden"  name="variantpagehid" id="variantpagehid" value="1_"/>
                <div id="ABVariant1">
                    <div  style="float:left; width: 670px;" class="clonedInput">
                        <input type="text" class="textbox textfocus" style="width:660px" name="11" id="variantname1" value="<?php echo $this->lang->line('Please enter the name here'); ?>" title="<?php echo $this->lang->line('tooltip_variant'); ?>"/>
                        <input type="text" class="validate[required] textbox" style="width:660px" name="1" id="variantpagename1" value="http://" title="<?php echo $this->lang->line('tooltip_variant'); ?>"/>
                    </div>
                    <div class="lp-delete-cont">
                        <input type="button" class="lp-button lp-delete lp_delete_url_1_3" onclick="DeleteVariant(1);" id="btnDel1"/>
                    </div>
                </div>
            <?php
            } else {
                ?>
                <input type="hidden"  name="variantpagehid" id="variantpagehid" value=""/>
                <?php
                foreach ($landingpages as $row) {
                    if ($row["pagetype"] == OPT_PAGETYPE_VRNT) {
                        $oldid .= $row["id"] . "_";
                        $vid = "old" . $row["id"];
                        ?>
                        <div id="ABVariant<?php echo $vid; ?>">
                            <div  style="float:left; width: 670px;" class="clonedInput">
                                <input type="text" class="textbox textfocus" style="width:660px" name="variantold<?php echo $row["id"]; ?>" id="variantold<?php echo $row["id"]; ?>" value="<?php echo $row["name"] == "" ? $this->lang->line('Please enter the name here') : $row["name"]; ?>" title="<?php echo $this->lang->line('tooltip_variant'); ?>"/>
                                <input type="text" class="validate[required] textbox" style="width:660px" name="variantpageold<?php echo $row["id"]; ?>" id="variantpageold<?php echo $row["id"]; ?>" value="<?php echo $row["lp_url"]; ?>" title="<?php echo $this->lang->line('tooltip_variant'); ?>"/><?php
                                // special styles for etracker
                                if ($tenant == 'etracker') {
                                    ?>
                                    <div class="star_url_1_3_2">*</div>
                                <?php } ?>
                            </div>
                            <div class="lp-delete-cont">
                                <input type="button" class="lp-button lp-delete lp_delete_url_1_3" onclick="DeleteVariant('<?php echo $vid; ?>');" id="btnDel1"/>
                            </div>
                        </div>
                    <?php
                    }
                }
            }
            ?>

            <input type="hidden" name="variantpagehidold" id="variantpagehidold" value="<?php echo $oldid; ?>" />
            <input type="hidden" name="deleteid" id="deleteid" value=""/>
            <div id="ABVariants">

            </div>
            <div style="clear: both;">
                <br>
                <label class="addvariant">
                    <a class="button-4-4 split-url-1-3" href="javascript:AddVariant();"><?php echo $this->lang->line('Add a variant'); ?></a>
                </label>
            </div>

            <div class="ctrl-buttons <?php
            // special styles for etracker
            if ($tenant == 'etracker') {
                ?>
                         etracker-float-right
                     <?php } ?>">
                <!--input type="submit" class="button ok" value="<?php echo $this->lang->line('Proceed'); ?>"/-->
                <input type="submit" class="button ok" value="<?php echo $this->lang->line('Save variants'); ?>">
            </div>
        </div>
    </form>
</div>