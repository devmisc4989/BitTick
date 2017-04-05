<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$tenant = $this->config->item('tenant');
$smsClass = ($sms_level == 'disabled' || ($edit_type == 'edit' && $is_smstest == 0)) ? 'disabled' : 'drop-nav';
$isSms = ($edit_type == 'edit' && $is_smstest == 1) ? '' : 'menu_sms_item';
$persoClass = ($perso_level == 'disabled') ? 'disabled' : 'perso_wizard_link perso_add_rule';
$jsClass = ($js_level == 'disabled') ? 'disabled' : '';
$cssClass = ($css_level == 'disabled') ? 'disabled' : '';
$clickJs = ($js_level == 'disabled') ? 'javascript:void(0);' : 'BlackTri.codeEdit.js()';
$clickCss = ($css_level == 'disabled') ? 'javascript:void(0);' : 'BlackTri.codeEdit.css()';
$addDelClass = ($allowed_variants == -1) ? '' : 'disabled';
//override
if($nossl){
	$baseurl = $this->config->item('base_url');
	$basesslurl = $baseurl;//$this->config->item('base_ssl_url');
}
//php echo &device=<?php echo $device_type
if($tracking_code_present){
    $temp_control_url = $control_url;
	if($final_client_url)
        $temp_control_url = $final_client_url;
	$iframeUrl = attachQuerystring($temp_control_url, "_=".time());
	$iframeUrl = attachQuerystring($iframeUrl, "__exclude__");
}
else {
	$iframeUrl = $editor_url."?blacktriurl=".urlencode($control_url)."&client=".$clientid."&device=".$device_type."&_".time();
    if(!$nossl)
        $iframeUrl .= "&protocol=ssl";
}

$newUrlData = $this->session->userdata('newUrlData');
if ($newUrlData) {
    $showKeepChanges = FALSE;
    $newData = json_decode($newUrlData);

    foreach ($newData->vData->pages as $idp => $page) {
        foreach ($page->variants as $idv => $variant) {
            $domcode = $variant->dom_modification_code;
            $js = '[JS]';
            $css = '[CSS]';
            $sms = '[SMS_HTML]';
            $hasSms = isset($domcode->$sms) && strtolower($domcode->$sms) != 'undefined';

            if (strtolower($domcode->$js) != 'null' || strtolower($domcode->$css) != 'null' || $hasSms) {
                $showKeepChanges = TRUE;
                break;
            }
        }
    }

    if ($showKeepChanges || $is_mptest) {
        ?>
        <script type="text/javascript">
            var newTestUrl = '<?= $newData->newUrl ?>';
            var savedVdata = <?= json_encode($newData->vData) ?>;
            var savedGoals = <?= json_encode($newData->gData) ?>;
        </script>
        <?php
    }
}
$this->session->unset_userdata('newUrlData');
$apigoals = $this->config->item('api_goals');
?>
        
<script type="text/javascript">
    var bt_clickgoals_vars = {
        goalType: "<?= $apigoals[GOAL_TYPE_CLICK] ?>",
        goalPrefix: "<?= $this->lang->line('Click goal prefix') ?>",
        goalLabel: "<?= $this->lang->line('Click goal name') ?>",
        goalTagLabel: "<?= $this->lang->line('CLick goal taglabel') ?>",
        pleaseSelect: "<?= $this->lang->line('Choose Target Page'); ?>",
        testtypeVisual: parseInt(<?= OPT_TESTTYPE_VISUALAB ?>),
        testtypeMultipage: parseInt(<?= OPT_TESTTYPE_MULTIPAGE ?>)
    };
</script>
        
