<?php
session_start();

include '../config/koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("location: ../auth/login.php?status=not_logged_in");
    exit();
}

// Ambil bulan dan tahun dari GET request, default ke bulan/tahun saat ini
$selected_month = $_GET['bulan'] ?? date('n'); // n untuk bulan tanpa leading zero
$selected_year = $_GET['tahun'] ?? date('Y');

// Array nama bulan untuk dropdown
$months = [
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

// Generate tahun dari tahun_awal hingga tahun sekarang + 1
$years = [];
$start_year = 2020; // Tahun awal untuk dropdown
$current_year = date('Y');
for ($y = $start_year; $y <= $current_year + 1; $y++) {
    $years[] = $y;
}

// --- Konfigurasi Paginasi ---
$limit = 10; // Jumlah transaksi unik per halaman
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// --- Ambil Total Data Transaksi Unik (untuk paginasi) dengan Filter Bulan & Tahun ---
$total_transactions_query = "SELECT COUNT(id) AS total FROM transactions WHERE MONTH(createdAt) = ? AND YEAR(createdAt) = ?";
$stmt_total = mysqli_prepare($link, $total_transactions_query);
mysqli_stmt_bind_param($stmt_total, "ii", $selected_month, $selected_year);
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$total_rows = 0;
if ($result_total) {
    $row = mysqli_fetch_assoc($result_total);
    $total_rows = $row['total'];
} else {
    error_log("Error mengambil total transaksi laporan: " . mysqli_error($link));
}
mysqli_stmt_close($stmt_total);

$total_pages = ceil($total_rows / $limit);

// Pastikan halaman tidak melebihi total halaman yang tersedia
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
} else if ($total_pages == 0) {
    $page = 1; // Jika tidak ada transaksi, tetap di halaman 1
}

$offset = ($page - 1) * $limit;

// --- Ambil Data Transaksi Utama untuk Halaman Saat Ini dengan Filter ---
$paginated_report_transactions = [];
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
                                t.createdAt DESC, t.id DESC
                            LIMIT ? OFFSET ?";

$stmt_main_report_transactions = mysqli_prepare($link, $main_report_transactions_query);
if ($stmt_main_report_transactions === false) {
    die("Error menyiapkan query transaksi laporan utama: " . mysqli_error($link));
}
mysqli_stmt_bind_param($stmt_main_report_transactions, "iiii", $selected_month, $selected_year, $limit, $offset);
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
            error_log("Error menyiapkan query item transaksi laporan: " . mysqli_error($link));
            continue; // Lanjutkan ke transaksi berikutnya jika ada error pada statement item
        }
        mysqli_stmt_bind_param($stmt_items, "i", $transaction_id);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);

        if ($result_items) {
            while ($item_row = mysqli_fetch_assoc($result_items)) {
                $transaction_row['items'][] = $item_row;
            }
        } else {
            error_log("Error mengambil item transaksi laporan (ID: $transaction_id): " . mysqli_error($link));
        }
        mysqli_stmt_close($stmt_items);

        $paginated_report_transactions[] = $transaction_row;
    }
} else {
    error_log("Query Error Laporan: " . mysqli_errno($link) . " - " . mysqli_error($link));
    $paginated_report_transactions = [];
}
mysqli_stmt_close($stmt_main_report_transactions);

mysqli_close($link);

