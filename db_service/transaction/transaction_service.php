<?php
// action/transaksi/proses_transaksi.php
session_start();

include '../../config/koneksi.php';

function redirect_with_status($status, $message = '') {
    $url = '../../pages/transaksi.php?status=' . urlencode($status);
    if ($message) {
        $url .= '&message=' . urlencode($message);
    }
    header("Location: " . $url);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_transaction'])) {

    $transactionCode = $_POST['transactionCode'] ?? null;
    $customerName = trim($_POST['customerName'] ?? '');
    $totalPrice = filter_var($_POST['totalPrice'] ?? '', FILTER_VALIDATE_FLOAT); // Total seluruh transaksi
    $paidAmount = filter_var($_POST['paidAmount'] ?? '', FILTER_VALIDATE_FLOAT);

    $changeAmount = $paidAmount - $totalPrice;

    // *** PERUBAHAN DI SINI ***
    // Ambil string JSON dari hidden input
    $products_json = $_POST['products_json'] ?? '[]';
    // Dekode string JSON menjadi array PHP
    $products_in_cart = json_decode($products_json, true);

    // Validasi apakah json_decode berhasil atau produk_json tidak kosong
    if ($products_in_cart === null && $products_json !== 'null') { // json_decode gagal atau bukan JSON valid
        redirect_with_status('invalid_data', 'Format data produk tidak valid.');
    }
    // Pastikan array produk tidak kosong
    if (empty($products_in_cart)) {
        redirect_with_status('invalid_data', 'Keranjang masih kosong.');
    }

    // --- Validasi Input Utama (sesuaikan dengan $products_in_cart yang sudah didekode) ---
    if (empty($transactionCode) || empty($customerName) || $totalPrice === false || 
        $paidAmount === false) { // $products_in_cart sudah divalidasi terpisah
        redirect_with_status('invalid_data', 'Data transaksi utama tidak lengkap atau tidak valid.');
    }

    if ($paidAmount < $totalPrice) {
        redirect_with_status('invalid_data', 'Jumlah bayar tidak mencukupi.');
    }

    mysqli_begin_transaction($link);

    try {
        // 1. Insert ke tabel `transactions`
        $stmt_transaction = mysqli_prepare($link, "INSERT INTO transactions (transactionCode, customerName, totalPrice, paidAmount, changeAmount) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_transaction === false) {
            throw new Exception("Error preparing transaction insert: " . mysqli_error($link));
        }
        mysqli_stmt_bind_param($stmt_transaction, "ssddd", $transactionCode, $customerName, $totalPrice, $paidAmount, $changeAmount);
        if (!mysqli_stmt_execute($stmt_transaction)) {
            throw new Exception("Error executing transaction insert: " . mysqli_error($link));
        }
        $transactionId = mysqli_insert_id($link);
        mysqli_stmt_close($stmt_transaction);

        // 2. Loop melalui produk di keranjang, insert ke `detail_transactions` dan update stok
        $stmt_detail = mysqli_prepare($link, "INSERT INTO detail_transactions (transactionId, productId, quantity, totalPrice) VALUES (?, ?, ?, ?)");
        $stmt_update_stock = mysqli_prepare($link, "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt_get_stock = mysqli_prepare($link, "SELECT stock FROM products WHERE id = ?");
        $product_price_query = mysqli_prepare($link, "SELECT price FROM products WHERE id = ?");

        if ($stmt_detail === false || $stmt_update_stock === false || $stmt_get_stock === false || $product_price_query === false) {
            throw new Exception("Error preparing detail/stock statements: " . mysqli_error($link));
        }

        foreach ($products_in_cart as $item) {
            $productId = filter_var($item['id'] ?? '', FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? '', FILTER_VALIDATE_INT);

            if ($productId === false || $quantity === false || $quantity <= 0) {
                throw new Exception("Data produk tidak valid di keranjang.");
            }

            mysqli_stmt_bind_param($stmt_get_stock, "i", $productId);
            mysqli_stmt_execute($stmt_get_stock);
            mysqli_stmt_bind_result($stmt_get_stock, $current_stock);
            mysqli_stmt_fetch($stmt_get_stock);
            mysqli_stmt_reset($stmt_get_stock); 

            if ($quantity > $current_stock) {
                throw new Exception("Stok untuk produk ID " . $productId . " tidak mencukupi. Hanya ada " . $current_stock . ".");
            }

            mysqli_stmt_bind_param($product_price_query, "i", $productId);
            mysqli_stmt_execute($product_price_query);
            mysqli_stmt_bind_result($product_price_query, $db_price);
            mysqli_stmt_fetch($product_price_query);
            mysqli_stmt_reset($product_price_query); 
            
            $item_subtotal = $quantity * $db_price;

            mysqli_stmt_bind_param($stmt_detail, "iiid", $transactionId, $productId, $quantity, $item_subtotal); 
            if (!mysqli_stmt_execute($stmt_detail)) {
                throw new Exception("Error executing transaction detail insert: " . mysqli_error($link));
            }

            mysqli_stmt_bind_param($stmt_update_stock, "iii", $quantity, $productId, $quantity); 
            if (!mysqli_stmt_execute($stmt_update_stock)) {
                throw new Exception("Error executing stock update: " . mysqli_error($link));
            }
            if (mysqli_stmt_affected_rows($stmt_update_stock) === 0) {
                 throw new Exception("Gagal memperbarui stok untuk produk ID " . $productId . ".");
            }
        }

        mysqli_stmt_close($stmt_detail);
        mysqli_stmt_close($stmt_update_stock);
        mysqli_stmt_close($stmt_get_stock);
        mysqli_stmt_close($product_price_query);

        mysqli_commit($link);
        redirect_with_status('transaction_success', 'Transaksi berhasil disimpan.');

    } catch (Exception $e) {
        mysqli_rollback($link);
        $error_message = $e->getMessage(); 
        redirect_with_status('transaction_failed', $error_message);
    }

} else {
    redirect_with_status('invalid_data', 'Akses tidak valid. Permintaan harus berupa POST dari form.');
}

mysqli_close($link);
exit();
?>