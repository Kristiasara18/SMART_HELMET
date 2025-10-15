<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include '../koneksi.php';

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Pastikan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Metode harus POST"]);
    exit();
}

// Ambil body JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak valid"]);
    exit();
}

// Sesuaikan kolom tabel kejadian_detail
$device_id = $data['device_id'] ?? 'HLM-001';
$nama_pekerja = $data['nama_pekerja'] ?? 'Bintang Dorkas';
$lokasi = $data['lokasi'] ?? 'Zona A';
$status = $data['status'] ?? 'Jatuh';
$catatan = $data['catatan'] ?? '';
$handled = 0;

// Insert ke kejadian_detail
$stmt = $conn->prepare("INSERT INTO kejadian_detail (device_id, nama_pekerja, lokasi, status, catatan, handled) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $device_id, $nama_pekerja, $lokasi, $status, $catatan, $handled);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Insiden berhasil disimpan"]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal menyimpan: ".$stmt->error]);
}

$stmt->close();
$conn->close();
?>
