<div class="mcbnews">
<h3 class="mcbnews_header"><?php _e('News from MailChimp...', MCBoard::NAME_SLUG); ?></h3><?php
$rss = fetch_feed('http://mailchimp.com/blog/feed');
if (!is_wp_error( $rss ) ) {
	$maxitems = $rss->get_item_quantity(5); 
    $rss_items = $rss->get_items(0, $maxitems); 
} ?>
	<ul>
    	<?php 
    	if ($maxitems == 0) 
    		echo '<li>No news!</li>';
        else
        	foreach ( $rss_items as $item ) { ?>
                <li>
                    <a href='<?php echo esc_url( $item->get_permalink() ); ?>'
                    title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
                    <?php echo esc_html( $item->get_title() ); ?></a>
                </li>
            <?php } ?>
	</ul>
</div><?php
$rss        = fetch_feed('http://blog.mandrill.com/feeds/all.atom.xml');
$maxitems   = 0;
if (!is_wp_error( $rss ) ) {
	$maxitems = $rss->get_item_quantity(5); 
    $rss_items = $rss->get_items(0, $maxitems); 
}
            
if ( $maxitems > 0 ) {
?>	    
    <div class="mcbnews mcb_mandrill">
    <h3 class="mcbnews_header"><?php _e('Latest from Mandrill...', MCBoard::NAME_SLUG); ?></h3>
            <ul>
                <?php
                foreach ( $rss_items as $item ) { ?>
                <li>
                    <a href='<?php echo esc_url( $item->get_permalink() ); ?>'
                    title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
                    <?php echo esc_html( $item->get_title() ); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div><?php 
} ?>
