<?php
require_once plugin_dir_path(__FILE__) . '/../classes/FT_DB_Handler.php';

$db = new FT_DB_Handler();
$categories = $db->get_categories();

// ✅ Handle Add Category Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Security Check (Verify Nonce)
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ft_manage_categories')) {
        wp_die(esc_html__('Security check failed', 'finance-tracker'));
    }

    // ✅ Sanitize Inputs
    $name = sanitize_text_field($_POST['new_category']);
    $slug = sanitize_title($name);

    // ✅ Check if Category Name Exists
    if ($db->get_category_by_name($name)) {
        echo '<div class="notice notice-error"><p>' 
             . esc_html__('Category already exists!', 'finance-tracker') 
             . '</p></div>';
    } else {
        // ✅ Add Category Using add_category()
        $result = $db->add_category($name, $slug);

        if ($result) {
            echo '<div class="notice notice-success"><p>' 
                 . esc_html__('Category added successfully!', 'finance-tracker') 
                 . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' 
                 . esc_html__('Failed to add category!', 'finance-tracker') 
                 . '</p></div>';
        }
    }
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Manage Categories', 'finance-tracker') ?></h1>

    <div class="ft-categories">
        <div class="ft-existing-categories">
            <h2><?php esc_html_e('Existing Categories', 'finance-tracker') ?></h2>
            <ul id="ft-category-list">
                <?php if (empty($categories)): ?>
                    <li><?php esc_html_e('No categories added yet.', 'finance-tracker') ?></li>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <li data-id="<?php echo $cat->id; ?>">
                        <?php echo esc_html($cat->name); ?>
                        <span class="category-slug">(<?php echo esc_html($cat->slug); ?>)</span>
                        <button 
                            class="button button-link-delete"
                            onclick="deleteCategory(<?php echo $cat->id; ?>)">
                            <?php esc_html_e('Delete', 'finance-tracker') ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="ft-add-category">
            <h2><?php esc_html_e('Add New Category', 'finance-tracker') ?></h2>
            <form method="POST" id="ft-add-category-form">
                <?php wp_nonce_field('ft_manage_categories'); ?>
                <input type="text" name="new_category" required placeholder="Category Name">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Add Category', 'finance-tracker') ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('#ft-add-category-form');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            }).then(res => res.json())
              .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.data.message);
                }
            });
        });
    });

    function deleteCategory(categoryId) {
        if (!confirm('Are you sure you want to delete this category?')) return;

        fetch(ajaxurl, {
            method: 'POST',
            body: new URLSearchParams({
                action: 'ft_delete_category',
                category_id: categoryId,
                nonce: '<?php echo wp_create_nonce('ft_ajax_nonce'); ?>'
            })
        }).then(res => res.json())
          .then(data => {
              if (data.success) {
                  alert('Category deleted successfully!');
                  location.reload();
              } else {
                  alert('Failed to delete category');
              }
          });
    }
</script>
