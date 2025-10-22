<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");


include "../../koneksi.php";

$id = $_GET['id'] ?? 0;

if($id){
    $query = "DELETE FROM karyawan WHERE id_pekerja=$id";
    if(mysqli_query($conn, $query)){
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
    }
}else{
    echo json_encode(["status"=>"error","message"=>"ID tidak diberikan"]);
}
?>
