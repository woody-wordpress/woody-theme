export default class woodyPublish {
    constructor() {
        this.element = document.querySelector('#submitdiv');
        this.init();
    }

    init() {
        if (this.element) {
            this.OpenAtLaunch();
            this.OrganizeInside();
            this.buildMoreBtn();
        }
    }

    OpenAtLaunch() {
        this.element.classList.remove('closed');
    }

    OrganizeInside() {
        let minor = this.element.querySelector('#minor-publishing');
        let major = this.element.querySelector('#major-publishing-actions');

        Array.from(major.children).forEach((child) => {
            if (child.getAttribute('id') !== 'publishing-action') {
                minor.appendChild(child);
            }
        });

        Array.from(minor.children).forEach((child) => {
            if (child.getAttribute('id') == 'minor-publishing-actions') {
                major.appendChild(child);
            }
        });
    }

    buildMoreBtn() {
        let more = document.createElement('span');
        more.classList.add('woody-more');
        more.textContent = 'Ouvrir les détails';
        this.element.querySelector('#major-publishing-actions').prepend(more);

        more.addEventListener('click', () => {
            this.element.classList.toggle('open');
            if(this.element.classList.contains('open')) {
                more.textContent = 'Fermer les détails';
            }
            else {
                more.textContent = 'Ouvrir les détails';
            }
        });
    }
}