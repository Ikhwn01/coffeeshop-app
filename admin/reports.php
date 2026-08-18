<?php
// admin/reports.php - Financial Reports & Analytics (Laporan Keuangan)
require_once __DIR__ . '/../config/database.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Date Filter Handling (Default: current month)
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-t');

// 1. Total Gross Income (From Paid Orders)
$stmtInc = $pdo->prepare("SELECT SUM(total_amount) as total, COUNT(*) as cnt FROM orders WHERE payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ?");
$stmtInc->execute([$start_date, $end_date]);
$inc_row = $stmtInc->fetch();
$total_income = floatval($inc_row['total'] ?? 0);
$total_orders = intval($inc_row['cnt'] ?? 0);

// 2. Total Operational Expenses
$stmtExp = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE expense_date BETWEEN ? AND ?");
$stmtExp->execute([$start_date, $end_date]);
$total_expenses = floatval($stmtExp->fetch()['total'] ?? 0);

// 3. Net Profit / Loss
$net_profit = $total_income - $total_expenses;

// 4. Daily Trend Data for Chart.js
$stmtTrendInc = $pdo->prepare("SELECT DATE(created_at) as date, SUM(total_amount) as total FROM orders WHERE payment_status = 'paid' AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY date ASC");
$stmtTrendInc->execute([$start_date, $end_date]);
$daily_income_rows = $stmtTrendInc->fetchAll();

$stmtTrendExp = $pdo->prepare("SELECT expense_date as date, SUM(amount) as total FROM expenses WHERE expense_date BETWEEN ? AND ? GROUP BY expense_date ORDER BY date ASC");
$stmtTrendExp->execute([$start_date, $end_date]);
$daily_exp_rows = $stmtTrendExp->fetchAll();

// Map dates
$dates_map = [];
foreach ($daily_income_rows as $r) {
    $dates_map[$r['date']]['income'] = floatval($r['total']);
}
foreach ($daily_exp_rows as $r) {
    $dates_map[$r['date']]['expense'] = floatval($r['total']);
}
ksort($dates_map);

$chart_labels  = [];
$chart_income  = [];
$chart_expense = [];
foreach ($dates_map as $dt => $val) {
    $chart_labels[]  = date('d M', strtotime($dt));
    $chart_income[]  = $val['income'] ?? 0;
    $chart_expense[] = $val['expense'] ?? 0;
}

// 5. Top Selling Products
$stmtTop = $pdo->prepare("SELECT p.name, c.name as category_name, SUM(oi.quantity) as total_qty, SUM(oi.subtotal) as total_revenue FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN products p ON oi.product_id = p.id JOIN categories c ON p.category_id = c.id WHERE o.payment_status = 'paid' AND DATE(o.created_at) BETWEEN ? AND ? GROUP BY oi.product_id, p.name, c.name ORDER BY total_qty DESC LIMIT 5");
$stmtTop->execute([$start_date, $end_date]);
$top_products = $stmtTop->fetchAll();

$page_title = __('admin_reports', 'Laporan Keuangan & Analitik Cafe');
$admin_active = "reports";

require_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Filter & Export Header -->
<div style="background: #ffffff; border: 1px solid var(--admin-border); padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm);">
    <form action="reports.php" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div>
            <label style="font-size: 0.8rem; color: var(--admin-muted); display: block; margin-bottom: 0.25rem; font-weight: 600;"><?php echo get_current_lang() == 'en' ? 'Start Date:' : 'Dari Tanggal:'; ?></label>
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="form-control" style="width: auto;">
        </div>

        <div>
            <label style="font-size: 0.8rem; color: var(--admin-muted); display: block; margin-bottom: 0.25rem; font-weight: 600;"><?php echo get_current_lang() == 'en' ? 'End Date:' : 'Sampai Tanggal:'; ?></label>
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="form-control" style="width: auto;">
        </div>

        <button type="submit" style="background: var(--admin-accent); color: #ffffff; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 1.2rem; display: flex; align-items: center; gap: 6px;">
            <i class="fas fa-filter"></i> <?php echo get_current_lang() == 'en' ? 'Generate Report' : 'Tampilkan Laporan'; ?>
        </button>
    </form>

    <button onclick="window.print()" style="background: #ffffff; border: 1px solid #cbd5e1; color: var(--admin-text-main); padding: 0.65rem 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 6px; box-shadow: var(--shadow-sm);">
        <i class="fas fa-print" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Print / Export PDF' : 'Cetak / Export PDF'; ?>
    </button>
