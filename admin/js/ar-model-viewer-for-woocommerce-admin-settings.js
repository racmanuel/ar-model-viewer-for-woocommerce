import "@google/model-viewer";

(function ($) {
  "use strict";

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
    /*$.ajax({
      type: "POST",
      url: ajax_object.ajax_url,
      data: {
        action:
          "ar_model_viewer_for_woocommerce_get_model_preview_with_global_settings",
      },
      dataType: "json",
      success: function (response) {
        console.log("AJAX Response:", response);

        // Verificar si la respuesta tiene éxito y contiene datos válidos
        if (
          !response ||
          !response.success ||
          !response.data ||
          typeof response.data !== "object"
        ) {
          console.error("Invalid response data from server");
          return;
        }

        // Extraer datos desde response.data con valores predeterminados
        const {
          ar_modes = [],
          model_3d_file = "",
          model_alt = "Default Alt Text",
          model_poster = "",
          scale = "auto",
          placement = "floor",
          poster_color = "#FFFFFF",
          xr_environment = "false",
          camera_controls = "true",
          auto_rotate = "true",
        } = response.data;

        // Crear el HTML del model-viewer dinámicamente usando los valores de la respuesta
        const modelViewerHTML = `
				<model-viewer
					src="${model_3d_file}"
					alt="${model_alt}"
					ar
					poster="${model_poster}"
					camera-controls="${camera_controls}"
					auto-rotate="${auto_rotate}"
					style="width: 100%; height: 400px; background-color: ${poster_color};">
				</model-viewer>
			`;

        // Insertar el model-viewer dentro del div con id 'ar-model-viewer-for-woocommerce-preview-content'
        $("#ar-model-viewer-for-woocommerce-preview-content").html(
          modelViewerHTML
        );
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX error:", textStatus, errorThrown);
      },
    });*/

    if ($("#ar_model_viewer_for_woocommerce_ar2").is(":checked")) {
      //alert("Radio Button Is checked!");
      $(".cmb2-id-ar-model-viewer-for-woocommerce-ar-modes").hide();
      $(".cmb2-id-ar-model-viewer-for-woocommerce-ar-scale").hide();
      $(".cmb2-id-ar-model-viewer-for-woocommerce-ar-placement").hide();
      $(".cmb2-id-ar-model-viewer-for-woocommerce-xr-environment").hide();
      $("#cmb2-id-ar-model-viewer-for-woocommerce-ar-settings").hide();
      $(".cmb2-id-ar-model-viewer-for-woocommerce-ar-button").hide();
    }
    if ($("#ar_model_viewer_for_woocommerce_ar_button2").is(":checked")) {
      //alert("Radio Button Is checked!");
      $(".cmb2-id-ar-model-viewer-for-woocommerce-ar-button-text").hide();
      $(
        ".cmb2-id-ar-model-viewer-for-woocommerce-ar-button-background-color"
      ).hide();
      $(".cmb2-id-ar-model-viewer-for-woocommerce-ar-button-text-color").hide();
    }
  });
})(jQuery);
