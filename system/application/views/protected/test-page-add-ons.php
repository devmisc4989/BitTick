<?php
/***************************************************************************************
 *  Consolidation of the old editor_wizard_edit to remove old fancybox editor components
 ***************************************************************************************/

$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('editor');
$this->lang->load('personalization');
$available_goals = $this->config->item('available_goals');
$tenant = $this->config->item('tenant');
$ttype = $testtype == OPT_TESTTYPE_SPLIT ? 'split' : 'visual';

$scripts = array(
    'js/jtable/jquery-ui.js',
    "js/jquery.ba-postmessage.js",
    /* "js/dash_testpage_wizards/fancybox-editor/main.js", */
    "js/dash_testpage_wizards/BT-editor-common-functions.js",
    "js/dash_testpage_wizards/wizard_edit-only.js",
    "js/BT-common/BT-urlpattern.js",
    'js/BT-common/BT-allocation.js',
    "js/BT-common/BT-clickgoals.js",
    "js/BT-common/BT-additionalconfig.js",
    "js/conflict_layer.js",
);

/* part of refactoring to move old fancybox editor to separate view files*/
$popup_data=array(
    'baseurl'=>$baseurl,
    'basesslurl' => $basesslurl,
    'available_goals' => $available_goals,
    'tenant' => $tenant,
    'landingpages' => $landingpages,
    'control_url' => $control_url,
    'collectionid' => $collectionid,
    'controlpageurl' => $controlpageurl,
    'control_pattern' => $control_pattern,
    'conflictLayerLang' => $this->lang->line('conflict layer popup'),
);
$popup_view_path='client-testpage-popups/';
$popupViews=array(
    /* Visual AB */
    array('file'=>'id-vab2_2-fbox-editor-wrap.php', 'deprecated'=>FALSE),
    array('file'=>'id-vab2_3-set-visual-url.php', 'deprecated'=>FALSE),
    array('file'=>'id-vab2_4-set-goals.php', 'deprecated'=>FALSE),
    array('file'=>'goal_details.php', 'deprecated'=>FALSE),
    array('file'=>'goal_reactivate.php', 'deprecated'=>FALSE),
    array('file'=>'allocation.php', 'deprecated'=>FALSE),
    /* diagnose */
    array('file'=>'diagnose_test-multiple_steps.php', 'deprecated'=>FALSE),
    array('file'=>'dashboard_project_conflict.php', 'deprecated'=>FALSE),
    /* split tests */
    array('file'=>'id-ab2_1-split_test_step_1.php', 'deprecated'=>FALSE),
    array('file'=>'DEPRECATED_id-ab2_2-split_test_step_2.php', 'deprecated'=>TRUE)

);
?>
<!-- edit wizard steps here -->

<!-- Visual AB Test -->
<div style="display:none;">
    <input type="text" name="vablpname" id="vablpname" value="<?= $control_url ?>" placeholder="http://"/>

    <?php
    /*   Load all the popup files */
    foreach ($popupViews as $popup){
        $this->load->view($popup_view_path.$popup['file'], $popup_data);
    }
    ?>

</div>

<script type="text/javascript">
    /* these vars all used in BT_editor_common-functions.js*/
    /* todo organize into object to remove from global space*/
    var newNum;
    var num =<?php echo count($landingpages) ?>;
    var variantid = $("#variantpagehid").val();
    var oldvariantid = $("#variantpagehidold").val();
    var BlackTriMaxCombinations = <?= $this->config->item('mvt_max_combinations') ?>;
    var EmptyNamePH = '<?= $this->lang->line('Please enter the name here'); ?>';
    var NewVariant = '<?= $this->lang->line('Variant label'); ?>';
    var EnableLog = true;
    var DisablePopupNotice = false;
    var BTIsEditable = true;
    var BTCurrentTab = 0;
    var BTRenamingIndex = 0;
    var BTVariantsData = <?= $variantsdata ?>;
    var BTTrackingCodeData = <?= $trackingcodedata ?>;
    var BTCurrentGoals = <?= json_encode($tracked_goals) ?>;
    var BTCurrentApproach = <?= $tracking_approach; ?>;
    var BTMenuWidth = 150;
    var BTTestType = '<?= $ttype ?>';
    var BTTenant = '<?= $tenant ?>';
    /* new vars to allow moving js to separate file*/
    var Is_etracker =<?php echo $tenant == "etracker" ? "true" : "false"; ?>;
    
    var BTeditorVars = {
        view: 'edit',
        isTT: '<?= (int) $isTT ?>',
        isMpt: '<?= $isMpt ?>' === 'true' || parseInt('<?= $isMpt ?>') === 1 ? true : false,
        FrameEditorBaseUrl: "<?php echo $this->config->item('editor_url'); ?>?blacktriurl=",
        DocDomain: '<?= $editorurl = $this->config->item('document_domain') ?>',
        conversionGoalsParams: <?php echo $conversionGoalsParams ?>,
        ClientId: <?= $clientid ?>,
        BaseSslUrl: "<?php echo $basesslurl ?>",
        goalsData: <?php echo json_encode($collectionGoals); ?>,
        CollectionId: <?php echo $collectionid ?>,
        ctrlAllocation: parseFloat('<?=$ctrlAllocation ?>'),
        GroupId: <?php echo $groupid ?>,
        enterNameText: "<?php echo $this->lang->line('Please enter the name here'); ?>"
    }

</script>
<?php foreach($scripts as $script):?>
<script type="text/javascript" src="<?php echo $basesslurl.$script ?>"></script>

<?php endforeach ?>

