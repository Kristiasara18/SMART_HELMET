<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include "../koneksi.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
  echo json_encode(["status"=>"error","message"=>"ID required"]);
  exit;
}

$stmt = $conn->prepare("UPDATE kejadian_detail SET handled = 1 WHERE id = ?");
$stmt->bind_param("i",$id);
$ok = $stmt->execute();

if ($ok) {
  echo json_encode(["status"=>"success","message"=>"Marked handled"]);
} else {
  echo json_encode(["status"=>"error","message"=>"DB error"]);
}
$conn->close();
?>
