const Trade = (() => {

    let accountInfo = null;
    let openOrders = new Map();
    let pendingOrders = new Map();
    let closedOrders = new Map();

    const type = {
        0: 'Buy',
        1: 'Sell',
        2: 'Buy Limit',
        3: 'Sell Limit',
        4: 'Buy Stop',
        5: 'Sell Stop'
    }

    const colors = (change) => {
        if(change >= 0) return 'positive';
        if(change < 0) return 'negative';
        return '';
    }

    const setOpenOrders = (orders) => {
        openOrders = new Map();
        if(!orders || !orders.length) {
            return;
        }

        orders.forEach((order) => openOrders.set(order.ticket, order));
    }

    const setPendingOrders = (orders) => {
        pendingOrders = new Map();
        if(!orders || !orders.length) {
            return;
        }

        orders.forEach((order) => pendingOrders.set(order.ticket, order));
    }

    const init = async (account) => {
        accountInfo = account;
    }

    return {
        init,
        type,
        colors,
        get openOrders() { return openOrders; },
        setOpenOrders,
        get pendingOrders() { return pendingOrders; },
        setPendingOrders
    }

})();

export default Trade;