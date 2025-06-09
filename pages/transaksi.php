<?php
session_start();

include '../config/koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("location: ../auth/login.php?status=not_logged_in");
    exit();
}

$products = [];
$product_query = "SELECT p.id, p.name AS product_name, p.price, p.stock, c.name AS category_name
                  FROM products AS p
                  INNER JOIN category AS c ON p.idCategory = c.id
                  ORDER BY p.name ASC";
$product_result = mysqli_query($link, $product_query);
if ($product_result) {
  while ($row = mysqli_fetch_assoc($product_result)) {
    $products[] = $row;
  }
} else {
  error_log("Error mengambil produk: " . mysqli_error($link));
  $products = [];
}

// --- Konfigurasi Paginasi untuk Transaksi ---
$limit = 5; // Jumlah transaksi per halaman
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
// Pastikan halaman tidak kurang dari 1
if ($page < 1) {
  $page = 1;
}
$offset = ($page - 1) * $limit;

// --- Ambil Total Data Transaksi (untuk paginasi) ---
$total_transactions_query = "SELECT COUNT(*) AS total FROM transactions";
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
  $offset = ($page - 1) * $limit; // Hitung ulang offset
} else if ($total_pages == 0) {
  $page = 1; // Jika tidak ada transaksi, tetap di halaman 1
  $offset = 0;
}


// --- Ambil Data Transaksi Sebelumnya untuk Tampilan dengan Paginasi ---
$transactions = [];
$transaction_query = "SELECT id, transactionCode, customerName, totalPrice AS total_amount_transaksi
                      FROM transactions ORDER BY createdAt DESC
                      LIMIT $limit OFFSET $offset"; // Tambahkan LIMIT dan OFFSET
$transaction_result = mysqli_query($link, $transaction_query);

if ($transaction_result) {
  while ($row = mysqli_fetch_assoc($transaction_result)) {
    $transactions[] = [
      'id' => $row['id'],
      'transaction_code' => $row['transactionCode'],
      'customer_name' => $row['customerName'],
      'total_amount' => $row['total_amount_transaksi']
    ];
  }
} else {
  error_log("Error mengambil transaksi: " . mysqli_error($link));
  $transactions = [];
}

mysqli_close($link);

// Ambil snap_token dan midtrans_order_id dari session (jika ada)
$midtransSnapToken = $_SESSION['midtrans_snap_token'] ?? null;
$midtransOrderId = $_SESSION['midtrans_order_id'] ?? null;

