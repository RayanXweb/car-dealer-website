<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$db = db();
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$logs = getActivityLogs($perPage, $offset);
$totalLogs = $db->query("SELECT COUNT(*) as total FROM activity_logs")->fetch_assoc()['total'];
$totalPages = ceil($totalLogs / $perPage);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Admin</title>
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
                <h1 class="h2">Activity Log</h1>
                <a href="logs.php?clear=1" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus semua log?')">
                    <i class="fas fa-trash"></i> Hapus Semua
                </a>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = $offset + 1; foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo $log['username'] ?? 'Guest'; ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $log['action']; ?></span>
                                    </td>
                                    <td><?php echo truncateText($log['description'], 80); ?></td>
                                    <td><?php echo $log['ip_address']; ?></td>
                                    <td><?php echo formatTimeAgo($log['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($logs)): ?>
                                <tr><td colspan="6" class="text-center py-3">Belum ada aktivitas</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
</body>
</html>
