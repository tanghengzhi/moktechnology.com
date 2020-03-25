/*
Document	:  Banners Manager Frontend
Author		:  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
age_restrictionBMFront = (function ($) {
	"use strict";
	
	// public
	var debug_level = 0;
	var ajaxurl = '';
	var mainwrap = null;
    var maincontainer = null;
    var mainform = null;
    var mainprods = null;
    var loading = null;
    var is_admin_bar_showing = 'no';
    var searchNbTries = 5;
    var slide_obj = { mainwrap: {} };
    var scrollbar_width = 0;
    
	function setAjaxUrl( url ) {
		if ( typeof url != 'undefined' && url != '' )
			ajaxurl = url;
	}
	
	function getBrowserScrollWidth()
	{
	   var outer = document.createElement("div");
	    outer.style.visibility = "hidden";
	    outer.style.width = "100px";
	    outer.style.msOverflowStyle = "scrollbar"; // needed for WinJS apps
	
	    document.body.appendChild(outer);
	
	    var widthNoScroll = outer.offsetWidth;
	    // force scrollbars
	    outer.style.overflow = "scroll";
	
	    // add innerdiv
	    var inner = document.createElement("div");
	    inner.style.width = "100%";
	    outer.appendChild(inner);        
	
	    var widthWithScroll = inner.offsetWidth;
	
	    // remove divs
	    outer.parentNode.removeChild(outer);
	
	    return widthNoScroll - widthWithScroll;
	}

	function row_loading( row, status, extra )
	{
		var extra = extra || {};
		var isextra = ( typeof extra != 'undefined' ? misc.size(extra) == 3 ? true : false : false );
  
		if( status == 'show' ){
			if( row.size() > 0 ){
				if( row.find('.age_restriction-row-loading-marker').size() == 0 ){
					var html = '<div class="age_restriction-meter age_restriction-animate" style="width:30%; margin: 10px 0px 0px 30%;"><span style="width:100%"></span></div>';
					if ( isextra ) {
						html = html + extra.html;
					}
					var row_loading_box = $('<div class="age_restriction-row-loading-marker"><div class="age_restriction-row-loading">' + html + '</div></div>');
					row_loading_box.find('div.age_restriction-row-loading').css({
						'width': row.outerWidth(),
						'height': row.outerHeight()
					});
					row.prepend(row_loading_box);
				}
				if ( isextra ) {
					row.find('.age_restriction-row-loading-marker').find('div.age_restriction-row-loading').find( extra.id ).html( extra.msg );
				}
				row.find('.age_restriction-row-loading-marker').find('div.age_restriction-row-loading').css({
					'width': row.outerWidth(),
					'height': row.outerHeight()
				});
				row.find('.age_restriction-row-loading-marker').fadeIn('fast');
			}
		} else {
			row.find('.age_restriction-row-loading-marker').fadeOut('fast');
		}
	}
/*
	function row_loading_prods( objWrappers, status, extra ) {
		var extra = extra || {};
		var isextra = ( typeof extra != 'undefined' ? misc.size(extra) == 3 ? true : false : false );
		var prodLoading = objWrappers.mainwrap.find('.age_restriction-data-prod-loading').clone(true)
			.removeClass('age_restriction-data-prod-loading').addClass('age_restriction-prod-loading');
		var prodClose = objWrappers.mainwrap.find('.age_restriction-data-prod-close').clone(true)
			.removeClass('age_restriction-data-prod-close').addClass('age_restriction-prod-close');
			
 		var wrapper = null, wrapper_prods = null;
 		if ( objWrappers.banner_type == 'bar' ) {
 			wrapper = objWrappers.maincontainer;
 			wrapper_prods = objWrappers.mainprods; 
 		}
 		else {
 			wrapper = objWrappers.mainform;
 			wrapper_prods = objWrappers.mainform;
 		}
 		
 		var searchBtn = wrapper.find('.age_restriction-buttons').find('.age_restriction-search-button');
 		 
		if ( status == 'show' ) {
			wrapper_prods.find('.age_restriction-prod-close').fadeOut(200);
			objWrappers.mainprods.fadeOut(200);

			if( wrapper.find('.age_restriction-prod-loading').size() == 0 ) {
				wrapper.find('.age_restriction-buttons').append( prodLoading );
			}
			var loading = wrapper.find('.age_restriction-prod-loading');
			
			wrapper.find('.age_restriction-buttons').find('.age_restriction-search-button').hide();
			if ( loading.size() > 0 ) {
				searchBtn.hide();
				loading.show();
			}
			
		} else {
			if( wrapper_prods.find('.age_restriction-prod-close').size() == 0 ) {
				if ( objWrappers.banner_type == 'bar' )
					wrapper_prods.prepend( prodClose );
				else {
					wrapper_prods.append( prodClose );
				}
			}

			var loading = wrapper.find('.age_restriction-prod-loading');
			if ( loading.size() > 0 ) {
				loading.hide();
				searchBtn.show();
				
				wrapper.parents('.age_restriction-main-container').eq(0).animate({
					'scrollTop': ( objWrappers.mainprods.position().top - 200 )
				}, 200);
				
				objWrappers.mainprods.show();
			}
		}
	}
*/
	function wrapp_css_size( main_container )
	{ 
		var _orig_main_container = main_container;
		if( main_container.hasClass('age_restriction-type-bar') ){
			main_container = main_container.find('.age_restriction-product-list-wrapper');
		}
		
		var container_width = main_container.width(),
			container_height = main_container.height(),
			extra_class = ''; 
			
		
		if( container_width < 550 ){
			extra_class = 'sm';
		}
		
		if( container_width >= 550 ){
			extra_class = 'lg';
		}
		
		if( _orig_main_container.hasClass('age_restriction-type-bar') ){
			_orig_main_container.addClass( 'age_restriction-' + extra_class );
		}else{
			main_container.addClass( 'age_restriction-' + extra_class );
		}
		
	}
	
	function check_scrollbar( objWrappers, main_container, recheck )
	{
		var resetCss = {
			'padding-right':  scrollbar_width + 'px',
			'overflow': 'hidden',
		};
		if (objWrappers.banner_type == 'bar') {
			resetCss.overflow = 'initial';
		}
				
		function fix_scrollbar( that, e ){
			
			var __css = {
				'padding-right': '0px',
				'overflow': 'auto',
			};
			that.css( __css );
			if (objWrappers.banner_type == 'bar') {
				__css.overflow = 'initial';
			}
			
		    if (e.type == 'mouseenter'){
		    	if( that.hasScrollBar().vertical != true ){
		    		that.css(resetCss);
		    	}
		    }
		    
		    if (e.type == 'mouseleave'){
		    	that.css(resetCss);
		    }
		}
		 
		if( recheck == true ){
			
			var __css = {
				'padding-right': '0px',
				'overflow': 'auto'
			};
			main_container.css( __css );
			if (objWrappers.banner_type == 'bar') {
				__css.overflow = 'initial';
			}
			
			if( main_container.hasScrollBar().vertical != true ){
	    		main_container.css(resetCss);
	    	}
			
		}else{
			main_container.live('hover', function (e){
				fix_scrollbar( $(this).find(".age_restriction-main-container"), e );
			});
		}
	}
    
	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function() {
			ajaxurl = age_restrictionBMFrontend_ajaxurl;
			
			// go through all banners on this page!
			var banners_list = $('.age_restriction-banners-list'), banners_opt = $('#age_restriction-banners-options').data('options');
			//console.log( banners_list, banners_opt );
			if ( typeof banners_opt != 'undefined' ) is_admin_bar_showing = banners_opt.is_admin_bar_showing;
 
			banners_list.each(function() {
				var $this = $(this),
				banner_type = $this.data('type'), opt = banners_opt[banner_type];
  
				mainwrap = $this;
				maincontainer = $this.find(".age_restriction-search-box-wrapper").parent();
				mainform = maincontainer.find("form.age_restriction-search-form");
				mainprods = $this.find(".age_restriction-product-list-wrapper");
				//if ( banner_type == 'bar' ) mainprods = $this.next(".age_restriction-product-list-wrapper");
				//else mainprods = $this.find(".age_restriction-product-list-wrapper");
				//console.log( mainwrap, maincontainer, mainform, mainprods );
				
				var objWrappers = {
					banner_type		: banner_type,
					mainwrap		: mainwrap,
					maincontainer	: maincontainer,
					mainform		: mainform,
					mainprods		: mainprods,
					opt				: $.extend(opt, {
						'is_admin_bar_showing'	: is_admin_bar_showing,
						'banner_box_opened'		: 'yes'
					}),
					amzcfg			: opt.amzcfg
				};
				if ( $.inArray(banner_type, ['slide', 'popup', 'bar']) > - 1 ) {
					objWrappers = $.extend( objWrappers, {
						slide_box 	: maincontainer,
						slide_icon	: $.inArray(banner_type, ['slide']) > - 1 ? mainwrap.next(".age_restriction-openside") : null
					} );
				}
				//console.log( objWrappers );

				// fix for external search
				// external products search
/*
				if (objWrappers.opt.banner_search_type == 'external' ) {
					objWrappers.mainform.find("input[name='age_restrictionParameter[Manufacturer]']").parent().css({'display': 'none'});
				}
*/

				// banner events!
				triggers( objWrappers );
				
				// position the banner on page!
				if ( $.inArray(banner_type, ['content_top', 'content_bottom', 'slide', 'bar', 'popup']) > -1 ) {
					build_banner_position( objWrappers );
 				}

				// popup
 				if ( banner_type == 'popup' ) {

					var o = objWrappers.opt, po = {};

					//for (var i in o) if (misc.hasOwnProperty(o, i)) o.i = $.trim( o.i );
					for (var i in o) {
						if (misc.hasOwnProperty(o, i)) {
							o[i] = $.trim( o[i] );
							if ( o[i] != '' ) {
								 
								//if ( i == 'popup_overlay_opacity' ) po.fadeOpacity = o[i];
								if ( i == 'background_color' ) po.fadeBackgroundColor = o[i];
								if ( i == 'background_image' ) po.fadeBackground = o[i];
								if ( i == 'box_background_color' ) po.boxBackgroundColor = o[i];
								if ( i == 'box_background_opacity' ) po.boxBackgroundOpacity = o[i];
								//if ( i == 'position_size_width' ) po.boxWidth = o[i];
								//if ( i == 'position_size_height' ) po.boxHeight = o[i];
								//if ( i == 'popup_show_btn_close' ) po.showBtnClose = o[i];
								//if ( i == 'popup_extended_close' ) po.extendedClose = o[i];
								
								if ( i == 'effect_open' ) po.effect_open = o[i];
								if ( i == 'effect_open_duration' ) po.effect_open_duration = parseInt( o[i] );
								if ( i == 'effect_close' ) po.effect_close = o[i];
								if ( i == 'effect_close_duration' ) po.effect_close_duration = parseInt( o[i] );
							}
							
							//if ( i == 'popup_autoclose' ) po.auto_close_time = parseInt( (o[i] != '' ? o[i] : 0) * 1000 );
						}
					}
 
					// init smartPopup
					po = jQuery.extend(po, {});
					localStorage.setItem('popupPms', JSON.stringify( po ));
					
					// count down: open delay
					if ( o.popup_open_delay > 0 ) {
						if ( jQuery('#age_restriction-pietimerholder').size() <= 0 ) {
							jQuery("body").append('<div id="age_restriction-pietimerholder"> </div>');
							jQuery("body").find('#age_restriction-pietimerholder').css({top: '30px'});
						}
						jQuery("body").find('#age_restriction-pietimerholder').html('auto open<br />');
						canvasPieTimer.action = 'close_open_delay';
						canvasPieTimer.timeLimit = parseInt( o.popup_open_delay * 1000 );
						canvasPieTimer.init(50, "age_restriction-canvaspietimer", "age_restriction-pietimerholder", canvasPieTimer.timeLimit);
					} else {
						smartPopup( po );
					}
 				} // end popup
			}); // end each
		});
	})();
	
	function build_banner_position( objWrappers, status ) {
		var status = status || '';
		var o = objWrappers.opt, banner_type = objWrappers.banner_type;
 
		// general variables
		var winW = $(window).width(), winH = $(window).height(),
		winTop = $(window).scrollTop(), winBottom = parseInt( winTop + $(window).height() ), winLeft = $(window).scrollLeft(),
		adminBarH = ( o.is_admin_bar_showing == 'yes' ? 30 : 0 );
		var css = {
			mainwrap: {}, maincontainer: {}, mainprods: {}, mainform: {},
			slide_box: {}, slide_icon: {}
		};
		
		var isBarPercentage = ( banner_type == 'bar' && o.position_size_type == 'proc' ? true : false );
 
		// box size! (banner & products)
		var __size = [], __boxsize = [];
		if ( banner_type == 'bar' ) {
			__size = [o.position_size_width, o.position_size_prods_width, o.position_size_prods_height];
			__boxsize = ['mainform@width', 'mainprods@width', 'mainprods@height'];
		} else {
			__size = [o.position_size_width, o.position_size_height];
			__boxsize = ['mainwrap@width', 'mainwrap@height'];
		}
		var size_convert = { px: 'px', proc: '%' };
		for (var i in __size) {
			if ( banner_type == 'bar' && i == 2 ) o.position_size_type = 'px';
			__size[i] = ( __size[i] == '' || isNaN(__size[i]) ? 'auto' : parseInt( __size[i] ) );
			__size[i] = __size[i] != 'auto' ? __size[i] + size_convert[o.position_size_type] : __size[i];
			
			var __x = __boxsize[i].split('@');
			__size[i] != 'auto' ? css[__x[0]][__x[1]] = __size[i] : '';
		}
		if ( banner_type == 'bar' ) {
			objWrappers.mainform.css( css.mainform );
			objWrappers.mainprods.css( css.mainprods );
		} else {
			if ( banner_type != 'slide'
				|| ( banner_type == 'slide' && status == '' ) ) {
				objWrappers.mainwrap.css( css.mainwrap );
			}
		}
		
		// specific box variables
		var mainwrapW = parseInt( objWrappers.mainwrap.outerWidth() ), mainwrapH = parseInt( objWrappers.mainwrap.outerHeight() );

		if ( $.inArray(banner_type, ['slide', 'popup']) > -1 ) {
			if ( status == '' ) { // default
				var mainwrapw = parseInt( objWrappers.mainwrap.width() ), mainwraph = parseInt( objWrappers.mainwrap.height() );
				
				slide_obj.mainwrap['widthdiff'] = parseInt( mainwrapW - mainwrapw );
				slide_obj.mainwrap['heightdiff'] = parseInt( mainwrapH - mainwraph );

				if ( mainwrapW > winW ) {
					css.mainwrap.width = winW - slide_obj.mainwrap['widthdiff'];
					objWrappers.mainwrap.css( css.mainwrap );
					
					mainwrapW = parseInt( objWrappers.mainwrap.outerWidth() );
					mainwrapw = parseInt( objWrappers.mainwrap.width() );
				}
				if ( mainwrapH > winH ) {
					css.mainwrap.height = winH - slide_obj.mainwrap['heightdiff'];
					objWrappers.mainwrap.css( css.mainwrap );
					
					mainwrapH = parseInt( objWrappers.mainwrap.outerHeight() );
					mainwraph = parseInt( objWrappers.mainwrap.height() );
				}

				slide_obj.mainwrap['width'] = mainwrapw;
				slide_obj.mainwrap['widthfull'] = mainwrapW;
				slide_obj.mainwrap['height'] = mainwraph;
				slide_obj.mainwrap['heightfull'] = mainwrapH;
			}
		}
 
		var slide_position = 'fixed';
		switch (banner_type) {
			// type: content top|bottom, widget
			case 'content_top':
			case 'content_bottom':
			case 'widget':
				break;

			// type: popup
			case 'popup':
				css.mainwrap.position = slide_position;
				css.mainwrap.left = parseInt( ( winW - slide_obj.mainwrap['widthfull'] ) / 2 );
				css.mainwrap.top = parseInt( ( winH - slide_obj.mainwrap['heightfull'] ) / 2 );
				css.slide_box.left = 0; css.slide_box.top = 0;
 
				objWrappers.mainwrap.css( css.mainwrap );
				objWrappers.slide_box.css( css.slide_box );

				break;

			// type: slide
			case 'slide':
				if ( status == 'open' ) {
					localStorage.setItem('banner-slide-status', 'open');
				} else if ( status == 'close' ) {
					localStorage.setItem('banner-slide-status', 'close');
				} else if ( o.banner_box_opened == 'yes' ) {
					localStorage.setItem('banner-slide-status', 'open');
				} else {
					localStorage.setItem('banner-slide-status', 'close');
				}
				//console.log( localStorage.getItem('banner-slide-status'), ret );
 
				css.mainwrap.position = css.slide_icon.position = slide_position;
				css.mainwrap.top = css.slide_icon.top = (slide_position == 'fixed' ? adminBarH : 0);
				css.slide_icon.top += 48; 
				css.slide_box.top = 0;
				
				var __slide_icon_css = {}, __slide_icon_html = '';

				// horizontal
				switch ( o.position_horizontal ) {
					case 'left':
						if ( status == 'open' ) {
							css.mainwrap.left = 0; css.slide_icon.left = slide_obj.mainwrap['widthfull'];
							__slide_icon_html = '&lsaquo;';
						} else if ( status == 'close' ) {
							css.mainwrap.left = 0; css.slide_icon.left = 0;
							__slide_icon_html = '&rsaquo;';
						} else if ( o.banner_box_opened == 'yes' ) {
							css.mainwrap.left = 0; css.slide_icon.left = slide_obj.mainwrap['widthfull'];
							__slide_icon_html = '&lsaquo;';
						} else {
							css.mainwrap.left = 0; css.slide_icon.left = 0;
							__slide_icon_html = '&rsaquo;';
						}
						
						__slide_icon_css = {
							left: (status == 'open' ? css.slide_icon.left : 0)
						};
						objWrappers.mainwrap.addClass('left');
						objWrappers.slide_icon.addClass('left');
						break;
						
					case 'right':
						if ( status == 'open' ) {
							css.mainwrap.right = 0; css.slide_icon.right = slide_obj.mainwrap['widthfull'];
							__slide_icon_html = '&rsaquo;';
						} else if ( status == 'close' ) {
							css.mainwrap.right = 0; css.slide_icon.right = 0;
							__slide_icon_html = '&lsaquo;';
						} else if ( o.banner_box_opened == 'yes'  ) {
							css.mainwrap.right = 0; css.slide_icon.right = slide_obj.mainwrap['widthfull'];
							__slide_icon_html = '&rsaquo;';
						} else {
							css.mainwrap.right = 0; css.slide_icon.right = 0;
							__slide_icon_html = '&lsaquo;';
						}
						
						__slide_icon_css = {
							right: (status == 'open' ? css.slide_icon.right : 0)
						};
						__slide_icon_html = (status == 'close' ? '&lsaquo;' : '&rsaquo;');
						objWrappers.mainwrap.addClass('right');
						objWrappers.slide_icon.addClass('right');
						break;
				}
				
				// positioning
				if ( status == '' ) { // default
 
 					objWrappers.mainwrap.removeClass('close').addClass('open');
					objWrappers.mainwrap.css( css.mainwrap );
					objWrappers.slide_icon.html( __slide_icon_html );
					objWrappers.slide_icon.css( css.slide_icon );

					objWrappers.mainwrap.css( {'display' : 'block'} );
					
				} else if ( $.inArray(status, ['open', 'close']) > -1 ) { // slide
 
 					if ( status == 'open' )
 						objWrappers.mainwrap.removeClass('close').addClass('open');

					objWrappers.maincontainer.width( slide_obj.mainwrap['width'] );

					objWrappers.mainwrap.animate( { 
							width				: (status == 'open' ? slide_obj.mainwrap['width'] : 0)
						}, 800, 'linear', function() {
 							if ( status == 'close' )
 								objWrappers.mainwrap.removeClass('open').addClass('close');
						}
					);
					objWrappers.slide_icon.animate( __slide_icon_css, 800, 'linear', function() {
						objWrappers.slide_icon.html( __slide_icon_html );
					} );
				}
				break;
				
			// type: bar
			case 'bar':
				var	mainprodsW = parseInt( objWrappers.mainprods.outerWidth() ),
				mainprodsH = parseInt( objWrappers.mainprods.outerHeight() );
 
				css.mainwrap.position = slide_position;
				if ( isBarPercentage ) {
					var __diff = parseInt( 100 - mainprodsW );
					css.mainprods['left'] = 0;
					css.mainprods['margin-left'] = parseInt( __diff / 2 ) + '%';
					css.mainprods['margin-right'] = parseInt( __diff / 2 ) + '%';
				} else {
					css.mainprods['margin-left'] = - parseInt( mainprodsW / 2 );
				}
 
				// vertical
				switch ( o.position_vertical ) {
					case 'top':
						css.mainwrap.top = (slide_position == 'fixed' ? adminBarH : 0);
						break;
						
					case 'bottom':
						delete css.mainprods.top;
						css.mainwrap.bottom = 0;
						break;
				}
				
				// positioning
				if ( status == '' ) { // default
		
					objWrappers.slide_box.css( css.slide_box );
					objWrappers.mainprods.css( css.mainprods );
					objWrappers.mainwrap.css( css.mainwrap );
					if ( o.position_vertical == 'bottom' ) {
						objWrappers.mainwrap.addClass('bottom');
						objWrappers.mainprods.addClass('bottom');
					}

					objWrappers.mainwrap.css( {'display' : 'block'} );
	
				}
				break;
		}

		if ( status == ''
			&& ( o.open_search_products == 'no' || o.banner_search_type == 'external' ) ) {
			objWrappers.mainprods.hide();
		}
		
		wrapp_css_size( objWrappers.mainwrap );
		
		scrollbar_width = getBrowserScrollWidth();
		
		check_scrollbar( objWrappers, objWrappers.mainwrap ); 
	}
	
	/* triggers */
	function triggers( objWrappers ) {
		build_design.init( objWrappers );
		
		// dont show me again
		dont_show_again( objWrappers );

/*
		// search products button
		objWrappers.mainform.find('.age_restriction-search-button').click(function(e) {
			e.preventDefault();
			search_products( objWrappers, 1, true );
		});
		$(document).on('keypress', function(e) {
			if(e.which == 13 && e.target.tagName != 'TEXTAREA') { // Enter
				objWrappers.mainform.find('.age_restriction-search-button').trigger('click'); // trigger event action!
			}
			e.stopPropagation();
			return true;
		});
		
		// close products button
		objWrappers.mainwrap.on('click', ".age_restriction_products_close", function(e) {
			e.preventDefault();
			objWrappers.mainprods.hide();
		});
		
		// products pagination
		objWrappers.mainprods.on('click', ".age_restriction-pagination ul li a", function(e) {
			e.preventDefault();
			
			var $this = $(this), href = $this.prop('href');
			var page_current = 1;
			if ( href.indexOf('#') > -1 )
				page_current = href.split('#')[1].replace('page=', '');
			
			search_products( objWrappers, page_current, true );
		});
*/
		// slide
		if ( objWrappers.banner_type == 'slide' ) {
			objWrappers.slide_icon.on('click', function(e) {
				var status = localStorage.getItem('banner-slide-status') == 'open' ? 'close' : 'open';
				build_banner_position( objWrappers, status );
			});
		}
		
		//var bso =
		//objWrappers.mainform.find("input[name='age_restriction-banner-opt-search-opened']").val();
		//if ( bso == 1 ) search_products( objWrappers, 1, true );
		//if ( objWrappers.opt.open_search_products == 'yes' ) search_products( objWrappers, 1, true );
	}
