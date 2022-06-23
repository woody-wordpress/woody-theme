export default class woodyMenu {
    constructor() {
        this.parent = document.querySelector('#adminmenuwrap');
        this.element = document.createElement('div');
        this.init();
    }

    init() {
        if (this.parent) {
            this.buildBurger();
            this.manageOpen();
            this.createQuickLinks();
            console.log(window.location)
        }
    }

    buildBurger() {
        this.element.classList.add('burger-menu');

        let burger = document.createElement('span');
        burger.classList.add('burger');

        let content = document.createElement('span');
        content.classList.add('content');
        content.textContent = 'Menu';

        this.element.appendChild(burger);
        this.element.appendChild(content);

        this.parent.appendChild(this.element);
    }

    manageOpen() {
        this.element.addEventListener('click', () => {
            this.parent.classList.toggle('open');

            if(this.parent.classList.contains('open')) {
                document.querySelector('#adminmenuwrap .content').textContent = 'Fermer';
            }
            else {
                document.querySelector('#adminmenuwrap .content').textContent = 'Menu';
            }
        });
    }

    createQuickLinks() {
        if (document.querySelector('body:not(.post-php)')) {
            let parent = this.parent.querySelector('#adminmenu');
            let children = Array.from(parent.children);
            let pageHaveSubMenu = true;
            let quickList = [];
            if(document.querySelector('body.role-administrator')) {
                if(window.location.host.substr(-10) === 'rc-dev.com'){
                    quickList = ['menu-pages','toplevel_page_main-menu', 'toplevel_page_footer-settings', 'toplevel_page_edit-post_type-acf-field-group', 'toplevel_page_mlang'];

                }
                else {
                    quickList = ['menu-pages','toplevel_page_main-menu', 'toplevel_page_footer-settings', 'menu-media', 'menu-tools'];
                }
            }
            else {
                quickList = ['menu-pages','toplevel_page_main-menu', 'toplevel_page_footer-settings', 'menu-media', 'toplevel_page_hawwwai_menu'];
            };
            let toolsBar = document.createElement('div');
            toolsBar.classList.add('woody-tools-bar');
            toolsBar.id = 'adminmenu';

            children.forEach((child) => {
                if (child.classList.contains('wp-has-current-submenu')) {
                    pageHaveSubMenu = false;
                    child.classList.add('fixedAtToolbar');
                    toolsBar.appendChild(child);
                }
            });

            if (pageHaveSubMenu) {
                children.forEach((child) => {
                    if(quickList.includes(child.id)) {
                        toolsBar.appendChild(child);

                        child.addEventListener('mouseover', () => {
                            child.classList.add('opensub');
                        });
                        child.addEventListener('mouseout', () => {
                            child.classList.remove('opensub');
                        });
                    }
                });
            }
            this.parent.appendChild(toolsBar);
        }
    }
}