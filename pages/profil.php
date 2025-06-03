<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Penjualan Cafe | Profil</title>
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
                <h3 class="text-xl py-2">Halaman Profil</h3>
            </div>

            <div class="p-6 flex items-start space-x-6">
                <!-- Cards and Charts -->
                <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/4">
                    <div class="w-full flex justify-center">
                        <svg width="72" height="72" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12.5" cy="12.5" r="8.5" fill="#D9D9D9" fill-opacity="0.3" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M16.3182 9.63642C16.3182 10.6491 15.916 11.6202 15.1999 12.3363C14.4839 13.0523 13.5127 13.4546 12.5001 13.4546C11.4874 13.4546 10.5163 13.0523 9.8002 12.3363C9.08416 11.6202 8.68188 10.6491 8.68188 9.63642C8.68188 8.62377 9.08416 7.6526 9.8002 6.93656C10.5163 6.22051 11.4874 5.81824 12.5001 5.81824C13.5127 5.81824 14.4839 6.22051 15.1999 6.93656C15.916 7.6526 16.3182 8.62377 16.3182 9.63642ZM14.4092 9.63642C14.4092 10.1427 14.208 10.6283 13.85 10.9863C13.492 11.3444 13.0064 11.5455 12.5001 11.5455C11.9937 11.5455 11.5082 11.3444 11.1501 10.9863C10.7921 10.6283 10.591 10.1427 10.591 9.63642C10.591 9.1301 10.7921 8.64451 11.1501 8.28649C11.5082 7.92846 11.9937 7.72733 12.5001 7.72733C13.0064 7.72733 13.492 7.92846 13.85 8.28649C14.208 8.64451 14.4092 9.1301 14.4092 9.63642Z"
                                fill="#5B5B5B" fill-opacity="0.3" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M12.5 2C6.70114 2 2 6.70114 2 12.5C2 18.2989 6.70114 23 12.5 23C18.2989 23 23 18.2989 23 12.5C23 6.70114 18.2989 2 12.5 2ZM3.90909 12.5C3.90909 14.495 4.58968 16.3315 5.73036 17.7901C6.53165 16.7383 7.56515 15.8859 8.7502 15.2994C9.93525 14.7129 11.2398 14.4082 12.562 14.4091C13.8673 14.4076 15.1556 14.7041 16.3288 15.2762C17.502 15.8482 18.5291 16.6806 19.3317 17.7099C20.1587 16.6252 20.7156 15.3591 20.9562 14.0164C21.1968 12.6737 21.1142 11.293 20.7153 9.98858C20.3164 8.68414 19.6125 7.49345 18.6621 6.51504C17.7116 5.53662 16.5418 4.79859 15.2495 4.36203C13.9571 3.92547 12.5794 3.80292 11.2303 4.00452C9.88123 4.20612 8.59953 4.72608 7.49128 5.52137C6.38303 6.31666 5.48009 7.36442 4.85717 8.57796C4.23425 9.7915 3.90926 11.1359 3.90909 12.5ZM12.5 21.0909C10.5278 21.0941 8.61514 20.4156 7.08582 19.1704C7.70132 18.2889 8.52068 17.5693 9.47416 17.0727C10.4276 16.5761 11.487 16.3173 12.562 16.3182C13.6237 16.3173 14.6702 16.5697 15.6147 17.0544C16.5591 17.5392 17.3743 18.2424 17.9925 19.1055C16.4513 20.3912 14.5071 21.094 12.5 21.0909Z"
                                fill="#5B5B5B" fill-opacity="0.3" />
                        </svg>
                    </div>
                    <hr>
                    <div>
                        <p class="font-semibold">Username</p>
                        <p class="text-sm">Ardhana</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-md shadow space-y-4 w-1/4">
                    <h4>Ganti Password</h4>
                    <hr />
                    <form id="login-form" class="flex flex-col gap-4">
                        <div class="flex flex-col">
                            <label class="mb-1 text-sm">Password Baru</label>
                            <input type="password" name="password" required
                                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 text-sm">Konfirmasi Password</label>
                            <input type="password" name="password" required
                                class="p-2 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B]" />
                        </div>

                        <button type="submit" class="p-2 bg-[#3B378B] text-white rounded hover:bg-[#524CC3] transition">
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
            <button onclick="closeModalEdit()"
                class="absolute text-2xl top-5 right-5 text-gray-400 hover:text-gray-600">
                &times;
            </button>

            <h2 class="text-md mb-6">Form Edit Kategori</h2>

            <form class="space-y-6">
                <div>
                    <label class="block text-sm text-gray-700 mb-2" for="kategori">Nama Kategori</label>
                    <input type="text" id="kategori" name="kategori"
                        class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-[#3B378B]"
                        value="Makanan" />
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
            <button onclick="closeModalHapus()"
                class="absolute text-2xl top-5 right-5 text-gray-400 hover:text-gray-600">
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