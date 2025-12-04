/**
 * Block: Office Card
 */

(function() {
    const ldrPostCard = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.ldr-post-card') || el;
        
    };

	document.querySelectorAll('.ldr-post-card').forEach((elem) => ldrPostCard(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=post-card', ldrPostCard);
    }

})();
