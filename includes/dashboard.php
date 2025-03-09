<?php
if (!defined('ABSPATH')) exit;

function finance_tracker_dashboard_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "finance_expenses";

    // Fetch expenses from the database
    $expenses = $wpdb->get_results("
    SELECT e.id, e.amount, 
           COALESCE(c.name, 'Miscellaneous') AS category, 
           e.expense_date, e.description, 
           e.payment_method, e.created_at 
    FROM wp_finance_expenses e 
    LEFT JOIN wp_finance_categories c 
    ON e.category_id = c.id 
    ORDER BY e.id DESC
");



// Debugging: Print fetched data



    // Prepare data for the chart with unique categories
    $category_totals = [];

    foreach ($expenses as $expense) {
        $category_name = strtoupper($expense->category);
        if (!isset($category_totals[$category_name])) {
            $category_totals[$category_name] = 0;
        }
        $category_totals[$category_name] += $expense->amount;
    }

    // Remove categories with zero amount
    $filtered_categories = [];
    $filtered_amounts = [];
    
    foreach ($category_totals as $category => $amount) {
        if ($amount > 0) {
            $filtered_categories[] = $category;
            $filtered_amounts[] = $amount;
        }
    }

    if ($wpdb->last_error) {
        echo "<p class='error-msg'>Error: " . $wpdb->last_error . "</p>";
    }
    ?>

<div class="dashboard-container">
        <h2>Expense Dashboard</h2>
        <table class="expense-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Amount</th>
                    <th>Category ID</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Payment Method</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses): ?>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?php echo $expense->id; ?></td>
                            <td><?php echo number_format($expense->amount, 2); ?></td>
                            <td><?php echo $expense->category; ?></td>
                            <td><?php echo $expense->expense_date; ?></td>
                            <td><?php echo $expense->description; ?></td>
                            <td><?php echo $expense->payment_method; ?></td>
                            <td><?php echo $expense->created_at; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No expenses found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
        <!-- Expense Chart -->
        <div class="expense-chart">
            <h3>Expense Distribution</h3>
            <canvas id="expenseChart"></canvas>
        </div>
    </div>

    <!-- Load Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var ctx = document.getElementById("expenseChart").getContext("2d");
            var expenseChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: <?php echo json_encode($filtered_categories); ?>,
                    datasets: [{
                        label: "Amount Spent",
                        data: <?php echo json_encode($filtered_amounts); ?>,
                        backgroundColor: "rgba(75, 192, 192, 0.6)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>

    <style>
        .dashboard-container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .expense-table table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .expense-table th, .expense-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .expense-table th {
            background: #28a745;
            color: white;
        }
        .expense-table tr:nth-child(even) {
            background: #f2f2f2;
        }
        .expense-chart {
            margin-top: 20px;
            text-align: center;
        }
    </style>

    <?php
}

function finance_tracker_register_dashboard() {
    add_menu_page("Finance Dashboard", "Dashboard", "manage_options", "finance-dashboard", "finance_tracker_dashboard_page");
}
add_action("admin_menu", "finance_tracker_register_dashboard");
?>
