/**
 * Block: Post Grid
 */
import $ from 'jquery';
import gsap from 'gsap';

(function() {
    const ldrPostGrid = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-post-grid') : el;
        const cardSettings = (block.dataset && block.dataset.cardSettings && JSON.parse(block.dataset.cardSettings));
        const customSelection = (block.dataset && block.dataset.selectedPosts && JSON.parse(block.dataset.selectedPosts)) || [];
        const filters = block.querySelector('.ldr-post-grid .grid-filter');
        const filterBtns = filters && filters.querySelectorAll('.ldr-post-grid .dropdown-item');
        const resetFilterBtn = block.querySelector('.reset-filter');
        const copyFilteredResultsBtn = block.querySelector('.copy-filtered-results');
        const excludeSticky = (block.dataset && block.dataset.excludeSticky) || 0;
        const output = block.querySelector('.ldr-post-grid .query-output');
        const loader = output.querySelector('.ldr-post-grid .loader');
        const grid = output.querySelector('.ldr-post-grid .grid-items');
        const postsNumber = (block.dataset) && parseInt(block.dataset.postsNumber) || -1;
        const loadMoreBtn = block.querySelector('.load-more');
        let currentPage = 1;
        let currentFilter = 0;
        let postCards = [];
        
        const filterQuery = (el) => {
            currentFilter = parseInt(el.target.id);

            if(currentFilter > 0) {
                resetFilterBtn.classList.remove('d-none');
                copyFilteredResultsBtn.classList.remove('d-none');
            } else {
                resetFilterBtn.classList.add('d-none');
                copyFilteredResultsBtn.classList.add('d-none');
            }

            copyFilteredResultsBtn.setAttribute('data-category', el.target.dataset.slug);
            
            grid.innerHTML = '';
            loader.classList.remove('d-none');
            loadMoreBtn.classList.add('d-none');

            loadData(currentFilter, excludeSticky, cardSettings, -1, 1);
        }

        const resetFilterQuery = (el) => {
            resetFilterBtn.classList.add('d-none');
            copyFilteredResultsBtn.classList.add('d-none');
            copyFilteredResultsBtn.removeAttribute('data-category');
            
            currentFilter = 0;
            currentPage = 1;
            grid.innerHTML = '';

            loader.classList.remove('d-none');
            loadMoreBtn.classList.remove('d-none');

            loadData(0, excludeSticky, cardSettings, postsNumber, 1);
        }

        const copyFilteredResults = (el) => {
            const slug = el.target.dataset.category;
            const url = window.location.href + 'category/' + slug;
            if (!navigator.clipboard) {
                console.log('Clipboard not available!');
                return;
            }
            try {
                navigator.clipboard.writeText(url);
              } catch (err) {
                console.error('Failed to copy!', err);
            }
        }

        const loadMorePosts = (el) => {
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
                    action: 'load_posts',
                    filter: currentFilter,
                    customSelection: customSelection,
                    cardSettings: cardSettings,
                    excludeSticky: excludeSticky,
                    postsNumber: postsNumber,
                    paged: currentPage,
                },
                success: (result) => {
                    loader.classList.add('d-none');
                    grid.innerHTML = grid.innerHTML + result;
                    postCards = [...document.querySelectorAll('.ldr-post-grid .ldr-post-card')];

                    const cards = [...grid.children].slice(-postsNumber);

                    gsap.from(cards, {
                        autoAlpha: 0,
                        stagger: 0.1
                    });
                    
                    postCards.forEach((card) => {
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

        /* Filter data */
        filterBtns && filterBtns.forEach((button) => button.addEventListener('click', (e) => filterQuery(e)));
        resetFilterBtn && resetFilterBtn.addEventListener('click', (e) => resetFilterQuery(e));
        copyFilteredResultsBtn && copyFilteredResultsBtn.addEventListener('click', (e) => copyFilteredResults(e));

        /* Load more */
        loadMoreBtn && loadMoreBtn.addEventListener('click', (e) => loadMorePosts(e));
    };

	document.querySelectorAll('.ldr-post-grid').forEach((elem) => ldrPostGrid(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=post-grid', ldrPostGrid);
    }

})();
