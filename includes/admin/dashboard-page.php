<?php
$db = new FT_DB_Handler();
$summary = $db->get_spending_summary();
$total = array_sum(array_column($summary, 'total'));
?>
<div class="wrap ft-dashboard">
    <h1><?php esc_html_e('Financial Dashboard', 'finance-tracker') ?></h1>
    
    <div class="ft-summary">
        <h3><?php esc_html_e('Spending Summary by Category', 'finance-tracker') ?></h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Category', 'finance-tracker') ?></th>
                    <th><?php esc_html_e('Total', 'finance-tracker') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item->name); ?></td>
                        <td><?php echo esc_html(number_format($item->total, 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="ft-total">
        <h3><?php esc_html_e('Total Spending', 'finance-tracker') ?></h3>
        <div class="amount">
            <?php echo esc_html(number_format($total, 2)); ?>
        </div>
    </div>
    
    <canvas id="ft-spending-chart"></canvas>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('ft-spending-chart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(wp_list_pluck($summary, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(wp_list_pluck($summary, 'total')); ?>,
                    backgroundColor: [
                        '#4dc9f6',
                        '#f67019',
                        '#f53794',
                        '#537bc4',
                        '#acc236',
                        '#166a8f'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
    </script>
</div>
