(() => {
    const pageToast = document.querySelector('[data-workflow-status]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

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
        const button = event.submitter;
        const body = button ? new FormData(form, button) : new FormData(form);
        if (button) button.disabled = true;
        if (pageToast) pageToast.textContent = '';

        try {
            const data = await request(form.action, { method: 'POST', body });
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
                location.reload();
            }
        } catch (error) {
            if (pageToast) pageToast.textContent = error.message;
            if (form.id === 'submit-booking' || form.dataset.workflowForm === 'quote') {
                document.getElementById('submit-booking').hidden = true;
                const quote = document.querySelector('[data-booking-quote]');
                if (quote) quote.hidden = true;
            }
        } finally {
            if (button) button.disabled = false;
        }
    });


    const findVehicles = document.querySelector('[data-find-vehicles]');
    if (findVehicles) {
        const form = findVehicles.closest('form');
        const status = form.querySelector('[data-vehicle-status]');
        const select = form.elements.vehicle_id;
        const appointment = form.elements.scheduled_start_at;
        const choices = form.querySelector('[data-vehicle-options]');
        const more = form.querySelector('[data-more-vehicles]');
        let nextPage = null;
        let searchVersion = 0;

        function updateSelection() {
            for (const button of choices.children) {
                button.setAttribute('aria-pressed', String(button.dataset.vehicle === select.value));
            }
        }

        function addVehicle(vehicle) {
            const id = String(vehicle.id);
            const detail = [vehicle.capacity_m3 && vehicle.capacity_m3 + ' m³', vehicle.base_price + ' JOD base']
                .filter(Boolean).join(' · ');
            const existing = [...select.options].find(option => option.value === id);
            if (existing) {
                existing.textContent = vehicle.type + ' · ' + detail;
            } else {
                select.add(new Option(vehicle.type + ' · ' + detail, id));
            }

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'customer-vehicle-choice';
            button.dataset.vehicle = id;
            const title = document.createElement('strong');
            title.textContent = vehicle.type;
            const description = document.createElement('span');
            description.textContent = detail;
            button.append(title, description);
            button.addEventListener('click', () => {
                select.value = id;
                updateSelection();
            });
            choices.append(button);
        }

        function clearResults(keepSelection = false) {
            const selected = select.value ? select.selectedOptions[0] : null;
            select.replaceChildren(new Option('Choose later', ''));
            if (keepSelection && selected) {
                select.add(selected);
                selected.selected = true;
            }
            choices.replaceChildren();
            nextPage = null;
            more.hidden = true;
        }

        async function loadVehicles(pageNumber) {
            if (!appointment.value) {
                status.textContent = 'Choose a booking time first.';
                return;
            }

            const version = ++searchVersion;
            findVehicles.disabled = true;
            more.disabled = true;
            status.textContent = 'Checking availability…';
            const url = new URL(findVehicles.dataset.findVehicles, location.origin);
            url.search = new URLSearchParams({ start: appointment.value, page: pageNumber });

            try {
                const page = await request(url);
                if (version !== searchVersion) return;
                if (pageNumber === 1) clearResults(true);
                page.data.forEach(addVehicle);
                updateSelection();
                nextPage = page.current_page < page.last_page ? page.current_page + 1 : null;
                more.hidden = nextPage === null;
                status.textContent = page.total
                    ? 'Showing ' + choices.children.length + ' of ' + page.total + ' available vehicles.'
                    : 'No vehicles available for this time.';
            } catch (error) {
                if (version === searchVersion) status.textContent = error.message;
            } finally {
                if (version === searchVersion) {
                    findVehicles.disabled = false;
                    more.disabled = false;
                }
            }
        }

        select.addEventListener('change', updateSelection);
        findVehicles.addEventListener('click', () => loadVehicles(1));
        more.addEventListener('click', () => loadVehicles(nextPage));
        appointment.addEventListener('input', () => {
            searchVersion++;
            clearResults();
            findVehicles.disabled = false;
            more.disabled = false;
            status.textContent = 'Check availability again for the new time.';
        });
    }

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
