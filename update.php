<?php
/**
 * This file performs an asyncronous update for a certain board.
 */

define('WP_USE_THEMES', false);

/** Loads the WordPress Environment and Template */
$wp_load = "../../../wp-load.php";
$count	 = 0;

while ( $count < 20 && !file_exists($wp_load) ) {
  $wp_load = '../' . $wp_load;
  $count++;
}
if ( !file_exists($wp_load) ) die('WP?');

require_once($wp_load);

if ( !isset($_REQUEST['board_id']) ) die('WP->MCBoards :: Wrong Call.');
$board_id 	= $_REQUEST['board_id'];
$blog_id	= '';

$force = isset($_REQUEST['force']) ? (bool) $_REQUEST['force'] : false;


// just in case we are working on a WordPress MultiSite installation
if ( isset($_REQUEST['blog_id']) && !empty($_REQUEST['blog_id']) && function_exists('switch_to_blog') ) {
	$blog_id = $_REQUEST['blog_id'];
	switch_to_blog($blog_id);	
}

$nonce		= $_REQUEST['nonce'];
//if ( !wp_verify_nonce( $nonce, 'mcboard-' . $board_id ) ) die('WP->MCBoards :: Busted!');

$boards = mcb_get_option(MCBoard::NAME_SLUG . '_boards');
if ( !isset($boards[$board_id]) || empty($boards[$board_id]) ) die('WP->MCBoards :: Board does not exists!');

$boards[$board_id]['update']['start'] = time();
update_option(MCBoard::NAME_SLUG . '_boards', $boards);

try {
	// tell the client the request has finished processing 
	header('Connection: close'); 
	
	@ob_end_clean(); 
	
	// continue running once client disconnects
	ignore_user_abort(); 
	
	ob_start();
	
	echo 'WP->MCBoards :: OK';
	
	$size = ob_get_length();
	header("Content-Length: $size");
	@ob_end_flush();
	
	flush();
	session_write_close();
	
	// script can run for up to one hour...
	if ( !ini_get('safe_mode') ) set_time_limit(3600);
	
	ini_set('memory_limit', '1024M') || 
	ini_set('memory_limit', '512M')  || 
	ini_set('memory_limit', '256M')  || 
	ini_set('memory_limit', '128M');

	clearstatcache();
	
} catch ( Exception $e ) {}

$board = $boards[$board_id];

// Is MailChimp responsive?
$options = get_option(MCBoard::NAME_SLUG);
$api = $options['api'];
	
require_once "mcboards.php";

$mailchimp 		= MCBoard::MailChimpClient($api);
if ( $mailchimp->errorCode )  die('WP->MCBoards :: Mailchimp?');

try {
	MCBoard::getBoardContent($board_id, $force);	// getBoardContent only brings the first page (page 0)
	
    $filters    = array(    'list_id' 		=> $board['list'], 
                            'status' 		=> 'sent', 
                            'folder_id' 	=> $board['folder'], 
                            'template_id' 	=> $board['template'], 
                            'type' 			=> $board['type'], 
                            'from_email' 	=> $board['email'] );

	if ( $board['entire_list'] ) $filters['uses_segment'] = false;
	
	// Get the UI for future AJAX call (used to get campaigns on-the-fly
	if ( !isset($options['cache']['api'][$api]['user_id']) || empty($options['cache']['api'][$api]['user_id']) ) {

		$account  	= $mailchimp->getAccountDetails();
		if ( $mailchimp->errorCode )  die('WP->MCBoards :: UID?');

		$options['cache']['api'][$api]['user_id'] = $account['user_id'];
		update_option(MCBoard::NAME_SLUG, $options);

	}
	$uid = $options['cache']['api'][$api]['user_id'];	

	$count 	= 0;
	$page 	= 1;	// page 0 was fetched by MCBoard::getBoardContent
	
	$count_limit = 50;
	$page_limit  = 10;
	if ( $force ) {	// Manual update updates a set twice larger than automatic updates 
			$count_limit *= 2;
			$page_limit  *= 2;	
	}
	
	do {
	    
		$batch = $mailchimp->campaigns($filters, $page, $board['count']);
		foreach ( $batch['data'] as $c ) {
			$hash 			= md5( $uid.'|'.$c['id'].'|'.$board['width'].'|'.$board['height'] );
			$transient_name = MCBoard::transientName("mcb_ca_{$hash}");
            $cache   		= get_transient($transient_name);
            if ( empty($cache) )  {
            	
            	$height = $board['height'];
            	if ( !$height || !is_numeric($height) ) {
            		$height = $options['height'];
            		if ( !$height || !is_numeric($height) ) {
            			$height = MCBoard::$default['height'];
            			if ( !$height || !is_numeric($height) ) $height = 1024;
            		}
            	}
            	
            	$width = $board['width'];
            	if ( !$width || !is_numeric($width) ) {
            		$width = $options['width'];
            		if ( !$width || !is_numeric($height) ) {
            			$width = MCBoard::$default['width'];
            			if ( !$width || !is_numeric($width) ) $width = 192;
            		}
            	}
            	
            	$board_item = MCBoard::getBoardItem($uid, $c['id'], $width, $height, $c, $force);
				if ( !empty($board_item) ) {            	
					$count++;
				}
            }
		}
		
		$page++;
	} while ( !empty($batch['data']) && $page < $page_limit && $count < $count_limit);	// Max: 10 pages or 50 campaigns
} catch ( Exception $e ) {}

$boards = mcb_get_option(MCBoard::NAME_SLUG . '_boards');
if ( !isset($boards[$board_id]) || empty($boards[$board_id]) ) die('WP->MCBoards :: Board does not exists!');

$boards[$board_id]['update']['last'] 	= time();
update_option(MCBoard::NAME_SLUG . '_boards', $boards);

function mcb_get_option($option) {
	global $wpdb;
	// Read option directly from the database
	// We can't use get_option because WP reads it from its cache 
	// (and given the asyncronous nature of this process, that's useless here)
	
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );
	
	$value = maybe_unserialize( $row->option_value );
	wp_cache_set( $option, $value, 'options' );
	
	return $value;
}

exit();