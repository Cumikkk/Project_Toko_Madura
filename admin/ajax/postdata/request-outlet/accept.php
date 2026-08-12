<?php
require_once __DIR__ . "/../../../../config/setting.php";
use App\Models\Outlet;

header('Content-Type: application/json');

$idOutlet = (int)($_POST['id_outlet'] ?? 0);
echo json_encode(Outlet::acceptRequest($idOutlet));
exit;
