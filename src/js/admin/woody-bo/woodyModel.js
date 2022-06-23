import EditPageTool from './EditPageTool';

export default class woodyModel extends EditPageTool {
    constructor(selector) {
        super(selector);
    }

    init() {
        if (this.element) {
            this.buildSwitcher();
            this.buildInside();
            this.manageOpen();
            this.manageSwitcher();
        }
    }

    manageSwitcher() {
        this.element.querySelector('.woody-switcher .woody-switcher-save').addEventListener('click', () => {
            this.element.querySelector('.woody-switcher').dataset.active = 'save';
            this.element.querySelector('.woody-switcher .woody-switcher-apply').classList.remove('active');
            this.element.querySelector('.woody-switcher .woody-switcher-save').classList.add('active');
            this.managePage();
        });
        this.element.querySelector('.woody-switcher .woody-switcher-apply').addEventListener('click', () => {
            this.element.querySelector('.woody-switcher').dataset.active = 'apply';
            this.element.querySelector('.woody-switcher .woody-switcher-apply').classList.add('active');
            this.element.querySelector('.woody-switcher .woody-switcher-save').classList.remove('active');
            this.managePage();
        });
    }

    managePage() {
        if (this.element.querySelector('.woody-switcher').dataset.active === 'apply'){
            this.element.querySelector('.woody-switcher-content fieldset:nth-child(2)').classList.add('active');
            this.element.querySelector('.woody-switcher-content fieldset:nth-child(1)').classList.remove('active');
        }
        else if (this.element.querySelector('.woody-switcher').dataset.active === 'save'){
            this.element.querySelector('.woody-switcher-content fieldset:nth-child(2)').classList.remove('active');
            this.element.querySelector('.woody-switcher-content fieldset:nth-child(1)').classList.add('active');
        }
    }

    buildSwitcher() {
        let switcher = document.createElement('div');
        switcher.classList.add('woody-switcher');
        switcher.dataset.active = 'save';

        let save = document.createElement('span');
        save.classList.add('woody-btn', 'active', 'woody-switcher-save');
        save.textContent = 'Sauvegarder un modèle';

        let apply = document.createElement('span');
        apply.classList.add('woody-btn', 'woody-switcher-apply');
        apply.textContent = 'Utiliser un modèle';

        switcher.appendChild(save);
        switcher.appendChild(apply);
        this.element.querySelector('.inside').appendChild(switcher);
    }

    buildInside() {
        let switcher = document.createElement('div');
        switcher.classList.add('woody-switcher-content');
        let fields = this.element.querySelector('.inside').querySelectorAll('fieldset');
        fields.forEach((field) => {
            switcher.appendChild(field);
        });
        this.element.querySelector('.inside').appendChild(switcher);
        this.managePage();
    }
}