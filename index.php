<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penjualan Cafe</title>
  <!-- Import TailwindCSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    window.onload = function() {
      setTimeout(function() {
        window.location.href = "/auth/login.php";
      }, 2000); 
    }
  </script>
</head>
<body class="h-screen w-screen overflow-hidden">

  <!-- Loader -->
  <div class="fixed inset-0 flex flex-col items-center justify-center bg-white z-50">
    <!-- Spinner -->
    <div class="w-12 h-12 border-4 border-teal-400 border-t-transparent rounded-full animate-spin mb-4"></div>
    <!-- Text -->
    <p class="text-lg font-semibold text-[#1E1B57]">Memuat halaman...</p>
  </div>

</body>
</html>
