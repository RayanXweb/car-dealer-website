<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $customer_name = sanitize($_POST['customer_name']);
    $customer_phone = sanitize($_POST['customer_phone']);
    $customer_email = sanitize($_POST['customer_email'] ?? '');
    $offer_amount = floatval($_POST['offer_amount'] ?? 0);
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($customer_name) || empty($customer_phone) || $offer_amount <= 0) {
        $error = 'Semua field wajib diisi';
    } else {
        $product = getProduct($product_id);
        if (!$product) {
            $error = 'Produk tidak ditemukan';
        } else {
            $offerData = [
                'user_id' => isLoggedIn() ? $_SESSION['user_id'] : null,
                'product_id' => $product_id,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
                'message' => $message,
                'offer_amount' => $offer_amount
            ];
            
            if (createOffer($offerData)) {
                // Generate WhatsApp message
                $waMessage = "Halo, saya tertarik dengan mobil berikut:\n\n";
                $waMessage .= "🚗 *" . $product['name'] . "*\n";
                $waMessage .= "💰 Harga: " . formatCurrency($product['price']) . "\n";
                $waMessage .= "💬 Penawaran: " . formatCurrency($offer_amount) . "\n\n";
                $waMessage .= "Nama: " . $customer_name . "\n";
                $waMessage .= "📱 " . $customer_phone . "\n";
                if ($customer_email) {
                    $waMessage .= "📧 " . $customer_email . "\n";
                }
                if ($message) {
                    $waMessage .= "\nPesan: " . $message;
                }
                
                $waLink = generateWhatsAppLink(WHATSAPP_NUMBER, $waMessage);
                header("Location: $waLink");
                exit();
            } else {
                $error = 'Gagal mengirim penawaran';
            }
        }
    }
}

// If error, redirect back
if ($error) {
    $_SESSION['error'] = $error;
    header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'catalog.php');
    exit();
}
?>
