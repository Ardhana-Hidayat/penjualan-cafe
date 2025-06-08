<?php
// action/transaksi/proses_transaksi.php
session_start();

include '../../config/koneksi.php';

require_once __DIR__ . '/../../vendor/autoload.php';

// --- KONFIGURASI MIDTRANS ---
\Midtrans\Config::$serverKey = 'SB-Mid-server-6BelgSK3SAgR1YuuNAJGekvv';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Fungsi helper untuk redirect dengan pesan status
function redirect_with_status($status, $message = '', $snap_token = null, $midtrans_order_id = null)
{
    $url = '../../pages/transaksi.php?status=' . urlencode($status);
    if ($message) {
        $url .= '&message=' . urlencode($message);
    }
    // Jika ada snap_token atau midtrans_order_id, simpan di session untuk digunakan frontend
    if ($snap_token) {
        $_SESSION['midtrans_snap_token'] = $snap_token;
        $_SESSION['midtrans_order_id'] = $midtrans_order_id; // Ini order_id dari transaksi lokal Anda
    }
    header("Location: " . $url);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_transaction'])) {

    $transactionCode = $_POST['transactionCode'] ?? null;
    $customerName = trim($_POST['customerName'] ?? '');
    $totalPrice = filter_var($_POST['totalPrice'] ?? '', FILTER_VALIDATE_FLOAT);
    $paidAmount = filter_var($_POST['paidAmount'] ?? '', FILTER_VALIDATE_FLOAT);

    $changeAmount = $paidAmount - $totalPrice;

    $products_json = $_POST['products_json'] ?? '[]';
    $products_in_cart = json_decode($products_json, true);

    // --- Validasi Input Utama ---
    if ($products_in_cart === null && $products_json !== 'null') {
        redirect_with_status('invalid_data', 'Format data produk tidak valid.');
    }
    if (empty($products_in_cart)) {
        redirect_with_status('invalid_data', 'Keranjang masih kosong.');
    }
    if (empty($transactionCode) || empty($customerName) || $totalPrice === false || $paidAmount === false) {
        redirect_with_status('invalid_data', 'Data transaksi utama tidak lengkap atau tidak valid.');
    }
    if ($paidAmount < $totalPrice) {
        redirect_with_status('invalid_data', 'Jumlah bayar tidak mencukupi.');
    }

    // --- Mulai Transaksi Database Lokal (ACID) ---
    mysqli_begin_transaction($link);

    try {
        // 1. Insert ke tabel `transactions` (TANPA midtrans_transaction_id dan payment_status)
        $stmt_transaction = mysqli_prepare($link, "INSERT INTO transactions (transactionCode, customerName, totalPrice, paidAmount, changeAmount) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_transaction === false) {
            throw new Exception("Error preparing transaction insert: " . mysqli_error($link));
        }
        // Sesuaikan bind_param: s (transactionCode), s (customerName), d (totalPrice), d (paidAmount), d (changeAmount)
        mysqli_stmt_bind_param($stmt_transaction, "ssddd", $transactionCode, $customerName, $totalPrice, $paidAmount, $changeAmount);
        if (!mysqli_stmt_execute($stmt_transaction)) {
            throw new Exception("Error executing transaction insert: " . mysqli_error($link));
        }
        $local_transaction_db_id = mysqli_insert_id($link);
        mysqli_stmt_close($stmt_transaction);

        // 2. Loop melalui produk di keranjang, insert ke `detail_transactions` dan update stok
        $stmt_detail = mysqli_prepare($link, "INSERT INTO detail_transactions (transactionId, productId, quantity, totalPrice) VALUES (?, ?, ?, ?)");
        $stmt_update_stock = mysqli_prepare($link, "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt_get_stock = mysqli_prepare($link, "SELECT stock FROM products WHERE id = ?");
        $product_price_query = mysqli_prepare($link, "SELECT price FROM products WHERE id = ?");

        if ($stmt_detail === false || $stmt_update_stock === false || $stmt_get_stock === false || $product_price_query === false) {
            throw new Exception("Error preparing detail/stock statements: " . mysqli_error($link));
        }

        $midtrans_item_details = [];
        // Di dalam loop foreach ($products_in_cart as $item)
        foreach ($products_in_cart as $item) {
            $productId = filter_var($item['id'] ?? '', FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? '', FILTER_VALIDATE_INT);
            $frontend_price = filter_var($item['price'] ?? '', FILTER_VALIDATE_FLOAT);

            if ($productId === false || $quantity === false || $quantity <= 0) {
                throw new Exception("Data produk tidak valid di keranjang.");
            }

            // Ambil nama produk dari database untuk konsistensi
            $stmt_get_product = mysqli_prepare($link, "SELECT name, price FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stmt_get_product, "i", $productId);
            mysqli_stmt_execute($stmt_get_product);
            mysqli_stmt_bind_result($stmt_get_product, $db_product_name, $db_price);
            mysqli_stmt_fetch($stmt_get_product);
            mysqli_stmt_reset($stmt_get_product);
            mysqli_stmt_close($stmt_get_product);

            if (empty($db_product_name)) {
                throw new Exception("Nama produk untuk ID " . $productId . " tidak ditemukan di database.");
            }

            // Validasi stok
            mysqli_stmt_bind_param($stmt_get_stock, "i", $productId);
            mysqli_stmt_execute($stmt_get_stock);
            mysqli_stmt_bind_result($stmt_get_stock, $current_stock);
            mysqli_stmt_fetch($stmt_get_stock);
            mysqli_stmt_reset($stmt_get_stock);

            if ($quantity > $current_stock) {
                throw new Exception("Stok untuk produk ID " . $productId . " tidak mencukupi. Hanya ada " . $current_stock . ".");
            }

            $item_subtotal = $quantity * $db_price;

            // Insert ke detail_transactions
            mysqli_stmt_bind_param($stmt_detail, "iiid", $local_transaction_db_id, $productId, $quantity, $item_subtotal);
            if (!mysqli_stmt_execute($stmt_detail)) {
                throw new Exception("Error executing transaction detail insert: " . mysqli_error($link));
            }

            // Update stok
            mysqli_stmt_bind_param($stmt_update_stock, "iii", $quantity, $productId, $quantity);
            if (!mysqli_stmt_execute($stmt_update_stock)) {
                throw new Exception("Error executing stock update: " . mysqli_error($link));
            }
            if (mysqli_stmt_affected_rows($stmt_update_stock) === 0) {
                throw new Exception("Gagal memperbarui stok untuk produk ID " . $productId . ".");
            }

            // Tambahkan ke midtrans_item_details dengan nama produk dari database
            $midtrans_item_details[] = [
                'id' => $productId,
                'price' => $db_price,
                'quantity' => $quantity,
                'name' => htmlspecialchars($db_product_name)
            ];
        }

        mysqli_stmt_close($stmt_detail);
        mysqli_stmt_close($stmt_update_stock);
        mysqli_stmt_close($stmt_get_stock);
        mysqli_stmt_close($product_price_query);

        // --- 3. Panggil API Midtrans untuk mendapatkan Snap Token ---
        $params = [
            'transaction_details' => [
                'order_id' => $transactionCode,
                'gross_amount' => $totalPrice,
            ],
            'customer_details' => [
                'first_name' => $customerName,
            ],
            'item_details' => $midtrans_item_details,
            // Hapus atau komentari bagian 'callbacks' ini karena tidak menggunakan webhook
            'callbacks' => [
                'finish' => 'http://pos-php.test:8080/pages/transaksi.php?status=payment_finished',
                'error' => 'http://pos-php.test:8080/pages/transaksi.php?status=payment_error',
                'pending' => 'http://pos-php.test:8080/pages/transaksi.php?status=payment_pending'
            ]
        ];

        // Dapatkan Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Jika berhasil mendapatkan Snap Token, commit transaksi lokal
        mysqli_commit($link);

        // Redirect dengan status sukses dan Snap Token di session
        redirect_with_status('transaction_success', 'Transaksi berhasil, silakan lanjutkan pembayaran.', $snapToken, $local_transaction_db_id);
        // di db_service/transaction/proses_transaksi.php
        header("Location: ../../pages/transaksi.php?status=transaction_success");
        exit();

    } catch (Exception $e) {
        // Rollback transaksi lokal jika ada kesalahan
        mysqli_rollback($link);
        $error_message = $e->getMessage();
        error_log("Midtrans/DB Transaction Error: " . $error_message);
        redirect_with_status('transaction_failed', $error_message);
    }

} else {
    redirect_with_status('invalid_data', 'Akses tidak valid. Permintaan harus berupa POST dari form.');
}

mysqli_close($link);
exit();
?>