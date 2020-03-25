<?php
/*
* Define class age_restrictionDashboard
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionDashboard') != true) {
    class age_restrictionDashboard
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
		public $the_plugin = null;

		private $module_folder = '';
		
		public $ga = null;
		public $ga_params = array();
		
		public $boxes = array();

		static protected $_instance;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        { 
        	global $age_restriction;
 
        	$this->the_plugin = $age_restriction;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/dashboard/';
			
			if (is_admin()) {
	            add_action( "admin_enqueue_scripts", array( &$this, 'admin_print_styles') );
				add_action( "admin_print_scripts", array( &$this, 'admin_load_scripts') );
			}
			 
			// load the ajax helper
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/dashboard/ajax.php' );
			new age_restrictionDashboardAjax( $this->the_plugin );
			
			// add the boxes
			$this->addBox( 'website_preview', '', $this->website_preview(), array(
				'size' => 'grid_1'
			) );
			
			$this->addBox( 'dashboard_links', '', $this->links(), array(
				'size' => 'grid_3'
			) );
			
			$this->addBox( 'aateam_products', 'Other products by AA-Team:', $this->aateam_products(), array(
				'size' => 'grid_4'
			) );
			
			$this->addBox( 'support', 'Need AA-Team Support?', $this->support() );
			$this->addBox( 'changelog', 'Changelog', $this->changelog() );
        }

		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionDashboard Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
	    
		public function admin_print_styles()
		{
			wp_register_style( 'age_restriction-DashboardBoxes', $this->module_folder . 'app.css', false, '1.0' );
        	wp_enqueue_style( 'age_restriction-DashboardBoxes' );
		}
		
		public function admin_load_scripts()
		{
			wp_enqueue_script( 'age_restriction-DashboardBoxes', $this->module_folder . 'app.class.js', array(), '1.0', true );
		}
		
		public function getBoxes()
		{
			$ret_boxes = array();
			if( count($this->boxes) > 0 ){
				foreach ($this->boxes as $key => $value) { 
					$ret_boxes[$key] = $value;
				}
			}
 
			return $ret_boxes;
		}
		
		private function formatAsFreamworkBox( $html_content='', $atts=array() )
		{
			return array(
				'size' 		=> isset($atts['size']) ? $atts['size'] : 'grid_4', // grid_1|grid_2|grid_3|grid_4
	            'header' 	=> isset($atts['header']) ? $atts['header'] : false, // true|false
	            'toggler' 	=> false, // true|false
	            'buttons' 	=> isset($atts['buttons']) ? $atts['buttons'] : false, // true|false
	            'style' 	=> isset($atts['style']) ? $atts['style'] : 'panel-widget', // panel|panel-widget
	            
	            // create the box elements array
	            'elements' => array(
	                array(
	                    'type' => 'html',
	                    'html' => $html_content
	                )
	            )
			);
		}
		
		private function addBox( $id='', $title='', $html='', $atts=array() )
		{ 
			// check if this box is not already in the list
			if( isset($id) && trim($id) != "" && !isset($this->boxes[$id]) ){
				
				$box = array();
				
				$box[] = '<div class="age_restriction-dashboard-status-box">';
				if( isset($title) && trim($title) != "" ){
					$box[] = 	'<h1>' . ( $title ) . '</h1>';
				}
				$box[] = 	$html;
				$box[] = '</div>';
				
				$this->boxes[$id] = $this->formatAsFreamworkBox( implode("\n", $box), $atts );
				
			}
		}
		
		public function formatRow( $content=array() )
		{
			$html = array();
			
			$html[] = '<div class="age_restriction-dashboard-status-box-row">';
			if( isset($content['title']) && trim($content['title']) != "" ){
				$html[] = 	'<h2>' . ( isset($content['title']) ? $content['title'] : 'Untitled' ) . '</h2>';
			}
			if( isset($content['ajax_content']) && $content['ajax_content'] == true ){
				$html[] = '<div class="age_restriction-dashboard-status-box-content is_ajax_content">';
				$html[] = 	'{' . ( isset($content['id']) ? $content['id'] : 'error_id_missing' ) . '}';
				$html[] = '</div>';
			}
			else{
				$html[] = '<div class="age_restriction-dashboard-status-box-content is_ajax_content">';
				$html[] = 	( isset($content['html']) && trim($content['html']) != "" ? $content['html'] : '!!! error_content_missing' );
				$html[] = '</div>';
			}
			$html[] = '</div>';
			
			return implode("\n", $html);
		}
		
		public function products_performances()
		{
			$html = array();
			
			$html[] = $this->formatRow( array( 
				'id' 			=> 'products_performances',
				'title' 		=> '',
				'html'			=> '',
				'ajax_content' 	=> true
			) );
			
			return implode("\n", $html);
		}

		public function support()
		{
			$html = array();
			$html[] = '<a href="http://support.aa-team.com" target="_blank"><img src="' . ( $this->module_folder ) . 'assets/support_banner.jpg"></a>';
			
			return implode("\n", $html);
		}
		
		public function aateam_products()
		{
			$html = array();
			
			$html[] = '<ul class="age_restriction-aa-products-tabs">';
			$html[] = 	'<li class="on">';
			$html[] = 		'<a href="javascript: void(0)" class="age_restriction-aa-items-codecanyon">CodeCanyon</a>';
			$html[] = 	'</li>';
			$html[] = 	'<li>';
			$html[] = 		'<a href="javascript: void(0)" class="age_restriction-aa-items-themeforest">ThemeForest</a>';
			$html[] = 	'</li>';
			$html[] = 	'<li>';
			$html[] = 		'<a href="javascript: void(0)" class="age_restriction-aa-items-graphicriver">GraphicRiver</a>';
			$html[] = 	'</li>';
			$html[] = '</ul>';
			
			$html[] = $this->formatRow( array( 
				'id' 			=> 'aateam_products',
				'title' 		=> '',
				'html'			=> '',
				'ajax_content' 	=> true
			) );
 
			return implode("\n", $html);
		}
		
		public function changelog()
		{
			$html = array();
			$html[] = '<textarea style="height: 500px;">';
			$changelog_file = file_get_contents( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/changelog.txt' );
			$html[] = $changelog_file;
			$html[] = '</textarea>';
			
			return implode("\n", $html);
		}
		
		public function audience_overview()
		{
			$html = array();
			$html[] = '<div class="age_restriction-audience-graph" id="age_restriction-audience-visits-graph" data-fromdate="' . ( date('Y-m-d', strtotime("-1 week")) ) . '" data-todate="' . ( date('Y-m-d') ) . '"></div>';

			return  implode("\n", $html);
		}
		
		public function website_preview()
		{
			$html = array();
			$html[] = '<div class="age_restriction-website-preview">';
			$html[] = 	'<h4><b style="color:#cd0923;">Premium Age Restriction Plugin for Wordpress</b> </p>';
			$html[] = 	'<h4>
It happens that you own a website that has content that’s not appropriate for all ages from moral or legal reasons? <br/><br/>You found the perfect plugin!</p>';
			$html[] = 	'<h4>Thank you for buying: <a target="_blank" href="http://codecanyon.net/item/plugin/' . ( get_option('age_restriction_register_item_id') ) . '">' . ( get_option('age_restriction_register_item_name') ) . '</a></h4>';
			$html[] = 	'<h4>Licence: <a target="_blank" href="http://codecanyon.net/licenses/regular_extended">' . ( get_option('age_restriction_register_licence') ) . '</a></h4>';
			$html[] = '</div>';
			
			return  implode("\n", $html);
		}
		
		public function links()
		{
			$html = array();
			$html[] = '<ul class="age_restriction-summary-links">';
			
			foreach ($this->the_plugin->cfg['modules'] as $key => $value) {
  
				if( !in_array( $key, array_keys($this->the_plugin->cfg['activate_modules'])) ) continue;
				//var_dump('<pre>',$value[$key],'</pre>');  
				$in_dashboard = isset($value[$key]['in_dashboard']) ? $value[$key]['in_dashboard'] : array();
  
				if( count($in_dashboard) > 0 ){
					
					$html[] = '
						<li>
							<a href="' . ( $in_dashboard['url'] ) . '">
								<img src="' . ( $value['folder_uri']  . $in_dashboard['icon'] ) . '">
								<span class="text">' . ( $value[$key]['menu']['title'] ) . '</span>
							</a>
						</li>';
				}
			}
			
			$html[] = '</ul>';
			
			return implode("\n", $html);
		}
    }
}

// Initialize the age_restrictionDashboard class
$age_restrictionDashboard = age_restrictionDashboard::getInstance();