/**
 * Block: Post Slider
 */
import Glide from '@glidejs/glide';

(function() {
    const postSlider = (elem, acfBlockData) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-post-slider') : el;
        const glide = block.querySelector('.ldr-post-slider .glide');
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
        let isStatic = (block.dataset && block.dataset.isStatic) || 0;
        const cards = el.querySelectorAll('.ldr-post-card') || null;
        
        if(glide && (isStatic != 1)) {
            const slider = new Glide('.ldr-post-slider .glide', {...defaultSettings, ...sliderSettings}).mount();

            if(acfBlockData) {
                if(acfBlockData.data.field_post_slider_type) {
                    sliderSettings.type = acfBlockData.data.field_post_slider_type;
                }
                if(acfBlockData.data.field_post_slider_start_at) {
                    sliderSettings.startAt = parseInt(acfBlockData.data.field_post_slider_start_at);
                }
                if(acfBlockData.data.field_post_slider_per_view) {
                    sliderSettings.perView = parseInt(acfBlockData.data.field_post_slider_per_view);
                }
                if(acfBlockData.data.field_post_slider_autoplay) {
                    sliderSettings.autoplay = parseInt(acfBlockData.data.field_post_slider_autoplay);
                }
                if(acfBlockData.data.field_post_slider_hoverpause) {
                    sliderSettings.hoverpause = true;
                } else {
                    sliderSettings.hoverpause = false;
                }
                if(acfBlockData.data.field_post_slider_animation_duration) {
                    sliderSettings.animationDuration = parseInt(acfBlockData.data.field_post_slider_animation_duration);
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

	document.querySelectorAll('.ldr-post-slider').forEach((elem) => postSlider(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=post-slider', postSlider);
    }

})();
