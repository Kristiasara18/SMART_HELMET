<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include "../koneksi.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? null;

if (!$id) {
  echo json_encode(["status" => "error", "message" => "ID required"]);
  exit;
}

// Cek apakah incident belum ditangani
$q = $conn->query("SELECT * FROM kejadian_detail WHERE id='$id' AND handled=0");
if ($q->num_rows > 0) {
  $row = $q->fetch_assoc();
  $pesan = "⚠️ *Peringatan Smart Helmet* ⚠️\nIncident belum ditangani!\nNama: {$row['nama_pekerja']}\nLokasi: {$row['lokasi']}\nStatus: {$row['status']}";
  
  // Contoh kirim ke WhatsApp (gunakan API Fonnte / Twilio / Meta Cloud)
  $token = "YOUR_FONNTE_TOKEN"; // ganti token kamu
  $target = "62xxxxxxxxxx"; // ganti nomor grup atau admin
  
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.fonnte.com/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
      "target" => $target,
      "message" => $pesan,
      "delay" => "0",
    ],
    CURLOPT_HTTPHEADER => [
      "Authorization: $token"
    ],
  ]);
  $response = curl_exec($curl);
  curl_close($curl);

  echo json_encode(["status" => "success", "message" => "WhatsApp alert sent", "data" => json_decode($response, true)]);
} else {
  echo json_encode(["status" => "ignored", "message" => "Incident already handled or not found"]);
}
$conn->close();
?>
