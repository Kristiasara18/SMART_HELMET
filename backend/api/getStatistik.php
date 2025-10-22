<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

$koneksi = new mysqli("localhost", "root", "", "db_karyawan");
if ($koneksi->connect_error) {
    die(json_encode(["error" => "Koneksi gagal: " . $koneksi->connect_error]));
}

// Generate 7 hari terakhir
$dates_query = "
    SELECT CURDATE() - INTERVAL 6 DAY AS tanggal UNION ALL
    SELECT CURDATE() - INTERVAL 5 DAY UNION ALL
    SELECT CURDATE() - INTERVAL 4 DAY UNION ALL
    SELECT CURDATE() - INTERVAL 3 DAY UNION ALL
    SELECT CURDATE() - INTERVAL 2 DAY UNION ALL
    SELECT CURDATE() - INTERVAL 1 DAY UNION ALL
    SELECT CURDATE()
";

// Query utama: jumlah kejadian per tanggal & status
$query = "
SELECT 
    dates.tanggal,
    status_list.status AS tipe_kejadian,
    IFNULL(COUNT(d.id), 0) AS jumlah_insiden
FROM 
    ($dates_query) AS dates
CROSS JOIN
    (SELECT DISTINCT status FROM kejadian_detail) AS status_list
LEFT JOIN kejadian_detail d 
    ON DATE(d.waktu) = dates.tanggal AND d.status = status_list.status
GROUP BY dates.tanggal, status_list.status
ORDER BY dates.tanggal DESC, status_list.status ASC
";

$result = $koneksi->query($query);
$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo json_encode(["error" => $koneksi->error]);
    exit;
}

echo json_encode($data);
?>
