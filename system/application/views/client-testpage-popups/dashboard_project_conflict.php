<script type="text/javascript">
    var conflictLayerLang = <?= json_encode($conflictLayerLang) ?>;
</script>

<div class="hide">
    <div class="confirmation confirmation-user" id="project_conflict_layer">
        <h1></h1>

        <div class="headline">
            <strong><?= $conflictLayerLang['subtitle'] ?>  "<span id="project_conflict_name"></span>"</strong><br />
            <span id="project_conflict_intro"></span>
        </div>
        <div id="project_conflict_result">
            <table id="project_conflict_table" class="wizard-table">
                <tbody></tbody>
            </table>
        </div>

        <div id="conflict_layer_confirm">
            <?php if ($this->config->item('tenant') == 'etracker') { ?>
                <div class="links-3-4">
                <?php } ?>
                <div class="ctrl-buttons">
                    <div class="links">
                        <a id="conflict_layer_cancel" class="editor_back close-btn" href="#"><?php echo $this->lang->line('Abbrechen'); ?></a>
                    </div>
                    <input id="conflict_layer_continue" class="button ok" type="submit" value="">
                </div>
                <?php if ($this->config->item('tenant') == 'etracker') { ?>
                </div>
            <?php } ?>
        </div>

    </div>
</div>
