<?php
include "koneksi.php";

$result = $conn->query("SELECT * FROM kejadian_detail WHERE handled = 0 AND TIMESTAMPDIFF(MINUTE, waktu, NOW()) >= 1");

while ($row = $result->fetch_assoc()) {
  $pesan = "⚠️ *Smart Helmet Alert* ⚠️\nIncident belum ditangani lebih dari 1 menit!\nNama: {$row['nama_pekerja']}\nLokasi: {$row['lokasi']}\nStatus: {$row['status']}";
  
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
    ],
    CURLOPT_HTTPHEADER => [
      "Authorization: $token"
    ],
  ]);
  $response = curl_exec($curl);
  curl_close($curl);
}

$conn->close();
?>
