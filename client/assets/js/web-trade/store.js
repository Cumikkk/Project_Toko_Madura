const Store = (() => {

    const symbolStore = () => {
        let storageName = 'symbols';
        
        const upload = (data) => {
            localStorage.setItem(storageName, JSON.stringify(Array.from(data.entries())));
        }

        const get = () => {
            if(!localStorage.getItem(storageName)) return null;
            return new Map(JSON.parse(localStorage.getItem(storageName)));
        }

        const reset = () => {
            localStorage.removeItem(storageName);
        }
       
        return {
            upload,
            reset,
            get
        }
    } 

    const accountStore = () => {
        let storageName = 'selectedAccount';
        
        const upload = (account) => {
            localStorage.setItem(storageName, account);
        }

        const get = () => {
            if(!localStorage.getItem(storageName)) return null;
            return Number(localStorage.getItem(storageName));
        }

        const reset = () => {
            localStorage.removeItem(storageName);
        }

        return {
            upload,
            reset,
            get
        }
    }

    const socketStore = () => {
        let storageName = 'socketToken';

        const upload = (token) => {
            localStorage.setItem(storageName, token);
        }

        const get = () => {
            if(!localStorage.getItem(storageName)) {
                return null;
            }
            
            let token = localStorage.getItem(storageName);
            let data = token.split('.');
            if(data.length !== 3) {
                localStorage.removeItem(storageName);
                return null;
            }

            let expired = JSON.parse(atob(data[1]));
            if(Number(expired.exp) < Date.now() / 1000) {
                console.log('Socket token expired. Removing from storage.');
                localStorage.removeItem(storageName);
                return null;
            }

            return token;
        }

        const reset = () => {
            localStorage.removeItem(storageName);
        }

        return {
            upload,
            reset,
            get
        }
    }

    return {
        symbolStore,
        accountStore,
        socketStore
    }
    
})();

export default Store;