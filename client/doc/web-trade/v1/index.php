<div class="dashboard-breadcrumb mb-25">
    <h2>Web Trade</h2>
</div>
<div class="row g-4">
    <div class="col-xxl-9 col-lg-8">
        <div class="panel mb-25">
            <div class="panel-body">
                <div id="candlestickChartTest"></div>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-lg-4">
        <div class="panel mb-25">
            <div class="panel-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="account" class="form-label">Account</label>
                        <select name="account" id="account" class="form-select">
                            <option value="">Pilih</option>
                            <?php $demoAccount = App\Models\Account::getDemoAccount($user['userid']); ?>
                            <?php if($demoAccount) : ?>
                                <option value="<?= $demoAccount['ACC_LOGIN'] ?>">
                                    <?= $demoAccount['ACC_LOGIN'] ?> (<?= App\Models\Helper::formatCurrency($demoAccount['FREE_MARGIN']) ?> USD)
                                </option>
                            <?php endif; ?>
                            <?php foreach(App\Models\Account::myAccount($user['MBR_ID']) as $key => $account) : ?>
                                <option value="<?= $account['ACC_LOGIN'] ?>" <?= ($key == 0)? "selected" : ""; ?>>
                                    <?= $account['ACC_LOGIN'] ?> (<?= App\Models\Helper::formatCurrency($account['MARGIN_FREE']) ?> USD)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="Symbol" class="form-label">Symbol</label>
                        <select id="Symbol" class="form-select">
                            <option selected>Pilih</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="type_order" class="form-label">Type</label>
                        <select id="type_order" class="form-select">
                            <option selected value="exchange_execution">Market Execution</option>
                            <option value="pending_order">Pending Order</option>
                        </select>
                    </div>
                </div>
                <div id="exchange_execution" style="display: block;">
                    <div class="row">
                        <div class="col-6">
                            <label for="exe-sl" class="form-label">SL</label>
                            <input type="number" name="exe-sl" id="exe-sl" class="form-control" value="0" required>
                        </div>
                        <div class="col-6">
                            <label for="exe-tp" class="form-label">TP</label>
                            <input type="number" name="exe-tp" id="exe-tp" class="form-control" value="0" required>
                        </div>
                        <div class="col-12">
                            <label for="exe-volume" class="form-label">Volume</label>
                            <input type="number" name="exe-volume" value="0.01" id="exe-volume" class="form-control" min="0.01" max="12" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <button type="button" id="exe-sell" name="exe-sell" data-type="sell" class="btn btn-block btn-danger w-100 mb-3">Sell</button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="exe-buy" name="exe-buy" data-type="buy" class="btn btn-block btn-success w-100 mb-3">Buy</button>
                        </div>
                    </div>
                </div>
                <div id="pending_order" style="display : none;">
                    <div class="row">
                        <div class="col-12">
                            <label for="po-type" class="form-label">Type</label>
                            <select id="po-type" class="form-select">
                                <option value="buy-limit">Buy Limit</option>
                                <option value="sell-limit">Sell Limit</option>
                                <option value="buy-stop">Buy Stop</option>
                                <option value="sell-stop">Sell Stop</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="po-volume" class="form-label">Volume</label>
                            <input type="number" name="po-volume" id="po-volume" class="form-control" min="0.01" max="12" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label for="po-sl" class="form-label">SL</label>
                            <input type="number" name="po-sl" id="po-sl" class="form-control" value="0" required>
                        </div>
                        <div class="col-6">
                            <label for="po-tp" class="form-label">TP</label>
                            <input type="number" name="po-tp" id="po-tp" class="form-control" value="0" required>
                        </div>
                        <div class="col-12">
                            <label for="po-price" class="form-label">Price</label>
                            <input type="number" name="po-price" id="po-price" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <button type="button" id="po-place" name="po-place" class="btn btn-block btn-primary w-100 mb-3">Place</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const type_order = document.getElementById('type_order');
    const exchange_execution = document.getElementById('exchange_execution');
    const pending_order = document.getElementById('pending_order');
    type_order.addEventListener('change', function handleChange(event) {
        if (event.target.value === 'exchange_execution') {
            exchange_execution.style.display  = 'block';
            pending_order.style.display  = 'none';
        } else {
            exchange_execution.style.display  = 'none';
            pending_order.style.display  = 'block';
        }
    });
