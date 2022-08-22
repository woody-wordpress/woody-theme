import CloseModal from './Objects/CloseModal';
import SkipContent from './Objects/SkipContent';
import NextContent from './Objects/NextContent';
import CookiesWoody from './Objects/Cookies';

export default class Changelog {
    constructor(){
        this.cookies = new CookiesWoody;
        if (this.cookies.getCookie('woodyUpdate') != 1 && document.querySelector('#woody-update')) {
            document.querySelector('body').classList.add('openModalChangelog');
            this.closeModalContainer = new CloseModal;
            this.skipContentContainer = new SkipContent;
            this.nextContentContainer = new NextContent(this.skipContentContainer);
        }
    }
}
