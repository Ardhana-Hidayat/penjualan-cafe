<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Penjualan Cafe | Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
    <h2 class="text-2xl text-left mb-6">Login</h2>

    <form id="login-form" action="action/proses_login.php" method="POST" class="flex flex-col gap-4">
      <div class="flex flex-col">
        <label class="mb-1 text-sm font-medium" for="username">Username</label>
        <input type="text" name="username" id="username" required
          class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
      </div>
      <div class="flex flex-col">
        <label class="mb-1 text-sm font-medium" for="password">Password</label>
        <input type="password" name="password" id="password" required
          class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
      </div>
      <p class="text-sm text-right">
        <a href="reset_password.php" class="text-[#3B378B]">Lupa Password?</a>
      </p>
      <div class="flex flex-row gap-2">
        <input type="text" id="captcha-value" disabled required
          class="flex-1 p-2 w-1/2 border border-gray-300 rounded bg-gray-200 cursor-not-allowed" />
        <input type="text" id="captcha-input" name="captcha_input" placeholder="Ketik Captcha" required
          class="flex-1 p-2 w-1/2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
      </div>
      <p id="captcha-message" class="text-sm text-center"></p>

      <button type="submit" name="login" class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition">
        Login
      </button>
      <p class="text-sm text-center mt-2">
        Belum mempunyai akun?
        <a href="register.php" class="text-yellow-500 hover:underline">Register</a>
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
      sessionStorage.setItem('generatedCaptcha', captcha);
    }

    document
      .getElementById("login-form")
      .addEventListener("submit", function (event) {
        const generatedCaptcha = sessionStorage.getItem('generatedCaptcha');
        const userCaptcha = document.getElementById("captcha-input").value;
        const captchaMessageElement = document.getElementById("captcha-message");

        if (userCaptcha !== generatedCaptcha) {
          event.preventDefault();
          captchaMessageElement.textContent = "Captcha salah, coba lagi.";
          captchaMessageElement.style.color = "red";
          generateCaptcha();
          document.getElementById("captcha-input").value = "";
        } else {
          captchaMessageElement.textContent = "";
        }
      });

    window.onload = generateCaptcha;

    // Logika SweetAlert
    window.addEventListener('DOMContentLoaded', (event) => {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');

      if (status) {
        let title = '';
        let text = '';
        let icon = '';

        switch (status) {
          case 'failed':
            title = 'Login Gagal!';
            text = 'Username atau password salah.';
            icon = 'error';
            break;
          case 'empty_fields':
            title = 'Peringatan!';
            text = 'Username dan password tidak boleh kosong.';
            icon = 'warning';
            break;
          case 'captcha_wrong':
            title = 'Gagal!';
            text = 'Captcha salah, coba lagi.';
            icon = 'error';
            break;
          case 'not_logged_in':
            title = 'Akses Ditolak!';
            text = 'Anda harus login untuk mengakses halaman ini.';
            icon = 'warning';
            break;
          case 'registration_success':
            title = 'Registrasi Berhasil!';
            text = 'Akun Anda telah berhasil dibuat. Silakan login.';
            icon = 'success';
            break;
          default:

            return;
        }

        Swal.fire({
          title: title,
          text: text,
          icon: icon,
          confirmButtonText: 'OK',
          width: '300px'
        }).then(() => {

          const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
          window.history.replaceState({ path: newUrl }, '', newUrl);
        });

        generateCaptcha();
      }
    });
  </script>
</body>

</html>