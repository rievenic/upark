(function () {
  'use strict';
  const CAMPUS = { lat: 10.6561, lng: 122.94769 };
  const PARKING_AREAS = [
    { id: 'ovy', name: 'Front of the OVY Building', lat: 10.656332, lng: 122.947260 },
    { id: 'pool', name: 'Near the University Swimming Pool', lat: 10.656025, lng: 122.948658 },
    { id: 'hm', name: 'Side of the HM Building', lat: 10.656863, lng: 122.948430 },
    { id: 'oratory', name: 'Side of the Oratory', lat: 10.658461, lng: 122.948377 }
  ];
  const CAMPUS_ADDRESS = 'University of Negros Occidental–Recoletos, Bacolod City, Philippines';
  let map = null;
  let userMarker = null;
  let directionsService = null;
  let directionsRenderer = null;
  let currentPosition = null;
  let selectedParking = PARKING_AREAS[0];
  const areaMarkers = [];
  const $ = (id) => document.getElementById(id);

  function status(message) {
    const node = $('uparkMapStatus');
    if (node) node.textContent = message;
  }
  function selectedArea() {
    const select = $('uparkParkingArea');
    return PARKING_AREAS.find(area => area.id === (select && select.value)) || selectedParking || PARKING_AREAS[0];
  }
  function selectedDestination() {
    const eventSelect = $('uparkEventSelect');
    if (eventSelect && eventSelect.value) {
      const option = eventSelect.options[eventSelect.selectedIndex];
      return { name: option ? option.textContent : 'Campus event', routeValue: eventSelect.value, linkValue: eventSelect.value };
    }
    const area = selectedArea();
    return { ...area, routeValue: { lat: area.lat, lng: area.lng }, linkValue: area.lat + ',' + area.lng };
  }
  function setDestination(area) {
    selectedParking = area;
    const eventSelect = $('uparkEventSelect');
    if (eventSelect) eventSelect.value = '';
    const input = $('uparkDestination');
    if (input) input.value = area.name;
    if (map) {
      map.panTo({ lat: area.lat, lng: area.lng });
      map.setZoom(19);
      areaMarkers.forEach(item => item.marker.setAnimation(item.area.id === area.id ? google.maps.Animation.BOUNCE : null));
      window.setTimeout(() => areaMarkers.forEach(item => item.marker.setAnimation(null)), 700);
    }
    status('Selected destination: ' + area.name + '. Get your route, then tap Start navigation. Verify this is an approved parking area before use.');
  }
  function openGoogleNavigation() {
    const destination = selectedDestination();
    const params = new URLSearchParams({ api: '1', destination: destination.linkValue, travelmode: 'driving' });
    if (currentPosition) params.set('origin', currentPosition.lat + ',' + currentPosition.lng);
    window.open('https://www.google.com/maps/dir/?' + params.toString(), '_blank', 'noopener,noreferrer');
    status('Google Maps opened for navigation to ' + destination.name + '. Follow campus signs and guard instructions; confirm the vehicle access point.');
  }
  function getCurrentLocation(onSuccess) {
    if (!navigator.geolocation) {
      status('This browser does not support location. Select a destination and open Google Maps to set your starting point.');
      return;
    }
    status('Requesting permission to use your current location…');
    navigator.geolocation.getCurrentPosition(function (position) {
      currentPosition = { lat: position.coords.latitude, lng: position.coords.longitude };
      if (map && window.google && google.maps) {
        if (!userMarker) {
          userMarker = new google.maps.Marker({
            map, position: currentPosition, title: 'Your current location',
            icon: { path: google.maps.SymbolPath.CIRCLE, scale: 7, fillColor: '#1877f2', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2 }
          });
        } else userMarker.setPosition(currentPosition);
      }
      if (onSuccess) onSuccess();
    }, function () {
      status('Location permission was denied or unavailable. You can still start navigation in Google Maps and choose your starting point there.');
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 });
  }
  function previewRoute() {
    if (!map || !directionsService || !directionsRenderer) {
      status('Google Maps is not ready. Check your API key and Maps JavaScript API settings.');
      return;
    }
    const destination = selectedDestination();
    const calculate = () => {
      if (!currentPosition) {
        status('Getting your current location to draw the route…');
        getCurrentLocation(() => calculateRoute(destination));
      } else calculateRoute(destination);
    };
    const calculateRoute = (routeDestination) => {
      directionsService.route({
        origin: currentPosition,
        destination: routeDestination.routeValue,
        travelMode: google.maps.TravelMode.DRIVING
      }, (result, code) => {
        if (code === 'OK' && result) {
          directionsRenderer.setDirections(result);
          const leg = result.routes[0] && result.routes[0].legs[0];
          const estimate = leg ? ` Approximate road route: ${leg.distance.text}, ${leg.duration.text}.` : '';
          status('Route preview shown to ' + routeDestination.name + '.' + estimate + ' Tap Start navigation to open Google Maps. Routes may end at the nearest routable road; verify the campus access point.');
        } else {
          status('Google could not calculate a driving route to this exact point (' + code + '). The point may be off-road or inside a restricted campus area. Try Start navigation to open Google Maps, and verify the approved vehicle access point.');
        }
      });
    };
    calculate();
  }

  window.initUParkMap = function () {
    const container = $('uparkMap');
    if (!container || !window.google || !google.maps) return;
    map = new google.maps.Map(container, {
      center: CAMPUS, zoom: 18, mapTypeControl: false, streetViewControl: false,
      fullscreenControl: true, clickableIcons: false
    });
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
      map, suppressMarkers: false,
      polylineOptions: { strokeColor: '#1677ff', strokeOpacity: 0.95, strokeWeight: 6 }
    });
    PARKING_AREAS.forEach((area, index) => {
      const marker = new google.maps.Marker({
        map, position: { lat: area.lat, lng: area.lng }, title: area.name,
        label: { text: String(index + 1), color: '#ffffff', fontWeight: '700' }
      });
      const info = new google.maps.InfoWindow({ content: '<div style="font-family:Arial,sans-serif;max-width:230px"><strong>' + area.name + '</strong><br><small>' + area.lat + ', ' + area.lng + '</small><br><small>Tap Select destination in the side panel to preview a route.</small></div>' });
      marker.addListener('click', () => { info.open({ anchor: marker, map }); const select = $('uparkParkingArea'); if (select) select.value = area.id; setDestination(area); });
      areaMarkers.push({ area, marker });
    });
    const input = $('uparkDestination');
    if (input && google.maps.places && google.maps.places.Autocomplete) {
      const autocomplete = new google.maps.places.Autocomplete(input, { fields: ['formatted_address', 'geometry', 'name'], componentRestrictions: { country: 'ph' } });
      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (place && place.geometry && place.geometry.location) {
          map.panTo(place.geometry.location); map.setZoom(17);
          status('Search result selected. For routing to one of the marked U-PARK areas, select that destination from the parking-area list.');
        }
      });
    }
    const loading = container.querySelector('.upark-map-loading');
    if (loading) loading.remove();
    setDestination(PARKING_AREAS[0]);
    status('Map loaded with four parking-area pins. These coordinates must be confirmed as safe, approved vehicle destinations before operational use.');
    if (typeof window.initUparkPortalMap === 'function') window.initUparkPortalMap();
  };

  document.addEventListener('DOMContentLoaded', function () {
    const areaSelect = $('uparkParkingArea');
    const previewButton = $('uparkRouteButton');
    const startButton = $('uparkStartNavigation');
    const locationButton = $('uparkMyLocation');
    const eventSelect = $('uparkEventSelect');
    if (areaSelect) areaSelect.addEventListener('change', () => setDestination(selectedArea()));
    if (eventSelect) eventSelect.addEventListener('change', function () {
      if (this.value) {
        const input = $('uparkDestination');
        if (input) input.value = this.value;
        status('Event destination selected. Preview the route or start navigation; event venue text may resolve to an approximate location.');
      } else setDestination(selectedArea());
    });
    if (previewButton) previewButton.addEventListener('click', previewRoute);
    if (startButton) startButton.addEventListener('click', openGoogleNavigation);
    if (locationButton) locationButton.addEventListener('click', () => getCurrentLocation(() => {
      if (map) { map.panTo(currentPosition); map.setZoom(18); }
      status('Your current location is marked on the map. Select a parking destination and tap Preview route. Your coordinates are not saved by this page.');
    }));
  });
})();
