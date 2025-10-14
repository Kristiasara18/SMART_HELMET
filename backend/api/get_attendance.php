<?php
require('../koneksi.php');

// Ambil tanggal hari ini dalam format YYYY-MM-DD
$tanggal_hari_ini = date('Y-m-d');

// Query untuk menghitung total pekerja dan jumlah yang hadir hari ini
$query = "
    SELECT 
        COUNT(DISTINCT id_pekerja) AS total_pekerja,
        SUM(CASE WHEN kehadiran = 'Hadir' THEN 1 ELSE 0 END) AS hadir
    FROM absensi
    WHERE tanggal = '$tanggal_hari_ini'
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(["status" => "success", "data" => $data]);
} else {
    echo json_encode(["status" => "error", "message" => "No data found for today"]);
}
?>
