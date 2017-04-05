<script type="text/javascript">
				bt_teasertest_translations = <?= json_encode($this->lang->line('tt config layer')) ?>;
				bt_teasertest_details = <?= json_encode($project) ?>;
</script>

<div id="main_container">
    <div class="whitebox" id="scrollToHere">
        <div class="details_menu tt_details_menu">
            <div class="action_trigger">
                <a class="action_title" href="javascript:void(0)">
                    <?= $this->lang->line('link_edit'); ?>
                </a>

                <div class="action_menu">
                    <div class="top"></div>
                    <div class="middle">
                        <a href="javascript:void(0);" id="tt_open_testpageurl"><?= $this->lang->line('Testseite'); ?></a>
                        <a href="<?= $project->previewurl ?>" target="_blank"><?= $this->lang->line('tto_link_preview'); ?></a>
                        <a href="javascript:void(0);" id="<?= $start_stop_id ?>" class="tt_start_stop_test"><?= $start_stop_label ?></a>
                    </div>
                    <div class="bottom"></div>
                </div>
            </div>
        </div>

        <div class="title">
            <span>
                <?= $project->name . " - " . $this->lang->line('testtype_teaser'); ?>
            </span>
            <br/>
            <?php 
                if($project->status == 'PAUSED') {
                    echo "<h3>" . $this->lang->line('smry_headline_paused') . "</h3>";
                }
            ?>
        </div>

        <?php if (!$no_tests) { ?>
            <div>
                <div class="tto_intro">
                    <?= $this->lang->line('tto_intro_headline') ?>
                </div>
            </div>

            <?php if ($interface != 'API') { ?>
                <div class="tto_btn_container">
                    <input id="tt_create_headline_link" type="submit" value="<?= $this->lang->line('tto_btn_newtest') ?>" class="button ok">
                </div>
            <?php } ?>

            <div>
                <table id="tto_headlines_table" border="0" cellspacing="0" cellpadding="0" class="table">
                    <tr class="table-title">
                        <td class="first"><?= $this->lang->line('table_tt_headline'); ?></td>
                        <td><?= $this->lang->line('table_tt_views_clicks'); ?></td>
                        <td><?= $this->lang->line('table_tt_ctr'); ?></td>
                        <td><?= $this->lang->line('table_tt_age'); ?></td>
                        <td><?= $this->lang->line('table_tt_result'); ?></td>
                        <td>&nbsp;</td>
                        <td><?= $this->lang->line('table_tt_action'); ?></td>
                    </tr>
                    <?php foreach ($groups as $row) { ?>
                        <tr id="<?= $row['id']; ?>" class="table-list" >
                            <td class="tto_table_headline"><?= $row['headline'] ?></td>
                            <td class="tto_table_views"><?= $row['views_clicks'] ?></td>
                            <td class="tto_table_ctr"><?= $row['ctr']; ?></td>
                            <td class="tto_table_age"><?= $row['age']; ?></td>
                            <td class="tto_table_result"><?= $row['result']; ?></td>
                            <td class="tto_table_rec"><?= $row['rec_icon']; ?></td>
                            <td class="tto_table_action">
                                <div class="details_menu">
                                    <div class="action_trigger">
                                        <a class="action_title" href="javascript:void(0)">
                                            <?= $this->lang->line('table_tt_action_select'); ?>
                                        </a>

                                        <div class="action_menu">
                                            <div class="top"></div>
                                            <div class="middle">
                                                <?= $row['options'] ?>
                                            </div>
                                            <div class="bottom"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <?php
        } else if ($interface != 'API') {
            ?>
            <div class="welcome-container">
                <div class="get-started">
                    <?= $this->lang->line('welcome_getstarted') ?> 
                    <a id="tt_create_headline_link" class="popup" href="javascript:void(0)">
                        <?= $this->lang->line('welcome_getstarted_headline_link') ?>
                    </a>
                    <p>
                        <?= $this->lang->line('welcome_getstartedpara') ?> 
                        <a href="<?= $this->lang->line('welcome_userdoc_target') ?>" target="_blank">
                            <?= $this->lang->line('welcome_userdoc_text') ?>
                        </a>
                    </p>
                </div>

            </div>
        <?php } ?>
    </div>
</div>