!(function ($, undefined) {
    $('#post').each(function () {
        var cloneEvent = new Event("cloneBlock");
        // This is dope
        var addBlock = function (field, args) {
            var $el = acf.duplicate({
                target: field.$clone(args.layout), //TODO: Revoir ce JS qui génère une erreur Cannot read properties of undefined (reading '$clone')
                append: field.proxy(function ($el, $el2) {

                    // append
                    if (args.before) {
                        args.before.before($el2);
                    } else {
                        field.$layoutsWrap().append($el2);
                    }

                    // enable
                    acf.enable($el2, field.cid);

                    // render
                    field.render();
                })
            });

            // trigger change for validation errors
            field.$input().trigger('change');

            return $el;
        };
        var name = "";
        var id = "";
        var clone;

        $(document).on('click', '.acf-button[data-name="add-layout"]', function () {
            addlayoutbutton = $(this);
            name = addlayoutbutton.closest('.acf-flexible-content').find('input[type="hidden"]').attr('name');
            clone = addlayoutbutton.closest('.acf-flexible-content').find('.clones');
            id = name.replace(/\]\[/g, '-');
            id = id.replace(/\]|\[/g, '-');
            if (id.substr(id.length - 1) == '-') {
                id = id.substr(0, id.length - 1);
            }
        });

        document.addEventListener('click', () => {
            const buttons = document.querySelectorAll('.acf-tooltip.acf-fc-popup>ul>li>a');

            for (const button of buttons) {
                let layouts = clone.find('div[data-layout="' + button.dataset.layout + '"]');
                let none = true;
                layouts.each(function () {
                    if ($(this).closest('[data-name="light_section_content"]').length == 0) {
                        none = false;
                    }
                });

                if (layouts.length <= 0 || none) {

                    if (!button.getAttribute('hasListener')) {
                        button.addEventListener('click', (e) => {

                            let layouts = clone.find('div[data-layout="' + button.dataset.layout + '"]');
                            let none = true;
                            layouts.each(function () {
                                // Si le layout est enfant de light section_content alors, ça ne compte pas ce n'est pas un clone qui nous intéresse
                                if ($(this).closest('[data-name="light_section_content"]').length == 0) {
                                    none = false;
                                }
                            });

                            if (layouts.length <= 0 || none) {
                                $.ajax({
                                    url: ajaxurl,
                                    data: {
                                        action: 'generate_layout_acf_clone',
                                        layout: button.dataset.layout,
                                        key: 'field_5b043f0525968'
                                    },
                                    type: 'GET',
                                    success: function (response) {
                                        response = response.replace(/name="#rowindex-name#/g, 'name="' + name);
                                        response = response.replace(/for="#rowindex-name#/g, 'for="' + id);
                                        response = response.replace(/id="#rowindex-name#/g, 'id="' + id);

                                        // Add clone
                                        clone.append(response);

                                        let fields = acf.getFields({ key: 'field_5b043f0525968' });
                                        let field;
                                        fields.forEach(element => {
                                            if (element.$el[0] == clone.closest('.acf-field-flexible-content')[0]) {
                                                field = element;
                                            }
                                        });

                                        // Add Block
                                        $el = addBlock(field, {
                                            layout: button.dataset.layout,
                                        });
                                    },
                                    error: function (error) {
                                        console.warn(error);
                                    }
                                });
                            }
                            window.dispatchEvent(cloneEvent);
                        });

                        button.setAttribute('hasListener', true);
                    }
                }
            }
        });
    });
})(jQuery);
