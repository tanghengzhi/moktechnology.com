<?php
$plugin = $GLOBALS['this_plugin'];
$banner = $GLOBALS['banner'];
$bannerMeta = isset($banner['meta']) ? $banner['meta'] : array();
$config = @unserialize( get_option( $plugin->the_plugin->alias . '_settings' ) );
  
$template_path = $plugin->the_plugin->cfg['paths']['design_dir_url'] .'/template_'. $bannerMeta['theme'] . '/';
  
if( isset($_POST['validate']) ) {
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

if( !isset($bannerMeta['date_format']) || trim($bannerMeta['date_format']) == '' ) {
	$bannerMeta['date_format'] = 'MM/DD/YYYY';
}

// Generate Months
$months = array(1=>'January', 2=>'February', 3=>'March', 4=>'April', 5=>'May', 6=>'June', 7=>'July', 8=>'August', 9=>'September', 10=>'October', 11=>'November', 12=>'December');
$html_months = '';					
foreach( $months as $no => $month_name ) {
	$html_months .= '<option value="' . ( $no ) . '"' . (isset($_REQUEST['age_restriction_month']) && htmlentities($_REQUEST['age_restriction_month']) == $no ? ' selected="selected"' : '') . '>' . ( __($month_name, 'age-restriction') ) . '</option>';
}

// Generate Days
$html_days = '';
foreach( range(1, 31) as $day ) {
	$html_days .= '<option value="' . $day . '"' . (isset($_REQUEST['age_restriction_day']) && htmlentities($_REQUEST['age_restriction_day']) == $day ? ' selected="selected"' : '') . '>' . $day . '</option>';
}

// Generate Years
$html_years = '';
foreach( range(1900, date('Y')) as $year ) {
	$html_years .= '<option value="' . ( $year ) . '"' . (isset($_REQUEST['age_restriction_year']) && htmlentities($_REQUEST['age_restriction_year']) == $year ? ' selected="selected"' : '') . '>' . ( $year ) . '</option>';
}
					
$date_format = explode('/', $bannerMeta['date_format']);
$date_fields = array(
	'MM' => '<div class="ara-form-row month">
				<select name="age_restriction_month">
					<option value="" selected="selected" disabled="disabled">' . __('Month', 'age-restriction') . '</option>
					' . ( $html_months ) . '
				</select>
			</div>',
	'DD' => '<div class="ara-form-row day">
				<select name="age_restriction_day">
					<option value="" selected="selected" disabled="disabled">' . __('Day', 'age-restriction') . '</option>
					' . ( $html_days ) . '
				</select>
			</div> ',
	'YYYY' => '<div class="ara-form-row year">
					<select name="age_restriction_year">
						<option value="" selected="selected" disabled="disabled">' . __('Year', 'age-restriction') . '</option>
						' . ( $html_years ) . '
					</select>
				</div>',
);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  
	<title><?php _e('Age restriction', 'age-restriction'); ?></title>
	
	<!-- FONTS AND STYLES -->
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="<?php echo $template_path; ?>css/reset.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $template_path; ?>css/style.css">
	<link rel="stylesheet" type="text/css" href="<?php echo $template_path; ?>css/responsive.css">
	
	<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="<?php echo $template_path; ?>js/mobile.detect.js"></script>
	<script src="<?php echo $template_path; ?>js/app.class.js"></script>
	
	<style type="text/css">
		<?php if( isset($background_image) || isset($bannerMeta['background_color']) ) { ?>
		#wrapper {
			<?php if( isset($background_image) ) { ?>
			background-image: url(<?php echo $background_image; ?>);
			<?php }else if( isset($bannerMeta['background_color']) ) { ?>
			background: #<?php echo $bannerMeta['background_color']; ?>;
			<?php } ?>
		}
		<?php } ?>
		
		<?php if( isset($bannerMeta['box_background_color']) &&  trim($bannerMeta['box_background_color']) != '' ) { ?>
		.ara-box {
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
		.ara-box h1 {
			color: #<?php echo $bannerMeta['title_text_color']; ?>;
		}
		<?php } ?>
		
		<?php if( isset($bannerMeta['text_color_before']) && trim($bannerMeta['text_color_before']) != '' ) { ?>
		p.text-before {
			color: #<?php echo $bannerMeta['text_color_before']; ?>;
		}
		<?php } ?>
		
		<?php if( isset($bannerMeta['text_color_after']) && trim($bannerMeta['text_color_after']) != '' ) { ?>
		h3.text-after {
			color: #<?php echo $bannerMeta['text_color_after']; ?>;
		}
		<?php } ?>
		
		<?php if( isset($bannerMeta['text_color']) && trim($bannerMeta['text_color']) != '' ) { ?>
		.ara-box .ar-form-row input[type="text"], .ar-box .ar-remember-me, .ar-box .ar-form-row select {
			color: #<?php echo $bannerMeta['text_color']; ?>;
		}
		.ara-box .ar-form-row input[type="text"]::-webkit-input-placeholder { /* WebKit browsers */
		    color:    #<?php echo $bannerMeta['text_color']; ?>;
		}
		.ara-box .ar-form-row input[type="text"]:-moz-placeholder { /* Mozilla Firefox 4 to 18 */
		   color:    #<?php echo $bannerMeta['text_color']; ?>;
		   opacity:  1;
		}
		.ara-box .ar-form-row input[type="text"]::-moz-placeholder { /* Mozilla Firefox 19+ */
		   color:    #<?php echo $bannerMeta['text_color']; ?>;
		   opacity:  1;
		}
		.ara-box .ar-form-row input[type="text"]:-ms-input-placeholder { /* Internet Explorer 10+ */
		   color:    #<?php echo $bannerMeta['text_color']; ?>;
		}
		<?php } ?>
				
		<?php if( (isset($bannerMeta['enter_btn_bg_color']) && trim($bannerMeta['enter_btn_bg_color']) != '') ||
				  (isset($bannerMeta['enter_btn_text_color']) && trim($bannerMeta['enter_btn_text_color']) != '') 
		) { ?>
		.ara-box .ar-submit {
			<?php if( isset($bannerMeta['enter_btn_bg_color']) && trim($bannerMeta['enter_btn_bg_color']) != '' ) { ?>
			background-color: #<?php echo $bannerMeta['enter_btn_bg_color']; ?>;
			<?php } ?>
			
			<?php if( isset($bannerMeta['enter_btn_text_color']) && trim($bannerMeta['enter_btn_text_color']) != '' ) { ?>
			color: #<?php echo $bannerMeta['enter_btn_text_color']; ?>;
			<?php } ?>
		}
		<?php } ?>
		
		<?php
		if( isset($bannerMeta['custom_css']) && trim($bannerMeta['custom_css']) != '' ) {
			echo $bannerMeta['custom_css'];
		}
		?>
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
		<div class="ara-box">
			<div class="ara-logo">
				<?php echo isset($banner_logo) ? '<img src="'.$banner_logo.'"/>' : ''; ?>
				<?php echo isset($bannerMeta['text_before']) && !empty($bannerMeta['text_before']) ? '<h2 class="text-before">' . $bannerMeta['text_before'] . '</h2>' : ''; ?>
			</div>
			
			<?php   
          	if( isset($validate) && $validate !== true ) {
          		echo '<span class="errors">&raquo; ' . ( $validate ) . ' &laquo;</span>';
          	}
          	?>
          	
			<h2><?php echo isset($bannerMeta['restriction_title']) && !empty($bannerMeta['restriction_title']) ? $bannerMeta['restriction_title'] : ''; ?></h2>
			
			<form method="post" action="<?php the_permalink(); ?>" id="agecheck">
				<input type="hidden" name="validate" value="<?php echo $banner['ID']; ?>" />
				<input type="hidden" name="social_details" id="social_details" value="" />
				
				<div class="ara-bdate">
					<?php
					foreach( $date_format as $format ) {
						echo $date_fields[$format];
					}
					?>
					
					<?php if( $bannerMeta['country_selection'] == 'yes' ) { ?>
					<h2 class="country-headline"><?php _e('What country are you in?', 'age-restriction'); ?></h2>
					<div class="ara-form-row country">
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
					</div>
					<?php }else{ ?>
						<br/><br/>
					<?php } ?>
				</div>
				
				<?php
				//check if "Enable Remember me" button is enabled		
				if( $bannerMeta['enable_remember_me'] == 'yes' ){
					
					$rememberMe[] = "<input type=\"checkbox\" name=\"remember_me\" id=\"ara-remember-me\" value=\"on\">";
					$rememberMe[] = "<label for=\"ara-remember-me\" class=\"ara-remember-me\">";
					$rememberMe[] = 		__('Remember me?', age-restriction); 
					$rememberMe[] = 	"<div>";
					$rememberMe[] = 			"<i class=\"fa fa-check\"></i>";
					$rememberMe[] = 	"</div>";
				    $rememberMe[] = "</label>";
					$rememberMe[] = "<p class=\"ara-login-preferences\">";
					$rememberMe[] = 		"Use a cookie to remember me.<br />Only check this box if you are not using a shared computer.";
					$rememberMe[] = "</p>";
					
					foreach($rememberMe as $rem)
						echo $rem;
				}
				?>
			
				<input type="submit" value="<?php echo isset($bannerMeta['enter_btn_title']) && !empty($bannerMeta['enter_btn_title']) ? $bannerMeta['enter_btn_title'] : 'ENTER'; ?>" id="ENTER" class="ara-form-row ara-submit" />
				<br/>
				
			</form>
			
			<div class="ara-social-connect">
				<?php
		    	if( (isset($bannerMeta['connect_w_google']) && $bannerMeta['connect_w_google'] == 'yes') && 
					(isset($config['google_client_id']) && trim($config['google_client_id']) != '') 
				) {
		    	?>
		    		<a href="#" id="age_restriction_gplusconnect" class="ara-google"><?php echo isset($bannerMeta['connect_w_google_btnText']) && trim($bannerMeta['connect_w_google_btnText']) != '' ? $bannerMeta['connect_w_google_btnText'] : 'Connect with Google+'; ?></a>
		    	<?php } ?>
		    	
		    	<?php
		    	if( (isset($bannerMeta['connect_w_facebook']) && $bannerMeta['connect_w_facebook'] == 'yes') && 
					(isset($config['fb_app_id']) && trim($config['fb_app_id']) != '') 
				) {
		    	?>
		    		<a href="#" id="age_restriction_fbconnect" class="ara-facebook"><?php echo isset($bannerMeta['connect_w_facebook_btnText']) && trim($bannerMeta['connect_w_facebook_btnText']) != '' ? $bannerMeta['connect_w_facebook_btnText'] : 'Connect with Facebook'; ?></a><br/>
		    	<?php } ?>
		    </div>
			
			<?php echo isset($bannerMeta['text_after']) && !empty($bannerMeta['text_after']) ? '<p class="ara-disclaimer">' . $bannerMeta['text_after'] . '</p>' : ''; ?>
		</div>
	</div>
	
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