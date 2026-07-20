<div class="dashboard-breadcrumb mb-25">
    <h2>Your Retention</h2>
</div>
<div class="row mb-3">
    <div class="col-12">
        <div class="panel">
            <div class="panel-header">
                <h5 class="panel-title">Waiting on the Retention</h5>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="tabel_dorman">
                            <thead>
                                <tr class="text-center">
                                    <th>Date Keep</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Balance</th>
                                    <th>Date Expired</th>
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
<div class="row mb-3">
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-header">
                <h5 class="panel-title">Pending Confirm Retention</h5>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="tabel_pending">
                            <thead>
                                <tr class="text-center">
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-header">
                <h5 class="panel-title">Available Confirm Retention</h5>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="tabel_available">
                            <thead>
                                <tr class="text-center">
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $getdormanexpired = $db->query("DELETE FROM tb_dormankeep WHERE tb_dormankeep.DRMKEEP_DATETIMEEXTEND < NOW()"); ?>
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
                url: "/ajax/datatable/dorman_keep",
                data: function(d){ return $.extend(d, prm); }
            },
            columnDefs: [
                { targets: 0,className: "text-center", },
                { targets: 1,className: "text-start", },
                { targets: 2,className: "text-start", },
                { targets: 3,className: "text-start", },
                {
                    targets: 4,
                    className: "text-end",
                    render: function(data, type, row, meta){
                        return Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data);
                    }
                },
                { targets: 5,className: "text-center", },
                {
                    targets: 6,
                    className: "text-center",
                    render: function(data, type, row, meta){
                        return `<button class="btn btn-sm btn-primary btn-keep" data-id="${data}">Extend</button> | 
                        <button class="btn btn-sm btn-success btn-confirm" data-id="${data}">Confirm</button>`;
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
                url: '/ajax/post/commission/dorman_extend',
                type: 'POST',
                data: { id: id },
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

        $('#tabel_dorman').on('click', '.btn-confirm', function() {
            // Simpan referensi ke tombol yang diklik
            let $btn = $(this);
            let id = $btn.data('id');
            
            // Disable tombol dan tambahkan indikator loading
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
            
            $.ajax({
                url: '/ajax/post/commission/dorman_confirm',
                type: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    tabel_dorman.ajax.reload(function() {
                    });
                    tabel_pending.ajax.reload(function() {
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
        
        tabel_pending = $('#tabel_pending').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: "/ajax/datatable/dorman_pending",
                data: function(d){ return $.extend(d, prm); }
            },
            columnDefs: [
                { targets: 0,className: "text-start", },
                { targets: 1,className: "text-start", },
                { targets: 2,className: "text-start", },
                {
                    targets: 3,
                    className: "text-end",
                    render: function(data, type, row, meta){
                        return Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data);
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
        
        tabel_available = $('#tabel_available').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: "/ajax/datatable/dorman_available",
                data: function(d){ return $.extend(d, prm); }
            },
            columnDefs: [
                { targets: 0,className: "text-start", },
                { targets: 1,className: "text-start", },
                { targets: 2,className: "text-start", },
                {
                    targets: 3,
                    className: "text-end",
                    render: function(data, type, row, meta){
                        return Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(data);
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
    });
</script>