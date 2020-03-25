<?php
if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


/* Hide notices to avoid AJAX errors
 * Sometimes the Class throws a notice about 'hook_suffix' being undefined,
 * which breaks every AJAX call.
 */


class age_restriction_Banners_List_Table extends WP_List_Table 
{
    private $parent = null;
    public $posts_list = array();
    public $table_cols = array();
    public $req = array();

	public $data = array();

	private $wpdb = null;
	public $config = array();
	public $_args = array();
	public $_pagination_args = array();
	
	/**
	 * REQUIRED. Set up a constructor that references the parent constructor. We 
	 * use the parent reference to set some default configs.
	 */
	public function __construct( $parent=null ) 
	{
		global $status, $page, $wpdb;
   
        if ( !is_null($parent) ) {
            $this->parent = $parent;
        }
        $settings = $this->parent->cfg['config_settings'];
 
		// This is used only if making any database queries
		$this->wpdb = $wpdb;
		
		// store database name
		$this->config['db_name'] = $this->wpdb->prefix . 'age_restriction_stats';
        
        // table listing columns
        $this->table_cols = $this->parent->get_stats_columns();
        //var_dump('<pre>', $this->table_cols, '</pre>'); die('debug...'); 
		
		// how many records per page to show
		$this->config['per_page'] = isset($settings['rows_per_page']) ? (int) $settings['rows_per_page'] : 5;
        
        // get row details from wp posts table
        $this->posts_list = $this->parent->get_banner_posts();
        //var_dump('<pre>', $this->posts_list, '</pre>'); die('debug...'); 
        
        // extra filters from REQUEST
        $this->req = array(
            'from_date'     => isset($_REQUEST['from_date']) ? $_REQUEST['from_date'] : '',
            'to_date'       => isset($_REQUEST['to_date']) ? $_REQUEST['to_date'] : '',
            'postid'        => isset($_REQUEST['postid']) ? $_REQUEST['postid'] : 'all',
        );
 
		// Set parent defaults
		parent::__construct(
			array(
				// singular name of the listed records
				'singular'	=> 'stat',
				// plural name of the listed records
				'plural'	=> 'stats',
				// does this table support ajax?
				'ajax'		=> true,
				'screen'    => 'stat'
			)
		);
		
		$this->_args['singular'] = 'stat';
		$this->_pagination_args['order'] = 'DESC';
		$this->_pagination_args['orderby'] = 'id';
	}

	public function column_default( $item, $column_name )
	{
	    $cols = $this->table_cols;
        if ( in_array($column_name, $cols) && !in_array($column_name, array('report_day', 'birthday', 'title')) ) {
            return $item[ $column_name ];
        }
  
		switch ( $column_name ) {

            case 'title':
                $post_id = $item['banner_id'];
                $post_title = isset($this->posts_list["$post_id"], $this->posts_list["$post_id"]->post_title) ? $post_id . ' - ' . $this->posts_list["$post_id"]->post_title : $post_id . ' - no title--';
                return $post_title;
                
            case 'report_day':
                return $this->__date( $item['report_day'], 'Y-m-d h:i' );
                
            case 'birthday':
                return $this->__date( $item['birthday'], 'Y-m-d' );

			default:
				//Show the whole array for troubleshooting purposes
				return print_r( $item, true );
		}
	}

	public function column_title( $item )
	{
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 'WZC';
        
        $paged = isset($_REQUEST['paged']) ? (int) $_REQUEST['paged'] : 0;
        $paged_var = !empty($paged) ? '&paged='.$paged : '';
        
		// Build row actions
		$actions = array(
		  'view'            => '<a href="' . ( get_permalink(  $item['banner_id']) ) . '" target="_blank">View</a>',
		);
        
        $post_id = $item['banner_id'];
        $post_title = isset($this->posts_list["$post_id"], $this->posts_list["$post_id"]->post_title) ? $post_id . ' - ' . $this->posts_list["$post_id"]->post_title : $post_id . ' - no title--';
		
		// Return the title contents
		return sprintf('%1$s%2$s',
			/*$1%s*/ $post_title,
			/*$3%s*/ $this->row_actions( $actions )
		);
	}

