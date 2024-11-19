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

    // Configura el tutorial para el producto 3D de WooCommerce
    function setupProduct3DTutorial() {
      $("#ar_model_viewer_for_woocommerce_product_tutorial").click((e) => {
        e.preventDefault();
        startProduct3DTutorial();
      });
    }

    // Configura el tutorial para el texto a 3D
    function setupTextTo3DTutorial() {
      $("#tutorial-text-to-3d").click((e) => {
        e.preventDefault();
        startTextTo3DTutorial();
      });
    }

    // Obtiene los pasos del tutorial para el producto 3D
    function getProduct3DTutorialSteps() {
      return [
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
            title: __("Using the Shortcode", "ar-model-viewer-for-woocommerce"),
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
      ];
    }

    // Obtiene los pasos del tutorial para el texto a 3D
    function getTextTo3DTutorialSteps() {
      return [
        {
          // Step 1: Target the element for the 3D object description prompt
          element: "#prompt",
          // Add a popover with the title and description for this step
          popover: {
            title: wp.i18n.__(
              "Describe the 3D Model you want",
              "ar-model-viewer-for-woocommerce"
            ),
            description: wp.i18n.__(
              "In this field, provide a detailed description of the 3D model you want to create. Include aspects such as shape, color, size, style, and other key details. Specific descriptions yield better results.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 2: Target the element for specifying what should not be included in the model
          element: "#negative_prompt",
          // Add a popover to explain the purpose of this field
          popover: {
            title: wp.i18n.__(
              "Describe what you don't want in the 3D Model",
              "ar-model-viewer-for-woocommerce"
            ),
            description: wp.i18n.__(
              "Use this field to specify elements you wish to avoid in the model. Be as detailed as possible to help exclude undesired features.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 3: Target the field for setting the art style of the 3D model
          element: "#art_style",
          // Add a popover explaining the art style choice
          popover: {
            title: wp.i18n.__("Art Style", "ar-model-viewer-for-woocommerce"),
            description: wp.i18n.__(
              "Choose an artistic style for the model. By default, this is set to 'realistic', but you can specify styles such as 'cartoon' or 'abstract' as well.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 4: Target the field to specify the topology of the model
          element: "#topology",
          // Add a popover explaining the topology setting
          popover: {
            title: wp.i18n.__(
              "Specify the topology",
              "ar-model-viewer-for-woocommerce"
            ),
            description: wp.i18n.__(
              "Define the model's topology, such as 'triangle' or 'quad'. The default is set to 'triangle'.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 5: Target the field for the target polycount
          element: "#target_polycount",
          // Add a popover explaining the polycount
          popover: {
            title: wp.i18n.__(
              "Specify the target number of polygons",
              "ar-model-viewer-for-woocommerce"
            ),
            description: wp.i18n.__(
              "Set the target polygon count for the model. The actual count may vary based on the model's complexity.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 6: Guide the user to generate the 3D model
          element: "#generate-text-to-3d",
          popover: {
            title: wp.i18n.__(
              "Generate 3D Model",
              "ar-model-viewer-for-woocommerce"
            ),
            description: wp.i18n.__(
              "Once all fields are filled, click this button to generate a preview of the 3D model.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 7: Guide the user to generate the 3D model
          element: "#ar_model_viewer_for_woocommerce_metabox_tasks",
          popover: {
            title: wp.i18n.__(
              "View you 3D Task",
              "ar-model-viewer-for-woocommerce"
            ),
            description: wp.i18n.__(
              "Once you have clicked on the button to generate a preview of the 3D model, in this table the progress of your model generation will appear; once a preview without textures is generated, you can click on the Refine button and it will create a new task that will generate your model with new textures.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
      ];
    }

    // Inicia el tutorial del producto 3D
    function startProduct3DTutorial() {
      const driverObj = driver({
        showProgress: true,
        allowClose: false,
        steps: getProduct3DTutorialSteps(),
      });
      driverObj.drive();
    }

    // Inicia el tutorial de texto a 3D
    function startTextTo3DTutorial() {
      const driverObj = driver({
        showProgress: true,
        allowClose: false,
        steps: getTextTo3DTutorialSteps(),
      });
      driverObj.drive();
    }

    // Ejecuta la configuración de ambos tutoriales
    setupProduct3DTutorial();
    setupTextTo3DTutorial();

    // Función genérica para copiar contenido al portapapeles
    function copyToClipboard(elementId, successMessage) {
      // Obtener el texto desde el elemento con el ID proporcionado
      var text = $(elementId).text().trim();

      // Crear un elemento temporal de textarea para copiar el texto
      var tempElement = $("<textarea>").val(text).appendTo("body").select();

      // Ejecutar el comando de copiar en el navegador
      document.execCommand("copy");

      // Eliminar el elemento temporal después de copiar
      tempElement.remove();

      // Mostrar mensaje de éxito utilizando Alertify y traducirlo con wp.i18n
      alertify.success(
        wp.i18n.__(successMessage, "ar-model-viewer-for-woocommerce")
      );
    }

    // Función para copiar el código PHP del archivo de plantilla
    $("#button-copy-shortcode-template-file").click(function (e) {
      e.preventDefault();
      copyToClipboard("#php-include-text", "PHP code copied to clipboard!");
    });

    // Función para copiar el shortcode
    $("#button-copy-shortcode").click(function (e) {
      e.preventDefault();
      copyToClipboard("#shortcode-text", "Shortcode copied to clipboard!");
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
          action: "ar_model_viewer_for_woocommerce_createTextTo3DTaskPreview", // Acción de tu PHP
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
            meshy_ai_get_tasks();
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

    setInterval(function () {
      meshy_ai_get_tasks();
    }, 60000);

    meshy_ai_get_tasks();

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
          console.log("test" + response.data);
          // Verificar si la respuesta fue exitosa
          if (response.success) {
            if (response.data && response.data.length > 0) {
              // Inicialización de Tabulator
              var table = new Tabulator("#table-task-3d", {
                data: response.data,
                layout: "fitColumns",
                columns: [
                  {
                    formatter: "rownum",
                    frozen: true,
                  },
                  {
                    title: "Preview Image",
                    field: "thumbnail_url",
                    formatter: "image",
                    formatterParams: {
                      height: "32px", // Altura fija
                      width: "32px", // Ancho fijo
                      normalizeHeight: true, // Normalizar la altura
                    },
                    cellClick: function (e, cell) {
                      // Obtén la URL de la imagen del campo
                      var imageUrl = cell.getValue();

                      // Usa alertify para mostrar una vista previa de la imagen
                      alertify
                        .alert(
                          '<img src="' +
                            imageUrl +
                            '" style="width: 100%; height: auto;">'
                        )
                        .set({
                          transition: "zoom",
                        }) // Puedes ajustar las opciones
                        .setHeader("Preview");
                    },
                  },
                  {
                    title: "Preview Video",
                    field: "video_url",
                    formatter: function (cell) {
                      var video_url = cell.getValue();

                      // Si el URL está presente, crea el botón
                      if (video_url) {
                        return (
                          "<button class='cmb2-upload-button button-secondary video-preview' onclick='window.open(\"" +
                          video_url +
                          '", "_blank")\'>View</button>'
                        );
                      } else {
                        return "<span style='color: gray;'>Not Available</span>"; // Muestra un mensaje si no hay URL
                      }
                    },
                    hozAlign: "center", // Alineación horizontal al centro
                    width: 150,
                  },
                  {
                    title: "Mode",
                    field: "mode",
                    formatter: function (cell) {
                      // Obtiene el valor de la celda
                      var value = cell.getValue();

                      // Define íconos en función del valor de la celda
                      switch (value) {
                        case "preview":
                          return (
                            '<img src="' +
                            ajax_object.mode_preview_icon +
                            '" style="width:32px; height: auto;">'
                          ); // Ícono de vista previa
                        case "refine":
                          return (
                            '<img src="' +
                            ajax_object.mode_refine_icon +
                            '" style="width:32px; height: auto;">'
                          ); // Ícono de edición
                        default:
                          return "<i class='fas fa-question-circle' style='color: gray;' title='Unknown Mode'></i>"; // Ícono para modo desconocido
                      }
                    },
                    hozAlign: "center", // Alineación horizontal al centro
                    width: 100,
                  },
                  { title: "Prompt", field: "prompt", width: 300 },
                  { title: "Negative Prompt", field: "negative_prompt" },
                  {
                    title: "Status",
                    field: "status",
                    formatter: function (cell) {
                      // Obtiene el valor de la celda
                      var value = cell.getValue();

                      // Define íconos en función del valor de la celda
                      switch (value) {
                        case "PENDING":
                          return (
                            '<img src="' +
                            ajax_object.mode_preview_icon +
                            '" style="width: 32px; height: auto;">'
                          ); // Ícono de vista previa
                        case "IN_PROGRESS":
                          return (
                            '<img src="' +
                            ajax_object.mode_refine_icon +
                            '" style="width: 32px; height: auto;">'
                          ); // Ícono de edición
                        case "SUCCEEDED":
                          return (
                            '<img src="' +
                            ajax_object.status_succeeded_icon +
                            '" style="width: 32px; height: auto;">'
                          ); // Ícono de edición
                        case "FAILED":
                          return (
                            '<img src="' +
                            ajax_object.mode_refine_icon +
                            '" style="width: 32px; height: auto;">'
                          ); // Ícono de edición
                        case "EXPIRED":
                          return (
                            '<img src="' +
                            ajax_object.mode_refine_icon +
                            '" style="width: 32px; height: auto;">'
                          ); // Ícono de edición
                        default:
                          return "<i class='fas fa-question-circle' style='color: gray;' title='Unknown Mode'></i>"; // Ícono para modo desconocido
                      }
                    },
                    hozAlign: "center", // Alineación horizontal al centro
                    width: 100,
                  },
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
                    title: "Refine",
                    field: "id",
                    formatter: function (cell) {
                      var rowData = cell.getRow().getData();
                      var mode = rowData.mode; // Obtiene el valor de la columna `mode`
                      var id = rowData.id;

                      // Verifica si el modo es "preview"
                      if (mode === "preview") {
                        return (
                          "<button class='cmb2-upload-button button-secondary refine-btn' data-id='" +
                          id +
                          "'>Refine</button>"
                        );
                      } else {
                        return "<span style='color: gray;'>Not Available</span>"; // Si no está en modo "preview", muestra "Not Available"
                      }
                    },
                    hozAlign: "center",
                    width: 150,
                  },
                  {
                    title: "3D Model File",
                    field: "model_urls.glb",
                    formatter: function (cell) {
                      var url = cell.getValue();
                      var rowData = cell.getRow().getData();
                      var id = rowData.id;

                      // Si el URL está presente, crea el botón
                      if (url) {
                        return (
                          "<button class='cmb2-upload-button button-secondary download-btn' data-id-download='" +
                          id +
                          "'>Download</button>"
                        );
                      } else {
                        return "<span style='color: gray;'>Not Available</span>"; // Muestra un mensaje si no hay URL
                      }
                    },
                    hozAlign: "center", // Alineación horizontal al centro
                    width: 150,
                  },
                ],
              });
              alertify.success("Tasks loaded correctly.");
            } else {
              alertify.error(response.message || "No existen registros.");
            }
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

    // Evento delegado para capturar el clic en los botones generados dinámicamente
    $(document).on("click", ".refine-btn", function (e) {
      e.preventDefault(); // Evita el comportamiento predeterminado del botón, si es necesario

      var dataId = $(this).attr("data-id"); // Obtiene el valor del atributo `data-id`
      console.log("Data ID:", dataId); // Muestra el valor de `data-id` en la consola

      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        data: {
          action: "ar_model_viewer_for_woocommerce_createTextTo3DTaskRefine",
          mode: "refine",
          preview_task_id: dataId,
        },
        dataType: "json",
        success: function (response) {
          console.log(response);
          meshy_ai_get_tasks();
        },
      });
    });

    // Evento delegado para capturar el clic en los botones generados dinámicamente
    $(document).on("click", ".download-btn", function (e) {
      e.preventDefault(); // Evita el comportamiento predeterminado del botón, si es necesario

      var dataId = $(this).attr("data-id-download"); // Obtiene el valor del atributo `data-id`
      console.log("Data ID Download:", dataId); // Muestra el valor de `data-id` en la consola

      // Recuperar el product_id desde el data-product-id del botón
      var product_id = $("#generate-text-to-3d").data("product-id");

      console.log("ID del Producto:" + product_id);

      // Mostrar notificación de que la descarga está en curso
      alertify.message("Descargando modelo, por favor espera...");

      // Inicia la solicitud AJAX
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        data: {
          action: "ar_model_viewer_for_woocommerce_get_task_and_download",
          task_id: dataId,
          product_id: product_id,
        },
        dataType: "json",
        beforeSend: function () {
          // Muestra una notificación de carga
          alertify.notify("Procesando la solicitud...", "success", 0);
        },
        success: function (response) {
          // Verifica si la respuesta fue exitosa
          if (response.success) {
            console.log(response); // Muestra la respuesta en la consola
            alertify.dismissAll(); // Cierra las notificaciones previas

            // Mostrar notificación de éxito
            alertify.notify(
              "Modelo descargado con éxito. Refrescando página...",
              "success",
              5
            ); // Visible por 5 segundos

            // Refrescar la página después de un breve retraso
            setTimeout(function () {
              location.reload();
            }, 5000); // 5000ms = 5 segundos
          } else {
            alertify.dismissAll(); // Cierra las notificaciones previas
            alertify.error(
              "Ocurrió un error al descargar el modelo. Intenta nuevamente."
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("Error AJAX:", error); // Muestra el error en la consola
          alertify.dismissAll(); // Cierra las notificaciones previas
          alertify.error(
            "Error en la solicitud AJAX. Por favor, revisa la consola para más detalles."
          );
        },
      });
    });
  });
})(jQuery);
