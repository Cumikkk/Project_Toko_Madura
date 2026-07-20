
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">NMI Setting</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Commision</li>
			<li class="breadcrumb-item active" aria-current="page">NMI Setting</li>
		</ol>
	</div>
</div>
<div class="card custom-card overflow-hidden">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="table_history">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Division</th>
                        <th style="vertical-align: middle" class="text-center">Structure</th>
                        <th style="vertical-align: middle" class="text-center">Target</th>
                        <th style="vertical-align: middle" class="text-center">Percent</th>
                        <th style="vertical-align: middle" class="text-center">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    let table_history;
    $(document).ready(function() {

        table_history = $('#table_history').DataTable({
            dom: 'Blfrtip',
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
            scrollX: true,
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: "/ajax/datatable/commision/nmisetting/view",
                contentType: "application/json",
                type: "GET",
            },
            order: [[0, 'desc'], [1, 'asc']],
            lengthMenu: [[20, 50, 100], [20, 50, 100]],
            columns: [
                { targets: 0, className: "text-start" },
                { targets: 1, className: "text-start" },
                { targets: 2, className: "text-end", render: $.fn.dataTable.render.number( ',', '.', 0, '' ) },
                { targets: 3, className: "text-end", render: $.fn.dataTable.render.number( ',', '.', 2, '', ' %' ) },
                { targets: 4, className: "text-center" }
            ],
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
<?php include 'update.php'; ?>