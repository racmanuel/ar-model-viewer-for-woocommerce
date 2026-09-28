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

    if (typeof window.armvwInitTabs === "function") {
      window.armvwInitTabs({ storageKey: "armvwProductTab" });
    }

    $(document).on("click", ".armvw-media", function (event) {
      event.preventDefault();

      var button = $(this);
      var input = document.getElementById(button.data("armvw-target"));
      var isModel = "model" === button.data("armvw-kind");

      if (!input || !window.wp || !window.wp.media) {
        return;
      }

      var frame = window.wp.media({
        title: button.data("armvw-title"),
        button: { text: button.data("armvw-button") },
        library: isModel
          ? {}
          : { type: ["image/jpeg", "image/png", "image/webp", "image/gif"] },
        multiple: false,
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        input.value = attachment.url;
        input.dispatchEvent(new Event("change"));
      });

      frame.open();
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
          <model-viewer id="model-viewer" src="" alt="" poster="" reveal="" loading="" ar ar-modes="" camera-controls ar-scale="auto" style="width: 100%; max-width: 600px; height: 400px;"></model-viewer>
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

          $("#model-viewer")
            .attr("src", data.model_3d_file || "")
            .attr("alt", data.model_alt || "")
            .attr("poster", data.model_poster || "")
            .attr("reveal", data.reveal || "auto")
            .attr("loading", data.loading || "auto")
            .attr("ar-modes", (data.ar_modes || []).join(" "))
            .attr("ar-scale", data.scale || "auto")
            .css("background-color", data.poster_color || "rgba(255,255,255,0)");
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