// Hapus dari session setelah diambil agar tidak muncul lagi saat refresh
unset($_SESSION['midtrans_snap_token']);
unset($_SESSION['midtrans_order_id']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penjualan Cafe | Transaksi</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="SB-Mid-client-S0U2jUxeqcjuQZi8"></script>
</head>

<body class="bg-gray-100 font-sans">
  <div class="flex min-h-screen">
    <?php include '../templates/sidebar.php'; ?>

    <div class="flex flex-col flex-1 overflow-auto h-screen">
      <div class="bg-white p-6 flex justify-between items-center shadow">
        <h3 class="text-xl">Halaman Transaksi</h3>
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
        <div class="space-y-6 w-2/3">
          <div class="bg-white p-6 rounded-md shadow space-y-4">
            <h3 class="text-left">Data Transaksi</h3>
            <hr />
            <table class="w-full text-sm border border-gray-400 border-collapse">
              <thead class="bg-gray-100">
                <tr>
                  <th class="font-medium border border-gray-400 px-4 py-3">No</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Kode Transaksi</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Nama Customer</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Total Transaksi</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($transactions)): ?>
                  <?php $no = 1;
                  foreach ($transactions as $transaction): ?>
                    <tr class="text-center">
                      <td class="border border-gray-400 px-4 py-3"><?php echo $no++; ?></td>
                      <td class="border border-gray-400 px-4 py-3 text-left">
                        <?php echo htmlspecialchars($transaction['transaction_code']); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3 text-left">
                        <?php echo htmlspecialchars($transaction['customer_name']); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3 text-left">Rp.
                        <?php echo htmlspecialchars(number_format($transaction['total_amount'], 0, ',', '.')); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3">
                        <div class="flex justify-center gap-3">
                          <button type="button" class="bg-[#5148FF] hover:bg-blue-500 text-white rounded-md p-2 text-sm"
                            onclick="printReceiptById(<?php echo htmlspecialchars($transaction['id']); ?>)">
                            Struk
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr class="text-center">
                    <td colspan="5" class="border border-gray-400 px-4 py-3">Belum ada transaksi sebelumnya.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="flex justify-center items-center bg-white p-4 rounded-md shadow mt-4">
            <div class="inline-flex space-x-2">
              <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                  << </a>
                  <?php else: ?>
                    <span class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                      << </span>
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
                          >>
                        </a>
                      <?php else: ?>
                        <span
                          class="px-4 py-2 text-sm font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                          >>
                        </span>
                      <?php endif; ?>
            </div>
          </div>

          <div class="bg-white p-6 rounded-md shadow space-y-4">
            <h3 class="text-left">Data Produk</h3>
            <hr />
            <table class="w-full text-sm border border-gray-400 border-collapse">
              <thead class="bg-gray-100">
                <tr>
                  <th class="font-medium border border-gray-400 px-4 py-3">No</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Nama Produk</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Harga Satuan</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Stok</th>
                  <th class="font-medium border border-gray-400 px-4 py-3">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($products)): ?>
                  <?php $no = 1;
                  foreach ($products as $product): ?>
                    <tr class="text-center">
                      <td class="border border-gray-400 px-4 py-3"><?php echo $no++; ?></td>
                      <td class="border border-gray-400 px-4 py-3 text-left">
                        <?php echo htmlspecialchars($product['product_name']); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3 text-left">Rp.
                        <?php echo htmlspecialchars(number_format($product['price'], 0, ',', '.')); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3 text-center">
                        <?php echo htmlspecialchars($product['stock']); ?>
                      </td>
                      <td class="border border-gray-400 px-4 py-3">
                        <button class="bg-white border rounded-md border-gray-300 hover:bg-gray-100 px-2 pb-1 text-xl"
                          onclick="addToCart(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                          +
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr class="text-center">
                    <td colspan="5" class="border border-gray-400 px-4 py-3">Tidak ada produk tersedia.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/3">
          <h4>Form Transaksi Baru</h4>
          <hr />
          <form class="space-y-4" id="transaction-form" action="../db_service/transaction/transaction_service.php"
            method="POST">
            <input type="hidden" name="transactionCode" id="transaction_code">
            <input type="hidden" name="products_json" id="products_json">

            <div class="flex flex-col">
              <label class="mb-2 text-sm">Produk yang Dipilih</label>
              <div id="cart-items-container" class="mb-4">
                <p class="text-gray-500 text-center text-sm" id="empty-cart-message">Belum ada produk<br>yang dipilih
                </p>
              </div>
            </div>
            <hr>
            <div class="flex flex-col">
              <label class="mb-2 text-sm">Nama Customer</label>
              <input type="text" name="customerName" id="customer_name"
                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
            </div>

            <div class="flex flex-col">
              <label class="mb-2 text-sm">Total Harga</label>
              <input type="text" name="total_amount_display" id="total_amount_display" readonly
                class="p-2 border border-gray-300 rounded bg-gray-100 cursor-not-allowed" value="0,00" /> <input
                type="hidden" name="totalPrice" id="total_amount_hidden" value="0">
            </div>

            <div class="flex flex-col">
              <label class="mb-2 text-sm">Jumlah Bayar</label>
              <input type="text" name="paid_amount_display" id="paid_amount_display"
                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
                value="0" /> <input type="hidden" name="paidAmount" id="paid_amount_hidden" value="0">
            </div>

            <div class="flex flex-col">
              <label class="mb-2 text-sm">Kembalian</label>
              <input type="text" name="change_amount_display" id="change_amount_display" readonly
                class="p-2 border border-gray-300 rounded bg-gray-100 cursor-not-allowed" value="0,00" /> <input
                type="hidden" name="changeAmount" id="change_amount_hidden" value="0">
            </div>

            <button type="submit" name="save_transaction"
              class="p-2 bg-[#3B378B] w-full text-white rounded hover:bg-[#524CC3] transition">
              Simpan Transaksi
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Inisialisasi cart dari localStorage saat halaman dimuat
    let cart = JSON.parse(localStorage.getItem('pos_cart')) || [];
    console.log('DEBUG: Cart initialized from localStorage:', cart);
    console.log('DEBUG: localStorage "pos_cart" content:', localStorage.getItem('pos_cart'));

    // Fungsi helper untuk menyimpan/memuat cart dari localStorage
    function saveCartToLocalStorage() {
      localStorage.setItem('pos_cart', JSON.stringify(cart));
    }

    function loadCartFromLocalStorage() {
      const storedCart = localStorage.getItem('pos_cart');
      try {
        if (storedCart) {
          cart = JSON.parse(storedCart);
        } else {
          cart = [];
        }
      } catch (e) {
        console.error("Error parsing cart from localStorage:", e);
        cart = [];
        localStorage.removeItem('pos_cart');
      }
    }

    // Fungsi formatRupiah (diubah untuk hanya menghasilkan angka murni, tanpa 'Rp.' atau koma desimal)
    function formatRupiah(angka) {
      if (angka === null || isNaN(angka)) {
        return "0"; // Default ke '0' (tanpa desimal untuk input)
      }
      let num = parseFloat(angka);
      // Jika Anda ingin ribuan dengan titik (misal 10.000) tanpa 'Rp.' dan tanpa koma desimal
      // Angka negatif juga ditangani
      let sign = num < 0 ? '-' : '';
      let absNum = Math.abs(num);
      let parts = absNum.toString().split('.');
      let integerPart = parts[0].split('').reverse().join('').match(/\d{1,3}/g).join('.').split('').reverse().join('');
      let decimalPart = parts.length > 1 ? '.' + parts[1] : ''; // Gunakan '.' sebagai pemisah desimal jika ada

      return sign + integerPart + decimalPart;
    }

    // Fungsi untuk memperbarui tampilan keranjang
    function updateCartDisplay() {
      console.log('DEBUG: updateCartDisplay called. Current cart state:', cart);
      const container = document.getElementById('cart-items-container');
      container.innerHTML = '';
      let total = 0;

      if (cart.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center text-sm" id="empty-cart-message">Belum ada produk<br>yang dipilih</p>';
        // Gunakan '0' langsung untuk default input tampilan, bukan formatRupiah(0)
        document.getElementById('total_amount_display').value = '0';
        document.getElementById('total_amount_hidden').value = 0;
        document.getElementById('paid_amount_display').value = '0'; // Default input '0'
        document.getElementById('change_amount_display').value = '0'; // Default input '0'
        document.getElementById('change_amount_hidden').value = 0;
        document.getElementById('paid_amount_display').classList.add('bg-gray-100', 'cursor-not-allowed');
        document.getElementById('paid_amount_display').readOnly = true;
        document.getElementById('paid_amount_display').placeholder = '';

        saveCartToLocalStorage();
        console.log('DEBUG: Cart is empty, saved empty cart to localStorage.');
        return;
      }

      document.getElementById('empty-cart-message')?.remove();
      document.getElementById('paid_amount_display').classList.remove('bg-gray-100', 'cursor-not-allowed');
      document.getElementById('paid_amount_display').readOnly = false;
      document.getElementById('paid_amount_display').placeholder = 'Masukkan jumlah bayar';

      cart.forEach((item, index) => {
        const subtotal = item.quantity * item.price;
        total += subtotal;

        const itemDiv = document.createElement('div');
        itemDiv.classList.add('flex', 'justify-between', 'items-center', 'mb-2', 'border-b', 'pb-2');
        itemDiv.innerHTML = `
                <div>
                    <span class="text-sm">${item.product_name}</span><br>
                    <span class="text-gray-600 text-xs">${formatRupiah(item.price)} x ${item.quantity}</span>
                </div>
                <div class="flex items-center gap-5">
                    <span class="font-medium">${formatRupiah(subtotal)}</span>
                    <button type="button" class="bg-white border rounded-md border-gray-300 hover:bg-gray-100 px-2 text-xl"
                            onclick="removeFromCart(${index})">
                        -
                    </button>
                </div>
            `;
        container.appendChild(itemDiv);
      });

      document.getElementById('total_amount_display').value = formatRupiah(total); // Menggunakan formatRupiah baru
      document.getElementById('total_amount_hidden').value = total;
      calculateChange();

      saveCartToLocalStorage();
      console.log('DEBUG: Cart saved to localStorage.');
    }

    function printReceiptById(transactionId) {
      if (!transactionId || transactionId === 0) {
        Swal.fire({
          icon: 'error',
          title: 'ID Transaksi Tidak Valid!',
          text: 'Tidak dapat mencetak struk. ID transaksi tidak ditemukan.',
          width: '350px'
        });
        console.error("DEBUG: printReceiptById called with invalid transactionId:", transactionId);
        return;
      }

      const printUrl = 'print_receipt.php?transaction_id=' + transactionId;
      console.log('DEBUG: Manually attempting to print receipt via Fetch API to:', printUrl);

      // Tampilkan loading SweetAlert
      Swal.fire({
        title: 'Mencetak Struk...',
        text: 'Mohon tunggu, struk sedang diproses.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
        width: '350px'
      });

      fetch(printUrl)
        .then(response => {
          if (!response.ok) { // Jika ada error HTTP (misal 404, 500)
            console.error('DEBUG: HTTP error when manually calling print_receipt.php:', response.status, response.statusText);
            return response.text().then(text => { // Ambil teks error jika ada
              Swal.fire({
                icon: 'error',
                title: 'Cetak Gagal!',
                text: 'Terjadi masalah saat mencetak struk. (Status: ' + response.status + '). Periksa log PHP.',
                width: '400px'
              });
              if (text && text.trim() !== "") {
                console.warn("DEBUG: print_receipt.php returned unexpected output:", text);
              }
            });
          }
          console.log('DEBUG: Manual print_receipt.php call successful (HTTP 200 OK).');
          Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, icon: 'success',
            title: 'Struk berhasil dicetak!', width: '300px'
          });
          return response.text(); // Ambil teks output PHP jika ada
        })
        .then(text => {
          if (text && text.trim() !== "") {
            console.warn("DEBUG: print_receipt.php returned unexpected output:", text);
          }
        })
        .catch(error => {
          console.error('DEBUG: Network or Fetch API error during manual receipt printing:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error Koneksi!',
            text: 'Gagal mengirim permintaan cetak struk. Periksa koneksi internet/server.',
            width: '350px'
          });
        });
    }

    // Fungsi addToCart, removeFromCart (tidak ada perubahan)
    function addToCart(product) {
      console.log('DEBUG: addToCart called for product:', product);
      const existingItemIndex = cart.findIndex(item => item.id === product.id);

      if (existingItemIndex > -1) {
        if (cart[existingItemIndex].quantity < product.stock) {
          cart[existingItemIndex].quantity++;
          Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, icon: 'success',
            title: `1x ${product.product_name} ditambahkan`, width: '300px'
          });
        } else {
          Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'warning',
            title: `Stok ${product.product_name} tidak cukup.`, width: '350px'
          });
          return;
        }
      } else {
        if (product.stock > 0) {
          cart.push({ ...product, quantity: 1 });
          Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, icon: 'success',
            title: `${product.product_name} ditambahkan`, width: '300px'
          });
        } else {
          Swal.fire({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'warning',
            title: `${product.product_name} tidak tersedia (stok kosong).`, width: '400px'
          });
          return;
        }
      }
      updateCartDisplay();
    }

    function removeFromCart(index) {
      if (cart[index].quantity > 1) {
        cart[index].quantity--;
        Swal.fire({
          toast: true, position: 'top-end', showConfirmButton: false, timer: 1000, icon: 'success',
          title: `1x ${cart[index].product_name} dikurangi`, width: '300px'
        });
      } else {
        Swal.fire({
          title: 'Hapus Item', text: `Anda yakin ingin menghapus "${cart[index].product_name}" dari keranjang?`, icon: 'warning',
          showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', reverseButtons: true, width: '350px'
        }).then((result) => {
          if (result.isConfirmed) {
            cart.splice(index, 1);
            updateCartDisplay();
            Swal.fire({
              toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, icon: 'success',
              title: 'Produk dihapus dari keranjang', width: '300px'
            });
          }
        });
        return;
      }
      updateCartDisplay();
    }

    // Fungsi calculateChange (diperbaiki akses ID dan logika)
    function calculateChange() {
      const totalAmount = parseFloat(document.getElementById('total_amount_hidden').value) || 0;
      let paidAmountText = document.getElementById('paid_amount_display').value;
      // Hanya hapus spasi dan titik ribuan, jangan hapus koma desimal jika ada
      paidAmountText = paidAmountText.replace(/\s+/g, '').replace(/\./g, '');
      const paidAmount = parseFloat(paidAmountText) || 0;

      document.getElementById('paid_amount_hidden').value = paidAmount;

      const change = paidAmount - totalAmount;
      document.getElementById('change_amount_display').value = formatRupiah(change); // Gunakan formatRupiah baru
      document.getElementById('change_amount_hidden').value = change;
    }

    // Event listener untuk input jumlah bayar (paid_amount_display)
    document.getElementById('paid_amount_display').addEventListener('input', function () {
      // Membersihkan string: hapus semua spasi dan titik (pemisah ribuan)
      // Koma (pemisah desimal) akan tetap ada jika user mengetiknya, lalu parseFloat akan menanganinya
      let value = this.value.replace(/\s+/g, '').replace(/\./g, '');

      // Hanya tampilkan '0' jika input kosong atau tidak valid setelah dibersihkan
      if (value === '') {
        this.value = '0'; // Default input 0
        calculateChange();
        return;
      }

      // Jika input valid angka, format ulang dan tampilkan
      let numValue = parseFloat(value);
      if (isNaN(numValue)) { // Jika bukan angka (misal user mengetik 'abc')
        this.value = '0'; // Default input 0
      } else {
        this.value = formatRupiah(numValue); // Gunakan formatRupiah baru
      }
      calculateChange();
    });

    // Event listener untuk blur jumlah bayar (paid_amount_display)
    document.getElementById('paid_amount_display').addEventListener('blur', function () {
      // Membersihkan string dengan cara yang sama
      let value = this.value.replace(/\s+/g, '').replace(/\./g, '');
      let numValue = parseFloat(value);

      // Jika saat blur, nilai input kosong atau 0 (setelah dibersihkan), tampilkan "0"
      if (isNaN(numValue) || numValue === 0) {
        this.value = '0'; // Default input 0
      } else {
        this.value = formatRupiah(numValue); // Gunakan formatRupiah baru
      }
    });

    // Event listener untuk submit form transaksi (tetap sama)
    // Event listener untuk submit form transaksi
    // Event listener untuk submit form transaksi
    document.getElementById('transaction-form').addEventListener('submit', function (event) {
      event.preventDefault(); // Mencegah submit default

      const customerName = document.getElementById('customer_name').value.trim();
      const totalAmount = parseFloat(document.getElementById('total_amount_hidden').value);
      const paidAmount = parseFloat(document.getElementById('paid_amount_hidden').value);

      if (cart.length === 0) {
        Swal.fire('Peringatan!', 'Keranjang masih kosong.', 'warning'); return;
      }
      if (customerName === '') {
        Swal.fire('Peringatan!', 'Nama customer tidak boleh kosong.', 'warning'); return;
      }
      if (totalAmount <= 0) {
        Swal.fire('Peringatan!', 'Total harga harus lebih dari Rp. 0.', 'warning'); return;
      }
      if (paidAmount < totalAmount) {
        Swal.fire('Peringatan!', 'Jumlah bayar tidak mencukupi.', 'warning'); return;
      }
      if (paidAmount <= 0) {
        Swal.fire('Peringatan!', 'Jumlah bayar harus lebih dari Rp. 0.', 'warning'); return;
      }

      const timestamp = Date.now().toString().slice(-6);
      const transactionCode = 'TRN-' + timestamp; // Hasilnya akan menjadi 4 ('TRN-') + 6 digit = 10 karakter
      document.getElementById('transaction_code').value = transactionCode;

      // *** PERUBAHAN DI SINI ***
      // Buat array baru yang hanya berisi id, price, dan quantity dari setiap produk di keranjang
      const productsToSend = cart.map(item => ({
        id: item.id,
        price: item.price,
        quantity: item.quantity,
        name: item.product_name // PASTIKAN NAMA PRODUK JUGA DIKIRIMKAN DI SINI!
      }));

      // Masukkan string JSON dari productsToSend ke input hidden products_json
      document.getElementById('products_json').value = JSON.stringify(productsToSend);

      // *** INI ADALAH PENAMBAHAN UNTUK MEMASTIKAN 'save_transaction' TERKIRIM ***
      const saveTransactionInput = document.createElement('input');
      saveTransactionInput.type = 'hidden';
      saveTransactionInput.name = 'save_transaction'; // Nama yang diharapkan oleh backend
      saveTransactionInput.value = '1'; // Nilai apapun, yang penting ada
      this.appendChild(saveTransactionInput);
      // *******************************************************************

      this.submit();
    });

    // Logika SweetAlert2 untuk notifikasi status (dari URL)
    window.addEventListener('DOMContentLoaded', (event) => {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');
      const message = urlParams.get('message');

      const snapToken = "<?php echo htmlspecialchars($midtransSnapToken ?? ''); ?>";
      const midtransOrderId = "<?php echo htmlspecialchars($midtransOrderId ?? ''); ?>";

      // DEBUGGING: Log snapToken dan midtransOrderId di frontend
      console.log('DEBUG Frontend: Snap Token received:', snapToken);
      console.log('DEBUG Frontend: Midtrans Order ID received:', midtransOrderId);
      console.log('DEBUG Frontend: Status URL param:', status);


      // --- Pemicu Midtrans Snap (Jika transaksi lokal berhasil disimpan) ---
      // --- Pemicu Midtrans Snap (Dipicu setelah transaksi lokal berhasil disimpan) ---
      if (status === 'transaction_success' && snapToken) {
        console.log('DEBUG Frontend: Triggering Midtrans Snap for Order ID:', midtransOrderId);

        snap.pay(snapToken, {
          onSuccess: function (result) {
            console.log('Midtrans Success:', result);
            // Kosongkan keranjang dan update tampilan hanya JIKA transaksi benar-benar selesai
            localStorage.removeItem('pos_cart');
            updateCartDisplay();

            // PANGGIL CETAK STRUK via AJAX/FETCH
            if (midtransOrderId && midtransOrderId !== '0') {
              const printUrl = 'print_receipt.php?transaction_id=' + midtransOrderId;
              console.log('DEBUG: Attempting to print receipt via Fetch API to:', printUrl);

              fetch(printUrl)
                .then(response => {
                  if (!response.ok) {
                    console.error('DEBUG: HTTP error when calling print_receipt.php:', response.status, response.statusText);
                    Swal.fire({
                      icon: 'error',
                      title: 'Cetak Struk Gagal!',
                      text: 'Terjadi masalah saat mencoba mencetak struk. Periksa log PHP dan konsol browser.',
                      width: '350px'
                    });
                  } else {
                    console.log('DEBUG: print_receipt.php call successful (HTTP 200 OK).');
                    Swal.fire({
                      toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, icon: 'success',
                      title: 'Struk berhasil dicetak!', width: '300px'
                    });
                  }
                  return response.text();
                })
                .then(text => {
                  if (text && text.trim() !== "") {
                    console.warn("DEBUG: print_receipt.php returned unexpected output:", text);
                  }
                })
                .catch(error => {
                  console.error('DEBUG: Network or Fetch API error during receipt printing:', error);
                  Swal.fire({
                    icon: 'error',
                    title: 'Error Koneksi!',
                    text: 'Gagal mengirim permintaan cetak struk.',
                    width: '350px'
                  });
                });
            } else {
              console.warn("DEBUG: midtransOrderId not valid for printing receipt (empty or 0).");
              Swal.fire({
                icon: 'info',
                title: 'Transaksi Berhasil!',
                text: 'Pembayaran berhasil, namun struk tidak dicetak otomatis karena ID transaksi tidak valid.',
                width: '450px'
              });
            }
            // Setelah semua aksi selesai, bersihkan URL.
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: newUrl }, '', newUrl);
          },
          onPending: function (result) {
            console.log('Midtrans Pending:', result);
            Swal.fire({
              title: 'Pembayaran Tertunda!',
              text: 'Pembayaran Anda sedang menunggu konfirmasi Midtrans.',
              icon: 'info',
              confirmButtonText: 'OK',
              width: '350px'
            }).then(() => {
              updateCartDisplay();
              const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
              window.history.replaceState({ path: newUrl }, '', newUrl);
            });
          },
          onError: function (result) {
            console.log('Midtrans Error:', result);
            Swal.fire({
              title: 'Pembayaran Gagal!',
              text: 'Terjadi kesalahan saat memproses pembayaran Anda. Silakan coba lagi.',
              icon: 'error',
              confirmButtonText: 'OK',
              width: '350px'
            }).then(() => {
              updateCartDisplay();
              const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
              window.history.replaceState({ path: newUrl }, '', newUrl);
            });
          },
          onClose: function () {
            console.log('Midtrans pop-up closed.');
            Swal.fire({
              title: 'Pembayaran Dibatalkan',
              text: 'Anda menutup jendela pembayaran. Transaksi belum selesai.',
              icon: 'info',
              confirmButtonText: 'OK',
              width: '350px'
            }).then(() => {
              updateCartDisplay();
              const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
              window.history.replaceState({ path: newUrl }, '', newUrl);
            });
          }
        });
      }
      // --- Penanganan Status Lain (Jika transaksi lokal GAGAL disimpan) ---
      else if (status) {
        // Jika status bukan 'transaction_success', tampilkan SweetAlert sesuai
        let title = '';
        let text = '';
        let icon = '';

        switch (status) {
          case 'transaction_failed':
            title = 'Transaksi Gagal!';
            text = message || 'Terjadi kesalahan saat menyimpan transaksi ke database lokal.';
            icon = 'error';
            break;
          case 'not_enough_stock':
            title = 'Transaksi Gagal!';
            text = message || 'Stok produk tidak mencukupi.';
            icon = 'warning';
            break;
          case 'invalid_data':
            title = 'Peringatan!';
            text = message || 'Data transaksi tidak lengkap atau tidak valid.';
            icon = 'warning';
            break;
          default:
            return;
        }

        Swal.fire({
          title: title, text: text, icon: icon, confirmButtonText: 'OK', width: '350px'
        }).then(() => {
          const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
          window.history.replaceState({ path: newUrl }, '', newUrl);
        });
      }
      // Pastikan updateCartDisplay() dipanggil di akhir DOMContentLoaded
      updateCartDisplay();
    });
  </script>
</body>

</html>