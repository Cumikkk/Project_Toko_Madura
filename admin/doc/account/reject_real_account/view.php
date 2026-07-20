<?php
    use App\Factory\UtmHandler;
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Reject Real Account</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Account</li>
            <li class="breadcrumb-item active" aria-current="page">Reject Real Account</li>
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
					<div class="col-md-4">
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
						<label class="form-label">Filter Date</label>
						<select id="filterDate" class="form-control">
							<option value="reg">Date Reg</option>
							<option value="reject">Reject Date</option>
						</select>
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
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped" id="table">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Date Reg</th>
                                <th style="vertical-align: middle" class="text-center">Date Reject</th>
                                <th style="vertical-align: middle" class="text-center">Nama</th>
                                <th style="vertical-align: middle" class="text-center">Email</th>
                                <th style="vertical-align: middle" class="text-center">UTM</th>
                                <th style="vertical-align: middle" class="text-center">Status</th>
                                <th style="vertical-align: middle" class="text-center">Action</th>
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
        let prm = {};
        let tbl = $('#table').DataTable( {
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
            ajax: {
                url: "/ajax/datatable/account/reject_real_account/view",
                type: "GET",
                data: function(d) {
                    d.filterUTM = $('#filterUTM').val();
                    d.filterUTMValue = $('#filterUTMValue').val();
                    d.filterDate = $('#filterDate').val();
                    d.filterDateFrom = $('#filterDateFrom').val();
                    d.filterDateTo = $('#filterDateTo').val();
                }
            },
            lengthMenu: [[10, 50, 100], [10, 50, 100]],
            scrollX: true,
            order: [[ 0, "desc" ]]
        } );
        try {
            const btnRef = tbl.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tbl.on('processing.dt', function (e, settings, processing) {
                const btn = tbl.button('refresh:name');
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

            tbl.on('xhr.dt', function () {
                const btn = tbl.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }

        $('#filterUTMValue').on('input', function() {
            tbl.ajax.reload();
        });

        $('#filterUTM, #filterDate').on('change', function() {
            tbl.ajax.reload();
        });

        $('#filterDateRangeHistory').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear', format: 'YYYY-MM-DD' }
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' — ' + picker.endDate.format('YYYY-MM-DD'));
            $('#filterDateFrom').val(picker.startDate.format('YYYY-MM-DD'));
            $('#filterDateTo').val(picker.endDate.format('YYYY-MM-DD'));
            tbl.ajax.reload();
        }).on('cancel.daterangepicker', function() {
            $(this).val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            tbl.ajax.reload();
        });

        // Reset filter button
        $('#btnResetFilter').on('click', function() {
            $('#filterUTM').val('');
            $('#filterUTMValue').val('');
            // $('#filterDate').val('');
            $('#filterDateRangeHistory').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            tbl.ajax.reload();
        });
    });
</script>