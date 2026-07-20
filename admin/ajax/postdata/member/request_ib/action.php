<?php

use App\Models\AccountType;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\Logger;
use App\Models\User;
use App\Library\Sales\SalesMain;
use App\Models\Refferal;
use Config\Core\Database;
use Config\Core\EmailSender;

if(!$adminPermissionCore->hasPermission($authorizedPermission, $url)) {
    JsonResponse([
        'success' => false,
        'message' => "Permission Denied",
        'data' => []
    ]);
}

$data = Helper::getSafeInput($_POST);
$required = [
    'type' => "Type",
    'id' => "ID",
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} is required",
            'data' => []
        ]);
    }
}

/** check ID */
$ibData = Ib::findById($data['id']);
if(!$ibData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid ID",
        'data' => []
    ]);
}

/** check Type */
$type = strtolower($data['type']);
if(!in_array($type, ['accept', 'reject'])) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Type",
        'data' => []
    ]);
}

/** check user */
$userData = User::findByMemberId($ibData['BECOME_MBR']);
if(!$userData) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid User",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

$status = ($type == "accept")? -1 : 1;
$note = $data['note'] ?? "";


/** update becomeib */
$update = Database::update("tb_become_ib", ['BECOME_STS' => $status, 'BECOME_NOTE' => $note], ['ID_BECOME' => $ibData['ID_BECOME']]);
if(!$update) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed to update status",
        'data' => []
    ]);
}

/** Update tb_member */
if($type == "accept") {
    if(isset($data['upline'])){        
        $MBRSPN_TYPE = User::findByCode($data['upline']);
        $MBR_TYPE = SalesMain::getUserType($MBRSPN_TYPE['MBR_TYPE']);
        
        $query = $db->query("SELECT ID_SLSSTRC FROM tb_sales_structure WHERE SLSSTRC_UP = ".$MBRSPN_TYPE['MBR_TYPE']." AND SLSSTRC_LEVEL = ".$MBR_TYPE->level()."+1 LIMIT 1");
        $row = $query->fetch_assoc();
        $updateMember = Database::update("tb_member", ['MBR_IDSPN' => $MBRSPN_TYPE['MBR_ID'], 'MBR_TYPE' => $row['ID_SLSSTRC']], ['MBR_ID' => $ibData['BECOME_MBR']]);
        if(!$updateMember) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Invalid Type",
                'data' => []
            ]);
        }

    } else if(isset($data['structure'])){
        $query = $db->query("SELECT ID_SLSSTRC FROM tb_sales_structure WHERE MD5(MD5(ID_SLSSTRC)) = '".$data['structure']."' LIMIT 1");
        $row = $query->fetch_assoc();

        $updateMember = Database::update("tb_member", ['MBR_TYPE' => $row['ID_SLSSTRC']], ['MBR_ID' => $ibData['BECOME_MBR']]);
        if(!$updateMember) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Invalid Type",
                'data' => []
            ]);
        }
    } else {
        $MBRSPN_TYPE = User::findUplineByMembeId($userData['MBR_IDSPN']);
        $MBR_TYPE = SalesMain::getUserType($MBRSPN_TYPE['MBR_TYPE']);
        
        $query = $db->query("SELECT ID_SLSSTRC FROM tb_sales_structure WHERE SLSSTRC_UP = ".$MBRSPN_TYPE['MBR_TYPE']." AND SLSSTRC_LEVEL = ".$MBR_TYPE->level()."+1 LIMIT 1");
        $row = $query->fetch_assoc();
        $updateMember = Database::update("tb_member", ['MBR_TYPE' => $row['ID_SLSSTRC']], ['MBR_ID' => $ibData['BECOME_MBR']]);
        if(!$updateMember) {
            $db->rollback();
            JsonResponse([
                'success' => false,
                'message' => "Invalid Type",
                'data' => []
            ]);
        }
    }

    /** Update list produk */
    $referral = [];
    $getProducts = AccountType::findByType(["ULTRA-LOW", "PRIME", "PRO", "PRO-ELITE", "STANDARD-PRO"]);
    if($getProducts) {
        foreach($getProducts as $product) {
            $type = strtoupper($product['RTYPE_TYPE'] ?? "-");
            $search = array_search($type, array_column($referral, 'RTYPE_TYPE'));
            if($search !== false && $referral[$search]['commission'] == $product['RTYPE_KOMISI']) {
                continue;
            }

            $referral[] = [
                'type' => $type,
                'commission' => $product['RTYPE_KOMISI']
            ];
        }
    }

    /** Update default product */
    $productSuffix = implode(",", array_map(fn($ar): string => $ar['RTYPE_SUFFIX'], $getProducts));
    $updateDefaultProduct = Database::update("tb_member", ["MBR_SUFFIX" => $productSuffix, "MBR_WALLET" => 1], ['MBR_ID' => $userData['MBR_ID']]);
    if(!$updateDefaultProduct) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Failed to set default product",
            'data' => []
        ]);
    }

    /** Notifikasi Email */
    $emailData = [
        'subject' => "Permintaan IB Anda Telah Disetujui",
        'name' => $userData['MBR_NAME'],
        'referral' => $referral,
    ];

    $emailSender = EmailSender::init(['email' => $userData['MBR_EMAIL'], 'name' => $userData['MBR_NAME']]);
    $emailSender->useFile("become-ib", $emailData);
    $emailSender->send();

}else {
    /** Notifikasi Email Reject */
    $emailData = [
        'subject' => "Permintaan IB Anda Telah Ditolak",
        'name' => $userData['MBR_NAME'],
        'note' => $note
    ];

    $emailSender = EmailSender::init(['email' => $userData['MBR_EMAIL'], 'name' => $userData['MBR_NAME']]);
    $emailSender->useFile("become-ib-reject", $emailData);
    $emailSender->send();

}

Logger::admin_log([
    'admid' => $user['ADM_ID'],
    'module' => "request_ib",
    'message' => "{$type} request ib for id: " . $data['id'],
    'data' => $data
]);

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => []
]);