/**
 * Product editor interactions for manually managed 3D models.
 */

var armvwVendor = (function (window, document) {
  "use strict";

  var files = (window.ajax_object && window.ajax_object.vendor_files) || {};
  var pending = {};

  function load(name) {
    if (pending[name]) {
      return pending[name];
    }

    pending[name] = new Promise(function (resolve) {
      var url = files[name];

      if (!url) {
        resolve(false);
        return;
      }

      var script = document.createElement("script");
      script.src = url;
      script.async = true;
      script.onload = function () {
        resolve(true);
      };
      script.onerror = function () {
        resolve(false);
      };
      document.head.appendChild(script);
    });

    return pending[name];
  }

  return {
    ensure: function (names) {
      return Promise.all((names || []).map(load));
    },
  };
})(window, document);

function armvwApplyStaticProperties() {
  var values = (window.ajax_object && window.ajax_object.static_properties) || {};

  if (Object.keys(values).length === 0 || !window.customElements) {
    return;
  }

  window.customElements.whenDefined("model-viewer").then(function () {
    var viewer = window.customElements.get("model-viewer");

    Object.keys(values).forEach(function (name) {
      viewer[name] = values[name];
    });
  });
}

function armvwApplyViewerAttributes(viewer, attributes) {
  Object.keys(attributes || {}).forEach(function (name) {
    var value = attributes[name];

    if (value === true) {
      viewer.setAttribute(name, "");
    } else if (value !== false && value !== null && value !== undefined && value !== "") {
      viewer.setAttribute(name, String(value));
    }
  });
}

function armvwApplyViewerData(viewer, data) {
  var attributes = {};

  Object.assign(attributes, data.attributes || {}, data.product_attributes || {});
  armvwApplyViewerAttributes(viewer, attributes);

  if (data.ar) {
    viewer.setAttribute("ar", "");
    viewer.setAttribute("ar-modes", (data.ar_modes || []).join(" "));
    viewer.setAttribute("ar-scale", data.scale || "auto");
    viewer.setAttribute("ar-placement", data.placement || "floor");

    if (data.xr_environment) {
      viewer.setAttribute("xr-environment", "");
    }
  }

  if (data.with_credentials) {
    viewer.setAttribute("with-credentials", "");
  }

  viewer
    .setAttribute("src", data.model_3d_file || "")
    .setAttribute("alt", data.model_alt || "")
    .setAttribute("poster", data.model_poster || "")
    .setAttribute("reveal", data.reveal || "auto")
    .setAttribute("loading", data.loading || "auto");
  viewer.style.backgroundColor = data.poster_color || "rgba(255,255,255,0)";
}

function armvwBuildArButton(data) {
  if (!data.ar_button || !data.ar_button_text) {
    return null;
  }

  var button = document.createElement("button");
  button.setAttribute("slot", "ar-button");
  button.textContent = data.ar_button_text;
  button.style.backgroundColor = data.ar_button_background_color || "#ffffff";
  button.style.color = data.ar_button_text_color || "#000000";
  button.style.border = "none";
  button.style.borderRadius = "999px";
  button.style.padding = "8px 14px";
  button.style.cursor = "pointer";

  return button;
}

armvwVendor.ensure(["alertify"]);

function armvwDriverFactory() {
  if (typeof window.driver === "function") {
    return window.driver;
  }

  if (
    window.driver &&
    window.driver.js &&
    typeof window.driver.js.driver === "function"
  ) {
    return window.driver.js.driver;
  }

  return null;
}

