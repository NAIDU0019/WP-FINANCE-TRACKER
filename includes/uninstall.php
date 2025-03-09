<?php
if (!defined('ABSPATH')) exit;

function finance_tracker_uninstall() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS " . FINANCE_TRACKER_TABLE);
}
?>
