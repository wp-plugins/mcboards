<?php
/*
Plugin Name: MailChimp Campaigns Boards
Plugin URI: http://connect.maichimp.com/integrations/mcboards
Description: Transform your MailChimp Campaign Archive into a Pinterest-like board.
Author: MC_Will
Version: 1.04
Requires at least: 3.0
*/
	
// Include library
if(!class_exists('WpFramework_Base_0_6'))   include_once "library/wp-framework/Base.php";
if(!class_exists('WpFramework_Vo_Form'))    include_once "library/wp-framework/Vo/Form.php";
if(!class_exists('MCAPI'))                  include_once "library/MCAPI.class.php";

class MCBoard extends WpFramework_Base_0_6 {
		
		const NAME = 'MailChimp Campaigns Boards';
		const NAME_SLUG = 'mcboards';
		
		static $force = false;
		
		static $options = '';
		
		private static $clients = array();
		private static $lists   = array();
		private static $campaigns = array();
		
		private static $sidebar = '';
		
		static $default = array(
		    'api'    => '',
			'width'  => 192,
			'height' => 1024,
			'count'  => 6,
		);
		static $board_default = array(
		    'board_id'	=> '',
		    'width'  	=>192,
			'height' 	=> 1024, 
		    'count'  	=> 6, 
		    'list'   	=> '',
		    'entire_list' => 0,
		    'type'   	=> 'regular',
		    'folder' 	=> '',
		    'template' 	=> '',
		    'email'   	=> ''
		);
		/**
		 * Constructor
		 * 
		 * @return void
		 */
		public function __construct() {

			// Call parent constructor
			parent::__construct();

			// Define form handlers
			$this->load(array('Abstract', 'NotEmpty', 'Integer', 'Set', 'Email'), 'WpFramework_Validators_');
			
			// Settings Tab
			$validators = array();
			$validators['api'][] 	= new WpFramework_Validators_NotEmpty(__('This field is required.', self::NAME_SLUG));
			$validators['width'][]  = new WpFramework_Validators_NotEmpty(__('This field is required.', self::NAME_SLUG));
			$validators['height'][] = new WpFramework_Validators_NotEmpty(__('This field is required.', self::NAME_SLUG));
			$validators['count'][]  = new WpFramework_Validators_NotEmpty(__('This field is required.', self::NAME_SLUG));
			
			$validators['width'][]  = new WpFramework_Validators_Integer(__('Make sure this field is between 100 and 600.', self::NAME_SLUG), 100, 600);
			$validators['height'][] = new WpFramework_Validators_Integer(__('Make sure this field is between 150 and 1024.', self::NAME_SLUG), 150, 1024);
			$validators['count'][]  = new WpFramework_Validators_Integer(__('Make sure this field is between 1 and 10.', self::NAME_SLUG), 1, 10);
			
			$this->add_form_handler('save-settings', $validators, array(&$this, 'saveSettings'));
			
			// Edit Tab
			$validators = array();
			$validators['board_id'][] 	= new WpFramework_Validators_NotEmpty(__('This field is required.', self::NAME_SLUG));
			
			$validators['width'][]  = new WpFramework_Validators_Integer(__('Make sure this field is between 100 and 600.', self::NAME_SLUG), 100, 600);
			$validators['height'][] = new WpFramework_Validators_Integer(__('Make sure this field is between 150 and 1024.', self::NAME_SLUG), 150, 1024);
			$validators['count'][]  = new WpFramework_Validators_Integer(__('Make sure this field is between 1 and 50.', self::NAME_SLUG), 1, 50);
			$validators['type'][]   = new WpFramework_Validators_Set(__('This field should be: regular, plaintext, auto, or rss.', self::NAME_SLUG), array('regular','plaintext','rss','auto'));
			$validators['entire_list'][]   = new WpFramework_Validators_Set(__('This field should be: 0, or 1.', self::NAME_SLUG), array('0','1'));
			$validators['email'][]   = new WpFramework_Validators_Email(__('This field should be a valid email address.', self::NAME_SLUG));
						
			$this->add_form_handler('save-edit', $validators, array(&$this, 'saveEdit'));
			
			// Create a New Board Tab
			$boards = get_option(self::NAME_SLUG . '_boards');
			if ( !is_array($boards) ) $boards = array();
			
			$validators = array();
			$validators['board_id'][] 	= new WpFramework_Validators_NotEmpty(__('This field is required.', self::NAME_SLUG));
			
			$validators['width'][]  = new WpFramework_Validators_Integer(__('Make sure this field is between 100 and 600.', self::NAME_SLUG), 100, 600);
			$validators['height'][] = new WpFramework_Validators_Integer(__('Make sure this field is between 150 and 1024.', self::NAME_SLUG), 150, 1024);
			$validators['count'][]  = new WpFramework_Validators_Integer(__('Make sure this field is between 1 and 10.', self::NAME_SLUG), 1, 10);
			$validators['type'][]   = new WpFramework_Validators_Set(__('This field should be: regular, plaintext, auto, or rss.', self::NAME_SLUG), array('regular','plaintext','rss','auto'));
			$validators['entire_list'][]   = new WpFramework_Validators_Set(__('This field should be one of: 0, or 1.', self::NAME_SLUG), array('0','1'));
			$validators['email'][]   = new WpFramework_Validators_Email(__('This field should be a valid email address.', self::NAME_SLUG));
						
			$this->add_form_handler('save-new', $validators, array(&$this, 'saveNew'));
			
			MCBoard::$options = get_option(self::NAME_SLUG);
		}
		
