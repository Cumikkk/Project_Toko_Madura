const ApiStore = (() => {

    const baseUrl = '/ajax/post';

    const fetchAccounts = async () => {
        const url = `${baseUrl}/web-trade/accounts`;
        let response = await $.ajax({
            url: url,
            method: 'GET',
        });

        if(!response.success) {
            console.error(response.message || 'Failed to fetch accounts');
            // throw new Error();
        }

        return response.data;
    }

    const fetchSymbols = async (account) => {
        const url = `${baseUrl}/web-trade/symbols?account=${account}`;
        let response = await $.ajax({
            url: url,
            method: 'GET',
        });

        if(!response.success) {
            console.error(response.message || 'Failed to fetch symbols');
            // throw new Error(response.message || 'Failed to fetch symbols');
        }

        return response;
    }

    const createSocketToken = async (account, server) => {
        const url = `${baseUrl}/web-trade/create-token`;
        let response = await $.ajax({
            url: url,
            method: 'POST',
            data: {
                account: account,
                server: server
            }
        });

        if(!response.success) {
            console.error(response.message || 'Failed to create socket token');
            // throw new Error(response.message || 'Failed to create socket token');
        }

        return response.token;
    }

    const updateAccountPassword = async (account, newPassword) => {
        const url = `${baseUrl}/account/update-password`;
        let response = await $.ajax({
            url: url,
            method: 'POST',
            data: { 
                login: account, 
                password: newPassword,
                change_password: true
            }
        });

        if(!response.success) {
            console.error(response.message || 'Failed to update account password');
            // throw new Error(response.message || 'Failed to update account password');
        }
     
        return response;
    }

    return {
        fetchAccounts,
        fetchSymbols,
        createSocketToken,
        updateAccountPassword
    }

})();

export default ApiStore;