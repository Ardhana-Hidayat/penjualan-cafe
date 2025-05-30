<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Akun Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css">
    <style>
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
  </head>
  <body class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
      <h2 class="text-2xl text-left mb-6">Register</h2>

      <?php
      // PHP untuk menampilkan pesan dari proses registrasi
      if (isset($_GET['status'])) {
          if ($_GET['status'] == 'success') {
              echo '<div class="message success">Registrasi berhasil! Silakan <a href="/auth/login.php" class="text-green-800 underline">Login</a>.</div>';
          } elseif ($_GET['status'] == 'password_mismatch') {
              echo '<div class="message error">Konfirmasi password tidak cocok.</div>';
          } elseif ($_GET['status'] == 'username_exists') {
              echo '<div class="message error">Username sudah terdaftar. Silakan gunakan username lain.</div>';
          } elseif ($_GET['status'] == 'empty_fields') {
              echo '<div class="message error">Username dan password tidak boleh kosong.</div>';
          } elseif ($_GET['status'] == 'captcha_wrong') {
              echo '<div class="message error">Captcha salah, coba lagi.</div>';
          } elseif ($_GET['status'] == 'failed') {
              echo '<div class="message error">Registrasi gagal. Silakan coba lagi.</div>';
          }
      }
      ?>

      <form
        id="register-form"
        action="action/proses_register.php" method="POST" class="flex flex-col gap-4"
      >
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium" for="username">Username</label>
          <input
            type="text"
            name="username"
            id="username"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium" for="password">Password</label>
          <input
            type="password"
            name="password"
            id="password"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium" for="confirm_password">Konfirmasi Password</label>
          <input
            type="password"
            name="confirm_password" id="confirm_password"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        <div class="flex flex-row gap-2">
          <input
            type="text"
            id="captcha-value"
            disabled
            required
            class="flex-1 p-2 w-1/2 border border-gray-300 rounded bg-gray-200 cursor-not-allowed"
          />
          <input
            type="text"
            id="captcha-input"
            name="captcha_input" placeholder="Ketik Captcha"
            required
            class="flex-1 p-2 w-1/2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        <p id="captcha-message" class="text-sm text-center"></p>

        <button
          type="submit"
          name="register" class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition"
        >
          Register
        </button>
        <p class="text-sm text-center mt-2">
          Sudah mempunyai akun?
          <a href="/auth/login.php" class="text-yellow-500 hover:underline"
            >Login</a
          >
        </p>
      </form>
    </div>

    <script>
      function generateCaptcha() {
        const chars =
          "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let captcha = "";
        for (let i = 0; i < 6; i++) {
          captcha += chars[Math.floor(Math.random() * chars.length)];
        }
        document.getElementById("captcha-value").value = captcha;
        // Simpan captcha di session storage untuk validasi sisi klien (sebelum submit)
        sessionStorage.setItem('generatedCaptcha', captcha);
      }

      // Validasi captcha di sisi klien sebelum submit
      document
        .getElementById("register-form") // ID form diubah
        .addEventListener("submit", function (event) {
          const generatedCaptcha = sessionStorage.getItem('generatedCaptcha'); // Ambil dari session storage
          const userCaptcha = document.getElementById("captcha-input").value;
          const captchaMessageElement = document.getElementById("captcha-message");

          if (userCaptcha !== generatedCaptcha) {
            event.preventDefault(); // Mencegah form disubmit
            captchaMessageElement.textContent = "Captcha salah, coba lagi.";
            captchaMessageElement.style.color = "red";
            generateCaptcha(); // Regenerasi captcha baru
            document.getElementById("captcha-input").value = ""; // Kosongkan input captcha
          } else {
            // Jika captcha benar di sisi klien, hapus pesan dan biarkan form disubmit
            captchaMessageElement.textContent = "";
          }
        });

      window.onload = generateCaptcha;
    </script>
  </body>
</html>