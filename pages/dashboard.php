<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/styles.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>

  <body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <?php include '../templates/sidebar.php'; ?>
      
      <div class="flex flex-col flex-1">
        <div class="bg-white p-6 flex justify-between items-center shadow">
          <h3 class="text-xl">Dashboard</h3>
          <div class="flex gap-2 p-2 rounded-md border border-[#1E1B57]">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M19.938 8H21C21.5304 8 22.0391 8.21071 22.4142 8.58579C22.7893 8.96086 23 9.46957 23 10V14C23 14.5304 22.7893 15.0391 22.4142 15.4142C22.0391 15.7893 21.5304 16 21 16H19.938C19.6944 17.9334 18.7535 19.7114 17.292 21.0002C15.8304 22.2891 13.9487 23.0002 12 23V21C13.5913 21 15.1174 20.3679 16.2426 19.2426C17.3679 18.1174 18 16.5913 18 15V9C18 7.4087 17.3679 5.88258 16.2426 4.75736C15.1174 3.63214 13.5913 3 12 3C10.4087 3 8.88258 3.63214 7.75736 4.75736C6.63214 5.88258 6 7.4087 6 9V16H3C2.46957 16 1.96086 15.7893 1.58579 15.4142C1.21071 15.0391 1 14.5304 1 14V10C1 9.46957 1.21071 8.96086 1.58579 8.58579C1.96086 8.21071 2.46957 8 3 8H4.062C4.30603 6.06689 5.24708 4.28927 6.70857 3.00068C8.17007 1.71208 10.0516 1.00108 12 1.00108C13.9484 1.00108 15.8299 1.71208 17.2914 3.00068C18.7529 4.28927 19.694 6.06689 19.938 8ZM3 10V14H4V10H3ZM20 10V14H21V10H20ZM7.76 15.785L8.82 14.089C9.77303 14.6861 10.8754 15.0019 12 15C13.1246 15.0019 14.227 14.6861 15.18 14.089L16.24 15.785C14.9693 16.5813 13.4996 17.0025 12 17C10.5004 17.0025 9.03067 16.5813 7.76 15.785Z"
                fill="#1E1B57"
              />
            </svg>

            <a href="/pages/profil.php" class="cursor-pointer">
              <span>Administrator</span>
            </a>
          </div>
        </div>

        <div class="p-6 space-y-6">
          <!-- Cards and Charts -->
          <div class="grid grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="flex bg-white rounded-xl shadow overflow-hidden">
              <div class="bg-yellow-400 w-20 flex justify-center items-center">
                <!-- SVG ICON -->
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 39 39"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  class="text-white"
                >
                  <rect
                    x="4.875"
                    y="4.875"
                    width="29.25"
                    height="29.25"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M16.575 25.8L22.075 30L30.875 19.5M8.875 9H30.875M8.875 17.4H17.675"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </div>
              <div class="p-4 flex flex-col justify-center text-center">
                <div class="text-gray-600 text-sm">Total Transaksi</div>
                <div class="text-xl font-bold text-gray-800">00</div>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="flex bg-white rounded-xl shadow overflow-hidden">
              <div class="bg-green-400 w-20 flex justify-center items-center">
                <!-- SVG ICON -->
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 39 39"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  class="text-white"
                >
                  <path
                    d="M35.75 4.875H3.25V14.625H4.875V32.5C4.875 33.362 5.21741 34.1886 5.8269 34.7981C6.4364 35.4076 7.26305 35.75 8.125 35.75H30.875C31.737 35.75 32.5636 35.4076 33.1731 34.7981C33.7826 34.1886 34.125 33.362 34.125 32.5V14.625H35.75V4.875ZM6.5 8.125H32.5V11.375H6.5V8.125ZM30.875 32.5H8.125V14.625H30.875V32.5ZM14.625 17.875H24.375C24.375 18.737 24.0326 19.5636 23.4231 20.1731C22.8136 20.7826 21.987 21.125 21.125 21.125H17.875C17.013 21.125 16.1864 20.7826 15.5769 20.1731C14.9674 19.5636 14.625 18.737 14.625 17.875Z"
                    fill="white"
                  />
                </svg>
              </div>
              <div class="p-4 ml-2 flex flex-col justify-center text-center">
                <div class="text-gray-600 text-sm">Total Produk</div>
                <div class="text-xl font-bold text-gray-800">00</div>
              </div>
            </div>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-2 gap-6">
            <div class="flex-1 space-y-6 bg-white p-4 rounded-xl shadow">
              <h4 class="text-md font-semibold">Bar Chart Penjualan</h4>
              <canvas id="barChart" class="w-1/2"></canvas>
            </div>
            <div class="w-64 space-y-6 bg-white p-4 rounded-xl shadow">
              <h4 class="text-md font-semibold">Pie Chart Penjualan</h4>
              <canvas id="pieChart" class="w-1/2"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>
      const barCtx = document.getElementById("barChart").getContext("2d");
      new Chart(barCtx, {
        type: "bar",
        data: {
          labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "Mei",
            "Jun",
            "Jul",
            "Agu",
            "Sep",
            "Okt",
            "Nov",
            "Des",
          ],
          datasets: [
            {
              label: "Penjualan",
              data: [120, 150, 180, 90, 200, 250, 300, 270, 230, 190, 220, 260],
              backgroundColor: "#60a5fa", // biru muda
              borderRadius: 8,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 50,
              },
            },
          },
        },
      });

      const pieCtx = document.getElementById("pieChart").getContext("2d");
      new Chart(pieCtx, {
        type: "pie",
        data: {
          labels: ["Minuman", "Makanan", "Snack"],
          datasets: [
            {
              label: "Kategori Produk",
              data: [40, 35, 25],
              backgroundColor: ["#34d399", "#fbbf24", "#60a5fa"],
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: "bottom",
            },
          },
        },
      });
    </script>
  </body>
</html>
