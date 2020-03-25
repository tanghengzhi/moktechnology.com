<?php
/**
 * AA-Team - http://www.aa-team.com
 * ================================
 *
 * @package		age_restrictionAjaxListTable
 * @author		Andrei Dinca
 * @version		1.0
 */
! defined( 'ABSPATH' ) and exit;

if(class_exists('age_restrictionAjaxListTable') != true) {
	class age_restrictionAjaxListTable {

		/*
        * Some required plugin information
        */
        const VERSION = '1.0';

		/*
        * Singleton pattern
        */
		static protected $_instance;

		/*
        * Store some helpers
        */
		public $the_plugin = null;

		/*
        * Store some default options
        */
		public $default_options = array(
			'id' => '', /* string, uniq list ID. Use for SESSION filtering / sorting actions */
			'debug_query' => false, /* default is false */
			'show_header' => true, /* boolean, true or flase */
			'list_post_types' => 'all', /* array('post', 'pages' ... etc) or 'all' */
			'items_per_page' => 15, /* number. How many items per page */
			'post_statuses' => 'all',
			'search_box' => true, /* boolean, true or flase */
			'show_statuses_filter' => true, /* boolean, true or flase */
			'show_pagination' => true, /* boolean, true or flase */
			'show_category_filter' => true, /* boolean, true or flase */
			'columns' => array(),
			'custom_table' => ''
		);
		private $items;
		private $items_nr;
		private $args;

		public $opt = array();

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $parent )
        {
        	$this->the_plugin = $parent;
			add_action('wp_ajax_age_restrictionAjaxList', array( $this, 'request' ));

			if(session_id() == '') {
			    // session isn't started
			    session_start();
			}
        }

		/**
	    * Singleton pattern
	    *
	    * @return class Singleton instance
	    */
	    static public function getInstance( $parent )
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self($parent);
	        }

	        return self::$_instance;
	    }

		/**
	    * Setup
	    *
	    * @return class
	    */
		public function setup( $options=array() )
		{
			$this->opt = array_merge( $this->default_options, $options );

			//unset($_SESSION['age_restrictionListTable']); // debug

			// check if set, if not, reset
			$_SESSION['age_restrictionListTable'][$this->opt['id']] = $options;

			return $this;
		}

		/**
	    * Singleton pattern
	    *
	    * @return class Singleton instance
	    */
		public function request()
		{
			$request = array(
				'sub_action' 	=> isset($_REQUEST['sub_action']) ? $_REQUEST['sub_action'] : '',
				'ajax_id' 		=> isset($_REQUEST['ajax_id']) ? $_REQUEST['ajax_id'] : '',
				'params' 		=> isset($_REQUEST['params']) ? $_REQUEST['params'] : '',
			);
  
			if( $request['sub_action'] == 'post_per_page' ){
				$new_post_per_page = $request['params']['post_per_page'];

				if( $new_post_per_page == 'all' ){
					$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['posts_per_page'] = '-1';
				}
				elseif( (int)$new_post_per_page == 0 ){
					$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['posts_per_page'] = $this->opt['items_per_page'];
				}
				else{
					$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['posts_per_page'] = $new_post_per_page;
				}

				// reset the paged as well
				$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['paged'] = 1;
			}
  
			if( $request['sub_action'] == 'paged' ){
				$new_paged = $request['params']['paged'];
				if( $new_paged < 1 ){
					$new_paged = 1;
				}

				$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['paged'] = $new_paged;
			}

			if( $request['sub_action'] == 'post_type' ){
				$new_post_type = $request['params']['post_type'];
				if( $new_post_type == "" ){
					$new_post_type = "";
				}

				$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['post_type'] = $new_post_type;

				// reset the paged as well
				$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['paged'] = 1;
			}

			if( $request['sub_action'] == 'post_status' ){
				$new_post_status = $request['params']['post_status'];
				if( $new_post_status == "all" ){
					$new_post_status = "";
				}

				$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['post_status'] = $new_post_status;

				// reset the paged as well
				$_SESSION['age_restrictionListTable'][$request['ajax_id']]['params']['paged'] = 1;
			}

			// create return html
			ob_start();

			$this->setup( $_SESSION['age_restrictionListTable'][$request['ajax_id']] );
			$this->print_html();
			$html = ob_get_contents();
			ob_clean();

			$return = array(
				'status' 	=> 'valid',
				'html'		=> $html
			);
			
			die( json_encode( array_map(utf8_encode, $return) ) );
		}

		/**
	    * Helper function
	    *
	    * @return object
	    */
		public function get_items()
		{
			global $wpdb;

			$ses = isset($_SESSION['age_restrictionListTable'][$this->opt['id']]['params']) ? $_SESSION['age_restrictionListTable'][$this->opt['id']]['params'] : array();
			
			$this->args = array(
				'posts_per_page'  	=> ( isset($ses['posts_per_page']) ? $ses['posts_per_page'] : $this->opt['items_per_page'] ),
				'paged'				=> ( isset($ses['paged']) ? $ses['paged'] : 1 ),
				'category'        	=> ( isset($ses['category']) ? $ses['category'] : '' ),
				'orderby'         	=> 'post_date',
				'order'          	=> 'DESC',
				'post_type'       	=> ( isset($ses['post_type']) && trim($ses['post_type']) != "all" ? $ses['post_type'] : array_keys($this->get_list_postTypes()) ),
				'post_status'     	=> ( isset($ses['post_status']) ? $ses['post_status'] : '' ),
				'suppress_filters' 	=> true
			);

			// if custom table, make request in the custom table not in wp_posts
			if( trim($this->opt["custom_table"]) == "amz_products"){
				$pages = array();

			    // select all pages and post from DB
			    $myQuery = "SELECT * FROM " . $wpdb->prefix  . ( $this->opt["custom_table"] ) . " WHERE type='post' and status='new' AND 1=1 ";
				
			    $__limitClause = $this->args['posts_per_page']>0 ? " 1=1 limit " . (($this->args['paged'] - 1) * $this->args['posts_per_page']) . ", " . $this->args['posts_per_page'] : '1=1 ';
				$result_query = str_replace("1=1 ", $__limitClause, $myQuery);
				
			    $query = $wpdb->get_results( $result_query, ARRAY_A);
  
			    foreach ($query as $key => $myrow){
					$pages[$myrow['post_id']] = array(
						'post_id' => $myrow['post_id'],
						'post_parent' => $myrow['post_parent'],
						'type' => $myrow['type'],
						'title' => $myrow['title'],
						'nb_assets' => $myrow['nb_assets'],
						'nb_assets_done' => $myrow['nb_assets_done'],
					);
			    }

				if( $this->opt['debug_query'] == true ){
					echo '<script>console.log("' . $result_query . '");</script>';
				}

				$this->items = $pages;
				$this->items_nr = $wpdb->get_var( str_replace("*", "count(post_id) as nbRow", $myQuery) );
				
			}else{

				// remove empty array
				$this->args = array_filter($this->args);

				$this->items = get_posts( $this->args );

				// get all post count
				$nb_args = $this->args;
				$nb_args['posts_per_page'] = '-1';
				$nb_args['fields'] = 'ids';
				$this->items_nr = (int) count(get_posts( $nb_args ));

				if( $this->opt['debug_query'] == true ){
					$query = new WP_Query( $this->args );
					echo '<script>console.log("' . $query->request . '");</script>';
				}
			}

			return $this;
		}

		private function getAvailablePostStatus()
		{

			$ses = isset($_SESSION['age_restrictionListTable'][$this->opt['id']]['params']) ? $_SESSION['age_restrictionListTable'][$this->opt['id']]['params'] : array();

			$post_type = isset($ses['post_type']) && trim($ses['post_type']) != "" ? $ses['post_type'] : '';

			$sql = "SELECT count(id) as nbRow, post_status, post_type FROM " . ( $this->the_plugin->db->prefix ) . "posts WHERE 1 = 1 " . ( trim($post_type) != "" ? " AND post_type = '" . ( $post_type ) . "' " : '' ) . " group by post_status";
			$sql = preg_replace('~[\r\n]+~', "", $sql);

			return $this->the_plugin->db->get_results( $sql, ARRAY_A );
		}

		private function get_list_postTypes()
		{
			// overwrite wrong post-type value
			if( !isset($this->opt['list_post_types']) ) $this->opt['list_post_types'] = 'all';
 
			// custom array case
			if( is_array($this->opt['list_post_types']) && count($this->opt['list_post_types']) > 0 ) {
				$__ = array();
				foreach ($this->opt['list_post_types'] as $key => $value) {
					$__[$value] = get_post_type_object( $value );
				} 
				return $__;
			}
			 
			// all case
			return get_post_types(array('show_ui' => TRUE, 'show_in_nav_menus' => TRUE), 'objects');
		}

		private function get_pagination()
		{
			$html = array();

			$ses = isset($_SESSION['age_restrictionListTable'][$this->opt['id']]['params']) ? $_SESSION['age_restrictionListTable'][$this->opt['id']]['params'] : array();

			$posts_per_page = ( isset($ses['posts_per_page']) ? $ses['posts_per_page'] : $this->opt['items_per_page'] );
			$paged = ( isset($ses['paged']) ? $ses['paged'] : 1 );
			$total_pages = ceil( $this->items_nr / $posts_per_page );
			
			if( $this->opt['show_pagination'] ){
				$html[] = 	'<div class="age_restriction-list-table-right-col">';

				$html[] = 		'<div class="age_restriction-box-show-per-pages">';
				$html[] = 			'<select name="age_restriction-post-per-page" id="age_restriction-post-per-page" class="age_restriction-post-per-page">';


				foreach( range(5, 50, 5) as $nr => $val ){
					$html[] = 			'<option val="' . ( $val ) . '" ' . ( $posts_per_page == $val ? 'selected' : '' ). '>' . ( $val ) . '</option>';
				}

				$html[] = 				'<option value="all">';
				$html[] =				__('Show All', 'age-restriction');
				$html[] = 				'</option>';
				$html[] =			'</select>';
				$html[] = 			'<label for="age_restriction-post-per-page" style="width:57px">' . __('per pages', 'age-restriction') . '</label>';
				$html[] = 		'</div>';

				$html[] = 		'<div class="age_restriction-list-table-pagination tablenav">';

				$html[] = 			'<div class="tablenav-pages">';
				$html[] = 				'<span class="displaying-num">' . ( $this->items_nr ) . ' items</span>';
				if( $total_pages > 1 ){
					$html[] = 				'<span class="pagination-links"><a class="first-page ' . ( $paged == 1 ? 'disabled' : '' ) . ' age_restriction-jump-page" title="Go to the first page" href="#paged=1">&laquo;</a>';
					$html[] = 				'<a class="prev-page ' . ( $paged == 1 ? 'disabled' : '' ) . ' age_restriction-jump-page" title="Go to the previous page" href="#paged=' . ( $paged > 2 ? ($paged - 1) : '' ) . '">&lsaquo;</a>';
					$html[] = 				'<span class="paging-input"><input class="current-page" title="Current page" type="text" name="paged" value="' . ( $paged ) . '" size="2" style="width: 45px;"> of <span class="total-pages">' . ( ceil( $this->items_nr / $this->args['posts_per_page'] ) ) . '</span></span>';
					$html[] = 				'<a class="next-page ' . ( ( $paged == ($total_pages)) ? 'disabled' : '' ) . ' age_restriction-jump-page" title="Go to the next page" href="#paged=' . ( $paged + 1 ) . '">&rsaquo;</a>';
					$html[] = 				'<a class="last-page ' . ( $paged ==  ($total_pages - 1) ? 'disabled' : '' ) . ' age_restriction-jump-page" title="Go to the last page" href="#paged=' . ( $total_pages ) . '">&raquo;</a></span>';
				}
				$html[] = 			'</div>';
				$html[] = 		'</div>';

				$html[] = 	'</div>';
			}

			return implode("\n", $html);
		}

		public function print_header()
		{
			$html = array();
			$ses = isset($_SESSION['age_restrictionListTable'][$this->opt['id']]['params']) ? $_SESSION['age_restrictionListTable'][$this->opt['id']]['params'] : array();

			$post_type = isset($ses['post_type']) && trim($ses['post_type']) != "" ? $ses['post_type'] : '';

			$html[] = '<div id="age_restriction-list-table-header">';

			if( trim($this->opt["custom_table"]) == ""){
				$list_postTypes = $this->get_list_postTypes();

				$html[] = '<div class="age_restriction-list-table-left-col">';
				$html[] = 		'<select name="age_restriction-filter-post_type" class="age_restriction-filter-post_type">';
				if( count($list_postTypes) > 2 ){
					$html[] = 		'<option value="all" >';
					$html[] =			__('Show All', 'age-restriction');
					$html[] = 		'</option>';	
				}

	            foreach ( $list_postTypes as $name => $postType ){
					$html[] = 		'<option ' . ( $name == $post_type ? 'selected' : '' ) . ' value="' . ( $this->the_plugin->escape($name) ) . '">';
					$html[] = 			( is_object($postType) ? ucfirst($this->the_plugin->escape($name)) : ucfirst($name) );
					$html[] = 		'</option>';
	            }
				$html[] = 		'</select>';


				if( $this->opt['show_statuses_filter'] ){
					$html[] = $this->post_statuses_filter();
				}
				$html[] = 		'</div>';

				if( $this->opt['search_box'] ){
					$html[] = 	'<div class="age_restriction-list-table-right-col">';
					$html[] = 		'<div class="age_restriction-list-table-search-box">';
					$html[] = 			'<input type="text" name="s" value="" >';
					$html[] = 			'<input type="button" name="" class="button" value="Search Posts">';
					$html[] = 		'</div>';
					$html[] = 	'</div>';
				}

				if( $this->opt['show_category_filter']  && 3==4 ){
					$html[] = '<div class="age_restriction-list-table-left-col" >';
					$html[] = 	'<select name="age_restriction-filter-post_type" class="age_restriction-filter-post_type">';
					$html[] = 		'<option value="all" >';
					$html[] =		__('Show All', 'age-restriction');
					$html[] = 		'</option>';
					$html[] =	'</select>';
					$html[] = '</div>';
				}
			}else{
				$html[] = '<div class="age_restriction-list-table-left-col">&nbsp;</div>';
			}

			$html[] = $this->get_pagination();

			$html[] = '</div>';

            echo implode("\n", $html);

			return $this;
		}

		public function print_main_table( $items=array() )
		{
			$html = array();

			if( $this->opt['id'] == 'age_restrictionSyncMonitor' ) {
				$last_updated_product = (int)get_option( 'age_restriction_last_updated_product', true);
				if( $last_updated_product > 0 ){
					$last_sync_date = get_post_meta($last_updated_product, '_last_sync_date', true);
					
					$html[] = 	'<div class="age_restriction-last-updated-product age_restriction-message age_restriction-info">';
					$html[] =		__('The last product synchronized was:', 'age-restriction');
					$html[] =		'<strong>' . $last_updated_product . '</strong>. ';
					$html[] =		__('This was synchronized at:', 'age-restriction');
					$html[] =		'<i>' . ( $last_sync_date ) . '</i>';
					$html[] = 	'</div>';
				}
			}
 
			$html[] = '<div id="age_restriction-list-table-posts">';	
			$html[] = 	'<table class="age_restriction-table" id="' . ( $this->opt["id"] ) . '" style="border: none;border-bottom: 1px solid #f2f2f2;">';
			$html[] = 		'<thead>';
			$html[] = 			'<tr>';
			foreach ($this->opt['columns'] as $key => $value){
				if( $value['th'] == 'checkbox' ){
					$html[] = '<th class="checkbox-column" width="20"><input type="checkbox" id="age_restriction-item-check-all" checked></th>';
				}
				else{
					$html[] = '<th' . ( isset($value['width']) && (int)$value['width'] > 0 ? ' width="' . ( $value['width'] ) . '"' : '' ) . '' . ( isset($value['align']) && $value['align'] != "" ? ' align="' . ( $value['align'] ) . '"' : '' ) . '>' . ( $value['th'] ) . '</th>';
				}
			}

			$html[] = 			'</tr>';
			$html[] = 		'</thead>';

			$html[] = 		'<tbody>';
			
			if( trim($this->opt["custom_table"]) == "amz_products" && count($this->items) == 0 ){
				$html[] = '<td colspan="' . ( count($this->opt['columns']) ) . '" style="text-align:left">
					<div class="age_restriction-message age_restriction-success">Good news, all products assets has been downloaded successfully!</div>
				</td>';
			}
			
			foreach ($this->items as $post){
				$post_id = 0;
				if ( isset($post->ID) ) $post_id = $post->ID;
				else if ( isset($post['post_id']) ) $post_id = $post['post_id'];
				
				if ( $post_id > 0 )
					$item_data = array(
						'score' 	=> get_post_meta( $post_id, 'age_restriction_score', true )
					);

				$html[] = 			'<tr data-itemid="' . ( $post_id ) . '">';
				foreach ($this->opt['columns'] as $key => $value){

					$html[] = '<td style="'
						. ( isset($value['align']) && $value['align'] != "" ? 'text-align:' . ( $value['align'] ) . ';' : '' ) . ''
						. ( isset($value['valign']) && $value['valign'] != "" ? 'vertical-align:' . ( $value['valign'] ) . ';' : '' ) . ''
						. ( isset($value['css']) && count($value['css']) > 0 ? $this->print_css_as_style($value['css']) : '' ) . '">';

					if( $value['td'] == 'checkbox' ){
						$html[] = '<input type="checkbox" class="age_restriction-item-checkbox" name="age_restriction-item-checkbox-' . ( $post->ID ) . '" checked>';
					}
					elseif( $value['td'] == '%ID%' ){
						$html[] = ( $post->ID );
					}
					elseif( $value['td'] == '%title%' ){
						$html[] = '<input type="hidden" id="age_restriction-item-title-' . ( $post->ID ) . '" value="' . ( str_replace('"', "'", $post->post_title) ) . '" />';
						$html[] = '<a href="' . ( sprintf( admin_url('post.php?post=%s&action=edit'), $post->ID)) . '">';
						$html[] = 	( $post->post_title . ( $post->post_status != 'publish' ? ' <span class="item-state">- ' . ucfirst($post->post_status) : '</span>') );
						$html[] = '</a>';
					}
					elseif( $value['td'] == '%button%' ){
						$value['option']['color'] = isset($value['option']['color']) ? $value['option']['color'] : 'gray';
						$html[] = 	'<input type="button" value="' . ( $value['option']['value'] ) . '" class="age_restriction-button ' . ( $value['option']['color'] ) . ' age_restriction-' . ( $value['option']['action'] ) . '">';
					}
					elseif( $value['td'] == '%date%' ){
						$html[] = '<i>' . ( $post->post_date ) . '</i>';
					}
					elseif( $value['td'] == '%thumb%' ){
						
						$html[] = get_the_post_thumbnail( $post->ID, array(50, 50) );
					}
					elseif( $value['td'] == '%date%' ){
						$html[] = '<i>' . ( $post->post_date ) . '</i>';
					}
					elseif( $value['td'] == '%hits%' ){
						$hits = (int) get_post_meta($post->ID, '_amzaff_hits', true);
						$html[] = '<i class="age_restriction-prod-stats-number">' . ( $hits ) . '</i>';
					}
					elseif( $value['td'] == '%bad_url%' ){
						$html[] = '<i>' . ( $post['url'] ) . '</i>';
					}
					elseif( $value['td'] == '%asin%' ){
						$asin = get_post_meta($post->ID, '_amzASIN', true);
						$html[] = '<strong>' . ( $asin ) . '</strong>';
					}
					elseif( $value['td'] == '%last_sync_date%' ){
						$last_sync_date = get_post_meta($post->ID, '_last_sync_date', true);
						$html[] = '<i class="age_restriction-data-last_sync_date">' . ( $last_sync_date ) . '</i>';
					}
					elseif( $value['td'] == '%last_date%' ){
						$html[] = '<i>' . ( $post['data'] ) . '</i>';
					}
					elseif( $value['td'] == '%preview%' ){
						$asin = get_post_meta($post->ID, '_amzASIN', true); 
						$html[] = "<div class='age_restriction-product-preview'>";
						$html[] = 	get_the_post_thumbnail( $post->ID, array(150, 150) );
						$html[] = 	"<div class='age_restriction-product-label'><strong>" . ( $post->post_title ) . "</strong></div>";
						$html[] = 	"<div class='age_restriction-product-label'>ASIN: <strong>" . ( $asin ) . "</strong></div>";
						$html[] = 	"<div class='age_restriction-product-label'>";
						$html[] = 		'<a href="' . ( get_permalink( $post->ID ) ) . '" class="age_restriction-button gray">' . __('View product', 'age-restriction') . '</a>';
						$html[] = 		'<a href="' . ( admin_url( 'post.php?post=' . ( $post->ID ) . '&action=edit' ) ) . '" class="age_restriction-button blue">' . __('Edit product', 'age-restriction') . '</a>';
						$html[] = 	"</div>";
						$html[] = "</div>";
					}
					
					$html[] = '</td>';
				}

				$html[] = 			'</tr>';
			}

			$html[] = 		'</tbody>';

			$html[] = 	'';

			$html[] = 	'</table>';

			if( trim($this->opt["custom_table"]) == ""){

				if( isset($this->opt['mass_actions']) && count($this->opt['mass_actions']) > 0 ){
					$html[] = '<div class="age_restriction-list-table-left-col" style="padding-top: 5px;">&nbsp;';

					foreach ($this->opt['mass_actions'] as $key => $value){
						$html[] = 	'<input type="button" value="' . ( $value['value'] ) . '" id="age_restriction-' . ( $value['action'] ) . '" class="age_restriction-button ' . ( $value['color'] ) . '">';
					}
					$html[] = '</div>';
				}else{
					
					$html[] = '<div class="age_restriction-list-table-left-col" style="padding-top: 5px;">&nbsp;';
					
					$html[] = '</div>';
				}
			}
			else{
				$html[] = '<div class="age_restriction-list-table-left-col" style="padding-top: 5px;">&nbsp;';
				if( trim($this->opt["custom_table"]) == "amz_products"){
					$html[] = '<a class="age_restriction-button orange age_restriction-download-all-assets-btn" href="#">Download ALL products assets NOW!</a>';
					$html[] = '<a class="age_restriction-button red age_restriction-delete-all-assets-btn" href="#">Delete selected products assets</a>';
				}
				$html[] = '</div>';
			}

			$html[] = $this->get_pagination();

			$html[] = '</div>';

            echo implode("\n", $html);

			return $this;
		}

		public function post_statuses_filter()
		{
			$html = array();

			$availablePostStatus = $this->getAvailablePostStatus();

			$ses = isset($_SESSION['age_restrictionListTable'][$this->opt['id']]['params']) ? $_SESSION['age_restrictionListTable'][$this->opt['id']]['params'] : array();

			$curr_post_status = isset($ses['post_status']) && trim($ses['post_status']) != "" ? $ses['post_status'] : 'all';

			if( $this->opt['post_statuses'] == 'all' ){
				$postStatuses = array(
				    'all'   	=> __('All', 'age-restriction'),
				    'publish'   => __('Published', 'age-restriction'),
				    'future'    => __('Scheduled', 'age-restriction'),
				    'private'   => __('Private', 'age-restriction'),
				    'pending'   => __('Pending Review', 'age-restriction'),
				    'draft'     => __('Draft', 'age-restriction'),
				);
			}
			else{
				$postStatuses = $this->opt['post_statuses'];
			}

			$html[] = 		'<ul class="subsubsub age_restriction-post_status-list">';
			$cc = 0;
			// add into _postStatus array only if have equivalent into query results
			$_postStatus = array();
			$totals = 0;
			foreach ($availablePostStatus as $key => $value){
				if( in_array($value['post_status'], array_keys($postStatuses))){
					$_postStatus[$value['post_status']] = $value['nbRow'];
					$totals = $totals + $value['nbRow'];
				}
			}

			foreach ($postStatuses as $key => $value){
				$cc++;

				if( $key == 'all' || in_array($key, array_keys($_postStatus)) ){
					$html[] = 		'<li class="ocs_post_status">';
					$html[] = 			'<a href="#post_status=' . ( $key ) . '" class="' . ( $curr_post_status == $key ? 'current' : '' ) . '" data-post_status="' . ( $key ) . '">';
					$html[] = 				$value . ' <span class="count">(' . ( ( $key == 'all' ? $totals : $_postStatus[$key] ) ) . ')</span>';
					$html[] = 			'</a>' . ( count($_postStatus) > ($cc) ? ' |' : '');
					$html[] = 		'</li>';
				}
			}

			$html[] = 		'</ul>';

			return implode("\n", $html);
		}

		public function print_html()
		{
			$html = array();

			$this->get_items();
			$items = $this->items;
  
			$html[] = '<input type="hidden" class="age_restriction-ajax-list-table-id" value="' . ( $this->opt['id'] ) . '" />';

			// header
			if( $this->opt['show_header'] === true ) $this->print_header();

			// main table
			$this->print_main_table( $items );
   
			echo implode("\n", $html);
   
			return $this;
		}

		private function print_css_as_style( $css=array() )
		{
			$style_css = array();
			if( isset($css) && count($css) > 0 ){
				foreach ($css as $key => $value) {
					$style_css[] = $key . ": " . $value;
				}
			}

			return ( count($style_css) > 0 ? implode(";", $style_css) : '' );
		}

	}
}