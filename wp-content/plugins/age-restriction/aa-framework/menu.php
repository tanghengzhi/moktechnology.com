<?php
/**
 * AA-Team - http://www.aa-team.com
 * ===============================+
 *
 * @package		age_restrictionAdminMenu
 * @author		Andrei Dinca
 * @version		1.0
 */
! defined( 'ABSPATH' ) and exit;

if(class_exists('age_restrictionAdminMenu') != true) {
	class age_restrictionAdminMenu {
		
		/*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
		public $the_plugin = null;
		private $the_menu = array();
		private $current_menu = '';
		private $ln = '';
		
		private $menu_depedencies = array();

		static protected $_instance;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $age_restriction;
        	$this->the_plugin = $age_restriction;
			$this->ln = 'age-restriction';
			
			// update the menu tree
			$this->the_menu_tree();
			
			return $this;
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
		
		private function the_menu_tree()
		{
			if ( isset($this->the_plugin->cfg['modules']['depedencies']['folder_uri'])
				&& !empty($this->the_plugin->cfg['modules']['depedencies']['folder_uri']) ) {
				$this->menu_depedencies['depedencies'] = array( 
					'title' => __( 'Plugin depedencies', $this->ln ),
					'url' => admin_url("admin.php?page=age_restriction"),
					'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
					'menu_icon' => 'images/16_dashboard.png'
				);
				return true;
			}

			$this->the_menu['dashboard'] = array( 
				'title' => __( 'Dashboard', $this->ln ),
				'url' => admin_url("admin.php?page=age_restriction#!/dashboard"),
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'images/16_dashboard.png'
			);
			
			$this->the_menu['restrictions_manager'] = array( 
				'title' => __( 'Restrictions Statistics', $this->ln ),
				'url' => admin_url("admin.php?page=age_restriction#!/restrictions_manager"),
				'folder_uri' => $this->the_plugin->cfg['modules']['restrictions_manager']['folder_uri'],
				'menu_icon' => 'assets/16_banners_manager.png'
			);
			
			$this->the_menu['configuration'] = array( 
				'title' => __( 'Configuration', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'images/16_config.png',
				'submenu' => array(
					'settings' => array(
						'title' => __( 'Settings', $this->ln ),
						'url' => admin_url("admin.php?page=age_restriction#!/settings"),
						'folder_uri' => $this->the_plugin->cfg['modules']['settings']['folder_uri'],
						'menu_icon' => 'assets/16_amzconfig.png'
					)
				)
			);
			
			$this->the_menu['info'] = array( 
				'title' => __( 'Plugin Status', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'images/16_pluginstatus.png',
				'submenu' => array(
					'server_status' => array(
						'title' => __( 'Server Status', $this->ln ),
						'url' => admin_url("admin.php?page=age_restriction_server_status"),
						'folder_uri' => $this->the_plugin->cfg['modules']['server_status']['folder_uri'],
						'menu_icon' => 'assets/16_serversts.png'
					)
				)
			);
			
			$this->the_menu['general'] = array( 
				'title' => __( 'Plugin Settings', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'images/16_pluginsett.png',
				'submenu' => array(
					'modules_manager' => array(
						'title' => __( 'Modules Manager', $this->ln ),
						'url' => admin_url("admin.php?page=age_restriction#!/modules_manager"),
						'folder_uri' => $this->the_plugin->cfg['modules']['modules_manager']['folder_uri'],
						'menu_icon' => 'assets/16_modules.png'
					),
					
					'setup_backup' => array(
						'title' => __( 'Setup / Backup', $this->ln ),
						'url' => admin_url("admin.php?page=age_restriction#!/setup_backup"),
						'folder_uri' => $this->the_plugin->cfg['modules']['setup_backup']['folder_uri'],
						'menu_icon' => 'assets/16_setupbackup.png'
					),
					
					'remote_support' => array(
						'title' => __( 'Remote Support', $this->ln ),
						'url' => admin_url("admin.php?page=age_restriction_remote_support"),
						'folder_uri' => $this->the_plugin->cfg['modules']['remote_support']['folder_uri'],
						'menu_icon' => 'assets/16_remotesupport.png'
					)
				)
			);
		}
		
		public function show_menu( $pluginPage='' )
		{
			$plugin_data = $this->the_plugin->get_plugin_data();
			
			$html = array();
			
			$html[] = '<div id="age_restriction-header">';
			$html[] = 	'<div id="age_restriction-header-bottom">';
			$html[] = 		'<div id="age_restriction-topMenu">';
			$html[] = 			'<a href="http://codecanyon.net/user/AA-Team" target="_blank" class="age_restriction-product-logo">
									<img src="' . ( $this->the_plugin->cfg['paths']['plugin_dir_url'] ) . 'thumb.png" alt="">
									<h2>Premium Age Restriction Plugin for Wordpress</h2>
									<h3>' . ( $plugin_data['version'] ) . '</h3>
									
									<span class="age_restriction-rate-now"></span>
									<img src="' . ( $this->the_plugin->cfg['paths']['freamwork_dir_url'] ) . 'images/rate-now.png" class="age_restriction-rate-img">
									<img src="' . ( $this->the_plugin->cfg['paths']['freamwork_dir_url'] ) . 'images/star.gif" class="age_restriction-rate-gif">
									<strong>Don’t forget to rate us!</strong>
								</a>';
			$html[] = 			'<ul>';

			if ( $pluginPage == 'depedencies' ) {
				$menu = $this->menu_depedencies;
				$this->current_menu = array(
					0 => 'depedencies',
					1 => 'depedencies'
				);
			} else {
				$menu = $this->the_menu;
			}

								foreach ($menu as $key => $value) {
									$iconImg = '<img src="' . ( $value['folder_uri'] . $value['menu_icon'] ) . '" />';
									$html[] = '<li id="age_restriction-nav-' . ( $key ) . '" class="age_restriction-section-' . ( $key ) . ' ' . ( isset($this->current_menu[0]) && ( $key == $this->current_menu[0] ) ? 'active' : '' ) . '">';
									
									if( $value['url'] == "#!/" ){
										$value['url'] = 'javascript: void(0)';
									}
									$html[] = 	'<a href="' . ( $value['url'] ) . '">' . ( $iconImg ) . '' . ( $value['title'] ) . '</a>';
									if( isset($value['submenu']) ){
										$html[] = 	'<ul class="age_restriction-sub-menu">';
										foreach ($value['submenu'] as $kk2 => $vv2) {
											if( ($kk2 != 'synchronization_log') && isset($this->the_plugin->cfg['activate_modules']) && is_array($this->the_plugin->cfg['activate_modules']) && !in_array( $kk2, array_keys($this->the_plugin->cfg['activate_modules'])) ) continue;
		
											$iconImg = '<img src="' . ( $vv2['folder_uri'] . $vv2['menu_icon'] ) . '" />';
											$html[] = '<li class="age_restriction-section-' . ( $kk2 ) . '  ' . ( isset($this->current_menu[1]) && $kk2 == $this->current_menu[1] ? 'active' : '' ) . '" id="age_restriction-sub-nav-' . ( $kk2 ) . '">';
											$html[] = 	$iconImg;
											$html[] = 	'<a href="' . ( $vv2['url'] ) . '">' . ( $vv2['title'] ) . '</a>'; 
											
											if( isset($vv2['submenu']) ){
												$html[] = 	'<ul class="age_restriction-sub-sub-menu">';
												foreach ($vv2['submenu'] as $kk3 => $vv3) {
													$html[] = '<li id="age_restriction-sub-sub-nav-' . ( $kk3 ) . '">';
													$html[] = 	'<a href="' . ( $vv3['url'] ) . '">' . ( $vv3['title'] ) . '</a>';
													$html[] = '</li>';
												}
												$html[] = 	'</ul>';
											}
											$html[] = '</li>';
										}
										$html[] = 	'</ul>';
									}
									$html[] = '</li>';
								}
			$html[] = 			'</ul>';
			$html[] = 		'</div>';
			$html[] = 	'</div>';
			
			$html[] = 	'<a href="http://codecanyon.net/user/AA-Team/portfolio?ref=AA-Team" class="age_restriction-aateam-logo">AA-Team</a>';
			
			$html[] = '</div>';
			
			$html[] = '<script>
			(function($) {
				var age_restrictionMenu = $("#age_restriction-topMenu");
				
				age_restrictionMenu.on("click", "a", function(e){
					
					var that = $(this),
						href = that.attr("href");
					
					if( href == "javascript: void(0)" ){
						var current_open = age_restrictionMenu.find("li.active");
						current_open.find(".age_restriction-sub-menu").slideUp(350);
						current_open.removeClass("active");
						
						that.parent("li").eq(0).find(".age_restriction-sub-menu").slideDown(350, function(){
							that.parent("li").eq(0).addClass("active");
						});
					}
				});
			})(jQuery);
			
			</script>';
			
			echo implode("\n", $html);
		}

		public function make_active( $section='' )
		{
			$this->current_menu = explode("|", $section);
			return $this;
		}
	}
}