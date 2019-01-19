<?php
class S8Sec_Rule {

    /**
     * Removes empty elements
     */
    static public function array_trim( &$a ) {
        for ( $n = count( $a ) - 1; $n >= 0; $n-- ) {
            if ( empty( $a[$n] ) )
                array_splice( $a, $n, 1 );
        }
    }

    /**
     * Returns nginx rules path
     *
     * @return string
     */
    static public function get_nginx_rules_path() {
        $config = Dispatcher::config();

        $path = $config->get_string( 'config.path' );

        if ( !$path ) {
            $path = S8Sec_Environment::site_path() . 'nginx.conf';
        }

        return $path;
    }

    /**
     * Returns path of wordpress root rules file
     *
     * @return string
     */
    static public function get_wproot_path() {
        switch ( true ) {
        case S8Sec_Environment::is_apache():
        case S8Sec_Environment::is_litespeed():
            return S8Sec_Environment::content_path() . '.htaccess';

        case S8Sec_Environment::is_nginx():
            return S8Sec_Rule::get_nginx_rules_path();
        }

        return false;
    }

    /**
     * Returns path of wordpress content directory rules file
     *
     * @return string
     */
    static public function get_wpcontent_path() {
        switch ( true ) {
        case S8Sec_Environment::is_apache():
        case S8Sec_Environment::is_litespeed():
            return S8Sec_Environment::content_path() . '.htaccess';

        case S8Sec_Environment::is_nginx():
            return S8Sec_Rule::get_nginx_rules_path();
        }

        return false;
    }

   /**
     * Trim rules
     *
     * @param string  $rules
     * @return string
     */
    static public function trim_rules( $rules ) {
        $rules = trim( $rules );

        if ( $rules != '' ) {
            $rules .= "\n";
        }

        return $rules;
    }

    /**
     * Cleanup rewrite rules
     *
     * @param string  $rules
     * @return string
     */
    static public function clean_rules( $rules ) {
        $rules = preg_replace( '~[\r\n]+~', "\n", $rules );
        $rules = preg_replace( '~^\s+~m', '', $rules );
        $rules = S8Sec_Rule::trim_rules( $rules );

        return $rules;
    }

    /**
     * Erases text from start to end
     *
     * @param string  $rules
     * @param string  $start
     * @param string  $end
     * @return string
     */
    static public function erase_rules( $rules, $start, $end ) {
        $r = '~' . S8Sec_Environment::preg_quote( $start ) . "\n.*?" . S8Sec_Environment::preg_quote( $end ) . "\n*~s";

        $rules = preg_replace( $r, '', $rules );
        $rules = S8Sec_Rule::trim_rules( $rules );

        return $rules;
    }

    /**
     * Check if rules exist
     *
     * @param string  $rules
     * @param string  $start
     * @param string  $end
     * @return int
     */
    static public function has_rules( $rules, $start, $end ) {
        return preg_match( '~' . S8Sec_Environment::preg_quote( $start ) . "\n.*?" . S8Sec_Environment::preg_quote( $end ) . "\n*~s", $rules );
    }

