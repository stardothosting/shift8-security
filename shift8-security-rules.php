<?php

if ( !defined( 'ABSPATH' ) ) {
    die();
}

define( 'S8SEC_FILE', 'shift8-security/shift8-security.php' );

if ( !defined( 'S8SEC_DIR' ) )
    define( 'S8SEC_DIR', realpath( dirname( __FILE__ ) ) );

if ( !defined( 'S8SEC_TEST_README_URL' ) )
define( 'S8SEC_TEST_README_URL', WP_PLUGIN_URL . '/' . dirname( S8SEC_FILE ) . '/readme.txt');

if ( !defined( 'S8SEC_TEST_XMLRPC_URL' ) )
define( 'S8SEC_TEST_XMLRPC_URL', set_url_scheme( get_option( 'siteurl' ), 'http' ) . '/xmlrpc.php');


define( 'S8SEC_MARKER_BEGIN_WORDPRESS', '# BEGIN WordPress' );
define( 'S8SEC_MARKER_END_WORDPRESS', '# END WordPress' );

// Apache WPScan Basic
define( 'S8SEC_MARKER_BEGIN_WPSCAN_BASIC', '# BEGIN S8SEC' );
//RewriteRule \.(txt|htaccess)$ http://www.my-domain.com [R,L]