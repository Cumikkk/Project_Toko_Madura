<?php
require_once __DIR__ . "/../../../../config/setting.php";
use App\Models\Outlet;

header('Content-Type: application/json');

$idOutlet = (int)($_POST['id_outlet'] ?? 0);
$alasan   = trim($_POST['alasan_penolakan'] ?? '');
echo json_encode(Outlet::rejectRequest($idOutlet, $alasan));
exit;
