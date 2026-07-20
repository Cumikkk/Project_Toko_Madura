<?php

    use App\Factory\UtmHandler;
    use App\Models\FileUpload;
    use App\Models\Dpwd;

    // Get filter parameters
    $filterStatus = $_GET['filterStatus'] ?? '';
    $filterDateFrom = $_GET['filterDateFrom'] ?? '';
    $filterDateTo = $_GET['filterDateTo'] ?? '';
    
    // Build WHERE conditions: keep legacy conditions, then append new filters
    $whereConditions = [
        'tb_dpwd.DPWD_STS != 0',
        'tb_dpwd.DPWD_TYPE = '.Dpwd::$typeWithdrawal
    ];

    if ($filterStatus !== '' && $filterStatus !== null) {
        $whereConditions[] = 'tb_dpwd.DPWD_STS = ' . (int) $filterStatus;
    }

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
            tb_racc.ACC_FULLNAME AS MBR_NAME,
            tb_racc.ACC_LOGIN,
            tb_dpwd.DPWD_BANKSRC,
            tb_dpwd.DPWD_BANK,
            tb_dpwd.DPWD_AMOUNT,
            tb_dpwd.DPWD_NOTE1,
            IF(tb_dpwd.DPWD_STSVER = -1, "Accept",
                IF(tb_dpwd.DPWD_STSVER = 1, "Reject",
                    IF(tb_dpwd.DPWD_STSVER = 0, "Pending", "Unknown")
                )
            ) AS DPWD_STSVER,
            IF(tb_dpwd.DPWD_STS = -1, "Accept",
                IF(tb_dpwd.DPWD_STS = 1, "Reject",
                    IF(tb_dpwd.DPWD_STS = 0, "Pending", "Unknown")
                )
            ) AS DPWD_STS,
            tb_member.MBR_METADATA,
            MD5(MD5(tb_dpwd.ID_DPWD)) AS ID_DPWD_HASH,
            tb_racctype.RTYPE_CURR_SYMBOL
        FROM tb_member
        JOIN tb_racc
        JOIN tb_dpwd
        JOIN tb_racctype
        ON(tb_member.MBR_ID = tb_racc.ACC_MBR
        AND tb_dpwd.DPWD_MBR = tb_member.MBR_ID
        AND tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
		AND tb_racc.ACC_TYPE = tb_racctype.ID_RTYPE)
        '.$whereClause.'
    ');
    
    $dt->edit('DPWD_DATETIME', function($data){
        return "<div class='text-center'>".$data['DPWD_DATETIME']."</div>";
    });
    $dt->edit('DPWD_STS', function($data){
        if($data['DPWD_STS'] == 'Accept'){
            return "
                <div class='text-center'>
                    <span class='badge bg-success h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STS']))."</span>
                </div>
            ";
        } else if($data['DPWD_STS'] == 'Reject'){
            return "
                <div class='text-center'>
                    <span class='badge bg-danger h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STS']))."</span>
                </div>
            ";
        } else {
            return "
                <div class='text-center'>
                    <span class='badge bg-secondary h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STS']))."</span>
                </div>
            ";
        };
    });
    $dt->edit('DPWD_STSVER', function($data){
        if($data['DPWD_STSVER'] == 'Accept'){
            return "
                <div class='text-center'>
                    <span class='badge bg-success h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STSVER']))."</span>
                </div>
            ";
        } else if($data['DPWD_STSVER'] == 'Reject'){
            return "
                <div class='text-center'>
                    <span class='badge bg-danger h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STSVER']))."</span>
                </div>
            ";
        } else {
            return "
                <div class='text-center'>
                    <span class='badge bg-secondary h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STSVER']))."</span>
                </div>
            ";
        };
    });

    $dt->edit('DPWD_BANKSRC', function($data) {
        $explode = explode('/', $data['DPWD_BANKSRC'] ?? '-/-/-');
        return '
            <div class="text-start">
                <p class="mb-0"><strong>'.$explode[0].'</strong></p>
                <p class="mb-0">'.$explode[1].'</p>
                <p class="mb-0">'.$explode[2].'</p>
            </div>
        ';
    });

    $dt->edit('DPWD_BANK', function($data) {
        $explode = explode('/', $data['DPWD_BANK'] ?? '-/-/-');
        return '
            <div class="text-start">
                <p class="mb-0"><strong>'.$explode[0].'</strong></p>
                <p class="mb-0">'.$explode[1].'</p>
                <p class="mb-0">'.$explode[2].'</p>
            </div>
        ';
    });

    $dt->edit('DPWD_AMOUNT', function($data){
        return "<div class='text-end'>".$data['RTYPE_CURR_SYMBOL'].". ".number_format($data['DPWD_AMOUNT'], 0)."</div>";
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
    
    $dt->edit('ID_DPWD_HASH', function($data){
        return "<div class='text-center'><a target='_blank' href='/export/trans-withdrawal?acc=".$data['ID_DPWD_HASH']."'>Print</a></div>";
    });
    echo $dt->generate()->toJson();