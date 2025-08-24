<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://deepa.dev.com
 * @since             1.0.0
 * @package           Movie_Rating
 *
 * @wordpress-plugin
 * Plugin Name:       movie-rating
 * Plugin URI:        https://deepa.dev.com
 * Description:       this is a movie-rating plugin
 * Version:           1.0.0
 * Author:            Deepa
 * Author URI:        https://deepa.dev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       movie-rating
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MOVIE_RATING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-movie-rating-activator.php
 */
function activate_movie_rating() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-movie-rating-activator.php';
	Movie_Rating_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-movie-rating-deactivator.php
 */
function deactivate_movie_rating() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-movie-rating-deactivator.php';
	Movie_Rating_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_movie_rating' );
register_deactivation_hook( __FILE__, 'deactivate_movie_rating' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-movie-rating.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_movie_rating() {

	$plugin = new Movie_Rating();
	$plugin->run();

}
run_movie_rating();
