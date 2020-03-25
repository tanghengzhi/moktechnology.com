<?php
/*
Plugin Name:	Premium Age Restriction Plugin for Wordpress

Plugin URI: 	http://codecanyon.net/user/AA-Team/portfolio
Description: 	It happens that you own a website that has content that’s not appropriate for all ages from moral or legal reasons? Do you have a website that has alcohol, adult or cigarettes related content? Enable Age Restrictions!
Version: 		1.8.4
Author: 		AA-Team
Author URI:		http://codecanyon.net/user/AA-Team/portfolio
*/
! defined( 'ABSPATH' ) and exit;

// Derive the current path and load up age_restriction
$plugin_path = dirname(__FILE__) . '/';
if(class_exists('age_restriction') != true) {
    require_once($plugin_path . 'aa-framework/framework.class.php');

	// Initalize the your plugin
	$age_restriction = new age_restriction();

	// Add an activation hook
	register_activation_hook(__FILE__, array(&$age_restriction, 'activate'));
}

// load textdomain
add_action( 'plugins_loaded', 'agerestriction_load_textdomain' );

function agerestriction_load_textdomain() {  
	load_plugin_textdomain( 'age-restriction', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}