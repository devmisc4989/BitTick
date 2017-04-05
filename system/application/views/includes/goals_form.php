<div class="confirmation-field w100 cgoal_main_container">
    <label>
        <?= $this->lang->line('Add conversion goals to your test'); ?>
    </label>
    <div>
        <?= $this->lang->line('Add conversion goals to your test description'); ?>
    </div>

    <div class="separator_line"></div>

    <div class="cgoal_row first_cgoal_row">
        <input name="conversion_goal_id[]" class="goals_value goals_id" type="hidden" value="" />
        <input name="conversion_goal_type[]" class="goals_value goals_type" type="hidden" value="" />
        <input name="conversion_goal_level[]" class="goals_value goals_level" type="hidden" value="" />
        <input name="conversion_goal_name[]" class="goals_value goals_name" type="hidden" value="" />
        <input name="conversion_goal_param[]" class="goals_value goals_param" type="hidden" value="" />
        <input name="conversion_goal_pageid[]" class="goals_value goals_pageid" type="hidden" value="" />
        <input name="conversion_goal_deleteddate[]" class="goals_value goals_deleteddate" type="hidden" value="" />

        <div class="goals_label_container">
            <label class="goals_label goals_type_label"></label>
            <label class="goals_label goals_name_label"></label>
        </div>
        <label class="goals_label goals_level_label primary"><?= $this->lang->line('Primary goal label') ?></label>
        <label class="goals_label goals_level_label secondary"><?= $this->lang->line('Secondary goal label') ?></label>
        <div class="action_trigger">
            <a class="action_title" href="javascript:void(0)">
                <?= $this->lang->line('Goal menu action'); ?>
            </a>
            <div class="action_menu">
                <div class="top"></div>
                <div class="middle">
                    <a href="javascript:void(0);" class="goal_menu_primary"><?= $this->lang->line('Set as primary goal'); ?></a>
                    <a href="javascript:void(0);" class="goal_menu_archive"><?= $this->lang->line('Goal menu archive'); ?></a>
                    <a href="javascript:void(0);" class="goal_menu_edit"><?= $this->lang->line('Goal menu edit'); ?></a>
                </div>
                <div class="bottom"></div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="separator_line"></div>
    </div>
</div>