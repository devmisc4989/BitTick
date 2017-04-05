<?php 
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
?>
<!--
/*********************************************************************************************/
******************************* DIAGNOSE MODE START ******************************/
/*********************************************************************************************/
-->

<div class="confirmation confirmation-user" id="step-enter-url">
    <h1><?php echo $this->lang->line('Debug_Test'); ?></h1>

    <div class="headline">
        <?php
        echo $this->lang->line('Diagnose_Mode_Headline1');
        ?>
    </div>

    <form method="post" id="frm-test-diagnose" action="#">

        <?php if ($tenant == 'etracker') { ?>
            <label class="label-step-3-4">
                <?php echo $this->lang->line('URL_Of_Page'); ?> <div class="orange-star-3-4">*</div>
            </label>
        <?php } ?>
        <input id="diagnose-page-url" class="textbox validate[required]" type="text" value="<?php echo $control_url ?>" style="width:400px" maxlength="200">
        <div style="clear:both"></div>
        <div class="popup-textinfo
                 <?php if ($tenant == 'etracker') { ?>popup-textinfo-3-4 <?php } ?>" >
            <?php echo $this->lang->line('Link Example'); ?>
        </div>

        <div style="clear:both"></div>

        <?php if ($tenant == 'etracker') { ?>
        <div class="links-3-4">
            <?php } ?>
            <div class="ctrl-buttons">
                <div class="links">
                    <a class="editor_back close-btn" href="#"><?php echo $this->lang->line('Back to details'); ?></a>
                </div>
                <input class="button ok" type="submit" value="<?php echo $this->lang->line('Start_Diagnose'); ?>">
            </div>
            <?php if ($tenant == 'etracker') { ?>
        </div>
    <?php } ?>
    </form>
</div>

<!-- After getting the tracecode this shows a link  -->
<div class="confirmation confirmation-user" id="step-tracecode-created">
    <h1><?php echo $this->lang->line('Start_Diagnose'); ?></h1>

    <div class="headline">
        <?php
        echo $this->lang->line('Diagnose_Mode_Headline3');
        ?>
    </div>

    <div style="clear:both"></div>

    <form method="post" id="frm-test-tracecode" action="#">
        <?php if ($tenant == 'etracker') { ?>
        <div class="links-3-4">
            <?php } ?>
            <div class="ctrl-buttons">
                <div class="links">
                    <a class="editor_back back-btn" href="#"><?php echo $this->lang->line('URL_Of_Page'); ?></a>
                </div>
                <a href="" target="__blank" class=" link-test" id="popup-loader">
                    <?php echo $this->lang->line('Test_Now'); ?>
                </a>
            </div>
            <?php if ($tenant == 'etracker') { ?>
        </div>
    <?php } ?>
    </form>
</div>

<!-- STEP RESULT SIMPLE -->
<div class="confirmation-user confirmation" id="step-result-simple">
    <h1><?php echo $this->lang->line('Diagnose_Result'); ?></h1>

    <div class="headline issue-depend" id="issue-1">
        <strong><?php echo $this->lang->line('Head_Issue1'); ?><br /></strong>
        <span class="status-depend" id="status-1"><?php echo $this->lang->line('Copy_Status1'); ?></span>
        <span class="status-depend" id="status-6"><?php echo $this->lang->line('Copy_Status6'); ?></span>
    </div>

    <div class="headline issue-depend" id="issue-2">
        <strong><?php echo $this->lang->line('Head_Issue2'); ?><br /></strong>
        <?php echo $this->lang->line('Copy_Issue2'); ?>
    </div>

    <div class="headline issue-depend" id="issue-5">
        <strong><?php echo $this->lang->line('Head_Issue5'); ?><br /></strong>
        <?php echo $this->lang->line('Copy_Issue5'); ?>
    </div>

    <div class="headline issue-depend" id="issue-6">
        <strong><?php echo $this->lang->line('Head_Issue6'); ?><br /></strong>
        <?php echo $this->lang->line('Copy_Issue6'); ?>
    </div>

    <div style="clear:both"></div>

    <?php if ($tenant == 'etracker') { ?>
    <div class="links-3-4">
        <?php } ?>
        <div class="ctrl-buttons">
            <div class="links">
                <a class="editor_back back-btn" href="#"><?php echo $this->lang->line('Start_Diagnose'); ?></a>
            </div>
            <input class="button ok close-btn" type="submit"  value="<?php echo $this->lang->line('Back to details'); ?>" />
        </div>
        <?php if ($tenant == 'etracker') { ?>
    </div>
<?php } ?>

