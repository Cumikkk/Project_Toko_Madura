<?php
use App\Models\Helper;
use Config\Core\Database;

$idOutlet = intval($_POST['id_outlet'] ?? 0);
$alasan   = trim(Helper::form_input($_POST['alasan_penolakan'] ?? ''));

if ($idOutlet <= 0) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "ID Outlet tidak valid",
        'data'    => []
    ]);
}

if (empty($alasan)) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Alasan penolakan wajib diisi",
        'data'    => []
    ]);
}

$escapedAlasan = $db->real_escape_string($alasan);
$update = $db->query("UPDATE outlet SET status = 'reject', alasan_penolakan = '{$escapedAlasan}', tanggal_ditolak = NOW() WHERE id_outlet = {$idOutlet}");

if (!$update) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => "Gagal memproses penolakan outlet",
        'data'    => []
    ]);
}

JsonResponse([
    'code'    => 200,
    'success' => true,
    'message' => "Request outlet berhasil ditolak.",
    'data'    => []
]);
