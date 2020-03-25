<?php
/*
* Define class age_restrictionDashboardAjax
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionDashboardAjax') != true) {
    class age_restrictionDashboardAjax extends age_restrictionDashboard
    {
    	public $the_plugin = null;
		private $module_folder = null;
		
		/*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $the_plugin=array() )
        {
        	$this->the_plugin = $the_plugin;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/dashboard/';
			
			// ajax  helper
			add_action('wp_ajax_age_restrictionDashboardRequest', array( &$this, 'ajax_request' ));
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
			
			$actions = isset($_REQUEST['sub_actions']) ? explode(",", $_REQUEST['sub_actions']) : '';
			
			if( in_array( 'aateam_products', $actions) ){
				
				$sites = array('codecanyon', 'themeforest', 'graphicriver');
				$html = array();
				foreach( $sites as $site ){
					$api_url = 'http://marketplace.envato.com/api/edge/new-files-from-user:AA-Team,%s.json';
					
					$response_data = $this->getRemote( sprintf( $api_url, $site)  );
					
					// reorder the array
					if( isset($response_data["new-files-from-user"]) && count($response_data["new-files-from-user"]) > 0 ){
						$data = array();
						$__arr = $response_data["new-files-from-user"];
						$__newarr = array(); $__newarrSales = array();
						foreach ($__arr as $k => $v) {
							$key = $v['id'];
							$__newarr["$key"] = $v;
							$__newarrSales["$key"] = $v['sales'];
						}
						asort($__newarrSales, SORT_NUMERIC);
						foreach ($__newarrSales as $k => $v) {
							$__newarrSales["$k"] = $__newarr["$k"];
						}
						$reversed_data = array_reverse($__newarrSales, true);
						
						if( count($reversed_data) > 0 ){
							$html[] = '<div class="age_restriction-aa-products-container" id="aa-prod-' . ( $site ) . '">';
							$html[] = 	'<ul style="width: ' . ( count($reversed_data) * 135 ) .  'px">';
							foreach ( $reversed_data as $item ){
								$html[] = 	'<li>';
								$html[] = 		'<a target="_blank" href="' . ( $item['url'] ) . '?rel=AA-Team" data-preview="' . ( $item['live_preview_url'] ) . '">';
								$html[] = 			'<img src="' . ( $item['thumbnail'] ) . '" width="80" alt="' . ( $item['item'] ) . '">';
								$html[] = 			'<span class="the-rate-' . ( ceil( $item['rating'] ) ) . '"></span>';
								$html[] = 			'<strong>$' . ( $item['cost'] ) . '</strong>';
								$html[] = 		'</a>';
								$html[] = 	'</li>';
							}
							$html[] = 	'</ul>';			
							$html[] = '</div>';	
						}
						
					}
				}

				$return['aateam_products'] = array(
					'status' => 'valid',
					'html' => implode("\n", $html)
				);
			}

			die(json_encode($return));
		}

	
		/**
		 * $cache_lifetime in minutes
		 */
		private function getRemote( $the_url, $cache_lifetime=60 )
		{
			// try to get from cache
			$request_alias = 'age_restriction_' . md5($the_url);
			$from_cache = get_option( $request_alias );
			
			if( $from_cache != false ){
				if( time() < ( $from_cache['when'] + ($cache_lifetime * 60) )){
					return $from_cache['data'];
				}
			}
			$response = wp_remote_get( $the_url, array('user-agent' => "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:24.0) Gecko/20100101 Firefox/24.0", 'timeout' => 10) ); 
			
			// If there's error
            if ( is_wp_error( $response ) ){
            	return array(
					'status' => 'invalid'
				);
            }
        	$body = wp_remote_retrieve_body( $response );
			
			$response_data = json_decode( $body, true );
			
			// overwrite the cache data 
			update_option( $request_alias, array(
				'when' => time(),
				'data' => $response_data
			) );
				
	        return $response_data;
		}
    }
}