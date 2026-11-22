(() => {
    const pageToast = document.querySelector('[data-workflow-status]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const quoteForm = document.querySelector('[data-workflow-form="quote"]');
    const checkout = document.getElementById('submit-booking');
    const quantityForms = [...document.querySelectorAll('[data-auto-quantity]')];
    const serviceFeedback = document.querySelector('.product-service-panel [data-workflow-status]');
    let quantityTimer;
    let savingQuantities = false;
    let quoteNeedsRefresh = true;

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
        if (!response.ok) {
            throw Object.assign(new Error(data.message || 'The request failed.'), { status: response.status });
        }
        return data;
    }

    function unsavedQuantities() {
        return quantityForms.filter(({ elements: { quantity } }) => quantity.value !== quantity.defaultValue);
    }

    function refreshQuote() {
        if (!quoteForm || !quoteNeedsRefresh || savingQuantities || unsavedQuantities().length
            || quoteForm.hasAttribute('aria-busy')) return;
        quoteNeedsRefresh = false;
        quoteForm.requestSubmit();
    }

    function queueQuantitySave() {
        quoteNeedsRefresh = true;
        if (checkout) checkout.hidden = true;
        clearTimeout(quantityTimer);
        quantityTimer = setTimeout(saveQuantities, 350);
    }

    async function saveQuantities() {
        if (savingQuantities) return;
        savingQuantities = true;
        let saveError = '';
        for (const form of unsavedQuantities()) {
            const input = form.elements.quantity;
            if (!input.validity.valid) continue;
            const sent = input.value;
            if (serviceFeedback) serviceFeedback.textContent = 'Saving quantities…';
            try {
                await request(form.action, { method: 'POST', body: new FormData(form) });
                input.defaultValue = sent;
            } catch (error) {
                saveError = error.message;
                if (input.value === sent) {
                    input.value = input.defaultValue;
                    syncQuantityButtons(input);
                }
            }
        }
        savingQuantities = false;
        const unsaved = unsavedQuantities();
        if (serviceFeedback) {
            serviceFeedback.textContent = saveError || (unsaved.length ? 'Enter a quantity from 1 to 1,000.' : '');
        }
        if (unsaved.some(form => form.elements.quantity.validity.valid)) queueQuantitySave();
        else refreshQuote();
    }

    function syncQuantityButtons(input) {
        const stepper = input.closest('[data-quantity-stepper]');
        stepper.querySelector('[data-quantity-delta="-1"]').setAttribute('aria-disabled', input.valueAsNumber <= Number(input.min));
        stepper.querySelector('[data-quantity-delta="1"]').setAttribute('aria-disabled', input.valueAsNumber >= Number(input.max));
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-quantity-delta]');
        if (!button || button.getAttribute('aria-disabled') === 'true') return;
        const input = button.closest('[data-quantity-stepper]').querySelector('input');
        const min = Number(input.min);
        const max = Number(input.max);
        const value = Number.isFinite(input.valueAsNumber) ? Math.trunc(input.valueAsNumber) + Number(button.dataset.quantityDelta) : min;
        input.value = Math.max(min, Math.min(max, value));
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });

    document.addEventListener('input', (event) => {
        const input = event.target;
        if (!input.matches('[data-quantity-stepper] input')) return;
        syncQuantityButtons(input);
        if (input.form?.matches('[data-auto-quantity]')) queueQuantitySave();
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-workflow-form]');
        if (!form) return;
        event.preventDefault();
        if (form.matches('[data-auto-quantity]')) {
            queueQuantitySave();
            return;
        }
        if (quantityForms.length && (savingQuantities || unsavedQuantities().length)) {
            if (serviceFeedback) serviceFeedback.textContent = 'Finish updating your quantities first.';
            return;
        }
        const scope = form.closest('[data-workflow-scope]');
        const feedback = scope?.querySelector('[data-workflow-status]') || pageToast;
        const button = event.submitter;
        const body = button ? new FormData(form, button) : new FormData(form);
        if (form.getAttribute('aria-busy') === 'true') return;
        form.setAttribute('aria-busy', 'true');
        if (button) button.disabled = true;
        if (feedback) feedback.textContent = form === quoteForm ? 'Calculating your quote…' : '';

        try {
            const data = await request(form.action, { method: 'POST', body });
            if (data.payment_url) {
                location.assign(data.payment_url);
                return;
            }
            if (form === quoteForm) {
                if (quoteNeedsRefresh) return;
                if (feedback) feedback.textContent = '';
                const submit = document.getElementById('submit-booking');
                if (submit) {
                    submit.elements.expected_total.value = data.total_price;
                    submit.hidden = false;
                }
                const deposit = data.deposit_amount === null ? 'Not set yet' : data.deposit_amount + ' JOD';
                const quote = document.querySelector('[data-booking-quote]');
                if (quote) {
                    quote.querySelector('[data-quote-distance]').textContent = data.distance_km + ' km';
                    quote.querySelector('[data-quote-total]').textContent = data.total_price + ' JOD';
                    quote.querySelector('[data-quote-deposit]').textContent = deposit;
                    quote.hidden = false;
                }
                const summary = document.querySelector('[data-booking-summary]');
                if (summary) {
                    summary.querySelector('[data-summary-distance]').textContent = data.distance_km + ' km';
                    summary.querySelector('[data-summary-total]').textContent = data.total_price + ' JOD';
                    summary.querySelector('[data-summary-deposit]').textContent = deposit;
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
                if (scope && form !== quoteForm) feedback.scrollIntoView({ block: 'nearest' });
            }
            if (form.id === 'submit-booking' || form.dataset.workflowForm === 'quote') {
                const submit = document.getElementById('submit-booking');
                if (submit) submit.hidden = true;
                const quote = document.querySelector('[data-booking-quote]');
                if (quote) quote.hidden = true;
                if (form.id === 'submit-booking' && error.status === 409) quoteNeedsRefresh = true;
            }
        } finally {
            form.removeAttribute('aria-busy');
            if (button) button.disabled = false;
            refreshQuote();
        }
    });


    refreshQuote();

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
