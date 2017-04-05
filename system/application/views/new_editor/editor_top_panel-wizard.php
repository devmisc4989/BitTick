<?php
global $config;
$heading_line = $is_visitor ? 'Editor Visitor Page Heading' : 'Editor page heading';
if (empty($device_type))
    $device_type = 'desktop';
?>

<div class="editor_url_container">
    <h4><?= $this->lang->line($heading_line) ?></h4>
    <div id="user_url">
        <div id="user_url_bg">
            <span class="user_url_edit">
                <?= trim_string($control_url, 65); ?>
            </span>
            <i class="fa fa-pencil url_edit_icon url_pencil button"></i>
            <div class="clear"></div>
        </div>
        <input type="text" class="edit_project_url" id="user_url_input" value="<?= trim_string($control_url, 65); ?>" />
        <i tabindex="0" class="fa fa-check url_edit_icon edit_project_url button" id="url_save"></i>
        <i tabindex="0" class="fa fa-close url_edit_icon edit_project_url button" id="url_cancel"></i>
    </div>
    <div class="clear"></div>
</div>
<div class="clear"></div>

<form id="frmVisualABStep2" name="frmVisualABStep2" method="post" onsubmit="return false;">
    <?php $class = $tenant == 'etracker' ? 'etracker-2-4-confirmation-field' : ''; ?>
    <div class="confirmation-field editor_top_buttons <?= $class ?>">

        <?php if (!$is_visitor): ?>
            <!--Registered Clients-->
            <div class="ctrl-buttons">	            
                <input type="submit" class="button next_step" onclick="CreateVisualAB(3)" value="<?php echo $this->lang->line('Perso nav title'); ?>"/>
                <div class="links" id="etracker-2-4-links">
                    <!--Cancel editor button-->
                    <a href="javascript:void(0)" onclick="CancelEditing()" class="editor_back">

                        <?php echo $this->lang->line('Abbrechen'); ?>
                    </a>

                </div>
                <div class="clear"></div>
            </div>

        <?php endif ?>

        <?php if ($is_visitor): ?>
            <!--Visitors-->
            <div class="ctrl-buttons">
                <?php
                $proceed_button_text = $this->lang->line('Editor Visitor Next Step Button');
                $cancel_button_text = $this->lang->line('Editor Visitor Cancel Button');
                ?>

                <div class="links" id="etracker-2-4-links">
                    <a href="javascript:void(0)" onclick="BlackTri.redirect('visitor_cancel')" class="editor_back"><?php echo $cancel_button_text; ?></a>
                </div>

                <input style="float: right" type="submit" class="button ok" <?= $id; ?> onclick="visitorProceed()" value="<?php echo $proceed_button_text ?>"/>
            </div>
        <?php endif ?>

        <div id="switch_mode">
            <input type="radio" id="editor_mode_editor" name="editor_mode" value="editor" onchange="SwitchMode('editor')"/>
            <label for="editor_mode_editor" class="first_radio"><?php echo $this->lang->line('editor_mode_edit') ?></label>			
            <input type="radio" id="editor_mode_browse" name="editor_mode" value="browse" onchange="SwitchMode('browse')" />
            <label for="editor_mode_browse" class="second_radio"><?php echo $this->lang->line('editor_mode_browse') ?></label>
        </div>

        <?php if ($is_mptest) { ?>
            <div class="top-panel-selector left-floating mpt-panel-selector">
                <label><?php echo $this->lang->line('testtype_multipage'); ?></label>
                <select class="top-panel-dropdown" id="mpt_select">
                    <option id="mpt_page_main"></option>
                </select>
                
                <a class="mpt-edit" href="javascript:void(0)"><?= $this->lang->line('Edit multipage test') ?></a>
            </div>
        <?php } ?>

        <div class="top-panel-selector left-floating">
            <label><?php echo $this->lang->line('Device type to load with'); ?></label>
            <select class="top-panel-dropdown" id="device_select">
                <?php foreach ($this->config->item('device_types') as $type => $devices) { ?>
                    <optgroup label="<?php echo $this->lang->line($type); ?>">
                        <?php foreach ($devices as $devkey => $device) { ?>
                            <option value="<?php echo $devkey ?>"<?php if ($device_type == $devkey) echo " selected"; ?>><?php echo $device['name'] . ($device['width'] != '0' ? ' (' . $device['width'] . 'x' . $device['height'] . ')' : '') ?></option>
                        <?php } ?>            
                    <?php } ?>
            </select>
        </div>

    </div>
</form>