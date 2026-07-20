<?php 
use App\Models\Helper;

    $codeHash = htmlspecialchars($_GET['code'] ?? '', ENT_QUOTES, 'UTF-8');
    $loginHash = htmlspecialchars($_GET['login'] ?? '', ENT_QUOTES, 'UTF-8');
    $sqlGetDpwd = $db->query(" 
        SELECT
            rb.login as `login`,
            rb.executed_date as `executed_date`,
            tm.MBR_NAME as `name`,
            IFNULL(sh.currency, '-') as `currency`,
            IFNULL(sh.amount_shared, 0) as `amount_shared`
        FROM (
            SELECT
                rh.H_LOGIN as login,
                MAX(rh.H_EXECUTE_AT) as executed_date
            FROM tb_rebate_history rh
            WHERE MD5(MD5(rh.H_CODE)) = '{$codeHash}'
              AND MD5(MD5(rh.H_LOGIN)) = '{$loginHash}'
            GROUP BY rh.H_LOGIN
        ) rb
        LEFT JOIN tb_racc racc ON racc.ACC_LOGIN = rb.login
        LEFT JOIN tb_member tm ON tm.MBR_ID = racc.ACC_MBR
        LEFT JOIN (
            SELECT
                td.DPWD_RACC as login,
                td.DPWD_CURR_TO as currency,
                SUM(td.DPWD_AMOUNT) as amount_shared
            FROM tb_dpwd td
            WHERE td.DPWD_TYPE = 4
              AND MD5(MD5(td.DPWD_CODE)) = '{$codeHash}'
              AND MD5(MD5(td.DPWD_RACC)) = '{$loginHash}'
            GROUP BY td.DPWD_RACC
        ) sh ON sh.login = rb.login
    ");
    $rebateInfo = $sqlGetDpwd->fetch_assoc();
?>
<div class="page-header mb-4">
	<h2 class="main-content-title tx-24 font-weight-bold">Summary Rebate</h2>
	<ol class="breadcrumb breadcrumb-transparent">
		<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard" class="text-primary">Home</a></li>
		<li class="breadcrumb-item">Account</li>
		<li class="breadcrumb-item"><a href="/account/sales_conditions/view" class="text-primary">Sales Conditions</a></li>
		<li class="breadcrumb-item active" aria-current="page">Summary Rebate</li>
	</ol>
</div>

<div class="row mt-3">
	<div class="col-lg-12">
		<div class="card border-0 shadow-lg" style="border-left: 4px solid #007bff; overflow: hidden;">
			<div class="card-body p-5">
				<h5 class="card-title text-muted text-uppercase font-weight-bold mb-5" style="font-size: 0.75rem; letter-spacing: 1px;">
					<i class="fas fa-chart-line text-primary mr-2"></i>Rebate Information
				</h5>

				<div class="row">
					<div class="col-md-3 mb-4">
						<p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem;">Login Account</p>
						<p class="text-dark font-weight-bold" style="font-size: 1.2rem;">
							<?= htmlspecialchars($rebateInfo['login'] ?? '-') ?>
						</p>
					</div>

					<div class="col-md-3 mb-4">
						<p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem;">
							<i class="fas fa-user text-primary mr-1"></i> Member Name
						</p>
						<p class="text-dark font-weight-bold" style="font-size: 1.2rem;">
							<?= htmlspecialchars($rebateInfo['name'] ?? '-') ?>
						</p>
					</div>

					<div class="col-md-3 mb-4">
						<p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem;">
							<i class="fas fa-calendar-alt text-primary mr-1"></i> Execution Date
						</p>
						<p class="text-dark font-weight-bold" style="font-size: 1.2rem;">
							<?= htmlspecialchars($rebateInfo['executed_date'] ?? '-') ?>
						</p>
					</div>

					<div class="col-md-3 mb-4">
						<p class="text-muted mb-2 font-weight-600" style="font-size: 0.85rem;">
							<i class="fas fa-dollar-sign text-success mr-1"></i> Total Amount
						</p>
						<p style="font-size: 1.5rem; font-weight: bold; color: #28a745;">
						    <?= Helper::currencyToSymbol($rebateInfo['currency']) ?> <?= Helper::formatCurrency($rebateInfo['amount_shared']); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Recipient</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered table-striped" id="table_recipient">
                    <thead>
                        <tr>
                            <th style="vertical-align: middle">Client Name</th>
                            <th style="vertical-align: middle">Sales Type</th>
                            <th style="vertical-align: middle">Symbol</th>
                            <th style="vertical-align: middle">Kategori</th>
                            <th style="vertical-align: middle">Commission</th>
                            <th style="vertical-align: middle">Volume</th>
                            <th style="vertical-align: middle">Amount</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let tableRecipient;
    
    $(document).ready(function() {
        tableRecipient = $('#table_recipient').DataTable({
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
                url: '/ajax/datatable/account/history_rebate_recipient',
                type: 'get',
                data: function(d) {
                    d.code = '<?= $codeHash ?>';
                    d.login = '<?= $loginHash ?>';
                }
            },
            columns: [
                { data: 'name' },
                { data: 'sales_type' },
                { data: 'symbol' },
                { data: 'kategori' },
                { data: 'commission' },
                { data: 'volume' },
                { data: 'amount' },
            ]
        })

        try {
            const btnRef = tableRecipient.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tableRecipient.on('processing.dt', function (e, settings, processing) {
                const btn = tableRecipient.button('refresh:name');
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

            tableRecipient.on('xhr.dt', function () {
                const btn = tableRecipient.button('refresh:name');
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