<?php

    use App\Factory\UtmHandler;
    use App\Models\Regol;

    $filterTypeAccount = $_GET['filterTypeAccount'] ?? '';
    $filterPlatform    = $_GET['filterPlatform'] ?? '';
    $filterRate        = $_GET['filterRate'] ?? '';
    $filterUTM         = $_GET['filterUTM'] ?? '';
    $filterUTMValue    = $_GET['filterUTMValue'] ?? '';
    $filterDateFrom    = $_GET['filterDateFrom'] ?? '';
    $filterDateTo      = $_GET['filterDateTo'] ?? '';

    $allowedUtmKeys = array_values(array_filter(UtmHandler::$utmKeys, fn($key) => $key !== 'referral'));
    if (!empty($filterUTM) && !in_array($filterUTM, $allowedUtmKeys, true)) {
        $filterUTM = '';
    }

    $whereConditions = [];

    if (!empty($filterTypeAccount)) {
        $whereConditions[] = 'trt.ID_RTYPE = "' . $db->real_escape_string($filterTypeAccount) . '"';
    }

    if (!empty($filterPlatform)) {
        $whereConditions[] = 'trt.RTYPE_TYPE_AS = "' . $db->real_escape_string($filterPlatform) . '"';
    }

    if ($filterRate !== '') {
        if ($filterRate === '0') {
            $whereConditions[] = 'trt.RTYPE_ISFLOATING = 1';
        } else {
            $whereConditions[] = 'trt.RTYPE_ISFLOATING != 1 AND trt.RTYPE_RATE = "' . $db->real_escape_string($filterRate) . '"';
        }
    }

    if (!empty($filterUTM)) {
        $safeKey = $db->real_escape_string($filterUTM);
        $whereConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tm.MBR_METADATA, "$.'.$safeKey.'")) IS NOT NULL'
                      . ' AND JSON_UNQUOTE(JSON_EXTRACT(tm.MBR_METADATA, "$.'.$safeKey.'")) != "null"'
                      . ' AND JSON_UNQUOTE(JSON_EXTRACT(tm.MBR_METADATA, "$.'.$safeKey.'")) != ""';
    }

    if ($filterUTMValue !== '') {
        $safeValue = $db->real_escape_string($filterUTMValue);

        if (!empty($filterUTM)) {
            $safeKey = $db->real_escape_string($filterUTM);
            $whereConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tm.MBR_METADATA, "$.'.$safeKey.'")) LIKE "%'.$safeValue.'%"';
        } elseif (!empty($allowedUtmKeys)) {
            $valueConditions = [];

            foreach ($allowedUtmKeys as $utmKey) {
                $safeKey = $db->real_escape_string($utmKey);
                $valueConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tm.MBR_METADATA, "$.'.$safeKey.'")) LIKE "%'.$safeValue.'%"';
            }

            if (!empty($valueConditions)) {
                $whereConditions[] = '(' . implode(' OR ', $valueConditions) . ')';
            }
        }
    }

    if (!empty($filterDateFrom)) {
        $whereConditions[] = 'DATE(tr.ACC_F_CMPLT_DATE) >= "' . $db->real_escape_string($filterDateFrom) . '"';
    }

    if (!empty($filterDateTo)) {
        $whereConditions[] = 'DATE(tr.ACC_F_CMPLT_DATE) <= "' . $db->real_escape_string($filterDateTo) . '"';
    }

    $whereClause = count($whereConditions) ? 'AND ' . implode(' AND ', $whereConditions) : '';
    $dt->query('
        SELECT
            tr.ACC_F_CMPLT_DATE,
            tr.ACC_FULLNAME,
            LOWER(tm.MBR_EMAIL) AS MBR_EMAIL,
            CONCAT(trt.RTYPE_NAME, "/", trt.RTYPE_KOMISI, "/", CASE WHEN RTYPE_ISFLOATING = 1 THEN "Floating" ELSE (RTYPE_RATE/1000) END) as ACC_TYPE,
            trt.RTYPE_TYPE_AS,
            IF(trt.RTYPE_ISFLOATING = 1, "Floating", RTYPE_RATE) as ACC_RATE,
            tm.MBR_METADATA,
            tr.ACC_WPCHECK,
            MD5(MD5(tr.ID_ACC)) AS ID_ACC
        FROM tb_racc tr
        JOIN tb_member tm ON (tm.MBR_ID = tr.ACC_MBR)
        JOIN tb_racctype trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
        WHERE tr.ACC_DERE = 1
        AND tr.ACC_STS = 1
        AND ACC_WPCHECK != '.Regol::$statusWPCheckActive.'
        AND ACC_F_DISC = 1
        '.$whereClause.'
    ');

    $dt->edit("ACC_WPCHECK", fn($col): string => Regol::wpCheckStatus($col['ACC_WPCHECK'])['html']);
    
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

    $dt->edit('ID_ACC', function($col){
        switch($col['ACC_WPCHECK']) {
            case Regol::$statusWPCheckBankVerification:
                return '
                    <div class="text-center">
                        <a href="/account/progress_real_account/bank_verification/'.$col['ID_ACC'].'" class="btn btn-sm btn-info">Detail</a>
                    </div>
                ';

            case Regol::$statusWPCheckRegister:
                return '
                    <div class="text-center">
                        <a href="/account/progress_real_account/wp_verification/'.$col['ID_ACC'].'" class="btn btn-sm btn-info">Detail</a>
                    </div>
                ';

            case Regol::$statusWPCheckGoodFund:
                return '
                    <div class="text-center">
                        <a href="/account/progress_real_account/dealer/'.$col['ID_ACC'].'" class="btn btn-sm btn-info">Detail</a>
                    </div>
                ';
        }
    });

    echo $dt->generate()->toJson();