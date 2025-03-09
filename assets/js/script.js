jQuery(document).ready(function($) {
    // Handle expense form submission
    $('#ft-expense-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.text();
        
        // Show loading state
        $submitBtn.prop('disabled', true).text('Processing...');
        
        // Get form data
        const formData = {
            action: 'ft_add_expense',
            amount: $form.find('input[name="amount"]').val(),
            category_id: $form.find('select[name="category_id"]').val(),
            expense_date: $form.find('input[name="expense_date"]').val(),
            description: $form.find('textarea[name="description"]').val(),
            nonce: ft_admin.nonce
        };

        // AJAX submission
        $.post(ft_admin.ajax_url, formData)
            .done(function(response) {
                if (response.success) {
                    // Show success message
                    $form.prepend(
                        `<div class="notice notice-success"><p>${response.data.message}</p></div>`
                    );
                    
                    // Clear form fields
                    $form[0].reset();
                } else {
                    // Show error message
                    $form.prepend(
                        `<div class="notice notice-error"><p>${response.data.message}</p></div>`
                    );
                }
            })
            .fail(function(error) {
                $form.prepend(
                    `<div class="notice notice-error"><p>Error submitting expense: ${error.statusText}</p></div>`
                );
            })
            .always(function() {
                // Restore button state
                $submitBtn.prop('disabled', false).text(originalBtnText);
                
                // Remove notices after 5 seconds
                setTimeout(() => {
                    $form.find('.notice').fadeOut();
                }, 5000);
            });
    });
});