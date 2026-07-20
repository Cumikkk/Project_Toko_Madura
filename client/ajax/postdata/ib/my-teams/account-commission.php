<?php

use App\Models\Account;
use App\Library\Sales\SalesMain;
use App\Models\Helper;

$data = Helper::getSafeInput($_POST);
if(!isset($data['mbr']) || !isset($data['racc'])) {
    JsonResponse([
        'success' => false, 
        'message' => 'Invalid Data Provided',
        'data' => []
    ]);
    exit;
}

$sqlGetDataCondition = $db->query("
    SELECT 
        SLSCONDITION_BRANCH as branch, 
        SLSCONDITION_SALES_NAME as sales_name, 
        SLSCONDITION_NOTE as note, 
        SLSCONDITION_STS as `status` 
    FROM tb_sales_conditions 
    WHERE MD5(MD5(SLSCONDITION_IDACC)) = '{$data['racc']}' 
    LIMIT 1
");

if(!$sqlGetDataCondition) {
    JsonResponse([
        'success' => false, 
        'message' => 'Failed to retrieve sales condition data',
        'data' => []
    ]);
    exit;
}

$sqlGetMember = Account::realAccountDetail($data['racc']);
if(!$sqlGetMember) {
    JsonResponse([
        'success' => false, 
        'message' => 'Member not found',
        'data' => []
    ]);
    exit;
}

// $sqlGetDataCommission = $db->query("
//     SELECT
//         b.MBR_EMAIL as email,
//         c.SLSSTRC_NAME as sales_structure,
//         a.SLSCOM_FOREX as forex,
//         a.SLSCOM_GOLD as gold,
//         a.SLSCOM_INDEX as `index`
//     FROM
//         tb_sales_commission a
//     JOIN tb_member b ON a.SLSCOM_MBR = b.MBR_ID
//     JOIN tb_sales_structure c ON c.ID_SLSSTRC = b.MBR_TYPE
//     WHERE
//         MD5(MD5(SLSCOM_IDACC)) = '{$data['racc']}'
// ");

$salesData = SalesMain::getUserType($user['MBR_TYPE']);
$sqlGetDataCommission = $db->query("
    WITH RECURSIVE member_hierarchy AS (
        SELECT 
            MBR_ID,
            MBR_NAME,
            MBR_CODE,
            MBR_IDSPN,
            MBR_EMAIL,
            MBR_TYPE
        FROM tb_member
        WHERE MD5(MD5(MBR_ID)) = '{$data['mbr']}'
        UNION ALL    
        SELECT 
            m.MBR_ID,
            m.MBR_NAME,
            m.MBR_CODE,
            m.MBR_IDSPN,
            m.MBR_EMAIL,
            m.MBR_TYPE
        FROM tb_member m
        INNER JOIN member_hierarchy mh ON m.MBR_ID = mh.MBR_IDSPN
    )    
    SELECT 
        mh.MBR_EMAIL as email,
        tsc.SLSCOM_FOREX as forex,
        tsc.SLSCOM_GOLD as gold,
        tsc.SLSCOM_INDEX as `index`,
        tss.SLSSTRC_NAME as sales_structure,
        '1' as status
    FROM member_hierarchy mh
    LEFT JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = mh.MBR_TYPE)
    LEFT JOIN tb_sales_commission tsc ON (tsc.SLSCOM_MBR = mh.MBR_ID AND MD5(MD5(tsc.SLSCOM_IDACC)) = '".$data['racc']."')
    WHERE MD5(MD5(mh.MBR_ID)) != '{$data['mbr']}'
    AND mh.MBR_ID != 1000000000 AND tss.SLSSTRC_LEVEL >= '{$salesData->salesDetail['SLSSTRC_LEVEL']}'
    ORDER BY tss.ID_SLSSTRC ASC
");

$salesConditions = $sqlGetDataCondition->fetch_assoc();
if($salesConditions) {
    $statusMap = [
        '0' => 'pending',
        '-1' => 'approved',
        '1' => 'rejected',
    ];

    $salesConditions['status'] = $statusMap[$salesConditions['status']] ?? 'unknown';
}


JsonResponse([
    'success' => true, 
    'message' => 'Data retrieved successfully',
    'data' => [
        'conditions' => $salesConditions,
        'max_commission' => $sqlGetMember['RTYPE_KOMISI'],
        'commission' => $sqlGetDataCommission->fetch_all(MYSQLI_ASSOC),
    ]
]);