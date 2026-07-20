<?php

use App\Models\Helper;

    $accountHash = htmlspecialchars($_GET['account'] ?? '', ENT_QUOTES, 'UTF-8');
    $sqlGetPartner = $db->query(" 
        SELECT 
            MAX(td.DPWD_DATETIME) as `date`,
            tm.MBR_NAME as `name`,
            td.DPWD_CURR_TO as `currency`,
            IFNULL(SUM(td.DPWD_AMOUNT), 0) as `amount_shared`
        FROM tb_dpwd td
        LEFT JOIN tb_member tm ON tm.MBR_ID = td.DPWD_MBR
        WHERE td.DPWD_TYPE = 4 AND MD5(MD5(td.DPWD_MBR)) = '{$accountHash}'
    ");
    $partnerInfo = $sqlGetPartner->fetch_assoc();

    $dateValue = $partnerInfo['date'] ?? '-';
    $nameValue = $partnerInfo['name'] ?? '-';
    $currencyValue = $partnerInfo['currency'] ?? '-';
    $amountValue = (float) ($partnerInfo['amount_shared'] ?? 0);
?>

<div class="page-header mb-4">
	<h2 class="main-content-title tx-24 font-weight-bold">Request Rebate By Partner</h2>

	<ol class="breadcrumb breadcrumb-transparent">
		<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard" class="text-primary">Home</a></li>
		<li class="breadcrumb-item">Account</li>
		<li class="breadcrumb-item"><a href="/account/sales_conditions/view" class="text-primary">Sales Conditions</a></li>
		<li class="breadcrumb-item active" aria-current="page">Request Rebate By Partner</li>
	</ol>
</div>

<div class="row mt-3">
	<div class="col-lg-12">
		<div class="card shadow-sm border-0 rounded-lg mb-4">
			<div class="card-body p-4 p-lg-5 bg-light">
				<div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
					<div>
						<h5 class="mb-1 font-weight-bold text-dark">Rebate Information</h5>
						<p class="mb-0 text-muted">Ringkasan partner dan total rebate yang sudah tercatat.</p>
					</div>
					<span class="badge badge-primary px-3 py-2 mt-3 mt-md-0">Partner Rebate</span>
				</div>

				<div class="row">

					<div class="col-md-4 mb-3">
						<div class="card border shadow-sm h-100 rounded-lg">
							<div class="card-body">
								<div class="d-flex align-items-center">
                                    <div class="rounded bg-primary text-white p-3 mr-3">
                                        <i class="fas fa-user"></i>
									</div>
									<div class="flex-grow-1 m-3">
										<div class="text-uppercase text-muted small font-weight-bold mb-1">Member Name</div>
										<div class="font-weight-bold text-dark text-break"><?= htmlspecialchars($nameValue) ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-4 mb-3">
						<div class="card border shadow-sm h-100 rounded-lg">
							<div class="card-body">
								<div class="d-flex align-items-center">
                                    <div class="rounded bg-primary text-white p-3 mr-3">
                                            <i class="fas fa-clock"></i>
									</div>
									<div class="flex-grow-1 m-3">
										<div class="text-uppercase text-muted small font-weight-bold mb-1">last date received</div>
										<div class="font-weight-bold text-dark text-break"><?= htmlspecialchars($dateValue) ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-4 mb-3">
						<div class="card border-success shadow-sm h-100 rounded-lg bg-white">
							<div class="card-body">
								<div class="d-flex align-items-center">
                                    <div class="rounded bg-success text-white p-3 mr-3">
                                        <i class="fas fa-dollar-sign"></i>
									</div>
									<div class="flex-grow-1 m-3">
										<div class="text-uppercase text-muted small font-weight-bold mb-1">Amount</div>
										<div class="font-weight-bold text-success h4 mb-0"><?= Helper::currencyToSymbol($currencyValue) ?> <?= Helper::formatCurrency($amountValue) ?></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <div class="col-md-12 mt-3">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-white">
                <h5 class="card-title mb-1 font-weight-bold">Source Rebate</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-striped mb-0" id="table_source_rebate">
                    <thead>
                        <tr>
                            <th class="align-middle">Date</th>
                            <th class="align-middle">Login</th>
                            <th class="align-middle">Client Name</th>
                            <th class="align-middle">Account</th>
                            <th class="align-middle">Commission</th>
                            <th class="align-middle">Volume</th>
                            <th class="align-middle">Amount</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let tableSourceRebate;
    
    $(document).ready(function() {
        tableSourceRebate = $('#table_source_rebate').DataTable({
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
                url: '/ajax/datatable/account/history_source_rebate',
                type: 'get',
                data: function(d) {
                    d.account = '<?= $accountHash ?>';
                }
            },
            columns: [
                { data: 'date' },
                { data: 'login' },
                { data: 'name' },
                { data: 'sales_type' },
                { data: 'commission' },
                { data: 'volume' },
                { data: 'amount' },
            ]
        })

        try {
            const btnRef = tableSourceRebate.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tableSourceRebate.on('processing.dt', function (e, settings, processing) {
                const btn = tableSourceRebate.button('refresh:name');
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

            tableSourceRebate.on('xhr.dt', function () {
                const btn = tableSourceRebate.button('refresh:name');
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