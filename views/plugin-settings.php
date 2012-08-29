<h2><?=__('Default settings', $this->plugin_name); ?></h2>
<div>
  <?php if( is_object($wpform) && property_exists($wpform, 'isSaved') && $wpform->isSaved()){ ?>
  	<div class="wpf_message success"><p><?php echo __('Changes were successfully saved.', $this->plugin_name); ?></p></div>
  <?php } ?>

        <div class="wpf_message warning legend-required"><?=__('<p>Fields marked with <span>*</span> are required.</p>', $this->plugin_name); ?></div>

        <fieldset>
        <input type="hidden" name="tab" value="settings">
            <legend><?=__('Define the parameters that you will be commonly using in your boards. You can change them later from board to board.', $this->plugin_name); ?></legend>
            <ul class="field-holder">
                <li>
                    <div class="form-label"><label for="api"><?php echo __('MailChimp API Key', $this->plugin_name) ?><span>*</span></label></div>
                    <div class="form-field"><input type="text" id="api" name="api" value="<?php echo $wpform->getField('api'); ?>" /></div>
                    <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('api') ? '<span class="error">' . $wpform->getErrors('api', 1) . '</span>' : sprintf(__('Find it in your <a href="%s" target="_blank">MailChimp API Keys Dashboard</a>.', $this->plugin_name),'https://admin.mailchimp.com/account/api/'); ?></div>
                </li>
                <li>
                    <div class="form-label"><label for="width"><?php echo __('Image Width', $this->plugin_name) ?><span>*</span></label></div>
                    <div class="form-field"><input type="text" id="width" name="width" value="<?php echo $wpform->getField('width'); ?>" /></div>
                    <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('width') ? '<span class="error">' . $wpform->getErrors('width', 1) . '</span>' : __('This value should be between 100 and 600. It should be less than the column width.', $this->plugin_name) ?></div>
                </li>
                <li>
                    <div class="form-label"><label for="height"><?php echo __('Maximum Image Height', $this->plugin_name) ?><span>*</span></label></div>
                    <div class="form-field"><input type="text" id="height" name="height" value="<?php echo $wpform->getField('height'); ?>" /></div>
                    <div class="form-description wpfextra wpfsmall"><?php echo $wpform->hasErrors('height') ? '<span class="error">' . $wpform->getErrors('height', 1) . '</span>' : __('When a campaign is larger than this threshold, it will be cropped to this value. Should be an integer between 150 and 1024.', $this->plugin_name) ?></div>
                </li>
            </ul>
        </fieldset>

</div>
