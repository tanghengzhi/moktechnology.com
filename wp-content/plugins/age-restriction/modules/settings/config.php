<?php
/**
 * Config file, return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
 echo json_encode(
	array(
		'settings' => array(
			'version' => '0.1',
			'menu' => array(
				'order' => 3,
				'title' => 'Settings',
				'icon' => 'assets/16_amzconfig.png'
			),
			'in_dashboard' => array(
				'icon' 	=> 'assets/32_amazonconfig.png',
				'url'	=> admin_url("admin.php?page=age_restriction#!/settings")
			),
			'description' => "Plugin Settings",
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/age-restriction/documentation/configuration-settings/'
			),
			'load_in' => array(
				'backend' => array(
					'admin-ajax.php'
				),
				'frontend' => true
			),
			'javascript' => array(
				'admin',
				'hashchange',
				'tipsy'
			),
			'css' => array(
				'admin',
				'tipsy'
			)
		)
	)
 );