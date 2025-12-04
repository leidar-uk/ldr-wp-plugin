/**
 * Block: Team Grid
 */
import $ from 'jquery';
import gsap from 'gsap';

(function() {
    const ldrTeamGrid = (elem) => {
        const el = (elem[0] === undefined) ? elem : elem[0];
        const block = el.querySelector('.acf-block-preview') ? el.querySelector('.ldr-team-grid') : el;
        const dataFilter = (block.dataset && parseInt(block.dataset.filter)) || 0;
        const customSelection = (block.dataset && block.dataset.selectedMembers && JSON.parse(block.dataset.selectedMembers)) || [];
        const excludedMembers = (block.dataset && block.dataset.excludedMembers && JSON.parse(block.dataset.excludedMembers)) || [];
        const filters = block.querySelector('.grid-filter');
        const filterToggle = filters && filters.querySelector('.dropdown-toggle');
        const filterBtns = filters && filters.querySelectorAll('.dropdown-item');
        const output = block.querySelector('.query-output');
        const loader = output.querySelector('.loader');
        const grid = output.querySelector('.grid-items');
        let memberCards = [];
        
        const filterQuery = (el) => {
            const filter = parseInt(el.target.id);
            gsap.to('.ldr-member-card', {
                autoAlpha: 0,
                stagger: 0.1
            });
            loader.classList.remove('d-none');
            if(filterToggle) {
                filterToggle.innerText = el.target.innerText;
            }
            loadData(filter);
        }

        const loadData = (filter = 0) => {
            $.ajax({
                url: themeData.wpAjax,
                type: 'POST',
                data: {
                    action: 'load_team_members',
                    filter: filter,
                    customSelection: customSelection,
                    excludedMembers: excludedMembers,
                },
                success: (result) => {
                    loader.classList.add('d-none');
                    grid.innerHTML = result;
                    memberCards = [...document.querySelectorAll('.ldr-member-card')];

                    gsap.from('.ldr-member-card', {
                        autoAlpha: 0,
                        stagger: 0.1
                    });
                    
                    memberCards.forEach((card) => {
                        const profileImage = card.querySelector('.profile-image');
                        const bioLink = card.querySelector('.bio-link');
                        
                        bioLink.addEventListener('mouseover', () => {
                            profileImage.classList.add('is-hovered');
                        });
                        bioLink.addEventListener('mouseout', () => {
                            profileImage.classList.remove('is-hovered');
                        });
                    });
                }
            });

            return false;
        };
        
        if(window.acf) {
            loadData(dataFilter);
        }

        /* Load data on document load */
        window.addEventListener('DOMContentLoaded', () => loadData(dataFilter));

        /* Filter data */
        filterBtns && filterBtns.forEach((button) => button.addEventListener('click', (e) => filterQuery(e)));
    };

	document.querySelectorAll('.ldr-team-grid').forEach((elem) => ldrTeamGrid(elem));

    if(window.acf) {
        window.acf.addAction('render_block_preview/type=team-grid', ldrTeamGrid);
    }

})();
