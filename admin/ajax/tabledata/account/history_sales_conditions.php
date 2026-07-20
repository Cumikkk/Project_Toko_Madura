<?php

$filterPartner = isset($_GET['partner']) ? $db->real_escape_string($_GET['partner']) : "";
$filterAccount = isset($_GET['account']) ? $db->real_escape_string($_GET['account']) : "";
$filterStatus = isset($_GET['status']) ? $db->real_escape_string($_GET['status']) : "";

$filterStatusMap = [
    'pending' => 0,
    'approved' => -1,
    'rejected' => 1
];

$queySearchPartner = !empty($filterPartner) ? "AND partner.MBR_NAME LIKE '%{$filterPartner}%'" : "";
$queySearchAccount = !empty($filterAccount) ? "AND tr.ACC_LOGIN LIKE '%{$filterAccount}%'" : "";
$queySearchStatus = !empty($filterStatus) ? "AND tsc.SLSCONDITION_STS = {$filterStatusMap[$filterStatus]}" : "";

$dt->query("
    SELECT 
        tsc.SLSCONDITION_RESPONSE_DATETIME as `response_date`,
        partner.MBR_NAME as `request_by_partner`,
        MD5(MD5(partner.MBR_ID)) as `id_partner`,
        client.MBR_NAME as `client_name`,
        tr.ACC_LOGIN as `account`,
        MD5(MD5(tr.ACC_LOGIN)) as `login`,
        trt.RTYPE_NAME as `product`,
        IF(trt.RTYPE_ISFLOATING, 'Floating', trt.RTYPE_RATE) as `rate`,
        tsc.SLSCONDITION_NOTE as `note`,
        tsc.SLSCONDITION_STS as `status`,
        tsc.SLSCONDITION_WPNAME as `response_by`,
        MD5(MD5(tsc.ID_SLSCONDITION)) as `id`
    FROM tb_sales_conditions as tsc
    JOIN tb_racc as tr ON (tr.ID_ACC = tsc.SLSCONDITION_IDACC)
    JOIN tb_racctype as trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
    JOIN tb_member as client ON (client.MBR_ID = tr.ACC_MBR)
    LEFT JOIN tb_member as partner ON (partner.MBR_ID = tsc.SLSCONDITION_PARTNER)
    WHERE tsc.SLSCONDITION_STS != 0
    {$queySearchPartner}
    {$queySearchAccount}
    {$queySearchStatus}
");

$dt->hide('account');
$dt->hide('login');
$dt->hide('id_partner');
$dt->edit('request_by_partner', function($col) {
    return '
        <a href="/account/sales_conditions/rebate_request_partner?account='.$col['id_partner'].'" target="_blank">
            <p class="mb-0">'.$col['request_by_partner'].'</p>
        </a>
    ';
});
$dt->edit('client_name', function($col) {
    return '
        <p class="mb-0">'.$col['client_name'].'</p>
        <a href="/account/sales_conditions/history_rebate?account='.$col['login'].'" target="_blank">
            <p class="mb-0 text-muted">ACC-'.$col['account'].'</p>
        </a>
    ';
});

$dt->edit("status", function($col) {
    $statusMap = [
        0 => '<span class="badge bg-warning">Pending</span>',
        -1 => '<span class="badge bg-success">Accepted</span>',
        1 => '<span class="badge bg-danger">Rejected</span>',
    ];

    return $statusMap[$col['status']] ?? '<span class="badge bg-secondary">Unknown</span>';
});

$dt->edit("note", function($col) {
    return nl2br(htmlspecialchars($col['note'] ?? ""));
});

$dt->edit("id", function($col) {
    if($col['status'] != -1) {
        return '';
    }

    return '<a target="_blank" href="/export/sales_conditions?id='.$col['id'].'" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>';
});

echo $dt->generate()->toJson();