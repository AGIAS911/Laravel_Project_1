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
                pageToast.textContent = `Distance ${data.distance_km} km · Total ${data.total_price} JOD · Deposit ${data.deposit_amount} JOD`;
            } else {
                location.reload();
            }
        } catch (error) {
            if (pageToast) pageToast.textContent = error.message;
            if (form.id === 'submit-booking') form.hidden = true;
        } finally {
            if (button) button.disabled = false;
        }
    });

    const findVehicles = document.querySelector('[data-find-vehicles]');
    findVehicles?.addEventListener('click', async () => {
        const form = findVehicles.closest('form');
        const status = form.querySelector('[data-vehicle-status]');
        const select = form.elements.vehicle_id;
        const start = form.elements.scheduled_start_at.value;
        const end = form.elements.scheduled_end_at.value;
        if (!start || !end) {
            status.textContent = 'Choose the start and end time first.';
            return;
        }
        findVehicles.disabled = true;
        const url = new URL(findVehicles.dataset.findVehicles, location.origin);
        url.search = new URLSearchParams({ start, end });
        try {
            const page = await request(url);
            const selected = select.value;
            select.replaceChildren(new Option('Choose later', ''));
            for (const vehicle of page.data) {
                select.add(new Option(`${vehicle.type} · ${vehicle.capacity_m3} · ${vehicle.base_price} JOD base`, vehicle.id));
            }
            if ([...select.options].some(option => option.value === selected)) select.value = selected;
            const choices = form.querySelector('[data-vehicle-options]');
            if (choices) {
                const buttons = page.data.map(vehicle => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'customer-vehicle-choice';

                    const title = document.createElement('strong');
                    title.textContent = vehicle.type;

                    const detail = document.createElement('span');
                    detail.textContent = [vehicle.capacity_m3 && vehicle.capacity_m3 + ' m³', vehicle.base_price + ' JOD base'].filter(Boolean).join(' · ');

                    button.append(title, detail);
                    button.dataset.vehicle = String(vehicle.id);
                    button.addEventListener('click', () => {
                        select.value = button.dataset.vehicle;
                        select.dispatchEvent(new Event('change'));
                    });
                    return button;
                });
                const updateSelection = () => {
                    for (const button of choices.children) {
                        button.setAttribute('aria-pressed', String(button.dataset.vehicle === select.value));
                    }
                };
                choices.replaceChildren(...buttons);
                select.onchange = updateSelection;
                updateSelection();
            }
            status.textContent = page.data.length ? `${page.total} available vehicles.` : 'No vehicles available for this time.';
        } catch (error) {
            status.textContent = error.message;
        } finally {
            findVehicles.disabled = false;
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
            const response = await fetch(location.href, { cache: 'no-store' });
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