(function ($) {
  "use strict";

  $(function () {
    var translate = wp.i18n.__;

    function getProduct3DTutorialSteps() {
      return [
        {
          element: "#ar_model_viewer_for_woocommerce_file_object",
          popover: {
            title: translate("Upload or Add the 3D Object File", "ar-model-viewer-for-woocommerce"),
            description: translate(
              'In the "3D Object File" field, enter the URL of the .glb or .gltf file that contains the 3D model.',
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          element: "#ar_model_viewer_for_woocommerce_file_poster",
          popover: {
            title: translate("Add a Loading Poster", "ar-model-viewer-for-woocommerce"),
            description: translate(
              "Add an image that will be displayed while the 3D model is loading.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          element: "#ar_model_viewer_for_woocommerce_file_alt",
          popover: {
            title: translate("Configure Alternative Text (alt)", "ar-model-viewer-for-woocommerce"),
            description: translate(
              "Enter a descriptive text that improves accessibility for visually impaired users.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          element: "#ar-model-viewer-for-woocommerce-template-file",
          popover: {
            title: translate("Embed in a Template File", "ar-model-viewer-for-woocommerce"),
            description: translate(
              "Copy the PHP code provided in the template section into your theme.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          element: "#ar-model-viewer-for-woocommerce-shortcode",
          popover: {
            title: translate("Using the Shortcode", "ar-model-viewer-for-woocommerce"),
            description: translate(
              "Copy the shortcode into the WordPress editor wherever you want the 3D model.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
        {
          element: "#publish",
          popover: {
            title: translate("Save", "ar-model-viewer-for-woocommerce"),
            description: translate(
              "Save the product after adding the model fields.",
              "ar-model-viewer-for-woocommerce"
            ),
          },
        },
      ];
    }

    function startProduct3DTutorial() {
      window.armvwVendor.ensure(["driver"]).then(function () {
        var createDriver = armvwDriverFactory();

        if (!createDriver) {
          return;
        }

        createDriver({
          showProgress: true,
          allowClose: false,
          steps: getProduct3DTutorialSteps(),
        }).drive();
      });
    }

    $("#ar_model_viewer_for_woocommerce_product_tutorial").on("click", function (event) {
      event.preventDefault();
      startProduct3DTutorial();
    });

    function copyToClipboard(elementId, successMessage) {
      var text = $(elementId).text().trim();
      var temporary = $("<textarea>").val(text).appendTo("body").select();

      document.execCommand("copy");
      temporary.remove();
      alertify.success(translate(successMessage, "ar-model-viewer-for-woocommerce"));
    }

    $("#button-copy-shortcode-template-file").on("click", function (event) {
      event.preventDefault();
      copyToClipboard("#php-include-text", "PHP code copied to clipboard!");
    });

    $("#button-copy-shortcode").on("click", function (event) {
      event.preventDefault();
      copyToClipboard("#shortcode-text", "Shortcode copied to clipboard!");
    });

    /*
     * The three kinds of resource the editor can ask for.
     *
     * The extension is the rule the viewer depends on and the mime type is only how the media
     * library is filtered, so both live together here: a new kind of file is added once and every
     * button of the product and of the variations picks it up.
     *
     * `application/octet-stream` is part of the model and USDZ filters on purpose. A file uploaded
     * through FTP, or by a version of the plugin that did not declare the type yet, is stored
     * without one, and a picker that only looked for the declared types would hide a file the
     * store knows is there.
     */
    var armvwKinds = {
      model: {
        extensions: ["glb", "gltf"],
        types: ["model/gltf-binary", "model/gltf+json", "application/octet-stream"],
      },
      usdz: {
        extensions: ["usdz"],
        types: ["model/vnd.usdz+zip", "application/octet-stream"],
      },
      image: {
        extensions: ["jpg", "jpeg", "png", "webp", "avif"],
        types: ["image/jpeg", "image/png", "image/webp", "image/avif"],
      },
    };

    function armvwKind(kind) {
      return armvwKinds[kind] || armvwKinds.model;
    }

    function armvwLibraryFor(kind) {
      return { type: armvwKind(kind).types };
    }

    function armvwExtensionIsAllowed(url, kind) {
      var clean = String(url || "").split("?")[0].split("#")[0];
      var extension = clean.substring(clean.lastIndexOf(".") + 1).toLowerCase();

      return kind && armvwKind(kind).extensions.indexOf(extension) !== -1;
    }

    $(document).on("click", ".armvw-media", function (event) {
      event.preventDefault();

      var button = $(this);
      var input = document.getElementById(button.data("armvw-target"));
      var kind = button.data("armvw-kind");

      if (!input || !window.wp || !window.wp.media) {
        return;
      }

      var frame = window.wp.media({
        title: button.data("armvw-title"),
        button: { text: button.data("armvw-button") },
        library: armvwLibraryFor(kind),
        multiple: false,
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();

        /*
         * The library is filtered by type, but a filter is a convenience and not a rule: an
         * attachment can be saved with a mime type that does not match its extension, and picking
         * a JPEG for the model would be discovered days later on the product page. The extension
         * is checked here, which is the last moment the store is still looking at the dialog.
         */
        if (!armvwExtensionIsAllowed(attachment.url, kind)) {
          window.alert(
            translate(
              "That file type is not allowed here. Expected:",
              "ar-model-viewer-for-woocommerce"
            ) + " " + armvwKind(kind).extensions.join(", ")
          );

          input.value = "";
          input.dispatchEvent(new Event("change"));

          var cleared = document.getElementById(button.data("armvw-id") || "");

          if (cleared) {
            cleared.value = "";
          }

          return;
        }

        input.value = attachment.url;
        input.dispatchEvent(new Event("change"));

        /*
         * The id of the attachment travels next to the URL. The URL is what the viewer prints
         * and what a CSV carries, so it stays the source of truth; the id is what lets the
         * editor know that the file came from this media library and not from a CDN.
         */
        var idInput = document.getElementById(button.data("armvw-id") || "");

        if (idInput) {
          idInput.value = attachment.id || "";
        }
      });

      frame.open();
    });

    /*
     * A variation only shows its three file fields once it is asked to. The fields are not
     * removed from the form, they are hidden: a store that turns the switch off and on again
     * before saving gets back what it had typed.
     *
     * The class is written in full and not shortened: it is the same one the partial uses, and a
     * typo here leaves a switch that silently does nothing.
     */
    $(document).on("change", ".armvw-variation__custom", function () {
      $(this)
        .closest(".armvw-variation")
        .find(".armvw-variation__files")
        .prop("hidden", !this.checked);
    });

    $(document).on("click", "#armvw-use-current-view", function () {
      var viewer = document.getElementById("model-viewer");

      if (!viewer || typeof viewer.getCameraOrbit !== "function") {
        alertify.error(translate(
          "Open the 3D preview first: there is no viewer to read the camera from.",
          "ar-model-viewer-for-woocommerce"
        ));
        return;
      }

      var values = {
        "armvw-product-camera-orbit": viewer.getCameraOrbit().toString(),
        "armvw-product-camera-target": viewer.getCameraTarget().toString(),
        "armvw-product-field-of-view": Math.round(viewer.getFieldOfView() * 100) / 100 + "deg",
      };
      var written = 0;

      Object.keys(values).forEach(function (id) {
        var input = document.getElementById(id);

        if (input) {
          input.value = values[id];
          written++;
        }
      });

      if (written === 0) {
        alertify.error(translate(
          "The fields of the 3D viewer were not found on this page.",
          "ar-model-viewer-for-woocommerce"
        ));
        return;
      }

      alertify.success(translate(
        "Camera copied. Save the product to keep it.",
        "ar-model-viewer-for-woocommerce"
      ));
    });

    $("#ar_model_viewer_for_woocommerce_product_preview").on("click", function (event) {
      event.preventDefault();

      var productId = $(this).data("product-id");

      if (!productId) {
        alertify.error(translate("Product ID is missing.", "ar-model-viewer-for-woocommerce"));
        return;
      }

      window.armvwVendor.ensure(["model-viewer"]).then(armvwApplyStaticProperties);

        var htmlContent = `
      <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
          <model-viewer id="model-viewer" style="width: 100%; max-width: 600px; height: 400px;"></model-viewer>
      </div>`;
      var loadingMessage;

      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        data: {
          action: "ar_model_viewer_for_woocommerce_get_model_and_settings",
          product_id: productId,
        },
        dataType: "json",
        beforeSend: function () {
          loadingMessage = alertify.success("Loading 3D model...", 0);
        },
        success: function (response) {
          if (loadingMessage) {
            loadingMessage.dismiss();
          }

          if (!response.success) {
            alertify.error("Error: " + response.data);
            return;
          }

          var data = response.data;
          var productName = data.product_name || "3D Model";

          alertify.alert(productName, htmlContent).set({
            transition: "zoom",
            movable: true,
            maximizable: true,
          }).setHeader(productName);

          var viewer = document.getElementById("model-viewer");

          if (!viewer) {
            alertify.error("The 3D preview could not be initialized.");
            return;
          }

          armvwApplyViewerData(viewer, data);

          var arButton = armvwBuildArButton(data);

          if (arButton) {
            viewer.appendChild(arButton);
          }
        },
        error: function (xhr, status, error) {
          if (loadingMessage) {
            loadingMessage.dismiss();
          }
          alertify.error("AJAX Error: " + error);
        },
      });
    });
  });
})(jQuery);
