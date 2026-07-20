<?php
    $dt->query('
        SELECT
            tb_ticket.TICKET_DATETIME,
            (
                SELECT
                    tb_ticket_detail.TDETAIL_DATETIME
                FROM tb_ticket_detail
                WHERE tb_ticket_detail.ID_TDETAIL = tb_ticket.ID_TICKET
                ORDER BY tb_ticket_detail.ID_TDETAIL DESC
                LIMIT 1
            ) AS LST_CONFR,
            tb_member.MBR_EMAIL,
            tb_ticket.TICKET_CODE,
            tb_ticket.TICKET_TOPIC,
            tb_ticket.TICKET_SUBJECT,
            IF(tb_ticket.TICKET_STS = -1, "Open", 
                IF(tb_ticket.TICKET_STS = 1, "Closed", "Unknown")
            ) AS TICKET_STS,
            MD5(MD5(tb_ticket.ID_TICKET)) AS ID_TIC
        FROM tb_ticket
        JOIN tb_member
        ON(tb_ticket.TICKET_MBR = tb_member.MBR_ID)
    ');

    $dt->edit('TICKET_TOPIC', function($data){
        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            App\Models\Ticket::topicColor($data['TICKET_TOPIC']),
            ucfirst($data['TICKET_TOPIC'])
        );
    });

    $dt->edit('ID_TIC', function($data){
        return '
            <div class="text-center">
                <a href="/support/ticket/detail/'.$data["ID_TIC"].'" class="btn btn-sm btn-info">Detail</a>
            </div>
        ';
    });

    echo $dt->generate()->toJson();