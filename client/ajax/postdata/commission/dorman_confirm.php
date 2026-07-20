<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$query_mbr = $db->query("SELECT ID_DRMKEEP, DRMKEEP_MBRUSER, DRMKEEP_DATETIMEEXTEND FROM tb_dormankeep WHERE MD5(MD5(ID_DRMKEEP)) = '".$data['id']."' LIMIT 1");
if($query_mbr->num_rows > 0) {
    $row_mbr = $query_mbr->fetch_assoc();

    $insert = Database::insert('tb_dormanconfirm', [
        'DRMCONFIRM_MBRUSER'        => $row_mbr['DRMKEEP_MBRUSER'],
        'DRMCONFIRM_MBRSLS'         => $user['MBR_ID'],
        'DRMCONFIRM_DATETIMECONFIRM' => date('Y-m-d H:i:s'),
    ]);
    if(!$insert) {
        JsonResponse([
            'success' => false,
            'message' => "Failed to insert data",
            'data' => []
        ]);
    }

    $delete = Database::delete('tb_dormankeep', [
        'ID_DRMKEEP' => $row_mbr['ID_DRMKEEP']
    ]);
    if(!$delete) {
        JsonResponse([
            'success' => false,
            'message' => "Failed to delete data",
            'data' => []
        ]);
    }
};

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);