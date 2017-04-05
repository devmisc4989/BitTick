<?php
$baseurl = $this->config->item('base_url');
$imgurl = $this->config->item('image_url');
$lg = $this->config->item('language');
$purl = $this->config->item('page_url');

$this->lang->load('about');
?>
<div id="title_bg">
    <div class="title-inner">
        <h2><?php echo $this->lang->line('about_head'); ?></h2>
    </div>
</div>
<div id="main_container">

    <div class="content_page">
        <div class="content_page_cols">
            <div class="left_col"></div>
            <div class="right_col">

                <div class="rc_bg">
                    <div class="right_col_headline">
                        <h2>Das Unternehmen</h2>
                    </div>
                    <div class="right_col_content">
                        <p>BlackTri Media wurde 2010 von Eckhard Schneider in Hamburg gegründet. Eckhard ist seit 1995 im Bereich Web-Entwicklung 
                            für Medienunternehmen und Agenturen tätig, darunter Gruner + Jahr, Pixelpark und Interone, zuletzt bis 2010 </p>
                    </div>
                    <div class="right_col_content">
                        <p>als CTO und Geschäftsführer von Interone. Seit 2010 ist er selbständig, betreibt zusammen mit einem internationalen 
                            Team die Plattform BlackTri Optimizer und berät als IT-Consultant mehrere Startups und Agenturen.</p>
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="rc_bg">
                    <div class="right_col_headline">
                        <h2>Warum wir BlackTri Optimizer entwickelt haben</h2>
                    </div>
                    <div class="right_col_content">
                        <p>BlackTri Optimizer ist aus dem Bedarf entstanden, für Kundenprojekte eine einfach zu bedienende Lösung für A/B-Testing 
                            und Targeting zu haben - es sollte das Tool sein, mit dem wir selber gerne Conversion-Optimierung durchführen würden. </p>
                    </div>
                    <div class="right_col_content">
                        <p>Die verfügbaren Tools waren entweder teuer, unflexibel oder schwer zu bedienen. Unser Ziel ist es, die beste Lösung für Betreiber von Shops jeder Größe, Internetmarketern und Online-Agenturen bereitzustellen!</p>
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="rc_bg">
                    <div class="right_col_headline">
                        <h2>Woher kommt der Name "BlackTri"?</h2>
                    </div>
                    <div class="right_col_content">
                        <p>BlackTri ist eine von 16 Färbungen von Australian Shepherd-Hütehunden. Solch einen Hund wollten Eckhard und seine 
                            Familie haben - mittlerweile ist es statt eines Aussies</p>
                    </div>
                    <div class="right_col_content">
                        <p>allerdings ein Elo mit Namen Bonny geworden. "BlackTri" hat zwar keinerlei Bezug zu A/B-Testing, aber der Name klingt doch super, oder?</p>
                    </div>
                    <div class="clear"></div>
                </div>

            </div>
        </div>
    </div>

    <!-- Newsletter subscription START -->
    <!-- 
      <div class="bottom_cols_newsletter" style="padding-bottom: 30px;">
        <div class="subscribe">
          <h2><?php echo $this->lang->line('large_nloptin_head'); ?></h2>
          <div class="subscribe_boxes">
            <div class="box_left">
              <p><?php echo $this->lang->line('large_nloptin_subline'); ?></p>
              <ul>
                <li><?php echo $this->lang->line('large_nloptin_bullet1'); ?></li>
                <li><?php echo $this->lang->line('large_nloptin_bullet2'); ?></li>
                <li><?php echo $this->lang->line('large_nloptin_bullet3'); ?></li>
              </ul>
            </div>
            <div class="box_right">
              <p><?php echo $this->lang->line('large_nloptin_cta'); ?></p>
              <div class="newsletter_form">
                <form method="post" action="http://www.aweber.com/scripts/addlead.pl">
    <input type="hidden" name="meta_web_form_id" value="1615064084" />
    <input type="hidden" name="meta_split_id" value="" />
    <input type="hidden" name="listname" value="bto_optin" />
    <input type="hidden" name="redirect" value="http://www.blacktri.com/pages/subc" id="redirect_740bbff242d3fa1540e122307d046b80" />
    
    <input type="hidden" name="meta_adtracking" value="bto_optin_about" />
    <input type="hidden" name="meta_message" value="1" />
    <input type="hidden" name="meta_required" value="email" />
    
    <input type="hidden" name="meta_tooltip" value="" />
                  <input name="email" type="text" class="submit_form_bg" />
                  <input name="Submit" type="submit" value="<?php echo $this->lang->line('large_nloptin_submit'); ?>" class="submit_form_button"/>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    -->
    <!-- Newsletter subscription END --> 

</div>
</div>
