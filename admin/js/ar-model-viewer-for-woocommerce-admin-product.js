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

    // Sets up the event listener for the WooCommerce 3D product tutorial
    function setupProduct3DTutorial() {
      // Attach a click event listener to the tutorial button
      $("#ar_model_viewer_for_woocommerce_product_tutorial").click((e) => {
        e.preventDefault(); // Prevents the default action of the button (e.g., navigation)

        // Initiates the tutorial when the button is clicked
        startProduct3DTutorial();
      });
    }

    // Sets up the event listener for the Text-to-3D tutorial
    function setupTextTo3DTutorial() {
      // Attach a click event listener to the tutorial button
      $("#tutorial-text-to-3d").click((e) => {
        e.preventDefault(); // Prevents the default action of the button (e.g., navigation)

        // Initiates the Text-to-3D tutorial when the button is clicked
        startTextTo3DTutorial();
      });
    }

    // Returns the steps for the 3D product tutorial in WooCommerce
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
          // Add a popover explaining the final step to save changes
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

    // Returns the steps for the Text-to-3D tutorial in WooCommerce
    function getTextTo3DTutorialSteps() {
      return [
        {
          // Step 1: Target the element for the 3D object description prompt
          element: "#prompt",
          // Add a popover with the title and description for this step
          popover: {
            title: __(
              "Describe the 3D Model you want",
              "ar-model-viewer-for-woocommerce"
            ),
            description: __(
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
            title: __(
              "Describe what you don't want in the 3D Model",
              "ar-model-viewer-for-woocommerce"
            ),
            description: __(
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
            title: __("Art Style", "ar-model-viewer-for-woocommerce"),
            description: __(
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
            title: __(
              "Specify the topology",
              "ar-model-viewer-for-woocommerce"
            ),
            description: __(
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
            title: __(
              "Specify the target number of polygons",
              "ar-model-viewer-for-woocommerce"
            ),
            description: __(
              "Set the target polygon count for the model. The actual count may vary based on the model's complexity.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 6: Guide the user to generate the 3D model
          element: "#generate-text-to-3d",
          popover: {
            title: __("Generate 3D Model", "ar-model-viewer-for-woocommerce"),
            description: __(
              "Once all fields are filled, click this button to generate a preview of the 3D model.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          // Step 7: Explain the task progress and refinement
          element: "#ar_model_viewer_for_woocommerce_metabox_tasks",
          // Add a popover describing how to monitor task progress and refine the model
          popover: {
            title: __("View your 3D Task", "ar-model-viewer-for-woocommerce"),
            description: __(
              "After clicking the button to generate a preview of the 3D model, the progress of your model generation will appear in this table. Once a preview without textures is generated, you can click the Refine button to create a new task that generates your model with updated textures.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
      ];
    }

    // Starts the 3D product tutorial
    function startProduct3DTutorial() {
      // Create a Driver.js instance with configuration options
      const driverObj = driver({
        showProgress: true, // Displays a progress bar to indicate the current step
        allowClose: false, // Disables the ability to close the tutorial manually
        steps: getProduct3DTutorialSteps(), // Fetches the steps for the 3D product tutorial
      });

      // Initiates the tutorial
      driverObj.drive();
    }

    // Starts the Text-to-3D tutorial
    function startTextTo3DTutorial() {
      // Create a Driver.js instance with configuration options
      const driverObj = driver({
        showProgress: true, // Displays a progress bar to indicate the current step
        allowClose: false, // Disables the ability to close the tutorial manually
        steps: getTextTo3DTutorialSteps(), // Fetches the steps for the Text-to-3D tutorial
      });

      // Initiates the tutorial
      driverObj.drive();
    }

    // Executes the setup for both tutorials
    setupProduct3DTutorial();
    setupTextTo3DTutorial();

    // Generic function to copy content to the clipboard
    function copyToClipboard(elementId, successMessage) {
      // Retrieve the text from the specified element by its ID
      var text = $(elementId).text().trim();

      // Create a temporary textarea element to hold the text for copying
      var tempElement = $("<textarea>").val(text).appendTo("body").select();

      // Execute the browser's copy command
      document.execCommand("copy");

      // Remove the temporary textarea element after copying
      tempElement.remove();

      // Display a success message using Alertify and translate it using wp.i18n
      alertify.success(
        wp.i18n.__(successMessage, "ar-model-viewer-for-woocommerce")
      );
    }

    // Function to copy the PHP code from the template file
    $("#button-copy-shortcode-template-file").click(function (e) {
      e.preventDefault(); // Prevent the default behavior of the button

      // Use the generic function to copy the PHP code
      copyToClipboard(
        "#php-include-text",
        __("PHP code copied to clipboard!", "ar-model-viewer-for-woocommerce")
      );
    });

    // Function to copy the shortcode
    $("#button-copy-shortcode").click(function (e) {
      e.preventDefault(); // Prevent the default behavior of the button

      // Use the generic function to copy the shortcode
      copyToClipboard(
        "#shortcode-text",
        __("Shortcode copied to clipboard!", "ar-model-viewer-for-woocommerce")
      );
    });

    // Handles the click event for the 3D product preview button
    $("#ar_model_viewer_for_woocommerce_product_preview").click(function (e) {
      e.preventDefault(); // Prevents the default button behavior

      // Retrieve the product_id from the button's data attribute
      var product_id = $(this).data("product-id");

      // Check if the product_id is defined
      if (!product_id) {
        alertify.error("Product ID is missing.");
        return;
      }

      // Custom HTML content for the 3D viewer with centered styling and camera controls
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
              style="width: 100%; max-width: 600px; height: 400px;">
          </model-viewer>
      </div>`;

      // Variable to store the loading message
      var loadingMessage;

      // AJAX request to fetch the 3D model and settings
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        data: {
          action: "ar_model_viewer_for_woocommerce_get_model_and_settings",
          product_id: product_id, // Pass the product ID in the AJAX request
        },
        dataType: "json",

        // Show a loading message before the request
        beforeSend: function () {
          loadingMessage = alertify.success("Loading 3D model...", 0); // Message remains visible until dismissed
        },

        success: function (response) {
          // Dismiss the loading message
          if (loadingMessage) {
            loadingMessage.dismiss();
          }

          if (response.success) {
            var data = response.data;

            // Use the product name as the modal title
            var productName = data.product_name || "3D Model";

            // Display the modal with the 3D viewer
            alertify
              .alert(productName, htmlContent)
              .set({
                transition: "zoom", // Smooth transition effect
                movable: true, // Allow the modal to be movable
                maximizable: true, // Allow the modal to be maximized
              })
              .setHeader(productName); // Set the modal header with the product name

            // Assign data to the <model-viewer> attributes if available
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

              // Ensure the scale value is set correctly
              var scale = data.scale || "auto"; // Default value if not defined
              $("#model-viewer").attr("ar-scale", scale); // Use "auto" or "fixed" as appropriate
            }
          } else {
            alertify.error("Error: " + response.data);
          }
        },

        // Dismiss the loading message and handle AJAX errors
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
      e.preventDefault(); // Prevent the default button behavior

      // Retrieve the product_id from the button's data-product-id attribute
      var product_id = $(this).data("product-id");

      // Check if the product_id is defined
      if (!product_id) {
        alertify.error("Product ID is missing.");
        return;
      }

      // Collect form values
      var prompt = $("#prompt").val();
      var negative_prompt = $("#negative_prompt").val();
      var art_style = $("#art_style").val();
      var topology = $("#topology").val();
      var target_polycount = $("#target_polycount").val();

      // Validate that essential fields are not empty
      if (!prompt) {
        alertify.error("Please insert a prompt.");
        return;
      }

      // Send data via AJAX
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url, // Ensure 'ajax_object' is defined in your template
        data: {
          action: "ar_model_viewer_for_woocommerce_createTextTo3DTaskPreview", // PHP action to handle the request
          prompt: prompt,
          negative_prompt: negative_prompt,
          art_style: art_style,
          topology: topology,
          target_polycount: target_polycount,
        },
        dataType: "json",
        beforeSend: function () {
          // Display loading message before the request
          alertify.success("Generating 3D model preview...", 0); // 0 indicates the message remains visible until manually dismissed
        },
        success: function (response) {
          // Dismiss all loading messages
          alertify.dismissAll();

          // Check if the response was successful
          if (response.success) {
            // Call function to fetch tasks (if necessary for updates)
            meshy_ai_get_tasks();

            // Notify success
            alertify.success("3D model generated successfully.");
          } else {
            // Handle failure with an error message
            alertify.error("There was an error generating the model.");
          }
        },
        error: function () {
          // Dismiss all loading messages in case of an error
          alertify.dismissAll();
          alertify.error("Communication error with the server.");
        },
      });
    });

    // Store the interval ID
    var intervalId = setInterval(function () {
      meshy_ai_get_tasks();
    }, 60000);

    // Perform an initial fetch
    meshy_ai_get_tasks();

    // Stop the interval when a condition is met
    function stopTaskFetching() {
      clearInterval(intervalId);
    }

    // Function to fetch and display AI 3D tasks
    function meshy_ai_get_tasks() {
      // Send AJAX request
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url, // Ensure 'ajax_object' is defined in your template
        data: {
          action: "ar_model_viewer_for_woocommerce_get_tasks", // PHP action to handle the request
        },
        dataType: "json",
        beforeSend: function () {
          // Display a loading message before the request
          alertify.success("Loading AI 3D Tasks...", 0); // Message remains visible until manually dismissed
        },
        success: function (response) {
          // Dismiss the loading message
          alertify.dismissAll();

          // Check if the response was successful
          if (response.success) {
            if (response.data && response.data.length > 0) {
              // Initialize Tabulator table
              var table = new Tabulator("#table-task-3d", {
                data: response.data, // Load data into the table
                layout: "fitColumns", // Fit columns to the table width
                columns: [
                  {
                    formatter: "rownum", // Display row numbers
                    frozen: true, // Keep the column fixed when scrolling
                  },
                  {
                    title: "Preview Image",
                    field: "thumbnail_url",
                    formatter: "image", // Display image in the cell
                    formatterParams: {
                      height: "32px",
                      width: "32px",
                      normalizeHeight: true,
                    },
                    cellClick: function (e, cell) {
                      var imageUrl = cell.getValue();
                      // Show image preview in a modal
                      alertify
                        .alert(
                          '<img src="' +
                            imageUrl +
                            '" style="width: 100%; height: auto;">'
                        )
                        .set({
                          transition: "zoom",
                        })
                        .setHeader("Preview");
                    },
                  },
                  {
                    title: "Preview Video",
                    field: "video_url",
                    formatter: function (cell) {
                      var video_url = cell.getValue();
                      return video_url
                        ? "<button class='cmb2-upload-button button-secondary video-preview' onclick='window.open(\"" +
                            video_url +
                            '", "_blank")\'>View</button>'
                        : "<span style='color: gray;'>Not Available</span>";
                    },
                    hozAlign: "center",
                    width: 150,
                  },
                  {
                    title: "Mode",
                    field: "mode",
                    formatter: function (cell) {
                      var value = cell.getValue();
                      switch (value) {
                        case "preview":
                          return (
                            '<img src="' +
                            ajax_object.mode_preview_icon +
                            '" style="width:32px; height: auto;">'
                          );
                        case "refine":
                          return (
                            '<img src="' +
                            ajax_object.mode_refine_icon +
                            '" style="width:32px; height: auto;">'
                          );
                        default:
                          return "<i class='fas fa-question-circle' style='color: gray;' title='Unknown Mode'></i>";
                      }
                    },
                    hozAlign: "center",
                    width: 100,
                  },
                  { title: "Prompt", field: "prompt", width: 300 },
                  { title: "Negative Prompt", field: "negative_prompt" },
                  {
                    title: "Status",
                    field: "status",
                    formatter: function (cell) {
                      var value = cell.getValue();
                      switch (value) {
                        case "PENDING":
                        case "IN_PROGRESS":
                        case "SUCCEEDED":
                        case "FAILED":
                        case "EXPIRED":
                          return (
                            '<img src="' +
                            ajax_object.status_icon[value.toLowerCase()] +
                            '" style="width: 32px; height: auto;">'
                          );
                        default:
                          return "<i class='fas fa-question-circle' style='color: gray;' title='Unknown Status'></i>";
                      }
                    },
                    hozAlign: "center",
                    width: 100,
                  },
                  {
                    title: "Progress",
                    field: "progress",
                    formatter: "progress", // Progress bar display
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
                      var mode = rowData.mode;
                      var id = rowData.id;

                      // Display refine button if mode is 'preview'
                      return mode === "preview"
                        ? "<button class='cmb2-upload-button button-secondary refine-btn' data-id='" +
                            id +
                            "'>Refine</button>"
                        : "<span style='color: gray;'>Not Available</span>";
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

                      // Display download button if URL exists
                      return url
                        ? "<button class='cmb2-upload-button button-secondary download-btn' data-id-download='" +
                            id +
                            "'>Download</button>"
                        : "<span style='color: gray;'>Not Available</span>";
                    },
                    hozAlign: "center",
                    width: 150,
                  },
                ],
              });

              alertify.success("Tasks loaded correctly.");
            } else {
              alertify.error(response.message || "No tasks available.");
            }
          } else {
            alertify.error("There was an error loading the tasks.");
          }
        },
        error: function () {
          alertify.dismissAll(); // Dismiss loading messages in case of an error
          alertify.error("Communication error with the server.");
        },
      });
    }

    // Delegated event to capture clicks on dynamically generated refine buttons
    $(document).on("click", ".refine-btn", function (e) {
      e.preventDefault(); // Prevent default button behavior

      var dataId = $(this).attr("data-id"); // Retrieve the value of the `data-id` attribute

      // Check if `dataId` is defined
      if (!dataId) {
        alertify.error("Data ID is missing.");
        return;
      }

      // Send AJAX request to refine the task
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url, // AJAX URL defined in your WordPress template
        data: {
          action: "ar_model_viewer_for_woocommerce_createTextTo3DTaskRefine", // PHP action to handle the request
          mode: "refine", // Refine mode
          preview_task_id: dataId, // Task ID to refine
        },
        dataType: "json",
        beforeSend: function () {
          // Show a loading message
          alertify.success("Refining the 3D model...", 0); // Message stays visible until dismissed
        },
        success: function (response) {
          alertify.dismissAll(); // Dismiss the loading message

          // Check if the response indicates success
          if (response.success) {
            alertify.success("3D model refinement started successfully.");
            meshy_ai_get_tasks(); // Refresh the tasks table
          } else {
            alertify.error(
              "Error: " + (response.message || "Refinement failed.")
            );
          }
        },
        error: function () {
          alertify.dismissAll(); // Dismiss the loading message on error
          alertify.error("Communication error with the server.");
        },
      });
    });

    // Delegated event to capture clicks on dynamically generated download buttons
    $(document).on("click", ".download-btn", function (e) {
      e.preventDefault(); // Prevent default button behavior

      // Retrieve the task ID from the `data-id-download` attribute
      var dataId = $(this).attr("data-id-download");

      // Retrieve the product ID from the `data-product-id` attribute of the generate button
      var product_id = $("#generate-text-to-3d").data("product-id");

      // Show notification that the download is in progress
      alertify.message("Downloading model, please wait...");

      // Start AJAX request
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url, // Ensure 'ajax_object' is defined in your WordPress template
        data: {
          action: "ar_model_viewer_for_woocommerce_get_task_and_download", // PHP action to handle the request
          task_id: dataId,
          product_id: product_id,
        },
        dataType: "json",
        beforeSend: function () {
          // Show a loading notification
          alertify.notify("Processing request...", "success", 0); // Message remains visible until dismissed
        },
        success: function (response) {
          // Check if the response was successful
          if (response.success) {
            alertify.dismissAll(); // Dismiss previous notifications

            // Show success notification
            alertify.notify(
              "Model downloaded successfully. Refreshing page...",
              "success",
              5
            ); // Visible for 5 seconds

            // Refresh the page after a short delay
            setTimeout(function () {
              location.reload();
            }, 5000); // 5000ms = 5 seconds
          } else {
            alertify.dismissAll(); // Dismiss previous notifications
            alertify.error(
              "An error occurred while downloading the model. Please try again."
            );
          }
        },
        error: function () {
          alertify.dismissAll(); // Dismiss previous notifications
          alertify.error(
            "AJAX request error. Please check the console for more details."
          );
        },
      });
    });
  });
})(jQuery);
