<?php
/**
 * AA-Team freamwork class
 * http://www.aa-team.com
 * =======================
 *
 * @package		age_restriction
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
! defined( 'ABSPATH' ) and exit;

if(class_exists('age_restriction') != true) {
	class age_restriction {

		const VERSION = 1.0;

		// The time interval for the remote XML cache in the database (21600 seconds = 6 hours)
		const NOTIFIER_CACHE_INTERVAL = 21600;

		public $alias = 'age_restriction';
		
		public $dev = '';
		public $debug = false;
		public $is_admin = false;

		/**
		 * configuration storage
		 *
		 * @var array
		 */
		public $cfg = array();

		/**
		 * plugin modules storage
		 *
		 * @var array
		 */
		public $modules = null;

		/**
		 * errors storage
		 *
		 * @var object
		 */
		private $errors = null;

		/**
		 * DB class storage
		 *
		 * @var object 
		 */
		public $db = array();

		public $facebookInstance = null;
		public $fb_user_profile = null;
		public $fb_user_id = null;

		private $plugin_hash = null;
		private $v = null;
		
		public $amzHelper = null;
		
		public $jsFiles = array();
		
		public $wp_filesystem = null;
		
		private $opStatusMsg = array(
			'operation'			=> '',
			'msg'				=> ''
		);
		
		public $pluginDepedencies = null;
		public $pluginName = 'Age Restriction';
		
		public $details = array();

		/**
		 * The constructor
		 */
		function __construct($here = __FILE__)
		{
			$this->is_admin = is_admin() === true ? true : false;
			
			$this->details = array('plugin_name' => 'age_restriction');
			
        	// load WP_Filesystem 
			include_once ABSPATH . 'wp-admin/includes/file.php';
		   	WP_Filesystem();
			global $wp_filesystem;
			$this->wp_filesystem = $wp_filesystem;

			$this->update_developer();

			$this->plugin_hash = get_option('age_restriction_hash');

			// set the freamwork alias
			$this->buildConfigParams('default', array( 'alias' => $this->alias ));

			// get the globals utils
			global $wpdb;

			// store database instance
			$this->db = $wpdb;

			// instance new WP_ERROR - http://codex.wordpress.org/Function_Reference/WP_Error
			$this->errors = new WP_Error();

			// plugin root paths
			$this->buildConfigParams('paths', array(
				// http://codex.wordpress.org/Function_Reference/plugin_dir_url
				'plugin_dir_url' => str_replace('aa-framework/', '', plugin_dir_url( (__FILE__)  )),

				// http://codex.wordpress.org/Function_Reference/plugin_dir_path
				'plugin_dir_path' => str_replace('aa-framework/', '', plugin_dir_path( (__FILE__) ))
			));

			// add plugin lib design paths and url
			$this->buildConfigParams('paths', array(
				'design_dir_url' => $this->cfg['paths']['plugin_dir_url'] . 'lib/design',
				'design_dir_path' => $this->cfg['paths']['plugin_dir_path'] . 'lib/design'
			));
			
			// add plugin lib design paths and url
			$this->buildConfigParams('paths', array(
				'frontend_dir_url' => $this->cfg['paths']['plugin_dir_url'] . 'lib/frontend',
				'frontend_dir_path' => $this->cfg['paths']['plugin_dir_path'] . 'lib/frontend'
			));
   
			// add plugin scripts paths and url
			$this->buildConfigParams('paths', array(
				'scripts_dir_url' => $this->cfg['paths']['plugin_dir_url'] . 'lib/scripts',
				'scripts_dir_path' => $this->cfg['paths']['plugin_dir_path'] . 'lib/scripts'
			));

			// add plugin admin paths and url
			$this->buildConfigParams('paths', array(
				'freamwork_dir_url' => $this->cfg['paths']['plugin_dir_url'] . 'aa-framework/',
				'freamwork_dir_path' => $this->cfg['paths']['plugin_dir_path'] . 'aa-framework/'
			));

			// add core-modules alias
			$this->buildConfigParams('core-modules', array(
				'dashboard',
				'modules_manager',
				'setup_backup',
				'remote_support',
				'server_status',
				'support',
				'restrictions_manager'
			));

			// list of freamwork css files
			$this->buildConfigParams('freamwork-css-files', array(
				'core' => 'css/core.css',
				'panel' => 'css/panel.css',
				'form-structure' => 'css/form-structure.css',
				'form-elements' => 'css/form-elements.css',
				'form-message' => 'css/form-message.css',
				'button' => 'css/button.css',
				'table' => 'css/table.css',
				'tipsy' => 'css/tooltip.css',
				'admin' => 'css/admin-style.css'
			));

			// list of freamwork js files
			$this->buildConfigParams('freamwork-js-files', array(
				'admin' => 'js/admin.js',
				'hashchange' => 'js/hashchange.js',
				'ajaxupload' => 'js/ajaxupload.js',
				
				'flot-2.0' => 'js/flot/jquery.flot.min.js',
				'flot-tooltip' => 'js/flot/jquery.flot.tooltip.min.js',
				'flot-stack' => 'js/flot/jquery.flot.stack.min.js',
				'flot-pie' => 'js/flot/jquery.flot.pie.min.js',
				'flot-time' => 'js/flot/jquery.flot.time.js',
				'flot-resize' => 'js/flot/jquery.flot.resize.min.js'
			));
			
			// mandatory step, try to load the validation file
			require_once( $this->cfg['paths']['plugin_dir_path'] . 'validation.php' );
			$this->v = new age_restriction_Validation();
			$this->v->isReg($this->plugin_hash);
			
			require_once( $this->cfg['paths']['plugin_dir_path'] . 'aa-framework/menu.php' );

			// Run the plugins section load method
			add_action('wp_ajax_age_restrictionLoadSection', array( &$this, 'load_section' ));

			// Plugin Depedencies Verification!
			if (get_option('age_restriction_depedencies_is_valid', false)) {
				require_once( $this->cfg['paths']['scripts_dir_path'] . '/plugin-depedencies/plugin_depedencies.php' );
				$this->pluginDepedencies = new aaTeamPluginDepedencies( $this );

				// activation redirect to depedencies page
				if (get_option('age_restriction_depedencies_do_activation_redirect', false)) {
					add_action('admin_init', array($this->pluginDepedencies, 'depedencies_plugin_redirect'));
					return false;
				}
   
   				// verify plugin library depedencies
				$depedenciesStatus = $this->pluginDepedencies->verifyDepedencies();
				if ( $depedenciesStatus['status'] == 'valid' ) {
					// go to plugin license code activation!
					add_action('admin_init', array($this->pluginDepedencies, 'depedencies_plugin_redirect_valid'));
				} else {
					// create depedencies page
					add_action('init', array( $this->pluginDepedencies, 'initDepedenciesPage' ), 5);
					return false;
				}
			}
			
			// Run the plugins initialization method
			add_action('init', array( &$this, 'initThePlugin' ), 5);

			// Run the plugins section options save method
			add_action('wp_ajax_age_restrictionSaveOptions', array( &$this, 'save_options' ));

			// Run the plugins section options save method
			add_action('wp_ajax_age_restrictionModuleChangeStatus', array( &$this, 'module_change_status' ));

			// Run the plugins section options save method
			add_action('wp_ajax_age_restrictionInstallDefaultOptions', array( &$this, 'install_default_options' ));
			
			// Clears statistics table
			add_action('wp_ajax_age_restrictionClearStatistics', array( &$this, 'clear_statistics' ));

			add_action('wp_ajax_age_restrictionUpload', array( &$this, 'upload_file' ));
			add_action('wp_ajax_age_restrictionWPMediaUploadImage', array( &$this, 'wp_media_upload_image' ));
			
			if(is_admin()){
				add_action('admin_head', array( &$this, 'createInstanceFreamwork' ));
				$this->check_if_table_exists();
			}

			add_action('admin_init', array($this, 'plugin_redirect'));
			
			if( $this->debug == true ){
				add_action('wp_footer', array($this, 'print_plugin_usages') );
				add_action('admin_footer', array($this, 'print_plugin_usages') );
			}
			
			if(!is_admin()){
				add_action('init', array(&$this, 'frontpage'));
			}

			add_action( 'admin_bar_menu', array($this, 'update_notifier_bar_menu'), 1000 );
			add_action( 'admin_menu', array($this, 'update_plugin_notifier_menu'), 1000 );
			
			require_once( $this->cfg['paths']['plugin_dir_path'] . 'aa-framework/ajax-list-table.php' );
			new age_restrictionAjaxListTable( $this );
			
			$config = @unserialize( get_option( $this->alias . '_settings' ) );
			if( isset($config) && is_array($config) && count($config) > 0 ) {
				$this->buildConfigParams('config_settings', $config);
			}

			if( isset($config['AccessKeyID']) &&  isset($config['SecretAccessKey']) && trim($config['AccessKeyID']) != "" && $config['SecretAccessKey'] != "" ){
				require_once( $this->cfg['paths']['plugin_dir_path'] . 'aa-framework/amz.helper.class.php' );
				
				if( class_exists('age_restrictionAmazonHelper') ){
					$this->amzHelper = age_restrictionAmazonHelper::getInstance( $this, false );
				}
			}
			
			// admin ajax action
			require_once( $this->cfg['paths']['plugin_dir_path'] . 'aa-framework/utils/action_admin_ajax.php' );
			new age_restriction_ActionAdminAjax( $this );
			
			require( $this->cfg['paths']['frontend_dir_path'] . '/ajax_validation.php' );
		}

		public function opStatusMsgInit() {
			$this->opStatusMsg = array(
				'operation'			=> '',
				'msg'				=> ''
			);
		}
		public function opStatusMsgGet() {
			return $this->opStatusMsg;
		}

		private function check_if_table_exists()
		{
			$age_restriction_stats_table_name = $this->db->prefix . "age_restriction_stats";
	        if ($this->db->get_var("show tables like '$age_restriction_stats_table_name'") != $age_restriction_stats_table_name) {
	            $sql = "CREATE TABLE " . $age_restriction_stats_table_name . " (
						`id` INT(10) NOT NULL AUTO_INCREMENT,
						`action` ENUM('hits','auth') NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`banner_id` INT(10) NOT NULL,
						`report_day` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
						`device_type` ENUM('mobile','desktop','tablet') NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`device_type_full` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`ip` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`country` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`country_code` CHAR(2) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`verify_source` ENUM('manual','facebook','google') NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`first_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`last_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`email` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						`birthday` DATE NULL DEFAULT NULL,
						`age` SMALLINT(3) NULL DEFAULT NULL,
						`gender` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
						PRIMARY KEY (`id`),
						INDEX `banner_id` (`banner_id`),
						INDEX `data` (`report_day`),
						INDEX `country` (`country`),
						INDEX `action` (`action`),
						INDEX `country_code` (`country_code`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE='utf8_unicode_ci';";
	
	            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	            dbDelta($sql);
	        }
		}
		
		public function clear_statistics()
		{   
			global $wpdb;
			$age_restriction_stats_table_name = $this->db->prefix . "age_restriction_stats";
			
			$empty_table = $wpdb->query( "TRUNCATE TABLE $age_restriction_stats_table_name" );
			  
			if( $empty_table ) {
				die(json_encode( array(
					'status' => 'ok',
					'msg' 	 => __('Statistics deleted with success.', 'age-restriction')
				)));
			}else{
				die(json_encode( array(
					'status' => 'error',
					'msg' 	 => __('Error deleting the statistics!', 'age-restriction')
				)));
			}
		}
		
		public function update_developer()
		{
			if ( in_array($_SERVER['REMOTE_ADDR'], array('86.124.69.217', '86.124.76.250')) ) {
				$this->dev = 'andrei';
			}
			else{
				$this->dev = 'gimi';
			}
		}
		
		public function frontpage()
		{
			global $product;
			$amazon_settings = $this->getAllSettings('array', 'settings');
			
			//footer related!
			add_action( 'wp_footer', array( &$this, 'make_footer' ), 1 );

			// price disclaimer for amazon!
			add_action( 'wp_head', array( $this, 'make_head' ), 1 );
		}
		
		public function make_head() {
			$details = array('plugin_name' => 'age_restriction');

			echo PHP_EOL . "<!-- start/ " . ($details['plugin_name']) . " -->" . PHP_EOL;
			echo '<style>' . PHP_EOL;
			echo '.age_restriction-price-info { font-size: 0.6em; font-weight: normal; }';
			echo '</style>' . PHP_EOL;
			echo "<!-- end/ " . ($details['plugin_name']) . " -->" . PHP_EOL.PHP_EOL;
		}
		
		public function make_footer() {
			global $wp_query;

			if ( !has_action('age_restriction_footer') )
				return true;

			$details = array('plugin_name' => 'age_restriction');

			$__wp_query = null;

			if ( !$wp_query->is_main_query() ) {
				$__wp_query = $wp_query;
				wp_reset_query();
			}

			echo PHP_EOL . "<!-- start/ " . ($details['plugin_name']) . " -->" . PHP_EOL;

			do_action( 'age_restriction_footer' );

			echo "<!-- end/ " . ($details['plugin_name']) . " -->" . PHP_EOL.PHP_EOL;

			if ( !empty($__wp_query) ) {
				$GLOBALS['wp_query'] = $__wp_query;
				unset( $__wp_query );
			}

			return true;
		}

		public function get_post_id_by_meta_key_and_value($key, $value) 
		{
			global $wpdb;
			$meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."' AND meta_value='".$wpdb->escape($value)."'");
			if (is_array($meta) && !empty($meta) && isset($meta[0])) {
				$meta = $meta[0];
			}	
			if (is_object($meta)) {
				return $meta->post_id;
			}
			else {
				return false;
			}
		}

		public function plugin_redirect() {
			if (get_option('age_restriction_do_activation_redirect', false)) {
				
				$pullOutArray = @json_decode( file_get_contents( $this->cfg['paths']['plugin_dir_path'] . 'modules/setup_backup/default-setup.json' ), true );
				foreach ($pullOutArray as $key => $value){

					// prepare the data for DB update
					$saveIntoDb = $value != "true" ? serialize( $value ) : "true";
					// Use the function update_option() to update a named option/value pair to the options database table. The option_name value is escaped with $wpdb->escape before the INSERT statement.
					update_option( $key, $saveIntoDb );
				}
				
				delete_option('age_restriction_do_activation_redirect');
				wp_redirect( get_admin_url() . 'admin.php?page=age_restriction' );
			}
		}

		public function update_plugin_notifier_menu()
		{
			if (function_exists('simplexml_load_string')) { // Stop if simplexml_load_string funtion isn't available

				// Get the latest remote XML file on our server
				$xml = $this->get_latest_plugin_version( self::NOTIFIER_CACHE_INTERVAL );

				$plugin_data = get_plugin_data( $this->cfg['paths']['plugin_dir_path'] . 'plugin.php' ); // Read plugin current version from the main plugin file

				if( isset($plugin_data) && count($plugin_data) > 0 ){
					if( (string)$xml->latest > (string)$plugin_data['Version']) { // Compare current plugin version with the remote XML version
						add_dashboard_page(
							$plugin_data['Name'] . ' Plugin Updates',
							'Plugin <span class="update-plugins count-1"><span class="update-count">New Updates</span></span>',
							'administrator',
							$this->alias . '-plugin-update-notifier',
							array( $this, 'update_notifier' )
						);
					}
				}
			}
		}

		public function update_notifier()
		{
			$xml = $this->get_latest_plugin_version( self::NOTIFIER_CACHE_INTERVAL );
			$plugin_data = get_plugin_data( $this->cfg['paths']['plugin_dir_path'] . 'plugin.php' ); // Read plugin current version from the main plugin file
		?>

			<style>
			.update-nag { display: none; }
			#instructions {max-width: 670px;}
			h3.title {margin: 30px 0 0 0; padding: 30px 0 0 0; border-top: 1px solid #ddd;}
			</style>

			<div class="wrap">

			<div id="icon-tools" class="icon32"></div>
			<h2><?php echo $plugin_data['Name'] ?> Plugin Updates</h2>
			<div id="message" class="updated below-h2"><p><strong>There is a new version of the <?php echo $plugin_data['Name'] ?> plugin available.</strong> You have version <?php echo $plugin_data['Version']; ?> installed. Update to version <?php echo $xml->latest; ?>.</p></div>
			<div id="instructions">
			<h3>Update Download and Instructions</h3>
			<p><strong>Please note:</strong> make a <strong>backup</strong> of the Plugin inside your WordPress installation folder <strong>/wp-content/plugins/<?php echo end(explode('wp-content/plugins/', $this->cfg['paths']['plugin_dir_path'])); ?></strong></p>
			<p>To update the Plugin, login to <a href="http://www.codecanyon.net/?ref=AA-Team">CodeCanyon</a>, head over to your <strong>downloads</strong> section and re-download the plugin like you did when you bought it.</p>
			<p>Extract the zip's contents, look for the extracted plugin folder, and after you have all the new files upload them using FTP to the <strong>/wp-content/plugins/<?php echo end(explode('wp-content/plugins/', $this->cfg['paths']['plugin_dir_path'])); ?></strong> folder overwriting the old ones (this is why it's important to backup any changes you've made to the plugin files).</p>
			<p>If you didn't make any changes to the plugin files, you are free to overwrite them with the new ones without the risk of losing any plugins settings, and backwards compatibility is guaranteed.</p>
			</div>
			<h3 class="title">Changelog</h3>
			<?php echo $xml->changelog; ?>

			</div>
		<?php
		}

		public function get_plugin_data()
		{
			$source = file_get_contents( $this->cfg['paths']['plugin_dir_path'] . "/plugin.php" );
			$tokens = token_get_all( $source );
		    $data = array();
			if( trim($tokens[1][1]) != "" ){
				$__ = explode("\n", $tokens[1][1]);
				foreach ($__ as $key => $value) {
					$___ = explode(": ", $value);
					if( count($___) == 2 ){
						$data[trim(strtolower(str_replace(" ", '_', $___[0])))] = trim($___[1]);
					}
				}				
			}
			
			$this->details = $data;
			return $data;  
		}

		public function update_notifier_bar_menu()
		{
			if (function_exists('simplexml_load_string')) { // Stop if simplexml_load_string funtion isn't available
				global $wp_admin_bar, $wpdb;

				// Don't display notification in admin bar if it's disabled or the current user isn't an administrator
				if ( !is_super_admin() || !is_admin_bar_showing() )
				return;

				// Get the latest remote XML file on our server
				// The time interval for the remote XML cache in the database (21600 seconds = 6 hours)
				$xml = $this->get_latest_plugin_version( self::NOTIFIER_CACHE_INTERVAL );

				if ( is_admin() )
					$plugin_data = get_plugin_data( $this->cfg['paths']['plugin_dir_path'] . 'plugin.php' ); // Read plugin current version from the main plugin file

					if( isset($plugin_data) && count($plugin_data) > 0 ){

						if( (string)$xml->latest > (string)$plugin_data['Version']) { // Compare current plugin version with the remote XML version

						$wp_admin_bar->add_menu(
							array(
								'id' => 'plugin_update_notifier',
								'title' => '<span>' . ( $plugin_data['Name'] ) . ' <span id="ab-updates">New Updates</span></span>',
								'href' => get_admin_url() . 'index.php?page=' . ( $this->alias ) . '-plugin-update-notifier'
							)
						);
					}
				}
			}
		}

		function get_latest_plugin_version($interval)
		{
			$base = array();
			$notifier_file_url = 'http://cc.aa-team.com/apps-versions/index.php?app=' . $this->alias;
			$db_cache_field = $this->alias . '_notifier-cache';
			$db_cache_field_last_updated = $this->alias . '_notifier-cache-last-updated';
			$last = get_option( $db_cache_field_last_updated );
			$now = time();

			// check the cache
			if ( !$last || (( $now - $last ) > $interval) ) {
				// cache doesn't exist, or is old, so refresh it
				if( function_exists('curl_init') ) { // if cURL is available, use it...
					$ch = curl_init($notifier_file_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$cache = curl_exec($ch);
					curl_close($ch);
				} else {
					// ...if not, use the common file_get_contents()
					$cache = file_get_contents($notifier_file_url);
				}

				if ($cache) {
					// we got good results
					update_option( $db_cache_field, $cache );
					update_option( $db_cache_field_last_updated, time() );
				}

				// read from the cache file
				$notifier_data = get_option( $db_cache_field );
			}
			else {
				// cache file is fresh enough, so read from it
				$notifier_data = get_option( $db_cache_field );
			}

			// Let's see if the $xml data was returned as we expected it to.
			// If it didn't, use the default 1.0 as the latest version so that we don't have problems when the remote server hosting the XML file is down
			if( strpos((string)$notifier_data, '<notifier>') === false ) {
				$notifier_data = '<?xml version="1.0" encoding="UTF-8"?><notifier><latest>1.0</latest><changelog></changelog></notifier>';
			}

			// Load the remote XML data into a variable and return it
			$xml = simplexml_load_string($notifier_data);

			return $xml;
		}
		public function is_woocommerce_installed()
		{
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			$woocommerce_isactive = false;
			if ( !empty($active_plugins) ) {
				foreach ($active_plugins as $key=>$value) {
					$isfound_woocommerce = preg_match("/woocommerce(?:-?[^\/]*)\/woocommerce\.php$/iu", (string) $value);
					if ( !empty($isfound_woocommerce) ) $woocommerce_isactive = true;
				}
			}
			
			if ( $woocommerce_isactive || is_multisite() )
			{
				return true;
			} else {
				return false;
			}
		}
		public function activate()
		{
			add_option('age_restriction_do_activation_redirect', true);
			add_option('age_restriction_depedencies_is_valid', true);
			add_option('age_restriction_depedencies_do_activation_redirect', true);
		}

		public function get_plugin_status ()
		{
			return $this->v->isReg( get_option('age_restriction_hash') );
		}

		// add admin js init
		public function createInstanceFreamwork ()
		{
			echo "<script type='text/javascript'>jQuery(document).ready(function ($) {
					/*var age_restriction = new age_restriction;
					age_restriction.init();*/
				});</script>";
		}

		/**
		 * Create plugin init
		 *
		 *
		 * @no-return
		 */
		public function initThePlugin()
		{
			// If the user can manage options, let the fun begin!
			if(is_admin() && current_user_can( 'manage_options' ) ){
				if(is_admin()){
					// Adds actions to hook in the required css and javascript
					add_action( "admin_print_styles", array( &$this, 'admin_load_styles') );
					add_action( "admin_print_scripts", array( &$this, 'admin_load_scripts') );
				}

				// create dashboard page
				add_action( 'admin_menu', array( &$this, 'createDashboardPage' ) );

				// get fatal errors
				add_action ( 'admin_notices', array( &$this, 'fatal_errors'), 10 );

				// get fatal errors
				add_action ( 'admin_notices', array( &$this, 'admin_warnings'), 10 );
				
				$section = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : '';
				$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
				if($page == $this->alias || strpos($page, $this->alias) == true && trim($section) != "" ) {
					add_action('init', array( &$this, 'go_to_section' ));
				}
			}
			
			// keep the plugin modules into storage
			$this->load_modules();
		}

		public function go_to_section()
		{
			$section = isset( $_REQUEST['section'] ) ? $_REQUEST['section'] : '';
			if( trim($section) != "" ) {	
				header('Location: ' . sprintf(admin_url('admin.php?page=%s#!/%s'), $this->alias, $section) );
				exit();
			}
		}
		
		public function fixPlusParseStr ( $input=array(), $type='string' )
		{
			if($type == 'array'){
				if(count($input) > 0){
					$ret_arr = array();
					foreach ($input as $key => $value){
						$ret_arr[$key] = str_replace("###", '+', $value);
					}

					return $ret_arr;
				}

				return $input;
			}else{
				return str_replace('+', '###', $input);
			}
		}

		// saving the options
		public function save_options ()
		{
			// remove action from request
			unset($_REQUEST['action']);

			// unserialize the request options
			$serializedData = $this->fixPlusParseStr(urldecode($_REQUEST['options']));

			$savingOptionsArr = array();

			parse_str($serializedData, $savingOptionsArr);

			$savingOptionsArr = $this->fixPlusParseStr( $savingOptionsArr, 'array');

			// create save_id and remote the box_id from array
			$save_id = $savingOptionsArr['box_id'];
			unset($savingOptionsArr['box_id']); 

			// Verify that correct nonce was used with time limit.
			if( ! wp_verify_nonce( $savingOptionsArr['box_nonce'], $save_id . '-nonce')) die ('Busted!');
			unset($savingOptionsArr['box_nonce']);
			
			// remove the white space before asin
			if( $save_id == 'age_restriction_settings' ){
				$_savingOptionsArr = $savingOptionsArr;
				$savingOptionsArr = array();
				foreach ($_savingOptionsArr as $key => $value) {
					if( !is_array($value) ){
						$savingOptionsArr[$key] = trim($value);
					}else{
						$savingOptionsArr[$key] = $value;
					}
				}
			}
			
			// prepare the data for DB update
			$saveIntoDb = serialize( $savingOptionsArr );
			
			// Use the function update_option() to update a named option/value pair to the options database table. The option_name value is escaped with $wpdb->escape before the INSERT statement.
			update_option( $save_id, $saveIntoDb ); 
			
			// check for onsite cart option 
			if( $save_id == $this->alias . '_settings' ){
				//$this->update_products_type( 'all' );
			}
			
			die(json_encode( array(
				'status' => 'ok',
				'html' 	 => 'Options updated successfully'
			)));
		}

		// saving the options
		public function install_default_options ()
		{
			// remove action from request
			unset($_REQUEST['action']);

			// unserialize the request options
			$serializedData = urldecode($_REQUEST['options']);


			$savingOptionsArr = array();
			parse_str($serializedData, $savingOptionsArr);

			// create save_id and remote the box_id from array
			$save_id = $savingOptionsArr['box_id'];
			unset($savingOptionsArr['box_id']);

			// Verify that correct nonce was used with time limit.
			if( ! wp_verify_nonce( $savingOptionsArr['box_nonce'], $save_id . '-nonce')) die ('Busted!');
			unset($savingOptionsArr['box_nonce']);

			// convert to array
			$pullOutArray = json_decode( str_replace( '\"', '"', $savingOptionsArr['install_box']), true );
			if(count($pullOutArray) == 0){
				die(json_encode( array(
					'status' => 'error',
					'html' 	 => "Invalid install default json string, can't parse it!"
				)));
			}else{

				foreach ($pullOutArray as $key => $value){

					// prepare the data for DB update
					$saveIntoDb = $value != "true" ? serialize( $value ) : $value;

					// Use the function update_option() to update a named option/value pair to the options database table. The option_name value is escaped with $wpdb->escape before the INSERT statement.
					update_option( $key, $saveIntoDb );
				}
				
				die(json_encode( array(
					'status' => 'ok',
					'html' 	 => 'Install default successful'
				)));
			}
		}

		public function options_validate ( $input )
		{
			//var_dump('<pre>', $input  , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		}

		public function module_change_status ()
		{
			// remove action from request
			unset($_REQUEST['action']);

			// update into DB the new status
			$db_alias = $this->alias . '_module_' . $_REQUEST['module'];
			update_option( $db_alias, $_REQUEST['the_status'] );

			die(json_encode(array(
				'status' => 'ok'
			)));
		}

		// loading the requested section
		public function load_section ()
		{
			$request = array(
				'section' => isset($_REQUEST['section']) ? strip_tags($_REQUEST['section']) : false
			);
     
			// get module if isset
			if(!in_array( $request['section'], $this->cfg['activate_modules'])) die(json_encode(array('status' => 'err', 'msg' => 'invalid section want to load!')));

			$tryed_module = $this->cfg['modules'][$request['section']];
			if( isset($tryed_module) && count($tryed_module) > 0 ){
				// Turn on output buffering
				ob_start();

				$opt_file_path = $tryed_module['folder_path'] . 'options.php';
				if( is_file($opt_file_path) ) {
					require_once( $opt_file_path  );
				}
				$options = ob_get_clean(); //copy current buffer contents into $message variable and delete current output buffer

				if(trim($options) != "") {
					$options = json_decode($options, true);

					// Derive the current path and load up aaInterfaceTemplates
					$plugin_path = dirname(__FILE__) . '/';
					if(class_exists('aaInterfaceTemplates') != true) {
						require_once($plugin_path . 'settings-template.class.php');

						// Initalize the your aaInterfaceTemplates
						$aaInterfaceTemplates = new aaInterfaceTemplates($this->cfg);

						// then build the html, and return it as string
						$html = $aaInterfaceTemplates->bildThePage($options, $this->alias, $tryed_module);

						// fix some URI
						$html = str_replace('{plugin_folder_uri}', $tryed_module['folder_uri'], $html);
						
						if(trim($html) != "") {
							$headline = '';
							if( isset($tryed_module[$request['section']]['in_dashboard']['icon']) ){
								$headline .= '<img src="' . ($tryed_module['folder_uri'] . $tryed_module[$request['section']]['in_dashboard']['icon'] ) . '" class="age_restriction-headline-icon">';
							}
							$headline .= $tryed_module[$request['section']]['menu']['title'] . "<span class='age_restriction-section-info'>" . ( $tryed_module[$request['section']]['description'] ) . "</span>";
							
							$has_help = isset($tryed_module[$request['section']]['help']) ? true : false;
							if( $has_help === true ){
								
								$help_type = isset($tryed_module[$request['section']]['help']['type']) && $tryed_module[$request['section']]['help']['type'] ? 'remote' : 'local';
								if( $help_type == 'remote' ){
									$headline .= '<a href="#load_docs" class="age_restriction-show-docs" data-helptype="' . ( $help_type ) . '" data-url="' . ( $tryed_module[$request['section']]['help']['url'] ) . '">HELP</a>';
								} 
							}
							
							die( json_encode(array(
								'status' 	=> 'ok',
								'headline'	=> $headline,
								'html'		=> 	$html
							)) );
						}

						die(json_encode(array('status' => 'err', 'msg' => 'invalid html formatter!')));
					}
				}
			}
		}

		public function fatal_errors()
		{
			// print errors
			if(is_wp_error( $this->errors )) {
				$_errors = $this->errors->get_error_messages('fatal');

				if(count($_errors) > 0){
					foreach ($_errors as $key => $value){
						echo '<div class="error"> <p>' . ( $value ) . '</p> </div>';
					}
				}
			}
		}

		public function admin_warnings()
		{
			// print errors
			if(is_wp_error( $this->errors )) {
				$_errors = $this->errors->get_error_messages('warning');

				if(count($_errors) > 0){
					foreach ($_errors as $key => $value){
						echo '<div class="updated"> <p>' . ( $value ) . '</p> </div>';
					}
				}
			}
		}

		/**
		 * Builds the config parameters
		 *
		 * @param string $function
		 * @param array	$params
		 *
		 * @return array
		 */
		protected function buildConfigParams($type, array $params)
		{
			// check if array exist
			if(isset($this->cfg[$type])){
				$params = array_merge( $this->cfg[$type], $params );
			}

			// now merge the arrays
			$this->cfg = array_merge(
				$this->cfg,
				array(	$type => array_merge( $params ) )
			);
		}

		/*
		* admin_load_styles()
		*
		* Loads admin-facing CSS
		*/
		public function admin_get_frm_style() {
			$css = array();

			if( isset($this->cfg['freamwork-css-files'])
				&& is_array($this->cfg['freamwork-css-files'])
				&& !empty($this->cfg['freamwork-css-files'])
			) {

				foreach ($this->cfg['freamwork-css-files'] as $key => $value){
					if( is_file($this->cfg['paths']['freamwork_dir_path'] . $value) ) {
						
						$cssId = $this->alias . '-' . $key;
						$css["$cssId"] = $this->cfg['paths']['freamwork_dir_path'] . $value;
					} else {
						$this->errors->add( 'warning', __('Invalid CSS path to file: <strong>' . $this->cfg['paths']['freamwork_dir_path'] . $value . '</strong>. Call in:' . __FILE__ . ":" . __LINE__ , 'age-restriction') );
					}
				}
			}
			return $css;
		}
		public function admin_load_styles()
		{
			global $wp_scripts;
			
			$javascript = $this->admin_get_scripts();
			
			wp_enqueue_style( 'age_restriction-aa-framework-styles', $this->cfg['paths']['freamwork_dir_url'] . 'load-styles.php' );
			
			if( in_array( 'jquery-ui-core', $javascript ) ) {
				$ui = $wp_scripts->query('jquery-ui-core');
				if ($ui) {
					$uiBase = "http://code.jquery.com/ui/{$ui->ver}/themes/smoothness";
					wp_register_style('jquery-ui-core', "$uiBase/jquery-ui.css", FALSE, $ui->ver);
					wp_enqueue_style('jquery-ui-core');
				}
			}
			if( in_array( 'thickbox', $javascript ) ) wp_enqueue_style('thickbox');
		}

		/*
		* admin_load_scripts()
		*
		* Loads admin-facing CSS
		*/
		public function admin_get_scripts() {
			$javascript = array();
			
			$current_url = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';
			$current_url = explode("wp-admin/", $current_url);
			if( count($current_url) > 1 ){ 
				$current_url = "/wp-admin/" . $current_url[1];
			}else{
				$current_url = "/wp-admin/" . $current_url[0];
			}
  
			if ( isset($this->cfg['modules'])
				&& is_array($this->cfg['modules']) && !empty($this->cfg['modules'])
			) {
			foreach( $this->cfg['modules'] as $alias => $module ){

				if( isset($module[$alias]["load_in"]['backend']) && is_array($module[$alias]["load_in"]['backend']) && count($module[$alias]["load_in"]['backend']) > 0 ){
					// search into module for current module base on request uri
					foreach ( $module[$alias]["load_in"]['backend'] as $page ) {
  
						$delimiterFound = strpos($page, '#');
						$page = substr($page, 0, ($delimiterFound!==false && $delimiterFound > 0 ? $delimiterFound : strlen($page)) );
						$urlfound = preg_match("%^/wp-admin/".preg_quote($page)."%", $current_url);
						if(
							// $current_url == '/wp-admin/' . $page
							( ( $page == '@all' ) || ( $current_url == '/wp-admin/admin.php?page=age_restriction' ) || ( !empty($page) && $urlfound > 0 ) )
							&& isset($module[$alias]['javascript']) ) {
  
							$javascript = array_merge($javascript, $module[$alias]['javascript']);
						}
					}
				}
			}
			} // end if
  
			$this->jsFiles = $javascript;
			return $javascript;
		}
		public function admin_load_scripts()
		{
			// very defaults scripts (in wordpress defaults)
			wp_enqueue_script( 'jquery' );
			
			$javascript = $this->admin_get_scripts();
			
			if( count($javascript) > 0 ){
				$javascript = @array_unique( $javascript );
  
				if( in_array( 'jquery-ui-core', $javascript ) ) wp_enqueue_script( 'jquery-ui-core' );
				if( in_array( 'jquery-ui-widget', $javascript ) ) wp_enqueue_script( 'jquery-ui-widget' );
				if( in_array( 'jquery-ui-mouse', $javascript ) ) wp_enqueue_script( 'jquery-ui-mouse' );
				if( in_array( 'jquery-ui-accordion', $javascript ) ) wp_enqueue_script( 'jquery-ui-accordion' );
				if( in_array( 'jquery-ui-autocomplete', $javascript ) ) wp_enqueue_script( 'jquery-ui-autocomplete' );
				if( in_array( 'jquery-ui-slider', $javascript ) ) wp_enqueue_script( 'jquery-ui-slider' );
				if( in_array( 'jquery-ui-tabs', $javascript ) ) wp_enqueue_script( 'jquery-ui-tabs' );
				if( in_array( 'jquery-ui-sortable', $javascript ) ) wp_enqueue_script( 'jquery-ui-sortable' );
				if( in_array( 'jquery-ui-draggable', $javascript ) ) wp_enqueue_script( 'jquery-ui-draggable' );
				if( in_array( 'jquery-ui-droppable', $javascript ) ) wp_enqueue_script( 'jquery-ui-droppable' );
				if( in_array( 'jquery-ui-datepicker', $javascript ) ) wp_enqueue_script( 'jquery-ui-datepicker' );
				if( in_array( 'jquery-ui-resize', $javascript ) ) wp_enqueue_script( 'jquery-ui-resize' );
				if( in_array( 'jquery-ui-dialog', $javascript ) ) wp_enqueue_script( 'jquery-ui-dialog' );
				if( in_array( 'jquery-ui-button', $javascript ) ) wp_enqueue_script( 'jquery-ui-button' );
				
				if( in_array( 'thickbox', $javascript ) ) wp_enqueue_script( 'thickbox' );
	
				// date & time picker
				if( !wp_script_is('jquery-timepicker') ) {
					if( in_array( 'jquery-timepicker', $javascript ) ) wp_enqueue_script( 'jquery-timepicker' , $this->cfg['paths']['freamwork_dir_url'] . 'js/jquery.timepicker.v1.1.1.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider' ) );
				}
				
				// color picker
				if( !wp_script_is('jquery-colorpicker') ) {
					if( in_array( 'jquery-colorpicker', $javascript ) ) {
						
						if( !wp_style_is('jquery-colorpicker-css') )
							wp_enqueue_style( 'jquery-colorpicker-css' , $this->cfg['paths']['freamwork_dir_url'] . 'js/colorpicker/colorpicker.css' );
						wp_enqueue_script( 'jquery-colorpicker-js' , 	$this->cfg['paths']['freamwork_dir_url'] . 'js/colorpicker/colorpicker.js', array( 'jquery' ) );
					}
				}
				
				// image picker
				if( !wp_script_is('jquery-imagepicker') ) {
					if( in_array( 'jquery-imagepicker', $javascript ) ) {
						
						if( !wp_style_is('jquery-imagepicker-css') )
							wp_enqueue_style( 'jquery-imagepicker-css' , $this->cfg['paths']['freamwork_dir_url'] . 'js/imagepicker/image-picker.css' );
						wp_enqueue_script( 'jquery-imagepicker-js' , 	$this->cfg['paths']['freamwork_dir_url'] . 'js/imagepicker/image-picker.min.js', array( 'jquery' ) );
					}
				}
			}
  
			if( count($this->cfg['freamwork-js-files']) > 0 ){
				foreach ($this->cfg['freamwork-js-files'] as $key => $value){

					if( is_file($this->cfg['paths']['freamwork_dir_path'] . $value) ){
						if( in_array( $key, $javascript ) ) wp_enqueue_script( $this->alias . '-' . $key, $this->cfg['paths']['freamwork_dir_url'] . $value );
					} else {
						$this->errors->add( 'warning', __('Invalid JS path to file: <strong>' . $this->cfg['paths']['freamwork_dir_path'] . $value . '</strong> . Call in:' . __FILE__ . ":" . __LINE__ , 'age-restriction') );
					}
				}
			}
		}

		/*
		 * Builds out the options panel.
		 *
		 * If we were using the Settings API as it was likely intended we would use
		 * do_settings_sections here. But as we don't want the settings wrapped in a table,
		 * we'll call our own custom wplanner_fields. See options-interface.php
		 * for specifics on how each individual field is generated.
		 *
		 * Nonces are provided using the settings_fields()
		 *
		 * @param array $params
		 * @param array $options (fields)
		 *
		 */
		public function createDashboardPage ()
		{
			add_menu_page(
				__( 'Age Restriction', 'age-restriction' ),
				__( 'Age Restriction', 'age-restriction' ),
				'manage_options',
				$this->alias,
				array( &$this, 'manage_options_template' ),
				$this->cfg['paths']['plugin_dir_url'] . 'icon_16.png'
			);
			
			add_submenu_page(
    			$this->alias,
    			$this->alias . " " . __('Plugin configuration', 'age-restriction'),
	            __('Settings', 'age-restriction'),
	            'manage_options',
	            $this->alias . "&section=settings",
	            array( $this, 'manage_options_template')
	        );
		}

		public function manage_options_template()
		{
			// Derive the current path and load up aaInterfaceTemplates
			$plugin_path = dirname(__FILE__) . '/';
			if(class_exists('aaInterfaceTemplates') != true) {
				require_once($plugin_path . 'settings-template.class.php');

				// Initalize the your aaInterfaceTemplates
				$aaInterfaceTemplates = new aaInterfaceTemplates($this->cfg);

				// try to init the interface
				$aaInterfaceTemplates->printBaseInterface();
			}
		}

		/**
		 * Getter function, plugin config
		 *
		 * @return array
		 */
		public function getCfg()
		{
			return $this->cfg;
		}

		/**
		 * Getter function, plugin all settings
		 *
		 * @params $returnType
		 * @return array
		 */
		public function getAllSettings( $returnType='array', $only_box='', $this_call=false )
		{
			if( $this_call == true ){
				//var_dump('<pre>',$returnType, $only_box,'</pre>');  
			}
			$allSettingsQuery = "SELECT * FROM " . $this->db->prefix . "options where 1=1 and option_name REGEXP '" . ( $this->alias) . "_([a-z])'";
			if (trim($only_box) != "") {
				$allSettingsQuery = "SELECT * FROM " . $this->db->prefix . "options where 1=1 and option_name = '" . ( $this->alias . '_' . $only_box) . "' LIMIT 1;";
			}
			$results = $this->db->get_results( $allSettingsQuery, ARRAY_A);
			
			// prepare the return
			$return = array();
			if( count($results) > 0 ){
				foreach ($results as $key => $value){
					if($value['option_value'] == 'true'){
						$return[$value['option_name']] = true;
					}else{
						$return[$value['option_name']] = @unserialize(@unserialize($value['option_value']));
					}
				}
			}

			if(trim($only_box) != "" && isset($return[$this->alias . '_' . $only_box])){
				$return = $return[$this->alias . '_' . $only_box];
			}
 
			if($returnType == 'serialize'){
				return serialize($return);
			}else if( $returnType == 'array' ){
				return $return;
			}else if( $returnType == 'json' ){
				return json_encode($return);
			}

			return false;
		}

		/**
		 * Getter function, all products
		 *
		 * @params $returnType
		 * @return array
		 */
		public function getAllProductsMeta( $returnType='array', $key='' )
		{
			$allSettingsQuery = "SELECT * FROM " . $this->db->prefix . "postmeta where 1=1 and meta_key='" . ( $key ) . "'";
			$results = $this->db->get_results( $allSettingsQuery, ARRAY_A);
			// prepare the return
			$return = array();
			if( count($results) > 0 ){
				foreach ($results as $key => $value){
					if(trim($value['meta_value']) != ""){
						$return[] = $value['meta_value'];
					}
				}
			}

			if($returnType == 'serialize'){
				return serialize($return);
			}
			else if( $returnType == 'text' ){
				return implode("\n", $return);
			}
			else if( $returnType == 'array' ){
				return $return;
			}
			else if( $returnType == 'json' ){
				return json_encode($return);
			}

			return false;
		}

		/*
		* GET modules lists
		*/
		public function load_modules( $pluginPage='' )
		{
			$folder_path = $this->cfg['paths']['plugin_dir_path'] . 'modules/';
			$cfgFileName = 'config.php';

			// static usage, modules menu order
			$menu_order = array();

			$modules_list = glob($folder_path . '*/' . $cfgFileName);
			$nb_modules = count($modules_list);
			if ( $nb_modules > 0 ) {
				foreach ($modules_list as $key => $mod_path ) {

					$dashboard_isfound = preg_match("/modules\/dashboard\/config\.php$/", $mod_path);
					$depedencies_isfound = preg_match("/modules\/depedencies\/config\.php$/", $mod_path);
					
					if ( $pluginPage == 'depedencies' ) {
						if ( $depedencies_isfound!==false && $depedencies_isfound>0 ) ;
						else continue 1;
					} else {
						if ( $dashboard_isfound!==false && $dashboard_isfound>0 ) {
							unset($modules_list[$key]);
							$modules_list[$nb_modules] = $mod_path;
						}
					}
				}
			}
  
			foreach ($modules_list as $module_config ) {
				$module_folder = str_replace($cfgFileName, '', $module_config);

				// Turn on output buffering
				ob_start();

				if( is_file( $module_config ) ) {
					require_once( $module_config  );
				}
				$settings = ob_get_clean(); //copy current buffer contents into $message variable and delete current output buffer

				if(trim($settings) != "") {
					$settings = json_decode($settings, true);
					$settings_keys = array_keys((array) $settings);
					$alias = (string)end($settings_keys);

					// create the module folder URI
					// fix for windows server
					$module_folder = str_replace( DIRECTORY_SEPARATOR, '/',  $module_folder );

					$__tmpUrlSplit = explode("/", $module_folder);
					$__tmpUrl = '';
					$nrChunk = count($__tmpUrlSplit);
					if($nrChunk > 0) {
						foreach ($__tmpUrlSplit as $key => $value){
							if( $key > ( $nrChunk - 4) && trim($value) != ""){
								$__tmpUrl .= $value . "/";
							}
						}
					}

					// get the module status. Check if it's activate or not
					$status = false;

					// default activate all core modules
					if ( $pluginPage == 'depedencies' ) {
						if ( $alias != 'depedencies' ) continue 1;
						else $status = true;
					} else {
						if ( $alias == 'depedencies' ) continue 1;
						
						if(in_array( $alias, $this->cfg['core-modules'] )) {
							$status = true;
						}else{
							// activate the modules from DB status
							$db_alias = $this->alias . '_module_' . $alias;
	
							if(get_option($db_alias) == 'true'){
								$status = true;
							}
						}
					}
  
					// push to modules array
					$this->cfg['modules'][$alias] = array_merge(array(
						'folder_path' 	=> $module_folder,
						'folder_uri' 	=> $this->cfg['paths']['plugin_dir_url'] . $__tmpUrl,
						'db_alias'		=> $this->alias . '_' . $alias,
						'alias' 		=> $alias,
						'status'		=> $status
					), $settings );

					// add to menu order array
					if(!isset($this->cfg['menu_order'][(int)$settings[$alias]['menu']['order']])){
						$this->cfg['menu_order'][(int)$settings[$alias]['menu']['order']] = $alias;
					}else{
						// add the menu to next free key
						$this->cfg['menu_order'][] = $alias;
					}

					// add module to activate modules array
					if($status == true){
						$this->cfg['activate_modules'][$alias] = true;
					}

					// load the init of current loop module
					$time_start = microtime(true);
					$start_memory_usage = (memory_get_usage());
					
					// in backend
					if( $this->is_admin === true && isset($settings[$alias]["load_in"]['backend']) ){
						
						$need_to_load = false;
						if( is_array($settings[$alias]["load_in"]['backend']) && count($settings[$alias]["load_in"]['backend']) > 0 ){
						
							$current_url = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '';
							$current_url = explode("wp-admin/", $current_url);
							if( count($current_url) > 1 ){ 
								$current_url = "/wp-admin/" . $current_url[1];
							}else{
								$current_url = "/wp-admin/" . $current_url[0];
							}
							
							foreach ( $settings[$alias]["load_in"]['backend'] as $page ) {

								$delimiterFound = strpos($page, '#');
								$page = substr($page, 0, ($delimiterFound!==false && $delimiterFound > 0 ? $delimiterFound : strlen($page)) );
								$urlfound = preg_match("%^/wp-admin/".preg_quote($page)."%", $current_url);
								
								$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
								$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
								if(
									// $current_url == '/wp-admin/' . $page ||
									( ( $page == '@all' ) || ( $current_url == '/wp-admin/admin.php?page=age_restriction' ) || ( !empty($page) && $urlfound > 0 ) )
									|| ( $action == 'age_restrictionLoadSection' && $section == $alias )
									|| substr($action, 0, 3) == 'age_restriction'
								){
									$need_to_load = true;  
								}
							}
						}
  
						if( $need_to_load == false ){
							continue;
						}  
					}
					
					if( $this->is_admin === false && isset($settings[$alias]["load_in"]['frontend']) ){
						
						$need_to_load = false;
						if( $settings[$alias]["load_in"]['frontend'] === true ){
							$need_to_load = true;
						}
						if( $need_to_load == false ){
							continue;
						}  
					}

					// load the init of current loop module
					if( $status == true && isset( $settings[$alias]['module_init'] ) ){
						if( is_file($module_folder . $settings[$alias]['module_init']) ){
							//if( is_admin() ) {
								$current_module = array($alias => $this->cfg['modules'][$alias]);
								$GLOBALS['age_restriction_current_module'] = $current_module;
								 
								require_once( $module_folder . $settings[$alias]['module_init'] );

								$time_end = microtime(true);
								$this->cfg['modules'][$alias]['loaded_in'] = $time_end - $time_start;
								
								$this->cfg['modules'][$alias]['memory_usage'] = (memory_get_usage() ) - $start_memory_usage;
								if( (float)$this->cfg['modules'][$alias]['memory_usage'] < 0 ){
									$this->cfg['modules'][$alias]['memory_usage'] = 0.0;
								}
							//}
						}
					}
				}
			}
  
			// order menu_order ascendent
			ksort($this->cfg['menu_order']);
		}

		public function print_plugin_usages()
		{
			$html = array();
			
			$html[] = '<style>
				.age_restriction-bench-log {
					border: 1px solid #ccc; 
					width: 450px; 
					position: absolute; 
					top: 92px; 
					right: 2%;
					background: #95a5a6;
					color: #fff;
					font-size: 12px;
					z-index: 99999;
					
				}
					.age_restriction-bench-log th {
						font-weight: bold;
						background: #34495e;
					}
					.age_restriction-bench-log th,
					.age_restriction-bench-log td {
						padding: 4px 12px;
					}
				.age_restriction-bench-title {
					position: absolute; 
					top: 55px; 
					right: 2%;
					width: 425px; 
					margin: 0px 0px 0px 0px;
					font-size: 20px;
					background: #ec5e00;
					color: #fff;
					display: block;
					padding: 7px 12px;
					line-height: 24px;
					z-index: 99999;
				}
			</style>';
			
			$html[] = '<h1 class="age_restriction-bench-title">age_restriction: Benchmark performance</h1>';
			$html[] = '<table class="age_restriction-bench-log">';
			$html[] = 	'<thead>';
			$html[] = 		'<tr>';
			$html[] = 			'<th>Module</th>';
			$html[] = 			'<th>Loading time</th>';
			$html[] = 			'<th>Memory usage</th>';
			$html[] = 		'</tr>';
			$html[] = 	'</thead>';
			
			
			$html[] = 	'<tbody>';
			
			$total_time = 0;
			$total_size = 0;
			foreach ($this->cfg['modules'] as $key => $module ) {

				$html[] = 		'<tr>';
				$html[] = 			'<td>' . ( $key ) . '</td>';
				$html[] = 			'<td>' . ( number_format($module['loaded_in'], 4) ) . '(seconds)</td>';
				$html[] = 			'<td>' . (  $this->formatBytes($module['memory_usage']) ) . '</td>';
				$html[] = 		'</tr>';
			
				$total_time = $total_time + $module['loaded_in']; 
				$total_size = $total_size + $module['memory_usage']; 
			}

			$html[] = 		'<tr>';
			$html[] = 			'<td colspan="3">';
			$html[] = 				'Total time: <strong>' . ( $total_time ) . '(seconds)</strong><br />';			
			$html[] = 				'Total Memory: <strong>' . ( $this->formatBytes($total_size) ) . '</strong><br />';			
			$html[] = 			'</td>';
			$html[] = 		'</tr>';

			$html[] = 	'</tbody>';
			$html[] = '</table>';
			
			echo implode("\n", $html );
		}

		public function check_secure_connection ()
		{

			$secure_connection = false;
			if(isset($_SERVER['HTTPS']))
			{
				if ($_SERVER["HTTPS"] == "on")
				{
					$secure_connection = true;
				}
			}
			return $secure_connection;
		}


		/*
			helper function, image_resize
			// use timthumb
		*/
		public function image_resize ($src='', $w=100, $h=100, $zc=2)
		{
			// in no image source send, return no image
			if( trim($src) == "" ){
				$src = $this->cfg['paths']['freamwork_dir_url'] . '/images/no-product-img.jpg';
			}

			if( is_file($this->cfg['paths']['plugin_dir_path'] . 'timthumb.php') ) {
				return $this->cfg['paths']['plugin_dir_url'] . 'timthumb.php?src=' . $src . '&w=' . $w . '&h=' . $h . '&zc=' . $zc;
			}
		}

		/*
			helper function, upload_file
		*/
		public function upload_file ()
		{
			$slider_options = '';
			 // Acts as the name
            $clickedID = $_POST['clickedID'];
            // Upload
            if ($_POST['type'] == 'upload') {
                $override['action'] = 'wp_handle_upload';
                $override['test_form'] = false;
				$filename = $_FILES [$clickedID];

                $uploaded_file = wp_handle_upload($filename, $override);
                if (!empty($uploaded_file['error'])) {
                    echo json_encode(array("error" => "Upload Error: " . $uploaded_file['error']));
                } else {
                    echo json_encode(array(
							"url" => $uploaded_file['url'],
							"thumb" => ($this->image_resize( $uploaded_file['url'], $_POST['thumb_w'], $_POST['thumb_h'], $_POST['thumb_zc'] ))
						)
					);
                } // Is the Response
            }else{
				echo json_encode(array("error" => "Invalid action send" ));
			}

            die();
		}
		
		public function wp_media_upload_image()
		{
			$image = wp_get_attachment_image_src( (int)$_REQUEST['att_id'], 'thumbnail' );
			die(json_encode(array(
				'status' 	=> 'valid',
				'thumb'		=> $image[0]
			)));
		}

		/**
		 * Getter function, shop config
		 *
		 * @params $returnType
		 * @return array
		 */
		public function getShopConfig( $section='', $key='', $returnAs='echo' )
		{
			if( count($this->app_settings) == 0 ){
				$this->app_settings = $this->getAllSettings();
			}

			if( isset($this->app_settings[$this->alias . "_" . $section])) {
				if( isset($this->app_settings[$this->alias . "_" . $section][$key])) {
					if( $returnAs == 'echo' ) echo $this->app_settings[$this->alias . "_" . $section][$key];

					if( $returnAs == 'return' ) return $this->app_settings[$this->alias . "_" . $section][$key];
				}
			}
		}

		public function download_image( $file_url='', $pid=0, $action='insert', $product_title='', $step=0 )
		{
			if(trim($file_url) != ""){
				$amazon_settings = $this->getAllSettings('array', 'settings');
				
				if( $amazon_settings["rename_image"] == 'product_title' ){
					$image_name = sanitize_file_name($product_title);
					$image_name = preg_replace("/[^a-zA-Z0-9-]/", "", $image_name);
					$image_name = substr($image_name, 0, 200);
				}else{
					$image_name = uniqid();
				}
				
				// Find Upload dir path
				$uploads = wp_upload_dir();
				$uploads_path = $uploads['path'] . '';
				$uploads_url = $uploads['url'];

				$fileExt = end(explode(".", $file_url));
				$filename = $image_name . "-" . ( $step ) . "." . $fileExt;
				
				// Save image in uploads folder
				$response = wp_remote_get( $file_url );
  
				if( !is_wp_error( $response ) ){
					$image = $response['body'];
					
					$image_url = $uploads_url . '/' . $filename; // URL of the image on the disk
					$image_path = $uploads_path . '/' . $filename; // Path of the image on the disk
					$ii = 0;
					while ( $this->verifyFileExists($image_path) ) {
						$filename = $image_name . "-" . ( $step );
						$filename .= '-'.$ii;
						$filename .= "." . $fileExt;
						
						$image_url = $uploads_url . '/' . $filename; // URL of the image on the disk
						$image_path = $uploads_path . '/' . $filename; // Path of the image on the disk
						$ii++;
					}

					// verify image hash
					$hash = md5($image);
					$hashFound = $this->verifyProdImageHash( $hash );
					if ( !empty($hashFound) && isset($hashFound->media_id) ) { // image hash not found!
					
						$orig_attach_id = $hashFound->media_id;
						$image_path = $hashFound->image_path;
						
						return array(
							'attach_id' 		=> $orig_attach_id, // $attach_id,
							'image_path' 		=> $image_path,
							'hash'				=> $hash
						);
					}
					//write image if the wp method fails
					$has_wrote = $this->wp_filesystem->put_contents(
						$uploads_path . '/' . $filename, $image, FS_CHMOD_FILE
					);
					
					if( !$has_wrote ){
						file_put_contents( $uploads_path . '/' . $filename, $image );
					}

					// Add image in the media library - Step 3
					$wp_filetype = wp_check_filetype( basename( $image_path ), null );
					$attachment = array(
						// 'guid' 			=> $image_url,
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $image_path ) ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);
 
					$attach_id = wp_insert_attachment( $attachment, $image_path, $pid  ); 
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $image_path );
					wp_update_attachment_metadata( $attach_id, $attach_data );
  
					return array(
						'attach_id' 		=> $attach_id,
						'image_path' 		=> $image_path,
						'hash'				=> $hash
					);
				}
				else{
					return array(
						'status' 	=> 'invalid',
						'msg' 		=> htmlspecialchars( implode(';', $response->get_error_messages()) )
					);
				}
			}
		}
		
		public function verifyProdImageHash( $hash ) {
			require( $this->cfg['paths']['plugin_dir_path'] . '/modules/assets_download/init.php' );
			$age_restrictionAssetDownloadCron = new age_restrictionAssetDownload();
			
			return $age_restrictionAssetDownloadCron->verifyProdImageHash( $hash );
		}

		/**
	    * HTML escape given string
	    *
	    * @param string $text
	    * @return string
	    */
	    public function escape($text)
	    {
	        $text = (string) $text;
	        if ('' === $text) return '';

	        $result = @htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	        if (empty($result)) {
	            $result = @htmlspecialchars(utf8_encode($text), ENT_COMPAT, 'UTF-8');
	        }

	        return $result;
	    }
		
		public function multi_implode($array, $glue) 
		{
		    $ret = '';
		
		    foreach ($array as $item) {
		        if (is_array($item)) {
		            $ret .= $this->multi_implode($item, $glue) . $glue;
		        } else {
		            $ret .= $item . $glue;
		        }
		    }
		
		    $ret = substr($ret, 0, 0-strlen($glue));
		
		    return $ret;
		}

		/**
		 * Usefull
		 */
		
		//format right (for db insertion) php range function!
		public function doRange( $arr ) {
			$newarr = array();
			if ( is_array($arr) && count($arr)>0 ) {
				foreach ($arr as $k => $v) {
					$newarr[ $v ] = $v;
				}
			}
			return $newarr;
		}
		
		//verify if file exists!
		public function verifyFileExists($file, $type='file') {
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
		
		public function formatBytes($bytes, $precision = 2) {
			$units = array('B', 'KB', 'MB', 'GB', 'TB');

			$bytes = max($bytes, 0);
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
			$pow = min($pow, count($units) - 1);

			// Uncomment one of the following alternatives
			// $bytes /= pow(1024, $pow);
			$bytes /= (1 << (10 * $pow));

			return round($bytes, $precision) . ' ' . $units[$pow];
		}
		
		public function prepareForInList($v) {
			return "'".$v."'";
		}
		
		public function db_custom_insert($table, $fields, $ignore=false, $wp_way=false) {
			if ( $wp_way && !$ignore ) {
				$this->db->insert( 
					$table, 
					$fields['values'], 
					$fields['format']
				);
			} else {
			
				$formatVals = implode(', ', array_map(array('age_restriction', 'prepareForInList'), $fields['format']));
				$theVals = array();
				foreach ( $fields['values'] as $k => $v ) $theVals[] = $k;

				$q = "INSERT " . ($ignore ? "IGNORE" : "") . " INTO $table (" . implode(', ', $theVals) . ") VALUES (" . $formatVals . ");";
				foreach ($fields['values'] as $kk => $vv)
					$fields['values']["$kk"] = esc_sql($vv);
  
				$q = vsprintf($q, $fields['values']);
				$r = $this->db->query( $q );
			}
		}
		
		public function verify_product_isamazon($prod_id) {
			// verify we are in woocommerce product
			if( function_exists('get_product') ){
				$product = get_product( $prod_id );
				if ( isset($product->id) && (int) $product->id > 0 ) {
					
					// verify is amazon product!
					$asin = get_post_meta($prod_id, '_amzASIN', true);
					if ( $asin!==false && strlen($asin) > 0 ) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * 
		 */
		/**
		 * setup module messages
		 */
		public function print_module_error( $module=array(), $error_number, $title="" )
		{
			$html = array();
			if( count($module) == 0 ) return true;
  
			$html[] = '<div class="age_restriction-grid_4 age_restriction-error-using-module">';
			$html[] = 	'<div class="age_restriction-panel">';
			$html[] = 		'<div class="age_restriction-panel-header">';
			$html[] = 			'<span class="age_restriction-panel-title">';
			$html[] = 				__( $title, 'age-restriction' );
			$html[] = 			'</span>';
			$html[] = 		'</div>';
			$html[] = 		'<div class="age_restriction-panel-content">';
			
			$error_msg = isset($module[$module['alias']]['errors'][$error_number]) ? $module[$module['alias']]['errors'][$error_number] : '';
			
			$html[] = 			'<div class="age_restriction-error-details">' . ( $error_msg ) . '</div>';
			$html[] = 		'</div>';
			$html[] = 	'</div>';
			$html[] = '</div>';
			
			return implode("\n", $html);
		}
		
		public function convert_to_button( $button_params=array() )
		{
			$button = array();
			$button[] = '<a';
			if(isset($button_params['url'])) 
				$button[] = ' href="' . ( $button_params['url'] ) . '"';
			
			if(isset($button_params['target'])) 
				$button[] = ' target="' . ( $button_params['target'] ) . '"';
			
			$button[] = ' class="age_restriction-button';
			
			if(isset($button_params['color'])) 
				$button[] = ' ' . ( $button_params['color'] ) . '';
				
			$button[] = '"';
			$button[] = '>';
			
			$button[] =  $button_params['title'];
		
			$button[] = '</a>';
			
			return implode("", $button);
		}

		public function load_terms($taxonomy){
    		global $wpdb;
			
			$query = "SELECT DISTINCT t.name FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} as tt ON tt.term_id = t.term_id WHERE 1=1 AND tt.taxonomy = '".esc_sql($taxonomy)."'";
    		$result =  $wpdb->get_results($query , OBJECT);
    		return $result;                 
		}

		/**
		 * age_restriction related
		 */
		public function get_client_ip()
		{
            $ipaddress = '';
			  
            if ($_SERVER['REMOTE_ADDR'])
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            else if ($_SERVER['HTTP_CLIENT_IP'])
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            else if ($_SERVER['HTTP_X_FORWARDED'])
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            else if ($_SERVER['HTTP_FORWARDED_FOR'])
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            else if( $_SERVER['HTTP_FORWARDED'])
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            else if ($_SERVER['HTTP_X_FORWARDED_FOR'])
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			 
			// debug - set real IP on local network
			//$ipaddress = $_SERVER['SERVER_ADDR'];

            return $ipaddress;
        }
		
		public function get_current_page_url() {
			$url = (!empty($_SERVER['HTTPS']))
				?
				"https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
				:
				"http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
			;
			return $url;
		}
		
		public function get_client_country() {
			$get_user_location = wp_remote_get( 'http://api.hostip.info/get_json.php?ip=' . $this->get_client_ip() );
			
			if ( is_wp_error( $get_user_location ) ) { // If there's error
				$body = false;
				// $err = htmlspecialchars( implode(';', $get_user_location->get_error_messages()) );
			} else {
				$body = wp_remote_retrieve_body( $get_user_location );
			}
			if (is_null($body) || !$body || trim($body)=='') { //status is Invalid!
				return false;
			} else {
				$body = json_decode($body);  
				$user_country = array(
					'name'		=> $body->country_name,
					'code'		=> $body->country_code
				);
			}

			return $user_country;
		}
		
		public function get_client_country2($banner_meta) {
			$get_user_location = wp_remote_get( 'http://api.hostip.info/country.php?ip=' . $this->get_client_ip() );
			if( isset($get_user_location->errors) ) {
				$main_aff_site = $this->banner_main_aff_site($banner_meta);
				$user_country = $this->banner_amzForUser( $banner_meta, strtoupper(str_replace(".", '', $main_aff_site)) );
			}else{
				$user_country = $this->banner_amzForUser( $banner_meta, $get_user_location['body'] );
			}
			return $user_country;
		}
		
		public function ip2number( $ip ) {
            $long = ip2long($ip);
            if ($long == -1 || $long === false) {
                return false;
            }
            return sprintf("%u", $long);
        }
		
		public function binary_search($key, array $list, $compare_func) 
		{
            $low = 0; 
            $high = count($list) - 1;
     
            while ($low <= $high) {
                $mid = (int) (($high - $low) / 2) + $low; // could use php ceil function
                $cmp = call_user_func($compare_func, $list[$mid], $key);
     
                if ($cmp < 0) {
                    $low = $mid + 1;
                } else if ($cmp > 0) {
                    $high = $mid - 1;
                } else {
                    return $mid;
                }
            }
            return -($low - 1);
        }
		
        public function binary_search_cmp($a, $b) 
        {
            return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
        }
		
		public function get_country_perip_external( $return_field='country' )
		{
            $ip = $this->get_client_ip();
			$paths = array(
				'api.hostip.info'			=> 'http://api.hostip.info/country.php?ip={ipaddress}',
				'www.geoplugin.net'			=> 'http://www.geoplugin.net/json.gp?ip={ipaddress}',
				'ipinfo.io'					=> 'http://ipinfo.io/{ipaddress}/geo',
			);
			  
			$service_used = 'www.geoplugin.net';
			if ( isset($this->cfg['config_settings']['services_used_forip']) && trim($this->cfg['config_settings']['services_used_forip']) != '' ) {
				$service_used = $this->cfg['config_settings']['services_used_forip'];
			}
			  
            $country = '';
            if ( $service_used == 'local_csv' ) { // local csv file with ip lists
                
                // read csv hash (string with ip from list)
                $csv_hash = $this->wp_filesystem->get_contents( $this->cfg['paths']['plugin_dir_path'] . 'assets/GeoIPCountryWhois-hash.csv' );
				$csv_hash = explode(',', $csv_hash);
				
                // read csv full (ip from, ip to, country)
                $csv_full = $this->wp_filesystem->get_contents( $this->cfg['paths']['plugin_dir_path'] . 'assets/GeoIPCountryWhois-full.csv' );
                $csv_full = explode(PHP_EOL, $csv_full);
                
                $ip2number = $this->ip2number( $ip );
                
                $ipHashIndex = $this->binary_search($ip2number, $csv_hash, array($this, 'binary_search_cmp'));
                if ( $ipHashIndex < 0 ) { // verify if is between (ip_from, ip_to) of csv row
                    $ipHashIndex = abs( $ipHashIndex );
                    $ipFullRow = $csv_full["$ipHashIndex"];
                    $csv_row = explode(',', $ipFullRow); 
                    if ( $ip2number >= $csv_row[0] && $ip2number <= $csv_row[1] ) {
                        $country = $csv_row[2];
                    }
                } else { // exact match in the list as ip_from of csv row
                    $ipFullRow = $csv_full["$ipHashIndex"];
                    $country = @end( @explode(',', $ipFullRow) );
                }
				 
                if (empty($country)) {
                    $country = 'NOT-FOUND';
                }
                $country = strtoupper( $country );
                
            } else { // external service
            
    			$service_url = $paths[$service_used];
    			$service_url = str_replace('{ipaddress}', $this->get_client_ip(), $service_url);
				  
    			$get_user_location = wp_remote_get( $service_url );
				
				// check if wp_remote_get fails
				if( !isset($get_user_location->errors['http_request_failed']) && $get_user_location['response']['code'] == 403 ) {
					$get_user_location = file_get_contents( $service_url );
				}
				  
    			if ( isset($get_user_location->errors) ) {
    				$country = 'ERROR';
    			} else {
    				$country = isset($get_user_location['body']) ? $get_user_location['body'] : $get_user_location;
					 
    				switch ($service_used) {
    					case 'api.hostip.info':  
							if( $country == 'XX' ) {
								$country = array(
									'name' => '',
									'code' => ''
								);
							}else{
								$country = array(
									'name' => $country,
									'code' => $country
								); 
							}
    						break;
    						
    					case 'www.geoplugin.net':
    						$country = json_decode($country);
							  
							if( isset($country) ) {
								$country = array(
									'name' => strtoupper( $country->geoplugin_countryName ),
									'code' => strtoupper( $country->geoplugin_countryCode )
								);
							}
    						break;
    						
    					case 'ipinfo.io':
    						$country = (array) json_decode($country);
							$country = array(
								'name' => strtoupper( $country['country'] ),
								'code' => strtoupper( $country['country'] )
							);  
    						break;
    						
    					default:
    						break;
    				}
    			}
            }
  
			return $country;
		}

		// add_action( 'init', array($this, 'cookie_set') );
		public function cookie_set( $cookie_arr = array() ) {
			extract($cookie_arr);
			if ( !isset($path) )
				$path = '/';
			if ( !isset($domain) )
				$domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        	setcookie($name, $value, $expire_sec, $path, $domain);
		}
		public function cookie_del( $cookie_arr = array() ) {
			extract($cookie_arr);
			if ( !isset($path) )
				$path = '/';
			if ( !isset($domain) )
				$domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
			setcookie($name, null, strtotime('-1 day'), $path, $domain);
		}
		
		public function __banner_get_json($json=null) {  
			// obj, post_id
			$obj = isset($json['obj']) ? $json['obj'] : null;
			if ( empty($obj) || is_null($obj) ) {
				$banner_content = get_post_field('post_content', $json['post_id']);
				$banner_content = unserialize($banner_content);
				$banner_content = json_decode($banner_content);

				$obj = $banner_content;
			}
			return $obj;
		}
		public function __banner_json_valid_elem($el, $elsub, $selected, $parsed) {
			$validEl1 = false;
			if ($selected==='all') {
				$validEl1 = true;
			} else if ($selected===true) {
				$validEl1 = (bool) $el->status;
			} else if ($selected===false) {
				$validEl1 = (bool) !$el->status;
			}
			
			$validEl2 = false;
			if ($parsed==='all') {
				$validEl2 = true;
			} else if ($parsed===true) {
				$validEl2 = (bool) ( isset($elsub) && !empty($elsub) );
			} else if ($parsed===false) {
				$validEl2 = (bool) !( isset($elsub) && !empty($elsub) );
			}
			
			$validEl = (bool) ($validEl1 && $validEl2);

			return $validEl;
		}
		
		public function get_banner_countries($json=null, $selected=true, $parsed='all') {
			$ret = new stdClass();

			if ( is_null($json) || empty($json) || !is_array($json) ) return $ret;
			// obj, post_id
			$obj = $this->__banner_get_json($json);
  			if ( empty($obj) ) return $ret;
			
			if ( isset($obj->countries) && !empty($obj->countries) ) {
				$ret->result = array();

				foreach ($obj->countries as $i => $ival) { // level 1 - countries

					$validEl = $this->__banner_json_valid_elem(
						$ival, $ival->categories, $selected, $parsed
					);
					if ( $validEl )
						array_push($ret->result, $ival->key);
				}
			} // end if
			else { // not parsed yet!

				$amazonCountries = $this->amzHelper->getAmazonCountries();
				if ( !empty($amazonCountries) && is_array($amazonCountries) )
					$ret->result = (array) array_keys($amazonCountries);
			}

			return $ret;
		}
		
		public function get_banner_info($post_id, $post_content=null) {

			$ret = array(
				'banner_type'		=> '',
				'banner_priority'	=> '',
				'new_banner'		=> ''
			);

			$banners_meta = get_post_meta( $post_id, '_age_restriction_meta', true );
			$banners_meta = isset($banners_meta['banner']) ? $banners_meta['banner'] : array();

			$meta_amzcfg = get_post_meta( $post_id, '_age_restriction_amzcfg', true );
			$meta_amzcfg = isset($meta_amzcfg['banner']) ? $meta_amzcfg['banner'] : array();
				
			$new_banner = array(
				'ID'			=> $post_id,
				'meta' 			=> $banners_meta,
				'amzcfg'		=> $meta_amzcfg,
			);
			
			return $new_banner;
		}
		
		public function get_client_utils() {
			$utils = array();

			$client_ip = $this->get_client_ip();
			$current_url = $this->get_current_page_url();
			$current_date = strtotime( date('Y-m-d H:i') );
			$get_current_country = $this->get_country_perip_external();
			 
			if ( !empty($get_current_country) && isset($get_current_country) && $get_current_country != '' && $get_current_country != 'NOT-FOUND' ) {
				if( is_array($get_current_country) ) {
					$current_country = $get_current_country['name'];
					$current_country_code = $get_current_country['code'];
				}else{
					$current_country = $get_current_country;
					$current_country_code = $get_current_country;
				}
			} else {
				$current_country = '';
				$current_country_code = '';
			}
			
			$utils = compact('client_ip', 'current_url', 'current_date', 'current_country', 'current_country_code');
			
  			// mobile
			require_once( $this->cfg['paths']["scripts_dir_path"] . '/mobile-detect/Mobile_Detect.php' );
			$mobileDetect = new ageRestriction_Mobile_Detect();
			
			$utils['isMobile'] = $mobileDetect->isMobile();
			$utils['device_type'] = $mobileDetect->type();
			
			return $utils;
		}

		public function getCountriesList( $use_key='code', $format='' )
		{
			$csv = $ret = array();
			
			// try to read the plugin_root/assets/GeoLite2-Country-Locations.csv file
			// check if file exists
			if( !is_file( $this->cfg['paths']['plugin_dir_path'] . 'assets/GeoLite2-Country-Locations-en.csv' ) ){
				die( 'Unable to load file: ' . $this->cfg['paths']['plugin_dir_path'] . 'assets/GeoLite2-Country-Locations-en.csv' );
			}
			
        	$csv_file_content = file_get_contents( $this->cfg['paths']['plugin_dir_path'] . 'assets/GeoLite2-Country-Locations-en.csv' );
			
			if( trim($csv_file_content) != "" ){
				$rows = explode("\n", $csv_file_content);
  
				if( count($rows) > 0 ){
					$rows = array_slice($rows, 1);
					foreach ($rows as $key => $value) {
						$csv[] = explode(",", trim($value));
					}
				}
			}
			
			if( count($csv) > 0 ){
				foreach ($csv as $key => $value) {
					$__key = $value[4];
					if ( $use_key == 'name' ) $__key = $value[4];
					$__val = $value[5];
					if ( $format == 'upper' ) $__val = strtoupper($__val);
					$ret[$__key] = trim($__val, '""');
				}
			} 
			asort($ret);
			
			return $ret;  
		}


        public function get_stats_columns( $option='age_restriction_list_table_cols', $force_add_cols=array() ) {
            $table_cols_excluded = array('id', 'banner_id', 'country_code', 'device_type_full');
            $table_cols_sortorder = array('id', 'banner_id', 'title', 'action', 'report_day', 'device_type', 'verify_source', 'country_code', 'country', 'ip', 'first_name', 'last_name', 'gender', 'email', 'birthday', 'age');

            $cache_name = !empty($option) ? $option : 'age_restriction_list_table_cols';
            $db_name = $this->db->prefix . 'age_restriction_stats';

            // try to get from cache 
            $table_cols = get_transient( $cache_name ); 
            if ( $table_cols == false || !is_array($table_cols) || empty($table_cols) ) {
                $table_cols = array();
                $table_cols[] = 'title';
                foreach ( $this->db->get_col( "DESC " . $db_name, 0 ) as $column_name ) {
                    $table_cols[] = $column_name;
                }
                $table_cols = array_diff($table_cols, $table_cols_excluded);
                $table_cols = array_merge($table_cols, $force_add_cols);
                $table_cols = array_merge( array_intersect($table_cols_sortorder, $table_cols), array_diff($table_cols, $table_cols_sortorder) );
                set_transient( $cache_name, $table_cols, 3600 ); // timeout: 1 hour
            }
            return (array) $table_cols;
        }

        public function get_banner_posts( $option='age_restriction_list_table_posts' ) {
            $cache_name = !empty($option) ? $option : 'age_restriction_list_table_posts';
            
            // try to get from cache 
            $table_cols = get_transient( $cache_name ); 
            if ( $table_cols == false || !is_array($table_cols) || empty($table_cols) ) {
                $args_banners_list = array(
                    'post_type'             => 'age_restriction',
                    'post_status'           => 'publish',
                    'posts_per_page'        => -1,
                    'orderby'               => 'post_date',
                    'order'                 => 'ASC',
                    'suppress_filters'      => true
                );
                $args_banners_list = array_filter($args_banners_list);
                $banners_list = get_posts( $args_banners_list );
                $table_cols = array();
                foreach ((array) $banners_list as $key => $val) {
                    $bid = isset($val->ID) ? $val->ID : 0;
                    $table_cols["$bid"] = $val;
                }
                set_transient( $cache_name, $table_cols, 600 ); // timeout: 10 min
            }
            return (array) $table_cols;
        }
	}
}