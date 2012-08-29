<?php
class Board_List_Table extends WP_List_Table {
	var $slug = '';
	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	 function __construct($slug) {
		 parent::__construct( array(
			'singular'=> 'wp_list_board', //Singular label
			'plural' => 'wp_list_boards', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
		) );
		
		$this->slug = $slug;
	 }

	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			//The code that goes before the table is here
			echo"";
		}
		if ( $which == "bottom" ){
			//The code that goes after the table is there
			echo"";
		}
	}
	
	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return array(
			'id'			=> __('ID', 		$this->slug),
			'width'			=> __('Image Width',$this->slug),
			'height'		=> __('Max-Height',	$this->slug),
			'count'			=> __('Page Size',	$this->slug),
			'conditionals'	=> __('Conditionals',$this->slug)
		);
	}
	
	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		return array(
						'id' => array( 'id', true )
					);
	}

	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		$screen = get_current_screen();
	
		/* -- the query -- */
			$boards = get_option($this->slug . '_boards');
			if ( !is_array($boards) ) $boards = array();
			
			// For Translators:
			$dummy = __('List',$this->slug).
					 __('Type',$this->slug).
					 __('Folder',$this->slug).
					 __('Template',$this->slug).
					 __('Email',$this->slug);
			
			foreach ( $boards as $board_id => $board ) {
				$conditionals = '';
				foreach ( $board as $field => $value ) {
					if ( in_array( $field, array('width','height', 'count','update_time', 'update') ) ) continue;
					
					if ( !empty($conditionals) ) {
						if ( $field != 'entire_list' || $value ) $conditionals .= ' ' .__('AND',$this->slug).'<br/>';	
					}
					if ( $field == 'entire_list' ) {
						if ( $value ) $conditionals .= __('<span class="mcb_conditional_field">Campaigns</span> were sent to the <span class="mcb_conditional_value">Entire List</span> (not a segment of it)',$this->slug);
					} else {
						$conditionals .= '<span class="mcb_conditional_field">'.__(ucfirst($field),$this->slug) . '</span> '.__('is',$this->slug).' "<span class="mcb_conditional_value">' . MCBoard::getFieldDescription($field,$value) .'</span>"';
					}
				}
				$boards[$board_id]['conditionals'] 	= $conditionals . (!empty($conditionals) ? '<br/><br/>' : '' ) . __('Shortcode:',$this->slug).' <input class="form-field regular-text" type="text" readonly="readonly" value="[mcboard id=&quot;'.$board_id.'&quot; /]" />';
			}
		/* -- Pagination parameters -- */
			$totalitems = count($boards);
	        //How many to display per page?
	        $perpage = 5;
	        //Which page is this?
	        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
		
		
		/* -- Ordering parameters -- */
		    //Parameters that are going to be used to order the result
		    $order 		= !empty($_GET["order"])   ? $_GET["order"]   : 'id';
	        $orderby 	= !empty($_GET["orderby"]) ? $_GET["orderby"] : 'ASC';
		    
		    if(!empty($orderby) && !empty($order)) {
		    	if ( strtoupper($order) == 'ASC' ) {
		    		ksort($boards);
		    	} else { 
		    		krsort($boards);
		    	} 
		    }
	        //Page Number
	        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
	        //How many pages do we have in total?
	        $totalpages = ceil($totalitems/$perpage);
	        
			
	        //adjust the query to take pagination into account
	        $offset = 0;
		    if(!empty($paged) && !empty($perpage)){
			    $offset=($paged-1)*$perpage;
		    }
			$boards = array_slice($boards, $offset, $perpage);
			
		/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );
			//The pagination links are automatically built according to those parameters
	
		/* -- Register the Columns -- */
			$this->_column_headers = array( 
										 $this->get_columns(),		// columns
										 $this->get_hidden_columns(),	// hidden
										 $this->get_sortable_columns(),	// sortable
									);								
			$this->items = $boards;
	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display_rows() {
		if ( function_exists('get_current_blog_id') ) 
	        $blog_id = get_current_blog_id();
		else {
			global $blog_id;
			$blog_id = absint($blog_id);
		}
		
		//Get the records registered in the prepare_items method
		$records = $this->items;
		
		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden, $sortables ) = $this->_column_headers;
		
		//Loop for each record
		if( !empty($records) ) {			
			foreach($records as $rec_id => $rec) {
				//Open the line
		        echo '<tr id="record_'.$rec_id.'">';
				foreach ( $columns as $column_name => $column_display_name ) {
		
					//Style attributes for each col
					$class = "class='$column_name column-$column_name'";
					
					$style = "";
										
					if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
					$attributes = $class . $style;
					
					//edit link
					$editlink  	= MCBoard::get_current_url(array('tab')) . '&tab=edit&board_id='.urlencode($rec_id);
					$updatelink = MCBoard::get_current_url(array('tab')) . '&tab=update&board_id='.urlencode($rec_id);
					
					$start_update_time 	= (isset($rec['update']['start']) && $rec['update']['start'] ) ? $rec['update']['start'] : 0;
					$last_update_time  	= (isset($rec['update']['last'] ) && $rec['update']['last'] )  ? $rec['update']['last']  : 0;					
					$last_update_msg   	= ( $last_update_time ) ? ucfirst( date(get_option('date_format'), $last_update_time + ( get_option( 'gmt_offset' ) * 3600 ))).' ' . date(get_option('time_format'), $last_update_time + ( get_option( 'gmt_offset' ) * 3600 )) : sprintf(__('<strong>Never</strong> <a href="%s">Do it now!</a>',$this->slug),$updatelink);
					
					$is_updating 		= false;
					if ( $start_update_time > $last_update_time ) {	// it's being updated right now
						if ( time() < ($start_update_time + 3 * 60) ) {	// less than two minutes ago...
							$last_update_msg = '<em>'.__('Updating...',$this->slug) . '</em>';
							$is_updating = true;
						}
					}
					$last_update_msg   = __('Last Update:',$this->slug) .' ' . $last_update_msg;
					
					$board_name 		= stripslashes($rec_id);
					if ( !$is_updating ) {
						$board_name = '<a href="'.$editlink.'" title="'.__('Edit',$this->slug).'">'.$board_name.'</a>'; 
					}
					switch ( $column_name ) {
						case "id":
							echo '<td '.$attributes.'>
								<strong>'.$board_name.'</strong>
								' . ( (!$is_updating) ? '<div class="row-actions">
									<span class="edit"><a title="'.__('Edit this board',$this->slug).'" href="'.$editlink .'">'.__('Edit',$this->slug).'</a> | </span>
									<span class="update"><a title="'.__('Update content',$this->slug).'" href="'.$updatelink .'">'.__('Update',$this->slug).'</a></span>
									<span class="trash" style="margin-left: 20px;">(<a href="#" title="'.__('Delete this board',$this->slug).'" class="submitdelete">'.__('Delete',$this->slug).'</a>)</span>
								</div>' : '<br/><br/>' ). $last_update_msg.'
							</td>';	
							break;
						case "width": 			echo '<td '.$attributes.'>'.stripslashes($rec['width']).'</td>'; break;
						case "height": 			echo '<td '.$attributes.'>'.stripslashes($rec['height']).'</td>'; break;
						case "count": 			echo '<td '.$attributes.'>'.stripslashes($rec['count']).'</td>'; break;
						case "conditionals": 	echo '<td '.$attributes.'>'.$rec['conditionals'].'</td>'; break;
					}
				}
				echo'</tr>';
			}
		}
	}
	
}