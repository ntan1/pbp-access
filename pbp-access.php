<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              fes.yorku.ca
 * @since             1.0.0
 * @package           Pbp_Access
 *
 * @wordpress-plugin
 * Plugin Name:       Page by Page Access
 * Plugin URI:        fes.yorku.ca
 * Description:       This plugin allow Administrators to assign editing access to specific pages by user or role.
 * Version:           1.0.0
 * Author:            Calin Armenean
 * Author URI:        fes.yorku.ca
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pbp-access
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pbp-access-activator.php
 */
function activate_pbp_access() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pbp-access-activator.php';
    Pbp_Access_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pbp-access-deactivator.php
 */
function deactivate_pbp_access() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-pbp-access-deactivator.php';
    Pbp_Access_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_pbp_access');
register_deactivation_hook(__FILE__, 'deactivate_pbp_access');


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-pbp-access.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pbp_access() {

    $plugin = new Pbp_Access();
    $plugin->run();
}

run_pbp_access();
