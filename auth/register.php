<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Penjualan Cafe | Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
      <h2 class="text-2xl text-left mb-6">Register</h2>

      <form
        id="register-form"
        action="action/proses_register.php" method="POST"
        class="flex flex-col gap-4"
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
          <a href="login.php" class="text-yellow-500 hover:underline">Login</a>
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
        sessionStorage.setItem('generatedCaptcha', captcha); // Simpan di session storage
      }

      document
        .getElementById("register-form")
        .addEventListener("submit", function (event) {
          const generatedCaptcha = sessionStorage.getItem('generatedCaptcha');
          const userCaptcha = document.getElementById("captcha-input").value;
          const captchaMessageElement = document.getElementById("captcha-message");

          if (userCaptcha !== generatedCaptcha) {
            event.preventDefault(); // Mencegah form disubmit
            captchaMessageElement.textContent = "Captcha salah, coba lagi.";
            captchaMessageElement.style.color = "red";
            generateCaptcha(); // Regenerasi captcha baru
            document.getElementById("captcha-input").value = ""; // Kosongkan input captcha
          } else {
            captchaMessageElement.textContent = "";
          }
        });

      window.onload = generateCaptcha;

      // Logika SweetAlert2
      window.addEventListener('DOMContentLoaded', (event) => {
          const urlParams = new URLSearchParams(window.location.search);
          const status = urlParams.get('status');

          if (status) {
              let title = '';
              let text = '';
              let icon = '';

              switch (status) {
                  case 'success': 
                      title = 'Registrasi Berhasil!';
                      text = 'Akun Anda telah berhasil dibuat. Silakan login.';
                      icon = 'success';
                      break;
                  case 'password_mismatch':
                      title = 'Gagal Registrasi!';
                      text = 'Konfirmasi password tidak cocok.';
                      icon = 'error';
                      break;
                  case 'username_exists':
                      title = 'Gagal Registrasi!';
                      text = 'Username sudah terdaftar. Silakan gunakan username lain.';
                      icon = 'warning';
                      break;
                  case 'empty_fields':
                      title = 'Peringatan!';
                      text = 'Username dan password tidak boleh kosong.';
                      icon = 'warning';
                      break;
                  case 'captcha_wrong':
                      title = 'Gagal Registrasi!';
                      text = 'Captcha salah, coba lagi.';
                      icon = 'error';
                      break;
                  case 'failed':
                      title = 'Gagal Registrasi!';
                      text = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
                      icon = 'error';
                      break;
                  default:
                      return;
              }

              Swal.fire({
                  title: title,
                  text: text,
                  icon: icon,
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