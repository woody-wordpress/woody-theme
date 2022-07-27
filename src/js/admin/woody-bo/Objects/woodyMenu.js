export default class woodyMenu {
    constructor() {
        this.parent = document.querySelector('#adminmenuwrap');
        this.element = document.createElement('div');
        this.init();
    }

    init() {
        if (this.parent) {
            this.addLogo();
            this.buildBurger();
            this.manageOpen();
            this.createQuickLinks();
        }
    }

    addLogo() {
        let link = document.createElement('a');
        link.classList.add('logo');
        link.href = document.querySelector('#wp-admin-bar-site-name > a').href;

        let logo = document.createElement('img');
        logo.src = `${window.location.origin}/app/themes/${document.querySelector('#wp-admin-bar-woody-dev-tools .disturl').dataset.sitekey}/logo.svg`;

        link.appendChild(logo);
        this.parent.appendChild(link);
    }

    buildBurger() {
        this.element.classList.add('burger-menu');

        let burger = document.createElement('span');
        burger.classList.add('burger');
        burger.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path d="M-5345-967a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Zm-9,0a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Zm9-9a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Zm-9,0a2,2,0,0,1-2-2v-4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2v4a2,2,0,0,1-2,2Z" transform="translate(5356 983.999)" fill="#fefefe"/></svg>`

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
