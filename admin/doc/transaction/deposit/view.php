<?php
    use App\Models\Admin;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Deposit</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Transaction</li>
            <li class="breadcrumb-item active" aria-current="page">Deposit</li>
        </ol>
    </div>
    <div>
        <?php if($permissionCreate = $adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
            <a href="<?= $permissionCreate['link'] ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Manual Deposit</a>
        <?php endif; ?>
    </div>
</div>
<?php 
    if($adminPermissionCore->isHavePermission($moduleId, "view.accounting")){
        include(__DIR__.'/view_accounting.php');
    } 
?>
<div class="card custom-card overflow-hidden">
    <div class="card-header">
        <h5 class="main-content-label mb-1">History</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Filter Status</label>
                <select id="filterStatus" class="form-control">
                    <option value="">All Status</option>
                    <option value="-1">Accept</option>
                    <option value="1">Reject</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="segregatedAccountFilter" class="form-label">Filter Segregated Account</label>
                <select name="segregatedAccountFilter" id="segregatedAccountFilter" class="form-control" required>
                    <option value="">Semua</option>
                    <?php $sqlGetBank = $db->query("SELECT * FROM tb_bankadm"); ?>
                    <?php if($sqlGetBank) : ?>
                        <?php foreach($sqlGetBank->fetch_all(MYSQLI_ASSOC) as $row) : ?>
                            <option value="<?= implode("/", [$row['BKADM_NAME'], $row['BKADM_HOLDER'], $row['BKADM_ACCOUNT']]) ?>"><?= implode("/", [$row['BKADM_NAME'], $row['BKADM_HOLDER'], $row['BKADM_ACCOUNT']]) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="col-md-3">
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
            <table id="table_history" class="table table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Date</th>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Login</th>
                        <th style="vertical-align: middle" class="text-center">Bank Account</th>
                        <th style="vertical-align: middle" class="text-center">Segregated Account</th>
                        <th style="vertical-align: middle" class="text-center">Amount</th>
                        <th style="vertical-align: middle" class="text-center">Pic</th>
                        <th style="vertical-align: middle" class="text-center">Note</th>
                        <th style="vertical-align: middle" class="text-center">Status</th>
                        <th style="vertical-align: middle" class="text-center">Document</th>
                        <th style="vertical-align: middle" class="text-center">UTM</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    let table_history;
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
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/transaction/history",
                contentType: "application/json",
                type: "GET",
                data: function(d) {
                    d.filterStatus = $('#filterStatus').val();
                    d.segregatedAccountFilter = $('#segregatedAccountFilter').val();
                    d.filterDateFrom = $('#filterDateFrom').val();
                    d.filterDateTo = $('#filterDateTo').val();
                }
            }
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

        $('#filterStatus, #segregatedAccountFilter').on('change', function() {
            table_history.ajax.reload();
        });

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
            $('#filterStatus').val('');
            $('#segregatedAccountFilter').val('');
            $('#filterDateRangeHistory').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table_history.ajax.reload();
        });
    });
</script>