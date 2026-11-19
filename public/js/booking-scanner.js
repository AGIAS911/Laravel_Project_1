(() => {
    const panel = document.querySelector('#furniture-scan[data-scan-url]');
    if (!panel) return;

    const form = panel.querySelector('#scan-form');
    const qrPanel = panel.querySelector('#scan-qr-panel');
    const qr = panel.querySelector('#scan-qr');
    const countdown = panel.querySelector('#scan-countdown');
    const status = panel.querySelector('#scan-status');
    const close = panel.querySelector('#scan-close');
    const renew = panel.querySelector('#scan-renew');
    const submit = form.querySelector('button[type="submit"]');
    const copy = panel.querySelector('#scan-copy');
    const csrf = form.elements._token.value;
    let session;
    let timer;
    let busy = false;

    function showState(message, connected = false) {
        if (status.textContent !== message) status.textContent = message;
        status.classList.toggle('is-connected', connected);
    }

    function setSession(value) {
        clearTimeout(timer);
        session = value;
        close.hidden = renew.hidden = !value;
        qrPanel.hidden = !value;
        submit.disabled = Boolean(value);
        if (value) qr.src = value.qr_code;
        else qr.removeAttribute('src');
    }

    async function watch(current) {
        if (session !== current) return;

        const seconds = Math.max(0, Math.ceil((Date.parse(current.expires_at) - Date.now()) / 1000));
        countdown.textContent = `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`;

        try {
            const response = await fetch(current.status_url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error();
            const { status: state } = await response.json();
            if (session !== current) return;

            if (state === 'paired') {
                qrPanel.hidden = true;
                showState('Phone connected', true);
            } else if (state === 'pending' && seconds > 0) {
                qrPanel.hidden = false;
                showState('Waiting for your phone…');
            } else {
                setSession(null);
                showState(state === 'closed' ? 'Scan session ended.' : 'Pairing expired. Generate a new code.');
                return;
            }
        } catch {
            if (session === current) showState('Connection check failed. Retrying…');
        }
        if (session === current) timer = setTimeout(() => watch(current), 2000);
    }

    async function endSession() {
        if (!session) return;
        const response = await fetch(session.close_url, {
            method: 'DELETE', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        if (!response.ok) throw new Error('Could not end the scan session. Try again.');
        setSession(null);
    }

    async function generate() {
        if (busy || !form.reportValidity()) return;
        busy = true;
        submit.disabled = close.disabled = renew.disabled = true;
        try {
            await endSession();
            submit.disabled = true;
            showState('Generating QR code…');
            const response = await fetch(panel.dataset.scanUrl, {
                method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.errors?.room_name?.[0] || data.message);
            setSession(data);
            showState('Waiting for your phone…');
            watch(data);
        } catch (error) {
            showState(error.message || 'Could not generate a QR code. Try again.');
        } finally {
            busy = close.disabled = renew.disabled = false;
            submit.disabled = Boolean(session);
        }
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        generate();
    });
    renew.addEventListener('click', generate);
    close.addEventListener('click', async () => {
        if (busy) return;
        busy = close.disabled = renew.disabled = true;
        try {
            await endSession();
            showState('Scan session ended.');
        } catch (error) {
            showState(error.message);
        } finally {
            busy = close.disabled = renew.disabled = false;
        }
    });
    copy.addEventListener('click', async () => {
        if (!session) return;
        try {
            await navigator.clipboard.writeText(session.scan_url);
            showState('Phone link copied.');
        } catch {
            showState('Copy is unavailable. Scan the QR code with your phone.');
        }
    });

    const preview = document.getElementById('scan-demo-video');
    const previewState = document.getElementById('scan-preview-state');
    preview.addEventListener('play', () => previewState.textContent = 'Preview playing');
    preview.addEventListener('pause', () => previewState.textContent = 'Preview paused');
    for (const button of document.querySelectorAll('[data-scan-preview]')) {
        button.addEventListener('click', async () => {
            if (button.dataset.scanPreview === 'play') {
                try { await preview.play(); } catch { previewState.textContent = 'Preview unavailable'; }
                return;
            }
            preview.pause();
            if (button.dataset.scanPreview === 'stop') preview.currentTime = 0;
        });
    }
})();
