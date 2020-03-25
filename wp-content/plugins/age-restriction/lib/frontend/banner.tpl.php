<?php 
/**
 * age_restrictionBMFrontendTpl class
 * ================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 * @access 		public
 * @return 		void
 */  
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBMFrontendTpl') != true) {
    class age_restrictionBMFrontendTpl
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
		
		private $frontend_folder = '';
		private $frontend_folder_path = '';
		
		private $banner_type = '';
		private $bannerData = array();
		private $bannerDiscount = array();
		private $bannerMeta = array();
		private $bannerAmzCfg = array();
		
		private $current_country = array();
		private $current_category = array();
		
		private static $CACHE_FOLDER = null;
		private static $CACHE_CONFIG_LIFE = 1440; // cache lifetime in minutes /1 day
		
		public static $utils = array();
		

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $the_plugin, $postid=0 )
        {
			$this->the_plugin = $the_plugin;
			
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
			$this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/restrictions_manager/';
			
			$this->frontend_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'lib/frontend/';
			$this->frontend_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'lib/frontend/';
			
			// cache folder & files
			self::$CACHE_FOLDER = $this->frontend_folder_path . 'cache-search/';

			if ( $this->the_plugin->is_admin === true ) {
			}
        }
		
		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionBMFrontendTpl Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
		public function ajax_actions() {
			/*
			// Ajax actions
			add_action('wp_ajax_age_restrictionCountryInterface_front', array($this, 'age_restrictionCountryCategories'));
			
			// nopriv
			add_action('wp_ajax_nopriv_age_restrictionCountryInterface_front', array($this, 'age_restrictionCountryCategories'));
			*/
		}
		
		
		public function set_banner_info( $banner, $banner_type ) {
			
			$this->banner_type = $banner_type;

			$this->bannerData = $banner;
			
			$discount = isset($banner['discount']) ? $banner['discount'] : array();
			if ( !empty($discount) ) {
				$discount = unserialize($discount);
				$discount = json_decode($discount);
				$this->bannerDiscount = $discount;
			}
			
			$meta = isset($banner['meta']) ? $banner['meta'] : array();
			$this->bannerMeta = $meta;
			
			$amzcfg = isset($banner['amzcfg']) ? $banner['amzcfg'] : array();
			//$amzcfg = $this->the_plugin->__amz_default_affid( $amzcfg, true );
			$this->bannerAmzCfg = $amzcfg;
		}
		
		
		/**
		 * Template - Popup
		 */
		public function get_tpl_popup() {
			require('lists.inc.php');
			
			// banner data!
			$bannerData = $this->bannerData;
			$bannerDiscount = $this->bannerDiscount;
			$bannerMeta = $this->bannerMeta;
			
			$template = $this->the_plugin->cfg['paths']['design_dir_path'] .'/template_1/index.php';  
			require($template);
			
			// end output
			die();
			// banner logo
			$banner_logo = array(
				0 => $this->frontend_folder . '/images/banner-logo.png',
				1 => 340,
				2 => 160
			);
			if ( isset($bannerMeta['logo']) && !empty($bannerMeta['logo']) ) {
				$image = wp_get_attachment_image_src( (int) $bannerMeta['logo'], 'thumbnail' );
				if ( isset($image[0]) && !empty($image[0]) ) $banner_logo = $image;
			}
			
			$banner_type = $this->banner_type;
  
			ob_start();
?>
		
		<?php
		$css_wrap = ''; $css_main = ''; $css_style = 'display: none;'; $css_prods = '';
		switch ($banner_type) {
			case 'popup':
				$css_main = 'age_restriction-main-container promo-page'; $css_wrap = 'age_restriction-type-popup age_restriction-smartPopup';
				break;
		}
		
		$template = $this->the_plugin->cfg['paths']['design_dir_path'] .'/template_'. $bannerMeta['theme'] . '/index.php';
		ob_start();
			require_once($template);
			$template = ob_get_contents();
		ob_end_clean();
		
		$template = str_replace('{logo}', (isset($banner_logo[0]) ? '<img src="'.$banner_logo[0].'"/>' : ''), $template);
		$template = str_replace('{title}', (isset($bannerMeta['restriction_title']) && !empty($bannerMeta['restriction_title']) ? $bannerMeta['restriction_title'] : ''), $template);
		$template = str_replace('{btn_enter_title}', (isset($bannerMeta['enter_btn_title']) && !empty($bannerMeta['enter_btn_title']) ? $bannerMeta['enter_btn_title'] : 'ENTER'), $template);
		
		?>

		<!-- main AgeRestrict -->
		<div class="age_restriction-banners-list <?php echo $css_wrap; ?>" style="<?php echo $css_style; ?>" data-type="<?php echo $banner_type; ?>">
		
			<!-- main container -->
			<div class="<?php echo $css_main; ?>">
				<?php echo $template; ?>
			</div><!-- END main container -->

		</div><!-- END main AgeRestrict -->
		
		<?php if ( $banner_type == 'popup' ) { ?>
		<div id="age_restriction-smartPopupfade"></div>
		<?php } ?>

<?php
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}

		// Build Html methods
		private function build_select( $param, $values, $default='', $css='' ) {
			if (empty($values) || !is_array($values)) return '';
			foreach ($values as $k => $v) {
				echo '<option value="' . $k . '"' . ($k == $default ? ' selected="selected"' : '') . '' . (!empty($css) ? ' '.$css : '') . '>' . $v . '</option>';
			}			
		}
		
		private function build_input_text( $param, $placeholder, $default='', $css='' ) {
			$name = "age_restrictionParameter[$param]";
			
			echo '<input placeholder="' . $placeholder . '" name="' . $name . '" id="' . $name . '" type="text" value="' . (isset($default) && !empty($default) ? $default : '') . '"' . (!empty($css) ? ' '.$css : '') . '>';
		}

		/** 
		 * Dont Show Me Again
		 */
		public function age_restrictionDontShowAgain() {
			$req = array(
				'banner_id'		=> isset($_REQUEST['banner_id']) ? $_REQUEST['banner_id'] : 0,
				'dont_show'		=> isset($_REQUEST['dont_show']) ? trim($_REQUEST['dont_show']) : ''
			);
			
			if ( $req['dont_show'] == 'yes' ) {
				$this->the_plugin->cookie_set(array(
					'name'			=> 'age_restriction_banners_dont_showme_' . $req['banner_id'],
					'value'			=> 'yes',
					'expire_sec'	=> strtotime( '+30 days' ) // time() + 604800, // 1 hour = 3600 || 1 day = 86400 || 1 week = 604800
				));
			} else {
				$this->the_plugin->cookie_del(array(
					'name'			=> 'age_restriction_banners_dont_showme_' . $req['banner_id']
				));
			}
			die(json_encode(array(
				'status' 	=> 'valid'
			)));
		}
	
		// verify if file exists!
		private function verifyFileExists($file, $type='file') {
			clearstatcache();
			if ($type=='file') {
				if (!file_exists($file) || !is_file($file) || !is_readable($file)) {
					return false;
				}
				return true;
			} else if ($type=='folder') {
				if (!is_dir($file) || !is_readable($file)) {
					return false;
				}
				return true;
			}
			// invalid type
			return 0;
		}
	
		// make a folder!
		private function makedir($path, $folder='') {
			$fullpath = $path . $folder;
	
			clearstatcache();
			if(file_exists($fullpath) && is_dir($fullpath) && is_readable($fullpath)) {
				return true;
			}else{
				$stat1 = @mkdir($fullpath);
				$stat2 = @chmod($fullpath, 0777);
				if ($stat1===true && $stat2===true)
					return true;
			}
			return false;
		}
		
		// get file name/ dot indicate if a .dot will be put in front of image extension, default is not
		private function fileName($fullname)
		{
			$return = substr($fullname, 0, strrpos($fullname, "."));
			return $return;
		}
	
		// get file extension
		private function fileExtension($fullname, $dot=false)
		{
			$return = "";;
			if( $dot == true ) $return .= ".";
			$return .= substr(strrchr($fullname, "."), 1);
			return $return;
		}
	}
}

//new age_restrictionBMFrontendTpl();