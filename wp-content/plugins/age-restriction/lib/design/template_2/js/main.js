$(document).ready(function() {
    var winH = $(window).height();
    if (winH < 768) {
        $('.globalfooter').addClass('footer2');
    } else {
        $('.globalfooter').removeClass('footer2');
    }
});
$(document).ready(function() {
    $('#age-list-months li').click(function() {
        $('.fade-d').removeClass('fade-txt');
    });
    $('.feb-m').click(function() {
        $('.fade-d').addClass('fade-txt');
    });
    $('.feb-m1').click(function() {
        $('.fade-d1').addClass('fade-txt');
    });
    $('.select-country').click(function() {
        $(this).toggleClass('dse');
        if ($(this).is('.dse')) {
            $('.countrieslanguages').css('top', '30px');
            $(this).addClass('activecountry');
        } else {
            $('.countrieslanguages').css('top', '-1000px');
            $(this).removeClass('activecountry');
        }
    });
    if ((!Modernizr.input.placeholder) || $.browser.safari) {
        $('[placeholder]').focus(function() {
            var input = $(this);
            if (input.val() == input.attr('placeholder')) {
                input.val('');
                input.removeClass('placeholder');
            }
        }).blur(function() {
            var input = $(this);
            if (input.val() == '' || input.val() == input.attr('placeholder')) {
                input.addClass('placeholder');
                input.val(input.attr('placeholder'));
            }
        }).blur();
    };
    
    $('form').h5Validate();
    
    var listNodeStandard = $('.age-list li');
    var listNodeYear = $('.age-year li');
    
    $(listNodeStandard).bind('mouseover', function(e) {
        $(this).addClass('active');
    });
    $(listNodeStandard).bind('mouseout', function(e) {
        $(this).removeClass('active');
    });
    $(listNodeYear).bind('mouseover', function(e) {
        $(this).addClass('active');
    });
    $(listNodeYear).bind('mouseout', function(e) {
        $(this).removeClass('active');
    });
    $(listNodeStandard).bind('click', function(e) {
        $(this).parent().find('li').removeClass('selected');
        if (!$(this).hasClass('selected')) {
            $(this).addClass('selected');
        }
        if ($(this).parent().attr('id') == 'age-list-months') {
            $('#month').val($(listNodeStandard).index(this) + 1);
        } else {
            $('#day').val($(this).text());
        }
    });
    $(listNodeYear).bind('click', function(e) {
        if (!$(this).hasClass('shim' || 'gray')) {
            $('.age-year').parent().find('li').removeClass('selected');
        }
        if (!$(this).hasClass('selected') && !$(this).hasClass('shim' || 'gray')) {
            $(this).addClass('selected');
        }
        if (!$(this).hasClass('shim' || 'gray')) {
            $('#year').val($(this).text());
        }
    });
	
    $('a.close, #fade').live('click', function() {
        $('#fade, .popup-message').fadeOut();
        return false;
    });
});
$(window).load(function() {
	$(".preloader").hide();
	
	if( ageRestriction_isMobile.any ) {
		$('#age-list-months').wrap('<div class="es-carousel-wrapper" id="carousel-month" style="height:60px;"><div class="es-carousel"></div></div>');
		$('#age-list-days').wrap('<div class="es-carousel-wrapper" id="carousel-day"><div class="es-carousel"></div></div>');
		
		$('#carousel-month').elastislide({
	        current: 0,
	        imageW: 78,
	        margin: 0,
	        border: 0,
	    }); 
	    $('#carousel-day').elastislide({
	        current: 0,
	        imageW: 28,
	        margin: 0,
	        border: 2
	    });
	}
    $('#carousel').elastislide({
        current: 0,
        imageW: 44,
        margin: 2,
        border: 0
    });
    $('.input-area').fadeIn('normal');
    
    // set main wrapper min-height to center box properly
	if( !ageRestriction_isMobile.any && $(window).height() <= 980 ) {
		$('#wrapper').css({
			minHeight: $('#agecheck').outerHeight()
		});
	}
});

// set main wrapper min-height to center box properly
$(window).resize(function() {
	if( !ageRestriction_isMobile.any && $(window).height() <= 980 ) {
		$('#wrapper').css({
			minHeight: $('#agecheck').outerHeight()
		});
	}
});

function setBirthdayAndSubmit(birthday, format, splitter) {
	var _birthday = birthday.split(splitter),
		birthday = [];
		
	jQuery(_birthday).each(function(i) {
		birthday[i] = parseInt(_birthday[i]);
	});
	
	if( format == 'MM-DD-YYYY' ) {
		jQuery('input[name="age_restriction_day"]').val(birthday[1]);
		jQuery('input[name="age_restriction_month"]').val(birthday[0]);
		jQuery('input[name="age_restriction_year"]').val(birthday[2]);
	}else if( format == 'YYYY-MM-DD' ) {
		jQuery('input[name="age_restriction_day"]').val(birthday[2]);
		jQuery('input[name="age_restriction_month"]').val(birthday[1]);
		jQuery('input[name="age_restriction_year"]').val(birthday[0]);
	}
	
	jQuery('input[type="submit"]').click();
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