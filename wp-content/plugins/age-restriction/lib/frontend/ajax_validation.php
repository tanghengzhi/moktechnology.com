<?php
/**
 * Age Validation Class
 */
if( !class_exists('age_restrictionAgeValidation') ) {
	class age_restrictionAgeValidation {
		
		public $validation = array();
		
		function __construct()
		{
			
		}
		
		public function birthday($birthday){ 
		    $age = strtotime($birthday);
		    
		    if($age === false){ 
		        return false; 
		    } 
		    
		    list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age)); 
		    
		    $now = strtotime("now"); 
		    
		    list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now)); 
		    
		    $age = $y2 - $y1; 
		    
		    if((int)($m2.$d2) < (int)($m1.$d1)) 
		        $age -= 1; 
		        
		    return $age; 
		} 
		
		public function validateAgeRestriction() {
			global $wpdb, $age_restriction;
			  
			$banner_id = $_REQUEST['validate'];
			$banner_meta = get_post_meta($_REQUEST['validate'], '_age_restriction_meta', true);
			$min_age = $banner_meta['banner']['minimum_age'];
			isset($_REQUEST['validate']) ? extract($_REQUEST) : null;
			
			$social_details = isset($_REQUEST['social_details']) && trim($_REQUEST['social_details']) != '' ? json_decode(stripcslashes($_REQUEST['social_details'])) : false;
			 
			// Remember Me validation
			if( isset($remember_me) && $remember_me == 'on' ) {
				$site_url = parse_url(get_site_url());
				setcookie( "ageValidationRemember_" . $min_age . "_bannerID-" . $banner_id, true, (time() + ( intval($banner_meta['banner']['set_cookie_duration']) * 86400 ) ), '/', $_SERVER['HTTP_HOST'] );
			}
			
			// If validate only with confirmation yes/no
			if( isset($age_restriction_confirmation) && $age_restriction_confirmation == 'yes' && isset($_REQUEST['social_details']) && trim($_REQUEST['social_details']) != '' ){
				$birthday = isset($social_details->birthday) ? $social_details->birthday : '';
				$str_time = strtotime($birthday);
				$day =  date("d", $str_time);
				$year =  date("Y", $str_time);
				$month =  date("m", $str_time);
			}
  			 
			// country validation
			if( $social_details === false ) {
				if( isset($age_restriction_country) && trim($age_restriction_country) == '' ) {
					$this->validation = __('Please select your country', 'age-restriction');
					return $this->validation; 
				}
			}
			
			// set country var's if selected by user
			if( isset($age_restriction_country) && trim($age_restriction_country) != '' ) { 
				$country = explode('|', $age_restriction_country);
				if( is_array($country) && count($country) > 0 ) {
					$country_code = $country[0];
					$country_name = strtoupper($country[1]);
				}
			}
			  
			// Birthday validation
			if( isset($age_restriction_day) && isset($age_restriction_month) && isset($age_restriction_year) &&
				trim($age_restriction_day) != '' && trim($age_restriction_month) != '' && trim($age_restriction_year) != '' &&
				is_numeric($age_restriction_day) && is_numeric($age_restriction_month) && is_numeric($age_restriction_year)
			) {
				$user_age = $this->birthday($age_restriction_year .'-'. $age_restriction_month .'-'. $age_restriction_day);
				
				if( $user_age >= $min_age && ((isset($age_restriction_country) && $age_restriction_country != '') || !isset($age_restriction_country) || $age_restriction_country == '') ) {
					$_SESSION['ageValidationPassed_' . $min_age . "_bannerID-" . $banner_id] = true;
				}
				
			// Confirmation validation
			}else if( isset($age_restriction_confirmation) && trim($age_restriction_confirmation) != '' && $age_restriction_confirmation == 'yes' ) { 
				$_SESSION['ageValidationPassed_' . $min_age . "_bannerID-" . $banner_id] = true;
			}
			
			if( isset($_SESSION['ageValidationPassed_' . $min_age . "_bannerID-" . $banner_id]) ) {
				// set verify source (manual / facebook / google)
				$verify_source = 'manual';
				if( isset($social_details) && $social_details->verify_source == 'facebook' ) {
					$verify_source = 'facebook';
				}
				elseif( isset($social_details) && $social_details->verify_source == 'google' ){
					$verify_source = 'google';
				}
				 
				$utils = $age_restriction->get_client_utils();
				$statistics_array = array(
                	'action' 			=> 'auth',
                   	'banner_id'			=> $banner_id,
                   	'device_type'		=> $utils['device_type']['type'],
                   	'device_type_full'	=> $utils['device_type']['device'],
                   	'ip'				=> $utils['client_ip'],
                   	'country'			=> isset($country_name) && $country_name != '' ? $country_name : $utils['current_country'],
					'country_code'		=> isset($country_code) && $country_code != '' ? $country_code : $utils['current_country_code'],
					'verify_source'		=> $verify_source,
					'birthday'			=> ( isset($age_restriction_confirmation) && trim($age_restriction_confirmation) != '' && $age_restriction_confirmation == 'yes' ? '' : ($age_restriction_year .'-'. $age_restriction_month .'-'. $age_restriction_day) ),
					'age'				=> ( isset($age_restriction_confirmation) && trim($age_restriction_confirmation) != '' && $age_restriction_confirmation == 'yes' ? $min_age : $user_age ),
				); 
				$insert_format = array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d');
				  
				if( isset($social_details) && count($social_details) > 0 ) { 
					$statistics_array = array_merge($statistics_array, array(
						'first_name'		=> $social_details->first_name,
						'last_name'			=> $social_details->last_name,
						'email'				=> $social_details->email,
						'birthday'			=> $age_restriction_year .'-'. $age_restriction_month .'-'. $age_restriction_day,
						'age'				=> $user_age,
						'gender'			=> $social_details->gender,
					));
					$insert_format = array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
				}
				
				$age_restriction->db_custom_insert(
					$wpdb->prefix . 'age_restriction_stats',
					array(
						'values' => $statistics_array,
						'format' => $insert_format
					),
					true
				);
				
				$this->validation = true;
				return $this->validation;
			}
			
			if( $this->validation !== true ) {
				$this->validation = isset($banner_meta['banner']['minimum_age_error_message']) && !empty($banner_meta['banner']['minimum_age_error_message']) ? $banner_meta['banner']['minimum_age_error_message'] : __('Minimum age required', 'age-restriction');
				return $this->validation;
			}
		}
				
	}
}
$ageValidation = new age_restrictionAgeValidation();