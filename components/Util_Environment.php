<?php
class Util_Environment {
    
    /**
     * Check if URL is valid
     *
     * @param string  $url
     * @return boolean
     */
    static public function is_url( $url ) {
        return preg_match( '~^(https?:)?//~', $url );
    }

    /**
     * Returns true if current connection is secure
     *
     * @return boolean
     */
    static public function is_https() {
        switch ( true ) {
        case ( isset( $_SERVER['HTTPS'] ) &&
                Util_Environment::to_boolean( $_SERVER['HTTPS'] ) ):
        case ( isset( $_SERVER['SERVER_PORT'] ) &&
                (int) $_SERVER['SERVER_PORT'] == 443 ):
        case ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ):
            return true;
        }

        return false;
    }

    /**
     * Returns true if server is Apache
     *
     * @return boolean
     */
    static public function is_apache() {
        // assume apache when unknown, since most common
        if ( empty( $_SERVER['SERVER_SOFTWARE'] ) )
            return true;

        return isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false;
    }

    /**
     * Check whether server is LiteSpeed
     *
     * @return bool
     */
    static public function is_litespeed() {
        return isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false;
    }

    /**
     * Returns true if server is nginx
     *
     * @return boolean
     */
    static public function is_nginx() {
        return isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false;
    }

    /**
     * Returns true if server is nginx
     *
     * @return boolean
     */
    static public function is_iis() {
        return isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'IIS' ) !== false;
    }

    /**
     * Returns true when it discovers which web service is running
     *
     * @return boolean
     */
    static public function which_webserver() {
        if (isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'IIS' ) !== false) return 'iis';
        if (isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false) return 'nginx';
        if (isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false) return 'litespeed';
        if (isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false) return 'apache';
        return false;
    }
    /**
     * Returns domain from host
     *
     * @param string  $host
     * @return string
     */
    static public function url_to_host( $url ) {
        $a = parse_url( $url );
        if ( isset( $a['host'] ) )
            return $a['host'];

        return '';
    }

    /**
     * Returns path from URL. Without trailing slash
     */
    static public function url_to_uri( $url ) {
        $uri = @parse_url( $url, PHP_URL_PATH );

        // convert FALSE and other return values to string
        if ( empty( $uri ) )
            return '';

        return rtrim( $uri, '/' );
    }

    /**
     * Returns URL regexp from URL
     *
     * @param string  $url
     * @return string
     */
    static public function get_url_regexp( $url ) {
        $url = preg_replace( '~(https?:)?//~i', '', $url );
        $url = preg_replace( '~^www\.~i', '', $url );

        $regexp = '(https?:)?//(www\.)?' . Util_Environment::preg_quote( $url );

        return $regexp;
    }

    /**
     * Copy of wordpress get_home_path, but accessible not only for wp-admin
     * Get the absolute filesystem path to the root of the WordPress installation
     * (i.e. filesystem path of siteurl)
     *
     * @return string Full filesystem path to the root of the WordPress installation
     */
    static public function site_path() {
        $home    = set_url_scheme( get_option( 'home' ), 'http' );
        $siteurl = set_url_scheme( get_option( 'siteurl' ), 'http' );

        $home_path = ABSPATH;
        if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
            $wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
            $pos = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
            // fix of get_home_path, used when index.php is moved outside of
            // wp folder.
            if ( $pos !== false ) {
                $home_path = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
                $home_path = trailingslashit( $home_path );
            }
        }

        return str_replace( '\\', DIRECTORY_SEPARATOR, $home_path );
    }

    /**
     * Returns absolute path to document root
     *
     * No trailing slash!
     *
     * @return string
     */
    static public function document_root() {
        static $document_root = null;

        if ( !is_null( $document_root ) )
            return $document_root;

        if ( !empty( $_SERVER['SCRIPT_FILENAME'] ) &&
            !empty( $_SERVER['PHP_SELF'] ) ) {
            $script_filename = Util_Environment::normalize_path(
                $_SERVER['SCRIPT_FILENAME'] );
            $php_self = Util_Environment::normalize_path(
                $_SERVER['PHP_SELF'] );
            if ( substr( $script_filename, -strlen( $php_self ) ) == $php_self ) {
                $document_root = substr( $script_filename, 0, -strlen( $php_self ) );
                $document_root = realpath( $document_root );
                return $document_root;
            }
        }

        if ( !empty( $_SERVER['PATH_TRANSLATED'] ) ) {
            $document_root = substr(
                Util_Environment::normalize_path( $_SERVER['PATH_TRANSLATED'] ),
                0,
                -strlen( Util_Environment::normalize_path( $_SERVER['PHP_SELF'] ) ) );
        } elseif ( !empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
            $document_root = Util_Environment::normalize_path( $_SERVER['DOCUMENT_ROOT'] );
        } else {
            $document_root = ABSPATH;
        }

        $document_root = realpath( $document_root );
        return $document_root;
    }

    /**
     * Returns absolute path to blog install dir
     *
     * Example:
     *
     * DOCUMENT_ROOT=/var/www/vhosts/domain.com
     * install dir=/var/www/vhosts/domain.com/site/blog
     * return /var/www/vhosts/domain.com/site/blog
     *
     * No trailing slash!
     *
     * @return string
     */
    static public function site_root() {
        $site_root = ABSPATH;
        $site_root = realpath( $site_root );
        $site_root = Util_Environment::normalize_path( $site_root );

        return $site_root;
    }

    /**
     * Returns blog path
     *
     * Example:
     *
     * siteurl=http://domain.com/site/blog
     * return /site/blog/
     *
     * With trailing slash!
     *
     * @return string
     */
    static public function site_url_uri() {
        return Util_Environment::url_to_uri( site_url() ) . '/';
    }

    /**
     * Removes all query strings from url
     */
    static public function remove_query_all( $url ) {
        $pos = strpos( $url, '?' );
        if ( $pos === false )
            return $url;

        return substr( $url, 0, $pos );
    }

    /**
     * Quotes regular expression string
     *
     * @param string  $string
     * @param string  $delimiter
     * @return string
     */
    static public function preg_quote( $string, $delimiter = '~' ) {
        $string = preg_quote( $string, $delimiter );
        $string = strtr( $string, array(
                ' ' => '\ '
            ) );

        return $string;
    }

    /**
     * Returns the apache, nginx version
     *
     * @return string
     */
    static public function get_server_version() {
        $sig= explode( '/', $_SERVER['SERVER_SOFTWARE'] );
        $temp = isset( $sig[1] ) ? explode( ' ', $sig[1] ) : array( '0' );
        $version = $temp[0];
        return $version;
    }
}