<div id="editor_wrap">
    <!--etracker borders-->
    <div class="editor_border top_border"></div>
    <div class="editor_border left_border"></div>
    <div class="editor_border right_border"></div>
    <div class="editor_border bottom_border"></div>


    <div id="editor_top">
        <?php
        echo '<input type="hidden" id="allowed-variants" value="' . $allowed_variants . '" />';
        /* explicitely define view file in case changes made to "edit_type" */
        $top_panel_view = $edit_type == 'edit' ? 'editor_top_panel-edit' : 'editor_top_panel-wizard';
        $top_panel_data = array('tenant' => $tenant, 'device_type' => $device_type);
        /* load buttons and headings above tabs */
        $this->load->view('new_editor/' . $top_panel_view, $top_panel_data);
        ?>
        <div class="help_box">
            <?php echo splink('wizard_step3'); ?>
        </div>
        <div class="zone_right" style="position:absolute; right:300px; top:5px;">
        </div>
        <div id="menu_tabs" class="tabs_main">
            <div class="left"></div>
            <div class="right"></div>
            <div class="tabs_container">
                <div id="variant_tabs" class="zone_tabs">
                    <div class="tab default selected original" id="variant_0"><a><?php echo $this->lang->line('Original Source'); ?></a></div>
                    <div class="tab new  <?php echo $addDelClass; ?>"><a title="<?php echo $this->lang->line('New Variant'); ?>">&nbsp;+&nbsp;</a></div>
                </div>
            </div>
        </div>
        <div class="client_mask" style="top: 0; left: 0; right: 0; bottom: 0; opacity: .6"></div>
        <div id="rearrange_control">

            <div><strong><?php echo $this->lang->line('Rearrange control title') ?></strong></div>
            <div><?php echo $this->lang->line('Rearrange control description') ?></div>
            <div class="ctrl-buttons" style="margin-left: 0px;">
                <div id="etracker-2-4-links" class="links">
                    <a class="editor_back" onclick="BlackTri.rearrange.cancel()" href="javascript:void(0)"><?php echo $this->lang->line('Abbrechen'); ?></a>
                </div>
                <input id="save_rearrange" type="button" disabled value="<?php echo $this->lang->line('Save'); ?>" onclick="BlackTri.rearrange.save();" class="button ok">
            </div>
        </div>

    </div>

    <div id="editor_iframe_wrap">
        <!-- MASKS For Rearrange-->
        <div class="client_mask client_mask_left"></div>
        <div class="client_mask client_mask_top"></div>
        <div class="client_mask client_mask_right"></div>
        <div class="client_mask client_mask_bottom"></div>
        <div id="elem_border_left" class="client_border element_border vertical"></div>
        <div id="elem_border_top" class="client_border element_border  horizontal"></div>
        <div id="elem_border_right" class="client_border element_border vertical"></div>
        <div id="elem_border_bottom" class="client_border element_border horizontal"></div>

        <div id="rearrange_border_left" class="client_border rearrange_border vertical"></div>
        <div id="rearrange_border_top" class="client_border rearrange_border  horizontal"></div>
        <div id="rearrange_border_right" class="client_border rearrange_border vertical"></div>
        <div id="rearrange_border_bottom" class="client_border rearrange_border horizontal"></div>



        <iframe scrolling="auto"
                frameborder="0"
                class="frames"
                name="frame_editor"
                id="frame_editor"
                marginheight="0"
                marginwidth="0"
                style="width:100%; height:100%;"
                    src="<? echo $iframeUrl;?>">"
                ></iframe>


        <div class="editor_overlayer" style="width: 100%; height: 100%;  display: block;"></div>

        <div class="editor_proxy"></div>
        <div id="frame_editor_ph"></div>
        <!-- Used for moving elements-->
        <div class="editor_action <?php
        // special styles for blacktri
        if ($tenant == 'blacktri') {
            ?>
                 special-green-popup<?php } ?>">

            <div id="BTMoveFrame">
                <div id="BTMoveElementControls" class="BTSkipElement BTRemovable">
                    <img class="BTSaveButton" onclick="window.BlackTri.editorMoveElementSaveHtml()" />
                    <img class="BTCancelButton" onclick="window.BlackTri.editorMoveElementCancel()" />
                </div>
            </div>

            <div id="BTMouseOverEditorMenu">
                <!-- Blacktri header for menu start -->

                <div class="BTMouseOverEditorMenu_header"><!--tag name inserted with JS here--></div>

                <a href="javascript://" onclick="BlackTri.codeEdit.html()"><?php echo $this->lang->line('Edit html') ?></a>
                <a href="javascript://" onclick="BlackTri.codeEdit.text()"><?php echo $this->lang->line('Edit text') ?></a>
                <a href="javascript://" class="st_editor_open"><?php echo $this->lang->line('Edit Styles') ?></a>
                <a href="javascript://" onclick="BlackTri.editUrlAttribute('link')" class="tag_specific" data-tag="a"><?php echo $this->lang->line('Edit link') ?></a>
                <a href="javascript://" onclick="BlackTri.editUrlAttribute('image')" class="tag_specific" data-tag="img"><?php echo $this->lang->line('Edit image') ?></a>
                <a href="javascript://" class="clickgoal_action" id="clickgoal_action_create"><?= $this->lang->line('Track click goals') ?></a>
                <a href="javascript://" class="clickgoal_action" id="clickgoal_action_edit"><?= $this->lang->line('Edit click goals') ?></a>
                <a href="javascript://" class="clickgoal_action" id="clickgoal_action_hide"><?= $this->lang->line('Highlight goals hide') ?></a>
                <a href="javascript://" class="clickgoal_action" id="clickgoal_action_highlight"><?= $this->lang->line('Highlight goals display') ?></a>
                <a href="javascript://" class="no_sms" onclick="window.BlackTri.menuActionHideElement()"><?php echo $this->lang->line('Hide element') ?></a>
                <a href="javascript://" class="no_sms" onclick="window.BlackTri.menuActionRemoveElement()"><?php echo $this->lang->line('Remove element') ?></a>
                <a href="javascript://" class="no_sms" onclick="window.BlackTri.menuActionMoveElement()"><?php echo $this->lang->line('Move element') ?></a>
                <a href="javascript://" class="no_sms" onclick="BlackTri.rearrange.init()"><?php echo $this->lang->line('Rearrange element') ?></a>
                <a id="parent_selector" class="menu_no_close" href="javascript://" onclick="BlackTri.activateParentElement()"><?php echo $this->lang->line('Select parent element') ?></a>
            </div>    
        </div>    
    </div>
    
    <!-- Confirm keep editor changes CSS names are kept to avoid repeated code. -->
    <div id="keep_changes_overlay" class="perso_confirm_popup perso_rule_overlay <?= $tenant; ?>"></div>
    <div id="keep_changes_wrapper" class="perso_confirm_popup perso_rule_wrapper <?= $tenant; ?>">
        <h1 class="<?= $tenant; ?>"><?= $this->lang->line('change url title'); ?></h1>
        <p><?= $this->lang->line('change utl text'); ?></p>
        <div class="clear"></div>

        <div class="perso_rule_btn_container <?= $tenant; ?>">
            <input type="button" class="perso_rule_btn perso_rule_cancelbtn <?php echo $tenant; ?>" 
                   id="editor_undo_changes" value="<?= $this->lang->line('change url undo'); ?>" />
            <input type="button" class="perso_rule_btn perso_rule_savebtn <?php echo $tenant; ?>" 
                   id="editor_keep_changes" value="<?= $this->lang->line('change url keep'); ?>" />
        </div>
    </div>


    <!-- Default Loading popup-->
    <div  id="editor_loading_message" class="editor_popup_loading editor_popup_message1" >
        <a onclick="/*RemoveEditorPreloader(); EditorLoaded(true);*/" href="javascript:;" class="close_popup"></a>
        <?php if ($tenant == 'etracker') { ?>
            <h1><?php echo $this->lang->line('Loading page...'); ?></h1>
        <?php } else { ?>
            <div><b><?php echo $this->lang->line('Loading page...'); ?></b></div>
        <?php } ?>
        <div><?php echo $this->lang->line('Loading page... description'); ?></div>
    </div>


    <div class="preloading_message">
        <?php echo $this->lang->line('You can now edit the page. Some elements are still downloaded.'); ?>
    </div>

