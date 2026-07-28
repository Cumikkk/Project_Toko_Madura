<?php
use App\Models\Helper;
use Config\Core\Database;

$idOutlet = intval($_POST['id_outlet'] ?? 0);

if ($idOutlet <= 0) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "ID Outlet tidak valid",
        'data'    => []
    ]);
}

$update = $db->query("UPDATE outlet SET status = 'active', tanggal_disetujui = NOW() WHERE id_outlet = {$idOutlet}");

if (!$update) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Gagal mengaktifkan outlet",
        'data'    => []
    ]);
}

JsonResponse([
    'code'    => 200,
    'success' => true,
    'message' => "Request outlet berhasil disetujui. Status outlet kini ACTIVE.",
    'data'    => []
]);
