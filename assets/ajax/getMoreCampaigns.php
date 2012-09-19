<?php
    $options = get_option(self::NAME_SLUG);
	$nonce  = $_POST['nonce'];

	try {
		if ( !wp_verify_nonce( $nonce, 'mcboard-nonce' ) ) throw new Exception('Busted!');

		$api = $options['api'];
			
		$mailchimp 		= MCBoard::MailChimpClient($api);
		if ( $mailchimp->errorCode )  throw new Exception('Can\'t connect to MailChimp.');
		
		$content 	= array();
		$board_id 	= $_POST['board_id'];
		$offset  	= isset($_POST['offset']) && is_numeric($_POST['offset']) ? $_POST['offset'] : 0;
		$boards 	= get_option(self::NAME_SLUG . '_boards');
		if ( !isset($boards[$board_id]) || empty($boards[$board_id]) ) throw new Exception('Invalid Board ID');
		
		$board = $boards[$board_id];

		if ( !isset($options['cache']['api'][$api]['user_id']) || empty($options['cache']['api'][$api]['user_id']) ) {
	
			$account  	= $mailchimp->getAccountDetails();
			if ( $mailchimp->errorCode )  die('WP->MCBoards :: UID?');
	
			$options['cache']['api'][$api]['user_id'] = $account['user_id'];
			update_option(MCBoard::NAME_SLUG, $options);
	
		}
		$uid = $options['cache']['api'][$api]['user_id'];			
		
	    $filters    = array(    'list_id' 		=> $board['list'], 
	                            'status' 		=> 'sent', 
	                            'folder_id' 	=> $board['folder'], 
	                            'template_id' 	=> $board['template'], 
	                            'type' 			=> $board['type'], 
	                            'from_email' 	=> $board['email'] );
	
		if ( $board['entire_list'] ) $filters['uses_segment'] = false;

		$page 	= absint( $offset / $board['count'] );
		
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
		
		$batch = $mailchimp->campaigns($filters, $page, $board['count']);
		foreach ( $batch['data'] as $c ) {
			
			$hash 			= md5( $uid.'|'.$c['id'].'|'.$board['width'].'|'.$board['height'] );
			$transient_name = MCBoard::transientName("mcb_ca_{$hash}");
            $cache   		= get_transient($transient_name);
            if ( empty($cache) )  {
            	$cache = MCBoard::getBoardItem($uid, $c['id'], $width, $height, $c);
            }
            
            $content[] = $cache;
		}
		
		$status   = 'success';
        $response = '';
	} catch ( Exception $e ) {
		$status = 'error';
		$response = $e->getMessage();
	}
	wp_create_nonce( 'mcboard-nonce' );
	
	ob_clean();

	header( "Content-Type: application/json" );
	echo json_encode( array('status' => $status, 'elements' => $content, 'response' => $response, 'scid' => $board_id ) );
	exit();
