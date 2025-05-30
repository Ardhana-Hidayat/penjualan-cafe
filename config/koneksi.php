<?php
    $host = 'localhost';    
    $username = 'root';          
    $password = '';             
    $database = 'sales'; 

    $link = mysqli_connect($host, $username, $password, $database);

    if (!$link) {
        die("Koneksi ke database gagal: " . mysqli_connect_error());
    } {
        echo "Koneksi berhasil";
    }
?>