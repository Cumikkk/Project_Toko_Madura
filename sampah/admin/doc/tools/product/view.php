<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Product</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Tools</li>
            <li class="breadcrumb-item active" aria-current="page">Product</li>
        </ol>
    </div>
    <div>
        <?php if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
            <a href="<?= $permisCreate['link'] ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Product</a>
        <?php endif; ?>
    </div>
</div>
<div class="row row-sm">
    <div class="col-lg-12 col-md-12 col-md-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped" id="table-product" data-url="<?= $filePermission['link']; ?>">
                        <thead>
                            <tr>
                                <th width="8%">Suffix</th>
                                <th>Name</th>
                                <th>Detail</th>
                                <th>Group</th>
                                <th>Trading Rules</th>
                                <th>Status</th>
                                <th>#</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let table;
    $(document).ready(function() {
        table = $('#table-product').DataTable({
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
            order: [[0, 'asc']],
            lengthMenu: [[10, 50, 100], [10, 50, 100]],
            ajax: {
                url: "/ajax/datatable".concat($('#table-product').data('url')),
                contentType: "application/json",
                type: "GET"
            },
            columnDefs: [
                { targets: 4, className: "text-center" },
                { targets: 5, className: "text-center" },
                { targets: 6, className: "text-center" },
            ]
        })
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

<?php require_once __DIR__ . "/update_button.php"; ?>