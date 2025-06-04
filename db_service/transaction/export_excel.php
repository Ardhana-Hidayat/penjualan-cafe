<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use Shuchkin\SimpleXLSXGen;

include '../../config/koneksi.php';

$selected_month = $_POST['bulan'] ?? date('n');
$selected_year = $_POST['tahun'] ?? date('Y');

// Array nama bulan untuk format nama file
$months_names = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];
$month_name = $months_names[(int) $selected_month];

// --- AMBIL DATA TRANSAKSI UTAMA DAN ITEMNYA (STRUKTUR DATA SEPERTI DI HALAMAN LAPORAN/RIWAYAT) ---
$report_transactions = [];
$main_report_transactions_query = "SELECT
                                t.id AS transaction_id,
                                t.transactionCode,
                                t.customerName,
                                t.totalPrice,
                                t.paidAmount,
                                t.changeAmount,
                                t.createdAt
                            FROM
                                transactions AS t
                            WHERE
                                MONTH(t.createdAt) = ? AND YEAR(t.createdAt) = ?
                            ORDER BY
                                t.createdAt DESC, t.id DESC";

$stmt_main_report_transactions = mysqli_prepare($link, $main_report_transactions_query);
if ($stmt_main_report_transactions === false) {
    error_log("DEBUG EXCEL: Error preparing main report query: " . mysqli_error($link));
    die("Gagal menyiapkan laporan Excel.");
}
mysqli_stmt_bind_param($stmt_main_report_transactions, "ii", $selected_month, $selected_year);
mysqli_stmt_execute($stmt_main_report_transactions);
$result_main_report_transactions = mysqli_stmt_get_result($stmt_main_report_transactions);

if ($result_main_report_transactions) {
    while ($transaction_row = mysqli_fetch_assoc($result_main_report_transactions)) {
        $transaction_id = $transaction_row['transaction_id'];
        $transaction_row['items'] = []; // Tambahkan array untuk menampung item

        // --- Ambil Detail Item untuk Setiap Transaksi yang Dipilih ---
        $items_query = "SELECT
                            dt.quantity,
                            dt.totalPrice AS item_subtotal,
                            p.name AS product_name,
                            p.price AS product_price
                        FROM
                            detail_transactions AS dt
                        INNER JOIN
                            products AS p ON dt.productId = p.id
                        WHERE
                            dt.transactionId = ?";
        $stmt_items = mysqli_prepare($link, $items_query);
        if ($stmt_items === false) {
            error_log("DEBUG EXCEL: Error preparing item query: " . mysqli_error($link));
            continue;
        }
        mysqli_stmt_bind_param($stmt_items, "i", $transaction_id);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);

        if ($result_items) {
            while ($item_row = mysqli_fetch_assoc($result_items)) {
                $transaction_row['items'][] = $item_row;
            }
        } else {
            error_log("DEBUG EXCEL: Error fetching items (ID: $transaction_id): " . mysqli_error($link));
        }
        mysqli_stmt_close($stmt_items);

        $report_transactions[] = $transaction_row;
    }
} else {
    error_log("DEBUG EXCEL: Main query failed: " . mysqli_errno($link) . " - " . mysqli_error($link));
}
mysqli_stmt_close($stmt_main_report_transactions);

mysqli_close($link);


// Fungsi untuk memformat angka Rupiah tanpa simbol Rp. atau koma desimal, hanya angka
function formatRupiahUntukExcel($angka)
{
    if ($angka === null || is_nan($angka)) {
        return 0; // Mengembalikan angka 0 untuk Excel
    }
    return (float) $angka; // Pastikan ini adalah float atau int
}

// --- PERSIAPAN DATA UNTUK EXCEL ---
$excel_data = [];

// Header Excel
$excel_data[] = [
    'No',
    'Kode Transaksi',
    'Pelanggan',
    'Produk',
    'Jumlah',
    'Harga Satuan',
    'Subtotal Item',
    'Total Transaksi',
    'Bayar',
    'Kembalian',
    'Tanggal'
];

$no = 1;
if (!empty($report_transactions)) {
    foreach ($report_transactions as $transaction) {
        $first_item_of_transaction = true;

        foreach ($transaction['items'] as $item) {
            $row_data = [];

            if ($first_item_of_transaction) {
                // Kolom yang hanya muncul sekali per transaksi
                $row_data[] = $no++;
                $row_data[] = htmlspecialchars($transaction['transactionCode']);
                $row_data[] = htmlspecialchars($transaction['customerName']);
            } else {
                // Kosongkan sel untuk baris item berikutnya dari transaksi yang sama
                $row_data[] = ''; // No
                $row_data[] = ''; // Kode Transaksi
                $row_data[] = ''; // Pelanggan
            }

            // Kolom yang muncul untuk setiap item
            $row_data[] = htmlspecialchars($item['product_name']);
            $row_data[] = (int) $item['quantity'];
            $row_data[] = formatRupiahUntukExcel($item['product_price']);
            $row_data[] = formatRupiahUntukExcel($item['item_subtotal']);

            if ($first_item_of_transaction) {
                // Kolom total transaksi, dibayar, kembali, tanggal hanya muncul sekali per transaksi
                $row_data[] = formatRupiahUntukExcel($transaction['totalPrice']);
                $row_data[] = formatRupiahUntukExcel($transaction['paidAmount']);
                $row_data[] = formatRupiahUntukExcel($transaction['changeAmount']);
                $row_data[] = date('d-m-Y H:i:s', strtotime($transaction['createdAt']));
            } else {
                // Kosongkan sel untuk baris item berikutnya dari transaksi yang sama
                $row_data[] = ''; // Total Transaksi
                $row_data[] = ''; // Dibayar
                $row_data[] = ''; // Kembalian
                $row_data[] = ''; // Tanggal
            }
            $excel_data[] = $row_data;
            $first_item_of_transaction = false;
        }
    }
} else {
    // Jika tidak ada data
    $excel_data[] = ['Tidak ada data laporan untuk bulan dan tahun ini.', '', '', '', '', '', '', '', '', '', ''];
}


// Log jumlah baris data yang akan diekspor
error_log("DEBUG EXCEL: Jumlah baris data yang akan diekspor (termasuk header): " . count($excel_data));
error_log("DEBUG EXCEL: Data yang akan diekspor (potongan): " . var_export(array_slice($excel_data, 0, 5), true)); // Ambil 5 baris pertama saja

// Tentukan nama file Excel
$nama_file = "Laporan_Transaksi_{$month_name}_{$selected_year}.xlsx";

SimpleXLSXGen::fromArray($excel_data)->downloadAs($nama_file);
exit;