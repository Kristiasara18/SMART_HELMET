<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include "../koneksi.php";

$data = json_decode(file_get_contents("php://input"), true);

// contoh fallback jika tidak ada body
$nama = $data['nama_pekerja'] ?? 'Test Operator';
$device_id = $data['device_id'] ?? 'HLM-999';
$lokasi = $data['lokasi'] ?? 'Blok X';
$status = $data['status'] ?? 'Jatuh';
$catatan = $data['catatan'] ?? 'Simulasi insiden';

// insert record
$stmt = $conn->prepare("INSERT INTO kejadian_helmet (device_id, nama_pekerja, lokasi, status, catatan, handled) VALUES (?, ?, ?, ?, ?, 0)");
$stmt->bind_param("sssss", $device_id, $nama, $lokasi, $status, $catatan);
$ok = $stmt->execute();

if ($ok) {
  echo json_encode(["status"=>"success","message"=>"Created incident","id"=>$conn->insert_id]);
} else {
  echo json_encode(["status"=>"error","message"=>"DB insert failed"]);
}

$conn->close();
