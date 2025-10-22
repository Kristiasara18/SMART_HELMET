<?php
set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

include "../koneksi.php";

function sendSSE($data) {
  echo "data: " . json_encode($data) . "\n\n";
  @ob_flush();
  @flush();
}

while (true) {
  if (connection_aborted()) break;

  $query = "SELECT nama_pekerja, lokasi, status, waktu FROM kejadian_detail WHERE aktif = 1 ORDER BY waktu DESC";
  $result = $conn->query($query);

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  sendSSE($data);
  sleep(3); // refresh tiap 3 detik
}
?>
