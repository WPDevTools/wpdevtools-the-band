<?php
/*
Plugin Name: WPDevTools - The Band
Plugin URI: http://wpdevtools.com
Description: It's a slider with some serious mojo: playback hooks, a custom content type, javascript callbacks... it's got it all.
Author: Christopher Frazier
Version: 0.2
Author URI: http://wpdevtools.com

*/

class TheBand {

	// Display the Band and relevant slides
	public function process ($atts, $content = '') {
	
		extract(shortcode_atts(array(
			'id' => '',
			'category' => '',
			'taxonomy' => '',
			'full_band' => 'false',
			'height' => '',
			'margin' => '',
			'timer' => '7000',
			'transition' => '1000',
			'background' => '',
		), $atts));
	
		// If no band content was provided, use the standard interface
	
		if (trim($content) == '') {
	
			$query_args = array('post_type' => 'theband-slides');
			
			if ($id != '') { $query_args['post__in'] = split(',', $id); }
			if ($category != '') { $query_args['category'] = split(',', $category); }
			if ($taxonomy != '') {
			    $query = array(
			        'taxonomy' => 'slide_categories',
			        'field' => 'slug',
			        'terms' => split(',', $taxonomy)
			    );
			    $query_args['tax_query'] = array($query);
			}
			
			// Query for slides
			$slides = new WP_Query($query_args);
		
			// Iterate through all of the slides found
			if ( $slides->have_posts() ) { 
		
				$html = '<div class="the_band" style="margin: ' . $margin . '" transition="' . $transition . '" timer="' . $timer . '">';
				if ($full_band != 'false') { $html .= '<div class="band_back" style="background: center top url(' . $background . ');"></div>'; }
				$html .= '<div class="band_viewport">' . "\n";
		
		
				while ( $slides->have_posts() ) { 
		
					$slides->the_post();
					
					$slide_content = TheBand::clean_shortcode_content($slides->post->post_content);
		
					$custom = get_post_custom($slides->post->ID);
					$background_image = wp_get_attachment_image_src($custom['_thumbnail_id'][0], 'full');
					$html .= '<div id="slide-' . $slides->post->ID . '" class="band_slide" style="background-color: ' . $custom["bgcolor"][0] . '; height: ' . $custom["height"][0] . '; background-image: url(' . $background_image[0] . ');"><div class="band_html" style=" padding: ' . $custom["padding"][0] . ';">' . $slide_content . '</div>' . "\n";
					$html .= '<style>' . $custom["css"][0] . '</style>' . "\n";
					$html .= '<div class="script"><!--// slide_script = { load : "' . str_replace(array("\r\n", "\n", "\r"), ' ', addslashes($custom["load"][0])) . '", ready : "' . str_replace(array("\r\n", "\n", "\r"), ' ', addslashes($custom["ready"][0])) . '", unload : "' . str_replace(array("\r\n", "\n", "\r"), ' ', addslashes($custom["unload"][0])) . '"} --></div></div>' . "\n";
				}
		
				if ($slides->post_count > 1) {
			
					$html .= '<div class="band_nav">';
			
					for ($i=0; $i < $slides->post_count; $i++) {
						$html .= '<a href="#slide_'  . $i . '" class="nav_button">' . ($i + 1) . '</a>';
					}
			
					$html .= '</div>' . "\n";
				}
				
				$html .= '</div></div>' . "\n";
		
			} else {
			
				$html = 'No slides found.';
		
			}
			
			// Return control to the main loop
			wp_reset_postdata();
	
		} else {
			
			// Display slides from the $content variable
			$content = trim($content);
	
			// Split out all of the slides in the $content variable
			preg_match_all("/\[slide(.*?)\]([\s\S]*?)\[\/slide\]/", $content, $slides);
			
			// Display the shortcode provided HTML in the band interface
	
			$html = '<div class="the_band" style="margin: ' . $margin . '" transition="' . $transition . '" timer="' . $timer . '">';
				if ($full_band != 'false') { $html .= '<div class="band_back" style="background-image: url(' . $background . ');"></div>'; }
			$html .= '<div class="band_viewport">' . "\n";
			
			for ($count = 0; $count < count($slides[0]); $count++) {
			
				preg_match_all("/\s(.+?)=[\"'](.+?)[\"']/", $slides[1][$count], $slide_attributes);
				
				// Clear out the options array
				$slide_options = array(
					'bgcolor' => '',
					'height' => '',
					'padding' => '',
					'image' => ''
				);
				
				for ($slide_option = 0; $slide_option < count($slide_attributes[0]); $slide_option++) {

					switch ($slide_attributes[1][$slide_option]) {
						case "background-color":
							$slide_options['bgcolor'] = $slide_attributes[2][$slide_option];
							break;
						case "height":
							$slide_options['height'] = $slide_attributes[2][$slide_option];
							break;
						case "padding":
							$slide_options['padding'] = $slide_attributes[2][$slide_option];
							break;
						case "background-image":
							$slide_options['image'] = $slide_attributes[2][$slide_option];
							break;
					}
				}
				
				$html .= '<div id="slide-' . $count . '" class="band_slide" style="background-color: ' . $slide_options['bgcolor'] . '; height: ' . $slide_options['height'] . '; background-image: url(' . $slide_options['image'] . ');"><div class="band_html" style=" padding: ' . $slide_options['padding'] . ';">' . TheBand::clean_shortcode_content($slides[2][$count]) . '</div>' . "\n";
				$html .= '<div class="script"><!--// slide_script = { load : "", ready : "", unload : ""} --></div></div>' . "\n";
			}
	
			if (count($slides[0]) > 1) {
		
				$html .= '<div class="band_nav">';
		
				for ($i=0; $i < count($slides[0]); $i++) {
					$html .= '<a href="#slide_'  . $i . '" class="nav_button">' . ($i + 1) . '</a>';
				}
		
				$html .= '</div>' . "\n";
			}
	
			
			$html .= '</div></div>' . "\n";
		
		}
		
		return $html;
	
	}

