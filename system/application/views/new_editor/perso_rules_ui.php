<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title></title>
        <?php
        $tenant = 'etracker/';
        $close_img = ($tenant == 'etracker') ? 'images/fancy_box_close_etracker.png' : 'images/fancy_box_close.png';
        if (isset($css)) {
            foreach ($css as $cssUrl) {
                $href = $basesslurl . $cssUrl;
                echo "<link type='text/css' href='$href' rel='stylesheet'/>\n";
            }
        }
        ?>
        <script type='text/javascript' src='<?php echo $basesslurl . 'js/jQuery-lib/jQuery-1.8.3.min.js' ?>'></script>
        <script type='text/javascript' src='<?php echo $basesslurl . 'js/BT-editor/perso/perso-rules.js' ?>'></script>
        <script type='text/javascript' src='<?php echo $basesslurl . 'jsi18n/jqueryValidationEngine.js' ?>'></script>
        <script type='text/javascript' src='<?php echo $basesslurl . 'js/jquery.validationEngine.js' ?>'></script>
        <script type='text/javascript' src='<?php echo $basesslurl . 'js/tooltips/jquery.powertip.min.js' ?>'></script>
    </head>

    <body>
        <!-- PERSO RULE EDIT CONTAINER-->
        <?php $min_conds = ($tenant == 'etracker') ? 3 : 1 ?>
        <input type="hidden" value="<?php echo $tenant ?>" id="cur_tenant" />
        <input type="hidden" value="<?php echo $basesslurl; ?>" id="baseurl" />
        <input type="hidden" value="<?php echo $min_conds; ?>" id="min_conds" />
        <input type="hidden" value="<?php echo $this->lang->line('Perso rule label'); ?>" id="rule_label" />
        <input type="hidden" value="<?php echo $this->lang->line('Perso option select'); ?>" id="option_select" />
        <input type="hidden" value="<?php echo $this->lang->line('Perso option select tt'); ?>" id="option_select_tt" />
        <input type="hidden" value="<?php echo $this->lang->line('Perso del condition'); ?>" id="msg_del_condition" />
        <input type="hidden" value="<?php echo $this->lang->line('Perso confirm cancel'); ?>" id="msg_conf_cancel" />
        <input type="hidden" value="<?php echo $this->lang->line('Perso confirm del rule'); ?>" id="msg_del_rule" />

        <div id="perso_rule_editor_overlay" class="perso_rule_overlay <?php echo $tenant; ?>"></div>

        <div id="perso_rule_editor_wrapper" class="perso_rule_wrapper <?php echo $tenant; ?>">

            <img src="<?php echo $basesslurl . $close_img ?>" id="perso_rule_editor_close"/>

            <div id="perso_rule_main_container" class="<?php echo $tenant; ?>">
                <h1 <?php echo 'class="' . $tenant . '"'; ?>><?php echo $this->lang->line('Perso popup title'); ?></h1>

                <div id="perso_rule_list_container">
                    <div id="perso_rule_list"></div>
                    <div id="perso_rule_actions">
                        <a href="javascript:void(0)" class="<?php echo $tenant; ?>" id="perso_rule_add_action">
                            +<?php echo $this->lang->line('Perso add rule'); ?>
                        </a>
                        <a href="javascript:void(0)" class="<?php echo $tenant; ?>" id="perso_rule_del_action">
                            -<?php echo $this->lang->line('Perso del rule'); ?>
                        </a>
                    </div>
                </div>

                <div id="perso_rule_param_container">

                    <h3 class="perso_rule_hidden_title"><?php echo $this->lang->line('Perso not defined'); ?></h3>
                    <p class="perso_rule_hidden_title"><?php echo $this->lang->line('Perso please start'); ?></p>

                    <form id="perso_rule_form">
                        <div id="perso_rule_parameters">
                            <label class="perso_rule_label  <?php echo $tenant; ?>">
                                <?php
                                echo $this->lang->line('Perso rule name') . ' ';
                                if ($tenant == 'etracker') {
                                    echo '<div class="orange-star-3-4">*</div>';
                                }
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
                            <div class="clear"></div>
                        </div>

                        <a href="javascript:void(0)" id="perso_rule_add_condition" class="perso_rule_add_cond <?php echo $tenant; ?>">
                            <?php echo $this->lang->line('Perso add condition'); ?>
                        </a>
                        <div class="clear"></div>
                    </form>

                </div>
            </div>
            <div class="clear"></div>

            <div class="perso_rule_btn_container <?php echo $tenant; ?>">
                <input type="button" class="perso_rule_btn perso_rule_cancelbtn <?php echo $tenant; ?>" 
                       id="perso_rule_cancel" value="<?php echo $this->lang->line('Abbrechen'); ?>" />
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

    </body>

</html>