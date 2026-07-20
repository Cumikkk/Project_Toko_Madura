<style>
    body.dark-theme .table-bg thead,
    body.dark-theme .table-bg tbody {
        background: #242526 !important;
    }

    body.light-theme .table-bg thead,
    body.light-theme .table-bg tbody {
        background: #fff !important;
    }

    .table-bg thead {
        border-bottom: 1px dashed rgba(223, 223, 223, 0.15);
    }

    .table-bg tbody tr, .table-bg tbody td {
        border: 0px !important;
    }

    .table-scroll {
        height: 550px;
        overflow-y: auto;
    }

    @media (max-width: 575.98px) {
        .table-scroll {
            height: 300px;
        }
    }

    .table-scroll thead {
        position:sticky;
        top:0;
    }

    .table-scroll::-webkit-scrollbar {
        width: 8px;
        color: #242526;
    }

    .table-scroll::-webkit-scrollbar{
        width:6px;
    }

    .table-scroll::-webkit-scrollbar-thumb{
        background:#888;
        border-radius:10px;
    }

    .table-scroll::-webkit-scrollbar-thumb:hover{
        background:#555;
    }

    body.dark-theme .select-icon,
    body.dark-theme .select-icon select {
        color: #fff;
    }

    .select-icon {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .select-icon .icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .select-icon select option {
        background: transparent;
    }
    
    .select-icon select {
        width: 100%;
        padding: 8px 10px 8px 35px; /* ruang untuk icon */
        font-size: 14px;
        background: transparent;
        border: none;
        appearance: none;
    }

    /* overlay */
    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.66); /* hitam 30% */
        border-radius: 3px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
        font-weight: bold;
        opacity: 0;
        pointer-events: none;
        transition: 0.3s;
    }

    /* aktif */
    .overlay.active {
        opacity: 1;
        pointer-events: all;
    }

    #symbols td {
        font-size: 11px;
        cursor: pointer;
    }

    .positive {
        color: #28a745 !important;
    }

    .negative {
        color: #dc3545 !important;
    }

    body.dark-theme .neutral {
        color: #A9B4CC !important;
    }

    .neutral {
        color: var(--bs-body-color) !important;
    }

    #table-opened-order tbody tr:hover {
        cursor: pointer;
    }

