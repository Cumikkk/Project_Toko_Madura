const Overlay = () => {
    const overlay = document.getElementById('overlay');

    const show = (message = 'Loading...') => {
        if(overlay) {
            overlay.querySelector('.overlay-message').textContent = message;
            overlay.classList.add('active');
        }
    };

    const hide = () => {
        if(overlay) {
            overlay.classList.remove('active');
        }
    };

    const showWithTimeout = (message, timeout) => {
        if(timeout <= 0 || isNaN(timeout)) {
            console.error('Invalid timeout value: ', timeout);
            return;
        }

        show(message);
        setTimeout(() => hide(), timeout);
    }

    return {
        show,
        hide,
        showWithTimeout
    }
}

export default Overlay();