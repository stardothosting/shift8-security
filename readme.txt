=== Shift8 Security ===
* Contributors: shift8
* Donate link: https://www.shift8web.ca
* Tags: security, probe, scan, wpscan, block wpscan, block scan, scanner block, block probe, 2fa, FreeOTP, FOTP
* Requires at least: 3.0.1
* Tested up to: 5.2.2
* Stable tag: 1.01
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

Plugin that implements several measures to generally improve the security of your Wordpress site. At this point security scan obfuscation of core Wordpress versions as well as plugin version enumeration are implemented.

== Want to see the plugin in action? ==

You can view three example sites where this plugin is live :

- Example Site 1 : [Wordpress Hosting](https://www.stackstar.com "Wordpress Hosting")
- Example Site 2 : [Web Design in Toronto](https://www.shift8web.ca "Web Design in Toronto")

= Features =

- Restricts or blocks the availability of plugin readme files for enumeration & version detection

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/shif8-geoip` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the plugin settings page and define your settings
3. Once enabled, the system should trigger for every site visit.

== Frequently Asked Questions ==

= I tested it on myself and its not working for me! =

Try clearing all cookies and re-visit the website. 

== Screenshots ==

1. Admin area 

== Changelog ==

= 1.0 =
* Stable version created

= 1.01 =
* Added functions to block WPScan, added Google Authenticator precursor code
