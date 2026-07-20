<?php
require_once __DIR__ . "/../../../../config/setting.php";

use App\Models\Ib;
use App\Models\Admin;
use Config\Core\Database;

try {
    // Authenticate admin user
    $user = Admin::authentication();
    if(empty($user)) {
        JsonResponse([
            'error' => true,
            'message' => "Unauthorized access"
        ]);
        exit;
    }

    $db = Database::connect();
    
    // Get all members with their hierarchy
    $query = "
        WITH RECURSIVE member_hierarchy AS (
            SELECT 
                m.MBR_ID,
                m.MBR_CODE,
                m.MBR_NAME,
                m.MBR_EMAIL,
                m.MBR_IDSPN,
                m.MBR_TYPE,
                1 as level
            FROM tb_member m
            WHERE m.MBR_IDSPN IS NULL OR m.MBR_IDSPN = m.MBR_ID
            
            UNION ALL
            
            SELECT 
                m.MBR_ID,
                m.MBR_CODE,
                m.MBR_NAME,
                m.MBR_EMAIL,
                m.MBR_IDSPN,
                m.MBR_TYPE,
                mh.level + 1 as level
            FROM tb_member m
            INNER JOIN member_hierarchy mh ON m.MBR_IDSPN = mh.MBR_ID
            WHERE m.MBR_IDSPN != m.MBR_ID
        )
        SELECT 
            mh.MBR_ID,
            mh.MBR_CODE,
            mh.MBR_NAME,
            mh.MBR_EMAIL,
            mh.MBR_IDSPN,
            IFNULL(tss.SLSSTRC_NAME, 'Trader') as SALES_TYPE
        FROM member_hierarchy mh
        LEFT JOIN tb_sales_structure tss ON tss.ID_SLSSTRC = mh.MBR_TYPE
        ORDER BY mh.level, mh.MBR_ID
    ";
    
    $result = $db->query($query);
    $members = $result->fetch_all(MYSQLI_ASSOC);
    
    // Convert to hierarchy structure
    $structures = Ib::toHierarcy($members);
    
    // Convert to tree view format
    function buildTreeArray(array $node): array {
        $entry = [
            'text' => $node['MBR_NAME'] . " (" . $node['MBR_CODE'] . ") - " . $node['SALES_TYPE'],
            'icon' => "fas fa-user",
            'expanded' => true,
            'nodes' => []
        ];

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {
                $entry['nodes'][] = buildTreeArray($child);
            }
        }

        return $entry;
    }
    
    $treeData = [];
    if (!empty($structures) && isset($structures['children'])) {
        foreach ($structures['children'] as $child) {
            $treeData[] = buildTreeArray($child);
        }
    } else if (!empty($structures)) {
        // If there's only root node
        $treeData[] = buildTreeArray($structures);
    }
    
    JsonResponse($treeData);

} catch (Exception $e) {
    JsonResponse([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
