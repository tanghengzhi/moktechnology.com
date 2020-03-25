// Iterate over each select element
function initCustomSelect() {
	$('select').each(function () {
	
	    // Cache the number of options
	    var $this = $(this),
	        numberOfOptions = $(this).children('option').length;
	
	    // Hides the select element
	    $this.addClass('s-hidden');
	
	    // Wrap the select element in a div
	    $this.wrap('<div class="country-sel-bx"><div class="select"></div></div>');
	
	    // Insert a styled div to sit over the top of the hidden select element
	    $this.after('<div class="styledSelect"></div>');
	
	    // Cache the styled div
	    var $styledSelect = $this.next('div.styledSelect');
	
	    // Show the first select option in the styled div if none selected
	    if( $(this).find('option:selected').val() != '' ) {
	    	$styledSelect.text( $(this).find('option:selected').text() );
	    }else{
	    	$styledSelect.text($this.children('option').eq(0).text());
	    }
	
	    // Insert an unordered list after the styled div and also cache the list
	    var $list = $('<ul />', {
	        'class': 'options'
	    }).insertAfter($styledSelect);
		
	    // Insert a list item into the unordered list for each select option
		for (var i = 0; i < numberOfOptions; i++) {
			if( typeof $this.children('option').eq(i).attr('disabled') == 'undefined' ) {
				$('<li />', {
					text: $this.children('option').eq(i).text(),
					rel: $this.children('option').eq(i).val()
				}).appendTo($list);
			}
		}
	
	    // Cache the list items
	    var $listItems = $list.children('li');
	    var keypress = [],
	    	int_reset = 0;
		
	    // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
	    $styledSelect.click(function (e) {
	        e.stopPropagation();

	        keypress = [];
	        var that2 = $(this);
	        
	        if( $(this).next('ul.options').css('display') == 'block' ){
	        	$list.hide();
	        	return;
	        }

	        $list.css({'width' : ($('.country-sel-bx').outerWidth() - 2) + 'px'});
	        
	        $('div.styledSelect.active').each(function () {
	            $(this).removeClass('active').next('ul.options').hide();
	        });
	        $(this).toggleClass('active').next('ul.options').toggle();

	        $(document).unbind('keypress').bind('keypress',function(e) {

		    	clearTimeout( int_reset );

				var key = String.fromCharCode(e.which);
				if( that2.next('ul.options').css('display')   == 'block' ){
					keypress.push( key );

					jump_to_element( keypress.join(""), that2.next('ul.options') );
				}
			});
	    });

	    function jump_to_element( keyword, options ){
			keyword = keyword.toLowerCase();

			int_reset = setTimeout( function(){
	    		keypress = [];
	    	}, 2000);
			options.find("li").each(function(){
				var that = $(this),
			       val = that.text().toLowerCase(),
			       first_letter = val.substring(0, keyword.length);
			
			   	if( first_letter == keyword ){
			   		options.scrollTop( 0 );
			    	var pos = that.position();

			    	options.scrollTop( pos.top );
			    	return false;
			   	}
			});

			last_time_search = new Date().getTime();
	    }
	
	    // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
	    // Updates the select element to have the value of the equivalent option
	    $listItems.click(function (e) {
			e.stopPropagation();
			$styledSelect.text($(this).text()).removeClass('active');
			$this.val($(this).attr('rel'));
			$list.hide();
	    });
	
	    // Hides the unordered list when clicking outside of it
	    $(document).click(function () {
	        $styledSelect.removeClass('active');
	        $list.hide();
	    });
	    
	});
}

function set_location( response ){
	if( typeof response.location != "undefined" ){
		var location = response.location;
		
		if( typeof location.name != "undefined" ){
			var country = location.name.split(",");
			
			country = jQuery.trim( country[1] );
			if( typeof country != "undefined" ){
				var found = false;
				jQuery("select[name='age_restriction_country'] option").each(function(){
					if( found == false ){
						if( $(this).text() == country ){
							found = true;
							jQuery("select[name='age_restriction_country']").val( $(this).val() );
							$(this).attr('selected', 'selected');
							
							jQuery(".styledSelect").text( country );
						}
					}
				});
				
			}
		}
	}
}

