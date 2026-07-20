<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Branch</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Sales Reporting</li>
            <li class="breadcrumb-item active" aria-current="page">Branch</li>
        </ol>
    </div>
    <div class="d-flex">
        <div class="justify-content-center">
            <div class="input-group">
                <input type="date" class="form-control" id="filter_date" name="filter_date" value="<?= date('Y-m-d') ?>" />
                <span class="input-group-btn">
                    <button class="btn ripple btn-primary" type="button" id="btn_filter">Filter</button>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped mg-b-0 text-md-nowrap" id="all_branch">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center">LOGIN</th>
                            <th rowspan="2" class="text-center">NAME</th>
                            <th rowspan="2" class="text-center">EMAIL</th>
                            <th rowspan="2" class="text-center">TYPE</th>
                            <th rowspan="2" class="text-center">TANGGAL FTD</th>
                            <th rowspan="2" class="text-center">RATE (IDR)</th>
                            <th rowspan="2" class="text-center">CHARGE ($)</th>
                            <th rowspan="2" class="text-center">FTD</th>
                            <th colspan="2" class="text-center">DEPOSIT</th>
                            <th colspan="2" class="text-center">WITHDRAWAL</th>
                        </tr>
                        <tr>
                            <th class="text-center">TODAY</th>
                            <th class="text-center">MONTH</th>
                            <th class="text-center">TODAY</th>
                            <th class="text-center">MONTH</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th class="text-center">LOGIN</th>
                            <th class="text-center">NAME</th>
                            <th class="text-center">EMAIL</th>
                            <th class="text-center">TYPE</th>
                            <th class="text-center">TANGGAL FTD</th>
                            <th class="text-center">RATE (IDR)</th>
                            <th class="text-center">CHARGE ($)</th>
                            <th class="text-center">FTD</th>
                            <th class="text-center">TODAY</th>
                            <th class="text-center">MONTH</th>
                            <th class="text-center">TODAY</th>
                            <th class="text-center">MONTH</th>
                        </tr>
                    </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
	let all_branch;
    $(document).ready(function(){
        all_branch = $('#all_branch').DataTable( {
            dom: 'Blfrtip',
            processing: true,
            serverSide: true,
            deferRender: true,
            initComplete: function () {
                this.api().columns().every(function () {
                    var column = this;
                    var title = $(column.footer()).text();
                    
                    $('<input type="text" class="form-control form-control-sm" placeholder="Search ' + title + '" />')
                        .appendTo($(column.footer()).empty())
                        .on('keyup change clear', function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                });
            },
			buttons: [
				{
					extend: 'excel',
					text: 'Excel',
				},
				{
					extend: 'copy',
					text: 'Copy'
				},
                {
                    text: 'Refresh',
                    name: 'refresh',
                    action: function (e, dt, node, config) {
                        const btn = dt.button('refresh:name');
                        const $node = $(btn.node());

                        if (!$node.data('original-text')) {
                            $node.data('original-text', $node.html());
                        }

                        btn.enable(false);
                        btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

                        dt.ajax.reload(null, false);
                    }
                }
			],
            lengthMenu: [[25, 50, 100], [25, 50, 100]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/salesreporting/branch/view",
                type: "GET",
                data: function(d) {
                    d.filter_date = $('#filter_date').val();
                }
            },
            columnDefs: [
                {
                    targets: [5], // RATE (IDR) - kolom ke-6
                    className: 'text-end',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return new Intl.NumberFormat('id-ID').format(data);
                        }
                        return data;
                    }
                },
                {
                    targets: [6], // CHARGE ($) - kolom ke-7
                    className: 'text-end',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(data);
                        }
                        return data;
                    }
                },
                {
                    targets: [7, 8, 9, 10, 11], // FTD, DEPOSIT TODAY/MONTH, WITHDRAWAL TODAY/MONTH
                    className: 'text-end',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var rate = parseFloat(row[5]) || 0; // RATE ada di index 5
                            var value = parseFloat(data) || 0;
                            
                            // Jika rate 0, tampilkan dalam dollar
                            if (rate === 0) {
                                // tambahkan '$ ' + setelah return jika ingin menampilkan symbol rupiah
                                return new Intl.NumberFormat('id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }).format(value);
                            }
                            
                            // Jika rate tidak 0, kalikan dengan rate dan tampilkan dalam rupiah
                            var idrValue = value * rate;
                            // tambahkan 'Rp ' + setelah return jika ingin menampilkan symbol rupiah
                            return new Intl.NumberFormat('id-ID', {
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(idrValue);
                        }
                        return data;
                    }
                }
            ]
        });
        try {
            const btnRef = all_branch.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            all_branch.on('processing.dt', function (e, settings, processing) {
                const btn = all_branch.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = $node.data('original-text') || originalRefText;
                    btn.text(original);
                }
            });

            all_branch.on('xhr.dt', function () {
                const btn = all_branch.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }

        // Filter button event
        $('#btn_filter').on('click', function() {
            all_branch.draw();
        });

        // Filter on enter key
        $('#filter_date').on('keypress', function(e) {
            if (e.which === 13) {
                all_branch.draw();
            }
        });
    });
</script>