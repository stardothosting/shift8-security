<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Encryption key generation
function shift8_security_generate_secret() {
    $encryption_key = bin2hex(openssl_random_pseudo_bytes(32));
    return $encryption_key;
}

// Callback for key regeneration
function shift8_security_ajax_process_request() {
    if (wp_verify_nonce($_GET['_wpnonce'], 'shift8-security-process') && $_GET['action'] == 'shift8_security_response') {
        $shift8_options = shift8_security_check_options();
        if (!empty($shift8_options['2fa_description'])) {
            $shift8_2fa = new S8Sec_2FA();
            $new_generate = $shift8_2fa->generate();
            echo $new_generate;
            die();
        } else {
            // To do : add error message when description field is not filled in
            die();
        }
    } else {
        die();
    }
}
add_action('wp_ajax_shift8_security_response', 'shift8_security_ajax_process_request');

// Initialize only if enabled
if (shift8_security_check_enabled()) {
    if (shift8_security_check_options()) {
        $shift8_options = shift8_security_check_options();

        add_action('init', 'shift8_security_init', 1);
        add_action('admin_init', 'shift8_security_loaded');
        
        // Implement 2FA form
        if($shift8_options['2fa_enabled'] == 'on') {
            //add_action('login_form','shift8_security_2fa_login_field');
            //add_filter( 'authenticate', 'shift8_security_2fa_authenticate', 10, 3 );         
        }
    }
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
        $webserver = S8Sec_Environment::which_webserver();
        
        switch ($webserver) {
            case 'apache':
            case 'litespeed':
                break;
            case 'nginx':
                // WPScan Basic
                if($shift8_options['wpscan_basic'] == 'on') { }

                // WPScan Plugin Enumeration
                if($shift8_options['wpscan_eap'] == 'on') {
                    if(!S8Sec_Environment::url_check(S8SEC_TEST_README_URL)) {
                        S8Sec_Environment::admin_notice($current_url, false, 'notice-error', '
                            Nginx is not configured to block or obfuscate WPScan plugin enumeration. Please add the following to your NGINX configuration : 
                            <pre>
                            location ~* ^/wp-content/plugins/.+\.(txt|log|md)$ {
                                deny all;
                                error_page 403 =404 / ;
                            }
                            </pre>
                            ');
                    } else {
                        S8Sec_Environment::admin_notice($current_url, true, 'notice-success', 'NGINX is setup correctly for WPScan plugin enumeration.');
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
    if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}

function shift8_security_remove_wp_version_rss() {
    return '';
}

function shift8_security_2fa_login_field(){
    //Output your HTML
?>
    <p>
        <label for="my_extra_field">Google Authenticator Code<br>
        <input type="text" tabindex="20" size="20" value="" class="input" id="googlecode" name="shift8_security_2fa"></label>
    </p>
<?php
}


function shift8_security_2fa_authenticate( $user, $username, $password ){

    $shift8_2fa = new S8Sec_2FA();
    
    //Get user object
    $user = get_user_by('login', $username );

    //Get POSTED value
    $shift8_2fa_value = esc_attr($_POST['shift8_security_2fa']);

    //exit(0);
    //die();

    echo "<center><img src='" . $shift8_2fa->generate_qr() . "'></center>";
    var_dump($shift8_2fa->generate_qr());
    //var_dump($shift8_2fa->validate_code($shift8_2fa_value));
    //var_dump($shift8_2fa_gen['secret']);


    if(!$user || empty($shift8_2fa_value) || $oneCode != $shift8_2fa_value || empty($oneCode)){
        //User note found, or no value entered or doesn't match stored value - don't proceed.
        remove_action('authenticate', 'wp_authenticate_username_password', 20);
        remove_action('authenticate', 'wp_authenticate_email_password', 20); 

        //Create an error to return to user
        return new WP_Error( 'denied', __("<strong>ERROR</strong>: You're unique identifier was invalid.") );
    }

    //Make sure you return null 
    return null;
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
    $shift8_options['2fa_enabled'] = esc_attr( get_option('shift8_security_2fa_enabled') );
    $shift8_options['2fa_description'] = esc_attr( get_option('shift8_security_2fa_description') );
    $shift8_options['2fa_secret'] = esc_attr( get_option('shift8_security_2fa_secret') );
    $shift8_options['wpscan_basic'] = esc_attr( get_option('shift8_security_wpscan_basic') );
    $shift8_options['wpscan_eap'] = esc_attr( get_option('shift8_security_wpscan_eap') );

    return $shift8_options;
}