$(document).ready(function() {
	
	if( !ageRestriction_isMobile.any && $(window).width() >= 768 ) {
		initCustomSelect();
	}
	
	// set main wrapper min-height to center box properly
    if( !ageRestriction_isMobile.any && $(window).height() <= 980 ) {
		$('#wrapper').css({
			minHeight: $('.ar-box').outerHeight() + 50
		});
	}
	
	$('body').on('click', '.agecheck_submit', function(e) {
		e.preventDefault();
		
		$('input[name="age_restriction_confirmation"]').val( $(this).data('value') );
		jQuery('form#agecheck').submit();
		
		return false;
	});
	
    $('a.close, #fade').on('click', function(e) {
    	e.preventDefault();
        $('#fade, .popup-message').fadeOut();
        return false;
    });
    
    $('input[name="confirmation"]').on('click', function(e) {
    	var $this = $(this).val();
    	
    	$('.ar-box .ar-form-row label').removeClass('active');
    	$(this).prev().addClass('active');
    });
    
	if( jQuery('#age_restriction_fbconnect').length > 0 ) {
		jQuery(document).on('click', '#age_restriction_fbconnect', function(e) {
			e.preventDefault();
			
			FB.getLoginStatus(function(response) {
				if (response.status === 'connected') {
					 FB.api('/me', 'get', {fields: 'first_name,last_name,email,gender,birthday,location'}, function(response) {
					 	
					 	if( typeof response.birthday != 'undefined' ) {
						
							var fb_details = {
						 		'verify_source' : 'facebook',
						 		'first_name'	: response.first_name, 
						 		'last_name'		: response.last_name, 
						 		'email' 		: response.email, 
						 		'gender' 		: response.gender,
								'birthday' 		: response.birthday
						 	};
						 	
						 	jQuery('#social_details').val( JSON.stringify(fb_details) );
						 	
						 	set_location( response );
						 	
							setBirthdayAndSubmit(response.birthday, 'MM-DD-YYYY', '/');
							
						}else{
							errorMsg = typeof errorMsg == 'undefined' ? 'Your birthday could not be retrived. Either is not public or is not set.' : errorMsg;
							
							jQuery('#fade').show();
			            	jQuery('.popup_content').html('<p>' + errorMsg + '</p>');
			            	jQuery('.popup-message').show();
						}
						
					});
				}else {
					FB.login(function(response) {
						if (response.authResponse) {
							FB.api('/me', 'get', {fields: 'first_name,last_name,email,gender,birthday,location'}, function(response) {
								
								if( typeof response.birthday != 'undefined' ) {
							
									var fb_details = {
										'verify_source' : 'facebook',
								 		'first_name'	: response.first_name, 
								 		'last_name'		: response.last_name, 
								 		'email' 		: response.email, 
								 		'gender' 		: response.gender,
										'birthday' 		: response.birthday
								 	};
								 	
								 	jQuery('#social_details').val( JSON.stringify(fb_details) );
								 	
								 	set_location( response );
								 	
									setBirthdayAndSubmit(response.birthday, 'MM-DD-YYYY', '/');
									
								}else{
									errorMsg = typeof errorMsg == 'undefined' ? 'Your birthday could not be retrived. Either is not public or is not set.' : errorMsg;
									
									jQuery('#fade').show();
					            	jQuery('.popup_content').html('<p>' + errorMsg + '</p>');
					            	jQuery('.popup-message').show();
								}
								
							});
						}else{
							console.log('User cancelled login or did not fully authorize.');
						}
					}, {scope: 'public_profile,email,user_birthday,user_location'});
				}
			});
			
			return false;
		});
	}
});

// set main wrapper min-height to center box properly
$(window).resize(function() {
	if( !ageRestriction_isMobile.any && $(window).height() <= 980 ) {
		$('#wrapper').css({
			minHeight: $('.ar-box').outerHeight() + 50
		});
	}
});

$(window).load(function(){
	$(".preloader").hide();
});

function getAge(today, birthDate) {
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function setBirthdayAndSubmit(birthday, format, splitter) {
	
	var _birthday = birthday,
		birthday = birthday.split(splitter),
		age = 0;
	
	if( format == 'MM-DD-YYYY' ) {
		jQuery('input[name="age_restriction_day"]').val(birthday[1]);
		jQuery('input[name="age_restriction_month"]').val(birthday[0]);
		jQuery('input[name="age_restriction_year"]').val(birthday[2]);
		
	}else if( format == 'YYYY-MM-DD' ) {
		jQuery('input[name="age_restriction_day"]').val(birthday[2]);
		jQuery('input[name="age_restriction_month"]').val(birthday[1]);
		jQuery('input[name="age_restriction_year"]').val(birthday[0]);
	}
	
	var today = new Date();
    var birthDate = new Date(_birthday);
    
    age = getAge( today, birthDate );
    if( age > 0 ){
    	if( jQuery( "#min-age" ).val() <= age ){
    		$('input[name="age_restriction_confirmation"]').val('yes');
    	}
    }
    jQuery('form#agecheck').submit();
}

var run = false;
function gPlusSigninCallback(authResult) {
	
	if( run == true ) return;
	run = true;
	
	if ( authResult['status']['signed_in'] ) {
		
		gapi.client.load('plus','v1', function() {
			var request = gapi.client.plus.people.get({
				'userId': 'me'
			});
			request.execute(function(resp) { 
				
				if( typeof resp.birthday != 'undefined' ) {
					
					var gplus_details = {
						'verify_source' : 'google',
				 		'first_name'	: resp.name.givenName, 
				 		'last_name'		: resp.name.familyName, 
				 		'email' 		: resp.emails.length > 0 ? resp.emails[0]['value'] : '', 
				 		'gender' 		: resp.gender,
						'birthday' 		: resp.birthday
				 	};
				 	
				 	jQuery('#social_details').val( JSON.stringify(gplus_details) );
					
					setBirthdayAndSubmit(resp.birthday, 'YYYY-MM-DD', '-');
				}else{
					errorMsg = typeof errorMsg == 'undefined' ? 'Your birthday could not be retrived. Either is not public or is not set.' : errorMsg;
					
					jQuery('#fade').show();
	            	jQuery('.popup_content').html('<p>' + errorMsg + '</p>');
	            	jQuery('.popup-message').show();
				}
			});
		});
		
	} else {
		
		// Update the app to reflect a signed out user
		// Possible error values:
		//   "user_signed_out" - User is signed-out
		//   "access_denied" - User denied access to your app
		//   "immediate_failed" - Could not automatically log in the user
		console.log('Sign-in state: ' + authResult['error']);
		
	}
}