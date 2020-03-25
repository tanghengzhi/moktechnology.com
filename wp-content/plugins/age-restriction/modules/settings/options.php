<?php
/**
 * Dummy module return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1 - in development mode
 */

global $age_restriction;
echo json_encode(array(
    $tryed_module['db_alias'] => array(
        
        /* define the form_sizes  box */
        'settings' => array(
            'title' => 'Settings',
            'icon' => '{plugin_folder_uri}assets/amazon.png',
            'size' => 'grid_4', // grid_1|grid_2|grid_3|grid_4
            'header' => true, // true|false
            'toggler' => false, // true|false
            'buttons' => true, // true|false
            'style' => 'panel', // panel|panel-widget
            
				// tabs
				'tabs'	=> array(
					'__tab1'	=> array(__('Plugin SETUP', 'age-restriction'), 'services_used_forip, allow_crawlers, crawlers_list, clear_statistics'),
					'__tab2'	=> array(__('Facebook Settings', 'age-restriction'), 'fb_app_id, fb_language'),
					'__tab3'	=> array(__('Google Settings', 'age-restriction'), 'google_client_id')
				),
            
            // create the box elements array
            'elements' => array(
            	'services_used_forip' => array(
					'type' => 'select',
			        'std' => 'local_csv',
			        'size' => 'large',
			        'force_width' => '380',
			        'title' => 'External server country detection or use local:',
			        'desc' => 'We use an external server for detecting client country per IP address or you can try local IP detection.',
			        'options' => array(
			            'local_csv'                 => 'Local IP detection (plugin local csv file with IP range lists)',
			            'api.hostip.info'           => 'api.hostip.info',
			            'api.hostip.info' 			=> 'api.hostip.info',
			            'www.geoplugin.net' 		=> 'www.geoplugin.net',
			            'ipinfo.io' 				=> 'ipinfo.io',
			        )
			    ),
			    
				'allow_crawlers' => array(
					'type' => 'select',
			        'std' => 'yes',
			        'size' => 'large',
			        'force_width' => '80',
			        'title' => 'Allow Crawlers/Robots:',
			        'desc' => 'This allows crawlers like google or bing to index your site',
			        'options' => array(
			            'yes'	=> 'Yes',
			            'no'	=> 'No',
			        )
			    ),
			    
				'crawlers_list' => array(
					'type' => 'textarea',
			        'std' => 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona| AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler| GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby',
			        'size' => 'large',
			        'force_width' => '380',
			        'title' => 'Crawlers/Robots list:',
			        'desc' => '&nbsp;'
			    ),
			    
				'clear_statistics' => array(
					'type' => 'buttons',
					'title' => 'Clear Statistics:',
			        'desc' => 'This option clears the table with statistics in the database if it becomes big.',
					'options' => array(
						'clear_statistics' => array(
							'width' => '162px',
							'type' => 'button',
							'value' => 'Clear Statistics',
							'color' => 'blue',
							'action' => 'age_restrictionClearStatistics'
						)
					)
			    ),
			    
            	'fb_app_id' => array(
                    'type' => 'text',
                    'std' => '',
                    'size' => 'large',
                    'title' => 'Facebook Application ID',
                    'desc' => 'You can obtain an Application ID if you go and create a facebook application <a href="https://developers.facebook.com/" target="_blank">here</a>.'
                ),
                
				'fb_language' => array(
                    'type' => 'text',
                    'std' => 'en_US',
                    'size' => 'large',
                    'force_width' => '100',
                    'title' => 'Facebook Language',
                    'desc' => 'Facebook language that will be used when the user is presented with the login dialog. You can get the language code from <a href="http://www.facebook.com/translations/FacebookLocales.xml" target="_blank">here</a> in the "representation" field.'
                ),
                
				'google_client_id' => array(
                    'type' => 'text',
                    'std' => '',
                    'size' => 'large',
                    'title' => 'Google Client ID',
                    'desc' => 'You can obtain a "Client ID" if you go and create a project <a href="https://console.developers.google.com/project" target="_blank">here</a>. After creating the project go to "APIs & auth -> Consent screen", select your "Email address" and write a "PRODUCT NAME".<br/>After that, go to "APIs & auth -> Credentials -> Create new Client ID" to generate the required IDs. In order for G+ Login to work, you must enable "Google+ API" from "APIs & auth -> APIs".<br/>For extra protection you can set "JAVASCRIPT ORIGINS" to your domain.'
                ),
            )
        )
    )
));