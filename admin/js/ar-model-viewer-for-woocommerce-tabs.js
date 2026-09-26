/**
 * Tab behaviour shared by the screens of the plugin.
 *
 * The markup owns the state (`is-active` on the button, `hidden` on the panel) and this file only
 * moves it, so a screen can ship its tabs without JavaScript and still show its first panel. It is
 * the same behaviour the settings screen has always had: click, keyboard navigation, the hash of
 * the URL and the last tab used, so a documentation link can point to a panel.
 *
 * @link       https://racmanuel.dev
 * @since      3.0.0
 * @package    Ar_Model_Viewer_For_Woocommerce
 */

(function (window, document) {
	"use strict";

	/**
	 * Wire a tab list.
	 *
	 * @param {Object} options                  Configuration.
	 * @param {string} [options.list]           Selector of the tab list. Defaults to `.armvw-tabs`.
	 * @param {string} [options.storageKey]     Key used to remember the last tab. Defaults to
	 *                                          `armvwTab`.
	 * @param {string} [options.panelPrefix]    Prefix of the panel ids. Defaults to `armvw-panel-`.
	 * @param {string} [options.attribute]      Attribute holding the slug. Defaults to
	 *                                          `data-armvw-tab`.
	 * @return {boolean} True when a tab list was wired.
	 */
	function armvwInitTabs(options) {
		var settings = options || {};
		var list = document.querySelector(settings.list || ".armvw-tabs");
		var attribute = settings.attribute || "data-armvw-tab";
		var panelPrefix = settings.panelPrefix || "armvw-panel-";
		var storageKey = settings.storageKey || "armvwTab";
		var tabs = [];

		if (!list) {
			return false;
		}

		tabs = Array.prototype.slice.call(list.querySelectorAll(".armvw-tab"));

		if (!tabs.length) {
			return false;
		}

		/**
		 * Read the slug of a tab.
		 *
		 * @param {HTMLElement} tab Tab button.
		 * @return {string} The slug.
		 */
		function slugOf(tab) {
			return tab.getAttribute(attribute);
		}

		/**
		 * Activate a tab and show its panel.
		 *
		 * @param {HTMLElement} tab      Tab button to activate.
		 * @param {boolean}     remember Whether the choice should be persisted.
		 * @return {void}
		 */
		function activate(tab, remember) {
			tabs.forEach(function (item) {
				var isActive = item === tab;
				var panel = document.getElementById(panelPrefix + slugOf(item));

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
					window.localStorage.setItem(storageKey, slugOf(tab));
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

		// The hash wins over the stored tab, so a link to a panel keeps working.
		var hash = window.location.hash.replace("#", "");
		var requested = tabs.filter(function (tab) {
			return slugOf(tab) === hash;
		})[0];

		if (requested) {
			activate(requested, false);

			return true;
		}

		try {
			var stored = window.localStorage.getItem(storageKey);
			var remembered = tabs.filter(function (tab) {
				return slugOf(tab) === stored;
			})[0];

			if (remembered) {
				activate(remembered, false);
			}
		} catch (error) {
			// Nothing to restore.
		}

		return true;
	}

	window.armvwInitTabs = armvwInitTabs;
})(window, document);
