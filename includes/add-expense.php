<?php
if (!defined('ABSPATH')) exit; // Prevent direct access

// Function to handle form submission
function finance_tracker_add_expense_page() {
    global $wpdb;

    if (isset($_POST['submit'])) {
        echo "<pre>";
        var_dump($_POST); // Debugging: Check received data
        echo "</pre>";

        // Sanitize input
        $category_name = isset($_POST['category']) ? trim($_POST['category']) : '';
        $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : 'N/A';
        $date = isset($_POST['date']) ? trim($_POST['date']) : '';
        $payment_method = !empty($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Cash';

        // Get user ID
        $user_id = get_current_user_id();
        if (!$user_id) {
            echo "<p class='error-msg'>Error: User not logged in.</p>";
            return;
        }

        // Fetch category ID from the categories table
        $category_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}finance_categories WHERE name = %s",
            $category_name
        ));

        if (!$category_id) {
            echo "<p class='error-msg'>Error: Invalid category.</p>";
            return;
        }

        // Validate input
        if (!empty($category_id) && !empty($amount) && !empty($date)) {
            // Insert expense into database
            $table_name = $wpdb->prefix . 'finance_expenses';
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'category_id' => $category_id,
                    'amount' => $amount,
                    'description' => $description,
                    'expense_date' => $date,
                    'payment_method' => $payment_method
                ),
                array('%d', '%d', '%f', '%s', '%s', '%s')
            );

            if ($wpdb->last_error) {
                echo "<p class='error-msg'>Error: " . $wpdb->last_error . "</p>";
            } else {
                echo "<p class='success-msg'>Expense added successfully!</p>";
            }
        } else {
            echo "<p class='error-msg'>Please fill in all required fields.</p>";
        }
    }
    ?>

    <div class="expense-form-container">
        <h2>Add Expense</h2>
        <form method="POST" class="expense-form">
            <label>Category:</label>
            <input type="text" name="category" required>

            <label>Amount:</label>
            <input type="number" step="0.01" name="amount" required>

            <label>Date:</label>
            <input type="date" name="date" required>

            <label>Description:</label>
            <textarea name="description" rows="3"></textarea> <!-- Optional -->

            <label>Payment Method:</label>
            <select name="payment_method">
                <option value="Cash" selected>Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>

            <input type="submit" name="submit" value="Add Expense" class="submit-btn">
        </form>
    </div>

    <style>
        .expense-form-container {
            max-width: 400px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .expense-form label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        .expense-form input, .expense-form textarea, .expense-form select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .submit-btn {
            background: #28a745;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 15px;
            cursor: pointer;
            border-radius: 5px;
        }
        .submit-btn:hover {
            background: #218838;
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
    </style>
    <?php
}

// Register Admin Menu
function finance_tracker_register_menu() {
    add_menu_page("Add Expense", "Add Expense", "manage_options", "add-expense", "finance_tracker_add_expense_page");
}
add_action("admin_menu", "finance_tracker_register_menu");
?>
