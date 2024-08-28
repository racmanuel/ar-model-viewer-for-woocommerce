=== AR Model Viewer for WooCommerce ===
Contributors: racmanuel
Donate link: https://racmanuel.dev 
Tags: Augmented Reality, AR, Model Viewer, 3D, Woocommerce
Requires at least: 5.9
Tested up to: 6.6
Stable tag: 1.0.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The AR Model Viewer for WooCommerce plugin shows 3D models on your website and in augmented reality. Supports .glb and .gltf files.

== Description ==

The AR Model Viewer for WooCommerce is a versatile plugin designed to enhance your online store by displaying 3D models of your products. Customers can view these models in augmented reality (AR), providing an interactive and immersive shopping experience. The plugin supports 3D model files in .glb and .gltf formats and is incredibly easy to use.

Whether your website is an eCommerce platform or a WooCommerce-based store, the AR Model Viewer plugin seamlessly integrates to allow customers to explore your products in 3D and AR.

## Key Features ✨

- Display 3D models of your products.
- Enable augmented reality viewing.
- Support for .glb and .gltf file formats.
- Easy integration with WooCommerce.

## Customization 🛠️

If you need any customization for the plugin, feel free to send me a message or visit my website: [https://racmanuel.dev](https://racmanuel.dev).

## Privacy Policy 🔒

AR Model Viewer for WooCommerce uses the Freemius SDK to collect telemetry data, but only with the user's explicit consent. This data collection helps us troubleshoot issues and improve our product.

- **No data is gathered by default.**
- Data collection only begins **after user consent via the admin notice**.
- Collected data ensures a great user experience.

Integrating the Freemius SDK **does not immediately start data collection without user confirmation**.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)

== Frequently Asked Questions ==

= Which file types are required for the 3D models/scenes? =
GLTF (*.glb) files are required for the 3D preview in the browser and the AR scenes on Android devices. Apple devices, such as iPhone, iPad or Apple Vision Pro, on the other hand, require **USDZ** (*.usdz) files to display AR content. AR Model Viewer for WooCommerce also adds support for **Reality** (*.reality) files for Apple devices.

= Do I have to add models for Android and iOS? =
To ensure that all visitors to your website can see the AR scenes, each scene must have a dedicated model in both formats (.glb, .usdz) for Android and iOS/Apple devices. However, only a GLTF file is required for the 3D preview in the browser.

== Screenshots ==


== Changelog ==

1.0.5 - Update the tested up to 6.5 and added Appsero
1.0.6 - Update the tested up to 6.5 and added Appsero
1.0.7 - Removed the Appsero SDK and add Freemius SDK, and Tested up to: 6.6 version of WordPress, removed uninstall.php, and on deactive plugin delete_option('ar_model_viewer_for_woocommerce_settings');

== Upgrade Notice ==
