/**
 * Block: Team Slider
 */
import Glide from '@glidejs/glide';

(function() {
    const teamSlider = (elem, acfBlockData) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-team-slider') : el;
        const glide = block.querySelector('.ldr-team-slider .glide');
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
        const settings = {...defaultSettings, ...sliderSettings};
        
        if(glide) {
            if(settings.autoplay > 0) {
                new Glide('.ldr-team-slider .glide', settings).mount();
            } else {
                const slideWidth = 100 / settings.perView;
                const slides = glide.querySelectorAll('.glide__slide');

                glide.classList.add('glide--static');
                
                if(glide.classList.contains('glide--static')) {
                    if(window.innerWidth > 768) {
                        [...slides].forEach((slide) => {
                            slide.style.width = `${slideWidth}%`;
                        });
                    }

                    window.addEventListener('resize', (event) => {
                        if(event.target.innerWidth > 768) {
                            [...slides].forEach((slide) => {
                                slide.style.width = `${slideWidth}%`;
                            });
                        } else {
                            [...slides].forEach((slide) => {
                                slide.style.width = '100%';
                            });
                        };
                    });
                }
            }
        } 
        
    };

	document.querySelectorAll('.ldr-team-slider').forEach((elem) => teamSlider(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=team-slider', teamSlider);
    }

})();
