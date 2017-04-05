<?php
$baseurl = $this->config->item('base_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');
$this->lang->load('questionnaire');
?>
<div id="main_container">
    <div class="whitebox">
        <div class="title"><h2><?php echo $this->lang->line('cancel_headline'); ?></h2></div>   
        <p><?php echo $this->lang->line('cancel_description'); ?></p>
        <input type="submit" id="buttonC" class="button ok" value="<?php echo $this->lang->line('button_delete'); ?>" onClick="cancelsubscripton()" />
        <input type="button" class="button-grey cancel" value="<?php echo $this->lang->line('button_cancel'); ?>" onclick="location.href = '<?php echo $baseurl; ?>lpc/cs'"/>
        <input type="hidden" name="path" id="path" value="<?php echo $baseurl; ?>"/>
    </div>
</div>
<div id="scrollToHere"></div>
<!--POPUP-->
<div id="popupContact" class="confirmation">
    <a id="popupContactClose" onclick="location.href = '<?php echo $baseurl . $purl[$lg]['logout']; ?>'"></a>
    <form method="post" name="frmQuestionnaire" action="<?php echo $baseurl; ?>users/setquistionnaire/">
        <div class="confirmation-user">	
            <h1><?php echo $this->lang->line('questionnaire_headline'); ?></h1>
            <label><?php echo $this->lang->line('questionnaire_satisfaction_label'); ?></label>
            <div class="confirmation-radio">
                <input type="radio" name="satisfaction" id="satisfaction" value="1"/><span class="radio"> <?php echo $this->lang->line('questionnaire_satisfaction1'); ?></span>      
                <input type="radio" name="satisfaction" id="satisfaction" value="2"/><span class="radio"><span class="radio"> <?php echo $this->lang->line('questionnaire_satisfaction2'); ?> </span>
                    <input type="radio" name="satisfaction" id="satisfaction" value="3"/><span class="radio"><span class="radio"> <?php echo $this->lang->line('questionnaire_satisfaction3'); ?></span>
                        </div>
                        <label><?php echo $this->lang->line('questionnaire_reason_label'); ?></label>
                        <div class="confirmation-radio">
                            <input type="radio" name="cancelsub" id="cancelsub" value="1"/><span class="radio"> <?php echo $this->lang->line('questionnaire_reason1'); ?> </span> 
                            <input type="radio" name="cancelsub" id="cancelsub" value="2"/><span class="radio"> <?php echo $this->lang->line('questionnaire_reason2'); ?> </span>
                            <input type="radio" name="cancelsub" id="cancelsub" value="3"/><span class="radio"> <?php echo $this->lang->line('questionnaire_reason3'); ?></span>
                            <input type="radio" name="cancelsub" id="cancelsub" value="4"/><span class="radio"> <?php echo $this->lang->line('questionnaire_reason4'); ?></span>
                            <input type="radio" name="cancelsub" id="cancelsub" value="5"/><span class="radio"> <?php echo $this->lang->line('questionnaire_reason5'); ?></span>
                            <input type="radio" name="cancelsub" id="cancelsub" value="99"/><span class="radio"> <?php echo $this->lang->line('questionnaire_reason99'); ?></span>
                        </div>
                        <label><?php echo $this->lang->line('questionnaire_message_label'); ?></label>
                        <textarea name="feedback" id="feedback" class="textbox pagecode-textarea"></textarea>
                        <div class="button-container">
                            <input type="submit" name="submit" id="submit" class="button save" value="<?php echo $this->lang->line('button_save'); ?>"/>
                            <input type="button" class="button-grey cancel" value="<?php echo $this->lang->line('button_cancel'); ?>" onclick="location.href = '<?php echo $baseurl . $purl[$lg]['logout']; ?>'"/>
                        </div>
                        </div>
                        </form>
                        </div>
                        <div id="backgroundPopup"></div>
                        <!--END POPUP-->
