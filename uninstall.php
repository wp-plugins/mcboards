<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();
	
wp_clear_scheduled_hook('mcb_update_boards');

mcb_delete_transients();
            
delete_option( 'mcboards' );
delete_option( 'mcboards-cache');
delete_option( 'mcboards_boards');

$upload_dir = wp_upload_dir();
if ( is_dir($upload_dir['basedir'].'/mcboards/') ) {
	mcb_rmdir( $upload_dir['basedir'].'/mcboards' );
}


		
function mcb_delete_transients() {
	global $wpdb;
    
	$transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_mcb_%';" );

    foreach( $transients as $transient ) {
    	$key        = str_replace('_transient_', '', $transient);
		delete_transient($key);
    }
}


		
function mcb_rmdir($path) {
  return is_file($path) ? @unlink($path) : array_map('mcb_rmdir',glob($path.'/*') ) == @rmdir($path);
}