  /**
     *
     *
     * @param S8Sec_Environment_Exceptions $exs
     * @param string  $path
     * @param string  $rules
     * @param string  $start
     * @param string  $end
     * @param array   $order
     */
    static public function add_rules( $exs, $path, $rules, $start, $end, $order ) {
        if ( empty( $path ) )
            return;

        $data = @file_get_contents( $path );

        if ( $data === false )
            $data = '';

        if ( empty( $rules ) ) {
            $rules_present = ( strpos( $data, $start ) !==  false );
            if ( !$rules_present )
                return;
        } else {
            $rules_missing = ( strstr( S8Sec_Rule::clean_rules( $data ), S8Sec_Rule::clean_rules( $rules ) ) === false );
            if ( !$rules_missing )
                return;
        }

        $replace_start = strpos( $data, $start );
        $replace_end = strpos( $data, $end );

        if ( $replace_start !== false && $replace_end !== false && $replace_start < $replace_end ) {
            $replace_length = $replace_end - $replace_start + strlen( $end ) + 1;
        } else {
            $replace_start = false;
            $replace_length = 0;

            $search = $order;

            foreach ( $search as $string => $length ) {
                $replace_start = strpos( $data, $string );

                if ( $replace_start !== false ) {
                    $replace_start += $length;
                    break;
                }
            }
        }

        if ( $replace_start !== false ) {
            $data = S8Sec_Rule::trim_rules( substr_replace( $data, $rules, $replace_start, $replace_length ) );
        } else {
            $data = S8Sec_Rule::trim_rules( $data . $rules );
        }

        if ( strpos( $path, W3TC_CACHE_DIR ) === false || S8Sec_Environment::is_nginx() ) {
            try {
                S8Sec_WpFile::write_to_file( $path, $data );
            } catch ( S8Sec_WpFile_FilesystemOperationException $ex ) {
                if ( $replace_start !== false )
                    $exs->push( new S8Sec_WpFile_FilesystemModifyException(
                            $ex->getMessage(), $ex->credentials_form(),
                            sprintf( __( 'Edit file <strong>%s
                            </strong> and replace all lines between and including <strong>%s</strong> and
                            <strong>%s</strong> markers with:', 'w3-total-caceh' ), $path, $start, $end ), $path, $rules ) );
                else
                    $exs->push( new S8Sec_WpFile_FilesystemModifyException(
                            $ex->getMessage(), $ex->credentials_form(),
                            sprintf( __( 'Edit file <strong>%s</strong> and add the following rules
                                    above the WordPress directives:', 'w3-total-cache' ),
                                $path ), $path, $rules ) );
                return;
            }
        } else {
            if ( !@file_exists( dirname( $path ) ) ) {
                S8Sec_File::mkdir_from( dirname( $path ), W3TC_CACHE_DIR );
            }

            if ( !@file_put_contents( $path, $data ) ) {
                try {
                    S8Sec_WpFile::delete_folder( dirname( $path ), '',
                        $_SERVER['REQUEST_URI'] );
                } catch ( S8Sec_WpFile_FilesystemOperationException $ex ) {
                    $exs->push( $ex );
                    return;
                }
            }
        }

        S8Sec_Rule::after_rules_modified();
    }

    /**
     * Remove rules
     */
    static public function remove_rules( $exs, $path, $start, $end ) {
        if ( !file_exists( $path ) )
            return;

        $data = @file_get_contents( $path );
        if ( $data === false )
            return;
        if ( strstr( $data, $start ) === false )
            return;

        $data = S8Sec_Rule::erase_rules( $data, $start,
            $end );

        try {
            S8Sec_WpFile::write_to_file( $path, $data );
        } catch ( S8Sec_WpFile_FilesystemOperationException $ex ) {
            $exs->push( new S8Sec_WpFile_FilesystemModifyException(
                    $ex->getMessage(), $ex->credentials_form(),
                    sprintf( __( 'Edit file <strong>%s</strong> and remove all lines between and including <strong>%s</strong>
                and <strong>%s</strong> markers.', 'w3-total-cache' ), $path, $start, $end ), $path ) );
        }
    }

    /**
     * Returns true if we can check rules
     *
     * @return bool
     */
    static public function can_check_rules() {
        return S8Sec_Environment::is_apache() ||
            S8Sec_Environment::is_litespeed() ||
            S8Sec_Environment::is_nginx() ||
            S8Sec_Environment::is_iis();
    }

    /**
     * Support for GoDaddy servers configuration which uses
     * SUBDOMAIN_DOCUMENT_ROOT variable
     */
    static public function apache_docroot_variable() {
        if ( isset( $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] ) &&
            $_SERVER['SUBDOMAIN_DOCUMENT_ROOT'] != $_SERVER['DOCUMENT_ROOT'] )
            return '%{ENV:SUBDOMAIN_DOCUMENT_ROOT}';
        elseif ( isset( $_SERVER['PHP_DOCUMENT_ROOT'] ) &&
            $_SERVER['PHP_DOCUMENT_ROOT'] != $_SERVER['DOCUMENT_ROOT'] )
            return '%{ENV:PHP_DOCUMENT_ROOT}';
        else
            return '%{DOCUMENT_ROOT}';
    }
}