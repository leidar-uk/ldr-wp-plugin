/**
 * Block: Office Slider
 */
import L from 'leaflet';
import Glide from '@glidejs/glide';

(function() {
    const officeSlider = (elem, acfBlockData) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-office-slider') : el;
        const glide = block.querySelector('.ldr-office-slider .glide');
        const defaultSettings = {
            type: 'carousel',
            startAt: 0,
            perView: 1,
            gap: 24,
            autoplay: 3000,
            hoverpause: true,
            animationDuration: 1000,
        };
        let sliderSettings = (block.dataset && block.dataset.sliderSettings && JSON.parse(block.dataset.sliderSettings)) || {};
        const cards = el.querySelectorAll('.ldr-office-card') || null;
        
        if(glide) {
            const slider = new Glide('.ldr-office-slider .glide', {...defaultSettings, ...sliderSettings}).mount();

            if(acfBlockData) {
                if(acfBlockData.data.field_office_slider_type) {
                    sliderSettings.type = acfBlockData.data.field_office_slider_type;
                }
                if(acfBlockData.data.field_office_slider_start_at) {
                    sliderSettings.startAt = parseInt(acfBlockData.data.field_office_slider_start_at);
                }
                if(acfBlockData.data.field_office_slider_per_view) {
                    sliderSettings.perView = parseInt(acfBlockData.data.field_office_slider_per_view);
                }
                if(acfBlockData.data.field_office_slider_autoplay) {
                    sliderSettings.autoplay = parseInt(acfBlockData.data.field_office_slider_autoplay);
                }
                if(acfBlockData.data.field_office_slider_hoverpause) {
                    sliderSettings.hoverpause = true;
                } else {
                    sliderSettings.hoverpause = false;
                }
                if(acfBlockData.data.field_office_slider_animation_duration) {
                    sliderSettings.animationDuration = parseInt(acfBlockData.data.field_office_slider_animation_duration);
                }

                slider.update({...defaultSettings, ...sliderSettings});
            }
        }

        if(cards) {
            cards.forEach((card) => {
                const mapContainer = card.querySelector('.card-map-top');
                const mapWrapper = document.createElement('div');
                const imageContainer = card.querySelector('.card-img-top');

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
            });
        }
        
    };

	document.querySelectorAll('.ldr-office-slider').forEach((elem) => officeSlider(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=office-slider', officeSlider);
    }

})();
