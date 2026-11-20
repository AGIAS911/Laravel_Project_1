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

    const routeInfo = document.createElement('p');
    routeInfo.className = 'small fw-semibold mb-0 mt-2';
    routeInfo.setAttribute('role', 'status');
    document.getElementById('booking-map').after(routeInfo);

    let routeLine = null;
    let routeRequest = 0;

    function updateRoute() {
        if (routeLine) {
            routeLine.remove();
            routeLine = null;
        }
        routeInfo.textContent = '';

        if (!markers.pickup || !markers.destination) return;

        const from = markers.pickup.getLatLng();
        const to = markers.destination.getLatLng();

        const url = 'https://router.project-osrm.org/route/v1/driving/'
            + from.lng + ',' + from.lat + ';'
            + to.lng + ',' + to.lat
            + '?overview=full&geometries=geojson';

        routeInfo.textContent = 'Calculating route…';

        routeRequest = routeRequest + 1;
        const thisRequest = routeRequest;

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
            markers[stop] = L.circleMarker([lat, lng], { radius: 8, color: '#fff', weight: 2, fillColor: stop === 'pickup' ? '#c9343d' : '#123342', fillOpacity: 1 }).addTo(map).bindTooltip(title);
        };
        latitude.addEventListener('change', () => { refresh(); updateRoute(); });
        longitude.addEventListener('change', () => { refresh(); updateRoute(); });
        refresh();
    }

    const existing = Object.values(markers);
    if (existing.length) map.fitBounds(L.featureGroup(existing).getBounds(), { padding: [25, 25], maxZoom: 14 });

    updateRoute();

    function setLocation(stop, lat, lng) {
        const latitude = form.elements[`${stop}_latitude`];
        form.elements[`${stop}_longitude`].value = lng.toFixed(7);
        latitude.value = lat.toFixed(7);
        latitude.dispatchEvent(new Event('change'));
    }

    map.on('click', ({ latlng }) => {
        const target = form.elements.map_target.value;
        setLocation(target, latlng.lat, latlng.lng);

        if (target === 'pickup' && !form.elements.destination_latitude.value) {
            document.getElementById('map-destination').checked = true;
        }
    });

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