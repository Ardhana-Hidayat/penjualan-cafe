<?php

include '../../config/koneksi.php'; 

function redirect_with_status($status, $message = '') {
    $url = '../../pages/kategori.php?status=' . urlencode($status);
    if ($message) {
        $url .= '&message=' . urlencode($message);
    }
    header("Location: " . $url);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Logika Tambah Kategori ---
    if (isset($_POST['add_kategori'])) {
        $name = trim($_POST['name'] ?? ''); 

        if (empty($name)) {
            redirect_with_status('empty_fields');
        }

        // Cek duplikasi nama kategori
        $check_query = "SELECT COUNT(*) FROM category WHERE name = ?";
        $stmt_check = mysqli_prepare($link, $check_query);
        mysqli_stmt_bind_param($stmt_check, "s", $name);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $count);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($count > 0) {
            redirect_with_status('create_failed', 'Nama kategori sudah ada.');
        }

        $query = "INSERT INTO category (name) VALUES (?)"; 
        $stmt = mysqli_prepare($link, $query);

        if ($stmt === false) {
            redirect_with_status('create_failed', 'Terjadi kesalahan server saat menyiapkan query.');
        }

        mysqli_stmt_bind_param($stmt, "s", $name); 
        $result = $stmt->execute();

        if ($result) {
            redirect_with_status('created');
        } else {
            redirect_with_status('create_failed', mysqli_error($link));
        }
        mysqli_stmt_close($stmt);
    }

    // --- Logika Update Kategori ---
    elseif (isset($_POST['update_kategori'])) {
        $id = $_POST['id'] ?? null; 
        $name = trim($_POST['name'] ?? ''); 

        if (empty($id) || empty($name)) {
            redirect_with_status('empty_fields');
        }
        
        // Cek duplikasi nama (kecuali untuk kategori itu sendiri)
        $check_query = "SELECT COUNT(*) FROM category WHERE name = ? AND id != ?";
        $stmt_check = mysqli_prepare($link, $check_query);
        mysqli_stmt_bind_param($stmt_check, "si", $name, $id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $count);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($count > 0) {
            redirect_with_status('update_failed', 'Nama kategori sudah ada.');
        }

        $query = "UPDATE category SET name = ? WHERE id = ?"; 
        $stmt = mysqli_prepare($link, $query);

        if ($stmt === false) {
            redirect_with_status('update_failed', 'Terjadi kesalahan server saat menyiapkan query.');
        }

        mysqli_stmt_bind_param($stmt, "si", $name, $id); 
        $result = $stmt->execute();

        if ($result) {
            redirect_with_status('updated');
        } else {
            redirect_with_status('update_failed', mysqli_error($link));
        }
        mysqli_stmt_close($stmt);
    }

    // --- Logika Hapus Kategori ---
    elseif (isset($_POST['delete_kategori'])) {
        $id = $_POST['id'] ?? null; 

        if (empty($id)) {
            redirect_with_status('invalid_id');
        }

        $query = "DELETE FROM category WHERE id = ?"; 
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
                redirect_with_status('delete_failed', 'Kategori tidak dapat dihapus karena masih digunakan pada produk.');
            } else {
                redirect_with_status('delete_failed', mysqli_error($link));
            }
        }
        mysqli_stmt_close($stmt);
    }

    else {
        redirect_with_status('invalid_action');
    }

} else {
    redirect_with_status('invalid_access');
}

mysqli_close($link);
exit(); 
?>