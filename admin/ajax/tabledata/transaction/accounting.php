<?php

    use App\Factory\FileUploadFactory;
    use App\Factory\UtmHandler;
    use App\Models\Dpwd;
    use App\Models\FileUpload;
    use App\Models\Helper;

    // Get filter parameters
    $segregatedAccount = $_GET['segregatedAccountFilterAccounting'] ?? '';
    $filterDateFrom = $_GET['filterDateFrom'] ?? '';
    $filterDateTo = $_GET['filterDateTo'] ?? '';

    // Build WHERE conditions: keep legacy conditions, then append new filters
    $whereConditions = [
        'tb_dpwd.DPWD_STS = 0',
        'tb_dpwd.DPWD_STSACC = 0',
        'tb_dpwd.DPWD_TYPE = '.Dpwd::$typeDeposit
    ];

    if (!empty($filterDateFrom)) {
        $whereConditions[] = 'DATE(tb_dpwd.DPWD_DATETIME) >= "' . $db->real_escape_string($filterDateFrom) . '"';
    }

    if (!empty($filterDateTo)) {
        $whereConditions[] = 'DATE(tb_dpwd.DPWD_DATETIME) <= "' . $db->real_escape_string($filterDateTo) . '"';
    }

    if ($segregatedAccount === 'Lainnya') {
        $whereConditions[] = '(
            tb_dpwd.DPWD_BANK IS NULL
            OR TRIM(tb_dpwd.DPWD_BANK) = ""
            OR tb_dpwd.DPWD_BANK NOT IN (
                SELECT CONCAT(tb_bankadm.BKADM_NAME, "/", tb_bankadm.BKADM_HOLDER, "/", tb_bankadm.BKADM_ACCOUNT)
                FROM tb_bankadm
            )
        )';
    } elseif (!empty($segregatedAccount)) {
        $whereConditions[] = 'tb_dpwd.DPWD_BANK = "' . $db->real_escape_string($segregatedAccount) . '"';
    }

    $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
    $dt->query('
        SELECT
            tb_dpwd.DPWD_DATETIME,
            tb_racc.ACC_FULLNAME,
            tb_racc.ACC_LOGIN,
            tb_dpwd.DPWD_BANKSRC,
            tb_dpwd.DPWD_BANK,
            tb_dpwd.DPWD_AMOUNT,
            tb_dpwd.DPWD_AMOUNT_SOURCE,
            tb_dpwd.DPWD_PIC,				
            tb_member.MBR_METADATA,
            MD5(MD5(tb_dpwd.ID_DPWD)) AS ID_DPWD,
            JSON_OBJECT(
                "acc-login", tb_racc.ACC_LOGIN,
                "acc-name", tb_racc.ACC_FULLNAME,
                "acc-amntl", CAST(CONCAT(DPWD_CURR_FROM, " ", FORMAT(tb_dpwd.DPWD_AMOUNT_SOURCE, 0)) AS CHAR),
                "acc-rate", CAST(IF(tb_racctype.RTYPE_ISFLOATING != 1, FORMAT(tb_racctype.RTYPE_RATE, 0), 0) AS CHAR),
                "acc-amnt", CAST(CONCAT(DPWD_CURR_TO, " ", FORMAT(tb_dpwd.DPWD_AMOUNT, 2)) AS CHAR),
                "acc-bksrc", tb_dpwd.DPWD_BANKSRC,
                "acc-bksrc-lst", REGEXP_REPLACE(tb_dpwd.DPWD_BANKSRC, "^[^/]*\/", ""),
                "acc-bkdst", tb_dpwd.DPWD_BANK,
                "acc-bkdst-lst", REGEXP_REPLACE(tb_dpwd.DPWD_BANK, "^[^/]*\/", ""),
                "acc-dpx", CAST(MD5(MD5(tb_dpwd.ID_DPWD)) AS CHAR)
            ) AS JSNDT,
            tb_racctype.RTYPE_CURR_SYMBOL
        FROM tb_member
        JOIN tb_racc
        JOIN tb_racctype
        JOIN tb_dpwd
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR
        AND tb_dpwd.DPWD_MBR = tb_member.MBR_ID
        AND tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
        AND tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE)
        '.$whereClause.'
    ');

    $dt->hide('JSNDT');
    $dt->hide('DPWD_AMOUNT');
    $dt->edit('DPWD_AMOUNT_SOURCE', function($data){
        $return = "";
        if($data['RTYPE_CURR_SYMBOL'] == "Rp") {
            $return .= '<p class="mb-0">Rp '.Helper::formatCurrency($data["DPWD_AMOUNT_SOURCE"]).'</p>';
        }

        $return .= '<p class="mb-0">$'.Helper::formatCurrency($data["DPWD_AMOUNT"]).'</p>';
        return $return;
    });

    $dt->edit('DPWD_AMOUNT', function($data){
        return '
            <div class="text-end">
                '.$data['RTYPE_CURR_SYMBOL'].'. '.number_format(($data["DPWD_AMOUNT"] ?? 0), 2).'
            </div>
        ';
    });

    $dt->edit('DPWD_PIC', function($data){
        if(!empty($data["DPWD_PIC"])){
            return '
                <div class="text-center">
                    <a target="_blank" href="'.FileUploadFactory::aws()->awsFile($data["DPWD_PIC"]).'">Open</a>
                </div>
            ';
        }
    });

    $dt->edit('DPWD_BANKSRC', function($data){
        return '<div class="text-start">'.str_replace('/', '<br>', htmlspecialchars(($data['DPWD_BANKSRC'] ?? '-'))).'</div>';
    });

    $dt->edit('DPWD_BANK', function($data){
        return '<div class="text-start">'.str_replace('/', '<br>', htmlspecialchars(($data['DPWD_BANK'] ?? '-'))).'</div>';
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
        $dpwdData = base64_encode(json_encode([
            "code" => $data["ID_DPWD"],
            "detail" => $data["JSNDT"]
        ]));

        return '
            <div class="action d-flex justify-content-center gap-1" data-data="'.$dpwdData.'">
            </div>
        ';
    });

    echo $dt->generate()->toJson();