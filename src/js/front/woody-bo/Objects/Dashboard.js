export default class Dashboard {
    constructor(selector) {
        this.element = document.querySelector(selector);
        this.container = document.createElement('section');
        this.container.classList.add('wrapper');
        this.actions = document.createElement('div');
        this.infos = document.createElement('div');

        if(this.element) {
            this.init();
        }
    }

    init() {
        this.createActions()
        this.createInfos()
        this.element.innerHTML = `<span class="dashboard-icon"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path d="M-5345-967a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Zm-9,0a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Zm9-9a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Zm-9,0a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Z" transform="translate(5356 983.999)" fill="#fefefe"/></svg></span>`;
        this.element.appendChild(this.container);
        this.manageOpen();
    }

    createActions() {
        this.actions.classList.add('actions');

        let title = document.createElement('p');
        title.textContent = 'Actions';
        title.classList.add('title');
        this.actions.appendChild(title);

        let goDashboard = document.createElement('a');
        goDashboard.href = document.querySelector('#wp-admin-bar-site-name > a').href;
        goDashboard.textContent = 'Revenir au tableau de bord';
        this.actions.appendChild(goDashboard);

        if (document.querySelector('#wp-admin-bar-edit')) {
            let editPage = document.createElement('a');
            editPage.href = document.querySelector('#wp-admin-bar-edit > a').href;
            editPage.textContent = 'Modifier la page';
            this.actions.appendChild(editPage);
        }

        if (document.querySelector('#wp-admin-bar-duplicate-post')) {
            let duplicatePage = document.createElement('a');
            duplicatePage.href = document.querySelector('#wp-admin-bar-duplicate-post > a').href;
            duplicatePage.textContent = 'Dupliquer la page';
            this.actions.appendChild(duplicatePage);
        }

        this.container.appendChild(this.actions);
    }

    createInfos() {
        this.infos.classList.add('infos');

        let title = document.createElement('p');
        title.textContent = 'Informations';
        title.classList.add('title');

        this.infos.appendChild(title);
        this.infos.appendChild(document.querySelector('#wp-admin-bar-query-monitor'));
        this.infos.querySelector('.ab-sub-wrapper').remove();

        this.container.appendChild(this.infos);
    }

    manageOpen() {
        document.querySelector('.dashboard-icon').addEventListener('click', () => {
            this.element.classList.toggle('open');
        });
    }
}
