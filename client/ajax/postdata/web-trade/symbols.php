<?php

use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Helper;

$accountLogin = Helper::form_input($_GET['account'] ?? 0);

/** Check Account */
$account = Account::findByLogin($accountLogin);
if(empty($account) || $account['ACC_MBR'] != $user['MBR_ID']) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Account",
        'data' => []
    ]);
}

$apiTerminal = MetatraderFactory::apiTerminal($account['RTYPE_SERVER']);
$token = MetatraderFactory::autoConnect($accountLogin);
if(!$token) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Token Connection",
        'require_reset' => true,
        'data' => []
    ]);
}

$symbols = $apiTerminal->symbols(['id' => $token]);
if(!$symbols || !is_object($symbols)) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Data",
        'data' => []
    ]);
}

$sqlGetSymbolCategory = $db->query("
    SELECT
        SYMCAT_NAME,
        GROUP_CONCAT(SYM_NAME SEPARATOR ',') as SYMBOLS
    FROM tb_symbolcat tsc
    JOIN tb_symbol ts ON (ts.ID_SYMCAT = tsc.ID_SYMCAT)
    GROUP BY ts.ID_SYMCAT
");

$result = [];
foreach($sqlGetSymbolCategory->fetch_all(MYSQLI_ASSOC) as $category) {
    foreach(explode(",", $category['SYMBOLS']) as $symbol) {
        $existsOnApi = false;
        foreach($symbols->data as $sm) {
            if($sm->currency == $symbol) {
                $existsOnApi = $sm;
                break;
            }
        }

        if($existsOnApi !== FALSE) {
            // Remove suffix after dot (e.g., XAUUSD.db -> XAUUSD)
            $cleanSymbol = explode('.', $existsOnApi->currency)[0] ?? "-";
            $result[] = [
                'group' => $category['SYMCAT_NAME'],
                'symbol' => $existsOnApi->currency,
                'symbol_alias' => $cleanSymbol,
                'contract_size' => $existsOnApi->contractSize,
                'spread' => $existsOnApi->spread,
                'digits' => $existsOnApi->digits,
                'trademode' => $existsOnApi->trademode,
                'volume_min' => $existsOnApi->volumeMin,
                'volume_max' => $existsOnApi->volumeMax,
                'favorite' => false,
                'bid' => 0,
                'bid_change' => 0,
                'ask' => 0,
                'ask_change' => 0,
            ];
        }
    }
}

JsonResponse([
    'success' => true,
    'message' => 'Symbols fetched successfully',
    'data' => $result
]);