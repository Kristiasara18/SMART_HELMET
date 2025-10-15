<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: DELETE");

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
