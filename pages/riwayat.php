<?php
// pages/riwayat-transaksi.php
session_start();

// Cek apakah user sudah login (sesuaikan dengan logika autentikasi Anda)
if (!isset($_SESSION['user_id'])) {
  header("Location: ../pages/auth/login.php"); // Redirect ke halaman login jika belum login
  exit();
}

include '../config/koneksi.php'; // Sesuaikan path jika lokasi koneksi.php berbeda

// --- Konfigurasi Paginasi ---
$limit = 10; // Jumlah transaksi unik per halaman
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
// Pastikan halaman tidak kurang dari 1
if ($page < 1) {
  $page = 1;
}

// --- Ambil Total Data Transaksi Unik (untuk paginasi) ---
// Kita hitung berdasarkan tabel 'transactions' saja agar tidak terpengaruh oleh jumlah item
$total_transactions_query = "SELECT COUNT(id) AS total FROM transactions";
$total_result = mysqli_query($link, $total_transactions_query);
$total_rows = 0;
if ($total_result) {
  $row = mysqli_fetch_assoc($total_result);
  $total_rows = $row['total'];
} else {
  error_log("Error mengambil total transaksi: " . mysqli_error($link));
}
$total_pages = ceil($total_rows / $limit);

// Pastikan halaman tidak melebihi total halaman yang tersedia
if ($page > $total_pages && $total_pages > 0) {
  $page = $total_pages;
} else if ($total_pages == 0) {
  $page = 1; // Jika tidak ada transaksi, tetap di halaman 1
}

$offset = ($page - 1) * $limit;

// --- Ambil Data Transaksi Utama untuk Halaman Saat Ini ---
$paginated_transactions = [];
$main_transactions_query = "SELECT 
                                t.id AS transaction_id,
                                t.transactionCode,
                                t.customerName,
                                t.totalPrice,
                                t.paidAmount,
                                t.changeAmount,
                                t.createdAt
                            FROM 
                                transactions AS t
                            ORDER BY 
                                t.createdAt DESC, t.id DESC
                            LIMIT ? OFFSET ?"; // Gunakan prepared statement untuk LIMIT/OFFSET
$stmt_main_transactions = mysqli_prepare($link, $main_transactions_query);
mysqli_stmt_bind_param($stmt_main_transactions, "ii", $limit, $offset);
mysqli_stmt_execute($stmt_main_transactions);
$result_main_transactions = mysqli_stmt_get_result($stmt_main_transactions);

if ($result_main_transactions) {
  while ($transaction_row = mysqli_fetch_assoc($result_main_transactions)) {
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
    mysqli_stmt_bind_param($stmt_items, "i", $transaction_id);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);

    if ($result_items) {
      while ($item_row = mysqli_fetch_assoc($result_items)) {
        $transaction_row['items'][] = $item_row;
      }
    } else {
      error_log("Error mengambil item transaksi (ID: $transaction_id): " . mysqli_error($link));
    }
    mysqli_stmt_close($stmt_items); // Tutup statement item

    $paginated_transactions[] = $transaction_row;
  }
} else {
  error_log("Query Error: " . mysqli_errno($link) . " - " . mysqli_error($link));
  $paginated_transactions = [];
}
mysqli_stmt_close($stmt_main_transactions); // Tutup statement transaksi utama

mysqli_close($link);

