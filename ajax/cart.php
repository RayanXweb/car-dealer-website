<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

$response = ['success' => false, 'error' => 'Invalid action'];

switch ($action) {
    case 'add':
        $product_id = intval($data['product_id'] ?? $_POST['product_id'] ?? 0);
        $quantity = intval($data['quantity'] ?? $_POST['quantity'] ?? 1);
        
        if (!$product_id) {
            $response = ['success' => false, 'error' => 'Produk tidak valid'];
            break;
        }
        
        $product = getProduct($product_id);
        if (!$product) {
            $response = ['success' => false, 'error' => 'Produk tidak ditemukan'];
            break;
        }
        
        if (isLoggedIn()) {
            $result = addToCart($product_id, $quantity, $_SESSION['user_id']);
        } else {
            $session_id = session_id();
            $result = addToCart($product_id, $quantity, null, $session_id);
        }
        
        if ($result) {
            $count = isLoggedIn() ? getCartCount($_SESSION['user_id']) : getCartCount(null, session_id());
            $response = [
                'success' => true,
                'count' => $count,
                'message' => 'Produk ditambahkan ke keranjang'
            ];
        } else {
            $response = ['success' => false, 'error' => 'Gagal menambahkan ke keranjang'];
        }
        break;
        
    case 'remove':
        $cart_id = intval($data['cart_id'] ?? $_POST['cart_id'] ?? 0);
        
        if (!$cart_id) {
            $response = ['success' => false, 'error' => 'ID keranjang tidak valid'];
            break;
        }
        
        if (isLoggedIn()) {
            $result = removeFromCart($cart_id, $_SESSION['user_id']);
        } else {
            $result = removeFromCart($cart_id, null, session_id());
        }
        
        if ($result) {
            $count = isLoggedIn() ? getCartCount($_SESSION['user_id']) : getCartCount(null, session_id());
            $total = isLoggedIn() ? getCartTotal($_SESSION['user_id']) : getCartTotal(null, session_id());
            $response = [
                'success' => true,
                'count' => $count,
                'total' => $total,
                'message' => 'Item dihapus dari keranjang'
            ];
        } else {
            $response = ['success' => false, 'error' => 'Gagal menghapus item'];
        }
        break;
        
    case 'update':
        $cart_id = intval($data['cart_id'] ?? $_POST['cart_id'] ?? 0);
        $quantity = intval($data['quantity'] ?? $_POST['quantity'] ?? 1);
        
        if (!$cart_id) {
            $response = ['success' => false, 'error' => 'ID keranjang tidak valid'];
            break;
        }
        
        if (isLoggedIn()) {
            $result = updateCartQuantity($cart_id, $quantity, $_SESSION['user_id']);
        } else {
            $result = updateCartQuantity($cart_id, $quantity, null, session_id());
        }
        
        if ($result) {
            $count = isLoggedIn() ? getCartCount($_SESSION['user_id']) : getCartCount(null, session_id());
            $total = isLoggedIn() ? getCartTotal($_SESSION['user_id']) : getCartTotal(null, session_id());
            $response = [
                'success' => true,
                'count' => $count,
                'total' => $total,
                'message' => 'Keranjang diperbarui'
            ];
        } else {
            $response = ['success' => false, 'error' => 'Gagal memperbarui keranjang'];
        }
        break;
        
    case 'clear':
        if (isLoggedIn()) {
            $result = clearCart($_SESSION['user_id']);
        } else {
            $result = clearCart(null, session_id());
        }
        
        if ($result) {
            $response = [
                'success' => true,
                'count' => 0,
                'total' => 0,
                'message' => 'Keranjang dikosongkan'
            ];
        } else {
            $response = ['success' => false, 'error' => 'Gagal mengosongkan keranjang'];
        }
        break;
        
    case 'get':
        if (isLoggedIn()) {
            $items = getCart($_SESSION['user_id']);
            $count = getCartCount($_SESSION['user_id']);
            $total = getCartTotal($_SESSION['user_id']);
        } else {
            $items = getCart(null, session_id());
            $count = getCartCount(null, session_id());
            $total = getCartTotal(null, session_id());
        }
        
        $response = [
            'success' => true,
            'items' => $items,
            'count' => $count,
            'total' => $total
        ];
        break;
        
    default:
        $response = ['success' => false, 'error' => 'Aksi tidak dikenal'];
}

echo json_encode($response);
exit();
?>
