import { load } from './helper.js';

const [ApiStore, Socket, Store, overlay, Trade] = await Promise.all([
    load('api'),
    load('socket'),
    load('store'),
    load('overlay'),
    load('trade'),
]);

const Symbol = (() => {

    const chartUrl = 'https://webchart-rrfx.techcrm.dev';
    let symbols = new Map();
    let selectedSymbol = null;

    let categories = new Set(['All', 'Favorite']);
    let selectedCategory = 'All';

    let socketTickListener = null;
    let chartContainer = document.getElementById('chart');
    let accountInfo = null;

    const load = async () => {
        symbols = Store.symbolStore().get();
        if(symbols && symbols.size) {
            let symbolFavorite = getFilteredSymbols('Favorite');
            if(symbolFavorite.length) {
                selectSymbol(symbolFavorite[0].symbol);
                return;
            } 

            selectSymbol(symbols.values().next().value.symbol);
            return;
        }

        let response = await ApiStore.fetchSymbols(accountInfo.login);
        if(!response) {
            console.error('No symbols found for the account: ', accountInfo.login);
            return;
        }

        if(Object.hasOwn(response, 'require_reset') && response.require_reset) {
            Store.symbolStore().reset();
            Swal.fire({
                title: 'Invalid Account Password',
                text: 'Please change your account password in tab "Accounts" and refresh the symbol list.',
                icon: 'info',
            })
            return;
        }

        symbols = new Map();
        response?.data.forEach((val) => symbols.set(val.symbol, val));
        Store.symbolStore().upload(symbols);
        selectSymbol(response.data[0].symbol);
    };

    const refreshCategories = () => {
        categories = new Set(['All', 'Favorite']);
        symbols?.forEach((symbol) => {
            if(!categories.has(symbol.group)) {
                categories.add(symbol.group);
            }
        });
    }

    const getFilteredSymbols = (category) => {
        if(!symbols) {
            return [];
        }

        switch(category) {
            case 'All':
                return Array.from(symbols.values());
            case 'Favorite':
                return Array.from(symbols.values()).filter((symbol) => symbol.favorite);
            default:
                return Array.from(symbols.values()).filter((symbol) => symbol.group === category);
        }
    }

    const renderMarketCategories = async () => {
        await refreshCategories();
        let arrayHtml = Array.from(categories).map((category) => {
            return `<a href="javascript:void(0)" class="btn btn-sm btn-outline-primary select-category ${category === selectedCategory ? 'active' : ''}" data-category="${category}">${category}</a>`;
        });

        document.getElementById('category-tab').innerHTML = arrayHtml.join('');

        const resetActiveClass = () => {
            document.querySelectorAll('.select-category').forEach((el) => el.classList.remove('active'));
        }

        const setActiveClass = (category) => {
            document.querySelectorAll('.select-category').forEach((el) => {
                if(el.dataset.category === category) {
                    el.classList.add('active');
                }
            });
        }

        /** add event listener click select category */
        document.querySelectorAll('.select-category').forEach((element) => {
            element.addEventListener('click', (e) => {
                selectedCategory = e.currentTarget.dataset.category;
                resetActiveClass();
                setActiveClass(selectedCategory);
                renderMarketSymbols();
            })
        });
    }

    const selectSymbol = (symbol) => {
        if(!symbols.has(symbol)) {
            console.error('Symbol not found: ', symbol);
            return;
        }
        
        selectedSymbol = symbol;
        updateSymbolInfo();
    }

    const updateSymbolInfo = () => {
        chartContainer.innerHTML = '';
        document.getElementById('symbol').textContent = selectedSymbol;
        document.getElementById('new-order').setAttribute('data-default', selectedSymbol);
    }

    const renderMarketSymbols = () => {
        let filteredSymbols = getFilteredSymbols(selectedCategory);
        let container = document.getElementById('symbols');
        container.innerHTML = filteredSymbols.map((symbol) => {
            return `<tr>
                <td><span class="${symbol.favorite ? 'fas fa-2x fa-star' : 'far fa-2x fa-star'} text-primary select-favorite" data-symbol="${symbol.symbol}"></span></td>
                <td class="text-start">${symbol.symbol}</td>
                <td class="text-center ${Trade.colors(symbol.bid_change)}">${symbol.bid}</td>
                <td class="text-center ${Trade.colors(symbol.ask_change)}">${symbol.ask}</td>
            </tr>`;
        }).join('');

        /** add event listener click select favorite */
        document.querySelectorAll('.select-favorite').forEach((element) => {
            element.addEventListener('click', (e) => {
                let symbol = e.currentTarget.dataset.symbol;
                if(symbols.has(symbol)) {
                    let symbolData = symbols.get(symbol);
                    symbolData.favorite = !symbolData.favorite;
                    symbols.set(symbol, symbolData);

                    Store.symbolStore().upload(symbols);
                    renderMarketSymbols();
                }
            })
        });

        document.querySelectorAll('#symbols tr td:nth-child(2), #symbols tr td:nth-child(3), #symbols tr td:nth-child(4)').forEach((element) => {
            element.addEventListener('click', (e) => {
                let symbol = e.currentTarget.parentElement.querySelector('.select-favorite').dataset.symbol;
                if(selectedSymbol === symbol) {
                    return;
                }

                overlay.show('Loading chart...');
                selectSymbol(symbol);
                renderMarketChart();
                setTimeout(() => overlay.hide(), 1500);
            })
        });
    }

    const renderMarketChart = async () => {
        if(!selectedSymbol) {
            console.error('No selected symbol found.');
            return;
        }

        let params = new URLSearchParams({
            symbol: selectedSymbol,
            server: accountInfo.type?.toLowerCase(),
            login: accountInfo.login,
            theme: document.querySelector('body').classList.contains('dark-theme') ? 'dark' : 'light'
        });

        console.log('Selected symbol: ', selectedSymbol);
        let iframe = document.createElement('iframe');
        iframe.src = `${chartUrl}/?${params.toString()}`;
        iframe.style.border = 'none';
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        chartContainer.appendChild(iframe);
        chartContainer.style.display = 'block';
    }

    const init = async (account) => {
        try {
            accountInfo = account;
            if(!accountInfo) {
                console.error('No selected account found for symbol initialization.');
                return;
            }
    
            await load();
            await Socket.init(accountInfo);
            await renderMarketCategories();
            await renderMarketChart();
            await renderMarketSymbols();

        } catch (error) {
            throw error;
        }
    }

    return {
        get selectedSymbol() { return selectedSymbol; },
        list: () => symbols,
        categories: () => categories,
        load,
        init,
        renderMarketCategories,
        renderMarketSymbols,
        renderMarketChart,
    }

})();

export default Symbol;
