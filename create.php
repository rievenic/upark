<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U-PARK - Reserve Slot</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --unor-blue: #002B49;
            --unor-blue-light: #004B7A;
            --unor-gold: #FDB813;
            --unor-gold-dark: #D49B0E;
        }

        body {
            background-color: #F4F6F9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .unor-navbar {
            background-color: var(--unor-blue);
            border-bottom: 4px solid var(--unor-gold);
        }

        .navbar-brand {
            font-weight: 700;
            color: #FFFFFF !important;
        }

        .navbar-brand span {
            color: var(--unor-gold);
        }

        .form-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-top: 4px solid var(--unor-gold);
        }

        .btn-unor-primary {
            background-color: var(--unor-blue);
            color: #FFFFFF;
            font-weight: 600;
            border: none;
        }

        .btn-unor-primary:hover {
            background-color: var(--unor-blue-light);
            color: var(--unor-gold);
        }

        .form-label {
            font-weight: 600;
            color: var(--unor-blue);
        }

        #route-map { width: 100%; height: 340px; border-radius: 8px; background: #e9eef2; }
        #directions-panel { max-height: 220px; overflow-y: auto; }
        .direction-step { padding: .65rem 0; border-bottom: 1px solid #e9ecef; }
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <nav class="navbar navbar-dark unor-navbar mb-4 shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="fa-solid fa-square-p fa-xl text-warning"></i>
                <span>U-PARK</span> | UNO-R Event Parking System
            </a>
        </div>
    </nav>

    <!-- Form Container -->
    <div class="container mb-5" style="max-width: 600px;">
        <div class="card form-card bg-white p-4">
            
            <div class="mb-4 text-center">
                <h3 class="fw-bold" style="color: var(--unor-blue);">Book Event Parking</h3>
                <p class="text-muted small">Reserve a slot for an upcoming UNO-R campus activity</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=create">
                
                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-user me-1 text-secondary"></i> Driver Full Name</label>
                    <input type="text" name="driver_name" class="form-control py-2" required placeholder="e.g. Juan Dela Cruz">
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fa-solid fa-car me-1 text-secondary"></i> Vehicle Plate Number</label>
                    <input type="text" name="plate_number" class="form-control py-2" required placeholder="e.g. ABC 1234">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="event-select"><i class="fa-solid fa-calendar-event me-1 text-secondary"></i> Target Event</label>
                    <select id="event-select" name="event_id" class="form-select py-2" required>
                        <option value="">-- Choose Campus Event --</option>
                        <?php foreach ($events as $e): ?>
                            <option value="<?= htmlspecialchars($e['id'], ENT_QUOTES, 'UTF-8') ?>" data-location="<?= htmlspecialchars($e['location'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($e['event_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <section class="mb-4" aria-labelledby="route-heading">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <label id="route-heading" class="form-label mb-0"><i class="fa-solid fa-route me-1 text-secondary"></i> Route to event</label>
                        <button id="locate-button" type="button" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-location-crosshairs me-1"></i> Use my location
                        </button>
                    </div>
                    <p id="route-status" class="small text-muted mb-2" role="status" aria-live="polite">Choose an event, then allow location access to see your driving route.</p>
                    <div id="route-map" aria-label="Map showing your location and route to the selected event"></div>
                    <div id="route-summary" class="small fw-semibold mt-2" aria-live="polite"></div>
                    <ol id="directions-panel" class="small text-muted mt-2 mb-0 ps-3"></ol>
                    <a id="navigation-link" class="btn btn-sm btn-success mt-2 d-none" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-turn-up me-1"></i> Open turn-by-turn navigation
                    </a>
                    <?php if (empty($googleMapsApiKey)): ?>
                        <div class="alert alert-warning small mt-3 mb-0">Google Maps is not configured. Set the <code>GOOGLE_MAPS_API_KEY</code> environment variable for Apache, with Maps JavaScript API and Routes API enabled.</div>
                    <?php endif; ?>
                </section>

                <div class="mb-4">
                    <label class="form-label"><i class="fa-solid fa-square-parking me-1 text-secondary"></i> Available Parking Slot</label>
                    <select name="slot_id" class="form-select py-2" required>
                        <option value="">-- Select Available Slot --</option>
                        <?php foreach ($slots as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['slot_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-unor-primary py-2 fs-6">
                        <i class="fa-solid fa-circle-check me-1"></i> Confirm Reservation
                    </button>
                    <a href="index.php" class="btn btn-light text-secondary py-2 border">Cancel</a>
                </div>

            </form>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($googleMapsApiKey)): ?>
    <script>
        let routeMap;
        let currentPosition = null;
        let currentLocationMarker = null;
        let routeLines = [];
        let routeRequestId = 0;
        let activeLocationWatch = null;
        let RouteClass;

        const routeStatus = document.getElementById('route-status');
        const routeSummary = document.getElementById('route-summary');
        const directionsPanel = document.getElementById('directions-panel');
        const navigationLink = document.getElementById('navigation-link');
        const eventSelect = document.getElementById('event-select');

        async function initializeRouteMap() {
            const { Map } = await google.maps.importLibrary('maps');
            const { Route } = await google.maps.importLibrary('routes');
            RouteClass = Route;
            routeMap = new Map(document.getElementById('route-map'), {
                center: { lat: 10.6765, lng: 122.9509 },
                zoom: 13,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });
        }

        function selectedDestination() {
            const option = eventSelect.options[eventSelect.selectedIndex];
            const location = option ? option.dataset.location.trim() : '';
            if (!eventSelect.value || !location) return '';
            return location + ', University of Negros Occidental-Recoletos, Bacolod City, Philippines';
        }

        function clearRoute() {
            routeLines.forEach(line => line.setMap(null));
            routeLines = [];
            routeSummary.textContent = '';
            directionsPanel.replaceChildren();
            navigationLink.classList.add('d-none');
        }

        async function showRoute() {
            const destination = selectedDestination();
            if (!destination) {
                clearRoute();
                routeStatus.textContent = eventSelect.value ? 'This event has no venue set yet. Please contact the event organizer.' : 'Choose an event, then allow location access to see your driving route.';
                return;
            }
            if (!currentPosition) {
                routeStatus.textContent = 'Choose “Use my location” to find your position and calculate the route.';
                return;
            }

            const requestId = ++routeRequestId;
            routeStatus.textContent = 'Finding the best driving route…';
            clearRoute();

            try {
                const result = await RouteClass.computeRoutes({
                    origin: currentPosition,
                    destination,
                    travelMode: 'DRIVING',
                    regionCode: 'ph',
                    fields: ['path', 'legs']
                });
                if (requestId !== routeRequestId) return;
                const route = result.routes && result.routes[0];
                if (!route) throw new Error('No driving route was found for this event venue.');

                routeLines = route.createPolylines();
                routeLines.forEach(line => line.setMap(routeMap));

                const bounds = new google.maps.LatLngBounds();
                route.path.forEach(point => bounds.extend(point));
                routeMap.fitBounds(bounds, 48);

                const leg = route.legs && route.legs[0];
                const distance = leg && leg.localizedValues ? leg.localizedValues.distance : '';
                const duration = leg && leg.localizedValues ? leg.localizedValues.staticDuration : '';
                routeSummary.textContent = [distance, duration].filter(Boolean).join(' · ') || 'Route ready';
                const origin = encodeURIComponent(currentPosition.lat + ',' + currentPosition.lng);
                navigationLink.href = 'https://www.google.com/maps/dir/?api=1&origin=' + origin
                    + '&destination=' + encodeURIComponent(destination) + '&travelmode=driving';
                navigationLink.classList.remove('d-none');

                (leg && leg.steps ? leg.steps : []).forEach(step => {
                    const item = document.createElement('li');
                    item.className = 'direction-step';
                    const instruction = document.createElement('div');
                    instruction.textContent = step.instructions || step.maneuver || 'Continue along the route';
                    item.appendChild(instruction);
                    if (step.localizedValues) {
                        const detail = document.createElement('small');
                        detail.className = 'text-muted';
                        detail.textContent = [step.localizedValues.distance, step.localizedValues.staticDuration].filter(Boolean).join(' · ');
                        item.appendChild(detail);
                    }
                    directionsPanel.appendChild(item);
                });
                routeStatus.textContent = 'Route shown. Your location can update while this page remains open.';
            } catch (error) {
                if (requestId !== routeRequestId) return;
                routeStatus.textContent = error.message || 'Could not calculate the route. Check the venue address and try again.';
            }
        }

        function startLocationTracking() {
            if (!navigator.geolocation) {
                routeStatus.textContent = 'This browser does not support location access.';
                return;
            }
            routeStatus.textContent = 'Requesting permission to use your location…';
            navigator.geolocation.getCurrentPosition(position => {
                currentPosition = { lat: position.coords.latitude, lng: position.coords.longitude };
                if (!currentLocationMarker) {
                    currentLocationMarker = new google.maps.Marker({ map: routeMap, title: 'Your location', label: 'You' });
                }
                currentLocationMarker.setPosition(currentPosition);
                routeMap.setCenter(currentPosition);
                routeMap.setZoom(15);
                showRoute();

                if (activeLocationWatch === null && navigator.geolocation.watchPosition) {
                    activeLocationWatch = navigator.geolocation.watchPosition(nextPosition => {
                        const next = { lat: nextPosition.coords.latitude, lng: nextPosition.coords.longitude };
                        const toRadians = value => value * Math.PI / 180;
                        const latDelta = toRadians(next.lat - currentPosition.lat);
                        const lngDelta = toRadians(next.lng - currentPosition.lng);
                        const a = Math.sin(latDelta / 2) ** 2
                            + Math.cos(toRadians(currentPosition.lat)) * Math.cos(toRadians(next.lat))
                            * Math.sin(lngDelta / 2) ** 2;
                        const movement = 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        currentPosition = next;
                        currentLocationMarker.setPosition(currentPosition);
                        if (movement > 75) showRoute();
                    }, () => {}, { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 });
                }
            }, error => {
                const messages = {
                    1: 'Location permission was denied. Allow location access in your browser to see the route.',
                    2: 'Your current location could not be determined. Check your device location settings.',
                    3: 'Location request timed out. Please try again.'
                };
                routeStatus.textContent = messages[error.code] || 'Could not access your location.';
            }, { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 });
        }

        document.getElementById('locate-button').addEventListener('click', startLocationTracking);
        eventSelect.addEventListener('change', showRoute);
        window.initUparkRouteMap = initializeRouteMap;
    </script>
    <script async src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($googleMapsApiKey, ENT_QUOTES, 'UTF-8') ?>&amp;v=weekly&amp;loading=async&amp;callback=initUparkRouteMap"></script>
    <?php endif; ?>
</body>
</html>
