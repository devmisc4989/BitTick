<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('collectionoverview');
//echo $this->config->item('language');
//echo $this->lang->line('headline');die();
$this->lang->load('editor');
$this->lang->load('personalization');
$this->lang->load('welcome');
$tenant = $this->config->item('tenant');

// dispatch between the full dashboard and the welcome-mode (if client has no tests yet)
if(!isset($no_tests))
    $no_tests = false;
else
    $no_tests = true;

// check validated email
if ($email_validated != CLIENT_EMAIL_VALIDATED) {
    $email_errmsg = sprintf($this->lang->line('error_notvalidated'), $basesslurl . "users/gup/" . $clientid . "/");
    $email_haserror = TRUE;
} else {
    $email_haserror = FALSE;
}

// check quota exceeded
if($quota <= $used_quota) {
    $quota_haserror = true;
    if($status==CLIENT_STATUS_ACTIVE) {
        $quota_errmsg = sprintf($this->lang->line('error_quotaexceeded'),$quota,$basesslurl . "users/gup/" . $clientid . "/");
    }
    else {
        $quota_errmsg = sprintf($this->lang->line('error_monthlyquotaexceeded'),$quota,$basesslurl . "users/gup/" . $clientid . "/");
    }
}
else {
    $quota_haserror = false;
}

// check test phase exceeded
if ($status == CLIENT_STATUS_ACTIVE) {
    $created = strtotime($createddate);
    $diff = ceil(($created + 2592000 - time()) / 86400);
    $diff = ($diff < 0) ? 0 : $diff;

    $plans = $this->config->item('PLAN');
    $orderurl = $basesslurl . "users/order/";
    if ($diff > 0) {
        $plan_errmsg = sprintf($this->lang->line('error_timetotest'), $diff, $orderurl);
    } else {
        $plan_errmsg = sprintf($this->lang->line('error_testexceeded'),$orderurl);
    }
    // do not show the notification in the first few days
    if($diff < 28)
        $plan_haserror = TRUE;
    else 
        $plan_haserror = FALSE;
} else {
    $plan_haserror = FALSE;
}
?>

<?php if($smsLevel === 'available'): ?>
<div id="full_editor_overlay"></div>
<div id="sms_ui">
    <iframe id="sms_ui_frame" src="<?php echo $basesslurl ?>editor/sms_dash" ></iframe>
</div>
<?php endif ?>

