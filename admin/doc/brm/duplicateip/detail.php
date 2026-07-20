<?php
use App\Models\Helper;
$data   = Helper::getSafeInput($_GET);
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Duplicate IP</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Business Relation Manager</li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view">Duplicate IP</a></li>
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
                        <th style="vertical-align: middle" class="text-center">Account</th>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Last Access</th>
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
                url: "/ajax/datatable/brm/duplicateipdetail/view?ip=<?= $data["e"] ?>",
                contentType: "application/json",
                type: "GET"
            },
            columns: [
                { data: 'LOGIN' },
                { data: 'NAME' },
                { data: 'LASTACCESS' }
            ],
            order: [[0, 'desc']],
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