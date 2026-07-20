import { load } from './helper.js';

const [ApiStore, Store] = await Promise.all([
    load('api'),
    load('store')
]);

const Account = (async () => {

    let accounts = new Map();
    let selectedAccount = null;

    const load = async () => {
        let response = await ApiStore.fetchAccounts();
        if(!response.length) {
            console.error('No accounts found for the user');
            return;
        }

        response?.forEach((val) => {
            accounts.set(val.login, val);
        })

        let selected = Store.accountStore().get();
        if(!selected || !accounts.has(selected)) {
            selected = response[0].login;
            console.log('selected account from server: ', selected);
        }

        selectAccount(selected);
    };

    const list = () => {
        return accounts;
    };

    const refreshAccounts = async () => {
        selectedAccount = null;
        await accounts.clear();
        await load();
    }

    const updateAccountDisplay = () => {
        let accountInfo = getAccount(selectedAccount);
        if(!accountInfo) {
            console.error('Account not found: ', selectedAccount);
            return;
        }

        document.getElementById('account-number').textContent = accountInfo?.login || 0;
        document.getElementById('account-server').textContent = accountInfo?.type || '';
        document.getElementById('balance').textContent = accountInfo?.balance || 0;
        document.getElementById('equity').textContent = accountInfo?.equity || 0;
        document.getElementById('margin').textContent = accountInfo?.margin || 0;
        document.getElementById('margin-free').textContent = accountInfo?.margin_free || 0;
        document.getElementById('margin-level').textContent = accountInfo?.margin_level || 0;
    };

    const getAccount = (account) => {
        const resolvedKey = Number(account);
        if(accounts.has(resolvedKey)) {
            return accounts.get(resolvedKey);
        }

        return null;
    }

    const setAccount = (account, data) => {
        const resolvedKey = Number(account);
        if(!accounts.has(resolvedKey)) {
            throw new Error('Account not found: ' + account);
        }

        accounts.set(resolvedKey, { ...accounts.get(resolvedKey), ...data });
        updateAccountDisplay();
    }


    const selectAccount = (account) => {
        const resolvedKey = Number(account);
        if(!accounts.has(resolvedKey)) {
            console.error('Failed selecting account. Account key not found: ', account);
            return false;
        }

        selectedAccount = resolvedKey;
        Store.accountStore().upload(resolvedKey);
        updateAccountDisplay();
        return true;
    };

    const init = async () => {
        await load();
    }

    return {
        get selectedAccount() { return selectedAccount; },
        selectAccount,
        init,
        load,
        list,
        getAccount,
        setAccount,
        refreshAccounts
    }

})();

export default Account;