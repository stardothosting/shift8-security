<?php

// Function to initialize & check for session 
function shift8_security_init() {
    $current_url = rtrim($_SERVER['REQUEST_URI'], '/');
    if ( strpos($current_url, '/admin-ajax.php') !== false ) {

  	    if (isset($_POST['data']['visible_pages']) && isset($_POST['data']['page_id']) && isset($_POST['data']['style']) && isset($_POST['data']['action']) && isset($_POST['data']['shortcode_id']) && isset($_POST['data']['tag']) && !isset($_POST['shift8-ajax'])) { 
    		$vc_id = $_POST['visible_pages'] . $_POST['page_id'] . $_POST['style'] . $_POST['action'] .  $_POST['shortcode_id'] . $_POST['tag'];
            $post_vars = $_POST;
        	$cache_file = plugin_dir_path(__FILE__)."cache/".hash('md5', $vc_id).".html"; 
    
            // If the file exists and was cached in the last 24 hours...
            if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 86400 ))) { 
                $file = file_get_contents($cache_file); // Get the file from the cache.
                echo $file; // echo the file out to the browser.
            } else {
                $post_vars['shift8-ajax'] = true;
                $result = wp_remote_post( 'http://shift8.local/wp-admin/admin-ajax.php',
                    array(
						'method' => 'POST',
						'timeout' => 45,
						'redirection' => 5,
						'httpversion' => '1.0',
						'blocking' => true,
						'headers' => array(),
                        'body' => $post_vars,
						'cookies' => array()
                    )
                );
				if ( is_wp_error( $result ) ) {
					$error_message = $response->get_error_message();
					echo "Something went wrong: $error_message";
				} else {
					$result_body = wp_remote_retrieve_body($result);
					file_put_contents($cache_file, $result_body, LOCK_EX);
					echo $result_body;
				}
            }
        } 
    }
}

// Initialize only if enabled
if (shift8_security_check_options()) {
    add_action('init', 'shift8_security_init', 1);
}

// Validate admin options
function shift8_security_check_options() {
    // If enabled is not set 
    if(esc_attr( get_option('shift8_security_enabled') ) != 'on') return false;
    // If none of the above conditions match, return true
    return true;
}
