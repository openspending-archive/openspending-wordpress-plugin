<?php
/*
Plugin Name: OpenSpending
Plugin URI: http://tryggvib.github.io/openspending-wordpress-plugin/
Description: Easily add <a href="http://openspending.org" title="OpenSpending - Mapping the Money">OpenSpending</a> visualisations to your blog or pages using the openspending shortcode. This means that you can easily map the money by loading a dataset into <a href="http://openspending.org" title="OpenSpending - Mapping the Money">OpenSpending</a> (if it isn't already there) and dropping a shortcode into your WordPress!
Author: Open Knowledge Foundation
Version: 0.4.1
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
        
        if ( $year != null)
            $div_open .= " data-year=\"{$year}\"";

        if ( $type == 'bubbletree' )
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
}

// Initialize our plugin class
OpenSpending::init();

?>
