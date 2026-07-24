<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">List Admin <?= $user['ADM_LEVEL'] ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin</li>
        </ol>
    </div>
</div>

<?php 
$isMasterOwner = (($user['ADM_LEVEL'] ?? 1) == 2);
Allmedia\Shared\AdminPermission\SharedViews::render("admins/view", [
    'isAllowToCreate' => $adminPermissionCore->isHavePermission($moduleId, "create"),
    'isAllowToUpdate' => $adminPermissionCore->isHavePermission($moduleId, "update"),
    'isAllowToDelete' => $adminPermissionCore->isHavePermission($moduleId, "delete"),
    'isAllowToUpdatePermission' => $isMasterOwner ? false : $adminPermissionCore->isHavePermission($moduleId, "update.permission"),
]); 
?>

<?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
    <script type="text/javascript">
        $(document).ready(function() {
            if(table) {
                table.on('draw.dt', function(evt) {
                    $.each($('#table tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(5);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-delete').length) {
                                let id = actionArea.data('id');
                                actionArea.append(`<a class="btn btn-danger btn-sm text-white btn-delete me-1" data-id="${id}"><i class="fas fa-trash"></i></a>`)
                            }
                        }
                    })

                    $('.btn-delete').on('click', function(event) {
                        let element = $(event.currentTarget);
                        if(element && element.data('id')) {
                            Swal.fire({
                                title: "Nonaktifkan Admin?",
                                text: "Apakah anda yakin ingin menonaktifkan admin ini?",
                                icon: "question",
                                showCancelButton: true,
                                reverseButtons: true
                            }).then((result) => {
                                if(result.isConfirmed) {
                                    Swal.fire({
                                        text: "Loading...",
                                        allowOutsideClick: false,
                                        didOpen: function() {
                                            Swal.showLoading();
                                        }
                                    })
    
                                    $.post("/ajax/post/admin/delete", {id: element.data('id')}, (resp) => {
                                        Swal.fire(resp.alert).then(() => {
                                            if(resp.success) {
                                                location.reload();
                                            }
                                        })
                                    }, 'json')
                                }
                            })
                        }
                    })
                })
            }
        })
    </script>
<?php endif; ?>