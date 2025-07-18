=== YITH Activator ===
Contributors: GPL Plugins
Tags: yith, activator, license
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 4.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==
YITH Activator is a universal license activator for all YITH plugins developed by GPL Plugins. It intercepts license activation requests and returns a custom response that simulates an active license with extended activation limits.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/yith-activator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The plugin will automatically intercept and simulate YITH license activations.

== Changelog ==

= 4.1 =
* Bug fixes

= 4.0 =
* Added a filter to intercept HTTP requests made to the YITH license API.
* Customizes responses to simulate 900 out of 999 activations remaining, with an activation limit of 999.
* Sets license expiration to 5 years from the current timestamp.
* Introduces the `YITH_Activator` class to:
  * Disable YITH license activation redirects.
  * Override YITH onboarding queues.
  * Ensure the plugin runs only in the admin area.

= 3.0 =
* Initial release of the YITH Activator plugin.