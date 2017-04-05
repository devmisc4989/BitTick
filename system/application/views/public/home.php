<?php
$imgurl = $this->config->item('image_url');
$baseurl = $this->config->item('base_url');
$basesslurl = $this->config->item('base_ssl_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('home');

$cta_link = $basesslurl . $purl[$lg]['register'] . '110';
$demo_link = $basesslurl . "users/demo";
?>

<div id="banner_wrap">
    <div id="banner">
        <div class="banner_shaddow"></div>
        <div class="banner_graph">
            <h1><?php echo $this->lang->line('home_heading'); ?></h1>
            <h4><?php echo $this->lang->line('home_headdescription'); ?></h4>
            <div class="form_area">
                <form method="get" action="<?php echo $basesslurl; ?>editor/" />
                <div class="form_area_inside">
                    <div class="form_element">
                    <input name="url" type="text" class="insert_url_homepage" placeholder="<?php echo $this->lang->line('home_calltoaction_url'); ?>"/>
                    </div>
                    <div class="form_element">
                    <input type="submit" class="submit_url_homepage" value="<?php echo $this->lang->line('home_calltoaction_headline'); ?>" />
                    </div>
                <div style="clear:both;"></div>
                </div>
                </form>
            </div>
            <div class="banner_homepage_intro"><?php echo $this->lang->line('home_intro'); ?></div>        
        </div>         
    </div>
</div>
<div id="main_container">
    <div class="logos">
        <div class="logos_text"><?php echo $this->lang->line('home_logos'); ?></div>
    </div>
    <div class="header_top"></div>

    <div class="content_block">
        <div class="logos_text"><?php echo $this->lang->line('home_testimonials'); ?></div>
        <div class="content_row">
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_testimonial_image1'); ?>" width="110" height="92" /></div>
                <div class="cbc_second">
                    <p><?php echo $this->lang->line('home_testimonial_copy1'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_testimonial_image2'); ?>" width="110" height="92" /></div>
                <div class="cbc_second">
                    <p><?php echo $this->lang->line('home_testimonial_copy2'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div class="header_top"></div>

    <div class="content_block">
        <div class="content_row">
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_teasericon6'); ?>" width="110" height="100" /></div>
                <div class="cbc_second">
                    <h2><?php echo $this->lang->line('home_teaserhead6'); ?></h2>
                    <p><?php echo $this->lang->line('home_teaser6'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_teasericon2'); ?>" width="110" height="100" /></div>
                <div class="cbc_second">
                    <h2><?php echo $this->lang->line('home_teaserhead2'); ?></h2>
                    <p><?php echo $this->lang->line('home_teaser2'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="content_row">
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_teasericon4'); ?>" width="110" height="100" /></div>
                <div class="cbc_second">
                    <h2><?php echo $this->lang->line('home_teaserhead4'); ?></h2>
                    <p><?php echo $this->lang->line('home_teaser4'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_teasericon5'); ?>" width="110" height="100" /></div>
                <div class="cbc_second">
                    <h2><?php echo $this->lang->line('home_teaserhead5'); ?></h2>
                    <p><?php echo $this->lang->line('home_teaser5'); ?></p>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="content_row">
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_teasericon3'); ?>" width="110" height="100" /></div>
                <div class="cbc_second">
                    <h2><?php echo $this->lang->line('home_teaserhead3'); ?></h2>
                    <p><?php echo $this->lang->line('home_teaser3'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="content_block_col_content">
                <div class="cbc_first"><img src="<?php echo $imgurl . $this->lang->line('home_teasericon1'); ?>" width="110" height="100" /></div>
                <div class="cbc_second">
                    <h2><?php echo $this->lang->line('home_teaserhead1'); ?></h2>
                    <p><?php echo $this->lang->line('home_teaser1'); ?></p>
                </div>
                <div class="clear"> </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="header_top"></div>

    <div class="cta_homepage_area">
        <h1><?php echo $this->lang->line('home_cta_area_head'); ?></h1>
        <h4><?php echo $this->lang->line('home_cta_area_subline'); ?></h4>
        <a class="button signup1" style="margin-left:200px" href="<?= $cta_link ?>"><?php echo $this->lang->line('home_cta_area_button_test'); ?></a>
        <a class="button signup1" style="float:right;margin-right:200px;" href="<?= $demo_link ?>"><?php echo $this->lang->line('home_cta_area_button_demo'); ?></a>    
    </div>

</div>