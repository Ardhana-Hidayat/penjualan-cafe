<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Penjualan Cafe | Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css" />
  </head>
  <body class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
      <h2 class="text-2xl text-left mb-6">Ganti Password</h2>
      <form
        id="login-form"
        action="/auth/login.php"
        class="flex flex-col gap-4"
      >
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium">Password Baru</label>
          <input
            type="password"
            name="password"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>
        <div class="flex flex-col">
          <label class="mb-1 text-sm font-medium">Konfirmasi Password</label>
          <input
            type="password"
            name="password"
            required
            class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]"
          />
        </div>

        <button
          type="submit"
          class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition"
        >
          Simpan
        </button>
      </form>
    </div>
  </body>
</html>
