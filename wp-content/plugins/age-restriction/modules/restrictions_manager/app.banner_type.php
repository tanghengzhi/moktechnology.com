<?php 
/**
 * age_restrictionBannersType class
 * ================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 * @access 		public
 * @return 		void
 */  
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBannersType') != true) {
    class age_restrictionBannersType
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
        public $the_plugin = null;
		public $amzHelper = null;

		private $module_folder = '';
		private $module_folder_path = '';

		static protected $_instance;
		
		public $postid = 0;
		private $elements = array();
		private $tabs = array();

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $the_plugin, $postid=0 )
        {
			$this->the_plugin = $the_plugin;
			if ( isset($this->the_plugin->amzHelper) && !empty($this->the_plugin->amzHelper) ) {
				$this->amzHelper = $this->the_plugin->amzHelper;
			} else {
				require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'aa-framework/amz.helper.class.php' );
				if( class_exists('age_restrictionAmazonHelper') ){
					$this->amzHelper = age_restrictionAmazonHelper::getInstance( $this->the_plugin, false);
				}
			}
			
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
			$this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/restrictions_manager/';

			$this->set_banner_postid( $postid );
			
			$this->config = @unserialize( get_option( $this->the_plugin->alias . '_settings' ) );
			  
			if ( $this->the_plugin->is_admin === true ) {
			}
        }
		
		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionBannersType Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
		
		/**
		 * Options
		 */
		private function setup() {
			$current_utc_datetime = gmdate("M d Y H:i:s");

			// elements
			$this->elements = array(
				/* commons - design */
				'theme' => array(
					'type' 		=> 'image_picker',
					'std' 		=> '1',
					'title' 	=> __('Theme:', 'age-restriction'),
					'desc' 		=> __('Select theme to use.', 'age-restriction'),
					'options'	=> array( 1, 2, 3, 4 ),
					'size' => 'large',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom')
				),
				
				'logo' => array(
					'type' 		=> 'upload_image_wp',
					'size' 		=> 'large',
					'title' 	=> __('Logo:', 'age-restriction'),
					'value' 	=> __('Upload image', 'age-restriction'),
					'thumbSize' => array(
						'w' => '100',
						'h' => '100',
						'zc' => '2',
					),
					'desc' 		=> __('Banner logo.', 'age-restriction'),
					'preview_size'	=> 'thumbnail',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'background_image' => array(
					'type' 		=> 'upload_image_wp',
					'size' 		=> 'large',
					'title' 	=> __('Background image:', 'age-restriction'),
					'value' 	=> __('Upload image', 'age-restriction'),
					'thumbSize' => array(
						'w' => '100',
						'h' => '100',
						'zc' => '2',
					),
					'desc' 		=> __('If not set, a default one will be used according to each theme style.', 'age-restriction'),
					'preview_size'	=> 'thumbnail',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'background_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> '000000',
					'size' 		=> 'large',
					'title' 	=> __('Background color:', 'age-restriction'),
					'desc' 		=> __('If background color is set, the background image will not be used.', 'age-restriction'),
					'cssclass'	=> array('popup')
				),
				
				'box_background_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> 'FFFFFF',
					'size' 		=> 'large',
					'title' 	=> __('Box Background color:', 'age-restriction'),
					'desc' 		=> __('If no background color is set for the box, the background will be transparent.', 'age-restriction'),
					'cssclass'	=> array('popup')
				),

				'box_background_opacity' => array(
					'type' 		=> 'range_input',
					'std'		=> '100',
					'size' 		=> 'large',
					'title' 	=> __('Box Background opacity:', 'age-restriction'),
					'desc' 		=> __('If no background opacity is set for the box, the default opacity will be 1.', 'age-restriction'),
					'cssclass'	=> array('popup')
				),
				
				'text_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> '363636',
					'size' 		=> 'large',
					'title' 	=> __('Text color:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				
				'text_hover_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> 'A3822D',
					'size' 		=> 'large',
					'title' 	=> __('Hover/Active text color:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				
				'country_selection' => array(
					'type' 		=> 'select',
					'std' 		=> 'yes',
					'size' 		=> 'large',
					'force_width'=> '100',
					'title' 	=> __('Show Country Selection:', 'age-restriction'),
					'desc' 		=> __('Enable Country Selection', 'age-restriction'),
					'options'	=> array(
						'yes' 			=> __('YES', 'age-restriction'),
						'no' 			=> __('NO', 'age-restriction'),
					),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'date_format' => array(
					'type' 		=> 'select',
					'std' 		=> 'MM/DD/YYYY',
					'size' 		=> 'large',
					'force_width'=> '130',
					'title' 	=> __('Date format:', 'age-restriction'),
					'desc' 		=> __('Order the fields', 'age-restriction'),
					'options'	=> array(
						'MM/DD/YYYY' 	=> 'MM/DD/YYYY',
						'DD/MM/YYYY' 	=> 'DD/MM/YYYY',
						'YYYY/DD/MM' 	=> 'YYYY/DD/MM',
						'YYYY/MM/DD' 	=> 'YYYY/MM/DD',
					),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'restriction_title' => array(
					'type' 		=> 'text',
					'size' 		=> 'large',
					'title' 	=> __('Title:', 'age-restriction'),
					'std'		=> 'PLEASE SELECT YOUR DATE OF BIRTH',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'title_text_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> '363636',
					'size' 		=> 'large',
					'title' 	=> __('Title text color:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				
				'text_before' => array(
					'type' 		=> 'textarea-wysiwyg',
					'size' 		=> 'large',
					'title' 	=> __('Text before:', 'age-restriction'),
					'std'		=> 'IN ORDER TO VIEW THIS PAGE YOU MUST BE AT LEAST [minimum_age] YEARS OLD.',
					'desc' 		=> __('Custom text before birth date selection.', 'age-restriction'),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'text_color_before' => array(
					'type' 		=> 'color_picker',
					'std'		=> '363636',
					'size' 		=> 'large',
					'title' 	=> __('Text color before:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				
				'text_after' => array(
					'type' 		=> 'textarea-wysiwyg',
					'size' 		=> 'large',
					'title' 	=> __('Text after:', 'age-restriction'),
					'std'		=> '',
					'desc' 		=> __('Custom text after "ENTER" button.', 'age-restriction'),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'text_color_after' => array(
					'type' 		=> 'color_picker',
					'std'		=> '363636',
					'size' 		=> 'large',
					'title' 	=> __('Text color after:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				
				'enter_btn_title' => array(
					'type' 		=> 'text',
					'size' 		=> 'large',
					'title' 	=> __('"ENTER" button title:', 'age-restriction'),
					'std'		=> 'ENTER',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				'enter_btn_bg_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> '000000',
					'size' 		=> 'large',
					'title' 	=> __('Submit button background color:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				
				'enter_btn_text_color' => array(
					'type' 		=> 'color_picker',
					'std'		=> 'FFFFFF',
					'size' 		=> 'large',
					'title' 	=> __('Submit button text color:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup')
				),
				'minimum_age_error_message' => array(
					'type' 		=> 'text',
					'size' 		=> 'large',
					'title' 	=> __('Minimum age ERROR message:', 'age-restriction'),
					'std'		=> 'Minimum age required',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				/* commons - custom css */
				'custom_css' => array(
					'type' 		=> 'textarea',
					'size' 		=> 'large',
					'title' 	=> __('Custom CSS:', 'age-restriction'),
					'std'		=> '',
					'desc' 		=> __('Restriction Custom CSS.', 'age-restriction'),
					'height'	=> '400px',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				/* commons - limitations */
				'minimum_age' => array(
					'type' 		=> 'text',
					'size' 		=> 'large',
					'force_width' => 50,
					'title' 	=> __('Minimum Age:', 'age-restriction'),
					'desc' 		=> __('This is usefull if you want to restrict to a specific age on a specific country(s).', 'age-restriction'),
					'std'		=> '18',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'redirect_under_age' => array(
					'type' 		=> 'text',
					'size' 		=> 'large',
					'title' 	=> __('Redirect if under age:', 'age-restriction'),
					'desc' 		=> __('Redirect user to other page/site if minimum age condition is not met.', 'age-restriction'),
					'std'		=> '',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'country_undetected' => array(
					'type' 		=> 'select',
					'std' 		=> 'no',
					'size' 		=> 'large',
					'force_width'=> '120',
					'title' 	=> __('Force show if undetected country:', 'age-restriction'),
					'desc' 		=> __('Show the restriction if the country is not detected.', 'age-restriction'),
					'options'	=> array(
						'no' 			=> __('No', 'age-restriction'),
						'yes' 			=> __('Yes', 'age-restriction')
					),
					'cssclass'	=> array('popup')
				),
				'enable_remember_me' => array(
					'type' 		=> 'select',
					'std' 		=> 'yes',
					'size' 		=> 'large',
					'force_width'=> '120',
					'title' 	=> __('Enable "Remember me" button?', 'age-restriction'),
					'desc' 		=> __('Disable this if you want your users to be prompted by the verification window each time they acces the page.', 'age-restriction'),
					'options'	=> array(
						'no' 			=> __('No', 'age-restriction'),
						'yes' 			=> __('Yes', 'age-restriction')
					),
					'cssclass'	=> array('popup')
				),
				'set_cookie_duration' => array(
					'type' 		=> 'text',
					'size' 		=> 'large',
					'force_width' => 50,
					'title' 	=> __('Cookies duration (in days):', 'age-restriction'),
					'desc' 		=> __('How much time should pass until the user will be prompted to  verify his age again (if "Remember me" is activated). Default is 30 days.', 'age-restriction'),
					'std'		=> '30',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'selected_countries' 	=> array(
					'type' 		=> 'multiselect_left2right',
					'std' 		=> array(),
					'size' 		=> 'large',
					'rows_visible'	=> 18,
					'force_width'=> '300',
					'title' 	=> __('Select countries', 'age-restriction'),
					'desc' 		=> __('Choose on what countries you want the restriction to be displayed.', 'age-restriction'),
					'info'		=> array(
						'left' => __('All Countries list', 'age-restriction'),
						'right' => __('Your chosen countries from list', 'age-restriction'),
					),
					'options' 	=> $this->countries_list(),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'testing_enabled' => array(
					'type' 		=> 'select',
					'std' 		=> 'no',
					'size' 		=> 'large',
					'force_width'=> '120',
					'title' 	=> '<br/>' . __('Enable testing:', 'age-restriction'),
					'desc' 		=> __('This enables the feature to delete the SESSION/COOKIE every time after successful validation. If this is enabled, after validation just reload the page to see again the restriction.<br/><span style="color:red;">Testing purpose only! Don\'t forget to disable this after you are done testing.</span>', 'age-restriction'),
					'options'	=> array(
						'no' 			=> __('No', 'age-restriction'),
						'yes' 			=> __('Yes', 'age-restriction')
					),
					'cssclass'	=> array('popup')
				),
				'enable_for_choose' => array(
					'type' 		=> 'select',
					'std' 		=> 'all',
					'size' 		=> 'large',
					'force_width'=> '220',
					'title' 	=> __('Enable for:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'options'	=> array(
						'all' 			=> __('Enable for all', 'age-restriction'),
						'me' 			=> __('Enable only me', 'age-restriction'),
						'iplist' 		=> __('Enable for ip list', 'age-restriction'),
						//'url' 			=> __('Enable for url', 'age-restriction'),
						'disable' 		=> __('Disable', 'age-restriction')
					),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),

				'enable_for_iplist' => array(
					'type' 		=> 'textarea',
					'size' 		=> 'large',
					'title' 	=> __('Enable for IP List:', 'age-restriction'),
					'std'		=> '',
					'desc' 		=> __('Enable for IP List (one IP per line).', 'age-restriction'),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				
				/* various */
				'connect_w_facebook' => array(
					'type' 		=> 'select',
					'std' 		=> 'no',
					'size' 		=> 'large',
					'force_width'=> '120',
					'title' 	=> __('Connect with Facebook:', 'age-restriction'),
					'desc' 		=> __('Enable the "CONNECT WITH FACEBOOK" button.' . (!isset($this->config['fb_app_id']) || trim($this->config['fb_app_id']) == '' ? ' <span style="color:red;">For this functionalty to work you must have a Facebook Application ID setup in the <a href="'.(get_admin_url() . 'admin.php?page=age_restriction#!/settings').'">plugin settings section</a>.</span>' : ''), 'age-restriction'),
					'options'	=> array(
						'yes' 	=> __('YES', 'age-restriction'),
						'no' 	=> __('NO', 'age-restriction')
					),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'connect_w_facebook_btnText' => array(
					'type' 		=> 'text',
					'std' 		=> 'CONNECT WITH FACEBOOK',
					'size' 		=> 'large',
					'title' 	=> __('Facebook button text:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'connect_w_google' => array(
					'type' 		=> 'select',
					'std' 		=> 'no',
					'size' 		=> 'large',
					'force_width'=> '120',
					'title' 	=> __('Sign in with Google+:', 'age-restriction'),
					'desc' 		=> __('Enable the "SIGN IN WITH GOOGLE" button' . (!isset($this->config['google_client_id']) || trim($this->config['google_client_id']) == '' ? ' <span style="color:red;">For this functionalty to work you must have a Google Client ID setup in the <a href="'.(get_admin_url() . 'admin.php?page=age_restriction#!/settings').'">plugin settings section</a>.</span>' : ''), 'age-restriction'),
					'options'	=> array(
						'yes' 	=> __('YES', 'age-restriction'),
						'no' 	=> __('NO', 'age-restriction')
					),
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
				'connect_w_google_btnText' => array(
					'type' 		=> 'text',
					'std' 		=> 'CONNECT WITH GOOGLE+',
					'size' 		=> 'large',
					'title' 	=> __('Google+ button text:', 'age-restriction'),
					'desc' 		=> '&nbsp;',
					'cssclass'	=> array('popup', 'widget', 'slide', 'content_top', 'content_bottom', 'bar')
				),
			);
			 
			foreach ($this->elements as $field_key => $field_val) {
				$cssclass = $field_val['cssclass'];
				$cssclass = implode(',', array_map(array($this, "do_prefix"), $cssclass));
				$this->elements["$field_key"]['cssclass'] = $cssclass;
			}
			
			// tabs
			$this->tabs = array(
				'__tab1'	=> array(
					__('Design', 'age-restriction'),
					'theme, logo, background_image, background_color, box_background_color, box_background_opacity, text_color, text_hover_color, country_selection, date_format'
				),
				'__tab2'	=> array(
					__('Content', 'age-restriction'),
					'restriction_title, title_text_color, text_before, text_color_before, text_after, text_color_after, enter_btn_title, enter_btn_bg_color, enter_btn_text_color, minimum_age_error_message'
				),
				'__tab3'	=> array(
					__('Custom CSS', 'age-restriction'),
					'custom_css'
				),
				'__tab4'	=> array(
					__('Limitations', 'age-restriction'),
					'minimum_age, redirect_under_age, country_undetected, enable_remember_me, set_cookie_duration, selected_countries, testing_enabled, enable_for_choose, enable_for_iplist, enable_for_url', //test_enabled, test_ip'
				),
				'__tab6'	=> array(
					__('Social Connect', 'age-restriction'),
					'connect_w_facebook, connect_w_google, connect_w_facebook_btnText, connect_w_google_btnText'
				)
			);
		}

		public function get_elements() {
			$this->setup();
			return $this->elements;
		}
		
		public function get_tabs() {
			$this->setup();
			return $this->tabs;
		}
		
		
		/**
		 * Extra
		 */
		public function set_banner_postid($postid) {
			$this->postid = (int) $postid;
		}
		
		
		/**
		 * Banner type specific
		 */
		private function do_prefix($arr) {
			return 'btype-' . $arr;
		}

		private function countries_list() {
			require($this->module_folder_path . 'lists.inc.php');
			return $age_restriction_countries_list;
		}

		private function get_range_opacity() {
			$arr = array();
			for ($i=0; $i<=1; $i += 0.1) {
				$arr["$i"] = $i;
			}
			return $arr;
		}
	}
}

//new age_restrictionBannersType();