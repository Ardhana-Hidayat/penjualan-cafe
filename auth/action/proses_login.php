<?php
// Mulai session di awal skrip. Penting untuk menyimpan status login pengguna.
session_start();

// Path ke file koneksi.php.
// Dari auth/action/, kita perlu keluar 2 folder (action/ lalu auth/) untuk sampai ke root proyek,
// lalu masuk ke folder config/
include '../../config/koneksi.php'; 

// Pastikan request datang dari metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captcha_input = $_POST['captcha_input'];
    // Jika Anda menggunakan validasi captcha yang lebih kuat dengan session,
    // Anda akan membandingkan $captcha_input dengan $_SESSION['captcha_text'] di sini.

    // --- 1. Validasi Input Dasar ---
    if (empty($username) || empty($password) || empty($captcha_input)) {
        header("location: ../login.php?status=empty_fields"); // Kembali ke login.php di folder auth
        exit();
    }

    // --- 2. Validasi Captcha (sisi server) ---
    // Seperti yang sudah dibahas sebelumnya, validasi sisi server yang kuat memerlukan session.
    // Jika Anda tidak menggunakan session untuk captcha, validasi ini mungkin tidak aman untuk produksi.
    // Contoh sederhana (tidak disarankan untuk produksi):
    // if ($captcha_input !== $_POST['captcha_value']) { // Mengambil dari hidden/disabled input
    //     header("location: ../login.php?status=captcha_wrong"); // Kembali ke login.php
    //     exit();
    // }


    // --- 3. Cari pengguna di database berdasarkan username ---
    // Gunakan prepared statements untuk mencegah SQL Injection
    $stmt = mysqli_prepare($link, "SELECT id, username, password FROM user WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username); // 's' menandakan tipe string
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt); // Simpan hasil untuk bisa memeriksa jumlah baris

    // --- 4. Cek apakah username ditemukan ---
    if (mysqli_stmt_num_rows($stmt) == 1) {
        // Bind hasil ke variabel
        mysqli_stmt_bind_result($stmt, $user_id, $db_username, $hashed_password_from_db);
        mysqli_stmt_fetch($stmt); // Ambil satu baris hasil

        // --- 5. Verifikasi Password ---
        // Gunakan password_verify() untuk membandingkan password yang dimasukkan dengan hash dari database
        if (password_verify($password, $hashed_password_from_db)) {
            // Login berhasil
            // Set session variables
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_username;

            // Redirect ke halaman dashboard atau halaman utama yang dilindungi
            header("location: ../../pages/dashboard.php?status=login_success"); //redirect ke dashboard
            // Anda perlu membuat file dashboard.php ini di root proyek atau sesuaikan path-nya
            exit();
        } else {
            // Password salah
            header("location: ../login.php?status=failed"); // Kembali ke login.php
            exit();
        }
    } else {
        // Username tidak ditemukan
        header("location: ../login.php?status=failed"); // Kembali ke login.php
        exit();
    }

} else {
    // Jika diakses langsung tanpa POST, redirect ke halaman login
    header("location: ../login.php");
    exit();
}
?>