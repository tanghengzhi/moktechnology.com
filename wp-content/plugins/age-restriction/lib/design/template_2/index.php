<?php
$plugin = $GLOBALS['this_plugin'];
$banner = $GLOBALS['banner'];
$bannerMeta = isset($banner['meta']) ? $banner['meta'] : array();
$config = @unserialize( get_option( $plugin->the_plugin->alias . '_settings' ) );

$template_path = $plugin->the_plugin->cfg['paths']['design_dir_url'] .'/template_'. $bannerMeta['theme'] . '/';

if( isset($_POST['submit']) ) {  
	require( $plugin->the_plugin->cfg['paths']['frontend_dir_path'] . '/ajax_validation.php' );
	$validate = $ageValidation->validateAgeRestriction();
	
	if( $validate === true ) {   
		wp_safe_redirect( get_permalink() );
	}else if( isset($bannerMeta['redirect_under_age']) && trim($bannerMeta['redirect_under_age']) != '' ) {
		$redirect_under_age = esc_url( $bannerMeta['redirect_under_age'] );
		wp_redirect( $redirect_under_age );
	}
}

// Background Image
if ( isset($bannerMeta['background_image']) && !empty($bannerMeta['background_image']) ) {
	if( (int) $bannerMeta['background_image'] == 0 && trim($bannerMeta['background_image']) != "" ){
		$image = array( $bannerMeta['background_image'] ); 
	}else{
		$image = wp_get_attachment_image_src( (int) $bannerMeta['background_image'], 'full' );
	}
	if ( isset($image[0]) && !empty($image[0]) ) $background_image = $image[0];  
}

// Convert hexdec color string to rgb(a) string */
function agerestriction_hex2rgba($color, $opacity = false) {
 
	$default = 'rgb(0,0,0)';
 
	//Return default if no color provided
	if(empty($color))
          return $default; 
 
	//Sanitize $color if "#" is provided 
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }
 
        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }
 
        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);
 
        //Check if opacity is set(rgba or rgb)
        if($opacity){
        	if(abs($opacity) > 1)
        		$opacity = 1.0;
        	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
        } else {
        	$output = 'rgb('.implode(",",$rgb).')';
        }
 
        //Return rgb(a) color string
        return $output;
}

// Logo  
if ( isset($bannerMeta['logo']) && !empty($bannerMeta['logo']) ) {
	
	if( (int) $bannerMeta['logo'] == 0 && trim($bannerMeta['logo']) != "" ){
		$image = array( $bannerMeta['logo'] ); 
	}else{
		$image = wp_get_attachment_image_src( (int) $bannerMeta['logo'], 'full' );
	}
	if ( isset($image[0]) && !empty($image[0]) ) $banner_logo = $image[0];  
}

// Replace shortcodes in before & after text
if( isset($bannerMeta['text_before']) && strstr($bannerMeta['text_before'], '[minimum_age]') ) {
	$bannerMeta['text_before'] = str_replace('[minimum_age]', $bannerMeta['minimum_age'], $bannerMeta['text_before']);
}
if( isset($bannerMeta['text_after']) && strstr($bannerMeta['text_after'], '[minimum_age]') ) {
	$bannerMeta['text_after'] = str_replace('[minimum_age]', $bannerMeta['minimum_age'], $bannerMeta['text_after']);
}

// Months
$months = array(
	array( __('Jan', 'age-restriction'), '' ),
	array( __('Feb', 'age-restriction'), 'feb-m' ),
	array( __('Mar', 'age-restriction'), '' ),
	array( __('Apr', 'age-restriction'), 'feb-m1' ),
	array( __('May', 'age-restriction'), '' ),
	array( __('Jun', 'age-restriction'), 'feb-m1' ),
	array( __('Jul', 'age-restriction'), '' ),
	array( __('Aug', 'age-restriction'), '' ),
	array( __('Sep', 'age-restriction'), 'feb-m1' ),
	array( __('Oct', 'age-restriction'), '' ),
	array( __('Nov', 'age-restriction'), 'feb-m1' ),
	array( __('Dec', 'age-restriction'), '' )
);

