<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Initialize only if enabled
if (shift8_security_check_enabled()) {
    add_action('init', 'shift8_security_init', 1);
    add_action('admin_init', 'shift8_security_loaded');
}

// Function to initialize & check for session 
function shift8_security_init() {
    $die_now = false;
    // Initiate logic only if enabled
    if (shift8_security_check_options()) {
        // Get all options configured as array
        $shift8_options = shift8_security_check_options();

        // Logic not dependent on web service used
        if($shift8_options['wpscan_basic'] == 'on') {

            // Get current URL
            $current_url = rtrim($_SERVER['REQUEST_URI'], '/');

            // Remove unwanted headers
            header_remove('X-Powered-By');
            header_remove('X-Pingback');
  
            // Disable XMLRPC
            add_filter('bloginfo_url', function($output, $property){
                return ($property == 'pingback_url') ? null : $output;
            }, 11, 2);
            add_filter( 'xmlrpc_enabled', '__return_false' );
            if ( strpos($current_url, '/xmlrpc.php') !== false ) {
                http_response_code(404);
                $die_now = true;
            }

            // Disable wp-admin/install.php
            wp_delete_file( WP_ROOT_DIR . '/wp-admin/install.php');

            // Disable wp-admin/upgrade.php
            if ( strpos($current_url, '/wp-admin/upgrade.php') !== false ) {
                http_response_code(404);
                $die_now = true;
            }

            // Disable RSS Feed
            add_action('do_feed', 'shift8_security_disable_feed', 1);
            add_action('do_feed_rdf', 'shift8_security_disable_feed', 1);
            add_action('do_feed_rss', 'shift8_security_disable_feed', 1);
            add_action('do_feed_rss2', 'shift8_security_disable_feed', 1);
            add_action('do_feed_atom', 'shift8_security_disable_feed', 1);
            add_action('do_feed_rss2_comments', 'shift8_security_disable_feed', 1);
            add_action('do_feed_atom_comments', 'shift8_security_disable_feed', 1);

            // Disable emoji's
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' ); 
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
            add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );

            // Remove META generator tags
            remove_action( 'wp_head', 'wp_generator' );
            remove_action( 'opml_head', 'the_generator' );
            add_filter('the_generator','shift8_security_remove_wp_version_rss');

            // Remove ver parameter from all enqueued CSS and JS files
            add_filter( 'style_loader_src', 'shift8_security_remove_wp_ver_css_js', 10, 2);
            add_filter( 'script_loader_src', 'shift8_security_remove_wp_ver_css_js', 10, 2);


        }
    }

    // If die_now is triggered, die
    if ($die_now) die();
}

// Functions for web service dependent security 
function shift8_security_loaded() {
    if (shift8_security_check_options()) {
        // Get all options configured as array
        $shift8_options = shift8_security_check_options();

        // Get current URL
        $current_url = rtrim($_SERVER['REQUEST_URI'], '/');
        
        // Webserver based logic for rule implementation
        $webserver = Util_Environment::which_webserver();
        
        switch ($webserver) {
            case 'apache':
            case 'litespeed':
                break;
            case 'nginx':
                // WPScan Basic
                if($shift8_options['wpscan_basic'] == 'on') { }

                // WPScan Plugin Enumeration
                if($shift8_options['wpscan_eap'] == 'on') {
                    if(!Util_Environment::url_check(S8SEC_TEST_README_URL)) {
                        var_dump('test');
                        exit(0);
                        die();
                        Util_Environment::admin_notice($current_url, false, 'notice-error', '
                            Nginx is not configured to block or obfuscate WPScan plugin enumeration. Please add the following to your NGINX configuration : 
                            <pre>
                            location ~* ^/wp-content/plugins/.+\.(txt|log|md)$ {
                                deny all;
                                error_page 403 =404 / ;
                            }
                            </pre>
                            ');
                    } else {
                        Util_Environment::admin_notice($current_url, true, 'notice-success', 'NGINX is setup correctly for WPScan plugin enumeration.');
                    }
                }
                break;
            case 'iis':

                break;
        }
    }
}

// Message to display if RSS feed is accessed when disabled
function shift8_security_disable_feed() {
    wp_die( __( 'No feed available, please visit the <a href="'. esc_url( home_url( '/' ) ) .'">homepage</a>!' ) );
}

// Remove wp version param from any enqueued scripts
function shift8_security_remove_wp_ver_css_js( $src ) {
    //if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}

function shift8_security_remove_wp_version_rss() {
    return '';
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