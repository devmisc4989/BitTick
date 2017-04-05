<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('used_quota');
$this->lang->load('collectionoverview');

?>

<div id="main_container">

    <div class="whitebox">

        <div class="head_line_container">
            <div class="title">
                <div class="head_line_title"><?= $this->lang->line('used_quota_title'); ?></div>
            </div>
            <div class="head_line_context">
                <?php echo $this->lang->line('used_quota_intro'); ?>
            </div>

            <?php
            foreach($usageData as $entry) {
                if(sizeof($entry['usage']) == 0)
                    $showTable = false;
                else 
                    $showTable = true;
                $blockHeadline = $this->lang->line('used_quota_month') . 
                    $entry['month'] . " / " . $entry['year'];
                $sublineAbTestSum = $this->lang->line('used_quota_sum_ab') . "<b>".$entry['monthly_usage_abtest']."</b>";
                $sublineTeaserTestSum = $this->lang->line('used_quota_sum_teaser') . "<b>".$entry['monthly_usage_teasertest']."</b>";                  
            ?>
            <div class="table_line">
                <div class="table_title"><b><?php echo $blockHeadline; ?></b></div>
                <?php 
                if($showTable) {
                ?>
                <div><?php echo $sublineAbTestSum . "<br>" . $sublineTeaserTestSum; ?></div>
                <table class="table table60">
                    <tr class="table-title">
                        <td style="width:220px;"><?= $this->lang->line('used_quota_teablehead_project'); ?></td>
                        <td><?= $this->lang->line('used_quota_teablehead_type'); ?></td>
                        <td class="table-last" style="width:120px;"><?= $this->lang->line('used_quota_teablehead_uv'); ?></td>
                    </tr>
                    <?php
                    foreach($entry['usage'] as $projectUsage) {
                        switch($projectUsage['testtype']) {
                            case 'VISUAL':
                                $testtype = $this->lang->line('testtype_visual');
                                break;
                            case 'SPLIT':
                                $testtype = $this->lang->line('testtype_split');
                                break;
                            case 'TEASERTEST':
                                $testtype = $this->lang->line('testtype_teaser');
                                break;
                            case 'MULTIPAGE':
                                $testtype = $this->lang->line('testtype_visual');
                                break;
                            default:
                                $testtype = "";
                                break;
                        }
                    ?>
                    <tr class="table-row">
                        <td><?= $projectUsage['name']; ?></td>
                        <td><?= $testtype; ?></td>
                        <td class="table-last"><?= $projectUsage['usage']; ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </table>
                <?php 
                }
                else {
                ?>
                <div><?php echo $this->lang->line('used_quota_no_data'); ?></div>
                <?php 
                }
                ?>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>