<div id="main_container">
    <div class="whitebox" id="scrollToHere">
        <?php
        if (($email_haserror || $plan_haserror || $quota_haserror) && ($this->config->item('tenant') == 'blacktri')) {
            ?>
            <div class="notification">
                <ul>
                    <?php
                    if ($email_haserror) {
                        ?>
                        <li class="icon_1"><?php echo $email_errmsg; ?></li>
                        <?php
                    }
                    if ($plan_haserror) {
                        ?>
                        <li class="icon_2"><?php echo $plan_errmsg; ?></li>
                        <?php
                    }
                    if ($quota_haserror) {
                        ?>
                        <li class="icon_2"><?php echo $quota_errmsg; ?></li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
        <?php
        }

        // show headline-block or welcome-block depending if tests present or not
        if(!$no_tests) {
        ?>
        <div class="head_line_container">
            <div class="title"><div class="head_line_title"><?php echo $this->lang->line('headline'); ?></div>		
                <div class="headline_button"><input type="submit" class="button new-collection" value="<?php echo $this->lang->line('button_createcollection'); ?>"
                                                    href="javascript:void(0)" class="popup" onclick="CreateVisualAB(0)" /></div>
            </div>
            <div class="head_line_context"><?php echo $this->lang->line('description'); ?> <?php echo splink('collectionoverview'); ?></div>
            <div class="error-message"><?php if ($this->uri->segment(3) == 1) echo 'collectionid not found.please try again'; ?></div>
        </div>
        <?php
        }
        if($no_tests && $tenant=='blacktri') {
        ?>
        <div class="welcome-container">
            <h2><?php printf($this->lang->line('welcome_salutation'), $firstname); ?></h2>
            <div class="get-started">
                <?php echo $this->lang->line('welcome_getstarted') ?> 
                <a class="popup" onclick="CreateVisualAB(0)" href="javascript:void(0)"><?php echo $this->lang->line('welcome_getstartedlink') ?></a>

                <p><?php echo $this->lang->line('welcome_getstartedpara') ?> <a href="<?php echo $this->lang->line('url_helpsupport') ?>" target="_new"><?php echo $this->lang->line('welcome_userdoc_text') ?></a></p>
            </div>

        </div>
        <?php 
        }
        ?>

        <!--	script for tooltip		-->
        <script type="text/javascript">
            $(document).ready(function() {
                
                bt_teasertest_translations = <?= json_encode($this->lang->line('tt config layer')) ?>;
                /**
                 * If the user has not enabled ab testing, split testing or SMS, when 
                 * he clicks on one of those  divs in dashboard, he is redirected to 
                 * the "upgrade" page (by etracker)
                 * It appends a link ( <a> ) to the body to add IE compatibility.
                 */
                var disabledAB = ($('#current_testing_level').val() === 'disabled');
                var disabledSms = ($('#current_sms_level').val() === 'disabled');
                if((disabledAB || disabledSms) && $('.etracker.confirmation-field').length > 0){
                    var redirection = $('#etracker_product_upgrade').val();
                    var htm = '<a href="' + redirection + '" class="hidden-link" id="upgrade-test-link" target="_blank">upgrade</a>';
                    $('body').append(htm);
                    
                    var $div = (disabledAB) ? 
                        $('.etracker.confirmation-field#vab *, .etracker.confirmation-field#ab1 *') : 
                            $('.etracker.confirmation-field#sms1 *');
                    
                    $div.not('div.ctrl-buttons').on('click', function(event){
                        event.stopPropagation();
                        $('a#upgrade-test-link')[0].click();
                    });
                }

                // if there are 2+ rows, add the sorting plugin
                if (parseInt(<?php echo count($collections); ?>) > 2) {
                    $('table').stupidtable();
                } else {
                    $('.table td[data-sort]').css('cursor', 'default');
                }

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
            });
            var path = "<?php echo $basesslurl ?>";
        </script>
        <!--	tooltip script ends here		-->
        <!-- end popup box	-->
        <div id="test">
        <?php 
            // do not show table if client has no tests
            if(!$no_tests) {
        ?>
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
                <tr class="table-title">
                    <td class="first" data-sort="string"><?php echo $this->lang->line('table_name'); ?></td>
                    <td class="conversion_rate"><div class="relative"><div class="absolute"><?php echo $this->lang->line('table_visitors_conversions_short'); ?></div></div></td>
                    <td class="conversion_rate"><div class="relative"><div class="absolute" style="left:-20px;"><?php echo $this->lang->line('table_cr_short'); ?></div></div></td>
                    <td class="test_results <?php if ($tenant == 'etracker') { ?>etracker_overview_border_right<?php } ?>">
                        <?php echo $this->lang->line('table_result'); ?>
                    </td>
                    <td style="width:30px; text-align:center;" <?php if ($tenant == 'etracker') { ?>class="etracker_overview_border_right"<?php } ?>>
                        <?php echo $this->lang->line('action_status'); ?>
                    </td>
                    <td class="lpc_list_warning" <?php if ($tenant == 'etracker') { ?> class="etracker_overview_border_right"<?php } ?>></td>
                    <td class="last"><?php echo $this->lang->line('table_nextstep'); ?></td>
                </tr>
                <?php
                foreach ($collections as $row) {
                        $warningIcon = $row['hasConflicts'] ? '<a href="javascript:void(0);" class="lpc_conflict_warning">&nbsp;</a>' : '';
                        $href = $basesslurl . 'lpc/lcd/' . $row['collectionid'] . "/" . $clientid . "/";
                        if ($row['testtype'] == OPT_TESTTYPE_TEASER) {
                            $href = $basesslurl . 'lpc/tto/' . $row['collectionid'];
                        }
                        ?>
                    <tr id="<?php echo $row['collectionid']; ?>" class="table-list" >
                        <td id="<?= $row['name'] ?>" class="td_project_name">
                            <a class="collection_link" 
                               id="<?php echo 'test-' . $row['collectionid']; ?>" 
                               href="<?= $href ?>">
                                   <?php echo $row['name']; 
                                    if($row['smartmessage'] == 1) {
                                        $ttype = $this->lang->line('testtype_sms');                                        
                                    }
                                    else {
                                        if ($row['testtype'] == OPT_TESTTYPE_SPLIT) {
                                            $ttype = $this->lang->line('testtype_split');
                                        }
                                        if ($row['testtype'] == OPT_TESTTYPE_VISUALAB) {
                                            $ttype = $this->lang->line('testtype_visual');
                                        }
                                        if ($row['testtype'] == OPT_TESTTYPE_TEASER) {
                                            $ttype = $this->lang->line('testtype_teaser');
                                        }
                                        if ($row['testtype'] == OPT_TESTTYPE_MULTIPAGE) {
                                            $ttype = $this->lang->line('testtype_multipage');
                                        }
                                    }
                                   ?>
                            </a>
                            <br>
                            <span class="test-type"><?= $ttype ?></span>                            
                            <div class="popup-textinfo">
                                <a href="javascript://" title="<?= $row["lp_url"] ?>">
                                    <?= trim_link($row["lp_url"], ($tenant == 'etracker' ? 65 : 45)) ?>
                                 </a>
                            </div>
                        </td>
                        
                        <td class="td_participants">
                        <?php if ($row['testtype'] != OPT_TESTTYPE_TEASER) {
                            echo $row['visitorcount'] . '/' . $row['conversioncount'];
                        } ?>
                        </td>
                        <td>
                        <?php if ($row['testtype'] != OPT_TESTTYPE_TEASER) {
                            echo $row['cr'];
                        } ?>
                        </td>
                        <td>
                        <?php if ($row['testtype'] != OPT_TESTTYPE_TEASER) {
                            echo $row['result'];
                        } ?>
                        </td>
                        
                        <td style="text-align:right;">
                            <?php
                            if ($row['status'] == OPT_PAGESTATUS_ACTIVE) {
                                ?>
                                <img src="<?php echo $basesslurl ?>/images/collection_running.png" alt="<?php echo $this->lang->line('tooltip_running'); ?>" title="<?php echo $this->lang->line('tooltip_running'); ?>"/>
                                <?php
                            } elseif (($row['status'] == OPT_PAGESTATUS_PAUSED) || $row['status'] == OPT_PAGESTATUS_UNVERIFIED) {
                                ?>
                                <img src="<?php echo $basesslurl ?>/images/collection_paused.png" alt="<?php echo $this->lang->line('tooltip_paused'); ?>" title="<?php echo $this->lang->line('tooltip_paused'); ?>"/>
                                <?php
                            }
                            ?>
                        </td>
                        <td class="lpc_list_warning"><?= $warningIcon ?></td>
                        <td class="last_td">
                            <div class="action_trigger">
                                <a href="javascript:void(0)" class="action_title"><?php echo $this->lang->line('table_action'); ?></a>
                                <div class="action_menu">
                                    <div class="top"></div>
                                    <div class="middle">
                                        <?php if ($row['status'] == OPT_PAGESTATUS_ACTIVE) {
                                            ?>
                                            <a href="#" onclick="togglecollection(<?php echo $row['collectionid'] . "," . $clientid; ?>, 0, 'lpc/cs')"><?php echo $this->lang->line('action_pause'); ?></a>
                                            <?php
                                        } elseif (($row['status'] == OPT_PAGESTATUS_PAUSED) || ($row['status'] == OPT_PAGESTATUS_UNVERIFIED)) {
                                            if ($row['visitorcount'] == 0)
                                                $startlabel = $this->lang->line('action_start');
                                            else
                                                $startlabel = $this->lang->line('action_play');
                                            ?>
                                            <a class="btn_start_continue" href="#" onclick="togglecollection(<?php echo $row['collectionid'] . "," . $clientid; ?>, 1, 'lpc/cs')">
                                                <?php echo $startlabel; ?>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                            
                                        <a href="<?= $href ?>"><?php echo $this->lang->line('action_show_details'); ?></a>
                                        
                                        <?php if ($row['testtype'] != OPT_TESTTYPE_TEASER) { ?>
                                            <a class="collectionDuplicate" clientid="<?php echo $clientid; ?>" id="<?php echo 'tid-' . $row['collectionid']; ?>"><?php echo $this->lang->line('Duplicate_Test'); ?></a>
                                        <?php } ?>
                                            
                                        <a class="collectionDelete" collectionid="<?php echo $row['collectionid']; ?>" clientid="<?php echo $clientid; ?>" ><?php echo $this->lang->line('action_delete'); ?></a>
                                    </div>
                                    <div class="bottom"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                <tr><td colspan="100" class="last">&nbsp;</td></tr>
            </table>
        <?php 
            } // end: do not show table if client has no tests

            if($no_tests && $tenant=='etracker') {
        ?>
                <!-- Etracker message --> 
                <div class="et-no-data-box"><div class="et-no-data-box-inner">Für den ausgewählten Zeitraum sind leider keine Daten vorhanden.</div></div>
        <?php 
            }
        ?>



            <input type="hidden" id="path" value="<?php echo $basesslurl ?>"/>
        </div>
    </div>
</div>
<!--	script for add element dynamically		-->
<script type="text/javascript">
        var num = 1;
        var variantid = '1_';
        $(document).ready(function() {
            //action mouse over
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
            $('#btnAdd').click(function() {
                variantid = $("#variantpagehid").val();
                var len = $('.clonedInput').length;
                if (variantid == '')
                {
                    document.getElementById("showdiv").innerHTML = '<div id="input1" style="margin-bottom:4px;" class="clonedInput"><input type="text" class="textbox"  name="1" id="variantpagename1" size="35" title="Enter URL of landing page variation"/><input type="button" class="lp-button lp-delete" onclick="return check(1);" id="btnDel1"/></div>';
                }
                if (len == 1)
                {
                    var vid = variantid.split("_");
                    //var len = fullURL.length;

                    num = new Number(vid[0]);
                }
                newNum = new Number(num + 1);		// the numeric ID of the new input field being added

                var newElem = $('<div id="input' + newNum + '" style="margin-bottom:4px;" class="clonedInput"><input type="text" id="variantpagename' + newNum + '" name="' + newNum + '" class="textbox"/>' + '<input type="button" class="lp-button lp-delete" onclick="return check(' + newNum + ');" /></div>');
                newElem.children(':first');
                $('#input' + num).after(newElem);
                variantid = variantid + newNum + "_";
                document.frmLandingPage.variantpagehid.value = variantid;
//					var newBut  = $('<input type="button" class="lp-button lp-delete" onclick="return check('+newNum+');" />').clone().attr('id', 'btnDel' + newNum);
//					newBut.children(':first').attr('id', 'name' + newNum).attr('name', 'name' + newNum);
//					$('#input' + num).after(newBut);
                num = newNum;
            });

            // catches the click event on the Duplicate Test link
            $('.collectionDuplicate').click(function() {
                openDuplicatePupup($(this).attr('clientid'), $(this).attr('id'));
            });
        });
        function check(numval)
        {
            $('#input' + numval).remove();
            $('#btnDel' + numval).remove();
            variantid = document.frmLandingPage.variantpagehid.value;
            document.frmLandingPage.variantpagehid.value = variantid.replace(numval + '_', '');

            variantid = $("#variantpagehid").val();
            var arry = new Array();
            arry = variantid.split("_");
            largest = arry.sort().reverse();
            num = new Number(largest[0]);
        }


        function Log()
        {
            if (window.console && EnableLog)
            {
                var args = arguments;
                var out = [];
                for (var i = 0; i < args.length; i++)
                    out.push('args[' + i + ']');
                eval('window.console.log(' + out.join(',') + ')');
            }
        }
</script>

<!--POPUP for Delete-->
<div style="display:none;">
    <div class="confirmation-user confirmation smallpopup" id="deleteConfirm">
        <input type="hidden" name="deleteid" id="deleteid" />
        <h1><?php echo $this->lang->line('deletecollection_headline'); ?></h1>
        <div><?php echo $this->lang->line('deletecollection_subline'); ?></div>

        <div class="ctrl-buttons <?php
// special styles for etracker 
        if ($tenant == 'etracker') {
            ?>
                 etracker-float-right
             <?php } ?>"> 
            <div class="links">
                <a class="editor_back" onclick="ClosePopup()" href="javascript:void(0)"><?php echo $this->lang->line('button_cancel'); ?></a>
            </div>
            <input type="submit" onclick="deletecollection($('#deleteid').val(),<?php echo $clientid; ?>)" value="<?php echo $this->lang->line('button_delete'); ?>" class="button ok">
        </div>                                
    </div>
</div>
<!--END POPUP-->

<!-- confirmation code here -->
<div style="display:none;">
    



    <!-- 
    /*********************************************************************************************/
    ******************************* DUPLICATE TEST START *******************************/
    /*********************************************************************************************/
    -->
    <div class="confirmation confirmation-user" id="popDuplicateTest">
        <input type="hidden" id="duplicate-copyof" value="<?php echo $this->lang->line('Duplicate_Copyof'); ?>" />
        <h1><?php echo $this->lang->line('Duplicate_Title'); ?></h1>

        <form id="frmDuplicateTest" method="post">

            <div class="confirmation-field w100">

                <div><?php echo $this->lang->line('Duplicate_Info'); ?> <br /><br /></div>
                <div style="clear:both"></div>

                <label <?php if ($tenant == 'etracker') { ?> class="label-step-3-4"<?php } ?>  >    
                    <?php
                    echo $this->lang->line('Choose a name for your test');
                    if ($tenant == 'etracker') {
                        ?>
                        <div class="orange-star-3-4">*</div>
                    <?php } ?>
                </label>

                <input type="text"  maxlength="100" class="textbox validate[required]" id="duplicate-name" value=""/>

                <div style="clear:both"></div>

                <?php if ($tenant == 'etracker') { ?>
                    <div class="links-3-4">
                    <?php } ?>  
                    <div class="ctrl-buttons">
                        <div class="links">
                            <a class="editor_back" onclick="$.fancybox.close();" href="#">
                                <?php echo $this->lang->line('button_cancel'); ?>
                            </a>
                        </div>
                        <input class="button ok" type="submit" value="<?php echo $this->lang->line('Duplicate_Test'); ?>">
                    </div>
                    <?php if ($tenant == 'etracker') { ?>
                    </div>
                <?php } ?>  
            </div>	
        </form>
    </div>

    <div class="confirmation confirmation-user" id="popDuplicateError">
        <h1><?php echo $this->lang->line('Duplicate_Error_Title'); ?></h1>
        <div><?php echo $this->lang->line('Duplicate_Error_Content'); ?> <br /><br /></div>

        <div style="clear:both"></div>

        <?php if ($tenant == 'etracker') { ?>
            <div class="links-3-4">
            <?php } ?>  
            <div class="ctrl-buttons">
                <div class="links">
                    <a class="editor_back" onclick="$.fancybox.close();" href="#">
                        <?php echo $this->lang->line('Click here to close'); ?>
                    </a>
                </div>
            </div>
            <?php if ($tenant == 'etracker') { ?>
            </div>
        <?php } ?>  
    </div>

    <!-- Shows the "please wait" message  -->
    <div class="confirmation confirmation-user" id="popDuplicateWait">
        <h1><?php echo $this->lang->line('Duplicate_Wait_Title'); ?></h1>

        <div class="headline">
            <?php echo $this->lang->line('Duplicate_Wait_Content'); ?>
        </div>

        <div style="clear:both"></div>

        <?php
        if ($tenant == 'etracker') {
            echo '<img class="loading-img etracker" src="' . $basesslurl . '/images/etracker/in_progress_dots.gif" alt="" /> ';
        } else {
            echo '<img class="loading-img" src="' . $basesslurl . '/images/preloader.gif" alt="" /> ';
        }
        ?>  
    </div>

    <!--  /*********************************************************************************************/ -->

</div>