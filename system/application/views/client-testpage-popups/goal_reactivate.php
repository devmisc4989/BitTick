<div class="confirmation confirmation-user" id="goal_reactivate_popup">
    <h1><?= $this->lang->line('Goal reactivate title'); ?></h1>
    <form id="frmGoalReactivate" name="frmGoalReactivate" method="post" action="javascript:void(0);">
        <div class="confirmation-field w100 archived_goals_main_container">
            <label>
                <?= $this->lang->line('Goal reactivate sub'); ?>
            </label>
            <div>
                <?= $this->lang->line('Goal reactivate intro'); ?>
            </div>

            <div class="separator_line"></div>

            <div class="cgoal_row archived_row first_cgoal_row">
                <input class="goals_id" type="hidden" value="" />
                <input class="goals_type" type="hidden" value="" />
                <input class="goals_level" type="hidden" value="" />
                <input class="goals_name" type="hidden" value="" />
                <input class="goals_param" type="hidden" value="" />

                <div class="goals_label_container">
                    <label class="goals_label goals_type_label"></label>
                    <label class="goals_label goals_name_label"></label>
                </div>
                <label class="goals_label goals_deleteddate_label"></label>
                <a class="goals_reactivate_link action_title" href="javascript:void(0)">
                    <?= $this->lang->line('Goal reactivate link'); ?>
                </a>
                <div class="clear"></div>
                <div class="separator_line"></div>
            </div>
        </div>

        <div class="ctrl-buttons">
            <div class="links">
                <a class="editor_back" href="javascript:void(0);">
                    <?= $this->lang->line('button_back'); ?>
                </a>
            </div>
        </div>
        <div class="clear"></div>
    </form>
</div>