<?php
    use App\Models\AccountType;
    use App\Factory\UtmHandler;
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Progress Real Account</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Account</li>
			<li class="breadcrumb-item active" aria-current="page">Progress Real Account</li>
		</ol>
	</div>
</div>

<!-- Row Filter -->
<div class="row row-sm">
	<div class="col-lg-12">
		<div class="card custom-card">
			<div class="card-header">
				<h6 class="card-title mb-0">Filter Data</h6>
			</div>
			<div class="card-body">
				<div class="row g-3">
					<div class="col-md-3">
						<label class="form-label">Filter Type Account</label>
						<select id="filterTypeAccount" class="form-control">
							<option value="">All Type Account</option>
							<?php foreach (AccountType::all() as $type): ?>
                                <option value="<?= htmlspecialchars($type['ID_RTYPE']) ?>"><?= htmlspecialchars(implode(' / ', [$type['RTYPE_NAME'], $type['RTYPE_KOMISI'], $type['RTYPE_ISFLOATING'] == 1 ? 'Floating' : ($type['RTYPE_RATE'] / 1000)])) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Filter Platform</label>
						<select id="filterPlatform" class="form-control">
                            <option value="">All Platform</option>
                            <option value="SPA">SPA</option>
                            <option value="Multilateral">Multilateral</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Filter Rate</label>
						<select id="filterRate" class="form-control">
							<option value="">All Rate</option>
							<option value="10000">10000</option>
							<option value="14000">14000</option>
							<option value="0">FLOTING</option>
						</select>
					</div>
                    <div class="col-md-3">
                        <label class="form-label">Filter UTM</label>
                        <div class="input-group">
                            <select id="filterUTM" class="form-control">
                                <option value="">All UTM</option>
                                <?php foreach (UtmHandler::$utmKeys as $utm): ?>
                                    <?php if ($utm === 'referral') continue; ?>
                                    <option value="<?= htmlspecialchars($utm) ?>"><?= htmlspecialchars($utm) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" class="form-control" id="filterUTMValue" placeholder="Enter UTM value">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date Range</label>
                        <input type="text" id="filterDateRangeHistory" class="form-control" placeholder="Select date range" autocomplete="off" readonly>
                        <input type="hidden" id="filterDateFrom">
                        <input type="hidden" id="filterDateTo">
                    </div>
					<div class="col-md-6 d-flex align-items-end">
						<button type="button" id="btnResetFilter" class="btn btn-secondary me-2">
							<i class="fe fe-refresh-cw"></i> Reset Filter
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row row-sm">
	<div class="col-lg-12">
		<div class="card custom-card overflow-hidden">
			<div class="card-body">
				<div class="table-responsive">
					<table id="table-progress-real-account" class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100" >
						<thead>
							<tr class="text-center">
								<th>Date Reg.</th>
								<th>Full Name</th>
								<th>Email</th>
								<th>Type Account</th>
								<th>Platform</th>
								<th>Rate</th>
								<th>UTM</th>
								<th>Status</th>
								<th>Detail</th>
							</tr>
						</thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        var table = $('#table-progress-real-account').DataTable( {
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
            lengthMenu: [[10, 50, 100], [10, 50, 100]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/account/progress_real_account/view",
                type: "GET",
                data: function(d) {
                    d.filterTypeAccount = $('#filterTypeAccount').val();
                    d.filterPlatform = $('#filterPlatform').val();
                    d.filterRate = $('#filterRate').val();
                    d.filterUTM = $('#filterUTM').val();
                    d.filterUTMValue = $('#filterUTMValue').val();
                    d.filterDateFrom = $('#filterDateFrom').val();
                    d.filterDateTo = $('#filterDateTo').val();
                }
            },
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

        $('#filterUTMValue').on('input', function() {
            table.ajax.reload();
        });

        $('#filterTypeAccount, #filterPlatform, #filterRate, #filterUTM').on('change', function() {
            table.ajax.reload();
        });

        $('#filterDateRangeHistory').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
            $('#filterDateFrom').val(picker.startDate.format('YYYY-MM-DD'));
            $('#filterDateTo').val(picker.endDate.format('YYYY-MM-DD'));
            table.ajax.reload();
        }).on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });

        // Reset filter button
        $('#btnResetFilter').on('click', function() {
            $('#filterTypeAccount').val('');
            $('#filterPlatform').val('');
            $('#filterRate').val('');
            $('#filterUTM').val('');
            $('#filterUTMValue').val('');
            $('#filterDateRangeHistory').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });
    });
</script>