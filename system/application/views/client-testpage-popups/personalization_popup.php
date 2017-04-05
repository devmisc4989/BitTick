<!-- PERSO RULE EDIT CONTAINER-->
<?php
$this->load->config('personalization');
$tenant = $this->config->item('tenant');
// use a protocol-relative url
$basesslurl = str_replace('http:','',str_replace('https:','',$this->config->item('base_ssl_url')));

$min_conds = ($tenant == 'etracker') ? 3 : 1;
$close_img = ($tenant == 'etracker') ? 'images/fancy_box_close_etracker.png' : 'images/fancy_box_close.png';
?>
<input type="hidden" value="<?php echo $tenant ?>" id="cur_tenant" />
<input type="hidden" value="<?php echo $basesslurl; ?>" id="baseurl" />
<input type="hidden" value="<?php echo $min_conds; ?>" id="min_conds" />
<input type="hidden" value="<?php echo $this->config->item('language'); ?>" id="site_lang" />
<input type="hidden" value="<?php echo $this->lang->line('Perso rule label'); ?>" id="rule_label" />
<input type="hidden" value="<?php echo $this->lang->line('Perso option select'); ?>" id="option_select" />
<input type="hidden" value="<?php echo $this->lang->line('Perso option select country'); ?>" id="option_select_country" />
<input type="hidden" value="<?php echo $this->lang->line('Perso option select state'); ?>" id="option_select_state" />
<input type="hidden" value="<?php echo $this->lang->line('Perso option select region'); ?>" id="option_select_region" />
<input type="hidden" value="<?php echo $this->lang->line('Perso option select city'); ?>" id="option_select_city" />
<input type="hidden" value="<?php echo $this->lang->line('Perso option select tt'); ?>" id="option_select_tt" />
<input type="hidden" value="<?php echo $this->lang->line('Perso del condition'); ?>" id="msg_del_condition" />
<input type="hidden" value="<?php echo $this->lang->line('Perso confirm cancel'); ?>" id="msg_conf_cancel" />
<input type="hidden" value="<?php echo $this->lang->line('Perso confirm del rule'); ?>" id="msg_del_rule" />

<div id="perso_rule_editor_overlay" class="perso_rule_overlay <?php echo $tenant; ?>"></div>

