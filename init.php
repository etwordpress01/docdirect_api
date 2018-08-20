<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://themeforest.net/user/themographics/portfolio
 * @since             1.0
 * @package           Docdirect APP Configurations
 *
 * @wordpress-plugin
 * Plugin Name:       Docdirect APP Configurations
 * Plugin URI:        https://themeforest.net/user/themographics/portfolio
 * Description:       This plugin is used for creating custom API for Docdirect Theme
 * Version:           1.0
 * Author:            Themographics
 * Author URI:        https://themeforest.net/user/themographics
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       docdirect_app_configuration
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-elevator-activator.php
 */
if( !function_exists( 'activate_docdirect_app' ) ) {
	function activate_docdirect_app() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-system-activator.php';
		DocdirectApp_Activator::activate();
	} 
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-elevator-deactivator.php
 */
if( !function_exists( 'deactivate_docdirect_app' ) ) {
	function deactivate_docdirect_app() {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-system-deactivator.php';
		DocdirectApp_Deactivator::deactivate();
	}
}

register_activation_hook( __FILE__, 'activate_docdirect_app' );
register_deactivation_hook( __FILE__, 'deactivate_docdirect_app' );

/**
 * Plugin configuration file,
 * It include getter & setter for global settings
 */
require plugin_dir_path( __FILE__ ) . 'config.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-system.php';
require plugin_dir_path( __FILE__ ) . 'hooks/hooks.php';
require plugin_dir_path( __FILE__ ) . 'lib/user.php';
require plugin_dir_path( __FILE__ ) . 'lib/categories.php';
require plugin_dir_path( __FILE__ ) . 'lib/top_categories.php';
require plugin_dir_path( __FILE__ ) . 'lib/featured_listing.php';
require plugin_dir_path( __FILE__ ) . 'lib/wishlist.php';
require plugin_dir_path( __FILE__ ) . 'lib/latest_providers.php';
require plugin_dir_path( __FILE__ ) . 'lib/doc_detail.php';
require plugin_dir_path( __FILE__ ) . 'lib/send_mail.php';
require plugin_dir_path( __FILE__ ) . 'lib/submit_claim.php';
require plugin_dir_path( __FILE__ ) . 'lib/team.php';
require plugin_dir_path( __FILE__ ) . 'lib/make_review.php';
require plugin_dir_path( __FILE__ ) . 'lib/reviews.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/specialities.php';
require plugin_dir_path( __FILE__ ) . 'lib/manage_team.php';
require plugin_dir_path( __FILE__ ) . 'lib/add_team_member.php';
require plugin_dir_path( __FILE__ ) . 'lib/remove_team_member.php';
require plugin_dir_path( __FILE__ ) . 'lib/config.php';
require plugin_dir_path( __FILE__ ) . 'lib/current_package.php';
require plugin_dir_path( __FILE__ ) . 'lib/packages.php';
require plugin_dir_path( __FILE__ ) . 'lib/user_schedule.php';
require plugin_dir_path( __FILE__ ) . 'lib/update_schedule.php';
require plugin_dir_path( __FILE__ ) . 'lib/user_booking.php';
require plugin_dir_path( __FILE__ ) . 'lib/change_appointment_status.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
if( !function_exists( 'run_DocdirectApp' ) ) {
	function run_DocdirectApp() {
	
		$plugin = new DocdirectApp_Core();
		$plugin->run();
	
	}
	run_DocdirectApp();
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
add_action( 'init', 'docdirect_app_load_textdomain' );
function docdirect_app_load_textdomain() {
  load_plugin_textdomain( 'docdirect_app_configuration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
