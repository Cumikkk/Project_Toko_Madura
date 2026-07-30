<?php
use Config\Core\Database;
use App\Models\Helper;

$idInvestor = intval($_GET['id_investor'] ?? 0);

if ($idInvestor <= 0) {
    JsonResponse([
        'code'    => 200,
        'success' => false,
        'message' => 'ID Investor tidak valid',
        'data'    => []
    ]);
}

$sql = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        o.alamat_outlet,
        o.kecamatan,
        DATE_FORMAT(o.tanggal_bergabung, '%d/%m/%Y') as tanggal_bergabung
    FROM outlet o
    WHERE o.id_investor = {$idInvestor}
    ORDER BY o.id_outlet DESC
";

$res = $db->query($sql);
$data = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

JsonResponse([
    'code'    => 200,
    'success' => true,
    'message' => 'Data outlet berhasil diambil',
    'data'    => $data
]);