/*
	function search_products( objWrappers, page_current, reset_page, nbTries ) {
		var page = parseInt( page_current ) || 1, nbTries = nbTries || 1,
		reset_page = reset_page || false;
		
		// external products search
		if (objWrappers.opt.banner_search_type == 'external' ) {
			searchProductsExternal.init(objWrappers);
			//searchProductsExternal.build();
			searchProductsExternal.open();
			return false;
		}
		
		// internal products search
		var loadArrMsg = ['first', 'second', 'third', '4th', '5th'];
		var loadObj = {
			'html' 	: '<div class="age_restriction-search-trymsg" style="width: 50%; margin: 10px 0px 0px 30%;"><span style="width: 100%; color: red; font-style: italic;"></span></div>',
			'id'	: 'div.age_restriction-search-trymsg > span',
			'msg'	: 'This is the ' + loadArrMsg[nbTries-1] + ' try to retrieve amazon response' 
		};
		;
 
		//if( reset_page == true ) page = 1;
 
		//loading.css('display', 'block');
		//row_loading( objWrappers.mainprods, 'show', loadObj );
		row_loading_prods( objWrappers, 'show', loadObj );
 		
		// get the current browse node
		var current_node = '';
		objWrappers.mainform.find('.age_restrictionGetChildrens select').each(function(){
		    var that_select = jQuery(this);
 
		    if( that_select.val() != "" ){
		        current_node = that_select.val();
		    }
		});
 
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionLaunchSearch_front',
			'banner_id'		: objWrappers.mainform.find("input[name='age_restriction-banner-opt-id']").val(),
			'params'		: objWrappers.mainform.serialize(),
			'page'			: page,
			'node'			: current_node,
			'reset_page'	: reset_page ? 'yes' : 'no',
			'nbtries'		: nbTries,
			'debug_level'	: debug_level
		}, function(response) {
			if( response.status == 'valid' ){

				objWrappers.mainprods.hide();
				objWrappers.mainprods.html('');

				// code > 0 => error occured! code = 0 => success!
				if ( response.code > 0 ) { // amazon returned error!
					if ( nbTries < searchNbTries ) {
						search_products( objWrappers, page_current, reset_page, ++nbTries );
					} else {
						objWrappers.mainprods.html( response.html );
					}
				} else {
					if( objWrappers.banner_type == 'bar' ){
						
						response.html += '<a href="#" class="age_restriction_products_close" style="margin-left: ' + ( parseInt( objWrappers.mainprods.width() / 2 - 60 ) ) + 'px; margin-top: ' + ( objWrappers.opt['position_vertical'] == 'bottom' ? '-37' : objWrappers.mainprods.height() + 0 ) + 'px;">close</a>';
					}
					if ( reset_page ) {
						objWrappers.mainprods.html( response.html );
					}
					else
						objWrappers.mainprods.find('.age_restriction-product-list').html( response.html );
				}
			}
 
			//loading.css('display', 'none');
			//row_loading( objWrappers.mainprods, 'hide' );
			row_loading_prods( objWrappers, 'close', loadObj );
		}, 'json');
	}
*/
	function custom_select_ajax( objWrappers, select, action ) {
		if ( action == 'node' ) {
			getChildNodes( objWrappers, select );
		} else if ( action == 'category' ) {
			load_category_interface( objWrappers, select );
		} else if ( action == 'country' ) {
			load_country_interface( objWrappers, select );
		}
	}
