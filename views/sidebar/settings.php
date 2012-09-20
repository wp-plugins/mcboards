<?php
$style = '';
if ( $page != 'help' ) $style = ' style="height: 525px; overflow: auto;"';
?><h3><?=__('How does it work?',MCBoard::NAME_SLUG); ?></h3>
<div <?php echo $style; ?>>
	<p><?=__('This plugin helps you show your Campaign Archive as an <em>Image Board</em>. These boards can be inserted into any page or post by using the following shortcode:',MCBoard::NAME_SLUG); ?></p>
	<code>[mcboard id="board_id"]</code>
	<p><?=__('Each board has a set of "conditionals" that must be met by any given campaign in order to be shown on the board. They filter your campaigns.',MCBoard::NAME_SLUG); ?></p>
	<p><?=__('Additionally, you can specify certain parameters such as the width of the images, their maximum height, and number of campaigns to show per page.',MCBoard::NAME_SLUG); ?></p>
	<p><?=__('Once a board is defined, its content must be <em>updated</em>. This is done by clicking its <em>update</em> link (found in the Boards tab), or when someone visits a page or post where this board is present. This is called a <em>front-end update</em>. It is a slow process that will negatively impact your visitors\' user experience. You should update the content of a board whenever you create it or update its properties or conditionals.',MCBoard::NAME_SLUG); ?></p>
</div>
