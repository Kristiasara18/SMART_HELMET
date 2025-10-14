<?php
// sse_incidents.php
// Note: untuk development / UTS. Tidak untuk production scale tanpa perbaikan.

set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

include "../koneksi.php";

// helper: kirim event SSE
function sendSSE($id, $data) {
    // id: event id, data: string (JSON)
    echo "id: {$id}\n";
    echo "data: {$data}\n\n";
    @ob_flush();
    @flush();
}

// simpan last_id awal (ambil id max sekarang)
$last_id = 0;
$res = $conn->query("SELECT MAX(id) as mx FROM kejadian_helmet");
if ($res) {
    $r = $res->fetch_assoc();
    $last_id = intval($r['mx']);
}

// loop terus-menerus, cek DB setiap detik
while (true) {
    // jika client sudah putus, hentikan loop
    if (connection_aborted()) {
        break;
    }

    // cari insiden baru (handled boleh 0 atau apapun sesuai kebutuhan)
    $stmt = $conn->prepare("SELECT * FROM kejadian_helmet WHERE id > ? AND handled = 0 ORDER BY id ASC");
    $stmt->bind_param("i", $last_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $last_id = intval($row['id']);
        // kirim data sebagai JSON
        $payload = json_encode($row);
        sendSSE($last_id, $payload);
    }

    // kecilkan beban server: tidur 1 detik
    sleep(1);
}
$conn->close();
