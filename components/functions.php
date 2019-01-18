<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Function to initialize & check for session 
function shift8_security_init() {
    $die_now = false;
    // Initiate logic only if enabled
    if (shift8_security_check_options()) {
        // Get all options configured as array
        $shift8_options = shift8_security_check_options();

        // Logic not dependent on web service used
        if($shift8_options['wpscan_basic'] == 'on') {
            // Remove unwanted headers
            header_remove('X-Powered-By');
            header_remove('X-Pingback');
  
            // Disable XMLRPC
            add_filter('bloginfo_url', function($output, $property){
                return ($property == 'pingback_url') ? null : $output;
            }, 11, 2);
            add_filter( 'xmlrpc_enabled', '__return_false' );
            $current_url = rtrim($_SERVER['REQUEST_URI'], '/');
            if ( strpos($current_url, '/xmlrpc.php') !== false ) {
                http_response_code(404);
                $die_now = true;
            }
        }

        // Webserver based logic for rule implementation
        $webserver = Util_Environment::which_webserver();

        switch ($webserver) {
            case 'apache':
            case 'litespeed':
                break;
            case 'nginx':
                if($shift8_options['wpscan_basic'] == 'on') { }
                if($shift8_options['wpscan_eap'] == 'on') {
                    if(Util_Environment::url_exists(S8SEC_TEST_README_URL)) {
                        Util_Environment::admin_notice($current_url, '
                            Nginx is not configured to block or obfuscate WPScan plugin enumeration.
                            <pre>
                            code header_remove
                            </pre>
                            ');
                    }
                }
                break;
            case 'iis':

                break;
        }
    }

    if ($die_now) {
        die();
    }
}

// Initialize only if enabled
if (shift8_security_check_enabled()) {
    add_action('init', 'shift8_security_init', 1);
}

// Validate admin options
function shift8_security_check_enabled() {
    // If enabled is not set 
    if(esc_attr( get_option('shift8_security_enabled') ) != 'on') return false;
    // If none of the above conditions match, return true
    return true;
}

// Process all options and return array
function shift8_security_check_options() {
    $shift8_options = array();
    $shift8_options['core_enabled'] = esc_attr( get_option('shift8_security_enabled') );
    $shift8_options['wpscan_basic'] = esc_attr( get_option('shift8_security_wpscan_basic') );
    $shift8_options['wpscan_eap'] = esc_attr( get_option('shift8_security_wpscan_eap') );

    return $shift8_options;
}