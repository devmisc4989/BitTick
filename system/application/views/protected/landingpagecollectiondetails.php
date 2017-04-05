<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$imgurl = $this->config->item('image_url');
$scripturl = $this->config->item('script_url');
$this->lang->load('landingpagecollectiondetails');
$this->lang->load('collectionoverview');
$this->lang->load('editor');
$this->lang->load('personalization');
$this->lang->load('table');
$tenant = $this->config->item('tenant');
$NoSingleNoSms = $persomode != 2 && $smscount == 0;
$editor_href = $basesslurl . 'editor/edit/' . $collectionid;
$menuClass = ' details_menu etracker_details_menu ';
$menuClass .= $isTT ? ' tt_details_menu ' : '';

$sandboxExportHref = $basesslurl . "lpc/sandboxExport/$collectionid/$clientid";
$sandboxExportFilename = "testdef_" . $collectionid . ".js";

$editor_link_text = $this->lang->line('Original und Varianten');
$editor_link_href = "javascript://";
if ($testtype == OPT_TESTTYPE_VISUALAB || $testtype == OPT_TESTTYPE_MULTIPAGE) {
    $editor_link_href = $editor_href;
}

if ($testtype == 2) {
    $editor_link_attributes = ' class="disabled_menu"';
} elseif ($testtype == 1) {
    $editor_link_attributes = '  onclick="OpenEditor();"';
}

$apigoals = $this->config->item('api_goals');

?>

<?php if ($testtype == OPT_TESTTYPE_SPLIT) { ?>
    <script type="text/javascript">
        var bt_clickgoals_vars = {
            lpcid: <?= $collectionid ?>,
            goalType: '',
            goalPrefix: '',
            goalLabel: '',
            goalTagLabel: '',
            pleaseSelect: "<?= $this->lang->line('Choose Target Page'); ?>",
            testtypeSplit: true,
            testtypeVisual: false,
            testtypeMultipage: false
        };
    </script>
<?php } else { ?>
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
<?php } ?>

<div id="main_container">
    <div class="whitebox" id="scrollToHere">
