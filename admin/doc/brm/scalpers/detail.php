<?php
use App\Models\Helper;
$data   = Helper::getSafeInput($_GET);
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Scalpers</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Business Relation Manager</li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view">Scalpers</a></li>
			<li class="breadcrumb-item active">Detail</li>
		</ol>
	</div>
</div>
<div class="card custom-card overflow-hidden">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="table">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Ticket</th>
                        <th style="vertical-align: middle" class="text-center">Symbol</th>
                        <th style="vertical-align: middle" class="text-center">Lots</th>
                        <th style="vertical-align: middle" class="text-center">Opened At</th>
                        <th style="vertical-align: middle" class="text-center">Closed At</th>
                        <th style="vertical-align: middle" class="text-center">Second</th>
                        <th style="vertical-align: middle" class="text-center">Profit</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    let table;
    $(document).ready(function() {
        table = $('#table').DataTable({
            dom: 'Blfrtip',
            scrollX: true,
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
            ajax: {
                url: "/ajax/datatable/brm/scalpersdetail/view?login=<?= $data["e"] ?>&startdate=<?= $data["f"] ?>&enddate=<?= $data["g"] ?>",
                contentType: "application/json",
                type: "GET"
            },
            columns: [
                { data: 'position_id' },
                { data: 'symbol' },
                { data: 'sum_volume', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) },
                { data: 'open_time', className: 'text-center' },
                { data: 'close_time', className: 'text-center' },
                { data: 'difference', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 0, '', ' sec' ) },
                { data: 'sum_profit', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) }
            ],
            order: [[5, 'asc']],
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
        });
        try {
            const btnRef = table.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            table.on('processing.dt', function (e, settings, processing) {
                const btn = table.button('refresh:name');
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

            table.on('xhr.dt', function () {
                const btn = table.button('refresh:name');
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