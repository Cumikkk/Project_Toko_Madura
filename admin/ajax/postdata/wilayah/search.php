<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
// Select2 mengirimkan kata kunci pencarian dalam variabel 'term' atau 'q'
$term = isset($data['term']) ? $data['term'] : (isset($_GET['q']) ? $_GET['q'] : '');
$db = Database::connect();

$results = [];

error_log("Wilayah Search API Hit! Term: " . $term);


if (!empty($term)) {
    $termSafe = $db->real_escape_string($term);
    // Mencari berdasarkan kelurahan atau kecamatan
    $query = "
        SELECT id_wilayah, kelurahan, kecamatan, kabupaten, provinsi, kodepos 
        FROM master_wilayah 
        WHERE kelurahan LIKE '%{$termSafe}%' OR kecamatan LIKE '%{$termSafe}%'
        ORDER BY provinsi, kabupaten, kecamatan, kelurahan
        LIMIT 20
    ";
    
    $res = $db->query($query);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            // Merangkai teks untuk ditampilkan di Select2
            $text = "Kel. {$r['kelurahan']}, Kec. {$r['kecamatan']} - {$r['kabupaten']}, {$r['provinsi']} ({$r['kodepos']})";
            
            $results[] = [
                'id' => $r['id_wilayah'],
                'text' => $text
            ];
        }
    }
}

// Format respons khusus untuk Select2
header('Content-Type: application/json');
echo json_encode([
    'results' => $results
]);
exit;
