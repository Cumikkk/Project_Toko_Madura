<?php

    use App\Factory\UtmHandler;
    use App\Models\FileUpload;
    use App\Models\Helper;

    // Get filter parameters
    $filterDateFrom = $_GET['filterDateFromAccounting'] ?? '';
    $filterDateTo = $_GET['filterDateToAccounting'] ?? '';

    // Build WHERE conditions: keep legacy conditions, then append new filters
    $whereConditions = [
        'tb_dpwd.DPWD_STS = 0',
        'tb_dpwd.DPWD_STSVER = -1',
        'tb_dpwd.DPWD_TYPE = 2'
    ];

    if (!empty($filterDateFrom)) {
        $whereConditions[] = 'DATE(tb_dpwd.DPWD_DATETIME) >= "' . $db->real_escape_string($filterDateFrom) . '"';
    }

    if (!empty($filterDateTo)) {
        $whereConditions[] = 'DATE(tb_dpwd.DPWD_DATETIME) <= "' . $db->real_escape_string($filterDateTo) . '"';
    }

    $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);

    $dt->query('
        SELECT
            tb_dpwd.DPWD_DATETIME,
            tb_racc.ACC_FULLNAME,
            tb_racc.ACC_LOGIN,
            tb_dpwd.DPWD_CODE,
            tb_dpwd.DPWD_BANKSRC,
            tb_dpwd.DPWD_AMOUNT,
            tb_dpwd.DPWD_AMOUNT_SOURCE,
            tb_member.MBR_METADATA,
            MD5(MD5(tb_dpwd.ID_DPWD)) AS ID_DPWD,
            JSON_OBJECT(
                "acc-login", tb_racc.ACC_LOGIN,
                "acc-name", tb_racc.ACC_FULLNAME,
                "acc-amntl", CAST(CONCAT("Rp. ", FORMAT(tb_dpwd.DPWD_AMOUNT, 0)) AS CHAR),
                "acc-rate", CAST(IF(tb_racctype.RTYPE_ISFLOATING != 1, FORMAT(tb_racctype.RTYPE_RATE, 0), 0) AS CHAR),
                "acc-amnt", CAST(CONCAT("$. ", FORMAT(tb_dpwd.DPWD_AMOUNT_SOURCE, 2)) AS CHAR),
                "acc-bksrc", tb_dpwd.DPWD_BANKSRC,
                "acc-bksrc-lst", REGEXP_REPLACE(tb_dpwd.DPWD_BANKSRC, "^[^/]*\/", ""),
                "acc-bkdst", tb_dpwd.DPWD_BANK,
                "acc-dpx", CAST(MD5(MD5(tb_dpwd.ID_DPWD)) AS CHAR)
            ) AS JSNDT,
            tb_racctype.RTYPE_ISFLOATING
        FROM tb_member
        JOIN tb_racc
        JOIN tb_dpwd
        JOIN tb_racctype
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR
        AND tb_dpwd.DPWD_MBR = tb_member.MBR_ID
        AND tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
        AND tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        '.$whereClause.'
    ');

    $dt->hide('JSNDT');
    $dt->hide('DPWD_AMOUNT_SOURCE');
    $dt->hide('RTYPE_ISFLOATING');
    $dt->edit('DPWD_AMOUNT', function($data){
        if($data["RTYPE_ISFLOATING"] == 1){
            return '
                <div class="text-end">
                    <p class="mb-0">$'.Helper::formatCurrency($data["DPWD_AMOUNT_SOURCE"]).'</p>
                </div>
            ';
        }else{
            return '
                <div class="text-end">
                    <p class="mb-0">Rp '.Helper::formatCurrency($data["DPWD_AMOUNT"]).'</p>
                    <p class="mb-0">$'.Helper::formatCurrency($data["DPWD_AMOUNT_SOURCE"]).'</p>
                </div>
            ';
        }
    });

    $dt->edit('DPWD_BANKSRC', function($data) {
        $explode = explode('/', $data['DPWD_BANKSRC']);
        return '
            <div class="text-start">
                <p class="mb-0"><strong>'.($explode[0] ?? '').'</strong></p>
                <p class="mb-0">'.($explode[1] ?? '').'</p>
                <p class="mb-0">'.($explode[2] ?? '').'</p>
            </div>
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

    $dt->edit('ID_DPWD', function($data){
        $dataDpwd = base64_encode(json_encode([
            'code' => $data['ID_DPWD'],
            'detail' => $data['JSNDT']
        ]));

        return '
            <div class="d-flex justify-content-center gap-1 action" data-data="'.$dataDpwd.'">
            </div>
        ';
    });

    echo $dt->generate()->toJson();
