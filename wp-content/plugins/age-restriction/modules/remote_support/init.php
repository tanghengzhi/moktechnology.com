<?php
/*
* Define class age_restrictionRemoteSupport
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('age_restrictionRemoteSupport') != true) {
    class age_restrictionRemoteSupport
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
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/remote_support/';
			$this->module = $this->the_plugin->cfg['modules']['remote_support'];

			if (is_admin()) {
	            add_action('admin_menu', array( &$this, 'adminMenu' ));
			}

			// load the ajax helper
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/remote_support/ajax.php' );
			new age_restrictionRemoteSupportAjax( $this->the_plugin );
        }

		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionRemoteSupport Singleton instance
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
    			$this->the_plugin->alias . " " . __('AA-Team Remote Support', 'age-restriction'),
	            __('Remote Support', 'age-restriction'),
	            'manage_options',
	            $this->the_plugin->alias . "_remote_support",
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
			
			$remote_access = get_option( 'age_restriction_remote_access', true );
			$login_token = get_option( 'age_restriction_support_login_token', true );
?>
		<script type="text/javascript" src="<?php echo $this->module_folder;?>app.class.js" ></script>
		<div id="age_restriction-wrapper" class="fluid wrapper-age_restriction">
			
			<?php
			// show the top menu
			age_restrictionAdminMenu::getInstance()->make_active('general|remote_support')->show_menu();
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
					if( isset($this->module['remote_support']['in_dashboard']['icon']) ){
						echo '<img src="' . ( $this->module_folder . $this->module['remote_support']['in_dashboard']['icon'] ) . '" class="age_restriction-headline-icon">';
					}
					?>
					<?php echo $this->module['remote_support']['menu']['title'];?>
					<span class="age_restriction-section-info"><?php echo $this->module['remote_support']['description'];?></span>
					<?php
					$has_help = isset($this->module['remote_support']['help']) ? true : false;
					if( $has_help === true ){
						
						$help_type = isset($this->module['remote_support']['help']['type']) && $this->module['remote_support']['help']['type'] ? 'remote' : 'local';
						if( $help_type == 'remote' ){
							echo '<a href="#load_docs" class="age_restriction-show-docs" data-helptype="' . ( $help_type ) . '" data-url="' . ( $this->module['remote_support']['help']['url'] ) . '">HELP</a>';
						} 
					} 
					?>
				</h1>

				<!-- Container -->
				<div class="age_restriction-container clearfix">

					<!-- Main Content Wrapper -->
					<div id="age_restriction-content-wrap" style="margin-top: 15px;">

						<!-- Content Area -->
						<div id="age_restriction-content-area">
							
							<div class="age_restriction-grid_4" id="age_restriction-boxid-access">
							    <div class="age_restriction-panel">
							        <div class="age_restriction-panel-header">
							            <span class="age_restriction-panel-title">
											Remote Support Details
										</span>
							        </div>
							        <div class="age_restriction-panel-content">
							            <form id="age_restriction_access_details" class="age_restriction-form">
							                <div class="age_restriction-form-row">
							                    <label for="protocol">Create WP Credential</label>
							                    <div class="age_restriction-form-item large">
							                        <span class="formNote">This will automatically create a wordpress administrator account for AA-Team support team</span>
							                        
							                        <?php 
							                        $selected = 'yes';
													if( 
														!isset($remote_access['age_restriction-create_wp_credential']) ||
														$remote_access['age_restriction-create_wp_credential'] == 'no'
													){
														$selected = 'no';
													}
							                        ?>
							                        <select id="age_restriction-create_wp_credential" name="age_restriction-create_wp_credential" style="width:80px;">
							                            <option value="yes" <?php echo ($selected == 'yes' ? 'selected="selected"' : '');?>>Yes</option>
							                            <option value="no" <?php echo ($selected == 'no' ? 'selected="selected"' : '');?>>NO</option>
							                        </select>
							                        
							                        <div class="age_restriction-wp-credential" <?php echo ( isset($remote_access['age_restriction-create_wp_credential']) && trim($remote_access['age_restriction-create_wp_credential']) == 'yes' ? 'style="display:block"' : 'style="display:none"' );?>>
							                        	<table class="age_restriction-table" style="border-collapse: collapse;">
							                        		<tr>
							                        			<td width="160">Admin username:</td>
							                        			<td>aateam_support</td>
							                        		</tr>
							                        		<tr>
							                        			<td>Admin password:</td>
							                        			<td>
								                        			<?php  
									                        			$admin_password = isset($remote_access['age_restriction-password']) ? $remote_access['age_restriction-password'] : $this->generateRandomString(10);
								                        			?>
								                        			<input type="text" name="age_restriction-password" id="age_restriction-password" value="<?php echo $admin_password;?>" />
							                        			</td>
							                        		</tr>
							                        	</table>
							                        	<div class="age_restriction-message age_restriction-info"><i>(this details will be send automatically on your open ticket)</i></div>
							                        </div>
							                    </div>
							                </div>
							                <div class="age_restriction-form-row">
							                    <label for="onsite_cart">Allow file remote access</label>
							                    <div class="age_restriction-form-item large">
							                        <span class="formNote">This will automatically give access for AA-Team support team to your chosen server path</span>
							                        
							                        <?php 
							                        $selected = 'yes';
													if( 
														!isset($remote_access['age_restriction-allow_file_remote']) ||
														$remote_access['age_restriction-allow_file_remote'] == 'no'
													){
														$selected = 'no';
													}
							                        ?>
							                        <select id="age_restriction-allow_file_remote" name="age_restriction-allow_file_remote" style="width:80px;">
							                            <option value="yes" <?php echo ($selected == 'yes' ? 'selected="selected"' : '');?>>Yes</option>
							                            <option value="no" <?php echo ($selected == 'no' ? 'selected="selected"' : '');?>>NO</option>
							                        </select>
							                        
							                        <div class="age_restriction-file-access-credential" <?php echo ( isset($remote_access['age_restriction-allow_file_remote']) && trim($remote_access['age_restriction-allow_file_remote']) == 'yes' ? 'style="display:block"' : 'style="display:none"' );?>>
							                        	<table class="age_restriction-table" style="border-collapse: collapse;">
							                        		<tr>
							                        			<td width="120">Access key:</td>
							                        			<td>
							                        				<?php 
									                        			$access_key = isset($remote_access['age_restriction-key']) ? $remote_access['age_restriction-key'] : md5( $this->generateRandomString(12) );
								                        			?>
							                        				<input type="text" name="age_restriction-key" id="age_restriction-key" value="<?php echo $access_key;?>" />
							                        			</td>
							                        		</tr>
							                        		<tr>
							                        			<td width="120">Access path:</td>
							                        			<td>
							                        				<input type="text" name="age_restriction-access_path" id="age_restriction-access_path" value="<?php echo isset($remote_access['age_restriction-access_path']) ? $remote_access['age_restriction-access_path'] : ABSPATH;?>" />
							                        			</td>
							                        		</tr>
							                        	</table>
							                        	<div class="age_restriction-message age_restriction-info"><i>(this details will be send automatically on your open ticket)</i> </div>
							                        </div>
							                    </div>
							                </div>
							                <div style="display:none;" id="age_restriction-status-box" class="age_restriction-message"></div>
							                <div class="age_restriction-button-row">
							                    <input type="submit" class="age_restriction-button blue" value="Save Remote Access" style="float: left;" />
							                </div>
							            </form>
							        </div>
							    </div>
							</div>
							
							<div class="age_restriction-grid_4" id="age_restriction-boxid-logininfo">
	                        	<div class="age_restriction-panel">
									<div class="age_restriction-panel-content">
										<div class="age_restriction-message age_restriction-info">
											
											<?php
											if( !isset($login_token) || trim($login_token) == "" ){
											?>
												In order to contact AA-Team support team you need to login into support.aa-team.com
											<?php 
											}
											
											else{
											?>
												Test your token is still valid on AA-Team support website ...
												<script>
													age_restrictionRemoteSupport.checkAuth( '<?php echo $login_token;?>' );
												</script>
											<?php
											}
											?>
										</div>
				            		</div>
								</div>
							</div>
							
							<div class="age_restriction-grid_2" id="age_restriction-boxid-login" style="display:none">
	                        	<div class="age_restriction-panel">
	                        		<div class="age_restriction-panel-header">
										<span class="age_restriction-panel-title">
											Login
										</span>
									</div>
									<div class="age_restriction-panel-content">
										<form class="age_restriction-form" id="age_restriction-form-login">
											<div class="age_restriction-form-row">
												<label class="age_restriction-form-label" for="email">Email <span class="required">*</span></label>
												<div class="age_restriction-form-item large">
													<input type="text" id="age_restriction-email" name="age_restriction-email" class="span12">
												</div>
											</div>
											<div class="age_restriction-form-row">
												<label class="age_restriction-form-label" for="password">Password <span class="required">*</span></label>
												<div class="age_restriction-form-item large">
													<input type="password" id="age_restriction-password" name="age_restriction-password" class="span12">
												</div>
											</div>
											
											<div class="age_restriction-form-row" style="height: 79px;">
												<input type="checkbox" id="age_restriction-remember" name="age_restriction-remember" style="float: left; position: relative; bottom: -12px;">
												<label for="age_restriction-remember" class="age_restriction-form-label" style="width: 120px;">&nbsp;Remember me</label>
											</div>
											
											<div class="age_restriction-message age_restriction-error" style="display:none;"></div>
	
											<div class="age_restriction-button-row">
												<input type="submit" class="age_restriction-button blue" value="Login" style="float: left;" />
											</div>
										</form>
				            		</div>
								</div>
							</div>
							
							<div class="age_restriction-grid_2" id="age_restriction-boxid-register" style="display:none">
	                        	<div class="age_restriction-panel">
	                        		<div class="age_restriction-panel-header">
										<span class="age_restriction-panel-title">
											Register
										</span>
									</div>
									<div class="age_restriction-panel-content">
										<form class="age_restriction-form" id="age_restriction-form-register">
											<div class="age_restriction-message error" style="display:none;"></div>
											
											<div class="age_restriction-form-row">
												<label class="age_restriction-form-label">Your name <span class="required">*</span></label>
												<div class="age_restriction-form-item large">
													<input type="text" id="age_restriction-name-register" name="age_restriction-name-register" class="span12">
												</div>
											</div>
											
											<div class="age_restriction-form-row">
												<label class="age_restriction-form-label">Your email <span class="required">*</span></label>
												<div class="age_restriction-form-item large">
													<input type="text" id="age_restriction-email-register" name="age_restriction-email-register" class="span12">
												</div>
											</div>
											
											<div class="age_restriction-form-row">
												<label class="age_restriction-form-label">Create a password <span class="required">*</span></label>
												<div class="age_restriction-form-item large">
													<input type="password" id="age_restriction-password-register" name="age_restriction-password-register" class="span6">
												</div>
											</div>
											
											<div class="age_restriction-button-row">
												<input type="submit" class="age_restriction-button blue" value="Register and login" style="float: left;" />
											</div>
										</form>
				            		</div>
								</div>
							</div>
							
							<div class="age_restriction-grid_4" style="display: none;" id="age_restriction-boxid-ticket">
							    <div class="age_restriction-panel">
							        <div class="age_restriction-panel-header">
							            <span class="age_restriction-panel-title">
											Details about problem:
										</span>
							        </div>
							        <div class="age_restriction-panel-content">
							            <form id="age_restriction_add_ticket" class="age_restriction-form">
							            	<input type="hidden" name="age_restriction-token" id="age_restriction-token" value="<?php echo $login_token;?>" />
							            	<input type="hidden" name="age_restriction-site_url" id="age_restriction-site_url" value="<?php echo admin_url();?>" />
							            	<input type="hidden" name="age_restriction-wp_username" id="age_restriction-wp_username" value="aateam_support" />
							            	<input type="hidden" name="age_restriction-wp_password" id="age_restriction-wp_password" value="" />
							            	
							            	<input type="hidden" name="age_restriction-access_key" id="age_restriction-access_key" value="" />
							            	<input type="hidden" name="age_restriction-access_url" id="age_restriction-access_url" value="<?php echo urlencode( str_replace("http://", "", $this->module_folder) . 'remote_tunnel.php');?>" />
							            	
							                
							                <div class="age_restriction-form-row">
												<label class="age_restriction-form-label">Ticket Subject<span class="required">*</span></label>
												<div class="age_restriction-form-item large">
													<input type="text" id="ticket_subject" name="ticket_subject" class="span6">
												</div>
											</div>
											
							                <div class="age_restriction-form-row">
						                        <?php
												wp_editor( 
													'', 
													'ticket_details', 
													array( 
														'media_buttons' => false,
														'textarea_rows' => 40,	
													) 
												); 
						                        ?>
							                </div>
							                <div style="display:none;" id="age_restriction-status-box" class="age_restriction-message age_restriction-success"></div>
							                <div class="age_restriction-button-row">
							                    <input type="submit" class="age_restriction-button green" value="Open ticket on support.aa-team.com" style="float: left;" />
							                </div>
							            </form>
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

		private function generateRandomString($length = 6) 
		{
		    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$%^*()';
		    $randomString = '';
		    for ($i = 0; $i < $length; $i++) {
		        $randomString .= $characters[rand(0, strlen($characters) - 1)];
		    }
		    return $randomString;
		}
    }
}

// Initialize the age_restrictionRemoteSupport class
$age_restrictionRemoteSupport = age_restrictionRemoteSupport::getInstance();
