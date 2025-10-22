<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

include "../../koneksi.php";

$id = $_GET['id'] ?? 0;
$data = json_decode(file_get_contents("php://input"), true);

$nama = $data['nama'] ?? '';
$jabatan = $data['jabatan'] ?? '';
$kode_helmet = $data['kode_helmet'] ?? '';

if($id && $nama && $jabatan && $kode_helmet){
    $query = "UPDATE karyawan SET nama='$nama', jabatan='$jabatan', kode_helmet='$kode_helmet' WHERE id_pekerja=$id";
    if(mysqli_query($conn, $query)){
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
    }
}else{
    echo json_encode(["status"=>"error","message"=>"Data tidak lengkap"]);
}
?>
