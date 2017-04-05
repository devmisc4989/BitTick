<div class="confirmation confirmation-user" id="vab2_4">
    <h1><?= $this->lang->line('Edit Visual A/B Test (Step 3 of 3)'); ?></h1>
    <form id="frmVisualABStep4" name="frmVisualABStep4" method="post" action="javascript:void(0);">

        <?php $this->load->view('includes/goals_form'); ?>

        <div class="links">
            <a class="button-4-4 button_addgoal goals_action_link" href="javascript:void(0)">
                <?= $this->lang->line('Create new goal'); ?>
            </a>
            <span class="links_separator">|</span>
            <a class="button-4-4 button_reactivategoal goals_action_link2" href="javascript:void(0)">
                <?= $this->lang->line('Add archived goal'); ?>
            </a>
            <div class="clear"></div>
        </div>

        <div class="ctrl-buttons">
            <div class="links">
                <a class="editor_back" href="javascript:void(0);">
                    <?= $this->lang->line('Abbrechen und zurueck zum Details'); ?>
                </a>
            </div>
            <input type="submit" class="button ok" value="<?= $this->lang->line('Save goals'); ?>">
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </form>
</div>