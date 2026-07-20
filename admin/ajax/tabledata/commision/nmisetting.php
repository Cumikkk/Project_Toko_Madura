<?php
$dt->query('
    SELECT
        tb_sales_division.SLSDIVISION_NAME,
        tb_sales_structure.SLSSTRC_NAME,
        tb_sales_structure.SLSSTRC_NMI_TARGET,
        tb_sales_structure.SLSSTRC_NMI_PERCENT,
        MD5(MD5(tb_sales_structure.ID_SLSSTRC)) AS ID_SLSSTRC
    FROM tb_sales_structure
    JOIN tb_sales_division ON(tb_sales_division.ID_SLSDIVISION = tb_sales_structure.SLSSTRC_DIV)
');

$dt->edit("ID_SLSSTRC", function($col) {
    $data = base64_encode(json_encode([
        'id' => $col['ID_SLSSTRC'],
        'nmi_target' => $col['SLSSTRC_NMI_TARGET'],
        'nmi_percent' => $col['SLSSTRC_NMI_PERCENT']
    ]));

    return '<div class="action d-flex justify-content-center gap-2" data-data="'.$data.'"></div>';
});

echo $dt->generate()->toJson();
?>