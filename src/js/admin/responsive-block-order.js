const posts = document.querySelectorAll('#post');

if (posts.length > 0) {
    posts.forEach(() => {


        //On créé l'interface de gestion de l'ordre des blocs
        const createResponsiveOrderInterface = (referenceElement) => {
            let listBlocks = referenceElement.parentNode.querySelector("div[data-name='section_content'] .acf-flexible-content .values");
            if (listBlocks != null){
                let nbBlocks = listBlocks.querySelectorAll('.layout').length;
                let hiddenRespOrderField = referenceElement.querySelector('.acf-input input');
                if (hiddenRespOrderField != null){
                    let hiddenRespOrderFieldValues = hiddenRespOrderField.value.split('-');
                    let max = 9;

                    let newNode = document.createElement('div');
                        newNode.classList.add('block-responsive-order');
                    let ul = document.createElement('ul');
                        ul.classList.add('block-list');
                        newNode.appendChild(ul);

                    for (let i = 0; i < nbBlocks; i++){
                        let li = document.createElement('li');
                        li.classList.add('block-item');
                        if(hiddenRespOrderFieldValues[i] != null && hiddenRespOrderFieldValues[i] != ''){
                            li.innerHTML = getOrderInterfaceHtml(i,max, hiddenRespOrderFieldValues[i]);
                        }else{
                            li.innerHTML = getOrderInterfaceHtml(i,max, max);
                        }
                        ul.appendChild(li);
                    }
                    referenceElement.append(newNode);

                    let reponsiveLayoutInputs = referenceElement.querySelectorAll('.responsive-layout-input');
                    if (reponsiveLayoutInputs.length > 0) {
                        reponsiveLayoutInputs.forEach( input => {
                            input.addEventListener('input', function (evt) {
                                hiddenRespOrderField.value = formatHiddenRespOrderFieldValue(reponsiveLayoutInputs);
                            });
                            hiddenRespOrderField.value = formatHiddenRespOrderFieldValue(reponsiveLayoutInputs);
                        });
                    }
                }
            }
        }

        // on écris dynamiquement le champ caché avec les données des inputs
        const formatHiddenRespOrderFieldValue = (inputs) => {
            let hiddenRespOrderFieldValue = "" ;
            let separator = "-";
            for(let i = 0; i < inputs.length; i++){
                if (i < inputs.length - 1){
                    hiddenRespOrderFieldValue += inputs[i].value + separator;
                }else{
                    hiddenRespOrderFieldValue += inputs[i].value;
                }
            }

            return hiddenRespOrderFieldValue;
        }

        // On créé l'html de l'interface de gestion de l'ordre des blocs
        const getOrderInterfaceHtml = (index,max, value) => {

            let html = `<div class="responsive-layout-block"><span class="responsive-layout-index">${index+1}</span></div><input class="responsive-layout-input" type="number" value="${value}" min="0" max="${max}">`;

            return html;
        }


        // On appelle la fonction qui créer l'interface de gestion de l'ordre des blocs au clic sur l'onglet "Responsive", ou si l'onglet "Responsive" est actif.
        acf.addAction('ready', function(){
            const sectionMobileOrder = document.querySelectorAll(".acf-tab-group [data-key='field_61e67f8d81ca3']");

            if (sectionMobileOrder.length > 0) {
                sectionMobileOrder.forEach(mobileOrderBlock => {
                    let section=mobileOrderBlock.closest(".acf-row").querySelector("[data-name='section_mobile_order']");
                    if (section != null){
                        mobileOrderBlock.addEventListener("click", function(){
                            let blockResponsiveOrder = section.querySelector('.block-responsive-order');
                            if(blockResponsiveOrder != null){
                                blockResponsiveOrder.remove();
                            }
                            createResponsiveOrderInterface(section);
                        }, false);
                        if(mobileOrderBlock.parentNode.classList.contains('active')){
                            createResponsiveOrderInterface(section);
                        }
                    }
                });
            }
        });


    });
}