</div>

<!-- STEP RESULT MATCH -->
<div class="confirmation-user confirmation" id="step-result-match">
    <h1><?php echo $this->lang->line('Diagnose_Result'); ?></h1>

    <input type="hidden" id="table-testname" value="<?php echo $this->lang->line('Table_Testname'); ?>" />
    <input type="hidden" id="table-testpage" value="<?php echo $this->lang->line('Table_Testpage'); ?>" />
    <input type="hidden" id="table-result" value="<?php echo $this->lang->line('Table_Result'); ?>" />
    <input type="hidden" id="table-match" value="<?php echo $this->lang->line('Table_Match'); ?>" />
    <input type="hidden" id="table-match-conflict" value="<?php echo $this->lang->line('Table_Match_Conflict'); ?>" />
    <input type="hidden" id="table-nomatch" value="<?php echo $this->lang->line('Table_Nomatch'); ?>" />
    <input type="hidden" id="table-conflict-sms" value="<?php echo $this->lang->line('Table_Conflict_Sms'); ?>" />
    <input type="hidden" id="table-conflict-split" value="<?php echo $this->lang->line('Table_Conflict_Split'); ?>" />
    <input type="hidden" id="table-nomatch" value="<?php echo $this->lang->line('Table_Nomatch'); ?>" />

    <div class="headline etpage-depend"><?php echo $this->lang->line('Match_Intro_Etpagename'); ?></div>
    <div class="headline url-depend"><?php echo $this->lang->line('Match_Intro'); ?></div>
    <div style="clear:both; margin-bottom: 8px;"></div>

    <div class="headline">
        <strong><?php echo $this->lang->line('Page_Url_Title'); ?><br /></strong>
        <span class="page-url-content"></span>
        <div style="clear:both; margin-bottom: 8px;"></div>
    </div>

    <div class="headline etpage-depend">
        <strong><?php echo $this->lang->line('Et_Pagename_Title'); ?><br /></strong>
        <span class="et-pagename-content"></span>
        <div style="clear:both; margin-bottom: 8px;"></div>
    </div>

    <div class="headline matching-depend">
        <strong><?php echo $this->lang->line('Checked_Test_Title'); ?><br /></strong>
        <div id="checked-test-table">
            <!-- Table goes in the document BODY -->
            <table id="checked-table" class="wizard-table"></table>
        </div>
        <div style="clear:both; margin-bottom: 8px;"></div>
    </div>

    <div class="headline match-depend" id="match-0">
        <strong><?php echo $this->lang->line('Head_Match0'); ?><br /></strong>
    </div>
    <div class="headline match-depend" id="match-1">
        <strong><?php echo $this->lang->line('Head_Match1'); ?><br /></strong>
    </div>
    <div class="headline match-depend" id="match-2">
        <strong><?php echo $this->lang->line('Head_Match2'); ?><br /></strong>
    </div>

    <div class="delivery-depend" id="delivery-0">
        <?php echo sprintf($this->lang->line('Copy_Delivery0'),$collectionname); ?>
    </div>
    <div class="delivery-depend" id="delivery-1">
        <?php echo sprintf($this->lang->line('Copy_Delivery1'),$collectionname); ?>
    </div>
    <div class="delivery-depend" id="delivery-2">
        <?php echo sprintf($this->lang->line('Copy_Delivery2'),$collectionname); ?>
    </div>

    <div style="clear:both"></div>

    <?php if ($tenant == 'etracker') { ?>
    <div class="links-3-4">
        <?php } ?>
        <div class="ctrl-buttons">
            <div class="links">
                <a class="editor_back back-btn" href="#"><?php echo $this->lang->line('Start_Diagnose'); ?></a>
            </div>
            <input class="button ok close-btn" type="submit" value="<?php echo $this->lang->line('Back to details'); ?>" />
        </div>
        <?php if ($tenant == 'etracker') { ?>
    </div>
<?php } ?>

</div>

<!-- Shows the "please wait" message  -->
<div class="confirmation confirmation-user" id="step-diagnose-wait">
    <h1><?php echo $this->lang->line('Please_Wait'); ?></h1>

    <div class="headline">
        <?php echo $this->lang->line('Please_Wait_Message'); ?>
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

<!--
/*********************************************************************************************/
********************************** DIAGNOSE MODE END **********************************/
/*********************************************************************************************/
-->
