<?php
/**
 * Plugin Name: Finance Tracker
 * Plugin URI:  https://yourwebsite.com
 * Description: A simple finance tracking plugin.
 * Version: 1.0
 * Author: Rajappa Adabala
 * Author URI:  https://yourwebsite.com
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin path
define('FINANCE_TRACKER_PATH', plugin_dir_path(__FILE__));

// Include necessary files
require_once FINANCE_TRACKER_PATH . 'includes/add-expense.php';
require_once FINANCE_TRACKER_PATH . 'includes/categories.php';
require_once FINANCE_TRACKER_PATH . 'includes/dashboard.php';
require_once FINANCE_TRACKER_PATH . 'includes/ftdb-handler.php';

// Register activation hook
function finance_tracker_activate() {
    finance_tracker_create_db();
}
register_activation_hook(__FILE__, 'finance_tracker_activate');

// Register uninstall hook
register_uninstall_hook(__FILE__, 'finance_tracker_uninstall');

// Enqueue styles
function finance_tracker_enqueue_assets() {
    wp_enqueue_style('finance-tracker-style', plugins_url('assets/styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'finance_tracker_enqueue_assets');
?>
