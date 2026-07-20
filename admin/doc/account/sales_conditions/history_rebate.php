<?php
    $accountHash = htmlspecialchars($_GET['account'] ?? '', ENT_QUOTES, 'UTF-8');

    $sqlGetPartner = $db->query(" 
        SELECT 
            tm.MBR_NAME as `name`,
            racc.ACC_LOGIN as `login`
        FROM tb_racc racc
        LEFT JOIN tb_member tm ON tm.MBR_ID = racc.ACC_MBR
        WHERE MD5(MD5(racc.ACC_LOGIN)) = '{$accountHash}'
    ");
    $partnerInfo = $sqlGetPartner->fetch_assoc();
?>

<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">History Rebate</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Account</li>
			<li class="breadcrumb-item"><a href="/account/sales_conditions/view">Sales Conditions</a></li>
			<li class="breadcrumb-item active" aria-current="page">History Rebate</li>
		</ol>
	</div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h5 class="card-title mb-0 fw-semibold text-dark">
                                History Rebate
                            </h5>
                        </div>
                        <div class="ms-md-auto">
                            <div class="d-flex align-items-center gap-3 bg-light border rounded-3 px-3 py-2 shadow-sm">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-dark lh-1 mb-1"><?= htmlspecialchars($partnerInfo['name'] ?? '-') ?></div>
                                    <div class="small text-muted lh-1">
                                        Login <span class="fw-semibold text-primary"><?= htmlspecialchars($partnerInfo['login'] ?? '-') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-12 col-md-6">
                        <label class="form-label mb-1">Date Range</label>
                        <input type="text" class="form-control" id="rebate_date_range" placeholder="Select date range" readonly>
                        <input type="hidden" id="rebate_start_date">
                        <input type="hidden" id="rebate_end_date">
                    </div>
                    <div class="col-auto mt-1">
                        <button type="button" class="btn btn-outline-secondary" id="btn_reset_rebate">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered table-striped" id="table_history_rebate">
                    <thead>
                        <tr>
                            <th style="vertical-align: middle">Date</th>
                            <th style="vertical-align: middle">Code</th>
                            <th style="vertical-align: middle">Amount</th>
                            <th style="vertical-align: middle">Volume</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let tableHistoryRebate;
    
    $(document).ready(function() {
        tableHistoryRebate = $('#table_history_rebate').DataTable({
            dom: "Brl<'table-responsive't>p",
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
                url: '/ajax/datatable/account/history_rebate',
                type: 'get',
                data: function(d) {
                    d.account = '<?= $accountHash ?>';
                    d.start_date = $('#rebate_start_date').val();
                    d.end_date = $('#rebate_end_date').val();
                }
            },
            columns: [
                { data: 'executed_date' },
                { data: 'code' },
                { data: 'amount' },
                { data: 'volume' },
            ]
        })

        try {
            if ($.fn.daterangepicker && typeof moment !== 'undefined') {
                $('#rebate_date_range').daterangepicker({
                    autoUpdateInput: false,
                    showDropdowns: true,
                    opens: 'left',
                    locale: {
                        format: 'YYYY-MM-DD',
                        separator: ' - ',
                        applyLabel: 'Apply',
                        cancelLabel: 'Clear'
                    }
                });

                $('#rebate_date_range').on('apply.daterangepicker', function(ev, picker) {
                    const startDate = picker.startDate.format('YYYY-MM-DD');
                    const endDate = picker.endDate.format('YYYY-MM-DD');
                    $(this).val(startDate + ' - ' + endDate);
                    $('#rebate_start_date').val(startDate);
                    $('#rebate_end_date').val(endDate);
                    tableHistoryRebate.ajax.reload();
                });

                $('#rebate_date_range').on('cancel.daterangepicker', function() {
                    $(this).val('');
                    $('#rebate_start_date').val('');
                    $('#rebate_end_date').val('');
                    tableHistoryRebate.ajax.reload();
                });
            } else {
                console && console.warn && console.warn('daterangepicker is not available on this page');
            }

            $('#btn_reset_rebate').on('click', function() {
                $('#rebate_date_range').val('');
                $('#rebate_start_date').val('');
                $('#rebate_end_date').val('');
                tableHistoryRebate.ajax.reload();
            });

            const btnRef = tableHistoryRebate.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tableHistoryRebate.on('processing.dt', function (e, settings, processing) {
                const btn = tableHistoryRebate.button('refresh:name');
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

            tableHistoryRebate.on('xhr.dt', function () {
                const btn = tableHistoryRebate.button('refresh:name');
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