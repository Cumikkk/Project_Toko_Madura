<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Hedging</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Business Relation Manager</li>
			<li class="breadcrumb-item active">Hedging</li>
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

<div class="card custom-card overflow-hidden">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered" width="100%" id="table">
                <thead>
                    <tr>
                        <th style="vertical-align: middle" class="text-center">Login</th>
                        <th style="vertical-align: middle" class="text-center">Name</th>
                        <th style="vertical-align: middle" class="text-center">Group</th>
                        <th style="vertical-align: middle" class="text-center">Symbol</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
<script>
    let table;
    const buttons       = document.querySelectorAll('.justify-content-center button');
    const container     = document.getElementById('customCardContainer'); // bisa null
    const startEl       = document.getElementById('dateStart');           // bisa null
    const endEl         = document.getElementById('dateEnd');             // bisa null
    const hasil         = document.getElementById('hasil');
    const customForm    = document.getElementById('customForm');

    // utils
    const fmt       = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const parse     = v => { const d = new Date(v); return isNaN(d) ? null : d; };
    const addDays   = (d, n) => { const x = new Date(d); x.setDate(x.getDate()+n); return x; };
    const addMonths = (d, n) => { const x = new Date(d); x.setMonth(x.getMonth()+n); return x; };

    const parseDateLocal = (v) => {
        if (!v) return null;
        const [y, m, d] = v.split('-').map(Number);
        return new Date(y, (m || 1) - 1, d || 1);
    };

    // aman untuk invalid input → return '' biar tidak kirim "Invalid Date"
    const toUnix = (d) => {
        const dt = d instanceof Date ? d : new Date(d);
        return isNaN(dt) ? '' : Math.floor(dt.getTime() / 1000); // detik
    };

    // Guards untuk clamp jika input tanggal belum ada di DOM
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

        if (container) container.style.display = 'none';
        if (startEl) startEl.value = '';
        if (endEl)   endEl.value = '';
        if (startEl && endEl) startEl.min = startEl.max = endEl.min = endEl.max = '';

        setHasil(label, start, end);

        if (table) table.ajax.reload(null, false);
    }

    $(document).ready(function() {
        console.log('datestart', toUnix(window.rangeStart), 'dateend', toUnix(window.rangeEnd));
        table = $('#table').DataTable({
            dom: 'Blfrtip',
            scrollX: true,
            processing: true,
            serverSide: true,
            deferRender: true,
            language: { processing: "Loading data…" },
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
                url: "/ajax/datatable/brm/hedging/view",
                type: "GET",
                data: function (d) {
                    d.startdate = toUnix(window.rangeStart);
                    d.enddate   = toUnix(window.rangeEnd);
                }
            },
            columns: [
                { data: 'LOGIN' },
                { data: 'name' },
                { data: 'GROUP_HEDGING' },
                { data: 'SYMBOL' }
            ],
            order: [[1, 'desc']],
            lengthMenu: [[10, 25, 50], [10, 25, 50]],
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
    });

    // events
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
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

        if (table) table.ajax.reload(null, false);
    });


    // initial state (sesuai tombol aktif)
    applyQuickRange('7days');
    clampEndAgainstStart();
    clampStartAgainstEnd();
</script>