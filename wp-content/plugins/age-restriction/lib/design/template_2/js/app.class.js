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
	        /* alert($this.val()); Uncomment this for demonstration! */
	    });
	
	    // Hides the unordered list when clicking outside of it
	    $(document).click(function () {
	        $styledSelect.removeClass('active');
	        $list.hide();
	    });
	    
	});
}

if( !ageRestriction_isMobile.any && $(window).width() >= 768 ) {
	initCustomSelect();
}
