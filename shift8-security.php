<?php
/**
 * Plugin Name: Shift8 Security
 * Plugin URI: https://github.com/stardothosting/shift8-security
 * Description: Plugin that implements several security measures to block, obfuscate or otherwise make more difficult for probes and automated scanners to enumerate plugins and expose any unforeseen vulnerabilities as a result.
 * Version: 1.01
 * Author: Shift8 Web 
 * Author URI: https://www.shift8web.ca
 * License: GPLv3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once(plugin_dir_path(__FILE__).'vendor/autoload.php');
require_once(plugin_dir_path(__FILE__).'components/enqueuing.php' );
require_once(plugin_dir_path(__FILE__).'components/settings.php' );
require_once(plugin_dir_path(__FILE__).'components/functions.php' );
require_once(plugin_dir_path(__FILE__).'shift8-security-rules.php' );
require_once(plugin_dir_path(__FILE__).'components/S8Sec_Environment.php' );
require_once(plugin_dir_path(__FILE__).'components/S8Sec_Rule.php' );
require_once(plugin_dir_path(__FILE__).'components/S8Sec_GoogleAuthenticator.php' );
require_once(plugin_dir_path(__FILE__).'components/S8Sec_2FA.php' );
require_once(plugin_dir_path(__FILE__).'components/admin.php' );