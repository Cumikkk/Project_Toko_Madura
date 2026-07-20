import { load } from './helper.js';

const [ApiStore, Store] = await Promise.all([
    load('api'),
    load('store')
]);

const Socket = (() => {
    
    const socketUrl = 'wss://socket-rrfx.techcrm.dev';
    let token = null;
    let accountInfo = null;

    const getToken = () => {
        return token;
    }

    const createToken = async () => {
        if(!accountInfo) {
            console.error('No selected account found.');
            return;
        }
     
        token = await ApiStore.createSocketToken(accountInfo.login, accountInfo.type?.toLowerCase());
        return token;
    }

    const tickSocket = () => {
        if(!accountInfo) {
            console.error('No selected account found.');
            return;
        }

        const params = new URLSearchParams({
            token: token,
            server: accountInfo.type?.toLowerCase(),
        });

        const socket = new WebSocket(`${socketUrl}/ws/tick?${params.toString()}`);
        socket.onopen = function() {
            console.log('Tick socket connected');
        };

        socket.onclose = function() {
            console.log('Tick socket disconnected');
        }

        return socket;
    }

    const accountSocket = () => {
        if(!accountInfo) {
            throw new Error('AccountSocket: No selected account found. ');
        }

        const params = new URLSearchParams({
            token: token,
            server: accountInfo.type?.toLowerCase(),
            login: accountInfo.login
        })

        const socket = new WebSocket(`${socketUrl}/ws/account?${params.toString()}`);
        socket.onopen = function() {
            console.log('Account socket connected');
        };

        socket.onclose = function() {
            console.log('Account socket disconnected');
        }

        return socket;
    }

    const init = async (account) => {
        accountInfo = account;
        token = Store.socketStore().get();
        if(!token) {
            token = await createToken();
            await Store.socketStore().upload(token);
        }
    }

    return {
        init,
        getToken,
        tickSocket,
        accountSocket
    }

})();

export default Socket;