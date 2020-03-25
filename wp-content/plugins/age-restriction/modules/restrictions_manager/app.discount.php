<?php 
/**
 * age_restrictionBannersDiscount class
 * ================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 * @access 		public
 * @return 		void
 */  
!defined('ABSPATH') and exit;
if (class_exists('age_restrictionBannersDiscount') != true) {
    class age_restrictionBannersDiscount
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

		static protected $_instance;
		
		public $postid = 0;

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

			$this->set_banner_postid( $postid );
			
			if ( $this->the_plugin->is_admin === true ) {
				add_action('wp_ajax_age_restrictionCategParameters_discount', array($this, 'age_restrictionCategParameters' ));
				add_action('wp_ajax_age_restrictionGetChildNodes_discount', array($this, 'age_restrictionGetChildNodes'));
				add_action('wp_ajax_age_restrictionCountryInterface_discount', array($this, 'advanced_search'));
			}
        }
		
		/**
	    * Singleton pattern
	    *
	    * @return age_restrictionBannersDiscount Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
		
		public function build_discount_html() {
			$html = array();
			$html[] = '<div class="age_restriction-discount-select">
			<div id="discount-list" class="categorydiv tabs age_restriction-conditions ui-tabs">';

			// countries list
			$countries = $this->amzHelper->getAmazonCountries();

			$html[] = 	'<div class="age_restriction-all-countries" style="display: none;"><input type="checkbox" name="age_restriction-countries-all" id="age_restriction-countries-all" value="on" class="age_restriction-countries-all age_restriction-iphone-switch"><label for="age_restriction-countries-all" class="age_restriction-select-all">' . __('(check all)', 'age-restriction') . '</label></div>';

			// tabs - header
			$html[] = '<ul class="conditions-tabs alignleft age_restriction-countries-list">';
			$first = true;
			$checked = '';
			foreach ( $countries as $country_code => $country_name ) {
				$html[] = '<li' . ($first ? ' class="on ui-state-active"' : '') . '><a href="#tabs-' . $country_code . '" class="selectit" title="' . esc_attr( $country_name ) . '" data-country="' . $country_code . '">' . esc_html( $country_name ) . '</a> <input type="checkbox" name="age_restriction-countries[]" value="' . $country_code . '" id="age_restriction-countries-' . $country_code . '"' . $checked . ' class="age_restriction-iphone-switch" /></li>';
				if ( $first ) $first = false;
			}
			$html[] = '</ul>';
			
			ob_start();
?>
			<style>#age_restriction-advanced-search {display: none;}</style>
			<?php ?><link rel='stylesheet' href='<?php echo $this->module_folder;?>app.discount.css' type='text/css' media='all' /><?php ?>
			
			<!-- no countries msg -->
			<div style="display: none;" class="age_restriction-msg-nocountries">
				<span>
				<?php echo 'Go to the <a href="#amazon" class="age_restriction-link-nocountries">Amazon</a> tab and enter <strong>Your Affiliate IDs</strong> to display countries.'; ?>
				</span>
			</div>
			<div style="display: none;" class="age_restriction-msg-affid-goto">
				<span class="age_restriction-msg-affid-goto-wrap">
				<?php echo 'Go to the <a href="#{country_code}" class="age_restriction-link-affid-goto" data-country_code="{country_code}">{country_name}</a> tab and choose your country settings. {amz_global}'; ?>
				</span>
			</div>
			<div style="display: none;" class="age_restriction-msg-affkeys-goto">
				<span class="age_restriction-msg-affkeys-goto-wrap">
				<?php echo '{amz_global}'; ?>
				</span>
			</div>
			
			<div id="age_restriction-advanced-search">
				<!-- Main loading box -->
				<div id="main-loading">
					<div id="loading-overlay"></div>
					<div id="loading-box">
						<div class="loading-text">Loading</div>
						<div class="meter animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
					</div>
				</div>
				
				<table id="age_restriction-layout-table" border="0" width="100%" cellspacing="0" cellpadding="15">
				</table>
			</div>
<?php
			$html[] = ob_get_clean();
			
			$html[] = '</div></div>';
			$html[] = '<script>jQuery(document).ready(function(){
				jQuery( ".age_restriction-discount-select .age_restriction-conditions.tabs" ).tabs();
			})</script>';
			
			return implode(PHP_EOL, $html);
		}

		public function advanced_search() {
			$html = '';
			$request = array(
				'country' => isset($_REQUEST['country']) ? $_REQUEST['country'] : ''
			);

			ob_start();
?>
				<tbody>
					<tr>
						<td class="col1" width="25%" style="vertical-align: top; min-width: 200px;">
							<ul class="age_restriction-categories-list">
								<?php $checked_all = ''; ?>
								<li><input type="checkbox" name="age_restriction-categories-all" id="age_restriction-categories-all" value="on" class="age_restriction-categories-all age_restriction-iphone-switch" <?php echo $checked_all; ?> /><label for="age_restriction-categories-all" class="age_restriction-select-all"><?php _e('(check all)', 'age-restriction');?></label></li>
								<?php 
								$categs = $this->amzHelper->getAmazonCategs( $request['country'] );
								if( count($categs) > 0 ) {
									$first = true;
									foreach ($categs as $key => $value){
										$__chk_name = 'age_restriction-categories[]';
										$__chk_id = 'age_restriction-categories-' . $request['country'] . '-' . $value;
										$checked = '';
										$categ_name = $this->the_plugin->__category_nice_name($key);
								?>
										<li <?php echo $first ? 'class="on"' : ''; ?>><input type="checkbox" name="<?php echo $__chk_name; ?>" id="<?php echo $__chk_id; ?>" value="on" <?php echo $checked; ?> class="age_restriction-iphone-switch" /><a href="#<?php echo $key;?>" data-categ="<?php echo $key;?>" data-nodeid="<?php echo $value;?>"><?php echo $categ_name;?></a></li>
								<?php
										if ( $first ) $first = false;
									}	
								}
								?>
								<li><input type="checkbox" name="age_restriction-categories-all" id="age_restriction-categories-all2" value="on" class="age_restriction-categories-all age_restriction-iphone-switch" <?php echo $checked_all; ?> /><label for="age_restriction-categories-all2" class="age_restriction-select-all"><?php _e('(check all)', 'age-restriction');?></label></li>
							</ul>
						</td>
						<td class="col2" width="75%"  style="vertical-align: top; min-width: 400px;">
							<div class="age_restriction-parameters-list" id="age_restriction-parameters-container"> <p>loading ...</p></div>
						</td>
					</tr>
				</tbody>
<?php
			$html = ob_get_clean();
			
			die(json_encode(array(
				'status' 	=> 'valid',
				'html'		=> $html
			)));
		}

		public function age_restrictionCategParameters() {
			$html = array();
			$request = array(
				'categ' 	=> isset($_REQUEST['categ']) ? $_REQUEST['categ'] : '',
				'nodeid' 	=> isset($_REQUEST['nodeid']) ? $_REQUEST['nodeid'] : '',
				'country' 	=> isset($_REQUEST['country']) ? $_REQUEST['country'] : '',
				'postid'	=> isset($_REQUEST['postid']) ? $_REQUEST['postid'] : '',
				
				'title'		=> isset($_REQUEST['title']) ? $_REQUEST['title'] : ''
			);
  
  			$country = $request['country'];
			$convertCountry = $this->the_plugin->discount_convert_country2country();
			$country_fromip = '';
			if ( in_array($country, array_keys($convertCountry)) ) {
				$country_fromip = $convertCountry['fromip']["$country"];
			}

			$this->amzHelper->setupAmazonWS( $country_fromip );

			// retrive the item search parameters
			$ItemSearchParameters = $this->amzHelper->getAmazonItemSearchParameters( $request['country'] );
			
			// retrive the item search parameters
			$ItemSortValues = $this->amzHelper->getAmazonSortValues( $request['country'] );
			
			$sort = array();
			$sort['relevancerank'] = 'Items ranked according to the following criteria: how often the keyword appears in the description, where the keyword appears (for example, the ranking is higher when keywords are found in titles), how closely they occur in descriptions (if there are multiple keywords), and how often customers purchased the products they found using the keyword.';
			$sort['salesrank'] = "Bestselling";
			$sort['pricerank'] = "Price: low to high";
			$sort['inverseprice'] = "Price: high to low";
			$sort['launch-date'] = "Newest arrivals";
			$sort['-launch-date'] = "Newest arrivals";
			$sort['sale-flag'] = "On sale";
			$sort['pmrank'] = "Featured items";
			$sort['price'] = "Price: low to high";
			$sort['-price'] = "Price: high to low";
			$sort['reviewrank'] = "Average customer review: high to low";
			$sort['titlerank'] = "Alphabetical: A to Z";
			$sort['-titlerank'] = "Alphabetical: Z to A";
			$sort['pricerank'] = "Price: low to high";
			$sort['inverse-pricerank'] = "Price: high to low";
			$sort['daterank'] = "Publication date: newer to older";
			$sort['psrank'] = "Bestseller ranking taking into consideration projected sales.The lower the value, the better the sales.";
			$sort['orig-rel-date'] = "Release date: newer to older";
			$sort['-orig-rel-date'] = "Release date: older to newer";
			$sort['releasedate'] = "Release date: newer to older";
			$sort['-releasedate'] = "Release date: older to newer";
			$sort['songtitlerank'] = "Most popular";
			$sort['uploaddaterank'] = "Date added";
			$sort['-video-release-date'] = "Release date: newer to older";
			$sort['-edition-sales-velocity'] = "Quickest to slowest selling products.";
			$sort['subslot-salesrank'] = "Bestselling";
			$sort['release-date'] = "Sorts by the latest release date from newer to older. See orig-rel-date, which sorts by the original release date.";
			$sort['-age-min'] = "Age: high to low";
		
			// print the title
			$html[] = '<h2>category: ' . ( $request['title'] ) . '</h2>';
			$html[] = 	'<div class="age_restrictionParameterSection"><input type="checkbox" name="age_restriction-parameter-all" id="age_restriction-parameter-all" value="on" class="age_restriction-parameter-all age_restriction-iphone-switch"><label for="age_restriction-parameter-all" class="age_restriction-select-all">' . __('(check all)', 'age-restriction') . '</label>';

			// store categ into input, use in search FORM
			$html[] = '<input type="hidden" name="age_restrictionParameter[categ]" id="age_restrictionParameter[categ]" value="' . ( $request['categ'] ) . '" /></div>';
			
			if ( isset($request['postid']) && !empty($request['postid']) ) {
				$post_id = $request['postid'];
				$banner_content = get_post_field('post_content', $post_id);
				$banner_content = unserialize($banner_content);
				$banner_content = json_decode($banner_content);
			}
			
			// MinPercentageOff / Discount
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MinPercentageOff', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[MinPercentageOff]" id="age_restrictionParameter-on[MinPercentageOff]" value="on" data-param="MinPercentageOff" class="age_restriction-iphone-switch">';
				// Min Percentage Off
				$html[] = 	'<label for="age_restrictionParameter-on[MinPercentageOff]">' . __('Discount', 'age-restriction') .'</label>';
				$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[MinPercentageOff]" data-param="MinPercentageOff">';
				$html[] = 	'<p>Specifies the minimum percentage off for the items to return.</p>';
				$html[] = '</div>';
			}
		
			// Keywords
			$html[] = '<div class="age_restrictionParameterSection">';
			$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[Keywords]" id="age_restrictionParameter-on[Keywords]" value="on" data-param="Keywords" class="age_restriction-iphone-switch">';
			$html[] = 	'<label for="age_restrictionParameter-on[Keywords]">' . __('Keywords', 'age-restriction') .'</label>';
			$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[Keywords]" data-param="Keywords">';
			$html[] = '</div>';
			
			// BrowseNode
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'BrowseNode', $ItemSearchParameters[$request['categ']] ) ){
				
				$nodes = $this->the_plugin->getBrowseNodes( $request['nodeid'] );
				
				$__childs_ids = $this->the_plugin->find( $request['country'], $request['nodeid'], 'node', array('obj' => $banner_content) );
				$childs_ids = '';
				if ( isset($__childs_ids->level3->value) )
					$childs_ids = (string) $__childs_ids->level3->value;
				$childs_ids = !empty($childs_ids) ? explode(',', $childs_ids) : array();
				
				$node_parent_options = array();
				foreach ($nodes as $key => $value){
					$is_checked = false;
					if( isset($value['BrowseNodeId']) && trim($value['BrowseNodeId']) != "" ) {
						if ( isset($value['BrowseNodeId'], $childs_ids[0]) && $value['BrowseNodeId'] == $childs_ids[0] )
							$is_checked = true;
						$node_parent_options[] = '<option value="' . ( $value['BrowseNodeId'] ) . '"' . ($is_checked ? ' selected="selected"' : '') . '>' . ( $value['Name'] ) . '</option>';
					}
				}
				
				if ( !empty($node_parent_options) ) {

				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[node]" id="age_restrictionParameter-on[node]" value="on" data-param="node" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[node]">' . __('BrowseNode', 'age-restriction') .'</label>';
		
				$html[] = 	'<div id="age_restrictionGetChildrens">';

				if ( !empty($node_parent_options) ) {
					$html[] = '<select name="age_restrictionParameter[node]" data-param="node">';
					$html[] = 	'<option value="">' . __('All', 'age-restriction') .'</option>';
					$html[] = implode("\n", $node_parent_options);
					$html[] = '</select>';
					
					$node_childs = '';
  					if ( !empty($childs_ids) ) {
						$node_childs = $this->age_restrictionGetChildNodes(true, implode(',', $childs_ids), $request['country']);
					}
					if ( !empty($node_childs) ) $html[] = $node_childs;
				}
  
				$html[] =	'</div>';
				$html[] = 	'<p>Browse nodes are identify items categories</p>';
				$html[] = '</div>';
				
				}
			}
		
			// Brand
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'Brand', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[Brand]" id="age_restrictionParameter-on[Brand]" value="on" data-param="Brand" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[Brand]">' . __('Brand', 'age-restriction') .'</label>';
				$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[Brand]" data-param="Brand">';
				$html[] = 	'<p>Name of a brand associated with the item. You can enter all or part of the name. For example, Timex, Seiko, Rolex. </p>';
				$html[] = '</div>';
			}
		
			// Condition
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'Condition', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[Condition]" id="age_restrictionParameter-on[Condition]" value="on" data-param="Condition" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[Condition]">' . __('Condition', 'age-restriction') .'</label>';
				$html[] = 	'<select name="age_restrictionParameter[Condition]" data-param="Condition">';
				$html[] = 		'<option value="">All Conditions</option>';
				$html[] = 		'<option value="New">New</option>';
				$html[] = 		'<option value="Used">Used</option>';
				$html[] = 		'<option value="Collectible">Collectible</option>';
				$html[] = 		'<option value="Refurbished">Refurbished</option>';
				$html[] = 	'</select>';
				$html[] = 	'<p>Use the Condition parameter to filter the offers returned in the product list by condition type. By default, Condition equals "New". If you do not get results, consider changing the value to "All. When the Availability parameter is set to "Available," the Condition parameter cannot be set to "New."</p>';
				$html[] = '</div>';
			}
		
			// Manufacturer
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'Manufacturer', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[Manufacturer]" id="age_restrictionParameter-on[Manufacturer]" value="on" data-param="Manufacturer" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[Manufacturer]">' . __('Manufacturer', 'age-restriction') .'</label>';
				$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[Manufacturer]" data-param="Manufacturer">';
				$html[] = 	'<p>Name of a manufacturer associated with the item. You can enter all or part of the name.</p>';
				$html[] = '</div>';
			}
		
			// MaximumPrice
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MaximumPrice', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[MaximumPrice]" id="age_restrictionParameter-on[MaximumPrice]" value="on" data-param="MaximumPrice" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[MaximumPrice]">' . __('Maximum Price', 'age-restriction') .'</label>';
				$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[MaximumPrice]" data-param="MaximumPrice">';
				$html[] = 	'<p>Specifies the maximum price of the items in the response. Prices are in terms of the lowest currency denomination, for example, pennies. For example, 3241 represents $32.41.</p>';
				$html[] = '</div>';
			}
		
			// MinimumPrice
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MinimumPrice', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[MinimumPrice]" id="age_restrictionParameter-on[MinimumPrice]" value="on" data-param="MinimumPrice" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[MinimumPrice]">' . __('Minimum Price', 'age-restriction') .'</label>';
				$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[MinimumPrice]" data-param="MinimumPrice">';
				$html[] = 	'<p>Specifies the minimum price of the items to return. Prices are in terms of the lowest currency denomination, for example, pennies, for example, 3241 represents $32.41.</p>';
				$html[] = '</div>';
			}
		
			// MerchantId
			if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MerchantId', $ItemSearchParameters[$request['categ']] ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[MerchantId]" id="age_restrictionParameter-on[MerchantId]" value="on" data-param="MerchantId" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[MerchantId]">' . __('Merchant Id', 'age-restriction') .'</label>';
				$html[] = 	'<input type="text" size="22" name="age_restrictionParameter[MerchantId]" data-param="MerchantId">';
				$html[] = 	'<p>An optional parameter you can use to filter search results and offer listings to only include items sold by Amazon. By default, Product Advertising API returns items sold by various merchants including Amazon. Use the Amazon to limit the response to only items sold by Amazon.</p>';
				$html[] = '</div>';
			}
		
			// Sort
			if( ( (int) $request['categ'] > 1 ) && ( $request['categ'] != "All" ) ){
				$html[] = '<div class="age_restrictionParameterSection">';
				$html[] = 	'<input type="checkbox" name="age_restrictionParameter-on[Sort]" id="age_restrictionParameter-on[Sort]" value="on" data-param="Sort" class="age_restriction-iphone-switch">';
				$html[] = 	'<label for="age_restrictionParameter-on[Sort]">' . __('Sort', 'age-restriction') .'</label>';
				$html[] = 	'<select name="age_restrictionParameter[Sort]" class="age_restrictionParameter-sort" data-param="Sort">';
		
				$curr_sort = array();
				if(isset($ItemSortValues[$request['categ']])){
					$curr_sort = $ItemSortValues[$request['categ']];
				}
		
				$first_sort_key = '';
				$first_sort_desc = '';
				$cc = 0; 
				foreach ( $sort as $key => $value ){
					if( isset($curr_sort) && in_array( $key, $curr_sort) ){
						if( $cc == 0 ){
							$first_sort_key = $key;
							$first_sort_desc = $value;
						}
		
						$html[] = '<option value="'. ( $key ) .'" data-desc="'. ( str_replace('"', "'", $value) ) .'">'. ( $key ) .'</option>';
		
						$cc++;
					}
				}
		
				$html[] = 	'</select>';
				$html[] = 	'<p id="age_restrictionOrderDesc" style="width: 100%;">' . ( "<strong>" . ( $first_sort_key ) . ":</strong> " . $first_sort_desc ) . '</p>';
				$html[] = 	'<p>Means by which the items in the response are ordered.</p>';
				$html[] = '</div>';
			}

			// print the title
			$html[] = 	'<div class="age_restrictionParameterSection"><input type="checkbox" name="age_restriction-parameter-all" id="age_restriction-parameter-all2" value="on" class="age_restriction-parameter-all age_restriction-iphone-switch"><label for="age_restriction-parameter-all2" class="age_restriction-select-all">' . __('(check all)', 'age-restriction') . '</label>';
			$html[] = '</div>';
			$html[] = '<h2>category: ' . ( $request['title'] ) . '</h2>';
			
			die(json_encode(array(
				'status' 	=> 'valid',
				'html'		=> implode("\n", $html)
			)));
		}
		
		public function age_restrictionGetChildNodes( $ret=false, $nodeidList=0, $country='' ) {
			$request = array(
				'country' 			=> isset($_REQUEST['country']) ? $_REQUEST['country'] : ''
			);
			
			if ( !empty($request['country']) )
				$country = $request['country'];
				
			$convertCountry = $this->the_plugin->discount_convert_country2country();
			$country_fromip = '';
			if ( in_array($country, array_keys($convertCountry)) ) {
				$country_fromip = $convertCountry['fromip']["$country"];
			}

			$this->amzHelper->setupAmazonWS($country_fromip);
		
			$request = array(
				'nodeid' => isset($_REQUEST['ascensor']) ? $_REQUEST['ascensor'] : ''
			);
			if ( isset($nodeidList) && !empty($nodeidList) )
				$request['nodeid'] = explode(',', $nodeidList);
		
			if ( !is_array($request['nodeid']) )
				$request['nodeid'] = (array) $request['nodeid'];
  
		 	$html = array();
			foreach ( $request['nodeid'] as $kk => $vv ) {
			$nodes = $this->the_plugin->getBrowseNodes( $vv/*$request['nodeid']*/ );
  			
			$has_nodes = false;
			foreach ($nodes as $key => $value){
				if( isset($value['BrowseNodeId']) && trim($value['BrowseNodeId']) != "" ) {
					if ( !$has_nodes ) {
						$html[] = '<select name="age_restrictionParameter[node]" style="margin: 10px 0px 0px 0px;" data-param="node">';
						$html[] = 	'<option value="">' . __('All', 'age-restriction') .'</option>';
					}
					
					$is_checked = false;
					if ( isset($request['nodeid'][$kk+1], $value['BrowseNodeId'])
						&& $request['nodeid'][$kk+1] == $value['BrowseNodeId'] ) {
						$is_checked = true;
					}
					$html[] = '<option value="' . ( $value['BrowseNodeId'] ) . '"' . ($is_checked ? ' selected="selected"' : '') . '>' . ( $value['Name'] ) . '</option>';
					
					$has_nodes = true;
				}
			}
			
			if ( $has_nodes ) {
				$html[] = '</select>';
			}

			} // end foreach - per nodes
  
			if ( $ret ) return implode("\n", $html);
			die(json_encode(array(
				'status' 	=> 'valid',
				'html'		=> implode("\n", $html)
			)));
		}

		/**
		 * Save Discount Settings for banner
		 */
		public function save_discount() {
			$post_id = $this->postid;

			$settings = isset($_REQUEST['age_restriction-discount-saveobj']) ? $_REQUEST['age_restriction-discount-saveobj'] : '';
			$settings_new = array();
			
			$settings_new = stripslashes($settings);
			$settings_new = serialize($settings_new);
			
			$this->update_post_content( $post_id, $settings_new );
			return $settings;
		}

		public function update_post_content($postid, $content) {
			global $wpdb;
			
			$wpdb->update(
				$wpdb->prefix.'posts',
				array('post_content' => $content),
				array('ID' => $postid, 'post_type' => 'age_restriction_banners')
			);
		}
		

		/**
		 * Extra
		 */
		public function set_banner_postid($postid) {
			$this->postid = (int) $postid;
		}
		
		private function build_tab_help() {
			$html = array();

			require_once('params.inc.php');
			$html[] = '<ul class="alignleft conditions-column">';
			foreach ($age_restriction_amz_search_params as $key => $val ) {
				$html[] = '<li><code>' . $val['title'] . '</code>
				<br />
				' . $val['desc'] . '
				</li>';
			}
			$html[] = '</ul>';
			
			return implode(PHP_EOL, $html);
		}
	}
}

//new age_restrictionBannersPerSections();