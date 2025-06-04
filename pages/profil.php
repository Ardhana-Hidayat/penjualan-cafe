<?php
// pages/profil.php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/auth/login.php"); // Redirect ke halaman login jika belum login
    exit();
}

include '../config/koneksi.php'; // Sertakan koneksi database
// include '../vendor/autoload.php'; // Ini tidak selalu diperlukan jika SweetAlert2 dipanggil via CDN di frontend

// Inisialisasi variabel untuk data user
$username = $_SESSION['username'] ?? 'Guest'; // Ambil username dari session
$user_id = $_SESSION['user_id'];
$full_name = 'N/A'; // Default jika tidak ada nama lengkap
$email = 'N/A';     // Default jika tidak ada email

// Ambil data user dari database
$stmt_user = mysqli_prepare($link, "SELECT username FROM user WHERE id = ?");
if ($stmt_user) {
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user_data = mysqli_fetch_assoc($result_user);
        $username = htmlspecialchars($user_data['username']);
        $full_name = htmlspecialchars($user_data['name'] ?? 'N/A'); // Asumsi ada kolom 'name'
        
    }
    mysqli_stmt_close($stmt_user);
} else {
    error_log("Error preparing user data query: " . mysqli_error($link));
}


// --- Logika untuk Mengganti Password (TANPA PASSWORD LAMA) ---
$message_type = '';
$message_text = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Hanya validasi password baru dan konfirmasinya
    if ($new_password === $confirm_password) {
        if (strlen($new_password) >= 6) { // Minimal 6 karakter
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt_update_password = mysqli_prepare($link, "UPDATE user SET password = ? WHERE id = ?");
            if ($stmt_update_password) {
                mysqli_stmt_bind_param($stmt_update_password, "si", $hashed_new_password, $user_id);
                if (mysqli_stmt_execute($stmt_update_password)) {
                    $message_type = 'success';
                    $message_text = 'Password berhasil diubah!';
                } else {
                    $message_type = 'error';
                    $message_text = 'Gagal mengubah password. Silakan coba lagi.';
                    error_log("Error updating password: " . mysqli_error($link));
                }
                mysqli_stmt_close($stmt_update_password);
            } else {
                $message_type = 'error';
                $message_text = 'Kesalahan database saat memperbarui password.';
                error_log("Error preparing update password query: " . mysqli_error($link));
            }
        } else {
            $message_type = 'warning';
            $message_text = 'Password baru minimal harus 6 karakter.';
        }
    } else {
        $message_type = 'warning';
        $message_text = 'Konfirmasi password tidak cocok.';
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Penjualan Cafe | Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> </head>

<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <?php include '../templates/sidebar.php'; ?>

        <div class="flex flex-col flex-1 overflow-auto h-screen">
            <div class="bg-white p-6 flex justify-between items-center shadow">
                <h3 class="text-xl py-2">Halaman Profil</h3>
                <div class="flex gap-2 p-2 rounded-md border border-[#1E1B57]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M19.938 8H21C21.5304 8 22.0391 8.21071 22.4142 8.58579C22.7893 8.96086 23 9.46957 23 10V14C23 14.5304 22.7893 15.0391 22.4142 15.4142C22.0391 15.7893 21.5304 16 21 16H19.938C19.6944 17.9334 18.7535 19.7114 17.292 21.0002C15.8304 22.2891 13.9487 23.0002 12 23V21C13.5913 21 15.1174 20.3679 16.2426 19.2426C17.3679 18.1174 18 16.5913 18 15V9C18 7.4087 17.3679 5.88258 16.2426 4.75736C15.1174 3.63214 13.5913 3 12 3C10.4087 3 8.88258 3.63214 7.75736 4.75736C6.63214 5.88258 6 7.4087 6 9V16H3C2.46957 16 1.96086 15.7893 1.58579 15.4142C1.21071 15.0391 1 14.5304 1 14V10C1 9.46957 1.21071 8.96086 1.58579 8.58579C1.96086 8.21071 2.46957 8 3 8H4.062C4.30603 6.06689 5.24708 4.28927 6.70857 3.00068C8.17007 1.71208 10.0516 1.00108 12 1.00108C13.9484 1.00108 15.8299 1.71208 17.2914 3.00068C18.7529 4.28927 19.694 6.06689 19.938 8ZM3 10V14H4V10H3ZM20 10V14H21V10H20ZM7.76 15.785L8.82 14.089C9.77303 14.6861 10.8754 15.0019 12 15C13.1246 15.0019 14.227 14.6861 15.18 14.089L16.24 15.785C14.9693 16.5813 13.4996 17.0025 12 17C10.5004 17.0025 9.03067 16.5813 7.76 15.785Z"
                            fill="#1E1B57" />
                    </svg>
                    <span>Profil</span>
                </div>
            </div>

            <div class="p-6 flex items-start space-x-6">
                <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/3">
                    <div class="w-full flex justify-center">
                        <svg width="72" height="72" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12.5" cy="12.5" r="8.5" fill="#D9D9D9" fill-opacity="0.3" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M16.3182 9.63642C16.3182 10.6491 15.916 11.6202 15.1999 12.3363C14.4839 13.0523 13.5127 13.4546 12.5001 13.4546C11.4874 13.4546 10.5163 13.0523 9.8002 12.3363C9.08416 11.6202 8.68188 10.6491 8.68188 9.63642C8.68188 8.62377 9.08416 7.6526 9.8002 6.93656C10.5163 6.22051 11.4874 5.81824 12.5001 5.81824C13.5127 5.81824 14.4839 6.22051 15.1999 6.93656C15.916 7.6526 16.3182 8.62377 16.3182 9.63642ZM14.4092 9.63642C14.4092 10.1427 14.208 10.6283 13.85 10.9863C13.492 11.3444 13.0064 11.5455 12.5001 11.5455C11.9937 11.5455 11.5082 11.3444 11.1501 10.9863C10.7921 10.6283 10.591 10.1427 10.591 9.63642C10.591 9.1301 10.7921 8.64451 11.1501 8.28649C11.5082 7.92846 11.9937 7.72733 12.5001 7.72733C13.0064 7.72733 13.492 7.92846 13.85 8.28649C14.208 8.64451 14.4092 9.1301 14.4092 9.63642Z"
                                fill="#5B5B5B" fill-opacity="0.3" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12.5 2C6.70114 2 2 6.70114 2 12.5C2 18.2989 6.70114 23 12.5 23C18.2989 23 23 18.2989 23 12.5C23 6.70114 18.2989 2 12.5 2ZM3.90909 12.5C3.90909 14.495 4.58968 16.3315 5.73036 17.7901C6.53165 16.7383 7.56515 15.8859 8.7502 15.2994C9.93525 14.7129 11.2398 14.4082 12.562 14.4091C13.8673 14.4076 15.1556 14.7041 16.3288 15.2762C17.502 15.8482 18.5291 16.6806 19.3317 17.7099C20.1587 16.6252 20.7156 15.3591 20.9562 14.0164C21.1968 12.6737 21.1142 11.293 20.7153 9.98858C20.3164 8.68414 19.6125 7.49345 18.6621 6.51504C17.7116 5.53662 16.5418 4.79859 15.2495 4.36203C13.9571 3.92547 12.5794 3.80292 11.2303 4.00452C9.88123 4.20612 8.59953 4.72608 7.49128 5.52137C6.38303 6.31666 5.48009 7.36442 4.85717 8.57796C4.23425 9.7915 3.90926 11.1359 3.90909 12.5ZM12.5 21.0909C10.5278 21.0941 8.61514 20.4156 7.08582 19.1704C7.70132 18.2889 8.52068 17.5693 9.47416 17.0727C10.4276 16.5761 11.487 16.3173 12.562 16.3182C13.6237 16.3173 14.6702 16.5697 15.6147 17.0544C16.5591 17.5392 17.3743 18.2424 17.9925 19.1055C16.4513 20.3912 14.5071 21.094 12.5 21.0909Z"
                                fill="#5B5B5B" fill-opacity="0.3" />
                        </svg>
                    </div>
                    <hr>
                    <div>
                        <p class="font-semibold">Username</p>
                        <p class="text-sm"><?php echo $username; ?></p>
                    </div>
                    <?php if ($full_name !== 'N/A'): ?>
                        <div>
                            <p class="font-semibold">Nama Lengkap</p>
                            <p class="text-sm"><?php echo $full_name; ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($email !== 'N/A'): ?>
                        <div>
                            <p class="font-semibold">Email</p>
                            <p class="text-sm"><?php echo $email; ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/3">
                    <h4>Ganti Password</h4>
                    <hr />
                    <form id="change-password-form" class="flex flex-col gap-4" method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="flex flex-col">
                            <label class="mb-1 text-sm" for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" required
                                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 text-sm" for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
                        </div>

                        <button type="submit"
                            class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
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
                });
            }
        });
    </script>
</body>

</html>