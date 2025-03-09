<?php
// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN') || !WP_UNINSTALL_PLUGIN) {
    exit;
}

global $wpdb;

// Delete custom tables
$tables = [
    $wpdb->prefix . 'finance_expenses',
    $wpdb->prefix . 'finance_categories'
];

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$table}");
}

// Delete plugin options
delete_option('ft_plugin_version');
delete_option('ft_db_version');

// Cleanup transients
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_ft_%'");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_ft_%'");

// Remove cron jobs
wp_clear_scheduled_hook('ft_daily_report');