/**
 * Block: Member Card
 */
(function() {
    const ldrMemberCard = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-member-card') : el;
        
        const profileImage = block.querySelector('.profile-image');
        const bioLink = block.querySelector('.bio-link');
        
        if(bioLink) {
            bioLink.addEventListener('mouseover', () => {
                profileImage.classList.add('is-hovered');
            });
            bioLink.addEventListener('mouseout', () => {
                profileImage.classList.remove('is-hovered');
            });
        }
    };

	document.querySelectorAll('.ldr-member-card').forEach((elem) => ldrMemberCard(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=member-card', ldrMemberCard);
    }

})();