</style>
<div class="position-relative">
    <div class="row g-4">
        <div class="col-xxl-3 col-lg-4 mb-25">
            <div class="panel">
                <div class="panel-header" style="min-height: 50px; height: auto; padding: 15px;">
                    <div class="btn-box d-flex flex-wrap gap-1" id="category-tab" role="tablist"></div>
                </div>
                <div class="panel-body p-0 w-100">
                    <div class="table-scroll">
                        <table class="table-bg table table-hover digi-dataTable">
                            <thead>
                                <tr>
                                    <th style="width: 5px;">#</th>
                                    <th class="text-start">Symbol</th>
                                    <th class="text-center">Bid</th>
                                    <th class="text-center">Ask</th>
                                </tr>
                            </thead>
                            <tbody id="symbols"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="col-xxl-9 col-lg-8 mb-25">
            <div class="panel mb-25">
                <div class="panel-header">
                    <div class="d-flex align-items-center gap-2 justify-content-between w-100 flex-wrap">
                        <h6 class="card-title">
                            <span id="account-number">0</span> - 
                            RRFX-<span id="account-server"></span> - 
                            <span id="symbol"></span>
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0)" id="new-order" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#dynamicModalDefault" data-url="/ajax/modal/web-trade/new-order" data-title="New Order" data-callback="assignSymbolNewOrder" title="New Order"><i class="fas fa-plus"></i> New Order</a>
                            <a href="javascript:void(0)" id="modify-order" class="btn btn-sm btn-outline-info d-none" data-bs-toggle="modal" data-bs-target="#dynamicModalDefault" data-url="/ajax/modal/web-trade/modify" data-title="Modify Order" title="Modify Order"><i class="fas fa-edit"></i> Modify Order</a>
                            <!-- <a href="javascript:void(0)" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#dynamicModalDefault" title="Change Account">Change Account</a> -->
                            <a href="javascript:void(0)" class="btn btn-sm btn-outline-success" title="refresh" id="btn-refresh"><i class="fa-solid fa-arrows-rotate"></i></a>
                        </div>
                    </div>
                </div>
                <div class="panel-body p-0" id="chart" style="height: 550px;"></div>
            </div>
        </div>
    </div>
    <div class="panel mb-25">
        <div class="panel-header">
            <nav>
                <div class="btn-box d-flex flex-wrap gap-1" id="nav-tab" role="tablist">
                    <button class="btn btn-sm btn-outline-primary" id="nav-account-tab" data-bs-toggle="tab" data-bs-target="#nav-account" type="button" role="tab" aria-controls="nav-trade" aria-selected="true">Account</button>
                    <button class="btn btn-sm btn-outline-primary active" id="nav-trade-tab" data-bs-toggle="tab" data-bs-target="#nav-trade" type="button" role="tab" aria-controls="nav-trade" aria-selected="true">Trade</button>
                    <button class="btn btn-sm btn-outline-primary" id="nav-history-tab" data-bs-toggle="tab" data-bs-target="#nav-history" type="button" role="tab" aria-controls="nav-history" aria-selected="false">History</button>
                </div>
            </nav>
        </div>
        <div class="panel-body p-0 pb-0">
            <div class="tab-content profile-edit-tab" id="nav-tabContent">
                <div class="tab-pane fade" id="nav-account" role="tabpanel" aria-labelledby="nav-account-tab" tabindex="0">
                    <div class="table-responsive">
                        <table id="table-account" class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped">
                            <thead>
                                <tr>
                                    <th width="20%" class="text-center">Login</th>
                                    <th width="20%" class="text-center">Leverage</th>
                                    <th width="20%" class="text-center">Balance</th>
                                    <th width="10%" class="text-center">#</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade show active" id="nav-trade" role="tabpanel" aria-labelledby="nav-trade-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-opened-order">
                            <thead>
                                <tr>
                                    <th colspan="10">
                                        <div class="d-flex gap-2 flex-wrap fw-bold align-items-center">
                                            <i class="fas fa-info-circle"></i>
                                            <div>Balance: <span id="balance">0</span></div>
                                            <div>USD</div>
                                            <div>Equity: <span id="equity">0</span></div>
                                            <div>Margin: <span id="margin">0</span></div>
                                            <div>Free Margin: <span id="margin-free">0</span></div>
                                            <div>Margin level: <span id="margin-level">0</span> %</div>
                                        </div>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-center">Symbol</th>
                                    <th class="text-center">Ticket</th>
                                    <th class="text-center">Time</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Volume</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">T/P</th>
                                    <th class="text-center">Current Price</th>
                                    <th class="text-center">Profit</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-pending-order">
                            <thead>
                                <tr>
                                    <th colspan="9">
                                        <div class="d-flex gap-2 align-items-center">
                                            <i class="fas fa-info-circle"></i>
                                            <div>Pending Order</div>
                                        </div>    
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-center">Symbol</th>
                                    <th class="text-center">Ticket</th>
                                    <th class="text-center">Time</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Volume</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">T/P</th>
                                    <th class="text-center">Current Price</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-history" role="tabpanel" aria-labelledby="nav-history-tab" tabindex="0">
                    <!-- <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-history">
                            <thead>
                                <tr>
                                    <th class="text-center">Open Time</th>
                                    <th class="text-center">Ticket</th>
                                    <th class="text-center">Symbol</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Volume</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">T/P</th>
                                    <th width="10%" class="text-center">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                            
                            </tbody>
                        </table>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
            <strong class="overlay-message">Loading...</strong>
        </div>
    </div>
</div>

<!-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> -->
<script type="text/javascript">
    window.APP_VERSION = "<?= filemtime(__DIR__ . "/../../../assets/js/web-trade/init.js") ?>"
</script>
<script type="module" src="/assets/js/web-trade/init.js?ver=<?= filemtime(__DIR__ . "/../../../assets/js/web-trade/init.js") ?>"></script>