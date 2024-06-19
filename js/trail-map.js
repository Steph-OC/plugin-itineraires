document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = pluginDirUrl.url;
    const mapSettings = pluginDirUrl.mapSettings;
    const map = L.map('map').setView([mapSettings.latitude, mapSettings.longitude], mapSettings.zoom);

    const OpenStreetMap_France = L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap France | &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let gpxLayers = [];

    function addGpxLayer(url) {
        console.log("Fetching GPX file from URL:", url);
        fetch(url)
            .then(response => {
                console.log("Response from fetching GPX file:", response);
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.text();
            })
            .then(gpx => {
                console.log("GPX Data:", gpx);
                var parser = new DOMParser();
                var gpxData = parser.parseFromString(gpx, 'application/xml');

                const startIconUrl = baseUrl + 'leaflet/images/pin-icon-start.png';
                const endIconUrl = baseUrl + 'leaflet/images/pin-icon-end.png';
                const shadowUrl = baseUrl + 'leaflet/images/marker-shadow.png';
                const wptIconUrl = baseUrl + 'leaflet/images/pin-icon-wpt.png';

                console.log("Start Icon URL:", startIconUrl);
                console.log("End Icon URL:", endIconUrl);
                console.log("Shadow URL:", shadowUrl);
                console.log("Waypoint Icon URL:", wptIconUrl);

                const layer = new L.GPX(gpxData.documentElement.outerHTML, {
                    async: true,
                    marker_options: {
                        startIconUrl: startIconUrl,
                        endIconUrl: endIconUrl,
                        shadowUrl: shadowUrl,
                        wptIconUrls: {
                            '': wptIconUrl
                        }
                    },
                    polyline_options: {
                        color: 'red',
                        weight: 5,
                        opacity: 1,
                        smoothFactor: 1,
                    }
                }).on('loaded', function (e) {
                    map.fitBounds(e.target.getBounds());
                });

                gpxLayers.push(layer);
                layer.addTo(map);
            })
            .catch(error => console.log('Error loading GPX file:', error));
    }

    function clearGpxLayers() {
        gpxLayers.forEach(layer => map.removeLayer(layer));
        gpxLayers = [];
    }

    function showAllTrails(urls) {
        clearGpxLayers();
        urls.forEach(url => addGpxLayer(url));
    }

    document.querySelectorAll('.show-trail').forEach(function(button) {
        button.addEventListener('click', function() {
            clearGpxLayers();
            addGpxLayer(button.getAttribute('data-url'));
        });
    });

    document.querySelector('#show-all').addEventListener('click', function() {
        const urls = Array.from(document.querySelectorAll('.show-trail')).map(button => button.getAttribute('data-url'));
        showAllTrails(urls);
    });

    // Bouton de géolocalisation
    let userMarker;
    let locateControlEnabled = false;
    const locateControl = L.control({ position: 'topright' });
    locateControl.onAdd = function (map) {
        const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
        const button = L.DomUtil.create('a', 'leaflet-control-locate locate-icon', container);
        button.innerHTML = '<i class="fa fa-map-marker" aria-hidden="true"></i>';

        button.onclick = () => {
            if (locateControlEnabled) {
                map.stopLocate();
                locateControlEnabled = false;
                button.classList.remove('enabled');
            } else {
                map.locate({ setView: true, maxZoom: 16, watch: true, enableHighAccuracy: true, timeout: 10000 }); // Augmenter le délai d'attente ici
                locateControlEnabled = true;
                button.classList.add('enabled');
            }
        };
        return container;
    };
    locateControl.addTo(map);

    map.on('locationfound', function (e) {
        const userIcon = L.divIcon({
            className: 'user-location-icon',
            html: '<i class="fas fa-hiking"></i>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });

        if (!userMarker) {
            userMarker = L.marker(e.latlng, { icon: userIcon }).addTo(map);
        } else {
            userMarker.setLatLng(e.latlng);
        }

        map.setView(e.latlng, 16); // Ajuster le niveau de zoom ici
    });

    map.on('locationerror', function (e) {
        // Afficher un message d'erreur convivial
        showErrorNotification("Erreur de localisation: " + e.message);
    });

    function showErrorNotification(message) {
        const notification = L.DomUtil.create('div', 'location-error-notification', document.body);
        notification.innerText = message;
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 5000); // Supprimer la notification après 5 secondes
    }
});
