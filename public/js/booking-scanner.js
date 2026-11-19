(() => {
    const modal = document.getElementById('furniture-scan');
    const form = document.getElementById('scan-form');
    const room = document.getElementById('scan-room');
    const qrPanel = document.getElementById('scan-qr-panel');
    const qr = document.getElementById('scan-qr');
    const countdown = document.getElementById('scan-countdown');
    const status = document.getElementById('scan-status');
    const close = document.getElementById('scan-close');
    const copy = document.getElementById('scan-copy');
    const submit = form.querySelector('button[type="submit"]');
    let request;
    let timer;
    let createUrl;
    const sessions = new Map();

    function stopWatching() {
        clearInterval(timer);
        request?.abort();
    }

    modal.addEventListener('show.bs.modal', (event) => {
        createUrl = event.relatedTarget.dataset.scanUrl;
        form.reset();
        submit.disabled = false;
        status.textContent = '';
        const existing = sessions.get(createUrl);
        close.hidden = !existing;
        if (existing) {
            request = new AbortController();
            submit.disabled = true;
            watchSession(existing, request.signal);
        }
    });
    modal.addEventListener('shown.bs.modal', () => room.focus());
    modal.addEventListener('hide.bs.modal', () => {
        stopWatching();
        qrPanel.hidden = true;
        qr.removeAttribute('src');
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        stopWatching();
        request = new AbortController();
        const { signal } = request;
        submit.disabled = true;
        status.textContent = 'Generating QR code…';

        try {
            const response = await fetch(createUrl, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
                signal,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.errors?.room_name?.[0] || data.message);
            if (signal.aborted) return;

            qr.src = data.qr_code;
            sessions.set(createUrl, data);
            close.hidden = false;
            qrPanel.hidden = false;
            status.textContent = 'Waiting for your phone…';
            watchSession(data, signal);
        } catch (error) {
            if (signal.aborted) return;
            status.textContent = error.message || 'Could not generate a QR code. Try again.';
            submit.disabled = false;
        }
    });

    copy.addEventListener('click', async () => {
        const session = sessions.get(createUrl);
        if (!session) return;
        try {
            await navigator.clipboard.writeText(session.scan_url);
            status.textContent = 'Phone link copied.';
        } catch {
            status.textContent = 'Copy is unavailable. Scan the QR code with your phone.';
        }
    });

    close.addEventListener('click', async () => {
        const url = createUrl;
        const session = sessions.get(url);
        if (!session) return;
        close.disabled = true;
        try {
            const response = await fetch(session.close_url, {
                method: 'DELETE',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': form.elements._token.value },
            });
            if (!response.ok) throw new Error();
            sessions.delete(url);
            if (url === createUrl) {
                stopWatching();
                close.hidden = qrPanel.hidden = true;
                qr.removeAttribute('src');
                submit.disabled = false;
                status.textContent = 'Scan session ended.';
            }
        } catch {
            if (url === createUrl) status.textContent = 'Could not end the scan session. Try again.';
        } finally {
            close.disabled = false;
        }
    });

    function watchSession(session, signal) {
        const expiresAt = Date.parse(session.expires_at);
        let checking = false;

        async function check() {
            const seconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
            countdown.textContent = `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`;
            if (!seconds) qrPanel.hidden = true;
            if (checking) return;
            checking = true;

            let state;
            try {
                const response = await fetch(session.status_url, {
                    headers: { Accept: 'application/json' },
                    signal,
                });
                if (!response.ok) throw new Error();
                state = (await response.json()).status;
                if (!signal.aborted && status.textContent !== 'Waiting for your phone…') {
                    status.textContent = 'Waiting for your phone…';
                }
            } catch {
                if (!signal.aborted) status.textContent = 'Connection check failed. Retrying…';
            } finally {
                checking = false;
            }

            if (signal.aborted) return;
            if (state === 'pending' && seconds) {
                qr.src = session.qr_code;
                qrPanel.hidden = false;
            }
            if (state === 'paired' || state === 'expired' || state === 'closed' || !seconds) {
                clearInterval(timer);
                qrPanel.hidden = true;
                qr.removeAttribute('src');
                status.textContent = state === 'paired'
                    ? 'Phone connected.'
                    : 'QR code expired. Generate a new one to reconnect.';
                if (state !== 'paired') {
                    sessions.delete(createUrl);
                    close.hidden = true;
                    submit.disabled = false;
                }
            }
        }

        timer = setInterval(check, 1000);
        check();
    }
})();
