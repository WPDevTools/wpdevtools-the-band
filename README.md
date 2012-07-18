WPDevTools - The Band Plugin for WordPress
==========================================

The Band provides a way to add slideshow galleries to a site that include full HTML, Javascript and CSS support for each slide, all within a simple to use, WordPress-style interface.  The Band has two parts: slides and the_band shortcode.  To get started, simply create the slides you intend to use through the -Slides- content interface.  After creating one or more slides, you can then place your slideshow in any content area by inserting the shortcode.


Plugin API
----------
The following are the specifications for how to call the band and what variables it will accept.

### Shortcode
**Shortcode Handle:** `the_band`
**Accepts Content:** Yes, using the `[slide][/slide]` shortcode
**Attributes:**

* `id` - A comma-separated list of slide IDs to display in the band
* `category` - Currently non-functional
* `full_band` - true | false - If true, the band will display across the full width of the viewport
* `height` - The desired height for the band.  If not specified, the band will default to the height of the largest slide displayed
* `margin` - CSS formatted margin specs for the outside of the band
* `timer` - The amount of time, in milliseconds, a slide should be displayed, defaults to 7 seconds
* `transition` - The amount of time, in milliseconds, taken for a slide to move into or out of place, defaults to 1 second

### Slides
Slides act like normal posts, but have a number of custom fields that allow a slide creator to quickly adjust parts of the slide without resorting to code.  The additional fields include:

* **Feature Image** - This is used to set the background image for the slide and can be set using the standard Feature Image interface on the right-hand side of the editing window
* **Background Color** - Each slide have have a full-width background color set
* **Padding** - This is the inner padding for the slide contents of the slide
* **Custom CSS** - Elements within the slide can be styled using the CSS here
* **Slide Javascript: On Load** - Javascript placed here will trigger when the slide is first loaded and begins transitioning into place
* **Slide Javascript: On Ready** - Javascript placed here will trigger when the slide is in position
* **Slide Javascript: On Unload** - Javascript places here will trigger when the slide begins to transition out of place

jQuery animation and transitions are supported through the plugin and elements within the slide can be targeted through either a class or ID selector.

### Shortcode Slides
Slides can be added to a band using the inline `[slide][/slide]` shortcode.  Any number of slides can be added to a single post or page without creating separate slide items.  The slide shortcode supports the following attributes:

* `background-image` - The URL to a background image to use for the slide
* `background-color` - Each slide have have a full-width background color set
* `padding` - This is the inner padding for the slide contents of the slide
* `height` - The desired height for the slide


Code Examples
-------------

		[the_band]
Displays all of the slides for the site in order of date published.

		[the_band id="45,46,53" full_band="true"]
Displays a set of slides with the full, color band going across the width of the window.

		[the_band height="100px"]
Display a band with a height of 100 pixels, no matter how tall the member slides are.

		[the_band]
			[slide background-color="red"]My First Slide[/slide]
			[slide background-color="white"]My Second Slide[/slide]
		[/the_band]
Displays two slides using the inline slide shortcode feature.  The first slide will have a red background while the second has a white background.


License
-------

Licensed under the GNU General Public License.