<div id="perso_rule_editor_wrapper" class="perso_rule_wrapper <?php echo $tenant; ?>">

    <img src="<?php echo $basesslurl . $close_img ?>" id="perso_rule_editor_close"/>

    <div id="perso_rule_main_container" class="<?php echo $tenant; ?>">
        <div id="perso_rule_loading"></div>
        <h1 <?php echo 'class="' . $tenant . '"'; ?>><?php echo $this->lang->line('Perso popup title'); ?></h1>
        <p id="perso_intro_text"><?php echo $this->lang->line('Perso popup introductory text'); ?></p>
        <div class="sep"></div>

        <h2 id="perso_rule_list_title"><?php echo $this->lang->line('Perso rule list title'); ?></h2>
        <div class="clear"></div>
        <div id="perso_rule_list_container">
            <div id="perso_rule_list"></div>
            <div id="perso_rule_actions" class="<?php echo $this->lang->line('Perso unpersonalized'); ?>">
                <a href="javascript:void(0)" class="custom_tenant_bg  <?php echo $tenant; ?>"  id="perso_rule_add_action">
                    <div class="perso_sharp_icon"><i class="fa fa-plus minus-plus-icons"></i></div>
                    <span><?php echo $this->lang->line('Perso add rule'); ?></span>
                </a>
                <div class="clear"></div>
                <a href="javascript:void(0)" class="custom_tenant_bg  <?php echo $tenant; ?>" id="perso_rule_del_action">
                    <div class="perso_sharp_icon   <?= $tenant; ?>"><i class="fa fa-minus minus-plus-icons"></i></div>
                    <span><?php echo $this->lang->line('Perso del rule'); ?></span>
                </a>
                <div class="clear"></div>
            </div>
        </div>

        <div id="perso_rule_param_container">

            <label class="perso_rule_hidden_title perso_rule_label  <?php echo $tenant; ?>">
                <?php echo $this->lang->line('Perso not defined'); ?>
            </label>
            <p class="perso_rule_hidden_title  <?php echo $tenant; ?>">
                <?php echo $this->lang->line('Perso please start'); ?>
            </p>

            <form id="perso_rule_form">
                <div id="perso_rule_parameters">
                    <label class="perso_rule_label  <?php echo $tenant; ?>">
                        <?php
                        echo $this->lang->line('Perso rule name');
                        ?>
                    </label>
                    <input type="text" class="text perso_rule_input validate[required]  <?php echo $tenant; ?>" id="perso_rule_name" maxlength="32" />
                    <div class="sep"></div>

                    <label class="perso_rule_label  <?php echo $tenant; ?>"><?php echo $this->lang->line('Perso rule valid'); ?></label>
                    <select class="select perso_rule_input  <?php echo $tenant; ?>" id="perso_rule_validif">
                        <?php
                        foreach ($this->config->item('valid_if_selected') as $valid) {
                            echo '<option value="' . $valid['value'] . '">' . $valid['label'] . '</option>';
                        }
                        ?>
                    </select>
                    <div class="sep"></div>
                    <label class="perso_rule_label  <?php echo $tenant; ?>">
                        <?php echo $this->lang->line('Perso title condition'); ?>:
                    </label>
                    <div class="smallsep"></div>

                    <div id="perso_rule_conditions" class="<?= $tenant; ?>">
                        <div id="perso_rule_contition_items"></div>
                        <div class="clear"></div>
                    </div>
                </div>

                    <a href="javascript:void(0)" id="perso_rule_add_condition" class="custom_tenant_bg  perso_rule_add_cond <?php echo $tenant; ?>">
                        <div class="perso_sharp_icon"><i class="fa fa-plus minus-plus-icons"></i></div>
                        <span><?php echo $this->lang->line('Perso add condition'); ?></span>
                        <div class="clear"></div>
                    </a>
                    <div class="clear"></div>
            </form>

        </div>
    </div>
    <div class="clear"></div>

    <div class="perso_rule_btn_container <?php echo $tenant; ?>">
        <input type="button" class="perso_rule_btn perso_rule_cancelbtn <?php echo $tenant; ?>" 
               id="perso_rule_cancel" value="<?php echo $this->lang->line('button_back'); ?>" />
        <input type="button" class="perso_rule_btn perso_rule_savebtn <?php echo $tenant; ?>" 
               id="perso_rule_save" value="<?php echo $this->lang->line('Perso select rule'); ?>" />
    </div>
    <div class="clear"></div>

</div>

<!-- Confirm delete Perso Rule -->
<div id="perso_rule_confirm_overlay" class="perso_confirm_popup perso_rule_overlay <?php echo $tenant; ?>"></div>
<div id="perso_rule_confirm_wrapper" class="perso_confirm_popup perso_rule_wrapper <?php echo $tenant; ?>">
    <h1 <?php echo 'class="' . $tenant . '"'; ?>><?php echo $this->lang->line('Perso button delete rule'); ?></h1>
    <p id="perso_del_rule_name"></p>
    <div class="clear"></div>

    <div class="perso_rule_btn_container <?php echo $tenant; ?>">
        <input type="button" class="perso_rule_btn perso_rule_cancelbtn <?php echo $tenant; ?>" 
               id="perso_rule_nodelete" value="<?php echo $this->lang->line('Abbrechen'); ?>" />
        <input type="button" class="perso_rule_btn perso_rule_savebtn <?php echo $tenant; ?>" 
               id="perso_rule_delete" value="<?php echo $this->lang->line('Perso button delete rule'); ?>" />
    </div>
</div>

<!-- Confirm cancel Rule -->
<div id="perso_rule_cancel_overlay" class="perso_confirm_popup perso_rule_overlay <?php echo $tenant; ?>"></div>
<div id="perso_rule_cancel_wrapper" class="perso_confirm_popup perso_rule_wrapper <?php echo $tenant; ?>">
    <h1 <?php echo 'class="' . $tenant . '"'; ?>><?php echo $this->lang->line('Perso confirm cancel title'); ?></h1>
    <p id="perso_discard_changes"><?php echo $this->lang->line('Perso confirm cancel'); ?></p>
    <div class="clear"></div>

    <div class="perso_rule_btn_container <?php echo $tenant; ?>">
        <input type="button" class="perso_rule_btn perso_rule_cancelbtn <?php echo $tenant; ?>" 
               id="perso_rule_noexit" value="<?php echo $this->lang->line('Perso back'); ?>" />
        <input type="button" class="perso_rule_btn perso_rule_savebtn <?php echo $tenant; ?>" 
               id="perso_rule_exit" value="<?php echo $this->lang->line('Perso discard'); ?>" />
    </div>
</div>