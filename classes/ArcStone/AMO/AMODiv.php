<?php

namespace ArcStone\AMO;

/**
 * Shortcode output wrapper.
 *
 * Adds wraps output with div and default CSS
 *
 * @version 2.1
 */
class AMODiv {

    public static  $css_id               = '';
    public static  $css_classes          = array( 'amo_wrapper' );
    private static $css_overrides        = false;
    private static $css_overrides_echoed = false;

    /**
     * The beginning of the HTML output.
     *
     * @since  2.0
     *
     * @return string starting <div> and any custom CSS overrides.
     */
    public static function get_header() {
        $ret         = '';
        $css_classes = implode( ' ', self::$css_classes );

        // output user set CSS overrides
        self::get_css_overrides();

        if ( self::$css_overrides && ! self::$css_overrides_echoed ) {
            $ret .= '<style type="text/css">' . self::css_tidy( self::$css_overrides ) . '</style>';

            self::$css_overrides_echoed = true;
        }

        $ret .= '<div id="' . \esc_attr( self::$css_id ) . '" class="' . $css_classes . '">';

        return $ret;
    }

    /**
     * The end of the HTML output.
     *
     * @since 2.0
     */
    public static function get_footer() {
        return '</div>';
    }

    /**
     * Get the CSS overrides if set
     *
     * @since 2.1
     */
    private static function get_css_overrides() {
        if ( self::$css_overrides === false ) {
            self::$css_overrides = \cmb2_get_option( 'amo_options', 'css_overrides' );
        }
    }

    /**
     *
     * Creates the output HTML.
     *
     * @since 2.0
     *
     * @param string $content     HTML to output.
     * @param string $css_id      Optional. ID for the wrapper div.
     * @param array  $css_classes Optional. Classes to add to wrapper div.
     *
     * @return string HTML output.
     */
    public static function do_output( $content, $css_id = '', $css_classes = array() ) {
        self::$css_id = $css_id;
        if ( ! empty( $css_classes ) && is_array( $css_classes ) ) {
            self::$css_classes = array_merge( self::$css_classes, $css_classes );
        }

        ob_start();
        echo self::get_header();
        echo $content;
        echo self::get_footer();

        return ob_get_clean();
    }

    /**
     * Load CSSTidy libraries.
     *
     * Used to escape CSS output, WP does not have an appropriate
     * escape method.
     *
     * @since 3.0
     *
     * @param string $input_css
     *
     * @return
     */
    private static function css_tidy( $input_css ) {
        include WP_AMO::$installed_path . 'includes/csstidy/class.csstidy.php';

        $csstidy = new \csstidy();
        $csstidy->set_cfg( 'remove_bslash', false );
        $csstidy->set_cfg( 'compress_colors', false );
        $csstidy->set_cfg( 'compress_font-weight', false );
        $csstidy->set_cfg( 'discard_invalid_properties', true );
        $csstidy->set_cfg( 'merge_selectors', false );
        $csstidy->set_cfg( 'remove_last_;', false );
        $csstidy->set_cfg( 'css_level', 'CSS3.0' );

        $input_css = wp_kses_split( $input_css, array(), array() );
        $csstidy->parse( $input_css );

        return $csstidy->print->plain();
    }

    /**
     * Display pagination links template
     *
     * @since        3.1.0
     *
     * @param int   $current_page Current page.
     * @param int   $per_page     Results per page.
     * @param int   $total        Total results.
     * @param array $url_params   Extra URL params.
     *
     * @return string
     */
    public static function paginate_links( $current_page, $per_page, $total, $url_params = null ) {
        $url_params = array_merge( $_GET, $url_params );

        ob_start();
        include WP_AMO::$installed_path . 'templates/pagination.tpl.php';

        return ob_get_clean();
    }
}
