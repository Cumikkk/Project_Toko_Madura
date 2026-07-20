<?php
$dt->query("
    SELECT 
        IFNULL(am.updated_at, am.created_at) as last_update,
        MD5(MD5(amg.id)) as id_group,
        amg.group as group_name,
        am.module,
        am.m_order,
        am.status,
        MD5(Md5(am.id)) as id_module
    FROM admin_module am
    JOIN admin_module_group amg ON (amg.id = am.group_id)
");

$dt->hide("id_group");
$dt->hide("m_order");
$dt->edit("last_update", function($data) {
    return '<div class="text-center">'.date("Y-m-d H:i:s", strtotime($data['last_update'])).'</div>';
});

$dt->edit("id_module", function($data) {
    return '
        <div class="action text-center gap-2" data-id="'.$data['id_module'].'">
        </div>
    ';
});

$dt->edit("status", function($data) {
    if($data['status'] == -1) {
        return '<div class="text-center"><span class="badge bg-success">Aktif</span></div>';
    }else {
        return '<div class="text-center"><span class="badge bg-danger">Nonaktif</span></div>';
    }
});

echo $dt->generate()->toJson();