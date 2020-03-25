<?php
/*
* Define class age_restrictionServerStatus
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionServerStatusAjax') != true) {
    class age_restrictionServerStatusAjax extends age_restrictionServerStatus
    {
    	public $the_plugin = null;
		private $module_folder = null;
		private $file_cache_directory = '/psp-page-speed';
		private $cache_lifetime = 60; // in seconds
		
		/*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $the_plugin=array() )
        {
        	$this->the_plugin = $the_plugin;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/server_status/';
			
			// ajax  helper
			add_action('wp_ajax_age_restrictionServerStatusRequest', array( $this, 'ajax_request' ));
		}
		
		/*
		* ajax_request, method
		* --------------------
		*
		* this will create requests to 404 table
		*/
		public function ajax_request()
		{
			$return = array();
			$actions = isset($_REQUEST['sub_action']) ? explode(",", $_REQUEST['sub_action']) : '';
			 
			if( in_array( 'check_memory_limit', array_values($actions)) ){
				
				$memory = $this->let_to_num( WP_MEMORY_LIMIT );
				$html = array();
            	if ( $memory < 127108864 ) {
            		$html[] = '<div class="age_restriction-message age_restriction-error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'age-restriction' ), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</div>';
            	} else {
            		$html[] = '<div class="age_restriction-message age_restriction-success">' . size_format( $memory ) . '</div>';
            	}

				$return = array(
					'status'	=> 'valid',
					'html' 		=> implode("\n", $html)
				);
			}
			
			if( in_array( 'export_log', array_values($actions)) ){
				
				$log = isset($_REQUEST['log']) ? $_REQUEST['log'] : '';
				$temp_file = tmpfile();
				fwrite( $temp_file, $log );
				fseek( $temp_file, 0 );
				
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename="age_restriction-logs.html"' );
				header( 'Content-Length: ' . strlen($log) );
				
				echo fread( $temp_file, strlen($log) );
				
				 // this removes the file
				fclose( $temp_file );
				
				die;
			}
			
			if( in_array( 'remote_get', array_values($actions)) ){
				
				$status = false;
				$msg = '';
				// WP Remote Get Check
				$params = array(
					'sslverify' 	=> false,
		        	'timeout' 		=> 20,
		        	'body'			=> isset($request) ? $request : array()
				);
				$response = wp_remote_post( 'http://webservices.amazon.com/AWSECommerceService/AWSECommerceService.wsdl', $params );
	 
				if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
	        		$msg = __('wp_remote_get() was successful - GeoIP is working.', 'age-restriction' );
	        		$status = true;
	        	} elseif ( is_wp_error( $response ) ) {
	        		$msg = __( 'wp_remote_get() failed. GeoIP won\'t work with your server. Contact your hosting provider. Error:', 'age-restriction' ) . ' ' . $response->get_error_message();
	        		$status = false;
	        	} else {
	            	$msg = __( 'wp_remote_get() failed. GeoIP may not work with your server.', 'age-restriction' );
	        		$status = false;
	        	}
				
				$return = array(
					'status'	=> ( $status == true ? 'valid' : 'invalid' ),
					'html' 		=> ( $status == true ? '<div class="age_restriction-message age_restriction-success">' : '<div class="age_restriction-message age_restriction-error">' ) . $msg . '</div>' 
				);
        	}

			if( in_array( 'check_soap', array_values($actions)) ){
				
				$status = false;
				$msg = '';
				
				if ( class_exists( 'SoapClient' ) ) {
					$msg = __('Your server has the SOAP Client class enabled.', 'age-restriction' );
					$status = true;
				} else {
	        		$msg = sprintf( __( 'Your server does not have the <a href="%s">SOAP Client</a> class enabled - some gateway plugins which use SOAP may not work as expected.', 'age-restriction' ), 'http://php.net/manual/en/class.soapclient.php' ) . '</mark>';
	        		$status = false;
	        	}

				$return = array(
					'status'	=> ( $status == true ? 'valid' : 'invalid' ),
					'html' 		=> ( $status == true ? '<div class="age_restriction-message age_restriction-success">' : '<div class="age_restriction-message age_restriction-error">' ) . $msg . '</div>' 
				);
			}
			
			if( in_array( 'active_plugins', array_values($actions)) ){
				$active_plugins = (array) get_option( 'active_plugins', array() );
									
     			if ( is_multisite() )
					$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );

				$wc_plugins = array();

				foreach ( $active_plugins as $plugin ) {

					$plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
					$dirname        = dirname( $plugin );
					$version_string = '';

					if ( ! empty( $plugin_data['Name'] ) ) {

						if ( strstr( $dirname, 'age-restriction' ) ) {

							if ( false === ( $version_data = get_transient( $plugin . '_version_data' ) ) ) {
								$changelog = wp_remote_get( 'http://dzv365zjfbd8v.cloudfront.net/changelogs/' . $dirname . '/changelog.txt' );
								$cl_lines  = explode( "\n", wp_remote_retrieve_body( $changelog ) );
								if ( ! empty( $cl_lines ) ) {
									foreach ( $cl_lines as $line_num => $cl_line ) {
										if ( preg_match( '/^[0-9]/', $cl_line ) ) {

											$date         = str_replace( '.' , '-' , trim( substr( $cl_line , 0 , strpos( $cl_line , '-' ) ) ) );
											$version      = preg_replace( '~[^0-9,.]~' , '' ,stristr( $cl_line , "version" ) );
											$update       = trim( str_replace( "*" , "" , $cl_lines[ $line_num + 1 ] ) );
											$version_data = array( 'date' => $date , 'version' => $version , 'update' => $update , 'changelog' => $changelog );
											set_transient( $plugin . '_version_data', $version_data , 60*60*12 );
											break;
										}
									}
								}
							}

							if ( ! empty( $version_data['version'] ) && version_compare( $version_data['version'], $plugin_data['Version'], '!=' ) )
								$version_string = ' &ndash; <strong style="color:red;">' . $version_data['version'] . ' ' . __( 'is available', 'age-restriction' ) . '</strong>';
						}

						$wc_plugins[] = $plugin_data['Name'] . ' ' . __( 'by', 'age-restriction' ) . ' ' . $plugin_data['Author'] . ' ' . __( 'version', 'age-restriction' ) . ' ' . $plugin_data['Version'] . $version_string;

					}
				}

				if ( sizeof( $wc_plugins ) > 0 ){
					$return = array(
						'status'	=> 'valid',
						'html' 		=> implode( ', <br/>', $wc_plugins ) 
					);
				}
			}
			
			die(json_encode($return));
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