<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Internal Transfer</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Transaction</li>
            <li class="breadcrumb-item active" aria-current="page">Internal Transfer</li>
        </ol>
    </div>
</div>


<div class="row row-sm">
    <div class="col-lg-12 mb-3">
        <div class="card custom-card overflow-hidden">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label">Date Range</label>
                    <input type="text" id="filterDateRangeHistory" class="form-control" placeholder="Select date range" autocomplete="off" readonly>
                    <input type="hidden" id="filterDateFrom">
                    <input type="hidden" id="filterDateTo">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="btnResetFilter" class="btn btn-secondary me-2">
                        <i class="fe fe-refresh-cw"></i> Reset Filter
                    </button>
                </div>
            </div>
        </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_history" class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100" >
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" rowspan="2" class="text-center">Date Time</th>
                                <th style="vertical-align: middle" rowspan="2" class="text-center">Name</th>
                                <th style="vertical-align: middle" rowspan="2" class="text-center">Email</th>
                                <th style="vertical-align: middle" colspan="2" class="text-center">Account</th>
                                <th style="vertical-align: middle" rowspan="2" class="text-center">Amount</th>
                            </tr>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Source</th>
                                <th style="vertical-align: middle" class="text-center">Destination</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        table_history = $('#table_history').DataTable({
            dom: 'Blfrtip',
            processing: true,
            serverSide: true,
            deferRender: true,
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
            columns: [
                { data: 'IT_DATETIME', className: 'text-center' },
                { data: 'IT_NAME' },
                { data: 'IT_EMAIL' },
                { data: 'FROM_LOGIN' },
                { data: 'TO_LOGIN' },
                { data: 'IT_AMOUNT', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 2, '') }
            ],
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/transaction/internal_transfer/view",
                contentType: "application/json",
                type: "GET",
                data: function(d) {
                    d.filterDateFrom = $('#filterDateFrom').val();
                    d.filterDateTo = $('#filterDateTo').val();
                }
            },
        });
        try {
            const btnRef = table_history.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table_history.on('processing.dt', function (e, settings, processing) {
                const btn = table_history.button('refresh:name');
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

            table_history.on('xhr.dt', function () {
                const btn = table_history.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }

        $('#filterDateRangeHistory').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
            $('#filterDateFrom').val(picker.startDate.format('YYYY-MM-DD'));
            $('#filterDateTo').val(picker.endDate.format('YYYY-MM-DD'));
            table_history.ajax.reload();
        }).on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table_history.ajax.reload();
        });

        // Reset filter button
        $('#btnResetFilter').on('click', function() {
            $('#filterDateRangeHistory').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table_history.ajax.reload();
        });
    });
</script>