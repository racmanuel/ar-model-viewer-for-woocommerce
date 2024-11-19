import '@google/model-viewer';
import alertify from 'alertifyjs';
import 'alertifyjs/build/css/alertify.css'; // Importa los estilos de Alertify
import 'alertifyjs/build/css/themes/default.css'; // Opcional: Importa un tema, como el tema por defecto

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
		$("#ar_model_viewer_for_woocommerce_btn").click(function () {
			// Recuperar el product_id desde el data-product-id del botón
			var product_id = $(this).data('product-id');

			// Verificar si el product_id está definido
			if (!product_id) {
				console.error('Product ID is missing');
				return;
			}

			// Tu contenido HTML personalizado, agregando el atributo camera-controls y estilos de centrado
			var htmlContent = `
				<div style="display: flex; justify-content: center; align-items: center; height: 100%;">
					<model-viewer 
						id="model-viewer" 
						src="" 
						alt="" 
						poster="" 
						reveal="" 
						loading="" 
						ar 
						ar-modes="" 
						camera-controls
						ar-scale="auto"
						style="width: 100%; max-width: 600px; height: 400px;"
					></model-viewer>
				</div>`;

			// Variable para almacenar el mensaje de carga
			var loadingMessage;

			$.ajax({
				type: "POST",
				url: ajax_object.ajax_url,
				data: {
					action: 'ar_model_viewer_for_woocommerce_get_model_and_settings',
					product_id: product_id // Pasar el ID del producto en la solicitud AJAX
				},
				dataType: "json",

				// Mostrar mensaje de carga antes de la solicitud
				beforeSend: function () {
					loadingMessage = alertify.success('Cargando el modelo 3D...', 0); // Muestra el mensaje hasta que se complete la carga
				},

				success: function (response) {
					console.log(response);

					// Ocultar el mensaje de carga
					if (loadingMessage) {
						loadingMessage.dismiss();
					}

					if (response.success) {
						var data = response.data;

						// Usar el nombre del producto en el título del modal
						var productName = data.product_name || 'Modelo 3D';

						alertify
							.alert(productName, htmlContent)
							.set({
								transition: 'zoom',
								movable: true,
								maximizable: true
							}) // Puedes ajustar las opciones
							.setHeader(productName); // Usar el nombre del producto en el header

						// Verificar que los datos existen antes de asignarlos al model-viewer
						if (data) {
							$('#model-viewer').attr('src', data.model_3d_file || '');
							$('#model-viewer').attr('alt', data.model_alt || '');
							$('#model-viewer').attr('poster', data.model_poster || '');
							$('#model-viewer').attr('reveal', data.reveal || 'auto');
							$('#model-viewer').attr('loading', data.loading || 'auto');
							$('#model-viewer').attr('ar-modes', (data.ar_modes || []).join(' '));
							$('#model-viewer').css('background-color', data.poster_color || 'rgba(255,255,255,0)');

							// Asegúrate de que el valor de scale sea correcto
							var scale = data.scale || 'auto'; // Valor por defecto si no está definido
							$('#model-viewer').attr('ar-scale', scale); // Usar "auto" o "fixed" según corresponda
						}
					} else {
						console.error(response.data);
					}
				},

				// Ocultar el mensaje de carga en caso de error
				error: function (xhr, status, error) {
					console.error('AJAX Error:', error);
					if (loadingMessage) {
						loadingMessage.dismiss();
					}
				}
			});
		});
	});
})(jQuery);