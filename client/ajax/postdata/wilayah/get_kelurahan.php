<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$provinsi = $data['provinsi'] ?? '';
$kabupaten = $data['kabupaten'] ?? '';
$kecamatan = $data['kecamatan'] ?? '';
$db = Database::connect();
$results = [];

if (!empty($provinsi) && !empty($kabupaten) && !empty($kecamatan)) {
    $provSafe = $db->real_escape_string($provinsi);
    $kabSafe = $db->real_escape_string($kabupaten);
    $kecSafe = $db->real_escape_string($kecamatan);
    $query = "SELECT id_wilayah, kelurahan, kodepos FROM master_wilayah WHERE provinsi = '{$provSafe}' AND kabupaten = '{$kabSafe}' AND kecamatan = '{$kecSafe}' ORDER BY kelurahan ASC";
    $res = $db->query($query);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $results[] = [
                'id' => $r['id_wilayah'],
                'text' => ucwords(strtolower($r['kelurahan'])) . " - " . $r['kodepos'],
                'kelurahan' => $r['kelurahan']
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['results' => $results]);
exit;