<?php 
if($action != 'showdataonly') { ?>
        <div class="<?= $menuClass ?>">
                <?php
                if ($testtype == OPT_TESTTYPE_TEASER) {
                    $href = $basesslurl . 'lpc/tto/' . $collectionid;
                    ?>
                    <a class="editor_back action_title tt_back" href="<?= $href ?>"><?= $this->lang->line('tt_back_overview') ?></a>
                <?php } ?>
            
                <?php if ($interface != 'API') { ?>
                    <div class="action_trigger">
                        <a class="action_title" href="javascript:void(0)"><?php 
                            if (!$isTT) 
                                echo $this->lang->line('link_edit');
                            else 
                                echo $this->lang->line('table_tt_action_edit');
                            if ($tenant == 'etracker') {
                                ?>
                                <span class="down">▼</span>
                                <span class="up">▲</span>
                            <?php } ?>
                        </a>
                        <!--<div class="action_menu" style="display: none;">-->
                        <div class="action_menu">
                            <div class="top"></div>
                            <div class="middle">
                                <a id="lcd_original_variants" href="<?= $editor_link_href ?>" <?= $editor_link_attributes ?> ><?= $editor_link_text ?></a>
                                <?php
                                 if (!$isTT) { ?>
                                    <a href="javascript://" onclick="OpenTestpageUrl();"><?php echo $this->lang->line('Testseite'); ?></a>
                                    <a href="javascript://" onclick="OpenGoals();"><?php echo $this->lang->line('Goals'); ?></a>
                                    <a href="javascript://" class="action_menu_allocation"><?= $this->lang->line('Edit Visual A/B Test (set allocation)'); ?></a>
                                    <a href="javascript://" onclick="OpenDiagnoseMode();"><?php echo $this->lang->line('Diagnose_Mode'); ?></a>
                                    <?php 
                                    if(getSandboxExportLevel()=='available') {
                                    ?>
                                    <a href="<?= $sandboxExportHref ?>" download="<?= $sandboxExportFilename ?>"><?php echo $this->lang->line('Sandbox export'); ?></a>
                                    <?php
                                    }
                                }
                                    ?>                                    
                            </div>
                            <div class="bottom"></div>
                        </div>
                    </div>

                    <?php
                }
                // display the link to start or restart collection only under certain conditions
        // and choose "start" and "restart" depending on the status and visitorcount
        $startlink = false;
        $restartlink = false;
        $playlink = false;
        $pauselink = false;
        if ($visitorcount == 0) {
            if (($collectionstatus == OPT_PAGESTATUS_PAUSED) || ($collectionstatus == OPT_PAGESTATUS_UNVERIFIED)) {
                $startlink = true;
            }
            if ($collectionstatus == OPT_PAGESTATUS_ACTIVE) {
                $pauselink = true;
            }
        } else {
            if (($collectionstatus == OPT_PAGESTATUS_PAUSED) || ($collectionstatus == OPT_PAGESTATUS_UNVERIFIED)) {
                $restartlink = true;
                $playlink = true;
            }
            if ($collectionstatus == OPT_PAGESTATUS_ACTIVE) {
                $restartlink = true;
                $pauselink = true;
            }
        }
        $target = "lpc/lcd/" . $collectionid;
        ?>

        <?php if ($tenant != 'etracker' && $interface != 'API') {
            if (!$isTT) {
                $pauseText = $this->lang->line('link_pause');
                $startText = $this->lang->line('link_start');
                $playText = $this->lang->line('link_play');                
            }
            else {
                $pauseText = $this->lang->line('table_tt_action_pause');
                $startText = $this->lang->line('table_tt_action_start');
                $playText = $this->lang->line('table_tt_action_play');                                
            }

            $toggleText = $pauseText;
            $toggleId = 'toggle_0';
            if (!$pauselink) {
                $toggleText = $playlink ? $playText : $startText;
                $toggleId = 'toggle_1';
            }
            ?>
            <div class="submenu etracker_testdetail">
                <a class="lcd_toggle btn_start_continue" id="<?= $toggleId ?>" href="javascript:void(0);"><?= $toggleText ?></a>
            <?php if ($restartlink) { 
                if(!$isTT) { ?>
                    <a id="lcd_restart" onclick="RestartCollection();" href="javascript:void(0);"><?= $this->lang->line('link_restart'); ?></a>
                <?php 
                } 
                else { ?>
                    <a id="lcd_restart" onclick="RestartCollection();" href="javascript:void(0);"><?= $this->lang->line('table_tt_action_restart'); ?></a>                
                <?php
                }
            } ?>           
            </div>
        <?php } ?>



    </div>

    <?php
    // special styles for etracker
    if ($tenant == 'etracker') {
        $headline = $this->lang->line('title_collection') . $collectionname;
        $maxlen = 40;
        if (strlen($headline) > $maxlen) {
            $headline = substr($headline, 0, $maxlen) . "...";
        }

        echo "<h3 class=\"test-name-header\" title=\"" . $collectionname . "\">" . $headline . "</h3>";
    }
    ?>

    <div class="title titlewrap etracker_titlewrap  <?php if($isTT) { ?> tt_titlewrap <?php } ?>">
        <span>
            <?php
            if ($tenant != 'etracker') {
                echo "$collectionname";
            }

            $ttype = '';
            if($smartmessage == 1) {
                $ttype = ' - ' . $this->lang->line('testtype_sms');
            } else if ($testtype == OPT_TESTTYPE_SPLIT) {
                $ttype = ' - ' . $this->lang->line('testtype_split');
            } else if ($testtype == OPT_TESTTYPE_VISUALAB) {
                $ttype = ' - ' . $this->lang->line('testtype_visual');
            } else if ($testtype == OPT_TESTTYPE_MULTIPAGE) {
                $ttype = ' - ' . $this->lang->line('testtype_multipage');
            } else if ($testtype == OPT_TESTTYPE_TEASER) {
                $ttype = ' - ' . $this->lang->line('testtype_teaser');
            }
            echo $ttype;
            $collectionid = $this->uri->segment(3);
            ?>
        </span>
        <br/>
        <?php
        if ($tenant == 'etracker' && $display_summary) {
            echo $summary_headline; 
        }
        ?>

    </div>

    <?php
    // special styles for etracker
    if ($tenant != 'etracker'  && $display_summary) {
        ?>
        <h3><?php echo $summary_headline; ?></h3>
    <?php 
    }
    if(!$isTT) {
    ?>
        <div class="etracker_summary">
            <?php
            echo ($display_summary) ? $summary_subline . '<br />' : '';
            
            if ($NoSingleNoSms && $display_status && !$isTT) {
                if ($autopilot == 1) {
                    echo $this->lang->line('autopilot_is_active');
                    ?>
                    <a onclick="toggleautopilot(<?php echo $collectionid . "," . $clientid ?>, 1, '<?= $target ?>');" href="javascript://">
                        <?php echo $this->lang->line('autopilot_stop'); ?>
                    </a>
                    <?php
                } else {
                    echo $this->lang->line('autopilot_is_stopped');
                    ?>
                    <a onclick="toggleautopilot(<?php echo $collectionid . "," . $clientid ?>, 0, '<?= $target ?>');" href="javascript://">
                        <?php echo $this->lang->line('autopilot_activate'); ?>
                    </a>
                    <?php
                }
                echo "<br>";
            }
            
                $persoStatus = 'Personalization_Status_' . $persomode;
                echo $this->lang->line($persoStatus) . ($persomode == 1 ? '"' . $personame . '".' : '.');                
            ?>
            <a href="javascript:void(0);" class="open_step_perso">
                <?php echo $this->lang->line('Personalization_Status_Link'); ?>
            </a>
        </div>
    <?php } ?>
        <br />


<?php if ($tenant == 'etracker') { ?>
    <div class="clear"></div>
    <div class="etracker_buttons etracker_testdetail">
        <?php
        // now display links as per dispatcher
        if ($playlink) {
            ?>
            <a class="btn_start_continue" onclick="togglecollection(<?php echo $collectionid . "," . $clientid ?>, 1, '<?= $target ?>');" href="javascript://"><?php echo $this->lang->line('link_play'); ?></a>
            <?php
        }
        if ($pauselink) {
            ?>
            <a onclick="togglecollection(<?php echo $collectionid . "," . $clientid ?>, 0, '<?= $target ?>');" href="javascript://"><?php echo $this->lang->line('link_pause'); ?></a>
            <?php
        }
        if ($restartlink) {
            ?>
            <a onclick="RestartCollection();" href="javascript://"><?php echo $this->lang->line('link_restart'); ?></a>
            <?php
        }
        if ($startlink) {
            ?>
            <a class="btn_start_continue" onclick="togglecollection(<?php echo $collectionid . "," . $clientid ?>, 1, '<?= $target ?>');" href="javascript://"><?php echo $this->lang->line('link_start'); ?></a>
        <?php }
        ?>

    </div>
<?php } 
}
?>

    <div class="clear"></div>
    
    <?php
    $clickrg = '/g+\_+[0-9]{4}/';
    $totalGoals = count($collectionGoals);
    $allGoals = $this->lang->line('Available Goals');
    $isVisualTest = $testtype == OPT_TESTTYPE_VISUALAB || $testtype == OPT_TESTTYPE_MULTIPAGE;
    
    if ($totalGoals > 0) { ?>
        <label class="details_goal_label"><?= $this->lang->line('Goals') ?>:</label>
        <?php if ($totalGoals > 1) { 
            ?>
            <select id="lpc_goal_select" class="details_goal_dropdown">
            <?php foreach ($collectionGoals as $goal) {
                if ($goal['status'] == 'ARCHIVED') {
                    continue;
                }

                $selected = $goal['selected'] ? ' selected="selected" ' : '';
                $gtype = $goal['type'];

                $label = $allGoals[$gtype];
                if ($gtype == GOAL_TYPE_CLICK && $isVisualTest) {
                    $gname = preg_replace($clickrg, '', $goal['name']);
                    $label = $this->lang->line('Click goal prefix') . $gname;
                }
                $label .= $goal['level'] == 1 ? ' (' . $this->lang->line('Primary goal label') . ')' : '';
                ?>
                <option value="<?= $goal['collection_goal_id'] ?>" <?= $selected ?> ><?= $label ?></option>
            <?php } ?>
            </select>
        <?php } else { 
            $gtype = $collectionGoals[0]['type'];
            $label = $allGoals[$gtype];
            if($gtype == GOAL_TYPE_CLICK && $isVisualTest){
                $gname = preg_replace($clickrg, '', $collectionGoals[0]['name']);
                $label = $this->lang->line('Click goal prefix') . $gname;
            }
            ?>
             <span id="<?= $collectionGoals[0]['collection_goal_id'] ?>" class="details_goal_name"><?= $label ?> </span>
        <?php 
        }
    }
    
    if ($testtype == OPT_TESTTYPE_TEASER) { 
        $urlInterval = filter_input(INPUT_GET, 'timeinterval');
        ?>
        <label class="details_goal_label time_interval_label"><?= $this->lang->line('Time interval label') ?>:</label>
            <select id="lpc_interval_select" class="details_goal_dropdown">
            <?php foreach ($this->lang->line('Available time intervals') as $key => $value) { 
                $selected = $urlInterval && $key == $urlInterval ? ' selected="selected" ' : '';
                ?>
                <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
            <?php } ?>
            </select>

        <img id="detail_page_reload" src="<?php echo $basesslurl; ?>images/reload.png" onClick="location.reload();" title="<?= $this->lang->line('button_reload') ?>">
    <?php } ?>
    <div class="clear"></div>
    
    <?php if ($testtype != OPT_TESTTYPE_TEASER) { ?>
        <div class="chart_dates">
            <a href="javascript:void(0);" class="chart_date_links prev_date">
                <?= $this->lang->line('lcd_previouslink'); ?>
            </a>
            <span class="chart_title"></span>
            <a href="javascript:void(0);" class="chart_date_links next_date">
                <?= $this->lang->line('lcd_nextlink'); ?>
            </a>
        </div>
        <div class="clear"></div>
    <?php } else { ?>
        <div class="chart_separator"></div>
    <?php } ?>
        
        <div id="lpc_chart_container">
            <div id="lpc_chart"></div>
            <div class="clear"></div>
        </div>
        
