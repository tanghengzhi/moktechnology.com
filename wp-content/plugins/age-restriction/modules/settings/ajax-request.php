<?php
if(!function_exists('amzStore_bulk_wp_exist_post_by_args')) {
	function amzStore_bulk_wp_exist_post_by_args($args) {
		global $wpdb;
		
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . ( $wpdb->prefix ) . "posts WHERE 1=1 and post_status = '" . ( $args['post_status'] ) . "' and post_title = %s", $args['post_title'] ), 'ARRAY_A' );
		if(count($result) > 0){
			return $result;
		}
		return false;
	}
}
?>