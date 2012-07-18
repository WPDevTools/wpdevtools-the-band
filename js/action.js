var TheBand = {

	bands : [],

	initialize : function () {

		// Iterate through the bands
		jQuery('.the_band').each(function () {

			// Create a new band in memory
			var current_band_id = TheBand.bands.length;

			// Find all of the elements inside the band
			var parent = jQuery(this).parent().addClass('band_parent');
			var slides = jQuery(this).find('.band_slide');
			var back = jQuery(this).find('.band_back');
			var viewport = jQuery(this).find('.band_viewport');
			var nav = jQuery(this).find('.band_nav');
			var nav_buttons = jQuery(this).find('.nav_button');

			// Set up the internal variables for this band instance			
			TheBand.bands[current_band_id] = {
				parent : parent,
				band : this,
				back : back,
				viewport : viewport,

				slides : slides,
				slide_count : slides.length,
				slide_script : [],

				nav : nav,
				nav_buttons : nav_buttons,

				max_height : 0,
				current_slide : null,

				obj_timer : null,
				timer : parseInt(jQuery(this).attr('timer')),
				transition_time : parseInt(jQuery(this).attr('transition'))
			}

			// Set up the current_band shorthand
			var current_band = TheBand.bands[current_band_id];
			
			// Set the current widths of the band and viewport
			TheBand.setWidths(current_band);

			// Find the largest slide height for this set
			slides.each(function () {
			
				// Grab the script from the slide (remove it) and add it to the slide's hook
				var current_script_id = current_band.slide_script.length;
				
				 eval(jQuery(this).find('div.script').html().replace('<!--// slide_script', 'TheBand.bands[' + current_band_id + '].slide_script[' + current_script_id + ']').replace(' -->', ''));
				
				// Get the maximum height and set it
				if (jQuery(this).height() >= current_band.max_height) { current_band.max_height = jQuery(this).height(); }
				jQuery(this).width(jQuery(current_band.viewport).width());
				
				// Set event hooks for links

				// Next Slide
				jQuery(this).find('a[href="#next_slide"]').click(function () {
					TheBand.nextSlide(current_band_id);
					clearInterval(current_band.obj_timer);
					return false;
				});
				
				// Previous Slide
				jQuery(this).find('a[href="#previous_slide"]').click(function () {
					TheBand.nextSlide(current_band_id);
					clearInterval(current_band.obj_timer);
					return false;
				});
				
				// Numbered Slide
				jQuery(this).find('a[href^="#slide_"]').click(function () {
					TheBand.changeSlide(current_band_id, parseInt(jQuery(this).attr('href').replace('#slide_', '')));
					clearInterval(current_band.obj_timer);
					return false;
				});

			});
			
			// Add the height of the nav to the window - keep this in mind when building backgrounds
			current_band.max_height += nav.height();

			// Make the nav buttons clickable
			nav_buttons.each(function () {
				var selected_slide = parseInt(jQuery(this).attr('href').replace('#slide_',''));
				jQuery(this).click(function () {
					TheBand.changeSlide(current_band_id, selected_slide);
					clearInterval(current_band.obj_timer);
					return false;
				});
			})


			// Size the inline band	and viewport		
			jQuery(this).height(current_band.max_height);
			jQuery(viewport).height(current_band.max_height);
			
			// Show the first slide
			TheBand.showSlide(current_band_id, 0);
			
			// Set up the timed transitions
			if (current_band.timer > 0) {
				current_band.obj_timer = window.setInterval(function () {
					TheBand.nextSlide(current_band_id);
				}, current_band.timer);
			}
		})

		// If the window is resized, reset the band widths
		jQuery(window).resize(function () { 
			jQuery(TheBand.bands).each(function () {
				TheBand.setWidths(this);
			}); 
		})
	
	},

	setWidths : function (obj_band) {
		// Calculate the current body and parent container widths
		var body_width = jQuery('body').width();
		var parent_width = jQuery(obj_band.band).parent().width();
		var margin_left = jQuery(obj_band.band).parent().offset().left;
		var margin_right = body_width - (jQuery(obj_band.band).parent().offset().left + jQuery(obj_band.band).parent().width());

		// Set the band and viewport widths to correctly reflect the current state of the window
		jQuery(obj_band.back).width(body_width).css('margin-left', 0 - margin_left).css('margin-right', 0 - margin_right);
		//jQuery(obj_band.viewport).width(parent_width).css('margin-left', margin_left);
	},
	
	showSlide : function (band_id, slide_id) {
		var obj_band = TheBand.bands[band_id];

		// Hide the current slide
		jQuery(obj_band.slides[obj_band.current_slide]).hide();
		jQuery(obj_band.nav_buttons[obj_band.current_slide]).removeClass('selected');

		// Show the new slide
		jQuery(obj_band.slides[slide_id]).show().height(obj_band.max_height);
		jQuery(obj_band.nav_buttons[slide_id]).addClass('selected');
		
		// Set the background color
		jQuery(obj_band.back).css('background-color', jQuery(obj_band.slides[slide_id]).css('background-color'));
		
		// Set the current slide variable
		obj_band.current_slide = slide_id;

		// Set the correct Nav color
		TheBand.setNavColor(band_id);
		
		// Run the slide javascript - be careful with this!
		eval(obj_band.slide_script[slide_id].ready);
		
	},
	
	changeSlide : function (band_id, slide_id) {
		var obj_band = TheBand.bands[band_id];
		
		if (slide_id != obj_band.current_slide) {

			// Get the viewport width for display purposes
			var slide_width = jQuery(obj_band.viewport).width();
			
			// Set up the slide objects
			var current_slide = jQuery(obj_band.slides[obj_band.current_slide]);
			var new_slide = jQuery(obj_band.slides[slide_id]);
			
			// Set the left value for the new slide and show it
			new_slide.css('left', slide_width).width(slide_width).height(obj_band.max_height);
			new_slide.show();
	
			// Animate the slide trasition
			current_slide.animate({ left: -slide_width }, obj_band.transition_time);
			new_slide.animate({ left: 0 }, obj_band.transition_time, 'swing');
			if (new_slide.css('background-color') != current_slide.css('background-color')) {
				jQuery(obj_band.back).animate({ backgroundColor : new_slide.css('background-color') }, obj_band.transition_time);
			}
			
			// Set the class for the nav buttons
			jQuery(obj_band.nav_buttons[obj_band.current_slide]).removeClass('selected');
			jQuery(obj_band.nav_buttons[slide_id]).addClass('selected');
			
			// Run the slide javascript - be careful with this!
			eval(obj_band.slide_script[obj_band.current_slide].unload);
			eval(obj_band.slide_script[slide_id].load);

			window.setTimeout(function () {
				eval(obj_band.slide_script[slide_id].ready);
			}, obj_band.transition_time);

			// Set the current slide variable
			obj_band.current_slide = slide_id;

			// Set the correct Nav color
			TheBand.setNavColor(band_id);

		}
		
	},
	
	nextSlide : function (band_id) {
		var obj_band = TheBand.bands[band_id];
		
		if (obj_band.current_slide + 1 >= obj_band.slide_count) { new_slide = 0 } else { new_slide = obj_band.current_slide + 1 }
		TheBand.changeSlide(band_id, new_slide);
	},
	
	previousSlide : function (band_id) {
		var obj_band = TheBand.bands[band_id];
		
		if (obj_band.current_slide - 1 < 0) { new_slide = obj_band.slide_count - 1 } else { new_slide = obj_band.current_slide - 1 }
		TheBand.changeSlide(band_id, new_slide);
	},
	
	setNavColor : function (band_id) {
		var obj_band = TheBand.bands[band_id];
		var slide = jQuery(obj_band.slides[obj_band.current_slide]);
		
		new_hex = '0x' + TheBand.rgb2hex(slide.css('background-color'));
		new_color = parseInt(new_hex);
		
		if (new_hex != '0x') {

			if (new_color < 7829367) {
				jQuery(obj_band.nav).addClass('light');
			} else {
				jQuery(obj_band.nav).removeClass('light');
			}

		}
	},
	
	rgb2hex : function (rgb) {
	    rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);
	    function hex(x) {
	        return ("0" + parseInt(x).toString(16)).slice(-2);
	    }
	    if (rgb != null && rgb[4] != 0) {
	    	return hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
	    } else {
	    	return '';
	    }
	}

}

// Initialze the Band
jQuery(document).ready(function () {
	TheBand.initialize();
})
