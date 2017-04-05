<div id="BTFactorEditPopup" >

    <label class="codeEdit-title BTFactorEditPopupHtml BTEnableDraggable" data-code="html"><?php echo $this->lang->line('Edit html source...') ?></label>
    <label class="codeEdit-title BTFactorEditPopupCss BTEnableDraggable"  data-code="css"><?php echo $this->lang->line('Edit custom css...') ?></label>
    <label class="codeEdit-title BTFactorEditPopupJs BTEnableDraggable"  data-code="javascript"><?php echo $this->lang->line('Edit custom js...') ?></label>
    <label class="codeEdit-title BTFactorEditPopupJs BTEnableDraggable"  data-code="text"><?php echo $this->lang->line('Edit element text') ?></label>

    <div class="clear"></div>
    <div id="code_blocks">
        <div id="code_editing">
            <textarea resize="none" id="BTCodeEditor" name="BTCodeEditor" class="BTHtmlEditor textbox"></textarea>
        </div>
        <div id="html_editing" style="display: none">
            <textarea resize="none" id="BTHtmlEditor" name="BTHtmlEditor" class="BTHtmlEditor textbox"></textarea>
        </div>
    </div>

    <div class="ctrl-buttons<?php if ($tenant == "etracker") { ?> etracker-float-right<?php } ?>">
        <input type="button" class="BtEditorButton cancel" value="<?php echo $this->lang->line('Cancel') ?>" onclick="window.BlackTri.editorCancel()"/>
        <input type="button" class="BtEditorButton ok" value="<?php echo $this->lang->line('Save') ?>" onclick="BlackTri.codeEdit.save()"/>

    </div>
    <div style="clear: both; height: 15px;"></div>
</div>

<div class="editor_popup_loading editor_popup_message2">
    <a onclick="RemoveEditorPreloader()" href="javascript:;" class="close_popup"></a>
    <?php if ($tenant == 'etracker') { ?>
        <h1><?php echo $this->lang->line('You can edit the page now'); ?></h1>
    <?php } else { ?>
        <div><b><?php echo $this->lang->line('You can edit the page now'); ?></b></div>
    <?php } ?>
    <div><?php echo $this->lang->line('You can edit the page now description'); ?><br /><?php echo splink('wizard_step3_layer'); ?></div>
    <div class="editor_popup_checkbox">
        <label>
            <input type="checkbox" value="yes" onclick="SaveEditorSettings(this)"/>
            <?php echo $this->lang->line('Do not show this message'); ?>
        </label>
    </div>
    <?php if ($tenant == "etracker") { ?>
        <div class="ctrl-buttons">
			<?php if ($is_visitor) { ?>
		        <input type="button" class="button cancel_step nomargin" onclick="BlackTri.redirect('visitor_cancel')" value="<?php echo $this->lang->line('Abbrechen'); ?>"/>
            <?php } else { ?>
    	        <input type="button" class="button cancel_step nomargin" onclick="CancelEditing()" value="<?php echo $this->lang->line('Abbrechen'); ?>"/>
            <?php } ?>
            <input type="submit" class="button ok" onclick="RemoveEditorPreloader()" value="<?php echo $this->lang->line('Seite jetzt bearbeiten.'); ?>"/>
        </div>
    <?php } else { ?>
        <div style="padding-top:5px;">
            <a class="help_link" onClick="RemoveEditorPreloader()" href="javascript:void(0)"><?php echo $this->lang->line('close and proceed editing'); ?></a>
        </div>
    <?php } ?>
