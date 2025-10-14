<?php
header("Content-Type: application/json");
require_once "../config/database.php";

$query = "SELECT * FROM incidents ORDER BY time DESC";
$result = mysqli_query($conn, $query);

$incidents = [];
while ($row = mysqli_fetch_assoc($result)) {
    $incidents[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $incidents
]);
?>
