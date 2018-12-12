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
require plugin_dir_path( __FILE__ ) . 'lib/search_team_member.php';
require plugin_dir_path( __FILE__ ) . 'lib/add_team_member.php';
require plugin_dir_path( __FILE__ ) . 'lib/remove_team_member.php';
require plugin_dir_path( __FILE__ ) . 'lib/config.php';
require plugin_dir_path( __FILE__ ) . 'lib/current_package.php';
require plugin_dir_path( __FILE__ ) . 'lib/packages.php';
require plugin_dir_path( __FILE__ ) . 'lib/user_schedule.php';
require plugin_dir_path( __FILE__ ) . 'lib/update_schedule.php';
require plugin_dir_path( __FILE__ ) . 'lib/user_booking.php';
require plugin_dir_path( __FILE__ ) . 'lib/update_appointment_status.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/security_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/delete_deactivate_account.php';
require plugin_dir_path( __FILE__ ) . 'lib/privacy_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/languages.php';
require plugin_dir_path( __FILE__ ) . 'lib/insurance_list.php';
require plugin_dir_path( __FILE__ ) . 'lib/directory_search.php';
require plugin_dir_path( __FILE__ ) . 'lib/blog/blog_post.php';
require plugin_dir_path( __FILE__ ) . 'lib/blog/blog_post_detail.php';
require plugin_dir_path( __FILE__ ) . 'lib/blog/recent_post.php';
require plugin_dir_path( __FILE__ ) . 'lib/blog/blog_categories.php';
require plugin_dir_path( __FILE__ ) . 'lib/articles/articles.php';
require plugin_dir_path( __FILE__ ) . 'lib/articles/create_article.php';
require plugin_dir_path( __FILE__ ) . 'lib/articles/delete_article.php';
require plugin_dir_path( __FILE__ ) . 'lib/articles/user-articles.php';
require plugin_dir_path( __FILE__ ) . 'lib/articles/manage_articles.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/question.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/recent_questions.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/vote.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/submit_answer.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/post_question.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/view_answers.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/answer.php';
require plugin_dir_path( __FILE__ ) . 'lib/question_answer/statics.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_social_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_price_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_basic_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_awards_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_experience_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_qualification_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_profile_languages.php';
require plugin_dir_path( __FILE__ ) . 'lib/profile_setting/update_speciality_setting.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/update_service_category.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/delete_service_category.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/service_category_listings.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/user_services_listings.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/update_user_service.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/delete_user_service.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/user_bookings.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/get_booking_time_slots.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/book_appointment.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/provider_services_listing.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/provider_category_listings.php';
require plugin_dir_path( __FILE__ ) . 'lib/booking_schedules/approve_disapprove_appointment.php';
require plugin_dir_path( __FILE__ ) . 'lib/media/profile_media_uploader.php';
require plugin_dir_path( __FILE__ ) . 'lib/get_team_listing.php';
require plugin_dir_path( __FILE__ ) . 'lib/get_providers.php';
require plugin_dir_path( __FILE__ ) . 'lib/get_filters.php';
require plugin_dir_path( __FILE__ ) . 'lib/remove_favourite.php';
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
