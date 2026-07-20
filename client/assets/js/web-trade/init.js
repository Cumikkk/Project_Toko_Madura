import { load } from './helper.js';

document.addEventListener('DOMContentLoaded', async () => {
    const [Account, Symbol, Socket, overlay, Trade, Store, ApiStore] = await Promise.all([
        load('account'),
        load('symbol'),
        load('socket'),
        load('overlay'),
        load('trade'),
        load('store'),
        load('api'),
    ]);

    DataTable.ext.errMode = function (s, tn, msg) {
        console.log(msg, tn);
    };

    let tableOpenOrders, 
        tablePendingOrders,
        tableAccounts, 
        socketAccountListener, 
        socketTickListener;

    const init = async () => {
        /** Init accounts */
        overlay.show('Loading accounts...');
        await Account.init();
        let activeAccount = Account.getAccount(Account.selectedAccount);
        if(!activeAccount) {
            console.error('Selected account not found: ' + Account.selectedAccount);
            // throw new Error('Selected account not found: ' + Account.selectedAccount);
            overlay.hide();
            return;
        }

        /** Init trade */
        Trade.init(activeAccount);
        
        /** Init symbols awal */
        await Symbol.init(activeAccount);

        /** Init account socket */
        await Socket.init(activeAccount);
        socketAccountListener = await Socket.accountSocket();
        socketAccountListener.onmessage = function(event) {
            let data = JSON.parse(event.data);
            Trade.setOpenOrders(data.open_positions);
            Trade.setPendingOrders(data.pending_orders);
            Account.setAccount(data.login, {
                balance: data.balance,
                credit: data.credit,
                equity: data.equity,
                margin: data.margin,
                margin_free: data.free_margin,
                margin_level: data.margin_level
            })
    
            const latestOpenOrders = Array.from(Trade.openOrders.values());
            tableOpenOrders.clear();
            tableOpenOrders.rows.add(latestOpenOrders).draw(false);

            const latestPendingOrders = Array.from(Trade.pendingOrders.values());
            tablePendingOrders.clear();
            tablePendingOrders.rows.add(latestPendingOrders).draw(false);
        }

        const updateSymbolRow = (symbol) => {
            let row = document.querySelector(`.select-favorite[data-symbol="${symbol.symbol}"]`)?.closest('tr');
            if(row) {
                row.querySelector('td:nth-child(3)').textContent = symbol.bid;
                row.querySelector('td:nth-child(3)').className = Trade.colors(symbol.bid_change) + ' text-center';

                row.querySelector('td:nth-child(4)').textContent = symbol.ask;
                row.querySelector('td:nth-child(4)').className = Trade.colors(symbol.ask_change) + ' text-center';
            }
        }

        /** init symbol socket */
        let symbols = Symbol.list();
        if(symbols && symbols.size > 0) {
            socketTickListener = await Socket.tickSocket();
            socketTickListener.onmessage = (event) => {
                let tick = JSON.parse(event.data);
                if(symbols.has(tick.symbol)) {
                    let symbolData = symbols.get(tick.symbol);
                    symbolData.bid_change = symbolData.bid ? (tick.bid - parseFloat(symbolData.bid)) : 0;
                    symbolData.ask_change = symbolData.ask ? (tick.ask - parseFloat(symbolData.ask)) : 0;
                    symbolData.bid = tick.bid.toFixed(tick.digits);
                    symbolData.ask = tick.ask.toFixed(tick.digits);
    
                    symbols.set(tick.symbol, symbolData);
                    Store.symbolStore().upload(symbols);
                    updateSymbolRow(symbolData);
                }
            }
        }

        overlay.hide();
    } 

    const reset = async () => {
        console.clear();
        Store.symbolStore().reset();
        socketTickListener?.close();
        socketAccountListener?.close();
    }

    await init();

    /** table Open Trade */
    tableOpenOrders = await $('#table-opened-order').DataTable({
        dom: 't',
        scrollX: true,
        searching: false,
        data: Array.from(Trade.openOrders.values()),
        order: [[1, 'desc']],
        columns: [
            { title: 'Symbol', data: 'symbol', className: 'text-center', orderable: false },
            { title: 'Ticket', data: 'ticket', className: 'text-center' },
            { title: 'Open Time', data: 'open_time', className: 'text-center' },
            { title: 'Type', data: 'type', className: 'text-center', orderable: false},
            { title: 'Volume', data: 'volume', className: 'text-center' },
            { title: 'Open Price', data: 'open_price', className: 'text-center', orderable: false },
            { title: 'Stop Loss', data: 'stop_loss', className: 'text-center', orderable: false },
            { title: 'Take Profit', data: 'take_profit', className: 'text-center', orderable: false },
            { title: 'Current Price', data: 'current_price', className: 'text-center', orderable: false },
            { title: 'Profit', data: 'profit', className: 'text-center' },
        ],
        columnDefs: [
            {
                targets: 2,
                render: function(data, type, row) {
                    return new Date(data * 1000).toLocaleString();
                }
            },
            {
                targets: 3,
                render: function(data, type, row) {
                    return Trade.type[data] || '-';
                }
            },
            {
                targets: 9,
                render: function(data, type, row) {
                    return `<span class="${Trade.colors(Number(data))}">${data}</span>`;
                }
            }
        ],
        drawCallback: function() {
            /** callback on table row clicked */
            $(this).find('tbody tr').on('click', function() {
                const ticket = $(this).find('td:nth-child(2)').text();
                const order = Array.from(Trade.openOrders.values()).filter(o => o.ticket == ticket)[0];
                if(!order) {
                    return false;
                }

                let url = `/ajax/modal/web-trade/modify?ticket=${ticket}&sl=${order.stop_loss}&tp=${order.take_profit}`;
                const modifyBtn = document.getElementById('modify-order');
                modifyBtn.setAttribute('data-url', url);
                modifyBtn.setAttribute('data-callback', 'modifyOrder');
                modifyBtn.setAttribute('data-title', `Modify Order [${Trade.type[order.type]?.toUpperCase() || '-'}] #${ticket}`);
                modifyBtn.setAttribute('data-orders', btoa(JSON.stringify(order)));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('dynamicModalDefault')).show(modifyBtn);
                console.log('Clicked order: ', order);
            });
        }
    })

    tablePendingOrders = await $('#table-pending-order').DataTable({
        dom: 't',
        scrollX: true,
        searching: false,
        data: Array.from(Trade.pendingOrders.values()),
        order: [[1, 'desc']],
        columns: [
            { title: 'Symbol', data: 'symbol', className: 'text-center', orderable: false },
            { title: 'Ticket', data: 'ticket', className: 'text-center' },
            { title: 'Time', data: 'time_setup', className: 'text-center' },
            { title: 'Type', data: 'type', className: 'text-center', orderable: false},
            { title: 'Volume', data: 'volume', className: 'text-center' },
            { title: 'Price', data: 'price_order', className: 'text-center', orderable: false },
            { title: 'Stop Loss', data: 'stop_loss', className: 'text-center', orderable: false },
            { title: 'Take Profit', data: 'take_profit', className: 'text-center', orderable: false },
            { title: 'Current Price', data: 'price_current', className: 'text-center', orderable: false },
        ],
        columnDefs: [
            {
                targets: 2,
                render: function(data, type, row) {
                    return new Date(data * 1000).toLocaleString();
                }
            },
            {
                targets: 3,
                render: function(data, type, row) {
                    return Trade.type[data] || '-';
                }
            }
        ],
        drawCallback: function() {
            /** callback on table row clicked */
            $(this).find('tbody tr').on('click', function() {
                const ticket = $(this).find('td:nth-child(2)').text();
                const order = Array.from(Trade.pendingOrders.values()).filter(o => o.ticket == ticket)[0];
                if(!order) {
                    return false;
                }

                order.is_pending = true
                let url = `/ajax/modal/web-trade/modify?ticket=${ticket}&sl=${order.stop_loss}&tp=${order.take_profit}`;
                const modifyBtn = document.getElementById('modify-order');
                modifyBtn.setAttribute('data-url', url);
                modifyBtn.setAttribute('data-callback', 'modifyOrder');
                modifyBtn.setAttribute('data-title', `Modify Order [${Trade.type[order.type]?.toUpperCase() || '-'}] #${ticket}`);
                modifyBtn.setAttribute('data-orders', btoa(JSON.stringify(order)));
                bootstrap.Modal.getOrCreateInstance(document.getElementById('dynamicModalDefault')).show(modifyBtn);
                console.log('Clicked order: ', order);
            });
        }
    })

    /** table accounts */
    tableAccounts = await $('#table-account').DataTable({
        dom: 't',
        scrollX: true,
        data: Array.from(Account.list().values()),
        columns: [
            { data: 'login', className: 'text-center' },
            { data: 'leverage', className: 'text-center' },
            { data: 'balance', className: 'text-center' },
            { data: 'action', className: 'text-center' },
        ],
        columnDefs: [
            {
                targets: 3,
                render: function(data, type, row) {
                    let buttonSelect = `<a href="javascript:void(0)" class="btn btn-sm btn-primary text-white text-decoration-none btn-select" data-login="${row.login}">Select</a>`;
                    if(row.login === Account.selectedAccount) {
                        buttonSelect = '<span class="badge bg-success">Selected</span>';
                    }

                    return `
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                            <a href="javascript:void(0)" class="btn btn-sm btn-success text-white btn-update" data-login="${row.login}"><i class="fas fa-lock"></i></a>
                            ${buttonSelect}
                        </div>
                    `;
                }
            }
        ],
        drawCallback: function() {
            $('#table-account .btn-update').on('click', function() {
                let clickedAccount = $(this).data('login');
                const { newPassword } = Swal.fire({
                    icon: "info",
                    title: 'Update Account Password',
                    input: 'password',
                    inputLabel: 'New Password',
                    inputPlaceholder: 'Enter new password',
                    showCancelButton: true,
                    inputValidator: (value) => {
                        if(!value) {
                            return 'Password is required!';
                        }
                    }
                }).then(async (result) => {
                    if(result.isConfirmed) {
                        const newPassword = result.value;
                        Swal.fire({
                            title: 'Updating Password',
                            text: 'Please wait while we update the account password.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        })

                        let response = await ApiStore.updateAccountPassword(clickedAccount, newPassword);
                        Swal.fire(response.alert);
                    }
                });
            });
        }
    })

    $('#table-account').on('click', '.btn-select', async function() {
        const selected = Account.selectAccount($(this).data('login'));
        if(!selected) {
            return;
        }

        const selectedAccountInfo = Account.getAccount(Account.selectedAccount);
        if(!selectedAccountInfo) {
            console.error('Selected account info not found after update: ', Account.selectedAccount);
            return;
        }

        tableAccounts.clear().rows.add(Array.from(Account.list().values())).draw(false);
        await reset();
        await init();
    });

    $('#btn-refresh').on('click', async function() {
        overlay.show('Refreshing accounts...');
        await reset();
        await init();
        overlay.hide();
    })
});