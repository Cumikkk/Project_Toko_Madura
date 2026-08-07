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
        u.alamat as alamat_outlet,
        u.kecamatan,
        o.status,
        DATE_FORMAT(o.tanggal_request, '%d/%m/%Y %H:%i') as tanggal_bergabung,
        DATE_FORMAT(o.tanggal_disetujui, '%d/%m/%Y %H:%i') as tanggal_disetujui
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    WHERE o.id_investor = {$idInvestor}
      AND o.status = 'active'
      AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= CURRENT_DATE())
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
