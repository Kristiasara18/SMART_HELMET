<?php
header('Content-Type: application/json');
$koneksi = new mysqli("localhost", "root", "", "db_karyawan");

if ($koneksi->connect_error) {
  die(json_encode(["error" => "Koneksi gagal: " . $koneksi->connect_error]));
}

// Ambil semua helmet aktif
$query = "
  SELECT nama_pekerja, lokasi, status, waktu
  FROM kejadian_detail
  WHERE aktif = 1
  ORDER BY waktu DESC
";
$result = $koneksi->query($query);

$data = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }
}

echo json_encode($data);
?>
