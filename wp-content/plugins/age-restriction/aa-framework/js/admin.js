/*
    Document   :  aaFreamwork
    Created on :  August, 2013
    Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
age_restriction = (function ($) {
    "use strict";

	var option = {
		'prefix': "age_restriction"
	};
	
    var t = null,
        ajaxBox = null,
        section = 'dashboard',
        in_loading_section = null,
        topMenu = null;
        
    var upload_popup_parent = null;

    function init() 
    {
        $(document).ready(function(){
        	
        	t = $("div.wrapper-age_restriction");
	        ajaxBox = t.find('#age_restriction-ajax-response');
	        topMenu = t.find('#age_restriction-topMenu');
	        
	        if (t.size() > 0 ) {
	            fixLayoutHeight();
	        }
	        
	        // plugin depedencies if default!
	        if ( $("li#age_restriction-nav-depedencies").length > 0 ) {
	        	section = 'depedencies';
	        }
	        
	        triggers();
        });
    }
    
    function ajaxLoading(status) 
    {
        var loading = $('<div id="age_restriction-ajaxLoadingBox" class="age_restriction-panel-widget">loading</div>'); // append loading
        ajaxBox.html(loading);
    }
    
    function makeRequest() 
    {
		// fix for duble loading of js function
		if( in_loading_section == section ){
			return false;
		}
		in_loading_section = section;
		
		// do not exect the request if we are not into our ajax request pages
		if( ajaxBox.size() == 0 ) return false;

        ajaxLoading();
        var data = {
            'action': 'age_restrictionLoadSection',
            'section': section
        }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {
            if (response.status == 'ok') {
            	$("h1.age_restriction-section-headline").html(response.headline);
                ajaxBox.html(response.html);

                makeTabs();
                
                if( typeof age_restrictionDashboard != "undefined" ){
					age_restrictionDashboard.init();
				}
				
                // find new open
                var new_open = topMenu.find('li#age_restriction-sub-nav-' + section);
                var in_submenu = new_open.parent('.age_restriction-sub-menu');
                
                // close current open menu
                var current_open = topMenu.find(">li.active");
                if( current_open != in_submenu.parent('li') ){
					current_open.find(".age_restriction-sub-menu").slideUp(250);
					current_open.removeClass("active");
				}
				
				// open current menu
				in_submenu.find('.active').removeClass('active');
				new_open.addClass('active');
				
				// check if is into a submenu
				if( in_submenu.size() > 0 ){
					if( !in_submenu.parent('li').hasClass('active') ){
						in_submenu.slideDown(100);
					}
					in_submenu.parent('li').addClass('active');
				}
				
				if( section == 'dashboard' ){
					topMenu.find(".age_restriction-sub-menu").slideUp(250);
					topMenu.find('.active').removeClass('active');
					
					topMenu.find('li#age_restriction-nav-' + section).addClass('active');
				}
				
				multiselect_left2right();
            }
        },
        'json');
    }
    
    function installDefaultOptions($btn) {
        var theForm = $btn.parents('form').eq(0),
            value = $btn.val(),
            statusBoxHtml = theForm.find('div.age_restriction-message'); // replace the save button value with loading message
        $btn.val('installing default settings ...').removeClass('blue').addClass('gray');
        if (theForm.length > 0) { // serialiaze the form and send to saving data
            var data = {
                'action': 'age_restrictionInstallDefaultOptions',
                'options': theForm.serialize()
            }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function (response) {
                if (response.status == 'ok') {
                    statusBoxHtml.addClass('age_restriction-success').html(response.html).fadeIn().delay(3000).fadeOut();
                    setTimeout(function () {
                        window.location.reload()
                    },
                    2000);
                } else {
                    statusBoxHtml.addClass('age_restriction-error').html(response.html).fadeIn().delay(13000).fadeOut();
                } // replace the save button value with default message
                $btn.val(value).removeClass('gray').addClass('blue');
            },
            'json');
        }
    }
    
    function saveOptions ($btn, callback) 
    {
        var theForm = $btn.parents('form').eq(0),
            value = $btn.val(),
            statusBoxHtml = theForm.find('div#age_restriction-status-box'); // replace the save button value with loading message
        	$btn.val('saving setings ...').removeClass('green').addClass('gray');
        
        multiselect_left2right(true);
  
        if (theForm.length > 0) { // serialiaze the form and send to saving data
            var data = {
                'action': 'age_restrictionSaveOptions',
                'options': theForm.serialize()
            }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
            jQuery.post(ajaxurl, data, function (response) {
                if (response.status == 'ok') {
                    statusBoxHtml.addClass('age_restriction-success').html(response.html).fadeIn().delay(3000).fadeOut();
                    if (section == 'synchronization') {
                        updateCron();
                    }
                    
                } // replace the save button value with default message
                $btn.val(value).removeClass('gray').addClass('green');
                
                if( typeof callback == 'function' ){
                	callback.call();
                }
            },
            'json');
        }
    }
    
    function moduleChangeStatus($btn) 
    {
        var value = $btn.text(),
            the_status = $btn.hasClass('activate') ? 'true' : 'false'; // replace the save button value with loading message
        $btn.text('saving setings ...');
        var data = {
            'action': 'age_restrictionModuleChangeStatus',
            'module': $btn.attr('rel'),
            'the_status': the_status
        }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {
            if (response.status == 'ok') {
                window.location.reload();
            }
        },
        'json');
    }
    
    function updateCron() 
    {
        var data = {
            'action': 'age_restrictionSyncUpdate'
        }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {},
        'json');
    }
    
    function fixLayoutHeight() 
    {
        var win = $(window),
            age_restrictionWrapper = $("#age_restriction-wrapper"),
            minusHeight = 40,
            winHeight = win.height(); // show the freamwork wrapper and fix the height
        age_restrictionWrapper.css('min-height', parseInt(winHeight - minusHeight)).show();
        $("div#age_restriction-ajax-response").css('min-height', parseInt(winHeight - minusHeight - 240)).show();
    }
    
    function activatePlugin( $that ) 
    {
        var requestData = {
            'ipc': $('#productKey').val(),
            'email': $('#yourEmail').val()
        };
        if (requestData.ipc == "") {
            alert('Please type your Item Purchase Code!');
            return false;
        }
        $that.replaceWith('Validating your IPC <em>( ' + (requestData.ipc) + ' )</em>  and activating  Please be patient! (this action can take about <strong>10 seconds</strong>)');
        var data = {
            'action': 'age_restrictionTryActivate',
            'ipc': requestData.ipc,
            'email': requestData.email
        }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {
            if (response.status == 'OK') {
                window.location.reload();
            } else {
                alert(response.msg);
                return false;
            }
        },
        'json');
    }
    
    function ajax_list()
	{
		var make_request = function( action, params, callback ){
			var loading = $("#age_restriction-main-loading");
			loading.show();
 
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, {
				'action' 		: 'age_restrictionAjaxList',
				'ajax_id'		: $(".age_restriction-table-ajax-list").find('.age_restriction-ajax-list-table-id').val(),
				'sub_action'	: action,
				'params'		: params
			}, function(response) {
   
				if( response.status == 'valid' )
				{
					$("#age_restriction-table-ajax-response").html( response.html );

					loading.fadeOut('fast');
				}
			}, 'json');
		}

		$(".age_restriction-table-ajax-list").on('change', 'select[name=age_restriction-post-per-page]', function(e){
			e.preventDefault();

			make_request( 'post_per_page', {
				'post_per_page' : $(this).val()
			} );
		})

		.on('change', 'select[name=age_restriction-filter-post_type]', function(e){
			e.preventDefault();

			make_request( 'post_type', {
				'post_type' : $(this).val()
			} );
		})

		.on('click', 'a.age_restriction-jump-page', function(e){
			e.preventDefault();

			make_request( 'paged', {
				'paged' : $(this).attr('href').replace('#paged=', '')
			} );
		})

		.on('click', '.age_restriction-post_status-list a', function(e){
			e.preventDefault();

			make_request( 'post_status', {
				'post_status' : $(this).attr('href').replace('#post_status=', '')
			} );
		});
	}
	
	function amzCheckAWS()
	{
		return true;
	}
	
	function removeHelp()
	{
		$("#age_restriction-help-container").remove();	
	}
	
	function showHelp( that )
	{
		removeHelp();
		var help_type = that.data('helptype');
        var html = $('<div class="age_restriction-panel-widget" id="age_restriction-help-container" />');
        html.append("<a href='#close' class='age_restriction-button red' id='age_restriction-close-help'>Close HELP</a>")
		if( help_type == 'remote' ){
			var url = that.data('url');
			var content_wrapper = $("#age_restriction-content");
			
			html.append( '<iframe src="' + ( url ) + '" style="width:100%; height: 100%;border: 1px solid #d7d7d7;" frameborder="0"></iframe>' )
			
			content_wrapper.append(html);
		}
	}
	
	function multiselect_left2right( autselect ) {
		var $allListBtn = $('.multisel_l2r_btn');
		var autselect = autselect || false;
 
		if ( $allListBtn.length > 0 ) {
			$allListBtn.each(function(i, el) {
 
				var $this = $(el), $multisel_available = $this.prevAll('.age_restriction-multiselect-available').find('select.multisel_l2r_available'), $multisel_selected = $this.prevAll('.age_restriction-multiselect-selected').find('select.multisel_l2r_selected');
 
				if ( autselect ) {
					$multisel_selected.find('option').each(function() {
						$(this).prop('selected', true);
					});
					$multisel_available.find('option').each(function() {
						$(this).prop('selected', false);
					});
				} else {

				$this.on('click', '.moveright', function(e) {
					e.preventDefault();
					$multisel_available.find('option:selected').appendTo($multisel_selected);
				});
				$this.on('click', '.moverightall', function(e) {
					e.preventDefault();
					$multisel_available.find('option').appendTo($multisel_selected);
				});
				$this.on('click', '.moveleft', function(e) {
					e.preventDefault();
					$multisel_selected.find('option:selected').appendTo($multisel_available);
				});
				$this.on('click', '.moveleftall', function(e) {
					e.preventDefault();
					$multisel_selected.find('option').appendTo($multisel_available);
				});
				
				}
			});
		}
	}
	
	function makeTabs( filter )
	{
		var filter = filter || '';

		$('ul.tabsHeader').each(function() {
			// For each set of tabs, we want to keep track of
			// which tab is active and it's associated content
			var $active, $content, $links = $(this).find('a');

			// If the location.hash matches one of the links, use that as the active tab.
			// If no match is found, use the first link as the initial active tab.
			var __tabsWrapper = $(this), __currentTab = $(this).find('li#tabsCurrent').attr('title');
			$active = $( $links.filter('[title="'+__currentTab+'"]')[0] || $links[0] );
			$active.addClass('active');
			$content = $( '.'+($active.attr('title')) );

			// Hide the remaining content
			$links.not($active).each(function () {
				$( '.'+($(this).attr('title')) ).hide();
			});
 
			if ( filter != '' ) {
				var filter_css = filter.cssPrefix + filter.el.data('current');
				$content.not( filter_css ).hide();
			}

			// Bind the click event handler
			$(this).on('click', 'a', function(e){
				// Make the old tab inactive.
				$active.removeClass('active');
				$content.hide();
 
				// Update the variables with the new link and content
				__currentTab = $(this).attr('title');
				__tabsWrapper.find('li#tabsCurrent').attr('title', __currentTab);
				$active = $(this);
				$content = $( '.'+($(this).attr('title')) );

				// Make the tab active.
				$active.addClass('active');
				if ( filter != '' ) {
					var filter_css = filter.cssPrefix + filter.el.data('current');
					$content.filter( filter_css ).show();
				} else {
					$content.show();
				}

				// Prevent the anchor's default click action
				e.preventDefault();
			});
		});
	}
	
	function send_to_editor()
	{
		if( window.send_to_editor != undefined ) {
			// store old send to editor function
			window.restore_send_to_editor = window.send_to_editor;	
		}

		window.send_to_editor = function(html){
			if( typeof( $(html).attr('class') ) == "undefined" ) { 
				var thumb_id = $('img', html).attr('class').split('wp-image-');
			} else {
				var thumb_id = $(html).attr('class').split('wp-image-');
			}
			
			thumb_id = parseInt(thumb_id[1]);
			
			$.post(ajaxurl, {
				'action' : 'age_restrictionWPMediaUploadImage',
				'att_id' : thumb_id
			}, function(response) {
				if (response.status == 'valid') {
					
					var upload_box = upload_popup_parent.parents('.age_restriction-upload-image-wp-box').eq(0);
					
					upload_box.find('input').val( thumb_id );
					
					var the_preview_box = upload_box.find('.upload_image_preview'),
						the_img = the_preview_box.find('img');
						
					the_img.attr('src', response.thumb );
					the_img.show();
					the_preview_box.show();
					upload_box.find('.age_restriction-prev-buttons').show();
					upload_box.find(".age_upload_image_button_wp").hide();
				
				}
			}, 'json');
			
			tb_remove();
			
			if( window.restore_send_to_editor != undefined ) {
				// store old send to editor function
				window.restore_send_to_editor = window.send_to_editor;	
			}
		}
	}
	
	function removeWpUploadImage( $this )
	{
		var upload_box = $this.parents(".age_restriction-upload-image-wp-box").eq(0);
		upload_box.find('input').val('');
		var the_preview_box = upload_box.find('.upload_image_preview'),
			the_img = the_preview_box.find('img');
			
		the_img.attr('src', '');
		the_img.hide();
		the_preview_box.hide();
		upload_box.find('.age_restriction-prev-buttons').hide();
		upload_box.find(".age_upload_image_button_wp").fadeIn('fast');
	}
	
	function fake_upload_image( input, new_img )
	{
		var $html = '';
		
		var upload_box = input.parents('.age_restriction-upload-image-wp-box').eq(0);

		upload_box.find('input').val( new_img );
		
		var $img = '<a href="' + ( new_img ) + '" target="_blank" class="upload_image_preview" style="display: block; max-width: 150px; max-height: 150px;">';
		$img += 	'<img src="' + ( new_img ) + '">';	
		$img += '</a>';
		
		upload_box.find( '.upload_image_preview' ).remove();
		input.after( $img );
		
		var the_preview_box = upload_box.find('.upload_image_preview'),
			the_img = the_preview_box.find('img');
			
		the_img.attr('src', new_img );
		the_img.show();
		the_preview_box.show();
		upload_box.find('.age_restriction-prev-buttons').show();
		upload_box.find(".age_upload_image_button_wp").hide();
	}
	
	function image2default( that )
	{
		var selected 	= that.val(),
			$html 		= "",
			logo 		= that.find('option:selected').data('logo-src'),
			bg 			= that.find('option:selected').data('bg-src');
		
		// check if have logo image
		var logo_value = $("input[name='logo']").val();
		fake_upload_image( $("input[name='logo']"), logo ); 
		
		var bg_value = $("input[name='background_image']").val();
		fake_upload_image( $("input[name='background_image']"), bg );
	}
	
    function triggers() 
    {
    	$('body').on('change', "#theme", function(){
    		image2default( $(this) ); 
    	});
    	
		$('body').on('click', '.age_upload_image_button_wp, .age_change_image_button_wp', function(e) {
			e.preventDefault();
			upload_popup_parent = $(this);
			var win = $(window);
			
			send_to_editor();
		
			tb_show('Select image', 'media-upload.php?type=image&amp;height=' + ( parseInt(win.height() / 1.2) ) + '&amp;width=610&amp;post_id=0&amp;from=aaframework&amp;TB_iframe=true');
		});
		
		$('body').on('click', '.remove_image_button_wp', function(e) {
			e.preventDefault();
			
			removeWpUploadImage( $(this) );
		});
		
		if ( typeof jQuery.fn.tipsy != "undefined" ) { // verify tipsy plugin is defined in jQuery namespace!
			$('a.aa-tooltip').tipsy({
				gravity: 'e'
			});
		}
    	
        $(window).resize(function () {
            fixLayoutHeight();
        });
         
		$('body').on('click', '.age_restriction_activate_product', function (e) {
            e.preventDefault();
            activatePlugin($(this));
        });
		$('body').on('click', '.age_restriction-saveOptions', function (e) {
            e.preventDefault();
            saveOptions($(this));
        });
        $('body').on('click', '.age_restriction-installDefaultOptions', function (e) {
            e.preventDefault();
            installDefaultOptions($(this));
        });
		
		$('body').on('click', '#' + option.prefix + "-module-manager a", function (e) {
            e.preventDefault();
            moduleChangeStatus($(this));
        }); // Bind the event.
        
        $(window).hashchange(function () { // Alerts every time the hash changes!
            if (location.hash != "") {
                section = location.hash.replace("#!/", '');
                if( t.size() > 0 ) {
                	makeRequest();
                }
            }else{
	            if( t.size() > 0 && location.search == "?page=age_restriction" ){
	            	makeRequest();
	            }
            }
        }) // Trigger the event (useful on page load).
        
        $(window).hashchange();
        
        ajax_list();
        
		$("body").on('click', "a.age_restriction-show-docs-shortcut", function(e){
        	e.preventDefault();
        	
        	$("a.age_restriction-show-docs").click();
        });
        
        $("body").on('click', "a.age_restriction-show-docs", function(e){
        	e.preventDefault();
        	
        	showHelp( $(this) );
        });
        
         $("body").on('click', "a#age_restriction-close-help", function(e){
        	e.preventDefault();
        	
        	removeHelp();
        });
        
        multiselect_left2right();
        
        $("body").on('click', '.age_restrictionClearStatistics', function(e) {
        	e.preventDefault();
        	
	        var data = {
	            'action': 'age_restrictionClearStatistics'
	        }; // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	        jQuery.post(ajaxurl, data, function (response) {
	            if (response.status) {
	            	alert(response.msg);
	            }else{
	            	alert("Error executing action!")
	            }
	        },
	        'json');
        });
    }
	
   	init();
   	
   	return {
   		'init'						: init,
   		'makeTabs'					: makeTabs,
   		'multiselect_left2right'	: multiselect_left2right,
   		'image2default'				: image2default
   	}
})(jQuery);