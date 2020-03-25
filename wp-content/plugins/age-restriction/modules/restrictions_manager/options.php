<?php
/**
 * module return as json_encode
 * http://www.aa-team.com
 * ======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
 
function age_restriction_stats_html() {
	global $age_restriction;
	
	$frm_folder = $age_restriction->cfg['paths']['freamwork_dir_url'];
	$module_folder = $age_restriction->cfg['paths']['plugin_dir_url'] . 'modules/restrictions_manager/';
   
	$args_banners_list = array(
  		'post_type' 			=> 'age_restriction',
  		'post_status' 			=> 'publish',
  		'posts_per_page'		=> -1,
  		//'caller_get_posts'		=> 1,
  		//'ignore_sticky_posts '	=> 1,
  		'orderby'         		=> 'post_date',
  		'order'          		=> 'ASC',
  		'suppress_filters' 		=> true
	);
	$args_banners_list = array_filter($args_banners_list);
	$banners_list = get_posts( $args_banners_list );
	$banners_list_len = (int) count($banners_list);
	
	if ( $banners_list_len <= 0 ) return '
	<div class="age_restriction-form-row" style="position:relative;">no banners available!</div>
	';

	ob_start();
?>
	<div class="age_restriction-form-row" id="age_restriction-choose-banner-wrap" style="position:relative;">
		<input type="hidden" id="age_restriction-postid" name="age_restriction-postid" value="" />
		<label for="age_restriction-choose-banner" style="display:inline; float:none;"><?php echo __('Select Banner', 'age-restriction');?>:</label>
		&nbsp;
		<select id="age_restriction-choose-banner" name="age_restriction-choose-banner" style="width:160px;">
			<option value="all">All banners</option>
			<?php
			foreach ($banners_list as $vv){
				$banner_id = $vv->ID;
				$banner_name = $banner_id . ' - ' . $vv->post_title;
				echo '<option value="' . ( $banner_id ) . '" ' . ( 0 ? 'selected="true"' : '' ) . '>' . ( $banner_name ) . '</option>';
			} 
			?>
		</select>
	</div>
		
	<div class="age_restriction-form-row" id="age_restriction-banner-stats" style="position:relative;"></div>
	
	<!-- admin css/js -->
	<link rel='stylesheet' href='<?php echo $module_folder;?>app.css' type='text/css' media='screen' />
	<link rel='stylesheet' href='<?php echo $module_folder;?>assets/flags/flags.css' type='text/css' media='screen' />
	
	<script type="text/javascript">
		var age_restriction_stats_loc = 'admin_options';
	</script>
	<script type="text/javascript" src="<?php echo $module_folder;?>charts.js" ></script>
	<script type="text/javascript" src="<?php echo $module_folder;?>app.class.js" ></script>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

global $age_restriction;
echo json_encode(
	array(
		$tryed_module['db_alias'] => array(
			/* define the form_messages box */
			'restrictions_manager' => array(
				'title' 	=> __('Restrictions Manager', 'age-restriction'),
				'icon' 		=> '{plugin_folder_uri}assets/menu_icon.png',
				'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
				'header' 	=> true, // true|false
				'toggler' 	=> false, // true|false
				'buttons' 	=> false, // true|false
				'style' 	=> 'panel', // panel|panel-widget

				// create the box elements array
				'elements'	=> array(
					'default_html' => array(
						'type' 		=> 'html',
						'html' 		=> age_restriction_stats_html()
					)
				)
			)
			
		)
	)
);