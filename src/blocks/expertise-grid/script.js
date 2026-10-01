/**
 * Block: Expertise Grid
 */
import $ from 'jquery';
import gsap from 'gsap';

(function() {
    const ldrExpertiseGrid = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.classList && el.classList.contains('ldr-expertise-grid') ? el : el.querySelector('.ldr-expertise-grid');

        // Block markup not rendered (yet), e.g. ACF preview is still loading
        if(!block) {
            return;
        }

        const cardSettings = (block.dataset && block.dataset.cardSettings && JSON.parse(block.dataset.cardSettings));
        const customSelection = (block.dataset && block.dataset.selectedExpertise && JSON.parse(block.dataset.selectedExpertise)) || [];
        const excludedExpertises = (block.dataset && block.dataset.excludedExpertise && JSON.parse(block.dataset.excludedExpertise)) || [];
        const filters = block.querySelector('.grid-filter');
        const filterBtns = filters && filters.querySelectorAll('.dropdown-item');
        const resetFilterBtn = block.querySelector('.reset-filter');
        const copyFilteredResultsBtn = block.querySelector('.copy-filtered-results');
        const expertiseCategories = (block.dataset && block.dataset.expertiseCategories && JSON.parse(block.dataset.expertiseCategories)) || [];
        const expertiseType = (block.dataset && block.dataset.expertiseType) || 'all';
        const excludeSticky = (block.dataset && block.dataset.excludeSticky) || 0;
        const output = block.querySelector('.query-output');
        const loader = output && output.querySelector('.loader');
        const grid = output && output.querySelector('.grid-items');
        const postsNumber = (block.dataset) && parseInt(block.dataset.expertiseNumber) || -1;

        if(!output || !grid) {
            return;
        }
        const loadMoreBtn = block.querySelector('.load-more');
        let currentPage = 1;
        let currentFilter = 0;
        let expertiseCards = [];

        const loadMoreExpertises = (el) => {
            currentPage++;

            loader && loader.classList.remove('d-none');

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
                    expertiseType: expertiseType,
                    expertiseCategories: expertiseCategories,
                    cardSettings: cardSettings,
                    excludeSticky: excludeSticky,
                    postsNumber: postsNumber,
                    paged: currentPage,
                },
                success: (result) => {
                    loader && loader.classList.add('d-none');
                    grid.innerHTML = grid.innerHTML + result;
                    expertiseCards = [...grid.querySelectorAll('.ldr-expertise-card')];

                    const cards = postsNumber > 0 ? [...grid.children].slice(-postsNumber) : [...grid.children];

                    gsap.from(cards, {
                        autoAlpha: 0,
                        stagger: 0.1
                    });
                    
                    expertiseCards.forEach((card) => {
                        const coverImage = card.querySelector('.card-img-top');
                        const cardLink = card.querySelector('.btn');

                        if(!coverImage || !cardLink) {
                            return;
                        }
                        
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
