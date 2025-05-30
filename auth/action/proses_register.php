<?php
// Mulai session di awal skrip jika Anda berencana menggunakan session untuk captcha atau pesan
// session_start(); 

// Path ke file koneksi.php.
// Dari auth/action/, kita perlu keluar 2 folder (action/ lalu auth/) untuk sampai ke root proyek,
// lalu masuk ke folder config/
include '../../config/koneksi.php';

// Pastikan request datang dari metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $captcha_input = $_POST['captcha_input'];
    // Untuk validasi captcha server-side yang kuat, Anda harus mendapatkan nilai captcha yang dihasilkan
    // dari $_SESSION, bukan dari input yang disabled di HTML.
    // Contoh: $generated_captcha_from_session = $_SESSION['captcha_text'] ?? '';


    // --- 1. Validasi Input Dasar ---
    if (empty($username) || empty($password) || empty($confirm_password) || empty($captcha_input)) {
        header("location: ../register.php?status=empty_fields"); // Kembali ke register.php di folder auth
        exit();
    }

    // --- 2. Validasi Captcha (sisi server) ---
    // PENTING: Validasi captcha ini hanya contoh dasar dan TIDAK AMAN untuk produksi
    // karena nilai captcha dari input yang disabled di HTML bisa dimanipulasi.
    // Untuk keamanan, Anda harus:
    // 1. Saat generate captcha di register.php, simpan nilai captcha di $_SESSION.
    // 2. Di sini, bandingkan $captcha_input dengan $_SESSION['captcha_text'].
    // Contoh validasi captcha sederhana (tidak disarankan untuk produksi)
    // if ($captcha_input !== $_POST['captcha_value']) { // Mengambil dari hidden/disabled input
    //     header("location: ../register.php?status=captcha_wrong"); // Kembali ke register.php
    //     exit();
    // }

    // --- 3. Verifikasi Konfirmasi Password ---
    if ($password !== $confirm_password) {
        header("location: ../register.php?status=password_mismatch"); // Kembali ke register.php
        exit();
    }

    // --- 4. Cek apakah username sudah ada (pencegahan duplikasi) ---
    // Gunakan prepared statements untuk keamanan
    $stmt_check = mysqli_prepare($link, "SELECT id FROM user WHERE username = ?");
    mysqli_stmt_bind_param($stmt_check, "s", $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        header("location: ../register.php?status=username_exists"); // Kembali ke register.php
        mysqli_stmt_close($stmt_check);
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // --- 5. Hashing Password ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // --- 6. Persiapan Query INSERT menggunakan Prepared Statements ---
    $stmt_insert = mysqli_prepare($link, "INSERT INTO user (username, password) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt_insert, "ss", $username, $hashed_password);

    // --- 7. Eksekusi Query ---
    if (mysqli_stmt_execute($stmt_insert)) {
        // Registrasi berhasil
        // Registrasi berhasil
        header("location: ../login.php?status=registration_success"); // redirect ke login.php
    } else {
        // Registrasi gagal (kesalahan database)
        header("location: ../register.php?status=failed&error=" . urlencode(mysqli_error($link))); // Kembali ke register.php
    }

    // Tutup statement
    mysqli_stmt_close($stmt_insert);

    // Tutup koneksi database
    mysqli_close($link);

    exit(); // Penting untuk menghentikan eksekusi setelah redirect

} else {
    // Jika diakses langsung tanpa POST, redirect ke halaman register
    header("location: ../register.php"); // Kembali ke register.php
    exit();
}
?>