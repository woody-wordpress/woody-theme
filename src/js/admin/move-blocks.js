!(function ($) {
    $('#post').each(function () {
        const createMoveBehavior = element => {
            if (!element.querySelector('.move-block-container')) {
                // Creating container for the button and the tooltip
                let container = document.createElement('div');
                container.classList.add('move-block-container');

                // Creating a button to call the tooltip
                let button = document.createElement('a');
                button.className = "acf-icon -arrow-combo small light acf-js-tooltip move-button";
                button.href = "#";
                button.dataset.name = 'move-layout';
                button.title = 'Déplacer le bloc';

                // Creating the tooltip
                let tooltip = document.createElement('div');
                tooltip.classList.add('move-block-tooltip');
                tooltip.innerHTML = `<label>Déplacer vers la section: </label>`

                // Creating a select to specify the section to move the block to
                let tooltipSelect = document.createElement('select');
                tooltipSelect.classList.add('move-block-select');
                tooltip.append(tooltipSelect);
                let tooltipOpen = false;

                // Binding events
                tooltipSelect.addEventListener('change', e => { moveBlock($(element), tooltipSelect.value, tooltip); });
                window.addEventListener('click', e => {
                    // If click is outside the tooltip and tooltip is open then toggle it
                    if (!tooltip.contains(e.target) && tooltipOpen) {
                        // Prevent tooltip from staying closed when clicking on the button
                        if (button.contains(e.target)) return;
                        tooltipOpen = toggleMoveTooltip(tooltipOpen, element.parentElement.firstElementChild.name, tooltip, tooltipSelect);
                    }
                });
                button.addEventListener('click', e => { tooltipOpen = toggleMoveTooltip(tooltipOpen, element.parentElement.firstElementChild.name, tooltip, tooltipSelect); });

                // Adding elements to DOM
                container.append(button);
                container.append(tooltip);
                element.prepend(container);
            }
        }

        const moveBlock = (element, section, tooltip = null) => {
            // Close tooltip
            if (tooltip) $(tooltip).removeClass('open');

            // Select section
            const sections = document.querySelectorAll('div[data-name="section_content"]');

            const sectionIndex = Math.min(Math.max(0, section), sections.length - 1);
            const newSectionKey = sections[sectionIndex].parentElement.parentElement.getAttribute('data-id');

            const values = sections[sectionIndex].querySelectorAll('.acf-flexible-content > .values');
            const blocks = values[values.length - 1];
            const last = blocks.lastElementChild;

            const oldValues = element[0].parentElement.parentElement;
            const oldSection = oldValues.parentElement;
            const prevSectionKey = oldSection.parentElement.parentElement.parentElement.parentElement.getAttribute('data-id');

            $(blocks).closest('.acf-flexible-content').removeClass('-empty');

            // Get field keys
            const prevKey = element.parent().data('id');
            const newKey = acf.uniqid('layout_');
            // Duplicate field and remove original
            let layout = acf.duplicate({
                $el: $(element).parent(),
                search: prevKey,
                replace: newKey,
                rename: (name, value, search, replace) => {
                    value = value.replace(prevSectionKey, newSectionKey);

                    const indexOfFirstKey = value.indexOf(newSectionKey);
                    const oldValueSubstr = value.substr(indexOfFirstKey + newSectionKey.length);
                    const newValueSubstr = oldValueSubstr.replace(prevKey, newKey);

                    value = value.replace(oldValueSubstr, newValueSubstr);
                    return value;
                },
                append: ($el, $el2) => {
                    if (last) $(last).after($el2);
                    else $(blocks).append($el2);
                },
            });
            acf.remove($(element).parent());

            // Reload TinyMCE
            layout.find('.acf-field-wysiwyg').each(function () {
                let wysiwyg = $(this);
                let textarea = wysiwyg.find('textarea');
                let inputVal = textarea.val();
                let textarea_id = textarea.attr('id');

                let iframe = layout.find('#' + textarea_id + '_ifr');
                if (iframe) {
                    iframe.closest('.mce-tinymce.mce-container.mce-panel').remove();
                    acf.tinymce.destroy(textarea_id);
                    acf.tinymce.initialize(textarea_id, {
                        tinymce: true,
                        quicktags: true,
                        toolbar: 'full',
                        mode: "text",
                    });

                    textarea
                        .val(inputVal)
                        .removeAttr('style');

                    layout.find('switch-html').on('click', function () {
                        textarea.css({ "display": "block", "min-height": "300px" });
                    });
                }
            });

            let fieldKey = `acf[field_5afd2c6916ecb][${newSectionKey}][field_5b043f0525968][${newKey}][acf_fc_layout]`;

            // Use ACF RENDER FUNCTION TO SORT NUMBERS
            acf.getFields({ type: 'flexible_content' }).forEach(element => { element.render() })

            const layoutMoveButton = layout.find('.move-button');
            const layoutMoveTooltip = layout.find('.move-block-tooltip');
            const layoutMoveSelect = layout.find('.move-block-select');
            const layoutMoveContainer = layout.find('.move-block-container');

            let tooltipOpen = false;

            // Bind new move button event
            layoutMoveSelect.on('change', e => { moveBlock(layoutMoveContainer.parent(), layoutMoveSelect[0].value, layoutMoveTooltip[0]); });
            layoutMoveButton.on('click', e => { tooltipOpen = toggleMoveTooltip(tooltipOpen, fieldKey, layoutMoveTooltip[0], layoutMoveSelect[0]); });

            // Bind closing tooltip on click outside
            window.addEventListener('click', e => {
                if (!layoutMoveTooltip[0].contains(e.target) && tooltipOpen) {
                    if (layoutMoveButton[0].contains(e.target)) return;
                    tooltipOpen = toggleMoveTooltip(tooltipOpen, layoutMoveContainer.parent().parent()[0].firstElementChild.name, layoutMoveTooltip[0], layoutMoveSelect[0]);
                }
            });

            // Overwrite name with correct field key
            layout.children().first().attr("name", fieldKey);

            openSection(document.querySelector(`[data-id="row-${sectionIndex}"]`), sectionIndex);

            if (
                (oldValues.lastElementChild.classList.contains('acf-temp-remove') && oldValues.children.length === 1)
                || oldValues.children.length === 0
            )
                oldSection.classList.add('-empty');
        }

        const toggleMoveTooltip = (open, key, tooltip, select) => {
            select.innerHTML = "";
            const sections = document.querySelectorAll('[data-name="section_content"]');
            const sectionLength = sections.length - 1;
            let isInSection = -1;

            for (let i = 0; i < sectionLength; ++i) {
                const sectionTitle = sections[i].parentElement.querySelector('[data-name="bo_section_title"] input[type="text"]').value;
                const sectionIndex = sections[i].parentElement.parentElement.getAttribute('data-id');
                if (key.startsWith(`acf[field_5afd2c6916ecb][${sectionIndex}]`)) isInSection = i;
                select.innerHTML += `<option value="${i}">${sectionTitle || i + 1}</option>`;
            }

            select.value = (isInSection >= 0) ? isInSection : 0;
            tooltip.classList.toggle('open');
            return !open;
        }

        const openSection = (section) => {
            if (section.classList.contains('-collapsed'))
                section.classList.remove('-collapsed');
        }


        // Selector to get all layout controls in sections without getting those in tabs groups
        const selector = 'div[data-name="section_content"]>.acf-input>.acf-flexible-content>.values>.layout>.acf-fc-layout-controls';

        // Observe if a block is added to section and add move behavior to the new block
        acf.addAction('append', function ($el) {
            if ($el.hasClass('layout') && $el.find('.acf-fc-layout-controls')) {
                createMoveBehavior($el.find('.acf-fc-layout-controls')[0]);
            }
        });

        // Add move behavior to current blocks
        document.querySelectorAll(selector).forEach(element => {
            createMoveBehavior(element);
        });
    });
})(jQuery);