</div>

<!-- Financial KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? 'Total Gross Revenue' : 'Total Pemasukan Kotor'; ?></h4>
            <div class="stat-value" style="color: #059669;"><?php echo format_rupiah($total_income); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fef2f2; color: #dc2626;">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? 'Total Expenses' : 'Total Pengeluaran Kas'; ?></h4>
            <div class="stat-value" style="color: #dc2626;"><?php echo format_rupiah($total_expenses); ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: var(--admin-accent-light); color: var(--admin-accent-dark);">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? 'Net Profit / (Loss)' : 'Laba Bersih (Net Profit)'; ?></h4>
            <div class="stat-value" style="color: <?php echo $net_profit >= 0 ? '#059669' : '#dc2626'; ?>;">
                <?php echo format_rupiah($net_profit); ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-details">
            <h4><?php echo get_current_lang() == 'en' ? 'Completed Orders' : 'Total Transaksi Lunas'; ?></h4>
            <div class="stat-value"><?php echo $total_orders; ?> <?php echo get_current_lang() == 'en' ? 'Orders' : 'Transaksi'; ?></div>
        </div>
    </div>
</div>

<!-- Financial Visual Chart Section -->
<div style="background: #ffffff; border: 1px solid var(--admin-border); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
    <h3 style="color: var(--admin-text-main); font-size: 1.1rem; margin-bottom: 1.5rem;">
        <i class="fas fa-chart-area" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Daily Revenue vs Expenses Comparison Chart' : 'Grafik Perbandingan Pemasukan vs Pengeluaran Harian'; ?>
    </h3>

    <div style="position: relative; height: 320px; width: 100%;">
        <canvas id="financialChart"></canvas>
    </div>
</div>

<!-- Top Selling Products Table -->
<div class="card-table">
    <div class="card-header">
        <h3><i class="fas fa-trophy" style="color: var(--admin-accent);"></i> <?php echo get_current_lang() == 'en' ? 'Top 5 Best Selling Items' : '5 Produk Menu Terlaris'; ?></h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ranking</th>
                    <th><?php echo get_current_lang() == 'en' ? 'Menu Item' : 'Nama Menu'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Category' : 'Kategori'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Total Sold (Qty)' : 'Total Terjual (Qty)'; ?></th>
                    <th><?php echo get_current_lang() == 'en' ? 'Total Sales Turnover' : 'Total Omset Penjualan'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_products)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--admin-muted); padding: 3rem;"><?php echo get_current_lang() == 'en' ? 'No sales data for this period.' : 'Belum ada data penjualan pada periode ini.'; ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($top_products as $idx => $tp): ?>
                        <tr>
                            <td><strong style="color: var(--admin-accent-dark); font-size: 1rem;">#<?php echo $idx + 1; ?></strong></td>
                            <td><strong style="color: var(--admin-text-main);"><?php echo htmlspecialchars($tp['name']); ?></strong></td>
                            <td><span class="badge" style="background: #f1f5f9; color: var(--admin-text); border: 1px solid #cbd5e1;"><?php echo htmlspecialchars($tp['category_name']); ?></span></td>
                            <td><strong><?php echo $tp['total_qty']; ?> <?php echo get_current_lang() == 'en' ? 'Cups/Portions' : 'Porsi'; ?></strong></td>
                            <td><strong style="color: #059669;"><?php echo format_rupiah($tp['total_revenue']); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('financialChart').getContext('2d');
    
    const chartLabels = <?php echo json_encode($chart_labels); ?>;
    const chartIncome = <?php echo json_encode($chart_income); ?>;
    const chartExpense = <?php echo json_encode($chart_expense); ?>;

    const labelIncome = "<?php echo get_current_lang() == 'en' ? 'Revenue (Rp)' : 'Pemasukan (Rp)'; ?>";
    const labelExpense = "<?php echo get_current_lang() == 'en' ? 'Expenses (Rp)' : 'Pengeluaran (Rp)'; ?>";

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: labelIncome,
                    data: chartIncome,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.08)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2.5
                },
                {
                    label: labelExpense,
                    data: chartExpense,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.08)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2.5
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#334155', font: { family: 'Plus Jakarta Sans', weight: '600' } }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#64748b' },
                    grid: { color: '#f1f5f9' }
                },
                y: {
                    ticks: { color: '#64748b' },
                    grid: { color: '#f1f5f9' }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
