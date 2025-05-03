<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web Penjualan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css">
  </head>
  <body class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
      <h2 class="text-2xl text-left mb-6">Login</h2>
      <form
        id="login-form"
        action="/dashboard.html"
        class="flex flex-col gap-4"
      >
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium">Username</label>
          <input
            type="text"
            name="username"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium">Password</label>
          <input
            type="password"
            name="password"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
          <p class="text-sm text-right">
            <a href="reset-password.html" class="text-[#524CC3] text-xs"
              >Lupa Password?</a
            >
          </p>
        </div>
        <div class="flex flex-row gap-2">
          <input
            type="text"
            id="captcha-value"
            disabled
            required
            class="flex-1 p-2 w-1/2 border border-gray-300 rounded"
          />
          <input
            type="text"
            id="captcha-input"
            placeholder="Ketik Captcha"
            required
            class="flex-1 p-2 w-1/2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        
        <button
          type="submit"
          class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition"
        >
          Login
        </button>
        
        <p class="text-sm text-center mt-2">
          Belum mempunyai akun?
          <a href="register.html" class="text-yellow-500 hover:underline"
            >Register</a
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
      }

      function validateCaptcha(event) {
        const generatedCaptcha = document.getElementById("captcha-value").value;
        const userCaptcha = document.getElementById("captcha-input").value;

        if (userCaptcha === generatedCaptcha) {
          document.getElementById("message").textContent = "Captcha benar!";
          document.getElementById("message").style.color = "green";
        } else {
          event.preventDefault();
          document.getElementById("message").textContent =
            "Captcha salah, coba lagi.";
          document.getElementById("message").style.color = "red";
          generateCaptcha();
          document.getElementById("captcha-input").value = "";
        }
      }

      document
        .getElementById("login-form")
        .addEventListener("submit", function (event) {
          validateCaptcha(event);
        });

      window.onload = generateCaptcha;
    </script>
  </body>
</html>
