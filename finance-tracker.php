<?php
/**
 * Plugin Name: Finance Tracker
 * Description: Track expenses and view financial reports with enhanced features
 * Version: 1.1
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

// Define plugin constants
define('FT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once FT_PLUGIN_PATH . 'includes/classes/FT_DB_Handler.php';

class FinanceTracker {
    private $db;

    public function __construct() {
        $this->db = new FT_DB_Handler();
        
        // Register hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Admin features
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        
        // AJAX handlers
        add_action('wp_ajax_ft_add_expense', [$this, 'ajax_add_expense']);
        
        // REST API
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function activate() {
        $this->db->create_tables();
        $this->db->seed_initial_data();
        update_option('ft_db_version', '1.0');
    }

    public function deactivate() {
        // Optional: Temporary data cleanup
    }

    public function admin_menu() {
        add_menu_page(
            'Finance Tracker',
            'Finance Tracker',
            'manage_options',
            'finance-tracker',
            [$this, 'render_dashboard'],
            'dashicons-chart-area',
            6
        );

        add_submenu_page(
            'finance-tracker',
            'Add Expense',
            'Add Expense',
            'manage_options',
            'finance-tracker-add',
            [$this, 'render_add_expense']
        );

        add_submenu_page(
            'finance-tracker',
            'Categories',
            'Categories',
            'manage_options',
            'finance-tracker-categories',
            [$this, 'render_categories']
        );
    }

    public function admin_assets($hook) {
        if (strpos($hook, 'finance-tracker') !== false) {
            // Styles
            wp_enqueue_style(
                'ft-admin-css',
                FT_PLUGIN_URL . 'assets/css/style.css'
            );
            
            // Scripts
            wp_enqueue_script(
                'ft-admin-js',
                FT_PLUGIN_URL . 'assets/js/script.js',
                ['jquery'],
                filemtime(FT_PLUGIN_PATH . 'assets/js/script.js'),
                true
            );
            
            // Localize script data
            wp_localize_script('ft-admin-js', 'ft_admin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ft_ajax_nonce')
            ]);
            
            // Chart.js
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js',
                [],
                '3.7.0',
                true
            );
        }
    }

    public function ajax_add_expense() {
        check_ajax_referer('ft_ajax_nonce', 'nonce');
        
        $data = [
            'amount' => (float) $_POST['amount'],
            'category_id' => (int) $_POST['category_id'],
            'expense_date' => sanitize_text_field($_POST['expense_date']),
            'description' => sanitize_textarea_field($_POST['description'])
        ];

        if ($this->db->add_expense($data)) {
            wp_send_json_success(['message' => 'Expense added successfully!']);
        }
        
        wp_send_json_error(['message' => 'Error adding expense']);
    }

    public function register_rest_routes() {
        register_rest_route('finance-tracker/v1', '/expenses', [
            'methods' => 'GET',
            'callback' => [$this->db, 'get_expenses'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
    }

    // Page renderers
    public function render_dashboard() {
        include FT_PLUGIN_PATH . 'includes/admin/dashboard-page.php';
    }

    public function render_add_expense() {
        include FT_PLUGIN_PATH . 'includes/admin/add-expense-page.php';
    }

    public function render_categories() {
        include FT_PLUGIN_PATH . 'includes/admin/categories-page.php';
    }
}

new FinanceTracker();
