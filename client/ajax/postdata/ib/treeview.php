<?php
use App\Models\Helper;
use App\Models\Ib;
use App\Models\User;

$result = [];
$downlines = Ib::getNetworks($user['MBR_ID'], "downline");
$structures = Ib::toHierarcy($downlines);

function subArray(array $array): array {
    $res = [];
    foreach($array as $ar) {
        if($ar['MBR_STS'] == 1) {
            continue;
        }
        
        $entry = [
            'text' => '
                '.$ar['MBR_NAME'].'
                <p class="mb-0" style="margin-left: 20px;">' . $ar['MBR_EMAIL'] . ' - ' . $ar['MBR_PHONE'] . ' - ' . $ar['SALES_TYPE'] . '</p>
            ',
            'icon' => "fas fa-user",
            'expanded' => true,
            'nodes' => []
        ];

        if(!empty($ar['children'])) {
            $entry['nodes'] = subArray($ar['children']);
        }

        $res[] = $entry;
    }

    return $res;
}

$result = subArray($structures['children']);
exit(json_encode($result));