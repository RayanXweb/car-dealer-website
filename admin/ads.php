<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$db = db();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $ad = $db->query("SELECT image FROM ads WHERE id = $id")->fetch_assoc();
    if ($ad && $ad['image']) {
        deleteImage($ad['image']);
    }
    $db->query("DELETE FROM ads WHERE id = $id");
    setFlash('success', 'Iklan berhasil dihapus');
    header('Location: ads.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = sanitize($_POST['title']);
    $link = sanitize($_POST['link']);
    $position = sanitize($_POST['position']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImage($_FILES['image']);
        if ($upload['success']) {
            $image = $upload['filename'];
        }
    }
    
    if ($id > 0) {
        $sql = "UPDATE ads SET title=?, link=?, position=?, is_active=?, start_date=?, end_date=?";
        $params = [$title, $link, $position, $is_active, $start_date, $end_date];
        $types = "sssiss";
        
        if ($image) {
            $oldAd = $db->query("SELECT image FROM ads WHERE id = $id")->fetch_assoc();
            if ($oldAd && $oldAd['image']) {
                deleteImage($oldAd['image']);
            }
            $sql .= ", image=?";
            $params[] = $image;
            $types .= "s";
        }
        
        $sql .= " WHERE id=?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        setFlash('success', 'Iklan berhasil diupdate');
    } else {
        $stmt = $db->prepare("INSERT INTO ads (title, image, link, position, is_active, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiss", $title, $image, $link, $position, $is_active, $start_date, $end_date);
        $stmt->execute();
        setFlash('success', 'Iklan berhasil ditambahkan');
    }
    
    header('Location: ads.php');
    exit();
}

$ads = $db->query("SELECT * FROM ads ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$editAd = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editAd = $db->query("SELECT * FROM ads WHERE id = $id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Iklan - Admin</title>
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
                <h1 class="h2">Kelola Iklan</h1>
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#adModal">
                    <i class="fas fa-plus"></i> Tambah Iklan
                </button>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <?php foreach ($ads as $ad): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <?php if ($ad['image']): ?>
                            <img src="../uploads/<?php echo $ad['image']; ?>" class="card-img-top" style="height:150px;object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h6 class="fw-bold"><?php echo $ad['title']; ?></h6>
                            <p class="small text-muted mb-1">
                                <i class="fas fa-map-pin"></i> <?php echo ucfirst($ad['position']); ?>
                            </p>
                            <p class="small text-muted mb-1">
                                <i class="fas fa-eye"></i> <?php echo number_format($ad['impressions']); ?> impressions
                            </p>
                            <p class="small text-muted mb-1">
                                <i class="fas fa-mouse-pointer"></i> <?php echo number_format($ad['clicks']); ?> clicks
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-<?php echo $ad['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $ad['is_active'] ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                                <div>
                                    <a href="ads.php?edit=<?php echo $ad['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#adModal">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="ads.php?delete=<?php echo $ad['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($ads)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-ad fa-4x text-muted mb-3"></i>
                    <h4>Belum ada iklan</h4>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Ad Modal -->
<div class="modal fade" id="adModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><?php echo $editAd ? 'Edit Iklan' : 'Tambah Iklan'; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $editAd['id'] ?? 0; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $editAd['title'] ?? ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if ($editAd && $editAd['image']): ?>
                            <small class="text-muted">Current: <?php echo $editAd['image']; ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control" value="<?php echo $editAd['link'] ?? ''; ?>" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Posisi</label>
                        <select name="position" class="form-control">
                            <option value="home" <?php echo ($editAd['position'] ?? '') === 'home' ? 'selected' : ''; ?>>Home</option>
                            <option value="sidebar" <?php echo ($editAd['position'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
                            <option value="footer" <?php echo ($editAd['position'] ?? '') === 'footer' ? 'selected' : ''; ?>>Footer</option>
                            <option value="popup" <?php echo ($editAd['position'] ?? '') === 'popup' ? 'selected' : ''; ?>>Popup</option>
                            <option value="banner" <?php echo ($editAd['position'] ?? '') === 'banner' ? 'selected' : ''; ?>>Banner</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?php echo ($editAd['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Mulai</label>
                            <input type="datetime-local" name="start_date" class="form-control" value="<?php echo $editAd['start_date'] ?? ''; ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sampai</label>
                            <input type="datetime-local" name="end_date" class="form-control" value="<?php echo $editAd['end_date'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
<script>
<?php if (isset($_GET['edit'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('adModal'));
    modal.show();
});
<?php endif; ?>
</script>
</body>
</html>
