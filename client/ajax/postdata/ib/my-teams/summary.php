<?php

$memberId = (int) $user['MBR_ID'];

$sql = $db->query("
	WITH RECURSIVE member_hierarchy AS (
		SELECT
			MBR_ID,
			MBR_IDSPN,
			MBR_STS
		FROM tb_member
		WHERE MBR_IDSPN = {$memberId}
		UNION ALL
		SELECT
			m.MBR_ID,
			m.MBR_IDSPN,
			m.MBR_STS
		FROM tb_member m
		INNER JOIN member_hierarchy mh ON m.MBR_IDSPN = mh.MBR_ID
	)

	SELECT
		SUM(CASE WHEN tsc.SLSCONDITION_STS = -1 THEN 1 ELSE 0 END) AS total_active,
		SUM(CASE WHEN tsc.SLSCONDITION_STS = 1 THEN 1 ELSE 0 END) AS total_rejected,
		SUM(CASE WHEN tsc.SLSCONDITION_STS = 0 THEN 1 ELSE 0 END) AS total_pending,
		SUM(CASE WHEN tsc.SLSCONDITION_STS IS NULL THEN 1 ELSE 0 END) AS total_not_activated
	FROM member_hierarchy as mh
	JOIN (
		SELECT
			tr.ID_ACC,
			tr.ACC_MBR
		FROM tb_racc as tr
		WHERE tr.ACC_STS = -1
		AND tr.ACC_DERE = 1
		AND tr.ACC_LOGIN != 0
	) as acc ON (acc.ACC_MBR = mh.MBR_ID)
	LEFT JOIN tb_sales_conditions as tsc ON (tsc.SLSCONDITION_IDACC = acc.ID_ACC)
	WHERE mh.MBR_STS != 1
");

if (!$sql) {
	JsonResponse([
		'success' => false,
		'message' => 'Failed to load summary data',
		'data' => []
	]);
}

$row = $sql->fetch_assoc() ?: [];
$totalActive = (int) ($row['total_active'] ?? 0);
$totalRejected = (int) ($row['total_rejected'] ?? 0);
$totalPending = (int) ($row['total_pending'] ?? 0);
$totalNotActivated = (int) ($row['total_not_activated'] ?? 0);

JsonResponse([
	'success' => true,
	'message' => 'Summary loaded',
	'data' => [
		'totalActive' => $totalActive,
		'totalRejected' => $totalRejected,
		'totalPending' => $totalPending,
		'totalNotActivated' => $totalNotActivated
	]
]);
