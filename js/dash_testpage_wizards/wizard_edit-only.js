$(function () {
    //set same domain as
    //document.domain= '<?= $editorurl = $this->config->item('document_domain'); ?>';
    document.domain = BTeditorVars.DocDomain;



    $("#frmVisualStep3").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                CreateVisualAB(4);
        }
    });

    /* A/B Test */
    $("#frmABStep1").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                SaveABTest('variants');
        }
    });

    $("#frmABStep2").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                SaveABTest('goals');
        }
    });

    /* Visual AB */
    $("#frmVisualABStep3").validationEngine({
        onValidationComplete: function (form, status) {
            if (status)
                SaveVisualABTest('approach');
        }
    });

    //set auto text selection
    $('textarea.trackingcode').click(function () {
        this.select()
    });

    //set autofocus text
    EnableTextOnFocus();

    SetInitialValues();

});



function CreateAB(step) {
    if (step == 0) {
        OpenPopup("#ws1");
    }
    else if (step == 1) {
        //remove iframe
        $("#frame_editor").remove();
        OpenPopup("#ab2_1");
    }
    else if (step == 2) {
        var controlpagename = $("#controlpagename").val();
        Log("Found control page name: " + controlpagename);
        if (controlpagename.indexOf("http") < 0)
            controlpagename = "http://" + controlpagename;

        //set visual user url
        $("#user_url").html(controlpagename);

        //store test url for step 4
        $("#testurl", $("#ab2_2")).val(controlpagename);
        //$("#control_pattern", $("#ab2_2")).val( controlpagename );

        //get return for tracking code and code
        //$.getJSON("<?=$basesslurl?>ue/ncab/?url=" + controlpagename + "&lpcid=" + 0, function(data){
        $.getJSON(BTeditorVars.BaseSslUrl + "ue/ncab/?url=" + controlpagename + "&lpcid=" + 0, function (data) {
            Log("Tracking code data received: ", data);
            //save return data to html
            if (data) {
                var lastStepContext = $("#ab2_2");
                $("#trackingcode_success", lastStepContext).val(data.lpctrackingcode_success);
                $("#trackingcode_control", lastStepContext).val(data.lpctrackingcode_control);
                $("#trackingcode_variant", lastStepContext).val(data.lpctrackingcode_variant);

                $("#trackingcode_ocpc", lastStepContext).val(data.lpctrackingcode_ocpc);

                $("#lpccode", lastStepContext).val(data.lpccode);
            }
        });

        OpenPopup("#ab2_2");
    }
    else if (step == 3) {
        //fix placeholders
        $('.clonedInput').each(function (i, o) {
            var t = $(this).find('input:first');
            if (t.val() == EmptyNamePH)
                t.val('');
        });
        /* similar AJAX as SaveABTest() , different redirect*/
        $.ajax({
            type: "POST",
            //url: "<?=$basesslurl?>lpc/save/<?=$clientid?>/",
            url: BTeditorVars.BaseSslUrl + "lpc/save/" + BTeditorVars.ClientId + "/",
            data: $(".frmAB").serialize(),
            cache: false,
            success: function (data) {
                $.fancybox.close();
                document.location = BTeditorVars.BaseSslUrl + "lpc/lcd/" + BTeditorVars.CollectionId + "/" + BTeditorVars.ClientId + "/";
            }
        });
    }
}



function AddVariant() {
    var variantid = $("#variantpagehid").val();
    var oldvariantid = $("#variantpagehidold").val();
    var len = $('.clonedInput').length;

    if (variantid == '' || variantid == 0) {
        num = 1;
        $('div#ABVariants').html(
            '<div id="ABVariant' + num + '">' +
                '<div style="float:left; width: 670px;" class="clonedInput">' +
                '<br>' +
                '<input type="text" class="validate[required] textbox" style="width:660px" name="11" id="variantname1" value="' + BTeditorVars.enterNameText + '" originaltext="' + BTeditorVars.enterNameText + '"/>' +
                '<input type="text" class="validate[required] textbox" style="width:660px" name="1" id="variantpagename1" value="http://"/>' +
                '</div>' +
                '<div class="lp-delete-cont">' +
                '<br>' +
                '<input type="button" class="lp-button lp-delete" onclick="DeleteVariant(1);" id="btnDel1"/>' +
                '</div>' +
                '</div>'
        );

        $('input#variantpagehid').attr('value', "1_");

    }
    else {
        maxval = variantid.split("_");
        var max = maxval[0];
        var len = maxval.length;
        for (var i = 1; i < len; i++) {
            if (parseInt(maxval[i]) > parseInt(max))
                max = parseInt(maxval[i]);
        }
        newNum = parseInt(max) + 1;

        $('div#ABVariant' + max).after(
            '<div id="ABVariant' + newNum + '">' +
                '<div style="float:left; width: 670px;" class="clonedInput">' +
                '<br>' +
                '<input type="text" class="validate[required] textbox textfocus" style="width:660px" name="' + newNum + newNum + '" id="variantname' + newNum + '" value="' + BTeditorVars.enterNameText + '" originaltext="' + BTeditorVars.enterNameText + '"/>' +
                '<input type="text" class="validate[required] textbox" style="width:660px" name="' + newNum + '" id="variantpagename' + newNum + '" value="http://"/>' +
                '</div>' +
                '<div class="lp-delete-cont">' +
                '<br>' +
                '<input type="button" class="lp-button lp-delete" onclick="DeleteVariant(' + newNum + ');" id="btnDel1"/>' +
                '</div>' +
                '</div>'
        );

        variantid = variantid + newNum + "_";
        $('input#variantpagehid').attr('value', variantid);
        num = newNum;
    }
}

