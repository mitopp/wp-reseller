<?php
/**
 * WP Reseller
 *
 * @package   WP Reseller
 * @author    Michael Topp <blog@codeschubser.de>
 * @license   GPL-2.0+
 * @copyright 2014 Michael Topp
 *
 * @wordpress-plugin
 * Plugin Name:       WP Reseller
 * Description:       Hide update notifications and unused admin options.
 * Version:           0.0.2
 * Author:            Michael Topp
 * Author URI:        http://codeschubser.de
 * Text Domain:       wp-reseller
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
{
	die;
}

// Public-Facing Functionality
require_once( plugin_dir_path( __FILE__ ) . 'public/class-wp-reseller.php' );

// Register hooks that are fired when the plugin is activated or deactivated.
// When the plugin is deleted, the uninstall.php file is loaded.
register_activation_hook( __FILE__, array( 'WP_Reseller', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Reseller', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WP_Reseller', 'get_instance' ) );

// Dashboard and Administrative Functionality
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) )
{
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-wp-reseller-admin.php' );
	add_action( 'plugins_loaded', array( 'WP_Reseller_Admin', 'get_instance' ) );
}
