/**
 * Block: Flip Cards
 */
import gsap from 'gsap';

(function() {
    const ldrFlipCards = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.classList && el.classList.contains('ldr-flip-cards') ? el : el.querySelector('.ldr-flip-cards');

        if(!block) {
            return;
        }

        const cards = block.querySelectorAll('.ldr-flip-card');

        cards.forEach((card) => {
            // Avoid double-binding the same card (e.g. re-triggered ACF preview render)
            if(card.dataset.flipCardsInit) {
                return;
            }
            card.dataset.flipCardsInit = '1';

            const inner = card.querySelector('.ldr-flip-card__inner');
            if(!inner) {
                return;
            }

            // Any link/button (or anything explicitly opted out) should behave
            // normally instead of being swallowed by the flip interaction.
            const isInteractiveTarget = (target) =>
                !!(target && target.closest && target.closest('a, button, [data-no-flip]'));

            // Links on the back face must not be reachable by keyboard while that
            // face is hidden, otherwise Tab would land on invisible links.
            const backLinks = card.querySelectorAll('.ldr-flip-card__side--back a');
            backLinks.forEach((link) => link.setAttribute('tabindex', '-1'));

            let isFlipped = false;

            // Flip timeline (front <-> back)
            const flipTl = gsap.timeline({ paused: true });
            flipTl.to(inner, {
                duration: 0.6,
                rotateY: 180,
                ease: 'power2.inOut',
            });

            const toggleFlip = () => {
                isFlipped = !isFlipped;
                card.setAttribute('aria-expanded', isFlipped ? 'true' : 'false');
                backLinks.forEach((link) => link.setAttribute('tabindex', isFlipped ? '0' : '-1'));

                if(isFlipped) {
                    flipTl.play();
                } else {
                    flipTl.reverse();
                }
            };

            // --- 3D hover tilt + "snap to cursor" ---
            const maxTilt = 8;    // degrees
            const hoverScale = 1.04;

            const handleMouseMove = (event) => {
                const rect = card.getBoundingClientRect();
                const relX = (event.clientX - rect.left) / rect.width;  // 0..1
                const relY = (event.clientY - rect.top) / rect.height;  // 0..1

                const rotateY = (relX - 0.5) * (maxTilt * 2); // left/right
                const rotateX = (0.5 - relY) * (maxTilt * 2); // up/down

                const originX = relX * 100;
                const originY = relY * 100;

                gsap.to(card, {
                    rotateX,
                    rotateY,
                    scale: hoverScale,
                    transformOrigin: `${originX}% ${originY}%`,
                    duration: 0.25,
                    ease: 'power2.out',
                });
            };

            const handleMouseLeave = () => {
                gsap.to(card, {
                    rotateX: 0,
                    rotateY: 0,
                    scale: 1,
                    transformOrigin: '50% 50%',
                    duration: 0.4,
                    ease: 'power2.out',
                });
            };

            // Pointer interactions
            card.addEventListener('mousemove', handleMouseMove);
            card.addEventListener('mouseleave', handleMouseLeave);

            // Click / keyboard to flip — but let real links/buttons inside the
            // card do their own thing instead of triggering the flip.
            card.addEventListener('click', (event) => {
                if(isInteractiveTarget(event.target)) return;
                event.preventDefault();
                toggleFlip();
            });

            card.addEventListener('keydown', (event) => {
                if(isInteractiveTarget(event.target)) return;
                if(event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggleFlip();
                }
            });
        });
    };

	document.querySelectorAll('.ldr-flip-cards').forEach((elem) => ldrFlipCards(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=flip-cards', ldrFlipCards);
    }

})();
