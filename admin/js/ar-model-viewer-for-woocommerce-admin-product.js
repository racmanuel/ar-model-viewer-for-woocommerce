import "@google/model-viewer";
import { driver } from "driver.js";
import "driver.js/dist/driver.css";
import alertify from "alertifyjs";
import "alertifyjs/build/css/alertify.css";
import "alertifyjs/build/css/themes/default.css";
import { TabulatorFull as Tabulator } from "tabulator-tables";

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
    // Destructure the 'wp.i18n' object to extract the '__' function.
    // The '__' function is typically used for internationalization (i18n) to translate strings in WordPress.
    const { __ } = wp.i18n;

    // Handle the click on the tutorial button
    $("#ar_model_viewer_for_woocommerce_product_tutorial").click(function (e) {
      // Prevent the default action of the button click (such as following a link)
      e.preventDefault();

      // Configure the steps of the tutorial using driver.js
      const driverObj = driver({
        // Show a progress bar during the tutorial to guide the user
        showProgress: true,
        // Disable the option to close the tutorial until all steps are completed
        allowClose: false,
        // Define the steps of the tutorial
        steps: [
          {
            // Step 1: Target the element for the 3D object file upload field
            element: "#ar_model_viewer_for_woocommerce_file_object",
            // Add a popover with the title and description for this step
            popover: {
              title: __(
                "Upload or Add the 3D Object File",
                "ar-model-viewer-for-woocommerce"
              ),
              description: __(
                'In the "3D Object File" field, enter the URL of the .glb or .gltf file that contains the 3D model.',
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 2: Target the "Add URL or File" button for uploading the 3D file
            element:
              "input.cmb2-upload-button.button-secondary[value='Add URL or File']",
            // Add a popover to explain how to upload the file directly from the user's computer
            popover: {
              title: "",
              description: __(
                "Alternatively, you can upload the file directly from your computer by using the Add URL or File button.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 3: Target the field to add a loading poster (an image displayed while the 3D model is loading)
            element: "#ar_model_viewer_for_woocommerce_file_poster",
            // Add a popover explaining how to add a poster image for better user experience during loading
            popover: {
              title: __(
                "Add a Loading Poster",
                "ar-model-viewer-for-woocommerce"
              ),
              description: __(
                'In the "Poster" field, you can enter a URL that points to an image that will be displayed while the 3D model is loading.',
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 4: Target the "Add Image" button for uploading the loading poster image
            element:
              "input.cmb2-upload-button.button-secondary[value='Add Image']",
            // Add a popover explaining how to upload an image from the media library or device
            popover: {
              title: "",
              description: __(
                "To upload an image, click the Add Image button and select an image from your media library or upload it from your device.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 5: Target the field for adding alternative text (alt text) for accessibility purposes
            element: "#ar_model_viewer_for_woocommerce_file_alt",
            // Add a popover explaining the importance of alt text for accessibility and SEO
            popover: {
              title: __(
                "Configure Alternative Text (alt)",
                "ar-model-viewer-for-woocommerce"
              ),
              description: __(
                "In the 'alt' field, enter a descriptive text that will be used as an alternative in case the image doesn't load correctly. This also improves accessibility for visually impaired users.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 6: Provide additional information about using the product name as the default alt text
            element: "#ar_model_viewer_for_woocommerce_file_alt",
            popover: {
              title: "",
              description: __(
                "If this field is left empty, the product name will be used as the alternative text by default.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 7: Target the section that explains how to embed the 3D viewer in a WordPress theme template file
            element: "#ar-model-viewer-for-woocommerce-template-file",
            // Add a popover explaining how to use the viewer in a template file with PHP code
            popover: {
              title: __(
                "Embed in a Template File",
                "ar-model-viewer-for-woocommerce"
              ),
              description: __(
                "To integrate the 3D object viewer into a WordPress theme template file, copy the PHP code provided in the Use in Template File section:",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 8: Target the button for copying the PHP code for embedding the 3D viewer in the template file
            element: "#button-copy-shortcode-template-file",
            // Add a popover explaining how to paste the copied PHP code into the theme template
            popover: {
              title: "",
              description: __(
                "Paste this code into your theme’s template file where you want the 3D viewer to appear.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 9: Target the section that explains how to use the 3D viewer via shortcode in pages or posts
            element: "#ar-model-viewer-for-woocommerce-shortcode",
            // Add a popover explaining how to use the shortcode in WordPress content like posts or widgets
            popover: {
              title: __(
                "Using the Shortcode",
                "ar-model-viewer-for-woocommerce"
              ),
              description: __(
                "If you prefer to use the viewer in a page, post, or widget, copy the shortcode from the Use in Shortcode section:",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 10: Target the button for copying the shortcode for use in posts, pages, or widgets
            element: "#button-copy-shortcode",
            // Add a popover explaining where to paste the shortcode in the WordPress editor
            popover: {
              title: "",
              description: __(
                "Paste this shortcode into the WordPress editor wherever you want the 3D model to display.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
          {
            // Step 11: Save the product
            element: "#publish",
            // Add a popover explaining how to use the shortcode in WordPress content like posts or widgets
            popover: {
              title: __("Save", "ar-model-viewer-for-woocommerce"),
              description: __(
                "Once you have added the fields, don't forget to save the changes with the publish button.",
                "ar-model-viewer-for-woocommerce"
              ),
            },
          },
        ],
      });
      // Start the tutorial by calling the driver.js 'drive' method
      driverObj.drive();
    });

    $("#ar_model_viewer_for_woocommerce_product_preview").click(function (e) {
      e.preventDefault();
      // Recuperar el product_id desde el data-product-id del botón
      var product_id = $(this).data("product-id");

      // Verificar si el product_id está definido
      if (!product_id) {
        alertify.error("Product ID is missing.");
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
          action: "ar_model_viewer_for_woocommerce_get_model_and_settings",
          product_id: product_id, // Pasar el ID del producto en la solicitud AJAX
        },
        dataType: "json",

        // Mostrar mensaje de carga antes de la solicitud
        beforeSend: function () {
          loadingMessage = alertify.success("Cargando el modelo 3D...", 0); // Muestra el mensaje hasta que se complete la carga
        },

        success: function (response) {
          // Ocultar el mensaje de carga
          if (loadingMessage) {
            loadingMessage.dismiss();
          }

          if (response.success) {
            var data = response.data;

            // Usar el nombre del producto en el título del modal
            var productName = data.product_name || "Modelo 3D";

            alertify
              .alert(productName, htmlContent)
              .set({
                transition: "zoom",
                movable: true,
                maximizable: true,
              }) // Puedes ajustar las opciones
              .setHeader(productName); // Usar el nombre del producto en el header

            // Verificar que los datos existen antes de asignarlos al model-viewer
            if (data) {
              $("#model-viewer").attr("src", data.model_3d_file || "");
              $("#model-viewer").attr("alt", data.model_alt || "");
              $("#model-viewer").attr("poster", data.model_poster || "");
              $("#model-viewer").attr("reveal", data.reveal || "auto");
              $("#model-viewer").attr("loading", data.loading || "auto");
              $("#model-viewer").attr(
                "ar-modes",
                (data.ar_modes || []).join(" ")
              );
              $("#model-viewer").css(
                "background-color",
                data.poster_color || "rgba(255,255,255,0)"
              );

              // Asegúrate de que el valor de scale sea correcto
              var scale = data.scale || "auto"; // Valor por defecto si no está definido
              $("#model-viewer").attr("ar-scale", scale); // Usar "auto" o "fixed" según corresponda
            }
          } else {
            alertify.error("Error: " + response.data);
          }
        },

        // Ocultar el mensaje de carga en caso de error
        error: function (xhr, status, error) {
          if (loadingMessage) {
            loadingMessage.dismiss();
          }
          alertify.error("AJAX Error: " + error);
        },
      });
    });

    // Function to copy PHP code for the template file
    $("#button-copy-shortcode-template-file").click(function (e) {
      // Prevent the default button behavior
      e.preventDefault();

      // Get the PHP code text from the element with ID 'php-include-text'
      var phpCode = $("#php-include-text").text().trim();

      // Create a temporary textarea element to copy the text
      var tempElement = $("<textarea>").val(phpCode).appendTo("body").select();

      // Execute the browser's copy command to copy the content
      document.execCommand("copy");

      // Remove the temporary textarea element after copying
      tempElement.remove();

      // Show a success message using Alertify and translate it with wp.i18n
      alertify.success(
        __("PHP code copied to clipboard!", "ar-model-viewer-for-woocommerce")
      );
    });

    // Function to copy shortcode
    $("#button-copy-shortcode").click(function (e) {
      // Prevent the default button behavior
      e.preventDefault();

      // Get the shortcode text from the element with ID 'shortcode-text'
      var shortcode = $("#shortcode-text").text().trim();

      // Create a temporary textarea element to copy the text
      var tempElement = $("<textarea>")
        .val(shortcode)
        .appendTo("body")
        .select();

      // Execute the browser's copy command to copy the content
      document.execCommand("copy");

      // Remove the temporary textarea element after copying
      tempElement.remove();

      // Show a success message using Alertify and translate it with wp.i18n
      alertify.success(
        __("Shortcode copied to clipboard!", "ar-model-viewer-for-woocommerce")
      );
    });

    // Function to Generate 3D Models with AI
    $("#generate-text-to-3d").on("click", function (e) {
      e.preventDefault(); // Evitar el comportamiento predeterminado del botón

      // Recuperar el product_id desde el data-product-id del botón
      var product_id = $(this).data("product-id");

      console.log("ID del Producto: " + product_id);

      // Verificar si el product_id está definido
      if (!product_id) {
        alertify.error("Product ID is missing.");
        return;
      }

      // Recoger los valores del formulario
      var prompt = $("#prompt").val();
      var negative_prompt = $("#negative_prompt").val();
      var art_style = $("#art_style").val();
      var topology = $("#topology").val();
      var target_polycount = $("#target_polycount").val();

      // Validar que los campos importantes no estén vacíos (opcional)
      if (!prompt) {
        alertify.error("Please insert a prompt.");
        return;
      }

      // Mostrar los valores en consola para debug
      console.log({
        prompt: prompt,
        negative_prompt: negative_prompt,
        art_style: art_style,
        topology: topology,
        target_polycount: target_polycount,
      });

      // Enviar datos vía AJAX
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url, // Asegúrate de que 'ajax_object' esté definido en tu plantilla
        data: {
          action: "ar_model_viewer_for_woocommerce_createTextTo3DTask", // Acción de tu PHP
          prompt: prompt,
          negative_prompt: negative_prompt,
          art_style: art_style,
          topology: topology,
          target_polycount: target_polycount,
        },
        dataType: "json",
        beforeSend: function () {
          // Mostrar mensaje de carga antes de la solicitud
          alertify.success("Generating 3D model preview...", 0); // 0 indica que el mensaje permanecerá visible hasta que se quite manualmente
        },
        success: function (response) {
          // Ocultar el mensaje de carga
          alertify.dismissAll();

          // Verificar si la respuesta fue exitosa
          if (response.success) {
            var model_url = response.data;
            console.log("Model URL: " + model_url);
            alertify.success("3D model generated successfully.");
          } else {
            alertify.error("There was an error generating the model.");
          }
        },
        error: function () {
          alertify.dismissAll(); // Ocultar mensaje de carga en caso de error
          alertify.error("Communication error with the server.");
        },
      });
    });

    //meshy_ai_get_tasks();

    function meshy_ai_get_tasks() {
      console.log("Si se ejecuta el codigo");
      // Enviar datos vía AJAX
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url, // Asegúrate de que 'ajax_object' esté definido en tu plantilla
        data: {
          action: "ar_model_viewer_for_woocommerce_get_tasks", // Acción de tu PHP
        },
        dataType: "json",
        beforeSend: function () {
          // Mostrar mensaje de carga antes de la solicitud
          alertify.success("Loading AI 3D Tasks...", 0); // 0 indica que el mensaje permanecerá visible hasta que se quite manualmente
        },
        success: function (response) {
          // Ocultar el mensaje de carga
          alertify.dismissAll();

          // Verificar si la respuesta fue exitosa
          if (response.success) {
            console.log(response.data);
            // Inicialización de Tabulator
            var table = new Tabulator("#table-task-3d", {
              data: response.data,
              columns: [
                { formatter: "rownum", hozAlign: "center", width: 40 ,  frozen:true},
                {
                  title: "Thumbnail",
                  field: "thumbnail_url",
                  formatter: "image",
                  formatterParams: {
                    height: "50px", // Altura fija
                    width: "50px", // Ancho fijo
                    normalizeHeight: true, // Normalizar la altura
                  },
                  cellClick: function (e, cell) {
                    // Obtén la URL de la imagen del campo
                    var imageUrl = cell.getValue();
                    
                    // Usa alertify para mostrar una vista previa de la imagen
                    alertify.alert('<img src="' + imageUrl + '" style="width: 100%; height: auto;">');
                  },
                },
                { title: "Mode", field: "mode" },
                { title: "Prompt", field: "prompt", width: 300 },
                { title: "Negative Prompt", field: "negative_prompt" },
                { title: "Status", field: "status" },
                { title: "ID", field: "id", width: 200 },
                {
                  title: "Progress",
                  field: "progress",
                  formatter: "progress",
                  formatterParams: {
                    min: 0,
                    max: 100,
                    color: ["red", "orange", "green"],
                    legendColor: "#000000",
                    legendAlign: "center",
                  },
                },
                {
                  title: "GLB Model",
                  field: "model_urls.glb",
                  formatter: "link",
                  formatterParams: { target: "_blank", label: "Download" },
                  width: 200,
                },
                {
                  title: "Video",
                  field: "video_url",
                  formatter: "link",
                  width: 150,
                  formatterParams: { target: "_blank", label: "Watch Video" },
                },
              ],
            });
            alertify.success("Tasks loaded correctly.");
          } else {
            alertify.error("There was an error generating the model.");
          }
        },
        error: function () {
          alertify.dismissAll(); // Ocultar mensaje de carga en caso de error
          alertify.error("Communication error with the server.");
        },
      });
    }
  });
})(jQuery);
