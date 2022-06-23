export default class EditPageTool {
    constructor(selector) {
        this.element = document.querySelector(selector);
        this.init();
    }

    init() {
        if (this.element) {
            this.manageOpen();
        }
    }

    manageOpen() {
        this.element.querySelector('.postbox-header').addEventListener('click', () => {
            let contains = this.element.classList.contains('closed') ? true : false;
            if (contains) {
                let list = ['ml_box', 'acf-group_5ba8ef4753801', 'acf-group_5b0d380ce3492', 'pageparentdiv', 'woody_model_metabox'];
                Array.from(document.querySelector('#side-sortables').children).forEach((child) => {
                    if (list.includes(child.id)) {
                        child.classList.add('closed');
                    }
                });
            }
            this.element.classList.toggle('closed');
        });
    }
}