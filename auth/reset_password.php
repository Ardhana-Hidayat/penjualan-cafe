<?php
// pages/auth/reset_password.php
session_start(); // Mulai sesi untuk pesan status

include '../config/koneksi.php'; // Sesuaikan path jika lokasi koneksi.php berbeda

$message_type = '';
$message_text = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($username)) {
    $message_type = 'error';
    $message_text = 'Username tidak boleh kosong.';
  } elseif (empty($new_password) || empty($confirm_password)) {
    $message_type = 'error';
    $message_text = 'Password baru dan konfirmasi password tidak boleh kosong.';
  } elseif ($new_password !== $confirm_password) {
    $message_type = 'warning';
    $message_text = 'Konfirmasi password tidak cocok.';
  } else {
    // Cek apakah username ada di database
    $stmt_check_user = mysqli_prepare($link, "SELECT id FROM user WHERE username = ?");
    if ($stmt_check_user) {
      mysqli_stmt_bind_param($stmt_check_user, "s", $username);
      mysqli_stmt_execute($stmt_check_user);
      mysqli_stmt_store_result($stmt_check_user);

      if (mysqli_stmt_num_rows($stmt_check_user) > 0) {
        // Username ditemukan, update password
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt_update_password = mysqli_prepare($link, "UPDATE user SET password = ? WHERE username = ?");
        if ($stmt_update_password) {
          mysqli_stmt_bind_param($stmt_update_password, "ss", $hashed_new_password, $username);
          if (mysqli_stmt_execute($stmt_update_password)) {
            $message_type = 'success';
            $message_text = 'Password berhasil direset!';
          } else {
            $message_type = 'error';
            $message_text = 'Gagal mereset password. Silakan coba lagi.';
            error_log("Error updating password on reset: " . mysqli_error($link));
          }
          mysqli_stmt_close($stmt_update_password);
        } else {
          $message_type = 'error';
          $message_text = 'Kesalahan database saat menyiapkan update password.';
          error_log("Error preparing update password query on reset: " . mysqli_error($link));
        }
      } else {
        $message_type = 'error';
        $message_text = 'Username tidak ditemukan.';
      }
      mysqli_stmt_close($stmt_check_user);
    } else {
      $message_type = 'error';
      $message_text = 'Kesalahan database saat memeriksa username.';
      error_log("Error preparing check user query on reset: " . mysqli_error($link));
    }
  }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penjualan Cafe | Reset Password</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
    <h2 class="text-2xl text-left mb-6">Ganti Password</h2>
    <form id="reset-password-form" action="" method="POST" class="flex flex-col gap-4">
      <div class="flex flex-col">
        <label class="mb-1 text-sm font-medium" for="username">Username</label>
        <input type="text" id="username" name="username" required
          class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
      </div>
      <div class="flex flex-col">
        <label class="mb-1 text-sm font-medium" for="new_password">Password Baru</label>
        <input type="password" id="new_password" name="new_password" required
          class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
      </div>
      <div class="flex flex-col">
        <label class="mb-1 text-sm font-medium" for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required
          class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
      </div>

      <button type="submit" class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition">
        Simpan
      </button>
    </form>
  </div>

  <script>
    // Logika SweetAlert2 untuk notifikasi status
    window.addEventListener('DOMContentLoaded', (event) => {
      const messageType = "<?php echo $message_type; ?>";
      const messageText = "<?php echo $message_text; ?>";

      if (messageType && messageText) {
        Swal.fire({
          icon: messageType,
          title: messageType === 'success' ? 'Berhasil!' : (messageType === 'warning' ? 'Peringatan!' : 'Error!'),
          text: messageText,
          showConfirmButton: true,
          confirmButtonText: 'OK',
          width: '350px'
        }).then((result) => {
          // Opsional: Redirect ke halaman login setelah sukses
          if (messageType === 'success') {
            window.location.href = 'login.php'; // Ganti dengan path ke halaman login Anda
          }
        });
      }
    });
  </script>
</body>

</html>