</div>
<div class="editor_popup_loading editor_popup_message3">
    <a onclick="RemoveEditorPreloader()" href="javascript:;" class="close_popup"></a>
    <?php if ($tenant == 'etracker') { ?>
        <h1><?php echo $this->lang->line('Error on page load'); ?></h1>
    <?php } else { ?>
        <div><b><?php echo $this->lang->line('Error on page load'); ?></b></div>
    <?php } ?>
	
    <div><?php echo $this->lang->line('Client page has invalid html'); ?></div>
	
    <?php if ($tenant == "etracker") { ?>
        <div class="ctrl-buttons">
			<?php if ($is_visitor) { ?>
		        <input type="button" class="button cancel_step nomargin" onclick="BlackTri.redirect('visitor_cancel')" value="<?php echo $this->lang->line('Abbrechen'); ?>"/>
            <?php } else { ?>
    	        <input type="button" class="button cancel_step nomargin" onclick="CancelEditing()" value="<?php echo $this->lang->line('Abbrechen'); ?>"/>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div style="padding-top:5px;">
            <a class="help_link" onClick="CancelEditing()" href="javascript:void(0)"><?php echo $this->lang->line('Abbrechen'); ?></a>
        </div>
    <?php } ?>
</div>
<div class="editor_popup_loading editor_rename_variant smallpopup editor_confirm">
    <h1><?php echo $this->lang->line('Rename variant'); ?></h1>
    <div><input type="text" class="validate[required] textbox" name="variant_name" id="variant_name" value=""/></div>
    <div class="clear"></div>
    <div class="ctrl-buttons <?php
    // special styles for etracker
    if ($tenant == 'etracker') {
        ?>
             etracker-float-right
         <?php } ?>">
        <div class="links">
            <a class="editor_back" onclick="CloseEditorPopups()" href="javascript:void(0)"><?php echo $this->lang->line('Cancel rename visual ab button') ?></a>
        </div>
        <input type="button" class="button ok" style="margin-left:10px;" value="<?php echo $this->lang->line('Rename visual ab button') ?>" onclick="SaveVisualABVariantName()"/>
    </div>
</div>
<!--  Edit link popup  -->

<!--  Edit element url attribute popup for img and a tags -->
<div id="edit_url_popup" class="editor_popup_loading  smallpopup editor_confirm">
    <h1 class="edit_url_text" data-text_link="<?php echo $this->lang->line('Edit link'); ?>" data-text_image="<?php echo $this->lang->line('Edit image'); ?>"><!--text filled by javascript--></h1>
    <div><input type="text" class="validate[required] textbox" name="new_link" id="edit_url_input" value=""/></div>
    <p><span class="edit_url_text" data-text_link="<?php echo $this->lang->line('Enter link URL'); ?>"  data-text_image="<?php echo $this->lang->line('Enter image URL'); ?>"><!--text filled by javascript--></span></p>
    <div class="clear"></div>
    <div class="ctrl-buttons <?php
    // special styles for etracker
    if ($tenant == 'etracker') {
        ?>
             etracker-float-right
         <?php } ?>">
        <div class="links">
            <a class="editor_back" onclick="CloseEditorPopups()" href="javascript:void(0)"><?php echo $this->lang->line('Cancel rename visual ab button') ?></a>
        </div>
        <input type="button" class="button ok" style="margin-left:10px;" value="<?php echo $this->lang->line('Save link') ?>" onclick="BlackTri.saveUrlAttribute()"/>
    </div>
</div>

<!-- Confirm delete variant popup-->
<div id="confirm_delete_variant_popup" class="editor_popup_loading smallpopup editor_confirm">
    <a class="close_popup" href="javascript:;" onclick="CloseEditorPopups()"></a>
    <h1><?php echo $this->lang->line('Confirm delete variant heading'); ?></h1>
    <p><?php echo $this->lang->line('Confirm delete variant copy'); ?></p>
    <div id="delete_variant_name"></div>
    <div class="clear"></div>
    <div class="ctrl-buttons <?php
    // special styles for etracker
    if ($tenant == 'etracker') {
        ?>
             etracker-float-right
         <?php } ?>">
        <div class="links">
            <a class="editor_back" onclick="CloseEditorPopups()" href="javascript:void(0)"><?php echo $this->lang->line('Abbrechen') ?></a>
        </div>
        <input type="button" class="button ok" style="margin-left:10px;" onclick="RemoveVisualABVariant()" value="<?php echo $this->lang->line('Confirm variant delete button') ?>" />
    </div>
</div>

<!--  Add "click" goal to a particular element -->
<div id="create_clickgoal_popup" class="editor_popup_loading  smallpopup editor_confirm ui-draggable">
    <form action="javascript:void(0);" id="create_clickgoal_form">
        <h1 class="edit_clickgoal_text">
            <?= $this->lang->line('Track click goals') ?>
        </h1>
        <div class="edit_clickgoal_content">
            <input type="hidden" class="click_goals_id"/>
            <div>
                <input type="text" name="click_goal" id="clickgoal_input" value=""
                       class="cgoal_input click_goals_name textbox validate[required,funcCall[validateUniqueNameAndSelector]]" />
            </div>
            <p>
                <span><?= $this->lang->line('Enter goal name') ?></span>
            </p>
            <div class="clear"></div>

            <div class="headline">
                <label class="bt_additional_label bt_additional_show">
                    <strong>► </strong><?= $this->lang->line('Click goal advanced'); ?>
                </label>
                <div class="bt_additional_settings">
                    <input type="text" id="cgoal_selector" 
                           class="cgoal_input click_goals_selector textbox validate[required,funcCall[validateUniqueNameAndSelector]]" />
                    <p>
                        <span><?= $this->lang->line('Enter goal selector short'); ?></span>
                    </p>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="clear"></div>

            <div class="ctrl-buttons">
                <input type="button" class="BtEditorButton cancel" value="<?= $this->lang->line('Cancel') ?>"/>
                <input type="submit" class="BtEditorButton ok" value="<?= $this->lang->line('Save') ?>" />
                <input type="button" class="BtEditorButton remove" value="<?= $this->lang->line('Click goal remove') ?>" />
                <div class="clear"></div>
            </div>
        </div>
    </form>
</div>
