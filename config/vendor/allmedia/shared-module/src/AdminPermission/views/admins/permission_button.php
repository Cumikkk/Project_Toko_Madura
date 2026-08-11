<?php if($isAllowToUpdatePermission) : ?>
    <script type="text/javascript">
        $(document).ready(function() {
            if(table) {
                table.on('draw.dt', function(evt) {
                    $.each($('#table tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(5);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.btn-permission').length) {
                                let id = actionArea.data('id');
                                actionArea.append(`<a href="/admin/permission/${id}" class="btn btn-primary btn-sm text-white btn-permission"><i class="fas fa-gear"></i></a>`)
                            }
                        }
                    })
                })
            }
        })
    </script>
<?php endif; ?>