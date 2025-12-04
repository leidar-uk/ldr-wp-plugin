/**
 * Block: Service Slider
 */
import Glide from '@glidejs/glide';

(function() {
    const serviceSlider = (elem, acfBlockData) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-service-slider') : el;
        const glide = block.querySelector('.ldr-service-slider .glide');
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
        const cards = el.querySelectorAll('.ldr-service-card') || null;
        
        if(glide) {
            const slider = new Glide('.ldr-service-slider .glide', {...defaultSettings, ...sliderSettings}).mount();

            if(acfBlockData) {
                if(acfBlockData.data.field_service_slider_type) {
                    sliderSettings.type = acfBlockData.data.field_service_slider_type;
                }
                if(acfBlockData.data.field_service_slider_start_at) {
                    sliderSettings.startAt = parseInt(acfBlockData.data.field_service_slider_start_at);
                }
                if(acfBlockData.data.field_service_slider_per_view) {
                    sliderSettings.perView = parseInt(acfBlockData.data.field_service_slider_per_view);
                }
                if(acfBlockData.data.field_service_slider_autoplay) {
                    sliderSettings.autoplay = parseInt(acfBlockData.data.field_service_slider_autoplay);
                }
                if(acfBlockData.data.field_service_slider_hoverpause) {
                    sliderSettings.hoverpause = true;
                } else {
                    sliderSettings.hoverpause = false;
                }
                if(acfBlockData.data.field_service_slider_animation_duration) {
                    sliderSettings.animationDuration = parseInt(acfBlockData.data.field_service_slider_animation_duration);
                }

                slider.update({...defaultSettings, ...sliderSettings});
            }
            
        }

        if(cards) {
            cards.forEach((card) => {
                const imageContainer = card.querySelector('.card-img-top');
                const colorOverlay = imageContainer.querySelector('.color-overlay');

                if(colorOverlay) {
                    [...colorOverlay.parentElement.children].forEach((elem) => {
                        if(!elem.classList.contains('color-overlay')) {
                            elem.remove();
                        }
                    });
                }
            });
        }
        
    };

	document.querySelectorAll('.ldr-service-slider').forEach((elem) => serviceSlider(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=service-slider', serviceSlider);
    }

})();
