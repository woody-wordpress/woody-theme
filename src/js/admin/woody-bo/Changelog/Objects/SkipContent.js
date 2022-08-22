import CookiesWoody from './Cookies';

export default class SkipContent {
    constructor() {
        this.element = document.querySelector('.skipContent');
        this.element.textContent = 'Passer';
        this.cookies = new CookiesWoody;
        this.close();
    }
    close() {
        this.element.addEventListener('click', () => {
            if(this.element.classList.contains('skipContent')) {
                document.querySelector('#changelog').remove();
                document.querySelector('body').classList.remove('openModalChangelog');
                this.cookies.setCookie('woodyUpdate',1,14);
            }
        });
    }
    changeToPrevious() {
        if(document.querySelector('.contentContainer').querySelector('.slide').classList.contains('slide-active') != true) {
            this.element.textContent = 'Précédent';
            this.element.classList.remove('skipContent');
            this.element.classList.add('prevContent');
        }
    }
}
