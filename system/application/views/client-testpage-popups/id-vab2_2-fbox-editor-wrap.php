<div class="confirmation confirmation-user" id="vab2_2">
    <h1><?php echo $this->lang->line('Edit Visual A/B Test (Step 1 of 3)') ?></h1>
    <form id="frmVisualABStep2" name="frmVisualABStep2" method="post" onsubmit="return false;">
        <div class="zone_left" style="padding-top:5px; width:100%;">
            <div id="user_url"><?= trim_string($control_url, 65); ?></div>
        </div>
        <div class="confirmation-field editor_top_buttons <?php
        // special styles for etracker
        if ($tenant == 'etracker') {
            ?>
                     etracker-2-4-confirmation-field <?php } ?>">
            <div class="ctrl-buttons">
                <?php if ($tenant == "etracker") { ?>
                    <div class="links" id="etracker-2-4-links">
                        <a href="javascript:void(0)" onclick="jQuery('#fancybox-close').trigger('click');" class="editor_back"><?php echo $this->lang->line('Abbrechen'); ?></a>
                    </div>
                <?php } ?>
                <input type="submit" class="button ok" <?php
                // special styles for etracker
                if ($tenant == 'etracker') {
                    ?>
                    id="etracker-edit-2-4-ok"<?php } ?> onclick="SaveVisualABTest('variants')" value="<?php echo $this->lang->line('Save variants'); ?>">
            </div>
        </div>
    </form>




</div>