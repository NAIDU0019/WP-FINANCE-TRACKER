<?php
class FT_DB_Old_Handler {
    
    public $expenses_table;
    public $categories_table;

    public function __construct() {
        global $wpdb;
        $this->expenses_table = $wpdb->prefix . 'finance_expenses';
        $this->categories_table = $wpdb->prefix . 'finance_categories';
    }

    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql_expenses = "CREATE TABLE {$this->expenses_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            category_id BIGINT UNSIGNED NOT NULL,
            expense_date DATE NOT NULL,
            description TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY category_id (category_id)
        ) {$charset_collate};";

        $sql_categories = "CREATE TABLE {$this->categories_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_expenses);
        dbDelta($sql_categories);
    }

    public function seed_initial_data() {
        global $wpdb;
        $default_categories = ['Food', 'Transport', 'Housing', 'Entertainment'];
        
        foreach ($default_categories as $category) {
            if (!$wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->categories_table} WHERE slug = %s",
                sanitize_title($category)
            ))) {
                $wpdb->insert($this->categories_table, [
                    'name' => $category,
                    'slug' => sanitize_title($category)
                ]);
            }
        }
    }

    public function add_expense($data) {
        global $wpdb;

        // Validate and sanitize input data
        $data['amount'] = floatval($data['amount']);
        $data['category_id'] = intval($data['category_id']);
        $data['expense_date'] = sanitize_text_field($data['expense_date']);
        $data['description'] = sanitize_textarea_field($data['description']);
        
        return $wpdb->insert($this->expenses_table, [
            'user_id' => get_current_user_id(),
            'amount' => $data['amount'],
            'category_id' => $data['category_id'],
            'expense_date' => $data['expense_date'],
            'description' => $data['description']
        ]);
    }

    public function get_expenses($category_id = null) {
        global $wpdb;

        $query = "SELECT e.id, e.amount, e.expense_date, e.description, c.name as category_name 
            FROM {$this->expenses_table} e
            LEFT JOIN {$this->categories_table} c ON e.category_id = c.id
            WHERE e.user_id = %d";
        
        if ($category_id) {
            $query .= " AND e.category_id = %d";
        }
        
        $query .= " ORDER BY e.expense_date DESC
            LIMIT 100";

        return $wpdb->get_results($wpdb->prepare(
            $query,
            get_current_user_id(),
            $category_id
        ));
    }

    public function get_spending_summary() {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.name, SUM(e.amount) as total 
            FROM {$this->expenses_table} e
            JOIN {$this->categories_table} c ON e.category_id = c.id
            GROUP BY c.id
            ORDER BY total DESC"
        ));
    }
}
