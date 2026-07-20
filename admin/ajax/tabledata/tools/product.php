<?php

use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
$dt->query("
    SELECT
        RTYPE_SUFFIX,
        RTYPE_NAME,
        RTYPE_KOMISI,
        RTYPE_ISFLOATING,
        RTYPE_RATE,
        RTYPE_CURR,
        RTYPE_TYPE,
        RTYPE_GROUP,
        tb_racctype_category.RCAT_TYPE,
        RTYPE_FILE,
        RTYPE_STS
    FROM tb_racctype
    JOIN tb_racctype_category ON(tb_racctype_category.ID_RCAT = tb_racctype.RTYPE_CAT)
    WHERE UPPER(RTYPE_TYPE) != 'DEMO'
    GROUP BY tb_racctype.ID_RTYPE
");

$dt->hide("RTYPE_KOMISI");
$dt->hide("RTYPE_ISFLOATING");
$dt->hide("RTYPE_RATE");
$dt->hide("RTYPE_CURR");
$dt->hide("RCAT_TYPE");
$dt->edit("RTYPE_NAME", function($col) {
    return '
        <p class="mb-0">Category: <b>'.$col['RTYPE_TYPE'].'</b></p>
        <p class="mb-0">Name: <b>'.$col['RTYPE_NAME'].'</b></p>
    ';
});
$dt->edit("RTYPE_TYPE", function($col) {
    $rate = $col['RTYPE_ISFLOATING']? "Floating" : App\Models\Helper::formatCurrency($col['RTYPE_RATE']);
    return '
        <p class="mb-0">Rate: <b>'.$rate.'</b></p>
        <p class="mb-0">Currency: <b>'.$col['RTYPE_CURR'].'</b></p>
        <p class="mb-0">Komisi: <b>'.$col['RTYPE_KOMISI'].'</b></p>
    ';
});
$dt->edit("RTYPE_GROUP", function($col) {
    return '
        <p class="mb-0">Group: <b>'.$col['RTYPE_GROUP'].'</b></p>
        <p class="mb-0">Sales Model: <b>'.$col['RCAT_TYPE'].'</b></p>
    ';
});

$dt->edit("RTYPE_FILE", fn($col): string => '<a href="'.FileUploadFactory::aws()->awsFile($col['RTYPE_FILE']).'" class="text-decoration-underline">Lihat</a>');
$dt->edit("RTYPE_STS", fn($col): string => App\Models\Product::$status[ $col['RTYPE_STS'] ]['html']);
$dt->add("ACTION", function($col) {
    return '<div class="action justify-content-center d-flex gap-2" data-suffix="'.$col['RTYPE_SUFFIX'].'"></div>';
});

echo $dt->generate()->toJson();