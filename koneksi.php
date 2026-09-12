<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_desa";

try {
    $koneksi = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $koneksi->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Jangan tampilkan pesan error asli ke pengunjung (bisa bocorin info server).
    // Dicatat ke error log PHP, user cuma lihat pesan umum.
    error_log("Koneksi database gagal: " . $e->getMessage());
    die("Maaf, sistem sedang gangguan. Silakan coba lagi nanti.");
}
?>
