<script type="bt_template" id="st_editor_radios" >
    <span class="four_side_radios">
        <label class="radio" ><input class="default_radio" type="radio" value="all" name="" checked>All</label>
        <!--<label class="radio" ><input type="radio" value="0" name="">Top</label>
        <label class="radio" ><input type="radio" value="1" name="">Right</label>
        <label class="radio" ><input type="radio" value="2" name="">Bottom</label>
        <label class="radio" ><input type="radio" value="3" name="">Left</label>-->
        <label class="radio" ><input type="radio" value="3" name="">Individual</label>
    </span>
</script>

<div id="st_editor" class="confirmation confirmation-user editor_popup">
    <h1><?php echo $this->lang->line('Edit Styles'); ?>  <span id="st_tagname"></span></h1>

    <div id="st_editor_tabs">
        <div class="tabs_main">
            <div class="tabs_container">
                <div class="zone_tabs">
                    <div class="tab selected" data-tab="text_tab"><a>Text</a></div>
                    <div class="tab" data-tab="color_tab"><a>Color/Background</a></div>
                    <div class="tab" data-tab="dimensions_tab"><a>Dimensions</a></div>
                    <div class="tab" data-tab="layout_tab"><a>Layout</a></div>
                    <div class="tab" data-tab="border_tab"><a>Borders</a></div>

                </div>
            </div>
        </div>

        <div id="text_tab" class="tab-content selected">
            <div class="column right">
                <div class="field">
                    <label>Font Style</label>
                    <select data-css_prop="font-style"></select>
                </div>

                <div class="field">
                    <label>Text Alignment</label>
                    <select data-css_prop="text-align"></select>
                </div>
                <div class="field">
                    <label>Text Decoration</label>
                    <select data-css_prop="text-decoration"></select>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label>Font Family</label>
                    <input  id="st_font_family" type="text" data-css_prop="font-family"/>
                </div>
                <div class="field">
                    <label>Font Size</label>
                   <!-- <select data-css_prop="font-size"></select>-->
                    <input type="text" data-css_prop="font-size">
                </div>

                <div class="field">
                    <label>Font Weight</label>
                    <select data-css_prop="font-weight"></select>
                </div>
            </div>

        </div>
        <div id="color_tab" class="tab-content">
            <!--<div class="column">-->
            <div class="column right block_labels">
                <div class="field">
                    <label>Background Position</label>
                    <input   type="text" data-css_prop="background-position"/>
                </div>
                <div class="field">
                    <label>Background Repeat</label>
                    <select data-css_prop="background-repeat"></select>
                </div>
            </div>
            <div class="column block_labels">
                <div class="field">
                    <label style="line-height: 30px">Font Color</label>
                    <input class="color_input" id="st_color" type="text" data-css_prop="color"/>
                </div>

                <div class="field">
                    <label>Background Color</label>
                    <input class=" color_input" id="st_bg_color" type="text" data-css_prop="background-color"/>

                </div>
                <!--<div class="field">
                    <label>Background Image</label>
                    <input   type="text" data-css_prop="background-image"/>
                </div>-->

            </div>
            <div class="block_labels">
                <div class="field">
                    <label>Background Image</label>
                    <input   type="text" data-css_prop="background-image" style="width: 446px"/>

                </div>
            </div>

        </div>

        <div id="dimensions_tab" class="tab-content">
            <div class="column right  narrow_labels">
                <div class="field">
                    <label>Margin</label>
                    <input  type="text" data-css_prop="margin"/>
                </div>
                <div class="field">
                    <label>Padding</label>
                    <input  type="text" data-css_prop="padding"/>
                </div>
            </div>
            <div class="column  narrow_labels">
                <div class="field">
                    <label>Width</label>
                    <input  type="text" data-css_prop="width"/>
                </div>
                <div class="field">
                    <label>Height</label>
                    <input  type="text" data-css_prop="height"/>
                </div>
            </div>

        </div>
        <div id="layout_tab" class="tab-content">
            <div class="column right narrow_labels">
                <div class="field">
                    <label  class="advanced">Display </label>
                    <select data-css_prop="display"></select>
                </div>
                <div class="field">
                    <label  class="advanced">Visibility</label>
                    <select data-css_prop="visibility"></select>
                </div>
                <div class="field">
                    <label>Position </label>
                    <select data-css_prop="position"></select>
                </div>
                <div class="field">
                    <label>Float</label>
                    <select data-css_prop="float"></select>
                </div>
                <div class="field">
                    <label>Clear</label>
                    <select data-css_prop="clear"></select>
                </div>

            </div>
            <div class="column  narrow_labels">

                <div class="field">
                    <label>Left </label>
                    <input data-css_prop="left" type="text">
                </div>

                <div class="field">
                    <label>Top</label>
                    <input data-css_prop="top" type="text">
                </div>

                <div class="field">
                    <label>Right </label>
                    <input data-css_prop="right" type="text">
                </div>

                <div class="field">
                    <label>Bottom</label>
                    <input data-css_prop="bottom" type="text">
                </div>
                <div class="field">
                    <label>Z Index</label>
                    <input data-css_prop="z-index" type="text">
                </div>

            </div>


        </div>

        <div id="border_tab" class="tab-content">
            <div class="column right"></div>
            <!--<div class="column">-->
            <div>
                <div class="field">
                    <label>Border Color</label>
                    <input class="color_input master_property" id="st_border_color" data-css_prop="border-color" type="text">
                </div>
                <div class="field">
                    <label>Border Style</label>
                    <select class="master_property" data-css_prop="border-style"></select>
                </div>
                <div class="field">
                    <label>Border Width</label>
                    <input  class="master_property" data-css_prop="border-width" type="text">

                </div>
                <div class="field">
                    <label>Border Radius</label>
                    <input  class="master_property" data-css_prop="border-radius" type="text">
                </div>
            </div>
        </div>


    </div>
    <!--Buttons-->
    <div class="links-3-4">
        <div class="ctrl-buttons">
            <?php if($tenant=='etracker'): ?>
            <div class="links">
                <a id="st_editor_cancel" class="editor_back" href="javascript:void(0)">
                    <?php echo $this->lang->line('Abbrechen'); ?>
                </a>
            </div>

            <input id="st_editor_save" type="button" class="button ok"
                   value="<?php echo $this->lang->line('Save test'); ?>">

             <?php else: ?>
            <input id="st_editor_cancel" class="BtEditorButton" value="<?php echo $this->lang->line('Abbrechen'); ?>"  type="button">
            <input id="st_editor_save" class="BtEditorButton" value="<?php echo $this->lang->line('Save test'); ?>"  type="button">

            <?php endif ?>






        </div>
    </div>
</div>