<script type="text/javascript">
    $(document).ready(function () {
        $('select#lpc_goal_select').on('change', function () {
            var goal = $(this).val();
            checkUrlQueryString('goalid', goal);
        });
        
        $('select#lpc_interval_select').on('change', function () {
            var interval = $(this).val();
            checkUrlQueryString('timeinterval', interval);
        });
        
        var checkUrlQueryString = function(qname, qvalue){
            var location = window.location.href.split('?');
            var newqs = '?';
            
            if(typeof(location[1]) !== 'undefined'){
                var qs = location[1].split('&');
                $.each(qs, function(ind, value){
                    var values = value.split('=');
                    if(values[0] !== qname){
                        newqs += values[0] + '=' + values[1] + '&';
                    }
                });
                newqs += qname + '=' + qvalue;
                window.location.href = newqs;
            }else{
                window.location.href = '?' + qname + '=' + qvalue;
            }
        };
    });
</script>

<div class="clear"></div>

<?php if($action != 'showdataonly') { ?>
<div class="etracker_help_link"><?php echo splink('lcd_description'); ?></div>
<?php } // end showdataonly ?>

<?php
$visitConv = $this->lang->line('table_visitors_conversions_short');
$crHeadline = $this->lang->line('table_cr');
$nameHeadline = $this->lang->line('table_page');
$resultHeadline = $this->lang->line('table_result');
if($isTT) {
    $visitConv = $this->lang->line('table_tt_views_clicks');    
    $crHeadline = $this->lang->line('table_tt_ctr');
    $nameHeadline = $this->lang->line('table_tt_headline');
    $resultHeadline = $this->lang->line('table_tt_result');
}
$showImpressions = TRUE;