/*
	function load_category_interface( objWrappers, that )
	{
		//loading.css('display', 'block');
		row_loading( objWrappers.mainform, 'show' );

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionCategParameters_front',
			'banner_id'		: objWrappers.mainform.find("input[name='age_restriction-banner-opt-id']").val(),
			'nodeid'		: that.val(),
			'country'		: objWrappers.mainform.find("select[name='age_restrictionParameter[Country]']").val(),
			'debug_level'	: debug_level
		}, function(response) {
			if( response.status == 'valid' ){
				objWrappers.mainform.find('.age_restriction-box-params-ajax').html( response.html );
				if ( objWrappers.banner_type == 'bar' ) {
					objWrappers.mainform.find('.age_restriction-advanced-settings-box').remove();
					objWrappers.mainform.append( response.html_advs );
				}
				
				build_design.select( objWrappers );
				build_design.slider( objWrappers );
				build_design.settings_toggle( objWrappers );
			}

			//loading.css('display', 'none');
			row_loading( objWrappers.mainform, 'hide' );
		}, 'json');
	}
	
	function load_country_interface( objWrappers, that )
	{
		//loading.css('display', 'block');
		row_loading( objWrappers.mainform, 'show' );

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionCountryInterface_front',
			'banner_id'		: objWrappers.mainform.find("input[name='age_restriction-banner-opt-id']").val(),
			'country'		: objWrappers.mainform.find("select[name='age_restrictionParameter[Country]']").val(),
			'debug_level'	: debug_level
		}, function(response) {
			if( response.status == 'valid' ){
				objWrappers.mainform.find('.age_restriction-box-category-ajax').html( response.html );

				build_design.select( objWrappers, 'category' );
				if ( objWrappers.mainform.find("select[name='age_restrictionParameter[Category]']").length > 0 ) {
					load_category_interface(
						objWrappers,
						objWrappers.mainform.find("select[name='age_restrictionParameter[Category]']")
					);
				} else {
					objWrappers.mainform.find('.age_restriction-box-params-ajax').html( '' );

					//loading.css('display', 'none');
					row_loading( objWrappers.mainform, 'hide' );
				}
			} else {
				//loading.css('display', 'none');
				row_loading( objWrappers.mainform, 'hide' );
			}
		}, 'json');
	}

	function getChildNodes( objWrappers, that )
	{
		//loading.css('display', 'block');
		row_loading( objWrappers.mainform, 'show' );

		// prev element valud
		var ascensor_value = that.val(),
			that_index = that.parent().index();
 
		// max 3 deep
		if ( that_index > 2 ){
			//loading.css('display', 'none');
			row_loading( objWrappers.mainform, 'hide' );
			return false;
		}

		var container = objWrappers.mainform.find('.age_restrictionGetChildrens');
		var remove = false;
		// remove items prev of current selected
		container.find('select').each( function(i){
			if( remove == true ) $(this).parent().remove();
			if( $(this).parent().index() == that_index ){
				remove = true;
			}
		});

		// store current childrens into array
		if( ascensor_value != "" ){
			// make the import
			jQuery.post(ajaxurl, {
				'action' 		: 'age_restrictionGetChildNodes_front',
				'banner_id'		: objWrappers.mainform.find("input[name='age_restriction-banner-opt-id']").val(),
				'country'		: objWrappers.mainform.find("select[name='age_restrictionParameter[Country]']").val(),
				'ascensor'		: ascensor_value,
				'debug_level'	: debug_level
			}, function(response) {
				if( response.status == 'valid' ){
					objWrappers.mainform.find('.age_restrictionGetChildrens').append( response.html );

					build_design.select( objWrappers, 'node' );
				}
				
				//loading.css('display', 'none');
				row_loading( objWrappers.mainform, 'hide' );
			}, 'json');

		}else{
			//loading.css('display', 'none');
			row_loading( objWrappers.mainform, 'hide' );
		}
	}
*/
	var build_design = {
		init: function( objWrappers ) {
			var self = this;
 
			self.select( objWrappers );
			self.slider( objWrappers );
			self.settings_toggle( objWrappers );
			//self.owl_carousel( objWrappers );
		},

		select: function( objWrappers, filterSel ) {

			var filterSel = filterSel || '';
 
			/* Custom select boxes */
			// Iterate over each select element
			var listSelects = objWrappers.mainform.find('select');
			if ( filterSel != '' ) {
				listSelects = listSelects.filter(function(i) {
					return $(this).data('action') == filterSel;
				});
			}
 
			listSelects.each(function () {
			
			    // Cache the number of options
			    var $this = $(this),
				numberOfOptions = $(this).children('option').length;
 
				if ( $this.parent().is('div.age_restriction-select') )
					return;
			
			    // Hides the select element
			    $this.addClass('age_restriction-s-hidden');
			    
			    // craete unique ID
			    var unique = $this.attr("id")
			    				.replace("age_restrictionParameter[", "")
			    				.replace("]", "");
			    
			    // Wrap the select element in a div
			    $this.wrap('<div class="age_restriction-select ' + ( unique + "" ) + '"></div>');
			
			    // Insert a styled div to sit over the top of the hidden select element
			    $this.after('<div class="age_restriction-styledSelect"></div>');
			
			    // Cache the styled div
			    var $styledSelect = $this.next('div.age_restriction-styledSelect');
				function age_restrictionConvertToSlug(String)
				{
				    return String
				        .toLowerCase()
				        .replace(/ /g,'-')
				        .replace(/[^\w-]+/g,'')
				        ;
				}
			    // Show the first select option in the styled div
			    $styledSelect.html('<span class="' + age_restrictionConvertToSlug($this.children('option:selected').eq(0).text())+ '">' + $this.children('option:selected').eq(0).text() + '</span>');
			
			    // Insert an unordered list after the styled div and also cache the list
			    var $list = $('<ul />', {
			        'class': 'age_restriction-options'
			    }).insertAfter($styledSelect);
			    
			    if ( objWrappers.banner_type == 'bar'
			    	&& objWrappers.opt['position_vertical'] == 'bottom' ) {
			    	$list.addClass('bottom');
			    }
			
			    // Insert a list item into the unordered list for each select option
			    for (var i = 0; i < numberOfOptions; i++) {
			        $('<li />', {
			            text: $this.children('option').eq(i).text(),
			            rel: $this.children('option').eq(i).val()
			        }).appendTo($list);
			    }
			
			    // Cache the list items
			    var $listItems = $list.children('li');
			
			    // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
			    $styledSelect.click(function (e) {
			        e.stopPropagation();
			        $list.hide();

			        objWrappers.mainform.find('div.styledSelect.active').each(function () {
						$(this).removeClass('active').next('ul.age_restriction-options').hide();
			        });
			        $(this).toggleClass('active').next('ul.age_restriction-options').toggle();
			    });
			
			    // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
			    // Updates the select element to have the value of the equivalent option
			    $listItems.click(function (e) {
			        e.stopPropagation();
 
			        //$styledSelect.text($(this).text()).removeClass('active');
			        '<span class="' + + '</span>'
			        $styledSelect.html('<span class="' + age_restrictionConvertToSlug($(this).text()) + '">' + $(this).text() + '</span>').removeClass('active');
			        $this.val($(this).attr('rel'));
			        $list.hide();
			        /* alert($this.val()); Uncomment this for demonstration! */

			       	custom_select_ajax( objWrappers, $this, $this.data('action') );
			    });
			
			    // Hides the unordered list when clicking outside of it
			    $(document).click(function () {
			        $styledSelect.removeClass('active');
			        $list.hide();
			    });
			});

		},

		slider: function( objWrappers ) {

			//discount slider
			if ( objWrappers.mainform.find('.age_restriction-discount-slider').length > 0 ) {
				objWrappers.mainform.find('.age_restriction-discount-slider').noUiSlider({
					start: [ objWrappers.mainform.find('.age_restriction-discount-current').val() ],
					range: {
						'min': 0,
						'max': 100
					},
					connect: "lower",
					serialization: {
						lower: [
						  $.Link({
							target: objWrappers.mainform.find('.age_restriction-discount-max'),
							format: {
								decimals: 0,
								postfix: '%'
							}
						  }),
						  $.Link({
							target: objWrappers.mainform.find('.age_restriction-discount-current'),
							format: {
								decimals: 0
							}
						  })
						]
					}
				});
			}

		},

		settings_toggle: function( objWrappers ) {

			//advanced setting toggle
			objWrappers.mainform.find('.age_restriction-settings-toggle').click(function(){
				var sign = '';
				if (objWrappers.banner_type == 'bar') sign = '.age_restriction-plus-sign-top';
				else sign = '.age_restriction-plus-sign';
				
				objWrappers.mainform.find('.age_restriction-advanced-settings-box').slideToggle(function(){
					if($(this).is(":hidden")) {
					   	objWrappers.mainform.find('.age_restriction-settings-toggle '+sign).html('+');
					} else {
					  	objWrappers.mainform.find('.age_restriction-settings-toggle '+sign).html('-');
					}
	
					check_scrollbar( objWrappers, objWrappers.maincontainer, true );
				});
			});

		},
		
		owl_carousel: function( objWrappers ) {

			//owl carousel
			objWrappers.mainform.find(".age_restriction-best-discount-list-wrapper").owlCarousel({
				items : 4,
				lazyLoad : true,
				navigation : true,
				navigationText : ["&lsaquo;","&rsaquo;"],
				 //Pagination
				pagination : false,
			}); 

		}
	}
	
	/* POPUP */
	function closePopup() {
		var options = JSON.parse( localStorage.getItem('popupPms') )
		
		if ( options.effect_close == 'fadeout' ) {
			jQuery("div#age_restriction-smartPopupfade").fadeOut(options.effect_close_duration);
			jQuery("div.age_restriction-smartPopup").fadeOut(options.effect_close_duration);
			jQuery("div#age_restriction-pietimerholder").fadeOut(options.effect_close_duration);
		}
		else { 
			jQuery("div#age_restriction-smartPopupfade").hide();
			jQuery("div.age_restriction-smartPopup").hide();
			jQuery("div#age_restriction-pietimerholder").hide();
		}

		jQuery("div.age_restriction-smartPopup").remove();
	}
	
	function closeOpenDelay() {
		smartPopup( JSON.parse( localStorage.getItem('popupPms') ) );
	}
	
	var	smartPopup = function(options) {
		//Set the default values
		var defaults = {
			'fadeBox'				: 'div#age_restriction-smartPopupfade',
			'wrapperBox'			: '#agecheck',
			'closeBtn'				: 'a.age_restriction-smartPopup-close',
			'showOn'				: 'open',
			'fadeOpacity'			: 1,
			'fadeBackgroundColor'	: '',
			'fadeBackground'		: '',
			'boxBackgroundColor'	: '',
			'boxBackgroundOpacity'  : '1',
			//'fadeOutTime'			: 600,
			//'fadeInTime'			: 600,
			//'boxWidth'				: '800',
			//'boxHeight'				: '600',
			
			'auto_close_time'		: 0,
			'showBtnClose'			: 'yes',
			'extendedClose'			: 'yes',
			
			'effect_open'			: 'fadein',
			'effect_open_duration'  : 600,
			'effect_close'			: 'fadeout',
			'effect_close_duration' : 600
		};
 
		// extends options object
		options = jQuery.extend(defaults, options);
		//console.log( options );

		// self define parent class
		var self = this;
		
		// prevent collisions
		var running = false;
	
		jQuery("div.age_restriction-smartPopup").each(function() {
			var opts = options;
			
			// cache jQuery(this) object
			var $this = jQuery(this);
			
			if(options.showOn == 'open'){
				// open feedback box
				openFeedback();
			}else{
				jQuery(window).bind("beforeunload", function(){
					// open feedback box
					openFeedback();
					return false;
				});
			}
			
			// open feedback box
			function openFeedback(){
				
				jQuery('html').css('overflow','hidden');
				
				// till close
				jQuery(options.fadeBox).css('display', 'none');
				
				// extra width and height + 30 (padding)
				/*$this.css({
					width			: options.boxWidth + 'px',
					height			: options.boxHeight + 'px',
					display			: 'none',
					// align to center
					marginTop		: "-" + (parseInt(options.boxHeight) + 30) / 2 + "px",
					marginLeft		: "-" + (parseInt(options.boxWidth) + 30) / 2 + "px"
				});*/
				$this.css({
					display			: 'none'
				});
				
				// set fade opacity
				var fadeBox_css = {};
				fadeBox_css.opacity = options.fadeOpacity;
				if ( options.fadeBackground != '' ) {
					fadeBox_css.background = 'url(' + options.fadeBackground + ')';
					fadeBox_css.backgroundSize = 'cover';
				} else if ( options.backgroundColor != '' ) {
					fadeBox_css.backgroundColor = '#' + options.fadeBackgroundColor;
 				}
 				jQuery(options.fadeBox).css(fadeBox_css);
 				
 				var wrapperBox_css = {};
 				if( options.boxBackgroundColor != '' ) {
 					wrapperBox_css.backgroundColor = '#' + options.boxBackgroundColor;
 				}else{
 					wrapperBox_css.backgroundColor = 'transparent';
 				}
 				jQuery(options.wrapperBox).css(wrapperBox_css);
 
				// open it
				if ( options.effect_open == 'fadein' ) {
					jQuery(options.fadeBox).fadeIn(options.effect_open_duration);
					$this.fadeIn(options.effect_open_duration);
				}
				else { 
					jQuery(options.fadeBox).show();
					$this.show();
				}
			}
			
			// close feedback box
			function closeFeedback(){
				if ( options.effect_close == 'fadeout' ) {
					jQuery(options.fadeBox).fadeOut(options.effect_close_duration);
					$this.fadeOut(options.effect_close_duration);
					jQuery("div#age_restriction-pietimerholder").fadeOut(options.effect_close_duration);
				}
				else { 
					jQuery(options.fadeBox).hide();
					$this.hide();
					jQuery("div#age_restriction-pietimerholder").hide();
				}
			}
	
		});
	}
	
	function dont_show_again(objWrappers) {
		objWrappers.mainwrap.on('click', '#age_restriction-dont-show-again', function(e) {
			//e.preventDefault();
			var $this = $(this);
			
			row_loading( $this.parent(), 'show' );

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post(ajaxurl, {
				'action' 		: 'age_restrictionDontShowAgain_front',
				'banner_id'		: objWrappers.mainform.find("input[name='age_restriction-banner-opt-id']").val(),
				'dont_show'		: $this.prop('checked') ? 'yes' : 'no',
				'debug_level'	: debug_level
			}, function(response) {
				if( response.status == 'valid' ){
				}

				row_loading( $this.parent(), 'hide' );
				location.reload();
			}, 'json');
			
		});
	}
