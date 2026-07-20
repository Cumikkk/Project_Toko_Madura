<?php

$memberId = $user['MBR_ID'];
$dt->query("
    WITH RECURSIVE member_hierarchy AS (
        SELECT 
            MBR_ID as root_id,
            MBR_ID,
            MBR_NAME,
            MBR_CODE,
            MBR_IDSPN,
            MBR_EMAIL,
            MBR_TYPE,
            MBR_DATETIME,
            MBR_STS
        FROM tb_member
        WHERE MBR_IDSPN = {$memberId}
        UNION ALL
        SELECT 
            mh.root_id,
            m.MBR_ID,
            m.MBR_NAME,
            m.MBR_CODE,
            m.MBR_IDSPN,
            m.MBR_EMAIL,
            m.MBR_TYPE,
            m.MBR_DATETIME,
            m.MBR_STS
        FROM tb_member m
        INNER JOIN member_hierarchy mh ON m.MBR_IDSPN = mh.MBR_ID
    )

    SELECT 
        MD5(MD5(acc.ACC_MBR)) as MBR_ID,
        IFNULL(acc.ACC_WPCHECK_DATE, acc.ACC_F_PROFILE_DATE) as active_date,
        mh.MBR_NAME as client_name,
        acc.ACC_LOGIN as account,
        acc.RTYPE_NAME as product,
        IF(acc.RTYPE_ISFLOATING, 'Floating', acc.RTYPE_RATE) as rate,
        tsc.SLSCONDITION_STS as status,
        MD5(MD5(acc.ID_ACC)) as action,
        MD5(MD5(tsc.ID_SLSCONDITION)) as id
    FROM member_hierarchy as mh
    JOIN (
        SELECT 
            tr.ID_ACC,
            tr.ACC_F_PROFILE_DATE,
            tr.ACC_MBR,
            tr.ACC_LOGIN,
            tr.ACC_WPCHECK_DATE,
            trt.RTYPE_RATE,
            trt.RTYPE_NAME,
            trt.RTYPE_ISFLOATING
        FROM tb_racc as tr
        JOIN tb_racctype as trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
        WHERE tr.ACC_STS = -1
        AND tr.ACC_DERE = 1
        AND tr.ACC_LOGIN != 0
    ) as acc ON (acc.ACC_MBR = mh.MBR_ID)
    LEFT JOIN tb_sales_conditions as tsc ON (tsc.SLSCONDITION_IDACC = acc.ID_ACC)
    WHERE mh.MBR_STS != 1
");

$dt->hide('MBR_ID');
$dt->hide('id');
$dt->edit("status", function($col) {
    if($col['status'] === null) {
        return '<span class="badge bg-secondary">Not Activated</span>';
    }

    if($col['status'] == -1) {
        return '<span class="badge bg-success">Active</span>';
    }

    if($col['status'] == 1) {
        return '<span class="badge bg-danger">Rejected</span>';
    }

    return '<span class="badge bg-warning">Pending</span>';
});

$dt->edit("action", function($col) {
    if($col['status'] === null) {
        return '<div data-account="' . $col['MBR_ID'] . '" data-action="' . $col['action'] . '" class="btn btn-sm btn-secondary text-white buttonModalAccountConditions">Activate</div>';
    }

    if($col['status'] == -1) {
        return '
            <div data-account="' . $col['MBR_ID'] . '" data-action="' . $col['action'] . '" class="btn btn-sm btn-success text-white buttonModalAccountConditions">Active</div>
            <a target="_blank" href="/export/sales_conditions?id='.$col['id'].'" class="btn btn-sm btn-danger text-white"> <i class="fa fa-file-pdf"></i> PDF</a>
        ';
    }

    if($col['status'] == 1) {
        return '<div data-account="' . $col['MBR_ID'] . '" data-action="' . $col['action'] . '" class="btn btn-sm btn-danger text-white buttonModalAccountConditions">Rejected</div>';
    }

    return '
        <div class="btn btn-sm text-white btn-warning">Pending</div>
    ';
});

echo $dt->generate()->toJson();