<?php $board = $wpform->getFields(); ?><h2><?php echo __('Create a New MCBoard', $this->plugin_name); ?></h2>
<div>
  <?php if( is_object($wpform) && property_exists($wpform, 'isSaved') && $wpform->isSaved()){ ?>
  	<div class="wpf_message success"><p><?php echo __('Changes were successfully saved.', $this->plugin_name); ?></p></div>
  <?php } ?>

    <div class="wpf_message warning legend-required"><?=__('<p>Fields marked with <span>*</span> are required.</p>', $this->plugin_name); ?></div>
    
	
    <h3><?=__('Settings', $this->plugin_name); ?></h3>
    <fieldset>
    	<input type="hidden" name="tab" value="edit">
        <legend><?=__('Define the general characteristics of this MCBoard.', $this->plugin_name); ?></legend>
        <ul class="field-holder">
        <li>
            <div class="form-label"><label for="board_id"><?php echo __('Board ID', $this->plugin_name) ?><span>*</span></label></div>
            <div class="form-field"><input type="text" id="board_id" name="board_id" value="<?php echo $board['board_id']; ?>" /></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('board_id') ? '<span class="error">' . $wpform->getErrors('board_id', 1) . '</span>' : __('Must be unique. Only letters and numbers, dashes, and underscores.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="width"><?php echo __('Image width', $this->plugin_name) ?></label></div>
            <div class="form-field"><input type="text" id="width" name="width" value="<?php echo $board['width']; ?>" /></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('width') ? '<span class="error">' . $wpform->getErrors('width', 1) . '</span>' : __('Image width for the campaign screenshots.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="height"><?php echo __('Maximum image height', $this->plugin_name) ?></label></div>
            <div class="form-field"><input type="text" id="height" name="height" value="<?php echo $board['height']; ?>" /></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('height') ? '<span class="error">' . $wpform->getErrors('height', 1) . '</span>' : __('Height where the images will be cropped if needed.', $this->plugin_name) ?></div>            
        </li>        
        <li>
            <div class="form-label"><label for="count"><?php echo __('Campaigns per page', $this->plugin_name) ?></label></div>
            <div class="form-field"><input type="text" id="count" name="count" value="<?php echo $board['count']; ?>" /></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('count') ? '<span class="error">' . $wpform->getErrors('count', 1) . '</span>' : __('Number of campaigns to show per page.', $this->plugin_name) ?></div>
        </li>
        </ul>
    </fieldset>

    <h3><?=__('Conditionals', $this->plugin_name); ?></h3>
    <fieldset>
        <legend><?=__('Define the criteria to select the campaigns to show.<br/>All conditions must be met in order to display a campaign.', $this->plugin_name); ?></legend>
        <ul class="field-holder">
        <li>
            <div class="form-label"><label for="list"><?php echo __('List', $this->plugin_name) ?></label></div>
            <div class="form-field"><?php 
            echo '<select id="list" name="list"><option value=""></option>';
	            foreach ($lists as $list) {
	            	echo '<option value="'.$list['id'].'" '.selected($board['list'],$list['id'],false).'>'.$list['name'].'</option>';
	            }
            echo '</select>';
            ?></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('list') ? '<span class="error">' . $wpform->getErrors('list', 1) . '</span>' : __('Include campaigns sent to this list.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="entire_list"><?php echo __('Sent to entire list?', $this->plugin_name) ?></label></div>
            <div class="form-field"><select id="entire_list" name="entire_list"><option value="0" <?php selected($board['entire_list'],0); ?>><?php echo __('No',$this->plugin_name); ?></option><option value="1" <?php selected($board['entire_list'],1); ?>><?php echo __('Yes',$this->plugin_name); ?></option></select></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('entire_list') ? '<span class="error">' . $wpform->getErrors('entire_list', 1) . '</span>' : __('If this setting is checked, any campaign sent to a segment of the list will be excluded.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="type"><?php echo __('Campaign Type', $this->plugin_name) ?></label></div>
            <div class="form-field"><?php echo '<select name="type" id="type"><option value=""></option><option value="regular" '.selected($board['type'],'regular',false).'>'.__('Regular Campaign', $this->plugin_name).'</option><option value="plaintext" '.selected($board['type'],'plaintext',false).'>'.__('Plain-Text Campaign', $this->plugin_name).'</option><option value="rss" '.selected($board['type'],'rss',false).'>'.__('RSS-to-Email Campaign', $this->plugin_name).'</option><option value="auto" '.selected($board['type'],'auto',false).'>'.__('Autoresponder Campaign', $this->plugin_name).'</option></select>'; ?></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('type') ? '<span class="error">' . $wpform->getErrors('type', 1) . '</span>' : __('Campaign type of the campaigns.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="folder"><?php echo __('Folder', $this->plugin_name) ?></label></div>
            <div class="form-field"><?php 
            
            // For translators:
            $dummy = __('Campaign Folders',$this->plugin_name).__('Autoresponder Folders',$this->plugin_name);
            
            echo '<select id="folder" name="folder"><option value=""></option>';
	            foreach ($folders as $folder_type => $fs) {
	            	echo '<optgroup label="'. __(sprintf('%s Folders', ucfirst($folder_type) ), $this->plugin_name) .'">';
	            	foreach ( $fs as $f ) {
	            		echo '<option value="'.$f['folder_id'].'" '.selected($board['folder'],$f['folder_id'],false).'>&nbsp;&nbsp;&nbsp;'.$f['name'].'</option>';
	            	}
	            	echo '</optgroup>';
	            }
            echo '</select>';
            ?></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('folder') ? '<span class="error">' . $wpform->getErrors('folder', 1) . '</span>' : __('Include campaigns archived in this folder.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="template"><?php echo __('Template', $this->plugin_name) ?></label></div>
            <div class="form-field"><?php 
            
            // For translators:
            $dummy = 	__('User Templates',$this->plugin_name).
            			__('Gallery Templates',$this->plugin_name).
            			__('Base Templates',$this->plugin_name).
            			__('Inactive Templates',$this->plugin_name);
            
            echo '<select id="template" name="template"><option value=""></option>';
            	foreach ( array('user','inactive','gallery','base') as $template_type) {
            		if ( !isset($templates[$template_type]) || empty($templates[$template_type]) ) continue;	
            	
	            	echo '<optgroup label="'. __(sprintf('%s Templates', ucfirst($template_type) ), $this->plugin_name) .'">';
	            	foreach ( $templates[$template_type] as $t ) {
	            		echo '<option value="'.$t['id'].'" '.selected($board['template'],$t['id'],false).'>&nbsp;&nbsp;&nbsp;'.$t['name'].' ('.ucfirst( date(get_option('date_format'), strtotime($t['date_created']))).')</option>';
	            	}
	            	echo '</optgroup>';
	            }
            echo '</select>';
            ?></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('template') ? '<span class="error">' . $wpform->getErrors('template', 1) . '</span>' : __('Template used by the campaigns.', $this->plugin_name) ?></div>
        </li>
        <li>
            <div class="form-label"><label for="email"><?php echo __('Sending Address', $this->plugin_name) ?></label></div>
            <div class="form-field"><input type="text" id="email" name="email" value="<?php echo $board['email']; ?>" /></div>
            <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('email') ? '<span class="error">' . $wpform->getErrors('email', 1) . '</span>' : __('Email address used to send the campaigns.', $this->plugin_name) ?></div>
        </li>
        </ul>
    </fieldset>
</div>
