<?php
    $options = get_option(self::NAME_SLUG);
	$nonce  = $_POST['nonce'];

	try {
		if ( !wp_verify_nonce( $nonce, 'mcboard-nonce' ) ) throw new Exception('Busted!');

		$id = isset($_POST['id']) ? $_POST['id'] : '';		
		if ( empty($id) ) throw new Exception('Parameter ID can\'t be blank.');
		
		$boards = get_option(self::NAME_SLUG . '_boards');
		if ( isset($boards[$id]) ) {			
			unset($boards[$id]);
			if ( !update_option(self::NAME_SLUG . '_boards', $boards) ) throw new Exception('MCBoard '.$id.' couldn\'t be deleted.'); 
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
	echo json_encode( array('status' => $status, 'response' => $response ) );
	exit();
