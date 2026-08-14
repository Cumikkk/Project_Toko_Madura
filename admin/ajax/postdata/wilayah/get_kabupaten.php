<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$provinsi = $data['provinsi'] ?? '';
$db = Database::connect();
$results = [];

if (!empty($provinsi)) {
    $provSafe = $db->real_escape_string($provinsi);
    $query = "SELECT DISTINCT kabupaten FROM master_wilayah WHERE provinsi = '{$provSafe}' ORDER BY kabupaten ASC";
    $res = $db->query($query);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $results[] = [
                'id' => $r['kabupaten'],
                'text' => ucwords(strtolower($r['kabupaten']))
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['results' => $results]);
exit;
