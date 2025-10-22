<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_karyawan";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]));
}

// Ambil total kejadian per tanggal
$sqlHarian = "
    SELECT tanggal, SUM(jumlah_kejadian) AS total
    FROM kejadian_helmet
    GROUP BY tanggal
    ORDER BY tanggal ASC
";

$resultHarian = $conn->query($sqlHarian);
$dataHarian = [];
while ($row = $resultHarian->fetch_assoc()) {
    $dataHarian[] = [
        "tanggal" => $row["tanggal"],
        "total" => (int)$row["total"],
    ];
}

// Ambil total kejadian per bulan
$sqlBulanan = "
    SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, SUM(jumlah_kejadian) AS total
    FROM kejadian_helmet
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY bulan ASC
";

$resultBulanan = $conn->query($sqlBulanan);
$dataBulanan = [];
while ($row = $resultBulanan->fetch_assoc()) {
    $dataBulanan[] = [
        "bulan" => $row["bulan"],
        "total" => (int)$row["total"],
    ];
}

echo json_encode([
    "harian" => $dataHarian,
    "bulanan" => $dataBulanan
]);
$conn->close();
?>
