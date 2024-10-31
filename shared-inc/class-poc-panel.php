<?php
/**
 * Pinch Of Code plugins panel class.
 *
 * @author  	Pinch Of Code <info@pinchofcode.com>
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // If this file is called directly, abort.

if( !class_exists( 'POC_Panel' ) ) :

/**
 * @class 		POC_Panel
 */
class POC_Panel {
	/**
     * Initial Options definition:
     *   'tab' => array(
     *      'label',
     *      'sections' => array(
     *          'fields' => array(
     *             'option1',
     *             'option2',
     *              ...
     *          )
     *      )
     *   )
     *
     * @var array
     */
    public $options = array();

    /**
     * Options group name
     *
     * @var string
     */
    public $option_group = 'panel_group';

    /**
     * Option name
     *
     * @var string
     */
    public $option_name = 'panel_options';

    /**
     * Get the plugin directory URL.
     *
     * @var string
     * @access protected
     */
    protected $_plugin_url;

    /**
     * Get the plugin directory path.
     *
     * @var string
     * @access protected
     */
    protected $_plugin_dir;

    /**
     * Parameters for add_submenu_page
     *
     *  add_submenu_page(
     *      'themes.php',		// The file name of a standard WordPress admin page
     *      'Theme Options',	// The text to be displayed in the title tags of the page when the menu is selected
     *      'Theme Options',	// The text to be used for the menu
     *      'administrator',	// The capability (or role) required for this menu to be displayed to the user.
     *      'theme-options',	// The slug name to refer to this menu by (should be unique for this menu).
     *      'theme_options_display_page' // The function to be called to output the content for this page.
     *  );
     *
     * @var 	array
     * @access 	protected
     */
    protected $_submenu = array();

