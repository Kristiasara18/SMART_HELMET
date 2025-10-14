<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include "../koneksi.php";

$input = json_decode(file_get_contents("php://input"), true);

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if ($email && $password) {
  $query = mysqli_query($conn, "SELECT * FROM user_login WHERE email='$email' AND password='$password'");
  if (mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);
    echo json_encode(["status" => "success", "user" => $user]);
  } else {
    echo json_encode(["status" => "error", "message" => "Email atau password salah!"]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
}
?>
