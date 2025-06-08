<?php
// pages/print_receipt.php
// Pastikan semua error dilaporkan dan dicatat untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Ini hanya akan tampil di log jika tidak ada output HTML
ini_set('log_errors', 1);    // Pastikan error dicatat ke log
ini_set('error_log', __DIR__ . '/../../php_error_log.log'); // Tentukan lokasi log error PHP Anda

require '../vendor/autoload.php'; // Memuat autoload Composer
include '../config/koneksi.php'; // Memuat koneksi database

// Menggunakan namespace untuk kelas Printer dan konektor
use Mike42\Escpos\PrintConnectors\FilePrintConnector; // Untuk debugging cetak ke file
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector; // Ini yang akan kita gunakan

error_log("DEBUG: print_receipt.php started. EXECUTION CONTEXT: " . (php_sapi_name() === 'cli' ? 'CLI' : 'WEB'));

// --- Validasi dan Ambil transaction_id ---
$transaction_id = 0; // Default nilai
if (php_sapi_name() === 'cli') {
    global $argv;
    if (isset($argv[1])) {
        $transaction_id = intval($argv[1]);
        error_log("DEBUG: CLI detected. Got transaction_id from CLI arg: " . $transaction_id);
    } else {
        error_log("DEBUG: CLI detected, but no transaction_id provided as argument. Exiting.");
        exit("Usage: php " . basename(__FILE__) . " <transaction_id>\n"); // Menggunakan exit() alih-alih die()
    }
} else {
    if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {
        error_log("DEBUG: Web detected, but transaction_id not found or empty in GET request. Exiting.");
        exit("Transaction ID is required for printing the receipt."); // Menggunakan exit()
    }
    $transaction_id = intval($_GET['transaction_id']);
    error_log("DEBUG: Web detected. Received transaction_id from GET: " . $transaction_id);
}

if ($transaction_id <= 0) {
    error_log("DEBUG: Invalid transaction_id received: " . $transaction_id . ". Exiting.");
    exit("Invalid Transaction ID. Must be a positive integer."); // Menggunakan exit()
}

// --- Ambil Detail Transaksi Utama dari Database ---
$transaction_data = null;
$items_data = [];

$stmt_transaction = mysqli_prepare($link, "SELECT id, transactionCode, customerName, totalPrice, paidAmount, changeAmount, createdAt FROM transactions WHERE id = ?");
if ($stmt_transaction === false) {
    $error_msg = "Failed to prepare main transaction query: " . mysqli_error($link);
    error_log("DEBUG: " . $error_msg);
    exit("Error retrieving transaction data. (DB prepare error: " . $error_msg . ")"); // Menggunakan exit()
}
mysqli_stmt_bind_param($stmt_transaction, "i", $transaction_id);
error_log("DEBUG: Executing main transaction query for ID: " . $transaction_id);
mysqli_stmt_execute($stmt_transaction);
$result_transaction = mysqli_stmt_get_result($stmt_transaction);

if ($result_transaction && mysqli_num_rows($result_transaction) > 0) {
    $transaction_data = mysqli_fetch_assoc($result_transaction);
    $local_transaction_id = $transaction_data['id'];
    error_log("DEBUG: Transaction found. Local DB ID: " . $local_transaction_id . ", Code: " . $transaction_data['transactionCode']);

    // --- Ambil Detail Item-item Transaksi ---
    $stmt_items = mysqli_prepare($link, "SELECT
                                            p.name AS product_name,
                                            ti.quantity,
                                            ti.totalPrice AS item_subtotal,
                                            p.price AS product_price
                                         FROM
                                            detail_transactions ti
                                         JOIN
                                            products p ON ti.productId = p.id
                                         WHERE
                                            ti.transactionId = ?");
    if ($stmt_items === false) {
        $error_msg = "Failed to prepare transaction items query: " . mysqli_error($link);
        error_log("DEBUG: " . $error_msg);
        exit("Error retrieving transaction items. (Query preparation failed: " . $error_msg . ")"); // Menggunakan exit()
    }
    mysqli_stmt_bind_param($stmt_items, "i", $local_transaction_id);
    error_log("DEBUG: Executing items query for transaction ID: " . $local_transaction_id);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);

    if ($result_items) {
        if (mysqli_num_rows($result_items) > 0) {
            while ($row = mysqli_fetch_assoc($result_items)) {
                $items_data[] = $row;
            }
            error_log("DEBUG: Successfully retrieved " . count($items_data) . " transaction items.");
        } else {
            error_log("DEBUG: No items found for transaction ID: " . $local_transaction_id . ". Receipt will indicate no items.");
        }
    } else {
        $error_msg = "Failed to get result for transaction items query: " . mysqli_error($link);
        error_log("DEBUG: " . $error_msg);
        exit("Error retrieving transaction items. (Query execution failed: " . $error_msg . ")"); // Menggunakan exit()
    }
    mysqli_stmt_close($stmt_items);
} else {
    error_log("DEBUG: Transaction with ID " . $transaction_id . " not found in DB. Exiting.");
    exit("Transaction with ID " . htmlspecialchars($transaction_id) . " not found."); // Menggunakan exit()
}

