<?php
/*
Plugin Name: WordPress Responsive Age Verification
Description: WordPress Responsive Age Verification allows you to force users to confirm their age, with a responsive dialog. Designed for distilleries, vineyards, dispensaries and online shops.
Version: 1.3.0
Author: DesignSmoke Web Developers
Author URI: https://www.designsmoke.com
License: GPL2

This code is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version./

This code is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this code. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

function agev_is_valid_string($astr){
    return !(!isset($astr) || $astr === false || trim($astr)==='');
}

function agev_is_valid_color($color) {
	if(!isset($color)) {
		return false;
	}
	
    if ($color[0] === "#") {
        $color = substr($color, 1);
        return in_array(strlen($color), [3, 4, 6, 8]) && ctype_xdigit($color);
    } else {
        return preg_match("/^(rgb|hsl)a?\((\d+%?(deg|rad|grad|turn)?[,\s]+){2,3}[\s\/]*[\d\.]+%?\)$/i", $color);
    }
}

/** Dialog Setting Variables (global) **/
$ageOverlayColor = (agev_is_valid_string(get_option('age_overlay_color')) && agev_is_valid_color(get_option('age_overlay_color'))) ? get_option('age_overlay_color') : '#282828';
$ageDialogColor = (agev_is_valid_string(get_option('age_dialog_color')) && agev_is_valid_color(get_option('age_dialog_color'))) ? get_option('age_dialog_color') : '#ff4646';
$ageDialogTitle = htmlspecialchars((agev_is_valid_string(get_option('age_dialog_title'))) ? get_option('age_dialog_title') : 'Are you 21 or older?');
$ageDialogText = htmlspecialchars((agev_is_valid_string(get_option('age_dialog_text'))) ? get_option('age_dialog_text') : 'This website requires you to be 21 years of age or older. Please verify your age to view the content, or click "Exit" to leave.');
$ageConfirmText = htmlspecialchars((agev_is_valid_string(get_option('age_confirm_text'))) ? get_option('age_confirm_text') : 'I am over 21');
$ageDeclineText = htmlspecialchars((agev_is_valid_string(get_option('age_decline_text'))) ? get_option('age_decline_text') : 'Exit');
$ageShowCredits = (agev_is_valid_string(get_option('age_show_credits'))) ? get_option('age_show_credits') : '0';
$ageSessionDuration = (agev_is_valid_string(get_option('age_session_duration'))) ? get_option('age_session_duration') : '8760';

function agevAgeVerificationInject() {
  global $ageOverlayColor, $ageDialogColor, $ageDialogTitle, $ageDialogText, $ageConfirmText, $ageDeclineText, $ageShowCredits, $ageSessionDuration; // Set scope
  include_once(plugin_dir_path( __FILE__ ) . 'verification-inject-html.php'); // This will output the age popup code
}
add_action('wp_footer', 'agevAgeVerificationInject'); // Inject code into footer
include(plugin_dir_path( __FILE__ ) . 'admin-settings.php'); // Admin settings page

if(is_admin()) {

	if(get_option('agev_installation_date') === false) {
		update_option('agev_installation_date', date('Y-m-d h:i:s'));
		update_option('agev_rating_div', 'no');
	}

	function agev_admin_notices() {
	    // Ask user for a review after 1 week
	    $install_date = get_option('agev_installation_date');
	    $display_date = date( 'Y-m-d h:i:s' );
	    $datetime1 = new DateTime( $install_date );
	    $datetime2 = new DateTime( $display_date );
	    $diff_intrval = round( ($datetime2->format( 'U' ) - $datetime1->format( 'U' )) / (60 * 60 * 24) );
	    if( $diff_intrval >= 7 && (get_option('agev_rating_div') == "no") ) {
	        echo '<div class="agev_fivestar update-nag" style="display:block; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
	    	<p>You\'ve been using <strong>Responsive Age Verification</strong> for over a week, and I just wanted to know how it\'s working out for you. <br> Is there any chance you\'d be able to <a target="_new" href="https://wordpress.org/support/plugin/responsive-age-verification/reviews/#new-post">give it a review on WordPress?</a><br><br>
	        This would be extremely helpful to me and many others, and would encourage people to try out the plugin.<br>If there were any bugs or issues, just let me know and I can fix them!
		<br>Also, try out <a href="'.admin_url('plugin-install.php?s=anti-spam+zapper&tab=search&type=term').'">Anti-Spam Zapper</a> - our <b>100% free</b> plugin to block all spam comments.
	        <ul>
	            <li class="float:left"><a href="https://wordpress.org/support/plugin/responsive-age-verification/reviews/#new-post" class="thankyou button button-primary" target="_new" title="Review it!" style="color: #ffffff;-webkit-box-shadow: 0 1px 0 #256e34;box-shadow: 0 1px 0 #256e34;font-weight: normal;float:left;margin-right:10px;">Sure, I\'ll review it!</a></li>
	            <li class="float:left"><a href="javascript:void(0);" class="button action agevRating" >Dismiss</a></li>
	        </ul>
	    </div>
	    <script>
	    jQuery( document ).ready(function( $ ) {
	
	    jQuery(\'.agevRating\').click(function(){
	        var data={\'action\':\'agev_hide_rating\'}
	             jQuery.ajax({
	        
	        url: "' . admin_url( 'admin-ajax.php' ) . '",
	        type: "post",
	        data: data,
	        dataType: "json",
	        async: !0,
	        success: function(e) {
	            //if (e=="success") {
				   
	            //}
	        }
	         });

		// Hide it without latency
		jQuery(\'.agev_fivestar\').slideUp(\'fast\');

	        })
	    
	    });
	    </script>
	    ';
	    }
	}
	add_action( 'admin_notices', 'agev_admin_notices' );

	// Add rate-this plugin link
	function agev_row_meta( $links, $file ) {    
	    if ( plugin_basename( __FILE__ ) == $file ) {
	        $row_meta = array(
	          'rate-plugin'    => '<a href="' . esc_url( 'https://wordpress.org/support/plugin/responsive-age-verification/reviews/#new-post' ) . '" target="_blank" style="font-weight:bold;">Rate This Plugin &raquo;</a>'
	        );
	        return array_merge( $links, $row_meta );
	    }
	    return (array) $links;
	}
	add_filter( 'plugin_row_meta', 'agev_row_meta', 10, 2 );


	// Add settings page link on left
	function agev_action_links( $links ) {
	   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=agev-age-verification') ) .'">Settings</a>';
	   $links[] = '<a href="https://www.designsmoke.com" target="_blank">DesignSmoke</a>';
	   return $links;
	}
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'agev_action_links' );
}

// Ajax callback, hides the rating div and has no parameters or options
function agev_hide_rating() {
	update_option('agev_rating_div', 'hide');
	wp_send_json_success('success');
	die();
}
add_action('wp_ajax_agev_hide_rating', 'agev_hide_rating');

?>
