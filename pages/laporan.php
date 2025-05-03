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
                <h3 class="text-xl">Halaman Laporan</h3>
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
                <div class="bg-white p-6 rounded-md shadow space-y-4 w-full">
                    <div class="flex justify-between">
                        <div class="flex space-x-4 items-center">
                            <h3 class="text-left">Data Laporan</h3>
                            <div class="flex flex-col">
                                <select name="bulan" id="bulan" required
                                    class="p-2 w-20 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B] text-sm">
                                    <option value="">Bulan</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            <div class="flex flex-col">
                                <select name="tahun" id="tahun" required
                                    class="p-2 w-20 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-[#3B378B] text-sm">
                                    <option value="">Tahun</option>
                                    <option value="1">2021</option>
                                    <option value="2">2022</option>
                                    <option value="3">2023</option>
                                    <option value="4">2024</option>
                                    <option value="5">2025</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex space-x-4 items-center">
                            <button class="p-2 bg-[#3B378B] rounded-md hover:bg-[#1E1B57]">
                                <svg width="16" height="16" viewBox="0 0 19 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M10.7 10L13.5 14H11.1L9.5 11.714L7.9 14H5.5L8.3 10L5.5 6H7.9L9.5 8.286L11.1 6H12.5V2H2.5V18H16.5V6H13.5L10.7 10ZM0.5 0.992C0.5 0.444 0.947 0 1.499 0H13.5L18.5 5V18.993C18.5009 19.1243 18.476 19.2545 18.4266 19.3762C18.3772 19.4979 18.3043 19.6087 18.2121 19.7022C18.1199 19.7957 18.0101 19.8701 17.8892 19.9212C17.7682 19.9723 17.6383 19.9991 17.507 20H1.493C1.23038 19.9982 0.979017 19.8931 0.793218 19.7075C0.607418 19.5219 0.502095 19.2706 0.5 19.008V0.992Z"
                                        fill="white" />
                                </svg>
                            </button>
                            <button class="p-2 bg-[#3B378B] rounded-md hover:bg-[#1E1B57]">
                                <svg width="16" height="16" viewBox="0 0 19 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.83325 8.66667V8H2.16659V8.66667H2.83325ZM8.16658 8.66667V8H7.49992V8.66667H8.16658ZM8.16658 14H7.49992V14.6667H8.16658V14ZM17.4999 4.66667H18.1666V4.39067L17.9719 4.19467L17.4999 4.66667ZM13.4999 0.666667L13.9719 0.194667L13.7759 0H13.4999V0.666667ZM2.83325 9.33333H4.16658V8H2.83325V9.33333ZM3.49992 14.6667V11.3333H2.16659V14.6667H3.49992ZM3.49992 11.3333V8.66667H2.16659V11.3333H3.49992ZM4.16658 10.6667H2.83325V12H4.16658V10.6667ZM4.83325 10C4.83325 10.1768 4.76301 10.3464 4.63799 10.4714C4.51297 10.5964 4.3434 10.6667 4.16658 10.6667V12C4.69702 12 5.20573 11.7893 5.5808 11.4142C5.95587 11.0391 6.16658 10.5304 6.16658 10H4.83325ZM4.16658 9.33333C4.3434 9.33333 4.51297 9.40357 4.63799 9.5286C4.76301 9.65362 4.83325 9.82319 4.83325 10H6.16658C6.16658 9.46957 5.95587 8.96086 5.5808 8.58579C5.20573 8.21071 4.69702 8 4.16658 8V9.33333ZM7.49992 8.66667V14H8.83325V8.66667H7.49992ZM8.16658 14.6667H9.49992V13.3333H8.16658V14.6667ZM11.4999 12.6667V10H10.1666V12.6667H11.4999ZM9.49992 8H8.16658V9.33333H9.49992V8ZM11.4999 10C11.4999 9.46957 11.2892 8.96086 10.9141 8.58579C10.5391 8.21071 10.0304 8 9.49992 8V9.33333C9.67673 9.33333 9.8463 9.40357 9.97132 9.5286C10.0963 9.65362 10.1666 9.82319 10.1666 10H11.4999ZM9.49992 14.6667C10.0304 14.6667 10.5391 14.456 10.9141 14.0809C11.2892 13.7058 11.4999 13.1971 11.4999 12.6667H10.1666C10.1666 12.8435 10.0963 13.013 9.97132 13.1381C9.8463 13.2631 9.67673 13.3333 9.49992 13.3333V14.6667ZM12.8333 8V14.6667H14.1666V8H12.8333ZM13.4999 9.33333H16.8333V8H13.4999V9.33333ZM13.4999 12H15.4999V10.6667H13.4999V12ZM2.16659 6.66667V2H0.833252V6.66667H2.16659ZM16.8333 4.66667V6.66667H18.1666V4.66667H16.8333ZM2.83325 1.33333H13.4999V0H2.83325V1.33333ZM13.0279 1.13867L17.0279 5.13867L17.9719 4.19467L13.9719 0.194667L13.0279 1.13867ZM2.16659 2C2.16659 1.82319 2.23682 1.65362 2.36185 1.5286C2.48687 1.40357 2.65644 1.33333 2.83325 1.33333V0C2.30282 0 1.79411 0.210714 1.41904 0.585786C1.04397 0.960859 0.833252 1.46957 0.833252 2H2.16659ZM0.833252 16V18H2.16659V16H0.833252ZM2.83325 20H16.1666V18.6667H2.83325V20ZM18.1666 18V16H16.8333V18H18.1666ZM16.1666 20C16.697 20 17.2057 19.7893 17.5808 19.4142C17.9559 19.0391 18.1666 18.5304 18.1666 18H16.8333C16.8333 18.1768 16.763 18.3464 16.638 18.4714C16.513 18.5964 16.3434 18.6667 16.1666 18.6667V20ZM0.833252 18C0.833252 18.5304 1.04397 19.0391 1.41904 19.4142C1.79411 19.7893 2.30282 20 2.83325 20V18.6667C2.65644 18.6667 2.48687 18.5964 2.36185 18.4714C2.23682 18.3464 2.16659 18.1768 2.16659 18H0.833252Z"
                                        fill="white" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <hr />
                    <table class="w-full text-sm border border-gray-400 border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="font-medium border border-gray-400 px-4 py-3">No</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Kode Transaksi</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Pelanggan</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Produk</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Jumlah</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Harga Satuan</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Total Transaksi</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Bayar</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Kembali</th>
                                <th class="font-medium border border-gray-400 px-4 py-3">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center">
                                <td class="border border-gray-400 px-4 py-3">1</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">KAND8918012</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">Kurniawan</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">Kopi</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">2</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 10.000</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 20.000</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 20.000</td>
                                <td class="border border-gray-400 px-4 py-3 text-left">Rp. 0</td>
                                <td class="border border-gray-400 px-4 py-3">2025-3-18, 08:45 PM</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>

</html>