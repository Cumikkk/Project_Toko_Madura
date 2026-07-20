<?php
$currentLevel = $user['ADM_LEVEL'] ?? 0;
$dt->query("
    SELECT 
        COUNTRY_CODE,
        COUNTRY_NAME,
        COUNTRY_CURR,
        COUNTRY_PHONE_CODE,
        MD5(MD5(ID_COUNTRY)) AS ID,
        JSON_OBJECT(
            'edit-country-name', COUNTRY_NAME,
            'edit-country-curr', COUNTRY_CURR,
            'edit-country-code', COUNTRY_CODE,
            'edit-country-phone-code', COUNTRY_PHONE_CODE,
            'submit-edit-country', MD5(MD5(ID_COUNTRY))
        ) AS JSNDT
    FROM tb_country
");

$dt->hide('JSNDT');

$dt->edit("ID", function($col) {
    $editButton = '<a href="javascript:void(0)" data-jsn="'.base64_encode($col["JSNDT"]).'" data-id="'.$col['ID'].'" data-name="'.$col['COUNTRY_NAME'].'" data-curr="'.$col['COUNTRY_CURR'].'" data-code="'.$col['COUNTRY_CODE'].'" data-phone="'.$col['COUNTRY_PHONE_CODE'].'" class="btn-edit btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalEditCountry"><i class="fas fa-edit"></i></a>';
    $deleteButton = '<a href="javascript:void(0)" data-id="'.$col['ID'].'" class="btn-delete btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>';
    
    return '
        <div class="text-center">
            '.($editButton).'        
            '.($deleteButton).'        
        </div>
    ';
});

echo $dt->generate()->toJson(); // same as 'echo $dt->generate()';