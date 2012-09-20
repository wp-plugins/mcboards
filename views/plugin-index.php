<div class="wrap">
<div class="icon32" id="icon-options-general"><br></div>
<h2>MCBoards</h2>

<div class="wpframework ">
	<h2 class="nav-tab-wrapper">
        <?php
        	$board = $wpform->getFields();
        	
        	// If API KEY doesn't exists, show only the Settings tab 
        	$options = get_option(MCBoard::NAME_SLUG);
        	if ( isset($options['api']) && !empty($options['api']) ) {
        		$tabs = array('boards' => __('Boards', $this->plugin_name), 'settings' => __('Settings', $this->plugin_name));
        	} else {
        		$tabs = array('settings' => __('Settings', $this->plugin_name));
        		$page = 'settings';
        	}
        	        	
        	// Showing on-demand tabs (edit and new appear only when user click on the appropriate links)
        	if ( $page == 'new' ) {
        		$boards = get_option(MCBoard::NAME_SLUG . '_boards');
        		if ( isset($boards[$board['board_id']] ) ) {
        			$page = 'edit';
        		} else {
        			$tabs[$page] = __('Create a New MCBoard', $this->plugin_name);
        		}
        	}
        	
        	if ( $page == 'edit' ) {
        		$tabs[$page] =  __('Edit Board', $this->plugin_name); 
        	}
        	
        	$tabs['help'] = '<img src="'.$this->plugin_url.'/assets/img/help_icon.gif" border="0" width="16" height="16" /> ' . __('Help', $this->plugin_name);
        	
        	echo $this->getNavTabs($tabs, $page);

        	$form_trigger = 'save-'.$page;
        ?>
    </h2>

    <form method="post" action="" enctype="multipart/form-data">

        <div class="wpf_header">
<!--             <input name="reset" type="submit" value="Reset Options" class="button left" onclick="return confirm('Click OK to reset. Any settings will be lost!');"> -->
<?php 
	  if ( $page == 'edit' ) { ?>
			<a class="button left" href="<?php echo $this->get_current_url(array('tab')) . '&tab=new'; ?>"><?php echo __('Create a New MCBoard', $this->plugin_name); ?></a>
<?php } 
	  if ( $page == 'boards' ) {?>
			<a class="button-primary right" href="<?php echo $this->get_current_url(array('tab')) . '&tab=new'; ?>"><?php echo __('Create a New MCBoard', $this->plugin_name); ?></a>
<?php } elseif ( $page != 'help' ) {?>
            <input type="submit" value="<?=__('Save Changes',$this->plugin_name);?>" class="button-primary right" name="<?php echo $form_trigger; ?>" id="save">
<?php } ?>
        </div>

        <div class="wpf_content">

            <!-- MAIN COLUMN START -->
            <div class="container">
                <div class="content">
                <?php include('plugin-' . $page . ".php"); ?>
                </div>
            </div>
            <!-- MAIN COLUMN END -->

            <!-- SIDE COLUMN START -->
            <?php include("plugin-side.php"); ?>
            <!-- SIDE COLUMN END -->
        </div>

        <div class="wpf_footer">
<!--             <input name="reset" type="submit" value="Reset Options" class="button left" onclick="return confirm('Click OK to reset. Any settings will be lost!');"> -->
<?php 
	  if ( $page == 'edit' ) { ?>
			<a class="button left" href="<?php echo $this->get_current_url(array('tab')) . '&tab=new'; ?>"><?php echo __('Create a New MCBoard', $this->plugin_name); ?></a>
<?php } 
      if ( $page == 'boards' ) {?>
            <a class="button-primary right" href="<?php echo $this->get_current_url(array('tab')) . '&tab=edit'; ?>"><?php echo __('Create a New MCBoard', $this->plugin_name); ?></a>
<?php } elseif ( $page != 'help' ) {?>
            <input type="submit" value="<?=__('Save Changes',$this->plugin_name);?>" class="button-primary right" name="<?php echo $form_trigger; ?>" id="save">
<?php } ?>
        </div>
    </form>

</div>

</div>