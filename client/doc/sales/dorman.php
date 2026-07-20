<?php
use Config\Core\Database;
?>
<div class="dashboard-breadcrumb mb-25">
    <h2>Dorman</h2>
</div>
<div class="row">
    <div class="col-12">
        <div class="panel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="tabel_dorman">
                            <thead>
                                <tr class="text-center">
                                    <th>Last Transaction</th>
                                    <th>Full Name</th>
                                    <th>Balance</th>
                                    <th>Days</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
    $getdormanexpired = $db->query("DELETE FROM tb_dormankeep WHERE tb_dormankeep.DRMKEEP_DATETIMEEXTEND < NOW()");
?>
<script type="text/javascript">
    let tabel_dorman;
    $(document).ready(function() {
        let prm = {
            "dte" : ''
        };
        tabel_dorman = $('#tabel_dorman').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: "/ajax/datatable/dorman",
                data: function(d){
                    return $.extend(d, prm);
                }
            },
            columnDefs: [
                {
                    targets: 0,
                    className: "text-center",
                },
                {
                    targets: 1,
                    className: "text-start",
                },
                {
                    targets: 2,
                    className: "text-end",
                    render: function(data, type, row, meta){
                        return Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data);
                    }
                },
                {
                    targets: 3,
                    className: "text-center",
                },
                {
                    targets: 4,
                    className: "text-center",
                    render: function(data, type, row, meta){
                        return `<button class="btn btn-sm btn-primary btn-keep" data-id="${data}">Keep</button>`;
                    }
                }
            ],
            drawCallback : (tbl) => {
                // $(`#fbd`).remove();
                if(!$(`#fbd`).length){
                    $(`input[aria-controls="${$(tbl.nTable).attr('id')}"]`).parent().before(`
                        <label id="fbd">
                            <input type="text" style="display:none;" class="filterDate datepicker" ${(prm.dte.length) ? `value="${prm.dte}"` : ``} style="width: 225px;" placeholder="filter by date">
                        </label>
                    `);
                }
                $('.filterDate').daterangepicker({
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                }).on('apply.daterangepicker', function(ev, picker){
                    prm.dte = `${picker.startDate.format('YYYY-MM-DD')},${picker.endDate.format('YYYY-MM-DD')}`;
                    console.log(prm.dte);
                    tabel_dorman.ajax.reload();
                });
            }
        });

        $('#tabel_dorman').on('click', '.btn-keep', function() {
            // Simpan referensi ke tombol yang diklik
            let $btn = $(this);
            let id = $btn.data('id');
            
            // Disable tombol dan tambahkan indikator loading
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            
            $.ajax({
                url: '/ajax/post/commission/dorman_keep',
                type: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    tabel_dorman.ajax.reload(function() {
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    $btn.prop('disabled', false).html('Keep');
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process request. Please try again.'
                    });
                }
            });
        });
    });
</script>