import { load } from './helper.js';

const [Store] = await Promise.all([
    load('store')
]);

const Market = (() => {

    function validateSymbol(symbol) {
        if (!symbol || typeof symbol !== 'string') {
            return false;
        }

        /** check symbol is exists */
        let existsSymbol = Store.symbolStore().get().get(symbol);
        if(!existsSymbol) {
            return false;
        }

        return existsSymbol;
    }

    function validateStopLoss(stopLoss, price, operation) {
        switch(true) {
            case ['buy', 'buylimit', 'buystop'].includes(operation):
                if(stopLoss && stopLoss > 0) {
                    if(stopLoss >= price) {
                        throw new Error("Stop loss must be less than the open price for buy orders.");
                    }
                }

                break;
            case ['sell', 'selllimit', 'sellstop'].includes(operation):
                if(stopLoss && stopLoss > 0) {
                    if(stopLoss <= price) {
                        throw new Error("Stop loss must be greater than the open price for sell orders.");
                    }
                }
                break;
            default:
                throw new Error("Invalid operation");
        }

        return true;
    }

    function priceToPips(price, openPrice, symbolInfo) {
        if(!symbolInfo) {
            throw new Error("Invalid symbol info");
        }

        return Math.abs(openPrice - price) * Math.pow(10, symbolInfo.digits);
    }

    function open(data) {
        return new Promise(async (resolve, reject) => {
             try {
                // Simulate an API call to place the order
                const { account, symbol, volume, operation, sl, tp, price } = data;

                let symbolInfo = await new Promise(async (res) => {
                    let checkSymbol = await validateSymbol(symbol)
                    res(checkSymbol);
                });

                if(!symbolInfo) {
                    throw new Error("Invalid symbol");
                }

                if(!account) {
                    throw new Error("Invalid Account");
                }

                if(price <= 0) {
                    throw new Error("Invalid price");
                }

                if(sl && sl > 0) {
                    let slPips = priceToPips(sl, price, symbolInfo);
                    data.sl = slPips.toFixed(symbolInfo.digits);
                }

                if(tp && tp > 0) {
                    let tpPips = priceToPips(tp, price, symbolInfo);
                    data.tp = tpPips.toFixed(symbolInfo.digits);
                }

                if(validateStopLoss(sl, price, operation) !== true) {
                    throw new Error("Invalid stop loss");
                }

                await $.ajax({
                    url: "/ajax/post/market/execution",
                    method: "POST",
                    dataType: "json",
                    data: data,
                    success: function(response) {
                        resolve(response);
                    },
                    error: function() {
                        reject(new Error('An error occurred while placing the order.'));
                    }
                })

            } catch (error) {
                reject(error);
            }
        });
    }

    function close(data) {
        return new Promise(async (resolve, reject) => { 
            try {
                const { account, ticket } = data;
                if(!ticket) {
                    throw new Error("Invalid order id");
                }

                if(!account) {
                    throw new Error("Invalid Account");
                }

                await $.ajax({
                    url: "/ajax/post/market/close",
                    method: "POST",
                    dataType: "json",
                    data: data,
                    success: function(response) {
                        resolve(response);
                    },
                    error: function() {
                        reject(new Error('An error occurred while closing the order.'));
                    }
                })
            } catch (error) {
                reject(error);
            }
        });
    }

    function modify(data) {
        return new Promise(async (resolve, reject) => { 
            try {
                let { 
                    account, 
                    ticket, 
                    sl = 0, 
                    tp = 0, 
                    price,
                    operation,
                    symbolInfo,
                    is_pending = false 
                } = data;

                if(!ticket) {
                    throw new Error("Invalid order id");
                }

                if(!account) {
                    throw new Error("Invalid Account");
                }

                if(sl && sl > 0) {
                    let slPips = priceToPips(sl, price, symbolInfo);
                    data.sl = slPips.toFixed(symbolInfo.digits);
                }

                if(tp && tp > 0) {
                    let tpPips = priceToPips(tp, price, symbolInfo);
                    data.tp = tpPips.toFixed(symbolInfo.digits);
                }

                if(validateStopLoss(sl, price, operation) !== true) {
                    throw new Error("Invalid stop loss");
                }

                await $.ajax({
                    url: "/ajax/post/market/modify",
                    method: "POST",
                    dataType: "json",
                    data: data,
                    success: function(response) {
                        resolve(response);
                    },
                    error: function() {
                        reject(new Error('An error occurred while modifying the order.'));
                    }
                })

            } catch (error) {
                reject(error);
            }
        });
    }
    
    return {
        open,
        close,
        modify,
        priceToPips
    }

})();

export default Market;