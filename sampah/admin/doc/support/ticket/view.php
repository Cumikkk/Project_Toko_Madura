<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Ticket</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Support</li>
            <li class="breadcrumb-item active" aria-current="page">Ticket</li>
        </ol>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="tbl_tckt" class="table table-striped table-hover table-bordered">
                <thead>
                    <tr class="text-center">
                        <th style="vertical-align: middle;">Date Req</th>
                        <th style="vertical-align: middle;">Last Confersation Date</th>
                        <th style="vertical-align: middle;">Email</th>
                        <th style="vertical-align: middle;">Code</th>
                        <th style="vertical-align: middle;">Topic</th>
                        <th style="vertical-align: middle;">Subject</th>
                        <th style="vertical-align: middle;">Status</th>
                        <th style="vertical-align: middle;">#</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    $(document).ready(() => {
        tabel_symbol = $('#tbl_tckt').DataTable({
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
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/support/ticket/view",
                contentType: "application/json",
                type: "GET"
            },
        });
        
        try {
            const btnRef = tabel_symbol.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tabel_symbol.on('processing.dt', function (e, settings, processing) {
                const btn = tabel_symbol.button('refresh:name');
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

            tabel_symbol.on('xhr.dt', function () {
                const btn = tabel_symbol.button('refresh:name');
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