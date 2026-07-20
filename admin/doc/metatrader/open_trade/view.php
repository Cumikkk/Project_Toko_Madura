
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Open Trade</h2>
        <ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">MetaTrader</li>
            <li class="breadcrumb-item active" aria-current="page">Open Trade</li>
        </ol>
    </div>
</div>
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="myOwnTable" class="table table-striped table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Date Time</th>
                                <th style="vertical-align: middle" class="text-center">Login</th>
                                <th style="vertical-align: middle" class="text-center">Name</th>
                                <th style="vertical-align: middle" class="text-center">Ticket</th>
                                <th style="vertical-align: middle" class="text-center">Type</th>
                                <th style="vertical-align: middle" class="text-center">Symbol</th>
                                <th style="vertical-align: middle" class="text-center">Volume</th>
                                <th style="vertical-align: middle" class="text-center">SL</th>
                                <th style="vertical-align: middle" class="text-center">TP</th>
                                <th style="vertical-align: middle" class="text-center">Price</th>
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
        table_history = $('#myOwnTable').DataTable({
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
                { data: 'datetime', className: 'text-center' },
                { data: 'login' },
                { data: 'fullname' },
                { data: 'ticket' },
                { data: 'type' },
                { data: 'symbol' },
                { data: 'volume', className: 'text-end' },
                { data: 'sl', className: 'text-end' },
                { data: 'tp', className: 'text-end' },
                { data: 'price', className: 'text-end' }
            ],
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/metatrader/open_trade/view",
                contentType: "application/json",
                type: "GET"
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
    });
</script>