		/**
		 * Add item to admin menu
		 *
		 * @return void
		 */
		public function action_admin_menu(){
			$plugin_page = $this->add_options_page('MCBoards Settings', 'MCBoards', self::USER_LEVEL_ADMINISTRATOR, self::NAME_SLUG, array(&$this, "adminPageShow"));
		}
		
		public function action_init() {
			wp_enqueue_script('jquery');
			
			$options = MCBoard::$options;
			
		    wp_register_script( 'mcboard', $this->plugin_url . '/assets/mcboards.js');
		    wp_enqueue_script('mcboard');
		
	        wp_register_style( 'mcboard',  $this->plugin_url . '/assets/mcboards.css' );
	        wp_enqueue_style( 'mcboard' );

	        wp_register_script( 'masonry', $this->plugin_url . '/assets/js/jquery.masonry.min.js', 'jquery');
			wp_enqueue_script('masonry');
			
		    add_shortcode( 'mcboard', array(__CLASS__,'shortcode' ));
		    
            wp_localize_script( 'mcboard', 'MCBoard', array( 
                                                            'ajaxurl' 				=> admin_url( 'admin-ajax.php' ),
                                                            'nonce' 				=> wp_create_nonce( 'mcboard-nonce' ),
            												'ajax_loading' 			=> $this->plugin_url . '/assets/img/ajax-loader.gif',
            
            												'strAreYouSure' 		=> __('Are you sure you want to delete this MCBoard?', MCBoard::NAME_SLUG),
            												'strNoMoreCampaigns' 	=> __('Sorry. No more campaigns here.', MCBoard::NAME_SLUG),
            												'strBoardExists'		=> __('Sorry, the Board ID must be unique. This one already exists.',MCBoard::NAME_SLUG),
            											)
                             );
	        
            add_action( 'wp_ajax_nopriv_mcboard-get-more-campaigns', 	array(__CLASS__,'getMoreCampaigns') );
            add_action( 'wp_ajax_mcboard-get-more-campaigns', 			array(__CLASS__,'getMoreCampaigns') );

            add_action( 'wp_ajax_nopriv_mcboard-delete-mcboard', 		array(__CLASS__,'deleteMCBoard') );
            add_action( 'wp_ajax_mcboard-delete-mcboard', 				array(__CLASS__,'deleteMCBoard') );

            add_action( 'wp_ajax_nopriv_mcboard-board-id-exists', 		array(__CLASS__,'checkMCBoard') );
            add_action( 'wp_ajax_mcboard-board-id-exists', 				array(__CLASS__,'checkMCBoard') );
            
            
		    self::$force = isset($_GET['force']) && $_GET['force'];

 
		    // Activate the cron job that will update the boards
		    add_action('mcb_update_boards', array(__CLASS__,'updateBoards'));
		    
			if ( !wp_next_scheduled( 'mcb_update_boards' ) ) {
				wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'mcb_update_boards');
			}
			
