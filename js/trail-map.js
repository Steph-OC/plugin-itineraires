document.addEventListener('DOMContentLoaded', function () {
    console.log('trail-map.js is loaded');

    const buttonColor = pluginDirUrl.buttonColor;
    const buttonTextColor = pluginDirUrl.buttonTextColor;
    const buttonHoverColor = pluginDirUrl.buttonHoverColor;
    const buttonFocusColor = pluginDirUrl.buttonFocusColor;
    const copyButton = document.getElementById('copy-shortcode');
    const shortcodeElement = document.getElementById('trail-map-shortcode');

    if (copyButton && shortcodeElement) {
        console.log('Copy button and shortcode element found');
        copyButton.addEventListener('click', function () {
            const range = document.createRange();
            range.selectNode(shortcodeElement);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    alert('Shortcode copié dans le presse-papiers !');
                } else {
                    console.error('Erreur lors de la copie du shortcode.');
                }
            } catch (err) {
                console.error('Erreur lors de la copie du shortcode : ', err);
            }

            window.getSelection().removeAllRanges();
        });
    } else {
        console.log('Copy button or shortcode element not found');
    }

    var sectionTitle = document.querySelector('.plugin-section-title');
    if (sectionTitle) {
        var sectionTitleColor = pluginDirUrl.sectionTitleColor;
        sectionTitle.style.color = sectionTitleColor;
    }

    // Vérifiez si l'élément map est présent avant de créer la carte
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.log('Map container not found');
        return;
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
        button.style.backgroundColor = buttonColor;
        button.style.color = buttonTextColor;
        button.addEventListener('mouseover', function () {
            button.style.backgroundColor = buttonHoverColor;
        });
        button.addEventListener('mouseout', function () {
            button.style.backgroundColor = buttonColor;
        });
        button.addEventListener('focus', function () {
            button.style.backgroundColor = buttonFocusColor;
        });
        button.addEventListener('blur', function () {
            button.style.backgroundColor = buttonColor;
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
                descriptionElement.style.display = 'block'; // Show description for individual trails
            }
        });
    });

    const showAllButton = document.querySelector('#show-all');
    if (showAllButton) {
        showAllButton.style.backgroundColor = buttonColor;
        showAllButton.style.color = buttonTextColor;
        showAllButton.addEventListener('mouseover', function () {
            showAllButton.style.backgroundColor = buttonHoverColor;
        });
        showAllButton.addEventListener('mouseout', function () {
            showAllButton.style.backgroundColor = buttonColor;
        });
        showAllButton.addEventListener('focus', function () {
            showAllButton.style.backgroundColor = buttonFocusColor;
        });
        showAllButton.addEventListener('blur', function () {
            showAllButton.style.backgroundColor = buttonColor;
        });
        showAllButton.addEventListener('click', function () {
            const urls = Array.from(document.querySelectorAll('.show-trail')).map(button => button.getAttribute('data-url'));
            showAllTrails(urls);
            const descriptionElement = document.getElementById('trail-description');
            if (descriptionElement) {
                descriptionElement.style.display = 'none'; // Hide description for "show all"
            }
        });
    } else {
        const showAllButtonElement = document.getElementById('show-all');
        if (showAllButtonElement) {
            showAllButtonElement.style.display = 'none'; // Hide the button
        }
    }

    // Masquer la section de description par défaut
    const descriptionElement = document.getElementById('trail-description');
    if (descriptionElement) {
        descriptionElement.style.display = 'none';
    }

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

        map.setView(e.latlng, 16);
    });

    map.on('locationerror', function (e) {
        alert("Erreur de localisation: " + e.message);
    });
});
