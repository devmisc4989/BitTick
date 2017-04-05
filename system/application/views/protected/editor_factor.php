<?
$baseurl = $this->config->item('base_url');
$baseurlssl = $this->config->item('base_ssl_url');
$this->lang->load('editor');
$this->lang->load('personalization');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link type="text/css" href="<?= $baseurlssl ?>css/style.css" rel="stylesheet"/>
        <link type="text/css" href="<?= $baseurlssl ?>css/admin.css" rel="stylesheet"/>
        <link type='text/css' href='<?= $baseurlssl ?>css/template.css' rel='stylesheet'/>
        <link type="text/css" href="<?= $baseurlssl ?>css/editor.css" rel="stylesheet"/>
        <link type="text/css" href="<?= $baseurlssl ?>css/factor.css" rel="stylesheet"/>
        <link type="text/css" href="<?= $baseurlssl ?>css/validationEngine.jquery.css" rel="stylesheet"/>
        <link type="text/css" href="<?= $baseurlssl ?>js/fancybox/jquery.fancybox-1.3.4.css" rel="stylesheet"/>

        <script type="text/javascript">
        //set same domain
            document.domain = "<?php echo $this->config->item('document_domain') ?>";
        </script>
        <script type="text/javascript" src="<?= $baseurlssl ?>js/jquery-latest.js"></script>
        <script type="text/javascript" src="<?= $baseurlssl ?>js/jquery.elastic.js"></script>
        <script type="text/javascript" src="<?= $baseurlssl ?>js/fancybox/jquery.fancybox-1.3.4.js"></script>
        <script type="text/javascript" src="<?= $baseurlssl ?>js/fancybox/jquery.easing-1.3.pack.js"></script>
        <script type="text/javascript" src="<?= $baseurlssl ?>jsi18n/jqueryValidationEngine.js"></script>
        <script type="text/javascript" src="<?= $baseurlssl ?>js/jquery.validationEngine.js"></script>
        <script type="text/javascript" src="<?= $baseurlssl ?>js/popup.js"></script>
        <script type="text/javascript">
        //setup current selector
            var path = "<?php echo $baseurlssl ?>";
            var jqSel = "<?= $qp ?>";
            var iVariants = <?= count($factor["levels"]) ?>;
            var lastHeight = 0;
            $(function() {
                lastHeight = 360;
                //tooltip
                ShowInfo1();
                //setup control values
                if ($("textarea#pagecode").val() == "")
                {
                    $("textarea#pagecode").val(parent.BlackTri.editorGetControlHtml(jqSel));
                }
                $(".variant-text, #pagecode").elastic({resize: ResizeParentEditor});
                $(".variant-text, #pagecode").trigger("blur");

                //attach validation on factor name
                $("#frmEditFactor").validationEngine({
                    onValidationComplete: function(form, status) {
                        if (status)
                            submitVariants(status);
                    }
                });
                ResizeParentEditor();
            })

            function ResizeParentEditor()
            {
                //tooltip
                ShowInfo1();

                var diff = $("body").height() - lastHeight;
                if (diff != 0)
                    parent.BlackTri.ResizeInlineFactor(diff);
                lastHeight = $("body").height();
            }


            function closeVariants()
            {
                parent.BlackTri.$.fancybox.close();
            }
            function addVariant()
            {
                iVariants++;
                var variantName = "<?php echo $this->lang->line('Variant prefix'); ?>" + iVariants;

                var dv = $("<div/>");
                $("<label/>").html("<?php echo $this->lang->line('Variant label'); ?>" + variantName + "<input type='hidden' name='variantname[]' value='" + variantName + "'/><input type='hidden' name='mvt_level_id[]' value='0'/>")
                        .appendTo(dv);
                $("<a class='lp-delete' href='javascript:;' onclick='removeVariant(this)'></a>")
                        .appendTo(dv)
                $("<textarea/>")
                        .addClass("textbox variant-text")
                        .attr("name", "variant[]").attr("rows", 2).css("resize", "none")
                        .val($("#pagecode").val())
                        .appendTo(dv);
                dv.appendTo($("#variants_content"));
                $("#variants_content").find("textarea:last-child")
                        .elastic({resize: ResizeParentEditor})
                        .trigger("blur");
                ResizeParentEditor();
            }
            function removeVariant(elm)
            {
                //iVariants--;
                var parentObj = $(elm).parent();
                parentObj.remove();
                ResizeParentEditor();
            }
            function submitVariants(ok)
            {
                if (typeof(ok) == "undefined")
                    return;
                $.ajax({
                    type: "POST",
                    url: '<?= $baseurlssl ?>ue/fs/',
                    data: $("#frmEditFactor").serialize(),
                    success: function(ret) {
                        parent.BlackTri.saveEditor(jqSel, ret);
                        closeVariants();
                    }
                });
            }
            function checkVariants(fid)
            {
                if ($("#variants_content label").length == 0)
                {
                    closeVariants();
                    return false;
                }
                return true;
            }
            function discardVariants()
            {

                //console.log($("#frmEditFactor").serialize());
                $.ajax({
                    type: "POST",
                    url: '<?= $baseurlssl ?>ue/fd/',
                    data: $("#frmEditFactor").serialize(),
                    success: function(ret) {
                        parent.BlackTri.removeFactor(jqSel);
                        closeVariants();
                    }
                });
            }
            function ShowInfo1()
            {
                if ($("textarea.variant-text").size() <= 0 && !$(".info1").is(":visible"))
                    $(".info1").fadeIn(200);
                if ($("textarea.variant-text").size() > 0 && $(".info1").is(":visible"))
                    $(".info1").fadeOut(200);
            }
        </script>
    </head>
    <body>
        <form method="post" name="frmEditFactor" id="frmEditFactor" onsubmit="return false;">
            <div class="confirmation confirmation-user">
                <h1><?php echo $this->lang->line('Content variations'); ?></h1>
                <div><?php echo splink('factor_editor'); ?></div>
                <div class="confirmation-field">
                    <label><?php echo $this->lang->line('Factor Name'); ?></label>
                    <div><?php echo $this->lang->line('Factor Name description'); ?></div>
                    <input type="text" title="Enter name of the factor" size="35" id="fname" name="fname" class="validate[required] textbox" value="<?= $factor["name"] ?>">
                        <div class="popup-textinfo"><?php echo $this->lang->line('Enter name of factor here'); ?></div>
                </div> 
                <div class="confirmation-field">
                    <br />
                    <hr style="width:530px; border:0; height:1px; background-color:#888;" />
                </div>					 				
                <label><?php echo $this->lang->line('Control'); ?></label>
                <div class="confirmation-field"><?php echo $this->lang->line('Control description'); ?></div>
                <textarea id="pagecode" class="textbox pagecode-textarea" name="pagecode" readonly="readonly" disabled="disabled"></textarea>					
                <div id="variants_content">
                    <?
                    foreach ($factor["levels"] as $level) {
                        ?>
                        <div>
                            <label><?= $level["n"] ?>		
                                <input type="hidden" value="<?= $level["n"] ?>" name="variantname[]">
                                    <input type="hidden" value="<?= $level["i"] ?>" name="mvt_level_id[]">
                                        </label>
                                        <a onclick="removeVariant(this)" href="javascript:;" class="lp-delete"></a>
                                        <textarea class="textbox variant-text" name="variant[]" rows="2" resize="none"><?= $level["v"] ?></textarea>
                                        </div>
                                        <?
                                    }
                                    ?>

                                    </div>
                                    <label class="addvariant">
                                        <a href="javascript:addVariant();"><?php echo $this->lang->line('Add a variant'); ?></a>
                                    </label>

                                    <div class="ctrl-buttons" style="width:400px">
                                        <input type="submit" id="signin_button" onclick="return submitVariants()" name="submitpage" value="<?php echo $this->lang->line('button_save'); ?>" class="button ok">
                                            <input type="button" id="signin_button" name="btn_remove" onclick="return discardVariants()" value="<?php echo $this->lang->line('button_delete'); ?>" class="cancel button ok">
                                                <input type="button" onclick="closeVariants()" value="<?php echo $this->lang->line('button_cancel'); ?>" class="button-grey cancel" name="Cancel" id="popupContactCancel">
                                                    </div>						  
                                                    </div>
                                                    <input type="hidden" name="qp" value="<?= $qp ?>" />
                                                    </form>
                                                    <div class="clear"></div>
                                                    </body>
                                                    </html>