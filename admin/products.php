<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireAdmin();

$db = db();
$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $product = getProduct($id);
    if ($product && $product['image']) {
        deleteImage($product['image']);
    }
    $db->query("DELETE FROM products WHERE id = $id");
    logActivity($_SESSION['user_id'], 'delete_product', "Deleted product ID: $id");
    setFlash('success', 'Produk berhasil dihapus');
    header('Location: products.php');
    exit();
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = sanitize($_POST['name']);
    $brand = sanitize($_POST['brand']);
    $model = sanitize($_POST['model']);
    $type = sanitize($_POST['type']);
    $year = intval($_POST['year'] ?? 0);
    $price = floatval($_POST['price']);
    $price_old = floatval($_POST['price_old'] ?? 0);
    $description = sanitize($_POST['description']);
    $specs = $_POST['specs'] ?? '';
    $color = sanitize($_POST['color'] ?? '');
    $transmission = sanitize($_POST['transmission'] ?? '');
    $fuel_type = sanitize($_POST['fuel_type'] ?? '');
    $engine_cc = intval($_POST['engine_cc'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $status = sanitize($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;
    
    // Image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImage($_FILES['image']);
        if ($upload['success']) {
            $image = $upload['filename'];
        } else {
            $error = $upload['error'];
        }
    }
    
    $specsJson = json_encode([
        'color' => $color,
        'transmission' => $transmission,
        'fuel_type' => $fuel_type,
        'engine_cc' => $engine_cc,
        'year' => $year
    ]);
    
    if ($id > 0) {
        // Update
        $sql = "UPDATE products SET 
            name=?, brand=?, model=?, type=?, year=?, price=?, price_old=?, 
            description=?, specs=?, stock=?, status=?, is_featured=?, is_new=?, is_hot=?";
        $params = [$name, $brand, $model, $type, $year, $price, $price_old, $description, $specsJson, $stock, $status, $is_featured, $is_new, $is_hot];
        $types = "ssssiddssiissi";
        
        if ($image) {
            // Delete old image
            $oldProduct = getProduct($id);
            if ($oldProduct && $oldProduct['image']) {
                deleteImage($oldProduct['image']);
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
        logActivity($_SESSION['user_id'], 'update_product', "Updated product: $name (ID: $id)");
        setFlash('success', 'Produk berhasil diupdate');
        header('Location: products.php');
        exit();
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO products (name, brand, model, type, year, price, price_old, description, specs, stock, status, is_featured, is_new, is_hot, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiddssdissis", $name, $brand, $model, $type, $year, $price, $price_old, $description, $specsJson, $stock, $status, $is_featured, $is_new, $is_hot, $image);
        $stmt->execute();
        logActivity($_SESSION['user_id'], 'add_product', "Added product: $name");
        setFlash('success', 'Produk berhasil ditambahkan');
        header('Location: products.php');
        exit();
    }
}

$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$editProduct = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $editProduct = getProduct($id);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin</title>
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
                <h1 class="h2">Kelola Produk</h1>
                <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>
            
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Brand</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                                     style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo $product['brand']; ?></td>
                            <td><?php echo formatCurrency($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo getStatusBadge($product['status']); ?></td>
                            <td>
                                <?php if ($product['is_featured']): ?>
                                    <i class="fas fa-star text-warning"></i>
                                <?php endif; ?>
                                <?php if ($product['is_new']): ?>
                                    <span class="badge bg-success">New</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#productModal">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus produk ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><?php echo $editProduct ? 'Edit Produk' : 'Tambah Produk'; ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $editProduct['id'] ?? 0; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo $editProduct['name'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand *</label>
                            <input type="text" name="brand" class="form-control" 
                                   value="<?php echo $editProduct['brand'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" 
                                   value="<?php echo $editProduct['model'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipe</label>
                            <input type="text" name="type" class="form-control" 
                                   value="<?php echo $editProduct['type'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="year" class="form-control" 
                                   value="<?php echo $editProduct['year'] ?? date('Y'); ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Harga *</label>
                            <input type="number" name="price" class="form-control" 
                                   value="<?php echo $editProduct['price'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Harga Lama</label>
                            <input type="number" name="price_old" class="form-control" 
                                   value="<?php echo $editProduct['price_old'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" 
                                   value="<?php echo $editProduct['stock'] ?? 0; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transmisi</label>
                            <select name="transmission" class="form-control">
                                <option value="Manual" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['transmission'] ?? '' : '') === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                <option value="Otomatis" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['transmission'] ?? '' : '') === 'Otomatis' ? 'selected' : ''; ?>>Otomatis</option>
                                <option value="CVT" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['transmission'] ?? '' : '') === 'CVT' ? 'selected' : ''; ?>>CVT</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bahan Bakar</label>
                            <select name="fuel_type" class="form-control">
                                <option value="Bensin" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['fuel_type'] ?? '' : '') === 'Bensin' ? 'selected' : ''; ?>>Bensin</option>
                                <option value="Diesel" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['fuel_type'] ?? '' : '') === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                                <option value="Listrik" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['fuel_type'] ?? '' : '') === 'Listrik' ? 'selected' : ''; ?>>Listrik</option>
                                <option value="Hybrid" <?php echo ($editProduct['specs'] ? json_decode($editProduct['specs'], true)['fuel_type'] ?? '' : '') === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo $editProduct['description'] ?? ''; ?></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="available" <?php echo ($editProduct['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="sold" <?php echo ($editProduct['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Terjual</option>
                                <option value="coming" <?php echo ($editProduct['status'] ?? '') === 'coming' ? 'selected' : ''; ?>>Segera</option>
                                <option value="preorder" <?php echo ($editProduct['status'] ?? '') === 'preorder' ? 'selected' : ''; ?>>Pre-order</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gambar</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($editProduct && $editProduct['image']): ?>
                                <small class="text-muted">Current: <?php echo $editProduct['image']; ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured" 
                                           <?php echo ($editProduct['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">Unggulan</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_new" class="form-check-input" id="is_new" 
                                           <?php echo ($editProduct['is_new'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_new">Baru</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_hot" class="form-check-input" id="is_hot" 
                                           <?php echo ($editProduct['is_hot'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_hot">HOT</label>
                                </div>
                            </div>
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
// Auto open modal when edit
<?php if (isset($_GET['edit'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('productModal'));
        modal.show();
    });
<?php endif; ?>
</script>
</body>
</html>
