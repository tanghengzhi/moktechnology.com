<?php
/*
* Define class age_restrictionServerStatus
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('age_restrictionServerStatus') != true) {
    class age_restrictionServerStatus
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
		private $module = '';

		static protected $_instance;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $age_restriction;

        	$this->the_plugin = $age_restriction;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/server_status/';
			$this->module = $this->the_plugin->cfg['modules']['server_status'];

			if (is_admin()) {
	            add_action('admin_menu', array( &$this, 'adminMenu' ));
			}

			// load the ajax helper
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/server_status/ajax.php' );
			new age_restrictionServerStatusAjax( $this->the_plugin );
        }

		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionServerStatus Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }

		/**
	    * Hooks
	    */
	    static public function adminMenu()
	    {
	       self::getInstance()
	    		->_registerAdminPages();
	    }

	    /**
	    * Register plug-in module admin pages and menus
	    */
		protected function _registerAdminPages()
    	{ 
    		add_submenu_page(
    			$this->the_plugin->alias,
    			$this->the_plugin->alias . " " . __('Check System status', 'age-restriction'),
	            __('System Status', 'age-restriction'),
	            'manage_options',
	            $this->the_plugin->alias . "_server_status",
	            array($this, 'display_index_page')
	        );

			return $this;
		}

		public function display_index_page()
		{
			$this->printBaseInterface();
		}
		
		/*
		* printBaseInterface, method
		* --------------------------
		*
		* this will add the base DOM code for you options interface
		*/
		private function printBaseInterface()
		{
			global $wpdb;
			
			$amz_settings = @unserialize( get_option( 'age_restriction_settings' ) );
			$plugin_data = get_plugin_data( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'plugin.php' );  
?>
		<link rel='stylesheet' href='<?php echo $this->module_folder;?>app.css' type='text/css' media='all' />
		<script type="text/javascript" src="<?php echo $this->module_folder;?>app.class.js" ></script>
		<div id="age_restriction-wrapper" class="fluid wrapper-age_restriction">
			
			<?php
			// show the top menu
			age_restrictionAdminMenu::getInstance()->make_active('info|server_status')->show_menu();
			?>
			
			<!-- Main loading box -->
			<div id="age_restriction-main-loading">
				<div id="age_restriction-loading-overlay"></div>
				<div id="age_restriction-loading-box">
					<div class="age_restriction-loading-text"><?php _e('Loading', 'age-restriction');?></div>
					<div class="age_restriction-meter age_restriction-animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
				</div>
			</div>

			<!-- Content -->
			<div id="age_restriction-content">
				
				<h1 class="age_restriction-section-headline">
					<?php 
					if( isset($this->module['server_status']['in_dashboard']['icon']) ){
						echo '<img src="' . ( $this->module_folder . $this->module['server_status']['in_dashboard']['icon'] ) . '" class="age_restriction-headline-icon">';
					}
					?>
					<?php echo $this->module['server_status']['menu']['title'];?>
					<span class="age_restriction-section-info"><?php echo $this->module['server_status']['description'];?></span>
					<?php
					$has_help = isset($this->module['server_status']['help']) ? true : false;
					if( $has_help === true ){
						
						$help_type = isset($this->module['server_status']['help']['type']) && $this->module['server_status']['help']['type'] ? 'remote' : 'local';
						if( $help_type == 'remote' ){
							echo '<a href="#load_docs" class="age_restriction-show-docs" data-helptype="' . ( $help_type ) . '" data-url="' . ( $this->module['server_status']['help']['url'] ) . '">HELP</a>';
						} 
					} 
					?>
				</h1>
				
				<!-- Container -->
				<div class="age_restriction-container clearfix">

					<!-- Main Content Wrapper -->
					<div id="age_restriction-content-wrap" class="clearfix" style="padding-top: 5px;">

						<!-- Content Area -->
						<div id="age_restriction-content-area">
							<div class="age_restriction-grid_4">
	                        	<div class="age_restriction-panel">
									<div class="age_restriction-panel-content">
										<table class="age_restriction-table" cellspacing="0">
											
											<thead>
												<tr>
													<th colspan="2"><?php _e( 'Environment', 'age-restriction' ); ?></th>
												</tr>
											</thead>
									
											<tbody>
												<tr>
									                <td width="190"><?php _e( 'Home URL','age-restriction' ); ?>:</td>
									                <td><?php echo home_url(); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'age_restriction Version','age-restriction' ); ?>:</td>
									                <td><?php echo $plugin_data['Version'];?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'WP Version','age-restriction' ); ?>:</td>
									                <td><?php if ( is_multisite() ) echo 'WPMU'; else echo 'WP'; ?> <?php bloginfo('version'); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'Web Server Info','age-restriction' ); ?>:</td>
									                <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] );  ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'PHP Version','age-restriction' ); ?>:</td>
									                <td><?php if ( function_exists( 'phpversion' ) ) echo esc_html( phpversion() ); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'MySQL Version','age-restriction' ); ?>:</td>
									                <td><?php if ( function_exists( 'mysql_get_server_info' ) ) echo esc_html( mysql_get_server_info() ); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'WP Memory Limit','age-restriction' ); ?>:</td>
									                <td><?php echo size_format( $this->let_to_num(WP_MEMORY_LIMIT) ); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'WP Debug Mode','age-restriction' ); ?>:</td>
									                <td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo __( 'Yes', 'age-restriction' ); else echo __( 'No', 'age-restriction' ); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e( 'WP Max Upload Size','age-restriction' ); ?>:</td>
									                <td><?php echo size_format( wp_max_upload_size() ); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e('PHP Post Max Size','age-restriction' ); ?>:</td>
									                <td><?php if ( function_exists( 'ini_get' ) ) echo size_format( $this->let_to_num( ini_get('post_max_size') ) ); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e('PHP Time Limit','age-restriction' ); ?>:</td>
									                <td><?php if ( function_exists( 'ini_get' ) ) echo ini_get('max_execution_time'); ?></td>
									            </tr>
									            <tr>
									                <td><?php _e('WP Remote GET','age-restriction' ); ?>:</td>
									                <td><div class="age_restriction-loading-ajax-details" data-action="remote_get"></div></td>
									            </tr>
											</tbody>
									
											<thead>
												<tr>
													<th colspan="2"><?php _e( 'Plugins', 'age-restriction' ); ?></th>
												</tr>
											</thead>
									
											<tbody>
									         	<tr>
									         		<td><?php _e( 'Installed Plugins','age-restriction' ); ?>:</td>
									         		<td><div class="age_restriction-loading-ajax-details" data-action="active_plugins"></div></td>
									         	</tr>
											</tbody>
									
											<thead>
												<tr>
													<th colspan="2"><?php _e( 'Settings', 'age-restriction' ); ?></th>
												</tr>
											</thead>
									
											<tbody>
									
									            <tr>
									                <td><?php _e( 'Force SSL','age-restriction' ); ?>:</td>
													<td><?php echo get_option( 'woocommerce_force_ssl_checkout' ) === 'yes' ? __( 'Yes', 'age-restriction' ) : __( 'No', 'age-restriction' ); ?></td>
									            </tr>
											</tbody>
										</table>
				            		</div>
								</div>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

<?php
		}

		/*
		* ajax_request, method
		* --------------------
		*
		* this will create requesto to 404 table
		*/
		public function ajax_request()
		{
			global $wpdb;
			$request = array(
				'id' 			=> isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0
			);
			
			$asin = get_post_meta($request['id'], '_amzASIN', true);
			
			$sync = new wwcAmazonSyncronize( $this->the_plugin );
			$sync->updateTheProduct( $asin, $request['id'] );
		}
		
		private function let_to_num( $size ) 
		{
		     $l      = substr( $size, -1 );
		     $ret    = substr( $size, 0, -1 );
		     switch( strtoupper( $l ) ) {
		         case 'P':
		             $ret *= 1024;
		         case 'T':
		             $ret *= 1024;
		         case 'G':
		             $ret *= 1024;
		         case 'M':
		             $ret *= 1024;
		         case 'K':
		             $ret *= 1024;
		     }
		     return $ret;
		}
    }
}

// Initialize the age_restrictionServerStatus class
$age_restrictionServerStatus = age_restrictionServerStatus::getInstance();
