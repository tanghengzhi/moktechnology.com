/*
Document   :  404 Monitor
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
age_restrictionRemoteSupport = (function ($) {
    "use strict";

    // public
    var debug_level = 0;
    var maincontainer = null;
    var loading = null;
    var loaded_page = 0;
    var token = null;

	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function(){
			maincontainer = $("#age_restriction-wrapper");
			loading = maincontainer.find("#age_restriction-main-loading");

			triggers();
		});
	})();
	
	function remote_register_and_login( that )
	{
		loading.show();
		
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionRemoteSupportRequest',
			'sub_actions'	: 'remote_register_and_login',
			'params'		: that.serialize(),
			'debug_level'	: debug_level
		}, function(response) {
			
			if( response.status == 'valid' ){
				token = response.token;
				$("#age_restriction-token").val(token);
				$("#age_restriction-boxid-login").fadeOut(100);
				$("#age_restriction-boxid-register").fadeOut(100);
				
				var box_info_message = $("#age_restriction-boxid-logininfo .age_restriction-message");
				box_info_message.removeClass("age_restriction-info");
				box_info_message.addClass("age_restriction-success");
				
				box_info_message.html("You have successfully login into http://support.aa-team.com . Now you can open a ticket for our AA-Team support team.");
				
				$("#age_restriction-boxid-ticket").fadeIn(100);
			}else{
				var status_block = that.find(".age_restriction-message");
				status_block.html( "<strong>" + ( response.error_code ) + ": </strong>" + response.msg );
				
				status_block.fadeIn('fast'); 
			}
			
			loading.hide();
		}, 'json'); 
	}
	
	function remote_login( that )
	{
		loading.show();
		
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionRemoteSupportRequest',
			'sub_actions'	: 'remote_login',
			'params'		: that.serialize(),
			'debug_level'	: debug_level
		}, function(response) {
			
			if( response.status == 'valid' ){
				token = response.token;
				$("#age_restriction-token").val(token);
				$("#age_restriction-boxid-login").fadeOut(100);
				$("#age_restriction-boxid-register").fadeOut(100);
				
				var box_info_message = $("#age_restriction-boxid-logininfo .age_restriction-message");
				box_info_message.removeClass("age_restriction-info");
				box_info_message.addClass("age_restriction-success");
				
				box_info_message.html("You have successfully login into http://support.aa-team.com . Now you can open a ticket for our AA-Team support team.");
				
				$("#age_restriction-boxid-ticket").fadeIn(100);
			}else{
				var status_block = that.find(".age_restriction-message");
				status_block.html( "<strong>" + ( response.error_code ) + ": </strong>" + response.msg );
				
				status_block.fadeIn('fast'); 
			}
			
			loading.hide();
		}, 'json'); 
	}
	
	function open_ticket( that )
	{
		loading.show();
		
		$("#age_restriction-wp_password").val( $("#age_restriction-password").val() );
		$("#age_restriction-access_key").val( $("#age_restriction-key").val() );
		
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionRemoteSupportRequest',
			'sub_actions'	: 'open_ticket',
			'params'		: that.serialize(),
			'token'			: $("#age_restriction-token").val(),
			'debug_level'	: debug_level
		}, function(response) {
			
			if( response.status == 'valid' ){
				that.find(".age_restriction-message").html( "The ticket has been open. New ticket ID: <strong>" + response.new_ticket_id + "</strong>" );
				that.find(".age_restriction-message").show();
			}
			 
			loading.hide();
			
		}, 'json'); 
	}
	
	function access_details( that )
	{
		loading.show();
		
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionRemoteSupportRequest',
			'sub_actions'	: 'access_details',
			'params'		: that.serialize(),
			'debug_level'	: debug_level
		}, function(response) {
			
			loading.hide();
		}, 'json'); 
	}
	
	function checkAuth( token )
	{
		var loading = $("#age_restriction-main-loading");
		loading.show(); 
		
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionRemoteSupportRequest',
			'sub_actions'	: 'check_auth',
			'params'		: {
				'token': token
			},
			'debug_level'	: debug_level
		}, function(response) {
			// if has a valid token
			if( response.status == 'valid' ){
				$("#age_restriction-boxid-ticket").show();
				$("#age_restriction-boxid-logininfo").hide();
			}
			
			// show the auth box
			else{
				$("#age_restriction-boxid-ticket").hide();
				$("#age_restriction-boxid-logininfo .age_restriction-message").html( 'In order to contact AA-Team support team you need to login into support.aa-team.com' );
				$("#age_restriction-boxid-login").show();
				$("#age_restriction-boxid-register").show();
			}
			loading.hide();
		}, 'json'); 
	}

	function triggers()
	{
		maincontainer.on('submit', '#age_restriction-form-login', function(e){
			e.preventDefault();

			remote_login( $(this) );
		});
		
		maincontainer.on('submit', '#age_restriction-form-register', function(e){
			e.preventDefault();

			remote_register_and_login( $(this) );
		});
		
		maincontainer.on('submit', '#age_restriction_access_details', function(e){
			e.preventDefault();

			access_details( $(this) );
		});
		
		maincontainer.on('change', '#age_restriction-create_wp_credential', function(e){
			e.preventDefault();

			var that = $(this);
			
			if( that.val() == 'yes' ){
				$(".age_restriction-wp-credential").show();
			}else{
				$(".age_restriction-wp-credential").hide();
			}
		});
		
		maincontainer.on('change', '#age_restriction-allow_file_remote', function(e){
			e.preventDefault();

			var that = $(this);
			
			if( that.val() == 'yes' ){
				$(".age_restriction-file-access-credential").show();
			}else{
				$(".age_restriction-file-access-credential").hide();
			}
		});
		
		maincontainer.on('submit', '#age_restriction_add_ticket', function(e){
			e.preventDefault();

			open_ticket( $(this) );
		});
	}

	// external usage
	return {
		'checkAuth': checkAuth,
		'token' : token
    }
})(jQuery);
