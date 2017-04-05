<?php
$baseurl = $this->config->item('base_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('tour');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("a#image_zoom").fancybox();
    });
</script>

<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('tour_head'); ?></h2>
    </div>
</div>
<div id="main_container">

    <div class="content_page"> 

        <div class="featured">
            <div class="content_page_cols">
                <div class="left_col_featured"><img src="<?php echo $this->lang->line('tour_image1'); ?>" width="238" height="128" alt="Placeholder" title="Title" /></div>
            </div>
            <div class="right_col_content_featured">
                <h2><?php echo $this->lang->line('tour_headline1'); ?></h2>
                <?php echo $this->lang->line('tour_copy1'); ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="featured">
            <div class="content_page_cols">
                <div class="left_col_featured"><a id="image_zoom" href="<?php echo $this->lang->line('tour_zoomimage2'); ?>"><img src="<?php echo $this->lang->line('tour_image2'); ?>" width="238" height="128" alt="Placeholder" title="Title" /></a></div>
            </div>
            <div class="right_col_content_featured">
                <h2><?php echo $this->lang->line('tour_headline2'); ?></h2>
                <?php echo $this->lang->line('tour_copy2'); ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="featured">
            <div class="content_page_cols">
                <div class="left_col_featured"><a id="image_zoom" href="<?php echo $this->lang->line('tour_zoomimage3'); ?>"><img src="<?php echo $this->lang->line('tour_image3'); ?>" width="238" height="128" alt="Placeholder" title="Title" /></a></div>
            </div>
            <div class="right_col_content_featured">
                <h2><?php echo $this->lang->line('tour_headline3'); ?></h2>
                <?php echo $this->lang->line('tour_copy3'); ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="featured">
            <div class="content_page_cols">
                <div class="left_col_featured"><a id="image_zoom" href="<?php echo $this->lang->line('tour_zoomimage4'); ?>"><img src="<?php echo $this->lang->line('tour_image4'); ?>" width="238" height="128" alt="Placeholder" title="Title" /></a></div>
            </div>
            <div class="right_col_content_featured">
                <h2><?php echo $this->lang->line('tour_headline4'); ?></h2>
                <?php echo $this->lang->line('tour_copy4'); ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="featured">
            <div class="content_page_cols">
                <div class="left_col_featured"><a id="image_zoom" href="<?php echo $this->lang->line('tour_zoomimage5'); ?>"><img src="<?php echo $this->lang->line('tour_image5'); ?>" width="238" height="128" alt="Placeholder" title="Title" /></a></div>
            </div>
            <div class="right_col_content_featured">
                <h2><?php echo $this->lang->line('tour_headline5'); ?></h2>
                <?php echo $this->lang->line('tour_copy5'); ?>
            </div>
            <div class="clear"></div>
        </div>

        <div class="featured">
            <div class="content_page_cols">
                <div class="left_col_featured"><img src="<?php echo $this->lang->line('tour_image6'); ?>" width="238" height="128" alt="Placeholder" title="Title" /></div>
            </div>
            <div class="right_col_content_featured">
                <h2><?php echo $this->lang->line('tour_headline6'); ?></h2>
                <?php echo $this->lang->line('tour_copy6'); ?>
            </div>
            <div class="clear"></div>
        </div>


    </div>
</div>
</div>
</div>
