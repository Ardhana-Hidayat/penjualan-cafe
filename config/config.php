<?php
    $host = 'localhost';    
    $username = 'root';          
    $password = '';             
    $database = 'sales'; 

    $con = mysqli_connect($host, $username, $password, $database);

    if (!$con) {
        die("Koneksi ke database gagal: " . mysqli_connect_error());
    } {
        echo "Koneksi berhasil";
    }
?>