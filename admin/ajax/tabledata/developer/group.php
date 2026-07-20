<?php
$dt->query("
    SELECT 
        `group` as groupName,
        `type`,
        `icon`,
        md5(md5(`id`)) as id_hash
    FROM admin_module_group
");

$dt->hide("type");
$dt->edit("icon", function($col) {
    return '<i class="'.$col['icon'].'"></i>';
});

$dt->edit("id_hash", function($col) {
    $dataEncode = base64_encode(json_encode([
        'group' => $col['groupName'],
        'type' => $col['type'],
        'icon' => $col['icon'],
    ]));

    return '<div class="action text-center" data-id="'.$col['id_hash'].'" data-other="'.$dataEncode.'"></div>';
});

echo $dt->generate()->toJson();