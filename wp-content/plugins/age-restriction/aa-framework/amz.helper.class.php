<?php 
/**
 *	Author: AA-Team
 *	Name: 	http://codecanyon.net/user/AA-Team/portfolio
 *	
**/
! defined( 'ABSPATH' ) and exit;

if(class_exists('age_restrictionAmazonHelper') != true) {
	class age_restrictionAmazonHelper extends age_restriction 
	{
		private $the_plugin = null;
		public $aaAmazonWS = null;
		private $amz_settings = array();
		
		static protected $_instance;
		
		
		/* The class constructor
		=========================== */
		public function __construct( $the_plugin=array(), $setupAmazonWS=true ) 
		{
			$this->the_plugin = $the_plugin; 
			
			// get all settings options
			$this->amz_settings = @unserialize( get_option( $this->the_plugin->alias . '_settings' ) );
		}
		
		/**
	    	* Singleton pattern
	    	*
	    	* @return pspGoogleAuthorship Singleton instance
	    	*/
		static public function getInstance( $the_plugin=array(), $setupAmazonWS=true )
		{
			if (!self::$_instance) {
				self::$_instance = new self( $the_plugin, $setupAmazonWS );
			}

			return self::$_instance;
		}
		
	}
}