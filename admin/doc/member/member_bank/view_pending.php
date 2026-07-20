<?php if($permisPendingView = $adminPermissionCore->isHavePermission($moduleId, "view.pending")) : ?>
    <div class="card custom-card mb-3">
        <div class="card-header">
            <h5 class="card-title">Request Pending</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-striped" id="table-pending">
                    <thead>
                        <tr>
                            <th width="15%">Date Time</th>
                            <th>User</th>
                            <th>Detail</th>
                            <th>File</th>
                            <th width="10%">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let table_pending;
        $(document).ready(function() {
            table_pending = $('#table-pending').DataTable({
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
                order: [[0, 'desc']],
                ajax: {
                    url: "/ajax/datatable/member/member_bank/pending"
                },
            })
            try {
                const btnRef = table_pending.button('refresh:name');
                const $nodeRef = $(btnRef.node && btnRef.node() || []);
                const originalRefText = $nodeRef.data('original-text') || 'Refresh';

                table_pending.on('processing.dt', function (e, settings, processing) {
                    const btn = table_pending.button('refresh:name');
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

                table_pending.on('xhr.dt', function () {
                    const btn = table_pending.button('refresh:name');
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

    <?php require_once __DIR__ . "/action_button.php"; ?>
<?php endif; ?>