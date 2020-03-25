<?php
/**
 * Dummy module return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1 - in development mode
 */

echo json_encode(
	array(
		$tryed_module['db_alias'] => array(
			/* define the form_messages box */
			'setup_box' => array(
				'title' 	=> 'Install plugin settings',
				'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
				'header' 	=> true, // true|false
				'toggler' 	=> false, // true|false
				'buttons' 	=> array(
					'install_btn' => array(
						'type' => 'submit',
						'value' => 'Install settings',
						'color' => 'blue',
						'action' => 'age_restriction-installDefaultOptions',
					)
				), // true|false|array
				'style' 	=> 'panel', // panel|panel-widget
				
				// create the box elements array
				'elements'	=> array(
					'install_box' => array(
						'type' 		=> 'textarea',
						'std' 		=> file_get_contents( $tryed_module["folder_path"] . 'default-setup.json' ),
						'size' 		=> 'large',
						'cols' 		=> '130',
						'title' 	=> 'Paste settings here',
						'desc' 		=> 'Default settings configuration loaded here.',
					)
				)
			)
			/* define the form_messages box */
			, 'backup_box' => array(
				'title' 	=> 'backup you current plugin settings',
				'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
				'header' 	=> true, // true|false
				'toggler' 	=> false, // true|false
				'buttons' 	=> false, // true|false
				'style' 	=> 'panel', // panel|panel-widget
				
				// create the box elements array
				'elements'	=> array(
					'backup_box' => array(
						'type' 		=> 'textarea',
						'std' 		=> $this->getAllSettings('json'),
						'size' 		=> 'large',
						'cols' 		=> '130',
						'title' 	=> 'Your current settings ',
						'desc' 		=> 'Copy / Paste this file if you want to backup all you plugins settings.',
					)
				)
			)
			
		)
	)
);