			//$this->deactivate();
		}
		
		/**
		 * Load stylesheet
		 * 
		 * @return void
		 */
		public function action_admin_print_styles() {
			if(isset($_GET['page']) && $_GET['page'] == self::NAME_SLUG) {
				$this->enqueue_style('wpframeworktest-style',  $this->plugin_url . '/assets/css/wpf.css', null, '1.0');
			}
		}
		
		/**
		 * Load javascript
		 * 
		 * @return void
		 */
		public function action_admin_print_scripts() {
			if(isset($_GET['page']) && $_GET['page'] == self::NAME_SLUG) {
				$this->enqueue_script('jquery');
				/*
                wp_enqueue_script("jquery-ui-core");
                wp_enqueue_script("jquery-ui-tabs");
                wp_enqueue_script("jquery-ui-mouse");
                wp_enqueue_script( 'jquery-ui-sortable' );
                */
				$this->enqueue_script('wpframeworktest-style',  $this->plugin_url . '/assets/script.js', array("jquery"), '1.0'); 
			}
		}
		
		/**
		 * Load settings page & handle form submit
		 *
		 * @return void
		 */
		public function adminPageShow(){
			$options 	= MCBoard::$options;			
			unset($options['cache']);
			
			$boards 		= get_option(self::NAME_SLUG . '_boards');
			$default_tab 	= count($boards) && !empty($options['api']) ? 'boards' : 'settings';
			
			// handling manual updates
			if ( isset($_GET['tab']) && $_GET['tab'] == 'update' && isset($_GET['board_id']) ) {
	        	if ( function_exists('get_current_blog_id') ) 
	        		$blog_id = get_current_blog_id();
				else {
					global $blog_id;
					$blog_id = absint($blog_id);
				}
				
				self::updateBoard($_GET['board_id'], $blog_id, true);
				$boards 		= get_option(self::NAME_SLUG . '_boards'); // self::updateBoard updates it so load it again
				
				$_GET['tab'] 		= 'boards';
			}
			
			$data['options']	= $options;
			$data['page'] 		= isset($_GET['tab']) && in_array($_GET['tab'], array('settings','boards', 'help', 'edit', 'new')) ? $_GET['tab'] : $default_tab;
			
			$ShowTabContentFunction = 'adminPageShowTab_'.$data['page'];
			if ( method_exists($this, $ShowTabContentFunction) ) call_user_func( array( $this, $ShowTabContentFunction ), &$data );
			
			// Validate fields and trigger form handler
			//$data['validation'] = $this->handle_form();
			if ( !isset($data['wpform']) ) $data['wpform'] = $this->auto_handle_forms($data['options']);

			// Make sure the data is secure to display
			$clean_data = $this->clean_display_array($data);
			
			$instance = array();
			if ( in_array($data['page'], array('edit','new') ) ) {
				foreach ( array_keys(self::$board_default) as $param ) {
					$instance[$param] = isset($_REQUEST[$param]) ? $_REQUEST[$param] : '';
				}
			}
			
			$instance = wp_parse_args( (array) $instance, self::$board_default );
			$validation_results = $this->validate_fields($instance, $this->_form_validators);
			$data['wpform'] = new WpFramework_Vo_Form($instance, $validation_results);
				
			// Load view
			// The tab to show is handled in the index.php view
			$this->load_view($this->plugin_path . "/views/plugin-index.php", $clean_data);
		}
		
		/**
		 * Load settings for the Boards tab
		 *
		 * @param array $data
		 * @return void
		 */		
		public function adminPageShowTab_boards( &$data ) {
			if ( !class_exists('WP_List_Table') ) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
			if (  class_exists('WP_List_Table') ) require_once( $this->plugin_path . '/library/wptable/boards.php' );
			
			$data['boards'] 	= new Board_List_Table(self::NAME_SLUG);
		}
		
		/**
		 * Load settings for the Edit tab
		 *
		 * @param array $data
		 * @return void
		 */		
		public function adminPageShowTab_edit( &$data ) {
			$options 	= MCBoard::$options;
			unset($options['cache']);
			
			$api 		= $options['api'];
			
			$data['lists'] 		= self::getLists($api);
			$data['folders'] 	= self::getFolders($api);
			$data['templates'] 	= self::getTemplates($api);
			
			$board	    = array( 
										'board_d' => '', 
										'count' => self::$default['count'], 
										'width' => self::$default['width'], 
										'height' => self::$default['height'],
									);
												
			$boards = get_option(self::NAME_SLUG . '_boards');
			if ( isset($_REQUEST['board_id']) && is_array($boards) && !empty($boards) ) {
				$board = $boards[$_REQUEST['board_id']];				
				$board['board_id'] 	= $_REQUEST['board_id'];
			}
			$data['wpform'] = $this->auto_handle_forms($board);			
		}
		
		/**
		 * Load settings for the Edit tab
		 *
		 * @param array $data
		 * @return void
		 */		
		public function adminPageShowTab_help( &$data ) {
			// nothing to declare yet...
		}
		
		/**
		 * Load settings for the Create a New Board tab
		 *
		 * @param array $data
		 * @return void
		 */		
		public function adminPageShowTab_new( &$data ) {
			$options 	= MCBoard::$options;
			unset($options['cache']);
			
			$api 		= $options['api'];
			
			$data['lists'] 		= self::getLists($api);
			$data['folders'] 	= self::getFolders($api);
			$data['templates'] 	= self::getTemplates($api);
			
			$board	    = array( 
										'board_d' => '', 
										'count' => self::$default['count'], 
										'width' => self::$default['width'], 
										'height' => self::$default['height'],
									);
			
			$data['wpform'] = $this->auto_handle_forms($board);			
		}
		
		/**
		 * Handle save settings
		 *
		 * @param array $data
		 * @return void
		 */
		public function saveSettings(&$form) {
			$this->save_option(self::NAME_SLUG, $form->getFields());
		}
		
		public function saveEdit(&$form) {
			$id 		= sanitize_title($form->getField('board_id'));			
			$new_board 	= $form->getField('new_board');
						
			$board  = array();
			
			$boards = get_option(self::NAME_SLUG . '_boards');
			if ( !empty($id) && isset($boards[$id]) )  $board = $boards[$id];
			
			foreach ( array( 'width','height', 'count', 'list','entire_list','type','folder','template','email' ) as $field ) {
				$value 			= $form->getField($field);
				unset($board[$field]);
				if ( !empty($value) || $field == 'entire_list' ) $board[$field] = $value;
			}
			
			$boards[$id] = $board;
						
			update_option(self::NAME_SLUG . '_boards', $boards);
		}

		public function saveNew(&$form) {
			$id 		= sanitize_title($form->getField('board_id'));			

			$board  = array();
			
			foreach ( array( 'width','height', 'count', 'list','entire_list','type','folder','template','email' ) as $field ) {
				$value 			= $form->getField($field);
				unset($board[$field]);
				if ( !empty($value) ) $board[$field] = $value;
			}
			
			$boards = get_option(self::NAME_SLUG . '_boards');
			$boards[$id] = $board;
						
			update_option(self::NAME_SLUG . '_boards', $boards);
		}
		
		static function getLists($api) {
			$lists 	= array();
			
			$options 	= MCBoard::$options;			
			$api 		= $options['api'];
			$mailchimp 	= self::MailChimpClient($api);
			
			if ( !$mailchimp->errorCode ) {
				$hash 			= md5($api);
				$transient_name = MCBoard::transientName("mcb_ls_$hash");
				$cache   		= get_transient($transient_name);
				if ( empty($cache) ) {
					$lists 	= array();
					$page   = 0;
					do {
						$batch = $mailchimp->lists( array(), $page, 100);
						if ( empty($batch['data']) ) break;
						
						$lists = array_merge($lists, $batch['data']);
							
						$page++;
					} while ( count($lists) < 100 );
					
					if ( !empty($lists) ) set_transient( $transient_name, $lists, 25 * 86400 ); // let's cache it a day...
				} else {
					$lists = $cache;
				}
			}
			
			return $lists;
		}

		static function getFolders($api) {
			$folders 	= array();
			
			$options 	= MCBoard::$options;			
			$api 		= $options['api'];
			$mailchimp 	= self::MailChimpClient($api);
			
			if ( !$mailchimp->errorCode ) {
				$hash 			= md5($api);
				$transient_name = MCBoard::transientName("mcb_fs_$hash");
				$cache   		= get_transient($transient_name);
				if ( empty($cache) ) {
					$folders= array();
					foreach ( array('campaign','autoresponder') as $folder_type) {
						$batch = $mailchimp->folders($folder_type);
						if ( !empty($batch) ) $folders[$folder_type] = $batch;
					}
					
					if ( !empty($folders) ) set_transient( $transient_name, $folders, 25 * 86400 ); // let's cache it for 25 days...
				} else {
					$folders = $cache;
				}
			}
			
			return $folders;
		}
		
		static function getTemplates($api) {
			$templates 	= array();
			
			$options 	= MCBoard::$options;			
			$api 		= $options['api'];
			$mailchimp 	= self::MailChimpClient($api);
			
			if ( !$mailchimp->errorCode ) {
				
				$hash 			= md5($api);
				
				// User Templates, active or inactives, should be cached for one day.
				$transient_name = MCBoard::transientName(("mcb_ut_$hash"));
				$cache   		= get_transient($transient_name);
				if ( empty($cache) ) {
					$cache = array();
					
					// User templates
					$batch = $mailchimp->templates( array('user'=>true) );
					if ( !empty($batch['user']) ) $cache = $batch;
					
					//inactive templates
					$batch = $mailchimp->templates(array('user'=>true), array('include' => true, 'only' => true));
					if ( !empty($batch['user']) ) $cache['inactive'] = $batch['user'];
					
					if ( !empty($cache) ) set_transient( $transient_name, $cache, 86400 ); // let's cache it for a day...
				}
				$templates = $cache;
				
				// MailChimp Templates should be cached for about a month 
				$transient_name = MCBoard::transientName("mcb_mc_templates");
				$cache   		= get_transient($transient_name);
				if ( empty($cache) ) {
					$cache = array();
					
					// User templates
					$batch = $mailchimp->templates(array('gallery'=>true, 'base' => true));
					if ( !empty($batch['gallery']) ) $cache['gallery'] 	= $batch['gallery'];
					if ( !empty($batch['base']) ) 	 $cache['base'] 	= $batch['base'];
										
					if ( !empty($cache) ) set_transient( $transient_name, $cache, 4 * 7 * 86400 ); // let's cache it for four weeks...
				}
				if ( isset($cache['gallery']) ) $templates['gallery'] 	= $cache['gallery'];
				if ( isset($cache['base']) ) 	$templates['base'] 		= $cache['base'];
			}
			
			return $templates;
		}
		
		static function getBoardContent($board_id, $force = false) {
			$defaults =  array(
			        'id'		=> '',
					'api' 		=> '',
					'list' 		=> '',
					'folder' 	=> '',
					'template' 	=> '',
					'email' 	=> '',
					'type' 		=> 'regular',
					'count' 	=> 10,
					'entire_list' => 0,
					'width' 	=> 192,
					'uid' 		=> '',
			);

			// Does the required board_id exists?
			$boards = get_option(self::NAME_SLUG . '_boards');
			if ( !isset($boards[$board_id]) || empty($boards[$board_id]) ) return '';
            
			// Is MailChimp responsive?
			$options = MCBoard::$options;
			$api = $options['api'];
			
		    $mailchimp 		= self::MailChimpClient($api);
			if ( $mailchimp->errorCode ) return '';
			
			// Everything looks chimpy. Go ahead.
			$board 		 = array_merge($defaults, $boards[$board_id]);  
            $board['id'] = $board_id;
            
			// Make sure the Campaign Type is a valid one.
			$board['type'] = strtolower($board['type']);
			if ( !in_array($type, array('rss','regular', 'auto', 'plaintext')) ) $board['type'] = 'regular';
			
			// Is the content of this board in the cache?			
			unset($board['update_time']);
            $hash = md5( implode('|', $board ) );

            $transient_name = MCBoard::transientName("mcb_sc_{$hash}");
		    $content   = get_transient($transient_name);
		    if ( !$force && !empty($content) ) return $content;
			$content = '';

			// Get the campaigns to show, based on the user requirements.
			$campaigns  = array();		

		    $filters    = array(    'list_id' 		=> $board['list'], 
		                            'status' 		=> 'sent', 
		                            'folder_id' 	=> $board['folder'], 
		                            'template_id' 	=> $board['template'], 
		                            'type' 			=> $board['type'], 
		                            'from_email' 	=> $board['email'] );

			if ( $board['entire_list'] ) $filters['uses_segment'] = false;
		    
			$batch = $mailchimp->campaigns($filters, 0, $board['count']);
			if ( !empty($batch['data']) ) $campaigns = array_merge($campaigns, $batch['data']);		    
			if ( empty($campaigns) )  return '';

			// Get the UI for future AJAX call (used to get campaigns on-the-fly
			if ( !isset($options['cache']['api'][$api]['user_id']) || empty($options['cache']['api'][$api]['user_id']) ) {
	
				$account  	= $mailchimp->getAccountDetails();
				if ( $mailchimp->errorCode ) return ''; // uid couldnt be found. Nothing can be shown.
	
				$options['cache']['api'][$api]['user_id'] = $account['user_id'];
				update_option(self::NAME_SLUG, $options);
	
			}
			$uid = $options['cache']['api'][$api]['user_id'];	
			self::cacheAPIKey($uid, $api);
		    $board['uid'] = $uid;
		    
			// Creating the content for each board item			
			$height = 0; // whole campaign (actually, up to 10.000px)
			
			foreach($campaigns as $c) {
				$content .= self::getBoardItem($uid, $c['id'], $board['width'], $board['height'], $c, $force);
			}
			
			if ( !empty($content) ) {
			    $content = '
			    <div id="'.$board_id.'" class="mcb_container">'.$content.'
			    </div>
			    <div style="clear:both;"></div>
			    <div id="mcb_loading-'.$board_id.'" class="mcb_loading">&nbsp;</div>
			    <div style="clear:both;"></div>
			    <a id="mcb_more-'.$board_id.'" class="mcb_more_campaigns" href="#">'.__('More', MCBoard::NAME_SLUG).'</a>
			    <script>
                    jQuery(document).ready(function($) {
                        var $container = jQuery("#'.$board_id.'");
                        
						$container.imagesLoaded(function(){
	                        $container.masonry({
	                            itemSelector: ".mcb_item",
	                            isAnimated: true,
							    animationOptions: {
							      duration: 750,
							      easing: "linear",
							      queue: false
							    }
	                        });
	                    });
	                    
                        jQuery("#'.$board_id.':hover .mcb_item:not(:hover)").live("mouseenter", function () {
                        	jQuery.each(jQuery(this), function () {
	                        	jQuery(this).css("filter","alpha(opacity=40)");
	                        	jQuery(this).css("-moz-opacity","0.40");
	            	            jQuery(this).css("opacity","0.40");
							});
						});
						
					});
			    </script>
			    <style>
					#'.$board_id.':hover .mcb_item:not(:hover) {
					    filter:alpha(opacity=40);
					    -moz-opacity: 0.4;
					    opacity: 0.4;
					}
				</style>
				<div style="clear:both;"></div>';
			    
			    set_transient($transient_name, $content, 3600 ); // let's cache it 60 minutes...
			}
			
			return $content;
		}
		
		static function getBoardItem($uid, $cid, $width, $height = 0, $c = '', $force = false) {
		    $hash = md5( $uid.'|'.$cid.'|'.$width.'|'.$height );

            $transient_name = MCBoard::transientName("mcb_ca_{$hash}");
            $cache   = get_transient($transient_name);
            if ( !$force && !empty($cache) )  return $cache;
			$cache = '';
			
            $options = MCBoard::$options;
			$api = $options['api'];
			
	        $dc = 'us1';
			if (strstr($api,"-")){
				list($dummy, $dc) = explode("-",$api,2);
				if (!$dc) $dc = 'us1';
			}
		
            if ( empty($c) ) {
			    $mailchimp 		= self::MailChimpClient($api);
				if ( $mailchimp->errorCode ) return '';
				
				$c = $mailchimp->campaigns(array('campaign_id' => $cid));
				
				if ( $mailchimp->errorCode || !isset($c['total']) || $c['total'] <= 0 ) return '';
				 
				$c = $c['data'][0];
            }
			
	        $archive_url = self::followRedirect($c['archive_url']);
	        if ( empty($archive_url) )  {
				$archive_url = sprintf('http://%s.campaign-archive.com/?u=%s&id=%s', $dc, $uid, $c['id'] );
	        }

	        $upload_dir = wp_upload_dir();
	        
	        $screenshot_hash 	= md5( $uid.'|'.$cid.'|'.$width );
	        $screenshot 		= self::getLocalScreenshot( $screenshot_hash );
	        if ( $force || empty($screenshot) || !file_exists( $upload_dir['baseurl'] . '/'.self::NAME_SLUG.'/' . $uid.'-'.$c['id'].'-'.$width . '.jpg' ) ) {

	        	self::$force 	= !empty($screenshot);
		        $remote_screenshot = MCBoard::getRemoteScreenshot($archive_url, $width, 0, $dc);
		        self::$force 	= false;
		        
		        $screenshot     = '';

		        if ($upload_dir) {
		            if ( !is_dir($upload_dir['basedir'] . '/'.self::NAME_SLUG.'/') ) mkdir( $upload_dir['basedir'] . '/'.self::NAME_SLUG.'/', 0777, true );
	            	$response = wp_remote_get( $remote_screenshot );
					if( !is_wp_error( $response ) && $response['response']['code'] == 200) {
		            	$fp = fopen($upload_dir['basedir'] . '/'.self::NAME_SLUG.'/' . $uid.'-'.$c['id'].'-'.$width . '.jpg', "w");
						fwrite($fp, $response['body']);
                        fclose($fp);
                        
		            	$size = getimagesize($upload_dir['basedir'] . '/'.self::NAME_SLUG.'/' . $uid.'-'.$c['id'].'-'.$width . '.jpg');
			            $screenshot = $upload_dir['baseurl'] . '/'.self::NAME_SLUG.'/' . $uid.'-'.$c['id'].'-'.$width . '.jpg';
	        		    self::saveLocalScreenshot($screenshot_hash, $screenshot);
					}
        	    }
        	    if ( empty($screenshot) ) $screenshot = $remote_screenshot;
        	    
	        }
	        	        
	        $size = isset($size[3]) ? $size[3] : 'width="'.$width.'"';
	        
                        $date = strtotime($c['send_time']) + (get_option('gmt_offset') * 3600);
                        $time = date(get_option('time_format'), $date);
                        $date = ucfirst( date(get_option('date_format'), $date));

	        $info_template = '*|DATE|* *|TIME|*: *|SUBJECT|*';
	        
	        $subject  = self::removePortion($c['subject'], '*|', '|*');
	        $search   = array('*|DATE|*','*|TIME|*','*|SUBJECT|*');
	        $replace  = array($date, $time, addslashes($subject));
	        
	        $info_template = str_replace($search, $replace, $info_template);
	        
	        $likeit 	= 'http://'.$dc.'.campaign-archive.com/?u='.$uid.'&id='.$cid.'&fblike=true&e=[UNIQID]&socialproxy='.urlencode('http://'.$dc.'.campaign-archive.com/social-proxy/facebook-like?u='.  $uid.'&id='.$cid.'&url='.$archive_url.'&title='.urlencode(addslashes($subject)));
	        $plusit		= 'http://'.$dc.'.campaign-archive.com/?u='.$uid.'&id='.$cid.'&socialproxy='.						urlencode('http://'.$dc.'.campaign-archive.com/social-proxy/google-plus-one?u='.$uid.'&id='.$cid.'&url='.$archive_url.'&title='.urlencode(addslashes($subject))).'&gpo=true';
	        
	        $tweetit 	= 'http://twitter.com/share?url='.urlencode($c['archive_url']).'&text='.urlencode(addslashes($subject)).'&count=none';
	        $pinit 		= 'http://pinterest.com/pin/create/button/?url='.urlencode($c['archive_url']).'&media='.urlencode(MCBoard::getRemoteScreenshot($archive_url, 450, 0, $dc)).'&description='.urlencode(addslashes($subject));

	        $cache 		= '
	            <div class="mcb_item" style="max-width: '.($width+8).'px !important;" >
	               <div class="mcb_item_img" style="max-height: '.$height.'px !important;">
	               		<div class="mcb_item_social mcb_hidden">
		                    <a target="_social" href="'.$pinit.'" class="pin-it-button" count-layout="none">
		                    	<img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" />
		                    </a><br/>
		                    <a target="_social" target="_social" href="'.$likeit.'"  class="like-it-button" title="Like '.addslashes(addslashes($subject)).' on Facebook" rel="socialproxy" id="fblike-">
			                    <img src="http://cdn-images.mailchimp.com/fb/like.gif" border="0" alt="Like Bill Received on Facebook" height="20" width="48" style="display:inline;"/>
		                    </a><br/>
		                    <a target="_social" href="'.$tweetit.'" class="tweet-it-button" count-layout="none">
		                    	<img border="0" src="http://cdn-images.mailchimp.com/social_connect_tweet.png" title="Tweet It" />
		                    </a><br/>
		                    <a target="_social" href="'.$plusit.'" class="plus-it-button" count-layout="none">
		                    	<img border="0" src="http://cdn-images.mailchimp.com/google-plusone.png" title="+1" />
		                    </a>
		                </div>		                    
	                    <a href="'.$c['archive_url'].'" title="'.$info_template.'" target="_mcwindow"><img class="fade-in" src="'.$screenshot.'" '.$size.' border="0" /></a>
	               </div>
	           </div>';
	           
	        set_transient($transient_name, $cache, 365 * 86400); // let's cache it a year...

            return $cache;
		}

		static function getFieldDescription($field, $value) {
			
			$options 	= MCBoard::$options;
			$api 		= $options['api'];
			$mailchimp 	= self::MailChimpClient($api);
			if ( !$mailchimp->errorCode ) {
				switch ( $field ) {
					case 'list':
						$response = $mailchimp->lists( array( 'list_id' => $value ) );
						if ( !$mailchimp->errorCode ) {
							$value = $response['data'][0]['name'];
						}
						break;
					case 'folder':
						$response = $mailchimp->folders('campaign');
						if ( $mailchimp->errorCode ) break;
						
						foreach ( $response as $folder ) {
							if ( $folder['folder_id'] == $value) {
								$value = $folder['name'];
								break 2;
							}
						}
						
						$response = $mailchimp->folders('autoresponder');
						if ( $mailchimp->errorCode ) break;
						
						foreach ( $response as $folder ) {
							if ( $folder['folder_id'] == $value) {
								$value = $folder['name'];
								break 2;
							}
						}
						break;
					case 'template':
						$templates = self::getTemplates($api);
						
						foreach ( $templates as $template_type => $ts ) {
							foreach ( $ts as $t ) {
								if ( $t['id'] == $value ) {
									$value = $t['name'];
									break 3;
								}
							}
						}
						
						break;
					case 'type': 	// campaign-type
						switch ( $value ) {
							case 'plaintext':
								$value = 'Plain-Text';
								break 2;
							case 'rss':
								$value = 'RSS-to-Email';
								break 2;
							case 'auto':
								$value = 'Autoresponder';
								break 2;
							case 'regular':
							default:
								$value = 'Regular';
								break 2;
						}
						break;
					case 'entire_list':
						$value = $value ? __('Yes', self::NAME_SLUG) : __('No', self::NAME_SLUG);
						break;
				}
			}
			
			return $value;
		}
		
		/**
		 * Add a settings link to the plugin overview page
		 * 
		 * @param array $links
		 * @return array
		 */
		public function filter_plugin_action_links($links){
			$settings_link = '<a href="options-general.php?page=' . self::NAME_SLUG . '">' . __('Settings', self::NAME_SLUG).'</a>'; 
  			array_unshift($links, $settings_link); 
			return $links;
		}
		
		/**
		 * Delete option when the plugin is deactivated
		 * Clean up your garbage!
		 * 
		 * @return void
		 */
		public function deactivate() {
		    
			wp_clear_scheduled_hook('mcb_update_boards');
			
		}
		
		/**
		 * Plugin activation processes.
		 *
		 * @return void
		 */
		public function activate() {
			$options 	= MCBoard::$options;
			
			if ( empty($options) ) {
				$options = MCBoard::$default;			
				update_option(self::NAME_SLUG, $options);
			}
			
			MCBoard::$options = $options;
		}
		
        static function loadAjaxFunction($name) {
        	include_once "assets/ajax/$name.php";
        	exit();
        }
        
        static function getMoreCampaigns() {
            self::loadAjaxFunction('getMoreCampaigns');
        }
        
        static function deleteMCBoard() {
            self::loadAjaxFunction('deleteMCBoard');
        }
		
        static function checkMCBoard() {
        	self::loadAjaxFunction('checkMCBoard');
        }
        
		function shortcode( $atts ) {
			extract( shortcode_atts(array('id' => ''), $atts) );
			if ( empty($id) ) return '';
			
			return self::getBoardContent($id);
		}
				
		private function getRemoteScreenshot($url, $width = 192, $height = 0, $dc = 'us1') {
			list($uid,$cid) = self::getUIDandCIDFromURL($url);
			
			$image   = "http://mcboards.mailchimpapp.com/$dc-$uid-$cid-$width.jpg";
			if ( self::$force ) $image .= '?force=1';

			return $image;
		}
		
		static function getLocalScreenshot($hash, $show = 'screenshots') {
            if ( isset($_GET['force']) && $_GET['force'] ) return '';
            
		    $options = get_option(self::NAME_SLUG . '-cache');
		    if ( isset($options[$show][$hash]) ) return $options[$show][$hash];
		    
		    return '';
		}
		
		static function saveLocalScreenshot($hash, $content, $show = 'screenshots') {
		
		    $options = get_option(self::NAME_SLUG . '-cache');
		    $options[$show][$hash] = $content;
		    update_option(self::NAME_SLUG . '-cache', $options);
		}
        
        static function MailChimpClient($api_key) {
            if ( !isset(self::$clients[$api_key]) ) {
                self::$clients[$api_key] = new MCAPI($api_key);
            }
            if ( self::$clients[$api_key]->ping() != 'Everything\'s Chimpy!' ) {
                self::$clients[$api_key]->errorCode = -999;
                self::$clients[$api_key]->errorMessage = 'Can\'t connect to MailChimp. Sorry. Wrong API Key?';
            } else {
                self::$clients[$api_key]->errorCode = 0;
                self::$clients[$api_key]->errorMessage = '';
            }
            
            return self::$clients[$api_key];
        }

        static function cacheAPIKey($uid, $api_key) {
			$options = MCBoard::$options;
		    
		    if ( !empty($uid) && !empty($api_key) ) {
		        
			    // If needed, caching the relation between UID/API_KEY  for future ajax calls 
			    //              where we can't publish API Keys in the JS code (but we can publish UID and ListID)
			    if ( !isset($options['cache']['uid'][$uid]['api']) || !is_array($options['cache']['uid'][$uid]['api']) ) {
			        $options['cache']['uid'][$uid]['api'] = array();
			    }
			    if ( !in_array($api_key,$options['cache']['uid'][$uid]['api'] ) ) {
			        $options['cache']['uid'][$uid]['api'][] = $api_key;
				    update_option(self::NAME_SLUG, $options);
				    MCBoard::$options = $options;
			    }
		    }
		}
		
        static function getAPIKeyFromUID($uid) {
            $options = MCBoard::$options;
            
            $api_key = '';
		    if ( isset($options['cache']['uid'][$uid]['api']) && is_array($options['cache']['uid'][$uid]['api']) ) {
                $api_keys    = $options['cache']['uid'][$uid]['api'];
                
                foreach ( $api_keys as $index => $curkey ) {
        		    $mailchimp 	   = self::MailChimpClient($curkey);
                    if ( !$mailchimp->errorCode )  {  // if there's no error, it's a valid key so break
                        $api_key = $curkey;
                        break; 
                    }
                    // API Key is invalid. Delete it from cache.
                    unset($options['cache']['uid'][$uid]['api'][$index]);
	                update_option(self::NAME_SLUG, $options);
	                MCBoard::$options = $options;
                }
		    }
		    
		    return $api_key;
        }
        
	    static function getUIDandCIDFromURL($url) {
            $uid = ''; $cid = '';	    	
            
            $query_string = substr($url, strpos($url,'?')+1);
            
	    	$params = explode('&', $query_string );
	    	foreach ( $params as $param) {
	    		$param = explode('=',$param);

	    		if ( $param[0] == 'u' ) 						$uid = $param[1];
	    		if ( $param[0] == 'id' || $param[0] == 'amp;id' || $param[0] == '#038;id' ) 	$cid = $param[1];
    			
	    		if ( !empty($uid) && !empty($cid) ) break;
	    		
	    	}
            
	    	return array($uid, $cid);
        }
        
		/**
		 * Given a URL, this function return the final URL where an user would be taken (performing any 
		 * necessary redirect in the process)
		 *
		 * @param string $url
		 * @return string
		 */
        static function followRedirect($url) {
           $redirect_url = null;


           if (function_exists("curl_init")) {
              $ch = curl_init($url);
              curl_setopt($ch, CURLOPT_HEADER, true);
              curl_setopt($ch, CURLOPT_NOBODY, true);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              $response = curl_exec($ch);
              curl_close($ch);
           } else{
              $url_parts = parse_url($url);
              $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80));
              $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n";
              $request .= 'Host: ' . $url_parts['host'] . "\r\n";
              $request .= "Connection: Close\r\n\r\n";
              fwrite($sock, $request);
              $response = fread($sock, 2048);
              fclose($sock);
           }


           $header 	= "Location: ";
           $pos 	= strpos($response, $header);
           if ($pos === false) {
              return false;
           } else{
              $pos += strlen($header);
              $redirect_url = substr($response, $pos, strpos($response, "\r\n", $pos)-$pos);
              return $redirect_url;
           }
        }   
        
		/**
		 * Return a string where the content between the passed tags have been removed from the 
		 * original string. 
		 *
		 * @param string $template
		 * @param string $start_tag
		 * @param string $end_tag
		 * @return string
		 */
        static function removePortion($template, $start_tag, $end_tag = '') {
		    	    
    		$start = strpos($template,$start_tag);
    		while ( $start !== false) {
    		    $finish = false;
    			if ( !empty($end_tag) ) $finish = strpos($template, $end_tag, $start);
    			
    			if ( $finish !== false ) {
    				$finish += strlen($end_tag);
    				$template = substr_replace($template,'',$start, $finish - $start);
    			} else {
    				$template = substr_replace($template,'',$start);
    			}
    			$start = strpos($template,$start_tag);
    		}
    		
    		return $template;
        }

		/**
		 * This function is called by a WP cron job. It makes an asyncronous HTTP request to
		 * array( __CALL__, 'updateBoard' ) for every board that needs to get updated.  
		 *
		 * @return void
		 */
        static function updateBoards() {
        	try {
	        	if ( function_exists('get_current_blog_id') ) 
	        		$blog_id = get_current_blog_id();
				else {
					global $blog_id;
					$blog_id = absint($blog_id);
				}
				
	        	$boards = get_option(self::NAME_SLUG . '_boards');
				if ( empty($boards) ) return;
	            
				// Is MailChimp responsive?
				$options = MCBoard::$options;
				$api = $options['api'];
				
			    $mailchimp 		= self::MailChimpClient($api);
				if ( $mailchimp->errorCode ) return;
	        	
				foreach ( $boards as $board_id => $board ) {
					if ( !isset($board['update_time']) ) $board['update_time']  = 0;
					
					if (  $board['update_time'] < time() - ( 4 * 3600 ) ) {
						// if last update was at least four hours ago for this board...
						self::updateBoard($board_id, $blog_id);
					}
				}
        	} catch ( Exception $e) {
        	}
        }
		/**
		 * It makes an asyncronous HTTP request to array( __CLASS__, 'async_call' ) for 
		 * a certain board  
		 *
		 * @return void
		 */
        static function updateBoard($board_id, $blog_id = '', $force = false) {
			$boards = get_option(MCBoard::NAME_SLUG . '_boards');
			if ( !isset($boards[$board_id]) || empty($boards[$board_id]) ) return;
        	
			$boards[$board_id]['update']['start'] = time();
			update_option(MCBoard::NAME_SLUG . '_boards', $boards);
        	
        	$url = plugins_url( 'update.php', __FILE__ );
			self::asyncCall($url, array( 
										'blog_id' 	=> $blog_id,
										'board_id' 	=> $board_id,
										'force'		=> $force,
										'nonce' 	=> wp_create_nonce( 'mcboard-' . $board_id )
									));
        }

		/**
		 * This function makes an asyncronous HTTP request to $url passing $params as parameter
		 *
		 * @param string $url
		 * @param array $params
		 * @return bool
		 */
        static function asyncCall($url, $params = array()) {
		    foreach ($params as $key => &$val) {
				if (is_array($val)) $val = implode(',', $val);
				$post_params[] = $key.'='.urlencode($val);
		    }
		    $post_string = implode('&', $post_params);
		
		    $parts = parse_url($url);
		
		    $fp = fsockopen($parts['host'],
		        isset($parts['port']) ? $parts['port'] : 80,
		        $errno, $errstr, 30);
			
		    if ( !$fp ) return false;
		    
		    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
		    $out.= "Host: ".$parts['host']."\r\n";
		    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		    $out.= "Content-Length: ".strlen($post_string)."\r\n";
		    $out.= "Connection: Close\r\n\r\n";
		    
		    if (isset($post_string)) $out.= $post_string;
		
		    fwrite($fp, $out);
		    fclose($fp);
		    
		    return true;
		}
		
		static function transientName($name) {
			return substr($name,0,45);
		}
}

$GLOBALS[MCBoard::NAME_SLUG] = new MCBoard();
