<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themeforest.net/user/amentotech/portfolio
 * @since      1.0.0
 *
 * @package    DocdirectApp
 * @subpackage DocdirectApp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    DocdirectApp
 * @subpackage DocdirectApp/public
 * @author     Amento Tech <theamentotech@gmail.com>
 */
class DocdirectApp_Public {

    public function __construct() {

        $this->plugin_name = DocdirectAppGlobalSettings::get_plugin_name();
        $this->version = DocdirectAppGlobalSettings::get_plugin_verion();
        $this->plugin_path = DocdirectAppGlobalSettings::get_plugin_path();
        $this->plugin_url = DocdirectAppGlobalSettings::get_plugin_url();
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in DocdirectApp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The DocdirectApp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        //wp_enqueue_style('system-public', plugin_dir_url(__FILE__) . 'css/system-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in DocdirectApp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The DocdirectApp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        //wp_enqueue_script('system-public', plugin_dir_url(__FILE__) . 'js/system-public.js', array('jquery'), $this->version, false);
    }

}
