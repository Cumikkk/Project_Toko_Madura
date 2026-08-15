<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$provinsi = $data['provinsi'] ?? '';
$kabupaten = $data['kabupaten'] ?? '';
$db = Database::connect();
$results = [];

if (!empty($provinsi) && !empty($kabupaten)) {
    $provSafe = $db->real_escape_string($provinsi);
    $kabSafe = $db->real_escape_string($kabupaten);
    $query = "SELECT DISTINCT kecamatan FROM master_wilayah WHERE provinsi = '{$provSafe}' AND kabupaten = '{$kabSafe}' ORDER BY kecamatan ASC";
    $res = $db->query($query);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $results[] = [
                'id' => $r['kecamatan'],
                'text' => ucwords(strtolower($r['kecamatan']))
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['results' => $results]);
exit;
