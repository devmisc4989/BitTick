<div class="tto_popup">
    <div class="confirmation confirmation-user" id="tt_manage_headlines">
        <h1><?= $this->lang->line('tt_headlines_title'); ?></h1>

        <form id="tt_headlines_form" name="tt_headlines_form" method="post" action="javascript:void(0);">
            <input type="hidden" id="tt_ov_groupid" name="tt_groupid" value="">

            <label><?= $this->lang->line('tt_headlines_main_label'); ?></label>
            <?= $this->lang->line('tt_headlines_main_intro') ?>
            <div class="clear"></div>

            <input type="text" class="textbox validate[required]" name="tt_control_headline" id="tt_control_headline" value="<?= $original->name ?>"/>

            <label><?= $this->lang->line('tt_headlines_variant_label'); ?></label>

            <div id="tt_headline_alternatives">
                <?php foreach ($variants as $ind => $variant) { ?>
                    <div class="tt_headline_variant">
                        <input class="tt_variant_id" type="hidden" name="tt_variant_id[]" value="<?= 0 + (int) $variant->id ?>">
                        <input type="text" class="textbox tt_variant_headlines" name="tt_variant_headlines[]" value="<?= $variant->name ?>"/>
                        <div class="tt_delete_icon" title="<?= $this->lang->line('tt_headlines_variant_delete'); ?>">
                            <span class="tt_icon"></span>
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
            </div>

            <div class="links">
                <a id="tt_new_variant" class="button-4-4" href="javascript:void(0);">
                    <?= $this->lang->line('tt_headlines_variant_add'); ?>
                </a>
            </div>

            <div class="ctrl-buttons">
                <div class="links">
                    <a id="tt_headlines_cancel" class="editor_back" href="#">
                        <?= $this->lang->line('button_back'); ?>
                    </a>
                </div>
                <input class="button ok" type="submit" value="<?= $this->lang->line('tt_headlines_variant_save'); ?>">
            </div>

        </form>
    </div>

</div>

<div class="tto_popup">
    <div class="confirmation-user confirmation smallpopup" id="deleteConfirm">
        <input type="hidden" name="deleteid" id="deleteid" />
        <h1><?= $this->lang->line('tt_delete_confirm_title'); ?></h1>
        <div><?= $this->lang->line('tt_delete_confirm_text'); ?></div>

        <div class="ctrl-buttons">
            <div class="links">
                <a id="tt_delete_cancel" class="editor_back" href="#">
                    <?= $this->lang->line('button_cancel'); ?>
                </a>
            </div>
            <input id="tt_confirm_delete_test" class="button ok" type="submit" value="<?= $this->lang->line('button_delete'); ?>">
        </div>                               
    </div>
</div>