foreach ($collectionGoals as $key => $goal) {
    if ($goal['selected']) {
        if ($goal['type'] == GOAL_TYPE_TIMEONPAGE) {
            $availableGoalsLang = $this->lang->line('Available Goals');
            $visitConv = $this->lang->line('table_impr');
            $crHeadline = $availableGoalsLang[GOAL_TYPE_TIMEONPAGE];
            $showImpressions = FALSE;
        }
        if ($goal['type'] == GOAL_TYPE_PI_LIFT) {
            $availableGoalsLang = $this->lang->line('Available Goals');
            $visitConv = $this->lang->line('table_impr');
            $crHeadline = $availableGoalsLang[GOAL_TYPE_PI_LIFT];
            $showImpressions = FALSE;
        }
        if ($goal['type'] == GOAL_TYPE_COMBINED) {
            $availableGoalsLang = $this->lang->line('Available Goals');
            $visitConv = $this->lang->line('table_impr');
            $crHeadline = $availableGoalsLang[GOAL_TYPE_COMBINED];
            $showImpressions = FALSE;
        }
        break;
    }
}
?>

<div class="clear"></div>
<table id="collectiondetails" width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
    <tr class="table-title">
        <td class="first" style="width:50px;"><?php echo $this->lang->line('table_display'); ?></td>
        <?php if($action != 'showdataonly') { ?>
        <td style="width:1px;">&nbsp;</td>
        <?php } 
            // when displaying page for teaser test we need more room for the name
            if($testtype == OPT_TESTTYPE_TEASER)
                $widthNameColumn = "width:350px;";
            else
                $widthNameColumn = "width:150px;";
        ?>
        <td style="<?= $widthNameColumn ?>"><?= $nameHeadline ?></td>
        <?php if($action != 'showdataonly' && !$isTT) { ?>
        <td style="width:150px;"><?php echo $this->lang->line('Perso table title'); ?></td>
        <?php } ?>
        <td><?= $visitConv ?></td>
        <td><?= $crHeadline ?></td>
        <td style="width:130px;" class="last"><?= $resultHeadline ?></td>
    </tr>
    <?php
    $colors = $this->config->item('COLORS');
    $count = 0;
    $oRuleName = '';
    foreach ($landingpages as $row) {
        if ($row["id"] != "")
            $lpid = $row["id"]; //$this->encrypt->encode($row["id"]);
        $inactive = $row['status'] == 1 ? 'inactive ' : '';
        
        if (is_array($controlpageurl)) {
            $previewurl = array();
            foreach ($controlpageurl AS $ctrl => $val) {
                foreach($val AS $ind => $idp){
                    if($ind == $row["variantindex"]) {
                        $attach = $row['pagetype'] == OPT_PAGETYPE_VRNT ? "_p=t&BT_lpid=" . $idp : "_p=t";
                        $previewurl[] = attachQuerystring($ctrl, $attach);
                    }
                }
            }
        } else {
            $attach = $row['pagetype'] == OPT_PAGETYPE_VRNT ? "_p=t&BT_lpid=" . $lpid : "_p=t";
            $previewurl = attachQuerystring($controlpageurl, $attach);
        }

        if ($row['pagetype'] == OPT_PAGETYPE_VRNT) {
            $linkClss = ($persomode == 1) ? 'disabled' : 'rule-table-link perso_wizard_link';
        } else {
            $linkClss = ($persomode != 1) ? 'disabled' : 'rule-table-link perso_wizard_link';
        }

    $linkId = (int)$row['rule_id'] . '_lprule_' . $lpid;
        $tdDisabled = ($count == 0 && !$NoSingleNoSms) ? 'class="disabled"' : '';
        $oRuleName .= ($count == 0) ? $row['rulename'] : '';
        $bgcolor = $colors[$count % sizeof($colors)];
        ?>
         <tr id="<?php echo $row['id']; ?>" data-bgcolor="<?= $bgcolor ?>" class="table-list  <?php echo 'variant_row_' . $count; ?>" >
            <td>
                <div class="squarecheck"><input type="checkbox"<?php if ($count < $this->config->item('chart_visible_lines')) { ?> checked="checked"
                    <?php
                    };
                    if ($count == 0) {
                        ?> disabled="disabled"<?php } ?> /></div>
                <div class="square" style="background-color:<?= $bgcolor ?>"></div>
            </td>
            <?php
            if ($tenant == 'etracker')
                $preview_icon_url = $basesslurl . "images/etracker/sbrowse.gif";
            else
                $preview_icon_url = $basesslurl . "images/icon_preview.png";
            ?>
            <?php if($action != 'showdataonly') { ?>
            <td class="icon">
                <?php if (is_array($previewurl)) { 
                    echo '<br /><br />';
                    foreach($previewurl AS $preview){ ?>
                        <a href="<?= $preview ?>" target="_blank"  <?php if ($row['pagetype'] != OPT_PAGETYPE_VRNT) { ?> class="menu_last"<?php } ?>>
                            <img title="Vorschau ID:<?= $lpid ?>" src="<?= $preview_icon_url ?>"/></a>
                        <br />
                    <?php 
                    }
                } else if (!$isTT) { ?>
                    <a href="<?= $previewurl ?>" target="_blank"  <?php if ($row['pagetype'] != OPT_PAGETYPE_VRNT) { ?> class="menu_last"<?php } ?>>
                        <img title="Vorschau ID:<?= $lpid ?>" src="<?= $preview_icon_url ?>"/></a>
                <?php } ?>
            </td>
            <?php } // end showdataonly ?>
            <td <?php echo $tdDisabled?> class="lpc_variant_name" data-vname="<?= $row['name'] ?>">
                <?php // determine the name field
                if($row['pagetype'] == OPT_PAGETYPE_CTRL) { // output static string for original page
                    if($testtype == OPT_TESTTYPE_TEASER) { // when teaser test, we need the real content of the name
                        $name = $row['name'];
                    }
                    else {
                        $name = $this->lang->line('Original Source');
                    }
                }
                else {
                    if (trim($row['name']) == "") { // when variant name empty, use url
                        $link = trim_link($row["lp_url"], $this->config->item('max_link_trim_length'));
                        $name = $link;
                    }
                    else {
                        $name = $row['name'];
                    }
                }
                echo mb_strimwidth($name, 0, 45, "...");
                
                if ($groupDetails&&$testtype==OPT_TESTTYPE_MULTIPAGE) {
                    foreach ($groupDetails AS $group) {
                        echo '<br />' . $group->name;
                    }
                }
                ?>
            </td>
            <?php if($action != 'showdataonly' && !$isTT) { ?>
            <td id="<?php echo $row['rule_id'] . '_' . $row['id'] . '_'.$count; ?>"  <?php echo $tdDisabled; ?>>
                <?php if ($persomode == 2 && $count == 0) {
                    echo ''; 
                } else if ($persomode == 0 || ($count > 0) && ($row['rule_id'] == 0 && $persomode == 2)) {
                    if ($persomode == 2 && $count > 0) { ?>
                        <a href="javascript:void(0)" id="<?php echo $linkId; ?>" class="<?php echo $linkClss; ?>">
                            <?php echo $this->lang->line('Personalization_Unpersonalized_change'); ?>
                        </a>
                    <?php } else {
                        echo $this->lang->line('Personalization_Not_Personalized'); 
                    }
                } else { ?>
                    <a href="javascript:void(0)" id="<?php echo $linkId; ?>" class="<?php echo $linkClss; ?>">
                        <?php echo ($persomode == 1) ? $oRuleName : $row['rulename']; ?>
                    </a>
                <?php } ?>
            </td>
            <?php } // end showdataonly ?>
            <td <?php echo $tdDisabled?>><?=  ($showImpressions ? $row['impressions'] . ' / ' : '') . $row['conversions']; ?></td>
            <td <?php echo $tdDisabled?>><?php echo $row['cr']; ?></td>
            <td><?php if($NoSingleNoSms) echo $row['result']; ?></td>
        </tr>
        <?php
        $count = $count + 1;
    }
    ?>
