<?php
set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

include "../koneksi.php";

// helper kirim SSE
function sendSSE($id, $data) {
    echo "id: {$id}\n";
    echo "data: {$data}\n\n";
    @ob_flush();
    @flush();
}

// ambil last_id awal dari kejadian_detail
$last_id = 0;
$res = $conn->query("SELECT MAX(id) as mx FROM kejadian_detail");
if ($res) {
    $r = $res->fetch_assoc();
    $last_id = intval($r['mx']);
}

// loop terus-menerus cek DB
while (true) {
    if (connection_aborted()) break;

    $stmt = $conn->prepare("SELECT * FROM kejadian_detail WHERE id > ? AND handled = 0 ORDER BY id ASC");
    $stmt->bind_param("i", $last_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $last_id = intval($row['id']);
        sendSSE($last_id, json_encode($row));
    }

    sleep(1);
}

$conn->close();
?>
