<?php
// action/laporan/export_pdf.php
session_start();

// Perubahan: Hapus autoload Composer untuk Dompdf jika tidak ada library lain yang memerlukannya
// require_once __DIR__ . '/../../vendor/autoload.php';

// Sertakan file FPDF
require_once __DIR__ . '/../../vendor/fpdf/fpdf.php'; // Sesuaikan path jika Anda menaruh FPDF di tempat lain

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

// Fungsi untuk memformat angka (sama seperti sebelumnya, akan digunakan di FPDF)
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

        $report_transactions[] = $transaction_row;
    }
} else {
    error_log("DEBUG PDF: Main query failed: " . mysqli_errno($link) . " - " . mysqli_error($link));
}
mysqli_stmt_close($stmt_main_report_transactions);

mysqli_close($link);


// --- MULAI GENERATE PDF DENGAN FPDF ---

// Buat kelas PDF baru yang mewarisi FPDF
class PDF extends FPDF
{
    // Header halaman
    function Header()
    {
        global $month_name, $selected_year;

        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 7, 'PENJUALAN CAFE KELOMPOK 2', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Jl. Serayu', 0, 1, 'C');
        $this->Cell(0, 5, 'Telp: (123) 12345678', 0, 1, 'C');
        $this->Ln(5);
        
        $this->Line(10, $this->GetY(), $this->GetPageWidth() - 10, $this->GetY());
        $this->Ln(5);

        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'LAPORAN TRANSAKSI PENJUALAN', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 7, 'Periode: ' . $month_name . ' ' . $selected_year, 0, 1, 'C');
        $this->Ln(10);
    }

    // Footer halaman
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Fungsi untuk membuat header tabel
    function TableHeader()
    {
        // Posisi X untuk centering tabel
        // Lebar total kolom = 255mm
        // Lebar halaman A4 Landscape = 297mm
        // Sisa ruang = 297 - 255 = 42mm
        // Offset untuk centering = 42 / 2 = 21mm
        $this->SetX(21); // <<< Tambahkan ini untuk mengatur posisi X awal
        
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(10, 8, 'No', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Kode Transaksi', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Pelanggan', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Produk', 1, 0, 'C', true);
        $this->Cell(15, 8, 'Jumlah', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Harga Satuan', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Subtotal Item', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Total Transaksi', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Bayar', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Kembali', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Tanggal', 1, 1, 'C', true); // 1 di akhir untuk ganti baris
    }

    // Fungsi untuk membuat baris data tabel
    function TableRow($data)
    {
        // Posisi X untuk centering tabel, harus sama dengan TableHeader
        $this->SetX(21); // <<< Tambahkan ini untuk mengatur posisi X awal
        
        $this->SetFont('Arial', '', 7);
        $this->Cell(10, 6, $data['no'], 1, 0, 'C');
        $this->Cell(30, 6, $data['transactionCode'], 1, 0, 'L');
        $this->Cell(25, 6, $data['customerName'], 1, 0, 'L');
        $this->Cell(35, 6, $data['product_name'], 1, 0, 'L');
        $this->Cell(15, 6, $data['quantity'], 1, 0, 'C');
        $this->Cell(25, 6, 'Rp. ' . $data['product_price'], 1, 0, 'R');
        $this->Cell(25, 6, 'Rp. ' . $data['item_subtotal'], 1, 0, 'R');
        $this->Cell(25, 6, 'Rp. ' . $data['total_transaction_amount'], 1, 0, 'R');
        $this->Cell(20, 6, 'Rp. ' . $data['paidAmount'], 1, 0, 'R');
        $this->Cell(20, 6, 'Rp. ' . $data['changeAmount'], 1, 0, 'R');
        $this->Cell(25, 6, $data['createdAt'], 1, 1, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

// Cetak header tabel
$pdf->TableHeader();

if (!empty($report_transactions)) {
    $no = 1;
    foreach ($report_transactions as $transaction) {
        $first_item_of_transaction = true;

        foreach ($transaction['items'] as $item) {
            $row_data_fpdf = [
                'no' => $first_item_of_transaction ? $no++ : '',
                'transactionCode' => $first_item_of_transaction ? htmlspecialchars($transaction['transactionCode']) : '',
                'customerName' => $first_item_of_transaction ? htmlspecialchars($transaction['customerName']) : '',
                'product_name' => htmlspecialchars($item['product_name']),
                'quantity' => htmlspecialchars($item['quantity']),
                'product_price' => formatRupiahDisplay($item['product_price']),
                'item_subtotal' => formatRupiahDisplay($item['item_subtotal']),
                'total_transaction_amount' => $first_item_of_transaction ? formatRupiahDisplay($transaction['totalPrice']) : '',
                'paidAmount' => $first_item_of_transaction ? formatRupiahDisplay($transaction['paidAmount']) : '',
                'changeAmount' => $first_item_of_transaction ? formatRupiahDisplay($transaction['changeAmount']) : '',
                'createdAt' => $first_item_of_transaction ? date('d-m-Y H:i', strtotime($transaction['createdAt'])) : ''
            ];
            
            $pdf->TableRow($row_data_fpdf);

            $first_item_of_transaction = false;
        }
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    // Posisi X untuk pesan "Tidak ada data"
    $pdf->SetX(21); // <<< Tambahkan ini juga
    $pdf->Cell(255, 10, 'Tidak ada data laporan untuk bulan dan tahun ini.', 1, 1, 'C'); // Lebar cell harus sama dengan total lebar tabel
}

$pdf->Output("I", "Laporan_Transaksi_{$month_name}_{$selected_year}.pdf");

exit;