</table>	 	
<input type="hidden" id="path" value="<?php echo $basesslurl ?>"/>
</div>
</div>
<!--POPUP-->

<script type="text/javascript">
//DM: JS comments starts with // <!-- script validate form --> // In my case the upper menu didn`t show on mouse hover
   $(document).ready(function()
   {
       bt_edit_personalization.init();

       $("div.action_trigger").hover(
               function() {
                   $(this).addClass('action_over');
                   var menu = $(this).parent().find("div.action_menu");
                   menu.show();
               },
               function() {
                   $(this).removeClass('action_over');
                   var menu = $(this).parent().find("div.action_menu");
                   menu.hide();
               }
       );

       $("#frmLandingPage").validationEngine();
       $('input[title]').qtip({
           position: {
               corner: {
                   target: 'topRight',
                   tooltip: 'bottomLeft'
               }
           },
           style: {
               name: 'cream',
               padding: '7px 13px',
               width: {
                   max: 210,
                   min: 0
               },
               tip: true
           }
       });
       $('textarea[title]').qtip({
       position: {
       corner: {
       target: 'topRight',
               tooltip: 'bottomLeft'
       }
       },
               style: {
       name: 'cream',
               padding: '7px 13px',
               width: {
       max: 210,
               min: 0
       },
               tip: true
 }
});

$('.allocation #allocation').val(<?php echo $allocation ?>);

    });
    var path = "<?php echo $basesslurl ?>";

    function updateCollections_local()
    {
       updateCollections2();

       $.fancybox.close();
       document.location = document.location.href;
    }
    function RestartCollection()
    {
       OpenPopup("#restartCollection");
    }
    function OpenEditor()
    {
       if (testtype == <?= OPT_TESTTYPE_MVT ?> || testtype == <?= OPT_TESTTYPE_VISUALAB ?>)
       {
           $("#vablpname").val("<?= $control_url ?>");
           setTimeout(function() {
               $('html').animate({scrollTop: 0}, 50, function() {
                   BTTestType = 'visual';
                   SetInitialValues();
                   CreateVisualAB(2)
               })
           }, 50);
       }
       else if (testtype == <?= OPT_TESTTYPE_SPLIT ?>)
       {
           setTimeout(function() {
               $('html').animate({scrollTop: 0}, 50, function() {
                   BTTestType = 'split';
                   SetInitialValues();
                   CreateAB(1)
               })
           }, 50);
       }

    }
    ;
    function OpenTestpageUrl()
    {
        if ($('#frmVisualABStep3').data('stored') !== true) {
            $('#frmVisualABStep3').rememberState({objName: 'save_obj_ref'});
            $('#frmVisualABStep3').rememberState("save");
            $('#frmVisualABStep3').data('stored', true);
        } else {
            $('#frmVisualABStep3').rememberState("restoreState");
            bt_urlpattern_config.restoreOriginalState();
        }
            
        $("#vablpname").val("<?= $control_url ?>");
           setTimeout(function() {
           $('html').animate({scrollTop: 0}, 50, function() {
               BTTestType = 'visual';
               SetInitialValues();
               InitTrackingCodes();
               CreateVisualAB(3)
           })
       }, 50);
    }
    
    function OpenGoals(){
            OpenPopup("#vab2_4");
    };

   var VariantsData = <?= $variantsdata ?>;
   var VariantsPerso = <?= $persolp ?>;
</script>

<div style="display:none;">  

    <div id="restartCollection" class="confirmation-user confirmation smallpopup">
        <h1><?php echo $this->lang->line('restart_headline'); ?></h1>
        <div><?php echo $this->lang->line('restart_subline'); ?></div>

        <div class="ctrl-buttons <?php if ($tenant == 'etracker') { ?>etracker-float-right <?php } ?>"> 
            <div class="links">
                <a class="editor_back" onclick="$.fancybox.close()" href="javascript:void(0)"><?php echo $this->lang->line('button_cancel'); ?></a>
            </div>
            <input type="submit" id="confirm_restart" value="<?= $this->lang->line('button_restart'); ?>" class="button ok">
        </div>
    </div>

</div>