<?php
// set output content type to Javascript
header('Content-type: application/x-javascript; charset=' . config_item('charset'));
header('Expires: Thu, 15 Apr 2020 20:00:00 GMT');
$this->lang->load('validatelogin');
?>
var login_msg = new Array();
login_msg['base_url'] = "<?php echo $this->config->item('base_url'); ?>"; 
login_msg['base_ssl_url'] = "<?php echo $this->config->item('base_ssl_url'); ?>"; 
login_msg['inputerror'] = "<?php echo $this->lang->line('validatelogin_inputerror'); ?>"; 
login_msg['subscriptioncancelled'] = "<?php echo $this->lang->line('validatelogin_subscriptioncancelled'); ?>";
login_msg['betanotapproved'] = "<?php echo $this->lang->line('validatelogin_betanotapproved'); ?>";

login_msg['pwr_emptyfield'] = "<?php echo $this->lang->line('validatelogin_pwr_emptyfield'); ?>"; 
login_msg['pwr_inputerror'] = "<?php echo $this->lang->line('validatelogin_pwr_inputerror'); ?>"; 
login_msg['pwr_success'] = "<?php echo $this->lang->line('validatelogin_pwr_success'); ?>";
login_msg['pwr_notvalidated'] = "<?php echo $this->lang->line('validatelogin_pwr_notvalidated'); ?>"; 
login_msg['pwr_mail_send_failed'] = "<?php echo $this->lang->line('validatelogin_pwr_mail_send_failed'); ?>"; 
login_msg['pwr_validationmail_sent_header'] = "<?php echo $this->lang->line('validatelogin_pwr_validationmail_sent_header'); ?>"; 
login_msg['pwr_validationmail_sent_copy'] = "<?php echo $this->lang->line('validatelogin_pwr_validationmail_sent_copy'); ?>"; 
login_msg['pwr_validationmail_failed_header'] = "<?php echo $this->lang->line('validatelogin_pwr_validationmail_failed_header'); ?>";

login_msg['validate_sendemail'] = "<?php echo $this->lang->line('validatelogin_validate_sendemail'); ?>"; 
login_msg['validate_emailerror'] = "<?php echo $this->lang->line('validatelogin_validate_emailerror'); ?>";
