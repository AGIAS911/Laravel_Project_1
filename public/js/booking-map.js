(() => {
    const panel = document.getElementById('booking-map-panel');
    if (!panel || !window.L) return;
    const form = panel.closest('form');
    const mapCard = panel.closest('.move-location-map-card');
    const hint = mapCard.querySelector('[data-map-hint]');
    const targets = panel.querySelectorAll('input[name="map_target"]');
    panel.hidden = false;
    mapCard.querySelector('[data-map-unavailable]').hidden = true;
    hint.hidden = false;
    const map = L.map('booking-map', { scrollWheelZoom: false }).setView([31.9539, 35.9106], 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    const markers = {};

    const routeInfo = panel.querySelector('[data-route-info]');

    function updatePinStatus() {
        const target = form.elements.map_target.value;
        const title = target === 'pickup' ? 'pickup' : 'destination';
        hint.textContent = markers[target]
            ? 'Tap the map or use its center to move the ' + title + ' pin.'
            : 'Tap the map or use its center to place the ' + title + ' pin.';

        for (const status of form.querySelectorAll('[data-pin-status]')) {
            const placed = Boolean(markers[status.dataset.pinStatus]);
            status.textContent = placed ? 'Pin placed' : 'Pin needed';
            status.classList.toggle('is-placed', placed);
        }
    }

    for (const target of targets) target.addEventListener('change', updatePinStatus);

    let routeLine = null;
    let routeRequest = 0;

    function updateRoute() {
        if (routeLine) {
            routeLine.remove();
            routeLine = null;
        }
        const thisRequest = ++routeRequest;
        routeInfo.hidden = !markers.pickup || !markers.destination;

        if (routeInfo.hidden) {
            routeInfo.textContent = '';
            return;
        }

        const from = markers.pickup.getLatLng();
        const to = markers.destination.getLatLng();

        const url = 'https://router.project-osrm.org/route/v1/driving/'
            + from.lng + ',' + from.lat + ';'
            + to.lng + ',' + to.lat
            + '?overview=full&geometries=geojson';

        routeInfo.textContent = 'Calculating route…';

        fetch(url)
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (thisRequest !== routeRequest) return;

                if (data.code !== 'Ok') {
                    routeInfo.textContent = 'No road route found between these points.';
                    return;
                }

                const route = data.routes[0];

                routeLine = L.geoJSON(route.geometry, {
                    style: { color: '#123342', weight: 5 },
                }).addTo(map);
                map.fitBounds(routeLine.getBounds(), { padding: [25, 25] });

                const km = (route.distance / 1000).toFixed(1);
                const minutes = Math.round(route.duration / 60);
                routeInfo.textContent = 'Road distance: ' + km + ' km - About ' + minutes + ' min drive';
            })
            .catch(function () {
                if (thisRequest !== routeRequest) return;
                routeInfo.textContent = 'Could not load the route. Check your internet connection.';
            });
    }

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
            const icon = L.divIcon({
                className: stop === 'pickup' ? 'move-location-pin is-pickup' : 'move-location-pin',
                html: stop === 'pickup' ? 'A' : 'B',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });
            markers[stop] = L.marker([lat, lng], { icon, title, alt: title }).addTo(map).bindTooltip(title, { direction: 'top' });
        };
        const updateStop = () => {
            refresh();
            updatePinStatus();
            updateRoute();
        };
        latitude.addEventListener('change', updateStop);
        longitude.addEventListener('change', updateStop);
        refresh();
    }

    const existing = Object.values(markers);
    if (existing.length) map.fitBounds(L.featureGroup(existing).getBounds(), { padding: [25, 25], maxZoom: 14 });

    if (markers.pickup && !markers.destination) form.elements.map_target.value = 'destination';
    updatePinStatus();
    updateRoute();

    function setLocation(stop, lat, lng) {
        const latitude = form.elements[`${stop}_latitude`];
        form.elements[`${stop}_longitude`].value = lng.toFixed(7);
        latitude.value = lat.toFixed(7);
        if (stop === 'pickup' && !markers.destination) form.elements.map_target.value = 'destination';
        latitude.dispatchEvent(new Event('change'));
    }

    function placePin({ lat, lng }) {
        setLocation(form.elements.map_target.value, lat, lng);
    }

    map.on('click', ({ latlng }) => placePin(latlng));
    panel.querySelector('[data-pin-center]').addEventListener('click', () => placePin(map.getCenter()));

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
                locationStatus.textContent = 'Location unavailable. Choose a pickup point on the map.';
                locationButton.disabled = false;
            }, { timeout: 10000 });
        });
    }
})();
