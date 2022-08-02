import '@google/model-viewer';

(function ($) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).on('load', function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practice to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$(function () {
		$("#dialog").dialog({
			autoOpen: false,
			modal: true,
			//Set the responsive dialog
			width: "auto",
  			// maxWidth: 660, // This won't work
			create: function( event, ui ) {
				// Set maxWidth
				$(this).css("maxWidth", "660px");
			}
		});
		$("#ar_model_viewer_for_woocommerce_btn").click(function () {
			$("#dialog").dialog("open");
		});
	});
})(jQuery);