/**
 * Block: Office Card
 */
import L from 'leaflet';

(function() {
    const ldrOfficeCard = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.ldr-office-card') || el;
        const mapContainer = block.querySelector('.card-map-top');
        const mapWrapper = document.createElement('div');
        const imageContainer = block.querySelector('.card-img-top');
        
        if(mapContainer) {
            let map = null;
            const mapSettings = mapContainer && mapContainer.dataset && mapContainer.dataset.mapSettings && JSON.parse(mapContainer.dataset.mapSettings);
            const options = {
                center: mapSettings.latLong,
                zoom: mapSettings.zoom,
                scrollWheelZoom: mapSettings.scrollWheelZoom,
                pan: true,
            }
            const marker = L.icon({
                iconUrl: mapSettings.markerUrl,
                iconSize: [mapSettings.markerSize, mapSettings.markerSize],
                iconAnchor: [mapSettings.markerSize/2, mapSettings.markerSize],
                popupAnchor:  [0, 0],
            });
            const layer = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

            mapWrapper.style.height = `${mapSettings.mapHeight}px`;
            mapWrapper.style.width = `${mapContainer.offsetWidth}px`;

            if(!mapContainer.firstChild) {
                mapContainer.appendChild(mapWrapper);
            } else {
                mapContainer.firstChild.remove();
                mapContainer.appendChild(mapWrapper);
            }

            map = L.map(mapWrapper, options);
            L.marker(mapSettings.latLong, {icon: marker}).addTo(map).bindPopup(mapSettings.address);
            map.addLayer(layer);

            window.addEventListener('resize', () => {
                mapWrapper.style.width = `${mapContainer.offsetWidth}px`;
            });
        } 

        if(imageContainer) {
            const colorOverlay = imageContainer.querySelector('.color-overlay');
            if(colorOverlay) {
                [...colorOverlay.parentElement.children].forEach((elem) => {
                    if(!elem.classList.contains('color-overlay')) {
                        elem.remove();
                    }
                });
            }
        }
    };

	document.querySelectorAll('.ldr-office-card').forEach((elem) => ldrOfficeCard(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=office-card', ldrOfficeCard);
    }

})();
