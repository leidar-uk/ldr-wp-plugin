/**
 * Block: Expertise Grid
 */
import $ from 'jquery';
import gsap from 'gsap';

(function() {
    const ldrExpertiseGrid = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-expertise-grid') : el;
        const cardSettings = (block.dataset && block.dataset.cardSettings && JSON.parse(block.dataset.cardSettings));
        const customSelection = (block.dataset && block.dataset.selectedExpertises && JSON.parse(block.dataset.selectedExpertises)) || [];
        const excludedExpertises = (block.dataset && block.dataset.excludedExpertises && JSON.parse(block.dataset.excludedExpertises)) || [];
        const filters = block.querySelector('.ldr-expertise-grid .grid-filter');
        const filterBtns = filters && filters.querySelectorAll('.ldr-expertise-grid .dropdown-item');
        const resetFilterBtn = block.querySelector('.reset-filter');
        const copyFilteredResultsBtn = block.querySelector('.copy-filtered-results');
        const excludeSticky = (block.dataset && block.dataset.excludeSticky) || 0;
        const output = block.querySelector('.ldr-expertise-grid .query-output');
        const loader = output.querySelector('.ldr-expertise-grid .loader');
        const grid = output.querySelector('.ldr-expertise-grid .grid-items');
        const postsNumber = (block.dataset) && parseInt(block.dataset.postsNumber) || -1;
        const loadMoreBtn = block.querySelector('.load-more');
        let currentPage = 1;
        let currentFilter = 0;
        let expertiseCards = [];

        const loadMoreExpertises = (el) => {
            currentPage++;

            loader.classList.remove('d-none');

            loadData(currentFilter, excludeSticky, cardSettings, postsNumber, currentPage);
        }

        const loadData = (
                currentFilter = 0, 
                excludeSticky = 0, 
                cardSettings = {}, 
                postsNumber = -1, 
                currentPage = 1
            ) => {
            $.ajax({
                url: themeData.wpAjax,
                type: 'POST',
                data: {
                    action: 'load_expertise',
                    filter: currentFilter,
                    customSelection: customSelection,
                    excludedExpertises: excludedExpertises,
                    cardSettings: cardSettings,
                    excludeSticky: excludeSticky,
                    postsNumber: postsNumber,
                    paged: currentPage,
                },
                success: (result) => {
                    loader.classList.add('d-none');
                    grid.innerHTML = grid.innerHTML + result;
                    expertiseCards = [...document.querySelectorAll('.ldr-expertise-grid .ldr-expertise-card')];

                    const cards = [...grid.children].slice(-postsNumber);

                    gsap.from(cards, {
                        autoAlpha: 0,
                        stagger: 0.1
                    });
                    
                    expertiseCards.forEach((card) => {
                        const coverImage = card.querySelector('.card-img-top');
                        const cardLink = card.querySelector('.btn');
                        
                        cardLink.addEventListener('mouseover', () => {
                            coverImage.classList.add('is-hovered');
                        });
                        cardLink.addEventListener('mouseout', () => {
                            coverImage.classList.remove('is-hovered');
                        });
                    });
                }
            });

            return false;
        };
        
        if(window.acf) {
            loadData(currentFilter, excludeSticky, cardSettings, postsNumber, currentPage);
        }

        /* Load data on document load */
        window.addEventListener('DOMContentLoaded', () => loadData(currentFilter, excludeSticky, cardSettings, postsNumber, currentPage));

        /* Load more */
        loadMoreBtn && loadMoreBtn.addEventListener('click', (e) => loadMoreExpertises(e));
    };

	document.querySelectorAll('.ldr-expertise-grid').forEach((elem) => ldrExpertiseGrid(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=expertise-grid', ldrExpertiseGrid);
    }

})();
