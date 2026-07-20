(function(){
    const scope = document.querySelector('.floatbtn_scope_floatbtn');
    if(!scope) return;


    const toggle = scope.querySelector('[data-floatbtn-toggle]');
    const fab = scope.querySelector('.fab_floatbtn');
    if(toggle && fab){
        const setExpanded = (val)=>{
            fab.setAttribute('aria-expanded', String(val));
            toggle.setAttribute('aria-expanded', String(val));
        };
        toggle.addEventListener('click', ()=>{
            const open = fab.getAttribute('aria-expanded') === 'true';
            setExpanded(!open);
        });
        // tutup saat klik di luar
        document.addEventListener('click', (e)=>{
            if(!fab.contains(e.target) && fab.getAttribute('aria-expanded') === 'true'){
                setExpanded(false);
            }
        });
        // tutup dengan ESC
        document.addEventListener('keydown', (e)=>{
        if(e.key === 'Escape') setExpanded(false);
        });
    }


    // Scroll to top action
    const scrollBtn = scope.querySelector('[data-floatbtn-scroll-top]');
    if(scrollBtn){
        scrollBtn.addEventListener('click', ()=>{
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
})();