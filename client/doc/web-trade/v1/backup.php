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
                        <label for="Symbol" class="form-label">Account</label>
                        <select id="Symbol" class="form-select">
                            <option selected>Choose...</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label for="Symbol" class="form-label">Symbol</label>
                        <select id="Symbol" class="form-select">
                            <option selected>Choose...</option>
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
                            <input type="number" name="po-sl" id="po-sl" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label for="po-tp" class="form-label">TP</label>
                            <input type="number" name="po-tp" id="po-tp" class="form-control" required>
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
                    <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">Login</th>
                                <th class="text-center">Leverage</th>
                                <th class="text-center">Balance</th>
                                <th class="text-center">#</th>
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
    let account = $('#account').val();
    let table_open, table_history, table_placed;
    
    (function($) {
        'use strict';

        $(document).ready(async function() {
            $('#Symbol').on('change', loadPrice);
            if(account.replace('-', '').length) {
                $('#Symbol').val('EURUSD').change()
            }

            // Action
            $('#exe-sell').on('click', exchangeExecution);
            $('#exe-buy').on('click', exchangeExecution);
            $('#po-place').on('click', pendingOrder)
            $('.close-order').on('click', closeOrder)


            // DataTable
            table_open = await $('#table-opened-order').DataTable({
                processing: true,
                order: [[1, 'desc']],
                ajax: {
                    url: "/ajax/marketPost.php",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        method: "openedOrders",
                        login: account
                    }
                },
                columns: [
                    { data: 'symbol' },
                    { data: 'ticket' },
                    { data: 'openTime' },
                    { data: 'orderType' },
                    { data: 'volume' },
                    { data: 'openPrice' },
                    { data: 'stopLoss' },
                    { data: 'takeProfit' },
                    { data: {ticket: 'ticket', symbol: 'symbol', stopLoss: 'stopLoss', takeProfit: 'takeProfit'} },
                ],
                columnDefs: [
                    {
                        targets: 8,
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            return `
                                <button type="button" class="btn btn-success btn-sm mb-1 edit-order" data-symbol="${data.symbol}" data-ticket="${data.ticket}" data-sl="${data.stopLoss}" data-tp="${data.takeProfit}" >edit</button>
                                <button type="button" onclick="closeOrder(this)" class="btn btn-danger btn-sm mb-1 close-order" data-ticket="${data.ticket}">close</button>
                            `
                        }
                    }
                ],
                drawCallback: function(settings) {
                    $('.edit-order').on('click', function() {
                        $('#update-ticket').val( $(this).data('ticket') )
                        $('#update-symbol').val( $(this).data('symbol') )
                        $('#update-current-sl').val( $(this).data('sl') )
                        $('#update-current-tp').val( $(this).data('tp') )
                        $('#modalEditSLTP').modal('show');
                    })
                }
            });

            table_placed = await $('#table-placed-order').DataTable({
                processing: true,
                order: [[1, 'desc']],
                ajax: {
                    url: "/ajax/marketPost.php",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        method: "placedOrders",
                        login: account
                    }
                },
                columns: [
                    { data: 'symbol' },
                    { data: 'ticket' },
                    { data: 'time_setup' },
                    { data: 'type' },
                    { data: 'volume' },
                    { data: 'price_open' },
                    { data: 'sl' },
                    { data: 'tp' },
                    { data: {ticket: 'ticket', symbol: 'symbol', sl: 'sl', tp: 'tp'} },
                ],
                columnDefs: [
                    {
                        targets: 8,
                        searchable: false,
                        orderable: false,
                        render: function(data) {
                            return `
                                <button type="button" onclick="closeOrder(this, true)" class="btn btn-danger btn-sm close-order" data-ticket="${data.ticket}">Cancel</button>
                            `
                        }
                    }
                ],
            });

            table_history = await $('#table-history').DataTable({
                processing: true,
                order: [[1, 'desc']],
                responsive: true,
                ajax: {
                    url: "/ajax/marketPost.php",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        method: "historyOrders",
                        login: account
                    }
                },
                columns: [
                    { data: 'openTime' },
                    { data: 'ticket' },
                    { data: 'symbol' },
                    { data: 'orderType' },
                    { data: 'lots' },
                    { data: 'openPrice' },
                    { data: 'stopLoss' },
                    { data: 'takeProfit' },
                    { data: 'profit' },
                ],
            });
        });

        async function loadPrice() {
            await $.ajax({
                url: "/ajax/marketPost.php",
                type: "POST",
                dataType: "JSON",
                data: {
                    method: "priceHistory",
                    symbol: this.value,
                    login: account
                }
            })
            .done(function(resp) {
                if(!resp.success) {
                    Swal.fire('Failed', resp?.error || "Gagal memuat price, mohon coba lagi", 'error');
                    return false;
                }

                let volumeMin = $('#Symbol option:selected')?.data('min')
                let volumeMax = $('#Symbol option:selected')?.data('max')
                let volumeStep = $('#Symbol option:selected')?.data('step')

                $('#exe-volume').attr('min', volumeMin).attr('max', volumeMax).attr('step', volumeStep).val(volumeMin);
                $('#po-volume').attr('min', volumeMin).attr('max', volumeMax).attr('step', volumeStep).val(volumeMin);

                let chartData = []; 
                resp.message.forEach(function(val, index) {
                    chartData.push({
                        x: new Date(val.time),
                        y: [val.openPrice, val.highPrice, val.lowPrice, val.closePrice]
                    })
                });

                let digits  = resp.message[0].digits || false;
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
            })
        }

        async function exchangeExecution(exchange_event) {
            exchange_event.preventDefault();
            let oldContent  = $(this).text();

            try {
                let executeType = $('#type_order').val()
                let orderType   = $(this).data('type') || false
                let stopLoss    = parseFloat($('#exe-sl').val())
                let takeProfit  = parseFloat($('#exe-tp').val())
                let volume      = parseFloat($('#exe-volume').val())
                let symbol      = $('#Symbol').val();
    
                if(!symbol || !symbol.length) {
                    Swal.fire('Failed', 'Invalid Symbol', 'error')
                    return false;
                }
                
                if(!orderType || (orderType != "buy" && orderType != "sell")) {
                    Swal.fire('Failed', 'Invalid Order Type', 'error')
                    return false;
                }
    
                if(isNaN(stopLoss)) {
                    Swal.fire('Failed', 'Invalid SL', 'error')
                    return false;
                }
    
                if(isNaN(takeProfit)) {
                    Swal.fire('Failed', 'Invalid TP', 'error')
                    return false;
                }
    
                if(!volume || volume <= 0 || isNaN(volume)) {
                    Swal.fire('Failed', 'Invalid Volume', 'error')
                    return false;
                }

                if(volume < parseFloat($('#exe-volume').attr('min'))) {
                    Swal.fire('Failed', 'Min volume = ' + $('#exe-volume').attr('min'), 'error')
                    return false;
                }

                if(volume > parseFloat($('#exe-volume').attr('max'))) {
                    Swal.fire('Failed', 'Max volume = ' + $('#exe-volume').attr('max'), 'error')
                    return false;
                }

                showLoading(this, true);
                await $.ajax({
                    url: "/ajax/marketPost.php",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        method: "orderSend",
                        login: account,
                        type: orderType,
                        sl: stopLoss,
                        tp: takeProfit,
                        volume: volume,
                        executeType: executeType,
                        symbol: symbol
                    }
                })
                .done(function(resp) {
                    if(!resp.success) {
                        Swal.fire('Failed', resp.error, 'error');
                        return false;
                    }

                    Swal.fire('Success', `Order Ticket: ${resp?.message?.ticket || 0}`, 'success');
                    table_open.ajax.reload();
                    table_placed.ajax.reload()
                })

            } catch (error) {
                Swal.fire('Error', error.statusText || 'error occured', 'error')
            }

            showLoading(this, false, oldContent)
        }

        async function pendingOrder(pendingEvent) {
            pendingEvent.preventDefault();
            let oldContent  = $(this).text();

            try {
                let executeType = $('#type_order').val()
                let orderType   = $('#po-type').val()
                let stopLoss    = parseFloat($('#po-sl').val())
                let takeProfit  = parseFloat($('#po-tp').val())
                let volume      = parseFloat($('#po-volume').val())
                let volumeMin   = parseFloat($('#po-volume').attr('min'))
                let volumeMax   = parseFloat($('#po-volume').attr('max'))
                let symbol      = $('#Symbol').val();
                let price       = parseFloat($('#po-price').val())
    
                if(!symbol || !symbol.length) {
                    Swal.fire('Failed', 'Invalid Symbol', 'error')
                    return false;
                }
                
                if(isNaN(stopLoss)) {
                    Swal.fire('Failed', 'Invalid SL', 'error')
                    return false;
                }
    
                if(isNaN(takeProfit)) {
                    Swal.fire('Failed', 'Invalid TP', 'error')
                    return false;
                }
    
                if(!volume || volume <= 0 || isNaN(volume)) {
                    Swal.fire('Failed', 'Invalid Volume', 'error')
                    return false;
                }

                if(!price || price <= 0 || isNaN(price)) {
                    Swal.fire('Failed', 'Invalid Price', 'error')
                    return false;
                }

                if(volume < volumeMin) {
                    Swal.fire('Failed', `Min Volume = ${volumeMin}`, 'error')
                }

                if(volume > volumeMax) {
                    Swal.fire('Failed', `Max Volume = ${volumeMax}`, 'error')
                }
                
                showLoading(this, true);
                await $.ajax({
                    url: "/ajax/marketPost.php",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        method: "orderPlace",
                        login: account,
                        type: orderType,
                        sl: stopLoss,
                        tp: takeProfit,
                        volume: volume,
                        price: price,
                        executeType: executeType,
                        symbol: symbol
                    }
                })
                .done(function(resp) {
                    if(!resp.success) {
                        Swal.fire('Failed', resp.error, 'error');
                        return false;
                    }

                    Swal.fire('Success', `Place Ticket: ${resp?.message?.ticket || 0}`, 'success');
                    table_open.ajax.reload();
                    table_placed.ajax.reload()
                })

            } catch (error) {
                Swal.fire('Error', error.statusText || 'error occured', 'error')
                throw error;
            }

            showLoading(this, false, oldContent);
        }
    })(jQuery);
    
    async function closeOrder(el, placed=false) {
        try {
            let ticket      = $(el).data('ticket')
            let oldContent  = $(this).text();

            if(ticket == null || isNaN(ticket)) {
                Swal.fire('error', 'Invalid Ticket', 'error')
                return false
            }

            showLoading(this, true);

            Swal.fire({
                title: "Confirmation",
                text: `Close Order Ticket: ${ticket} ?`,
                showCancelButton: true,
                showConfirmButton: true,
                icon: "question"
            
            }).then(function(result) {
                if(result.value) {

                    $.ajax({
                        url: "/ajax/marketPost.php",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            method: "closeOrder",
                            placed: placed,
                            login: account,
                            ticket: ticket
                        }
                    })
                    .done(function(resp) {
                        if(!resp.success) {
                            Swal.fire('error', (resp.error || "Error Response"), 'error')
                            return false
                        }
        
                        Swal.fire('Success', `Berhasil close ticket: #${ticket}`, 'success')
                        table_open.ajax.reload();
                        table_placed.ajax.reload()
                        table_history.ajax.reload();
                    })
                }
            })

            showLoading(this, false, oldContent);

        } catch (error) {
            Swal.fire('Error', (error.statusText || "error occured"), 'error')
            throw error
        }
    }

    async function modifyOrder(button) {
        try {
            let sl    = $('#update-sl').val();
            let tp    = $('#update-tp').val();
            let oldContent  = $(button).text();

            if(!sl.length || isNaN(sl)) {
                Swal.fire('Failed', 'Invalid SL', 'error')
                return false;
            }

            if(!tp.length || isNaN(tp)) {
                Swal.fire('Failed', 'Invalid TP', 'error')
                return false;
            }

            await $.ajax({
                url: "/ajax/marketPost.php",
                type: "POST",
                dataType: "JSON",
                data: {
                    method: "orderModify",
                    login: account,
                    ticket: $('#update-ticket').val(),
                    symbol: $('#update-symbol').val(),
                    stoploss: sl, 
                    takeprofit: tp,
                    placed: $(button).data('pending')
                }
            })
            .done(function(resp) {
                if(!resp.success) {
                    Swal.fire('error', (resp.error || "Error Response"), 'error')
                    return false
                }

                Swal.fire('Success', `Berhasil update SLTP: #${$('#update-ticket').val()}`, 'success')
                table_open.ajax.reload();
                table_placed.ajax.reload()
                table_history.ajax.reload();
            })


        } catch (error) {
            Swal.fire('Error', (error.statusText || "error occured"), 'error')
            throw error
        }

        $(button).attr('data-pending', 'false')
        showLoading(button, false, oldContent);
    }

    async function showLoading(element, enable=true, content="-") {
        if(enable) {
            $(element).html(`
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span role="status">Loading...</span>
            `)
            .attr('disabled', 'disabled')
        
        }else {
            $(element).html(content).removeAttr('disabled')
        }
    }
</script>