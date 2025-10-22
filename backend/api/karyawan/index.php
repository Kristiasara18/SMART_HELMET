<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

include "../../koneksi.php";

// Ambil semua kolom dari tabel karyawan
$query = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY id_pekerja ASC");

$data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row; // $row sudah berupa associative array dengan semua kolom
}

// Kirim JSON lengkap
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
