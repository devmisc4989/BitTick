$(function(){
    //set same domain as

    document.domain= BTeditorVars.DocDomain;
   // console.log(document.domain);
    //visual AB				
    $("#frmVisualABStep3").validationEngine({
        onValidationComplete: function(form, status){
            if( status )
                CreateVisualAB(4);
        }
    });
    $("#frmVisualABStep4").validationEngine({
        onValidationComplete: function(form, status){
            if( status )
                CreateVisualAB(5);
        }
    });

    //set auto text selection
    $('textarea.trackingcode').click(function(){this.select()});

    //set autofocus text
    EnableTextOnFocus();

    //start with editor
    CreateVisualAB(2);
});


function CreateVisualAB(step, keepState)
{
    Log('CreateVisualAB step: ' + step);
    Log('Keep editor state: ' + (keepState?'Yes':'No'));
Log('cccccccc');
    BTTestType = 'visual';
    //$('html, body').removeClass('noscroll').addClass('noscroll');

    if( step == 0 )
    {
console.log("step0");
        EditorCleanup();
        $("#vablpname").val('http://');
console.log("before");
        OpenPopup("#ws1");
console.log("after");
    }
    else if( step == 1 )
    {
        //reset data
        OpenPopup("#vab2_1");
    }
    else if( step == 2)
    {
console.log("step2");
        EditorCleanup();
        var context = $("#vab2_2");
        var tabContext = $('.tabs_main');
        if( !keepState )
        {
            //be sure you use #fancybox-content context on every editor html operations
            var vablpname = $("#vablpname").val();
            if( vablpname.indexOf("http") < 0 )
                vablpname = "http://" + vablpname;

            //set visual user url
            $("#user_url", context).html( trimUrl(vablpname) );
            //store test url for step 4
            $("#testurl", $("#vab2_3")).val( vablpname );
            $("#control_pattern", $("#vab2_3")).val( vablpname );


            //create iframe if doesnt exists
            if( $("#frame_editor").size() == 0 )
            {
                $('<iframe frameborder="0" marginwidth="0" marginheight="0" scrolling="auto" id="frame_editor" name="frame_editor" class="frames"></iframe>').appendTo('#fancybox-wrap');
                //move tabs also
                $('.tabs_main').appendTo('#fancybox-wrap');
                //pop-ups
                $('.editor_popup_loading').appendTo('#fancybox-wrap');
                //overlayer
                $('.editor_overlayer').appendTo('#fancybox-wrap');
            }

            ResizeEditor("#vab2_2", 0);
            //OpenPopup("#ws2_2", {margintop:0});
        }

        OpenPopup("#vab2_2", {
            margintop:0,
            fullScreen: true,
            onCleanup: function(){
                Log('Hide editor layers');
                $("#frame_editor").fadeOut();
                $('.tabs_main').fadeOut();
                HideEditorPreloader();
            },
            onComplete: function(){
                Log('Show editor layers');
                PositionEditor();
                if(!keepState)
                {
                    ShowEditorPreloader(context);
                    var cacheBuster = Math.random();
                    var FrameUrl = BTeditorVars.FrameEditorBaseUrl + encodeURIComponent(vablpname) + "&BT_lg=" + BTeditorVars.lang;

                    FrameUrl += "&" + cacheBuster;
                    $("#frame_editor").attr("src", FrameUrl);
                }
                $("#frame_editor").fadeIn();
                $('.tabs_main').fadeIn();
            }
        });
    }
    else if( step == 3)
    {
        //regenerate tracking codes after client logged in
        GenerateTrackingCodes();
        Log("Default Tracking Approach to: OCPC");
        ResetLastStep();

        TrackingApproach(GetTrackingApproach())

        //goto last step
        OpenPopup("#vab2_3");
        //GetVariantsData();
    }
    else if( step == 4 )
    {
        $(window).scrollTop(0);
        OpenPopup("#vab2_4");
    }
    else if( step == 5 )
    {
        var variantdata = GetVariantData();
        $('#variantdata', $("#vab2_3")).val( $.toJSON(variantdata) );
        $.ajax({
            type: "POST",
            url: BTeditorVars.BaseSslUrl+"ue/ls/?isnew=yes",
            data: $("#frmVisualABStep3, #frmVisualABStep4").serialize(),
            cache: false,
            success: function(data){
                $.fancybox.close();
                document.location = BTeditorVars.BaseSslUrl+'lpc/lcd/' + data;
            }
        });
    }
}