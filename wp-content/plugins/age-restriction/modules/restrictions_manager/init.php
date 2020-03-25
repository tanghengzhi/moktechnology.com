<?php
/*
* Define class age_restrictionBM
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBM') != true) {
    class age_restrictionBM
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
		public $the_plugin = null;
		
		protected $module_folder = '';
		protected $module_folder_path = '';
		private $module = '';
		
		protected $settings = array();
		
		static protected $_instance;
		
		static protected $slug = 'age-restriction';
		private $banner_sections = null;
		private $banner_discount = null;
		private $banner_type = null;
		private $banner_amzcfg = null;
		private $banner_stats = null;
		
		static protected $bannerTypes = array();
		
	
		/**
		 * Required __construct() function that initalizes the AA-Team Framework
		 */
        public function __construct()
        {
        	global $age_restriction;
			
        	$this->the_plugin = $age_restriction;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
			$this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/restrictions_manager/';
			$this->module = $this->the_plugin->cfg['modules']['restrictions_manager'];
			
			$this->settings = array();
			// $this->settings = $this->the_plugin->getAllSettings( 'array', 'restrictions_manager' );
			
			self::$bannerTypes = array(
				'popup' 			=> __( 'Popup', 'age-restriction' ),
			);
			
			if ( isset($this->settings['slug']) && !empty($this->settings['slug']) )
				self::$slug = $this->settings['slug'];

			if ( $this->the_plugin->is_admin === true ) {
				$this->admin_init();
			} else {
				$this->frontend_init();
			}
        }
		
		/**
		 * Frontend init
		 */
		public function frontend_init() {
			// social sharing module
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'lib/frontend/banner.php' );
			$age_restrictionFrontend = new age_restrictionBMFrontend( $this->the_plugin );
		}
		
		
        /**
		 * Admin init
		 */
        public function admin_init() {
        	$this->init_postType();
        }

        
		/**
		 * Register Post Type
		 */
		public function init_postType() 
		{
			global $post;
			$post_id = 0;
			if ( isset($post) && is_object($post) && isset($post->ID) )
				$post_id = (int) $post->ID;
    
			// banner sections!
			require_once( 'app.sections.php' );
			$this->banner_sections = new age_restrictionBannersPerSections( $this->the_plugin );
			
			// banner discount!
			//require_once( 'app.discount.php' );
			//$this->banner_discount = new age_restrictionBannersDiscount( $this->the_plugin, $post_id );
			
			// banner type!
			require_once( 'app.banner_type.php' );
			$this->banner_type = new age_restrictionBannersType( $this->the_plugin, $post_id );
  			
			// banner stats!
			require_once( 'app.statistics.php' );
			$this->banner_stats = new age_restrictionBannersStats( $this->the_plugin, $post_id );

		    // get label
		    $labels = array(
		        'name' 					=> __('Restrictions', 'age-restriction'),
		        'singular_name' 		=> __('Restriction', 'age-restriction'),
		        'add_new' 				=> __('Add new', 'age-restriction'),
		        'add_new_item' 			=> __('Add new restriction', 'age-restriction'),
		        'edit_item'			 	=> __('Edit Restriction', 'age-restriction'),
		        'new_item' 				=> __('New Restriction', 'age-restriction'),
		        'view_item' 			=> __('View Restriction', 'age-restriction'),
		        'search_items' 			=> __('Search into restrictions', 'age-restriction'),
		        'not_found' 			=> __('No restrictions found', 'age-restriction'),
		        'not_found_in_trash' 	=> __('No restrictions in trash', 'age-restriction')
		    );
		  
		    // start formationg arguments
		    $args = array(
			    'rewrite' => array(
				    'slug' => self::$slug
		    	),
		        'labels' => $labels,
		        'public' => true,
		        'publicly_queryable' => false,
		        'exclude_from_search' => true,
		        'show_ui' => true,
		        'has_archive' => true,
		        'query_var' => true,
				'menu_icon' => $this->module_folder . 'assets/menu_icon.png',
		        'capability_type' => 'post',
		        'show_in_menu' => true,
		        'supports' => array( 'title'/*, 'editor'*/ )
		    );
		
		    register_post_type('age_restriction', $args);
			
			add_action( 'admin_head', array( $this, 'add_32px_icon' ) );
			
			// add meta boxes to post type
			add_action('admin_menu', array($this, 'add_to_menu_metabox'));
			
			/* use save_post action to handle data entered */
			add_action( 'save_post', array( $this, 'meta_box_save_postdata' ) );
			
			if( isset($_GET['post_type']) && $_GET['post_type'] == 'age_restriction') {
				add_action('admin_head', array( $this, 'extra_css') );
			}
			
			add_action( 'admin_head', array($this, 'remove_link_bycss') );
	    }

		// remove links
		public function remove_link_GetShortlink( $false, $post_id ) {
			return 'age_restriction' === get_post_type( $post_id ) ? '' : $false;
		}

		public function remove_link_bycss() {
			global $post_type, $post;
			
			if ( isset($post) && is_object($post) && isset($post->ID) ) {
			$post_id = (int) $post->ID;
			
			$home_url = home_url('/');
			
			if ( 'age_restriction' == $post_type ) {
				ob_start();
?>
				<style type="text/css">
				#edit-slug-box,
				#edit-slug-box #sample-permalink,
				#edit-slug-box #change-permalinks,
				#edit-slug-box #view-post-btn,
				#edit-slug-box #edit-slug-buttons,
				#edit-slug-box > input, #edit-slug-box > a,
				#preview-action #post-preview,
				.updated p a{display: none;}.view{display:none;}
				</style>
<?php
				$content = ob_get_contents();
				ob_end_clean();
				
				echo $content;
			}
			}
		}

	    
		public function add_32px_icon()
		{
			?>
			<style type="text/css" media="screen">
    			.icon32-posts-age_restriction {
    				background: url(<?php echo $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/assets/32.png';?>) no-repeat !important;
    			}
    		</style>
    		<?php 
		}
		
		public function add_to_menu_metabox()
		{
			
			self::getInstance()
	       		->_registerMetaBoxes();
		}
		
		/**
		 * Register plug-in Post Type admin metaboxes
		 */
	    protected function _registerMetaBoxes()
	    {
	    	$screens = array(
	    		'age_restriction' => __( 'Age Restriction Details', 'age-restriction' )
	    	);
		    foreach ($screens as $key => $screen) {
		    	$screen = str_replace("_", " ", $screen);
				$screen = ucfirst($screen);
		        add_meta_box(
		            'age_restriction_meta_box',
		            $screen,
		            array($this, 'custom_metabox'),
		            $key,
		            'normal'
		        );
		    }
	        return $this;
	    }
		
		public function custom_metabox() {

			global $post;

			if ( isset($post) && is_object($post) && isset($post->ID) ) ;
			else return false;
				
			$post_id = (int) $post->ID;
  
			// load the settings template class
			require_once( $this->the_plugin->cfg['paths']['freamwork_dir_path'] . 'settings-template.class.php' );
			
			// Initalize the your aaInterfaceTemplates
			$aaInterfaceTemplates = new aaInterfaceTemplates($this->the_plugin->cfg);


			// retrieve the existing value(s) for this meta field. This returns an array
			$banners_meta = get_post_meta( $post_id, '_age_restriction_meta', true );
			
			// then build the html, and return it as string
			$html_banner = $aaInterfaceTemplates->bildThePage( $this->build_banner_options( $banners_meta, $post_id ) , $this->the_plugin->alias, array(), false);
			
			$html_sections = $aaInterfaceTemplates->bildThePage( $this->build_sections_options( null, $post_id ) , $this->the_plugin->alias, array(), false);
			
			$html_stats = $aaInterfaceTemplates->bildThePage( $this->build_stats_options( null, $post_id ) , $this->the_plugin->alias, array(), false);
		?>
			<link rel='stylesheet' href='<?php echo $this->module_folder;?>app.css' type='text/css' media='screen' />
			<link rel='stylesheet' href='<?php echo $this->module_folder;?>assets/flags/flags.css' type='text/css' media='screen' />
			<link rel='stylesheet' href='<?php echo $this->module_folder;?>iphone-switch/iphone-switch.css' type='text/css' media='screen' />
			
			<script type="text/javascript">
				var age_restriction_stats_loc = 'admin_metabox';
			</script>
			<script type="text/javascript" src="<?php echo $this->module_folder;?>charts.js" ></script>
			<script type="text/javascript" src="<?php echo $this->module_folder;?>app.class.js" ></script>
			
			<input type="hidden" name="age_restriction-postid" id="age_restriction-postid" value="<?php echo $post_id; ?>" />
			<textarea name="age_restriction-discount-saveobj" id="age_restriction-discount-saveobj" style="display: none;"></textarea>
			<textarea name="age_restriction-discount-saveobj-current" id="age_restriction-discount-saveobj-current" style="display: none;">
				<?php 
					$banner_content = get_post_field('post_content', $post_id);
					$banner_content = unserialize($banner_content);
					echo $banner_content;
				?>
			</textarea>
			<div id="age_restriction-settings" style="display: none;">
				<?php $amzcfg = array(); ?>
				<div id="age_restriction-amazon-global" data-values="<?php echo $amzcfg; ?>"></div>
			</div>
			
			<div id="age_restriction-meta-box-preload" style="height:200px; position: relative;">
				<!-- Main loading box -->
				<div id="age_restriction-main-loading" style="display:block;">
					<div id="age_restriction-loading-box" style="top: 50px">
						<div class="age_restriction-loading-text"><?php _e('Loading', 'age-restriction');?></div>
						<div class="age_restriction-meter age_restriction-animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
					</div>
				</div>
			</div>
			
			<div class="age_restriction-meta-box-container" style="display:none;">
				<!-- box Tab Menu -->
				<div class="age_restriction-tab-menu">
					<a href="#banner" class="open"><?php _e('How / What to display', 'age-restriction');?></a>
					<a href="#sections"><?php _e('Where to display', 'age-restriction');?></a>
					<a href="#statistics"><?php _e('Statistics', 'age-restriction');?></a>
				</div>
				
				<div class="age_restriction-tab-container">

					<div id="age_restriction-tab-div-id-banner" style="display:block;">
						<div class="age_restriction-dashboard-box span_3_of_3">
							<!-- Creating the option fields -->
							<div class="age_restriction-form">
								<?php echo $html_banner;?>
							</div>
						</div>
					</div>
					
					<div id="age_restriction-tab-div-id-sections" style="display:none;">
						<div class="age_restriction-dashboard-box span_3_of_3">
							<!-- Creating the option fields -->
							<div class="age_restriction-form">
								<?php echo $html_sections;?>
							</div>
						</div>
					</div>
					
					<div id="age_restriction-tab-div-id-statistics" style="display:none;">
						<div class="age_restriction-dashboard-box span_3_of_3">
							<!-- Creating the option fields -->
							<div class="age_restriction-form">
								<?php echo $html_stats; ?>
							</div>
						</div>
					</div>
				</div>
				<div style="clear:both"></div>
			</div>
		<?php
		}
		
		public function build_banner_options( $defaults=array(), $post_id=0 )
		{
			if( !is_array($defaults) ) $defaults = array();
			
			// banner sections!
			$this->banner_type->set_banner_postid( $post_id );
			$bannertype_elements = $this->banner_type->get_elements();
			$bannertype_tabs = $this->banner_type->get_tabs();
			
			$options = array(
				array(
					/* define the form_sizes  box */
					'banner' => array(
						'title' 	=> 'How to display options',
						'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
						'header' 	=> true, // true|false
						'toggler' 	=> false, // true|false
						'buttons' 	=> false, // true|false
						'style' 	=> 'panel', // panel|panel-widget
						
						// create the box elements array
						'elements'	=> $bannertype_elements,

						// tabs
						'tabs'		=> $bannertype_tabs
					)
				)
			);

			// setup the default value base on array with defaults
			if(count($defaults) > 0){
				foreach ($options as $option){
					foreach ($option as $box_id => $box){
						if(in_array($box_id, array_keys($defaults))){
							foreach ($box['elements'] as $elm_id => $element){
								
								if ( $elm_id == 'views_current' ) {
									$current_hits = (int) get_post_meta( $post_id, '_age_restriction_hits', true );
									$defaults[$box_id][$elm_id] = $current_hits;
								}

								if(isset($defaults[$box_id][$elm_id])){
									$option[$box_id]['elements'][$elm_id]['std'] = $defaults[$box_id][$elm_id];
								}
							}
						}
					}
				}
				
				// than update the options for returning
				$options = array( $option );
			}
			
			return $options;
		}
		
		public function build_sections_options( $defaults=array(), $post_id=0 )
		{
			if( !is_array($defaults) ) $defaults = array();
			
			// banner sections!
			$this->banner_sections->set_banner_postid( $post_id );
			$sections_html = $this->banner_sections->get_sidebar_conditions();

			$options = array(
				array(
					/* define the form_sizes  box */
					'banner' => array(
						'title' 	=> 'Where to display options',
						'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
						'header' 	=> true, // true|false
						'toggler' 	=> false, // true|false
						'buttons' 	=> false, // true|false
						'style' 	=> 'panel', // panel|panel-widget
						
						// create the box elements array
						'elements'	=> array(
							array(
								'type' 		=> 'html',
								'html' 		=> $sections_html,
							)
						)
					)
				)
			);
  
			return $options;
		}
		
		public function build_discount_options( $defaults=array(), $post_id=0 )
		{
			if( !is_array($defaults) ) $defaults = array();
			
			// banner sections!
			$this->banner_discount->set_banner_postid( $post_id );
			$discount_html = $this->banner_discount->build_discount_html();
  
			$options = array(
				array(
					/* define the form_sizes  box */
					'banner' => array(
						'title' 	=> 'What to display options',
						'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
						'header' 	=> true, // true|false
						'toggler' 	=> false, // true|false
						'buttons' 	=> false, // true|false
						'style' 	=> 'panel', // panel|panel-widget
						
						// create the box elements array
						'elements'	=> array(
							array(
								'type' 		=> 'html',
								'html' 		=> $discount_html,
							)
						)
					)
				)
			);
			
			return $options;
		}

		public function build_stats_options( $defaults=array(), $post_id=0 )
		{
			if( !is_array($defaults) ) $defaults = array();
			
			// banner sections!
			$this->banner_stats->set_banner_postid( $post_id );
			$stats_html = $this->banner_stats->build_html();
  
			$options = array(
				array(
					/* define the form_sizes  box */
					'banner' => array(
						'title' 	=> 'Statistics',
						'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
						'header' 	=> true, // true|false
						'toggler' 	=> false, // true|false
						'buttons' 	=> false, // true|false
						'style' 	=> 'panel', // panel|panel-widget
						
						// create the box elements array
						'elements'	=> array(
							array(
								'type' 		=> 'html',
								'html' 		=> $stats_html,
							)
						)
					)
				)
			);
			
			return $options;
		}
		
		public function choose_banner_type() {
			global $post, $post_id;
	
			$post_id = (int) $post->ID;
			
			// banner type
			$banners_meta = get_post_meta( $post_id, '_age_restriction_meta', true );
			$db_choose_banner_type = isset($banners_meta['banner'], $banners_meta['banner']['choose_banner_type']) ? $banners_meta['banner']['choose_banner_type'] : '';
	
			if ( !empty($db_choose_banner_type) )
				$choose_banner_type = $db_choose_banner_type;
			else
				$choose_banner_type = apply_filters( 'default_choose_banner_type', 'takeover' );

			$choose_banner_type_selector = apply_filters( 'choose_banner_type_selector', self::$bannerTypes, $choose_banner_type );
	
			$choose_banner_type_box  = '<label for="choose_banner_type"><select id="choose_banner_type" name="choose_banner_type"><optgroup label="' . __( 'Type', 'age-restriction' ) . '">';
			foreach ( $choose_banner_type_selector as $value => $label )
				$choose_banner_type_box .= '<option value="' . esc_attr( $value ) . '" ' . selected( $choose_banner_type, $value, false ) .'>' . esc_html( $label ) . '</option>';
			$choose_banner_type_box .= '</optgroup></select></label>';
			
			$html = '<span class="banner-type"> &mdash; ' . $choose_banner_type_box . '</span>';
			return $html;
		}
		
		
		/**
		 * Post Type Columns
		 */
		public function banners_edit_columns($columns) {
		    $new_columns['cb'] 						= '<input type="checkbox" />';
		    $new_columns['banners_id'] 				= __('ID', 'age-restriction');
		    $new_columns['title'] 					= __('Title', 'age-restriction');
			$new_columns['banners_country'] 		= __('Countries', 'age-restriction');
		    $new_columns['date'] 					= __('Date', 'age-restriction');
		
		    return $new_columns;
		}
		
		public function banners_posts_columns($column_name, $id) {
		    global $id, $wpdb;
  
			$bannerInfo = $this->the_plugin->get_banner_info($id);
			 
			// trash
			if (empty($bannerInfo)) {
				//$banner_type = '';
				$banner_status = '';
				$banner_stats_html = '';
				$banner_country_html = '';

			} else {
			
				// meta
				$banners_meta = $bannerInfo['meta'];
				
				// banners country
				$banner_country_html = '';
				$banners_country = array();  
				$countries = isset($banners_meta['selected_countries']) ? $banners_meta['selected_countries'] : null;
				
				if ( !empty($countries) && is_array($countries) ) {
					foreach ( $countries as $country_code => $country_name ) {
						$banners_country[] = $country_name;
					}
					$banners_country = implode(', ', $banners_country);
					$banner_country_html = $banners_country;
				}else{
					$banner_country_html = 'All';
				}
			
			} // end else!

		    switch ($column_name) {
				case 'banners_id':
		            echo $id;
		            break;
					
				case 'banners_country':
					echo $banner_country_html;
					break;
					
		        default:
		            break;
		    } // end switch
		}
		
		/**
		 * when the post is saved, save the custom data
		 */
		public function meta_box_save_postdata( $post_id ) 
		{
			global $post;
			
			if( isset($post) ) {
				// do not save if this is an auto save routine
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
					return $post_id;
					
				if($post->post_type == 'age_restriction') {

					$banners_meta = array();

					$options = array();
					$tmp1 =  $this->build_banner_options();
					$options = array_merge_recursive( $options, reset($tmp1) );
					
					// banner sections!
					$this->banner_sections->set_banner_postid( $post_id );
					$this->banner_sections->save_sidebar();
					
					foreach ($options as $box_id => $box) {
						foreach ($box['elements'] as $elm_id => $element){

							if ( $element['type'] == 'html' ) {
								continue 1;
							}
							if ( isset($_POST[$elm_id]) )
								$banners_meta[$box_id][$elm_id] = $_POST[$elm_id];
						}
						
						// banner type
						if ( isset($_POST['choose_banner_type']) )
							$banners_meta[$box_id]['choose_banner_type'] = (string) $_POST['choose_banner_type'];
					}

					update_post_meta( $post_id, '_age_restriction_meta', $banners_meta );
				}
			}
		}


		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionBM Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }
	        
			if ( self::$_instance->the_plugin->is_admin === true ) {
	        	add_action( 'admin_init', array( self::$_instance, '__instanceActions' ) );
			}
	        
	        return self::$_instance;
	    }
	    
	    public function __instanceActions() {

			// change the layout
	    	$screens = array('age_restriction');
		    foreach ($screens as $screen) {

				add_filter( 'manage_edit-' . $screen . '_columns', array( &$this, 'banners_edit_columns' ), 10, 1 );
				add_action( 'manage_' . $screen . '_posts_custom_column', array( $this, 'banners_posts_columns' ), 10, 2 );
		    }
	    }
		
		public function extra_css() 
		{
			ob_start();
		?>
		<style type='text/css' data-action='gimi'>
		.gimi {}
		th#banners_id {width: 40px;}
		th#banners_type {width: 100px;}
		th#banners_status {width: 100px;}
		th#banners_stats {width: 250px;}
		th#banners_country {width: 200px;}
		th#banners_thumbnail {width: 100px;}
		th#date {width: 190px;}
				        
		.age_restriction-statistics {
			font-family: 'Open Sans';
			font-size: 14px;
			font-weight: 600;
		}
			.age_restriction-statistics .age_restriction-statistics-list {
				width: 100%;
				overflow: hidden;
				padding: 0;
				margin: 0;
			}
				.age_restriction-statistics .age_restriction-statistics-list li {
					width: 100%;
					display: block;
					border-bottom: 1px solid #e2e2e2;
					float: left;
					/*padding: 5px 0px 12px 0px;*/
				}
			.age_restriction-statistics .age_restriction-list-value {
				display: inline-block;
			}
			.age_restriction-statistics .age_restriction-list-value.impressions {
				color: #7ac127;
			}
			.age_restriction-statistics .age_restriction-list-value.searches {
				color: #ff750a;
			}
			.age_restriction-statistics .age_restriction-list-value.redirects {
				color: #2470b0;
			}
			.age_restriction-statistics .age_restriction-list-value.date {
				color: #737373;
			}
				.age_restriction-statistics .age_restriction-statistics-list li .age_restriction-list-value:last-child {
					padding: 0px 0px 0px 0px;
					border: none;
				}
		
		
		.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li {
			width: 250px;
			border-bottom: 0px;
			vertical-align: bottom;
		}
		.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li .age_restriction-list-value {
			/*width: 60%;*/
		}
		.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li .age_restriction-list-nb {
			display: inline-block;
			padding-left: 5%;
		}
			.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li .age_restriction-list-value span {
			    display: inline-block;
			    height: 10px;
			    width: 20px;
			}
			.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li:last-child .age_restriction-list-value span {
				border-right: 0px;
			}
			.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li:before {
		    	content:'\2022';
		    	font-size: 25px;
		    	vertical-align: bottom;
			}
			.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li.impressions:before {
				color: #7ac127;
			}
			.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li.searches:before {
				color: #ff750a;
			}
			.age_restriction-statistics .age_restriction-statistics-list.age_restriction-legend li.redirects:before {
				color: #2470b0;
			}
				        </style>
		<?php
			$content = ob_get_contents();
			ob_end_clean();
			echo $content;
		}
    }
}

// Initialize the age_restrictionBM class
$age_restrictionBM = age_restrictionBM::getInstance();  