<div class="mpt_popup">
    <input id="mpt_name_error" type="hidden" value="<?= $this->lang->line('Mpt name error') ?>" />
    <input id="mpt_url_error" type="hidden" value="<?= $this->lang->line('Mpt url error') ?>" />
    
    <div class="confirmation confirmation-user" id="mpt_manage_pages">
        <h1><?= $this->lang->line('multipage popup title'); ?></h1>
        <form id="mpt_pages_form" name="mpt_pages_form" method="post" action="javascript:void(0);">
            <?= $this->lang->line('multipage popup intro') ?>

            <div id="mpt_pages_list">
                <div class="mpt_page_container mpt_page_first">
                    <input class="mpt_page_id" type="hidden" name="mpt_page_id[]" />
                    <input class="mpt_page_url" type="hidden" name="mpt_page_url[]" />
                    <input type="text" class="textbox mpt_pages" name="mpt_pages[]" />
                    <div class="mpt_delete_icon" title="<?= $this->lang->line('multipage popup delete'); ?>">
                        <span class="mpt_icon"></span>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>

            <div class="sep"></div>

            <div class="links">
                <a id="mtp_new_page" class="button-4-4" href="javascript:void(0);">
                    <?= $this->lang->line('multipage popup add'); ?>
                </a>
            </div>

            <div class="ctrl-buttons">
                <div class="links">
                    <a id="mpt_cancel_pages" class="editor_back" href="#">
                        <?= $this->lang->line('button_cancel'); ?>
                    </a>
                </div>
                <input class="button ok" type="submit" value="<?= $this->lang->line('multipage popup save'); ?>">
            </div>

        </form>
    </div>

</div>

<div class="mpt_popup">
    <div class="confirmation-user confirmation smallpopup" id="deleteConfirm">
        <input type="hidden" name="deleteid" id="deleteid" />
        <h1><?= $this->lang->line('multipage delete title'); ?></h1>
        <div><?= $this->lang->line('multipage delete info'); ?></div>

        <div class="ctrl-buttons">
            <div class="links">
                <a id="mpt_delete_cancel" class="editor_back" href="#">
                    <?= $this->lang->line('button_cancel'); ?>
                </a>
            </div>
            <input id="mpt_confirm_page_delete" class="button ok" type="submit" value="<?= $this->lang->line('button_delete'); ?>">
        </div>                               
    </div>
</div>

<div class="mpt_popup">
    <div class="confirmation-user confirmation" id="mpt_visual_name_url">
        <h1><?= $this->lang->line('Mpt pageurl title'); ?></h1>
        <form id="mpt_pageurl_form" method="post">
            <div class="confirmation-field w100">
                <label><?= $this->lang->line('Mpt enter name') ?></label>
                <input type="text" id="mpt_page_name" class="validate[required,funcCall[mpt_validatePageName]] textbox">
                <div class="clear"></div>
                <div class="popup-textinfo"><?= $this->lang->line('Mpt name example') ?></div>
                <div class="clear"></div>

                <label><?= $this->lang->line('Enter URL of your page'); ?></label>
                <div>
                    <?= $this->lang->line('Enter URL of your page description'); ?> 
                    <?= splink('wizard_step2'); ?>
                </div>
                <input type="text" class="validate[required,custom[urlsmall],[required,funcCall[mpt_validatePageUrl]]] textbox" id="mpt_vab_page_url" placeholder="http://"/>
                <div class="clear"></div>
                <div class="popup-textinfo"><?= $this->lang->line('Link Example'); ?></div>


                <div class="ctrl-buttons">
                    <div class="links">
                        <a class="editor_back" href="#">
                            <?= $this->lang->line('button_back') ?>
                        </a>
                    </div>
                    <input id="mpt_save_page_url" class="button ok" type="submit" value="<?= $this->lang->line('multipage popup save'); ?>">
                    <div class="clear"></div>
                </div>
            </div>
            <div class="clear"></div>
        </form>
    </div>
</div>