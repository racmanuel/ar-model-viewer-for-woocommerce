import '@google/model-viewer';
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";
import swal from 'sweetalert';

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
	document.addEventListener("DOMContentLoaded", function() {
		Fancybox.bind("[data-fancybox]", {});
	});

	$(function () {
		$(".view-ar-model").click(function (e) {
			e.preventDefault();

			// Obtener el valor del data-id
			var postId = $(this).data('id');

			// Imprimir el valor en la consola (o hacer algo con él)
			console.log("El valor de data-id es: " + postId);

			// Aquí puedes agregar cualquier otra lógica que necesites usando el valor de postId
			$.ajax({
				type: "POST",
				url: ajax_object.ajax_url,
				data: {
					action: 'ar_model_viewer_for_woocommerce_get_model_data',
					post_id: postId,
					nonce: ajax_object.ajax_nonce
				},
				dataType: "json",
				beforeSend: function (response) {
					swal({
						title: "Cargando...",
						text: "Por favor espera mientras se carga el modelo.",
						icon: "https://loading.io/icon/5pwfff", // Puedes usar una URL de imagen para un mejor efecto
						button: false,
						allowOutsideClick: false
					});
				},
				success: function (response) {
					// Cierra la alerta de SweetAlert cuando la solicitud es exitosa
					swal.close();
					// Aquí se asume que response contiene los datos necesarios
					var modelViewerHtml = `<model-viewer 
                    alt="${response.data.alt}" 
                    src="${response.data.android_file}" 
                    ios-src="${response.data.ios_file}" 
                    poster="${response.data.poster}" 
                    ar 
                    ar-modes="webxr scene-viewer quick-look" 
                    camera-controls 
                    seamless-poster 
                    shadow-intensity="1" 
                    enable-pan
					style="width: 100%; height: 350px;">
                </model-viewer>`;

					swal({
						html: true,
						content: {
							element: "div",
							attributes: {
								innerHTML: modelViewerHtml
							},
						},
						confirm: {
							text: "Close",
							value: true,
							visible: true,
							closeModal: true
						},
						closeOnClickOutside: false,
						closeOnEsc: false,
					});
				},
				error: function (xhr, status, error) {
					// Manejo de errores
					console.log(error);
					swal({
						title: "Error",
						text: "Hubo un problema al cargar el modelo.",
						type: "error",
						confirmButtonText: "Ok"
					});
				}
			});
		});
	});

})(jQuery);