<?php
use Config\Core\SystemInfo;

// Get filter parameters
$filterDateFrom = $_GET['filterDateFrom'] ?? '';
$filterDateTo = $_GET['filterDateTo'] ?? '';
$whereConditions = [];
// Build WHERE conditions: keep legacy conditions, then append new filters
if (!empty($filterDateFrom)) {
    $whereConditions[] = 'DATE(IT_DATETIME) >= "' . $db->real_escape_string($filterDateFrom) . '"';
}

if (!empty($filterDateTo)) {
    $whereConditions[] = 'DATE(IT_DATETIME) <= "' . $db->real_escape_string($filterDateTo) . '"';
}

$whereClause = !empty($whereConditions) ? ' WHERE ' . implode(' AND ', $whereConditions) : '';
$dbmetasrv = SystemInfo::app('DB_METALIVE');
    $dt->query("
        SELECT 
            IT_DATETIME,
            from_tb_member.MBR_NAME AS IT_NAME,
            from_tb_member.MBR_EMAIL AS IT_EMAIL,
            IT_FROM AS FROM_LOGIN,
            IT_TO AS TO_LOGIN,
            IT_AMOUNT
        FROM tb_internal_transfer
        JOIN tb_racc from_tb_racc ON (from_tb_racc.ACC_LOGIN = tb_internal_transfer.IT_FROM)
        JOIN tb_member from_tb_member ON (from_tb_member.MBR_ID = from_tb_racc.ACC_MBR)
        ". $whereClause ."
        ORDER BY IT_DATETIME DESC
    ");

    echo $dt->generate()->toJson();