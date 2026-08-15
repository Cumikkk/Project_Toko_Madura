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
        u.alamat_lengkap as alamat_outlet,
        mw.provinsi,
        mw.kabupaten,
        mw.kecamatan,
        mw.kelurahan,
        o.status,
        DATE_FORMAT(o.tgl_request, '%d/%m/%Y %H:%i') as tanggal_bergabung,
        DATE_FORMAT(o.tgl_disetujui, '%d/%m/%Y %H:%i') as tgl_disetujui
    FROM outlet o
    JOIN users u ON o.id_users = u.id_users
    LEFT JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah
    WHERE o.id_investor = {$idInvestor}
      AND (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan'))
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
