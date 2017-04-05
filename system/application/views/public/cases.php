<?php
$baseurl = $this->config->item('base_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
$targeturl = "/de/erfolgreiche-ab-tests";

$this->lang->load('cases');

/* helper function to render HTML from the arrays passed in
 * $fc: array with filters passed to this view
 * $key: key for the filter to use
 * $label: Label for this checkbox
 */

function renderLinkFilter($fc, $key, $label) {
    $out = "";
    $fcount = $fc[$key]['facetcount'];
    $group = substr($key, 0, 2);
    $url = $targeturl . "?action=flt&filter=$key&grp=$group";
    if ($fc[$key]['visibility'] == "visible") {
        // if filter is marked as visible, render it as link
        $out .= "<a href=\"$url\">$label</a> ($fcount)";
    } else {
        // if filter is marked as hidden, render nothing
    }

    return $out;
}

function renderLinkFilterRemove($more, $key, $label) {
    $group = substr($key, 0, 2);
    $hasMore = $more[$group];
    $out = "";
    // render a "More" link only, when clicking on it leads to more result
    if ($hasMore) {
        $url = $targeturl . "?action=flt&filter=$key&grp=$group";
        $out = "<a href=\"$url\">$label</a>";
    }
    return $out;
}
?>

<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('cases_head'); ?></h2>
    </div>
</div>
<div id="main_container">
    <div class="terms">
        <?php echo $this->lang->line('cases_copy'); ?>

        <br>Project Type<br>
        <?php echo renderLinkFilter($filter, 'pt_lp', 'Landingpage'); ?>
        <?php echo renderLinkFilter($filter, 'pt_op', 'Order Process'); ?>
        <?php echo renderLinkFilter($filter, 'pt_ws', 'Website'); ?>
        <?php echo renderLinkFilterRemove($moreresults, 'pt_all', '...Mehr'); ?>

        <br><br>Conversion Goal<br>
        <?php echo renderLinkFilter($filter, 'cg_o', 'Order'); ?>
        <?php echo renderLinkFilter($filter, 'cg_l', 'Lead'); ?>
        <?php echo renderLinkFilter($filter, 'cg_e', 'Engagement'); ?>
        <?php echo renderLinkFilterRemove($moreresults, 'cg_all', '...Mehr'); ?>

        <br><br>
        <table border="2">
            <tr><td><b>Name</b></td><td><b>Type</b></td><td><b>Goal</b></td></tr>
            <?php
//print_r($results);
            foreach ($results as $entry) {
                echo("<tr>");
                echo("<td>" . $entry['clientname'] . "</td>");
                echo("<td>" . $entry['type'] . "</td>");
                echo("<td>" . $entry['goal'] . "</td>");
                echo("</tr>");
            }
            ?>
        </table>




    </div>
</div>
