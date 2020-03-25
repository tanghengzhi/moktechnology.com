<?php 
/**
 * age_restrictionBannersPerSections class
 * ================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 * @access 		public
 * @return 		void
 */  
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBannersPerSections') != true) {
    class age_restrictionBannersPerSections
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
        public $the_plugin = null;
		public $the_theme = null;

		private $module_folder = '';

		static protected $_instance;
		
		public $conditions = array();
		public $conditions_headings = array();
		public $conditions_reference = array();
		
		public $postid = 0;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $the_plugin, $postid=0 )
        {
			$this->the_plugin = $the_plugin;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
			
			$this->set_banner_postid( $postid );
        }

		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionBannersPerSections Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
		public function sidebar_replacement()
		{
			$this->determine_conditions();
			$this->match_sidebars();
		}
		
		public function determine_conditions() 
		{
			$this->is_hierarchy();
			$this->is_taxonomy();
			$this->is_post_type_archive();
			$this->is_page_template(); 
		}
		
		public function match_sidebars()
		{
			if( 
				isset($this->the_theme->coreFunctions->data['sidebar'])  && 
				count($this->the_theme->coreFunctions->data['sidebar']) > 0 && 
				count($this->conditions) > 0 
			){
				$this->the_theme->coreFunctions->data['page_sidebars'] = array();
				foreach ($this->the_theme->coreFunctions->data['sidebar'] as $sidebar_key => $sidebar_value) {					
					if( isset($sidebar_value['settings']['conditions']) && count($sidebar_value['settings']['conditions']) > 0 ){
						
						// find if any current page condition found into sidebar condition
						$found = false;
						foreach( $this->conditions as $condition ){
							if( in_array($condition, $sidebar_value['settings']['conditions']) && in_array($sidebar_value['settings']['position'], array('left', 'right'))){
								$found = true;
							}
						}
						
						if( $found === true ){
							$this->the_theme->coreFunctions->data['page_sidebars'][$sidebar_key] = $sidebar_value;
						}
					}
				}
			}
		}
		
		public function setup_default_conditions_reference()
		{
			$conditions = array();
			$conditions_headings = array();
	
			// Get an array of the different post status labels, in case we need it later.
			$post_statuses = get_post_statuses();
	
			// Pages
			$conditions['pages'] = array();
	
			$statuses_string = join( ',', array_keys( $post_statuses ) );
			$pages = get_pages( array( 'post_status' => $statuses_string ) );
	
			if ( count( $pages ) > 0 ) {
	
				$conditions_headings['pages'] = __( 'Pages', 'age-restriction' );
	
				foreach ( $pages as $k => $v ) {
					$token = 'post-' . $v->ID;
					$label = esc_html( $v->post_title );
					if ( 'publish' != $v->post_status ) {
						$label .= ' (' . $post_statuses[$v->post_status] . ')';
					}
					
					$pagelink = get_permalink($v->ID);
					$pagelink_full = '<a href="' . $pagelink . '" target="_blank">' . (__('view page', 'age-restriction')) . '</a>';
					$label .= ' - ' . $pagelink_full;
	
					$conditions['pages'][$token] = array(
						'label' => $label,
						'description' => sprintf( __( 'The "%s" page', 'age-restriction' ), $v->post_title )
					);
				}
	
			}
	
			$args = array(
				'show_ui' => true,
				'public' => true,
				'publicly_queryable' => true,
				'_builtin' => false
			);
	
			$post_types = get_post_types( $args, 'object' );
	
			// Set certain post types that aren't allowed to have custom sidebars.
			$disallowed_types = array( 'slide' );
	
			// Make the array filterable.
			$disallowed_types = apply_filters( $this->the_plugin->alias.'_disallowed_post_types', $disallowed_types );
	
			if ( count( $post_types ) ) {
				foreach ( $post_types as $k => $v ) {
					if ( in_array( $k, $disallowed_types ) ) {
						unset( $post_types[$k] );
					}
				}
			}
	
			// Add per-post support for any post type that supports it.
			$args = array(
					'show_ui' => true,
					'public' => true,
					'publicly_queryable' => true,
					'_builtin' => true
					);
	
			$built_in_post_types = get_post_types( $args, 'object' );
	
			foreach ( $built_in_post_types as $k => $v ) {
				if ( $k == 'post' ) {
					$post_types[$k] = $v;
					break;
				}
			}
			
			// Page Templates
			$conditions['templates'] = array();
	
			$page_templates = wp_get_theme()->get_page_templates();
	
			if ( count( $page_templates ) > 0 ) {
	
				$conditions_headings['templates'] = __( 'Page Templates', 'age-restriction' );
  
				foreach ( $page_templates as $k => $v ) {
					$token = str_replace( '.php', '', 'page-template-' . $k );
					$conditions['templates'][$token] = array(
										'label' => $v,
										'description' => sprintf( __( 'The "%s" page template', 'age-restriction' ), $v )
										);
				}
			}
	
			// Post Type Archives
			$conditions['post_types'] = array();
	
			if ( count( $post_types ) > 0 ) {
	
				$conditions_headings['post_types'] = __( 'Post Types', 'age-restriction' );
	
				foreach ( $post_types as $k => $v ) {
					$token = 'post-type-archive-' . $k;
	
					if ( $v->has_archive ) {
						$conditions['post_types'][$token] = array(
											'label' => sprintf( __( '"%s" Post Type Archive', 'age-restriction' ), $v->labels->name ),
											'description' => sprintf( __( 'The "%s" post type archive', 'age-restriction' ), $v->labels->name )
											);
					}
				}
	
				foreach ( $post_types as $k => $v ) {
					$token = 'post-type-' . $k;
					$conditions['post_types'][$token] = array(
										'label' => sprintf( __( 'Each Individual %s', 'age-restriction' ), $v->labels->singular_name ),
										'description' => sprintf( __( 'Entries in the "%s" post type', 'age-restriction' ), $v->labels->name )
										);
				}
	
			}
	
			// Taxonomies and Taxonomy Terms
			$conditions['taxonomies'] = array();
	
			$args = array(
						'public' => true
						);
	
			$taxonomies = get_taxonomies( $args, 'objects' );
	
			if ( count( $taxonomies ) > 0 ) {
	
				$conditions_headings['taxonomies'] = __( 'Taxonomy Archives', 'age-restriction' );
	
				foreach ( $taxonomies as $k => $v ) {
					$taxonomy = $v;
	
					if ( $taxonomy->public == true ) {
						$conditions['taxonomies']['archive-' . $k] = array(
											'label' => esc_html( $taxonomy->labels->name ) . ' (' . esc_html( $k ) . ')',
											'description' => sprintf( __( 'The default "%s" archives', 'age-restriction' ), strtolower( $taxonomy->labels->name ) )
											);
	
						// Setup each individual taxonomy's terms as well.
						$conditions_headings['taxonomy-' . $k] = $taxonomy->labels->name;
						$terms = get_terms( $k );
						if ( count( $terms ) > 0 ) {
							$conditions['taxonomy-' . $k] = array();
							foreach ( $terms as $i => $j ) {
								$conditions['taxonomy-' . $k]['term-' . $j->term_id] = array( 'label' => esc_html( $j->name ), 'description' => sprintf( __( 'The %s %s archive', 'age-restriction' ), esc_html( $j->name ), strtolower( $taxonomy->labels->name ) ) );
								if ( $k == 'category' ) {
									$conditions['taxonomy-' . $k]['in-term-' . $j->term_id] = array( 'label' => sprintf( __( 'All posts in "%s"', 'age-restriction' ), esc_html( $j->name ) ), 'description' => sprintf( __( 'All posts in the %s %s archive', 'age-restriction' ), esc_html( $j->name ), strtolower( $taxonomy->labels->name ) ) );
								}
							}
						}
	
					}
				}
			}
	
			$conditions_headings['hierarchy'] = __( 'Template Hierarchy', 'age-restriction' );
	
			// Template Hierarchy
			$conditions['hierarchy']['page'] = array(
										'label' => __( 'Pages', 'age-restriction' ),
										'description' => __( 'Displayed on all pages that don\'t have a more specific widget area.', 'age-restriction' )
										);
	
			$conditions['hierarchy']['search'] = array(
										'label' => __( 'Search Results', 'age-restriction' ),
										'description' => __( 'Displayed on search results screens.', 'age-restriction' )
										);
	
			$conditions['hierarchy']['home'] = array(
										'label' => __( 'Default "Your Latest Posts" Screen', 'age-restriction' ),
										'description' => __( 'Displayed on the default "Your Latest Posts" screen.', 'age-restriction' )
										);
	
			$conditions['hierarchy']['front_page'] = array(
										'label' => __( 'Front Page', 'age-restriction' ),
										'description' => __( 'Displayed on any front page, regardless of the settings under the "Settings -> Reading" admin screen.', 'age-restriction' )
										);
	
			$conditions['hierarchy']['single'] = array(
										'label' => __( 'Single Entries', 'age-restriction' ),
										'description' => __( 'Displayed on single entries of any public post type other than "Pages".', 'age-restriction' )
										);
	
			$conditions['hierarchy']['archive'] = array(
										'label' => __( 'All Archives', 'age-restriction' ),
										'description' => __( 'Displayed on all archives (category, tag, taxonomy, post type, dated, author and search).', 'age-restriction' )
										);
	
			$conditions['hierarchy']['author'] = array(
										'label' => __( 'Author Archives', 'age-restriction' ),
										'description' => __( 'Displayed on all author archive screens (that don\'t have a more specific sidebar).', 'age-restriction' )
										);
	
			$conditions['hierarchy']['date'] = array(
										'label' => __( 'Date Archives', 'age-restriction' ),
										'description' => __( 'Displayed on all date archives.', 'age-restriction' )
										);
	
			$conditions['hierarchy']['404'] = array(
										'label' => __( '404 Error Screens', 'age-restriction' ),
										'description' => __( 'Displayed on all 404 error screens.', 'age-restriction' )
										);
	
			$this->conditions_reference = (array)apply_filters( 'woo_conditions_reference', $conditions );
			$this->conditions_headings = (array)apply_filters( 'woo_conditions_headings', $conditions_headings );
		}

		public function admin_options_panel()
		{
			$html = '';
			
			$sidebars_meta = get_option( 'kingdom_dynamic_sidebars', true );
			
			$html .= '<div class="age_restriction-form-row">';
			$html .= 	'<label for="AccessKeyID">Select Sidebar</label>';
			$html .= 	'<div class="age_restriction-form-item large">';
			$html .= 	'<span class="formNote">Are required in order setup a sidebar per section.</span>';
			$html .= 		'<select class="age_restriction_sidebar_selector" name="age_restriction_sidebar_selector" style="width:180px;">';
			$html .= 			'<option value="">Select a sidebar</option>';
			
			if( $sidebars_meta && count($sidebars_meta) > 0 ){
				if( count($sidebars_meta) > 0 ){
					foreach ( $sidebars_meta as $sidebar ) {
						$html .= '<option value="' . ( sanitize_title( $sidebar['title'] ) ) . '">' . ( $sidebar['title'] ) . '</option>';
					}
				}
			}
			$html .= 		'</select>';
			$html .= 	'</div>';
			$html .= '</div>';
			
			$html .= '<div id="age_restriction-conditions-ajax"></div>';
			
			$html .= '<div style="display:none;" id="wwcAmzAff-status-box" class="wwcAmzAff-message"></div>';
			
			return $html;
		}

		public function get_sidebar_conditions()
		{
			$current_sidebar = '';
			
			if ( count( $this->conditions_reference ) <= 0 ) $this->setup_default_conditions_reference();
			
			$post_id = $this->postid;
			$sidebar = get_post_meta( $post_id, '_age_restriction_sections', true );

			$selected_conditions = isset($sidebar['banner']) ? $sidebar['banner'] : array();

			$html = '';
			
			$html .= '<div class="age_restriction-conditions-select">';
	
			if ( count( $this->conditions_reference ) > 0 ) {
	
				// Separate out the taxonomy items for use as sub-tabs of "Taxonomy Terms".
				$taxonomy_terms = array();
				 
				foreach ( $this->conditions_reference as $k => $v ) {
					if ( substr( $k, 0, 9 ) == 'taxonomy-' ) {
						$taxonomy_terms[$k] = $v;
						unset( $this->conditions_reference[$k] );
					}
				}
				
				$html .= '<div id="taxonomy-category" class="categorydiv tabs age_restriction-conditions">' . "\n";
	
					$html .= '<ul id="category-tabs" class="conditions-tabs alignleft">' . "\n";
	
					$count = 0;
	
					foreach ( $this->conditions_reference as $k => $v ) {
						$count++;
						$class = '';
						if ( $count == 1 ) {
							$class = 'tabs';
						} else {
							$class = 'hide-if-no-js';
						}
						if ( in_array( $k, array( 'pages' ) ) ) {
							$class .= ' basic';
						}
	
						if ( isset( $this->conditions_headings[$k] ) ) {
							$html .= '<li class="' . esc_attr( $class ) . '"><a href="#tab-' . esc_attr( $k ) . '">' . esc_html( $this->conditions_headings[$k] ) . '</a></li>' . "\n";
						}
	
						if ( $k == 'taxonomies' ) {
							$html .= '<li class="' . esc_attr( $class ) . '"><a href="#tab-taxonomy-terms">' . __( 'Taxonomy Terms', 'woosidebars' ) . '</a></li>' . "\n";
						}
					}
	
					$class = 'hide-if-no-js advanced';
	
					$html .= '</ul>' . "\n";
					 
				foreach ( $this->conditions_reference as $k => $v ) {
					if( is_array($v) && count($v) > 0 ) {
						$count = 0;
		
						$tab = '';
		
						$tab .= '<div id="tab-' . esc_attr( $k ) . '" class="condition-tab">' . "\n";
						$tab .= '<h4>' . esc_html( $this->conditions_headings[$k] ) . '</h4>' . "\n";
						$tab .= '<ul class="alignleft conditions-column">' . "\n";
							foreach ( $v as $i => $j ) {
								$count++;
		
								$checked = '';
								if ( isset($selected_conditions) && is_array($selected_conditions) && in_array( $i, $selected_conditions ) ) {
									$checked = ' checked="checked"';
								}
								$tab .= '<li><label class="selectit" title="' . esc_attr( $j['description'] ) . '"><input type="checkbox" name="conditions[]" value="' . $i . '" id="checkbox-' . $i . '"' . $checked . ' /> ' . ( $k == 'pages' ? $j['label'] : esc_html( $j['label'] ) ) . '</label></li>' . "\n";
		
								if ( $count % 10 == 0 && $count < ( count( $v ) ) ) {
									$tab .= '</ul><ul class="alignleft conditions-column">';
								}
							}
						$tab .= '</ul>' . "\n";
						// Filter the contents of the current tab.
						$tab = apply_filters( 'woo_conditions_tab_' . esc_attr( $k ), $tab );
						$html .= $tab;
						$html .= '<div class="clear"></div>';
						$html .= '</div>' . "\n";
					}
				}
	
				// Taxonomy Terms Tab
				$html .= '<div id="tab-taxonomy-terms" class="condition-tab inner-tabs">' . "\n";
						$html .= '<ul class="conditions-tabs-inner hide-if-no-js">' . "\n";
				foreach ( $taxonomy_terms as $k => $v ) {
					if ( ! isset( $this->conditions_headings[$k] ) ) { unset( $taxonomy_terms[$k] ); }
				}
	
				$count = 0;
				foreach ( $taxonomy_terms as $k => $v ) {
					$count++;
					$class = '';
					if ( $count == 1 ) {
						$class = 'tabs';
					} else {
						$class = 'hide-if-no-js';
					}
	
					$html .= '<li><a href="#tab-' . $k . '" title="' . __( 'Taxonomy Token', 'age-restriction' ) . ': ' . str_replace( 'taxonomy-', '', $k ) . '" style="padding:0 1em;">' . esc_html( $this->conditions_headings[$k] ) . '</a>';
						if ( $count != count( $taxonomy_terms ) ) {
							$html .= ' |';
						}
					$html .= '</li>' . "\n";
				}
	
				$html .= '</ul>' . "\n";
	
				foreach ( $taxonomy_terms as $k => $v ) {
					$count = 0;
	
					$html .= '<div id="tab-' . $k . '" class="condition-tab">' . "\n";
					$html .= '<h4>' . esc_html( $this->conditions_headings[$k] ) . '</h4>' . "\n";
					$html .= '<ul class="alignleft conditions-column">' . "\n";
						foreach ( $v as $i => $j ) {
							$count++;
	
							$checked = '';
							if ( isset($selected_conditions) && is_array($selected_conditions) && in_array( $i, $selected_conditions ) ) {
								$checked = ' checked="checked"';
							}
							$html .= '<li><label class="selectit" title="' . esc_attr( $j['description'] ) . '"><input type="checkbox" name="conditions[]" value="' . $i . '" id="checkbox-' . esc_attr( $i ) . '"' . $checked . ' /> ' . esc_html( $j['label'] ) . '</label></li>' . "\n";
	
							if ( $count % 10 == 0 && $count < ( count( $v ) ) ) {
								$html .= '</ul><ul class="alignleft conditions-column">';
							}
						}
	
					$html .= '</ul>' . "\n";
					$html .= '<div class="clear"></div>';
					$html .= '</div>' . "\n";
				}
				$html .= '</div>' . "\n";
			}
	
			// Allow themes/plugins to act here (key, args).
			do_action( 'woo_conditions_meta_box', $k, $v );
	
			$html .= '<br class="clear" />' . "\n";
			
			$html .= '</div></div>' . "\n";
			$html .= '<script>jQuery(document).ready(function(){
				jQuery( ".age_restriction-conditions-select .age_restriction-conditions.tabs" ).tabs();
				jQuery( ".age_restriction-conditions-select .age_restriction-conditions.tabs .inner-tabs" ).tabs();
			})</script>' . "\n";
			
			/*die( json_encode( array(
				'status' => 'valid',
				'html' => $html
			)) );*/
			return $html;
		}

		public function save_sidebar()
		{
			$post_id = $this->postid;

			$settings = isset($_REQUEST['conditions']) ? $_REQUEST['conditions'] : '';
			$settings_new = array();
			$settings_new['banner'] = $settings;

			update_post_meta( $post_id, '_age_restriction_sections', $settings_new );
			
			return $settings;
		}

		public function is_hierarchy() 
		{
			if ( is_front_page() && ! is_home() ) {
				$this->conditions[] = 'static_front_page';
			}
	
			if ( ! is_front_page() && is_home() ) {
				$this->conditions[] = 'inner_posts_page';
			}
	
			if ( is_front_page() ) {
				$this->conditions[] = 'front_page';
			}
	
			if ( is_home() ) {
				$this->conditions[] = 'home';
			}
	
			if ( is_singular() ) {
				$this->conditions[] = 'singular';
			}
	
			if ( is_single() ) {
				$this->conditions[] = 'single';
			}
	
			if ( is_single() || is_singular() ) {
				$this->conditions[] = 'post-type-' . get_post_type();
				$this->conditions[] = get_post_type();
	
				$categories = get_the_category( get_the_ID() );
	
				if ( ! is_wp_error( $categories ) && ( count( $categories ) > 0 ) ) {
					foreach ( $categories as $k => $v ) {
						$this->conditions[] = 'in-term-' . $v->term_id;
					}
				}
	
				$this->conditions[] = 'post' . '-' . get_the_ID();
			}
	
			if ( is_search() ) {
				$this->conditions[] = 'search';
			}
	
			if ( is_home() ) {
				$this->conditions[] = 'home';
			}
	
			if ( is_front_page() ) {
				$this->conditions[] = 'front_page';
			}
	
			if ( is_archive() ) {
				$this->conditions[] = 'archive';
			}
	
			if ( is_author() ) {
				$this->conditions[] = 'author';
			}
	
			if ( is_date() ) {
				$this->conditions[] = 'date';
			}
	
			if ( is_404() ) {
				$this->conditions[] = '404';
			}
		}
	
		public function is_taxonomy() 
		{
			if ( ( is_tax() || is_archive() ) && ! is_post_type_archive() ) {
				$obj = get_queried_object();
	
				if ( ! is_category() && ! is_tag() ) {
					$this->conditions[] = 'taxonomies';
				}
	
				if ( is_object( $obj ) ) {
					$this->conditions[] = 'archive-' . $obj->taxonomy;
					$this->conditions[] = 'term-' . $obj->term_id;
				}
			}
		}
	
		public function is_post_type_archive() 
		{
			if ( is_post_type_archive() ) {
				$this->conditions[] = 'post-type-archive-' . get_post_type();
			}
		}
	
		public function is_page_template() 
		{
			if ( is_singular() ) {
				global $post;
				$template = get_post_meta( $post->ID, '_wp_page_template', true );
	
				if ( $template != '' && $template != 'default' ) {
					$this->conditions[] = str_replace( '.php', '', 'page-template-' . $template );
				}
			}
		}


		/**
		 * Extra
		 */
		public function set_banner_postid($postid) {
			$this->postid = (int) $postid;
		}
	}
}

//new age_restrictionBannersPerSections();