</script>
<div class="panel mb-25">
    <div class="panel-header">
        <nav>
            <div class="btn-box d-flex flex-wrap gap-1" id="nav-tab" role="tablist">
                <button class="btn btn-sm btn-outline-primary active" id="nav-account-tab" data-bs-toggle="tab" data-bs-target="#nav-account" type="button" role="tab" aria-controls="nav-trade" aria-selected="true">Account</button>
                <button class="btn btn-sm btn-outline-primary" id="nav-trade-tab" data-bs-toggle="tab" data-bs-target="#nav-trade" type="button" role="tab" aria-controls="nav-trade" aria-selected="true">Trade</button>
                <button class="btn btn-sm btn-outline-primary" id="nav-history-tab" data-bs-toggle="tab" data-bs-target="#nav-history" type="button" role="tab" aria-controls="nav-history" aria-selected="false">History</button>
            </div>
        </nav>
    </div>
    <div class="panel-body">
        <div class="tab-content profile-edit-tab" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-account" role="tabpanel" aria-labelledby="nav-account-tab" tabindex="0">
                <div class="table-responsive">
                    <table id="table-account" class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">Login</th>
                                <th class="text-center">Leverage</th>
                                <th class="text-center">Balance</th>
                                <th width="10%" class="text-center">#</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-trade" role="tabpanel" aria-labelledby="nav-trade-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-opened-order">
                        <thead>
                            <tr>
                                <th class="text-center">Symbol</th>
                                <th class="text-center">Ticket</th>
                                <th class="text-center">Time</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Volume</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">S/L</th>
                                <th class="text-center">T/P</th>
                                <th class="text-center">#</th>
                            </tr>
                        </thead>
                        <tbody>
                           
                        </tbody>
                    </table>
                </div>

                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table-placed-order">
                        <thead>
                            <tr>
                                <th class="text-center">Symbol</th>
                                <th class="text-center">Ticket</th>
                                <th class="text-center">Time</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Volume</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">S/L</th>
                                <th class="text-center">T/P</th>
                                <th class="text-center">#</th>
                            </tr>
                        </thead>
                        <tbody>
                           
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade" id="nav-history" role="tabpanel" aria-labelledby="nav-history-tab" tabindex="0">
                <div class="table-responsive">
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
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script type="text/javascript">
    let table_opentrade, table_historytrade;
    $(document).ready(function() {
        const swalPasswordOption = {
            title: "Enter password",
            text: "Please enter your metatrader account password",
            icon: "info",
            allowOutsideClick: false,
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: "Connect",
            input: "text",
            inputPlaceholder: "Enter Password",
            preConfirm: async function(password) {
                if(!password || !password.length) {
                    return await Swal.showValidationMessage('Password required');
                }

                /** update password */
                let response = await $.post("/ajax/post/account/update-password", {login: account.element.val(), password: password, change_password: 0}, (resp) => {
                    return resp;
                }, 'json')

                if(!response.success) {
                    return Swal.showValidationMessage(response.message);
                }

                return response.success;
            }
        }

        const account = {
            element: $('#account'),
            showLoading(message = "Waiting Connection...") {
                Swal.fire({
                    text: message,
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading()
                    }
                })
            },
            onChange() {
                if(!this.element.val()) {
                    return;
                }
                
                this.showLoading();
                $.post("/ajax/post/market/connect", {account: this.element.val()}, (resp) => {
                    if(!resp.success) {
                        /** Show Password Upate */
                        if(resp.data.required_password) {
                            Swal.fire(swalPasswordOption).then((result) => {
                                if(result.value) {
                                    symbol.refreshSymbol();
                                    table_opentrade.draw();
                                    return;
                                }
                            });
                            return;
                        }

                        Swal.fire(resp.alert);
                        this.element.val("")
                        return;
                    }

                    Swal.close();
                    symbol.refreshSymbol();
                    table_opentrade.draw();
                }, 'json');
            },
            init() {
                this.element.on('change', () => this.onChange()).change();
            },
        }

        const symbol = {
            element: $('#Symbol'),
            prices: [],
            refreshSymbol() {
                this.element.empty().append('<option disabled selected>Loading...</option>');
                let parent = this;
                $.post("/ajax/post/market/symbols", {account: account.element.val()}, async (resp) => {
                    this.element.empty().append('<option disabled selected>Pilih</option>');
                    if(!resp.success) {
                        Swal.fire(resp.alert);
                        return;
                    }

                    Swal.close();
                    await resp.data.forEach((val, i) => {
                        let selected = (i == 0)? "selected" : "";
                        this.element.append(`<option ${selected} value="${val.currency}" data-digits="${val.digits}" data-min="${val.volumeMin}" data-max="${val.volumeMax}">${val.currency}</option>`);
                    })

                    let selected = parent?.element?.find('option:selected')?.length;
                    if(selected) {
                        parent.element.change();
                    }
                }, 'json')
            },
            onChange() {
                account.showLoading("Loading...");
                let data = {
                    account: account.element.val(),
                    symbol: this.element.val()
                }

                $.post("/ajax/post/market/price-history", data, (resp) => {
                    if(!resp.success) {
                        Swal.fire(resp.alert);
                        return;
                    }

                    Swal.close();
                    this.prices = resp.data;
                    this.loadChart();
                }, 'json')
            },
            loadChart() {
                let chartData = []; 
                this.prices.forEach(function(val, index) {
                    chartData.push({
                        x: new Date(val.time),
                        y: [val.openPrice, val.highPrice, val.lowPrice, val.closePrice]
                    })
                });

                let digits  = this.prices[0].digits || false;
                var candlestickChartoptions = {
                    series: [{
                        color: '#FFFFFF',
                        data: chartData
                    }],
                    chart: {
                        type: 'candlestick',
                        height: 537,
                        toolbar: {
                            show: false
                        }
                    },
                    grid: {
                        borderColor: '#334652',
                        strokeDashArray: 3,
                        xaxis: {
                            lines: {
                                show: true,
                            }
                        },
                    },
                    plotOptions: {
                        candlestick: {
                            colors: {
                                upward: '#198754',
                                downward: '#dc3545'
                            }
                        }
                    },
                    xaxis: {
                        type: 'datetime',
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                    },
                    yaxis: {
                        tooltip: {
                            enabled: true,
                        },
                        labels: {
                            show: true,
                            style: {
                                colors: '#ffffff',
                                fontWeight: 400
                            },
                            formatter: function (value) {
                                return (digits !== false) ? parseFloat(value).toFixed(digits) : value
                            },
                        }
                    },
                    // responsive: [{
                    //     breakpoint: 575,
                    //     options: {
                    //         chart: {
                    //             height: 250,
                    //         }
                    //     },
                    // }]
                };
                var chart = new ApexCharts(document.querySelector("#candlestickChartTest"), candlestickChartoptions);
                chart.render();
            },
            init() {
                this.element.on('change', () => this.onChange());
            }
        }

        const execution = {
            buttonBuy: $('#exe-buy'),
            buttonSell: $('#exe-sell'),
            validate() {
                let data = {
                    account: $('#account').val(),
                    symbol: symbol.element.val(),
                    sl: $('#exe-sl').val(),
                    tp: $('#exe-tp').val(),
                    volume: $('#exe-volume').val(),
                }

                if(!data.account) {
                    Swal.fire("Gagal", "Mohon pilih account", "error");
                    return false;
                }

                if(!data.symbol) {
                    Swal.fire("Gagal", "Mohon pilih symbol", "error");
                    return false;
                }

                if(!data.volume || data.volume <= 0) {
                    Swal.fire("Gagal", "Mohon isi jumlah volume", "error");
                    return false;
                }

                selected = symbol.element.find('option:selected').data();
                if(selected) {
                    if(data.volume > selected.max) {
                        Swal.fire("Gagal", `Max Volume (${selected.max})`, "error");
                        return;
                    }

                    if(data.volume < selected.min) {
                        Swal.fire("Gagal", `Min Volume (${selected.min})`, "error");
                        return;
                    }
                }

                return data;
            },
            redraw() {
                table_opentrade.draw();
                table_historytrade.draw();
            },
            async buy() {
                let data = await this.validate();
                data.type = "buy";
                $.post("/ajax/post/market/execution", data, (resp) => {
                    if(!resp.success) {
                        Swal.fire(resp.alert);
                        return;
                    }

                    this.redraw()
                }, 'json')
            },
            async sell() {
                let data = await this.validate();
                data.type = "sell";
                $.post("/ajax/post/market/execution", data, (resp) => {
                    if(!resp.success) {
                        Swal.fire(resp.alert);
                        return;
                    }

                    this.redraw()
                }, 'json')
            },
            init() {
                this.buttonBuy.on('click', () => this.buy());
                this.buttonSell.on('click', () => this.sell());
            }
        }

        account.init();
        symbol.init();
        execution.init();
    })
