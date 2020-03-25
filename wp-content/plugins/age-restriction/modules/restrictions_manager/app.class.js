/*
Document   :  Banners Manager
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
age_restrictionBM = (function ($) {
    "use strict";

    // public
    var debug_level = 0;
    var maincontainer = null;
    var loading = null;
    var IDs = [];
    var loaded_page = 0;
    var discount_current = {
    	country		: null,
    	categ		: { slug: null, nodeid: null }
    };
	// both bellow objects has 3 level structure: 1.countries, 2.categories, 3.parameters
    var discount_obj_current = {}, discount_obj_save = {};
    var amz_settings = {};
    
    var stats_vars = null;
    
	function row_loading( row, status )
	{
		if( status == 'show' ){
			if( row.size() > 0 ){
				if( row.find('.age_restriction-row-loading-marker').size() == 0 ){
					var row_loading_box = $('<div class="age_restriction-row-loading-marker"><div class="age_restriction-row-loading"><div class="age_restriction-meter age_restriction-animate" style="width:30%; margin: 10px 0px 0px 30%;"><span style="width:100%"></span></div></div></div>')
					row_loading_box.find('div.age_restriction-row-loading').css({
						'width': row.width(),
						'height': row.height()
					});
					row.prepend(row_loading_box);
				}
				row.find('.age_restriction-row-loading-marker').find('div.age_restriction-row-loading').css({
					'width': row.width(),
					'height': row.height()
				});
				row.find('.age_restriction-row-loading-marker').fadeIn('fast');
			}
		}else{
			row.find('.age_restriction-row-loading-marker').fadeOut('fast');
		}
	}

	// init function, autoload
	(function init() {

		// load the triggers
		$(document).ready(function(){
			maincontainer = $("#age_restriction_meta_box");
			loading = maincontainer.find("#age_restriction-main-loading");
			
			get_amazon_settings();
			triggers();
		});
	})();
	
	function get_amazon_settings() {
		amz_settings = $('#age_restriction-settings #age_restriction-amazon-global').data('values');
	}
	
	function fixMetaBoxLayout()
	{
		maincontainer.find("#age_restriction-meta-box-preload").hide();
		maincontainer.find(".age_restriction-meta-box-container").fadeIn('fast');

		var fixCountriesOpenFirst = false;
		//fix_countries_jqueryui_tabs();
		maincontainer.on('click', '.age_restriction-tab-menu a', function(e, country){
			e.preventDefault();

			var that 	= $(this),
				open 	= maincontainer.find(".age_restriction-tab-menu a.open"),
				href 	= that.attr('href').replace('#', '');
			var country = country || '';

			maincontainer.find(".age_restriction-meta-box-container").hide();

			maincontainer.find("#age_restriction-tab-div-id-" + href ).show();

			// close current opened tab
			var rel_open = open.attr('href').replace('#', '');

			maincontainer.find("#age_restriction-tab-div-id-" + rel_open ).hide();

			maincontainer.find("#age_restriction-meta-box-preload").show();

			maincontainer.find("#age_restriction-meta-box-preload").hide();
			maincontainer.find(".age_restriction-meta-box-container").fadeIn('fast');
			
			open.removeClass('open');
			that.addClass('open');
		});
	}
	
	function metabox_post_type() {
		// Type box
		$('.banner-type').appendTo( maincontainer.find('h3.hndle span') );
	
		// Prevent inputs in meta box headings opening/closing contents
		$(function(){
			$( maincontainer.find(' h3.hndle') ).unbind('click.postboxes');
	
			jQuery( maincontainer ).on('click', 'h3.hndle', function(event){
	
				// If the user clicks on some form input inside the h3 the box should not be toggled
				if ( $(event.target).filter('input, option, label, select').length )
					return;
	
				$( maincontainer ).toggleClass('closed');
			});
		});
		
		// choose banner type
		var $choose_btype = 'popup';

		$('#age_restriction-tab-div-id-banner').find('[class*="wrap-btype-"]').hide();
		choose_banner_type( $choose_btype );
		$('.banner-type #choose_banner_type').change(function() {
			choose_banner_type( $(this).val() );
		});
	}
	
	function choose_banner_type( current ) {
		var current = current || '';
		
		// new current banner type
		$('#age_restriction-tab-div-id-banner .wrap-btype-' + current ).show();
		
		// make tabs
		age_restriction.makeTabs();
		
		// hide empty tabs
		$('#age_restriction-tab-div-id-banner').find('ul.tabsHeader li a').each(function() {
			var $this = $(this), tabid = $this.prop('title');
			var $content = $( '.'+tabid ), tabHasElements = false;

			$content.each(function(i) {
				if ( $(this).hasClass('wrap-btype-'+current) )
					tabHasElements = true;
			});

			if ( !tabHasElements ) $this.hide();
			else $this.show();
		});
	}

	function __whatToDisplayLoading() {
		var wrap = $('#discount-list'), wrapW = wrap.outerWidth(true),
		left = parseInt( ( wrapW * 25 ) / 100 ), right = parseInt( wrapW - left );
		
		return {
			'left'		: left,
			'right' 	: right
		}
	}

	/* Statistics */
	function stats_refreshGraph()
	{
		row_loading($('#age_restriction-stats-graph'), 'show');
 
		// float jQuery
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionGetStatsGraphData',
			'postid'		: $('#age_restriction-postid').val(),
			'from_date'		: $("#age_restriction-filter-by-date-from").val(),
			'to_date'		: $("#age_restriction-filter-by-date-to").val(),
			'debug_level'	: debug_level
		}, function(response) {
			//data not received!
			if (response.status == 'invalid') {
				$("#age_restriction-stats-graph").fadeOut('fast');
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-graph'), 'close');
				return false;
			}
			
			if( response.status == 'valid' ){
				$("#age_restriction-stats-graph").fadeIn('fast');
				var plot = $.plot("#age_restriction-stats-graph", response.data, {
					series: {
						lines: {
							show: true
						},
						points: {
							show: true
						}
					},
					grid: {
						hoverable: true,
						clickable: true,
						borderWidth: 2,
					
						
					},
					tooltip: true,
					tooltipOpts: {
						defaultTheme: true,
						content: "%s (%x)<br />Value: %y"
					},
					xaxis: {
						mode: "time",
						timeformat: "%d %b", //"%d/%m/%y"
						minTickSize: [1, "day"],
						color: 'grey',
						labelWidth: '80'
						//,reserveSpace: true
					},
					yaxes: [ { 
						min: 1,
						tickFormatter: (function formatter(val, axis) { 
							return val;
						}),
						minTickSize: 1 
					} ],
				});
				
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-graph'), 'close');
			}
		}, 'json');
	}
	
	function init_stats_country() {
		row_loading($('#age_restriction-stats-country'), 'show');
 
		// float jQuery
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionGetStats',
			'subaction'		: 'country',
			'postid'		: $('#age_restriction-postid').val(),
			'from_date'		: $("#age_restriction-filter-by-date-from").val(),
			'to_date'		: $("#age_restriction-filter-by-date-to").val(),
			'debug_level'	: debug_level
		}, function(response) {
			//data not received!
			if (response.status == 'invalid') {
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-country'), 'close');
				return false;
			}
			
			if( response.status == 'valid' ){
				$("#age_restriction-stats-country").html( response.data );
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-country'), 'close');
			}
		}, 'json');
	}
	
	function init_stats_keyword() {
		row_loading($('#age_restriction-stats-keyword'), 'show');
 
		// float jQuery
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionGetStats',
			'subaction'		: 'keyword',
			'postid'		: $('#age_restriction-postid').val(),
			'from_date'		: $("#age_restriction-filter-by-date-from").val(),
			'to_date'		: $("#age_restriction-filter-by-date-to").val(),
			'debug_level'	: debug_level
		}, function(response) {
			//data not received!
			if (response.status == 'invalid') {
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-keyword'), 'close');
				return false;
			}
			
			if( response.status == 'valid' ){
				$("#age_restriction-stats-keyword").html( response.data );
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-keyword'), 'close');
			}
		}, 'json');
	}
	
	function init_stats_charts(){
		var _charts = ['#age_restriction-chart-devices', '#age_restriction-chart-impressions', '#age_restriction-chart-authentications', '#age_restriction-chart-snetworks'];
		for (var i in _charts) $( _charts[i] ).html('');

		row_loading($('.age_restriction-chart-container'), 'show');
 
		// float jQuery
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionGetStats',
			'subaction'		: 'charts',
			'postid'		: $('#age_restriction-postid').val(),
			'from_date'		: $("#age_restriction-filter-by-date-from").val(),
			'to_date'		: $("#age_restriction-filter-by-date-to").val(),
			'debug_level'	: debug_level
		}, function(response) {
			//data not received!
			if (response.status == 'invalid') {
				//loading.css('display', 'none');
				row_loading($('.age_restriction-chart-container'), 'close');
				return false;
			}
			
			if( response.status == 'valid' ){
				
				var mobile = misc.hasOwnProperty(response.data, 'mobile') ? parseInt( response.data.mobile ) : 0,
				tablet = misc.hasOwnProperty(response.data, 'tablet') ? parseInt( response.data.tablet ) : 0,
				desktop = misc.hasOwnProperty(response.data, 'desktop') ? parseInt( response.data.desktop ) : 0, 
				hits = misc.hasOwnProperty(response.data, 'hits') ? parseInt( response.data.hits ) : 0,
				hits_all = misc.hasOwnProperty(response.data, 'hits_all') ? parseInt( response.data.hits_all ) : 0,
				auth = misc.hasOwnProperty(response.data, 'auth') ? parseInt( response.data.auth ) : 0,
				auth_all = misc.hasOwnProperty(response.data, 'auth_all') ? parseInt( response.data.auth_all ) : 0,
				manual = misc.hasOwnProperty(response.data, 'manual') ? parseInt( response.data.manual ) : 0,
				facebook = misc.hasOwnProperty(response.data, 'facebook') ? parseInt( response.data.facebook ) : 0,
				google = misc.hasOwnProperty(response.data, 'google') ? parseInt( response.data.google ) : 0;
				
				var mobile_views = misc.hasOwnProperty(response.data, 'mobile_views') ? parseInt( response.data.mobile_views ) : 0,
				tablet_views = misc.hasOwnProperty(response.data, 'tablet_views') ? parseInt( response.data.tablet_views ) : 0,
				desktop_views = misc.hasOwnProperty(response.data, 'desktop_views') ? parseInt( response.data.desktop_views ) : 0;
				
				var manual_views = misc.hasOwnProperty(response.data, 'manual') ? parseInt( response.data.manual_views ) : 0,
				facebook_views = misc.hasOwnProperty(response.data, 'facebook') ? parseInt( response.data.facebook_views ) : 0,
				google_views = misc.hasOwnProperty(response.data, 'google') ? parseInt( response.data.google_views ) : 0;
				
				hits_all = parseInt( hits_all - hits );
				auth_all = parseInt( auth_all - auth );
				//console.log( hits, hits_all, search, search_all, amz, amz_all, mobile, desktop );
				
				$('.age_restriction-chart-container .devices .age_restriction-mobile').find('span').remove();
				$('.age_restriction-chart-container .devices .age_restriction-mobile').append('&nbsp;<span>(' + mobile_views + ')</span>');
				$('.age_restriction-chart-container .devices .age_restriction-tablet').find('span').remove();
				$('.age_restriction-chart-container .devices .age_restriction-tablet').append('&nbsp;<span>(' + tablet_views + ')</span>');
				$('.age_restriction-chart-container .devices .age_restriction-desktop').find('span').remove();
				$('.age_restriction-chart-container .devices .age_restriction-desktop').append('&nbsp;<span>(' + desktop_views + ')</span>');
				
				$('.age_restriction-chart-container .snetworks .age_restriction-manual').find('span').remove();
				$('.age_restriction-chart-container .snetworks .age_restriction-manual').append('&nbsp;<span>(' + manual_views + ')</span>');
				$('.age_restriction-chart-container .snetworks .age_restriction-facebook').find('span').remove();
				$('.age_restriction-chart-container .snetworks .age_restriction-facebook').append('&nbsp;<span>(' + facebook_views + ')</span>');
				$('.age_restriction-chart-container .snetworks .age_restriction-google').find('span').remove();
				$('.age_restriction-chart-container .snetworks .age_restriction-google').append('&nbsp;<span>(' + google_views + ')</span>');
				
				var optionsDevices = {
			        segmentShowStroke : true,
			        segmentStrokeColor : "#f9f9f9",
			        segmentStrokeWidth : 10,
			        baseColor: "rgba(249,249,249,1)",
			        baseOffset: 4,
			        edgeOffset : 10,//offset from edge of $this
			        percentageInnerCutout : 55
			    }
				$("#age_restriction-chart-devices").drawDoughnutChart([
					{ title: "Mobile",  value : mobile,  color: "#64d1f4" },
					{ title: "Tablet",  value : tablet,  color: "#000000" },
					{ title: "Desktop", value:  desktop,   color: "#9acd00" }
				], optionsDevices);
				
				var optionsImpressions = {
			        segmentShowStroke : true,
			        segmentStrokeColor : "#f9f9f9",
			        segmentStrokeWidth : 10,
			        baseColor: "rgba(249,249,249,1)",
			        baseOffset: 4,
			        edgeOffset : 10,//offset from edge of $this
			        percentageInnerCutout : charts_math_innercut( hits ),
			        indexDataValue: 0
			    }
				$("#age_restriction-chart-impressions").drawDoughnutChart([
					{ title: "Total Impressions", value : hits, color: "#7ac127" }
				], optionsImpressions);
				
				var optionsAuths = {
			        segmentShowStroke : true,
			        segmentStrokeColor : "#f9f9f9",
			        segmentStrokeWidth : 10,
			        baseColor: "rgba(249,249,249,1)",
			        baseOffset: 4,
			        edgeOffset : 10,//offset from edge of $this
			        percentageInnerCutout : charts_math_innercut( auth ),
			        indexDataValue: 0
			    }
				$("#age_restriction-chart-authentications").drawDoughnutChart([
					{ title: "Total Authentications", value : auth, color: "#ff750a" }
				], optionsAuths);
				
				var optionsSNetworks = {
			        segmentShowStroke : true,
			        segmentStrokeColor : "#f9f9f9",
			        segmentStrokeWidth : 10,
			        baseColor: "rgba(249,249,249,1)",
			        baseOffset: 4,
			        edgeOffset : 10,//offset from edge of $this
			        percentageInnerCutout : 55
			    }
				$("#age_restriction-chart-snetworks").drawDoughnutChart([
					{ title: "Manual",  value : manual,  color: "#ffa700" },
					{ title: "Facebook",  value : facebook,  color: "#3b5998" },
					{ title: "Google", value:  google,   color: "#dd4b39  " }
				], optionsSNetworks);
				
				//loading.css('display', 'none');
				row_loading($('.age_restriction-chart-container'), 'close');
			}
		}, 'json');
	}
	
	function stats_load_more( $btn, action ) {
		row_loading($('#age_restriction-stats-'+action), 'show');

		var step = parseInt( localStorage.getItem('age_restriction-stats-step-'+action) ) > 0
			? parseInt( localStorage.getItem('age_restriction-stats-step-'+action) ) : 1;
		localStorage.setItem('age_restriction-stats-step-'+action, parseInt( step + 1 ));
 
		// float jQuery
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restrictionGetStats',
			'subaction'		: action,
			'step'			: step,
			'postid'		: $('#age_restriction-postid').val(),
			'from_date'		: $("#age_restriction-filter-by-date-from").val(),
			'to_date'		: $("#age_restriction-filter-by-date-to").val(),
			'debug_level'	: debug_level
		}, function(response) {
			//data not received!
			if (response.status == 'invalid') {
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-'+action), 'close');
				return false;
			}
			
			if( response.status == 'valid' ){
				$("#age_restriction-stats-"+action).append( response.data );
				//loading.css('display', 'none');
				row_loading($('#age_restriction-stats-'+action), 'close');
			}
			$btn.prop('disabled', false); $btn.removeClass('disabled');
		}, 'json');
	}
	
	function stats_buildInterface()
	{
        stats_vars = $('.age_restriction-statistics').first().find('.age_restriction-vars').html();
        stats_vars = JSON && JSON.parse(stats_vars) || $.parseJSON(stats_vars);
        stats_vars = $.extend(stats_vars, {});
        $('.age_restriction-statistics').find('.age_restriction-vars').remove();
        
		// Datepicker (range)
		$( "#age_restriction-filter-by-date-from" ).datepicker({
			changeMonth: true,
			//changeYear:	true,
			numberOfMonths: 1,
			dateFormat: "yy-mm-dd",
			maxDate: new Date(),
			onClose: function( selectedDate ) {
				$( "#age_restriction-filter-by-date-to" ).datepicker( "option", "minDate", selectedDate );
			}
		});
		
		$( "#age_restriction-filter-by-date-to" ).datepicker({
			changeMonth: true,
			//changeYear:	true,
			numberOfMonths: 1,
			dateFormat: "yy-mm-dd",
			maxDate: new Date(),
			onClose: function( selectedDate ) {
				$( "#age_restriction-filter-by-date-from" ).datepicker( "option", "maxDate", selectedDate );
			}
		});
		
		stats_refreshGraph();
		init_stats_charts();
		init_stats_country();
		//init_stats_keyword();
        init_stats_listtable();
		
		// reset storage
		for (var arr = ['age_restriction-stats-step-country', 'age_restriction-stats-step-keyword'], i = 0; i < arr.length; i++) {
			localStorage.removeItem( arr[i] );
		}
		
		// export event
        $('#age_restriction-stats-export').on('click', '#export-btn', function(e){
            e.preventDefault();
            
            export_stats();
        });
	}
	
	function charts_math_innercut(val) {
		val = '' + val;
		var l = val.length;
 
		if ( l > 8 ) {
			return 95;
		} else if ( l > 7 ) {
			return 85;
		} else if ( l > 6 ) {
			return 75;
		} else if ( l > 4 ) {
			return 65;
		} else if ( l > 2 ) {
			return 55;
		}
		return 55;
	}
	
	function get_banner_stats() {
		$('#age_restriction-postid').val( $('#age_restriction-choose-banner').val() );
		
		row_loading($('#age_restriction-banner-stats'), 'show');
		
		// float jQuery
		jQuery.post(ajaxurl, {
			'action' 		: 'age_restriction_getBannerStats',
			'postid'		: $('#age_restriction-choose-banner').val(),
			'debug_level'	: debug_level
		}, function(response) {
			//data not received!
			if (response.status == 'invalid') {
				row_loading($('#age_restriction-banner-stats'), 'close');
				return false;
			}
			
			if( response.status == 'valid' ){
				$("#age_restriction-banner-stats").html( response.html );
				row_loading($('#age_restriction-banner-stats'), 'close');
				
				var optinit = localStorage.getItem('age_restriction-stats-optinit') || false;
				localStorage.setItem( 'age_restriction-stats-optinit', true );
			
				// statistics tab
				stats_buildInterface();

				if ( !optinit ) {
					triggers_stats();
					
					$('body').on('change', '#age_restriction-choose-banner', function(e) {
						e.preventDefault();
						get_banner_stats();
					});
				}
			}
		}, 'json');
	}
	
	function triggers_stats() {
		$('body').on('click', "#age_restriction-filter-graph-data", function(e){
			e.preventDefault();

			// reset storage
			for (var arr = ['age_restriction-stats-step-country', 'age_restriction-stats-step-keyword'], i = 0; i < arr.length; i++) {
				localStorage.removeItem( arr[i] );
			}
		
			stats_refreshGraph();
			init_stats_charts();
			init_stats_country();
			//init_stats_keyword();
            init_stats_listtable();
		});
		$('body').on('click', '.age_restriction-load-more', function(e) {
			e.preventDefault();
			
			var $this = $(this), act = $this.data('action');
			$this.prop('disabled', true); $this.addClass('disabled');
			stats_load_more($this, act);
		});
	}
	
    function init_stats_listtable() {
        row_loading($('#age_restriction-stats-details'), 'show');
 
        // use the URL to extract our needed variables
        var query = location.href;

        // float jQuery
        jQuery.post(ajaxurl, {
            'page'          : __query( query, 'page' ) || '',
            'action'        : 'age_restriction_banners_list_table_full',
            'postid'        : $('#age_restriction-postid').val(),
            'from_date'     : $("#age_restriction-filter-by-date-from").val(),
            'to_date'       : $("#age_restriction-filter-by-date-to").val(),
            'debug_level'   : debug_level
        }, function(response) {
            //data not received!
            if (response.status == 'invalid') {
                row_loading($('#age_restriction-stats-details'), 'close');
                return false;
            }
            
            if( response.status == 'valid' ){
                $("#age_restriction-stats-details").html( response.data );
                
                // init back our event handlers
                listtable_triggers();

                //loading.css('display', 'none');
                row_loading($('#age_restriction-stats-details'), 'close');
            }
        }, 'json');
    }
    
    function update( data )
    {
        var data = typeof data == 'object' ? data : {};

        var maincontainer = $('#age_restriction-stats-details');

        row_loading($('#age_restriction-stats-details'), 'show');
  
        $.ajax({
            url: ajaxurl,
            data: $.extend(
                {
                    _ajax_custom_list_nonce: $('#_ajax_custom_list_nonce').val(),
                    action          : $("#ajaxid").val(),
                    'postid'        : $('#age_restriction-postid').val(),
                    'from_date'     : $("#age_restriction-filter-by-date-from").val(),
                    'to_date'       : $("#age_restriction-filter-by-date-to").val(),
                    'debug_level'   : debug_level
                },
                data
            ),
            // Handle the successful result
            success: function( response ) {
  
                // WP_List_Table::ajax_response() returns json
                var response = $.parseJSON( response );

                // add the requested rows
                if ( response.rows.length )
                    maincontainer.find('#the-list').html( response.rows );
                    
                // update column headers for sorting
                if ( response.column_headers.length )
                    maincontainer.find('thead tr, tfoot tr').html( response.column_headers );
                    
                // update pagination for navigation
                if ( response.pagination.bottom.length )
                    maincontainer.find('.tablenav.top .tablenav-pages').html( $(response.pagination.top).html() );
                    
                if ( response.pagination.top.length )
                    maincontainer.find('.tablenav.bottom .tablenav-pages').html( $(response.pagination.bottom).html() );

                // init back our event handlers
                listtable_triggers();
				
                row_loading($('#age_restriction-stats-details'), 'close');
            }
        });
    }
    
    function __query( query, variable )
    {
        var vars = query.split("&");
        for ( var i = 0; i <vars.length; i++ ) {
            var pair = vars[ i ].split("=");
            if ( pair[0] == variable )
                return pair[1];
        }
        return false;
    }
    
    function listtable_triggers() {
        var maincontainer = $('#age_restriction-stats-details');

        // Pagination links, sortable link
        maincontainer.find('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on('click', function(e) {
            e.preventDefault();
            
            // use the URL to extract our needed variables
            var query = this.search.substring( 1 );
            
            var data = {
                paged: __query( query, 'paged' ) || '1',
                order: __query( query, 'order' ) || 'desc',
                orderby: __query( query, 'orderby' ) || 'id'
            };
  
            update( data );
        });

        // page number input
        maincontainer.find('input[name=paged]').on('keyup', function(e) {

            // If user hit enter, we don't want to submit the form
            if ( 13 == e.which )
                e.preventDefault();

            // This time we fetch the variables in inputs
            var data = {
                paged: parseInt( maincontainer.find('input[name=paged]').val() ) || '1',
                order: maincontainer.find('input[name=order]').val() || 'desc',
                orderby: maincontainer.find('input[name=orderby]').val() || 'id',
                filter_by: maincontainer.find('input[name=filter_by]').val() || 'post',
            };
 
            update( data );
        });
        
        maincontainer.find('select[name=filter_by]').on('change', function(e) {
            var that = $(this),
                val = that.val();
            
            maincontainer.find('select[name=filter_by]').val( val ); 
        });
        
        // bulk actions - Apply
        maincontainer.find('input#doaction').on('click', function(e) {
            e.preventDefault();
            
            var that = $(this),
                form = that.parents('form').eq(0),
                _action = form.find('select#bulk-action-selector-top').val();
 
            // '_ajax_custom_list_nonce', 'ajaxid', 'order', 'orderby', '_wpnonce', '_wp_http_referer'
            var field_reset = ['_ajax_custom_list_nonce', 'ajaxid', 'order', 'orderby', '_wp_http_referer'];
            for (var i in field_reset) {
                $( 'input[name="'+field_reset[i]+'"]' ).remove();
            }
            if ( 'delete' == _action ) {
                $( 'input[name="paged"]' ).val(1);
            }
  
            form.submit();
        });
    }
	
    function export_stats() {
        var maincontainer = $('#age_restriction-stats-export');
        
        var data = {
            'postid'        : $('#age_restriction-postid').val(),
            'from_date'     : $("#age_restriction-filter-by-date-from").val(),
            'to_date'       : $("#age_restriction-filter-by-date-to").val(),
                    
            'action'        : 'age_restriction_banners_list_export',
            'sub_action'    : 'export',
            'debug_level'   : debug_level
        };
        
        // get selected columns
        var export_type = maincontainer.find('#export_type').val();
		
        var cols = maincontainer.find('table input[name^="export-col"]:checked').map(function() {
            return $(this).prop('id').replace('export-col-', '');}
        ).get().join(',');
        
        data = $.extend({
           cols                     : cols,
           export_type              : export_type,
           export_action            : maincontainer.find('#export_action').val(),
           export_device_type       : maincontainer.find('#export_device_type').val(),
           export_verify_source     : maincontainer.find('#export_verify_source').val(),
           export_orderby           : maincontainer.find('#export_orderby').val(),
           export_order             : maincontainer.find('#export_order').val()
        }, data);
        
        jQuery.post(ajaxurl, data, function(response) {
            if( response.status == 'valid' ){
                maincontainer.find('.action_status').removeClass('age_restriction-error').addClass('age_restriction-success')
                    .html( response.data ).show();
            } else {
                maincontainer.find('.action_status').removeClass('age_restriction-success').addClass('age_restriction-error')
                    .html( response.data ).show();
                return false;
            }
            
            // build download link
            data = $.param( data );
            var dwurl = stats_vars.ajaxurl + '?' + data + '&do_export=1';
            console.log( dwurl );
            
            // force download        
            window.location = dwurl;
            return true;

        }, 'json');
    }


	function triggers()
	{
		if ( age_restriction_stats_loc == 'admin_metabox' ) { 
			// register post type - metabox
			fixMetaBoxLayout();
			
			// register post type metabox
			metabox_post_type();
	
			// wp publish button
			var btnPublishActive = false; 
			$('.inside #publish').on('click', function(e) {
				// remove nonce and referer from custom listtable
				jQuery('#age_restriction-stats-details #_wpnonce, #age_restriction-stats-details input[name="_wp_http_referer"]').remove();
				
				if ( btnPublishActive ) {
					age_restriction.multiselect_left2right(true);
					
					return true;
				}
	
				e.preventDefault();
				btnPublishActive = true;
				$(this).trigger('click'); // trigger event action!
			});
			$(document).on('keypress', function(e) {
				if(e.which == 13 && e.target.tagName != 'TEXTAREA') { // Enter
					btnPublishActive = true;
					$('.inside #publish').trigger('click'); // trigger event action!
				}
				e.stopPropagation();
				return true;
			});
			
			// statistics tab
			stats_buildInterface();
			triggers_stats();

		} else if ( age_restriction_stats_loc == 'admin_options' ) {

			localStorage.removeItem( 'age_restriction-stats-optinit' );
			get_banner_stats();
		}
	}
	
	var misc = {

		arrayHasOwnIndex: function(array, prop) {
			return array.hasOwnProperty(prop) && /^0$|^[1-9]\d*$/.test(prop) && prop <= 4294967294; // 2^32 - 2
		},
		
		hasOwnProperty: function(obj, prop) {
			var proto = obj.__proto__ || obj.constructor.prototype;
			return (prop in obj) &&
			(!(prop in proto) || proto[prop] !== obj[prop]);
		}

	};

	// external usage
	return {
		"stats_buildInterface"		: stats_buildInterface
    }
})(jQuery);