	// Enqueue all scripts and CSS
	public function enqueue_scripts () {
		if (is_admin()) {
			wp_enqueue_script("color-picker", plugins_url('/lib/jQuery-ColorPicker/colorpicker.min.js', __FILE__));
			wp_enqueue_style("color-picker", plugins_url('/lib/jQuery-ColorPicker/css/colorpicker.min.css', __FILE__));

			wp_enqueue_script("codemirror", plugins_url('/lib/codemirror2/lib/codemirror.js', __FILE__));
			wp_enqueue_style("codemirror", plugins_url('/lib/codemirror2/lib/codemirror.css', __FILE__));

			wp_enqueue_script("codemirror-mode-javascript", plugins_url('/lib/codemirror2/mode/javascript/javascript.js', __FILE__));
			wp_enqueue_script("codemirror-mode-css", plugins_url('/lib/codemirror2/mode/css/css.js', __FILE__));
			wp_enqueue_style("codemirror-theme-default", plugins_url('/lib/codemirror2/theme/elegant.css', __FILE__));

		} else {
	
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script('jquery-color');
		
			wp_enqueue_script("wpdevtools-the-band", plugins_url('/js/action.js', __FILE__));
			wp_enqueue_style("wpdevtools-the-band", plugins_url('/css/style.css', __FILE__));
	
		}
	}

	// Register the Slides custom post type
	public function setup_post_type() {
		$labels = array(
			'name' => _x('Slides', 'post type general name'),
			'singular_name' => _x('Slide', 'post type singular name'),
			'add_new' => _x('Add New', 'portfolio item'),
			'add_new_item' => __('Add New Slide'),
			'edit_item' => __('Edit Slide'),
			'new_item' => __('New Slide'),
			'view_item' => __('View Slide'),
			'search_items' => __('Search Slides'),
			'not_found' =>  __('Nothing found'),
			'not_found_in_trash' => __('Nothing found in Trash'),
			'parent_item_colon' => ''
		);
	 
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title','editor','thumbnail')
		  ); 
	 
		register_post_type( 'theband-slides' , $args );
		
