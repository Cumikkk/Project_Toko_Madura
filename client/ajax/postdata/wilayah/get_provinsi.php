<?php
use Config\Core\Database;

$db = Database::connect();
$results = [];

$query = "SELECT DISTINCT provinsi FROM master_wilayah ORDER BY provinsi ASC";
$res = $db->query($query);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $results[] = [
            'id' => $r['provinsi'],
            'text' => ucwords(strtolower($r['provinsi']))
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(['results' => $results]);
exit;
