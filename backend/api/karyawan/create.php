<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

include "../../koneksi.php";

$data = json_decode(file_get_contents("php://input"), true);

$nama = $data['nama'] ?? '';
$jabatan = $data['jabatan'] ?? '';
$kode_helmet = $data['kode_helmet'] ?? '';

if($nama && $jabatan && $kode_helmet){
    $query = "INSERT INTO karyawan (nama, jabatan, kode_helmet) VALUES ('$nama', '$jabatan', '$kode_helmet')";
    if(mysqli_query($conn, $query)){
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error","message"=>mysqli_error($conn)]);
    }
}else{
    echo json_encode(["status"=>"error","message"=>"Data tidak lengkap"]);
}
?>
