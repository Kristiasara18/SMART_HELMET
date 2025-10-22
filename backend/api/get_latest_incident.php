<?php
include '../koneksi.php';
header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT * FROM kejadian_detail ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'data' => $row
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Tidak ada data insiden'
    ]);
}

$conn->close();
?>
