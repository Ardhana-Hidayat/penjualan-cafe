<?php
session_start();

include '../config/koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
  header("location: ../auth/login.php?status=not_logged_in");
  exit();
}

$loggedInUsername = $_SESSION['username'] ?? 'Pengguna';

// --- Data untuk Bar Chart (Jumlah Transaksi per Hari dalam Bulan Ini) ---
$sales_data_per_day = [];
$days_in_month = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')); // Jumlah hari dalam bulan ini
$current_month = date('Y-m');

// Inisialisasi array dengan 0 untuk setiap hari dalam bulan
for ($i = 1; $i <= $days_in_month; $i++) {
  $day_key = str_pad($i, 2, '0', STR_PAD_LEFT); // Format '01', '02', dst.
  $sales_data_per_day[$day_key] = 0;
}

// Query untuk mendapatkan JUMLAH TRANSAKSI per hari di bulan ini
$query_daily_sales = "SELECT 
                        DATE_FORMAT(createdAt, '%d') AS transaction_day, 
                        COUNT(id) AS daily_transaction_count -- Mengubah SUM(totalPrice) menjadi COUNT(id)
                      FROM 
                        transactions 
                      WHERE 
                        DATE_FORMAT(createdAt, '%Y-%m') = ? 
                      GROUP BY 
                        transaction_day 
                      ORDER BY 
                        transaction_day ASC";

$stmt_daily_sales = mysqli_prepare($link, $query_daily_sales);
if ($stmt_daily_sales) {
  mysqli_stmt_bind_param($stmt_daily_sales, "s", $current_month);
  mysqli_stmt_execute($stmt_daily_sales);
  $result_daily_sales = mysqli_stmt_get_result($stmt_daily_sales);
  while ($row = mysqli_fetch_assoc($result_daily_sales)) {
    // Mengambil daily_transaction_count
    $sales_data_per_day[$row['transaction_day']] = (int) $row['daily_transaction_count'];
  }
  mysqli_stmt_close($stmt_daily_sales);
} else {
  error_log("Error preparing daily sales query: " . mysqli_error($link));
}

$barChartLabels = array_keys($sales_data_per_day); // Hari-hari dalam bulan (01, 02, ...)
$barChartData = array_values($sales_data_per_day); // Jumlah transaksi untuk setiap hari

// --- Data untuk Pie Chart (Jumlah Produk per Kategori) ---
// ... (Bagian ini tidak ada perubahan) ...
$category_product_counts = [];
$pieChartLabels = [];
$pieChartData = [];

$query_category_counts = "SELECT 
                            c.name AS category_name, 
                            COUNT(p.id) AS product_count 
                          FROM 
                            category AS c 
                          LEFT JOIN 
                            products AS p ON c.id = p.idCategory 
                          GROUP BY 
                            c.name 
                          ORDER BY 
                            c.name ASC";

$result_category_counts = mysqli_query($link, $query_category_counts);
if ($result_category_counts) {
  while ($row = mysqli_fetch_assoc($result_category_counts)) {
    $pieChartLabels[] = htmlspecialchars($row['category_name']);
    $pieChartData[] = (int) $row['product_count'];
  }
} else {
  error_log("Error fetching category product counts: " . mysqli_error($link));
}

// --- Total Transaksi dan Total Produk (Untuk Cards) ---
// ... (Bagian ini tidak ada perubahan) ...
$total_transactions_count = 0;
$query_total_transactions = "SELECT COUNT(id) AS total FROM transactions";
$res_total_transactions = mysqli_query($link, $query_total_transactions);
if ($res_total_transactions) {
  $row = mysqli_fetch_assoc($res_total_transactions);
  $total_transactions_count = $row['total'];
}

$total_products_count = 0;
$query_total_products = "SELECT COUNT(id) AS total FROM products";
$res_total_products = mysqli_query($link, $query_total_products);
if ($res_total_products) {
  $row = mysqli_fetch_assoc($res_total_products);
  $total_products_count = $row['total'];
}