	public function column_cb( $item )
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  
			/*$2%s*/ $item['id']
		);
	}

	public function get_columns()
	{
	    $cols = array();
	    foreach ($this->table_cols as $key => $val) {
	        $title = str_replace('_', ' ', $val);
            $title = ucwords($title);
            $cols["$val"] = $title;
	    }
  
		return $columns = array_merge( array(
			//'cb'			=> '<input type="checkbox" />', //Render a checkbox instead of text
			//'title'		    => 'Title',
		), $cols );
	}

	public function get_sortable_columns()
	{
        $cols = array();
        foreach ($this->table_cols as $key => $val) {
            $cols["$val"] = array( $val, false );
        }

		// true means it's already sorted
		return $sortable_columns = array_merge( array(
			'title'	 		=> array( 'title', false ),	
		), $cols );
	}

	public function get_bulk_actions()
	{
        $array = array();
        return $array;
	}


	public function prepare_items() 
	{
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( $columns, $hidden, $sortable );
     
		$total_items = $this->wpdb->get_var( "SELECT COUNT(id) FROM " . $this->config['db_name'] . " WHERE 1=1" . $this->query_filter_clause() );

	    $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
	    $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $orderby = $orderby == 'title' ? 'banner_id' : $orderby;
	    $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';
		
		$offset = $paged * $this->config['per_page'];  
	    $this->items = $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM " . ( $this->config['db_name'] ) . " WHERE 1=1" . $this->query_filter_clause() . " ORDER BY $orderby $order LIMIT %d OFFSET %d", $this->config['per_page'], $offset), ARRAY_A);

		// REQUIRED for pagination
		$current_page = $this->get_pagenum();
		
		// register our pagination options & calculations.
		$this->set_pagination_args(
			array(
				// WE have to calculate the total number of items
				'total_items'	=> $total_items,
				// WE have to determine how many items to show on a page
				'per_page'	=> $this->config['per_page'],
				// WE have to calculate the total number of pages
				'total_pages'	=> ceil( $total_items / $this->config['per_page'] ),
				// Set ordering values if needed (useful for AJAX)
				'orderby'	=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'id',
				'order'		=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'desc'
			)
		);
	}

	/**
	 * Display the table
	 * Adds a Nonce field and calls parent's display method
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display()
	{ 
		//wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );
		
		echo '<input type="hidden" id="ajaxid" name="ajaxid" value="age_restriction_banners_list_table" />';
		echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';

		parent::display();
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function ajax_response() 
	{
		//check_ajax_referer( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );
   
		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();

		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}
        $response = json_encode( $response );
		die( $response );
	}

    public function query_filter_clause() {
        $req = $this->req;
        $q = array();
        
        if ( !empty($req['postid']) && $req['postid'] != 'all' ) {
            $postid = $req['postid'];
            $q[] = "AND banner_id = '$postid'";
        }
        if ( !empty($req['from_date']) && !empty($req['to_date']) ) {
            $from_date = $req['from_date'];
            $to_date = $req['to_date'];
            $q[] = "AND DATE(report_day) between '$from_date' AND '$to_date'";
            
        } else if ( !empty($req['from_date']) ) {
            $q[] = "AND DATE(report_day) >= '$from_date'";
            
        } else if ( !empty($req['to_date']) ) {
            $q[] = "AND DATE(report_day) <= '$to_date'";
        }
        
        return !empty($q) ? ' ' . trim( implode(' ', $q) ) : '';
    }
    
    public function __date( $date, $format='Y-m-d h:i' ) {
        if ( empty($date) ) return '';
        $format = !empty($format) ? $format : 'Y-m-d h:i';
        return date($format, strtotime($date));
    }
}