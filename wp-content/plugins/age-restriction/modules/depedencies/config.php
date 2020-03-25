<?php
/**
* Config file, return as json_encode
* http://www.aa-team.com
* =======================
*
* @author		Andrei Dinca, AA-Team
* @version		1.0
*/
echo json_encode(array(
    'depedencies' => array(
        'version' => '1.0',
        'menu' => array(
            'order' => 1,
            'title' => 'Plugin Depedencies'
            ,'icon' => 'assets/16_dashboard.png'
        ),
        'description' => "Plugin Depedencies",
        'help' => array(
			'type' => 'remote',
			'url' => 'http://docs.aa-team.com/products/amazon-discount-finder/'
		),
        'module_init' => 'init.php',
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
));