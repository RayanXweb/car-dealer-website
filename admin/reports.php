<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$db = db();

// Date filter
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$salesData = getSalesReport($startDate, $endDate);
$popularProducts = getPopularProducts(10);

// Summary
$totalOrders = array_sum(array_column($salesData, 'total_orders'));
$totalRevenue = array_sum(array_column($salesData, 'total_revenue'));
$avgOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Penjualan</h1>
            </div>
            
            <!-- Filter -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-auto">
                            <label class="form-label">Dari</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">Sampai</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
                        </div>
                        <div class="col-auto align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="value"><?php echo number_format($totalOrders); ?></div>
                        <div class="label">Total Pesanan</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="value"><?php echo formatCurrency($totalRevenue); ?></div>
                        <div class="label">Total Pendapatan</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="value"><?php echo formatCurrency($avgOrder); ?></div>
                        <div class="label">Rata-rata Pesanan</div>
                    </div>
                </div>
            </div>
            
            <!-- Sales Chart Data -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0">Grafik Penjualan Harian</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Sales Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0">Detail Penjualan Harian</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah Pesanan</th>
                                    <th>Total Pendapatan</th>
                                    <th>Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salesData as $row): ?>
                                <tr>
                                    <td><?php echo formatDate($row['date'], 'd/m/Y'); ?></td>
                                    <td><?php echo $row['total_orders']; ?></td>
                                    <td><?php echo formatCurrency($row['total_revenue']); ?></td>
                                    <td><?php echo formatCurrency($row['average_order']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($salesData)): ?>
                                <tr><td colspan="4" class="text-center py-3">Tidak ada data</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Popular Products -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0">Produk Terlaris</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Produk</th>
                                    <th>Terjual</th>
                                    <th>Harga</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($popularProducts as $product): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['total_sold'] ?? 0; ?> unit</td>
                                    <td><?php echo formatCurrency($product['price']); ?></td>
                                    <td><?php echo formatCurrency(($product['total_sold'] ?? 0) * $product['price']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($popularProducts)): ?>
                                <tr><td colspan="5" class="text-center py-3">Belum ada data produk terjual</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const labels = <?php echo json_encode(array_column($salesData, 'date')); ?>;
    const data = <?php echo json_encode(array_column($salesData, 'total_revenue')); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: data,
                borderColor: '#D61C1C',
                backgroundColor: 'rgba(214, 28, 28, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
</body>
</html>