// Tutup koneksi database
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penjualan Cafe | Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans">
  <div class="flex min-h-screen">
    <?php include '../templates/sidebar.php'; ?>

    <div class="flex flex-col flex-1">
      <div class="bg-white p-6 flex justify-between items-center shadow">
        <h3 class="text-xl">Selamat Datang, <?php echo $loggedInUsername; ?></h3>
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

      <div class="p-6 space-y-6">
        <div class="grid grid-cols-4 gap-6">
          <div class="flex bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-yellow-400 w-20 flex justify-center items-center">
              <svg width="24" height="24" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg"
                class="text-white">
                <rect x="4.875" y="4.875" width="29.25" height="29.25" stroke="white" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round" />
                <path d="M16.575 25.8L22.075 30L30.875 19.5M8.875 9H30.875M8.875 17.4H17.675" stroke="white"
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="p-4 flex flex-col justify-center text-center">
              <div class="text-gray-600 text-sm">Total Transaksi</div>
              <div class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($total_transactions_count); ?>
              </div>
            </div>
          </div>

          <div class="flex bg-white rounded-xl shadow overflow-hidden">
            <div class="bg-green-400 w-20 flex justify-center items-center">
              <svg width="24" height="24" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg"
                class="text-white">
                <path
                  d="M35.75 4.875H3.25V14.625H4.875V32.5C4.875 33.362 5.21741 34.1886 5.8269 34.7981C6.4364 35.4076 7.26305 35.75 8.125 35.75H30.875C31.737 35.75 32.5636 35.4076 33.1731 34.7981C33.7826 34.1886 34.125 33.362 34.125 32.5V14.625H35.75V4.875ZM6.5 8.125H32.5V11.375H6.5V8.125ZM30.875 32.5H8.125V14.625H30.875V32.5ZM14.625 17.875H24.375C24.375 18.737 24.0326 19.5636 23.4231 20.1731C22.8136 20.7826 21.987 21.125 21.125 21.125H17.875C17.013 21.125 16.1864 20.7826 15.5769 20.1731C14.9674 19.5636 14.625 18.737 14.625 17.875Z"
                  fill="white" />
              </svg>
            </div>
            <div class="p-4 ml-2 flex flex-col justify-center text-center">
              <div class="text-gray-600 text-sm">Total Produk</div>
              <div class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($total_products_count); ?></div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div class="flex-1 space-y-6 bg-white p-4 rounded-xl shadow">
            <h4 class="text-md font-semibold">Bar Chart Penjualan (Bulan Ini)</h4>
            <canvas id="barChart" class="w-1/2"></canvas>
          </div>
          <div class="w-64 space-y-6 bg-white p-4 rounded-xl shadow">
            <h4 class="text-md font-semibold">Pie Chart Produk per Kategori</h4>
            <canvas id="pieChart" class="w-1/2"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Data dari PHP untuk Bar Chart
    const barChartLabels = <?php echo json_encode($barChartLabels); ?>;
    const barChartData = <?php echo json_encode($barChartData); ?>;

    const barCtx = document.getElementById("barChart").getContext("2d");
    new Chart(barCtx, {
      type: "bar",
      data: {
        labels: barChartLabels,
        datasets: [
          {
            label: "Jumlah Transaksi", // Label diubah
            data: barChartData,
            backgroundColor: "#60a5fa", // biru muda
            borderRadius: 8,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 5, // Sudah disetel 5 sebelumnya
            },
            // *** Tambahan: suggestedMax untuk memastikan skala selalu terlihat ***
            suggestedMax: 10, // Ini akan membuat skala Y mencapai minimal 10
            // Jika ada data lebih dari 10, skala akan menyesuaikan secara otomatis
          },
        },
      },
    });

    // Data dari PHP untuk Pie Chart (tidak ada perubahan)
    const pieChartLabels = <?php echo json_encode($pieChartLabels); ?>;
    const pieChartData = <?php echo json_encode($pieChartData); ?>;

    const pieCtx = document.getElementById("pieChart").getContext("2d");
    new Chart(pieCtx, {
      type: "pie",
      data: {
        labels: pieChartLabels,
        datasets: [
          {
            label: "Jumlah Produk",
            data: pieChartData,
            backgroundColor: ["#34d399", "#fbbf24", "#60a5fa", "#ef4444", "#8b5cf6", "#ec4899"],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    });

    // Logika SweetAlert (tidak ada perubahan)
    window.addEventListener('DOMContentLoaded', (event) => {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');
      const username = "<?php echo htmlspecialchars($loggedInUsername); ?>";

      if (status === 'login_success') {
        Swal.fire({
          title: 'Berhasil Login!',
          text: 'Anda berhasil login sebagai ' + username + '.',
          icon: 'success',
          confirmButtonText: 'OK',
          width: '350px'
        }).then(() => {
          const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
          window.history.replaceState({ path: newUrl }, '', newUrl);
        });
      }
    });
  </script>
</body>

</html>