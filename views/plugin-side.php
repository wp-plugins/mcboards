<div class="side">
    <div class="content">
    <?php 
    if ( file_exists($this->plugin_path . "/views/sidebar/$page.php") ) include($this->plugin_path . "/views/sidebar/$page.php");
    else { 
        include($this->plugin_path . "/views/sidebar/default.php");
    } ?>
    </div>
</div>
