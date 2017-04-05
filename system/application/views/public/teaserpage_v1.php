<?php
$imgurl = $this->config->item('image_url');
$baseurl = $this->config->item('base_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('teaserpage');
?>
<style type='text/css'>
    * 
    label.error { color: red;font-size: 11px;  }
</style>
<script>
    $(document).ready(function() {
        // SUCCESS AJAX CALL, replace 'success: false,' by:     success : function() { callSuccessFunction() },
        $('#teaserform1').validationEngine({success: callSendEmail1, scroll: false})
        $('#teaserform2').validationEngine({success: callSendEmail2, scroll: false})

        $(".teaserform .email")
                .focus(function() {
            if ($(this).val() == 'Ihre Emailadresse')
                $(this).val('')
        })
                .blur(function() {
            if ($(this).val() == '')
                $(this).val('Ihre Emailadresse')
        });
    });
    var path = '<?= base_url() ?>';
    function callSendEmail1()
    {
        var email = $("#teaserform1 .email").val();
        if (email != '')
            $.get('<?php echo $baseurl . "users/tae"; ?>',
                    {email: email},
            function(ret) {
                //save message
                $("#message").html(ret);
                //centering with css
                centerPopup('#popupContact');
                //load popup
                loadPopup('#popupContact');
                //reset and revalidate
                $(this).val('Ihre Emailadresse');
                $('#teaserform1').validationEngine({success: callSendEmail1, scroll: false})
            });
    }
    function callSendEmail2()
    {
        var email = $("#teaserform2 .email").val();
        if (email != '')
            $.get('<?php echo $baseurl . "users/tae"; ?>',
                    {email: email},
            function(ret) {
                //save message
                $("#message").html(ret);
                //centering with css
                centerPopup('#popupContact');
                //load popup
                loadPopup('#popupContact');
                //reset and revalidate
                $(this).val('Ihre Emailadresse');
                $('#teaserform2').validationEngine({success: callSendEmail2, scroll: false})
            });
    }
    function trackConversion() {
        var pageTracker = _gat._getTracker('UA-1870369-3');
        pageTracker._trackPageview('/vpv/saveEmail/');
        convert();
    }
</script>

<div id="scrollToHere"></div>
<div id="banner_wrap">
    <div id="banner" class="teaser">     	
        <div class="banner-left" style="padding: 0 30px;">
            <a href="<?php echo $baseurl . $purl[$lg]['home']; ?>" id="logo"><img src="<?php echo $imgurl ?>logo.png" /></a>
            <h1><?php echo $this->lang->line('teaser_heading'); ?></h1>
            <p style="font-size:17px"><?php echo $this->lang->line('teaser_headdescription'); ?></p>
            <form id="teaserform1" class="teaserform" onsubmit="return false;">
                <input type="text" style="width:225px;" value="Ihre Emailadresse" class="validate[custom1[teaseremail] text-input textbox email" id="email" name="email">
                <input type="submit" style="height:34px;margin:2px 0 0 6px;width:120px" onclick="" value="Speichern" name="Submit" class="but register" id="register_button">
            </form>
        </div>
        <div class="banner-right" style="width:505px;">
            <img src="<?php echo $imgurl ?>banner_image_de5.png" />
        </div>
    </div>
</div>
<div id="main_container">
    <div class="pricing-title">
        <h1><?php echo $this->lang->line('teaser_headline1'); ?></h1>
    </div>

    <div class="pricing-des padding-r">
        <h3><?php echo $this->lang->line('teaser_subline1'); ?></h3>
        <p><?php echo $this->lang->line('teaser_subcopy1'); ?></p>
    </div>
    <div class="pricing-des padding-l">
        <h3><?php echo $this->lang->line('teaser_subline2'); ?></h3>
        <p><?php echo $this->lang->line('teaser_subcopy2'); ?></p>
    </div>

    <div class="pricing-des padding-r">
        <h3><?php echo $this->lang->line('teaser_subline3'); ?></h3>
        <p><?php echo $this->lang->line('teaser_subcopy3'); ?></p>
    </div>
    <div class="pricing-des padding-l">
        <h3><?php echo $this->lang->line('teaser_subline4'); ?></h3>
        <p><?php echo $this->lang->line('teaser_subcopy4'); ?></p>
    </div>

    <p>&nbsp;</p>

    <div class="pricing-title">
        <h1><?php echo $this->lang->line('teaser_headline2'); ?></h1>
    </div>

    <div id="bottominput">
        <form id="teaserform2" class="teaserform" onsubmit="return false;">
            <input type="text" style="width:400px;" value="Ihre Emailadresse" class="validate[custom1[teaseremail] text-input textbox email" id="email" name="email">
            <input type="submit" style="height:34px;margin:2px 0 0 6px;width:120px" onclick="" value="Speichern" name="Submit" class="but register" id="register_button">
        </form>
    </div>

</div>

<!--POPUP for teaser response-->
<div id="popupContact" class="confirmation">
    <a id="popupContactClose"></a>
    <div class="confirmation confirmation-user">
        <div class="confirmation align-left">
            <h2><?php echo $this->lang->line('teaser_popup_headline'); ?></h2>
            <p><?php echo $this->lang->line('teaser_popup_copy'); ?></p>
        </div>
    </div>
    <div id="message">
    </div>
</div>
<div id="backgroundPopup"></div>
<!--END POPUP-->