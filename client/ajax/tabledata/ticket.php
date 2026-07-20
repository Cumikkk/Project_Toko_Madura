<?php
$dt->query("
    SELECT
        TICKET_DATETIME,
        TICKET_CODE,
        TICKET_TOPIC,
        TICKET_SUBJECT,
        TICKET_STS
    FROM tb_ticket 
    WHERE TICKET_MBR = ".$user['MBR_ID']." 
");

$dt->hide("TICKET_TOPIC");

$dt->edit("TICKET_SUBJECT", function($col) {
    return sprintf("[%s] %s", ucwords($col['TICKET_TOPIC'] ?? ""), $col['TICKET_SUBJECT']);
});

$dt->edit("TICKET_CODE", function($col) {
    return '<a href="/ticket/detail?code='.$col['TICKET_CODE'].'">'.$col['TICKET_CODE'].'</a>';
});

$dt->edit("TICKET_STS", function($col) {
    return App\Models\Ticket::$status[ $col['TICKET_STS'] ]['html'] ?? "";
});

echo $dt->generate()->toJson();