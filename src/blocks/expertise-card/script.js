/**
 * Block: Expertise Card
 */

(function() {
    const ldrExpertiseCard = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.ldr-expertise-card') || el;
        
    };

	document.querySelectorAll('.ldr-expertise-card').forEach((elem) => ldrExpertiseCard(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=expertise-card', ldrExpertiseCard);
    }

})();
