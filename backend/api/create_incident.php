<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Metode harus POST"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak valid"]);
    exit();
}

$device_id = $data['device_id'] ?? 'HLM-001';
$nama_pekerja = $data['nama_pekerja'] ?? 'Bintang Dorkas';
$lokasi = $data['lokasi'] ?? 'Zona A';
$status = $data['status'] ?? 'Jatuh';
$catatan = $data['catatan'] ?? '';
$handled = 0;

$stmt = $conn->prepare("INSERT INTO kejadian_detail (device_id, nama_pekerja, lokasi, status, catatan, handled, waktu) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssi", $device_id, $nama_pekerja, $lokasi, $status, $catatan, $handled);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    $incident = $conn->query("SELECT * FROM kejadian_detail WHERE id = $id")->fetch_assoc();

    // Mapping device_id → id_pekerja
    $q = $conn->prepare("SELECT id_pekerja FROM karyawan WHERE kode_helmet = ?");
    $q->bind_param("s", $device_id);
    $q->execute();
    $id_pekerja = $q->get_result()->fetch_assoc()['id_pekerja'] ?? null;

    if ($id_pekerja) {
        // Update atau insert ke kejadian_helmet
        $check = $conn->prepare("SELECT id_kejadian, jumlah_kejadian FROM kejadian_helmet WHERE id_pekerja = ? AND tanggal = CURDATE()");
        $check->bind_param("i", $id_pekerja);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $newCount = $row['jumlah_kejadian'] + 1;
            $update = $conn->prepare("UPDATE kejadian_helmet SET jumlah_kejadian = ? WHERE id_kejadian = ?");
            $update->bind_param("ii", $newCount, $row['id_kejadian']);
            $update->execute();
            $update->close();
        } else {
            $id_tipe = 1;
            $insert = $conn->prepare("INSERT INTO kejadian_helmet (id_pekerja, tanggal, jumlah_kejadian, id_tipe) VALUES (?, CURDATE(), 1, ?)");
            $insert->bind_param("ii", $id_pekerja, $id_tipe);
            $insert->execute();
            $insert->close();
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Insiden berhasil disimpan dan rekap diperbarui",
        "data" => $incident
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
