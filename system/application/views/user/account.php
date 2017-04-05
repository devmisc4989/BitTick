<?php
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$this->lang->load('account');

$status = $profileDetails['status'];
$loginstatus = $this->session->userdata('sessionLoginStatus');

$plan_data = getPlanDetails($profileDetails['userplan']);
$plan_name = $plan_data['plan_name'];
$plan_quota = $plan_data['plan_quota'];
$plan_quota_name = $plan_data['plan_quota_name'];

if ($status == CLIENT_STATUS_ACTIVE) {
    $created = strtotime($createddate);
    $diff = ceil(($created + 2592000 - time()) / 86400);
    $diff = ($diff < 0) ? 0 : $diff;

    $plans = $this->config->item('PLAN');
    $orderurl = $this->config->item('LINK_BASIC');
    switch ($userplan) {
        case $plans['PLAN_STARTER']:
            $orderurl = $this->config->item('LINK_STARTER');
            break;
        case $plans['PLAN_BASIC']:
            $orderurl = $this->config->item('LINK_BASIC');
            break;
        case $plans['PLAN_PROFESSIONAL']:
            $orderurl = $this->config->item('LINK_PROFESSIONAL');
            break;
    }
    if ($diff > 0) {
        $plan_errmsg = sprintf($this->lang->line('error_timetotest'), $diff, $orderurl);
    } else {
        $plan_errmsg = $this->lang->line('error_testexceeded');
    }
    $plan_haserror = TRUE;
} else {
    $plan_haserror = FALSE;
}
?>
<div id="main_container">
    <div class="whitebox">

<?php
if ($plan_haserror) {
    ?>
            <div class="notification">
                <ul>
                    <li class="icon_2"><?php echo $plan_errmsg; ?></li>
                </ul>
            </div>
    <?php
}
?>

        <div class="title"><?php echo $this->lang->line('headline_account'); ?></div>
        <h3><?php echo $this->lang->line('sublineline_account_plan'); ?></h3>
        <br/>
        <p>
<?php echo $this->lang->line('tablehead plan'); ?>:</br>
<?php
echo "<b>" . $plan_name . " ";
if ($profileDetails['status'] == CLIENT_STATUS_ACTIVE)
    echo $this->lang->line('evaluation phase') . " ";
echo "(" . $plan_quota_name . " " . $this->lang->line('visitors per month') . ")";
?></b>
        </p>
        <br/>
        <p>
            <?php
            $dateformat = $this->lang->line('dateformat');
            // if user is in testphase, use createddate as startdate. if user has purchased a product, use subscriptionstartdate.
            if ($profileDetails['status'] == CLIENT_STATUS_ACTIVE)
                $mydate = $profileDetails['createddate'];
            else
                $mydate = $profileDetails['subscriptionstartdate'];
            if ($mydate == NULL)
                $mydate = $profileDetails['createddate'];

            $billingdate = computeBillingPeriod($mydate);
            $startdate = date($dateformat, strtotime($billingdate['startdate']));
            $enddate = date($dateformat, strtotime($billingdate['enddate']));
            echo $this->lang->line('tablehead usage') . ":</br>";
            echo "<b>" . $profileDetails['used_quota'] . " " . $this->lang->line('in this month') . " (" . $startdate . " - " . $enddate . ")</b>";
            ?></br>
        </p>
        <br/>

            <?php
            if (($profileDetails['role'] != CLIENT_ROLE_SUB) && ($loginstatus == LOGIN_STATUS_FULL) && 1 == 2) {
                ?>
            <p>
                <a class="notification_green" href="#">Tarif ändern</a>
            </p>
            <?php
        }
        if ($profileDetails['role'] != CLIENT_ROLE_SUB && 1 == 2) {
            ?>

            <h3 style="margin-top:30px;"><?php echo $this->lang->line('sublineline_account_invoices'); ?></h3>

            <div class="invoicelist">
                <ul>
                    <li>Rechnung vom 04.05.2013 <a href="#">Herunterladen</a></li>
                    <li>Rechnung vom 04.05.2013 <a href="#">Herunterladen</a></li>
                    <li>Rechnung vom 04.05.2013 <a href="#">Herunterladen</a></li>
                    <li>Rechnung vom 04.05.2013 <a href="#">Herunterladen</a></li>
                    <li>Rechnung vom 04.05.2013 <a href="#">Herunterladen</a></li>
                </ul>
            </div>
    <?php
}
?>


    </div>	
</div>