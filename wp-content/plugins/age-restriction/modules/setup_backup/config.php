<?php
/**
 * Config file, return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1 - in development mode
 */
 echo json_encode(
	array(
		'setup_backup' => array(
			'version' => '0.1',
			'menu' => array(
				'order' => 20,
				'title' => 'Setup / Backup',
				'icon' => 'assets/16_setupbackup.png'
			),
			'in_dashboard' => array(
				'icon' 	=> 'assets/32_setupbackup.png',
				'url'	=> admin_url("admin.php?page=age_restriction#!/setup_backup")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/products/age-restriction/'
			),
			'description' => "Using the setup backup module you can setup the plugin for the first time, and backup settings & products if you change servers.",
			'load_in' => array(
				'backend' => array(
					'admin-ajax.php'
				),
				'frontend' => false
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