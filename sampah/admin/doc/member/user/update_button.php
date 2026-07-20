<?php if($permisDetail = $adminPermissionCore->isHavePermission($moduleId, "view.update")) : ?>
    <script type="text/javascript">
        $(document).ready(function() {
            if(table) {
                table.on('draw.dt', function() {
                    $.each($('#table tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(7);
                        let actionArea = $(td).find('.action');
                        let code = actionArea.data('code');

                        if(actionArea && !actionArea.find('.btn-detail').length) {
                            actionArea.append(`<a href="<?= $permisDetail['link'] ?>/${code}" class="btn btn-sm btn-info">Update</a>`)
                        }
                    })
                })
            }
        })
    </script>
<?php endif; ?>