</script>
<script type="text/javascript">
    $(document).ready(function() {
        let table_account = $('#table-account').DataTable({
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/web-trade-account",
                data: function() {
    
                }
            },
            columnDefs: [
                { targets: 0, className: "text-center" },
                { targets: 1, className: "text-end" },
                { targets: 3, className: "text-center" },
            ],
            drawCallback: function() {
                $('.btn-update').on('click', async function(evt) {
                    let target = $(evt.currentTarget);
                    if(target) {
                        const { value: password } = await Swal.fire({
                            title: "Update Password",
                            showCancelButton: true,
                            reverseButtons: true,
                            input: "password",
                            inputLabel: "Password",
                            inputPlaceholder: "Enter your password",
                            inputAttributes: {
                                autocapitalize: "off",
                                autocorrect: "off",
                            }
                        })

                        if(!password) {
                            Swal.fire("Gagal", "Mohon isi kolom password", "info");
                            return;
                        }

                        Swal.fire({
                            text: "Loading...",
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        })

                        $.post("/ajax/post/account/update-password", {login: target.data('login'), password: password, change_password: 1}, (resp) => {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    table_account.draw();
                                }
                            });
                        }, 'json')
                    }
                })
            }
        })

        table_opentrade = $('#table-opened-order').DataTable({
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/web-trade-opentrade",
                data: function(d) {
                    d.account = $('#account').val()
                }
            },
            columnDefs: [
                { targets: 0, className: "text-center" },
                { targets: 1, className: "text-center" },
                { targets: 2, className: "text-center" },
                { targets: 3, className: "text-center" },
                { targets: 4, className: "text-end" },
                { targets: 5, className: "text-end" },
                { targets: 6, className: "text-end" },
                { targets: 7, className: "text-end" },
                { targets: 8, className: "text-center" },
            ],
            drawCallback: function() {
                $('.close').on('click', function(evt) {
                    let target = $(evt.currentTarget);
                    if(target && target.data('ticket')) {
                        Swal.fire({
                            title: "Close Order",
                            text: "Konfirmasi untuk melanjutkan",
                            icon: "question",
                            showCancelButton: true,
                            reverseButtons: true,
                        }).then((result) => {
                            if(result.isConfirmed) {
                                $.post("/ajax/post/market/close-trade", {account: $('#account').val(), ticket: target.data('ticket')}, (resp) => {
                                    Swal.fire(resp.alert).then(() => {
                                        if(resp.success) {
                                            table_opentrade.draw()
                                            table_historytrade.draw()
                                        }
                                    })
                                }, 'json')
                            }
                        })
                    }
                })
            }
        })

        table_historytrade = $('#table-history').DataTable({
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/web-trade-historytrade",
                data: function(d) {
                    d.account = $('#account').val()
                }
            },
            columnDefs: [
                { targets: 0, className: "text-center" },
                { targets: 1, className: "text-center" },
                { targets: 2, className: "text-center" },
                { targets: 3, className: "text-center" },
                { targets: 4, className: "text-end" },
                { targets: 5, className: "text-end" },
                { targets: 6, className: "text-end" },
                { targets: 7, className: "text-end" },
                { targets: 8, className: "text-center" },
            ],
        })
    })
</script>