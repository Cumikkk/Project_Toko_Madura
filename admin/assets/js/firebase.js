import { initializeApp } from "https://www.gstatic.com/firebasejs/10.13.1/firebase-app.js";
import { getDatabase, ref, get, query, orderByChild, startAt, onChildAdded } from "https://www.gstatic.com/firebasejs/10.13.1/firebase-database.js";

const firebaseConfig = {
    apiKey: "AIzaSyDAW1eAXP0pyDWo0pOdkXALXIImKYsoN3k",
    authDomain: "first-state-ca146.firebaseapp.com",
    databaseURL: "https://first-state-ca146-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "first-state-ca146",
    messagingSenderId: "94938698556",
    appId: "1:94938698556:web:158132c04be83dfa020439",
};

const app = initializeApp(firebaseConfig);
const db  = getDatabase(app);

let audioCtx = null;
let audioReady = false;
let pendingBeeps = 0;

function createCtx() {
if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    return audioCtx;
}

async function ensureAudioReady() {
    createCtx();
    try {
        if (audioCtx.state !== 'running') await audioCtx.resume();
        audioReady = (audioCtx.state === 'running');
    } catch (_) {
        audioReady = false;
    }
}

function playBeep(freq=880, dur=0.30, vol=100) {
    if (!audioReady || !audioCtx) return;
    const o = audioCtx.createOscillator();
    const g = audioCtx.createGain();
    o.type = 'sine';
    o.frequency.value = freq;
    o.connect(g); g.connect(audioCtx.destination);
    g.gain.setValueAtTime(vol, audioCtx.currentTime);
    o.start();
    o.stop(audioCtx.currentTime + dur);
}

async function beep() {
    await ensureAudioReady();
    if (audioReady) {
        while (pendingBeeps-- > 0) playBeep();
        pendingBeeps = 0;
        playBeep();
    } else {
        pendingBeeps++;
    }
}

['pointerdown','click','keydown','touchstart','visibilitychange'].forEach(evt => {
    window.addEventListener(evt, async () => {
        const wasBlocked = !audioReady;
        await ensureAudioReady();
        if (wasBlocked && audioReady && pendingBeeps > 0) {
            while (pendingBeeps-- > 0) playBeep();
            pendingBeeps = 0;
        }
    }, { passive: true });
});

(async () => {
    try {
        let serverOffset = 0;
        try {
            const offSnap = await get(ref(db, '.info/serverTimeOffset'));
            if (offSnap.exists()) serverOffset = offSnap.val() || 0;
        } catch (_) { /* abaikan, pakai 0 */ }

        const nowServer = Date.now() + serverOffset;

        const lastSeen = Number(sessionStorage.getItem('ops_lastSeenTs')) || 0;

        const since = Math.max(lastSeen + 1, nowServer + 1);

        const allowedTypes = new Set(['deposit', 'withdrawal', 'internal_transfer', 'del_acc', 'bank', 'progress_real_account']);
        
        const titleMap = {
            deposit: {
                title: 'New Deposit',
                color: '#198754',
                path: '/transaction/deposit/view',
                tables: [
                    'table-accounting'
                ]
            },
            withdrawal: {
                title: 'New Withdrawal',
                color: '#ffc107',
                path: '/transaction/withdrawal/view',
                tables: [
                    'table-authorization'
                ]
            },
            internal_transfer: {
                title: 'New Internal Transfer',
                color: '#0dcaf0',
                path: '/transaction/internal_transfer/view',
                tables: [
                    'table_history'
                ]
            },
            del_acc: {
                title: 'New Delete Account',
                color: '#dc3545',
                path: '/member/delete_user/view',
                tables: [
                    'table'
                ]
            },
            bank: {
                title: 'New Bank Entry',
                color: '#0d6efd',
                path: '/member/member_bank/view',
                tables: [
                    'table-pending'
                ]
            },
            progress_real_account: {
                title: 'Progress Real Account',
                color: '#5a0268',
                path: '/account/progress_real_account/view',
                tables: [
                    'table-progress-real-account'
                ]
            }
        };

        const q = query(
        ref(db, 'ops/events'),
            orderByChild('createdAt'),
            startAt(since)
        );

        onChildAdded(q, (snap) => {
            const ev = snap.val() || {};
            if (!allowedTypes.has(ev.type)) return;

            beep();

            const ts = Number(ev.createdAt) || (Date.now() + serverOffset);
            sessionStorage.setItem('ops_lastSeenTs', String(ts));

            /** toas notification */
            if(ev.type in titleMap) {
                const textInfo = ev.comment || "Ada aktivitas baru pada sistem. Silakan periksa segera.";
                const titleInfo = titleMap[ev.type].title || "-";
                const color = titleMap[ev.type].color || "#000000";
                const toastHTML = `
                    <div class="toast fade" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header text-default">
                            <svg class="bd-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="${color}"></rect></svg>
                            <strong class="me-auto">${titleInfo}</strong>
                            <small class="text-muted">${new Date(ev.createdAt).toLocaleTimeString()}</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close">✕</button>
                        </div>
                        <div class="toast-body">
                            ${textInfo}
                            <hr>
                            <a target="_blank" href="${titleMap[ev.type].path || '#'}" class="btn btn-sm btn-primary">Lihat</a>
                        </div>
                    </div>
                `;

                /** reload required table */
                let reloadedTable = titleMap[ev.type].tables;
                if(reloadedTable) {
                    reloadedTable.forEach((id) => {
                        let table = $(`#${id}`).dataTable().api();
                        if(table) {
                            table?.draw();
                        }
                    })
                }

                /** show toast with delay 5 seconds */
                const toastContainer = document.getElementById('toast-container');
                if (toastContainer) {
                    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                    const toastElement = toastContainer.lastElementChild;
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: true,
                        delay: 5000,
                        animation: true,
                    });
                    
                    toast.show();
                }
            }
        });
    } catch (e) {
        console.error('Subscribe error:', e);
    }
})();