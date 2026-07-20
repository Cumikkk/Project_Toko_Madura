<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Closed Trade</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Business Relation Manager</li>
			<li class="breadcrumb-item active">Closed Trade</li>
		</ol>
	</div>
    <div id="hasil"></div>
    <div class="d-flex">
        <div class="justify-content-center">
            <button id="today" type="button" class="btn btn-white btn-icon-text my-2 me-2">
                <i class="fe fe-calendar me-2"></i> Today
            </button>
            <button id="7days" type="button" class="btn btn-primary btn-icon-text my-2 me-2">
                <i class="fe fe-calendar me-2"></i> Last 7 Days
            </button>
            <button id="1months" type="button" class="btn btn-white btn-icon-text my-2 me-2">
                <i class="fe fe-calendar me-2"></i> Last 1 Months
            </button>
            <button id="custom" type="button" class="btn btn-white btn-icon-text my-2 me-2">
                <i class="fe fe-calendar me-2"></i> Custom
            </button>
        </div>
    </div>
</div>

<div id="customCardContainer" class="card custom-card overflow-hidden" style="display: none;">
    <form id="customForm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="dateStart" class="form-label">Date Start</label>
                    <input type="date" class="form-control" id="dateStart" name="datestart">
                </div>
                <div class="col-md-6">
                    <label for="dateEnd" class="form-label">Date End</label>
                    <input type="date" class="form-control" id="dateEnd" name="dateend">
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Filter</button>
            <button type="button" id="cancelCustom" class="btn btn-outline-secondary">Cancel</button>
        </div>
    </form>
</div>

