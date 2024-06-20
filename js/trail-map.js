document.addEventListener('DOMContentLoaded', function () {
    const buttonColor = pluginDirUrl.buttonColor;
    const buttonTextColor = pluginDirUrl.buttonTextColor;
    const buttonHoverColor = pluginDirUrl.buttonHoverColor;
    const buttonFocusColor = pluginDirUrl.buttonFocusColor;

    var sectionTitle = document.querySelector('.plugin-section-title');
    if (sectionTitle) {
        var sectionTitleColor = pluginDirUrl.sectionTitleColor;
        sectionTitle.style.color = sectionTitleColor;
    }

    const baseUrl = pluginDirUrl.url;
    const map = L.map('map').setView([mapSettings.latitude, mapSettings.longitude], mapSettings.zoom);

    const OpenStreetMap_France = L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap France | &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let gpxLayers = [];
    let waypointMarkers = [];

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

                // Add waypoint markers with numbers
                const waypoints = gpxData.getElementsByTagName('wpt');
                Array.from(waypoints).forEach((wpt, index) => {
                    const lat = wpt.getAttribute('lat');
                    const lon = wpt.getAttribute('lon');
                    const name = wpt.getElementsByTagName('name')[0].textContent;
                    const desc = wpt.getElementsByTagName('desc')[0] ? wpt.getElementsByTagName('desc')[0].textContent : '';

                    const waypointIcon = L.divIcon({
                        className: 'waypoint-icon',
                        html: `<div style="position: relative;"><i class="fa fa-map-marker" aria-hidden="true"></i><span style="position: absolute; top: 0; left: 0; transform: translate(-50%, -50%);">${index + 1}</span></div>`,
                        iconSize: [30, 42],
                        iconAnchor: [15, 42]
                    });

                    const marker = L.marker([lat, lon], { icon: waypointIcon }).addTo(map)
                        .bindPopup(`<b>${name}</b><br>${desc}`);
                    waypointMarkers.push(marker);
                });
            })
            .catch(error => console.log('Error loading GPX file:', error));
    }

    function clearGpxLayers() {
        gpxLayers.forEach(layer => map.removeLayer(layer));
        gpxLayers = [];
        waypointMarkers.forEach(marker => map.removeLayer(marker));
        waypointMarkers = [];
    }

    function showAllTrails(urls) {
        clearGpxLayers();
        urls.forEach(url => addGpxLayer(url));
    }

    document.querySelectorAll('.show-trail').forEach(function (button) {
        button.style.backgroundColor = buttonColor; // Set button color
        button.style.color = buttonTextColor; // Set button text color
        button.addEventListener('mouseover', function () {
            button.style.backgroundColor = buttonHoverColor; // Set button hover color
        });
        button.addEventListener('mouseout', function () {
            button.style.backgroundColor = buttonColor; // Reset button color
        });
        button.addEventListener('focus', function () {
            button.style.backgroundColor = buttonFocusColor; // Set button focus color
        });
        button.addEventListener('blur', function () {
            button.style.backgroundColor = buttonColor; // Reset button color
        });
        button.addEventListener('click', function () {
            clearGpxLayers();
            addGpxLayer(button.getAttribute('data-url'));
            const description = button.getAttribute('data-description');
            const distance = button.getAttribute('data-distance');
            const difficulty = button.getAttribute('data-difficulty');
            const duration = button.getAttribute('data-duration');
            const precautions = button.getAttribute('data-precautions');

            const descriptionElement = document.getElementById('trail-description');
            if (descriptionElement) {
                descriptionElement.innerHTML = `<h3>Informations de l'itinéraire</h3>
                                                <p><strong>Distance :</strong> ${distance} km</p>
                                                <p><strong>Difficulté :</strong> ${difficulty}</p>
                                                <p><strong>Durée :</strong> ${duration}</p>
                                                <h3>Description</h3>
                                                <p>${description}</p>
                                                <p><strong>Précautions :</strong> ${precautions}</p>`;
            }
        });
    });

    document.querySelector('#show-all').style.backgroundColor = buttonColor; // Set button color
    document.querySelector('#show-all').style.color = buttonTextColor; // Set button text color
    document.querySelector('#show-all').addEventListener('mouseover', function () {
        document.querySelector('#show-all').style.backgroundColor = buttonHoverColor; // Set button hover color
    });
    document.querySelector('#show-all').addEventListener('mouseout', function () {
        document.querySelector('#show-all').style.backgroundColor = buttonColor; // Reset button color
    });
    document.querySelector('#show-all').addEventListener('focus', function () {
        document.querySelector('#show-all').style.backgroundColor = buttonFocusColor; // Set button focus color
    });
    document.querySelector('#show-all').addEventListener('blur', function () {
        document.querySelector('#show-all').style.backgroundColor = buttonColor; // Reset button color
    });
    document.querySelector('#show-all').addEventListener('click', function () {
        const urls = Array.from(document.querySelectorAll('.show-trail')).map(button => button.getAttribute('data-url'));
        showAllTrails(urls);
        const descriptionElement = document.getElementById('trail-description');
        if (descriptionElement) {
            descriptionElement.innerText = ''; // Clear the description when showing all trails
        }
    });

    // Bouton de géolocalisation
    let userMarker;
    let locateControlEnabled = false;
    const locateControl = L.control({ position: 'topright' });
    locateControl.onAdd = function (map) {
        const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
        container.innerHTML = '<a class="leaflet-control-locate"><i class="fa fa-map-marker" aria-hidden="true" style="line-height:1.65; font-size: 20px;"></i></a>';
        container.onclick = () => {
            if (locateControlEnabled) {
                map.stopLocate();
                locateControlEnabled = false;
                container.querySelector('i').style.color = 'red';
            } else {
                map.locate({ setView: true, maxZoom: 16, watch: true, enableHighAccuracy: true });
                locateControlEnabled = true;
                container.querySelector('i').style.color = 'green';
            }
        };
        return container;
    };
    locateControl.addTo(map);

    map.on('locationfound', function (e) {
        const userIcon = L.divIcon({
            className: 'user-location-icon',
            html: '<i class="fas fa-street-view"></i>',
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
        alert("Erreur de localisation: " + e.message);
    });
});
