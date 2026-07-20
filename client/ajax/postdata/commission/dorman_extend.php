<?php

use App\Models\Helper;
use App\Models\ProfilePerusahaan;
use Config\Core\Database;
$data = Helper::getSafeInput($_POST);
$query_mbr = $db->query("SELECT ID_DRMKEEP, DRMKEEP_DATETIMEEXTEND FROM tb_dormankeep WHERE MD5(MD5(ID_DRMKEEP)) = '".$data['id']."' LIMIT 1");
if($query_mbr->num_rows > 0) {
    $row_mbr = $query_mbr->fetch_assoc();

    if($row_mbr['DRMKEEP_DATETIMEEXTEND'] >= date('Y-m-d H:i:s')) {
        $update = Database::update('tb_dormankeep', [
            'DRMKEEP_DATETIMEEXTEND' => date_add(date_create(), date_interval_create_from_date_string(ProfilePerusahaan::get()['PROF_DORMAN_EXTEND'] . " days"))->format('Y-m-d H:i:s'),
        ], [
            'ID_DRMKEEP' => $row_mbr['ID_DRMKEEP']
        ]);
        if(!$update) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Failed to update data",
                'data' => []
            ]);
        }
    };
};

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);