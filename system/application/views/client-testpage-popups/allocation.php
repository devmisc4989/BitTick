<div class="confirmation confirmation-user wizard_step_allocation" id="allocation_popup">

    <h1 class="goal_edit_only"><?= $this->lang->line('Edit Visual A/B Test (set allocation)'); ?></h1>

    <form id="frmAllocation" name="frmAllocation" method="post" action="javascript:void(0);">

        <div class="line-3-4"></div>
        <input type="hidden" name="savestep" value='allocation' />

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

        <div class="separator_line "></div>

        <div class="variant_allocation">
            <label><?= $this->lang->line('Allocation for each variant'); ?></label>
            <div><?= $this->lang->line('Allocation for each variant intro'); ?></div>
            <a class="allocation_reset" href="javascript:void(0);"><?= $this->lang->line('Allocation reset link'); ?></a>
            <div class="clear"></div>

            <ul id="percentage_sliders">
                <li class="slider_label slider_element slider_original">
                    <label class="slider_vname"><?= $this->lang->line('Original Source') ?>:</label>
                    <div class="clear"></div>
                </li>
                <li class="slider_bar slider_element slider_original">
                    <div class="slider"></div>
                </li>
            </ul>

            <div id="allocation_percent_container">
                <div class="allocation_percent_content slider_element slider_original">
                    <input type="hidden" class="allocation_variantid" name="variant_id[]" value="<?= $originalid ?>" />
                    <input class="allocation_percent_value" type="text" name="variant_allocation[]" value=""  />
                    <label class="allocation_percent_label"> % </label>
                    <div class="clear"></div>
                </div>
            </div>

            <div class="clear"></div>

        </div>

        <div class="ctrl-buttons">
            <div class="links">
                <a class="editor_back" href="javascript:void(0);">
                    <?= $this->lang->line('Abbrechen'); ?>
                </a>
            </div>
            <input type="submit" class="button ok" value="<?= $this->lang->line('button_save'); ?>"/>
        </div>
        <div class="clear"></div>
    </form>
</div>