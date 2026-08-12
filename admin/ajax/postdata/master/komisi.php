<?php
require_once __DIR__ . "/../../../../config/setting.php";
use App\Models\Master;

header("Content-Type: application/json");

$action   = trim($_POST["action"] ?? "");
$idKomisi = intval($_POST["id_komisi"] ?? 0);

if ($action === "delete") {
    $result = Master::deleteKomisi($idKomisi);
    echo json_encode($result);
    exit;
}

$result = Master::saveKomisi($_POST, $_FILES);
echo json_encode($result);

