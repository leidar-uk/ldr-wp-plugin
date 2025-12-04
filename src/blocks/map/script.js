/**
 * Block: Map
 */
import L from 'leaflet';

(function() {
    const ldrMap = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.classList.contains('acf-block-preview') ? el.querySelector('.ldr-map') : el;
        const mapContainer = el.querySelector('.ldr-map-container');
        const mapSettings = block && block.dataset && block.dataset.mapSettings && JSON.parse(block.dataset.mapSettings);
        const options = {
            center: [mapSettings.lat, mapSettings.long],
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
        L.marker([mapSettings.lat, mapSettings.long], {icon: marker}).addTo(map).bindPopup(mapSettings.pop_up_text);
        map.addLayer(layer);
    };

	document.querySelectorAll('.ldr-map').forEach((elem) => ldrMap(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=map', ldrMap);
    }
})();
