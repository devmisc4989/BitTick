<?php
// collecting all base url
$baseurl = $this->config->item('base_url');
$jsurl = $this->config->item('js_url');
$cssurl = $this->config->item('css_url');
$imgurl = $this->config->item('image_url');
$code = $this->uri->segment(3);
?>
<html>
    <!--	utilities for popup		-->
    <link rel="stylesheet" href="<?php echo $cssurl ?>popup.css" type="text/css" media="screen" />
    <script src="<?php echo $jsurl ?>jquery.js" type="text/javascript"></script>
    <script src="<?php echo $jsurl ?>popup.js" type="text/javascript"></script> 

    <script type="text/javascript">
        $(document).ready(function() {
<?php if ($code) { ?>
                //centering with css
                centerPopup('#deleteConfirm');
                //load popup
                loadPopup('#deleteConfirm');
    <?php
} else {
    ?>
                //centering with css
                centerPopup('#deleteConfirm');
                //load popup
                loadPopup('#deleteConfirm');
    <?php
}
?>
        });
    </script>
    <!-- popup for delete confirmation -->
    <div id='deleteConfirm'>
        <a id="popupDeleteClose">x</a>
        <?php if ($success == 1) { ?>
            <div style="font-size: 14px;">Successfully registered</div>
        <?php } else { ?>
            <div style="font-size: 14px;">Failed!please try again </div>
        <?php } ?>
        <br></br>
        <input type="hidden" name="collectiondeleteid" id="collectiondeleteid"/>
        <input type="button" value="OK" name="Submit" onclick=""/>
        <div id="popupDeleteCancel"><input type="submit" value="Cancel" /></div>
    </div>
    <div id="backgroundPopup"></div>
    <!-- end of popup -->
    <form name="">
    </form>
</html>
