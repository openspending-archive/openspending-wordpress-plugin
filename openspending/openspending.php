<?php
/*
Plugin Name: OpenSpending
Plugin URI: http://github.com/openspending/openspending-wordpress-plugin/
Description: Easily add <a href="http://openspending.org" title="OpenSpending - Mapping the Money">OpenSpending</a> visualisations to your blog or pages using the openspending shortcode. This means that you can easily map the money by loading a dataset into <a href="http://openspending.org" title="OpenSpending - Mapping the Money">OpenSpending</a> (if it isn't already there) and dropping a shortcode into your WordPress!
Author: Open Knowledge Foundation
Version: 0.5.1
Author URI: http://okfn.org/
License: GPLv2 or later
*/

/*  Copyright 2013 Open Knowledge Foundation ( http://okfn.org )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* OpenSpending plugin class
   Adds a shortcode handler for [openspending] that needs at least three
   attributes:
   * 'type': The type of visualisation
             e.g. treemap, bubbletree
   * 'dataset': What dataset on OpenSpending it should visualize,
                e.g. ukgov-finances-cra
   * 'drilldowns': Dimensions of the data to drilldown into,
                   e.g. from,to
*/
class OpenSpending {

    // Variable set when shortcode is found to print stylesheets and scripts
    static $add_style_and_script;

    // Set hooks for this plugin
    static function init() {
        // Add popup asset hack to the init hook
        add_action( 'init', array( __CLASS__, 'openspending_popup_assets' ) );

        // This plugin searches for the openspending shortcode which creates
        // as visualisation
        add_shortcode('openspending', array(__CLASS__, 'add_visualisation'));

        // Add triggers to register scripts and print them
        add_action('init', array(__CLASS__, 'register_style_and_script'));
        add_action('wp_footer', array(__CLASS__, 'print_style_and_script'));

        add_action('init', array(__CLASS__, 'add_mce_button'));
    }

    // Function called when the shortcode is found in a post/page
    static function add_visualisation($atts) {
        // Extract the attributes for the shortcode
        extract( shortcode_atts( array(
            'type' => null,
            'dataset' => null,
            'drilldowns' => null,
            'year' => null,
	), $atts ) );

        // Type, Dataset and Drilldowns must be defined
        if ( $type == null || $dataset == null || $drilldowns == null )
            return "<emph>Badly formed OpenSpending visualisation</emph>";

        // Set variable that adds style and script to the page/post
        self::$add_style_and_script = true;

        $div_open = "<div class=\"{$type}\"" .
                    " data-dataset=\"{$dataset}\"" .
                    " data-drilldowns=\"{$drilldowns}\"";

        // We try to do automatic inflation based on the previous year
        $div_open .= " data-inflate=\"" . (date("Y")-1) ."\"";

        if ( $year != null)
            $div_open .= " data-year=\"{$year}\"";

        if ( $type == 'bubbletree' || $type == 'barchart' )
            $div_open .= " data-icons-path=\"".
                         plugins_url('openspending/svg/') .
                         "\"";

        return $div_open . "></div>";
    }

    // Register the stylesheet and javascript we might need to use
    static function register_style_and_script() {
        // Register the CSS stylesheet for inclusion in the page if needed
        wp_register_style('openspending', 
                          plugins_url('css/openspending.min.css', __FILE__));

        // Register the JS script for inclusion if needed.
        wp_register_script('openspending',
                           plugins_url('js/openspending.min.js', __FILE__),
                           array('jquery'),
                           null, true);
    }

    static function print_style_and_script() {
        // If the we don't have to add the style and script we just return
        if ( ! self::$add_style_and_script )
            return;

        // Add the stylesheets and the scripts. This way we add the stylesheet
        // into the body, not into the header, but we can safely assume that
        // it takes longer to make a request for the data than it is to parse
        // and apply the stylesheet
        wp_print_styles('openspending');
        wp_print_scripts('openspending');
    }

    static function add_mce_button() {
        if ( ! current_user_can('edit_posts') &&
             ! current_user_can('edit_pages') ) {
            return;
        }

        if ( get_user_option('rich_editing') == 'true' ) {
            add_filter( 'mce_external_plugins', array(__CLASS__,'add_plugin'));
            add_filter( 'mce_buttons', array(__CLASS__,'register_button'));
        }
    }


    static function register_button( $buttons ) {
        array_push( $buttons, "", "openspending" );
        return $buttons;
    }

    static function add_plugin( $plugin_array ) {
        $plugin_array['openspending'] = plugins_url('openspending/js/openspending-mce.js');
        return $plugin_array;
    }

    // Function called on init to check if openspending asset should be served
    // instead of the wordpress page itself. This is a "curvy" solution to the
    // problem raised by the plugin where a the tinymce popup page is loaded
    // with an iframe and wordpress functions aren't accessible. Instead of
    // loading wordpress core files (which are not necessarily stored in the
    // same location across wordpress instances) we make the assets available
    // this way.
    // This hack is based on a similar solution made by Marius Jensen (Clorith)
    static function openspending_popup_assets() {
        // We just check if the openspending-plugin-asset query variable is
        // present (on any page served by wordpress)
        if ( isset( $_GET['openspending-plugin-asset'] ) )
        {
            // If it is set the function must be one of these allowed functions
            $allowed_funcs = array('spinner', 'tinymce', 'styles', 'scripts');
            $func = $_GET['openspending-plugin-asset'];

            // Check if it's allowed and if it is we serve plain text content
            // where we call the class function (which prints out the asset)
            // and then we die() to avoid loading the wordpress page
            if ( in_array($func, $allowed_funcs) ) {
                header( 'Content-Type: text/plain' );
                call_user_func(array(__CLASS__, 'openspending_popup_' . $func));
                die();
            }
        }
    }

    // The following methods (openspending_popup_*) print out assets
    // available to the openspending tinymce popup

    static function openspending_popup_spinner() {
        // Spinner is the included wordpress spinner
        echo includes_url('images/wpspin.gif');
    }
    static function openspending_popup_tinymce() {
        // TinyMCE popup is the included one
        echo includes_url('js/tinymce/tiny_mce_popup.js');
    }
    static function openspending_popup_styles() {
        // We enqueue a few styles via the wordpress mechanism and tailor it
        // to the user then we print it out, again with the help of wordpress

        // Get the user's layout (for the colors)
        global $user_ID;
        $layout = get_user_meta($user_ID, 'admin_color', true);

        // Enqueue the stylesheets and scripts we'll be needing
        wp_enqueue_style( "colors-{$layout}" );
        wp_enqueue_style( "buttons" );
        wp_enqueue_style( 'ie' );
        wp_enqueue_style( 'popup-specific',
                          plugins_url('openspending/css/popup.css') );

        do_action('admin_print_styles');
    }
    static function openspending_popup_scripts() {
        // Load and print scripts (jquery) with the wordpress mechanism
        wp_enqueue_script( 'jquery' );
        do_action('admin_print_scripts');
    }
}

// Initialize our plugin class
OpenSpending::init();

?>
