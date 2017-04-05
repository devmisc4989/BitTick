<?php
$tenant = $this->config->item('tenant');
$basesslurl = $this->config->item('base_ssl_url');
$persointro = (int) $persomode > 0 ? $this->lang->line('Perso edit campaign') : $this->lang->line('Perso new campaign');
$persoid = ((int) $persoid > 0) ? $persoid : 0;
?>
<div class="hide">
    <?php
    echo '<input type="hidden" value="' . $tenant . '" id="current-tenant" />';
    echo '<input type="hidden" value="' . $collectionid . '" id="lpc-collection-id" />';
    echo '<input type="hidden" value="' . $persomode . '" id="current-persomode" />';
    echo '<input type="hidden" value="' . $personame . '" id="complete-personame" />';
    echo '<input type="hidden" value="' . $persoid . '" id="complete-persoid" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso new campaign') . '" id="perso-new-campaign" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso edit campaign') . '" id="perso-edit-campaign" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso noperso intro') . '" id="perso-noperso-intro" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso complete intro') . '" id="perso-complete-intro" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso single intro') . '" id="perso-single-intro" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso has sms') . '" id="perso-has-sms" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso table title') . '" id="perso-table-title" />';
    echo '<input type="hidden" value="' . $this->lang->line('Perso unpersonalized') . '" id="perso-unpersonalized" />';
    echo '<input type="hidden" value="' . $this->lang->line('Variant label') . '" id="perso-variant-label" />';
    $persoClass = ($persolevel == 'disabled') ? 'disabled' : '';
    ?>
    <div class="confirmation confirmation-user" id="details-page-personalization">
        <h1><?php echo $this->lang->line('Perso nav title'); ?></h1>
        <div style="clear:both"></div>

        <form method="post" id="frm_edit_lpc_personalization" class="<?php echo $tenant; ?>" action="javascript:void(0)">
            <div class="headline">
                <p id="perso-headline" class="<?php echo $tenant; ?>"><?php echo $this->lang->line('Perso edit campaign'); ?></p>
                <div style="clear:both"></div>

                <div id="steps-perso-radio-container">
                    <input class="perso-type-radio" id="perso-type-0" type="radio" name="perso-type-selection" value="0" 
                           <?php if ((int) $persomode == 0) echo 'checked'; ?> />
                    <label class="perso-type-label <?php echo $tenant; ?>" for="perso-type-0">
                        <?php echo $this->lang->line('Perso no personalization'); ?>
                    </label>
                    <div class="clear"></div>

                    <input class="perso-type-radio" id="perso-type-2" <?php echo $persoClass; ?> type="radio" name="perso-type-selection" value="2" 
                           <?php if ((int) $persomode == 2) echo 'checked'; ?> />
                    <label class="perso-type-label <?php echo $persoClass . '  ' . $tenant; ?>" for="perso-type-2">
                        <?php echo $this->lang->line('Perso single variant'); ?>
                    </label>
                    <div class="clear"></div>

                    <input class="perso-type-radio" id="perso-type-1" <?php echo $persoClass; ?> type="radio" name="perso-type-selection" value="1" 
                           <?php if ((int) $persomode == 1) echo 'checked'; ?> />
                    <label class="perso-type-label <?php echo $persoClass . '  ' . $tenant; ?>" for="perso-type-1">
                        <?php echo $this->lang->line('Perso complete test'); ?>
                    </label>
                    <div class="clear"></div>
                </div>

                <div id="steps-perso-upgrade-container">
                    <?php if ($persolevel == 'disabled') { ?>
                        <a class="step-product-upgrade  <?php echo $tenant; ?>" target="_blank" href="<?php echo $this->config->item('etracker_product_upgrade') ?>" >
                            <span class="upgrade-text"><?php echo $this->lang->line('Perso enable now'); ?></span>
                            <span class="upgrade-rocket  <?php echo $tenant ?>">
                                <i class="fa fa-rocket"></i>
                            </span>
                        </a>
                    <?php } ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>

            <div id="step-perso-bottom-container" class="<?php echo $tenant; ?>">
                <span id="perso-copy-text" class="<?php echo $tenant; ?>"></span>

                <div id="perso-complete-rule" class="<?php echo $tenant; ?>">
                    <div id="edit-rule-link-container" class="<?php echo $tenant; ?>">
                        <a href="javascript:void(0)" class="rule-list-link perso_wizard_link"><?php echo $this->lang->line('Perso table title'); ?>: </a>
                        <div class="clear"></div>
                    </div>
                </div>

                <div id="perso-table-container" class="<?php echo $tenant; ?>"></div>
                <input type="hidden" id="perso-complete-ruleid" name="perso-complete-ruleid" value="0" />
                <div style="clear:both"></div>
            </div>

            <?php if ($tenant == 'etracker') { ?><div class="links-3-4"><?php } ?>
                <div class="ctrl-buttons">
                    <div class="links  <?php echo $persolevel . '-perso'; ?>">
                        <a href="javascript:void(0)" class="editor_back" id="perso_cancel_edition"><?php echo $this->lang->line('Abbrechen'); ?></a>
                    </div>
                    <?php if ($persolevel != 'disabled') { ?>
                        <input type="submit" class="button ok" value="<?php echo $this->lang->line('Personalization_Save_Changes'); ?>"/>
                           <?php } ?>
                </div>
                <?php if ($tenant == 'etracker') { ?></div><?php } ?>
        </form>
    </div>
</div>