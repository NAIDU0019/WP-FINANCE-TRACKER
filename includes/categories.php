<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
define('FINANCE_CATEGORIES_TABLE', $wpdb->prefix . 'finance_categories');

function finance_tracker_categories_page() {
    global $wpdb;

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $category_name = strtoupper(trim(sanitize_text_field($_POST['category_name'])));
        $slug = strtolower(str_replace(' ', '-', $category_name)); // Generate slug

        // Check if category already exists (case-insensitive)
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM " . FINANCE_CATEGORIES_TABLE . " WHERE UPPER(name) = UPPER(%s)", 
            $category_name
        ));

        if ($existing > 0) {
            echo "<p class='error-msg'>Category already exists!</p>";
        } else {
            $wpdb->insert(FINANCE_CATEGORIES_TABLE, ['name' => $category_name, 'slug' => $slug]);
            echo "<p class='success-msg'>Category added successfully!</p>";
            echo "<meta http-equiv='refresh' content='0'>"; // Refresh to show the updated list
        }
    }

    // Fetch existing categories
    $categories = $wpdb->get_results("SELECT * FROM " . FINANCE_CATEGORIES_TABLE . " ORDER BY name ASC");

    echo "<h2>Expense Categories</h2>";
    echo "<p>Example categories: Food, Transport, Entertainment, Bills.</p>";

    // Category Form
    echo '<div class="category-form-container">
            <h3>Add New Category</h3>
            <form method="POST" class="category-form">
                <label>Category Name:</label>
                <input type="text" name="category_name" required>
                <input type="submit" name="submit" value="Add Category" class="submit-btn">
            </form>
          </div>';

    // Display Categories
    if ($categories) {
        echo "<h3>Existing Categories</h3>";
        echo "<ul class='category-list'>";
        foreach ($categories as $category) {
            echo "<li>" . esc_html($category->name) . "</li>"; // Changed from category_name to name
        }
        echo "</ul>";
    } else {
        echo "<p>No categories added yet.</p>";
    }
}

// Register the submenu page
function finance_tracker_register_categories_menu() {
    add_submenu_page("add-expense", "Categories", "Categories", "manage_options", "categories", "finance_tracker_categories_page");
}
add_action("admin_menu", "finance_tracker_register_categories_menu");

?>

<style>
    .category-form-container {
        max-width: 400px;
        margin: auto;
        background: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }
    .category-form label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
    }
    .category-form input {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .submit-btn {
        background: #007bff;
        color: white;
        padding: 10px;
        border: none;
        margin-top: 15px;
        cursor: pointer;
        border-radius: 5px;
    }
    .submit-btn:hover {
        background: #0056b3;
    }
    .success-msg {
        color: green;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .error-msg {
        color: red;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .category-list {
        list-style-type: none;
        padding: 0;
    }
    .category-list li {
        background: #eee;
        padding: 8px;
        margin: 5px 0;
        border-radius: 5px;
    }
</style>
