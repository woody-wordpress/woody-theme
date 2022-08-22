import CookiesWoody from './Cookies';

export default class NextContent {
    constructor(skipContentContainer) {
        this.element = document.querySelector('.nextContent');
        this.element.textContent = 'Suivant';
        this.skipContent = skipContentContainer;
        this.cookies = new CookiesWoody;
        this.next();
        this.prev();
        this.close();
    }
    next() {
        this.element.addEventListener('click', (e) => {
            let currentSlide = document.querySelector('#changelog .slide-active');
            let currentImg = document.querySelector('#changelog .active');

            if (currentSlide.nextElementSibling.classList.contains('slide') && currentImg.nextElementSibling) {
                currentSlide.nextElementSibling.classList.add('slide-active');
                currentImg.nextElementSibling.classList.add('active');
                currentSlide.classList.remove('slide-active');
                currentImg.classList.remove('active');

                document.querySelector('#changelog').classList.remove(document.querySelector('#changelog').classList);
                document.querySelector('#changelog').classList.add(currentSlide.nextElementSibling.dataset.product);
            }

            if(currentSlide.nextElementSibling.nextElementSibling.classList.contains('actionsContainer')) {
                this.element.textContent = 'Fermer';
                this.element.classList.add('close');
                this.skipContent.element.classList.add('last');
                e.stopImmediatePropagation();
            }

            this.skipContent.changeToPrevious();
        });
    }
    prev() {
        this.skipContent.element.addEventListener('click', () => {
            if(this.skipContent.element.classList.contains('prevContent')) {
                this.skipContent.element.classList.remove('last');
                this.element.classList.remove('close');
                this.element.textContent = 'Suivant';
                let currentSlide = document.querySelector('#changelog .slide-active');
                let currentImg = document.querySelector('#changelog .active');

                if (currentSlide.previousElementSibling && currentImg.previousElementSibling) {
                    currentSlide.previousElementSibling.classList.add('slide-active');
                    currentImg.previousElementSibling.classList.add('active');
                    currentSlide.classList.remove('slide-active');
                    currentImg.classList.remove('active');

                    document.querySelector('#changelog').classList.remove(document.querySelector('#changelog').classList);
                    document.querySelector('#changelog').classList.add(currentSlide.previousElementSibling.dataset.product);
                }

                if(currentSlide.previousElementSibling.previousElementSibling == null) {
                    this.skipContent.element.classList.add('skipContent');
                    this.element.classList.remove('prevContent');
                    this.skipContent.element.textContent = 'Passer';
                }
            }

        });
    }
    close() {
        this.element.addEventListener('click', () => {
            if (this.element.classList.contains('close') && this.skipContent.element.classList.contains('last')) {
                document.querySelector('#changelog').remove();
                document.querySelector('body').classList.remove('openModalChangelog');
                this.cookies.setCookie('woodyUpdate',1,14);
            }
        });
    }
}
