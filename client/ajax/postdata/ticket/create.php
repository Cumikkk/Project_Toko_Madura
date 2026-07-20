<?php

use App\Models\Helper;
use App\Models\Logger;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
if(empty($data['topic'])) {
    JsonResponse([
        'success' => false,
        'message' => "Mohon untuk memilih topik",
        'data' => []
    ]);
}

if(!in_array($data['topic'], \App\Models\Ticket::topicList())) {
    JsonResponse([
        'success' => false,
        'message' => "Topik yang dipilih tidak valid",
        'data' => []
    ]);
}

if(empty($data['subject'])) {
    JsonResponse([
        'success' => false,
        'message' => "Kolom Subjek perlu diisi",
        'data' => []
    ]);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** Insert */
$ticketCode = uniqid();
$datetime = date("Y-m-d H:i:s");
$insert = Database::insert("tb_ticket", [
    'TICKET_CODE' => $ticketCode,
    'TICKET_MBR' => $user['MBR_ID'],
    'TICKET_TOPIC' => $data['topic'],
    'TICKET_SUBJECT' => $data['subject'],
    'TICKET_STS' => -1,
    'TICKET_DATETIME' => $datetime
]);

if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Gagal membuat ticket",
        'data' => []
    ]);
}

/** first message */
if(!empty($data['desc'])) {
    $insertFirstMessage = Database::insert("tb_ticket_detail", [
        'TDETAIL_TCODE' => $ticketCode,
        'TDETAIL_FROM' => $user['MBR_ID'],
        'TDETAIL_TYPE' => "member",
        'TDETAIL_CONTENT_TYPE' => "message",
        'TDETAIL_CONTENT' => $data['desc'],
        'TDETAIL_DATETIME' => $datetime,
    ]);

    if(!$insertFirstMessage) {
        $db->rollback();
        JsonResponse([
            'success' => false,
            'message' => "Gagal membuat pesan",
            'data' => []
        ]);
    }
}

Logger::client_log([
    'mbrid' => $user['MBR_ID'],
    'module' => "ticket",
    'message' => "Membua ticket baru dengan kode: $ticketCode, subjek: ".$data['subject'].", topik: ".$data['topic'],
    'data' => $data
]);

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => [
        'redirect' => "/ticket/detail?code=$ticketCode"
    ]
]);