/*
	var searchProductsExternal = {
		objWrappers: null,
		amz: {},

		init: function(objWrappers) {
			var self = this;

			self.objWrappers = objWrappers;
			self.amz = $.extend( self.amz, {
				'country'			: '',
				'category'			: '',
				'subcategory'		: '',
				'tag'				: '',
	
				'siteext'			: '',
				'_pms'				: [],
				'pms'				: '',
				'_fullurl'			: 'http://www.amazon.{siteext}/gp/redirect.html?ie=UTF8&location=/gp/search?{pms}&redirect=true&tag={tag}',
				'fullurl'			: ''
			} );
		},
		
		_country2site: function() {
			return {
				'united-states' 	: ['com', 'United States'],
				'germany' 			: ['de', 'Germany'],
				'united-kingdom' 	: ['co.uk', 'United Kingdom'],
				'canada' 			: ['ca', 'Canada'],
				'france' 			: ['fr', 'France'],
				'japan' 			: ['co.jp', 'Japan'],
				'india' 			: ['in', 'India'],
				'italy' 			: ['it', 'Italy'],
				'china'				: ['cn', 'China'],
				'spain' 			: ['es', 'Spain']
			};
		},
		
		_pms4amz: function() {
			return {
				'Category'					: 'node={val}',
				'Keywords'					: 'field-keywords={val}',
				'PercentageOff'				: 'pct-off={val}', // value format: MinPercentageOff-MaxPercentageOff
				'Price'						: 'field-price={val}', // value format: MinimumPrice-MaximumPrice
				'Brand'						: 'field-brand={val}',
				'Manufacturer'				: '', //???todo
				'Condition'					: 'field-p_n_condition-type={val}', //'p_{val}=1'
				'Sort'						: 'sort={val}'
			};
		},
		
		_condition: function() {
			return {
				//'p_{val}=1'
				//'New'						: 26,
				//'Used'						: 17,
				//'Collectible'				: 18,
				//'Refurbished'				: 0 //???todo

				//'field-p_n_condition-type={val}'
				'New'						: '1294423011',
				'Used'						: '1294425011',
				'Collectible'				: '1294422011',
				'Refurbished'				: '1294424011'
			};
		},
		
		_gettag: function() {
			var self = this;
			var tag = '', amzcfg = self.objWrappers.amzcfg;

			if ( typeof amzcfg.AffiliateID[ self.amz.country ] != 'undefined' ) {
				if ( $.trim(amzcfg.AffiliateID[ self.amz.country ]) != '' ) {
					tag = amzcfg.AffiliateID[ self.amz.country ];
				}
			}
			//else if ( typeof amzcfg.AffiliateID[ amzcfg.main_aff_id ] != 'undefined' ) {
			//	if ( $.trim(amzcfg.AffiliateID[ amzcfg.main_aff_id ]) != '' ) {
			//		tag = amzcfg.AffiliateID[ amzcfg.main_aff_id ];
			//	}
			//}
			self.amz.tag = tag;
			return tag;
		},
		
		build: function() {
			var self = this;
			
			// get form parameters
			var form = self.objWrappers.mainform.serialize(), form = decodeURIComponent( form );
			if ( form == '' ) return false;
			
			form = form.split('&');
			if (form.length <= 0) return false;
 
			// filter parameters
			var ret = [], price = { 'min': '', 'max': '' }, percentageoff = { 'min': '', 'max': '' };
			for (var i = 0; i < form.length; i++) {
				var val = form[i], val = val.split('=');
 
				if ( val.length != 2 ) continue;
				if ( $.trim(val[1]) == '' ) continue;
				if ( $.inArray(val[0], ['age_restriction-banner-opt-id', 'age_restriction-banner-opt-search-opened']) > -1 ) continue;
				val[0] = val[0].replace(/age_restrictionParameter\[(\w*)\]/gi, '$1');
				
				if ( val[0] == 'Country' ) { // country
					self.amz.country = val[1];
					val[1] = self._country2site()[ val[1] ][0];
					self.amz.siteext = val[1];

				} else if ( val[0] == 'Category' ) { // category
					self.amz.category = val[1];

				} else if ( val[0] == 'node' ) { // subcategory
					if ( $.trim(val[1]) != '' )
						self.amz.subcategory = val[1];

				} else if ( val[0] == 'Condition' ) { // condition
					val[1] = self._condition()[ val[1] ];

				} else if ( val[0] == 'MinPercentageOff' ) { // percentageoff / min
					val[1] = $.trim(val[1]);
					if ( $.inArray(val[1], ['', 'title', 'none']) == -1 )
						percentageoff.min = parseInt( val[1] );

				} else if ( val[0] == 'MaxPercentageOff' ) { // percentageoff / max
					val[1] = $.trim(val[1]); 
					if ( $.inArray(val[1], ['', 'title', 'none']) == -1 )
						percentageoff.max = parseInt( val[1] );

				} else if ( val[0] == 'MinimumPrice' ) { // price / min
					if ( $.trim(val[1]) != '' ) {
						val[1] = val[1].replace(/[^0-9\.]/gi, '');
						price.min = parseInt( Math.ceil( val[1] * 100 ) );
					}

				} else if ( val[0] == 'MaximumPrice' ) { // price / max
					if ( $.trim(val[1]) != '' ) {
						val[1] = val[1].replace(/[^0-9\.]/gi, '');
						price.max = parseInt( Math.ceil( val[1] * 100 ) );
					}
				}
				
				if ( $.inArray(val[0], [
					'Country', 'Category', 'node',
					'MinPercentageOff', 'MaxPercentageOff',
					'MinimumPrice', 'MaximumPrice'
				]) > -1 ) continue;
		
				var el = {
					'key'	: val[0],
					'val'	: val[1]
				};
				ret.push(el);
			}
			
			// add category
			if ( self.amz.subcategory != '' )
				ret.push({ 'key' : 'Category', 'val' : self.amz.subcategory });
			else if ( self.amz.category != '' )
				ret.push({ 'key' : 'Category', 'val' : self.amz.category });
				
			// add price
			if ( price.min != '' || price.max != '' )
				ret.push({ 'key' : 'Price', 'val' : price.min + '-' + price.max });
			
			// add percentageoff
			if ( percentageoff.min != '' || percentageoff.max != '' )
				ret.push({ 'key' : 'PercentageOff', 'val' : percentageoff.min + '-' + percentageoff.max });
				
			// final parameters list
			self.amz._pms = ret;
			
			// build url
			var url = self.amz._fullurl;
			url = url.replace('{siteext}', self.amz.siteext);
			url = url.replace('{tag}', self._gettag());
			var pms = [];
			for (var i in self.amz._pms) { // go through parameters
				if (!misc.hasOwnProperty(self.amz._pms, i)) continue;
				var val = self.amz._pms[i], val2 = self._pms4amz()[ val['key'] ];
				val2 = val2.replace('{val}', val['val']);
				pms.push( val2 );
			}
			pms = pms.join('&');
			pms = encodeURIComponent( pms );
			self.amz.pms = pms;
			url = url.replace('{pms}', pms);
			self.amz.fullurl = url;
			//console.dir( self.amz ); console.log( self.amz.fullurl ); 
		},
		
		open: function() {
			var self = this;

			self.build();
			window.open( self.amz.fullurl );
		}
	}
*/
	var misc = {
	
		hasOwnProperty: function(obj, prop) {
			var proto = obj.__proto__ || obj.constructor.prototype;
			return (prop in obj) &&
			(!(prop in proto) || proto[prop] !== obj[prop]);
		},
	
		arrayHasOwnIndex: function(array, prop) {
			return array.hasOwnProperty(prop) && /^0$|^[1-9]\d*$/.test(prop) && prop <= 4294967294; // 2^32 - 2
		},
	
		arrayIntersect: function(a, b) {
	    	return $.grep(a, function(i) {
	        	return $.inArray(i, b) > -1;
	    	});
		},
	   
		arrayUnique: function(array) {
	    	var a = array.concat();
	    	for(var i=0; i<a.length; ++i) {
	        	for(var j=i+1; j<a.length; ++j) {
	            	if(a[i] === a[j])
	                	a.splice(j--, 1);
	        	}
	    	}
	    	return a;
		},
	   
	    arrayGetElement: function(array, type) { // second parameter possible values: key | value
			for (var i in array) {
				if (misc.hasOwnProperty(array, i)) {
					if ( type == 'key' ) return i;
					return array[i];
				}
			}
	    },
	   
	    arrayRemoveElement: function(array, value) {
			var idx = array.indexOf(value);
			if (idx != -1) array.splice(idx, 1);
			return array;
		},
		
		size: function(obj) {
    		var size = 0;
    		for (var key in obj) {
        		if (misc.hasOwnProperty(obj, key)) size++;
    		}
    		return size;
		}
	
	};
 
	// external usage
	return {
		'setAjaxUrl'		: setAjaxUrl,
		'closePopup'		: closePopup,
		'closeOpenDelay'	: closeOpenDelay
	}
})(jQuery);


(function($) {
    $.fn.hasScrollBar = function() {
        var hasScrollBar = {}, e = this.get(0);
        if ( typeof e !== 'undefined' ) {
        	hasScrollBar.vertical = (e.scrollHeight > e.clientHeight) ? true : false;
        	hasScrollBar.horizontal = (e.scrollWidth > e.clientWidth) ? true : false;
        }
        return hasScrollBar;
    }
})(jQuery);