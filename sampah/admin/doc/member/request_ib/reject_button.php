<script type="text/javascript">
    $(document).ready(function() {
        if(table_pending) {
            table_pending.on('draw.dt', function() {
                $.each($('#table_pending tbody tr'), (i, tr) => {
                    let td = $(tr).find('td').eq(4);
                    if(td) {
                        let actionArea = td.find('.action');
                        if(actionArea && !actionArea.find('.btn-delete').length && actionArea.data('data')) {
                            let data = JSON.parse(atob(actionArea.data('data')));
                            let dataArray = [];
                            for(i in data) {
                                dataArray.push(`data-${i}="${data[i]}"`);
                            }
                            actionArea.append(`<a href="javascript:void(0)" class="btn btn-sm btn-danger btn-delete" data-type="reject" ${dataArray.join(" ")}><i class="fas fa-trash"></i></a>`);
                        }
                    }
                })

                $('.btn-delete').on('click', function(evt) {
                    let target = $(evt.currentTarget);
                    if(target) {
                        Swal.fire({
                            title: "Delete This Record",
                            text: "Are you sure to continue?",
                            icon: "question",
                            input: 'text',
                            inputLabel: 'Reason for rejection',
                            inputPlaceholder: 'Type your reason here...',
                            inputAttributes: {
                                'aria-label': 'Type your reason here'
                            },
                            showCancelButton: true,
                            reverseButtons: true,
                            preConfirm: (note) => {
                                if(!note) {
                                    Swal.showValidationMessage(
                                        'Reason is required'
                                    )
                                }
                                return note;
                            }
                        }).then((result) => {
                            if(result.isConfirmed) {
                                Swal.fire({
                                    text: "Please wait...",
                                    allowOutsideClick: false,
                                    didOpen: function() {
                                        Swal.showLoading();
                                    }
                                })

                                let data = {
                                    id: target.data('id'), 
                                    note: result.value,
                                    type: "reject"
                                };

                                $.post("/ajax/post/member/request_ib/action", data, (resp) => {
                                    Swal.fire(resp.alert).then(() => {
                                        if(resp.success) {
                                            table_pending.draw();
                                        }
                                    })
                                }, "json")
                            }
                        })     
                    }
                })
            })
        }
    })
</script>