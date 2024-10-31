<?php
/**
 * Plugin Name:       POC Constants Definer
 * Plugin URI:        https://wordpress.org/plugins/poc-constants-definer/
 * Description:       POC Constants Definer define WordPress constants quickly without change code.
 * Version:           1.0
 * Author:            Pinch Of Code
 * Author URI:        http://pinchofcode.com
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 3.5
 * Tested up to:      3.9.1
 *
 * Text Domain:       poc-cd
 * Domain Path:       /i18n
 * GitHub Plugin URI: https://github.com/PinchOfCode/poc-constants-definer
 *
 * @author      Pinch Of Code <info@pinchofcode.com>
 * @copyright   2014 Pinch Of Code
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // If this file is called directly, abort.

if( !class_exists( 'POC_Constants_Defined' ) ) :

/**
 * Main POC POC_Constants_Defined Auctions class
 *
 * @class POC_Constants_Defined
 */
final class POC_Constants_Defined {
    /**
     * Plugin latest version
     *
     * @var     string
     */
    public $version = '1.0';

    /**
     * @var     POC_Constants_Defined
     * @access  protected
     */
    protected static $_instance = null;

    /**
     * Plugin basename file.
     *
     * @var     string
     * @access  protected
     */
    protected $_plugin_basename;

    /**
     * Cloning is forbidden.
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'poc-cd' ), '1.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'poc-cd' ), '1.0' );
    }

    /**
     * Main POC WooCommerce Auctions instance
     *
     * Ensures only one instance of POC WooCommerce Auctions is loaded or can be loaded.
     *
     * @static
     * @see POC_WCA()
     * @return POC_WCA
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * __construct method
     */
    private function __construct() {
    }

    /**
     * __get method
     *
     * @param  string $prop
     * @return mixed
     */
    public function __get( $prop ) {
        if( method_exists( $this, $prop ) ) {
            return $this->$prop();
        }

        if( $prop == 'plugin_basename' ) {
            return plugin_basename( __FILE__ );
        }

        if( property_exists( $this, $prop ) ) {
            return $this->{$prop};
        } elseif( property_exists( $this, '_' . $prop ) ) {
            return $this->{'_' . $prop};
        } else {
            return null;
        }
    }

    /**
     * Show action links on the plugin screen
     *
     * @param mixed $links
     * @return array
     */
    public function action_links( $links ) {
        return array_merge( array(
            '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@pinchofcode.com&item_name=Donation+for+Pinch+Of+Code" title="' . __( 'Donate', 'poc-cd' ) . '" target="_blank">' . __( 'Donate', 'poc-cd' ) . '</a>',
            '<a href="' . admin_url( 'options-general.php?page=poc-cd' ) . '">' . __( 'Settings', 'poc-cd' ) . '</a>'
        ), $links );
    }

    /**
     * Init POC WooCommerce Auctions
     */
    public function init() {
        $this->_define_constants();

        //Load shared functions
        include_once( $this->_plugin_dir . '/shared-inc/functions-poc-plugin.php' );

        //Load admin classes
        if( is_admin() ) {
            include_once( $this->_plugin_dir . '/includes/admin/class-poc-cd-admin.php' );
        }

        //Hooks
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

        // Set up localisation
        $this->load_plugin_textdomain();

        //Activation hook
        register_activation_hook( __FILE__, array( $this, 'on_activation' ) );

        //Init hook
        do_action( 'poc_woocommerce_auctions_init' );
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present
     */
    public function load_plugin_textdomain() {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'poc-cd' );

        // Admin Locale
        if ( is_admin() ) {
            load_textdomain( 'poc-cd', WP_LANG_DIR . "/poc-cd/poc-cd-admin-$locale.mo" );
            load_textdomain( 'poc-cd', $this->plugin_dir . "/i18n/poc-cd-admin-$locale.mo" );
        }

        // Global + Frontend Locale
        load_textdomain( 'poc-cd', WP_LANG_DIR . "/poc-cd/poc-cd-$locale.mo" );
        load_plugin_textdomain( 'poc-cd', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n" );
    }

    /**
     * Activation hook
     */
    public function on_activation() {
        update_option( 'poc_cd_wp_debug', WP_DEBUG );
    }

    private function _define_constants() {
        //Define constants
        if( !defined( 'POC_CD_PLUGIN_FILE' ) ) {
            define( 'POC_CD_PLUGIN_FILE', __FILE__ );
        }

        if( !defined( 'POC_CD_VERSION' ) ) {
            define( 'POC_CD_VERSION', $this->version );
        }
    }

    /*****
     * Helpers
     */

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_dir() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
}

endif;

/**
 * Returns the main instance of POC WooCommerce Auctions to prevent the need to use globals.
 *
 * @return POC_WooCommerce_Auctions
 */
function POC_CD() {
    return POC_Constants_Defined::instance();
}

POC_CD()->init();
