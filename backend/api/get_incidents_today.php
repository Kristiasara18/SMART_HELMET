<?php
include "../koneksi.php";
header('Content-Type: application/json');

$today = date('Y-m-d');
$q = $conn->query("SELECT COUNT(*) AS total_insiden 
                   FROM kejadian_detail 
                   WHERE DATE(waktu) = '$today'");
$r = $q->fetch_assoc();

echo json_encode([
  "status" => "success",
  "data" => ["total_insiden" => intval($r['total_insiden'])]
]);
?>
