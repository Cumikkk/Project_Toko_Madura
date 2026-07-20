<?php
use App\Models\Helper;
use App\Models\Ib;
use App\Models\User;

$result = [];

// Get all members with their hierarchy
global $db;
$query = "
    SELECT 
        m.MBR_ID,
        m.MBR_CODE,
        m.MBR_NAME,
        m.MBR_EMAIL,
        m.MBR_IDSPN,
        IFNULL(tss.SLSSTRC_NAME, 'Trader') as SALES_TYPE
    FROM tb_member m
    LEFT JOIN tb_sales_structure tss ON tss.ID_SLSSTRC = m.MBR_TYPE
    ORDER BY m.MBR_ID
";

$sqlResult = $db->query($query);
$members = $sqlResult->fetch_all(MYSQLI_ASSOC);

// Get all accounts for each member
$accountsQuery = "
    SELECT 
        ra.ACC_MBR,
        ra.ACC_LOGIN,
        IFNULL(mt5users.BALANCE, 0) as BALANCE,
        SUM(IF(mt5deals.profit > 0, mt5deals.profit, 0)) as DEPOSIT,
        SUM(IF(mt5deals.profit < 0, (mt5deals.profit), 0)) as WITHDRAWAL
    FROM tb_racc ra
    JOIN meta_rrfxreal.mt5_users mt5users ON mt5users.login = ra.ACC_LOGIN
    JOIN meta_rrfxreal.mt5_deals mt5deals ON mt5deals.login = mt5users.login
    WHERE ra.ACC_DERE = 1
    AND mt5deals.`action` = 2
    GROUP BY mt5deals.login
    ORDER BY ra.ACC_MBR, ra.ACC_LOGIN
";

$accountsResult = $db->query($accountsQuery);
$accountsData = $accountsResult->fetch_all(MYSQLI_ASSOC);

// Group accounts by member ID
$accountsByMember = [];
foreach($accountsData as $acc) {
    $accountsByMember[$acc['ACC_MBR']][] = $acc;
}

// Add accounts to members
foreach($members as &$member) {
    $member['accounts'] = $accountsByMember[$member['MBR_ID']] ?? [];
}
unset($member);

$structures = Ib::toHierarcy($members);

function subArray(array $array): array {
    $res = [];
    foreach($array as $ar) {
        $entry = [
            'text' => $ar['MBR_NAME'] . " (" . $ar['MBR_CODE'] . ") - " . $ar['SALES_TYPE'],
            'email' => $ar['MBR_EMAIL'] ?? '-',
            'parent_id' => $ar['MBR_IDSPN'] ?? '-',
            'icon' => "fas fa-user",
            'expanded' => true,
            'nodes' => []
        ];

        // Add account rows as child nodes
        if(!empty($ar['accounts'])) {
            foreach($ar['accounts'] as $acc) {
                $entry['nodes'][] = [
                    'text' => 'Account: ' . $acc['ACC_LOGIN'],
                    'email' => '',
                    'parent_id' => '',
                    'account_login' => $acc['ACC_LOGIN'],
                    'account_balance' => number_format($acc['BALANCE'], 2),
                    'account_deposit' => number_format($acc['DEPOSIT'], 2),
                    'account_withdrawal' => number_format($acc['WITHDRAWAL'], 2),
                    'icon' => "fas fa-wallet",
                    'is_account' => true,
                    'nodes' => []
                ];
            }
        }

        // Add member children
        if(!empty($ar['children'])) {
            $childNodes = subArray($ar['children']);
            $entry['nodes'] = array_merge($entry['nodes'], $childNodes);
        }

        $res[] = $entry;
    }

    return $res;
}

if(!empty($structures) && isset($structures['children'])) {
    $result = subArray($structures['children']);
} else if (!empty($structures)) {
    $result = [subArray([$structures])[0]];
}

exit(json_encode($result));
