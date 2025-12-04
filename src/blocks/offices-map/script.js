/**
 * Block: Office Map
 */
import L from 'leaflet';

(function() {
    const ldrOfficesMap = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.classList.contains('acf-block-preview') ? el.querySelector('.ldr-offices-map') : el;
        const mapContainer = el.querySelector('.ldr-offices-map-container');
        const mapSettings = block && block.dataset && block.dataset.mapSettings && JSON.parse(block.dataset.mapSettings);
        const options = {
            center: [mapSettings.map_center_lat, mapSettings.map_center_long],
            zoom: mapSettings.zoom,
            scrollWheelZoom: mapSettings.scroll_wheel_zoom,
        }
        const map = new L.map(mapContainer, options);
        const layer = new L.TileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
        const marker = L.icon({
            iconUrl: mapSettings.marker,
            iconSize: [mapSettings.marker_size, mapSettings.marker_size],
            iconAnchor: [mapSettings.marker_size/2, mapSettings.marker_size],
            popupAnchor:  [(mapSettings.marker_size/2), 0],
        });

        mapSettings.offices.forEach((office) => {
            if(mapSettings.show_popup) {
                L.marker([office.lat, office.long], {icon: marker}).addTo(map).bindPopup(office.address);
            } else {
                L.marker([office.lat, office.long], {icon: marker}).addTo(map);
            }
        });

        map.addLayer(layer);
        
    };

	document.querySelectorAll('.ldr-offices-map').forEach((elem) => ldrOfficesMap(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=offices-map', ldrOfficesMap);
    }

})();
