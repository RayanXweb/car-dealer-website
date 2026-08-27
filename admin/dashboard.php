<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$stats = getDashboardStats();
$db = db();

// Recent orders
$recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Recent users
$recentUsers = $db->query("SELECT id, username, full_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Recent activity
$recentLogs = getActivityLogs(10);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 40px rgba(0,0,0,0.08);
        }
        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 12px;
        }
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .stat-card .label {
            color: #999;
            font-size: 0.85rem;
        }
        .stat-card .change {
            font-size: 0.8rem;
            font-weight: 600;
        }
        .stat-card .change.up { color: #00C853; }
        .stat-card .change.down { color: #D32F2F; }
    </style>
</head>
<body>

<?php include 'includes/admin-header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="text-muted small">
                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d F Y'); ?>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon" style="background:#FFF3E0;color:#E65100;">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="value"><?php echo number_format($stats['products']); ?></div>
                        <div class="label">Total Produk</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon" style="background:#E3F2FD;color:#0D47A1;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="value"><?php echo number_format($stats['orders']); ?></div>
                        <div class="label">Total Pesanan</div>
                        <div class="change up">
                            <?php echo $stats['pending_orders']; ?> pending
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon" style="background:#E8F5E9;color:#1B5E20;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="value"><?php echo number_format($stats['users']); ?></div>
                        <div class="label">Total Pengguna</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">
                        <div class="icon" style="background:#FCE4EC;color:#880E4F;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="value"><?php echo formatCurrency($stats['revenue'] ?? 0); ?></div>
                        <div class="label">Total Pendapatan</div>
                        <div class="change up">
                            <?php echo formatCurrency($stats['revenue_month'] ?? 0); ?> bulan ini
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Orders -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="fw-bold mb-0">Pesanan Terbaru</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Pelanggan</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="orders.php?view=<?php echo $order['id']; ?>" class="text-decoration-none">
                                                    <?php echo $order['order_number']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo $order['customer_name']; ?></td>
                                            <td><?php echo formatCurrency($order['final_amount']); ?></td>
                                            <td><?php echo getStatusBadge($order['status']); ?></td>
                                            <td><?php echo formatDate($order['created_at']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentOrders)): ?>
                                        <tr><td colspan="5" class="text-center py-3">Belum ada pesanan</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Side Info -->
                <div class="col-lg-4">
                    <!-- Recent Users -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="fw-bold mb-0">Pengguna Baru</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($recentUsers as $user): ?>
                            <div class="d-flex align-items-center py-2 border-bottom">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                     style="width:36px;height:36px;font-size:0.8rem;">
                                    <?php echo strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)); ?>
                                </div>
                                <div class="ms-2">
                                    <div class="fw-bold small"><?php echo $user['full_name'] ?? $user['username']; ?></div>
                                    <small class="text-muted"><?php echo $user['email']; ?></small>
                                </div>
                                <small class="ms-auto text-muted"><?php echo formatDate($user['created_at'], 'd/m'); ?></small>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($recentUsers)): ?>
                            <p class="text-muted text-center py-2 mb-0">Belum ada pengguna</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="fw-bold mb-0">Aktivitas Terbaru</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentLogs as $log): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="badge bg-secondary"><?php echo $log['action']; ?></span>
                                            <span class="small"><?php echo truncateText($log['description'], 30); ?></span>
                                        </div>
                                        <small class="text-muted"><?php echo formatTimeAgo($log['created_at']); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if (empty($recentLogs)): ?>
                                <div class="list-group-item text-center text-muted">Belum ada aktivitas</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
