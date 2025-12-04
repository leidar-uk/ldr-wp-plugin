/**
 * Block: Masonry Gallery
 */
import imagesloaded from 'imagesloaded';
import Masonry from 'masonry-layout';

(function() {
    const c83MasonryGallery = (elem) => {
        const msnry = new Masonry(elem[0] || elem, {
            itemSelector: '.item',
            columnWidth: '.sizer',
            percentPosition: true,
        });

        imagesloaded(elem).on('progress', () => msnry.layout());
    };

	document.querySelectorAll('.ldr-masonry-gallery__items').forEach((elem) => c83MasonryGallery(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=masonry-gallery', c83MasonryGallery);
    }
})();
