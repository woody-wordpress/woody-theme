export default class Account {
    constructor(selector) {
        this.element = document.querySelector(selector);
        if(this.element) {
            this.init();
        }
    }

    init() {
        this.head = this.element.querySelector('.ab-item');
        this.imgUrl = this.head.querySelector('img').src;
        this.clearHead();
    }

    clearHead() {
        this.head.innerHTML = `<img alt="" src="${this.imgUrl}" class="avatar avatar-26 photo" height="26" width="26" loading="lazy">`;
    }
}
