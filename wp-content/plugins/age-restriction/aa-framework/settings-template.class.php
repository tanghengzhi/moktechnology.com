<?php
/*
* Define class aaInterfaceTemplates
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
! defined( 'ABSPATH' ) and exit;

if(class_exists('aaInterfaceTemplates') != true) {

	class aaInterfaceTemplates {

		/*
		* Some required plugin information
		*/
		const VERSION = '1.0';
		
		/*
		* Store some helpers config
		* 
		*/
		public $cfg	= array();

		/*
		* Required __construct() function that initalizes the AA-Team Framework
		*/
		public function __construct($cfg) 
		{
			$this->cfg = $cfg;   
		}
		
		
		/*
		* bildThePage, method
		* -------------------
		*
		* @params $options = array (requiered)
		* @params $alias = string (requiered)
		* this will create you interface via options array elements
		*/
		public function bildThePage ( $options = array(), $alias='', $module=array(), $showForm=true ) 
		{
			global $age_restriction;
			
			// reset as array, this will stock all the html content, and at the end return it
			$html = array();
  
			if(count($options) == 0) {
				return 'Please fill whit some options content first!';
			}
			
			$noRowElements = array('message', 'html', 'app');
			
			foreach ( $options as $theBoxs ) {
				
				// loop the all the boxs
				foreach ( $theBoxs as $box_id => $box ){
					
					$box_id = $alias . "_" . $box_id;
					$settings = array();
					
					// get the values from DB
					$dbValues = get_option($box_id);
					
					// check if isset and string have content
					if(isset($dbValues) && @trim($dbValues) != ""){
						$settings = maybe_unserialize($dbValues);
					}
					
					// create defalt setup for each header, prevent php notices
					if(!isset($box['header'])) $box['header']= false;
					if(!isset($box['toggler'])) $box['toggler']= false;
					if(!isset($box['buttons'])) $box['buttons']= false;
					if(!isset($box['style'])) $box['style']= 'panel';
					
					$box_show_wrappers = true;
					if ( !isset($box['panel_setup_verification']) )
						$box['panel_setup_verification'] = false;
					
					if ( $box['panel_setup_verification'] ) {

						$tryLoadInterface = str_replace("{plugin_folder_path}", $module["folder_path"], $box['elements'][0]['path']);
									
						if(is_file($tryLoadInterface)) {
							// Turn on output buffering
							ob_start();
										
							require( $tryLoadInterface  );
  
							if ( isset($__module_is_setup_valid) && $__module_is_setup_valid !==true ) {
								$box_show_wrappers = false;
							}
									
							//copy current buffer contents into $message variable and delete current output buffer
							$__error_msg_panel = ob_get_clean();
						}
					}
  
					if ( $box_show_wrappers ) {
					// container setup
					$html[] = '<div class="age_restriction-' . ( $box['size'] ) . '">
                        	<div class="age_restriction-' . ( $box['style'] ) . '">';
							
					// hide panel header only if it's requested
					if( $box['header'] == true ) {
						$html[] = '<div class="age_restriction-panel-header">
							<span class="age_restriction-panel-title">
								' . ( isset($box['icon']) ? '<img src="' . ( $box['icon'] ) . '" />' : '' ) . '
								' . ( $box['title'] ) . '
							</span>
							 ' . ( $box['toggler'] == true ? '<span class="age_restriction-panel-toggler"></span>' : '' ) . '
						</div>';
					}
						
					$html[] = '<div class="age_restriction-panel-content">';
					if($showForm){
						$html[] = '<form class="age_restriction-form" id="' . ( $box_id ) . '" action="#save_with_ajax">';
					}
					
					// create a hidden input for sending the prefix
					$html[] = '<input type="hidden" id="box_id" name="box_id" value="' . ( $box_id ) . '" />';
					
					$html[] = '<input type="hidden" id="box_nonce" name="box_nonce" value="' . ( wp_create_nonce( $box_id . '-nonce') ) . '" />';
					} // end if show box wrappers

					$html[] = $this->tabsHeader($box); // tabs html header

					// loop the box elements
					if(count($box['elements']) > 0){
					
						// loop the box elements now
						foreach ( $box['elements'] as $elm_id => $value ){

							// some helpers. Reset an each loop, prevent collision
							$val = '';
							$select_value = '';
							$checked = '';
							$option_name = isset($option_name) ? $option_name : '';
							
							// Set default value to $val
							if ( isset( $value['std']) ) {
								$val = $value['std'];
							}
							
							// If the option is already saved, ovveride $val
							if ( ( $value['type'] != 'info' ) ) {
								if ( isset($settings[($elm_id)] )
									&& (
										( !is_array($settings[($elm_id)]) && @trim($settings[($elm_id)]) != "" )
										||
										( is_array($settings[($elm_id)]) /*&& !empty($settings[($elm_id)])*/ )
									)
								) {
										$val = $settings[( $elm_id )];
										
										// Striping slashes of non-array options
										if ( !is_array($val) ) {
											$val = stripslashes( $val );
											if($val == '') $val = true;
										}
								}
							}
							
							// If there is a description save it for labels
							$explain_value = '';
							if ( isset( $value['desc'] ) ) {
								$explain_value = $value['desc'];
							}
							
							if ( isset($value['cssclass']) && !empty($value['cssclass']) ) {
								$__tmp = explode(',', $value['cssclass']);
								$value['cssclass_wrap'] = implode(' ', array_map(array($this, "do_prefix"), $__tmp));
								$value['cssclass'] = implode(' ', $__tmp);
							}
							
							if(!in_array( $value['type'], $noRowElements)){
								// the row and the label 
								$html[] = '<div class="age_restriction-form-row' . ($this->tabsElements($box, $elm_id)) . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass_wrap'] . '' : '' ) . '">
									   <label for="' . ( $elm_id ) . '">' . ( isset($value['title']) ? $value['title'] : '' ) . '</label>
									   <div class="age_restriction-form-item'. ( isset($value['size']) ? " " . $value['size'] : '' ) .'">';
							}
							
							// the element description
							if(isset($value['desc'])) $html[]	= '<span class="formNote">' . ( $value['desc'] ) . '</span>';
							
							switch ( $value['type'] ) {
								
								// Basic text input
								case 'text':
									$html[] = '<input ' . ( isset($value['readonly']) && $value['readonly'] == true ? 'readonly ' : '' ) . ' ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="text" value="' . esc_attr( $val ) . '" ' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? 'class="' . $value['cssclass'] . '" ' : '' ) . '/>';
								break;
								
								// Basic checkbox input
								case 'checkbox':
									if($val == '') $val = true;
									$html[] = '<input ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' ' . ( $val == true ? 'checked' : '' ). ' id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="checkbox" value="" ' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? 'class="' . $value['cssclass'] . '" ' : '' ) . '/>';
								break;
								
								// Basic upload_image
								case 'upload_image':
									$html[] = '<table border="0" ' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? 'class="' . $value['cssclass'] . '" ' : '' ) . '>';
									$html[] = '<tr>';
									$html[] = 	'<td>';
									$html[] = 		'<input class="upload-input-text" name="' . ( $elm_id ) . '" id="' . ( $elm_id ) . '_upload" type="text" value="' . ( $val ) . '" />';
									
									$html[] = 		'<script type="text/javascript">
										jQuery("#' . ( $elm_id ) . '_upload").data({
											"w": ' . ( $value['thumbSize']['w'] ) . ',
											"h": ' . ( $value['thumbSize']['h'] ) . ',
											"zc": ' . ( $value['thumbSize']['zc'] ) . '
										});
									</script>';
									
									$html[] = 	'</td>';
									$html[] = '<td>';
									$html[] = 		'<a href="#" class="button upload_button" id="age_restriction_' . ( $elm_id ) . '">' . ( $value['value'] ) . '</a> ';
									//$html[] = 		'<a href="#" class="button reset_button ' . $hide . '" id="reset_' . ( $elm_id ) . '" title="' . ( $elm_id ) . '">Remove</a> ';
									$html[] = '</td>';
									$html[] = '</tr>';
									$html[] = '</table>';
									
									$html[] = '<a class="thickbox' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] . '' : '' ) . '" id="uploaded_image_' . ( $elm_id ) . '" href="' . ( $val ) . '" target="_blank">';
									
									if(!empty($val)){
										$imgSrc = $age_restriction->image_resize( $val, $value['thumbSize']['w'], $value['thumbSize']['h'], $value['thumbSize']['zc'] );
										$html[] = '<img style="border: 1px solid #dadada;" id="image_' . ( $elm_id ) . '" src="' . ( $imgSrc ) . '" />';
									}
									$html[] = '</a>';
									
									$html[] = 		'<script type="text/javascript">
										age_restriction_loadAjaxUpload( jQuery("#age_restriction_' . ( $elm_id ) . '") );
									</script>';
									
								break;
								
								// Basic upload_image
								case 'upload_image_wp':
									$image = ''; $image_full = '';

									$preview_size = (isset($value['preview_size']) ? $value['preview_size'] : 'thumbnail');  
									
									if( (int) $val > 0 ){
										$image = wp_get_attachment_image_src( $val, $preview_size );
										$image_full = wp_get_attachment_image_src( $val, 'full' );
										if( count($image) > 0 ){
											$image = $image[0];
										}
										
										if( count($image_full) > 0 ){
											$image_full = $image_full[0];
										}
									}
									else{
										if( trim($val) != "" ){
											$image_full = $val;
											$image = $val;
										}
									}
									
									$html[] = '<div class="age_restriction-upload-image-wp-box">';
									$html[] = 	'<a data-previewsize="' . ( $preview_size ) . '" class="age_upload_image_button_wp age_restriction-button blue" ' . ( isset($value['force_width']) ? "style='" . ( trim($val) != "" ? 'display:none;' : '' ) . "width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' href="#">' . ( $value['value'] ) . '</a>';
									$html[] = 	'<input type="hidden" name="' . ( $elm_id ) . '" value="' . ( $val ) . '">';
									if ( !empty($image_full) )
										$html[] = 	'<a href="' . ( $image_full ) . '" target="_blank" class="upload_image_preview" style="display: ' . ( trim($val) == "" ? 'none' : 'block' ). '; max-width: 150px; max-height: 150px;">';
									if ( !empty($image) )
										$html[] = 		'<img src="' . ( $image ) . '" style="display: ' . ( trim($val) == "" ? 'none' : 'inline-block' ). '">';	
									$html[] = 	'</a>';
									$html[] =	'<div class="age_restriction-prev-buttons" style="display: ' . ( trim($val) == "" ? 'none' : 'inline-block' ). '">';
									$html[] = 		'<span class="age_change_image_button_wp age_restriction-button green">Change Image</span>';
									$html[] = 		'<span class="remove_image_button_wp age_restriction-button red">Remove Image</span>';
									$html[] =	'</div>';
									$html[] = '</div>';
								break;
								
								// Basic textarea
								case 'textarea-array':
									$textType = 'array';
									
								case 'textarea':
									$cols = "120";
									if(isset($value['cols'])) {
										$cols = $value['cols'];
									}
									$height = "style='height:120px;'";
									if(isset($value['height'])) {
										$height = "style='height:{$value['height']};'";
									}
									
  									if ( isset($textType) && $textType == 'array' )
  										$val = var_export($val, true);
									$val = esc_attr( $val );
									
									$html[] = '<textarea id="' . esc_attr( $elm_id ) . '" ' . $height . ' cols="' . ( $cols ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" ' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? 'class="' . $value['cssclass'] . '" ' : '' ) . '>' . esc_attr( $val ) . '</textarea>';
									
								break;
								
								case 'textarea-wysiwyg':
									$cols = "120";
									if(isset($value['cols'])) {
										$cols = $value['cols'];
									}
									$height = "style='height:120px;'";
									if(isset($value['height'])) {
										$height = "style='height:{$value['height']};'";
									}
									
  									if ( isset($textType) && $textType == 'array' )
  										$val = var_export($val, true);
									
									ob_start();
										wp_editor( $val, esc_attr($elm_id), array('media_buttons' => false) );
										$wp_editor = ob_get_contents();
									ob_end_clean();
									$html[] = $wp_editor;
								break;
								
								// Basic html/text message
								case 'message':
									$html[] = '<div class="age_restriction-message age_restriction-' . ( $value['status'] ) . ' ' . ($this->tabsElements($box, $elm_id)) . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] . '' : '' ) . '">' . ( $value['html'] ) . '</div>';
								break;
								
								// buttons
								case 'buttons':
								
									// buttons for each box
									
									if(count($value['options']) > 0){
										foreach ($value['options'] as $key => $value){
											$html[] = '<input 
												type="' . ( $value['type'] ) . '" 
												style="width:' . ( $value['width'] ) . '" 
												value="' . ( $value['value'] ) . '" 
												class="age_restriction-button ' . ( $value['color'] ) . ' ' . ( isset($value['pos']) ? $value['pos'] : '' ) . ' ' . ( $value['action'] ) . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] . '' : '' ) . '" />';
										}
									}
									
								break;
								
								
								// Basic html/text message
								case 'html':
									$html[] = $value['html'];
								break;
								
								// Basic app, load the path of this file
								case 'app':
									
									$tryLoadInterface = str_replace("{plugin_folder_path}", $module["folder_path"], $value['path']);
									
									if(is_file($tryLoadInterface)) {
										// Turn on output buffering
										ob_start();
										
										require( $tryLoadInterface  );
										
										//copy current buffer contents into $message variable and delete current output buffer
										$html[] = ob_get_clean();
									}
								break;
								
								// Select Box
								case 'select':
									$html[] = '<select ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' name="' . esc_attr( $elm_id ) . '" id="' . esc_attr( $elm_id ) . '" ' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? 'class="' . $value['cssclass'] . '" ' : '' ) . '>';
									
									foreach ($value['options'] as $key => $option ) {
										$selected = '';
										if( $val != '' ) {
											if ( $val == $key || $val == $option ) { $selected = ' selected="selected"';} 
										}
										$html[] = '<option'. $selected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
									 } 
									$html[] = '</select>';
								break;
								
								// multiselect Box
								case 'multiselect':
									$html[] = '<select multiple="multiple" size="3" name="' . esc_attr( $elm_id ) . '[]" id="' . esc_attr( $elm_id ) . '" ' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? 'class="' . $value['cssclass'] . '" ' : '' ) . '>';
									
									if(count($option) > 1){
										foreach ($value['options'] as $key => $option ) {
											$selected = '';
											if( $val != '' ) {
												if ( in_array($key, $val) ) { $selected = ' selected="selected"';} 
											}
											$html[] = '<option'. $selected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
										} 
									}
									$html[] = '</select>';
								break;
								
								// multiselect Box
								case 'multiselect_left2right':

									$available = array(); $selected = array();
									foreach ($value['options'] as $key => $option ) {
										if( $val != '' ) {
											if ( in_array($key, $val) ) { $selected[] = $key; } 
										}
									}
									$available = array_diff(array_keys($value['options']), $selected);
									
									$html[] = '<div class="age_restriction-multiselect-half age_restriction-multiselect-available' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] . '' : '' ) . '" style="margin-right: 2%;">';
									if( isset($value['info']['left']) ){
										$html[] = '<h5>' . ( $value['info']['left'] ) . '</h5>';
									}
									$html[] = '<select multiple="multiple" size="' . (isset($value['rows_visible']) ? $value['rows_visible'] : 5) . '" name="' . esc_attr( $elm_id ) . '-available[]" id="' . esc_attr( $elm_id ) . '-available" class="multisel_l2r_available">';
									
									if(count($available) > 0){
										foreach ($value['options'] as $key => $option ) {
											if ( !in_array($key, $available) ) continue 1;
											$html[] = '<option value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
										} 
									}
									$html[] = '</select>';
									
									$html[] = '</div>';
									
									$html[] = '<div class="age_restriction-multiselect-half age_restriction-multiselect-selected' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] . '' : '' ) . '">';
									if( isset($value['info']['right']) ){
										$html[] = '<h5>' . ( $value['info']['right'] ) . '</h5>';
									}
									$html[] = '<select multiple="multiple" size="' . (isset($value['rows_visible']) ? $value['rows_visible'] : 5) . '" name="' . esc_attr( $elm_id ) . '[]" id="' . esc_attr( $elm_id ) . '" class="multisel_l2r_selected">';
									
									if(count($selected) > 0){
										foreach ($value['options'] as $key => $option ) {
											if ( !in_array($key, $selected) ) continue 1;
											$isselected = ' selected="selected"'; 
											$html[] = '<option'. $isselected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
										} 
									}
									$html[] = '</select>';
									$html[] = '</div>';
									$html[] = '<div style="clear:both"></div>';
									$html[] = '<div class="multisel_l2r_btn' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] . '' : '' ) . '" style="">';
									$html[] = '<span style="display: inline-block; width: 24.1%; text-align: center;"><input id="' . esc_attr( $elm_id ) . '-moveright" type="button" value="Move Right" class="moveright age_restriction-button gray"></span>';
									$html[] = '<span style="display: inline-block; width: 24.1%; text-align: center;"><input id="' . esc_attr( $elm_id ) . '-moverightall" type="button" value="Move Right All" class="moverightall age_restriction-button gray"></span>';
									$html[] = '<span style="display: inline-block; width: 24.1%; text-align: center;"><input id="' . esc_attr( $elm_id ) . '-moveleft" type="button" value="Move Left" class="moveleft age_restriction-button gray"></span>';
									$html[] = '<span style="display: inline-block; width: 24.1%; text-align: center;"><input id="' . esc_attr( $elm_id ) . '-moveleftall" type="button" value="Move Left All" class="moveleftall age_restriction-button gray"></span>';
									$html[] = '</div>';
								break;
								
								case 'date':

									$html[] = '<input ' . ( isset($value['readonly']) && $value['readonly'] == true ? 'readonly' : '' ) . ' ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="text" value="' . esc_attr( $val ) . '" />';
									$html[] = '<input type="hidden" id="' . esc_attr( $elm_id ) . '-format" value="" />';
									
									$defaultDate = '';
									if ( isset($value['std']) && !empty($value['std']) )
										$defaultDate = $value['std'];
									if ( isset($value['defaultDate']) && !empty($value['defaultDate']) )
										$defaultDate = $value['defaultDate'];
										
									$html[] = "<script type='text/javascript'>
										jQuery(document).ready(function($){
										 	// datepicker
										 	var atts = {
												changeMonth:	true,
												changeYear:		true,
												onClose: function() {
													$('input#" . ( $elm_id ) . "').trigger('change');
												}
											};
											atts.dateFormat 	= '" . ( isset($value['format']) && !empty($value['format']) ? $value['format'] : 'yy-mm-dd' ) . "';
											atts.defaultDate 	= '" . ( isset($defaultDate) && !empty($defaultDate) ? $defaultDate : null ) . "';
											atts.altField		= 'input#" . ( $elm_id ) . "-format';
											atts.altFormat		= 'yy-mm-dd';";

									if ( isset($value['yearRange']) && !empty($value['yearRange']) )
										$html[] = "atts.yearRange	= '" . $value['yearRange'] . "';";

									$html[] = "$( 'input#" . ( $elm_id ) . "' ).datepicker( atts ); // end datepicker
										});
									</script>";

									break;

								case 'time':

									$__hourmin_init = array();
									if ( isset($value['std']) && !empty($value['std']) )
										$__hourmin_init = $this->getTimeDefault( $value['std'] );
									if ( isset($value['defaultDate']) && !empty($value['defaultDate']) )
										$__hourmin_init = $this->getTimeDefault( $value['defaultDate'] );
										
									$__hour_range = array();
									if ( isset($value['hour_range']) && !empty($value['hour_range']) )
										$__hour_range = $this->getTimeDefault( $value['hour_range'] );
										
									$__min_range = array();
									if ( isset($value['min_range']) && !empty($value['min_range']) )
										$__min_range = $this->getTimeDefault( $value['min_range'] );
									
									$html[] = '<input ' . ( isset($value['readonly']) && $value['readonly'] == true ? 'readonly' : '' ) . ' ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="text" value="' . esc_attr( $val ) . '" />';
									
									$html[] = "<script type='text/javascript'>
										jQuery(document).ready(function($){
										 	// timepicker
										 	var atts = {};
										 	//atts.timezone = '-0300';
										 	";

									if ( isset($value['ampm']) && ( $value['ampm'] || $value['ampm'] == 'true' ) )
										$html[] = "atts.ampm	= true;";
									else 
										$html[] = "atts.ampm	= false;";

									if ( isset($__hourmin_init) && !empty($__hourmin_init) )
										$html[] = "atts.defaultValue	= '" . $value['std'] . "';";

									if ( isset($__hourmin_init) && !empty($__hourmin_init) )
										$html[] = "atts.hour	= " . $__hourmin_init[0] . ";";
									if ( isset($__hourmin_init) && !empty($__hourmin_init) )
										$html[] = "atts.minute	= " . $__hourmin_init[1] . ";";
									if ( isset($__hour_range) && !empty($__hour_range) )
										$html[] = "atts.hourMin	= " . $__hour_range[0] . ";";
									if ( isset($__hour_range) && !empty($__hour_range) )
										$html[] = "atts.hourMax	= " . $__hour_range[1] . ";";
									if ( isset($__min_range) && !empty($__min_range) )
										$html[] = "atts.minuteMin	= " . $__min_range[0] . ";";
									if ( isset($__min_range) && !empty($__min_range) )
										$html[] = "atts.minuteMax	= " . $__min_range[1] . ";";

									$html[] = "$( 'input#" . ( $elm_id ) . "' ).timepicker( atts ); // end timepicker
										});
									</script>";

									break;
									
								case 'ratestar':

									$html[] = '<input id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="hidden" value="' . esc_attr( $val ) . '" />';
									$html[] = '<div id="rateit-' . esc_attr( $elm_id ) . '"></div>';
									$html[] = "<script type='text/javascript'>
											 jQuery(document).ready(function($){
												$('#rateit-" . ( $elm_id ) . "').rateit({ max: " . ( isset($value['nbstars']) && !empty($value['nbstars']) ? $value['nbstars'] : 10 ) . ", step: 1, backingfld: '#" . ( $elm_id ) . "' });
											});
									</script>";

									break;
									
								case 'color_picker':

									$html[] = '<input ' . ( isset($value['readonly']) && $value['readonly'] == true ? 'readonly' : '' ) . ' ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="text" value="' . esc_attr( $val ) . '" />';
									
									$defaultColor = '';
									if ( isset($value['std']) && !empty($value['std']) )
										$defaultColor = $value['std'];
									if ( isset($value['defaultColor']) && !empty($value['defaultColor']) )
										$defaultColor = $value['defaultColor'];
										
									$html[] = "<script type='text/javascript'>
										jQuery(document).ready(function($){
										 	// color picker
										 	var pickColor = $('input#" . ( $elm_id ) . "'),
										 	__bkcolor = pickColor.data('background_color');
											
											var pickColorOpt = { 
												eventName		: 'click',
												onSubmit		: function(hsb, hex, rgb, el) {
													pickColor.val(hex);
													pickColor.ColorPickerHide();
												},
												onBeforeShow	: function () {
													$(this).ColorPickerSetColor(this.value);
												},
												onChange: function (hsb, hex, rgb) {
													pickColor.css('backgroundColor', '#'+hex);
													pickColor.val(hex);
												}
											};
											if ( typeof __bkcolor != 'undefined' && __bkcolor != null && __bkcolor != '' ) pickColorOpt.color = __bkcolor;
											pickColor
											.ColorPicker( pickColorOpt )
											.bind('keyup', function(){
												$(this).ColorPickerSetColor( this.value );
											});
										});
									</script>";

									break;

								case 'range_input':

									$html[] = '<input ' . ( isset($value['readonly']) && $value['readonly'] == true ? 'readonly' : '' ) . ' ' . ( isset($value['force_width']) ? "style='width:" . ( $value['force_width'] ) . "px;'" : '' ) . ' id="' . esc_attr( $elm_id ) . '" name="' . esc_attr( $option_name . $elm_id ) . '" type="range" min="0" max="100" value="' . esc_attr( $val ) . '" />';
									
									$defaultRangeValue = '';
									if ( isset($value['std']) && !empty($value['std']) )
										$defaultRangeValue = $value['std'] / 100;
									if ( isset($value['defaultRangeValue']) && !empty($value['defaultRangeValue']) )
										$defaultRangeValue = $value['defaultRangeValue'] / 100;
										

									break;
								
								// Select by image
								case 'image_picker':
								  
									$html[] = '<select name="' . esc_attr( $elm_id ) . '" id="' . esc_attr( $elm_id ) . '" class="image-picker show-html' . ( isset($value['cssclass']) && !empty($value['cssclass']) ? ' ' . $value['cssclass'] : '' ) . '">';
									
									foreach ($value['options'] as $key => $option ) {
									
										// try to find theme thumb
										$thumb = '';
										if( is_file( $this->cfg['paths']['design_dir_path'] . '/template_' . $option . '/images/thumb_theme.jpg' ) ){
											$thumb = $this->cfg['paths']['design_dir_url'] . '/template_' . $option . '/images/thumb_theme.jpg';
										}
										
										$logo = '';
										if( is_file( $this->cfg['paths']['design_dir_path'] . '/template_' . $option . '/images/logo.png' ) ){
											$logo = $this->cfg['paths']['design_dir_url'] . '/template_' . $option . '/images/logo.png';
										}
										
										$bg = '';
										if( is_file( $this->cfg['paths']['design_dir_path'] . '/template_' . $option . '/images/bg.png' ) ){
											$bg = $this->cfg['paths']['design_dir_url'] . '/template_' . $option . '/images/bg.png';
										}
										
										$selected = '';
										if( $val != '' ) {
											if ( $val == $option ) { $selected = ' selected="selected"';} 
										}
										$html[] .= '<option'. $selected .' data-logo-src="' . ( $logo ) . '" data-bg-src="' . ( $bg ) . '" data-img-src="' . ( $thumb ) . '" data-img-src="' . ( $thumb ) . '" value="' . esc_attr( $option ) . '">  Theme #' . esc_attr( $key ) . '  </option>';
									 } 
									$html[] .= '</select>';
									$html[] .= '
										<script type="text/javascript">
											jQuery("#' . esc_attr( $elm_id ) . '").imagepicker();
										</script>
									';
								break;	
							}
							
							if(!in_array( $value['type'], $noRowElements)){
								// close: .age_restriction-form-row
								$html[] = '</div>';
								
								// close: .age_restriction-form-item
								$html[] = '</div>';
							}
							
						}
					}
					
					// age_restriction-message use for status message, default it's hidden
					$html[] = '<div class="age_restriction-message" id="age_restriction-status-box" style="display:none;"></div>';
					
					if( $box['buttons'] == true && !is_array($box['buttons']) ) {
						// buttons for each box
						$html[] = '<div class="age_restriction-button-row">
							<input type="reset" value="Reset to default value" class="age_restriction-button gray left" />
							<input type="submit" value="Save the settings" class="age_restriction-button green age_restriction-saveOptions" />
						</div>';
					}
					elseif( is_array($box['buttons']) ){
						// buttons for each box
						$html[] = '<div class="age_restriction-button-row">';
						
						foreach ( $box['buttons'] as $key => $value ){
							$html[] = '<input type="submit" value="' . ( $value['value'] ) . '" class="age_restriction-button ' . ( $value['color'] ) . ' ' . ( $value['action'] ) . '" />';
						}
						
						$html[] = '</div>';
					}
					
					if ( $box_show_wrappers ) {
						
					if($showForm){
						// close: form
						$html[] = '</form>';
					}
					
					// close: .age_restriction-panel-content
					$html[] = '</div>';
					
					// close: box style  div (.age_restriction-panel)
					$html[] = '</div>';
					
					// close: box size div
					$html[] = '</div>';
					
					} // end if show box wrappers
				}
			}
			
			// return the $html
			return implode("\n", $html);
		}
		
		
		/*
		* printBaseInterface, method
		* --------------------------
		*
		* this will add the base DOM code for you options interface
		*/
		public function printBaseInterface( $pluginPage='' ) 
		{
?>
		<div id="age_restriction-wrapper" class="fluid wrapper-age_restriction">
    
			<!-- Header -->
			<?php
			// show the top menu
			age_restrictionAdminMenu::getInstance()->show_menu( $pluginPage );
			?>
		
			<!-- Content -->
			<div id="age_restriction-content">
				
				<h1 class="age_restriction-section-headline">
				</h1>
				
				<!-- Container -->
				<div class="age_restriction-container clearfix">
				
					<!-- Main Content Wrapper -->
					<div id="age_restriction-content-wrap" class="clearfix">
					
						<!-- Content Area -->
						<div id="age_restriction-content-area">
							<!-- Content Area -->
							<div id="age_restriction-ajax-response"></div>
							
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

<?php
		}
		
		//make Tabs!
		private function tabsHeader($box) {
			$html = array();

			// get tabs
			$__tabs = isset($box['tabs']) ? $box['tabs'] : array();

			$__ret = '';
			if (is_array($__tabs) && count($__tabs)>0) {
				$html[] = '<ul class="tabsHeader">';
				$html[] = '<li style="display:none;" id="tabsCurrent" title=""></li>'; //fake li with the current tab value!
				foreach ($__tabs as $tabClass=>$tabElements) {
					$html[] = '<li><a href="javascript:void(0);" title="'.$tabClass.'">'.$tabElements[0].'</a></li>';
				}
				$html[] = '</ul>';
				$__ret = implode('', $html);
				
			}
			return $__ret;
		}
		
		private function tabsElements($box, $elemKey) {
			// get tabs
			$__tabs = isset($box['tabs']) ? $box['tabs'] : array();

			$__ret = '';
			if (is_array($__tabs) && count($__tabs)>0) {
				foreach ($__tabs as $tabClass=>$tabElements) {

					$tabElements = $tabElements[1];
					$tabElements = trim($tabElements);
					$tabElements = array_map('trim', explode(',', $tabElements));
					if (in_array($elemKey, $tabElements)) 
						$__ret .= ($tabClass.' '); //support element on multiple tabs!
				}
			}
			return ' '.trim($__ret).' ';
		}

		// retrieve default from option
		private function getTimeDefault( $range='0:0' ) {
			
			if ( empty($range) ) return array(0, 0);
			
			$range = isset($range) && !empty($range) ? $range : '0:0';
			$range = explode(':', $range);
			if ( count($range)==2 )
				return array( (int) $range[0], (int) $range[1]);
			else 
				return array(0, 0);
		}

		private function do_prefix($arr) {
			return 'wrap-' . $arr;
		}
	}
}