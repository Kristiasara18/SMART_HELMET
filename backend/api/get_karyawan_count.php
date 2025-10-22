<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include '../koneksi.php';

$query = "SELECT COUNT(*) AS total_pekerja FROM karyawan";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    echo json_encode(["status" => "success", "data" => ["total_pekerja" => (int)$row['total_pekerja']]]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mengambil data karyawan"]);
}

$conn->close();
?>
