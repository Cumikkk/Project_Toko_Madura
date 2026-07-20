<?php if($permisAccept = $adminPermissionCore->isHavePermission($moduleId, "action")) : ?>
    <script type="text/javascript">
        $(document).ready(function(){
            if(table) {
                table.on('draw.dt', function() {
                    $.each($('#table tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(6);
                        let actionArea = $(td).find('.action');
                        if(td && actionArea) {
                            let id = actionArea.data('id');
                            if(!actionArea.find('.btn-accept').length) {
                                actionArea.append(`<a class="btn btn-sm btn-success btn-accept" data-id="${id}" data-type="accept" title="confirm"><i class="fas fa-check text-white"></i></a>`);
                            }

                            if(!actionArea.find('.btn-release').length) {
                                actionArea.append(`<a class="btn btn-sm btn-danger btn-release" data-id="${id}" data-type="release" title="release"><i class="fas fa-close text-white"></i></a>`);
                            }
                        }
                    })

                    $('.btn-accept, .btn-release').on('click', function(evt) {
                        let target = $(evt.currentTarget);
                        let action = target.data('type');
                        if(target) {
                            Swal.fire({
                                title: `${action.charAt(0).toUpperCase() + action.slice(1)} Retention Queue`,
                                text: `Confirm to continue`,
                                icon: "question",
                                showCancelButton: true,
                                reverseButtons: true
                            }).then((result) => {
                                if(result.isConfirmed) {
                                    Swal.fire({
                                        text: "Please wait...",
                                        allowOutsideClick: false,
                                        didOpen: function() {
                                            Swal.showLoading();
                                        } 
                                    })

                                    let data = target.data();
                                    $.post("/ajax/post<?= $permisAccept['link'] ?>", data, (resp) => {
                                        Swal.fire(resp.alert).then(() => {
                                            if(resp.success) {
                                                table.draw();
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