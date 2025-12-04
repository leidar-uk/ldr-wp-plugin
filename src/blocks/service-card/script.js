/**
 * Block: Service Card
 */

(function() {
    const ldrServiceCard = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.ldr-service-card') || el;
        
    };

	document.querySelectorAll('.ldr-service-card').forEach((elem) => ldrServiceCard(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=service-card', ldrServiceCard);
    }

})();
