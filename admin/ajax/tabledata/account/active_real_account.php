<?php

    use App\Factory\UtmHandler;
    use App\Models\Regol;
    use Config\Core\SystemInfo;

    $dbmetasrv = SystemInfo::app('DB_METALIVE');
    $filterJob         = $_GET['filterJob'] ?? '';
    $filterTypeAccount = $_GET['filterTypeAccount'] ?? '';
    $filterGroup       = $_GET['filterGroup'] ?? '';
    $filterPlatform    = $_GET['filterPlatform'] ?? '';
    $filterRate        = $_GET['filterRate'] ?? '';
    $filterUTM         = $_GET['filterUTM'] ?? '';
    $filterUTMValue    = $_GET['filterUTMValue'] ?? '';
    $filterDate        = $_GET['filterDate'] ?? '';
    $filterDateFrom    = $_GET['filterDateFrom'] ?? '';
    $filterDateTo      = $_GET['filterDateTo'] ?? '';

    $allowedUtmKeys = array_values(array_filter(UtmHandler::$utmKeys, fn($key) => $key !== 'referral'));
    if (!empty($filterUTM) && !in_array($filterUTM, $allowedUtmKeys, true)) {
        $filterUTM = '';
    }

    $whereConditions = [
        'tb_racc.ACC_WPCHECK = 6',
        'tb_racc.ACC_STS = -1',
    ];

    if (!empty($filterJob)) {
        if (strtolower($filterJob) === 'lainnya') {
            $knownJobs = array_filter(Regol::$listPekerjaan, function ($job) {
                return strtolower($job) !== 'lainnya';
            });

            $escapedKnownJobs = array_map(function ($job) use ($db) {
                return '"' . $db->real_escape_string($job) . '"';
            }, $knownJobs);

            $whereConditions[] = '(tb_racc.ACC_F_APP_KRJ_TYPE NOT IN (' . implode(',', $escapedKnownJobs) . ') OR tb_racc.ACC_F_APP_KRJ_TYPE IS NULL OR tb_racc.ACC_F_APP_KRJ_TYPE = "")';
        } else {
            $whereConditions[] = 'tb_racc.ACC_F_APP_KRJ_TYPE = "' . $db->real_escape_string($filterJob) . '"';
        }
    }

    if (!empty($filterTypeAccount)) {
        $whereConditions[] = 'tb_racc.ACC_TYPE = "' . $db->real_escape_string($filterTypeAccount) . '"';
    }

    if (!empty($filterGroup)) {
        $whereConditions[] = 'mt4users.`group` = "' . $db->real_escape_string($filterGroup) . '"';
    }

    if (!empty($filterPlatform)) {
        $whereConditions[] = 'EXISTS (
            SELECT 1
            FROM tb_racctype trt
            WHERE trt.ID_RTYPE = tb_racc.ACC_TYPE
            AND trt.RTYPE_TYPE_AS = "' . $db->real_escape_string($filterPlatform) . '"
        )';
    }

    if ($filterRate !== '') {
        if ($filterRate === '0') {
            $whereConditions[] = 'EXISTS (
                SELECT 1
                FROM tb_racctype trt
                WHERE trt.ID_RTYPE = tb_racc.ACC_TYPE
                AND trt.RTYPE_ISFLOATING = 1
            )';
        } else {
            $whereConditions[] = 'EXISTS (
                SELECT 1
                FROM tb_racctype trt
                WHERE trt.ID_RTYPE = tb_racc.ACC_TYPE
                AND trt.RTYPE_ISFLOATING != 1
                AND trt.RTYPE_RATE = "' . $db->real_escape_string($filterRate) . '"
            )';
        }
    }

    if (!empty($filterUTM)) {
        $safeKey = $db->real_escape_string($filterUTM);
        $whereConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tb_member.MBR_METADATA, "$.'.$safeKey.'")) IS NOT NULL'
                           . ' AND JSON_UNQUOTE(JSON_EXTRACT(tb_member.MBR_METADATA, "$.'.$safeKey.'")) != "null"'
                           . ' AND JSON_UNQUOTE(JSON_EXTRACT(tb_member.MBR_METADATA, "$.'.$safeKey.'")) != ""';
    }

    if ($filterUTMValue !== '') {
        $safeValue = $db->real_escape_string($filterUTMValue);

        if (!empty($filterUTM)) {
            $safeKey = $db->real_escape_string($filterUTM);
            $whereConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tb_member.MBR_METADATA, "$.'.$safeKey.'")) LIKE "%'.$safeValue.'%"';
        } elseif (!empty($allowedUtmKeys)) {
            $valueConditions = [];

            foreach ($allowedUtmKeys as $utmKey) {
                $safeKey = $db->real_escape_string($utmKey);
                $valueConditions[] = 'JSON_UNQUOTE(JSON_EXTRACT(tb_member.MBR_METADATA, "$.'.$safeKey.'")) LIKE "%'.$safeValue.'%"';
            }

            if (!empty($valueConditions)) {
                $whereConditions[] = '(' . implode(' OR ', $valueConditions) . ')';
            }
        }
    }

    if (!empty($filterDateFrom)) {
        $dateField = ($filterDate === 'active') ? 'tb_racc.ACC_WPCHECK_DATE' : 'tb_racc.ACC_F_CMPLT_DATE';
        $whereConditions[] = 'DATE('.$dateField.') >= "' . $db->real_escape_string($filterDateFrom) . '"';
    }

    if (!empty($filterDateTo)) {
        $dateField = ($filterDate === 'active') ? 'tb_racc.ACC_WPCHECK_DATE' : 'tb_racc.ACC_F_CMPLT_DATE';
        $whereConditions[] = 'DATE('.$dateField.') <= "' . $db->real_escape_string($filterDateTo) . '"';
    }

    $whereClause = ' WHERE ' . implode(' AND ', $whereConditions);
    $dt->query('
        SELECT
            tb_racc.ACC_F_CMPLT_DATE,
            tb_racc.ACC_WPCHECK_DATE,
            tb_racc.ACC_LOGIN,
            tb_racc.ACC_FULLNAME,
            LOWER(tb_member.MBR_EMAIL) AS MBR_EMAIL,
            IF(tb_racc.ACC_CDD = 1, "Standart", "Sederhana") AS ACC_CDD,
            (
                SELECT
                    CONCAT(
                        tb_racctype.RTYPE_NAME, "/",
                        tb_racctype.RTYPE_KOMISI, "/",
                        CASE
                            WHEN RTYPE_RATE = 0 THEN "Floating"
                            ELSE (RTYPE_RATE/1000)
                        END
                    )
                FROM tb_racctype
                WHERE tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE
                LIMIT 1 
            ) AS ACC_TYPE,
            mt4users.`group` AS META_GROUP,
            (
                SELECT
                    tb_racctype.RTYPE_TYPE_AS
                FROM tb_racctype
                WHERE tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE
                LIMIT 1 
            ) AS RTYPE_TYPE_AS,
            (
                SELECT
                    IF((RTYPE_RATE = 0 OR RTYPE_ISFLOATING = 1), "Floating", FORMAT(tb_racctype.RTYPE_RATE, 0))
                FROM tb_racctype
                WHERE tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE
                LIMIT 1 
            ) AS ACC_RATE,
            IF(tb_racc.ACC_LOGIN = 0 AND tb_racc.ACC_WPCHECK = 0, "REGISTER",
                IF(tb_racc.ACC_LOGIN = 0 AND tb_racc.ACC_WPCHECK = 1, "Verified",
                    IF(tb_racc.ACC_LOGIN = 0 AND tb_racc.ACC_WPCHECK = 2, "Deposit New Account",
                        IF(tb_racc.ACC_LOGIN = 0 AND tb_racc.ACC_WPCHECK = 3, "Waiting Depo",
                            IF(tb_racc.ACC_LOGIN = 0 AND tb_racc.ACC_WPCHECK = 4, "Waiting Depo.",
                                IF(tb_racc.ACC_LOGIN = 0 AND tb_racc.ACC_WPCHECK = 5, "GoodFund",
                                    IF(tb_racc.ACC_LOGIN <> 0 AND tb_racc.ACC_WPCHECK = 6, "Active", "Unknown")
                                )
                            )
                        )
                    )
                )
            ) AS ACC_STATUS,
            IFNULL((
                SELECT
                    tb_acccond.ACCCND_DATEMARGIN
                FROM tb_acccond
                WHERE tb_acccond.ACCCND_ACC = tb_racc.ID_ACC
                AND tb_acccond.ACCCND_LOGIN = tb_racc.ACC_LOGIN
                ORDER BY ID_ACCCND DESC
                LIMIT 1
            ),tb_racc.ACC_WPCHECK_DATE) AS DATE_ACTV,
            tb_member.MBR_METADATA,
            MD5(MD5(tb_racc.ID_ACC)) AS ID_ACC
        FROM tb_racc
        JOIN tb_member ON(tb_racc.ACC_MBR = tb_member.MBR_ID)
        LEFT JOIN '.$dbmetasrv.'.MT4_USERS mt4users ON(mt4users.login = tb_racc.ACC_LOGIN)
        '.$whereClause.'
    ');

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
    
    $dt->edit('ACC_STATUS', function($data){ 
        if($data['ACC_STATUS'] == 'REGISTER'){
            return "
                <div class='text-center'>
                    <span class='badge bg-success h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>Register</span>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Deposit New Account'){
            return "
                <div class='text-center'>
                    <span class='badge bg-primary h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['ACC_STATUS']))."</span>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Waiting Depo'){
            return "
                <div class='text-center'>
                    <span class='badge h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px; background-color: purple;'>".(($data['ACC_STATUS']))."</span>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Waiting Depo.'){
            return "
                <div class='text-center'>
                    <span class='badge bg-secondary h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>Waiting Finance</span>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'GoodFund'){
            return "
                <div class='text-center'>
                    <span class='badge bg-info h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['ACC_STATUS']))."</span>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Active'){
            if($data['ACC_WPCHECK_DATE'] < '2023-02-06 00:00:00'){
                return "
                    <div class='text-center'>
                        <span class='badge h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px; background: orange ;'>".(($data['ACC_STATUS']))."</span>
                    </div>
                ";
            } else {
                return "
                    <div class='text-center'>
                        <span class='badge bg-warning h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['ACC_STATUS']))."</span>
                    </div>
                ";
            }
        } else { 
            return "
                <div class='text-center'>
                    <span class='badge h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px; background-color: #184421;'>".(($data['ACC_STATUS']))."</span>
                </div>
            "; 
        }
    });
    $dt->edit('ID_ACC', function($data){
        if($data['ACC_STATUS'] == 'REGISTER'){
            return "
                <div class='text-center'>
                    <a href='/account/progress_real_account/wp_verification/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Deposit New Account'){
            return "
                <div class='text-center'>
                    <a href='/account/progress_real_account/client_deposit/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Waiting Depo'){
            return "
                <div class='text-center'>
                    <a href='/account/progress_real_account/wp_check/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Waiting Depo.'){
            return "
                <div class='text-center'>
                    <a href='/account/progress_real_account/accounting/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'GoodFund'){
            return "
                <div class='text-center'>
                    <a href='/account/progress_real_account/dealer/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'Active'){
            if($data['ACC_WPCHECK_DATE'] < '2023-02-06 00:00:00'){
                return "
                    <div class='text-center'>
                        <a href='/account/active_real_account/document1/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                    </div>
                ";
            } else {
                return "
                    <div class='text-center'>
                        <a href='/account/active_real_account/document/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a> ||
                        <a href='/account/active_real_account/edit/".$data["ID_ACC"]."' class='btn btn-sm btn-primary'>Edit</a>
                    </div>
                ";
            }
        } else { 
            return "
                <div class='text-center'>
                    <a href='/account/progress_real_account/temporary_detail/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            "; 
        }
    });

    echo $dt->generate()->toJson();