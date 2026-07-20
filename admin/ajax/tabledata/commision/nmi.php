<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\Ib;

try {
    $dateStart = Helper::form_input($_GET['start'] ?? null);
    $dateEnd = Helper::form_input($_GET['end'] ?? null);
    $listSales = [];
    $sqlGetSales = $db->query("
        SELECT
            tm.MBR_ID,
            tm.MBR_NAME,
            tm.MBR_EMAIL,
            tm.MBR_CODE,
            tss.*,
            tb_nmi.*
        FROM tb_member tm
        JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = tm.MBR_TYPE)
        LEFT JOIN tb_nmi ON (tb_nmi.NMI_MBR = tm.MBR_ID)
        WHERE tss.SLSSTRC_LEVEL > 0
    ");

    if($sqlGetSales->num_rows <= 0) {
        exit(json_encode([]));
    }

    $resultNmi = [];
    foreach($sqlGetSales->fetch_all(MYSQLI_ASSOC) as $row) {
        $downlines = Ib::getNetworks($row['MBR_ID'], "downline");
        $settingBonus = [
            'target' => $row['SLSSTRC_NMI_TARGET'],
            'bonus' => $row['SLSSTRC_NMI_PERCENT']
        ];

        if(!empty($row['ID_NMI'])) {
            $settingBonus = [
                'target' => $row['NMI_TARGET'],
                'bonus' => $row['NMI_PERCENT']
            ];
        }

        $nmi = 0;
        $total = 0;
        $listId = [];
        foreach(array_column($downlines, "MBR_ID") as $downline) {
            if($downline != $row['MBR_ID']) {
                $listId[] = $downline;
            }
        }

        $NMIData = SalesMain::searchNmi($row['MBR_ID'], $listId, $dateStart, $dateEnd);
        if($NMIData) {
            $nmi = $NMIData->nmi();
            if($nmi > $settingBonus['target']) {
                $total = $nmi * ($settingBonus['bonus'] / 100);
            }
        }

        $resultNmi[] = [
            'id' => $row['MBR_CODE'],
            'pid' => 0,
            'structure' => $row['SLSSTRC_NAME'],
            'email' => $row['MBR_EMAIL'],
            'nmi' => Helper::formatCurrency($nmi),
            'percentage' => $settingBonus['bonus'] . "%",
            'result' => Helper::formatCurrency($total),
        ];
    }

    exit(json_encode($resultNmi));

} catch (Exception $e) {
    exit(json_encode([]));
}