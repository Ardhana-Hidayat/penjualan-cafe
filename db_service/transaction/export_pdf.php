<?php
// action/laporan/export_pdf.php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include '../../config/koneksi.php';

$selected_month = $_POST['bulan'] ?? date('n');
$selected_year = $_POST['tahun'] ?? date('Y');

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

// Fungsi untuk memformat angka (sama seperti sebelumnya)
function formatRupiahDisplay($angka)
{
    if ($angka === null || is_nan($angka)) {
        return "0,00";
    }
    $num = (float) $angka;
    $sign = '';
    if ($num < 0) {
        $sign = '-';
        $num = abs($num);
    }

    $parts = explode('.', (string) $num);
    $integerPart = number_format((float) $parts[0], 0, ',', '.');
    $decimalPart = isset($parts[1]) ? $parts[1] : '00';
    $decimalPart = str_pad($decimalPart, 2, '0', STR_PAD_RIGHT);

    return $sign . $integerPart . ',' . $decimalPart;
}

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
    error_log("DEBUG PDF: Error preparing main report query: " . mysqli_error($link));
    die("Gagal menyiapkan laporan PDF.");
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
            error_log("DEBUG PDF: Error preparing item query: " . mysqli_error($link));
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
            error_log("DEBUG PDF: Error fetching items (ID: $transaction_id): " . mysqli_error($link));
        }
        mysqli_stmt_close($stmt_items);

        $report_transactions[] = $transaction_row; // Gunakan variabel yang berbeda untuk menghindari konflik nama
    }
} else {
    error_log("DEBUG PDF: Main query failed: " . mysqli_errno($link) . " - " . mysqli_error($link));
}
mysqli_stmt_close($stmt_main_report_transactions);

mysqli_close($link);


// --- MULAI GENERATE HTML UNTUK PDF ---
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi ' . $month_name . ' ' . $selected_year . '</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif; /* Font Times New Roman */
            margin: 25px; /* Margin halaman */
            font-size: 10px; /* Ukuran font dasar */
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000; /* Garis bawah pada header */
            padding-bottom: 10px;
        }
        .header-section h1 {
            margin: 0;
            font-size: 20px; /* Ukuran nama toko */
            color: #333;
        }
        .header-section p {
            margin: 0;
            font-size: 11px; /* Ukuran detail alamat/telp */
            color: #555;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 5px 0; /* Margin setelah header */
        }
        .report-period {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000; /* Border tabel hitam */
            padding: 5px;
            text-align: left;
            vertical-align: top; /* Rata atas */
            font-size: 9px; /* Ukuran font isi tabel */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center; /* Header tabel rata tengah */
        }
        /* Penyesuaian alignment untuk kolom tertentu */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header-section">
        <h1>PENJUALAN CAFE</h1>
        <p>Jl. Contoh No. 123, Kota Anda</p>
        <p>Telp: (021) 12345678</p>
    </div>

    <div class="report-title">LAPORAN TRANSAKSI PENJUALAN</div>
    <div class="report-period">Periode: ' . $months_names[(int) $selected_month] . ' ' . $selected_year . '</div>

    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Kode Transaksi</th>
                <th class="text-center">Pelanggan</th>
                <th class="text-center">Produk</th>
                <th class="text-center">Jumlah</th>
                <th class="text-center">Harga Satuan</th>
                <th class="text-center">Subtotal Item</th>
                <th class="text-center">Total Transaksi</th>
                <th class="text-center">Bayar</th>
                <th class="text-center">Kembali</th>
                <th class="text-center">Tanggal</th>
            </tr>
        </thead>
        <tbody>';

if (!empty($report_transactions)) {
    $no = 1;
    foreach ($report_transactions as $transaction) {
        $rowspan_count = max(1, count($transaction['items']));
        $first_item_of_transaction = true;

        foreach ($transaction['items'] as $item) {
            $html .= '<tr>';
            if ($first_item_of_transaction) {
                $html .= '<td class="text-center" rowspan="' . $rowspan_count . '">' . $no++ . '</td>';
                $html .= '<td class="text-left" rowspan="' . $rowspan_count . '">' . htmlspecialchars($transaction['transactionCode']) . '</td>';
                $html .= '<td class="text-left" rowspan="' . $rowspan_count . '">' . htmlspecialchars($transaction['customerName']) . '</td>';
            }
            $html .= '<td class="text-left">' . htmlspecialchars($item['product_name']) . '</td>';
            $html .= '<td class="text-center">' . htmlspecialchars($item['quantity']) . '</td>';
            $html .= '<td class="text-right">Rp. ' . formatRupiahDisplay($item['product_price']) . '</td>';
            $html .= '<td class="text-right">Rp. ' . formatRupiahDisplay($item['item_subtotal']) . '</td>';

            if ($first_item_of_transaction) {
                $html .= '<td class="text-right" rowspan="' . $rowspan_count . '">Rp. ' . formatRupiahDisplay($transaction['totalPrice']) . '</td>';
                $html .= '<td class="text-right" rowspan="' . $rowspan_count . '">Rp. ' . formatRupiahDisplay($transaction['paidAmount']) . '</td>';
                $html .= '<td class="text-right" rowspan="' . $rowspan_count . '">Rp. ' . formatRupiahDisplay($transaction['changeAmount']) . '</td>';
                $html .= '<td class="text-center" rowspan="' . $rowspan_count . '">' . date('d-m-Y H:i', strtotime($transaction['createdAt'])) . '</td>';
            }
            $html .= '</tr>';
            $first_item_of_transaction = false;
        }
    }
} else {
    $html .= '
        <tr>
            <td colspan="11" class="text-center">Tidak ada data laporan untuk bulan dan tahun ini.</td>
        </tr>';
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// --- Konfigurasi Dompdf (sama seperti sebelumnya) ---
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false); // Biasanya diatur false untuk keamanan dan performa, kecuali Anda butuh load gambar dari URL eksternal

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape'); // Menggunakan orientasi landscape untuk kolom yang banyak

$dompdf->render();

$dompdf->stream("Laporan_Transaksi_{$month_name}_{$selected_year}.pdf", [
    "Attachment" => true // Mengatur agar file diunduh daripada ditampilkan di browser
]);

exit;