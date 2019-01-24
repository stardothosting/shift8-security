<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// create custom plugin settings menu
add_action('admin_menu', 'shift8_security_create_menu');
function shift8_security_create_menu() {
        //create new top-level menu
        if ( empty ( $GLOBALS['admin_page_hooks']['shift8-settings'] ) ) {
                add_menu_page('Shift8 Settings', 'Shift8', 'administrator', 'shift8-settings', 'shift8_main_page' , 'dashicons-building' );
        }
        add_submenu_page('shift8-settings', 'Security Settings', 'Security Settings', 'manage_options', __FILE__.'/custom', 'shift8_security_settings_page');
        //call register settings function
        add_action( 'admin_init', 'register_shift8_security_settings' );
}

// Register admin settings
function register_shift8_security_settings() {
    //register our settings
    register_setting( 'shift8-security-settings-group', 'shift8_security_enabled' );
    register_setting( 'shift8-security-settings-group', 'shift8_security_2fa_enabled' );
    register_setting( 'shift8-security-settings-group', 'shift8_security_wpscan_basic' );
    register_setting( 'shift8-security-settings-group', 'shift8_security_wpscan_eap' );
    // 2FA Settings
    register_setting( 'shift8-security-settings-group', 'shift8_security_2fa_description', 'shift8_security_2fa_description_validate' );
    register_setting( 'shift8-security-settings-group', 'shift8_security_2fa_secret' );
}

function shift8_security_2fa_description_validate($data){
    if(preg_match('/^[a-zA-Z]+[a-zA-Z0-9._]+$/', $data)) {
        return $data;
    } else {
        add_settings_error(
            'shift8_security',
            'shift8-security-notice',
            'You cannot enter special characters for the description field',
            'error');
    }
}