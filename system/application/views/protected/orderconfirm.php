<?php
$this->lang->load('order');
?>

<div id="main_container">

    <div class="whitebox">

        <div class="head_line_container">
            <div class="title">
                <div class="head_line_title"><?= $this->lang->line('order_title'); ?></div>
            </div>
            <div class="head_line_context">
                <h5><?= $this->lang->line('order_confirm_title'); ?> </h5>
                <p><br /><?= $this->lang->line('order_confirm_content'); ?></p>
            </div>
            <div class="error-message"><?= isset($errMsg) ? $errMsg : ''; ?></div>
        </div>

    </div>

</div>