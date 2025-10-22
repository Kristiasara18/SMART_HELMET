<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include "../koneksi.php";

$name = $_GET['name'] ?? '';

if ($name) {
  $query = mysqli_query($conn, "SELECT * FROM karyawan WHERE nama = '$name'");
  if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    echo json_encode(["status" => "success", "data" => $data]);
  } else {
    echo json_encode(["status" => "error", "message" => "Data karyawan tidak ditemukan"]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "Nama tidak diberikan"]);
}
?>
