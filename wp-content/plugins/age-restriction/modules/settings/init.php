<?php
/**
 * Init
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1
 */

// load metabox
if(	is_admin() ) {
	require_once( 'ajax-request.php' );

    /* Adds a custom section to the "side" of the product edit screen */
    function age_restriction_api_search_metabox() {
		add_meta_box('age_restriction_api_search', 'Search product(s) on Amazon', 'age_restriction_api_search_custom_box', 'product', 'normal', 'high');
    }

	/* The code for api search custom metabox */
	function age_restriction_api_search_custom_box() {
		global $age_restriction;
   
		$amazon_settings = $age_restriction->getAllSettings('array', 'amazon');
		$plugin_uri = $age_restriction->cfg['paths']['plugin_dir_url'] . 'modules/amazon/';
	?>
		<link rel='stylesheet' id='age_restriction-metabox-css' href='<?php echo $plugin_uri . 'meta-box.css';?>' type='text/css' media='all' />

		<script type='text/javascript' src='<?php echo $plugin_uri . 'meta-box.js';?>'></script>

		</form> <!-- closing the top form -->
			<form id="age_restriction-search-form" action="/" method="POST">
			<div style="bottom: 0px; top: 0px;" class="age_restriction-shadow"></div>
			<div id="age_restriction-search-bar">
				<div class="age_restriction-search-content">
					<div class="age_restriction-search-block">
						<label for="age_restriction-search">Search by Keywords or ASIN:</label>
						<input type="text" name="age_restriction-search" id="age_restriction-search" value="" />
					</div>

					<div class="age_restriction-search-block" style="width: 220px">
						<span class="caption">Category:</span>
						<select name="age_restriction-category" id="age_restriction-category">
						<?php
							foreach ($age_restriction->amazonCategs() as $key => $value){
								echo '<option value="' . ( $value ) . '">' . ( $value ) . '</option>';
							}
						?>
						</select>
					</div>

					<div class="age_restriction-search-block" style="width: 320px">
						<span>Import to category:</span>
						<?php
						$args = array(
							'orderby' 	=> 'menu_order',
							'order' 	=> 'ASC',
							'hide_empty' => 0
						);
						$categories = get_terms('product_cat', $args);
						echo '<select name="age_restriction-to-category" id="age_restriction-to-category" style="width: 200px;">';
						echo '<option value="amz">Use category from Amazon</option>';
						if(count($categories) > 0){
							foreach ($categories as $key => $value){
								echo '<option value="' . ( $value->name ) . '">' . ( $value->name ) . '</option>';
							}
						}
						echo '</select>';
						?>
					</div>

					<input type="submit" class="button-primary" id="age_restriction-search-link" value="Search" />
				</form>
				<div id="age_restriction-ajax-loader"><img src="<?php echo $plugin_uri;?>assets/ajax-loader.gif" /> searching on <strong>Amazon.<?php echo $amazon_settings['country'];?></strong> </div>
			</div>
		</div>
		<div id="age_restriction-results">
			<div id="age_restriction-ajax-results"><!-- dynamic content here --></div>
			<div style="clear:both;"></div>
		</div>

		<?php
		if($_REQUEST['action'] == 'edit'){
			echo '<style>#amzStore_shop_products_price, #amzStore_shop_products_markers { display: block; }</style>';
		}
		?>
	<?php
	}
}
require_once( 'product-tabs.php' );