// Fungsi untuk memformat angka sesuai standar (tanpa 'Rp.', dengan titik ribuan, koma desimal, 2 desimal)
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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Penjualan Cafe | Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <?php include '../templates/sidebar.php'; ?>

        <div class="flex flex-col flex-1 overflow-auto h-screen">
            <div class="bg-white p-6 flex justify-between items-center shadow">
                <h3 class="text-xl">Halaman Laporan</h3>
                <div class="flex gap-2 p-2 rounded-md border border-[#1E1B57]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M19.938 8H21C21.5304 8 22.0391 8.21071 22.4142 8.58579C22.7893 8.96086 23 9.46957 23 10V14C23 14.5304 22.7893 15.0391 22.4142 15.4142C22.0391 15.7893 21.5304 16 21 16H19.938C19.6944 17.9334 18.7535 19.7114 17.292 21.0002C15.8304 22.2891 13.9487 23.0002 12 23V21C13.5913 21 15.1174 20.3679 16.2426 19.2426C17.3679 18.1174 18 16.5913 18 15V9C18 7.4087 17.3679 5.88258 16.2426 4.75736C15.1174 3.63214 13.5913 3 12 3C10.4087 3 8.88258 3.63214 7.75736 4.75736C6.63214 5.88258 6 7.4087 6 9V16H3C2.46957 16 1.96086 15.7893 1.58579 15.4142C1.21071 15.0391 1 14.5304 1 14V10C1 9.46957 1.21071 8.96086 1.58579 8.58579C1.96086 8.21071 2.46957 8 3 8H4.062C4.30603 6.06689 5.24708 4.28927 6.70857 3.00068C8.17007 1.71208 10.0516 1.00108 12 1.00108C13.9484 1.00108 15.8299 1.71208 17.2914 3.00068C18.7529 4.28927 19.694 6.06689 19.938 8ZM3 10V14H4V10H3ZM20 10V14H21V10H20ZM7.76 15.785L8.82 14.089C9.77303 14.6861 10.8754 15.0019 12 15C13.1246 15.0019 14.227 14.6861 15.18 14.089L16.24 15.785C14.9693 16.5813 13.4996 17.0025 12 17C10.5004 17.0025 9.03067 16.5813 7.76 15.785Z"
                            fill="#1E1B57" />
                    </svg>
                    <a href="/pages/profil.php" class="cursor-pointer">
                        <span>Profil</span>
                    </a>
                </div>
            </div>

            <div class="p-6 flex items-start space-x-6">
                <div class="bg-white p-6 rounded-md shadow space-y-4 w-full overflow-x-auto">
                    <div class="flex justify-between">
                        <div class="flex space-x-4 items-center">
                            <h3 class="text-left">Data Laporan</h3>
                            <form action="" method="GET" id="filter-form" class="flex space-x-4 items-center">
                                <div class="flex flex-col">
                                    <select name="bulan" id="bulan" required
                                        class="p-2 w-20 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B] text-sm">
                                        <option value="">Bulan</option>
                                        <?php foreach ($months as $num => $name): ?>
                                            <option value="<?php echo $num; ?>" <?php echo ($selected_month == $num) ? 'selected' : ''; ?>>
                                                <?php echo $name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="flex flex-col">
                                    <select name="tahun" id="tahun" required
                                        class="p-2 w-20 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B] text-sm">
                                        <option value="">Tahun</option>
                                        <?php foreach ($years as $year): ?>
                                            <option value="<?php echo $year; ?>" <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                                                <?php echo $year; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                            </form>
                            <button class="p-2 bg-green-500 text-white rounded-md hover:bg-green-600"
                                onclick="exportReport('excel')">
                                <span>Excel</span>
                            </button>
                            <button class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600"
                                onclick="exportReport('pdf')">
                                <span>PDF</span>
                            </button>
                        </div>

                    </div>

                    <hr />
                    <table class="w-full text-sm border border-gray-400 border-collapse min-w-max">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="font-medium border border-gray-400 px-4 py-3">No</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Kode Transaksi</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Pelanggan</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Produk</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Jumlah</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Harga Satuan</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Subtotal Item</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Total Transaksi</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Bayar</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Kembali</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paginated_report_transactions)): ?>
                                <?php
                                $no_start = ($page - 1) * $limit + 1; // Untuk nomor urut yang benar
                                $current_no = $no_start;
                                foreach ($paginated_report_transactions as $transaction):
                                    $rowspan_count = max(1, count($transaction['items'])); // Minimal 1 row jika tidak ada item
                                    $first_item = true;
                                    ?>
                                    <?php foreach ($transaction['items'] as $item): ?>
                                        <tr class="text-center">
                                            <?php if ($first_item): ?>
                                                <td class="border border-gray-400 px-4 py-3 text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    <?php echo $current_no++; ?>
                                                </td>
                                                <td class="border border-gray-400 px-4 py-3 text-left text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    <?php echo htmlspecialchars($transaction['transactionCode']); ?>
                                                </td>
                                                <td class="border border-gray-400 px-4 py-3 text-left text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    <?php echo htmlspecialchars($transaction['customerName']); ?>
                                                </td>
                                            <?php endif; ?>
                                            <td class="border border-gray-400 px-4 py-3 text-left text-xs">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </td>
                                            <td class="border border-gray-400 px-4 py-3 text-center text-xs">
                                                <?php echo htmlspecialchars($item['quantity']); ?>
                                            </td>
                                            <td class="border border-gray-400 px-4 py-3 text-left text-xs">Rp.
                                                <?php echo formatRupiahDisplay($item['product_price']); ?>
                                            </td>
                                            <td class="border border-gray-400 px-4 py-3 text-left text-xs">Rp.
                                                <?php echo formatRupiahDisplay($item['item_subtotal']); ?>
                                            </td>
                                            <?php if ($first_item): ?>
                                                <td class="border border-gray-400 px-4 py-3 text-left text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    Rp. <?php echo formatRupiahDisplay($transaction['totalPrice']); ?>
                                                </td>
                                                <td class="border border-gray-400 px-4 py-3 text-left text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    Rp. <?php echo formatRupiahDisplay($transaction['paidAmount']); ?>
                                                </td>
                                                <td class="border border-gray-400 px-4 py-3 text-left text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    Rp. <?php echo formatRupiahDisplay($transaction['changeAmount']); ?>
                                                </td>
                                                <td class="border border-gray-400 px-4 py-3 text-xs"
                                                    rowspan="<?php echo $rowspan_count; ?>">
                                                    <?php echo date('d-m-Y H:i', strtotime($transaction['createdAt'])); ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php $first_item = false; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="text-center">
                                    <td colspan="11" class="border border-gray-400 px-4 py-3 text-xs">Tidak ada data laporan
                                        untuk bulan dan tahun ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="flex justify-between items-center bg-white p-4 rounded-md shadow mt-4">
                        <span class="text-sm text-gray-700">Menampilkan
                            <?php
                            $start_entry = min($offset + 1, $total_rows);
                            $end_entry = min($offset + $limit, $total_rows);
                            if ($total_rows == 0) { // Handle case with no transactions
                                $start_entry = 0;
                                $end_entry = 0;
                            }
                            echo $start_entry;
                            ?>
                            sampai
                            <?php echo $end_entry; ?>
                            dari
                            <?php echo $total_rows; ?>
                            data
                        </span>
                        <div class="inline-flex space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?bulan=<?php echo $selected_month; ?>&tahun=<?php echo $selected_year; ?>&page=<?php echo $page - 1; ?>"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                    Previous
                                </a>
                            <?php else: ?>
                                <span
                                    class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                                    Previous
                                </span>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?bulan=<?php echo $selected_month; ?>&tahun=<?php echo $selected_year; ?>&page=<?php echo $i; ?>"
                                    class="px-4 py-2 text-sm font-medium rounded-md
                                   <?php echo ($i == $page) ? 'bg-[#3B378B] text-white' : 'text-gray-700 bg-gray-200 hover:bg-gray-300'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?bulan=<?php echo $selected_month; ?>&tahun=<?php echo $selected_year; ?>&page=<?php echo $page + 1; ?>"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                    Next
                                </a>
                            <?php else: ?>
                                <span
                                    class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                                    Next
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Fungsi untuk ekspor laporan (generik untuk Excel/PDF)
        function exportReport(format) { // Mengubah nama fungsi dari exportTable menjadi exportReport
            const bulan = document.getElementById('bulan').value; // Ambil nilai dari select HTML
            const tahun = document.getElementById('tahun').value; // Ambil nilai dari select HTML

            // Validasi sederhana: Pastikan bulan dan tahun dipilih
            if (!bulan || !tahun) {
                Swal.fire('Peringatan!', 'Silakan pilih bulan dan tahun untuk laporan.', 'warning');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST'; // Gunakan POST, lebih aman untuk mengirim data filter
            form.target = '_blank'; // Buka di tab baru untuk mencegah reload halaman laporan

            // Sesuaikan action URL ke script backend yang benar
            // Jika pages/laporan.php, maka ../db_service/transaction/export_excel.php
            if (format === 'excel') {
                form.action = '../db_service/transaction/export_excel.php';
            } else if (format === 'pdf') {
                form.action = '../db_service/transaction/export_pdf.php'; // Anda perlu membuat file ini nanti
            } else {
                Swal.fire('Error!', 'Format export tidak dikenal.', 'error');
                return;
            }

            // Tambahkan input hidden untuk bulan
            const inputBulan = document.createElement('input');
            inputBulan.type = 'hidden';
            inputBulan.name = 'bulan';
            inputBulan.value = bulan;
            form.appendChild(inputBulan);

            // Tambahkan input hidden untuk tahun
            const inputTahun = document.createElement('input');
            inputTahun.type = 'hidden';
            inputTahun.name = 'tahun';
            inputTahun.value = tahun;
            form.appendChild(inputTahun);

            // Debugging: Log data yang akan dikirim
            console.log(`Exporting ${format} report for ${bulan}/${tahun}`);

            document.body.appendChild(form); // Tambahkan form ke body
            form.submit(); // Submit form
            document.body.removeChild(form); // Hapus form setelah submit
        }

        // Event listener untuk memastikan form filter disubmit saat bulan/tahun berubah
        document.getElementById('bulan').addEventListener('change', function () {
            document.getElementById('filter-form').submit();
        });
        document.getElementById('tahun').addEventListener('change', function () {
            document.getElementById('filter-form').submit();
        });
    </script>
</body>

</html>