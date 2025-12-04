/**
 * Block: Service Grid
 */
import $ from 'jquery';
import gsap from 'gsap';

(function() {
    const ldrServiceGrid = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-service-grid') : el;
        const dataFilter = (block.dataset && parseInt(block.dataset.filter)) || 0;
        const cardSettings = (block.dataset && block.dataset.cardSettings && JSON.parse(block.dataset.cardSettings));
        const customSelection = (block.dataset && block.dataset.selectedServices && JSON.parse(block.dataset.selectedServices)) || [];
        const filters = block.querySelector('.ldr-service-grid .grid-filter');
        const filterToggle = filters && filters.querySelector('.ldr-service-grid .dropdown-toggle');
        const filterBtns = filters && filters.querySelectorAll('.ldr-service-grid .dropdown-item');
        const excludeSticky = (block.dataset && block.dataset.excludeSticky) || 0;
        const omitChildren = (block.dataset && block.dataset.omitChildren) || 0;
        const output = block.querySelector('.ldr-service-grid .query-output');
        const loader = output.querySelector('.ldr-service-grid .loader');
        const grid = output.querySelector('.ldr-service-grid .grid-items');
        let serviceCards = [];

        console.log(omitChildren)
        
        const filterQuery = (el) => {
            const filter = parseInt(el.target.id);
            gsap.to('.ldr-service-grid .ldr-service-card', {
                autoAlpha: 0,
                stagger: 0.1
            });
            loader.classList.remove('d-none');
            if(filterToggle) {
                filterToggle.innerText = el.target.innerText;
            }
            loadData(filter, excludeSticky, cardSettings, omitChildren);
        }

        const loadData = (filter = 0, excludeSticky = 0, cardSettings = {}, omitChildren = 0) => {
            $.ajax({
                url: themeData.wpAjax,
                type: 'POST',
                data: {
                    action: 'load_services',
                    filter: filter,
                    customSelection: customSelection,
                    cardSettings: cardSettings,
                    excludeSticky: excludeSticky,
                    omitChildren: omitChildren,
                },
                success: (result) => {
                    loader.classList.add('d-none');
                    grid.innerHTML = result;
                    serviceCards = [...document.querySelectorAll('.ldr-service-grid .ldr-service-card')];

                    gsap.from('.ldr-service-grid .ldr-service-card', {
                        autoAlpha: 0,
                        stagger: 0.1
                    });
                    
                    serviceCards.forEach((card) => {
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
            loadData(dataFilter, excludeSticky, cardSettings, omitChildren);
        }

        /* Load data on document load */
        window.addEventListener('DOMContentLoaded', () => loadData(dataFilter, excludeSticky, cardSettings, omitChildren));

        /* Filter data */
        filterBtns && filterBtns.forEach((button) => button.addEventListener('click', (e) => filterQuery(e)));
    };

	document.querySelectorAll('.ldr-service-grid').forEach((elem) => ldrServiceGrid(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=service-grid', ldrServiceGrid);
    }

})();
