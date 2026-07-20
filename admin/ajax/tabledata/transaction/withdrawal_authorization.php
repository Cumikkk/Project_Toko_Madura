<?php

    use App\Factory\UtmHandler;
    use App\Models\FileUpload;
    use App\Models\Helper;
    use App\Models\Dpwd;

    // Get filter parameters
    $filterDateFrom = $_GET['filterDateFromAuthorization'] ?? '';
    $filterDateTo = $_GET['filterDateToAuthorization'] ?? '';
    
    // Build WHERE conditions: keep legacy conditions, then append new filters
    $whereConditions = [
        'tb_dpwd.DPWD_STS = 0',
        'tb_dpwd.DPWD_STSVER = 0',
        'tb_dpwd.DPWD_TYPE = '. Dpwd::$typeWithdrawal
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
            tb_racctype.RTYPE_CURR,
            tb_dpwd.DPWD_CODE,
            tb_dpwd.DPWD_BANKSRC,
            tb_dpwd.DPWD_AMOUNT,
            tb_dpwd.DPWD_AMOUNT_SOURCE,
            tb_dpwd.DPWD_CURR_TO,
            tb_member.MBR_METADATA,
            MD5(MD5(tb_dpwd.ID_DPWD)) AS ID_DPWD,
            JSON_OBJECT(
                "auth-login", tb_racc.ACC_LOGIN,
                "auth-name", tb_racc.ACC_FULLNAME,
                "auth-amntl", CAST(CONCAT("Rp. ", FORMAT(tb_dpwd.DPWD_AMOUNT, 0)) AS CHAR),
                "auth-rate", CAST(IF(tb_racctype.RTYPE_ISFLOATING != 1, FORMAT(tb_racctype.RTYPE_RATE, 0), 0) AS CHAR),
                "auth-amnt", CAST(CONCAT("$. ", FORMAT(tb_dpwd.DPWD_AMOUNT_SOURCE, 2)) AS CHAR),
                "auth-bksrc", tb_dpwd.DPWD_BANKSRC,
                "auth-bksrc-lst", REGEXP_REPLACE(tb_dpwd.DPWD_BANKSRC, "^[^/]*\/", ""),
                "auth-bkdst", tb_dpwd.DPWD_BANK,
                "auth-bkdst-lst", REGEXP_REPLACE(tb_dpwd.DPWD_BANK, "^[^/]*\/", ""),
                "auth-dpx", CAST(MD5(MD5(tb_dpwd.ID_DPWD)) AS CHAR)
            ) AS JSNDT
        FROM tb_member
        JOIN tb_racc
        JOIN tb_racctype
        JOIN tb_dpwd
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR
        AND tb_dpwd.DPWD_MBR = tb_member.MBR_ID
        AND tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
        AND tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        '. $whereClause .'
    ');

    $dt->hide('JSNDT');
    $dt->hide('DPWD_AMOUNT_SOURCE');
    $dt->hide('DPWD_CURR_TO');
    $dt->hide('RTYPE_CURR');
    $dt->edit('DPWD_AMOUNT', function($data){
        $amountIdr = '<p class="mb-0">Rp '.Helper::formatCurrency($data["DPWD_AMOUNT"]).'</p>';
        $amountUsd = '<p class="mb-0">$'.Helper::formatCurrency($data["DPWD_AMOUNT_SOURCE"]).'</p>';
        
        if($data['DPWD_CURR_TO'] == "USD" && $data['RTYPE_CURR'] == "USD"){
            $amountIdr = '';
        } 

        return '
            <div class="text-end">
                '.$amountIdr.'
                '.$amountUsd.'
            </div>
        ';
    });

    $dt->edit('DPWD_BANKSRC', function($data) {
        $explode = explode('/', $data['DPWD_BANKSRC']);
        return '
            <div class="text-start">
                <p class="mb-0"><strong>'.$explode[0].'</strong></p>
                <p class="mb-0">'.$explode[1].'</p>
                <p class="mb-0">'.$explode[2].'</p>
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