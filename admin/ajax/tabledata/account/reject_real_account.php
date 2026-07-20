<?php
    use App\Factory\UtmHandler;
    use App\Models\Helper;

    $data = Helper::getSafeInput($_GET);
    $filterUTM = $data['filterUTM'] ?? '';
    $filterUTMValue = $data['filterUTMValue'] ?? '';
    $filterDate = $data['filterDate'] ?? '';
    $filterDateFrom = $data['filterDateFrom'] ?? '';
    $filterDateTo = $data['filterDateTo'] ?? '';

    $allowedUtmKeys = array_values(array_filter(UtmHandler::$utmKeys, fn($key) => $key !== 'referral'));
    if (!empty($filterUTM) && !in_array($filterUTM, $allowedUtmKeys, true)) {
        $filterUTM = '';
    }

    $whereConditions = [
        'tb_racc.ACC_STS = 2'
    ];

    $dateField = 'ACC_WPCHECK_DATE';
    if ($filterDate === 'reg') {
        $dateField = 'ACC_F_PROFILE_DATE';
    }

    if (!empty($filterDateFrom)) {
        $whereConditions[] = 'DATE(tb_racc.'.$dateField.') >= "'.$db->real_escape_string($filterDateFrom).'"';
    }

    if (!empty($filterDateTo)) {
        $whereConditions[] = 'DATE(tb_racc.'.$dateField.') <= "'.$db->real_escape_string($filterDateTo).'"';
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

    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
    $old_query = '
        SELECT
            tb_racc.ACC_F_PROFILE_DATE,
            tb_racc.ACC_WPCHECK_DATE,
            tb_racc.ACC_FULLNAME,
            LOWER(tb_member.MBR_EMAIL) AS MBR_EMAIL,
            "Rejected" AS ACC_STATUS,
            (
                SELECT
                    tb_note.NOTE_NOTE
                FROM tb_note
                WHERE tb_note.NOTE_RACC = tb_racc.ID_ACC
                AND tb_note.NOTE_TYPE = "WP VER REJECT"
                LIMIT 1
            ) AS RJCT_NOTE,
            MD5(MD5(tb_racc.ID_ACC)) AS ID_ACC
        FROM tb_racc
        JOIN tb_member
        ON(tb_racc.ACC_MBR = tb_member.MBR_ID)
        WHERE tb_racc.ACC_WPCHECK = 6
        AND tb_racc.ACC_STS = -1
    ';

    $new_query = '
        SELECT
            tb_racc.ACC_F_PROFILE_DATE,
            (
                SELECT
                    tb_note.NOTE_DATETIME
                FROM tb_note
                WHERE tb_note.NOTE_RACC = tb_racc.ID_ACC
                AND tb_note.NOTE_TYPE IN("WP VER REJECT", "BANK VERIFICATION REJECT")
                ORDER BY tb_note.ID_NOTE DESC
                LIMIT 1
            ) AS RJCT_NOTE2,
            tb_racc.ACC_FULLNAME,
            LOWER(tb_member.MBR_EMAIL) AS MBR_EMAIL,
            tb_member.MBR_METADATA,
            "Rejected" AS ACC_STATUS,
            MD5(MD5(tb_racc.ID_ACC)) AS ID_ACC
        FROM tb_racc
        JOIN tb_member ON(tb_racc.ACC_MBR = tb_member.MBR_ID)
        '.$whereClause.'
    ';
    $dt->query($new_query);

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
        if($data['ACC_STATUS'] == 'Rejected'){
            return "
                <div class='text-center'>
                    <span class='badge bg-danger h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>Rejected</span>
                </div>
            ";
        } else if($data['ACC_STATUS'] == 'REGISTER'){
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
            if($data['ACC_F_PROFILE_DATE'] < '2023-02-06 00:00:00'){
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
            if($data['ACC_F_PROFILE_DATE'] < '2023-02-06 00:00:00'){
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
                    <a href='/account/reject_real_account/detail/".$data["ID_ACC"]."' class='btn btn-sm btn-info'>Detail</a>
                </div>
            "; 
        }
    });

    echo $dt->generate()->toJson();