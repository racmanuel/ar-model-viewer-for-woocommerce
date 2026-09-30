/**
 * The front of the site: the library is fetched when a page really needs it and the modal is a
 * native `<dialog>`.
 *
 * Everything the plugin used to do here was bundled inside a 880 KB file that also carried the
 * viewer library, jQuery and alertify. Now the library is requested only when the page has a
 * viewer or the button of a product, and the modal is a dialog element with the palette of the
 * plugin, which removes a dependency and a stylesheet injection at the same time.
 *
 * @link       https://racmanuel.dev
 * @since      3.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 */

(function (window, document) {
	"use strict";

	var config = window.armvwFront || {};
	var libraryPromise = null;
	var dialog = null;
	var modalSession = null;

	function analyticsOptedOut() {
		var analytics = config.analytics || {};

		try {
			return "1" === window.localStorage.getItem(analytics.storageKey || "armvwAnalyticsOptOut");
		} catch (error) {
			return false;
		}
	}

	function analyticsEnabled() {
		return !!(config.analytics && config.analytics.enabled) && !analyticsOptedOut();
	}

	function durationBucket(start) {
		if (!start) {
			return "";
		}

		var seconds = Math.max(0, Math.round((window.performance.now() - start) / 1000));

		if (seconds < 5) {
			return "0-4s";
		}

		if (seconds < 15) {
			return "5-14s";
		}

		if (seconds < 60) {
			return "15-59s";
		}

		return "60s+";
	}

	function sendAnalytics(eventName, details) {
		var analytics = config.analytics || {};

		if (!analyticsEnabled() || !analytics.endpoint) {
			return;
		}

		var body = new window.URLSearchParams();
		body.append("event", eventName);
		body.append("product_id", String((details && details.productId) || 0));
		body.append("variation_id", String((details && details.variationId) || currentVariation || 0));
		body.append("mode", String((details && details.mode) || ""));
		body.append("error_code", String((details && details.errorCode) || ""));
		body.append("duration_bucket", String((details && details.durationBucket) || ""));

		window.fetch(analytics.endpoint, {
			method: "POST",
			credentials: "same-origin",
			body: body,
			keepalive: true,
		}).catch(function () {
			// Analytics must never affect the viewer when the endpoint is unavailable.
		});
	}

	function viewerMode(viewer) {
		return (viewer.getAttribute("ar-modes") || "").split(" ")[0] || "";
	}

	function trackViewer(viewer, productId) {
		if (!viewer || viewer.dataset.armvwTracked) {
			return;
		}

		viewer.dataset.armvwTracked = "1";
		var interactionSent = false;
		var interactionTimer = null;

		viewer.addEventListener("load", function () {
			sendAnalytics("viewer_open", { productId: productId });
			sendAnalytics("viewer_load", { productId: productId });
		});
		viewer.addEventListener("error", function () {
			sendAnalytics("viewer_error", { productId: productId, errorCode: "model-load-failed" });
		});
		viewer.addEventListener("ar-status", function () {
			var status = viewer.getAttribute("ar-status");

			if ("session-started" === status) {
				sendAnalytics("ar_session_started", { productId: productId, mode: viewerMode(viewer) });
			} else if ("object-placed" === status) {
				sendAnalytics("ar_object_placed", { productId: productId, mode: viewerMode(viewer) });
			} else if ("failed" === status) {
				sendAnalytics("ar_failed", { productId: productId, mode: viewerMode(viewer), errorCode: "tracking-failed" });
			}
		});
		viewer.addEventListener("click", function (event) {
			if (event.target.closest && event.target.closest('[slot="ar-button"]')) {
				sendAnalytics("ar_attempt", { productId: productId, mode: viewerMode(viewer) });
			}
		});
		viewer.addEventListener("camera-change", function () {
			if (interactionSent) {
				return;
			}

			interactionSent = true;
			sendAnalytics("viewer_interaction", { productId: productId });
			interactionTimer = window.setTimeout(function () {
				interactionSent = false;
			}, 10000);
		});
	}

	/**
	 * Fetch the viewer library, at most once per page.
	 *
	 * The library registers the custom element on its own, so a static viewer (the product tab or
	 * the shortcode) only needs this to be called. The promise is memoized so several viewers on
	 * the same page share one request.
	 *
	 * @return {Promise<void>} Resolves when the custom element is available.
	 */
	function ensureLibrary() {
		if (window.customElements && window.customElements.get("model-viewer")) {
			return Promise.resolve();
		}

		if (libraryPromise) {
			return libraryPromise;
		}

		if (!config.viewerUrl) {
			return Promise.reject(new Error("The viewer library has no URL."));
		}

		libraryPromise = new Promise(function (resolve, reject) {
			var script = document.createElement("script");

			script.src = config.viewerUrl;
			script.async = true;

			script.onload = function () {
				applyStaticProperties();
				resolve();
			};

			script.onerror = function () {
				reject(new Error("The viewer library could not be loaded."));
			};

			document.head.appendChild(script);
		});

		return libraryPromise;
	}

	/**
	 * Apply the static properties declared in the settings.
	 *
	 * They are not HTML attributes, so they cannot travel in the markup and have to be assigned
	 * once the class exists and before a viewer is created.
	 *
	 * @return {void}
	 */
	function applyStaticProperties() {
		var values = config.staticProperties || {};

		if (!window.customElements || !Object.keys(values).length) {
			return;
		}

		window.customElements.whenDefined("model-viewer").then(function () {
			var viewer = window.customElements.get("model-viewer");

			Object.keys(values).forEach(function (name) {
				viewer[name] = values[name];
			});
		});
	}

	/**
	 * Variation the shopper is looking at, or 0 while the parent product is shown.
	 *
	 * The modal and every inline viewer read it, so the model that is rendered always belongs to
	 * the options that are selected on the page.
	 *
	 * @type {number}
	 */
	var currentVariation = 0;

	/**
	 * Transfer the values of the endpoint to a viewer element.
	 *
	 * The endpoint already hands over the attributes the settings produce, so the script does not
	 * decide which setting maps to which attribute: it copies. That is what made the old modal
	 * ignore half of the settings without anyone noticing.
	 *
	 * Every attribute this function writes is remembered on the element, because switching from one
	 * variation to another has to remove the attributes of the previous one. Without that, a
	 * shopper who goes from a variation with augmented reality to one without would keep the `ar`
	 * attribute of the first and be offered a feature the product no longer has.
	 *
	 * @param {HTMLElement} viewer The viewer element.
	 * @param {Object}      data   Response of the endpoint.
	 * @return {void}
	 */
	function applyData(viewer, data) {
		var previous = (viewer.dataset.armvwApplied || "").split(" ").filter(Boolean);

		previous.forEach(function (name) {
			viewer.removeAttribute(name);
		});

		var applied = [];
		var set = function (name, value) {
			viewer.setAttribute(name, value);
			applied.push(name);
		};

		set("src", data.model_3d_file || "");
		set("alt", data.model_alt || "");

		if (data.model_poster) {
			set("poster", data.model_poster);
		}

		if (data.poster_color) {
			viewer.style.backgroundColor = data.poster_color;
		}

		if (data.with_credentials) {
			set("with-credentials", "");
		}

		/*
		 * The AR attributes arrive as separate keys and not inside the attribute map, because they
		 * depend on the AR switch and on a list of modes. They are applied the same way the server
		 * renders them on a product page, so the dialog and the tab cannot disagree.
		 */
		if (data.ar) {
			set("ar", "");

			if (Array.isArray(data.ar_modes) && data.ar_modes.length) {
				set("ar-modes", data.ar_modes.join(" "));
			}

			if (data.scale) {
				set("ar-scale", data.scale);
			}

			if (data.placement) {
				set("ar-placement", data.placement);
			}

			if (data.xr_environment) {
				set("xr-environment", "");
			}
		}

		var shared = data.attributes || {};
		var own = data.product_attributes || {};

		[shared, own].forEach(function (group) {
			Object.keys(group).forEach(function (name) {
				var value = group[name];

				if (value === true || value === "") {
					set(name, "");
					return;
				}

				set(name, value);
			});
		});

		/*
		 * The USDZ file can belong to the variation, so it is written after the attribute map and
		 * replaces whatever the parent had put there.
		 */
		if (data.model_ios_src) {
			viewer.removeAttribute("ios-src");
			set("ios-src", data.model_ios_src);
		}

		viewer.dataset.armvwApplied = applied.join(" ");
	}

	/**
	 * Build the AR button of the modal, when the settings ask for one.
	 *
	 * @param {Object} data Response of the endpoint.
	 * @return {HTMLElement|null} The button, or null when it is not enabled.
	 */
	function buildArButton(data) {
		if (!data.ar_button || !data.ar_button_text) {
			return null;
		}

		var button = document.createElement("button");

		button.setAttribute("slot", "ar-button");
		button.type = "button";
		button.className = "armvw-ar-button";
		button.textContent = data.ar_button_text;
		button.style.backgroundColor = data.ar_button_background_color || "#ffffff";
		button.style.color = data.ar_button_text_color || "#000000";

		return button;
	}

	/**
	 * Create the dialog the first time it is needed.
	 *
	 * @return {HTMLDialogElement} The dialog element.
	 */
	function buildDialog() {
		if (dialog) {
			return dialog;
		}

		dialog = document.createElement("dialog");
		dialog.className = "armvw-modal";

		var head = document.createElement("header");
		head.className = "armvw-modal__head";

		var title = document.createElement("h2");
		title.className = "armvw-modal__title";
		title.id = "armvw-modal-title";
		head.appendChild(title);

		var close = document.createElement("button");
		close.type = "button";
		close.className = "armvw-modal__close";
		close.setAttribute("aria-label", config.i18n.close || "Close");
		close.textContent = "\u2715";
		close.addEventListener("click", closeModal);
		head.appendChild(close);

		var body = document.createElement("div");
		body.className = "armvw-modal__body";

		var status = document.createElement("p");
		status.className = "armvw-modal__status";
		status.textContent = config.i18n.loading || "Loading the 3D model…";
		body.appendChild(status);

		if (config.analytics && config.analytics.showOptOut) {
			var privacy = document.createElement("button");
			privacy.type = "button";
			privacy.className = "armvw-modal__privacy";
			privacy.addEventListener("click", function () {
				var storageKey = config.analytics.storageKey || "armvwAnalyticsOptOut";

				try {
					if (analyticsOptedOut()) {
						window.localStorage.removeItem(storageKey);
					} else {
						window.localStorage.setItem(storageKey, "1");
					}
				} catch (error) {
					return;
				}

				updatePrivacyControl(privacy);
			});
			body.appendChild(privacy);
			updatePrivacyControl(privacy);
		}

		dialog.appendChild(head);
		dialog.appendChild(body);
		dialog.setAttribute("aria-labelledby", "armvw-modal-title");

		// A click on the backdrop lands on the dialog itself, never on its content.
		dialog.addEventListener("click", function (event) {
			if (event.target === dialog) {
				closeModal();
			}
		});
		dialog.addEventListener("close", finishModalSession);

		document.body.appendChild(dialog);

		return dialog;
	}

	function updatePrivacyControl(control) {
		if (!control) {
			return;
		}

		control.textContent = analyticsOptedOut()
			? (config.i18n.analyticsOptIn || "Allow anonymous viewer analytics")
			: (config.i18n.analyticsOptOut || "Do not measure this browser");
		control.setAttribute("aria-pressed", analyticsOptedOut() ? "true" : "false");
	}

	function finishModalSession() {
		if (!modalSession) {
			return;
		}

		sendAnalytics("viewer_close", {
			productId: modalSession.productId,
			durationBucket: durationBucket(modalSession.startedAt),
		});
		modalSession = null;
	}

	/**
	 * Close the dialog and release the model it was showing.
	 *
	 * @return {void}
	 */
	function closeModal() {
		if (!dialog) {
			return;
		}

		finishModalSession();
		dialog.close();
		dialog.querySelectorAll("model-viewer").forEach(function (viewer) {
			viewer.removeAttribute("src");
		});
	}

	/**
	 * Ask the server for the model of a product and show it in the dialog.
	 *
	 * @param {number} productId Product id.
	 * @return {void}
	 */
	function openModal(productId) {
		var box = buildDialog();
		var body = box.querySelector(".armvw-modal__body");
		var title = box.querySelector(".armvw-modal__title");
		var status = box.querySelector(".armvw-modal__status");

		status.textContent = config.i18n.loading || "Loading the 3D model…";
		status.hidden = false;
		status.classList.remove("armvw-modal__status--error");
		box.querySelectorAll("model-viewer").forEach(function (viewer) {
			viewer.remove();
		});

		box.showModal();
		modalSession = {
			productId: productId,
			startedAt: window.performance.now(),
		};
		sendAnalytics("viewer_open", { productId: productId });

		Promise.all([ensureLibrary(), requestModel(productId, currentVariation)])
			.then(function (results) {
				var data = results[1];

				/*
				 * The dialog is cleared again right before the viewer is built. A second request can
				 * land while the first one is still loading, and two viewers in one dialog are a
				 * model drawn on top of a model where only one of them receives the events.
				 */
				box.querySelectorAll("model-viewer").forEach(function (existing) {
					existing.remove();
				});

				title.textContent = data.product_name || "";
				status.hidden = true;

				var viewer = document.createElement("model-viewer");
				viewer.className = "armvw-viewer";
				// Marks the viewer of the dialog, so a change of variation can find it and reload it.
				viewer.setAttribute("data-armvw-modal", "1");
				viewer.setAttribute("loading", "eager");
				viewer.setAttribute("reveal", "auto");
				viewer.setAttribute("camera-controls", "");

				applyData(viewer, data);

				var arButton = buildArButton(data);

				if (arButton) {
					viewer.appendChild(arButton);
				}

				body.appendChild(viewer);
				var privacy = body.querySelector(".armvw-modal__privacy");

				if (privacy) {
					body.appendChild(privacy);
				}

				trackViewer(viewer, productId);
			})
			.catch(function (error) {
				sendAnalytics("viewer_error", { productId: productId, errorCode: "model-load-failed" });
				status.textContent = config.i18n.error || "The 3D model could not be loaded.";
				status.hidden = false;
				status.classList.add("armvw-modal__status--error");

				if (window.console && window.console.error) {
					window.console.error(error);
				}
			});
	}

	/**
	 * Request the model and the settings of a product, or of one of its variations.
	 *
	 * @param {number} productId   Product id.
	 * @param {number} variationId Optional variation id.
	 * @return {Promise<Object>} The data of the endpoint.
	 */
	function requestModel(productId, variationId) {
		var endpoint = (config.modelEndpoint || "") + encodeURIComponent(productId) + "/model";

		if (variationId) {
			endpoint += "?variation_id=" + encodeURIComponent(variationId);
		}

		return window.fetch(endpoint, {
			method: "GET",
			credentials: "same-origin",
		})
			.then(function (response) {
				return response.json().then(function (json) {
					if (!response.ok) {
						throw new Error((json && json.message) || "The server refused the request.");
					}

					return json;
				});
			});
	}

	/**
	 * Put the model of a variation, or of the parent, into a viewer that is already on the page.
	 *
	 * A variation without a model of its own is answered by the server with the values of the
	 * parent, so the fallback happens in one place and this function does not have to know about
	 * it.
	 *
	 * @param {HTMLElement} viewer      The viewer element.
	 * @param {number}      variationId Variation id, or 0 for the parent.
	 * @return {void}
	 */
	function refreshViewer(viewer, variationId) {
		var productId = viewer.getAttribute("data-product-id");

		if (!productId) {
			return;
		}

		requestModel(productId, variationId)
			.then(function (data) {
				applyData(viewer, data);
			})
			.catch(function () {
				/*
				 * A product with no model, or a request that failed, leaves the viewer as it is: an old
				 * model on screen is less confusing than an empty box where a model used to be.
				 */
			});
	}

	/**
	 * Reload the model of the dialog, without closing it.
	 *
	 * @return {void}
	 */
	function reloadModal() {
		if (!dialog || !modalSession) {
			return;
		}

		var viewer = dialog.querySelector("model-viewer[data-armvw-modal]");

		if (!viewer) {
			return;
		}

		requestModel(modalSession.productId, currentVariation)
			.then(function (data) {
				applyData(viewer, data);

				var title = dialog.querySelector(".armvw-modal__title");

				if (title) {
					title.textContent = data.product_name || "";
				}

				var existing = viewer.querySelector('[slot="ar-button"]');

				if (existing) {
					existing.remove();
				}

				var arButton = buildArButton(data);

				if (arButton) {
					viewer.appendChild(arButton);
				}
			})
			.catch(function () {
				// The dialog keeps showing the model it already had.
			});
	}

	/**
	 * Show the model of a variation, or of the parent when there is none.
	 *
	 * @param {number} variationId Variation id, or 0.
	 * @return {void}
	 */
	function updateVariation(variationId) {
		currentVariation = variationId || 0;

		if (dialog && dialog.open) {
			reloadModal();
			return;
		}

		if (currentVariation) {
			document.querySelectorAll("model-viewer[data-product-id]").forEach(function (viewer) {
				refreshViewer(viewer, currentVariation);
			});
		}
	}

	/**
	 * Follow the variation the shopper selects.
	 *
	 * WooCommerce publishes which variation its options resolve to, both on the product page and
	 * in the quick view, so the viewer follows it instead of reaching for the DOM of the form.
	 * The events are bound to the form and not to the document, so a page with several variable
	 * products in a loop does not cross its variations.
	 *
	 * @return {void}
	 */
	function initVariations() {
		if (!window.jQuery) {
			return;
		}

		var form = window.jQuery(".variations_form");

		if (!form.length) {
			return;
		}

		form.on("found_variation", function (event, variation) {
			updateVariation(variation && variation.variation_id ? variation.variation_id : 0);
		});

		/*
		 * `reset_data` is what WooCommerce fires when the options no longer form a variation, so it
		 * is the signal to go back to the model of the parent product.
		 */
		form.on("reset_data", function () {
			updateVariation(0);
		});
	}

	/**
	 * Wire the button of the product page, if there is one.
	 *
	 * @return {void}
	 */
	function initButton() {
		var button = document.getElementById(config.buttonId || "ar_model_viewer_for_woocommerce_btn");

		if (!button) {
			return;
		}

		button.addEventListener("click", function (event) {
			event.preventDefault();
			openModal(button.getAttribute("data-product-id"));
		});
	}

	/**
	 * Move the button into the visible image frame when the theme did not put it there.
	 *
	 * Some themes place the button in a gallery wrapper that is taller than the visible image frame.
	 * Positioning it against that wrapper makes the button appear below the product image, so prefer
	 * the visible figure or slide and only fall back to the gallery wrapper when no image frame exists.
	 *
	 * @return {void}
	 */
	function placeOverImage() {
		var button = document.querySelector(".armvw-button--over-image");

		if (!button) {
			return;
		}

		var selectors = [
			".flexy-item-is-visible figure.ct-media-container",
			".flexy-item-is-visible",
			".woocommerce-product-gallery__image",
			".flexy-view",
			".woocommerce-product-gallery",
			".woocommerce-product-gallery__wrapper",
			"[class*='product-gallery']",
		];
		var gallery = null;

		for (var i = 0; i < selectors.length && !gallery; i += 1) {
			gallery = document.querySelector(selectors[i]);
		}

		if (!gallery) {
			return;
		}

		if (!gallery || button.parentElement === gallery) {
			return;
		}

		// The button is absolutely positioned, so its container has to be a positioned ancestor.
		if ("static" === window.getComputedStyle(gallery).position) {
			gallery.style.position = "relative";
		}

		gallery.appendChild(button);
	}

	/**
	 * Start the front of the site.
	 *
	 * The library is only requested when the page has something to render, which is the whole point
	 * of moving it out of the bundle: a product without a model downloads nothing.
	 *
	 * @return {void}
	 */
	function init() {
		var hasViewer = !!document.querySelector("model-viewer");
		document.querySelectorAll("model-viewer").forEach(function (viewer) {
			trackViewer(viewer, viewer.getAttribute("data-product-id") || 0);
		});

		initButton();
		placeOverImage();
		initVariations();

		if (hasViewer) {
			ensureLibrary().catch(function () {
				// A static viewer without the library stays as an empty box; nothing to do here.
			});
		}
	}

	document.addEventListener("visibilitychange", function () {
		if ("hidden" === document.visibilityState) {
			finishModalSession();
		}
	});

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})(window, document);
