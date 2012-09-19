<h2><?=__('Introduction', MCBoard::NAME_SLUG); ?></h2>
<div>
	<p><?=__('MCBoards is a different way to show your campaign archive. It displays your campaign links not just as a list of text links, but with actual screenshoots of the campaigns, in <a href="http://pinterest.com/" target="_blank">a way that is pretty popular these days</a>.',MCBoard::NAME_SLUG); ?></p>
	<p><?=__('You can define and include as many MCBoards as you need in any page or post, each one with its own properties and conditions. These conditions allow you to filter which campaigns to show in a certain board.',MCBoard::NAME_SLUG); ?></p>
	<p><?=__('The content will be automatically updated every hour or so, saving the screenshots in your own <em>UPLOADS/mcboards</em> folder.',MCBoard::NAME_SLUG); ?></p>
	<p><?=__('Once you\'ve created your boards, update their content and insert the provided shortcode in a page or post. That\'s it!',MCBoard::NAME_SLUG); ?></p>
</div>
<h2><?=__('Sections (or tabs)', MCBoard::NAME_SLUG); ?></h2>
<div>
    <?php include($this->plugin_path . "/views/sidebar/settings.php"); ?>
    <?php include($this->plugin_path . "/views/sidebar/boards.php"); ?>
    <?php include($this->plugin_path . "/views/sidebar/new.php"); ?>
    <?php include($this->plugin_path . "/views/sidebar/edit.php"); ?>
</div>
<h2><?=__('How to customize the board?', MCBoard::NAME_SLUG); ?></h2>
<div>
	<p><?=__('If you want to customize the output of this plugin, here\'s the layout. You can define you own CSS to modify it:',MCBoard::NAME_SLUG); ?></p>
	<pre>
	&lt;div class="<span style="color: brown">mcb_container</span>" id="<span style="color: blue">$board_id</span>"&gt;
		&lt;div class="<span style="color: brown">mcb_item</span>"&gt;
			&lt;div class="<span style="color: brown">mcb_item_img</span>"&gt;
				&lt;a target="_mcwindow" title="<span style="color: blue">$campaign_subject</span>" href="<span style="color: blue">$campaign_url</span>"&gt;
					&lt;img width="<span style="color: blue">$image_width</span>" height="<span style="color: blue">$image_height</span>" border="0" src="<span style="color: blue">$image_url</span>" /&gt;
				&lt;/a&gt;
			&lt;/div&gt;			
		&lt;/div&gt;			
	&lt;/div&gt;<span style="color: #777777">&lt;div style="clear:both;"&gt;&lt;/div&gt;</span>
	&lt;div class="<span style="color: brown">mcb_loading</span>" id="mcb_loading-<span style="color: blue">$board_id</span>"&gt;&amp;nbsp;&lt;/div&gt;<span style="color: #777777">&lt;div style="clear:both;"&gt;&lt;/div&gt;</span>
	&lt;a href="#" class="<span style="color: brown">mcb_more_campaigns</span>" id="mcb_more-<span style="color: blue">$board_id</span>"&gt;More Campaigns&lt;/a&gt;
	</pre>
</div>
<h2><?=__('Help! I need help!', MCBoard::NAME_SLUG); ?></h2>
<div>
	<p><?= sprintf(__('If you are struggling to make it work, please, visit the <a href="%s" target="_blank">FAQ</a> section of the plugin or send your questions to its <a href="%s" target="_blank">support forum</a>. It\'s the easiest way to get support.',MCBoard::NAME_SLUG),'http://wordpress.org/extend/plugins/mcboards/faq/','http://wordpress.org/support/plugin/mcboards'); ?></p>
</div>