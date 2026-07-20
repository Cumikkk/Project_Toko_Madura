<?php
use App\Models\Helper;
use App\Models\Ib;
use App\Models\User;

$result = [];
$downlines = Ib::getNetworks($user['MBR_ID'], "downline");
$structures = Ib::toHierarcy($downlines);

/**
 * Get total accounts for a member from DB_METALIVE
 * Query mt6_users.login yang ada di tb_racc.ACC_LOGIN
 * dengan relasi tb_member.MBR_ID = tb_racc.ACC_MBR dan ACC_DERE = 1
 */
function getTotalAccounts(int $memberId): int {
    global $db;
    
    try {
        $query = "
            SELECT COUNT(DISTINCT ml.Login) as total_account
            FROM tb_racc ra
            INNER JOIN meta_rrfxreal.mt5_users ml ON ml.login = ra.ACC_LOGIN
            WHERE ra.ACC_MBR = {$memberId}
            AND ra.ACC_DERE = 1
        ";
        
        $result = $db->query($query);
        if ($result && $row = $result->fetch_assoc()) {
            return (int) $row['total_account'];
        }
        
        return 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Build tree structure recursively from tb_member data
 * Using: MBR_ID as key, MBR_IDSPN as parent, MBR_NAME as name
 * Returns complete member data for tree table display
 */
function subArray(array $array): array {
    $res = [];
    foreach($array as $ar) {
        $memberId = $ar['MBR_ID'] ?? 0;
        $totalAccount = getTotalAccounts($memberId);
        
        $entry = [
            'text' => $ar['MBR_NAME'] . " - " . $ar['SALES_TYPE'],
            'icon' => "fas fa-user",
            'totalAccount' => $totalAccount,
            'email' => $ar['MBR_EMAIL'] ?? '',
            'salesType' => $ar['SALES_TYPE'] ?? 'Trader',
            'memberId' => $memberId,
            'expanded' => true,
            'nodes' => []
        ];

        if(!empty($ar['children'])) {
            $entry['nodes'] = subArray($ar['children']);
        }

        $res[] = $entry;
    }

    return $res;
}

$result = subArray($structures['children']);
exit(json_encode($result));
