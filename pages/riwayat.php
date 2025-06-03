<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penjualan Cafe | Riwayat Transaksi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 font-sans">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include '../templates/sidebar.php'; ?>

    <div class="flex flex-col flex-1 overflow-auto h-screen">
      <!-- Topbar -->
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

      <div class="p-6 flex items-start space-x-6">
        <!-- Cards and Charts -->
        <div class="bg-white p-6 rounded-md shadow space-y-4 w-full">
          <h3 class="text-left">Data Riwayat Transaksi</h3>
          <hr />
          <table class="w-full text-sm border border-gray-400 border-collapse">
            <thead class="bg-gray-100">
              <tr>
                <th class="font-medium border border-gray-400 px-4 py-3">No</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Kode Transaksi</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Produk</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Jumlah</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Total Transaksi</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Bayar</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Kembali</th>
                <th class="font-medium border border-gray-400 px-4 py-3">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <tr class="text-center">
                <td class="border border-gray-400 px-4 py-3">1</td>
                <td class="border border-gray-400 px-4 py-3 text-left">KAND8918012</td>
                <td class="border border-gray-400 px-4 py-3 text-left">Kopi</td>
                <td class="border border-gray-400 px-4 py-3 text-left">1</td>
                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 20.000</td>
                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 20.000</td>
                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 0</td>
                <td class="border border-gray-400 px-4 py-3">2025-3-18, 08:45 PM</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  </div>
</body>

</html>