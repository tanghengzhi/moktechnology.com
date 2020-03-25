<?php
/**
 * Restrictions Manager file, return as json_encode
 * http://www.aa-team.com
 * ======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
global $age_restriction;
 echo json_encode(
	array(
		'restrictions_manager' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 11,
				'title' => __('Restrictions Manager', 'age-restriction'),
				'icon' => 'assets/menu_icon.png'
			),
			'in_dashboard' => array(
				'icon' 	=> 'assets/32.png',
				'url'	=> admin_url("admin.php?page=age_restriction#!/restrictions_manager")
			),
			'description' => __('With this module you can create an age restriction to whatever section/page you want in your site.', 'age-restriction'),
			'module_init' => 'init.php',
      	  	'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/amazon-discount-finder/documentation/restrictions-manager/'
			),
	        'load_in' => array(
				'backend' => array(
					'@all'
				),
				'frontend' => true
			),
			'javascript' => array(
				'admin',
				'hashchange',
				'tipsy',
				'ajaxupload',
				'thickbox',
				'jquery-colorpicker',
				'jquery-ui-core',
				'jquery-ui-datepicker',
				'jquery-ui-slider',
				'jquery-timepicker',
				'jquery-imagepicker',
				'jquery-ui-tabs',
				'flot-2.0',
				'flot-tooltip',
				'flot-stack',
				'flot-pie',
				'flot-time',
				'flot-resize'
			),
			'css' => array(
				'admin'
			),
			'shortcodes_btn' => array(
				'icon' 	=> 'assets/20-icon.png',
				'title'	=> __('Insert Restrictions Manager Shortcode', 'age-restriction')
			)
		)
	)
 );