mysqli_close($link);
error_log("DEBUG: Database connection closed.");

function formatRupiahUntukStruk($angka)
{
    return number_format($angka, 0, '', '.');
}

// --- MULAI CETAK STRUK ---
error_log("DEBUG: Starting printer connection attempt.");
try {
    $printer_name = "POS-58"; // GANTI DENGAN NAMA PRINTER ANDA YANG SESUAI DI "DEVICES AND PRINTERS"
    error_log("DEBUG: Attempting to connect to printer: '" . $printer_name . "' using WindowsPrintConnector.");

    $connector = new Mike42\Escpos\PrintConnectors\WindowsPrintConnector($printer_name);

    // --- DEBUGGING KE FILE (UNCOMMENT UNTUK MENGAKTIFKAN INI SAJA) ---
    // $rootDir = realpath(__DIR__ . '/../..') . DIRECTORY_SEPARATOR;
    // $file_path = $rootDir . 'receipt_debug.txt';
    // error_log("DEBUG: Attempting to connect to file: '" . $file_path . "' using FilePrintConnector.");
    // $connector = new Mike42\Escpos\PrintConnectors\FilePrintConnector($file_path);


    $printer = new Printer($connector);
    error_log("DEBUG: Printer object successfully instantiated.");

    /* Header Struk */
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("===============================\n");
    $printer->text("NAMA CAFE ANDA\n");
    $printer->text("Alamat Cafe Anda\n");
    $printer->text("Telp: 0812-XXXX-XXXX\n");
    $printer->text("===============================\n");
    $printer->text("\n");
    error_log("DEBUG: Header text added.");

    /* Detail Transaksi */
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("No. Transaksi: " . htmlspecialchars($transaction_data['transactionCode']) . "\n");
    $printer->text("Pelanggan    : " . htmlspecialchars($transaction_data['customerName']) . "\n");
    $printer->text("Tanggal      : " . date('d/m/Y H:i:s', strtotime($transaction_data['createdAt'])) . "\n");
    $printer->text("--------------------------------\n");
    error_log("DEBUG: Transaction details added.");

    /* Daftar Item */
    if (empty($items_data)) {
        $printer->text("--- Tidak ada item pada transaksi ini ---\n");
        error_log("DEBUG: No items to print for this transaction.");
    } else {
        foreach ($items_data as $item) {
            $product_name = substr($item['product_name'], 0, 20);
            $qty = $item['quantity'];
            $product_price = formatRupiahUntukStruk($item['product_price']);
            $item_subtotal = formatRupiahUntukStruk($item['item_subtotal']);

            $printer->text(sprintf(
                "%-20s %3sx %-8s %10s\n",
                $product_name,
                $qty,
                $product_price,
                $item_subtotal
            ));
        }
        error_log("DEBUG: Transaction items added.");
    }
    $printer->text("--------------------------------\n");

    /* Total Pembayaran */
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("TOTAL              : " . formatRupiahUntukStruk($transaction_data['totalPrice']) . "\n");
    $printer->text("BAYAR              : " . formatRupiahUntukStruk($transaction_data['paidAmount']) . "\n");
    $printer->text("KEMBALIAN          : " . formatRupiahUntukStruk($transaction_data['changeAmount']) . "\n");
    $printer->text("\n");
    error_log("DEBUG: Total payment details added.");

    /* Footer Struk */
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("Terima Kasih Atas Kunjungan Anda\n");
    $printer->text("===============================\n");
    error_log("DEBUG: Footer text added.");

    $printer->cut();
    error_log("DEBUG: Printer cut command sent.");
    $printer->close();
    error_log("DEBUG: Printer connection closed.");

    // Hapus semua output HTML/JavaScript dari sini
    // echo "<script>alert('Struk berhasil dicetak!'); window.close();</script>"; // DIHAPUS
    error_log("DEBUG: Receipt printing completed successfully. No browser output.");

} catch (Exception $e) {
    $error_message = $e->getMessage();
    error_log("FATAL ERROR: Could not print to this printer: " . $error_message);
    // Hapus semua output HTML/JavaScript dari sini
    // echo "<script>alert('Gagal mencetak struk: " . addslashes($error_message) . "\\n\\nPeriksa file php_error_log.log untuk detail lebih lanjut.'); window.close();</script>"; // DIHAPUS
    error_log("FATAL ERROR: Receipt printing failed with exception. No browser output.");
}
error_log("DEBUG: print_receipt.php finished execution.");
exit(); // Pastikan tidak ada output lain setelah ini
?>