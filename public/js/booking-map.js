(() => {
    const panel = document.getElementById('booking-map-panel');
    if (!panel || !window.L) return;
    panel.hidden = false;
    const form = panel.closest('form');
    const map = L.map('booking-map').setView([31.9539, 35.9106], 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    const markers = {};
    for (const stop of ['pickup', 'destination']) {
        const latitude = form.elements[`${stop}_latitude`];
        const longitude = form.elements[`${stop}_longitude`];
        const title = stop === 'pickup' ? 'Pickup' : 'Destination';
        const refresh = () => {
            markers[stop]?.remove();
            if (!latitude.value || !longitude.value) return;
            const lat = Number(latitude.value);
            const lng = Number(longitude.value);
            if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;
            markers[stop] = L.marker([lat, lng], { title, alt: title }).addTo(map).bindTooltip(title);
        };
        latitude.addEventListener('change', refresh);
        longitude.addEventListener('change', refresh);
        refresh();
    }
    const existing = Object.values(markers);
    if (existing.length) map.fitBounds(L.featureGroup(existing).getBounds(), { padding: [25, 25], maxZoom: 14 });
    map.on('click', ({ latlng }) => {
        const stop = form.elements.map_target.value;
        const latitude = form.elements[`${stop}_latitude`];
        form.elements[`${stop}_longitude`].value = latlng.lng.toFixed(7);
        latitude.value = latlng.lat.toFixed(7);
        latitude.dispatchEvent(new Event('change'));
    });
})();
