<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $filePermission['desc']; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Transaction</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $filePermission['desc']; ?></li>
        </ol>
    </div>
    <div>
        <?php require_once __DIR__ . "/create.php"; ?>
    </div>
</div>


<div class="card custom-card overflow-hidden">
    <div class="card-header">
        <h5 class="main-content-label mb-1">History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="table-history" class="table table-striped table-hover" width="100%" data-url="/ajax/datatable<?= $filePermission['link']; ?>">
                <thead>
                    <tr>
                        <th width="10%" style="vertical-align: middle" class="text-center">Created At</th>
                        <th width="15%" style="vertical-align: middle" class="text-center">Account</th>
                        <th width="10%" style="vertical-align: middle" class="text-center">Type</th>
                        <th width="10%" style="vertical-align: middle" class="text-center">Ticket</th>
                        <th width="15%" style="vertical-align: middle" class="text-center">Amount</th>
                        <th style="vertical-align: middle" class="text-center">Comment</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    let table_history;
    $(document).ready(function() {
        table_history = $('#table-history').DataTable({
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
            lengthMenu: [[50, 100, -1], [50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: $('#table-history').data('url'),
                contentType: "application/json",
                type: "GET"
            },
            columnDefs: [
                { targets: 1, className: 'text-center' },
                { targets: 2, className: 'text-center' },
                { targets: 4, className: 'text-end' },
            ]
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
    })
</script>
