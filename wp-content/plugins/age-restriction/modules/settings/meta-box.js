jQuery(document).ready(function($) {

	var age_restriction_launch_search = function (data) {
		var searchAjaxLoader 	= jQuery("#age_restriction-ajax-loader"),
			searchBtn 			= jQuery("#age_restriction-search-link");
			
		searchBtn.hide();	
		searchAjaxLoader.show();
		
		var data = {
			action: 'amazon_request',
			search: jQuery('#age_restriction-search').val(),
			category: jQuery('#age_restriction-category').val(),
			page: ( parseInt(jQuery('#age_restriction-page').val(), 10) > 0 ? parseInt(jQuery('#age_restriction-page').val(), 10) : 1 )
		};
		
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#age_restriction-ajax-results").html(response);
			
			searchBtn.show();	
			searchAjaxLoader.hide();
		});
	};
	
	jQuery('body').on('change', '#age_restriction-page', function (e) {
		age_restriction_launch_search();
	});
	
	jQuery("#age_restriction-search-form").submit(function(e) {
		age_restriction_launch_search();
		return false;
	});
	
	jQuery('body').on('click', 'a.age_restriction-load-product', function (e) {
		e.preventDefault();
		
		var data = {
			'action': 'age_restriction_load_product',
			'ASIN':  jQuery(this).attr('rel')
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: data,
			success: function(response) {
				if(response.status == 'valid'){
					window.location = response.redirect_url;
					return true;
				}else{
					alert(response.msg);
					return false
				}
			}
		});
	});
});