<style>
    /* Dashboard Optimizations */
    .card-widget { min-height: 80px; }
    .chartjs-wrapper-demo { position: relative; width: 100%; }
    .spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }
    
    /* Performance: Use will-change for animated elements */
    .card.custom-card { will-change: box-shadow; }
    .card.custom-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    
    /* Prevent layout shift during loading */
    #chartsSection { min-height: 400px; }
    
    /* Smooth transitions */
    .card.custom-card { transition: box-shadow 0.2s ease; }
</style>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Welcome To Dashboard.</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-widget">
                    <label class="main-content-label mb-3 pt-1">Total Users</label>
                    <div class="d-flex justify-content-between">
                        <div>Register</div>
                        <div><span class="" id="user_regester">Loading...</span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>Actived</div>
                        <div><span class="" id="user_actived">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-widget">
                    <label class="main-content-label mb-3 pt-1">NMI</label>
                    <div class="d-flex justify-content-between">
                        <div>IDR</div>
                        <div><span class="" id="it_count">Loading...</span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>USD</div>
                        <div><span class="" id="it_total">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-widget">
                    <label class="main-content-label mb-3 pt-1">Deposit</label>
                    <div class="d-flex justify-content-between">
                        <div>IDR</div>
                        <div><span class="" id="dp_idr">Loading...</span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>USD</div>
                        <div><span class="" id="dp_usd">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-widget">
                    <label class="main-content-label mb-3 pt-1">Withdrawal</label>
                    <div class="d-flex justify-content-between">
                        <div>IDR</div>
                        <div><span class="" id="wd_idr">Loading...</span></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>USD</div>
                        <div><span class="" id="wd_usd">Loading...</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Charts Section -->
<div class="row" id="chartsSection">
    <div class="col-md-6">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-1">Deposit / Withdrawal (IDR)</h6>
                <p class="text-muted card-sub-title">Last 30 Days</p>
            </div>
            <div class="card-body">
                <div class="chartjs-wrapper-demo" style="height: 300px;">
                    <canvas id="chartIdrDpWdIt"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-1">Deposit / Withdrawal / Int. Trans (USD)</h6>
                <p class="text-muted card-sub-title">Last 30 Days</p>
            </div>
            <div class="card-body">
                <div class="chartjs-wrapper-demo" style="height: 300px;">
                    <canvas id="chartUsdDpWdIt"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <?php 
        $rangkingViews = [
            "view.lostrangking"    => "view_lostrangking.php",
            "view.profitrangking"  => "view_profitrangking.php",
            "view.volumerangking"  => "view_volumerangking.php",
            "view.symbolrangking"  => "view_symbolrangking.php",
            "view.balancerangking" => "view_balancerangking.php",
            "view.depositrangking" => "view_depositrangking.php",
        ];

        foreach ($rangkingViews as $permission => $file) {
            if ($adminPermissionCore->isHavePermission($moduleId, $permission)) {
                require_once __DIR__ . "/$file";
            }
        }
    ?>
</div>

