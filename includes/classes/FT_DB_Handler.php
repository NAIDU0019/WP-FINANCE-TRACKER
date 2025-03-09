<?php

class FT_DB_Handler {

    private $wpdb;
    public $table_expenses;
    public $table_categories;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_expenses = $wpdb->prefix . 'ft_expenses';
        $this->table_categories = $wpdb->prefix . 'ft_categories';

        // ✅ Automatically create tables if not exists
        $this->create_tables();
    }

    // ✅ 🚀 Automatically Create Tables (Expenses + Categories)
    public function create_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // ✅ Create Categories Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_categories} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";
        dbDelta($sql);

        // ✅ Create Expenses Table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_expenses} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id BIGINT(20) UNSIGNED NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            expense_date DATE NOT NULL,
            description TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES {$this->table_categories}(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;";
        dbDelta($sql);
    }

    // ✅ 💸 Fetch All Categories
    public function get_categories() {
        return $this->wpdb->get_results("SELECT * FROM {$this->table_categories}");
    }

    // ✅ 💯 Get Category By Name (to prevent duplicates)
    public function get_category_by_name($name) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_categories} WHERE name = %s", $name)
        );
    }

    // ✅ 💯 Add New Category (with slug)
    public function add_category($name, $slug) {
        // Prevent Duplicate Categories
        if ($this->get_category_by_name($name)) {
            return false;
        }

        return $this->wpdb->insert(
            $this->table_categories,
            [
                'name' => $name,
                'slug' => $slug
            ],
            ['%s', '%s']
        );
    }

    // ✅ 💯 Delete Category
    public function delete_category($id) {
        return $this->wpdb->delete($this->table_categories, ['id' => $id], ['%d']);
    }

    // ✅ 💯 Add Expense to Database
    public function add_expense($data) {
        return $this->wpdb->insert(
            $this->table_expenses,
            [
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'expense_date' => $data['expense_date'],
                'description' => $data['description']
            ],
            ['%d', '%f', '%s', '%s']
        );
    }

    // ✅ 💯 Fetch All Expenses (with Category Name)
    public function get_all_expenses() {
        $sql = "
        SELECT e.id, e.amount, e.expense_date, e.description, c.name AS category_name 
        FROM {$this->table_expenses} AS e
        LEFT JOIN {$this->table_categories} AS c ON e.category_id = c.id
        ORDER BY e.created_at DESC";
        
        return $this->wpdb->get_results($sql);
    }

    // ✅ 💯 Get Single Expense By ID
    public function get_expense_by_id($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_expenses} WHERE id = %d", $id)
        );
    }

    // ✅ 💯 Update Expense (Edit)
    public function update_expense($id, $data) {
        return $this->wpdb->update(
            $this->table_expenses,
            [
                'category_id' => $data['category_id'],
                'amount' => $data['amount'],
                'expense_date' => $data['expense_date'],
                'description' => $data['description']
            ],
            ['id' => $id],
            ['%d', '%f', '%s', '%s'],
            ['%d']
        );
    }

    // ✅ 💯 Delete Expense
    public function delete_expense($id) {
        return $this->wpdb->delete($this->table_expenses, ['id' => $id], ['%d']);
    }

    // ✅ 💯 Get Expenses By Date Range
    public function get_expenses_by_date($start_date, $end_date) {
        $sql = $this->wpdb->prepare("
            SELECT e.id, e.amount, e.expense_date, e.description, c.name AS category_name
            FROM {$this->table_expenses} AS e
            LEFT JOIN {$this->table_categories} AS c ON e.category_id = c.id
            WHERE e.expense_date BETWEEN %s AND %s
            ORDER BY e.expense_date ASC
        ", $start_date, $end_date);

        return $this->wpdb->get_results($sql);
    }

    // ✅ 💯 Get Total Expenses Amount
    public function get_total_expenses() {
        return $this->wpdb->get_var("SELECT SUM(amount) FROM {$this->table_expenses}");
    }

    // ✅ 💯 Delete All Expenses (used when category is deleted)
    public function delete_expenses_by_category($category_id) {
        return $this->wpdb->delete($this->table_expenses, ['category_id' => $category_id], ['%d']);
    }
}