$html_months = '';   
foreach( $months as $month_no => $month ) {
	$html_months .= '<li class="'. (isset($month[1]) ? $month[1] : '') . (isset($_REQUEST['age_restriction_month']) && htmlentities($_REQUEST['age_restriction_month']) == $month_no+1 ? ' selected' : '') . '">' . ( $month[0] ) . '</li>';
}

// Days
$cc = 1;
$html_days = '';
foreach ( range( 1, 31 ) as $_day ) {
	$day = str_pad($_day, 2, "0", STR_PAD_LEFT);	
	if( $day == '01' ) {
		$html_days .= '<li class="first' . (isset($_REQUEST['age_restriction_day']) && htmlentities($_REQUEST['age_restriction_day']) == $_day ? ' selected' : '') . '" id="'. $day .'">'. $day .'</li>';
	} elseif ( $day == '30' ) {
		$html_days .= '<li class="fade-d' . (isset($_REQUEST['age_restriction_day']) && htmlentities($_REQUEST['age_restriction_day']) == $_day ? ' selected' : '') . '" id="'. $day .'" id="'. $day .'">'. $day .'</li>';
	} elseif ( $day == '31' ) {
		$html_days .= '<li class="fade-d fade-d1 last' . (isset($_REQUEST['age_restriction_day']) && htmlentities($_REQUEST['age_restriction_day']) == $_day ? ' selected' : '') . '" id="'. $day .'" id="'. $day .'">'. $day .'</li>';
	} else {
		$html_days .= '<li id="'. $day .'" class="' . (isset($_REQUEST['age_restriction_day']) && htmlentities($_REQUEST['age_restriction_day']) == $_day ? 'selected' : '') . '" id="'. $day .'">'. $day .'</li>';
	}
	$cc++;		
}

// Years
$currentYear = date( "Y" );
$cc = 1;
$html_years = '';
foreach ( range(1920, (int)$currentYear  ) as $year ) {
	if( $year >= $currentYear ) { 
    	$html_years .= '<li class="gray' . (isset($_REQUEST['age_restriction_year']) && htmlentities($_REQUEST['age_restriction_year']) == $year ? ' selected' : '') . '" id="'. $day .'" id="' . $year . '">' . $year . '</li>';
	} else {
		$html_years .= '<li id="' . $year . '" class="' . (isset($_REQUEST['age_restriction_year']) && htmlentities($_REQUEST['age_restriction_year']) == $year ? ' selected' : '') . '">' . $year . '</li>';
	}
	$cc++;
}

if( !isset($bannerMeta['date_format']) || trim($bannerMeta['date_format']) == '' ) {
	$bannerMeta['date_format'] = 'MM/DD/YYYY';
}

$date_format = explode('/', $bannerMeta['date_format']);
$date_fields = array(
	'MM' => '<ul class="age-list standard group" id="age-list-months">' . ( $html_months ) . '</ul>',
	'DD' => '<ul class="age-list standard group" id="age-list-days">' . ( $html_days ) . '</ul>',
	'YYYY' => '<div class="es-carousel-wrapper" id="carousel">
				    <div class="es-carousel">
				        <ul class="age-year" id="age-yearul">
				        	' . ( $html_years ) . '
				        </ul> 
				    </div> 
				</div>',
);
?>

