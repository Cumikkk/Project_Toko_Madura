<?php

use App\Models\Helper;
use App\Models\Ticket;
use Config\Core\Database;

/** check code */
$ticketCode = Helper::form_input($_POST['code']);
$ticket = Ticket::findByCode($ticketCode);
if(!$ticket) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** Check status */
if($ticket['TICKET_STS'] != -1) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status",
        'data' => []
    ]);
}

$update = Database::update("tb_ticket", ['TICKET_STS' => 1], ['TICKET_CODE' => $ticketCode]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Penutupan tiket gagal",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);