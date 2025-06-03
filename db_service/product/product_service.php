<?php

include '../../config/koneksi.php';

function redirect_with_status($status, $message = '')
{
    $url = '../../pages/produk.php?status=' . urlencode($status);
    if ($message) {
        $url .= '&message=' . urlencode($message);
    }
    header("Location: " . $url);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Logika Tambah Kategori ---
    if (isset($_POST['add_product'])) {
        $name = trim($_POST['name'] ?? '');
        $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
        $idCategory = filter_var($_POST['idCategory'] ?? '', FILTER_VALIDATE_INT);

        if (empty($name) || $price === false || $stock === false || $idCategory === false) {
            redirect_with_status('empty_fields', 'Semua field (Nama, Harga, Stok, Kategori) harus diisi dengan benar.');
        }

        // Cek duplikasi nama kategori
        $check_query = "SELECT COUNT(*) FROM products WHERE name = ?";
        $stmt_check = mysqli_prepare($link, $check_query);
        mysqli_stmt_bind_param($stmt_check, "s", $name);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $count);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($count > 0) {
            redirect_with_status('create_failed', 'Nama produk sudah ada.');
        }

        $query = "INSERT INTO products (name, price, stock, idCategory) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $query);

        if ($stmt === false) {
            redirect_with_status('create_failed', 'Terjadi kesalahan server saat menyiapkan query.');
        }

        mysqli_stmt_bind_param($stmt, "sdii", $name, $price, $stock, $idCategory);
        $result = $stmt->execute();

        if ($result) {
            redirect_with_status('created');
        } else {
            redirect_with_status('create_failed', mysqli_error($link));
        }
        mysqli_stmt_close($stmt);
    }

    // --- Logika Update Kategori ---
    elseif (isset($_POST['update_product'])) {
        $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        $price = filter_var($_POST['price'] ?? '', FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['stock'] ?? '', FILTER_VALIDATE_INT);
        $idCategory = filter_var($_POST['idCategory'] ?? '', FILTER_VALIDATE_INT);

        if ($id === false || empty($name) || $price === false || $stock === false || $idCategory === false) {
            redirect_with_status('empty_fields', 'ID atau semua field produk harus diisi dengan benar.');
        }

        // Cek duplikasi nama (kecuali untuk produk itu sendiri)
        $check_query = "SELECT COUNT(*) FROM products WHERE name = ? AND id != ?";
        $stmt_check = mysqli_prepare($link, $check_query);
        mysqli_stmt_bind_param($stmt_check, "si", $name, $id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $count);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($count > 0) {
            redirect_with_status('update_failed', 'Nama produk sudah ada.');
        }

        $query = "UPDATE products SET name = ?, idCategory = ?, price = ?, stock = ? WHERE id = ?";
        $stmt = mysqli_prepare($link, $query);

        if ($stmt === false) {
            redirect_with_status('update_failed', 'Terjadi kesalahan server saat menyiapkan query.');
        }

        mysqli_stmt_bind_param($stmt, "sidii", $name, $idCategory, $price, $stock, $id);
        $result = $stmt->execute();

        if ($result) {
            redirect_with_status('updated');
        } else {
            redirect_with_status('update_failed', mysqli_error($link));
        }
        mysqli_stmt_close($stmt);
    }

    // --- Logika Hapus Kategori ---
    elseif (isset($_POST['delete_product'])) {
        $id = $_POST['id'] ?? null;

        if (empty($id)) {
            redirect_with_status('invalid_id');
        }

        $query = "DELETE FROM products WHERE id = ?";
        $stmt = mysqli_prepare($link, $query);

        if ($stmt === false) {
            redirect_with_status('delete_failed', 'Terjadi kesalahan server saat menyiapkan query.');
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = $stmt->execute();

        if ($result) {
            redirect_with_status('deleted');
        } else {

            if (mysqli_errno($link) == 1451) {
                redirect_with_status('delete_failed', 'Produk tidak dapat dihapus karena masih digunakan pada transaksi atau data lain.');
            } else {
                redirect_with_status('delete_failed', mysqli_error($link));
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        redirect_with_status('invalid_action');
    }

} else {
    redirect_with_status('invalid_access');
}

mysqli_close($link);
exit();
?>