		register_taxonomy("slide_categories", array("theband-slides"), array("hierarchical" => true, "label" => "Slide Categories", "singular_label" => "Slide Category", "rewrite" => true));
	
	}

	// Add the slides metadata section to the slide options area
	public function init_slides_admin(){
	  add_meta_box("theband_slides_meta", "Slide Options", array('TheBand','show_slides_meta'), "theband-slides", "normal", "low");
	  add_meta_box("theband_slides_js", "Slide Javascript", array('TheBand','show_slides_js'), "theband-slides", "normal", "low");
	}

	// Display the slide metadata in the slide editor
	public function show_slides_meta() {
		global $post;
		$custom = get_post_custom($post->ID);
		if (isset($custom['bgcolor'])) {
			$bgcolor = $custom["bgcolor"][0];
			$height = $custom["height"][0];
			$padding = $custom["padding"][0];
			$css = $custom["css"][0];
		}
	?>
	
		<p><label>Background Color:</label><br />
		<input name="bgcolor" id="bgcolor" value="<?php echo $bgcolor; ?>"></p>
	
		<p><label>Slide Height:</label><br />
		<input name="height" id="height" value="<?php echo $height; ?>"></p>
	
		<p><label>Inner HTML Padding:</label><br />
		<input name="padding" id="padding" value="<?php echo $padding; ?>"></p>
	
		<p><label>Custom CSS:</label><br />
		<div class="widget" style="background-color: #fff;"><textarea style="width: 100%;" rows="10" name="css" id="css"><?php echo $css; ?></textarea></div></p>
		
		<script>
		jQuery(document).ready(function () {
			jQuery('#bgcolor').ColorPicker({
				onChange : function (hsb, hex, rgb) {
					jQuery('#bgcolor').attr('value', '#' + hex);
				}
			});

			var cmCSS = CodeMirror.fromTextArea(document.getElementById("css"), { mode : 'css', lineNumbers : true });
			var cmJSLoad = CodeMirror.fromTextArea(document.getElementById("load"), { mode : 'javascript', lineNumbers : true });
			var cmJSReady = CodeMirror.fromTextArea(document.getElementById("ready"), { mode : 'javascript', lineNumbers : true });
			var cmJSUnload = CodeMirror.fromTextArea(document.getElementById("unload"), { mode : 'javascript', lineNumbers : true });
		})
		</script>
	
	<?php
	}
	
	// Display the slide metadata in the slide editor
	public function show_slides_js() {
		global $post;
		$custom = get_post_custom($post->ID);
		if (isset($custom['load'])) {
			$load = $custom["load"][0];
			$ready = $custom["ready"][0];
			$unload = $custom["unload"][0];
		}
	?>
	
		<p><label>On Load:</label><br />
		<div class="widget" style="background-color: #fff;"><textarea style="width: 100%;" rows="10" name="load" id="load"><?php echo $load; ?></textarea></div></p>
	
		<p><label>On Ready:</label><br />
		<div class="widget" style="background-color: #fff;"><textarea style="width: 100%;" rows="10" name="ready" id="ready"><?php echo $ready; ?></textarea></div></p>
		
		<p><label>On Unload:</label><br />
		<div class="widget" style="background-color: #fff;"><textarea style="width: 100%;" rows="10" name="unload" id="unload"><?php echo $unload; ?></textarea></div></p>
	
	<?php
	}

	// Collect and save the slide meta data
	public function save_slides_meta(){
		global $post;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			// Do nothing?
		} else {
			if (isset($post->ID) && get_post_type($post->ID) == 'theband-slides') {
				update_post_meta($post->ID, "bgcolor", $_POST["bgcolor"]);
				update_post_meta($post->ID, "height", $_POST["height"]);
				update_post_meta($post->ID, "padding", $_POST["padding"]);
				update_post_meta($post->ID, "css", $_POST["css"]);
				update_post_meta($post->ID, "load", $_POST["load"]);
				update_post_meta($post->ID, "ready", $_POST["ready"]);
				update_post_meta($post->ID, "unload", $_POST["unload"]);
			}
		}
	}

	public function clean_shortcode_content( $content ) {

	// Found here: http://donalmacarthur.com/articles/cleaning-up-wordpress-shortcode-formatting/
	
	
		/* Parse nested shortcodes and add formatting. */
		$content = trim( wpautop( do_shortcode( $content ) ) );
		
		/* Remove '</p>' from the start of the string. */
		if ( substr( $content, 0, 4 ) == '</p>' )
			$content = substr( $content, 4 );
		
		/* Remove '<p>' from the end of the string. */
		if ( substr( $content, -3, 3 ) == '<p>' )
			$content = substr( $content, 0, -3 );
		
		/* Remove any instances of '<p></p>'. */
		$content = str_replace( array( '<p></p>' ), '', $content );
		
		return $content;
	}


}



// Register all of the actions
add_shortcode('the_band', array('TheBand','process'));
add_action('init', array('TheBand','enqueue_scripts'));
add_action('init', array('TheBand','setup_post_type'));
add_action('admin_init', array('TheBand','init_slides_admin'));
add_action('save_post', array('TheBand','save_slides_meta'));

?>