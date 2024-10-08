//import '@google/model-viewer';
import alertify from 'alertifyjs';
import 'alertifyjs/build/css/alertify.css'; // Importa los estilos de Alertify
import 'alertifyjs/build/css/themes/default.css'; // Opcional: Importa un tema, como el tema por defecto
import {
	driver
} from "driver.js";
import "driver.js/dist/driver.css";

(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

		if ($('#ar_model_viewer_for_woocommerce_ar2').is(':checked')) {
			//alert("Radio Button Is checked!");
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-modes').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-scale').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-placement').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-xr-environment').hide();
			$('#cmb2-id-ar-model-viewer-for-woocommerce-ar-settings').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button').hide();
		}
		if ($('#ar_model_viewer_for_woocommerce_ar_button2').is(':checked')) {
			//alert("Radio Button Is checked!");
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button-text').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button-background-color').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button-text-color').hide();
		}
	});

})(jQuery);