<?php
require('../koneksi.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$tanggal_hari_ini = date('Y-m-d');

// Hitung jumlah pekerja yang hadir hari ini
$query = "
    SELECT 
        COUNT(*) AS hadir
    FROM absensi
    WHERE tanggal = '$tanggal_hari_ini' 
      AND kehadiran = 'Hadir'
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "data" => [
            "hadir" => (int)$data['hadir']
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No attendance data found for today"
    ]);
}

$conn->close();
?>
