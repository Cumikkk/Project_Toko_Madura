<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$idOutlet = intval($data['id_outlet'] ?? 0);

$db = Database::connect();
$riwayat = [];
$resRiwayat = $db->query("SELECT * FROM riwayat_langganan WHERE id_outlet = {$idOutlet} ORDER BY id_riwayat DESC");
if ($resRiwayat) {
    while ($r = $resRiwayat->fetch_assoc()) {
        $riwayat[] = $r;
    }
}

JsonResponse([
    'code' => 200,
    'success' => true,
    'data' => $riwayat
]);
exit;
