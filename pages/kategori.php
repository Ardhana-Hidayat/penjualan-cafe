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

    <div class="flex flex-col flex-1 overflow-auto h-screen">
      <!-- Topbar -->
      <div class="bg-white p-6 flex justify-between items-center shadow">
        <h3 class="text-xl">Halaman Kategori</h3>
        <div class="flex gap-2 p-2 rounded-md border border-[#1E1B57]">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M19.938 8H21C21.5304 8 22.0391 8.21071 22.4142 8.58579C22.7893 8.96086 23 9.46957 23 10V14C23 14.5304 22.7893 15.0391 22.4142 15.4142C22.0391 15.7893 21.5304 16 21 16H19.938C19.6944 17.9334 18.7535 19.7114 17.292 21.0002C15.8304 22.2891 13.9487 23.0002 12 23V21C13.5913 21 15.1174 20.3679 16.2426 19.2426C17.3679 18.1174 18 16.5913 18 15V9C18 7.4087 17.3679 5.88258 16.2426 4.75736C15.1174 3.63214 13.5913 3 12 3C10.4087 3 8.88258 3.63214 7.75736 4.75736C6.63214 5.88258 6 7.4087 6 9V16H3C2.46957 16 1.96086 15.7893 1.58579 15.4142C1.21071 15.0391 1 14.5304 1 14V10C1 9.46957 1.21071 8.96086 1.58579 8.58579C1.96086 8.21071 2.46957 8 3 8H4.062C4.30603 6.06689 5.24708 4.28927 6.70857 3.00068C8.17007 1.71208 10.0516 1.00108 12 1.00108C13.9484 1.00108 15.8299 1.71208 17.2914 3.00068C18.7529 4.28927 19.694 6.06689 19.938 8ZM3 10V14H4V10H3ZM20 10V14H21V10H20ZM7.76 15.785L8.82 14.089C9.77303 14.6861 10.8754 15.0019 12 15C13.1246 15.0019 14.227 14.6861 15.18 14.089L16.24 15.785C14.9693 16.5813 13.4996 17.0025 12 17C10.5004 17.0025 9.03067 16.5813 7.76 15.785Z"
              fill="#1E1B57" />
          </svg>

          <span>Administrator</span>
        </div>
      </div>

      <div class="p-6 flex items-start space-x-6">
        <!-- Cards and Charts -->
        <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/3">
          <h3 class="text-left">Data Kategori</h3>
          <hr />
          <table class="w-full text-sm border border-gray-400 border-collapse">
            <thead class="bg-gray-100">
              <tr>
                <th class="font-medium border border-gray-400 px-4 py-3">
                  No
                </th>
                <th class="font-medium border border-gray-400 px-4 py-3">
                  Nama
                </th>
                <th class="font-medium border border-gray-400 px-4 py-3">
                  Action
                </th>
              </tr>
            </thead>
            <tbody>
              <tr class="text-center">
                <td class="border border-gray-400 px-4 py-3">
                  1
                </td>
                <td class="border border-gray-400 px-4 py-3">
                  Makanan
                </td>
                <td class="border border-gray-400 px-4 py-3">
                  <div class="flex justify-center gap-3">
                    <button class="bg-[#5148FF] hover:bg-blue-500 rounded-md p-2" onclick="openModalEdit()">
                      <svg width="13" height="13" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                          d="M13.8368 3.14373C13.5808 3.40814 13.3323 3.66477 13.3247 3.9214C13.3022 4.17025 13.5582 4.42689 13.7992 4.66796C14.1607 5.0568 14.5146 5.40675 14.4995 5.78781C14.4845 6.16886 14.1004 6.56548 13.7163 6.95431L10.6062 10.1739L9.53682 9.06957L12.7374 5.77225L12.0144 5.02569L10.9451 6.1222L8.12105 3.20594L11.0128 0.227468C11.3065 -0.0758228 11.796 -0.0758228 12.0747 0.227468L13.8368 2.04722C14.1305 2.33495 14.1305 2.84044 13.8368 3.14373ZM0.5 11.0837L7.69933 3.64144L10.5233 6.5577L3.32401 14H0.5V11.0837Z"
                          fill="white" />
                      </svg>
                    </button>
                    <button class="bg-[#FF4848] hover:bg-red-500 rounded-md p-2" onclick="openModalHapus()">
                      <svg width="13" height="13" viewBox="0 0 13 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                          d="M1.51743 5.05263H11.4817L10.886 14.4211C10.8587 14.8487 10.6666 15.25 10.3488 15.5433C10.0309 15.8367 9.61124 16 9.17514 16H3.82486C3.38876 16 2.96907 15.8367 2.65122 15.5433C2.33336 15.25 2.14126 14.8487 2.114 14.4211L1.51743 5.05263ZM12.5 2.52632V4.21053H0.5V2.52632H3.07143V1.68421C3.07143 1.23753 3.25204 0.809144 3.57353 0.493294C3.89502 0.177443 4.33106 0 4.78571 0H8.21429C8.66894 0 9.10498 0.177443 9.42647 0.493294C9.74796 0.809144 9.92857 1.23753 9.92857 1.68421V2.52632H12.5ZM4.78571 2.52632H8.21429V1.68421H4.78571V2.52632Z"
                          fill="white" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/4">
          <h4>Form Tambah Kategori</h4>
          <hr />
          <form class="space-y-4">
            <div class="flex flex-col">
              <label class="mb-2 text-sm">Nama Kategori</label>
              <input type="text" name="kategori" required
                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
            </div>
            <button type="submit" class="p-2 bg-[#3B378B] w-full text-white rounded hover:bg-[#524CC3] transition">
              Simpan
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal edit -->
  <div id="modal-edit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg w-96 p-6 relative">
      <!-- Close button -->
      <button onclick="closeModalEdit()" class="absolute text-2xl top-5 right-5 text-gray-400 hover:text-gray-600">
        &times;
      </button>

      <h2 class="text-md mb-6">Form Edit Kategori</h2>

      <form class="space-y-4">
        <div>
          <label class="block text-sm text-gray-700 mb-2" for="kategori">Nama Kategori</label>
          <input type="text" id="kategori" name="kategori"
            class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#3B378B]" value="Makanan" />
        </div>

        <button type="submit" class="w-full bg-[#3B378B] hover:bg-[#524CC3] text-white py-2 rounded">
          Simpan
        </button>
      </form>
    </div>
  </div>

  <!-- Modal hapus -->
  <div id="modal-delete" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg w-96 p-6 relative">
      <!-- Close button -->
      <button onclick="closeModalHapus()" class="absolute text-2xl top-5 right-5 text-gray-400 hover:text-gray-600">
        &times;
      </button>

      <h2 class="text-md mb-6">Konfirmasi Hapus</h2>

      <form>
        <label class="block text-sm text-gray-700 mb-2" for="kategori">Anda yakin untuk menghapus " "?</label>
        <br />
        <button type="submit" class="w-full bg-[#FF4848] hover:bg-[#FF6464] text-white py-2 rounded">
          Hapus
        </button>
      </form>
    </div>
  </div>

  <script>
    function openModalEdit() {
      document.getElementById("modal-edit").classList.remove("hidden");
    }

    function closeModalEdit() {
      document.getElementById("modal-edit").classList.add("hidden");
    }
    function openModalHapus() {
      document.getElementById("modal-delete").classList.remove("hidden");
    }

    function closeModalHapus() {
      document.getElementById("modal-delete").classList.add("hidden");
    }
  </script>
</body>

</html>