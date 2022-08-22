import CookiesWoody from './Cookies';

export default class CloseModal {
    constructor() {
        this.element = document.querySelector('.closeModalContainer');
        this.cookies = new CookiesWoody;
        this.close();
    }
    close() {
        this.element.addEventListener('click', () => {
            document.querySelector('#changelog').remove();
            document.querySelector('body').classList.remove('openModalChangelog');
            this.cookies.setCookie('woodyUpdate',1,14);
        });
    }
}
