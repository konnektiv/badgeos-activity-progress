<?php
/**
 * Plugin Name: BadgeOS Activity Progress
 * Plugin URI: https://wordpress.org/plugins/activity-progress-add-on-for-badgeos/
 * Description: Adds a shortcode to show a progress bar for the users current activity points.
 * Author: konnektiv
 * Version: 1.0.0
 * Author URI: https://konnektiv.de/
 * License: GNU AGPL
 * Text Domain: badgeos-activity-progress
 */

/*
 * Copyright © 2016 Konnektiv, Christoph Herbst
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General
 * Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>;.
*/

class BadgeOS_Activity_Progress {

	function __construct() {

		// Define plugin constants
		$this->basename       = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );
		$this->directory_url  = plugins_url( 'badgeos-activity-progress/' );

		// Load translations
		load_plugin_textdomain( 'badgeos-activity-progress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Run our activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// If BadgeOS is unavailable, deactivate our plugin
		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );
		add_action( 'plugins_loaded', array( $this, 'includes' ) );
		add_action( 'wp_print_scripts', array( $this, 'enqueue_scripts' ) );

	}

	/**
	 * Files to include for BadgeOS integration.
	 *
	 * @since  1.0.0
	 */
	public function includes() {
		if ( $this->meets_requirements() ) {
			require_once $this->directory_path . '/includes/activity-progress-shortcode-class.php';
		}
	}

	/**
	 * Enqueue custom scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
	}

	/**
	 * Activation hook for the plugin.
	 *
	 * @since 1.0.0
	 */
	public function activate() {

		// If BadgeOS is available, run our activation functions
		if ( $this->meets_requirements() ) {

		}

	}

	/**
	 * Check if BadgeOS is available
	 *
	 * @since  1.0.0
	 * @return bool True if BadgeOS is available, false otherwise
	 */
	public static function meets_requirements() {

		if ( class_exists( 'BadgeOS' ) && version_compare( BadgeOS::$version, '1.4.0', '>=' ) ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Generate a custom error message and deactivates the plugin if we don't meet requirements
	 *
	 * @since 1.0.0
	 */
	public function maybe_disable_plugin() {
		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
			echo '<p>' . sprintf( __( 'BadgeOS Activity Progress Add-On requires BadgeOS 1.4.0 or greater and has been <a href="%s">deactivated</a>. Please install and activate BadgeOS and then reactivate this plugin.', 'badgeos-activity-progress' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';

			// Deactivate our plugin
			deactivate_plugins( $this->basename );
		}
	}

}
new BadgeOS_Activity_Progress();