<div class="row row-sm">
    <div class="col-12 col-md-6 col-lg-4 col-xl-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order ">
                    <label class="main-content-label mb-3 pt-1">Total Profit (USD)</label>
                    <h4 class="text-end"><span class="font-weight-bold" id="summary_TotalProfit">Loading...</span></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order ">
                    <label class="main-content-label mb-3 pt-1">Profit (USD)</label>
                    <h4 class="text-end"><span class="font-weight-bold" id="summary_Profit">Loading...</span></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order ">
                    <label class="main-content-label mb-3 pt-1">Loss (USD)</label>
                    <h4 class="text-end"><span class="font-weight-bold" id="summary_Loss">Loading...</span></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order ">
                    <label class="main-content-label mb-3 pt-1">Total Trades</label>
                    <h4 class="text-end"><span class="font-weight-bold" id="summary_TotalTrades">Loading...</span></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order ">
                    <label class="main-content-label mb-3 pt-1">Profit Trades</label>
                    <h4 class="text-end"><span class="font-weight-bold" id="summary_ProfitTrades">Loading...</span></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 col-xl-2">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order ">
                    <label class="main-content-label mb-3 pt-1">Loss Trades</label>
                    <h4 class="text-end"><span class="font-weight-bold" id="summary_LossTrades">Loading...</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-sm">
    <div class="col-md-6">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Profit By Symbol</h6>
                    <p class="text-muted card-sub-title">Top 10 highest </p>
                </div>
                <button type="button" class="btn btn-primary btn-refresh-table" style="margin-top:-20px;" data-dt="#tabel_SymbolHighest">Refresh</button>
            </div>
            <div class="card-body" style="overflow-y: hidden;height: 490px;">
                <div class="table-responsive">
                    <table class="table table-bordered mg-b-0 table-striped table-hover" id="tabel_SymbolHighest">
                        <thead>
                            <tr class="text-center">
                                <th>Symbol</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Profit By Symbol</h6>
                    <p class="text-muted card-sub-title">Top 10 lowest </p>
                </div>
                <button type="button" class="btn btn-primary btn-refresh-table" style="margin-top:-20px;" data-dt="#tabel_SymbolLowest">Refresh</button>
            </div>
            <div class="card-body" style="overflow-y: hidden;height: 490px;">
                <div class="table-responsive">
                    <table class="table table-bordered mg-b-0 table-striped table-hover" id="tabel_SymbolLowest">
                        <thead>
                            <tr class="text-center">
                                <th>Symbol</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Profit By Account</h6>
                    <p class="text-muted card-sub-title">Top 10 highest </p>
                </div>
                <button type="button" class="btn btn-primary btn-refresh-table" style="margin-top:-20px;" data-dt="#tabel_AccountHighest">Refresh</button>
            </div>
            <div class="card-body" style="overflow-y: hidden;height: 490px;">
                <div class="table-responsive">
                    <table class="table table-bordered mg-b-0 table-striped table-hover" id="tabel_AccountHighest">
                        <thead>
                            <tr class="text-center">
                                <th>Account</th>
                                <th>Name</th>
                                <th>Group</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Profit By Account</h6>
                    <p class="text-muted card-sub-title">Top 10 lowest </p>
                </div>
                <button type="button" class="btn btn-primary btn-refresh-table" style="margin-top:-20px;" data-dt="#tabel_AccountLowest">Refresh</button>
            </div>
            <div class="card-body" style="overflow-y: hidden;height: 490px;">
                <div class="table-responsive">
                    <table class="table table-bordered mg-b-0 table-striped table-hover" id="tabel_AccountLowest">
                        <thead>
                            <tr class="text-center">
                                <th>Account</th>
                                <th>Name</th>
                                <th>Group</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const buttons      = document.querySelectorAll('.justify-content-center button');
    const container    = document.getElementById('customCardContainer');
    const startEl      = document.getElementById('dateStart');
    const endEl        = document.getElementById('dateEnd');
    const hasil        = document.getElementById('hasil');
    const customForm   = document.getElementById('customForm');
    
    const elTotalProfit   = document.getElementById('summary_TotalProfit');
    const elProfit       = document.getElementById('summary_Profit');
    const elLoss         = document.getElementById('summary_Loss');
    const elTotalTrades  = document.getElementById('summary_TotalTrades');
    const elProfitTrades = document.getElementById('summary_ProfitTrades');
    const elLossTrades   = document.getElementById('summary_LossTrades');

    // UTILS
    const fmt       = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const parse     = v => { const d = new Date(v); return isNaN(d) ? null : d; };
    const addDays   = (d, n) => { const x = new Date(d); x.setDate(x.getDate()+n); return x; };
    const addMonths = (d, n) => { const x = new Date(d); x.setMonth(x.getMonth()+n); return x; };

    const parseDateLocal = (v) => {
        if (!v) return null;
        const [y, m, d] = v.split('-').map(Number);
        return new Date(y, (m || 1) - 1, d || 1);
    };
    const toUnix = (d) => {
        const dt = d instanceof Date ? d : new Date(d);
        return isNaN(dt) ? '' : Math.floor(dt.getTime() / 1000);
    };

    (function () {
        const getSummaryTotalProfitEl = () => document.getElementById('summary_TotalProfit');
        const getSummaryProfitEl = () => document.getElementById('summary_Profit');
        const getSummaryLossEl = () => document.getElementById('summary_Loss');
        const getSummaryTotalTradesEl = () => document.getElementById('summary_TotalTrades');
        const getSummaryProfitTradesEl = () => document.getElementById('summary_ProfitTrades');
        const getSummaryLossTradesEl = () => document.getElementById('summary_LossTrades');
        
        async function _updateTotalTrades(startDate, endDate) {
            if (!(startDate instanceof Date) || !(endDate instanceof Date)) {
                console.warn('updateTotalTrades dipanggil tanpa tanggal yang valid');
                return;
            }
            const qs = new URLSearchParams({
                startdate: String(toUnix(startDate)),
                enddate:   String(toUnix(endDate)),
            });
            try {
                const resp = await fetch(`/ajax/post/brm/closedtrade_summary?${qs.toString()}`, {
                    headers: { Accept: 'application/json' }
                });
                const res = await resp.json();

                const TotalProfit = res?.data?.totalprofit;
                const Profit = res?.data?.profit;
                const Loss = res?.data?.loss;
                const TotalTrades = res?.data?.totaltrades;
                const ProfitTrades = res?.data?.profittrades;
                const LossTrades = res?.data?.losstrades;
                if (res?.success === true && TotalProfit !== undefined && Profit !== undefined && Loss !== undefined && TotalTrades !== undefined && ProfitTrades !== undefined && LossTrades !== undefined) {
                    getSummaryTotalProfitEl().textContent = TotalProfit;
                    getSummaryProfitEl().textContent = Profit;
                    getSummaryLossEl().textContent = Loss;
                    getSummaryTotalTradesEl().textContent = TotalTrades;
                    getSummaryProfitTradesEl().textContent = ProfitTrades;
                    getSummaryLossTradesEl().textContent = LossTrades;
                } else {
                    console.error('Fetch success but data is invalid:', res);
                    getSummaryTotalProfitEl().textContent = '--';
                    getSummaryProfitEl().textContent = '--';
                    getSummaryLossEl().textContent = '--';
                    getSummaryTotalTradesEl().textContent = '--';
                    getSummaryProfitTradesEl().textContent = '--';
                    getSummaryLossTradesEl().textContent = '--';
                }
            } catch (e) {
                console.error('Fetch error:', e);
                getTotalProfitEl().textContent = '-';
                getProfitEl().textContent = '-';
                getLossEl().textContent = '-';
                getTotalTradesEl().textContent = '-';
                getProfitTradesEl().textContent = '-';
                getLossTradesEl().textContent = '-';
            }
        }
        window.updateTotalTrades = _updateTotalTrades;
    })();
    
    // FUNGSI BANTU
    function clampEndAgainstStart() {
        if (!startEl || !endEl) return;
        const s = parse(startEl.value);
        if (!s) { endEl.min = ''; endEl.max = ''; return; }
        const minEnd = addDays(s, 1);
        const maxEnd = addMonths(s, 2);
        endEl.min = fmt(minEnd);
        endEl.max = fmt(maxEnd);
        const e = parse(endEl.value);
        if (e) {
            if (e < minEnd) endEl.value = fmt(minEnd);
            if (e > maxEnd) endEl.value = fmt(maxEnd);
        }
    }
    function clampStartAgainstEnd() {
        if (!startEl || !endEl) return;
        const e = parse(endEl.value);
        if (!e) { startEl.min = ''; startEl.max = ''; return; }
        const maxStart = addDays(e, -1);
        const minStart = addMonths(e, -2);
        startEl.max = fmt(maxStart);
        startEl.min = fmt(minStart);
        const s = parse(startEl.value);
        if (s) {
            if (s > maxStart) startEl.value = fmt(maxStart);
            if (s < minStart) startEl.value = fmt(minStart);
        }
    }
    function setHasil(label, startDate, endDate) {
        hasil.innerHTML = `<div><i><strong>${label}</strong> ${fmt(startDate)} s/d ${fmt(endDate)}</i></div>`;
    }

    // FUNGSI UTAMA UNTUK FILTER
    function applyQuickRange(id) {
        const today = new Date();
        let start, end, label;

        if (id === 'today') {
            start = today; end = today; label = 'Today (1 hari)';
        } else if (id === '7days') {
            end = today; start = addDays(today, -6); label = 'Last 7 Days';
        } else if (id === '1months') {
            end = today; start = addDays(today, -29); label = 'Last 1 Months (30 hari)';
        } else {
            return;
        }

        window.rangeStart = start;
        window.rangeEnd   = end;
        
        window.updateTotalTrades(start, end);
        reloadAllTables();

        if (container) container.style.display = 'none';
        if (startEl) startEl.value = '';
        if (endEl)   endEl.value = '';
        if (startEl && endEl) startEl.min = startEl.max = endEl.min = endEl.max = '';

        setHasil(label, start, end);
    }
    
    function reloadAllTables() {
        if (tabel_DepositHighest) tabel_DepositHighest.ajax.reload(null, false);
        if (tabel_DepositLowest) tabel_DepositLowest.ajax.reload(null, false);
        if (tabel_WithdrawalHighest) tabel_WithdrawalHighest.ajax.reload(null, false);
        if (tabel_WithdrawalLowest) tabel_WithdrawalLowest.ajax.reload(null, false);
    }

    let tabel_DepositHighest, tabel_DepositLowest, tabel_WithdrawalHighest, tabel_WithdrawalLowest;
    
    // INISIALISASI DATATABLES
    $(document).ready(function() {
        // Buat fungsi data bersama untuk semua tabel
        const commonAjaxData = function (d) {
            d.startdate = toUnix(window.rangeStart);
            d.enddate   = toUnix(window.rangeEnd);
        };

        tabel_DepositHighest = $('#tabel_DepositHighest').DataTable({
            scrollX: true, processing: true, serverSide: true,
            deferRender: true, lengthChange: false, searching: false,
            ordering: false, paging: false, "info": false,
            ajax: { url: "/ajax/datatable/brm/netmargin_dphighest/view", contentType: "application/json", type: "GET", data: commonAjaxData },
            columns: [ { data: 'LOGIN' }, { data: 'AMOUNT', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) } ],
        });
        
        tabel_DepositLowest = $('#tabel_DepositLowest').DataTable({
            scrollX: true, processing: true, serverSide: true,
            deferRender: true, lengthChange: false, searching: false,
            ordering: false, paging: false, "info": false,
            ajax: { url: "/ajax/datatable/brm/netmargin_dplowest/view", contentType: "application/json", type: "GET", data: commonAjaxData },
            columns: [ { data: 'LOGIN' }, { data: 'AMOUNT', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) } ],
        });
        
        tabel_WithdrawalHighest = $('#tabel_WithdrawalHighest').DataTable({
            scrollX: true, processing: true, serverSide: true,
            deferRender: true, lengthChange: false, searching: false,
            ordering: false, paging: false, "info": false,
            ajax: { url: "/ajax/datatable/brm/netmargin_wdhighest/view", contentType: "application/json", type: "GET", data: commonAjaxData },
            columns: [ { data: 'LOGIN' }, { data: 'AMOUNT', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) } ],
        });
        
        tabel_WithdrawalLowest = $('#tabel_WithdrawalLowest').DataTable({
            scrollX: true, processing: true, serverSide: true,
            deferRender: true, lengthChange: false, searching: false,
            ordering: false, paging: false, "info": false,
            ajax: { url: "/ajax/datatable/brm/netmargin_wdlowest/view", contentType: "application/json", type: "GET", data: commonAjaxData },
            columns: [ { data: 'LOGIN' }, { data: 'AMOUNT', className: 'text-end', render: $.fn.dataTable.render.number( ',', '.', 2, '' ) } ],
        });
        
        $(document).on('click', '.btn-refresh-table', function() {
            const $btn = $(this);
            const selector = $btn.data('dt');
            const dt = $(selector).DataTable();

            const originalHtml = $btn.html();
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

            dt.ajax.reload(() => {
                $btn.prop('disabled', false).html(originalHtml);
            }, false);
        });
    });

    // EVENT LISTENERS
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            elDeposit.textContent = 'Loading...';
            elWithdrawal.textContent = 'Loading...';
            elNet.textContent = 'Loading...';
            buttons.forEach(b => { b.classList.remove('btn-primary'); b.classList.add('btn-white'); });
            this.classList.remove('btn-white'); this.classList.add('btn-primary');
            if (this.id === 'custom') {
                if (container) container.style.display = 'block';
            } else {
                applyQuickRange(this.id);
            }
        });
    });

    startEl?.addEventListener('change', clampEndAgainstStart);
    endEl?.addEventListener('change', clampStartAgainstEnd);
    customForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const s  = parseDateLocal(startEl?.value);
        const en = parseDateLocal(endEl?.value);
        if (!s || !en) {
            alert('Lengkapi tanggal Start dan End.');
            return;
        }
        if (s > en) {
            alert('Start tidak boleh lebih besar dari End.');
            return;
        }
        setHasil('Custom', s, en);
        window.rangeStart = s;
        window.rangeEnd   = en;

        window.updateTotalTrades(s, en);
        reloadAllTables();
    });

    // Initial load
    applyQuickRange('7days');
    clampEndAgainstStart();
    clampStartAgainstEnd();
</script>