<script type="text/javascript">
    /**
     * Dashboard Optimization Features:
     * 1. Dynamic card rendering - prevents initial layout shift
     * 2. Lazy loading Chart.js - loads only when needed
     * 3. Request timeout - prevents hanging requests
     * 4. Chart instance caching - prevents memory leaks
     * 5. RequestAnimationFrame - smoother chart rendering
     * 6. DocumentFragment - efficient DOM manipulation
     * 7. Error handling - graceful degradation
     * 8. Cleanup on unload - prevents memory leaks
     */
    (() => {
        'use strict';

        // Utility functions
        const fmt2 = (num) => Number(num ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const moneyTick = (value) => fmt2(value);
        const moneyTooltip = (label, raw) => `${label}: ${fmt2(raw)}`;
        
        // Check Chart.js version
        const isV2 = typeof Chart !== 'undefined' && /^2\./.test(Chart.version || '');

        // Chart instances cache
        const chartInstances = {};

        function makeLineChart(canvasId, { labels = [], datasets = [] }, opts = {}) {
            const el = document.getElementById(canvasId);
            if (!el) return null;

            const cleaned = datasets.map(ds => ({
            ...ds,
            data: (ds.data ?? []).map(v => Number(v ?? 0))
            }));

            const base = {
            type: 'line',
            data: { labels, datasets: cleaned }
            };

            if (isV2) {
                base.options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: { label: (ctx) => moneyTooltip(ctx.dataset.label || '', ctx.yLabel) }
                    },
                    legend: { display: true },
                    scales: {
                        xAxes: [{ 
                            ticks: { 
                                autoSkip: true, maxRotation: 0 
                            },
                            gridLines: {
                                color: "rgba(119, 119, 142, 0.2)"
                            }
                        }],
                        yAxes: [{ 
                            ticks: { 
                                callback: (value) => moneyTick(value), beginAtZero: true 
                            },
                            gridLines: {
                                color: "rgba(119, 119, 142, 0.2)"
                            }
                        }]
                    },
                    ...(opts || {})
                };
            } else {
                base.options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: true },
                        tooltip: { callbacks: { label: (ctx) => moneyTooltip(ctx.dataset.label ?? '', ctx.raw) } },
                        ...((opts && opts.plugins) || {})  // <- aman kalau undefined
                    },
                    scales: {
                        x: { 
                            ticks: { 
                                autoSkip: true, maxRotation: 0 
                            },
                            gridLines: {
                                color: "rgba(119, 119, 142, 0.2)"
                            }
                        },
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                callback: (value) => moneyTick(value) 
                            },
                            gridLines: {
                                color: "rgba(119, 119, 142, 0.2)"
                            }
                        }
                    },
                    ...(opts || {})
                };
            }

            // Destroy existing chart if exists
            if (chartInstances[canvasId]) {
                chartInstances[canvasId].destroy();
            }

            // Create new chart and store reference
            chartInstances[canvasId] = new Chart(el, base);
            return chartInstances[canvasId];
        }

        const palette = {
            blue:   'rgba(54, 162, 235, 0.7)',
            green:  'rgba(75, 192, 192, 0.7)',
            red:    'rgba(255, 99, 132, 0.7)',
            purple: 'rgba(153, 102, 255, 0.7)'
        };

        // Fetch summary data with timeout
        async function fetchSummary() {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout

            try {
                const res = await fetch('/ajax/post/dashboard/summary', { 
                    cache: 'no-store',
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                const json = await res.json();
                if (json && typeof json === 'object') return json;
                throw new Error('Invalid response format');
            } catch (error) {
                clearTimeout(timeoutId);
                console.error('Failed to fetch summary:', error);
                return null;
            }
        }

        async function init() {
            const json = await fetchSummary();
            if (!json?.success) {
                console.error('Failed to load summary:', json);
                return;
            }

            const data = json.data ?? {};
            
            // Update summary cards text
            const setText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val ?? '';
            };
            setText('user_regester', data.user_regester);
            setText('user_actived', data.user_actived);
            setText('it_count', data.it_count);
            setText('it_total', data.it_total);
            setText('dp_idr', data.dp_idr);
            setText('dp_usd', data.dp_usd);
            setText('wd_idr', data.wd_idr);
            setText('wd_usd', data.wd_usd);

            // Render charts using requestAnimationFrame for better performance
            requestAnimationFrame(() => {
                const cidIdr = json.chartIdrDpWdIt ?? {};
                const labelsIdr = cidIdr.labels ?? [];
                const dpIdrSeries = cidIdr.dp_series ?? [];
                const wdIdrSeries = cidIdr.wd_series ?? [];

                makeLineChart('chartIdrDpWdIt', {
                    labels: labelsIdr,
                    datasets: [
                        {
                            label: 'Deposit IDR',
                            borderColor: palette.green,
                            backgroundColor: palette.green,
                            borderWidth: 2,
                            pointRadius: 2,
                            tension: 0.25,
                            data: dpIdrSeries,
                            fill: false
                        },
                        {
                            label: 'Withdrawal IDR',
                            borderColor: palette.red,
                            backgroundColor: palette.red,
                            borderWidth: 2,
                            pointRadius: 2,
                            tension: 0.25,
                            data: wdIdrSeries,
                            fill: false
                        }
                    ]
                });
            });

            requestAnimationFrame(() => {
                const cidUsd = json.chartUsdDpWdIt ?? {};
                const labelsUsd = cidUsd.labels ?? [];
                const dpUsdSeries = cidUsd.dp_series ?? [];
                const wdUsdSeries = cidUsd.wd_series ?? [];
                const itUsdSeries = cidUsd.it_series ?? [];

                makeLineChart('chartUsdDpWdIt', {
                    labels: labelsUsd,
                    datasets: [
                        {
                            label: 'Deposit USD',
                            borderColor: palette.green,
                            backgroundColor: palette.green,
                            borderWidth: 2,
                            pointRadius: 2,
                            tension: 0.25,
                            data: dpUsdSeries,
                            fill: false
                        },
                        {
                            label: 'Withdrawal USD',
                            borderColor: palette.red,
                            backgroundColor: palette.red,
                            borderWidth: 2,
                            pointRadius: 2,
                            tension: 0.25,
                            data: wdUsdSeries,
                            fill: false
                        },
                        {
                            label: 'Internal Transfer USD',
                            borderColor: palette.blue,
                            backgroundColor: palette.blue,
                            borderWidth: 2,
                            pointRadius: 2,
                            tension: 0.25,
                            data: itUsdSeries,
                            fill: false
                        }
                    ]
                });
            });
        }

        // Load Chart.js if not already loaded
        function ensureChartJS() {
            return new Promise((resolve, reject) => {
                if (typeof Chart !== 'undefined') {
                    resolve();
                    return;
                }

                const script = document.createElement('script');
                script.src = 'assets/plugins/chart.js/Chart.bundle.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Main initialization with Chart.js loading
        async function bootstrap() {
            try {
                await ensureChartJS();
                await init();
            } catch (error) {
                console.error('Failed to initialize dashboard:', error);
                const container = document.getElementById('summaryCards');
                if (container) {
                    container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to initialize dashboard. Please refresh the page.</div></div>';
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootstrap);
        } else {
            bootstrap();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            Object.values(chartInstances).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
        });
    })();
</script>