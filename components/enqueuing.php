<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Register admin scripts for custom fields
function load_shift8_security_wp_admin_style() {
        // admin always last
        wp_enqueue_style( 'shift8_security_css', plugin_dir_url(dirname(__FILE__)) . 'css/shift8_security_admin.css' );
        wp_enqueue_script( 'shift8_security_script', plugin_dir_url(dirname(__FILE__)) . 'js/shift8_security_admin.js' );
        wp_localize_script( 'shift8_security_script', 'the_ajax_script', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( "shift8_security_response_nonce"),
        ));  
}
add_action( 'admin_enqueue_scripts', 'load_shift8_security_wp_admin_style' );
