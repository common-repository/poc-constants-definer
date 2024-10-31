<?php
/**
 * POC Constants Definer admin
 *
 * @author      Pinch Of Code <info@pinchofcode.com>
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // If this file is called directly, abort.

if( !class_exists( 'POC_CD_Admin' ) ) :

/**
 * @class       POC_CD_Admin
 */
class POC_CD_Admin {
    /**
     * Initial Options definition:
     *
     * @var array
     */
    public $options = array();

    /**
     * __construct method
     */
    public function __construct() {
        include_once( POC_CD()->plugin_dir . '/shared-inc/class-poc-panel.php' );

        add_action( 'init', array( $this, 'init_panel' ) );
        add_action( 'init', array( $this, 'default_options' ) );

        add_action( 'init', array( $this, 'save_config' ) );
    }

    /**
     * Init the panel
     *
     * @return void
     */
    public function init_panel() {
        $submenu = array(
            'options-general.php',
            __( 'WordPress Constants', 'poc-cd' ),
            __( 'WordPress Constants', 'poc-cd' ),
            'manage_options',
            'poc-cd'
        );

        if( empty( $this->options ) ) {
            $options    = array();
            $options[]  = include_once( POC_CD()->plugin_dir . '/includes/admin/options/general.php' );

            $this->options = $options;
        }

        new POC_Panel( $submenu, $options, 'poc-cd-group', 'poc-cd' );
    }

    /**
     * Sets up the default options used on the settings page
     *
     * @return void
     */
    public function default_options() {
        foreach( $this->options as $tab ) {
            foreach( $tab['sections'] as $section ) {
                foreach ( $section['fields'] as $id => $value ) {
                    if ( isset( $value['default'] ) && isset( $id ) ) {
                        add_option( $id, $value['default'] );
                    }
                }
            }
        }
    }

    /**
     * Save wp-config.php new configuration.
     *
     * @return bool
     */
    public function save_config() {
        $wp_config = ABSPATH . 'wp-config.php';

        if( !file_exists( $wp_config ) || !is_writable( $wp_config ) || !isset( $_GET['settings-updated'] ) || $_GET['settings-updated'] !=  'true' ) { return false; }

        $consts_to_config = array(
            'WP_DEBUG'                      => get_option( 'poc_cd_wp_debug' ),
            'WP_DEBUG_LOG'                  => get_option( 'poc_cd_wp_debug_log' ),
            'WP_DEBUG_DISPLAY'              => get_option( 'poc_cd_wp_debug_display' ),
            'SCRIPT_DEBUG'                  => get_option( 'poc_cd_script_debug' ),
            'SAVEQUERIES'                   => get_option( 'poc_cd_savequeries' ),
            'WP_POST_REVISIONS'             => get_option( 'poc_cd_wp_post_revisions' ),
            'CONCATENATE_SCRIPTS'           => get_option( 'poc_cd_concatenate_scripts' ),
            'DISALLOW_FILE_MODS'            => get_option( 'poc_cd_disallow_file_mods' ),
            'DISALLOW_FILE_EDIT'            => get_option( 'poc_cd_disallow_file_edit' ),
            'AUTOMATIC_UPDATER_DISABLED'    => get_option( 'poc_cd_automatic_updater_disabled' ),
        );

        $wp_config_content = file_get_contents( $wp_config );

        /* Return all lines containing 'define' statements in wp-config.php. */
        preg_match_all( '/^.*\bdefine\b.*$/im', $wp_config_content, $matches );

        /* Turn $matches array into string for further preg_match() calls. */
        $matches_str = implode( $matches[0] );

        foreach( $consts_to_config as $const => $new_val ) {
            $found = $this->_search_array( $matches[0], $const );

            //If the
            if( $found !== false ) {
                $new_value = filter_var( get_option( 'poc_cd_' . strtolower( $const ) ), FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false';
                $new_const = str_replace( array( 'true', 'false' ), $new_value, trim( $matches[0][$found] ) );

                $wp_config_content = str_replace( trim( $matches[0][$found] ), $new_const, $wp_config_content );
            } elseif( get_option( 'poc_cd_add_if_not_exists' ) == 'on' ) {
                //If the constant does not exist, add it at the end of the file
                $wp_config_content = preg_replace(
                    '~<\?(php)?~',
                    "\\0\r\ndefine('" . $const . "', " . ( filter_var( get_option( 'poc_cd_' . strtolower( $const ) ), FILTER_VALIDATE_BOOLEAN ) ? 'true' : 'false' ) . ");",
                    $wp_config_content,
                    1
                );
            }
        }

        /* Update wp-config.php. */
        file_put_contents( $wp_config, $wp_config_content );
    }

    /**
     * Search part of a string in an array values
     *
     * @param array $array
     * @param string $term
     * @return mixed
     * @access protected
     */
    protected function _search_array ( array $array, $term ) {
        foreach ( $array as $key => $value )
            if ( strpos( $value, $term ) !== false )
                return $key;

        return false;
    }
}

endif;

return new POC_CD_Admin();
