<?php
/*
* Define class age_restrictionBMFrontend
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/

!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBMFrontend') != true) {
	class age_restrictionBMFrontend
	{
		/*
		* Some required plugin information
		*/
		const VERSION = '1.0';

		/*
		* Store some helpers config
		*/
		public $the_plugin = null;
		private $plugin_settings = array();
		
		protected $module_folder = '';
		protected $module_folder_path = '';

		static protected $_instance;

		
		private $banner_types = array(); // available banner types - init in constructor!
		
		private $bannerTpl = null; // banner template object

		public $page_conditions = array();
		public $page_banners = array(); // banners associated to current page
		public $page_banners_upd = array(); // banners associated to current page - reorder & updated for easier use!
		
		public static $utils = array();
		private $age_restrictionbid = 0;


		/*
		* Required __construct() function that initalizes the AA-Team Framework
		*/
		public function __construct( $parent )
		{
			$this->the_plugin = $parent;
			$this->plugin_settings = $this->the_plugin->getAllSettings( 'array', 'settings' );
			
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
			$this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/restrictions_manager/';

			$this->init();
		}
		
		/**
		 * Singleton pattern
		 * 
	   	*/
		static public function getInstance()
		{
			if (!self::$_instance) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}
		
		/**
		 * Frontend load
		 */
		public function init() {

			$this->banner_types = array(
				'popup' 			=> __( 'Popup', 'age-restriction' ),
			);
			
			if ( !is_admin() ) {
				add_action('wp', array($this, 'frontend_load') );
				
				// banner template object!
				require_once( 'banner.tpl.php' );
				$this->bannerTpl = new age_restrictionBMFrontendTpl( $this->the_plugin );
			}
		}

		public function crawlerDetect($userAgent)
		{
			$crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona| AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler| GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby';
			
			if( isset($this->the_plugin->cfg['config_settings']['crawlers_list']) && trim($this->the_plugin->cfg['config_settings']['crawlers_list']) != '' ) {
		 		$crawlers = $this->the_plugin->cfg['config_settings']['crawlers_list'];
			}
		 	
		 	$isCrawler = (preg_match("/$crawlers/", $userAgent) > 0);
			
			return $isCrawler;
		}
		
		public function frontend_load ()
		{
			// get banners per page
			$this->banner_per_page();
			
			$isEnabled = $this->is_enabled();
			if( $isEnabled === false ) {
				return;
			}
			
			$min_age = $this->page_banners_upd['meta']['minimum_age'];
			 
			$ageValidation_pass = true;
			if( !$_SESSION['ageValidationPassed_' . $min_age . "_bannerID-" . $this->page_banners_upd['ID'] ] ){
				$ageValidation_pass = false;
			}
			
			if( $_COOKIE['ageValidationRemember_' . $min_age . "_bannerID-" . $this->page_banners_upd['ID'] ] ){
				$ageValidation_pass = true;
			}
			
			// If crawler don't restrict content
			if( (isset($this->the_plugin->cfg['config_settings']['allow_crawlers']) && $this->the_plugin->cfg['config_settings']['allow_crawlers'] == 'yes') && 
				$this->crawlerDetect($_SERVER['HTTP_USER_AGENT']) 
			) {
				$ageValidation_pass = true;
			}
			 
			// check validation
			if( !$ageValidation_pass ) {
				remove_action('wp_head', 'feed_links_extra', 3); // This is the main code that removes unwanted RSS Feeds
	            remove_action('wp_head', 'feed_links', 2); // Removes Post and Comment Feeds
	            remove_action('wp_head', 'rsd_link'); // Removes link to RSD + XML
	            remove_action('wp_head', 'wlwmanifest_link'); // Removes the link to Windows manifest
	            remove_action('wp_head', 'index_rel_link'); // Removes the index link
	            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0); // Remove relational links for the posts adjacent to the current post.
	            remove_action('wp_head', 'wp_generator'); // Remove the XHTML generator link
	            remove_action('wp_head', 'rel_canonical'); // Remove canonical url
	            remove_action('wp_head', 'start_post_rel_link', 10, 0); // Remove start link
	            remove_action('wp_head', 'parent_post_rel_link', 10, 0); // Remove previous/next link
	            remove_action('wp_head', 'locale_stylesheet'); // Remove local stylesheet from theme
	              
	            $GLOBALS['this_plugin'] = $this;
				$GLOBALS['banner'] = $this->page_banners_upd;
				     
	            $template = $this->the_plugin->cfg['paths']['design_dir_path'] .'/template_'.($GLOBALS['banner']['meta']['theme']).'/index.php';
				require($template);
				
				// end output
				die();
			}
			
			// Testing option enabled - unset SESSION and delete COOKIE.
			if( isset($this->page_banners_upd['meta']['testing_enabled']) && $this->page_banners_upd['meta']['testing_enabled'] == 'yes' ) { 
				if( $_SESSION['ageValidationPassed_' . $min_age . "_bannerID-" . $this->page_banners_upd['ID']] ) unset($_SESSION['ageValidationPassed_' . $min_age . "_bannerID-" . $this->page_banners_upd['ID'] ]);
				if( $_COOKIE['ageValidationRemember_' . $min_age . "_bannerID-" . $this->page_banners_upd['ID']] ) {
					unset($_COOKIE['ageValidationRemember_' . $min_age . "_bannerID-" . $this->page_banners_upd['ID']]);
					setcookie( "ageValidationRemember_" . $min_age . "_bannerID-" . $this->page_banners_upd['ID'], '', (time() - 300000) );
				}
			}	
		}
		
		public function update_page_data()
		{
			// $page_object = get_queried_object();
			// $page_id     = get_queried_object_id();
		}
		
		public function banner_per_page() {
			// view banner - special page!
			$age_restrictionbid = isset($_REQUEST['age_restrictionbid']) && !empty($_REQUEST['age_restrictionbid']) ? trim($_REQUEST['age_restrictionbid']) : 0;
			$this->age_restrictionbid = $age_restrictionbid;
			if ( empty($this->age_restrictionbid) ) {
				
				// banner sections!
				require_once( $this->module_folder_path . 'app.sections.php' );
				$banner_sections = new age_restrictionBannersPerSections( $this->the_plugin );
				
				$banner_sections->determine_conditions();
				$this->page_conditions = $banner_sections->conditions;
	 
				$this->page_banners = $this->get_banner_per_page_db(
					$this->page_conditions
				);
				
			} else {
				
				$this->page_banners = $this->get_banner_per_id(
					$age_restrictionbid
				);
								
			}
			
			$this->page_banners_upd = $this->where_to_display(
				$this->page_banners
			);
			 
			if ( empty($this->age_restrictionbid) ) {
				// get utils
				self::$utils = $this->the_plugin->get_client_utils();
				
				$this->page_banners_upd = $this->page_by_limitations(
					$this->page_banners_upd
				);
			}
			  
			// one banner per type!
			if ( !empty($this->page_banners_upd) ) {
				$this->page_banners_upd = current($this->page_banners_upd);
			}
			 
  			if ( empty($this->age_restrictionbid) ) {
				// stats - impressions/hits
				global $wpdb;
				if ( !empty($this->page_banners_upd) ) {
					$banner = $this->page_banners_upd;
					
					// banner current hits!
					$current_hits = (int) get_post_meta( $banner['ID'], '_age_restriction_hits', true );
					
					// update banner hits!
					update_post_meta( $banner['ID'], '_age_restriction_hits', (int) ($current_hits + 1) );
					   
					// stats table!
                   	$this->the_plugin->db_custom_insert(
                   		$wpdb->prefix.'age_restriction_stats',
                   		array(
	                        'values' => array(
		                    	'action' 			=> 'hits',
			                   	'banner_id'			=> $banner['ID'],
			                   	'device_type'		=> self::$utils['device_type']['type'],
			                   	'device_type_full'	=> self::$utils['device_type']['device'],
			                   	'ip'				=> self::$utils['client_ip'],
			                   	'country'			=> self::$utils['current_country'],
								'country_code'		=> self::$utils['current_country_code']
							),
							'format' => array(
								'%s', '%d', '%s', '%s', '%s', '%s', '%s'
							)
						),
	                    true
					);
				}
			}

			//$this->json_parse_test();
		}
		
		public function the_styles() {
			if( !wp_style_is('age_restriction_css_gfonts') )
				wp_enqueue_style( 'age_restriction_css_gfonts' , 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' );

			if( !wp_style_is('age_restriction_banners_css') )
				wp_enqueue_style( 'age_restriction_banners_css' , $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'lib/frontend/load-banner-styles.php' );
		}
		public function the_scripts() {
			if( !wp_script_is('jquery') ) { // first, check to see if it is already loaded
				wp_enqueue_script( 'jquery' , 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js' );
			}

			if( !wp_script_is('age_restriction_jquery_nouislider') )
				wp_enqueue_script( 'age_restriction_jquery_nouislider' , $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'lib/frontend/js/jquery.nouislider.min.js', array('jquery') );
			
			if( !wp_script_is('age_restriction_owl_carousel') )
				wp_enqueue_script( 'age_restriction_owl_carousel' , $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'lib/frontend/js/owl.carousel.min.js' );
			
			if( !wp_script_is('age_restriction_banners_js') ) {
				wp_enqueue_script( 'age_restriction_banners_js' , $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'lib/frontend/js/banner.js', array('jquery') );
				wp_localize_script( 'age_restriction_banners_js', 'age_restrictionBMFrontend_ajaxurl', admin_url('admin-ajax.php') );
			}
			
			if( !wp_script_is('age_restriction_countdown.js') )
				wp_enqueue_script( 'age_restriction_countdown.js' , $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'lib/frontend/js/countdown.js', array('jquery', 'age_restriction_banners_js') );
		}
		public function the_header() {
			$isEnabled = $this->is_enabled();

			// the content inserted in the post content
			if ( $isEnabled['isContent'] ) {
				add_filter( 'the_content', array($this, 'update_the_content'), 15 );
			}
			return ;
		}
		public function the_footer() {
			$isEnabled = $this->is_enabled();

			$banner = $this->page_banners_upd;
			if ( empty($banner) ) return ;
			
			if ( !$isEnabled['isFooter'] ) return ;
			
			// keep in mind: only one banner per type remained!
			$theContent = $this->get_tpl_popup();
			if ( !empty($theContent) ) {
				echo ''
				//. '<div class="age_restriction-banners-list' . ($type=='popup' ? ' age_restriction-smartPopup' : '') . '" data-type="' . $type . '" style="display: none;">'
				. $theContent
				//. '</div>'
				;
			}

			return ;
		}
		public function update_the_content($content) {
			return $content; // only POPUP allowed!
			
			$isEnabled = $this->is_enabled();
  
			if ( !$isEnabled['isContent'] ) return $content;
  
			$banner = $this->page_banners_upd;
			if ( empty($banner) ) return $content;
  
			// keep in mind: only one banner per type remained!
			$content = $this->get_tpl_popup();
			
  			if ( !empty($this->age_restrictionbid) ) {
  				$content .= '
  				<script type="text/javascript">
  				jQuery(document).ready(function() {
  					console.log( "dbg" ); 
  				});
  				</script>
  				';
			}
			return $content;
		}


		/**
		 * Usefull
		 */
		// is enabled
		private function is_enabled() {
			$opt = $this->plugin_settings;
			
			$ret = false;
			
			$restriction = $this->page_banners_upd;
			if ( isset($restriction) && is_array($restriction) && count($restriction) > 0 ) {
				return true;
			}
			
			return $ret;
		}
		

		/**
		 * Where to display - banners per page
		 */
		public function where_to_display( $banners_found = array() ) {  
			$ret = array();
			if ( !empty($banners_found) && is_array($banners_found) && count($banners_found) > 0 ) {
				foreach ($banners_found as $info) {
					
					$bannerInfo = $this->the_plugin->get_banner_info($info->ID);
					
					array_push( $ret, $bannerInfo ); 
				} // end foreach
			}
			return $ret;
		}
		
		public function get_banner_per_page_db( $conditions = array() ) {
			if ( empty($conditions) ) return array();

			global $wpdb;
			$conditions = array_map( array($this, 'map_wrap_page_conditions'), $conditions);
			$conditions = implode('|', $conditions);
		
			// a.ID, a.post_content, b.meta_value	
			$q = "
				SELECT a.ID FROM {$wpdb->prefix}posts AS a
				 LEFT JOIN {$wpdb->prefix}postmeta AS b ON a.ID = b.post_id
				 WHERE 1=1
				 AND a.post_status = 'publish' AND a.post_type = 'age_restriction'
				 AND b.meta_key = '_age_restriction_sections'
				 AND b.meta_value REGEXP '$conditions'
				;
			";
			$res = $wpdb->get_results( $q );
			return $res;
		}
		
		public function get_banner_per_id( $id=0 ) {
			$id = (array) $id;
			
			if (empty($id) ) return false;
			
			global $wpdb;
			$idlist = array_map(array($this, 'map_trim'), $id);
			$idlist = array_filter($idlist);
			$idlist = implode(', ', array_map(array($this->the_plugin, 'prepareForInList'), $idlist));
	
			// a.ID, a.post_content, b.meta_value	
			$q = "
				SELECT a.ID, a.post_content FROM {$wpdb->prefix}posts AS a
				# LEFT JOIN {$wpdb->prefix}postmeta AS b ON a.ID = b.post_id
				 WHERE 1=1
				 AND a.post_status = 'publish' AND a.post_type = 'age_restriction_banners'
				# AND !isnull(b.post_id)
				 AND a.ID IN ($idlist)
				# GROUP BY b.post_id
				;
			";
			$res = $wpdb->get_results( $q );
			return $res;
		}
		
		public function page_by_limitations( $banners ) {
			$utils = self::$utils;
			  
			// parse banners
			foreach ($banners as $key => $banner) {

				// banner current hits!
				$current_hits = (int) get_post_meta( $banner['ID'], '_age_restriction_hits', true );
				$utils["current_hits"] = $current_hits;

				if ( !$this->page_banner_verify_status($banner, $utils) ) {
					unset( $banners["$key"] );
				} else {
					// update banner hits!
					//pdate_post_meta( $banner['ID'], '_age_restriction_hits', (int) ($current_hits + 1) );
				}
			}
			
			return $banners;
		}
		
		public function page_banner_verify_status( $banner, $utils ) {
			extract($utils);
			
			$bmeta = $banner['meta'];
			$bid = $banner['ID'];
			
			// disable
			if ( isset($bmeta['enable_for_choose']) && $bmeta['enable_for_choose'] == 'disable' ) {
					return false;
			} // end disable
			
			// enable per me
			if ( isset($bmeta['enable_for_choose']) && $bmeta['enable_for_choose'] == 'me' ) {

				if ( !is_user_logged_in() ) return false;
				
				$post_author_id = get_post_field( 'post_author', $bid );
				
				//$current_user_id = get_current_user_id();
				$current_user = wp_get_current_user(); // Since: 2.0.3
				$current_user_id = (int) $current_user->ID;
  
				// verify banner status!
				if ( $post_author_id != $current_user_id )
					return false;
			} // end per me

			// enable per IP
			if ( isset($bmeta['enable_for_choose']) && $bmeta['enable_for_choose'] == 'iplist' 
				&& isset($bmeta['enable_for_iplist']) && !empty($bmeta['enable_for_iplist']) ) {

				$allowed_ip = $bmeta['enable_for_iplist'];
				$allowed_ip = trim($allowed_ip);
				$allowed_ip = explode(PHP_EOL, $allowed_ip);
  
				// verify banner status!
				if ( !empty($allowed_ip) && is_array($allowed_ip)
					&& !in_array($client_ip, $allowed_ip) )
					return false;
			} // end per IP
			
			// enable per URL
			if ( isset($bmeta['enable_for_url'])
				&& !empty($bmeta['enable_for_url']) ) {

				$allowed_url = $bmeta['enable_for_url'];
				$allowed_url = trim($allowed_url);
  				
				// verify banner status!
				if ( !empty($allowed_url) && $current_url!=$allowed_url )
					return false;
			}
			
			// per Start date & time
			if ( isset($bmeta['startdate']) && !empty($bmeta['startdate']) ) {
				$startdate = $bmeta['startdate'];
				if ( isset($bmeta['starttime']) && !empty($bmeta['starttime']) ) {
					$startdate .= ' ' . $bmeta['starttime'];
				}
				$startdate = strtotime($startdate);
  
				if ( $current_date < $startdate )
					return false;
			}
			
			// per End date & time
			if ( isset($bmeta['enddate']) && !empty($bmeta['enddate']) ) {
				$enddate = $bmeta['enddate'];
				if ( isset($bmeta['endtime']) && !empty($bmeta['endtime']) ) {
					$enddate .= ' ' . $bmeta['endtime'];
				}
				$enddate = strtotime($enddate);

				if ( $current_date > $enddate )
					return false;
			}

			// per Impressions / Hits
			if ( isset($bmeta['views_limit']) && !empty($bmeta['views_limit']) ) {
				$allowed_hits = (int) $bmeta['views_limit'];
				
				if ( $allowed_hits > 0 && $allowed_hits < $current_hits )
					return false;
			}
			
			// per Country
			if( isset($bmeta['selected_countries']) && !empty($bmeta['selected_countries']) && !empty($current_country_code) )
			{   
				$allowed_countries = (array) $bmeta['selected_countries'];
				$allowed_countries = array_map( array($this, 'map_countries_upper'), $allowed_countries);
				
				if ( !in_array($current_country_code, $allowed_countries) )
					return false;
			}
			
			// per Country - check if Country Undetected option is not enforced
			if( empty($current_country_code) && $bmeta['country_undetected'] == 'no' ) {   
				return false;
			}
			
			// per Show only registered/unregistered users
			if ( isset($bmeta['only_unregistered_users'], $bmeta['only_registered_users'])
				&& $bmeta['only_unregistered_users'] == 'yes'
				&& $bmeta['only_registered_users'] == 'yes' ) {
				
				// only registered users verification is used!
				if ( !is_user_logged_in() ) return false;
			} else if ( isset($bmeta['only_unregistered_users'])
				&& $bmeta['only_unregistered_users'] == 'yes' ) {

				if ( is_user_logged_in() ) return false;
			} else if ( isset($bmeta['only_registered_users'])
				&& $bmeta['only_registered_users'] == 'yes' ) {

				if ( !is_user_logged_in() ) return false;
			}
  
			// per Don't show me this again
			if ( isset($bmeta['dont_show_me_again'])
				&& $bmeta['dont_show_me_again'] == 'yes' ) {
				
				$cookie_dont_showme = 'age_restriction_banners_dont_showme_' . $bid;
				//$this->the_plugin->cookie_del(array('name' => $cookie_dont_showme));
				if ( isset($_COOKIE["$cookie_dont_showme"])
					&& $_COOKIE["$cookie_dont_showme"] == 'yes' )
					return false;
			}
			
			// per Mobile
			if ( isset($bmeta['users_devices'])
				&& $bmeta['users_devices'] != 'both' ) {

				//require_once( $this->the_plugin->cfg['paths']["scripts_dir_path"] . '/mobile-detect/Mobile_Detect.php' );
				//$mobileDetect = new ageRestriction_Mobile_Detect();
			
				if ( $bmeta['users_devices'] == 'mobile' ) {
					// Any mobile device (phones or tablets).
					//if ( !$mobileDetect->isMobile() ) return false;
					if ( !$isMobile ) return false;

				} else if ( $bmeta['users_devices'] == 'desktop' ) {
					// Any mobile device (phones or tablets).
					//if ( $mobileDetect->isMobile() ) return false;
					if ( $isMobile ) return false;
				}
			}
			
			// pased all limitations verifications
			return true;
		}


		/**
		 * How to display - banners per page
		 */
		private function bannerJsOptions() {
			// banner banner type options!
			require_once( $this->module_folder_path . 'app.banner_type.php' );
			$banner_type = new age_restrictionBannersType( $this->the_plugin );
			$__banner_tabs = $banner_type->get_tabs();
			if ( empty($__banner_tabs) ) return array();
			
			$ret = array();
			foreach ( $__banner_tabs as $tabid => $tabinfo ) {
				$tabid2 = (int) str_replace('__tab', '', $tabid);
				if ( $tabid2 == 2 ) continue 1;
				
				$ret[] = $tabinfo[1];
			}
			//$ret[] = 'open_search_products';
  
			$ret = implode(', ', $ret);
			$ret = explode(',', $ret);
			$ret = array_unique($ret);
			$ret = array_map(array($this, 'map_trim'), $ret);
  
			return $ret;
		}

		private function setOptionsJS() {
			$tblList = array();
			
			$restriction = $this->page_banners_upd; 
			if ( !isset($restriction) ) return '';

			$banner_jsoptions_fields = $this->bannerJsOptions();
			  
			// keep in mind: only one banner per type remained!
			foreach ($banners as $type => $banner) {
				
				$toolbarPms = array();

				// meta
				$meta = $banner['meta'];
				if ( empty($meta) ) continue 1;
				foreach ($meta as $optkey => $optval) {
					if ( in_array($optkey, $banner_jsoptions_fields) ) {
						$toolbarPms["$optkey"] = $optval;
					}
					
					if ( $optkey == 'background_image' ) {
						$optval = trim($optval);
						if ( !empty($optval) ) {
							$image = wp_get_attachment_image_src( (int) $optval, 'full' );
							if ( isset($image[0]) && !empty($image[0]) ) $image = $image[0];

							$toolbarPms["$optkey"] = $image;
						}
					}
				}
				
				if ( !empty($toolbarPms) )
					$tblList["$type"] = $toolbarPms;
			}

			if ( !empty($tblList) ) {
				$tblList["is_admin_bar_showing"] = is_admin_bar_showing() ? 'yes' : 'no';

				$tblList = json_encode($tblList);
				$tblList = htmlentities($tblList);
  
  				$tblList = PHP_EOL . "<!-- start/ " . ($this->the_plugin->details['plugin_name']) . "/ AgeRestrict custom js -->" . PHP_EOL
				. '<div id="age_restriction-banners-options" style="display: none;" data-options="' . $tblList . '"></div>'
				. PHP_EOL . "<!-- end/ " . ($this->the_plugin->details['plugin_name']) . "/ AgeRestrict custom js -->" . PHP_EOL;
				return $tblList;
			}

			return '';
		}

		public function setOptionsCss() {
			$tblList = array();
			
			$restriction = $this->page_banners_upd; 
			if ( !isset($restriction) ) return '';
			
			// keep in mind: only one banner per type remained!
			foreach ($banners as $type => $banner) {
				$toolbarPms = array();

				$meta = $banner['meta'];
				if ( empty($meta) ) continue 1;
				foreach ($meta as $optkey => $optval) {
					if ( in_array($optkey, array('custom_css')) ) {
						$toolbarPms["$optkey"] = $optval;
					}
				}

				if ( !empty($toolbarPms) )
					$tblList["$type"] = implode(PHP_EOL, $toolbarPms);
			}
			
			if ( !empty($tblList) ) {
				$tblList = implode(PHP_EOL, $tblList);
			
				$tblList = PHP_EOL . "<!-- start/ " . ($this->the_plugin->details['plugin_name']) . "/ AgeRestrict custom css -->" . PHP_EOL
				. '<style type="text/css">' . PHP_EOL
				. $tblList
				. PHP_EOL . '</style>'
				. PHP_EOL . "<!-- end/ " . ($this->the_plugin->details['plugin_name']) . "/ AgeRestrict custom css -->" . PHP_EOL;
				return $tblList;
			}
			return ''; 
		}
		 
		 
		/**
		 * What to display - banners per page
		 */
		public function json_parse_test() {
			$restriction = $this->page_banners_upd; 
			if ( !isset($restriction) ) return '';
  
			foreach ($banners as $type => $banner) {
				$discount = $banner['discount'];
				$discount = unserialize($discount);
				$discount = json_decode($discount);
				
				var_dump('<pre>banner: ',$type, $banner['ID'],'</pre>');

				$json = array('obj' => $discount);
				$countries = $this->the_plugin->
				get_banner_countries($json, true, 'all');
				if (!isset($countries->result) || empty($countries->result)) {
					var_dump('<pre>', 'no country','</pre>');
					continue 1;
				}
				var_dump('<pre>countries: ', $countries->result,'</pre>');

				foreach ($countries->result as $country) {
					$categories = $this->the_plugin->
					get_banner_categories($country, $json, true, 'all');
					if (!isset($categories->result) || empty($categories->result)) {
						var_dump('<pre>', $country, 'no category','</pre>');
						continue 1;
					}
					var_dump('<pre>categories: ', $country, $categories->result,'</pre>');
					
					foreach ($categories->result as $category) {
						$params = $this->the_plugin->
						get_banner_params($country, $category, $json, true, 'all');
						if (!isset($params->result) || empty($params->result)) {
							var_dump('<pre>', $country, $category, 'no params','</pre>');
							continue 1;
						}
						var_dump('<pre>params: ', $country, $category, $params->result,'</pre>');  
					}
				}
			}
		}

		public function get_tpl_popup() {
			$banner = $this->page_banners_upd;
			if ( empty($banner) ) return '';
			
			// banner templates!
			$this->bannerTpl->set_banner_info( $banner );
			return $this->bannerTpl->get_tpl_popup();
		}

	
		/**
		 * Array Map functions!
		 */
		private function map_wrap_page_conditions( $arr ) {
			return '"' . $arr . '"';
		}
		private function map_countries_upper($arr) {
			return strtoupper($arr);
		}
		private function map_trim($arr) {
			return trim($arr);
		}
	}
}

// Initialize the age_restrictionBMFrontend class
//$age_restrictionBMFrontend = new age_restrictionBMFrontend();
