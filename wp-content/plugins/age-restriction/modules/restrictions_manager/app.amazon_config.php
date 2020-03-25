<?php 
/**
 * age_restrictionBannersAmazonConfig class
 * ================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 * @access 		public
 * @return 		void
 */ 
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBannersAmazonConfig') != true) {
    class age_restrictionBannersAmazonConfig
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
			// elements
			$this->elements = array(
                'amzcfg_help_required_fields' => array(
                    'type' => 'message',
                    'status' => 'info',
                    'html' => 'The following fields are required in order to send requests to Amazon and retrieve data about products and listings. If you do not already have access keys set up, please visit the <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&amp;action=access-key#access_credentials" target="_blank">AWS Account Management</a> page to create and retrieve them.'
                ),
                'amzcfg_AccessKeyID' => array(
                    'type' => 'text',
                    'std' => '',
                    'size' => 'large',
                    'title' => 'Access Key ID',
                    'force_width' => '250',
                    'desc' => 'Are required in order to send requests to Amazon API.'
                ),
                'amzcfg_SecretAccessKey' => array(
                    'type' => 'text',
                    'std' => '',
                    'size' => 'large',
                    'force_width' => '300',
                    'title' => 'Secret Access Key',
                    'desc' => 'Are required in order to send requests to Amazon API.'
                ),
                'amzcfg_AffiliateId' => array(
                    'type' => 'html',
                    'std' => '',
                    'size' => 'large',
                    'title' => 'Affiliate Information',
                    'html' => $this->__age_restrictionAffIDsHTML( '' )
                ),
                
                'amzcfg_help_available_countries' => array(
                    'type' => 'message',
                    'status' => 'info',
                    'html' => '
							<strong>Available countries: &nbsp;</strong>
							<a href="https://affiliate-program.amazon.com/" target="_blank">United States</a>, &nbsp;
							<a href="https://affiliate-program.amazon.co.uk/" target="_blank">United Kingdom</a>, &nbsp;
							<a href="https://partnernet.amazon.de/" target="_blank">Deutschland</a>, &nbsp;
							<a href="https://partenaires.amazon.fr/" target="_blank">France</a>, &nbsp;
							<a href="https://affiliate.amazon.co.jp/" target="_blank">Japan</a>, &nbsp;
							<a href="https://associates.amazon.ca/" target="_blank">Canada</a>, &nbsp;
							<a href="https://associates.amazon.cn/" target="_blank">China</a>, &nbsp;
							<a href="https://programma-affiliazione.amazon.it/" target="_blank">Italia</a>, &nbsp;
							<a href="https://afiliados.amazon.es/" target="_blank">España</a>, &nbsp;
							<a href="https://affiliate-program.amazon.in/" target="_blank">India</a>
						'
                )
			);
			
			// tabs
			$this->tabs = array(
			);
		}

		public function get_elements() {
			$this->setup();
			return $this->elements;
		}
		
		public function get_tabs() {
			return $this->tabs;
		}
		
		public function save_amzcfg() {
			$post_id = $this->postid;

			$banners_meta = array();

			$options = array(
				array(
					/* define the form_sizes  box */
					'banner' => array(
						// create the box elements array
						'elements'	=> $this->get_elements()
					)
				)
			);
			$options = reset($options);
			
			foreach ($options as $box_id => $box) {
				foreach ($box['elements'] as $elm_id => $element){

					if ( $element['type'] == 'html' ) {
						if ( $elm_id == 'amzcfg_AffiliateId' && isset($_POST["amzcfg_AffiliateID"]) )
							$banners_meta[$box_id]["amzcfg_AffiliateID"] = $_POST["amzcfg_AffiliateID"];
						continue 1;
					}
					if ( isset($_POST[$elm_id]) )
						$banners_meta[$box_id][$elm_id] = $_POST[$elm_id];
				}
			}

			update_post_meta( $post_id, '_age_restriction_amzcfg', $banners_meta );
		}
		
		
		/**
		 * Extra
		 */
		public function set_banner_postid($postid) {
			$this->postid = (int) $postid;
		}
		
		
		/**
		 * Specific
		 */
		public function __age_restrictionAffIDsHTML( $istab = '' )
		{
		    global $age_restriction;
		    
		    $html         = array();
		    $img_base_url = $age_restriction->cfg['paths']["plugin_dir_url"] . 'modules/amazon/assets/flags/';
		    
		    $post_id = $this->postid;
		    $config = get_post_meta( $post_id, '_age_restriction_amzcfg', true );
			$config = isset($config['banner']) ? $config['banner'] : array();
			$config = $this->the_plugin->__amz_default_affid( $config, true );
			foreach (array('com', 'ca', 'uk', 'de', 'fr', 'in', 'it', 'es', 'jp', 'cn') as $val) {
				if ( isset($config['amzcfg_AffiliateID']["$val"]) ) ;
				else {
					$config['amzcfg_AffiliateID']["$val"] = ''; 
				}
			}
  
		    $html[] = '<div class="age_restriction-form-row' . ($istab!='' ? ' '.$istab : '') . '">';
		    $html[] = '<label>Your Affiliate IDs</label>';
		    $html[] = '<div class="age_restriction-form-item large">';
		    $html[] = '<span class="formNote">Your Affiliate ID probably ends in -20, -21 or -22. You get this ID by signing up for Amazon Associates.</span>';
		    $html[] = '<div class="age_restriction-aff-ids">';
		    $html[] = '<img src="' . ($img_base_url) . 'US-flag.gif" height="20" title="US"><input type="text" value="' . ($config['amzcfg_AffiliateID']['com']) . '" name="amzcfg_AffiliateID[com]" id="amzcfg_AffiliateID[com]" data-country_name="Worldwide"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'CA-flag.gif" height="20" title="CA"><input type="text" value="' . ($config['amzcfg_AffiliateID']['ca']) . '" name="amzcfg_AffiliateID[ca]" id="amzcfg_AffiliateID[ca]" data-country_name="Canada"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'UK-flag.gif" height="20" title="UK"><input type="text" value="' . ($config['amzcfg_AffiliateID']['uk']) . '" name="amzcfg_AffiliateID[uk]" id="amzcfg_AffiliateID[uk]" data-country_name="United Kingdom"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'DE-flag.gif" height="20" title="DE"><input type="text" value="' . ($config['amzcfg_AffiliateID']['de']) . '" name="amzcfg_AffiliateID[de]" id="amzcfg_AffiliateID[de]" data-country_name="Germany"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'FR-flag.gif" height="20" title="FR"><input type="text" value="' . ($config['amzcfg_AffiliateID']['fr']) . '" name="amzcfg_AffiliateID[fr]" id="amzcfg_AffiliateID[fr]" data-country_name="France"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'IN-flag.gif" height="20" title="IN"><input type="text" value="' . ($config['amzcfg_AffiliateID']['in']) . '" name="amzcfg_AffiliateID[in]" id="amzcfg_AffiliateID[in]" data-country_name="India"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'IT-flag.gif" height="20" title="IT"><input type="text" value="' . ($config['amzcfg_AffiliateID']['it']) . '" name="amzcfg_AffiliateID[it]" id="amzcfg_AffiliateID[it]" data-country_name="Italy"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'ES-flag.gif" height="20" title="ES"><input type="text" value="' . ($config['amzcfg_AffiliateID']['es']) . '" name="amzcfg_AffiliateID[es]" id="amzcfg_AffiliateID[es]" data-country_name="Spain"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'JP-flag.gif" height="20" title="JP"><input type="text" value="' . ($config['amzcfg_AffiliateID']['jp']) . '" name="amzcfg_AffiliateID[jp]" id="amzcfg_AffiliateID[jp]" data-country_name="Japan"> <br />';
		    $html[] = '<img src="' . ($img_base_url) . 'CN-flag.gif" height="20" title="CN"><input type="text" value="' . ($config['amzcfg_AffiliateID']['cn']) . '" name="amzcfg_AffiliateID[cn]" id="amzcfg_AffiliateID[cn]" data-country_name="China"> <br />';
		    $html[] = '</div>';
		    $html[] = '<h3>Some hints and information:</h3>';
		    $html[] = '- The link will use IP-based Geolocation to geographically target your visitor to the Amazon store of his/her country (according to their current location). <br />';
		    $html[] = '- You don\'t have to specify all affiliate IDs if you are not registered to all programs. <br />';
		    $html[] = '- The ASIN is unfortunately not always globally unique. That\'s why you sometimes need to specify several ASINs for different shops. <br />';
		    $html[] = '- If you have an English website, it makes most sense to sign up for the US, UK and Canadian programs. <br />';
		    $html[] = '</div>';
		    $html[] = '</div>';
		    
		    return implode("\n", $html);
		}
	}
}

//new age_restrictionBannersAmazonConfig();