(() => {
    const pageToast = document.querySelector('[data-workflow-status]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (location.hash === '#inventory') {
        window.addEventListener('load', () => {
            requestAnimationFrame(() => {
                if (location.hash === '#inventory') {
                    document.getElementById('inventory')?.scrollIntoView({ block: 'start' });
                }
            });
        }, { once: true });
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        if (response.status === 204 || response.redirected && response.ok) return {};
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'The request failed.');
        return data;
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-workflow-form]');
        if (!form) return;
        event.preventDefault();
        if (form.dataset.workflowConfirm && !window.confirm(form.dataset.workflowConfirm)) return;
        const scope = form.closest('[data-workflow-scope]');
        const feedback = scope?.querySelector('[data-workflow-status]') || pageToast;
        const button = event.submitter;
        const body = button ? new FormData(form, button) : new FormData(form);
        if (button) button.disabled = true;
        if (feedback) feedback.textContent = '';

        try {
            const data = await request(form.action, { method: 'POST', body });
            if (data.payment_url) {
                location.assign(data.payment_url);
                return;
            }
            if (form.dataset.workflowForm === 'quote') {
                const submit = document.getElementById('submit-booking');
                submit.elements.expected_total.value = data.total_price;
                submit.hidden = false;
                const quote = document.querySelector('[data-booking-quote]');
                if (quote) {
                    quote.querySelector('[data-quote-distance]').textContent = data.distance_km + ' km';
                    quote.querySelector('[data-quote-total]').textContent = data.total_price + ' JOD';
                    quote.querySelector('[data-quote-deposit]').textContent = data.deposit_amount + ' JOD';
                    quote.hidden = false;
                }
                const summary = document.querySelector('[data-booking-summary]');
                if (summary) {
                    summary.querySelector('[data-summary-distance]').textContent = data.distance_km + ' km';
                    summary.querySelector('[data-summary-total]').textContent = data.total_price + ' JOD';
                    summary.querySelector('[data-summary-deposit]').textContent = data.deposit_amount + ' JOD';
                }
            } else {
                if (scope?.dataset.workflowReturn) {
                    history.replaceState(history.state, '', '#' + scope.dataset.workflowReturn);
                }
                location.reload();
            }
        } catch (error) {
            if (feedback) {
                feedback.textContent = error.message;
                if (scope) feedback.scrollIntoView({ block: 'nearest' });
            }
            if (form.id === 'submit-booking' || form.dataset.workflowForm === 'quote') {
                document.getElementById('submit-booking').hidden = true;
                const quote = document.querySelector('[data-booking-quote]');
                if (quote) quote.hidden = true;
            }
        } finally {
            if (button) button.disabled = false;
        }
    });


    const inventory = document.querySelector('[data-live-inventory]');
    if (!inventory) return;
    let refreshing = false;
    setInterval(async () => {
        if (refreshing || document.hidden || inventory.contains(document.activeElement)
            || inventory.querySelector('button:disabled')) return;
        refreshing = true;
        try {
            const response = await fetch(inventory.dataset.liveInventory, { cache: 'no-store' });
            if (!response.ok || response.redirected) return;
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updated = page.querySelector('[data-live-inventory]');
            if (updated && updated.innerHTML !== inventory.innerHTML && !inventory.contains(document.activeElement)
                && !inventory.querySelector('button:disabled')) {
                inventory.replaceChildren(...updated.childNodes);
            }
        } catch {
            // Retry on the next tick.
        } finally {
            refreshing = false;
        }
    }, 4000);
})();
