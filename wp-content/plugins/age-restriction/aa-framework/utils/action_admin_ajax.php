<?php
/*
* Define class age_restriction_ActionAdminAjax
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('age_restriction_ActionAdminAjax') != true) {
    class age_restriction_ActionAdminAjax
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

		static protected $_instance;
		
	
		/*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $parent )
        {
			$this->the_plugin = $parent;
    
			$this->amzHelper = $this->the_plugin->amzHelper;
  
			// banner related!
			add_action('wp_ajax_age_restriction_getBannerStats', array( $this, 'getBannerStats' ));
			$this->init_banner_template();
			
			// banner preview
			add_action('wp_ajax_age_restriction-preview', array($this, 'banner_preview' ));
			add_action('wp_ajax_nopriv_age_restriction-preview', array($this, 'banner_preview' ));
        }
        
		/**
	    * Singleton pattern
	    *
	    * @return Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }
	        
	        return self::$_instance;
	    }
	    
	    
		/**
		 * Banner related!
		 */
		public function init_banner_template() {
			// banner template object!
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'lib/frontend/banner.tpl.php' );
			$this->bannerTpl = new age_restrictionBMFrontendTpl( $this->the_plugin );
  
			$this->bannerTpl->ajax_actions();
		}
		
		public function getBannerStats() {
			//$action = isset($_REQUEST['sub_action']) ? $_REQUEST['sub_action'] : '';
			$post_id = isset($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;

			$ret = array(
				'status'		=> 'invalid',
				'html'			=> ''
			);

			// banner stats!
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/restrictions_manager/app.statistics.php' );
			$banner_stats = new age_restrictionBannersStats( $this->the_plugin, $post_id );
			
			// banner sections!
			$banner_stats->set_banner_postid( $post_id );
			$stats_html = $banner_stats->build_html();
			
			$ret['status'] = 'valid';
			$ret['html'] = $stats_html;
			
			die(json_encode($ret));
		}
		
		public function banner_preview() {
			$bannerid = isset($_REQUEST['bannerid']) ? (int) $_REQUEST['bannerid'] : 0;
			
			// banner object!
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'lib/frontend/banner.php' );
			$banner = new age_restrictionBMFrontend( $this->the_plugin );
			$preview = $banner->get_preview( $bannerid );
			echo $preview; die;
		}
    }
}

// Initialize the age_restriction_ActionAdminAjax class
//$age_restriction_ActionAdminAjax = new age_restriction_ActionAdminAjax();
