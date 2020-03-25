<?php 
/**
 * age_restrictionBannersStats class
 * ================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 * @access 		public
 * @return 		void
 */  
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBannersStats') != true) {
    class age_restrictionBannersStats
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
        public $the_plugin = null;
		public $amzHelper = null;

		private $module_folder = '';
        private $module_folder_path = '';

		static protected $_instance;
		
		public $postid = 0;
		
		private $__initialDate = array();
		private $req = array();
		
		private static $convert = array(
			'desktop' => array('Desktop views', '#9acd00'),
			'mobile' => array('Mobile views', '#64d1f4'),
			'tablet' => array('Tablet views', '#000000'),
			'hits' => array('Impressions', '#7ac127'), 
			'auth' => array('Authentications', '#ff750a'), 
			'manual' => array('Manual Auth', '#ffa700'),
			'facebook' => array('Facebook Auth', '#3b5998'),
			'google' => array('Google Auth', '#dd4b39'),
		);
		private static $countries = array();
		

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $the_plugin, $postid=0 )
        {
			$this->the_plugin = $the_plugin;
			if ( isset($this->the_plugin->amzHelper) && !empty($this->the_plugin->amzHelper) ) {
				$this->amzHelper = $this->the_plugin->amzHelper;
			} else {
				require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'aa-framework/amz.helper.class.php' );
				if( class_exists('age_restrictionAmazonHelper') ){
					$this->amzHelper = age_restrictionAmazonHelper::getInstance( $this->the_plugin, false);
				}
			}
			
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
            $this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/restrictions_manager/';

			$this->set_banner_postid( $postid );
			
			self::$countries = $this->the_plugin->getCountriesList('code', 'upper');
			
			if ( $this->the_plugin->is_admin === true ) {
				add_action('wp_ajax_age_restrictionGetStatsGraphData', array($this, 'getStatsGraphData' ));
				add_action('wp_ajax_age_restrictionGetStats', array($this, 'getStats' ));
			}
			
			if ( $this->the_plugin->is_admin === true ) {
			
				$this->__initialDate = $this->getInitialData(); //initial date!
				if ( empty($this->__initialDate) )
					$this->__initialDate = array( date( 'Y-m-d' ) => 1  );
				$this->__initialDate = array(
					'from' 	=> date( 'Y-m-d', strtotime( "-1 week", strtotime( key($this->__initialDate) ) ) ),
					'to' 	=> date( 'Y-m-d', strtotime( key($this->__initialDate) ) )
				);
			}

            if ( $this->the_plugin->is_admin === true ) {
                // create AJAX request
                add_action('wp_ajax_age_restriction_banners_list_table', array(
                    $this,
                    '_ajax_fetch_list_table'
                ));
                add_action('wp_ajax_age_restriction_banners_list_table_full', array(
                    $this,
                    '_ajax_fetch_list_table_full'
                ));
				add_action('wp_ajax_age_restriction_banners_list_export', array(
                    $this,
                    '_ajax_export_list'
				));
                
                require_once ($this->module_folder_path . 'list-table.class.php' );
            }
        }

		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionBannersStats Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
		
		public function build_html() {
			//$post_id = $this->postid;
			
			$html = array();

			ob_start();
?>

<div class="age_restriction-statistics">
	
	<div class="age_restriction-statistics-content">
	    
	    <div class="age_restriction-vars" style="display: none;">
	        <?php
	           $stats_vars = array(
	               'ajaxurl'       => admin_url( 'admin-ajax.php' ),
               );
               $stats_vars = htmlentities( json_encode( $stats_vars ) );
               echo $stats_vars;
	        ?>
	    </div>
	
		<!-- Main loading box -->
		<div id="main-loading" style="display: none;">
			<div id="loading-overlay"></div>
			<div id="loading-box">
				<div class="loading-text">Loading</div>
				<div class="meter animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
			</div>
		</div>
		
		<h2>General Analytics</h2>
		
		<div id="age_restriction-filter-by-date">
			<label for="age_restriction-filter-by-date-from">From:</label>
			<input type="text" name="age_restriction-filter-by-date-from" id="age_restriction-filter-by-date-from" class="" value="<?php echo $this->__initialDate['from']; ?>">
			<label for="age_restriction-filter-by-date-to">to</label>
			<input type="text" name="age_restriction-filter-by-date-to" id="age_restriction-filter-by-date-to" class="" value="<?php echo $this->__initialDate['to']; ?>">
			<input type="button" value="Apply Filters" id="age_restriction-filter-graph-data" class="age_restriction-button blue">
		</div>
		
		<ul class="age_restriction-statistics-list age_restriction-legend">
			<li class="desktop">
				<div class="age_restriction-list-value desktop">Desktop<span></span></div>
			</li>
			<li class="mobile">
				<div class="age_restriction-list-value mobile">Mobile<span></span></div>
			</li>
			<li class="tablet">
				<div class="age_restriction-list-value tablet">Tablet<span></span></div>
			</li>
			<li class="impressions">
				<div class="age_restriction-list-value impressions">Impressions<span></span></div>
			</li>
			<li class="authentications">
				<div class="age_restriction-list-value authentications">Authentications<span></span></div>
			</li>
			<li class="auth-manual">
				<div class="age_restriction-list-value auth-manual">Manual Verify<span></span></div>
			</li>
			<li class="auth-facebook">
				<div class="age_restriction-list-value auth-facebook">Facebook Verify<span></span></div>
			</li>
			<li class="auth-google">
				<div class="age_restriction-list-value auth-google">Google Verify<span></span></div>
			</li>
		</ul>
		
		<!-- graph -->
		<div id="age_restriction-stats-graph"></div>

		<!-- charts -->
		<div class="age_restriction-chart-container">
			<div class="age_restriction-chart-title devices">
				<p>
					<span class="age_restriction-desktop">Desktop</span>
					<span class="age_restriction-mobile">Mobile</span>
					<span class="age_restriction-tablet">Tablet</span>
				</p>
			</div>
			<div id="age_restriction-chart-devices" class="chart">
				<!--<div class="age_restriction-chart-refresh"></div>-->
			</div>
		</div>
		
		<div class="age_restriction-chart-container">
			<div class="age_restriction-chart-title impressions">
				<p>Total Impressions</p>
			</div>
			<div id="age_restriction-chart-impressions" class="chart">
				<!--<div class="age_restriction-chart-refresh"></div>-->
			</div>
		</div>
		
		<div class="age_restriction-chart-container">
			<div class="age_restriction-chart-title authentications">
				<p>Total Authentications</p>
			</div>
			<div id="age_restriction-chart-authentications" class="chart">
				<!--<div class="age_restriction-chart-refresh"></div>-->
			</div>
		</div>
		
		<div class="age_restriction-chart-container">
			<div class="age_restriction-chart-title snetworks">
				<p>
					<span class="age_restriction-manual">Manual</span>
					<span class="age_restriction-facebook">Facebook</span>
					<span class="age_restriction-google">Google</span>
				</p>
			</div>
			<div id="age_restriction-chart-snetworks" class="chart">
				<!--<div class="age_restriction-chart-refresh"></div>-->
			</div>
		</div>
		
        <?php echo $this->export_display(); ?>

        <!-- database table listing -->
        <hr class="age_restriction-statistics-divider">
        
        <div class="age_restriction-statistics-list" id="age_restriction-stats-details">
        </div>
        
		<!-- order per country -->
		<hr class="age_restriction-statistics-divider">
		<h2>Top / Country</h2>
		
		<ul class="age_restriction-statistics-list" id="age_restriction-stats-country"></ul>
		<a name="age_restriction-load-more-country" href="#age_restriction-load-more-country" class="age_restriction-load-more" data-action="country">Load More</a>
	</div>
</div>

<?php
			$html[] = ob_get_clean();
			return implode(PHP_EOL, $html);
		}

        private function export_display() {
            $html = array();
            ob_start();
            
            $cols = $this->the_plugin->get_stats_columns('age_restriction_export_table_cols', array('id', 'banner_id', 'country_code'));
            $export_settings = (array) get_option('age_restriction_export', array());
            $cols_selected = (array) ( isset($export_settings['export_cols']) ? $export_settings['export_cols'] : array() );
?>
        <!-- database table listing -->
        <hr class="age_restriction-statistics-divider">
        <h2>Export</h2>

        <div class="age_restriction-statistics-list" id="age_restriction-stats-export">
            <table class="wp-list-table widefat fixed export">
                <thead>
                    <tr>
                        <th width="70%;">
                            Select columns
                        </th>
                        <th>
                            Filters & Action
                        </th>
                    </tr>
                </thead>
                <tfoot></tfoot>
                <tbody>
                    <tr>
                        <td>
                        <?php
                            $html2 = array();
                            foreach ($cols as $key => $val) {
                                $title = str_replace('_', ' ', $val);
                                $title = ucwords($title);

                                $checked = in_array($val, $cols_selected) ? ' checked="checked"' : '';
                                $html2[] =   '<label for="export-col-'.$val.'">';
                                $html2[] =       '<input type="checkbox" name="export-col['.$val.']" id="export-col-'.$val.'"' . $checked . '>';
                                $html2[] =   $title . '</label>';
                            }
                            echo implode(PHP_EOL, $html2);
                        ?>
                        </td>
                        <td>
                            <select name="export_action" id="export_action">
                                <?php
                                    $export_filters['export_action'] = array(
                                        'all'      => 'Action (All)',
                                        'hits'  => 'Hits',
                                        'auth'  => 'Auth',
                                    ); 
                                    echo $this->build_select('export_action', $export_filters['export_action'], (isset($export_settings['export_action']) ? $export_settings['export_action'] : ''));
                                ?>
                            </select>
                            <select name="export_device_type" id="export_device_type">
                                <?php
                                    $export_filters['export_device_type'] = array(
                                        'all'          => 'Device Type (All)',
                                        'desktop'   => 'Desktop',
                                        'mobile'    => 'Mobile',
                                        'tablet'    => 'Tablet',
                                    ); 
                                    echo $this->build_select('export_device_type', $export_filters['export_device_type'], (isset($export_settings['export_action']) ? $export_settings['export_device_type'] : ''));
                                ?>
                            </select>
                            <select name="export_verify_source" id="export_verify_source">
                                <?php
                                    $export_filters['export_verify_source'] = array(
                                        'all'          => 'Verify Source (All)',
                                        'manual'    => 'Manual',
                                        'facebook'  => 'Facebook',
                                        'google'    => 'Google',
                                    ); 
                                    echo $this->build_select('export_verify_source', $export_filters['export_verify_source'], (isset($export_settings['export_verify_source']) ? $export_settings['export_verify_source'] : ''));
                                ?>
                            </select>
                            <select name="export_orderby" id="export_orderby">
                                <?php
                                    $__cols = array_flip($cols);
                                    foreach ($__cols as $key => $val) { $__cols["$key"] = $key; }
                                    $__cols = array_map(array($this, '__nice_title'), $__cols);
                                    $export_filters['export_orderby'] = array_merge( array(
                                        ''        => 'Order by',
                                    ), $__cols );
                                    unset($export_filters['export_orderby']['title']);
                                    echo $this->build_select('export_orderby', $export_filters['export_orderby'], (isset($export_settings['export_orderby']) ? $export_settings['export_orderby'] : ''));
                                ?>
                            </select>
                            <select name="export_order" id="export_order">
                                <?php
                                    $export_filters['export_order'] = array(
                                        'asc'       => 'ASC',
                                        'desc'      => 'DESC',
                                    ); 
                                    echo $this->build_select('export_order', $export_filters['export_order'], (isset($export_settings['export_order']) ? $export_settings['export_order'] : ''));
                                ?>
                            </select>
                            <select name="export_type" id="export_type">
                                <?php
                                    $export_filters['export_type'] = array(
                                        ''          => 'Export type',
                                        'csv'       => 'CSV',
                                        'sml'       => 'spreadsheetML',
                                    ); 
                                    echo $this->build_select('export_type', $export_filters['export_type'], (isset($export_settings['export_type']) ? $export_settings['export_type'] : ''));
                                ?>
                            </select>
                            <input type="button" class="age_restriction-button green" id="export-btn" value="Export">
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="age_restriction-message action_status" style="display: none;"></div>
        </div>
<?php
            $html[] = ob_get_clean();
            return implode(PHP_EOL, $html);
        }

        private function build_select( $param, $values, $default='', $css='' ) {
            if (empty($values) || !is_array($values)) return '';
            
            $html = array();
            foreach ($values as $k => $v) {
                $html[] = '<option value="' . $k . '"' . ($k == $default ? ' selected="selected"' : '') . '' . (!empty($css) ? ' '.$css : '') . '>' . $v . '</option>';
            }
            return implode(PHP_EOL, $html);
        }

		/**
		 * retrieve stats 
		 */
		public function getStats() {
			$req = array(
				'action' 		=> isset($_REQUEST['subaction']) ? trim($_REQUEST['subaction']) : '',
				'step'			=> isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 0
			);
			$ret = array(
				'status' 	=> 'invalid',
				'data'		=> ''
			);
			
			if ( in_array($req['action'], array('charts', 'country')) ) {
				global $wpdb;
				
				//default clause!
				$q_keyurl_clause = $this->getDefaultClause();
				
				if ( in_array($req['action'], array('country')) ) {
					$start = $req['step'] * 5; $limitnb = 5;
				}
			}
			
			if ( $req['action'] == 'charts' ) {
				
				$ret['data'] = array();

				//Query
				$get_ranks_sql = "SELECT COUNT(a.id) AS nb
				 FROM {$wpdb->prefix}age_restriction_stats AS a
				 WHERE 1=1
				 " . $q_keyurl_clause . "
				;";
				$results = $wpdb->get_var( $get_ranks_sql );
				$nbrows = isset($results) && !empty($results) ? (int) $results : 0;

  				if ( $nbrows > 0 ) {

					//Query
					$get_ranks_sql = "SELECT action, COUNT(a.id) AS nb
					 FROM {$wpdb->prefix}age_restriction_stats AS a
					 WHERE 1=1
					 " . $q_keyurl_clause . "
					 GROUP BY a.action
					 ORDER BY nb DESC
					;";
					$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
					if ( $results ) {
						foreach ($results as $k => $v) {
							$act = $v['action'];
							$ret['data']["$act"] = (int) $v['nb'];
						}
					}
					
					//Query - All banners
					//default clause!
					$qall_keyurl_clause = $this->getDefaultClause(true);
				
					$get_ranks_sql = "SELECT action, COUNT(a.id) AS nb
					 FROM {$wpdb->prefix}age_restriction_stats AS a
					 WHERE 1=1
					 " . $qall_keyurl_clause . "
					 GROUP BY a.action
					 ORDER BY nb DESC
					;";
					$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
					if ( $results ) {
						foreach ($results as $k => $v) {
							$act = $v['action'];
							$ret['data']["{$act}_all"] = (int) $v['nb'];
						}
					}
  
					//Query
					$get_ranks_sql = "SELECT device_type, COUNT(a.id) AS nb, CEIL( ROUND( ( COUNT(a.id) / $nbrows ), 2 ) * 100 ) AS proc
					 FROM {$wpdb->prefix}age_restriction_stats AS a
					 WHERE 1=1
					 " . $q_keyurl_clause . "
					 GROUP BY a.device_type
					 ORDER BY nb DESC
					;";
					$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
					if ( $results ) {
						foreach ($results as $k => $v) {
							$act = $v['device_type'];
							$ret['data']["$act"] = (int) $v['proc'];
							$ret['data']["{$act}_views"] = (int) $v['nb'];
						}
					}
					
					//Query Networks
					$get_ranks_sql = "SELECT verify_source, COUNT(a.id) AS nb, CEIL( ROUND( ( COUNT(a.id) / $nbrows ), 2 ) * 100 ) AS proc
					 FROM {$wpdb->prefix}age_restriction_stats AS a
					 WHERE 1=1
					 " . $q_keyurl_clause . "
					 GROUP BY a.verify_source
					 ORDER BY nb DESC
					;";
					$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
					if ( $results ) {
						foreach ($results as $k => $v) {
							$act = $v['verify_source'];
							if( $act != '' ) {
								$ret['data']["$act"] = (int) $v['proc'];
								$ret['data']["{$act}_views"] = (int) $v['nb'];
							}
						}
					}
				}
			}
			
			if ( $req['action'] == 'country' ) {
				
				$ret['data'] = array();

				//Query
				$get_ranks_sql = "SELECT country, country_code, COUNT(a.id) AS nb
				 FROM {$wpdb->prefix}age_restriction_stats AS a
				 WHERE 1=1
				 " . $q_keyurl_clause . "
				 GROUP BY a.country_code
				 ORDER BY nb DESC
				 LIMIT $start, $limitnb
				;";
				$results2 = $wpdb->get_results( $get_ranks_sql, ARRAY_A );

  				if ( $results2 ) {

					foreach ( $results2 as $kk => $vv ) {
						//Query
						$get_ranks_sql = "SELECT country, country_code, action, COUNT(a.id) AS nb
						 FROM {$wpdb->prefix}age_restriction_stats AS a
						 WHERE 1=1
						 AND a.country_code = '" . $vv['country_code'] . "'
						 " . $q_keyurl_clause . "
						 GROUP BY a.action
						 ORDER BY nb DESC
						;";
						$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
						if ( $results ) {
							$__tmp = array('hits' => 0, 'auth' => 0, 'manual' => 0, 'facebook', );
							foreach ($results as $k => $v) {
								$__tmp["{$v['action']}"] = $v['nb'];
							}
							
							$country_code = strtolower($vv['country_code']);
	
							$ret['data'][] = '
			<li>
				<div class="age_restriction-list-country"><span class="flag flag-' . $country_code . '"></span>' . $vv['country'] . '</div>
				<div class="age_restriction-list-value impressions">' . $__tmp['hits'] . '</div>
				<div class="age_restriction-list-value authentications">' . $__tmp['auth'] . '</div>
				<div class="age_restriction-list-value date">' . ( date('M d', strtotime($this->req['from'])) . ' - ' . date('M d', strtotime($this->req['to'])) ) . '</div>
			</li>
								';
						}
					}
					
					$ret['data'] = implode(PHP_EOL, $ret['data']);
				}
			}

			if ( in_array($req['action'], array('charts', 'country')) ) {
				die( json_encode(
					array(
						'status' 	=> 'valid',
						'data'		=> $ret['data']
					)
				));
			}
			
			die( json_encode(
				array(
					'status' 	=> 'invalid'
				)
			));
		}

		 
		/*
		* getStatsGraphData, method
		* ------------------------
		*
		* this will create request to age_restriction_stats_reporter table
		*/
		public function getStatsGraphData()
		{
			global $wpdb;
   
			//default clause!
			$q_keyurl_clause = $this->getDefaultClause();
			
			//Query
			$get_ranks_sql = "SELECT device_type, DATE(a.report_day) AS _report_day, COUNT(a.id) AS nb
			 FROM {$wpdb->prefix}age_restriction_stats AS a
			 WHERE 1=1
			 " . $q_keyurl_clause . "
			 GROUP BY a.device_type, _report_day
			 ORDER BY device_type ASC, _report_day ASC
			;";
			$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
			
			// reorder array base on focus kw and link as key
			if( count($results) > 0 ){
				$graph_data = array();
				foreach ($results as $key => $value){
					if( trim($value['device_type']) != '' ) {
						$graph_data[sanitize_text_field( $value['device_type'] )][$value['_report_day']] = $value;
					}
				}
				
				if( count($graph_data) > 0 ){
					$ret_data_deviceTypes = array();
					foreach ($graph_data as $key => $value){
						
						// Alias 
						$alias = self::$convert["$key"][0];
						
						// rank per day
						$data = array();
						if( count($value) > 0 ){
							foreach ($value as $key2 => $value2) {
								$data[] = array( strtotime($value2['_report_day']) * 1000, (int) $value2['nb'] );
							}  
						}
						
						$ret_data_deviceTypes[] = array(
							'label' => $alias,
							'data' 	=> $data,
							'color' => self::$convert["$key"][1]
						);
					}
				}
			}
			
			//Query
			$get_ranks_sql = "SELECT action, DATE(a.report_day) AS _report_day, COUNT(a.id) AS nb
			 FROM {$wpdb->prefix}age_restriction_stats AS a
			 WHERE 1=1
			 " . $q_keyurl_clause . "
			 GROUP BY a.action, _report_day
			 ORDER BY ACTION ASC, _report_day ASC
			;";
			$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
			
			// reorder array base on focus kw and link as key
			if( count($results) > 0 ){
				$graph_data = array();
				foreach ($results as $key => $value){
					$graph_data[sanitize_text_field( $value['action'] )][$value['_report_day']] = $value;
				}
				
				if( count($graph_data) > 0 ){
					$ret_data_actions = array();
					foreach ($graph_data as $key => $value){
						
						// Alias 
						$alias = self::$convert["$key"][0];
						
						// rank per day
						$data = array();
						if( count($value) > 0 ){
							foreach ($value as $key2 => $value2) {
								$data[] = array( strtotime($value2['_report_day']) * 1000, (int) $value2['nb'] );
							}  
						}
						
						$ret_data_actions[] = array(
							'label' => $alias,
							'data' 	=> $data,
							'color' => self::$convert["$key"][1]
						);
					}
				}
			}
			
			//Query
			$get_ranks_sql = "SELECT verify_source, DATE(a.report_day) AS _report_day, COUNT(a.id) AS nb
			 FROM {$wpdb->prefix}age_restriction_stats AS a
			 WHERE 1=1
			 " . $q_keyurl_clause . "
			 GROUP BY a.verify_source, _report_day
			 ORDER BY verify_source ASC, _report_day ASC
			;";
			$results = $wpdb->get_results( $get_ranks_sql, ARRAY_A );
			
			// reorder array base on focus kw and link as key
			if( count($results) > 0 ){
				$graph_data = array();
				foreach ($results as $key => $value){
					if( trim($value['verify_source']) != '' ) {
						$graph_data[sanitize_text_field( $value['verify_source'] )][$value['_report_day']] = $value;
					}
				}
				
				$ret_data_sources = array();
				if( count($graph_data) > 0 ){
					foreach ($graph_data as $key => $value){
						
						// Alias 
						$alias = self::$convert["$key"][0];
						
						// rank per day
						$data = array();
						if( count($value) > 0 ){
							foreach ($value as $key2 => $value2) {
								$data[] = array( strtotime($value2['_report_day']) * 1000, (int) $value2['nb'] );
							}  
						}
						
						$ret_data_sources[] = array(
							'label' => $alias,
							'data' 	=> $data,
							'color' => self::$convert["$key"][1]
						);
					}
				}
			}
			
			if( count($ret_data_deviceTypes) > 0 || count($ret_data_actions) > 0 || count($ret_data_sources) > 0 ) {
				$ret_data = array_merge( $ret_data_deviceTypes, $ret_data_actions, $ret_data_sources );
				
				die( json_encode(
					array(
						'status' 	=> 'valid',
						'data'		=> $ret_data
					)
				));
			}
			
			die( json_encode(
				array(
					'status' 	=> 'invalid'
					//,'sql'		=> $get_ranks_sql
				)
			));
		}
		
		public function getDefaultClause($only_bydate=false) {
			global $wpdb;
  
			$req = array(
				'banner_id' 	=> isset($_REQUEST['postid']) ? (int) $_REQUEST['postid'] : $this->postid,
				'from' 			=> isset($_REQUEST['from_date']) ? trim($_REQUEST['from_date']) : $this->__initialDate['from'],
				'to' 			=> isset($_REQUEST['to_date']) ? trim($_REQUEST['to_date']) : $this->__initialDate['to']
			);
			$this->req = $req;
  
			$q = '';
			if ( $req['banner_id'] > 0 && !$only_bydate ) {
				$q .= 'AND a.banner_id = %s';
				$q = $wpdb->prepare( $q, $req['banner_id'] );
			}
			$q .= ' AND DATE(a.report_day) between %s AND %s';
			$q = $wpdb->prepare( $q, $req['from'], $req['to'] );
			$q = trim($q); $q = !empty($q) ? " $q " : '';
			return $q;
		}

		public function getInitialData() {
			global $wpdb;
			
			$post_id = $this->postid;
			
			$sql = "
				SELECT COUNT(a.id) AS nb, date(a.report_day) as report_day FROM {$wpdb->prefix}age_restriction_stats as a WHERE 1=1
				 AND a.banner_id > 0 " . ($post_id > 0 ? " AND a.banner_id = '$post_id' " : "") . "
				 GROUP BY date(a.report_day)
				 HAVING nb>1
				 ORDER BY date(a.report_day) DESC
				 limit 7;
			";
			$results = $wpdb->get_results( $sql, ARRAY_A );

			// reorder array
			$ret = array();
			if( count($results) > 0 ){
				foreach ($results as $kk=>$vv) {
					$ret[ $vv['report_day'] ] = $vv['nb'];
				}
			}
			return $ret;
		}

        public function _ajax_fetch_list_table()
        {
            $list_table = new age_restriction_Banners_List_Table( $this->the_plugin );
            $list_table->ajax_response();
        }
        
        public function _ajax_fetch_list_table_full()
        {
            // Create an instance of our package class...
            $list_table = new age_restriction_Banners_List_Table( $this->the_plugin );
            
            // Fetch, prepare, sort, and filter our data...
            $list_table->prepare_items();
            
			ob_start();
			?>
				<h2>Stats details</h2>
				
				<input type="hidden" name="page" value="<?php echo isset($_REQUEST['page']) ? $_REQUEST['page'] : ''; ?>" />
				<?php $list_table->display() ?>
			<?php
			$html = ob_get_contents();
			ob_clean();
			   
            die( json_encode(
                array(
                    'status'    => 'valid',
                    'data'      => $html
                )
            ));
        }
        
        public function _ajax_export_list() {
            $req = array(
                'from_date'             => isset($_REQUEST['from_date']) ? trim($_REQUEST['from_date']) : '',
                'to_date'               => isset($_REQUEST['to_date']) ? trim($_REQUEST['to_date']) : '',
                'postid'                => isset($_REQUEST['postid']) ? trim($_REQUEST['postid']) : 'all',
            
                'cols'                  => isset($_REQUEST['cols']) ? trim($_REQUEST['cols']) : array(),
                'export_type'           => isset($_REQUEST['export_type']) ? trim($_REQUEST['export_type']) : '',
                'export_action'         => isset($_REQUEST['export_action']) ? trim($_REQUEST['export_action']) : 'all',
                'export_device_type'    => isset($_REQUEST['export_device_type']) ? trim($_REQUEST['export_device_type']) : 'all',
                'export_verify_source'  => isset($_REQUEST['export_verify_source']) ? trim($_REQUEST['export_verify_source']) : 'all',
                'export_orderby'        => isset($_REQUEST['export_orderby']) ? trim($_REQUEST['export_orderby']) : 'id',
                'export_order'          => isset($_REQUEST['export_order']) ? trim($_REQUEST['export_order']) : 'asc',
                
                'do_export'             => isset($_REQUEST['do_export']) ? true : false,
            );
            $req = array_merge($req, array(
                'export_type'           => !empty($req['export_type']) ? $req['export_type'] : '',
                'export_orderby'        => !empty($req['export_orderby']) ? $req['export_orderby'] : 'id',
                'export_order'          => !empty($req['export_order']) ? $req['export_order'] : 'asc',
            ));
            extract($req);
 
            $ret = array(
                'status'    => 'invalid',
                'data'      => '',
            );
            
            if ( empty($cols) ) {
                $ret = array_merge($ret, array(
                    'data'      => 'Please choose at least one column!'
                ));
                die(json_encode( $ret ));
            }
            if ( empty($export_type) ) {
                $ret = array_merge($ret, array(
                    'data'      => 'Please choose an export type!'
                ));
                die(json_encode( $ret ));
            }
            
            $cols_arr = (array) explode(',', trim($cols));
            $cols_arr = array_filter($cols_arr);
            
            $new_arr = array(
                'export_cols'           => $cols_arr,
                'export_type'           => $export_type,
                'export_action'         => $export_action,
                'export_device_type'    => $export_device_type,
                'export_verify_source'  => $export_verify_source,
                'export_orderby'        => $export_orderby,
                'export_order'          => $export_order,
            );
            update_option('age_restriction_export', $new_arr);

            $file_rows = $this->get_export_file($req);
            if ( empty($file_rows) ) {
                $ret = array_merge($ret, array(
                    'data'      => 'no items found for export!'
                ));
                die(json_encode( $ret ));
            }
            
            if ( $do_export ) {
                $this->do_export( $file_rows, $req );
                die;
            }
            
            $ret = array_merge($ret, array(
                'status'        => 'valid',
                'data'          => 'export was successfull.',
            ));
            die(json_encode( $ret ));
        }

        private function get_export_file( $req ) {
            global $wpdb;

            extract($req);
            
            $cols = $this->the_plugin->get_stats_columns('age_restriction_export_table_cols', array('id', 'country_code', 'banner_id'));
            $export_settings = (array) get_option('age_restriction_export', array());
            $cols_selected = (array) ( isset($export_settings['export_cols']) ? $export_settings['export_cols'] : array() );
            $cols = !empty($cols_selected) ? $cols_selected : $cols;
            $keep_banner_id = true;
            if ( in_array('title', $cols) && !in_array('banner_id', $cols) ) {
                $cols[] = 'banner_id';
                $keep_banner_id = false;
            }
            $cols_str = implode(',', array_diff($cols, array('title')));
            
            $db_name = $wpdb->prefix . 'age_restriction_stats';
            $orderby = isset($export_orderby) ? $export_orderby : 'id';
            $order = isset($export_order) ? $export_order : 'asc';
            $sql = "SELECT $cols_str FROM " . ( $db_name ) . " WHERE 1=1" . $this->query_filter_clause( $req ) . " ORDER BY $orderby $order";
			
            $items = (array) $wpdb->get_results($sql, ARRAY_A);
            
            if ( in_array('title', $cols) ) {
                $posts_list = $this->the_plugin->get_banner_posts();
                foreach ((array) $items as $key => $val) {
                    $post_id = isset($val['banner_id']) ? $val['banner_id'] : 0;
                    $items["$key"]['title'] = isset($posts_list["$post_id"], $posts_list["$post_id"]->post_title) ? $post_id . ' - ' . $posts_list["$post_id"]->post_title : $post_id . ' - no title--';
                    if ( !$keep_banner_id ) unset($items["$key"]['banner_id']);
                }
            } 
            return $items;
        }
        
        public function query_filter_clause( $req ) {
            $q = array();
            
            if ( !empty($req['postid']) && $req['postid'] != 'all' ) {
                $postid = $req['postid'];
                $q[] = "AND banner_id = '$postid'";
            }

            if ( !empty($req['from_date']) && !empty($req['to_date']) ) {
                $from_date = $req['from_date'];
                $to_date = $req['to_date'];
                $q[] = "AND DATE(report_day) between '$from_date' AND '$to_date'";
                
            } else if ( !empty($req['from_date']) ) {
                $q[] = "AND DATE(report_day) >= '$from_date'";
                
            } else if ( !empty($req['to_date']) ) {
                $q[] = "AND DATE(report_day) <= '$to_date'";
            }

            if ( !empty($req['export_action']) && $req['export_action'] != 'all' ) {
                $export_action = $req['export_action'];
                $q[] = "AND action = '$export_action'";
            }
            if ( !empty($req['export_device_type']) && $req['export_device_type'] != 'all' ) {
                $export_device_type = $req['export_device_type'];
                $q[] = "AND device_type = '$export_device_type'";
            }
            if ( !empty($req['export_verify_source']) && $req['export_verify_source'] != 'all' ) {
                $export_verify_source = $req['export_verify_source'];
                $q[] = "AND verify_source = '$export_verify_source'";
            }
 
            return !empty($q) ? ' ' . trim( implode(' ', $q) ) : '';
        }
        
        private function export_filename( $req ) {
            extract($req);

            $f = array();
            $f[] = 'age_restriction_export';
            if ( !empty($from_date) ) {
                $f[] = $from_date;
            }
            if ( !empty($to_date) ) {
                $f[] = $to_date;
            }
            $f[] = $postid;
            $f[] = time();
            
            return implode('__', $f);         
        }

        private function __nice_title($item) {
            $title = str_replace('_', ' ', $item);
            $title = ucwords($title);
            return $title;
        }
        
        private function do_export( $result, $req ) {
            if (!$result) return false;
            
            extract($req);
            
            $filename = $this->export_filename($req);
            switch ($export_type) {
                case 'csv' :
                    $file_ext = 'csv';
                    $content_type = 'text/csv';
                    break;
                    
                case 'sml':
                    $file_ext = 'xls';
                    $content_type = 'application/vnd.ms-excel';
                    
                    require_once( $this->the_plugin->cfg['paths']['scripts_dir_path'] . '/php-export-data/php-export-data.class.php' );
                    $exporter = null; 
                    if( class_exists('ExportDataExcel') ){
                        $exporter = new ExportDataExcel('string', 'test.xls');
                    }
                    break;
            }

            ob_end_clean();

            // export headers
            header("Content-Description: File Transfer");           
            header("Content-Type: $content_type; charset=utf-8"); //application/force-download
            header("Content-Disposition: attachment; filename=$filename.$file_ext");
            // Disable caching
            header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
            header("Cache-Control: private", false);
            header("Pragma: no-cache"); // HTTP 1.0
            header("Expires: 0"); // Proxies
            
            $isExport = false;
            if ( $export_type == 'csv'
                || ( $export_type == 'sml' && !is_null($exporter) ) ) {
                $isExport = true;
            }

            // begin export file
            if ( $isExport ) {
                $fp = fopen('php://output', 'w');
                $headrow = $result[0];
                $headrow = array_keys($headrow);
                $headrow = array_map(array($this, '__nice_title'), $headrow);
            }

            // export file content
            if ( $export_type == 'csv' ) {
                fputcsv($fp, $headrow);
                foreach ($result as $data) {
                    fputcsv($fp, $data);
                }
                
            } else if ( $export_type == 'sml' && !is_null($exporter) ) {
                $exporter->initialize(); // starts streaming data to web browser
                
                // pass addRow() an array and it converts it to Excel XML format and sends 
                // it to the browser
                $exporter->addRow($headrow); 
                
                foreach ($result as $data) {
                    $exporter->addRow($data);
                }
                
                $exporter->finalize(); // writes the footer, flushes remaining data to browser.
                
                $content = $exporter->getString();
                fwrite($fp, $content);
            }
            
            // end export file
            if ( $isExport ) {
                fclose($fp);
            }

            $contLength = ob_get_length();

            die;
        }
        
        
		/**
		 * Extra
		 */
		public function set_banner_postid($postid) {
			$this->postid = (int) $postid;
		}
		
		public function get_proc_css($val) {
			$val = (int) $val;
			
			$ret = 0;
			if ($val >= 70) {
				$ret = 70;
			} else if ($val >= 50) {
				$ret = 50;
			} else if ($val >= 40) {
				$ret = 40;
			} else if ($val >= 30) {
				$ret = 30;
			} else if ($val >= 20) {
				$ret = 20;
			}
			return 'after_' . $ret;
		}
	}
}

//new age_restrictionBannersStats();