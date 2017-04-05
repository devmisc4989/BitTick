<?php
$baseurl = $this->config->item('base_url');
$this->lang->load('account');

//print_r($accounts);die();
?>

<div id="main_container">
    <div class="whitebox" id="scrollToHere">

        <div class="title"><?php echo $this->lang->line('headline_subaccounts'); ?>			
            <input type="submit" class="button new-collection" value="<?php echo $this->lang->line('create account'); ?>"
                   href="javascript:void(0)" class="popup" onclick="CreateAccount()" />
        </div>
        <div><?php echo $this->lang->line('description'); ?></div>
        <div class="error-message"></div>

        <div id="test">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
                <tr class="table-title">
                    <td class="first"><?php echo $this->lang->line('tablehead account'); ?></td>
                    <td><?php echo $this->lang->line('tablehead plan'); ?><br><?php echo $this->lang->line('tablehead visitors'); ?><br>
                        <?php echo $this->lang->line('tablehead days'); ?></td>
                    <td class="last"><?php echo $this->lang->line('tablehead action'); ?></td>
                </tr>
                <?php
                foreach ($accounts as $account) {
                    ?>
                    <tr id="240" class="table-list" >
                        <td>
                            <a href="<?php echo $baseurl; ?>users/gup/<?php echo $account['clientid']; ?>" target="_blank"><?php echo $account['firstname'] . " " . $account['lastname']; ?></a>
                            <div class="popup-textinfo"><?php echo $account['email']; ?></div>
                        </td>
                        <td><b><?php echo $account['plan_name']; ?></b><br/><?php echo $account['used_quota'] . "/" . $account['plan_quota'] . "<br>" . $account['days_in_period'] . "/30"; ?></td>
                        <td style="text-align:right; padding-right:0px;">
                            <div class="action_trigger">
                                <a href="javascript:void(0)" class="action_title"><?php echo $this->lang->line('select'); ?></a>
                                <div class="action_menu">
                                    <div class="top"></div>
                                    <div class="middle">
                                        <a href="<?php echo $baseurl; ?>lpc/cs/<?php echo $account['clientid']; ?>" target="_blank"><?php echo $this->lang->line('Show Dashboard'); ?></a>
                                        <a href="<?php echo $baseurl; ?>users/gup/<?php echo $account['clientid']; ?>" target="_blank"><?php echo $this->lang->line('Edit Profile'); ?></a>
                                        <a href="#"><?php echo $this->lang->line('Change Plan'); ?></a>
                                        <a href="#"><?php echo $this->lang->line('Purchase Plan'); ?></a>
                                        <a href="#"><?php echo $this->lang->line('Stop Subscription'); ?></a>
                                        <a href="#"><?php echo $this->lang->line('Delete Account'); ?></a>
                                    </div>
                                    <div class="bottom"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <input type="hidden" id="path" value="http://blacktri-dev.de/"/>
        </div>
    </div>
</div>

<script type="text/javascript">
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
                     });

                     function CreateAccount() {
                         alert("create account");
                     }
</script>


