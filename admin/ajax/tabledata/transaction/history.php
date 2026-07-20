<?php

    use App\Factory\FileUploadFactory;
    use App\Factory\UtmHandler;
    use App\Models\Dpwd;
    use App\Models\FileUpload;

    // Get filter parameters
    $filterStatus = $_GET['filterStatus'] ?? '';
    $segregatedAccount = $_GET['segregatedAccountFilter'] ?? '';
    $filterDateFrom = $_GET['filterDateFrom'] ?? '';
    $filterDateTo = $_GET['filterDateTo'] ?? '';
    
    // Build WHERE conditions: keep legacy conditions, then append new filters
    $whereConditions = [
        'tb_dpwd.DPWD_STS != 0',
        'tb_dpwd.DPWD_TYPE = '.Dpwd::$typeDeposit
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
            tb_racc.ACC_FULLNAME AS MBR_NAME,
            tb_racc.ACC_LOGIN,
            tb_dpwd.DPWD_BANKSRC,
            tb_dpwd.DPWD_BANK,
            IF(tb_racctype.RTYPE_ISFLOATING = 0, tb_dpwd.DPWD_AMOUNT_SOURCE, tb_dpwd.DPWD_AMOUNT) AS DPWD_AMOUNT,
            tb_dpwd.DPWD_PIC,
            tb_dpwd.DPWD_NOTE1,
            IF(tb_dpwd.DPWD_STS = -1, "Accept",
                IF(tb_dpwd.DPWD_STS = 1, "Reject",
                    IF(tb_dpwd.DPWD_STS = 0, "Pending", "Unknown")
                )
            ) AS DPWD_STS,
            MD5(MD5(tb_dpwd.ID_DPWD)) AS ID_DPWD_HASH,
            tb_racctype.RTYPE_CURR_SYMBOL,
            tb_member.MBR_METADATA
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

    $dt->hide("RTYPE_CURR_SYMBOL");
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
        } else if($data['DPWD_STSACC'] == 'Pending'){
            return "
                <div class='text-center'>
                    <span class='badge bg-warning h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STS']))."</span>
                </div>
            ";
        } else {
            return "
                <div class='text-center'>
                    <span class='badge bg-secondary h-50 d-inline-block bg-opacity-15 text-white' style='font-size: 12px;'>".(($data['DPWD_STSACC']))."</span>
                </div>
            ";
        };
    });
    
    $dt->edit('DPWD_BANKSRC', function($data){
        return '<div class="text-start">'.str_replace('/', '<br>', htmlspecialchars(($data['DPWD_BANKSRC'] ?? '-'))).'</div>';
    });
    
    $dt->edit('DPWD_BANK', function($data){
        return '<div class="text-start">'.str_replace('/', '<br>', htmlspecialchars(($data['DPWD_BANK'] ?? '-'))).'</div>';
    });

    $dt->edit('DPWD_AMOUNT', function($data){
        return "<div class='text-end'>".$data['RTYPE_CURR_SYMBOL'].". ".number_format($data['DPWD_AMOUNT'], 0)."</div>";
    });
    $dt->edit('DPWD_PIC', function($data){
        if(!empty($data["DPWD_PIC"])){
            return "<div class='text-center'><a target='_blank' href='".FileUploadFactory::aws()->awsFile($data["DPWD_PIC"])."'>Pic</a></div>";
        }
    });
    $dt->edit('ID_DPWD_HASH', function($data){
        return "<div class='text-center'><a target='_blank' href='/export/trans-topup?acc=".$data['ID_DPWD_HASH']."'>Print</a></div>";
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
            <div class="d-flex flex-wrap gap-1">'.$html.'</div>
        ';
    });

    echo $dt->generate()->toJson();