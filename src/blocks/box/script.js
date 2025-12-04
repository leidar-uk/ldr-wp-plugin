/**
 * Block: Box
 */
(function() {
    const ldrCard = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.classList.contains('acf-block-preview') ? el.querySelector('.ldr-box') : el;
        
        
    };

	document.querySelectorAll('.ldr-box').forEach((elem) => ldrCard(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=box', ldrCard);
    }

})();
