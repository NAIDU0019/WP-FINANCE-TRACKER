

<?php
if (!class_exists('FT_DB_Handler')) {
    require_once plugin_dir_path(__FILE__) . '/../classes/FT_DB_Handler.php';
}



$db = new FT_DB_Handler();
$categories = $db->get_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ft_add_expense') || !current_user_can('manage_options')) {
        wp_die(esc_html__('Security check failed', 'finance-tracker'));
    }

    // Sanitize and validate input data
    $data = [
        'amount' => (float) sanitize_text_field($_POST['amount']),
        'category_id' => (int) sanitize_text_field($_POST['category_id']),
        'expense_date' => sanitize_text_field($_POST['expense_date']),
        'description' => sanitize_textarea_field($_POST['description'])
    ];

    // Validate category
    if ($data['category_id'] <= 0) {
        echo '<div class="notice notice-error"><p>' 
             . esc_html__('Please select a valid category.', 'finance-tracker') 
             . '</p></div>';
        return;
    }

    // Validate amount
    if ($data['amount'] <= 0) {
        echo '<div class="notice notice-error"><p>' 
             . esc_html__('Amount must be a positive number.', 'finance-tracker') 
             . '</p></div>';
        return;
    }

    // Validate expense date
    $expense_date = DateTime::createFromFormat('Y-m-d', $data['expense_date']);
    if ($expense_date === false || $expense_date > new DateTime()) {
        echo '<div class="notice notice-error"><p>' 
             . esc_html__('Expense date must be today or in the past.', 'finance-tracker') 
             . '</p></div>';
        return;
    }

    // Insert data into the database
    if ($db->add_expense($data)) {
        echo '<div class="notice notice-success"><p>' 
             . esc_html__('Your expense has been added successfully!', 'finance-tracker') 
             . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' 
             . esc_html__('There was an error adding your expense. Please try again.', 'finance-tracker') 
             . '</p></div>';
    }
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Add New Expense', 'finance-tracker') ?></h1>
    
    <form method="POST" class="ft-form" id="ft-expense-form">
        <?php wp_nonce_field('ft_add_expense'); ?>
        
        <div class="form-group">
            <label><?php esc_html_e('Amount', 'finance-tracker') ?></label>
            <input type="number" step="0.01" name="amount" required 
                   min="0.01" value="">
        </div>
        
        <div class="form-group">
            <label><?php esc_html_e('Category', 'finance-tracker') ?></label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->id); ?>">
                    <?php echo esc_html($cat->name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label><?php esc_html_e('Date', 'finance-tracker') ?></label>
            <input type="date" name="expense_date" required 
                   value="<?php echo esc_attr(date('Y-m-d')); ?>">
        </div>
        
        <div class="form-group">
            <label><?php esc_html_e('Description', 'finance-tracker') ?></label>
            <textarea name="description" rows="3"></textarea>
        </div>
        
        <button type="submit" class="button button-primary">
            <?php esc_html_e('Save Expense', 'finance-tracker') ?>
        </button>
    </form>
</div>
                