<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
define('FINANCE_TRACKER_TABLE', $wpdb->prefix . 'finance_tracker');

function finance_tracker_create_db() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE " . FINANCE_TRACKER_TABLE . " (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(255) NOT NULL,
        amount FLOAT NOT NULL,
        date DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function finance_tracker_add_expense($category, $amount) {
    global $wpdb;
    $wpdb->insert(FINANCE_TRACKER_TABLE, [
        'category' => sanitize_text_field($category),
        'amount' => floatval($amount)
    ]);
}

function finance_tracker_get_expenses() {
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM " . FINANCE_TRACKER_TABLE);
}
?>
