import Account from './Account';

export default class AdminTopBar {
    constructor(){
        this.element = document.querySelector('#wpadminbar');
        this.tools = document.createElement('section');
        this.tools.classList.add('tools');
        this.container = document.createElement('section');
        this.container.classList.add('wrapper');
        if(this.element) {
            this.init();
        }
    }

    init() {
        const woodyAccount = new Account('#wp-admin-bar-my-account');
        this.createTools();
        document.querySelector('#wp-admin-bar-site-name').remove();
        document.querySelector('#wp-admin-bar-new-content').remove();
        document.querySelector('#wp-admin-bar-woody-dev-tools').remove();
        document.querySelector('#wp-admin-bar-warm-all-dropzone').remove();
        document.querySelector('#wp-admin-bar-flush-varnish').remove();
        document.querySelector('#wp-admin-bar-view').remove();
        document.querySelector('#wp-admin-bar-duplicate-post').remove();
    }

    createTools() {
        let icon = document.createElement('span')
        icon.classList.add('tool-icon');
        icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18.103" height="17.047" viewBox="0 0 18.103 17.047"><g id="woodydevtools" transform="translate(0 0)"><g id="Groupe_21" data-name="Groupe 21" transform="translate(0 0)"><path id="Tracé_1" data-name="Tracé 1" d="M41.545,0a2.421,2.421,0,0,1,1.754.7l4.059,3.675.435-.47,1.55,1.4L46.982,7.924l-1.55-1.413.385-.425L41.736,2.39a2.36,2.36,0,0,0-1.759-.58,3.636,3.636,0,0,0-1.339.327Q40.006.079,41.545,0Z" transform="translate(-31.24 0)" fill="#000"/><rect id="Rectangle_41" data-name="Rectangle 41" width="2.517" height="0.486" transform="translate(15.093 3.246) rotate(-137.845)" fill="#000"/><path id="Tracé_2" data-name="Tracé 2" d="M8.542,50.429,3.583,55.666l2.379,2.154,4.728-5.453Z" transform="translate(-2.897 -40.772)" fill="#000"/><g id="Groupe_20" data-name="Groupe 20" transform="translate(0 1.588)"><path id="Tracé_3" data-name="Tracé 3" d="M46.03,23.259l2.9-3.345-1.877-1.7-3.04,3.225Z" transform="translate(-35.585 -16.316)" fill="#000"/><path id="Tracé_4" data-name="Tracé 4" d="M15.918,21.114a2.64,2.64,0,0,0,.068-.733,2.559,2.559,0,0,0-2.706-2.44,2.924,2.924,0,0,0-.723.13L9.993,15.76,10,15.747,7.966,13.909l0,0-2.684-2.43a2.8,2.8,0,0,0,.058-.722,2.5,2.5,0,0,0-.84-1.8A2.48,2.48,0,0,0,2.641,8.3a2.685,2.685,0,0,0-.733.141l2.073,1.877-1.837,2.04L.071,10.48A2.648,2.648,0,0,0,0,11.213a2.479,2.479,0,0,0,.839,1.782,2.479,2.479,0,0,0,1.856.658,2.714,2.714,0,0,0,.774-.153l.9.815,0,0L10.233,19.6l-.011.012.507.461a2.7,2.7,0,0,0-.076.775,2.5,2.5,0,0,0,.834,1.793,2.475,2.475,0,0,0,1.862.658,2.646,2.646,0,0,0,.722-.14L12,21.277l1.847-2.041Z" transform="translate(0 -8.296)" fill="#000"/></g></g></g></svg>`;
        this.tools.appendChild(icon);

        if (document.querySelector('#wp-admin-bar-view') || document.querySelector('#wp-admin-bar-duplicate-post')) {
            let title = document.createElement('p');
            title.textContent = 'Actions';
            title.classList.add('title');
            this.container.appendChild(title);

            if (document.querySelector('#wp-admin-bar-view')) {
                let seePage = document.createElement('a');
                seePage.href = document.querySelector('#wp-admin-bar-view > a').href;
                seePage.textContent = 'Voir la page';
                this.container.appendChild(seePage);
            }

            if (document.querySelector('#wp-admin-bar-duplicate-post')) {
                let duplicatePage = document.createElement('a');
                duplicatePage.href = document.querySelector('#wp-admin-bar-duplicate-post > a').href;
                duplicatePage.textContent = 'Dupliquer la page';
                this.container.appendChild(duplicatePage);
            }
        }

        let title = document.createElement('p');
        title.textContent = 'Outils';
        title.classList.add('title');
        this.container.appendChild(title);

        let dropzone = document.createElement('a');
        dropzone.href = document.querySelector('#wp-admin-bar-warm-all-dropzone > a').href;
        dropzone.textContent = 'Rafraichir la Dropzone';
        this.container.appendChild(dropzone);

        let varnish = document.createElement('a');
        varnish.href = document.querySelector('#wp-admin-bar-flush-varnish > a').href;
        varnish.textContent = 'Flush Varnish';
        this.container.appendChild(varnish);

        this.container.appendChild(document.querySelector('#wp-admin-bar-query-monitor'));
        this.container.querySelector('.ab-sub-wrapper').setAttribute('style', 'display:none; pointer-event:none');

        document.querySelector('#wp-admin-bar-top-secondary').appendChild(document.querySelector('#wp-admin-bar-languages'));

        this.tools.appendChild(this.container);
        document.querySelector('#wp-admin-bar-top-secondary').appendChild(this.tools);
    }
}
