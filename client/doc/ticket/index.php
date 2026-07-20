<div class="dashboard-breadcrumb mb-25">
    <h2>Tiket</h2>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <?php require_once __DIR__ . "/create.php"; ?>
    </div>

    <div class="col-md-8 mb-3">
        <div class="panel">
            <div class="panel-header">
                <h5 class="panel-title">Riwayat</h5>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-ticket">
                        <thead>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <th>Kode</th>
                                <th>Subjek</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#table-ticket').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/ticket",
                data: function() {

                }
            },
            columnDefs: [
                { targets: 0, orderable: true },
                { targets: 1, orderable: false, className: 'text-start' },
                { targets: 2, orderable: false, className: 'text-start' },
                { targets: 3, orderable: false, className: 'text-center' },
            ]
        })
    })
</script>