	/**
     * __construct method
     *
     * @param 	array 	$submenu
     * @param 	array 	$options
     * @param  	string 	$option_group
     * @param  	string 	$option_name
     */
    public function __construct( $submenu, $options, $option_group = false, $option_name = false ) {
        $this->_submenu = apply_filters( 'poc_panel_submenu', $submenu );
        $this->options  = apply_filters( 'poc_panel_options', $options );

        if( $option_group ) {
            $this->option_group = $option_group;
        }

        if( $option_name ) {
            $this->option_name = $option_name;
        }

        $this->_plugin_url = untrailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );
        $this->_plugin_dir = untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );

        //add new menu item
        //register new settings option group
        //include js and css files
        //print browser
        add_action( 'admin_menu', array( $this, 'add_submenu_page') );
        add_action( 'admin_init', array( $this, 'panel_register_setting') );
        add_action( 'admin_enqueue_scripts', array( $this, 'panel_enqueue') );

        // add the typography javascript vars
        add_action( 'poc_panel_after_panel', array( $this, 'js_typo_vars' ) );
    }

    /**
     * Create new submenu page
     *
     * @return 	void
     * @link 	http://codex.wordpress.org/Function_Reference/add_submenu_page
     */
    public function add_submenu_page() {
        $submenu = $this->_submenu;
        add_submenu_page(
            $submenu[0],
            $submenu[1],
            $submenu[2],
            $submenu[3],
            $submenu[4],
            array( $this, isset($submenu[5]) ? $submenu[5] : 'display_options_page' )
        );
    }

	public function display_options_page() {
		// Create a header in the default WordPress 'wrap' container
        $page = $this->_get_tab();
        ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach( $this->options as $k=>$tab ): ?>
                <a class="nav-tab<?php if( $page == $k ): ?> nav-tab-active<?php endif ?>" href="<?php echo add_query_arg('panel_page', $k) ?>"><?php echo $tab['label'] ?></a>
            <?php endforeach ?>
            <?php do_action( 'poc_panel_after_tabs' ); ?>
        </h2>

        <div class="wrap">
            <form action="options.php" method="post">
                <p class="submit">
                    <input type="hidden" name="panel_page" value="<?php echo $page ?>" />
                    <input class="button-primary" type="submit" name="save_options" value="Save Options" />
                </p>

                <?php do_settings_sections( $this->option_name ); ?>
                <?php settings_fields( $this->option_group ) ?>

                <p class="submit">
                    <input type="hidden" name="panel_page" value="<?php echo $page ?>" />
                    <input class="button-primary" type="submit" name="save_options" value="Save Options" />
                </p>
            </form>
            <?php do_action( 'poc_panel_after_panel' ); ?>
            <?php _e( '<p>Thank you for using our plugin.</p><p><small>Build with <a href="https://github.com/PinchOfCode/grunt-init-wordpress-plugin" target="_blank" title="grunt-init-wordpress-plugin">grunt-init-wordpress-plugin</a> and <a href="https://github.com/PinchOfCode/poc-plugin-boilerplate" target="_blank" title="POC Plugin Boilerplate">POC Plugin Boilerplate</a>.</small></p>', 'poc-cs' ) ?>
        </div>
        <?php
	}

	/**
     * Register a new settings option group
     *
     * @return 	void
     * @link 	http://codex.wordpress.org/Function_Reference/register_setting
     * @link 	http://codex.wordpress.org/Function_Reference/add_settings_section
     * @link 	http://codex.wordpress.org/Function_Reference/add_settings_field
     */
    public function panel_register_setting() {
        $page = $this->_get_tab();
        $tab = isset( $this->options[$page] ) ? $this->options[$page] : array();

        if( !empty($tab['sections']) ) {
            //add sections and fields
            foreach( $tab['sections'] as $section_name => $section) {
                //add the section
                add_settings_section(
                    $section_name,
                    $section['title'],
                    array( $this, 'panel_section_content'),
                    $this->option_name
                );

                //add the fields
                foreach( $section['fields'] as $option_name => $option ) {
                    $option_title = $option['title'];

                    //Default text
                    if( $option['type'] != 'textarea' ) {
                    	$option_title = isset( $option['default'] ) && $option['default'] != get_option( $option_name ) ? $option['title'] . '<small class="default">' . sprintf( __( 'Default: %s'), $option['default'] ) . '</small>' : $option['title'];

                    	if( $option['type'] == 'checkbox' ) {
                    		$option_default = $option['default'] == 'off' ? '' : 'on';
                    		$option_title = isset( $option['default'] ) && $option_default != get_option( $option_name ) ? $option['title'] . '<small class="default">' . sprintf( __( 'Default: %s'), $option['default'] ) . '</small>' : $option['title'];
                    	}
                    }

                    $option['id'] = $option_name;
                    $option['label_for'] = $option_name;

                    if( isset( $option['filter'] ) && function_exists( $option['filter'] ) ) {
                        $filter = $option['filter'];
                    } elseif( function_exists( 'poc_panel_filter_' . $option['type'] ) ) {
                        $filter = 'poc_panel_filter_' . $option['type'];
                    } else {
                        $filter = array( $this, 'panel_sanitize' );
                    }

                    //register settings group
                    register_setting(
                        $this->option_group,
                        $option_name,
                        $filter
                    );

                    add_settings_field(
                        $option_name,
                        $option_title,
                        array( $this, 'panel_field_content' ),
                        $this->option_name,
                        $section_name,
                        $option
                    );
                }
            }
        }
    }

    /**
     * Display sections content
     *
     * @return void
     */
    public function panel_section_content( $section ) {
        $page = $this->_get_tab();
        if( isset( $this->options[$page]['sections'][ $section['id'] ]['description'] ) ) {
            echo "<p class='section-description'>" . $this->options[$page]['sections'][ $section['id'] ]['description'] . "</p>";
        }
    }

    /**
     * Sanitize the option's value
     *
     * @param 	array $input
     * @return 	array
     */
    public function panel_sanitize( $input ) {
        return apply_filters( 'poc_panel_sanitize', $input );
    }

    /**
     * Display field content
     *
     * @return void
     */
    public function panel_field_content( $field ) {
        $value 	= get_option( $field['id'], isset( $field['default'] ) ? $field['default'] : '' );
        $id 	= $field['id'];
        $name 	= $field['id'];

        $echo = '';

        switch( $field['type'] ) {
            case 'text':
                $echo  = '<input type="text" id=' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text code" />';

                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'email':
                $echo  = '<input type="email" id=' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text code" />';

                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'url':
                $echo  = '<input type="text" id=' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text code" />';

                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'textarea': $echo = '<textarea name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" class="large-text code" rows="10" cols="50">' . esc_attr( $value ) .'</textarea>';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'checkbox': $echo = '<input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="on" '. checked( $value, 'on', false ) . ' />';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= ' <label for="' . esc_attr( $id ) . '"><span class="description">' . $field['description'] . '</span></label>';
                }
                break;

            case 'select': $echo  = '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '">';
                foreach( $field['options'] as $v=>$label ) {
                    $echo .= "<option value='{$v}'". selected($value, $v, false) .">{$label}</option>";
                }
                $echo .= '</select>';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'number':
                $mms = '';
                if( isset( $field['min'] ) ) {
                    $mms .= ' min="' . esc_attr( $field['min'] ) . '"';
                }

                if( isset( $field['max'] ) ) {
                    $mms .= ' max="' . esc_attr( $field['max'] ) . '"';
                }

                if( isset( $field['step'] ) ) {
                    $mms .= ' step="' . esc_attr( $field['step'] ) . '"';
                }

                $echo = '<input type="number" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="small-text" ' . $mms . ' />';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'colorpicker':
                $default = isset( $field['default'] ) ? $field['default'] : '';

                $echo = '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="medium-text code panel-colorpicker" data-default-color="' . esc_attr( $default ) . '" />';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'datepicker':
                $default = isset( $field['default'] ) ? $field['default'] : array( 'date' => '', 'hh' => 0, 'mm' => 0, 'ss' => 0 );
                $value = ! empty( $value ) ? $value : array( 'date' => '', 'hh' => 0, 'mm' => 0, 'ss' => 0 );

                $echo  = '<input type="text" id="' . esc_attr( $id ) . '_date" name="' . esc_attr( $name['date'] ) . '" value="' . esc_attr( $value['date'] ) . '" class="medium-text code panel-datepicker" colorpicker="' . __( 'Select a date', 'poc-cs' ) . '" /> - ';
                $echo .= '<input type="text" id="' . esc_attr( $id ) . '_hh" name="' . esc_attr( $name['hh'] ) . '" value="' . esc_attr( $value['hh'] ) . '" class="small-text code" colorpicker="' . __( 'Hours', 'poc-cs' ) . '" /> : ';
                $echo .= '<input type="text" id="' . esc_attr( $id ) . '_mm" name="' . esc_attr( $name['mm'] ) . '" value="' . esc_attr( $value['mm'] ) . '" class="small-text code" colorpicker="' . __( 'Minutes', 'poc-cs' ) . '" /> : ';
                $echo .= '<input type="text" id="' . esc_attr( $id ) . '_ss" name="' . esc_attr( $name['ss'] ) . '" value="' . esc_attr( $value['ss'] ) . '" class="small-text code" colorpicker="' . __( 'Minutes', 'poc-cs' ) . '" />';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'upload':
                $echo  = '<div class="uploader">';
                $echo .= '  <input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text code" /> <input type="button" name="" id="' . esc_attr( $id ) . '_button" class="button" value="' . __( 'Upload', 'poc-cs' ) . '">';
                $echo .= '</div>';
                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'checkboxes':
                $echo   = '<div class="checkboxes">';
                $value  = maybe_unserialize( $value );
                foreach ( $field['options'] as $check_value => $check_label ) {
                    $echo .= '<label><input type="checkbox" id="' . esc_attr( $id ) . '_' . esc_attr( $check_value ) . '" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $check_value ) . '"' . checked( in_array( $check_value,  ( array ) $value ), true, false) . ' /> ' . $check_label . '</label><br />';
                }

                $echo .= '<p class="description">' . $field['description'] . '</p>';
                break;

            case 'typography': $value = wp_parse_args( $value, $field['std'] ); ?>
                <div class="typography_container typography">
                    <div class="option">
                        <!-- Size -->
                        <div class="spinner_container">
                            <input class="typography_size number small-text" type="number" name="<?php echo $name ?>[size]" id="<?php echo $id ?>-size" value="<?php echo $value['size'] ?>" data-min="<?php if(isset( $field['min'] )) echo $field['min'] ?>" data-max="<?php if(isset( $field['max'] )) echo $field['max'] ?>" />
                        </div>

                        <!-- Unit -->
                        <div class="select-wrapper font-unit">
                            <select class="typography_unit" name="<?php echo $name ?>[unit]" id="<?php echo $id ?>-unit">
                                <option value="px" <?php selected( $value['unit'], 'px' ) ?>><?php _e( 'px', 'poc-cs' ) ?></option>
                                <option value="em" <?php selected( $value['unit'], 'em' ) ?>><?php _e( 'em', 'poc-cs' ) ?></option>
                                <option value="pt" <?php selected( $value['unit'], 'pt' ) ?>><?php _e( 'pt', 'poc-cs' ) ?></option>
                                <option value="rem" <?php selected( $value['unit'], 'rem' ) ?>><?php _e( 'rem', 'poc-cs' ) ?></option>
                            </select>
                        </div>

                        <!-- Family -->
                        <div class="select-wrapper font-family">
                            <select class="typography_family" name="<?php echo $name ?>[family]" id="<?php echo $id ?>-family" data-instance="false">
                                <?php if( $value['family'] ): ?>
                                    <option value="<?php echo stripslashes( $value['family'] ) ?>"><?php echo $value['family'] ?></option>
                                <?php else: ?>
                                    <option value=""><?php _e('Select a font family', 'poc-cs') ?></option>
                                <?php endif ?>
                            </select>
                        </div>

                        <!-- Style -->
                        <div class="select-wrapper font-style">
                            <select class="typography_style" name="<?php echo $name ?>[style]" id="<?php echo $id ?>-style">
                                <option value="regular" <?php selected( $value['style'], 'regular' ) ?>><?php _e( 'Regular', 'poc-cs' ) ?></option>
                                <option value="bold" <?php selected( $value['style'], 'bold' ) ?>><?php _e( 'Bold', 'poc-cs' ) ?></option>
                                <option value="extra-bold" <?php selected( $value['style'], 'extra-bold' ) ?>><?php _e( 'Extra bold', 'poc-cs' ) ?></option>
                                <option value="italic" <?php selected( $value['style'], 'italic' ) ?>><?php _e( 'Italic', 'poc-cs' ) ?></option>
                                <option value="bold-italic" <?php selected( $value['style'], 'bold-italic' ) ?>><?php _e( 'Italic bold', 'poc-cs' ) ?></option>
                            </select>
                        </div>

                        <!-- Color -->
                        <input type='text' id='<?php echo $id ?>-color' name='<?php echo $name ?>[color]' value='<?php echo $value['color'] ?>' class='medium-text code panel-colorpicker typography_color' data-default-color='<?php echo $field['std']['color'] ?>' />

                    </div>
                    <div class="clear"></div>
                    <div class="font-preview">
                        <p>The quick brown fox jumps over the lazy dog</p>
                        <!-- Refresh -->
                        <div class="refresh_container"><button class="refresh"><?php _e( 'Click to preview', 'poc-cs' ) ?></button></div>
                    </div>
                </div>
                <?php
                    global $poc_panel_if_typography;
                    $poc_panel_if_typography = true;
                break;

            case 'autocomplete':
                $placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : false;
                $values = maybe_unserialize( $value );

                $echo  = '<select' . ( $placeholder ? ' data-placeholder="' . $placeholder . '"' : ''  ) . ' style="min-width: 350px;" name="' . esc_attr( $name ) . '[]" id="' . esc_attr( $id ) . '" multiple="multiple">';
                foreach( $values as $v ) {
                    $echo .= '<option value="' . $v . '" ' . selected( in_array( $v, $values ), true, false ) . '">' . $v . '</option>';
                }
                $echo .= '</select>';

                $echo .= '<script>
                jQuery( document ).ready( function( $ ) {
                    $( "#' . esc_attr( $id ) . '" ).ajaxChosen({
                        type  : "GET",
                        url   : ajaxurl,
                        dataType : "json",
                        afterTypeDelay: 100,
                        data:       {
                            action : "poc_panel_' . str_replace( '-', '_', esc_attr( $id ) ) . '"
                        }
                    }, function( data ) {
                        var terms = {};

                        $.each(data, function (i, val) {
                            terms[i] = val;
                        });

                        return terms;
                    });
                });
                </script>';

                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            case 'products_search':
                if( !is_woocommerce_active() ) { break; }

                $placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : false;

                $echo  = '<select class="poc_panel_products_search"' . ( $placeholder ? ' data-placeholder="' . $placeholder . '"' : ''  ) . ' style="min-width: 350px;" name="' . esc_attr( $name ) . '[]" id="' . esc_attr( $id ) . '" multiple="multiple">';

                $product_ids = maybe_unserialize( $value );
                if ( $product_ids ) {
                    $product_ids = array_map( 'absint', $product_ids );
                    foreach ( $product_ids as $product_id ) {
                        $product = get_product( $product_id );

                        $echo .= '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                    }
                }
                $echo .= '</select>';

                if( isset($field['description']) && $field['description'] != '' ) {
                    $echo .= '<p class="description">' . $field['description'] . '</p>';
                }
                break;

            default:
                do_action( 'poc_panel_field_' . $field['type'] );
                break;
        }

        echo $echo;
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function panel_enqueue( $hook ) {
        global $pagenow;

        if( $pagenow == $this->_submenu[0] && isset( $_GET['page'] ) && $_GET['page'] == $this->_submenu[4] ) {
            $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

            wp_enqueue_style(  'wp-color-picker' );
            wp_enqueue_style(  'jquery-ui', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' );
            wp_enqueue_script( 'jquery-ui-datepicker' );

            wp_enqueue_style(  'poc-panel',     $this->_plugin_url . '/shared-inc/assets/css/poc-panel.css', array( 'wp-color-picker' ) );
            wp_enqueue_style(  'chosen',        $this->_plugin_url . '/shared-inc/assets/css/chosen' . $min . '.css' );
            wp_enqueue_script( 'poc-panel',     $this->_plugin_url . '/shared-inc/assets/js/poc-panel.js', array( 'jquery', 'wp-color-picker' ), null, true );
            wp_enqueue_script( 'chosen',        $this->_plugin_url . '/shared-inc/assets/js/chosen.jquery' . $min . '.js', array( 'jquery' ) );
            wp_enqueue_script( 'ajax-chosen',   $this->_plugin_url . '/shared-inc/assets/js/ajax-chosen' . $min . '.js', array( 'jquery', 'chosen' ) );

            wp_enqueue_media();

            do_action( 'poc_panel_enqueue' );
        }
    }

    /**
     * Add the vars for the typography options
     */
    public function js_typo_vars() {
        global $poc_panel_if_typography;
        if ( ! isset( $poc_panel_if_typography ) || ! $poc_panel_if_typography ) return;

        $web_fonts = array(
            "Arial",
            "Arial Black",
            "Comic Sans MS",
            "Courier New",
            "Georgia",
            "Impact",
            "Lucida Console",
            "Lucida Sans Unicode",
            "Thaoma",
            "Trebuchet MS",
            "Verdana"
        );

        // http://niubbys.altervista.org/google_fonts.php
        $google_fonts = file_get_contents( $this->_plugin_dir . '/shared-inc/assets/js/google_fonts.json' );
        ?>
        <script type="text/javascript">
            var poc_panel_google_fonts = '<?php echo $google_fonts ?>',
                poc_panel_web_fonts = '{"items":<?php echo json_encode( $web_fonts ) ?>}',
                poc_panel_family_string = '';
        </script>
        <?php
    }

    /**
     * Get the active tab. If the page isn't provided, the function
     * will return the first tab name
     *
     * @return string
     * @access protected
     */
    protected function _get_tab() {
        if( isset( $_POST['panel_page'] ) && $_POST['panel_page'] != '' ) {
            return $_POST['panel_page'];
        } elseif( isset( $_GET['panel_page'] ) && $_GET['panel_page'] != '' ) {
            return $_GET['panel_page'];
        } else {
            $tabs = array_keys( $this->options );
            return $tabs[0];
        }
    }


}

endif;
