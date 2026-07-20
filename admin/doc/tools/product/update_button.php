<?php if($permisUpdate = $adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
    <script type="text/javascript">
        $(document).ready(function() {
            if(table) {
                table.on('draw.dt', function() {
                    $.each($('#table-product tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(6);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-update').length) {
                                let suffix = actionArea.data('suffix');
                                actionArea.append(`<a href="<?= $permisUpdate['link'] ?>/${suffix}" class="btn btn-sm btn-success btn-update"><i class="fas fa-edit text-white"></i></a>`);
                            }
                        }
                    })
                })
            }
        })
    </script>
<?php endif; ?>