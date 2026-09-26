/**
 * Settings screen interactions.
 *
 * Dependency free (no bundler, no jQuery): the file is enqueued as-is.
 *
 * @link       https://racmanuel.dev
 * @since      3.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 */

(function () {
	"use strict";

	/**
	 * Option name used by every field of the settings form.
	 *
	 * @type {string}
	 */
	var OPTION_NAME = "ar_model_viewer_for_woocommerce_settings";

	/**
	 * Local storage key that remembers the last visited tab.
	 *
	 * @type {string}
	 */
	var STORAGE_KEY = "armvwSettingsTab";

	/**
	 * Boot every behaviour once the DOM is available.
	 *
	 * @return {void}
	 */
	function init() {
		var form = document.getElementById("armvw-settings-form");

		if (!form) {
			return;
		}

		initTabs();
		initDependencies(form);
		initApiKeyReveal(form);
		initPreviewLoader();
		initResetConfirmation();
	}

	/**
	 * Wire the tab list: click, keyboard navigation, hash and persistence.
	 *
	 * @return {void}
	 */
	function initTabs() {
		var list = document.querySelector(".armvw-tabs");
		var tabs = Array.prototype.slice.call(document.querySelectorAll(".armvw-tab"));

		if (!list || !tabs.length) {
			return;
		}

		/**
		 * Activate a tab and show its panel.
		 *
		 * @param {HTMLElement} tab      Tab button to activate.
		 * @param {boolean}     remember Whether the choice should be persisted.
		 * @return {void}
		 */
		function activate(tab, remember) {
			var name = tab.getAttribute("data-armvw-tab");

			tabs.forEach(function (item) {
				var isActive = item === tab;
				var panel = document.getElementById("armvw-panel-" + item.getAttribute("data-armvw-tab"));

				item.classList.toggle("is-active", isActive);
				item.setAttribute("aria-selected", isActive ? "true" : "false");
				item.setAttribute("tabindex", isActive ? "0" : "-1");

				if (panel) {
					if (isActive) {
						panel.removeAttribute("hidden");
					} else {
						panel.setAttribute("hidden", "hidden");
					}
				}
			});

			if (remember) {
				try {
					window.localStorage.setItem(STORAGE_KEY, name);
				} catch (error) {
					// Storage can be unavailable in private mode; the tab still switches.
				}
			}
		}

		tabs.forEach(function (tab) {
			tab.addEventListener("click", function () {
				activate(tab, true);
			});

			tab.addEventListener("keydown", function (event) {
				var index = tabs.indexOf(tab);
				var next = null;

				if (event.key === "ArrowRight" || event.key === "ArrowDown") {
					next = tabs[(index + 1) % tabs.length];
				} else if (event.key === "ArrowLeft" || event.key === "ArrowUp") {
					next = tabs[(index - 1 + tabs.length) % tabs.length];
				} else if (event.key === "Home") {
					next = tabs[0];
				} else if (event.key === "End") {
					next = tabs[tabs.length - 1];
				}

				if (next) {
					event.preventDefault();
					next.focus();
					activate(next, true);
				}
			});
		});

		// The hash wins over the stored tab so documentation links stay shareable.
		var hash = window.location.hash.replace("#", "");
		var requested = tabs.filter(function (tab) {
			return tab.getAttribute("data-armvw-tab") === hash;
		})[0];

		if (requested) {
			activate(requested, false);
			return;
		}

		try {
			var stored = window.localStorage.getItem(STORAGE_KEY);
			var remembered = tabs.filter(function (tab) {
				return tab.getAttribute("data-armvw-tab") === stored;
			})[0];

			if (remembered) {
				activate(remembered, false);
			}
		} catch (error) {
			// Nothing to restore.
		}
	}

	/**
	 * Read the current value of a setting from the form.
	 *
	 * @param {HTMLFormElement} form Settings form.
	 * @param {string}          key  Setting key without the option prefix.
	 * @return {string|null} The checked value of the group, or the field value.
	 */
	function readValue(form, key) {
		var fields = form.querySelectorAll('[name="' + OPTION_NAME + "[" + key + ']"]');

		for (var i = 0; i < fields.length; i += 1) {
			var field = fields[i];

			if (field.type === "radio" || field.type === "checkbox") {
				if (field.checked) {
					return field.value;
				}
			} else if (field.value !== "") {
				return field.value;
			}
		}

		return null;
	}

	/**
	 * Show or hide the fields that depend on the value of another field.
	 *
	 * Fields declare their condition with `data-armvw-depends="key=value"`.
	 *
	 * @param {HTMLFormElement} form Settings form.
	 * @return {void}
	 */
	function initDependencies(form) {
		var dependent = Array.prototype.slice.call(form.querySelectorAll("[data-armvw-depends]"));

		if (!dependent.length) {
			return;
		}

		function apply() {
			dependent.forEach(function (field) {
				var parts = field.getAttribute("data-armvw-depends").split("=");
				var current = readValue(form, parts[0]);
				var matches = current === parts[1];

				if (matches) {
					field.removeAttribute("hidden");
				} else {
					field.setAttribute("hidden", "hidden");
				}
			});
		}

		form.addEventListener("change", apply);
		form.addEventListener("input", apply);
		apply();
	}

	/**
	 * Allow revealing the API key field.
	 *
	 * @param {HTMLFormElement} form Settings form.
	 * @return {void}
	 */
	function initApiKeyReveal(form) {
		Array.prototype.slice.call(form.querySelectorAll(".armvw-reveal")).forEach(function (button) {
			button.addEventListener("click", function () {
				var input = document.getElementById(button.getAttribute("data-armvw-target"));

				if (!input) {
					return;
				}

				var isHidden = input.type === "password";

				input.type = isHidden ? "text" : "password";
				button.setAttribute("aria-pressed", isHidden ? "true" : "false");
				button.setAttribute("aria-label", button.getAttribute(isHidden ? "data-armvw-hide-label" : "data-armvw-show-label") || "");
				button.classList.toggle("is-active", isHidden);
			});
		});
	}

	/**
	 * Apply the static properties of the viewer element.
	 *
	 * The render scale, the power preference, the cache size and the decoder locations are static
	 * properties of the element class and not attributes, so they cannot travel in the markup.
	 * They have to be assigned once the library is defined and before a viewer is created, which
	 * is why this runs right after the library finishes loading.
	 *
	 * @return {void}
	 */
	function applyStaticProperties() {
		var values = (window.armvwSettings || {}).static_properties || {};

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

	/**
	 * Load the 3D viewer only when the visitor asks for the demo.
	 *
	 * The library weighs around 1 MB, so the card shows the poster until it is clicked. The
	 * `<model-viewer>` element is already in the markup and upgrades itself as soon as the
	 * library registers the custom element.
	 *
	 * @return {void}
	 */
	function initPreviewLoader() {
		var button = document.getElementById("armvw-preview-load");
		var wrapper = document.querySelector(".armvw-preview");
		var url = (window.armvwSettings || {}).viewer_url;

		if (!button || !wrapper || !url) {
			return;
		}

		button.addEventListener("click", function () {
			button.disabled = true;

			var script = document.createElement("script");

			script.src = url;
			script.async = true;
			script.onload = function () {
				applyStaticProperties();
				wrapper.classList.add("is-ready");
			};
			script.onerror = function () {
				button.disabled = false;
			};

			document.head.appendChild(script);
		});
	}

	/**
	 * Ask for confirmation before restoring the default settings.
	 *
	 * @return {void}
	 */
	function initResetConfirmation() {		Array.prototype.slice.call(document.querySelectorAll(".armvw-reset")).forEach(function (link) {
			link.addEventListener("click", function (event) {
				if (!window.confirm(link.getAttribute("data-armvw-confirm") || "")) {
					event.preventDefault();
				}
			});
		});
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