</div><!--End editor wrap-->


<div class="menu_template hide">
    <div class="menu_container">
        <?php if ($tenant == "etracker") { ?>
            <a class="up">▲</a>
            <a class="down">▼</a>
        <?php } ?>
        <div class="menu">

            <a class="history_undo disabled" onclick="BlackTri.history.restore()"><?php echo $this->lang->line('Undo change') ?></a>
            <div class="menu_separator"></div>

            <a onclick="RenameVisualABVariant(this)"><?php echo $this->lang->line('Rename visual ab variant') ?></a>
            <a class="delete_variant" onclick="OpenDeleteVariantPopup(this)"><?php echo $this->lang->line('Remove visual ab variant') ?></a>
            <div class="menu_separator"></div>

            <span class="selected_tab_only">
                <a class="<?php echo $jsClass; ?>" href="javascript:void(0)" onclick="<?php echo $clickJs; ?>">
                    <?php echo $this->lang->line('Edit custom js') ?>
                </a>
                <a class="<?php echo $cssClass; ?>" onclick="<?php echo $clickCss; ?>">
                    <?php echo $this->lang->line('Edit custom css') ?>
                </a>
            </span>

            <?php if ($tenant == 'etracker' && $sms_level != 'hidden') { ?>
                <div class="<?php echo $smsClass . ' ' . $isSms; ?>">
                    <a id="open_sms_menu" class="dropdown_link  <?php echo $tenant . ' ' . $smsClass; ?>">
                        <?php
                        echo $this->lang->line('Smart message');
                        echo ($smsClass != 'disabled') ? '<span class="dropnav_etracker" id="open">&#9658;</span>' : '';
                        ?>
                    </a>
                    <?php if ($smsClass != 'disabled') { ?>
                        <ul class="dropdown" class="sms_nav">
                            <li class="sms_add">
                                <a onclick="BlackTri.sms.new_sms()"><?php echo $this->lang->line('Smart message Add') ?></a>
                            </li>
                            <li class="sms_edit">
                                <a onclick="BlackTri.sms.edit_sms()"><?php echo $this->lang->line('Smart message Edit') ?></a>
                            </li>
                            <li class="sms_toggle">
                                <a onclick="BlackTri.sms.toggle_sms()"
                                   data-show="<?php echo $this->lang->line('Smart message Show') ?>"
                                   data-hide="<?php echo $this->lang->line('Smart message Hide') ?>"
                                   ><!--Text from JS--></a>
                            </li>

                            <li class="sms_delete">
                                <a onclick="BlackTri.sms.delete_sms()"><?php echo $this->lang->line('Smart message Delete') ?></a>
                            </li>
                        </ul>
                    <?php } ?>
                </div>
            <?php }
            if (!$is_mptest) {
                ?>
                <div class="menu_separator menu_personalization_container"></div>

                <div class="menu_personalization_container">
                    <h4 class="menu_perso_title"><?php echo $this->lang->line('Perso nav title') ?>:</h4>
                    <a href="javascript:void(0)" class="<?php echo $persoClass ?>" id="variant_rule_0">
                        <?php echo $this->lang->line('Perso unpersonalized') ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div style="display: none">
    <iframe id="textEditIframe" src="<?php echo $basesslurl; ?>editor/textEditIframe"></iframe>
</div>
<div id="full_editor_overlay"></div>
<div id="sms_ui">
    <iframe id="sms_ui_frame" src="<?php echo $basesslurl ?>editor/sms" ></iframe>
</div>
