<?php

use App\Models\Helper;
use App\Models\ProfilePerusahaan;
use Config\Core\Database;
$data = Helper::getSafeInput($_POST);
$query_mbr = $db->query("SELECT MBR_ID FROM tb_member WHERE MD5(MD5(MBR_ID)) = '".$data['id']."' LIMIT 1");
if($query_mbr->num_rows == 0) {
    JsonResponse([
        'success' => false,
        'message' => "Data id not found",
        'data' => []
    ]);
};
$row_mbr = $query_mbr->fetch_assoc();

$checkmbr = $db->query("SELECT DRMKEEP_MBRUSER FROM tb_dormankeep WHERE DRMKEEP_MBRUSER = '".$row_mbr['MBR_ID']."' LIMIT 1");
if($checkmbr->num_rows > 0) {
    JsonResponse([
        'success' => false,
        'message' => "Member sudah di keep 1",
        'data' => []
    ]);
}
$insert = Database::insert('tb_dormankeep', [
    'DRMKEEP_MBRUSER'           => $row_mbr['MBR_ID'],
    'DRMKEEP_MBRSLS'            => $user['MBR_ID'],
    'DRMKEEP_DATETIMEEXTEND'    => date_add(date_create(), date_interval_create_from_date_string(ProfilePerusahaan::get()['PROF_DORMAN_EXTEND'] . " days"))->format('Y-m-d H:i:s'),
]);
if(!$insert) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to save data",
        'data' => []
    ]);
}
JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);