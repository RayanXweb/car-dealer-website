<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$db = db();

// Update user status
if (isset($_POST['update_status'])) {
    $userId = intval($_POST['user_id']);
    $status = sanitize($_POST['status']);
    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $userId);
    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], 'update_user_status', "User ID: $userId -> $status");
        setFlash('success', 'Status pengguna berhasil diupdate');
    }
    header('Location: users.php');
    exit();
}

// Delete user
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    if ($userId != $_SESSION['user_id']) { // Prevent self-delete
        $db->query("DELETE FROM users WHERE id = $userId");
        logActivity($_SESSION['user_id'], 'delete_user', "Deleted user ID: $userId");
        setFlash('success', 'Pengguna berhasil dihapus');
    } else {
        setFlash('error', 'Tidak dapat menghapus akun sendiri');
    }
    header('Location: users.php');
    exit();
}

$users = $db->query("SELECT id, username, email, full_name, phone, role, status, created_at FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin</title>
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
                <h1 class="h2">Kelola Pengguna</h1>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>HP</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['full_name'] ?? '-'; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['phone'] ?? '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'banned' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at'], 'd/m/Y'); ?></td>
                                    <td>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="status" class="form-select form-select-sm" style="width:auto;display:inline-block;">
                                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Aktif</option>
                                                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                                                <option value="banned" <?php echo $user['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
