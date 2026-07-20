<?php

use App\Models\Account;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\User;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$required = [
	'racc' => 'Account',
	'slscondition_branch' => 'Branch'
];

foreach ($required as $key => $label) {
	if (empty($data[$key])) {
		JsonResponse([
			'success' => false,
			'message' => "{$label} is required",
			'data' => []
		]);
	}
}


$members = $_POST['email_client'] ?? [];
$forex = $_POST['slscom_forex'] ?? [];
$gold = $_POST['slscom_gold'] ?? [];
$index = $_POST['slscom_index'] ?? [];

if (!is_array($members) || !is_array($forex) || !is_array($gold) || !is_array($index)) {
	JsonResponse([
		'success' => false,
		'message' => 'Invalid commission data format',
		'data' => []
	]);
}

$account = Account::check_account_id($data['racc']);
if (!$account) {
	JsonResponse([
		'success' => false,
		'message' => 'Account not found',
		'data' => []
	]);
}

$member = User::findByMemberIdHash($data['mbr']);
if (!$member) {
    JsonResponse([
        'success' => false,
        'message' => 'Member not found',
        'data' => []
    ]);
}

if ($member['MBR_STS'] == 1) {
    JsonResponse([
        'success' => false,
        'message' => 'Member is disabled',
        'data' => []
    ]);
}

$getDataKomisi = Account::realAccountDetail($data['racc']);
if(!$getDataKomisi) {
	JsonResponse([
		'success' => false,
		'message' => 'Failed to retrieve account data',
		'data' => []
	]);
}
$maxKomisi = Helper::stringTonumber($getDataKomisi['RTYPE_KOMISI'] ?? 0);
if ($maxKomisi < 0) {
	JsonResponse([
		'success' => false,
		'message' => 'Invalid max commission value',
		'data' => []
	]);
}

$branch = $data['slscondition_branch'] ?? '';
if (empty($branch)) {
	JsonResponse([
		'success' => false,
		'message' => 'Branch is required',
		'data' => []
	]);
}

$rows = [];
$memberIds = [];
$totalForex = 0;
$totalGold = 0;
$totalIndex = 0;
foreach ($members as $i => $memberEmail) {
	$memberEmail = Helper::form_input($memberEmail);
	if (empty($memberEmail)) {
		JsonResponse([
			'success' => false,
			'message' => 'Invalid client email',
			'data' => []
		]);
	}

	$memberData = User::findByEmail($memberEmail);
	if(!$memberData) {
		JsonResponse([
			'success' => false,
			'message' => 'Client not found for email: ' . htmlspecialchars($memberEmail),
			'data' => []
		]);
	}

	if($memberData['MBR_STS'] == 1) {
		JsonResponse([
			'success' => false,
			'message' => 'Client is disabled for email: ' . htmlspecialchars($memberEmail),
			'data' => []
		]);
	}

	if($member['MBR_EMAIL'] === $memberEmail) {
		JsonResponse([
			'success' => false,
			'message' => 'Client email cannot be the same as the member email: ' . htmlspecialchars($memberEmail),
			'data' => []
		]);
	}

	if (isset($memberIds[$memberData['MBR_ID']])) {
		JsonResponse([
			'success' => false,
			'message' => 'Duplicate member email: ' . htmlspecialchars($memberEmail),
			'data' => []
		]);
	}
	$memberIds[$memberData['MBR_ID']] = true;

	$forexValue = Helper::stringTonumber($forex[$i] ?? 0);
	$goldValue = Helper::stringTonumber($gold[$i] ?? 0);
	$indexValue = Helper::stringTonumber($index[$i] ?? 0);

	if ($forexValue < 0 || $goldValue < 0 || $indexValue < 0) {
		JsonResponse([
			'success' => false,
			'message' => 'Invalid commission value',
			'data' => []
		]);
	}

	$totalForex += $forexValue;
	$totalGold += $goldValue;
	$totalIndex += $indexValue;

	$rows[] = [
		'SLSCOM_MBR' => (int) $memberData['MBR_ID'],
		'SLSCOM_IDACC' => $account['ID_ACC'],
		'SLSCOM_FOREX' => $forexValue,
		'SLSCOM_GOLD' => $goldValue,
		'SLSCOM_INDEX' => $indexValue
	];
}

