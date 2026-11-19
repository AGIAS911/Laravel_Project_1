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
            delete markers[stop];
            if (!latitude.value || !longitude.value) return;
            const lat = Number(latitude.value);
            const lng = Number(longitude.value);
            if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;
            markers[stop] = L.circleMarker([lat, lng], { radius: 8, color: '#fff', weight: 2, fillColor: stop === 'pickup' ? '#c9343d' : '#123342', fillOpacity: 1 }).addTo(map).bindTooltip(title);
        };
        latitude.addEventListener('change', refresh);
        longitude.addEventListener('change', refresh);
        refresh();
    }
    const existing = Object.values(markers);
    if (existing.length) map.fitBounds(L.featureGroup(existing).getBounds(), { padding: [25, 25], maxZoom: 14 });
    function setLocation(stop, lat, lng) {
        const latitude = form.elements[`${stop}_latitude`];
        form.elements[`${stop}_longitude`].value = lng.toFixed(7);
        latitude.value = lat.toFixed(7);
        latitude.dispatchEvent(new Event('change'));
    }

    map.on('click', ({ latlng }) => setLocation(form.elements.map_target.value, latlng.lat, latlng.lng));

    const locationButton = panel.querySelector('[data-current-location]');
    const locationStatus = panel.querySelector('[data-location-status]');
    if (locationButton && locationStatus && navigator.geolocation && window.isSecureContext) {
        locationButton.hidden = false;
        locationButton.addEventListener('click', () => {
            locationButton.disabled = true;
            locationStatus.textContent = 'Finding your location…';
            navigator.geolocation.getCurrentPosition(({ coords }) => {
                setLocation('pickup', coords.latitude, coords.longitude);
                map.setView([coords.latitude, coords.longitude], 15);
                locationStatus.textContent = 'Pickup set to your current location.';
                locationButton.disabled = false;
            }, () => {
                locationStatus.textContent = 'Location unavailable. Choose a pickup point on the map or enter coordinates.';
                locationButton.disabled = false;
            }, { timeout: 10000 });
        });
    }
})();
