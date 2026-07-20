<?php
use App\Factory\UtmHandler;

// Get filter parameters
$filterName = $_GET['filterName'] ?? '';
$filterEmail = $_GET['filterEmail'] ?? '';
$filterPhone = $_GET['filterPhone'] ?? '';
$filterStatus = $_GET['filterStatus'] ?? '';
$filterUTM = $_GET['filterUTM'] ?? '';
$filterUTMValue = $_GET['filterUTMValue'] ?? '';
$filterDateFrom = $_GET['filterDateFrom'] ?? '';
$filterDateTo = $_GET['filterDateTo'] ?? '';

$allowedUtmKeys = array_values(array_filter(UtmHandler::$utmKeys, fn($key) => $key !== 'referral'));
if (!empty($filterUTM) && !in_array($filterUTM, $allowedUtmKeys, true)) {
    $filterUTM = '';
}

// Build WHERE conditions
$whereConditions = [];

if (!empty($filterName)) {
    $whereConditions[] = 'tm1.MBR_NAME LIKE "%' . $db->real_escape_string($filterName) . '%"';
}

if (!empty($filterEmail)) {
    $whereConditions[] = 'tm1.MBR_EMAIL LIKE "%' . $db->real_escape_string($filterEmail) . '%"';
}

if (!empty($filterPhone)) {
    $whereConditions[] = 'tm1.MBR_PHONE LIKE "%' . $db->real_escape_string($filterPhone) . '%"';
}

if ($filterStatus !== '' && $filterStatus !== null) {
    $whereConditions[] = 'tm1.MBR_STS = ' . intval($filterStatus);
}

if (!empty($filterUTM)) {
    $safeKey = $db->real_escape_string($filterUTM);
    $whereConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tm1.MBR_METADATA, "$.'.$safeKey.'")) IS NOT NULL'
                        . ' AND JSON_UNQUOTE(JSON_EXTRACT(tm1.MBR_METADATA, "$.'.$safeKey.'")) != "null"'
                        . ' AND JSON_UNQUOTE(JSON_EXTRACT(tm1.MBR_METADATA, "$.'.$safeKey.'")) != ""';
}

if ($filterUTMValue !== '') {
    $safeValue = $db->real_escape_string($filterUTMValue);

    if (!empty($filterUTM)) {
        $safeKey = $db->real_escape_string($filterUTM);
        $whereConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tm1.MBR_METADATA, "$.'.$safeKey.'")) LIKE "%'.$safeValue.'%"';
    } elseif (!empty($allowedUtmKeys)) {
        $valueConditions = [];

        foreach ($allowedUtmKeys as $utmKey) {
            $safeKey = $db->real_escape_string($utmKey);
            $valueConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tm1.MBR_METADATA, "$.'.$safeKey.'")) LIKE "%'.$safeValue.'%"';
        }

        if (!empty($valueConditions)) {
            $whereConditions[] = '(' . implode(' OR ', $valueConditions) . ')';
        }
    }
}

if (!empty($filterDateFrom)) {
    $whereConditions[] = 'DATE(tm1.MBR_DATETIME) >= "' . $db->real_escape_string($filterDateFrom) . '"';
}

if (!empty($filterDateTo)) {
    $whereConditions[] = 'DATE(tm1.MBR_DATETIME) <= "' . $db->real_escape_string($filterDateTo) . '"';
}

$whereClause = '';
if (count($whereConditions) > 0) {
    $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
}

$dt->query('
    SELECT
        tm1.MBR_DATETIME,
        tm1.MBR_CODE,
        tm1.MBR_NAME,
        tm1.MBR_EMAIL,
        tm1.MBR_PHONE,
        tm1.MBR_METADATA,
        IFNULL(racc.REAL_ACCOUNT, "-") as REAL_ACCOUNT,
        IFNULL(upline.MBR_NAME, "-") as REFERRED_BY,
        tm1.MBR_STS,
        tm1.MBR_CODE as CODE
    FROM tb_member tm1
    LEFT JOIN tb_member upline ON (upline.MBR_ID = tm1.MBR_IDSPN AND upline.MBR_ID != 1000000000)
    LEFT JOIN (
        SELECT
            ACC_MBR,
            GROUP_CONCAT(ACC_LOGIN ORDER BY ACC_LOGIN SEPARATOR ",<br>") AS REAL_ACCOUNT
        FROM tb_racc
        WHERE ACC_DERE = 1
        AND ACC_STS = -1
        GROUP BY ACC_MBR
    ) racc ON (racc.ACC_MBR = tm1.MBR_ID)
    ' . $whereClause . '
');

$dt->edit('MBR_STS', function($data){
    return '
        <div class="text-center">
            '.App\Models\User::status($data['MBR_STS'])['html'].'
        </div>
    ';
});

$dt->hide('MBR_EMAIL');
$dt->hide('MBR_PHONE');
$dt->edit('MBR_NAME', function($col) {
    return '
        <p class="fw-bold">'.$col['MBR_NAME'].'</p>
        <p class="mb-0">'.$col['MBR_EMAIL'].'</p>
        <p class="mb-0">'.$col['MBR_PHONE'].'</p>
    '; 
});

$dt->edit('MBR_METADATA', function($col) {
    $metadata = json_decode($col['MBR_METADATA'] ?? '{}', true);
    $metaKey = UtmHandler::$utmKeys;
    $html = '';

    foreach ($metaKey as $key) {
        if (isset($metadata[$key])) {
            $html .= '<span class="text-start badge bg-dark text-white" style="width: fit-content;">'.ucwords(str_replace('_', ' ', $key)).': '.htmlspecialchars($metadata[$key] ?? '-', ENT_QUOTES, 'UTF-8').'</span>';
        }
    }
    
    return '
        <div class="d-flex flex-column flex-wrap gap-1">'.$html.'</div>
    ';
});

$dt->edit('CODE', function($data){
    return '<div class="text-center action" data-code="'.$data['MBR_CODE'].'"></div>';
});

echo $dt->generate()->toJson();