if ($totalForex > $maxKomisi || $totalGold > $maxKomisi || $totalIndex > $maxKomisi) {
	JsonResponse([
		'success' => false,
		'message' => 'Total commission exceeds max',
		'data' => []
	]);
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

$conditionData = [
	'SLSCONDITION_IDACC' => $account['ID_ACC'],
	'SLSCONDITION_MBR' => $member['MBR_ID'],
	'SLSCONDITION_PARTNER' => $user['MBR_ID'],
	'SLSCONDITION_CHARGE' => $account['RTYPE_KOMISI'],
	'SLSCONDITION_SALES_NAME' => $data['slscondition_sales'] ?? '',
	'SLSCONDITION_BRANCH' => $branch,
	'SLSCONDITION_STS' => 0,
	'SLSCONDITION_DATETIME' => date("Y-m-d H:i:s")
];

$sqlCheckCondition = $db->query("SELECT ID_SLSCONDITION FROM tb_sales_conditions WHERE SLSCONDITION_IDACC = {$account['ID_ACC']} LIMIT 1");
if ($sqlCheckCondition && $sqlCheckCondition->num_rows > 0) {
	$updateCondition = Database::update("tb_sales_conditions", $conditionData, [
		'SLSCONDITION_IDACC' => $account['ID_ACC']
	]);

	if (!$updateCondition) {
		$db->rollback();
		JsonResponse([
			'success' => false,
			'message' => 'Failed to save account conditions',
			'data' => []
		]);
	}
    
    $salesCondition = $sqlCheckCondition->fetch_assoc();
} else {
    $insertCondition = Database::insert("tb_sales_conditions", $conditionData);
	if (!$insertCondition) {
		$db->rollback();
		JsonResponse([
			'success' => false,
			'message' => 'Failed to save account conditions',
			'data' => []
		]);
	}
    $salesCondition = [];
    $salesCondition['ID_SLSCONDITION'] = $db->insert_id;
}

$columns = [
	'SLSCOM_MBR',
	'SLSCOM_IDACC',
	'SLSCOM_IDCONDITION',
	'SLSCOM_FOREX',
	'SLSCOM_GOLD',
	'SLSCOM_INDEX',
	'SLSCOM_DATETIME'
];

$types = '';
$values = [];
$placeholders = [];
$now = date("Y-m-d H:i:s");

foreach ($rows as $row) {
	$types .= 'iiiddds';
	$placeholders[] = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
	array_push(
		$values,
		$row['SLSCOM_MBR'],
		$account['ID_ACC'],
		$salesCondition['ID_SLSCONDITION'],
		$row['SLSCOM_FOREX'],
		$row['SLSCOM_GOLD'],
		$row['SLSCOM_INDEX'],
		$now
	);
}

$sqlBuilder = "INSERT INTO tb_sales_commission (" . implode(',', $columns) . ") VALUES " . implode(', ', $placeholders) .
	" ON DUPLICATE KEY UPDATE SLSCOM_FOREX = VALUES(SLSCOM_FOREX), SLSCOM_GOLD = VALUES(SLSCOM_GOLD), SLSCOM_INDEX = VALUES(SLSCOM_INDEX), SLSCOM_DATETIME = VALUES(SLSCOM_DATETIME)";

$stmt = $db->prepare($sqlBuilder);
if (!$stmt) {
	$db->rollback();
	JsonResponse([
		'success' => false,
		'message' => 'Failed to prepare commission save',
		'data' => []
	]);
}
$stmt->bind_param($types, ...$values);
if (!$stmt->execute()) {
	$db->rollback();
	JsonResponse([
		'success' => false,
		'message' => 'Failed to save commission data',
		'data' => []
	]);
}

$db->commit();

Logger::client_log([
	'mbrid' => $user['MBR_ID'] ?? 0,
	'module' => 'account_commission',
	'message' => 'Submit account commission',
	'data' => [
		'account' => $account['ID_ACC'],
		'rows' => $rows
	]
]);

JsonResponse([
	'success' => true,
	'message' => 'Commission data saved successfully',
	'data' => []
]);
