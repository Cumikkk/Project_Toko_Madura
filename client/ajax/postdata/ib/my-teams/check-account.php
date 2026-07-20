<?php

use App\Models\Helper;
use App\Models\User;
use App\Library\Sales\SalesMain;

$data = Helper::getSafeInput($_POST);
$required = [
	'email' => 'Email',
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

$memberEmail = Helper::form_input($_POST['email']);
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

$salesData = SalesMain::getUserType($memberData['MBR_TYPE']);
if(!$salesData) {
	JsonResponse([
		'success' => false,
		'message' => 'Client is not eligible to request account condition',
		'data' => []
	]);
}

$data = [
	'email' => $memberEmail,
	'posisi' => $salesData->salesDetail['SLSSTRC_NAME'] ?? ''
];

JsonResponse([
	'success' => true,
	'message' => 'Client found successfully',
	'data' => $data
]);