function CreateVisualAB(step, keepState) {
    Log('CreateVisualAB step: ' + step);
    Log('Keep editor state: ' + (keepState ? 'Yes' : 'No'));
    BTTestType = 'visual';

    if (step == 2) {
        var context = $("#vab2_2");
        var tabContext = $('.tabs_main');

        if (!keepState) {
            EditorCleanup();
            //be sure you use #fancybox-content context on every editor html operations
            var vablpname = $("#vablpname").val();
            Log('vablpname=' + vablpname);
            if (vablpname.indexOf("http") < 0) {
                vablpname = "http://" + vablpname;
            }

            //set visual user url
            //$("#user_url", context).html( vablpname );//removed we have this from database
            //store test url for step 4
            $("#testurl", $("#vab2_3")).val(vablpname);
            //$("#control_pattern", $("#vab2_3")).val( vablpname );//removed because we have this in database

            //create iframe if doesnt exists
            if ($("#frame_editor").size() == 0) {
                $('<iframe frameborder="0" marginwidth="0" marginheight="0" scrolling="auto" id="frame_editor" name="frame_editor" class="frames"></iframe>').appendTo('#fancybox-wrap');
                //move tabs also
                $('.tabs_main').appendTo('#fancybox-wrap');
                //pop-ups
                $('.editor_popup_loading').appendTo('#fancybox-wrap');
                //overlayer
                $('.editor_overlayer').appendTo('#fancybox-wrap');
                //overlayer
                $('.editor_proxy, .editor_action').appendTo('#fancybox-wrap');
            }

            ResizeEditor("#vab2_2", 0);
            //resize fix
            setTimeout(function () {
                ResizeEditor("#vab2_2", 0);
            }, 100);

            //OpenPopup("#ws2_2", {margintop:0});
        }

        OpenPopup("#vab2_2", {
            margintop: 0,
            fullScreen: true,
            //showCloseButton: <?php echo $tenant=="etracker"?"false":"true";?>,
            showCloseButton: Is_etracker,
            onCleanup: function () {
                Log('Hide editor layers');
                $("#frame_editor").fadeOut();
                $('.tabs_main').fadeOut();
                HideEditorPreloader();
                //hide
                BlackTri.DisableEditor();
            },
            onComplete: function () {
                Log('Show editor layers');
                PositionEditor();
                if (!keepState) {
                    ShowEditorPreloader(context);
                    var cacheBuster = Math.random();
                    //$("#frame_editor").attr("src", "<?= $editorurl = $this->config->item('editor_url'); ?>?blacktriurl=" + encodeURIComponent(vablpname) + "&client=<?=$clientid?>&" + cacheBuster);
                    var FrameUrl = BTeditorVars.FrameEditorBaseUrl + encodeURIComponent(vablpname) + "&client=" + BTeditorVars.ClientId + "&" + cacheBuster;
                    $("#frame_editor").attr("src", FrameUrl);
                }
                $("#frame_editor").fadeIn();
                $('.tabs_main').fadeIn();
            }
        });
    }
    else if (step == 3) {
        Log("Tracking Approach is: " + GetTrackingApproach());
        //ResetLastStep();

        TrackingApproach(GetTrackingApproach())
        OpenPopup("#vab2_3");
        //GetVariantsData();
		
		$('#ip_filter_action').unbind('change').bind('change', function(){
			$('#ip_filter_list').attr('disabled', $(this).val() == 'not_used');
		});
    }
    else if (step == 4) {
        CreateGoals('#vab2_4');
        OpenPopup("#vab2_4");
    }
}