<!doctype html>
<!--[if lt IE 7]><html class="no-js ie6" lang="en"><![endif]-->
<!--[if IE 7]><html class="no-js ie7" lang="en"><![endif]-->
<!--[if IE 8]><html class="no-js ie8" lang="en"><![endif]-->
<!--[if (gte IE 9)|!(IE)]>
<!-->
<html class="no-js js" lang="en">
	<!--<![endif]-->
	<head id="head1">
		<title><?php _e('Age restriction'); ?></title>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link href='//fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,700italic,700,600italic,800,800italic&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="<?php echo $template_path; ?>css/style.css" />
		<link rel="stylesheet" href="<?php echo $template_path; ?>css/responsive.css" />
		<script src="<?php echo $template_path; ?>js/modernizr-1.7.min.js"></script>
		<script src="<?php echo $template_path; ?>js/jquerylibrary.js"></script>
		
		<style type="text/css">
			<?php if( isset($background_image) || isset($bannerMeta['background_color']) ) { ?>
			#wrapper {
				<?php if( isset($bannerMeta['background_image']) ) { ?>
				background-image: url(<?php echo $background_image; ?>);
				<?php }else if( isset($bannerMeta['background_color']) ) { ?>
				background: #<?php echo $bannerMeta['background_color']; ?>;
				<?php } ?>
			}
			<?php } ?>
			
			<?php if( isset($bannerMeta['box_background_color']) &&  trim($bannerMeta['box_background_color']) != '' ) { ?>
			.main {
				background-color: <?php 
										if( isset($bannerMeta['box_background_opacity']) &&  trim($bannerMeta['box_background_opacity']) != '' ) {
											echo agerestriction_hex2rgba( $bannerMeta['box_background_color'], $bannerMeta['box_background_opacity'] / 100 );
										}
										else {
											echo agerestriction_hex2rgba($bannerMeta['box_background_color']);
										}
									?>
			}
			<?php } ?>
			
			<?php if( isset($bannerMeta['title_text_color']) && trim($bannerMeta['title_text_color']) != '' ) { ?>
			.input-area h1 {
				color: #<?php echo $bannerMeta['title_text_color']; ?>;
			}
			<?php } ?>
			
			<?php if( isset($bannerMeta['text_color_before']) && trim($bannerMeta['text_color_before']) != '' ) { ?>
			p.text-before {
				color: #<?php echo $bannerMeta['text_color_before']; ?>;
			}
			<?php } ?>
			
			<?php if( isset($bannerMeta['text_color_after']) && trim($bannerMeta['text_color_after']) != '' ) { ?>
			p.text-after {
				color: #<?php echo $bannerMeta['text_color_after']; ?>;
			}
			<?php } ?>
			
			<?php if( isset($bannerMeta['text_color']) && trim($bannerMeta['text_color']) != '' ) { ?>
			#age-list-container ul li, .rememberme label, .select {
				color: #<?php echo $bannerMeta['text_color']; ?> !important;
			}
			<?php } ?>
			
			<?php if( isset($bannerMeta['text_hover_color']) && trim($bannerMeta['text_hover_color']) != '' ) { ?>
			#age-list-container ul li.selected,
			#age-list-container ul li.active {
				color: #<?php echo $bannerMeta['text_hover_color']; ?> !important;
			}
			<?php } ?>
			
			<?php if( (isset($bannerMeta['enter_btn_bg_color']) && trim($bannerMeta['enter_btn_bg_color']) != '') ||
					  (isset($bannerMeta['enter_btn_text_color']) && trim($bannerMeta['enter_btn_text_color']) != '') 
			) { ?>
			input.enter-btn {
				<?php if( isset($bannerMeta['enter_btn_bg_color']) && trim($bannerMeta['enter_btn_bg_color']) != '' ) { ?>
				background-color: #<?php echo $bannerMeta['enter_btn_bg_color']; ?>;
				<?php } ?>
				
				<?php if( isset($bannerMeta['enter_btn_text_color']) && trim($bannerMeta['enter_btn_text_color']) != '' ) { ?>
				color: #<?php echo $bannerMeta['enter_btn_text_color']; ?>;
				<?php } ?>
			}
			
			<?php } ?>
		</style>
	</head>
	<body>
		
		<?php 
		if( (isset($bannerMeta['connect_w_facebook']) && $bannerMeta['connect_w_facebook'] == 'yes') && 
			(isset($config['fb_app_id']) && trim($config['fb_app_id']) != '') 
		) {
		?>
		<script type="text/javascript">
			window.fbAsyncInit = function() {
				FB.init({
					appId      : '<?php echo $config['fb_app_id']; ?>',
					xfbml      : true,
					version    : 'v2.9'
				});
			};
			
			(function(d, s, id){
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {return;}
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
		<?php } ?>
		
		<div class="preloader"><p><?php _e('Loading...', 'age-restriction'); ?></p></div>
		
		<div id="wrapper">
			<form method="post" action="<?php the_permalink(); ?>" id="agecheck">
				<input type="hidden" name="validate" value="<?php echo $banner['ID']; ?>" />
				<input type="hidden" name="social_details" id="social_details" value="" />
				
			    <div>
			        <div class="main" role="main">
			            <div class="wrap" id="divMain">
			                <div class="input-area">
			                    <hgroup>
						          <div id="dvHeaderText">
						          	<div class="logo"><?php echo isset($banner_logo) ? '<img src="'.$banner_logo.'"/>' : ''; ?></div>
						          	<?php   
						          	if( isset($validate) && $validate !== true ) {
						          		echo '<span class="errors">&raquo; ' . ( $validate ) . ' &laquo;</span>';
						          	}
						          	?>
						            <h1><?php echo isset($bannerMeta['restriction_title']) && !empty($bannerMeta['restriction_title']) ? $bannerMeta['restriction_title'] : ''; ?></h1>
						            <?php echo isset($bannerMeta['text_before']) && !empty($bannerMeta['text_before']) ? '<p class="text-before">' . $bannerMeta['text_before'] . '</p>' : ''; ?>
						          </div>
						      	</hgroup>
								<label for="month">Month:</label>
					            <input name="age_restriction_month" type="text" id="month" maxlength="2" pattern="(0[1-9]|1[012])" placeholder="MM" required="" value="<?php echo isset($_REQUEST['age_restriction_month']) ? htmlentities($_REQUEST['age_restriction_month']) : ''; ?>"/>
					            <label for="day">Day:</label>
					            <input name="age_restriction_day" type="text" id="day" maxlength="2" pattern="(0[1-9]|1[0-9]|2[0-9]|3[01])" placeholder="DD" required="" value="<?php echo isset($_REQUEST['age_restriction_day']) ? htmlentities($_REQUEST['age_restriction_day']) : ''; ?>"/>
					            <label for="year">Year:</label>
					            <input name="age_restriction_year" type="text" id="year" maxlength="4" pattern="(?:19|20)[0-9]{2}" placeholder="YYYY" required="" value="<?php echo isset($_REQUEST['age_restriction_year']) ? htmlentities($_REQUEST['age_restriction_year']) : ''; ?>"/>
			                    <div id="age-list-container">    
									<div class="sfContentBlock">
										<?php
										foreach( $date_format as $format ) {
											echo $date_fields[$format];
										}
										?>
									</div>
			                    </div>
			                    
			                    <?php if( $bannerMeta['country_selection'] == 'yes' ) { ?>
			                    <div class="country-selector">
					             	<h1><?php _e('PLEASE SELECT YOUR COUNTRY', 'age-restriction'); ?></h1>
			                        <select name="age_restriction_country">
			                        	<option value=""><?php _e('Select Country', 'age-restriction'); ?></option>
			                        	<?php
										foreach ( $plugin->the_plugin->getCountriesList('code') as $key => $val ) {
											if( isset($bannerMeta['selected_countries']) && is_array($bannerMeta['selected_countries']) && 
												in_array($key, $bannerMeta['selected_countries']) 
											) {
												echo '<option value="'. $key .'|' .$val.'" '. ( isset($_REQUEST['age_restriction_country']) && htmlentities($_REQUEST['age_restriction_country']) == $key.'|'.$val ? 'selected="selected"' : '' ) .'>'. $val .'</option>';
											}else if( !isset($bannerMeta['selected_countries']) ){
												echo '<option value="'. $key .'|'. $val.'" '. ( isset($_REQUEST['age_restriction_country']) && htmlentities($_REQUEST['age_restriction_country']) == $key.'|'.$val ? 'selected="selected"' : '' ) .'>'. $val .'</option>';
											}
										}
			                        	?>
									</select>
				                	<div class="clearfix"></div>
				                </div>
			                	<?php } ?>
			                	
			                	<input type="submit" name="submit" value="<?php echo isset($bannerMeta['enter_btn_title']) && !empty($bannerMeta['enter_btn_title']) ? $bannerMeta['enter_btn_title'] : 'ENTER'; ?>" id="ENTER" class="enter-btn" />
			                	
			                	<?php
								//check if "Enable Remember me" button is enabled		
								
								if( $bannerMeta['enable_remember_me'] == 'yes' ){
									
									$rememberMe[] = "<div class=\"rememberme\">";
									$rememberMe[] = 		"<input id=\"remember\" class=\"css-checkbox\" name=\"remember_me\" type=\"checkbox\" value=\"on\">"; 
									$rememberMe[] = 		"<label for=\"remember\" class=\"css-label lite-green-check\">";
									$rememberMe[] = 				__('Remember me?', 'age-restriction');
									$rememberMe[] = 		"</label>";
									$rememberMe[] = "</div>";
									
									foreach($rememberMe as $rem)
										echo $rem;
								}
								?>	
			                	 
			                    <div class="disclaimer">
				                	<?php echo isset($bannerMeta['text_after']) && !empty($bannerMeta['text_after']) ? '<p class="text-after">' . $bannerMeta['text_after'] . '</p>' : ''; ?>
				                </div>
			                </div>
			                
			                <div class="socialconnect">
			                	<?php
			                	if( (isset($bannerMeta['connect_w_facebook']) && $bannerMeta['connect_w_facebook'] == 'yes') && 
									(isset($config['fb_app_id']) && trim($config['fb_app_id']) != '') 
								) {
			                	?>
			                		<a href="#" id="age_restriction_fbconnect" class="fbconnect"><?php echo isset($bannerMeta['connect_w_facebook_btnText']) && trim($bannerMeta['connect_w_facebook_btnText']) != '' ? $bannerMeta['connect_w_facebook_btnText'] : 'Connect with Facebook'; ?></a>
			                	<?php } ?>
			                	
			                	<?php
			                	if( (isset($bannerMeta['connect_w_google']) && $bannerMeta['connect_w_google'] == 'yes') && 
									(isset($config['google_client_id']) && trim($config['google_client_id']) != '') 
								) {
			                	?>
			                		<a href="#" id="age_restriction_gplusconnect" class="gplusconnect"><?php echo isset($bannerMeta['connect_w_google_btnText']) && trim($bannerMeta['connect_w_google_btnText']) != '' ? $bannerMeta['connect_w_google_btnText'] : 'Connect with Google+'; ?></a>
			                	<?php } ?>
			                </div>
			            </div>
			        </div>
			    </div>
			</form>
		</div>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script>
	    <script src="<?php echo $template_path; ?>js/jquery-ui.min.js"></script>
	    <script> window.jQuery || document.write("<script src='js/jquery-1.7.min.js'>\x3C/script>")</script>
	    <script src="<?php echo $template_path; ?>js/jquery.elastislide.js"></script>
	    <script src="<?php echo $template_path; ?>js/jquery.h5validate.js"></script>
	    <script src="<?php echo $template_path; ?>js/mobile.detect.js"></script>
	    <script src="<?php echo $template_path; ?>js/main.js"></script>
	    <script src="<?php echo $template_path; ?>js/app.class.js"></script>
	    <?php
		if( (isset($bannerMeta['connect_w_google']) && $bannerMeta['connect_w_google'] == 'yes') && 
			(isset($config['google_client_id']) && trim($config['google_client_id']) != '') 
		) {
		?>
		<script src="https://apis.google.com/js/client:platform.js" async defer></script>
		<script type="text/javascript">
			jQuery(document).on('click', '#age_restriction_gplusconnect', function(e) {
				e.preventDefault();
				
				var signInAdditionalParams = {
					'clientid': '<?php echo $config['google_client_id']; ?>',
					'scope': 'email',
					'cookiepolicy': 'single_host_origin',
					'callback': 'gPlusSigninCallback'
				};
				
				gapi.auth.signIn(signInAdditionalParams);
			});
			
		</script>
		<?php } ?>
	    <div id="fade"></div>
	    <div class="popup-message">
	    	<div class="popup_content"></div>
	    	<a href="#" class="btn close"><?php _e('OK', 'age-restriction'); ?></a>
	    </div>
	</body>
</html>