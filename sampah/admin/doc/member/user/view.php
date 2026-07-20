<?php
    use App\Factory\UtmHandler;
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5"><?php echo $vp = 'User'; ?></h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Member</li>
			<li class="breadcrumb-item active" aria-current="page"><?php echo $vp; ?></li>
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
						<label class="form-label">Filter Nama</label>
						<input type="text" id="filterName" class="form-control" placeholder="Cari nama...">
					</div>
					<div class="col-md-3">
						<label class="form-label">Filter Email</label>
						<input type="text" id="filterEmail" class="form-control" placeholder="Cari email...">
					</div>
					<div class="col-md-3">
						<label class="form-label">Filter No Telepon</label>
						<input type="text" id="filterPhone" class="form-control" placeholder="Cari no telepon...">
					</div>
					<div class="col-md-3">
						<label class="form-label">Filter Status</label>
						<select id="filterStatus" class="form-control">
							<option value="">All Status</option>
							<option value="0">Pending</option>
							<option value="1">Disabled</option>
							<option value="2">Unverified</option>
							<option value="-1">Active</option>
						</select>
					</div>
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
		</div>
	</div>
</div>

<!-- Row Table -->
<div class="row row-sm">
	<div class="col-lg-12">
		<div class="card custom-card overflow-hidden">
			<div class="card-body">
				<div class="table-responsive">
					<table id="table" class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100" >
						<thead>
							<tr class="text-center">
								<th>Date Reg.</th>
								<th>Reff Code</th>
								<th>Register as</th>
								<th>UTM Data</th>
								<th>Real Account</th>
								<th>Referred by</th>
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

<script>
	let table;
    $(document).ready(function(){
        table = $('#table').DataTable( {
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
            lengthMenu: [[50, 100, -1], [50, 100, "All"]],
            scrollX: true,
            order: [[ 0, "desc" ]],
            ajax: {
                url: "/ajax/datatable/member/user/view",
                contentType: "application/json",
                type: "GET",
                data: function(d) {
                    d.filterName = $('#filterName').val();
                    d.filterEmail = $('#filterEmail').val();
                    d.filterPhone = $('#filterPhone').val();
                    d.filterStatus = $('#filterStatus').val();
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

        // Filter event listeners
        $('#filterName, #filterEmail, #filterPhone').on('keyup', function() {
            table.ajax.reload();
        });

        $('#filterUTMValue').on('input', function() {
            table.ajax.reload();
        });

        $('#filterStatus, #filterUTM').on('change', function() {
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
            $('#filterName').val('');
            $('#filterEmail').val('');
            $('#filterPhone').val('');
            $('#filterStatus').val('');
            $('#filterUTM').val('');
            $('#filterUTMValue').val('');
            $('#filterDateRangeHistory').val('');
            $('#filterDateFrom').val('');
            $('#filterDateTo').val('');
            table.ajax.reload();
        });
    });
</script>

<?php require_once __DIR__ . "/update_button.php"; ?>