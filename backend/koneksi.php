<?php
$host = "localhost";
$user = "root";        // sesuaikan
$pass = "";            // isi jika MySQL kamu pakai password
$db   = "db_karyawan";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Koneksi gagal: " . $conn->connect_error]));
}
?>
