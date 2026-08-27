<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$db = db();

// Generate transaction number
function generateTransactionNumber() {
    return 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
}

// Get all transactions with order info
$transactions = $db->query("
    SELECT t.*, o.order_number, o.customer_name 
    FROM transactions t 
    LEFT JOIN orders o ON t.order_id = o.id 
    ORDER BY t.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Admin</title>
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
                <h1 class="h2">Kelola Transaksi</h1>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Transaksi</th>
                                    <th>Order</th>
                                    <th>Pelanggan</th>
                                    <th>Jumlah</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $trx): ?>
                                <tr>
                                    <td><?php echo $trx['transaction_number']; ?></td>
                                    <td><?php echo $trx['order_number'] ?? '-'; ?></td>
                                    <td><?php echo $trx['customer_name'] ?? '-'; ?></td>
                                    <td><?php echo formatCurrency($trx['amount']); ?></td>
                                    <td><?php echo ucfirst($trx['method'] ?? 'Manual'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $trx['status'] === 'success' ? 'success' : ($trx['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($trx['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($trx['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($transactions)): ?>
                                <tr><td colspan="7" class="text-center py-3">Belum ada transaksi</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
