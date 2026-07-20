<?php

$dt->query("
    SELECT 
        tr.ACC_WPCHECK_DATE as `active_date`,
        partner.MBR_NAME as `request_by_partner`,
        CONCAT(client.MBR_NAME, ' / ', tr.ACC_LOGIN) as `client_name`,
        trt.RTYPE_NAME as `product`,
        IF(trt.RTYPE_ISFLOATING, 'Floating', trt.RTYPE_RATE) as `rate`,
        tsc.SLSCONDITION_STS as `status`,
        MD5(MD5(tsc.ID_SLSCONDITION)) as action_id
    FROM tb_sales_conditions as tsc
    JOIN tb_racc as tr ON (tr.ID_ACC = tsc.SLSCONDITION_IDACC)
    JOIN tb_racctype as trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
    JOIN tb_member as client ON (client.MBR_ID = tr.ACC_MBR)
    JOIN tb_member as partner ON (partner.MBR_ID = tsc.SLSCONDITION_PARTNER)
    WHERE tsc.SLSCONDITION_STS = 0
");

$dt->edit("status", function($col) {
    $statusMap = [
        0 => '<span class="badge bg-warning">Pending</span>',
        -1 => '<span class="badge bg-success">Accepted</span>',
        1 => '<span class="badge bg-danger">Rejected</span>',
    ];

    return $statusMap[$col['status']] ?? '<span class="badge bg-secondary">Unknown</span>';
});

$dt->edit("action_id", function($col) {
    return '<div class="action d-flex gap-2 justify-content-center" data-actionid="'.$col['action_id'].'"></div>';
});

echo $dt->generate()->toJson();