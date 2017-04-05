<div class="confirmation confirmation-user" id="goal_details_popup">

    <h1 class="goal_edit_only"><?= $this->lang->line('Goal details title'); ?></h1>
    <h1 class="goal_create_only"><?= $this->lang->line('Goal create title'); ?></h1>

    <form id="frmGoalDetails" name="frmGoalDetails" method="post" action="javascript:void(0);">
        <div class="confirmation-field w90">

            <label class="goal_create_only">
                <?= $this->lang->line('Goal details sub'); ?>
            </label>
            <div class="goal_create_only">
                <?= $this->lang->line('Goal details intro'); ?>
            </div>

            <input type="hidden" id="goal_details_id"/>

            <select id="goal_details_type" class="goal_create_only goal_dropdownlist dropdown validate[required]"></select>
            <label id="goal_type_label" class="goal_edit_only"></label>

            <div class="conversion_goal_desc">
                <label class="desc goal_details_desc"></label>
                <div style="clear:both;"></div>
            </div>                            
            <div class="clear"></div>    

            <div class="goal_params_container">
                <div>
                    <input type="text" id="goal_details_name" 
                           class="conversion_goal_name textbox validate[required,funcCall[validateUniqueNameAndSelector]]"/>
                    <div class="popup-textinfo goal_name_label"></div>

                    <div class="param_item_container">
                        <div class="goal_param_item">
                            <input type="text" id="0_goal_param_value" 
                                   class="conversion_goal_param textbox validate[required,funcCall[validateUniqueNameAndSelector]]"/>
                            <div class="targetpage_remove">
                                <input class="lp-button lp-delete lp_delete_4_4" type="button">
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="popup-textinfo goal_desc_label"></div>

                    <div class="links">
                        <a class="button-4-4 button-addurl goals_action_link" href="javascript:void(0)">
                            <?= $this->lang->line('Add url pattern'); ?>
                        </a>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <div class="additional_project_config bt_clickgoals_config">
                <input type="hidden" name="click_goal_pageid" class="pageid_clickgoal" />

                <input type="text" id="goal_details_name" 
                       class="conversion_goal_name textbox validate[required,funcCall[validateUniqueNameAndSelector]]"/>
                <div class="popup-textinfo goal_name_label"></div>

                <div class="headline">
                    <label class="bt_additional_label bt_additional_show">
                        <strong>► </strong><?= $this->lang->line('Click goal advanced'); ?>
                    </label>
                    <div class="bt_additional_settings">
                        <label><?= $this->lang->line('Enter goal selector short'); ?></label>
                        <div class="clear"></div>

                        <input type="text" id="cgoal_selector" 
                               class="cgoal_input conversion_goal_param textbox validate[required,funcCall[validateUniqueNameAndSelector]]" />
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="separator_line "></div>
        </div>

        <div class="ctrl-buttons">
            <div class="links">
                <a class="editor_back" href="javascript:void(0);">
                    <?= $this->lang->line('Abbrechen'); ?>
                </a>
            </div>
            <input type="submit" class="goal_create_only button ok" value="<?= $this->lang->line('Goal create add'); ?>">
            <input type="submit" class="goal_edit_only button ok" value="<?= $this->lang->line('Goal details change'); ?>">
        </div>
        <div class="clear"></div>
    </form>
</div>