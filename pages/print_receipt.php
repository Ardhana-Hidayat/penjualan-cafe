<?php
// pages/print_receipt.php
session_start();

include '../config/koneksi.php'; // Sesuaikan path jika lokasi koneksi.php berbeda

$transactionDetails = null;
$transactionId = $_GET['transaction_id'] ?? null;

if (!$transactionId) {
    die("ID Transaksi tidak ditemukan.");
}

// --- Ambil Data Transaksi Utama ---
// Tambahkan paidAmount dan changeAmount ke SELECT query
$query_main = "SELECT id, transactionCode, customerName, totalPrice, paidAmount, changeAmount, createdAt 
               FROM transactions 
               WHERE id = ?";
$stmt_main = mysqli_prepare($link, $query_main);

if ($stmt_main === false) {
    die("Error menyiapkan query utama: " . mysqli_error($link));
}

mysqli_stmt_bind_param($stmt_main, "i", $transactionId);
mysqli_stmt_execute($stmt_main);
$result_main = mysqli_stmt_get_result($stmt_main);

if (mysqli_num_rows($result_main) == 0) {
    die("Transaksi tidak ditemukan.");
}
$transaction = mysqli_fetch_assoc($result_main);
mysqli_stmt_close($stmt_main);

// --- Ambil Detail Transaksi (Item Produk yang Dibeli) ---
$query_details = "SELECT td.quantity, td.totalPrice AS subtotal_item, 
                         p.name AS product_name, p.price AS product_price 
                  FROM detail_transactions AS td
                  INNER JOIN products AS p ON td.productId = p.id
                  WHERE td.transactionId = ?";
$stmt_details = mysqli_prepare($link, $query_details);

if ($stmt_details === false) {
    die("Error menyiapkan query detail: " . mysqli_error($link));
}

mysqli_stmt_bind_param($stmt_details, "i", $transactionId);
mysqli_stmt_execute($stmt_details);
$result_details = mysqli_stmt_get_result($stmt_details);

$items = [];
if (mysqli_num_rows($result_details) > 0) {
    while ($row = mysqli_fetch_assoc($result_details)) {
        $items[] = $row;
    }
}
mysqli_stmt_close($stmt_details);

mysqli_close($link);

// Fungsi untuk memformat angka sesuai standar (tanpa 'Rp.', dengan titik ribuan, koma desimal, 2 desimal)
function formatRupiahDisplay($angka)
{
    if ($angka === null || is_nan($angka)) {
        return "0,00"; // Default dengan 2 desimal
    }
    $num = (float) $angka;
    // Handle angka negatif
    $sign = '';
    if ($num < 0) {
        $sign = '-';
        $num = abs($num);
    }

    $parts = explode('.', (string) $num);
    $integerPart = number_format((float) $parts[0], 0, ',', '.'); // Format integer
    $decimalPart = isset($parts[1]) ? $parts[1] : '00';
    $decimalPart = str_pad($decimalPart, 2, '0', STR_PAD_RIGHT); // Pastikan 2 desimal

    return $sign . $integerPart . ',' . $decimalPart;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #<?php echo htmlspecialchars($transaction['transactionCode']); ?></title>
    <style>
        body {
            font-family: monospace;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
        }

        .receipt-container {
            width: 300px;
            /* Lebar standar untuk struk thermal */
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            line-height: 1.5;
            text-align: center;
        }

        .receipt-header h1 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #333;
        }

        .receipt-header p {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #666;
        }

        .receipt-divider {
            border-top: 1px dashed #ccc;
            margin: 15px 0;
        }

        .receipt-details,
        .receipt-items {
            text-align: left;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .receipt-items th,
        .receipt-items td {
            text-align: left;
            padding: 5px 0;
        }

        /* Penyesuaian agar kolom Qty dan Subtotal rata kanan */
        .receipt-items th:nth-child(2),
        .receipt-items td:nth-child(2) {
            /* Qty */
            text-align: center;
        }

        .receipt-items th:nth-child(3),
        .receipt-items td:nth-child(3) {
            /* Subtotal */
            text-align: right;
        }

        .receipt-items .item-qty-price {
            font-size: 11px;
            color: #888;
        }

        .receipt-summary td {
            text-align: left;
            padding: 5px 0;
        }

        .receipt-summary td:nth-child(2) {
            text-align: right;
            font-weight: bold;
        }

        .receipt-footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .receipt-footer p {
            margin: 0;
        }

        .col-summary {
            display: flex;
            justify-content: space-between;
        }

        /* --- Media Print Styles --- */
        @media print {
            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                border: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <br>
        <div class="receipt-header">
            <h1>Penjualan Cafe</h1>
            <p>Jl. Contoh No. 123, Kota Anda</p>
            <p>Telp: (021) 12345678</p>
        </div>

        <div class="receipt-divider"></div>

        <div class="receipt-details">
            <p><strong>Transaksi ID:</strong> <?php echo htmlspecialchars($transaction['transactionCode']); ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($transaction['customerName']); ?></p>
            <p><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i:s', strtotime($transaction['createdAt'])); ?></p>
        </div>

        <div class="receipt-divider"></div>

        <table class="receipt-items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['product_name']); ?><br>
                            <span class="item-qty-price"><?php echo formatRupiahDisplay($item['product_price']); ?> x
                                <?php echo htmlspecialchars($item['quantity']); ?></span>
                        </td>

                        <td><?php echo formatRupiahDisplay($item['subtotal_item']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="receipt-divider"></div>

        <div class="receipt-summary">
            <div class="col-summary">
                <span>Total:</span>
                <span>Rp. <?php echo formatRupiahDisplay($transaction['totalPrice']); ?></span>
            </div>
            <div class="col-summary">
                <span>Bayar:</span>
                <span>Rp. <?php echo formatRupiahDisplay($transaction['paidAmount']); ?></span>
            </div>
            <div class="col-summary">
                <span>Kembalian:</span>
                <span>Rp. <?php echo formatRupiahDisplay($transaction['changeAmount']); ?></span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <div class="receipt-footer">
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p>~~ Selamat Menikmati ~~</p>
        </div>
    </div>

    <script>
        // Pemicu dialog cetak saat halaman dimuat
        window.onload = function () {
            window.print();
        };

        // Tutup tab/jendela setelah cetak (opsional)
        window.onafterprint = function () {
            window.close();
        };
    </script>
</body>

</html>