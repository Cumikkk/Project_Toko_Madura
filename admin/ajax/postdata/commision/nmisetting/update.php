<?php
use App\Models\Helper;
use Config\Core\Database;

$data = Helper::getSafeInput($_POST);
$required = [
    'id' => "id",
    'nmi_target' => "target",
    'nmi_percent' => "percent",
];

foreach($required as $req => $text) {
    if(empty($data[ $req ])) {
        JsonResponse([
            'success' => false,
            'message' => "{$text} field is required",
            'data' => []
        ]);
    }
}

$SQL_CHECK = mysqli_query($db, 'SELECT ID_SLSSTRC FROM tb_sales_structure WHERE MD5(MD5(ID_SLSSTRC)) = "'.$data['id'].'" LIMIT 1');
if(($SQL_CHECK) && $SQL_CHECK->num_rows == 0){
    JsonResponse([
        'code'      => 200,
        'success'   => false,
        'message'   => "Category already registered",
        'data'      => []
    ]);
}
$row = mysqli_fetch_assoc($SQL_CHECK);

$update = Database::update("tb_sales_structure", [
    'SLSSTRC_NMI_TARGET' => $data['nmi_target'],
    'SLSSTRC_NMI_PERCENT' => $data['nmi_percent']
], ['ID_SLSSTRC' => $row['ID_SLSSTRC']]);
if(!$update) {
    JsonResponse([
        'success' => false,
        'message' => "Failed to update product",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Successfull",
    'data' => []
]);