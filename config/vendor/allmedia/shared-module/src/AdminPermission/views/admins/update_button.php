<?php if($isAllowToUpdate) : ?>
    <script type="text/javascript">
        $(document).ready(function() {
            if(table) {
                table.on('draw.dt', function(evt) {
                    $.each($('#table tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(5);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-edit').length) {
                                let id = actionArea.data('id');
                                let adminUrl = '<?= \Config\Core\SystemInfo::app("ADMIN_URL") ?>';
                                actionArea.append(`<a href="${adminUrl}/admin/update?c=${id}" class="btn btn-success btn-sm text-white btn-edit me-1"><i class="fas fa-edit"></i></a>`)
                            }
                        }
                    })
                })
            }
        })
    </script>
<?php endif; ?>