<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\User;

$code = Helper::form_input($_POST['code'] ?? '');
$type = Helper::form_input($_POST['type'] ?? 'downline');
$userdata = User::findByCode($code);
if(!$userdata) {
    JsonResponse([
        'success' => false,
        'message' => "Member not found",
        'data' => []
    ]);
}

$salesType = $salesType = SalesMain::getUserType($userdata['MBR_TYPE']);

// Function to build tree structure
function buildTree($structures, $parentId = null) {
    $tree = [];
    
    foreach ($structures as $member) {
        // Check if this member's upline (MBR_IDSPN) matches the parentId
        if ($member['MBR_IDSPN'] == $parentId) {
            // Find children for this member
            $children = buildTree($structures, $member['MBR_ID']);
            
            // Build member node
            $node = [
                'id' => $member['MBR_CODE'],
                'name' => $member['MBR_NAME'],
                'email' => $member['MBR_EMAIL'], 
                'type' => $member['SALES_TYPE'],
                'refferalCode' => $member['MBR_CODE'] ?? '-',
                'children' => $children
            ];
            
            $tree[] = $node;
        }
    }
    
    return $tree;
}

$structures = [];
$rootUser = [];
switch($type) {
    case "upline" :
        // Add the main user as root node
        $structures = Ib::getNetworks($userdata['MBR_ID'], 'upline');
        $isAdmin = array_search(1000000000, array_column($structures, 'MBR_ID'));
        if($isAdmin !== false) {
            unset($structures[$isAdmin]);
            $structures = array_values($structures);
        }

        $endOfStructures = end($structures);
        if($endOfStructures) {
            $rootUser = [
                'id' => $endOfStructures['MBR_CODE'],
                'name' => $endOfStructures['MBR_NAME'],
                'email' => $endOfStructures['MBR_EMAIL'],
                'type' => $endOfStructures['SALES_TYPE'] ?? "Trader",
                'refferalCode' => $userdata['MBR_CODE'] ?? '-',
                'children' => buildTree($structures, $endOfStructures['MBR_ID'] ?? null)
            ];
        }

        break;
    case "downline" :
        // Add the main user as root node
        $structures = Ib::getNetworks($userdata['MBR_ID'], 'downline');
        $rootUser = [
            'id' => $userdata['MBR_CODE'],
            'name' => $userdata['MBR_NAME'],
            'email' => $userdata['MBR_EMAIL'],
            'type' => $salesType? $salesType->salesDetail['SLSSTRC_NAME'] : "Trader",
            'refferalCode' => $userdata['MBR_CODE'] ?? '-',
            'children' => buildTree($structures, $userdata['MBR_ID'])
        ];
        break;
}

if(!$structures) {
    JsonResponse([
        'success' => true,
        'message' => "No $type data",
        'data' => []
    ]);
}



JsonResponse([
    'success' => true,
    'message' => "Tree data loaded successfully",
    'data' => [$rootUser]
]);