// Fungsi untuk memformat angka menjadi mata uang Rupiah (tanpa Rp. dan koma, hanya angka murni)
function formatRupiahDisplay($angka)
{
  if ($angka === null || is_nan($angka)) {
    return "0,00"; // Default dengan 2 desimal
  }
  $num = (float) $angka;
  $parts = explode('.', (string) abs($num));
  $integerPart = number_format((float) $parts[0], 0, ',', '.');
  $decimalPart = isset($parts[1]) ? $parts[1] : '00';
  $decimalPart = str_pad($decimalPart, 2, '0', STR_PAD_RIGHT); // Pastikan 2 desimal

  return ($num < 0 ? '-' : '') . $integerPart . ',' . $decimalPart;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penjualan Cafe | Riwayat Transaksi</title>
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
        <h3 class="text-xl">Halaman Riwayat Transaksi</h3>
        <div class="flex gap-2 p-2 rounded-md border border-[#1E1B57]">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M19.938 8H21C21.5304 8 22.0391 8.21071 22.4142 8.58579C22.7893 8.96086 23 9.46957 23 10V14C23 14.5304 22.7893 15.0391 22.4142 15.4142C22.0391 15.7893 21.5304 16 21 16H19.938C19.6944 17.9334 18.7535 19.7114 17.292 21.0002C15.8304 22.2891 13.9487 23.0002 12 23V21C13.5913 21 15.1174 20.3679 16.2426 19.2426C17.3679 18.1174 18 16.5913 18 15V9C18 7.4087 17.3679 5.88258 16.2426 4.75736C15.1174 3.63214 13.5913 3 12 3C10.4087 3 8.88258 3.63214 7.75736 4.75736C6.63214 5.88258 6 7.4087 6 9V16H3C2.46957 16 1.96086 15.7893 1.58579 15.4142C1.21071 15.0391 1 14.5304 1 14V10C1 9.46957 1.21071 8.96086 1.58579 8.58579C1.96086 8.21071 2.46957 8 3 8H4.062C4.30603 6.06689 5.24708 4.28927 6.70857 3.00068C8.17007 1.71208 10.0516 1.00108 12 1.00108C13.9484 1.00108 15.8299 1.71208 17.2914 3.00068C18.7529 4.28927 19.694 6.06689 19.938 8ZM3 10V14H4V10H3ZM20 10V14H21V10H20ZM7.76 15.785L8.82 14.089C9.77303 14.6861 10.8754 15.0019 12 15C13.1246 15.0019 14.227 14.6861 15.18 14.089L16.24 15.785C14.9693 16.5813 13.4996 17.0025 12 17C10.5004 17.0025 9.03067 16.5813 7.76 15.785Z"
              fill="#1E1B57" />
          </svg>
          <span>Administrator</span>
        </div>
      </div>

      <div class="px-4 py-6 flex items-start space-x-6">
        <div class="bg-white p-6 rounded-md shadow space-y-4 w-full overflow-x-auto">
          <h3 class="text-left">Data Riwayat Transaksi</h3>
          <hr />
          <table class="w-full text-sm border border-gray-400 border-collapse min-w-max">
            <thead class="bg-gray-100">
              <tr>
                <th class="font-medium border border-gray-400 px-4 py-3">No</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Kode Transaksi</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Pelanggan</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Produk</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Jumlah</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Subtotal Item</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Total Transaksi</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Bayar</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Kembali</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($paginated_transactions)): ?>
                <?php
                $no_start = ($page - 1) * $limit + 1; // Untuk nomor urut yang benar
                $current_no = $no_start;
                foreach ($paginated_transactions as $transaction):
                  $rowspan_count = max(1, count($transaction['items'])); // Minimal 1 row jika tidak ada item (shouldn't happen)
                  $first_item = true;
                  ?>
                  <?php foreach ($transaction['items'] as $item): ?>
                    <tr class="text-center">
                      <?php if ($first_item): ?>
                        <td class="border border-gray-400 px-4 py-3 text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          <?php echo $current_no++; ?>
                        </td>
                        <td class="border border-gray-400 px-4 py-3 text-left text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          <?php echo htmlspecialchars($transaction['transactionCode']); ?><br>

                        </td>
                        <td class="border border-gray-400 px-4 py-3 text-left text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          <?php echo htmlspecialchars($transaction['customerName']); ?><br>

                        </td>
                      <?php endif; ?>
                      <td class="border border-gray-400 px-4 py-3 text-left text-xs">
                        <?php echo htmlspecialchars($item['product_name']); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3 text-center text-xs">
                        <?php echo htmlspecialchars($item['quantity']); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3 text-left text-xs">Rp.
                        <?php echo formatRupiahDisplay($item['item_subtotal']); ?>
                      </td>
                      <?php if ($first_item): ?>
                        <td class="border border-gray-400 px-4 py-3 text-left text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          Rp. <?php echo formatRupiahDisplay($transaction['totalPrice']); ?>
                        </td>
                        <td class="border border-gray-400 px-4 py-3 text-left text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          Rp. <?php echo formatRupiahDisplay($transaction['paidAmount']); ?>
                        </td>
                        <td class="border border-gray-400 px-4 py-3 text-left text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          Rp. <?php echo formatRupiahDisplay($transaction['changeAmount']); ?>
                        </td>
                        <td class="border border-gray-400 px-4 py-3 text-xs" rowspan="<?php echo $rowspan_count; ?>">
                          <?php echo date('d-m-Y H:i', strtotime($transaction['createdAt'])); ?>
                        </td>
                      <?php endif; ?>
                    </tr>
                    <?php $first_item = false; ?>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              <?php else: ?>
                <tr class="text-center">
                  <td colspan="9" class="border border-gray-400 px-4 py-3 text-xs">Tidak ada riwayat transaksi.</td>
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
              entri
            </span>
            <div class="inline-flex space-x-2">
              <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                  Previous
                </a>
              <?php else: ?>
                <span class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                  Previous
                </span>
              <?php endif; ?>

              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>"
                  class="px-4 py-2 text-sm font-medium rounded-md
                                   <?php echo ($i == $page) ? 'bg-[#3B378B] text-white' : 'text-gray-700 bg-gray-200 hover:bg-gray-300'; ?>">
                  <?php echo $i; ?>
                </a>
              <?php endfor; ?>

              <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                  Next
                </a>
              <?php else: ?>
                <span class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                  Next
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>