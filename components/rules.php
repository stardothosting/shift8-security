<?php

if ( !defined( 'ABSPATH' ) ) {
    die();
}

if ( !defined( 'S8S_DIR' ) )
    define( 'S8S_DIR', realpath( dirname( __FILE__ ) ) );

if ( !defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', realpath( S8S_DIR . '/../..' ) );

define( 'S8S_MARKER_BEGIN_WORDPRESS', '# BEGIN WordPress' );
define( 'S8S_MARKER_END_WORDPRESS', '# END WordPress' );

// Apache WPScan Basic
define( 'S8S_MARKER_BEGIN_WPSCAN_BASIC', '# BEGIN S8S' );
//RewriteRule \.(txt|htaccess)$ http://www.my-domain.com [R,L]